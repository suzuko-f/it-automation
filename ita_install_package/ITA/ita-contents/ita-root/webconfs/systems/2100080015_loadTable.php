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
//////////////////////////////////////////////////////////////////////
//
//  【処理概要】
//    ・Terraform代入値自動登録設定
//
//////////////////////////////////////////////////////////////////////
// 共通モジュールをロード
if ( empty($root_dir_path) ){
    $root_dir_temp = array();
    $root_dir_temp = explode( "ita-root", dirname(__FILE__) );
    $root_dir_path = $root_dir_temp[0] . "ita-root";
}

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104810");
/*
Terrraform 代入値自動登録設定
*/
    $tmpAry = array(
        'TT_SYS_01_JNL_SEQ_ID'=>'JOURNAL_SEQ_NO',
        'TT_SYS_02_JNL_TIME_ID'=>'JOURNAL_REG_DATETIME',
        'TT_SYS_03_JNL_CLASS_ID'=>'JOURNAL_ACTION_CLASS',
        'TT_SYS_04_NOTE_ID'=>'NOTE',
        'TT_SYS_04_DISUSE_FLAG_ID'=>'DISUSE_FLAG',
        'TT_SYS_05_LUP_TIME_ID'=>'LAST_UPDATE_TIMESTAMP',
        'TT_SYS_06_LUP_USER_ID'=>'LAST_UPDATE_USER',
        'TT_SYS_NDB_ROW_EDIT_BY_FILE_ID'=>'ROW_EDIT_BY_FILE',
        'TT_SYS_NDB_UPDATE_ID'=>'WEB_BUTTON_UPDATE',
        'TT_SYS_NDB_LUP_TIME_ID'=>'UPD_UPDATE_TIMESTAMP'
    );

    $table = new TableControlAgent('D_TERRAFORM_VAL_ASSIGN','COLUMN_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104820"), 'D_TERRAFORM_VAL_ASSIGN_JNL', $tmpAry);
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['COLUMN_ID']->setSequenceID('B_TERRAFORM_VAL_ASSIGN_RIC');
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_TERRAFORM_VAL_ASSIGN_JSQ');
    unset($tmpAryColumn);

    // ----VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定
    $table->setDBMainTableHiddenID('B_TERRAFORM_VAL_ASSIGN');
    $table->setDBJournalTableHiddenID('B_TERRAFORM_VAL_ASSIGN_JNL');
    // 利用時は、更新対象カラムに、「$c->setHiddenMainTableColumn(true);」を付加すること
    // VIEWをコンテンツソースにする場合、構成する実体テーブルを更新するための設定----

    //動的プルダウンの作成用
    $table->setJsEventNamePrefix(true);

    // QMファイル名プレフィックス
    $table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104830"));
    // エクセルのシート名
    $table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104840"));

    $table->setAccessAuth(true);    // データごとのRBAC設定


    ////////////////////////////////////////////////////////////
    // ColumnGroup:パラメータシート 開始
    ////////////////////////////////////////////////////////////
    $cgg = new ColumnGroup($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104850"));

        ////////////////////////////////////////////////////////////
        // カラムグループ メニューグループ(一覧のみ表示)
        ////////////////////////////////////////////////////////////
        $cg = new ColumnGroup($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104860"));

            ////////////////////////////////////////////////////////////
            // メニューグループID
            ////////////////////////////////////////////////////////////
            $c = new IDColumn('MENU_GROUP_ID', $g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104870"), 'A_MENU_GROUP_LIST', 'MENU_GROUP_ID', 'MENU_GROUP_ID', '', array('OrderByThirdColumn'=>'MENU_GROUP_ID'));
            $c->addClass("number");
            $c->setHiddenMainTableColumn(false);
            $c->setAllowSendFromFile(false);
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104880"));
            $c->getOutputType("update_table")->setVisible(false);
            $c->getOutputType("register_table")->setVisible(false);
            $c->getOutputType("excel")->setVisible(false);
            $c->getOutputType("csv")->setVisible(false);
            $c->setDeleteOffBeforeCheck(false);

            $c->getOutputType('json')->setVisible(false); // RestAPIでは隠す

            $objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
            $aryTraceQuery = array(
                array(
                    'TRACE_TARGET_TABLE'=>'A_MENU_LIST_JNL',
                    'TTT_SEARCH_KEY_COLUMN_ID'=>'MENU_ID',
                    'TTT_GET_TARGET_COLUMN_ID'=>'MENU_GROUP_ID',
                    'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
                    'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
                    'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
                )
            );

            $objOT->setTraceQuery($aryTraceQuery);
            $objOT->setFirstSearchValueOwnerColumnID('MENU_ID');
            $c->setOutputType('print_journal_table',$objOT);
            $c->setMasterDisplayColumnType(0);
            $cg->addColumn($c);

            ////////////////////////////////////////////////////////////
            // メニューグループ名
            ////////////////////////////////////////////////////////////
            $c = new IDColumn('MENU_GROUP_ID_CLONE', $g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104890"), 'A_MENU_GROUP_LIST', 'MENU_GROUP_ID', 'MENU_GROUP_NAME');
            $c->setHiddenMainTableColumn(false);
            $c->setAllowSendFromFile(false);
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104900"));
            $c->getOutputType("update_table")->setVisible(false);
            $c->getOutputType("register_table")->setVisible(false);
            $c->getOutputType("excel")->setVisible(false);
            $c->getOutputType("csv")->setVisible(false);

            $c->getOutputType('json')->setVisible(false); // Append RestAPIでは隠す

            $objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
            $aryTraceQuery = array(
                array(
                        'TRACE_TARGET_TABLE'=>'A_MENU_LIST_JNL',
                        'TTT_SEARCH_KEY_COLUMN_ID'=>'MENU_ID',
                        'TTT_GET_TARGET_COLUMN_ID'=>'MENU_GROUP_ID',
                        'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
                        'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
                        'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
                    ),
                array(
                        'TRACE_TARGET_TABLE'=>'A_MENU_GROUP_LIST_JNL',
                        'TTT_SEARCH_KEY_COLUMN_ID'=>'MENU_GROUP_ID',
                        'TTT_GET_TARGET_COLUMN_ID'=>'MENU_GROUP_NAME',
                        'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
                        'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
                        'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
                    )
            );
            $objOT->setTraceQuery($aryTraceQuery);
            $objOT->setFirstSearchValueOwnerColumnID('MENU_ID');
            $c->setOutputType('print_journal_table',$objOT);

            $cg->addColumn($c);

        $cgg->addColumn($cg);
        // カラムグループ（メニューグループ）----

        ////////////////////////////////////////////////////////////
        // カラムグループ メニュー(一覧のみ表示)
        ////////////////////////////////////////////////////////////
        $cg = new ColumnGroup($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104910"));

            ////////////////////////////////////////////////////////////
            // メニューID
            ////////////////////////////////////////////////////////////
            $url = "01_browse.php?no=";
            $c = new LinkIDColumn('MENU_ID_CLONE', $g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104920"), "A_MENU_LIST", 'MENU_ID', "MENU_ID", $url, false, true, '', '', '', '', array('OrderByThirdColumn'=>'MENU_ID'));
            $c->addClass("number");
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104930"));
            $c->setJournalTableOfMaster('A_MENU_LIST_JNL');
            $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
            $c->setJournalKeyIDOfMaster('MENU_ID');
            $c->setJournalDispIDOfMaster('MENU_NAME');
            $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
            $c->setHiddenMainTableColumn(false);
            $c->getOutputType("update_table")->setVisible(false);
            $c->getOutputType("register_table")->setVisible(false);
            $c->getOutputType("excel")->setVisible(false);
            $c->getOutputType("csv")->setVisible(false);

            $c->getOutputType('json')->setVisible(false); // RestAPIでは隠す

            //----復活時に二重チェックになるので付加
            $c->setDeleteOffBeforeCheck(false);
            //復活時に二重チェックになるので付加----
            $objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
            $aryTraceQuery = array(
                array(
                    'TRACE_TARGET_TABLE'=>'A_MENU_LIST_JNL',
                    'TTT_SEARCH_KEY_COLUMN_ID'=>'MENU_ID',
                    'TTT_GET_TARGET_COLUMN_ID'=>'MENU_ID',
                    'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
                    'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
                    'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
                )
            );
            $objOT->setTraceQuery($aryTraceQuery);
            $objOT->setFirstSearchValueOwnerColumnID('MENU_ID');
            $c->setOutputType('print_journal_table',$objOT);
            //登録更新関係から隠す----
            $c->setMasterDisplayColumnType(0);
            $cg->addColumn($c);

            ////////////////////////////////////////////////////////////
            // メニュー名
            ////////////////////////////////////////////////////////////
            $url = "01_browse.php?no=";
            $c = new LinkIDColumn('MENU_ID_CLONE_02', $g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104940"), 'A_MENU_LIST', 'MENU_ID', 'MENU_NAME', $url, false, true, 'MENU_ID');
            $c->setHiddenMainTableColumn(false);
            $c->setAllowSendFromFile(false);
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104950"));
            //----登録更新関係から隠す
            $c->setHiddenMainTableColumn(false);
            $c->getOutputType("update_table")->setVisible(false);
            $c->getOutputType("register_table")->setVisible(false);
            $c->getOutputType("excel")->setVisible(false);
            $c->getOutputType("csv")->setVisible(false);

            $c->getOutputType('json')->setVisible(false); //RestAPIでは隠す

            $objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
            $objOT->setFirstSearchValueOwnerColumnID('MENU_ID_CLONE_02');
            $aryTraceQuery = array(array('TRACE_TARGET_TABLE'=>'A_MENU_LIST_JNL',
                'TTT_SEARCH_KEY_COLUMN_ID'=>'MENU_ID',
                'TTT_GET_TARGET_COLUMN_ID'=>'MENU_NAME',
                'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
                'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
                'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
                )
            );
            $objOT->setTraceQuery($aryTraceQuery);
            $c->setOutputType('print_journal_table',$objOT);

            //登録更新関係から隠す----
            $cg->addColumn($c);

        $cgg->addColumn($cg);
        // カラムグループ メニュー----

        ////////////////////////////////////////////////////////////
        // メニューID
        ////////////////////////////////////////////////////////////
        // RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したMENU_IDを設定する。
        $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
                    global    $g;
                    $boolRet = true;
                    $intErrorType = null;
                    $aryErrMsgBody = array();
                    $strErrMsg = "";
                    $strErrorBuf = "";

                    // シナリオタイプをSCRABに設定する。
                    $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
                    if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
                        if(strlen($g['MENU_ID_UPDATE_VALUE']) !== 0){
                            $exeQueryData[$objColumn->getID()] = $g['MENU_ID_UPDATE_VALUE'];
                        }
                    }else if( $modeValue=="DTUP_singleRecDelete" ){
                    }
                    $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
                    return $retArray;
        };

        $c = new IDColumn('MENU_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104960"),'D_CMDB_MENU_LIST_SHEET_TYPE_3','MENU_ID','MENU_PULLDOWN','',array('OrderByThirdColumn'=>'MENU_ID'));
        $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104970"));

        $c->setHiddenMainTableColumn(true); //更新対象カラム

        $c->setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。

        $c->getOutputType('filter_table')->setVisible(false);
        $c->getOutputType('print_table')->setVisible(false);
        $c->getOutputType('delete_table')->setVisible(false);
        $c->getOutputType('print_journal_table')->setVisible(false);

        $c->getOutputType('excel')->setVisible(false);
        $c->getOutputType('csv')->setVisible(false);
        $c->getOutputType('json')->setVisible(false); // RestAPIでは隠す

        $c->setEvent('update_table', 'onchange', 'menu_upd');
        $c->setEvent('register_table', 'onchange', 'menu_reg');

        $c->setJournalTableOfMaster('D_CMDB_MENU_LIST_SHEET_TYPE_3_JNL');
        $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
        $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
        $c->setJournalKeyIDOfMaster('MENU_ID');
        $c->setJournalDispIDOfMaster('MENU_PULLDOWN');

        $c->setRequiredMark(true);//必須マークのみ付与

        $c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

        $cgg->addColumn($c);

        unset($tmpObjFunction);

        ////////////////////////////////////////////////////////////
        //カラムタイトル名
        ////////////////////////////////////////////////////////////
        // RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したCOLUMN_LIST_IDを設定する。
        $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
                    global    $g;

                    $boolRet = true;
                    $intErrorType = null;
                    $aryErrMsgBody = array();
                    $strErrMsg = "";
                    $strErrorBuf = "";

                    // シナリオタイプをSCRABに設定する。
                    $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
                    if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
                        if(strlen($g['COLUMN_LIST_ID_UPDATE_VALUE']) !== 0){
                            $exeQueryData[$objColumn->getID()] = $g['COLUMN_LIST_ID_UPDATE_VALUE'];
                        }
                    }else if( $modeValue=="DTUP_singleRecDelete" ){
                    }
                    $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
                    return $retArray;
        };

        $c = new IDColumn('COLUMN_LIST_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104980"),'D_CMDB_MENU_COLUMN_SHEET_TYPE_3','COLUMN_LIST_ID','COL_TITLE','',array('SELECT_ADD_FOR_ORDER'=>array('COL_TITLE_DISP_SEQ'),'ORDER'=>'ORDER BY ADD_SELECT_1') );

        $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-104990"));

        $c->setHiddenMainTableColumn(true); //更新対象カラム

        $c->setAllowSendFromFile(false);//エクセル/CSVからのアップロードを禁止する。

        $c->getOutputType('excel')->setVisible(false);
        $c->getOutputType('csv')->setVisible(false);
        $c->getOutputType('json')->setVisible(false); // #3049 2018/03/12 Append RestAPIでは隠す

        $objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
            global $g;
            $retBool = false;
            $intErrorType = null;
            $aryErrMsgBody = array();
            $strErrMsg = "";
            $aryDataSet = array();

            $strFxName = "";

            $strMenuIDNumeric = $aryVariant['MENU_ID'];

            $strQuery = "SELECT "
                       ." TAB_1.COLUMN_LIST_ID  KEY_COLUMN "
                       .",TAB_1.COL_TITLE       DISP_COLUMN "
                       .",TAB_1.ACCESS_AUTH ACCESS_AUTH "
                       .",TAB_1.ACCESS_AUTH_01 ACCESS_AUTH_01 "
                       .",TAB_1.ACCESS_AUTH_02 ACCESS_AUTH_02 "
                       .",TAB_1.ACCESS_AUTH_03 ACCESS_AUTH_03 "
                       ."FROM "
                       ." D_CMDB_MENU_COLUMN_SHEET_TYPE_3 TAB_1 "
                       ."WHERE "
                       ." TAB_1.DISUSE_FLAG IN ('0') "
                       ." AND TAB_1.MENU_ID = :MENU_ID "
                       ."ORDER BY COL_TITLE_DISP_SEQ";

            $aryForBind['MENU_ID'] = $strMenuIDNumeric;

            if( 0 < strlen($strMenuIDNumeric) ){
                // ログインユーザーのロール・ユーザー紐づけ情報を内部展開
                $obj = new RoleBasedAccessControl($g['objDBCA']);
                $ret  = $obj->getAccountInfo($g['login_id']);
                if($ret === false) {
                    $intErrorType = 500;
                    $retBool = false;
                }

                $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
                if( $aryRetBody[0] === true ){
                    $objQuery = $aryRetBody[1];
                    while($row = $objQuery->resultFetch() ){
                        // レコード毎のアクセス権を判定
                        list($ret,$permission) = $obj->chkOneRecodeMultiAccessPermission($row);
                        if($ret === false) {
                            $intErrorType = 500;
                            $retBool = false;
                        }else{
                            if($permission === true){
                                $aryDataSet[]= $row;
                            }
                        }
                    }
                    unset($objQuery);
                    $retBool = true;
                }else{
                    $intErrorType = 500;
                    $intRowLength = -1;
                }
            }
            $retArray = array($retBool,$intErrorType,$aryErrMsgBody,$strErrMsg,$aryDataSet);
            return $retArray;
        };

        $objFunction02 = $objFunction01;

        $objFunction03 = function($objCellFormatter, $rowData, $aryVariant){
            global $g;
            $retBool = false;
            $intErrorType = null;
            $aryErrMsgBody = array();
            $strErrMsg = "";
            $aryDataSet = array();

            $strFxName = "";

            $strMenuIDNumeric = $rowData['MENU_ID'];

            $strQuery = "SELECT "
                       ." TAB_1.COLUMN_LIST_ID  KEY_COLUMN "
                       .",TAB_1.COL_TITLE       DISP_COLUMN "
                       .",TAB_1.ACCESS_AUTH ACCESS_AUTH "
                       .",TAB_1.ACCESS_AUTH_01 ACCESS_AUTH_01 "
                       .",TAB_1.ACCESS_AUTH_02 ACCESS_AUTH_02 "
                       .",TAB_1.ACCESS_AUTH_03 ACCESS_AUTH_03 "
                       ."FROM "
                       ." D_CMDB_MENU_COLUMN_SHEET_TYPE_3 TAB_1 "
                       ."WHERE "
                       ." TAB_1.DISUSE_FLAG IN ('0') "
                       ." AND TAB_1.MENU_ID = :MENU_ID "
                       ."ORDER BY COL_TITLE_DISP_SEQ";

            $aryForBind['MENU_ID'] = $strMenuIDNumeric;

            if( 0 < strlen($strMenuIDNumeric) ){
                // ログインユーザーのロール・ユーザー紐づけ情報を内部展開
                $obj = new RoleBasedAccessControl($g['objDBCA']);
                $ret  = $obj->getAccountInfo($g['login_id']);
                if($ret === false) {
                    $intErrorType = 500;
                    $retBool = false;
                }

                $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
                if( $aryRetBody[0] === true ){
                    $objQuery = $aryRetBody[1];
                    while($row = $objQuery->resultFetch() ){
                        // レコード毎のアクセス権を判定
                        list($ret,$permission) = $obj->chkOneRecodeMultiAccessPermission($row);
                        if($ret === false) {
                            $intErrorType = 500;
                            $retBool = false;
                        }else{
                            if($permission === true){
                                $aryDataSet[$row['KEY_COLUMN']]= $row['DISP_COLUMN'];
                            }
                        }
                    }
                    unset($objQuery);
                    $retBool = true;
                }else{
                    $intErrorType = 500;
                    $intRowLength = -1;
                }
            }
            $aryRetBody = array($retBool, $intErrorType, $aryErrMsgBody, $strErrMsg, $aryDataSet);
            return $aryRetBody;
        };

        $strSetInnerText = $g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105000");
        $objVarBFmtUpd = new SelectTabBFmt();
        $objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);
        $objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);
        $objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);

        $objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);
        $objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

        $objVarBFmtReg = new SelectTabBFmt();
        $objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);
        $objVarBFmtReg->setFunctionForGetSelectList($objFunction03);

        $objVarBFmtReg->setSelectWaitingText($strSetInnerText);
        $objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);
        $objOTForReg->setFunctionForGetFADSelectList($objFunction02);

        $c->setOutputType('update_table',$objOTForUpd);
        $c->setOutputType('register_table',$objOTForReg);


        $c->setJournalTableOfMaster('D_CMDB_MENU_COLUMN_SHEET_TYPE_3_JNL');
        $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
        $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
        $c->setJournalKeyIDOfMaster('COLUMN_LIST_ID');
        $c->setJournalDispIDOfMaster('COL_TITLE');

        $c->setRequiredMark(true);//必須マークのみ付与

        $c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

        $cgg->addColumn($c);

        unset($tmpObjFunction);

        unset($objFunction01);
        unset($objFunction02);
        unset($objFunction03);

        ////////////////////////////////////////////////////////////
        //Excel/CSV/RestAPI 用カラムタイトル名
        ////////////////////////////////////////////////////////////
        // Excel/CSV/RestAPI 用カラムタイトル名
        $c = new IDColumn('REST_COLUMN_LIST_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105010"),'D_CMDB_MG_MU_COL_LIST_SHEET_TYPE_3','COLUMN_LIST_ID','MENU_COL_TITLE_PULLDOWN','',array('SELECT_ADD_FOR_ORDER'=>array('MENU_ID','COL_TITLE_DISP_SEQ'),'ORDER'=>'ORDER BY ADD_SELECT_1,ADD_SELECT_2') );

        $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105020"));
        $c->setJournalTableOfMaster('D_CMDB_MG_MU_COL_LIST_SHEET_TYPE_3_JNL');
        $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
        $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
        $c->setJournalKeyIDOfMaster('COLUMN_LIST_ID');
        $c->setJournalDispIDOfMaster('MENU_COL_TITLE_PULLDOWN');

        //コンテンツのソースがヴューの場合、登録/更新の対象外
        $c->setHiddenMainTableColumn(false);

        //エクセル/CSVからのアップロード対象
        $c->setAllowSendFromFile(true);

        //REST/excel/csv以外は非表示
        $c->getOutputType('filter_table')->setVisible(false);
        $c->getOutputType('print_table')->setVisible(false);
        $c->getOutputType('update_table')->setVisible(false);
        $c->getOutputType('register_table')->setVisible(false);
        $c->getOutputType('delete_table')->setVisible(false);
        $c->getOutputType('print_journal_table')->setVisible(false);
        $c->getOutputType('excel')->setVisible(true);
        $c->getOutputType('csv')->setVisible(true);
        $c->getOutputType('json')->setVisible(true);

        //登録/更新時には、必須でない
        $c->setRequired(false);
        $c->setRequiredMark(true);//必須マークのみ付与

        $cgg->addColumn($c);

    ////////////////////////////////////////////////////////////
    // ColumnGroup:パラメータシート 終了
    ////////////////////////////////////////////////////////////
    $table->addColumn($cgg);

    ////////////////////////////////////////////////////////////
    //登録方式
    ////////////////////////////////////////////////////////////
    $c = new IDColumn('COL_TYPE',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105030"),'B_CMDB_MENU_COL_TYPE','COLUMN_TYPE_ID','COLUMN_TYPE_NAME','',array('OrderByThirdColumn'=>'COLUMN_TYPE_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105040"));

    $c->setHiddenMainTableColumn(true); //更新対象カラム

    $c->setJournalTableOfMaster('B_CMDB_MENU_COL_TYPE_JNL');
    $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
    $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
    $c->setJournalKeyIDOfMaster('COLUMN_TYPE_ID');
    $c->setJournalDispIDOfMaster('COLUMN_TYPE_NAME');

    $c->setRequired(true);//登録/更新時には、入力必須

    $table->addColumn($c);

    ////////////////////////////////////////////////////////////
    // ColumnGroup:IaC変数 開始
    ////////////////////////////////////////////////////////////
    $cgg = new ColumnGroup($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105050"));

        ////////////////////////////////////////////////////////////
        //作業パターン
        ////////////////////////////////////////////////////////////
        // RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したPATTERN_IDを設定する。
        $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
                    global    $g;
                    $boolRet = true;
                    $intErrorType = null;
                    $aryErrMsgBody = array();
                    $strErrMsg = "";
                    $strErrorBuf = "";

                    $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
                    if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
                        if(strlen($g['PATTERN_ID_UPDATE_VALUE']) !== 0){
                            $exeQueryData[$objColumn->getID()] = $g['PATTERN_ID_UPDATE_VALUE'];
                        }
                    }else if( $modeValue=="DTUP_singleRecDelete" ){
                    }
                    $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
                    return $retArray;
        };

        $c = new IDColumn('PATTERN_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105060"),'E_TERRAFORM_PATTERN','PATTERN_ID','PATTERN','',array('OrderByThirdColumn'=>'PATTERN_ID'));
        $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105070"));

        $c->setHiddenMainTableColumn(true); //更新対象カラム

        $c->setJournalTableOfMaster('E_TERRAFORM_PATTERN_JNL');
        $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
        $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
        $c->setJournalKeyIDOfMaster('PATTERN_ID');
        $c->setJournalDispIDOfMaster('PATTERN');

        // 必須チェックは組合せバリデータで行う。
        $c->setRequired(false);
        $c->setRequiredMark(true);//必須マークのみ付与

        //コンテンツのソースがヴューの場合、登録/更新の対象とする
        $c->setHiddenMainTableColumn(true);

        //エクセル/CSVからのアップロードを禁止する。
        $c->setAllowSendFromFile(false);

        // REST/excel/csvで項目無効
        $c->getOutputType('excel')->setVisible(false);
        $c->getOutputType('csv')->setVisible(false);
        $c->getOutputType('json')->setVisible(false);

        // データベース更新前のファンクション登録
        $c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

        $c->setEvent('update_table', 'onchange', 'pattern_upd');
        $c->setEvent('register_table', 'onchange', 'pattern_reg');

        $cgg->addColumn($c);

        //////////////////////////////////////////////////
        // ColumnGroup:Key変数 開始                     //
        //////////////////////////////////////////////////
        $cg = new ColumnGroup($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105080"));

            //////////////////////////////////////////////////
            // Key変数                                      //
            //////////////////////////////////////////////////
            // RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したPATTERN_IDを設定する。
            $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
                        global    $g;
                        $boolRet = true;
                        $intErrorType = null;
                        $aryErrMsgBody = array();
                        $strErrMsg = "";
                        $strErrorBuf = "";

                        $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
                        if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
                            if(strlen($g['KEY_VARS_LINK_ID_UPDATE_VALUE']) !== 0){
                                $exeQueryData[$objColumn->getID()] = $g['KEY_VARS_LINK_ID_UPDATE_VALUE'];
                            }
                        }else if( $modeValue=="DTUP_singleRecDelete" ){
                        }
                        $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
                        return $retArray;
            };

            $c = new IDColumn('KEY_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105090"),'D_TERRAFORM_PTN_VARS_LINK','MODULE_VARS_LINK_ID','VARS_LINK_PULLDOWN','D_TERRAFORM_PTN_VARS_LINK_VFP');

            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105100"));//エクセル・ヘッダでの説明

            $c->setHiddenMainTableColumn(true); //更新対象カラム

            $c->setJournalTableOfMaster('D_TERRAFORM_PTN_VARS_LINK_JNL');
            $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
            $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
            $c->setJournalKeyIDOfMaster('MODULE_VARS_LINK_ID');
            $c->setJournalDispIDOfMaster('VARS_LINK_PULLDOWN');

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数
            $objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
                global $g;
                $retBool = false;
                $intErrorType = null;
                $aryErrMsgBody = array();
                $strErrMsg = "";
                $aryDataSet = array();

                $strFxName = "";

                $strPatternIdNumeric = $aryVariant['PATTERN_ID'];

                $strQuery = "SELECT "
                           ." TAB_1.MODULE_VARS_LINK_ID       KEY_COLUMN "
                           .",TAB_1.VARS_LINK_PULLDOWN DISP_COLUMN "
                           .",TAB_1.ACCESS_AUTH ACCESS_AUTH "
                           .",TAB_1.ACCESS_AUTH_01 ACCESS_AUTH_01 "
                           .",TAB_1.ACCESS_AUTH_02 ACCESS_AUTH_02 "
                           ."FROM "
                           ." D_TERRAFORM_PTN_VARS_LINK_VFP TAB_1 "
                           ."WHERE "
                           ." TAB_1.DISUSE_FLAG IN ('0') "
                           ." AND TAB_1.PATTERN_ID = :PATTERN_ID "
                           ."ORDER BY KEY_COLUMN ASC ";

                $aryForBind['PATTERN_ID']        = $strPatternIdNumeric;

                if( 0 < strlen($strPatternIdNumeric) ){
                    // ログインユーザーのロール・ユーザー紐づけ情報を内部展開
                    $obj = new RoleBasedAccessControl($g['objDBCA']);
                    $ret  = $obj->getAccountInfo($g['login_id']);
                    if($ret === false) {
                        $intErrorType = 500;
                        $retBool = false;
                    }

                    $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
                    if( $aryRetBody[0] === true ){
                        $objQuery = $aryRetBody[1];
                        while($row = $objQuery->resultFetch() ){
                            // レコード毎のアクセス権を判定
                            list($ret,$permission) = $obj->chkOneRecodeMultiAccessPermission($row);
                            if($ret === false) {
                                $intErrorType = 500;
                                $retBool = false;
                            }else{
                                if($permission === true){
                                    $aryDataSet[]= $row;
                                }
                            }
                        }
                        unset($objQuery);
                        $retBool = true;
                    }else{
                        $intErrorType = 500;
                        $intRowLength = -1;
                    }
                }
                $retArray = array($retBool,$intErrorType,$aryErrMsgBody,$strErrMsg,$aryDataSet);
                return $retArray;
            };

            //$objFunction02 = $objFunction01;
            $objFunction02 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
                global $g;
                $retBool = false;
                $intErrorType = null;
                $aryErrMsgBody = array();
                $strErrMsg = "";
                $aryDataSet = array();

                $strFxName = "";

                $strPatternIdNumeric = $aryVariant['PATTERN_ID'];

                $strQuery = "SELECT "
                           ." TAB_1.MODULE_VARS_LINK_ID       KEY_COLUMN "
                           .",TAB_1.VARS_LINK_PULLDOWN DISP_COLUMN "
                           .",TAB_1.ACCESS_AUTH ACCESS_AUTH "
                           .",TAB_1.ACCESS_AUTH_01 ACCESS_AUTH_01 "
                           .",TAB_1.ACCESS_AUTH_02 ACCESS_AUTH_02 "
                           ."FROM "
                           ." D_TERRAFORM_PTN_VARS_LINK_VFP TAB_1 "
                           ."WHERE "
                           ." TAB_1.DISUSE_FLAG IN ('0') "
                           ." AND TAB_1.PATTERN_ID = :PATTERN_ID "
                           ."ORDER BY KEY_COLUMN ASC ";

                $aryForBind['PATTERN_ID']        = $strPatternIdNumeric;

                if( 0 < strlen($strPatternIdNumeric) ){
                    // ログインユーザーのロール・ユーザー紐づけ情報を内部展開
                    $obj = new RoleBasedAccessControl($g['objDBCA']);
                    $ret  = $obj->getAccountInfo($g['login_id']);
                    if($ret === false) {
                        $intErrorType = 500;
                        $retBool = false;
                    }

                    $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
                    if( $aryRetBody[0] === true ){
                        $objQuery = $aryRetBody[1];
                        while($row = $objQuery->resultFetch() ){
                            // レコード毎のアクセス権を判定
                            list($ret,$permission) = $obj->chkOneRecodeMultiAccessPermission($row);
                            if($ret === false) {
                                $intErrorType = 500;
                                $retBool = false;
                            }else{
                                if($permission === true){
                                    $aryDataSet[]= $row;
                                }
                            }
                        }
                        unset($objQuery);
                        $retBool = true;
                    }else{
                        $intErrorType = 500;
                        $intRowLength = -1;
                    }
                }
                $retArray = array($retBool,$intErrorType,$aryErrMsgBody,$strErrMsg,$aryDataSet);
                return $retArray;
            };
            // フォームの表示直後、選択できる選択肢リストを作成する関数
            $objFunction03 = function($objCellFormatter, $rowData, $aryVariant){
                global $g;
                $retBool = false;
                $intErrorType = null;
                $aryErrMsgBody = array();
                $strErrMsg = "";
                $aryDataSet = array();

                $strFxName = "";

                $strPatternIdNumeric = $rowData['PATTERN_ID'];

                $strQuery = "SELECT "
                           ." TAB_1.MODULE_VARS_LINK_ID       KEY_COLUMN "
                           .",TAB_1.VARS_LINK_PULLDOWN DISP_COLUMN "
                           .",TAB_1.ACCESS_AUTH ACCESS_AUTH "
                           .",TAB_1.ACCESS_AUTH_01 ACCESS_AUTH_01 "
                           .",TAB_1.ACCESS_AUTH_02 ACCESS_AUTH_02 "
                           ."FROM "
                           ." D_TERRAFORM_PTN_VARS_LINK_VFP TAB_1 "
                           ."WHERE "
                           ." TAB_1.DISUSE_FLAG IN ('0') "
                           ." AND TAB_1.PATTERN_ID = :PATTERN_ID "
                           ."ORDER BY KEY_COLUMN ASC ";

                $aryForBind['PATTERN_ID']        = $strPatternIdNumeric;

                if( 0 < strlen($strPatternIdNumeric) ){
                    // ログインユーザーのロール・ユーザー紐づけ情報を内部展開
                    $obj = new RoleBasedAccessControl($g['objDBCA']);
                    $ret  = $obj->getAccountInfo($g['login_id']);
                    if($ret === false) {
                        $intErrorType = 500;
                        $retBool = false;
                    }

                    $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
                    if( $aryRetBody[0] === true ){
                        $objQuery = $aryRetBody[1];
                        while($row = $objQuery->resultFetch() ){
                            // レコード毎のアクセス権を判定
                            list($ret,$permission) = $obj->chkOneRecodeMultiAccessPermission($row);
                            if($ret === false) {
                                $intErrorType = 500;
                                $retBool = false;
                            }else{
                                if($permission === true){
                                    $aryDataSet[$row['KEY_COLUMN']]= $row['DISP_COLUMN'];
                                }
                            }
                        }
                        unset($objQuery);
                        $retBool = true;
                    }else{
                        $intErrorType = 500;
                        $intRowLength = -1;
                    }
                }
                $aryRetBody = array($retBool, $intErrorType, $aryErrMsgBody, $strErrMsg, $aryDataSet);
                return $aryRetBody;
            };

            //$strSetInnerText = '作業パターンを選択して下さい'
            $strSetInnerText = $g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105110");
            $objVarBFmtUpd = new SelectTabBFmt();

            // フォームの表示直後、変更反映カラムの既存値が、選べる選択肢の中になかった場合のメッセージ
            $objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ
            $objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);

            // フォームの表示直後、選択できる選択肢リストを作成する関数指定
            $objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);

            $objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定
            $objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

            $objVarBFmtReg = new SelectTabBFmt();

            // フォームの表示直後、トリガーカラムが選ばれていない場合のメッセージ
            $objVarBFmtReg->setSelectWaitingText($strSetInnerText);

            $objVarBFmtReg->setFunctionForGetSelectList($objFunction03);

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ
            $objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);

            $objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定
            $objOTForReg->setFunctionForGetFADSelectList($objFunction02);

            $c->setOutputType('update_table',$objOTForUpd);

            $c->setOutputType('register_table',$objOTForReg);

            // 必須チェックは組合せバリデータで行う。
            $c->setRequired(false);

            //コンテンツのソースがヴューの場合、登録/更新の対象とする
            $c->setHiddenMainTableColumn(true);

            //エクセル/CSVからのアップロードを禁止する。
            $c->setAllowSendFromFile(false);

            //Filter/Print/deleteのみ無効
            $c->getOutputType('filter_table')->setVisible(false);
            $c->getOutputType('print_table')->setVisible(false);
            $c->getOutputType('delete_table')->setVisible(false);

            // REST/excel/csvで項目無効
            $c->getOutputType('excel')->setVisible(false);
            $c->getOutputType('csv')->setVisible(false);
            $c->getOutputType('json')->setVisible(false);

            // データベース更新前のファンクション登録
            $c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

            $cg->addColumn($c);

            unset($objFunction01);
            unset($objFunction02);
            unset($objFunction03);

            // Key変数名Webページ表示用
            $c = new IDColumn('KEY_VARS_PTN_LINK_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105150"),'E_TERRAFORM_PTN_VAR_LIST','MODULE_PTN_LINK_ID','VARS_LINK_PULLDOWN','',array('OrderByThirdColumn'=>'MODULE_PTN_LINK_ID'));
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105100"));

            $c->setHiddenMainTableColumn(false); //更新対象カラム

            // 必須チェックは組合せバリデータで行う。
            $c->setRequired(false);

            //コンテンツのソースがヴューの場合、登録/更新の対象とする
            $c->setHiddenMainTableColumn(false);

            //エクセル/CSVからのアップロードを禁止する。
            $c->setAllowSendFromFile(false);

            //Filter/Print/delete以外無効
            $c->getOutputType('filter_table')->setVisible(true);
            $c->getOutputType('print_table')->setVisible(true);
            $c->getOutputType('delete_table')->setVisible(true);
            $c->getOutputType('update_table')->setVisible(false);
            $c->getOutputType('register_table')->setVisible(false);
            $c->getOutputType('print_journal_table')->setVisible(false);
            $c->getOutputType('excel')->setVisible(false);
            $c->getOutputType('csv')->setVisible(false);
            $c->getOutputType('json')->setVisible(false);

            $cg->addColumn($c);


            ////////////////////////////////////////////////////////
            //REST/excel/csv入力用 Key変数　Movement+変数名
            ////////////////////////////////////////////////////////
            // REST/excel/csv入力用 Key変数　Movement+変数名
            $c = new IDColumn('REST_KEY_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105120"),'E_TERRAFORM_PTN_VAR_LIST','MODULE_PTN_LINK_ID','PTN_VAR_PULLDOWN','',array('OrderByThirdColumn'=>'MODULE_PTN_LINK_ID'));
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105130"));
            $c->setJournalTableOfMaster('E_TERRAFORM_PTN_VAR_LIST_JNL');
            $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
            $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
            $c->setJournalKeyIDOfMaster('MODULE_VARS_LINK_ID');
            $c->setJournalDispIDOfMaster('PTN_VAR_PULLDOWN');

            //REST/excel/csv以外は非表示
            $c->getOutputType('filter_table')->setVisible(false);
            $c->getOutputType('print_table')->setVisible(false);
            $c->getOutputType('update_table')->setVisible(false);
            $c->getOutputType('register_table')->setVisible(false);
            $c->getOutputType('delete_table')->setVisible(false);
            $c->getOutputType('print_journal_table')->setVisible(false);
            $c->getOutputType('excel')->setVisible(true);
            $c->getOutputType('csv')->setVisible(true);
            $c->getOutputType('json')->setVisible(true);

            //コンテンツのソースがヴューの場合、登録/更新の対象外
            $c->setHiddenMainTableColumn(false);

            //エクセル/CSVからのアップロード対象
            $c->setAllowSendFromFile(true);

            //登録/更新時には、必須でない
            $c->setRequired(false);

            $cg->addColumn($c);


        //////////////////////////////////////////////////
        // ColumnGroup:Key変数 終了                     //
        //////////////////////////////////////////////////
        $cgg->addColumn($cg);

        //////////////////////////////////////////////////
        // ColumnGroup:Value変数 開始                   //
        //////////////////////////////////////////////////
        $cg = new ColumnGroup($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105140"));

            //////////////////////////////////////////////////
            // Value変数 開始                               //
            //////////////////////////////////////////////////
            // RestAPI/Excel/CSVからの登録の場合に組み合わせバリデータで退避したPATTERN_IDを設定する。
            $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
                    global    $g;
                    $boolRet = true;
                    $intErrorType = null;
                    $aryErrMsgBody = array();
                    $strErrMsg = "";
                    $strErrorBuf = "";

                    $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
                    if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" ){
                        if(strlen($g['VAL_VARS_LINK_ID_UPDATE_VALUE']) !== 0){
                            $exeQueryData[$objColumn->getID()] = $g['VAL_VARS_LINK_ID_UPDATE_VALUE'];
                        }
                    }else if( $modeValue=="DTUP_singleRecDelete" ){
                    }
                    $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
                    return $retArray;
            };

            $c = new IDColumn('VAL_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105150"),'D_TERRAFORM_PTN_VARS_LINK','MODULE_VARS_LINK_ID','VARS_LINK_PULLDOWN','D_TERRAFORM_PTN_VARS_LINK_VFP');
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105160"));

            $c->setHiddenMainTableColumn(true); //更新対象カラム

            $c->setJournalTableOfMaster('D_TERRAFORM_PTN_VARS_LINK_JNL');
            $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
            $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
            $c->setJournalKeyIDOfMaster('MODULE_VARS_LINK_ID');
            $c->setJournalDispIDOfMaster('VARS_LINK_PULLDOWN');

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数
            $objFunction01 = function($objOutputType, $aryVariant, $arySetting, $aryOverride, $objColumn){
                global $g;
                $retBool = false;
                $intErrorType = null;
                $aryErrMsgBody = array();
                $strErrMsg = "";
                $aryDataSet = array();

                $strFxName = "";

                $strPatternIdNumeric = $aryVariant['PATTERN_ID'];

                $strQuery = "SELECT "
                           ." TAB_1.MODULE_VARS_LINK_ID KEY_COLUMN "
                           .",TAB_1.VARS_LINK_PULLDOWN DISP_COLUMN "
                           .",TAB_1.ACCESS_AUTH ACCESS_AUTH "
                           .",TAB_1.ACCESS_AUTH_01 ACCESS_AUTH_01 "
                           .",TAB_1.ACCESS_AUTH_02 ACCESS_AUTH_02 "
                           ."FROM "
                           ." D_TERRAFORM_PTN_VARS_LINK_VFP TAB_1 "
                           ."WHERE "
                           ." TAB_1.DISUSE_FLAG IN ('0') "
                           ." AND TAB_1.PATTERN_ID = :PATTERN_ID "
                           ."ORDER BY KEY_COLUMN ASC ";

                $aryForBind['PATTERN_ID']        = $strPatternIdNumeric;

                if( 0 < strlen($strPatternIdNumeric) ){
                    // ログインユーザーのロール・ユーザー紐づけ情報を内部展開
                    $obj = new RoleBasedAccessControl($g['objDBCA']);
                    $ret  = $obj->getAccountInfo($g['login_id']);
                    if($ret === false) {
                        $intErrorType = 500;
                        $retBool = false;
                    }

                    $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
                    if( $aryRetBody[0] === true ){
                        $objQuery = $aryRetBody[1];
                        while($row = $objQuery->resultFetch() ){
                            // レコード毎のアクセス権を判定
                            list($ret,$permission) = $obj->chkOneRecodeMultiAccessPermission($row);
                            if($ret === false) {
                                $intErrorType = 500;
                                $retBool = false;
                            }else{
                                if($permission === true){
                                    $aryDataSet[]= $row;
                                }
                            }
                        }
                        unset($objQuery);
                        $retBool = true;
                    }else{
                        $intErrorType = 500;
                        $intRowLength = -1;
                    }
                }
                $retArray = array($retBool,$intErrorType,$aryErrMsgBody,$strErrMsg,$aryDataSet);
                return $retArray;
            };

            $objFunction02 = $objFunction01;

            // フォームの表示直後、選択できる選択肢リストを作成する関数
            $objFunction03 = function($objCellFormatter, $rowData, $aryVariant){
                global $g;
                $retBool = false;
                $intErrorType = null;
                $aryErrMsgBody = array();
                $strErrMsg = "";
                $aryDataSet = array();

                $strFxName = "";

                $strPatternIdNumeric = $rowData['PATTERN_ID'];

                $strQuery = "SELECT "
                           ." TAB_1.MODULE_VARS_LINK_ID KEY_COLUMN "
                           .",TAB_1.VARS_LINK_PULLDOWN DISP_COLUMN "
                           .",TAB_1.ACCESS_AUTH ACCESS_AUTH "
                           .",TAB_1.ACCESS_AUTH_01 ACCESS_AUTH_01 "
                           .",TAB_1.ACCESS_AUTH_02 ACCESS_AUTH_02 "
                           ."FROM "
                           ." D_TERRAFORM_PTN_VARS_LINK_VFP TAB_1 "
                           ."WHERE "
                           ." TAB_1.DISUSE_FLAG IN ('0') "
                           ." AND TAB_1.PATTERN_ID = :PATTERN_ID "
                           ."ORDER BY KEY_COLUMN ASC ";

                $aryForBind['PATTERN_ID']        = $strPatternIdNumeric;

                if( 0 < strlen($strPatternIdNumeric) ){
                    // ログインユーザーのロール・ユーザー紐づけ情報を内部展開
                    $obj = new RoleBasedAccessControl($g['objDBCA']);
                    $ret  = $obj->getAccountInfo($g['login_id']);
                    if($ret === false) {
                        $intErrorType = 500;
                        $retBool = false;
                    }

                    $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);
                    if( $aryRetBody[0] === true ){
                        $objQuery = $aryRetBody[1];
                        while($row = $objQuery->resultFetch() ){
                            // レコード毎のアクセス権を判定
                            list($ret,$permission) = $obj->chkOneRecodeMultiAccessPermission($row);
                            if($ret === false) {
                                $intErrorType = 500;
                                $retBool = false;
                            }else{
                                if($permission === true){
                                    $aryDataSet[$row['KEY_COLUMN']]= $row['DISP_COLUMN'];
                                }
                            }
                        }
                        unset($objQuery);
                        $retBool = true;
                    }else{
                        $intErrorType = 500;
                        $intRowLength = -1;
                    }
                }
                $aryRetBody = array($retBool, $intErrorType, $aryErrMsgBody, $strErrMsg, $aryDataSet);
                return $aryRetBody;
            };

            $strSetInnerText = $g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105170");
            $objVarBFmtUpd = new SelectTabBFmt();

            // フォームの表示直後、変更反映カラムの既存値が、選べる選択肢の中になかった場合のメッセージ
            $objVarBFmtUpd->setNoOptionMessageText($strSetInnerText);

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ
            $objVarBFmtUpd->setFADNoOptionMessageText($strSetInnerText);

            // フォームの表示直後、選択できる選択肢リストを作成する関数指定
            $objVarBFmtUpd->setFunctionForGetSelectList($objFunction03);

            $objOTForUpd = new OutputType(new ReqTabHFmt(), $objVarBFmtUpd);

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定
            $objOTForUpd->setFunctionForGetFADSelectList($objFunction01);

            $objVarBFmtReg = new SelectTabBFmt();

            // フォームの表示直後、トリガーカラムが選ばれていない場合のメッセージ
            $objVarBFmtReg->setSelectWaitingText($strSetInnerText);

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたが、選べる選択肢がなかった場合のメッセージ
            $objVarBFmtReg->setFADNoOptionMessageText($strSetInnerText);

            $objVarBFmtReg->setFunctionForGetSelectList($objFunction03);

            $objOTForReg = new OutputType(new ReqTabHFmt(), $objVarBFmtReg);

            // フォームの表示後、ユーザによりトリガーカラムが選ばれたとき、選べる選択肢リストを作成する関数を指定
            $objOTForReg->setFunctionForGetFADSelectList($objFunction02);

            $c->setOutputType('update_table',$objOTForUpd);
            $c->setOutputType('register_table',$objOTForReg);

            // 必須チェックは組合せバリデータで行う。
            $c->setRequired(false);

            //コンテンツのソースがヴューの場合、登録/更新の対象とする
            $c->setHiddenMainTableColumn(true);

            //エクセル/CSVからのアップロードを禁止する。
            $c->setAllowSendFromFile(false);

            //Filter/Print/deleteのみ無効
            $c->getOutputType('filter_table')->setVisible(false);
            $c->getOutputType('print_table')->setVisible(false);
            $c->getOutputType('delete_table')->setVisible(false);

            // REST/excel/csvで項目無効
            $c->getOutputType('excel')->setVisible(false);
            $c->getOutputType('csv')->setVisible(false);
            $c->getOutputType('json')->setVisible(false);

            // データベース更新前のファンクション登録
            $c->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

            $cg->addColumn($c);

            unset($objFunction01);
            unset($objFunction02);
            unset($objFunction03);

            // Value変数名Webページ表示用
            $c = new IDColumn('VAL_VARS_PTN_LINK_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105150"),'E_TERRAFORM_PTN_VAR_LIST','MODULE_PTN_LINK_ID','VARS_LINK_PULLDOWN','',array('OrderByThirdColumn'=>'MODULE_PTN_LINK_ID'));
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105160"));

            $c->setHiddenMainTableColumn(false); //更新対象カラム

            // 必須チェックは組合せバリデータで行う。
            $c->setRequired(false);

            //コンテンツのソースがヴューの場合、登録/更新の対象とする
            $c->setHiddenMainTableColumn(false);

            //エクセル/CSVからのアップロードを禁止する。
            $c->setAllowSendFromFile(false);

            //Filter/Print/delete以外無効
            $c->getOutputType('filter_table')->setVisible(true);
            $c->getOutputType('print_table')->setVisible(true);
            $c->getOutputType('delete_table')->setVisible(true);
            $c->getOutputType('update_table')->setVisible(false);
            $c->getOutputType('register_table')->setVisible(false);
            $c->getOutputType('print_journal_table')->setVisible(false);
            $c->getOutputType('excel')->setVisible(false);
            $c->getOutputType('csv')->setVisible(false);
            $c->getOutputType('json')->setVisible(false);

            $cg->addColumn($c);


            ////////////////////////////////////////////////////////
            //REST/excel/csv入力用 Val変数　Movement+変数名
            ////////////////////////////////////////////////////////
            // REST/excel/csv入力用 Value変数　Movement+変数名
            $c = new IDColumn('REST_VAL_VARS_LINK_ID',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105180"),'E_TERRAFORM_PTN_VAR_LIST','MODULE_PTN_LINK_ID','PTN_VAR_PULLDOWN','',array('OrderByThirdColumn'=>'MODULE_PTN_LINK_ID'));
            $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105190"));
            $c->setJournalTableOfMaster('E_TERRAFORM_PTN_VAR_LIST_JNL');
            $c->setJournalSeqIDOfMaster('JOURNAL_SEQ_NO');
            $c->setJournalLUTSIDOfMaster('LAST_UPDATE_TIMESTAMP');
            $c->setJournalKeyIDOfMaster('MODULE_VARS_LINK_ID');
            $c->setJournalDispIDOfMaster('PTN_VAR_PULLDOWN');

            //REST/excel/csv以外は非表示
            $c->getOutputType('filter_table')->setVisible(false);
            $c->getOutputType('print_table')->setVisible(false);
            $c->getOutputType('update_table')->setVisible(false);
            $c->getOutputType('register_table')->setVisible(false);
            $c->getOutputType('delete_table')->setVisible(false);
            $c->getOutputType('print_journal_table')->setVisible(false);
            $c->getOutputType('excel')->setVisible(true);
            $c->getOutputType('csv')->setVisible(true);
            $c->getOutputType('json')->setVisible(true);

            //コンテンツのソースがヴューの場合、登録/更新の対象外
            $c->setHiddenMainTableColumn(false);

            //エクセル/CSVからのアップロード対象
            $c->setAllowSendFromFile(true);

            //登録/更新時には、必須でない
            $c->setRequired(false);

            $cg->addColumn($c);

        //////////////////////////////////////////////////
        // ColumnGroup:Value変数 終了                   //
        //////////////////////////////////////////////////
        $cgg->addColumn($cg);

    //////////////////////////////////////////////////
    // ColumnGroup:IaC変数 終了                     //
    //////////////////////////////////////////////////
    $table->addColumn($cgg);

    ////////////////////////////////////////////////////////////////////
    // Sensitive設定
    ////////////////////////////////////////////////////////////////////
    $c = new IDColumn('HCL_FLAG',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105240"), 'B_TERRAFORM_HCL_FLAG', 'HCL_FLAG', 'HCL_FLAG_SELECT', '', array('SELECT_ADD_FOR_ORDER'=>array('HCL_FLAG'), 'ORDER'=>'ORDER BY ADD_SELECT_1'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105250")); //エクセル・ヘッダでの説明
    $c->setJournalTableOfMaster('B_TERRAFORM_HCL_FLAG_JNL');
    $c->setDefaultValue("register_table", 1); //デフォルト値で1(OFF)
    $c->setRequired(true); //登録/更新時には、入力必須
    //コンテンツのソースがヴューの場合、登録/更新の対象とする
    $c->setHiddenMainTableColumn(true);

    $objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
    $objOT->setFirstSearchValueOwnerColumnID('HCL_FLAG');
    $aryTraceQuery = array(array('TRACE_TARGET_TABLE'=>'B_TERRAFORM_HCL_FLAG_JNL',
        'TTT_SEARCH_KEY_COLUMN_ID'=>'HCL_FLAG',
        'TTT_GET_TARGET_COLUMN_ID'=>'HCL_FLAG_SELECT',
        'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
        'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
        'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
        )
    );
    $objOT->setTraceQuery($aryTraceQuery);
    $c->setOutputType('print_journal_table',$objOT);

    $table->addColumn($c);

    ////////////////////////////////////////////////////////////////////
    // パラメータシートの具体値がNULLでも代入値管理に登録するかのフラグ
    ////////////////////////////////////////////////////////////////////
    $c = new IDColumn('NULL_DATA_HANDLING_FLG',$g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105220"),'B_VALID_INVALID_MASTER','FLAG_ID','FLAG_NAME','', array('OrderByThirdColumn'=>'FLAG_ID'));
    $c->setDescription($g['objMTS']->getSomeMessage("ITATERRAFORM-MNU-105230"));
    $c->setHiddenMainTableColumn(true); //更新対象カラム
    $c->setRequired(false);
    //コンテンツのソースがヴューの場合、登録/更新の対象とする
    $c->setHiddenMainTableColumn(true);

    $objOT = new TraceOutputType(new ReqTabHFmt(), new TextTabBFmt());
    $objOT->setFirstSearchValueOwnerColumnID('NULL_DATA_HANDLING_FLG');
    $aryTraceQuery = array(array('TRACE_TARGET_TABLE'=>'B_VALID_INVALID_MASTER_JNL',
        'TTT_SEARCH_KEY_COLUMN_ID'=>'FLAG_ID',
        'TTT_GET_TARGET_COLUMN_ID'=>'FLAG_NAME',
        'TTT_JOURNAL_SEQ_NO'=>'JOURNAL_SEQ_NO',
        'TTT_TIMESTAMP_COLUMN_ID'=>'LAST_UPDATE_TIMESTAMP',
        'TTT_DISUSE_FLAG_COLUMN_ID'=>'DISUSE_FLAG'
        )
    );
    $objOT->setTraceQuery($aryTraceQuery);
    $c->setOutputType('print_journal_table',$objOT);

    $table->addColumn($c);


    // 登録/更新/廃止/復活があった場合、データベースを更新した事をマークする。
    $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
        $boolRet = true;
        $intErrorType = null;
        $aryErrMsgBody = array();
        $strErrMsg = "";
        $strErrorBuf = "";
        $strFxName = "";

        $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
        if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" || $modeValue=="DTUP_singleRecDelete" ){

            $strQuery = "UPDATE A_PROC_LOADED_LIST "
                       ."SET LOADED_FLG='0' ,LAST_UPDATE_TIMESTAMP = NOW(6) "
                       ."WHERE ROW_ID IN (2100080002) ";

            $aryForBind = array();

            $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);

            if( $aryRetBody[0] !== true ){
                $boolRet = false;
                $strErrMsg = $aryRetBody[2];
                $intErrorType = 500;
            }
        }
        $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
        return $retArray;
    };
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['COLUMN_ID']->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

    $table->fixColumn();

    //----組み合わせバリデータ----
    $tmpAryColumn = $table->getColumns();
    $objLU4UColumn = $tmpAryColumn[$table->getRequiredUpdateDate4UColumnID()];

    $objFunction = function($objClientValidator, $value, $strNumberForRI, $arrayRegData, $arrayVariant){
        global $g;
        $retBool = true;
        $retStrBody = '';

        $strModeId = "";
        $modeValue_sub = "";

        $query = "";

        $boolExecuteContinue = true;
        $boolSystemErrorFlag = false;

        // --UPD--
        $pattan_tbl         = "E_TERRAFORM_PATTERN";
        $ptn_vars_link_view = "D_TERRAFORM_PTN_VARS_LINK_VFP";
        $aryVariantForIsValid = $objClientValidator->getVariantForIsValid();

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }
        if($strModeId == "DTUP_singleRecDelete"){
            //----更新前のレコードから、各カラムの値を取得
            $rg_menu_id                = isset($arrayVariant['edit_target_row']['MENU_ID'])?
                                               $arrayVariant['edit_target_row']['MENU_ID']:null;
            $rg_column_list_id         = isset($arrayVariant['edit_target_row']['COLUMN_LIST_ID'])?
                                               $arrayVariant['edit_target_row']['COLUMN_LIST_ID']:null;
            $rg_col_type               = isset($arrayVariant['edit_target_row']['COL_TYPE'])?
                                               $arrayVariant['edit_target_row']['COL_TYPE']:null;
            $rg_pattern_id             = isset($arrayVariant['edit_target_row']['PATTERN_ID'])?
                                               $arrayVariant['edit_target_row']['PATTERN_ID']:null;
            $rg_key_vars_link_id       = isset($arrayVariant['edit_target_row']['KEY_VARS_LINK_ID'])?
                                               $arrayVariant['edit_target_row']['KEY_VARS_LINK_ID']:null;
            $rg_val_vars_link_id       = isset($arrayVariant['edit_target_row']['VAL_VARS_LINK_ID'])?
                                               $arrayVariant['edit_target_row']['VAL_VARS_LINK_ID']:null;
            $rg_rest_column_list_id    = isset($arrayVariant['edit_target_row']['REST_COLUMN_LIST_ID'])?
                                               $arrayVariant['edit_target_row']['REST_COLUMN_LIST_ID']:null;
            $rg_rest_val_vars_link_id  = isset($arrayVariant['edit_target_row']['REST_VAL_VARS_LINK_ID'])?
                                               $arrayVariant['edit_target_row']['REST_VAL_VARS_LINK_ID']:null;
            $rg_rest_key_vars_link_id  = isset($arrayVariant['edit_target_row']['REST_KEY_VARS_LINK_ID'])?
                                               $arrayVariant['edit_target_row']['REST_KEY_VARS_LINK_ID']:null;

            $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];("on"/"off")
            if( $modeValue_sub == "on" ){
                //----廃止の場合はチェックしない
                $boolExecuteContinue = false;
                //廃止の場合はチェックしない----
            }else{
                //----復活の場合
                if( strlen($rg_rest_column_list_id) === 0 ||  strlen($rg_col_type) === 0 || strlen($rg_pattern_id) === 0 ){
                    $boolSystemErrorFlag = true;
                }
                //復活の場合----

                $columnId = $strNumberForRI;
            }
            //更新前のレコードから、各カラムの値を取得----
        }else if( $strModeId == "DTUP_singleRecUpdate" || $strModeId == "DTUP_singleRecRegister" ){
            $rg_menu_id                = array_key_exists('MENU_ID',$arrayRegData) ?
                                            $arrayRegData['MENU_ID']:null;
            $rg_column_list_id         = array_key_exists('COLUMN_LIST_ID',$arrayRegData) ?
                                            $arrayRegData['COLUMN_LIST_ID']:null;
            $rg_col_type               = array_key_exists('COL_TYPE',$arrayRegData) ?
                                            $arrayRegData['COL_TYPE']:null;
            $rg_pattern_id             = array_key_exists('PATTERN_ID',$arrayRegData) ?
                                            $arrayRegData['PATTERN_ID']:null;
            $rg_key_vars_link_id       = array_key_exists('KEY_VARS_LINK_ID',$arrayRegData) ?
                                            $arrayRegData['KEY_VARS_LINK_ID']:null;
            $rg_val_vars_link_id       = array_key_exists('VAL_VARS_LINK_ID',$arrayRegData) ?
                                            $arrayRegData['VAL_VARS_LINK_ID']:null;
            $rg_rest_column_list_id    = array_key_exists('REST_COLUMN_LIST_ID',$arrayRegData) ?
                                            $arrayRegData['REST_COLUMN_LIST_ID']:null;
            $rg_rest_val_vars_link_id  = array_key_exists('REST_VAL_VARS_LINK_ID',$arrayRegData) ?
                                            $arrayRegData['REST_VAL_VARS_LINK_ID']:null;
            $rg_rest_key_vars_link_id  = array_key_exists('REST_KEY_VARS_LINK_ID',$arrayRegData) ?
                                            $arrayRegData['REST_KEY_VARS_LINK_ID']:null;

            // 主キーの値を取得する。
            if( $strModeId == "DTUP_singleRecUpdate" ){
                // 更新処理の場合
                $columnId = $strNumberForRI;
            }
            else{
                // 登録処理の場合
                $columnId = array_key_exists('COLUMN_ID',$arrayRegData)?$arrayRegData['COLUMN_ID']:null;
            }
        }

        $g['MENU_ID_UPDATE_VALUE']        = "";
        $g['COLUMN_LIST_ID_UPDATE_VALUE'] = "";
        //----呼出元がUIがRestAPI/Excel/CSVかを判定
        // MENU_ID;未設定 COLUMN_LIST_ID:未設定 MENU_COLUMN_LIST_ID:設定 => RestAPI/Excel/CSV
        // その他はUI
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            if((strlen($rg_menu_id)             === 0) &&
               (strlen($rg_column_list_id)      === 0) &&
               (strlen($rg_rest_column_list_id) !== 0)){
                $query =  "SELECT                                             "
                         ."  TBL_A.COLUMN_LIST_ID,                            "
                         ."  TBL_A.MENU_ID,                                   "
                         ."  COUNT(*) AS COLUMN_LIST_ID_CNT,                  "
                         ."  (                                                "
                         ."    SELECT                                         "
                         ."      COUNT(*)                                     "
                         ."    FROM                                           "
                         ."      D_CMDB_MENU_LIST_SHEET_TYPE_3 TBL_B                       "
                         ."    WHERE                                          "
                         ."      TBL_B.MENU_ID      = TBL_A.MENU_ID AND       "
                         ."      TBL_B.DISUSE_FLAG  = '0'                     "
                         ."  ) AS MENU_CNT                                    "
                         ."FROM                                               "
                         ."  D_CMDB_MENU_COLUMN_SHEET_TYPE_3 TBL_A                     "
                         ."WHERE                                              "
                         ."  TBL_A.COLUMN_LIST_ID  = :COLUMN_LIST_ID   AND    "
                         ."  TBL_A.DISUSE_FLAG     = '0'                      ";
                $aryForBind = array();
                $aryForBind['COLUMN_LIST_ID'] = $rg_rest_column_list_id;
                $retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
                if( $retArray[0] === true ){
                    $objQuery =& $retArray[1];
                    $intCount = 0;
                    $row = $objQuery->resultFetch();
                    if( $row['MENU_CNT'] == '1' ){
                        if( $row['COLUMN_LIST_ID_CNT'] == '1' ){
                            $rg_menu_id               = $row['MENU_ID'];
                            $rg_column_list_id        = $row['COLUMN_LIST_ID'];
                            $g['MENU_ID_UPDATE_VALUE']        = $rg_menu_id;
                            $g['COLUMN_LIST_ID_UPDATE_VALUE'] = $rg_column_list_id;
                            if($boolExecuteContinue === true){
                                $boolExecuteContinue = true;
                            }
                        }else if( $row['COLUMN_LIST_ID_CNT'] == '0' ){
                            $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211310");
                            $retBool = false;
                            $boolExecuteContinue = false;
                        }else{
                            $boolSystemErrorFlag = true;
                        }
                    }else if( $row['MENU_CNT'] == '0' ){
                        $boolExecuteContinue = false;
                        $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211320");
                        $retBool = false;
                        $boolExecuteContinue = false;
                    }else{
                        $boolSystemErrorFlag = true;
                    }
                    unset($row);
                    unset($objQuery);
                }else{
                    $boolSystemErrorFlag = true;
                }
                unset($retArray);
            }
        }

        //メニューと項目の組み合わせチェック----
        //登録方式のチェック
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            $retBool = false;
            $boolExecuteContinue = false;
            if(strlen($rg_col_type) == 0){
                $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211330");
            }
            else{
                switch($rg_col_type){
                case '1':   // Value
                case '2':   // Key
                case '3':   // Key-Value
                    $retBool = true;
                    $boolExecuteContinue = true;
                    break;
                default:
                    $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211340");
                    break;
                }
            }
        }

        $g['PATTERN_ID_UPDATE_VALUE']        = "";
        $g['KEY_VARS_LINK_ID_UPDATE_VALUE']  = "";
        $g['VAL_VARS_LINK_ID_UPDATE_VALUE']  = "";
        //----呼出元がUIがRestAPI/Excel/CSVかを判定
        // PATTERN_ID;未設定 KEY_VARS_LINK_ID:未設定 REST_KEY_VARS_LINK_ID:設定 => RestAPI/Excel/CSV
        // その他はUI
        $chk_pattern_id = $rg_pattern_id;
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            if((strlen($chk_pattern_id)              === 0) &&
               (strlen($rg_key_vars_link_id)         === 0) &&
               (($rg_col_type == '2') || ($rg_col_type == '3')) &&
               (strlen($rg_rest_key_vars_link_id)    !== 0)){
                $query =  "SELECT                                             "
                         ."  TBL_A.MODULE_PTN_LINK_ID,                        "
                         ."  TBL_A.MODULE_VARS_LINK_ID,                              "
                         ."  TBL_A.PATTERN_ID,                                "
                         ."  COUNT(*) AS VARS_LINK_ID_CNT                     "
                         ."FROM                                               "
                         ."  E_TERRAFORM_PTN_VAR_LIST TBL_A                     "
                         ."WHERE                                              "
                         ."  TBL_A.MODULE_PTN_LINK_ID    = :MODULE_PTN_LINK_ID   AND      "
                         ."  TBL_A.DISUSE_FLAG     = '0'                      ";
                $aryForBind = array();
                $aryForBind['MODULE_PTN_LINK_ID'] = $rg_rest_key_vars_link_id;
                $retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
                if( $retArray[0] === true ){
                    $objQuery =& $retArray[1];
                    $intCount = 0;
                    $row = $objQuery->resultFetch();
                    if( $row['VARS_LINK_ID_CNT'] == '1' ){
                        $rg_pattern_id                       = $row['PATTERN_ID'];
                        $rg_key_vars_link_id                 = $row['MODULE_VARS_LINK_ID'];
                        $g['PATTERN_ID_UPDATE_VALUE']        = $rg_pattern_id;
                        $g['KEY_VARS_LINK_ID_UPDATE_VALUE']  = $rg_key_vars_link_id;
                    }else if( $row['VARS_LINK_ID_CNT'] == '0' ){
                        $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211350");
                        $retBool = false;
                        $boolExecuteContinue = false;
                    }else{
                        web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
                        $boolSystemErrorFlag = true;
                    }
                    unset($row);
                    unset($objQuery);
                }else{
                    web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
                    $boolSystemErrorFlag = true;
                }
                unset($retArray);
            }
        }
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            if((strlen($chk_pattern_id)              === 0) &&
               (strlen($rg_val_vars_link_id)         === 0) &&
               (($rg_col_type == '1') || ($rg_col_type == '3')) &&
               (strlen($rg_rest_val_vars_link_id)    !== 0)){
                $query =  "SELECT                                             "
                         ."  TBL_A.MODULE_PTN_LINK_ID,                        "
                         ."  TBL_A.MODULE_VARS_LINK_ID,                              "
                         ."  TBL_A.PATTERN_ID,                                "
                         ."  COUNT(*) AS VARS_LINK_ID_CNT                     "
                         ."FROM                                               "
                         ."  E_TERRAFORM_PTN_VAR_LIST TBL_A                     "
                         ."WHERE                                              "
                         ."  TBL_A.MODULE_PTN_LINK_ID    = :MODULE_PTN_LINK_ID   AND      "
                         ."  TBL_A.DISUSE_FLAG     = '0'                      ";
                $aryForBind = array();
                $aryForBind['MODULE_PTN_LINK_ID'] = $rg_rest_val_vars_link_id;
                $retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
                if( $retArray[0] === true ){
                    $objQuery =& $retArray[1];
                    $intCount = 0;
                    $row = $objQuery->resultFetch();
                    if( $row['VARS_LINK_ID_CNT'] == '1' ){
                        if(strlen($rg_pattern_id) != 0){
                            if($rg_pattern_id != $row['PATTERN_ID']){
                                $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211360");
                                $retBool = false;
                                $boolExecuteContinue = false;
                            }
                            else{
                                $rg_pattern_id                       = $row['PATTERN_ID'];
                                $rg_val_vars_link_id                 = $row['MODULE_VARS_LINK_ID'];
                                $g['PATTERN_ID_UPDATE_VALUE']        = $rg_pattern_id;
                                $g['VAL_VARS_LINK_ID_UPDATE_VALUE']  = $rg_val_vars_link_id;
                            }
                        }
                        else{
                            $rg_pattern_id                       = $row['PATTERN_ID'];
                            $rg_val_vars_link_id                 = $row['MODULE_VARS_LINK_ID'];
                            $g['PATTERN_ID_UPDATE_VALUE']        = $rg_pattern_id;
                            $g['VAL_VARS_LINK_ID_UPDATE_VALUE']  = $rg_val_vars_link_id;
                        }
                    }else if( $row['VARS_LINK_ID_CNT'] == '0' ){
                        $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211350");
                        $retBool = false;
                        $boolExecuteContinue = false;
                    }else{
                        web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
                        $boolSystemErrorFlag = true;
                    }
                    unset($row);
                    unset($objQuery);
                }else{
                    web_log("DB Access error file:" . basename(__FILE__) . " line:" . __LINE__);
                    $boolSystemErrorFlag = true;
                }
                unset($retArray);
            }
        }

        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            if( strlen($rg_menu_id) === 0 || strlen($rg_column_list_id) === 0 ) {
                $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211370");
                $boolExecuteContinue = false;
                $retBool = false;
            }
            else if( strlen($rg_col_type) === 0) {
                $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211330");
                $boolExecuteContinue = false;
                $retBool = false;
            }
            else if( strlen($rg_pattern_id) === 0 ){
                $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211380");
                $boolExecuteContinue = false;
                $retBool = false;
            }
        }

        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            $chk_value_key_flag = true;
            switch($rg_col_type){
            case '1':   // Value
                if((strlen($rg_key_vars_link_id) != 0)) {
                    $chk_value_key_flag = false;
                    $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211390",array("Value","Key"));
                }
                break;
            case '2':   // Key
                if((strlen($rg_val_vars_link_id) != 0)) {
                    $chk_value_key_flag = false;
                    $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211390",array("Key","Value"));
                }
                break;
            }

            if($chk_value_key_flag !== true) {
                $boolExecuteContinue = false;
                $retBool = false;
            }
        }

        //----メニューと項目の組み合わせチェック
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            $retBool = false;
            $boolExecuteContinue = false;
            $query = " SELECT "
                     ."   COUNT(*) AS MENU_CNT, "
                     ."   ( "
                     ."     SELECT  "
                     ."       COUNT(*) "
                     ."     FROM "
                     ."       D_CMDB_MENU_COLUMN_SHEET_TYPE_3 TBL_B "
                     ."     WHERE "
                     ."       TBL_B.MENU_ID        = :MENU_ID          AND "
                     ."       TBL_B.COLUMN_LIST_ID = :COLUMN_LIST_ID   AND "
                     ."       TBL_B.DISUSE_FLAG  = '0' "
                     ."   ) AS COLUMN_CNT "
                     ." FROM "
                     ."   D_CMDB_MENU_LIST_SHEET_TYPE_3 TBL_A  "
                     ." WHERE "
                     ."   TBL_A.MENU_ID      = :MENU_ID   AND "
                     ."   TBL_A.DISUSE_FLAG  = '0' ";

            $aryForBind = array();
            $aryForBind['MENU_ID']        = $rg_menu_id;
            $aryForBind['COLUMN_LIST_ID'] = $rg_column_list_id;

            $retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
            if( $retArray[0] === true ){
                $objQuery =& $retArray[1];
                $intCount = 0;
                $row = $objQuery->resultFetch();
                if( $row['MENU_CNT'] == '1' ){
                    if( $row['COLUMN_CNT'] == '1' ){
                        $retBool = true;
                        $boolExecuteContinue = true;
                    }else if( $row['COLUMN_CNT'] == '0' ){
                        $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211310");
                    }else{
                        $boolSystemErrorFlag = true;
                    }
                    $retBool = true;
                }else if( $row['MENU_CNT'] == '0' ){
                    $boolExecuteContinue = false;
                    $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211320");
                }else{
                    $boolSystemErrorFlag = true;
                }
                unset($row);
                unset($objQuery);
            }else{
                $boolSystemErrorFlag = true;
            }
            unset($retArray);
        }
        //メニューと項目の組み合わせチェック----
        //----作業パターンのチェック
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            $retBool = false;
            $boolExecuteContinue = false;
            $query = " SELECT "
                     ."   COUNT(*) AS PATTAN_CNT "
                     ." FROM "
                     ."   $pattan_tbl TBL_A  "
                     ." WHERE "
                     ."   TBL_A.PATTERN_ID   = :PATTERN_ID   AND "
                     ."   TBL_A.DISUSE_FLAG  = '0' ";

            $aryForBind = array();
            $aryForBind['PATTERN_ID']     = $rg_pattern_id;

            $retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
            if( $retArray[0] === true ){
                $objQuery =& $retArray[1];
                $intCount = 0;
                $row = $objQuery->resultFetch();
                if( $row['PATTAN_CNT'] == '1' ){
                    $retBool = true;
                    $boolExecuteContinue = true;
                }else if( $row['PATTAN_CNT'] == '0' ){
                    $boolExecuteContinue = false;
                    $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211400");
                }else{
                    $boolSystemErrorFlag = true;
                }
                unset($row);
                unset($objQuery);
            }else{
                $boolSystemErrorFlag = true;
            }
            unset($retArray);
        }
        //作業パターンのチェック----

        //----Key変数の種類ごとに、バリデーションチェック
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            switch($rg_col_type){
            case '2':   // Key
            case '3':   // Key-Value
                // --UPD--
                $vars_link_id          = $rg_key_vars_link_id;
                // Key変数入力チェック
                if(strlen($vars_link_id) == 0){
                    // --UPD--
                    $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211410");
                    $retBool = false;
                    $boolExecuteContinue = false;
                    break;
                }
                // --UPD--
                $strQuery = "SELECT "
                       ." TAB_1.MODULE_VARS_LINK_ID "
                       ."FROM "
                       ." $ptn_vars_link_view TAB_1 "
                       ."WHERE "
                       ." TAB_1.DISUSE_FLAG = '0' "
                       ." AND TAB_1.MODULE_VARS_LINK_ID = :MODULE_VARS_LINK_ID ";

                $aryForBind = array();
                $aryForBind['MODULE_VARS_LINK_ID'] = $vars_link_id;

                $retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VARS_TYPE_CHECK)");
                if( $retArray[0] === true ){
                    $objQuery = $retArray[1];
                    $tmpAryRow = array();
                    while($row = $objQuery->resultFetch() ){
                        $tmpAryRow[]= $row;
                    }

                    if( count($tmpAryRow) === 0 ){
                        $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211420");
                        $retBool = false;
                        $boolExecuteContinue = false;
                    }
                    unset($tmpAryRow);
                    unset($objQuery);
                }
                unset($retArray);

                if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
                    //作業パターンの組み合わせチェック
                    $retBool = false;
                    $query = "SELECT "
                            ." COUNT(*) REC_COUNT "
                            ."FROM "
                            ." D_TERRAFORM_PTN_VARS_LINK_VFP TAB_1 "
                            ."WHERE "
                            ." TAB_1.DISUSE_FLAG = '0' "
                            ."AND TAB_1.PATTERN_ID = :PATTERN_ID "
                            ."AND TAB_1.MODULE_VARS_LINK_ID = :MODULE_VARS_LINK_ID ";
        
                    $aryForBind = array();
                    $aryForBind['PATTERN_ID'] = $rg_pattern_id;
                    $aryForBind['MODULE_VARS_LINK_ID'] = $vars_link_id;
        
                    $retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
                    if( $retArray[0] === true ){
                        $objQuery =& $retArray[1];
                        $intCount = 0;
                        $aryDiscover = array();
                        $row = $objQuery->resultFetch();
                        unset($objQuery);
                        if( $row['REC_COUNT'] == '1' ){
                            $retBool = true;
                        }else if( $row['REC_COUNT'] == '0' ){
                            $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211470");
                        }else{
                            $boolSystemErrorFlag = true;
                        }
                        unset($row);
                        unset($objQuery);
                    }else{
                        $boolSystemErrorFlag = true;
                    }
                    unset($retArray);
                }
                break;
            }
        }
        //----Value変数の種類ごとに、バリデーションチェック
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false){
            switch($rg_col_type){
            case '1':   // Value
            case '3':   // Key-Value
                // --UPD--
                $vars_link_id          = $rg_val_vars_link_id;
                // Key変数入力チェック
                if(strlen($vars_link_id) == 0){
                    // --UPD--
                    $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211430");
                    $retBool = false;
                    $boolExecuteContinue = false;
                    break;
                }
                // --UPD--
                $strQuery = "SELECT "
                       ." TAB_1.MODULE_VARS_LINK_ID "
                       ."FROM "
                       ." $ptn_vars_link_view TAB_1 "
                       ."WHERE "
                       ." TAB_1.DISUSE_FLAG = '0' "
                       ." AND TAB_1.MODULE_VARS_LINK_ID = :MODULE_VARS_LINK_ID ";

                $aryForBind = array();
                $aryForBind['MODULE_VARS_LINK_ID'] = $vars_link_id;

                $retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VARS_TYPE_CHECK)");
                if( $retArray[0] === true ){
                    $objQuery = $retArray[1];
                    $tmpAryRow = array();
                    while($row = $objQuery->resultFetch() ){
                        $tmpAryRow[]= $row;
                    }

                    if( count($tmpAryRow) === 0 ){
                        $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211440");
                        $retBool = false;
                        $boolExecuteContinue = false;
                    }

                    unset($tmpAryRow);
                    unset($objQuery);
                }
                unset($retArray);

                if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
                    //作業パターンの組み合わせチェック
                    $retBool = false;
                    $query = "SELECT "
                            ." COUNT(*) REC_COUNT "
                            ."FROM "
                            ." D_TERRAFORM_PTN_VARS_LINK_VFP TAB_1 "
                            ."WHERE "
                            ." TAB_1.DISUSE_FLAG = '0' "
                            ."AND TAB_1.PATTERN_ID = :PATTERN_ID "
                            ."AND TAB_1.MODULE_VARS_LINK_ID = :MODULE_VARS_LINK_ID ";
        
                    $aryForBind = array();
                    $aryForBind['PATTERN_ID'] = $rg_pattern_id;
                    $aryForBind['MODULE_VARS_LINK_ID'] = $vars_link_id;
        
                    $retArray = singleSQLExecuteAgent($query, $aryForBind, "NONAME_FUNC(VARS_MULTI_CHECK)");
                    if( $retArray[0] === true ){
                        $objQuery =& $retArray[1];
                        $intCount = 0;
                        $aryDiscover = array();
                        $row = $objQuery->resultFetch();
                        unset($objQuery);
                        if( $row['REC_COUNT'] == '1' ){
                            $retBool = true;
                        }else if( $row['REC_COUNT'] == '0' ){
                            $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211470");
                        }else{
                            $boolSystemErrorFlag = true;
                        }
                        unset($row);
                        unset($objQuery);
                    }else{
                        $boolSystemErrorFlag = true;
                    }
                    unset($retArray);
                }
                break;
            }
        }
        //変数の種類ごとに、バリデーションチェック----

        // 代入値自動登録設定テーブルの重複レコードチック
        if( $boolExecuteContinue === true && $boolSystemErrorFlag === false ){
            $strQuery =   "SELECT "
                        . "  COLUMN_ID "
                        . "FROM "
                        . " B_TERRAFORM_VAL_ASSIGN "
                        . "WHERE  "
                        . " COLUMN_ID   <> :COLUMN_ID AND "
                        . " PATTERN_ID  =  :PATTERN_ID  AND "
                        . " DISUSE_FLAG =  '0'"
                        . " AND (";

            $aryForBind = array();
            $aryForBind['COLUMN_ID']    = $columnId;
            $aryForBind['PATTERN_ID']   = $rg_pattern_id;

            // Key変数が必須の場合
            if(in_array($rg_col_type, array(2, 3))){
                $strQuery .= " ( ";
                $strQuery .= "COL_TYPE in (2, 3) AND KEY_VARS_LINK_ID = :KEY_VARS_LINK_ID_1 ";
                $strQuery .= " ) OR (";
                $strQuery .= "COL_TYPE in (1, 3) AND VAL_VARS_LINK_ID = :VAL_VARS_LINK_ID_1 ";
                $strQuery .= " ) ";
                $aryForBind['KEY_VARS_LINK_ID_1']           = $rg_key_vars_link_id;
                $aryForBind['VAL_VARS_LINK_ID_1']           = $rg_key_vars_link_id;
            }

            if(in_array($rg_col_type, array(3))){
                $strQuery .= " OR ";
            }

            // Value変数が必須の場合
            if(in_array($rg_col_type, array(1, 3))){
                $strQuery .= " ( ";
                $strQuery .= "COL_TYPE in (2, 3) AND KEY_VARS_LINK_ID = :KEY_VARS_LINK_ID_2 ";
                $strQuery .= " ) OR (";
                $strQuery .= "COL_TYPE in (1, 3) AND VAL_VARS_LINK_ID = :VAL_VARS_LINK_ID_2 ";
                $strQuery .= " ) ";
                $aryForBind['KEY_VARS_LINK_ID_2']           = $rg_val_vars_link_id;
                $aryForBind['VAL_VARS_LINK_ID_2']           = $rg_val_vars_link_id;
            }
            $strQuery .= " ) ";
            $retArray = singleSQLExecuteAgent($strQuery, $aryForBind, "NONAME_FUNC(VALASSIGN_DUP_CHECK)");
            if( $retArray[0] === true ){
                $objQuery = $retArray[1];
                $dupnostr = "";
                while($row = $objQuery->resultFetch() ){
                    $dupnostr = $dupnostr . "[" . $row['COLUMN_ID'] . "]";
                }
                if( strlen($dupnostr) != 0 ){
                    $retBool = false;
                    $boolExecuteContinue = false;
                    $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211450",array($dupnostr));
                }
                unset($objQuery);

                // key-Value型で各変数が同一か判定
                if($retBool === true) {
                    if(in_array($rg_col_type, array(3))){
                        if($rg_key_vars_link_id === $rg_val_vars_link_id) {
                            $retBool = false;
                            $boolExecuteContinue = false;
                            $retStrBody = $g['objMTS']->getSomeMessage("ITATERRAFORM-ERR-211460");
                        }
                    }
                }

            }
            else{
                $boolSystemErrorFlag = true;
            }
            unset($retArray);
        }

        if( $boolSystemErrorFlag === true ){
            $retBool = false;
            //----システムエラー
            $retStrBody = $g['objMTS']->getSomeMessage("ITAWDCH-ERR-3001");
        }

        if($retBool===false){
            $objClientValidator->setValidRule($retStrBody);
        }
        return $retBool;
    };

    $objVarVali = new VariableValidator();
    $objVarVali->setErrShowPrefix(false);
    $objVarVali->setFunctionForIsValid($objFunction);
    $objVarVali->setVariantForIsValid(array());

    $objLU4UColumn->addValidator($objVarVali);
    //組み合わせバリデータ----

    $table->setGeneObject('webSetting', $arrayWebSetting);
    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
