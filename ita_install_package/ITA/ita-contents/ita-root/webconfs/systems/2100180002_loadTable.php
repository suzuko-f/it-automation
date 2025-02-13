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
//    ・WebDBCore機能を用いたWebページの中核設定を行う。
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITABASEH-MNU-305030");
/*
シンフォニークラス情報
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
        'TT_SYS_NDB_LUP_TIME_ID'=>'UPD_UPDATE_TIMESTAMP',
        'TT_SYS_08_DUPLICATE_ID'=>'WEB_BUTTON_DUPLICATE'
    );

    $table = new TableControlAgent('C_CONDUCTOR_EDIT_CLASS_MNG','CONDUCTOR_CLASS_NO', $g['objMTS']->getSomeMessage("ITABASEH-MNU-305040"), 'C_CONDUCTOR_EDIT_CLASS_MNG_JNL', $tmpAry);
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['CONDUCTOR_CLASS_NO']->setSequenceID('C_CONDUCTOR_EDIT_CLASS_MNG_RIC');
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('C_CONDUCTOR_EDIT_CLASS_MNG_JSQ');
    unset($tmpAryColumn);
    $table->setJsEventNamePrefix(true);
    
    // QMファイル名プレフィックス
    $table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITABASEH-MNU-305050"));
    // エクセルのシート名
    $table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile', $g['objMTS']->getSomeMessage("ITABASEH-MNU-305060"));

    $table->setAccessAuth(true);    // データごとのRBAC設定


    //----リンクボタン
    $objFunction = function($rowData){
            $retLinkable = true;
            global $g;

            $php_req_gate_php = '/libs/commonlibs/common_php_req_gate.php';
            ($g['root_dir_path'] . $php_req_gate_php );

            try
            {
                // DBコネクト
                $db_connect_php = '/libs/commonlibs/common_db_connect.php';
                require ($g['root_dir_path'] . $db_connect_php );

                $sql = "SELECT DISUSE_FLAG FROM C_CONDUCTOR_EDIT_CLASS_MNG WHERE (CONDUCTOR_CLASS_NO = :CONDUCTOR_CLASS_NO)";

                // SQLパース
                $objQuery = $objDBCA->sqlPrepare($sql);
                if( $objQuery->getStatus()===false )
                {
                    // 例外処理へ
                    throw new Exception( "sqlPrepare Error" );
                }

                // SQLバインド
                $arrayBind = array();
                $arrayBind['CONDUCTOR_CLASS_NO'] = $rowData['CONDUCTOR_CLASS_NO'];
                if( $objQuery->sqlBind($arrayBind) != "" )
                {
                    // 例外処理へ
                    throw new Exception( "sqlBind Error" );
                }

                // SQL実行
                $r = $objQuery->sqlExecute();
                if(!$r)
                {
                    // 例外処理へ
                    throw new Exception( "sqlExecute Error" );
                }
            }
            catch (Exception $e)
            {

            }

            $row = $objQuery->resultFetch();
            $disUseFlag = $row['DISUSE_FLAG'];

            // DBアクセス事後処理
            if ( isset($objQuery)  ) unset($objQuery);

            if( $disUseFlag === "1" )
            {
                //ボタンを非活性とする
                $retLinkable = "disabled";
            }
            return $retLinkable;
        };

    $c = new LinkButtonColumn('detail_show',$g['objMTS']->getSomeMessage("ITABASEH-MNU-201010"), $g['objMTS']->getSomeMessage("ITABASEH-MNU-201020"), 'jumpToConductorClassEdit', array(':CONDUCTOR_CLASS_NO')); 
    $c->setOutputType('print_table', new OutputType(new SortedTabHFmt(),new LinkButtonTabBFmt(0,array($objFunction),array(""))));
    $c->setEvent("print_table", "onClick", "jumpToConductorClassEdit", array(':CONDUCTOR_CLASS_NO'));
    $c->setDBColumn(false);
    $table->addColumn($c);

    //リンクボタン----

    $objVldt = new SingleTextValidator(1,256,false);
    $c = new TextColumn('CONDUCTOR_NAME',$g['objMTS']->getSomeMessage("ITABASEH-MNU-305070"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITABASEH-MNU-305080"));//エクセル・ヘッダでの説明
    $c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $c->setUnique(true);//登録/更新時には、DB上ユニークな入力であること必須
    $table->addColumn($c);

    $c = new MultiTextColumn('DESCRIPTION',$g['objMTS']->getSomeMessage("ITABASEH-MNU-305090"));
    $table->addColumn($c);


    // 登録/更新/廃止/復活があった場合、代入値自動登録設定のbackyard処理の処理済みフラグをOFFにする
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
                       ."WHERE ROW_ID IN (2100020002,2100020004,2100020006,2100080002) ";

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
    $tmpAryColumn[$table->getDBTablePK()]->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);


    $table->fixColumn();
    $tmpAryColumn= $table->getColumns();

    $strResultType01 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12202");   //登録
    $strResultType02 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12203");   //更新
    $strResultType03 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12204");   //廃止
    $strResultType04 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12205");   //復活
    $strResultType99 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-12206");   //エラー

    $tmpAryColumn['ROW_EDIT_BY_FILE']->setResultCount(array( 
        'delete'=>array('name'=>"$strResultType03"  ,'ct'=>0)
        ,'revive'  =>array('name'=>"$strResultType04"  ,'ct'=>0)
        ,'error'  =>array('name'=>"$strResultType99"  ,'ct'=>0)
        )
    );

    $tmpAryColumn['ROW_EDIT_BY_FILE']->setCommandArrayForEdit(array( 
       3=>"$strResultType03"
       ,4=>"$strResultType04"
        )
    );
    $outputType = new OutputType(new TabHFmt(), new DelTabBFmt());
    $tmpAryColumn['WEB_BUTTON_UPDATE']->getOutputType('print_table')->setVisible(false);
    $tmpAryColumn['WEB_BUTTON_DUPLICATE']->getOutputType('print_table')->setVisible(false);

    list($strTmpValue,$tmpKeyExists) = isSetInArrayNestThenAssign($aryVariant,array('callType'),null);
    if( $tmpKeyExists===true ){
        if(($strTmpValue=="insConstruct" ) ||
           ($strTmpValue=="insConstruct_ConductorExport" )){
            $objRadioColumn = $tmpAryColumn['WEB_BUTTON_UPDATE'];
            $objRadioColumn->setColLabel($g['objMTS']->getSomeMessage("ITABASEH-MNU-201050"));

            if($strTmpValue=="insConstruct" ) {
                $objFunctionB = function ($objOutputType, $rowData, $aryVariant, $objColumn){
                    $strInitedColId = $objColumn->getID();

                    $aryVariant['callerClass'] = get_class($objOutputType);
                    $aryVariant['callerVars'] = array('initedColumnID'=>$strInitedColId,'free'=>null);
                    $strRIColId = $objColumn->getTable()->getRIColumnID();

                    #$rowData[$strInitedColId] = '<input type="radio" name="symNo" onclick="javascript:ConductorLoadForExecute(' . $rowData[$strRIColId] . ')"/>';

                    $rowData[$strInitedColId] = '<input type="radio" name="symNo" onclick="javascript:symphonyLoadForExecute(' . $rowData[$strRIColId] . ')"/>';

                    return $objOutputType->getBody()->getData($rowData,$aryVariant);
                };
            }
            else{
                $objFunctionB = function ($objOutputType, $rowData, $aryVariant, $objColumn){
                    $strInitedColId = $objColumn->getID();

                    $aryVariant['callerClass'] = get_class($objOutputType);
                    $aryVariant['callerVars'] = array('initedColumnID'=>$strInitedColId,'free'=>null);
                    $strRIColId = $objColumn->getTable()->getRIColumnID();

                    $rowData[$strInitedColId] = '<input type="checkBox" name="symNo" value="' . $rowData[$strRIColId] .'"/>';

                    return $objOutputType->getBody()->getData($rowData,$aryVariant);
                };
            }

            $objTTBF = new TextTabBFmt();
            $objTTHF = new TabHFmt();//new SortedTabHFmt();
            $objTTBF->setSafingHtmlBeforePrintAgent(false);
            $objOutputType = new VariantOutputType($objTTHF, $objTTBF);
            $objOutputType->setFunctionForGetBodyTag($objFunctionB);
            $objOutputType->setVisible(true);
            $objRadioColumn->setOutputType("print_table", $objOutputType);
            
            $table->getFormatter('print_table')->setGeneValue("linkExcelHidden",true);
            $table->getFormatter('print_table')->setGeneValue("linkCSVFormShow",false);
        }
    }
    unset($tmpAryColumn);
    $table->setGeneObject('webSetting', $arrayWebSetting);
    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
