<?php
// die("DBG-mode qsearch");
require_once(dirname(__FILE__) . "/../../../config/global_config.php");

$lang = AfwLanguageHelper::getGlobalLanguage();
$please_wait = AFWObject::gtr("PLEASE_WAIT", $lang);
$loading = AFWObject::gtr("LOADING", $lang);
$please_wait_loading = $please_wait . " " . $loading;

$themeArr = AfwThemeHelper::loadTheme();
foreach ($themeArr as $theme => $themeValue) {
        $$theme = $themeValue;
}
require_once("afw_rights.php");


$new_instance =  AfwLanguageHelper::translateKeyword("new_instance", $lang);
$qedit_new =     AfwLanguageHelper::translateKeyword("qedit_new", $lang);
$qedit_update =  AfwLanguageHelper::translateKeyword("qedit_update", $lang);
// die("DBG-qsearch requirements");

if (!$currmod) {
        $currmod = AfwUrlManager::currentWebModule();
} else AfwAutoLoader::addModule($currmod);

AfwMainPage::initOutput("");
$objme = AfwSession::getUserConnected();
if (!$objme) {
        AfwSession::pushError("الرجاء تسجيل الدخول أولا");
        header("Location: login.php");
        exit();
}

/**
 * @var Auser $objme
 * 
 */


if ($xls_on) $datatable_on = true;
if ($migration_on) $datatable_on = true;
if (!$action) $action = "retrieve";
if (!$action_params) $action_params = "";

if ($fixed_criterea_arr) $session_previous_search = $fixed_criterea_arr;
else $session_previous_search = AfwSession::getSessionVar("search-$cl");

/*
if ($session_previous_search and ($cl == "WorkflowRequest")) {
        die("DBG-session_previous_search=" . var_export($session_previous_search, true));
}
*/


if (($session_previous_search) and (!$datatable_on)) {
        /*if ($session_previous_search and ($cl == "WorkflowRequest")) {
                die("DBG-session_previous_search=" . var_export($session_previous_search, true));
        }*/
        foreach ($session_previous_search as $criteria_arr) {

                $nom_col = $criteria_arr["col"];
                $_POST[$nom_col] = $criteria_arr["val"];
                ${$nom_col} = $criteria_arr["val"];
                $_POST["oper_" . $nom_col] = $criteria_arr["oper"];
                ${"oper_" . $nom_col} = $criteria_arr["oper"];
        }

        if (!$datatable_off) $datatable_on = 1;
}
/*
if ($_POST and ($cl == "WorkflowRequest")) {
        die("DBG-_POST=" . var_export($_POST, true));
}*/

if (!$currmod) {
        $currmod = $uri_module;
}

$cl_short = strtolower(substr($cl, 0, 10));
/**
 * @var AFWObject $myClassInstance
 * 
 */
$myClassInstance = new $cl();
$my_contextCols = $myClassInstance->getContextCols();
$take_context = true;
$force_context = true;
if ($objme) {
        foreach ($my_contextCols as $ccol) {
                $ccol = trim($ccol);
                $val_col_context = $objme->getContextValue($currmod, $ccol);
                if ($_POST[$ccol] and ($val_col_context != $_POST[$ccol])) $take_context = false;
        }

        if ($take_context or $force_context) {
                foreach ($my_contextCols as $ccol) {
                        $ccol = trim($ccol);
                        $val_col_context = $objme->getContextValue($currmod, $ccol);
                        if (!$_POST[$ccol]) $_POST[$ccol] = $val_col_context;
                        ${$ccol} = $_POST[$ccol];
                        $_POST["oper_" . $ccol] = "=";
                        ${"oper_" . $ccol} = "=";
                }
        }
}

$actions_tpl_arr = AfwUmsPagHelper::getAllActions($myClassInstance, 0, false);
// throw new AfwRun timeException("debugg :: actions_tpl_arr of $cl = ".var_export($actions_tpl_arr,true));
if ($resetcrit) {
        $_POST = array();
        $datatable_on = false;
}

$excel_link = "";
$search_result_html = "";

// $myClassInstance->debuggObj($_POST);
if ($datatable_on) {
        // die("DBG-before afw_handle_default_search");
        $handle_return = include 'afw_handle_default_search.php';
        $excel_link = $handle_return['excel_link'];
        $search_result_html = $handle_return['search_result_html'];
        AfwSession::log("End of afw_handle_default_search");
        // die("DBG-after afw_handle_default_search");
        $collapse_show = "";
} else $collapse_show = "show";




$newo = $myClassInstance->QEDIT_MODE_NEW_OBJECTS_AFTER_SEARCH;
if (!$newo) $newo = $myClassInstance->QEDIT_MODE_NEW_OBJECTS_DEFAULT_NUMBER;

//

if ($objme) {
        $report_can_qsearch = AfwSession::getLog("iCanDo");
        $can = $objme->iCanDoOperationOnObjClass($myClassInstance, "qsearch");
        $report_can_qsearch = AfwSession::getLog("iCanDo");
        $canEdit = $objme->iCanDoOperationOnObjClass($myClassInstance, "edit");
        // $report_can_edit = AfwSession::getLog("iCanDo");
        // if(($cl == "CrmOrgunit") and $canEdit)  die("report_can_edit=$report_can_edit");
} else {
        $can = $myClassInstance->public_search;
        $canEdit = $myClassInstance->public_edit;
}
// $objme->showICanDoLog();
// $myClassInstance->simpleError("debugg :: iCanDoOperationLog ::");

if (!$can) {
        $myClassInstanceClass = get_class($myClassInstance);
        AfwSession::setSessionVar("operation", "quick search on $myClassInstanceClass class");
        AfwSession::setSessionVar("result", "failed");
        AfwSession::setSessionVar("report", $report_can_qsearch);
        AfwSession::setSessionVar("other_log", $log);
        header("Location: /lib/afw/modes/afw_denied_access_page.php");
        exit();
}






//debug
////AFWObject::setDebugg(true);
//AFWDebugg::initialiser($START_TREE.$TMP_DIR,"afw-debugg".$g_array_user["PAGE_NUMBER"].".txt");

// **@todo $hide_cr = AfwLanguageHelper::tarjem('HIDE CRITEREA',$lang,false);
// **@todo $show_cr = AfwLanguageHelper::tarjem('SHOW CRITEREA',$lang,false);

$plural_obj_name =  $myClassInstance->transClassPlural($lang, false, true);
$plural_obj_name_short =  $myClassInstance->transClassPlural($lang, true, true);
$single_obj_name =  $myClassInstance->transClassSingle($lang);


$out_scr_btns = "";
if ($datatable_on) {
        $btns_total = 0;
        $btns_display = [];
        //$btns_display["lookup"] = (($newo>0) and ($myClassInstance->IS_LOOKUP) and $objme and $objme->isAdmin()) ? 1 : 0;
        $btns_display["lookup"] = (($newo > 0) and (!$myClassInstance->OwnedBy) and $objme and $objme->isAdmin()) ? 1 : 0;
        $btns_total += $btns_display["lookup"];

        $btns_display["excel"] = ($myClassInstance->excelExport and ($ids_count > 0) and $objme) ? 1 : 0;
        $btns_total += $btns_display["excel"];

        $btns_display["pdf"] = ($myClassInstance->pdfExport and ($ids_count > 0) and $objme) ? 1 : 0;
        $btns_total += $btns_display["pdf"];

        if (AfwSession::config('MODE_DEVELOPMENT', false) and $myClassInstance->migrationExport) {
                $btns_display["migration"] = (($ids_count > 0) and $objme) ? 1 : 0;
                $btns_total += $btns_display["migration"];
        }





        $btns_display["ddb"] = ($ids and ($ids_count > 1) and ($ids_count < 8) and (!$myClassInstance->OwnedBy) and $objme and $objme->isAdmin()) ? 1 : 0;
        $btns_total += $btns_display["ddb"];

        $btns_display["add"] = (($cl) and (!$myClassInstance->OwnedBy) and $canEdit) ? 1 : 0;
        $btns_total += $btns_display["add"];

        $btns_display["qedit-result"] = ($ids and ($ids_count < 101) and (!$myClassInstance->OwnedBy) and $objme and $objme->isAdmin()) ? 1 : 0;
        $btns_total += $btns_display["qedit-result"];





        $out_scr_btns .= "<div class='btns-qsearch'>";

        $btn_num = 1;


        if ($btns_display["add"]) {
                $other_hidden_arr = [];
                $fixmSearchColsArr = $myClassInstance->getFixmSearchCols($_POST);
                foreach ($fixmSearchColsArr as $fixmSearchCol => $fixmSearchVal) {
                        $other_hidden_arr["sel_$fixmSearchCol"] = $fixmSearchVal;
                }
                $out_scr_btns .= '<div class="btn-qsearch btn-centered-' . $btns_total . '-btn-' . $btn_num . '" style="">';
                $out_scr_btns .= '<form name="editForm" id="editForm" method="post" action="' . "main.php" . '">';
                $out_scr_btns .= '<input type="hidden" name="Main_Page" value="afw_mode_edit.php"/>';
                $out_scr_btns .= '<input type="hidden" name="cl" value="' . $cl . '"/>';
                $out_scr_btns .= '<input type="hidden" name="currmod" value="' . $currmod . '"/>';
                $out_scr_btns .= '<input type="hidden" name="action" value="' . $action . '"/>';
                $out_scr_btns .= '<input type="submit" class="longbtn bluebtn submit-btn fright" name="submit"  id="submit-form-new-instance" value="' . $new_instance . " " . $single_obj_name . '" />
                                  <input type="hidden" size="3" name="newo" value="' . $newo . '"/>';
                foreach ($other_hidden_arr as $hiddenCol => $hiddenVal) {
                        $out_scr_btns .= "<input type=\"hidden\" name=\"$hiddenCol\" value=\"$hiddenVal\"/>";
                }
                $out_scr_btns .= '<input type="hidden" name="limit" value=""/>';
                $out_scr_btns .= '</form>';
                $out_scr_btns .= '</div>';
                $btn_num++;
        }

        if ($btns_display["lookup"]) {
                $fixm_arr = [];
                $other_hidden_arr = [];
                $fixmSearchColsArr = $myClassInstance->getFixmSearchCols($_POST);
                foreach ($fixmSearchColsArr as $fixmSearchCol => $fixmSearchVal) {
                        $fixm_arr[] = "$fixmSearchCol=$fixmSearchVal";
                        $other_hidden_arr["sel_$fixmSearchCol"] = $fixmSearchVal;
                }
                $other_hidden_arr["fixm"] = implode(",", $fixm_arr);

                $out_scr_btns .= '<div class="btn-qsearch btn-centered-' . $btns_total . '-btn-' . $btn_num . '" style="">';
                $out_scr_btns .= '<form name="qeditForm" id="qeditForm" method="post" action="' . "main.php" . '">';
                $out_scr_btns .= '<input type="hidden" name="Main_Page" value="afw_mode_qedit.php"/>';
                $out_scr_btns .= '<input type="hidden" name="cl" value="' . $cl . '"/>';
                $out_scr_btns .= '<input type="hidden" name="currmod" value="' . $currmod . '"/>';
                $out_scr_btns .= '<input type="submit" class="longbtn purplebtn submit-btn fright" name="submit"  id="submit-form-qedit-new" value="' . $qedit_new . " " . $plural_obj_name_short . '" />';
                $out_scr_btns .= '<input type="hidden" size="3" name="newo" value="' . $newo . '"/>';
                foreach ($other_hidden_arr as $hiddenCol => $hiddenVal) {
                        $out_scr_btns .= "<input type=\"hidden\" name=\"$hiddenCol\" value=\"$hiddenVal\"/>";
                }
                $out_scr_btns .= '<input type="hidden" name="limit" value=""/>';
                $out_scr_btns .= '</form>';
                $out_scr_btns .= '</div>';
                $btn_num++;
        }




        if ($btns_display["excel"]) {

                $out_scr_btns .= '<div class="btn-qsearch btn-centered-' . $btns_total . '-btn-' . $btn_num . '" style="">';
                if (!$excel_link) {
                        $xls_export = $myClassInstance->translate('EXCEL-EXPORT', $lang, true);
                        $out_scr_btns .= '<form name="xlsForm" id="xlsForm" method="post" action="' . "main.php" . '">';
                        $out_scr_btns .= '<input type="hidden" name="xls_on"  value="1"/>';
                        $out_scr_btns .= '<input type="hidden" name="cl" value="' . $cl . '"/>';
                        $out_scr_btns .= '<input type="hidden" name="currmod" value="' . $currmod . '"/>';
                        $out_scr_btns .= '<input type="hidden" name="limite"    value="0"/>';
                        $out_scr_btns .= '<input type="hidden" name="Main_Page" value="' . $current_page . '"/>';
                        $out_scr_btns .= AfwShowHelper::showObject($myClassInstance, "HTML", "afw_hidden_search_criteria.php");
                        $out_scr_btns .= '<input type="submit" class="longbtn greenbtn submit-btn fright" name="submit_xls"  id="submit_xls" value="' . $xls_export . '" />';
                        $out_scr_btns .= '</form>';
                } else {
                        $xls_download = $myClassInstance->translate('EXCEL-DOWNLOAD', $lang, true);
                        $out_scr_btns .= '<a target="_excel" href="' . $excel_link . '" class="longbtn greenbtn excel submit-btn fright"> ' . $xls_download . '</a>';
                }

                $out_scr_btns .= '</div>';
                $btn_num++;
        }

        if ($btns_display["pdf"]) {
                $out_scr_btns .= '<div class="btn-qsearch btn-centered-' . $btns_total . '-btn-' . $btn_num . '" style="">';
                $pdf_export = $myClassInstance->translate('PDF-EXPORT', $lang, true);
                $out_scr_btns .= '<input type="button" class="longbtn orangebtn submit-btn fright" name="submit_pdf"  id="submit_pdf" value="' . $pdf_export . '" onclick="exportToPDF()" />';
                $classe_pdf = strtolower(get_class($myClassInstance));
                $out_scr_btns .= AfwShowHelper::showPdfButton('example', $classe_pdf, $page_title);
                $out_scr_btns .= '</div>';
                $btn_num++;
        }

        if ($btns_display["migration"]) {
                $out_scr_btns .= '<div class="btn-qsearch btn-centered-' . $btns_total . '-btn-' . $btn_num . '" style="">';
                $migration_export = $myClassInstance->translate('MIGRATION-EXPORT', $lang, true);

                $out_scr_btns .= '<form name="migrationForm" id="migrationForm" method="post" action="' . 'main.php' . '">';
                $out_scr_btns .= '<input type="hidden" name="migration_on"  value="1"/>';
                $out_scr_btns .= '<input type="hidden" name="cl" value="' . $cl . '"/>';
                $out_scr_btns .= '<input type="hidden" name="currmod" value="' . $currmod . '"/>';
                $out_scr_btns .= '<input type="hidden" name="limite"    value="0"/>';
                $out_scr_btns .= '<input type="hidden" name="Main_Page" value="afw_mode_qsearch.php"/>';
                $out_scr_btns .= AfwShowHelper::showObject($myClassInstance, "HTML", "afw_hidden_search_criteria.php");
                $out_scr_btns .= '<input type="submit" class="longbtn greenbtn submit-btn fright" name="submit_migration"  id="submit_migration" value="' . $migration_export . '" />';
                $out_scr_btns .= '</form>';
                $out_scr_btns .= '</div>';
                $btn_num++;
        }

        if ($btns_display["qedit-result"]) {
                $out_scr_btns .= '<div class="btn-qsearch btn-centered-' . $btns_total . '-btn-' . $btn_num . '" style="">';
                $out_scr_btns .= '<form name="qedit_updateForm" id="qedit_updateForm" method="post" action="' . "main.php" . '">';
                $out_scr_btns .= '<input type="hidden" name="Main_Page" value="afw_mode_qedit.php"/>';
                $out_scr_btns .= '<input type="hidden" name="cl" value="' . $cl . '"/>';
                $out_scr_btns .= '<input type="hidden" name="currmod" value="' . $currmod . '"/>';
                $out_scr_btns .= '<input type="submit" class="longbtn yellowbtn submit-btn fright" name="submit"  id="submit-form" value="' . $qedit_update . '" />
                                        <input type="hidden" size="3" name="newo" value="-1"/>';
                if ($qsearchview and ($qsearchview != "all")) {
                        $out_scr_btns .= '<input type="hidden" name="submode" value="FGROUP"/>';
                        $out_scr_btns .= '<input type="hidden" name="fgroup" value="' . $qsearchview . '"/>';
                }
                $out_scr_btns .= '<input type="hidden" name="limit" value="' . $data_count . '"/>';
                $out_scr_btns .= '<input type="hidden" name="ids" value="' . $ids . '"/>';
                $out_scr_btns .= '</form>';
                $out_scr_btns .= '</div>';
                $btn_num++;
        }


        if ($btns_display["ddb"]) {
                $out_scr_btns .= '<div class="btn-qsearch btn-centered-' . $btns_total . '-btn-' . $btn_num . '" style="">';
                $out_scr_btns .= '<form name="ddbForm" id="ddbForm" method="post" action="' . "main.php" . '">';
                $out_scr_btns .= '<input type="hidden" name="Main_Page" value="afw_mode_ddb.php"/>';
                $out_scr_btns .= '<input type="hidden" name="cl" value="' . $cl . '"/>';
                $out_scr_btns .= '<input type="hidden" name="currmod" value="' . $currmod . '"/>';
                $ddb_btn = $myClassInstance->translate('DDB-BTN', $lang, true);
                $out_scr_btns .= '<input type="submit" class="longbtn redbtn submit-btn fright" name="submit"  id="submit-form-ddb" value="' . $ddb_btn . '" />';
                $out_scr_btns .= '<input type="hidden" name="ids" value="' . $ids . '"/>';
                $out_scr_btns .= '</form>';
                $out_scr_btns .= '</div>';
                $btn_num++;
        }





        $out_scr_btns .= '</div>';
        $out_scr_btns .= '<br><br>';
}

if ($qsearch_page_title) {
        $page_title = $qsearch_page_title;
        $page_sub_title = null;
        $page_action_description = null;
        $execute_btn = 'SUBMIT-SEARCH';
} elseif ($action == "retrieve") {
        $page_title = $myClassInstance->translate('QSEARCH', $lang, true) . " " . $single_obj_name;
        $page_sub_title = null;
        $page_action_description = null;
        $execute_btn = 'SUBMIT-SEARCH';
} else {

        $methodAction = "${action}RetrieveAction";
        if ($action_params) $actionParamsArr = explode(",", $action_params);
        else $actionParamsArr = array();
        $actionParamsTranslator = implode(".", $actionParamsArr);


        $methodTranslated = $myClassInstance->translate('action.' . $action . '.' . $actionParamsTranslator, $lang);
        list($success, $page_action_description) = $myClassInstance->$methodAction($lang, $actionParamsArr, $only_get_description = true);

        $page_sub_title = $myClassInstance->translate('action.' . $action . '.' . $action_params, $lang);
        $page_title = $myClassInstance->translate($action . '.action.on', $lang) . " " . $plural_obj_name_short;
        $execute_btn = 'EXECUTE';
}


AfwMainPage::addOutput("<div id='page-content-wrapper' class='qsearch_page'>
                <div class='row row-filter-$cl_short'>
                        <div class='qfilter col-sm-10 col-md-10 pb10'><h1>$page_title</h1>");
if ($page_sub_title) {
        AfwMainPage::addOutput("<h2>$page_sub_title</h2>");
        AfwMainPage::addOutput("<h3>$page_action_description</h3>");
}

$list_of_ret_cols_all = array();
$list_of_ret_cols_default = array();

$class_db_structure = $myClassInstance->getMyDbStructure();

foreach ($class_db_structure as $nom_col => $desc) {
        if (AfwPrevilegeHelper::keyIsToDisplayForUser($myClassInstance, $nom_col, $objme)) {
                if (!$desc["NO-RETRIEVE"]) {
                        $list_of_ret_cols_all[$nom_col] = $myClassInstance->translate($nom_col, $lang);
                }

                if ($desc["RETRIEVE"]) {
                        $list_of_ret_cols_default[] =  $nom_col;
                }
        }
}

if (!$_POST["ms_ret_cols"]) $_POST["ms_ret_cols"] = $list_of_ret_cols_default;

ob_start();
AfwQsearchMotor::select(
        $list_of_ret_cols_all,
        ((isset($_POST["ms_ret_cols"])) ? $_POST["ms_ret_cols"] : array()),
        array(
                "class" => "search_comm_select $class_inputSelect_multi_big",
                "name"  => "ms_ret_cols[]",
                "size"  => 5,
                "multi" => true
        ),
        "asc",
        false
);

$input_select_list_of_ret_cols  = ob_get_clean();

// AfwMainPage::addOutput( '<div align="center" class="aaa" style="width:81%;">';
if (!$myClassInstance->isLourde()) {
        $aclourde = '';
} else {
        $aclourde = 'class="form_lourde"';
}
AfwMainPage::addOutput('<form name="searchForm" id="searchForm" ' . $aclourde . ' method="post" action="' . "main.php" . '">');
$cl_short = strtolower(substr($myClassInstance->getMyClass(), 0, 10));

AfwMainPage::addOutput('<div class="row row-' . $cl_short . '">');

if ($formColumns) {
        $myClassInstance->formColumns = $formColumns;
}

if ($requiredColumns) {
        $myClassInstance->requiredColumns = $requiredColumns;
}

if ($readOnlyColumns) {
        $myClassInstance->readOnlyColumns = $readOnlyColumns;
}

if ($specialStructure) {
        $myClassInstance->specialStructure = $specialStructure;
}

if ($instanceOptions) {
        foreach($instanceOptions as $instanceOption => $instanceOptionValue)
        $myClassInstance->$instanceOption = $instanceOptionValue;
}



AfwMainPage::addOutput(AfwShowHelper::showObject($myClassInstance, "HTML", "afw_template_default_qsearch.php"));





if ($action == "retrieve") {
        if ($qsearchview == "all") $fgroup_all_selected = "selected";
        else $fgroup_all_selected = "";

        if (!isset($disable_select_view_in_qsearch_mode[$cl])) {
                $disable_select_view_in_qsearch_mode[$cl] = AfwSession::config("disable_select_view_in_qsearch_mode_for_$cl", false);
        }

        if (!$disable_select_view_in_qsearch_mode[$cl]) {
                $all_fields = AFWObject::gtr("all fields", $lang);
                $select_view = "<div class='qsearchview_select'><select id='qsearchview' name='qsearchview' class='form-control $lang_input'>
                  <option value='all' $fgroup_all_selected >$all_fields</option>
                ";
                $qsrch_fgroups = $myClassInstance->getFieldGroupArr($lang);
                $size_qsearch_text = ${"size_qsearch_" . $myClassInstance};
                if (!$size_qsearch_text) $size_qsearch_text = 3;
                foreach ($qsrch_fgroups as $fgroupcode => $fgroupname) {
                        if ($qsearchview == $fgroupcode) $fgroup_selected = "selected";
                        else $fgroup_selected = "";
                        $select_view .= "<option value='$fgroupcode' $fgroup_selected> $fgroupname</option>";
                }
                $select_view .= "</select></div>";
                $what_to_see = $myClassInstance->translate('WHAT-TO-SEE', $lang, true);
                AfwMainPage::addOutput('<div class="col-md-' . $size_qsearch_text . '">                
                        <div class="form-group">                        
                                <label>' . $what_to_see . '</label>                        		
                                ' . $select_view . '		                
                        </div>        
                </div>');
        } else {
                AfwMainPage::addOutput("<input type='hidden' id='qsearchview' name='qsearchview' value='all' />");
        }
} elseif ($action != "retrieve-simple") {
        if ($action_params) $actionParamsArr = explode(",", $action_params);
        else $actionParamsArr = array();
        $actionParamsTranslator = implode(".", $actionParamsArr);


        $methodTranslated = $myClassInstance->translate('action.' . $action . '.' . $actionParamsTranslator, $lang);

        if (!$qsearchview) $fgroup_0_selected = "selected";
        else $fgroup_0_selected = "";


        if ($qsearchview == "exec") $fgroup_exec_selected = "selected";
        else $fgroup_exec_selected = "";

        $select_view = "<div class='qsearchview_select'>
                <select id='qsearchview' name='qsearchview' class='form-control $lang_input'>
                  <option value='' $fgroup_0_selected>فقط اظهار القائمة المعنية وعدد عناصرها </option>
                  <option value='exec' $fgroup_exec_selected>تنفيذ $methodTranslated</option>
                </select></div>";
        AfwMainPage::addOutput('<div class="col-md-' . $size_qsearch_text . '">                
                <div class="form-group">                        
                        <label>ماذا تريد أن تفعل ؟</label>                        		
                        ' . $select_view . '		                
                </div>        
        </div>');
}




AfwMainPage::addOutput('</div>');
AfwMainPage::addOutput('<input type="hidden" name="datatable_on"  value="1"/>');
//AfwMainPage::addOutput( '<input type="hidden" name="file_obj"  value="'.$file_obj.'"/>');
AfwMainPage::addOutput('<input type="hidden" name="cl" value="' . $cl . '"/>');
AfwMainPage::addOutput('<input type="hidden" name="currmod" value="' . $currmod . '"/>');
AfwMainPage::addOutput('<input type="hidden" name="action" value="' . $action . '"/>');
AfwMainPage::addOutput('<input type="hidden" name="action_params" value="' . $action_params . '"/>');
AfwMainPage::addOutput('<input type="hidden" name="r" value="' . $r . '"/>');
AfwMainPage::addOutput('<input type="hidden" name="option" value="' . $option . '"/>');
AfwMainPage::addOutput('<input type="hidden" name="limite"    value="0"/>');
if (!$current_page) $current_page = "afw_mode_qsearch.php";
AfwMainPage::addOutput('<input type="hidden" id="Main_Page" name="Main_Page" value="' . $current_page . '"/>');
/*
AfwMainPage::addOutput( '<script type="text/javascript">
    function avancedSubmitToggle()
    {
        if($("#Main_Page").val()!="afw_mode_search.php") 
        {
                $("#Main_Page").val("afw_mode_search.php");
                $("#submit_advanced").removeClass("togglebtn");
                $("#submit_advanced").addClass("toggledbtn");
                $("#submit_advanced").val("الانتقال إلى البحث المتقدم");
                
        }        
        else 
        {
                $("#Main_Page").val("afw_mode_qsearch.php");
                $("#submit_advanced").addClass("togglebtn");
                $("#submit_advanced").removeClass("toggledbtn");
                $("#submit_advanced").val("إستعلام فقط");
        }
        
        // alert($("#Main_Page").val());
    }
</script>';
*/

AfwMainPage::addOutput('<script type="text/javascript">
        $(document).ready(function() {       
                $("#qsearch-submit-form").click(function(){
                        $(".alert-dismissable").fadeOut().remove();
                        $("#search_result_div").html(\'<div class="footer1 hzm-relative-loader-div" id="mySQLloader"><div class="relative hzm-loading-div" id="myloading">
                        ' . $please_wait_loading . '
                        </div></div>\');
                });
        });
    
</script>');

AfwMainPage::addOutput("<div class='btn-group' role='group' aria-label='...'>
                <table>
                <tr>
                        <td width='15px'>&nbsp;</td>
                        <td>
                             <input id='qsearch-submit-form' type='submit' name='submit' class='simple-btn smallbtn fright' value='" . $myClassInstance->translate($execute_btn, $lang, true) . "'>
                        </td>
                        <td width='15px'>&nbsp;</td>
                </form>
                
");

// die("DBG-qsearch form ready");

//  مؤقتا البحث المتقدم لا يسمح به إلا للادارة
// @todo : is not working well so disabled
if (false and $objme and $objme->isAdmin()) {
        AfwMainPage::addOutput('<td><form name="adv_searchForm" id="adv_searchForm" method="post" action="' . "main.php" . '">');
        AfwMainPage::addOutput('<input type="hidden" name="cl" value="' . $cl . '"/>');
        AfwMainPage::addOutput('<input type="hidden" name="currmod" value="' . $currmod . '"/>');
        AfwMainPage::addOutput('<input type="hidden" name="get_cache"    value="1"/>');
        AfwMainPage::addOutput('<input type="hidden" id="Main_Page" name="Main_Page" value="afw_mode_search.php"/>');
        AfwMainPage::addOutput("<input id='submit-form' type='submit' name='submit' class='greenbtn smallbtn fright' value='" . $myClassInstance->translate('SUBMIT-SEARCH-ADVANCED', $lang, true) . "'>");
        AfwMainPage::addOutput('</form></td>');
} else {
        AfwMainPage::addOutput('<td>&nbsp;</td>');
}
AfwMainPage::addOutput('<td width="15px">&nbsp;</td>');
if ($datatable_on and ($action == "retrieve")) {
        AfwMainPage::addOutput('   <td>');
        AfwMainPage::addOutput("<a href=\"main.php?Main_Page=afw_mode_qsearch.php&cl=$cl&currmod=$currmod&resetcrit=1\"><div id='reset-form' type='submit' name='submit-reset-form' class='yellbtn smallbtn fright'>" . $myClassInstance->translate('RESET-CRITEREA', $lang, true) . "</div></a>");
        AfwMainPage::addOutput('   </td>');
} else {
        AfwMainPage::addOutput('<td>&nbsp;</td>');
}
AfwMainPage::addOutput("   <td width='15px'>&nbsp;</td>
                </tr></table>
        </div>");
AfwMainPage::addOutput("</center>");
AfwMainPage::addOutput('');



AfwMainPage::addOutput("</div>
       </div>
</div>");
AfwMainPage::addOutput("<div id=\"search_result_div\">");
if ($datatable_on) {

        AfwMainPage::addOutput($search_result_html);
        AfwMainPage::addOutput($out_scr_btns);
}
AfwMainPage::addOutput("</div>");
// AfwMainPage::addOutput( '</div>'); 