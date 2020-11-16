<?php
//   Copyright 2019 NEC Corporation
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.
//
/**
 * 【処理内容】
 *    ホストグループ変数化機能
 *      ホストグループとそれに紐付くホストを変数化する。
 */

if( empty($root_dir_path) ){
    $root_dir_temp = array();
    $root_dir_temp = explode('ita-root', dirname(__FILE__));
    $root_dir_path = $root_dir_temp[0] . 'ita-root';
}

define('ROOT_DIR_PATH',         $root_dir_path);
require_once ROOT_DIR_PATH      . '/libs/backyardlibs/hostgroup/ky_hostgroup_env.php';
require_once HOSTGROUP_LIB_PATH . 'ky_hostgroup_classes.php';
require_once HOSTGROUP_LIB_PATH . 'ky_hostgroup_functions.php';
require_once COMMONLIBS_PATH    . 'common_php_req_gate.php';

try{

    $logPrefix = basename( __FILE__, '.php' ) . '_';
    $tmpDir = "";

    if(LOG_LEVEL === 'DEBUG'){
        // 処理開始ログ
        outputLog($objMTS->getSomeMessage('ITAHOSTGROUP-STD-10001', basename( __FILE__, '.php' )));
    }

    $updateCnt = 0;             // 更新件数
    $insertCnt = 0;             // 登録件数
    $disuseCnt = 0;             // 廃止件数
    $tranStartFlg = false;

    ////////////////////////////////
    // 処理済みフラグを判定
    ////////////////////////////////
    $sql = "SELECT LOADED_FLG,LAST_UPDATE_TIMESTAMP " .
           "FROM A_PROC_LOADED_LIST " .
           "WHERE ROW_ID = :ROW_ID";

    $objQuery = $objDBCA->sqlPrepare($sql);
    if($objQuery->getStatus()===false){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
        outputLog($msg);
        outputLog($sql);
        outputLog($objQuery->getLastError());
        throw new Exception($msg);
    }

    $bindParam = array('ROW_ID'=>2100170005);
    $objQuery->sqlBind($bindParam);

    $r = $objQuery->sqlExecute();
    if (!$r){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
        outputLog($msg);
        outputLog($sql);
        outputLog($objQuery->getLastError());
        throw new Exception($msg);
    }
    // FETCH行数を取得
    $procLoadList = array();
    while ( $row = $objQuery->resultFetch() ){
        $procLoadList[] = $row;
    }
    // DBアクセス事後処理
    unset($objQuery);

    if (1 != count($procLoadList)){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
        outputLog($msg);
        outputLog($sql);
        outputLog($objQuery->getLastError());
        throw new Exception($msg);
    }

    if('1' == $procLoadList[0]['LOADED_FLG']){
        //処理対象がないため処理終了
        if(LOG_LEVEL === 'DEBUG'){
            // 終了ログ出力
            outputLog($objMTS->getSomeMessage('ITAHOSTGROUP-STD-10002', basename( __FILE__, '.php' )));
        }
        return true;
    }
    $procLastUpdateTimeStamp = $procLoadList[0]['LAST_UPDATE_TIMESTAMP'];

    //////////////////////////
    // ホストグループ一覧テーブルを検索
    //////////////////////////
    $hostgroupListTable = new HostgroupListTable($objDBCA, $db_model_ch);
    $sql = $hostgroupListTable->createSselect("WHERE DISUSE_FLAG = '0'");

    // SQL実行
    $result = $hostgroupListTable->selectTable($sql);
    if(!is_array($result)){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
        outputLog($msg);
        throw new Exception($msg);
    }
    $hostgroupListArray = $result;

    // ツリー作成
    $treeArray = makeTree($hierarchy);

    if(false === $treeArray) {
        throw new Exception();
    }

    $linkDataArray = array();

    // ツリーデータから親子関係を作成する
    foreach($treeArray as $treeData) {

        if($treeData['HIERARCHY'] === 1) {
            continue;
        }

        $hgName = NULL;
        foreach($hostgroupListArray as $hostgroupList){

            if($hostgroupList['ROW_ID'] == $treeData['KY_KEY'] - 10000){
                $hgName = $hostgroupList['HOSTGROUP_NAME'];
                $accessAuth = $hostgroupList['ACCESS_AUTH'];
            }
        }

        if(NULL !== $hgName){
            foreach($treeData['ALL_CHILD_IDS'] as $childId) {
                $linkDataArray[] = array('VARS_NAME'    => "VAR_hostgroup_" . $hgName,
                                         'HOSTGROUP_ID' => $treeData['KY_KEY'] - 10000,
                                         'CHILD_ID'     => $childId,
                                         'ACCESS_AUTH'  => $accessAuth,
                                        );
            }
        }
    }

    $linkDataArray = array_values(array_unique($linkDataArray, SORT_REGULAR));

    $hostGroupIds = array();
    $childIds = array();

    foreach($linkDataArray as $key => $data) {
        $hostGroupIds[$key] = $data['HOSTGROUP_ID'];
        $childIds[$key] = $data['CHILD_ID'];
    }
    array_multisort($hostGroupIds, SORT_ASC, $childIds, SORT_ASC, $linkDataArray);

    //////////////////////////
    // ホストグループ変数化テーブルを検索
    //////////////////////////
    $hostgroupVarTable = new HostgroupVarTable($objDBCA, $db_model_ch);
    $sql = $hostgroupVarTable->createSselect("WHERE DISUSE_FLAG = '0'");

    // SQL実行
    $result = $hostgroupVarTable->selectTable($sql);
    if(!is_array($result)){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
        outputLog($msg);
        throw new Exception($msg);
    }
    $hostGrpVarArray = $result;

    // トランザクション開始
    $result = $objDBCA->transactionStart();
    if(false === $result){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', array($result));
        outputLog($msg);
        throw new Exception($msg);
    }
    $tranStartFlg = true;

    // ホストグループ変数化を登録する
    foreach($linkDataArray as $linkData) {

        $matchFlg = false;
        $updateData = NULL;
        $insertData = NULL;

        // ホストグループ変数化メニューの件数分ループ
        foreach($hostGrpVarArray as $hostGrpVar) {

            // ホストグループ変数化メニューのデータとホストグループ名、ホスト名が一致する場合
            if($linkData['HOSTGROUP_ID'] == $hostGrpVar['HOSTGROUP_NAME'] &&
               $linkData['CHILD_ID'] == $hostGrpVar['HOSTNAME']) {
                $updateData = $hostGrpVar;
                $matchFlg = true;
                break;
            }
        }

        // ホストグループ名、ホスト名が一致するデータがない場合
        if(false === $matchFlg) {
            // 登録する
            $insertData['HOSTGROUP_NAME']   = $linkData['HOSTGROUP_ID'];    // ホストグループ名
            $insertData['VARS_NAME']        = $linkData['VARS_NAME'];       // ホストグループ変数名
            $insertData['HOSTNAME']         = $linkData['CHILD_ID'];        // ホスト名
            $insertData['ACCESS_AUTH']      = $linkData['ACCESS_AUTH'];     // アクセス許可ロール
            $insertData['DISUSE_FLAG']      = "0";                          // 廃止フラグ
            $insertData['LAST_UPDATE_USER'] = USER_ID_MAKE_HOST_GRP_VAR;    // 最終更新者

            //////////////////////////
            // 出力用テーブルに登録
            //////////////////////////
            $result = $hostgroupVarTable->insertTable($insertData, $seqNo, $jnlSeqNo);
            if(true !== $result){
                $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
                outputLog($msg);
                throw new Exception($msg);
            }
            $insertCnt ++;
        }
        // ホストグループ変数化メニューのデータとホストグループ変数名が一致しない場合
        else if($linkData['VARS_NAME'] != $hostGrpVar['VARS_NAME'] || $linkData['ACCESS_AUTH'] != $hostGrpVar['ACCESS_AUTH']){
            // 更新する
            $updateData['VARS_NAME']        = $linkData['VARS_NAME'];       // ホストグループ変数名
            $updateData['ACCESS_AUTH']      = $linkData['ACCESS_AUTH'];     // アクセス許可ロール
            $updateData['LAST_UPDATE_USER'] = USER_ID_MAKE_HOST_GRP_VAR;    // 最終更新者

            //////////////////////////
            // 出力用テーブルを更新
            //////////////////////////
            $result = $hostgroupVarTable->updateTable($updateData, $jnlSeqNo);
            if(true !== $result){
                $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
                outputLog($msg);
                throw new Exception($msg);
            }
            $updateCnt ++;
        }
    }
    unset($linkData);

    // 廃止処理のため、ホストグループ変数化メニューの件数分ループ
    foreach($hostGrpVarArray as $hostGrpVar) {

        $matchFlg = false;
        $disuseData = $hostGrpVar;

        // 紐付データの件数分ループ
        foreach($linkDataArray as $linkData) {

            // ホストグループ変数化メニューのデータとホストグループ名、ホスト名が一致する場合
            if($linkData['HOSTGROUP_ID']    == $disuseData['HOSTGROUP_NAME'] &&
               $linkData['CHILD_ID']        == $disuseData['HOSTNAME']){

                $matchFlg = true;
                break;
            }
        }
        
        if(true === $matchFlg) {
            continue;
        }

        // 廃止する
        $disuseData['DISUSE_FLAG']      = "1";                          // 廃止フラグ
        $disuseData['LAST_UPDATE_USER'] = USER_ID_MAKE_HOST_GRP_VAR;    // 最終更新者

        //////////////////////////
        // 出力用テーブルを更新
        //////////////////////////
        $result = $hostgroupVarTable->updateTable($disuseData, $jnlSeqNo);
        if(true !== $result){
            $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
            outputLog($msg);
            throw new Exception($msg);
        }
        $disuseCnt ++;
    }

    ////////////////////////////////
    // 処理済みフラグをONにする
    ////////////////////////////////
    $sql = "UPDATE A_PROC_LOADED_LIST " .
           "SET LOADED_FLG = :LOADED_FLG, LAST_UPDATE_TIMESTAMP = :LAST_UPDATE_TIMESTAMP " .
           "WHERE ROW_ID = :ROW_ID AND LAST_UPDATE_TIMESTAMP = :LAST_UPDATE_TIMESTAMP2 ";

    $objQuery = $objDBCA->sqlPrepare($sql);

    if( $objQuery->getStatus() === false ){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
        outputLog($msg);
        outputLog($sql);
        outputLog($objQuery->getLastError());
        throw new Exception($msg);
    }

    $objDBCA->setQueryTime();
    $bindParam = array('LOADED_FLG'=>'1', 'ROW_ID'=>2100170005, 'LAST_UPDATE_TIMESTAMP'=>$objDBCA->getQueryTime(), 'LAST_UPDATE_TIMESTAMP2'=>$procLastUpdateTimeStamp);
    $objQuery->sqlBind($bindParam);

    $r = $objQuery->sqlExecute();

    if (!$r){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
        outputLog($msg);
        outputLog($sql);
        outputLog($objQuery->getLastError());
        throw new Exception($msg);
    }
    unset($objQuery);

    if($insertCnt > 0 || $updateCnt > 0 ||  $disuseCnt > 0){

        ////////////////////////////////
        // 処理済みフラグをOFFにする
        ////////////////////////////////
        $sql = "UPDATE A_PROC_LOADED_LIST " .
               "SET LOADED_FLG = :LOADED_FLG, LAST_UPDATE_TIMESTAMP = :LAST_UPDATE_TIMESTAMP " .
               "WHERE ROW_ID IN (2100170006, 2100170007) ";

        $objQuery = $objDBCA->sqlPrepare($sql);

        if( $objQuery->getStatus() === false ){
            $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
            outputLog($msg);
            outputLog($sql);
            outputLog($objQuery->getLastError());
            throw new Exception($msg);
        }

        $objDBCA->setQueryTime();
        $bindParam = array('LOADED_FLG' => "0", 'LAST_UPDATE_TIMESTAMP'=>$objDBCA->getQueryTime());
        $objQuery->sqlBind($bindParam);

        $r = $objQuery->sqlExecute();

        if (!$r){
            $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
            outputLog($msg);
            outputLog($sql);
            outputLog($objQuery->getLastError());
            throw new Exception($msg);
        }
        unset($objQuery);
    }

    // コミット
    $result = $objDBCA->transactionCommit();
    if(false === $result){
        $msg = $objMTS->getSomeMessage('ITAHOSTGROUP-ERR-5001', $result);
        outputLog($msg);
        throw new Exception($msg);
    }
    $tranStartFlg = false;

    if(LOG_LEVEL === 'DEBUG'){
        // 件数ログ出力
        outputLog($objMTS->getSomeMessage('ITAHOSTGROUP-STD-10004', array("ホストグループ変数化", $insertCnt, $updateCnt, $disuseCnt)));
        // 終了ログ出力
        outputLog($objMTS->getSomeMessage('ITAHOSTGROUP-STD-10002', basename( __FILE__, '.php' )));
    }
    return true;

}
catch(Exception $e){
    // ロールバック
    if(true === $tranStartFlg){
        $objDBCA->transactionRollback();
    }
    if(LOG_LEVEL === 'DEBUG'){
        // 終了ログ出力
        outputLog($objMTS->getSomeMessage('ITAHOSTGROUP-STD-10003', basename( __FILE__, '.php' )));
    }
    return false;
}
