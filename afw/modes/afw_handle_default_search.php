<?php
// die("DBG-mode handle search");
require_once(dirname(__FILE__) . "/../../../config/global_config.php");


$themeArr = AfwThemeHelper::loadTheme("handle-qsearch");
foreach ($themeArr as $theme => $themeValue) {
        $$theme = $themeValue;
}
$images = AfwThemeHelper::loadTheme();

if (!$objme) $objme = AfwSession::getUserConnected();
$me =  $objme->id;

$MAX_ROW_DEFAULT = AfwSession::config("MAX_ROW", 500);
$MAX_ROW = AfwSession::config("MAX_ROW-$cl", $MAX_ROW_DEFAULT);
if (!$objme->isAdmin()) $MAX_ROW = AfwSession::config("MAX_ROW-$cl-not-admin", $MAX_ROW);

if ($_REQUEST["xls_on"]) $genere_xls = true;
if ($_REQUEST["migration_on"]) $genere_migration = true;
//if($genere_migration) die("genere_migration ...");
if (!$action) $action = "retrieve";
if (!$action_params) $action_params = "";
// die("DBG-User Connected Got");
if (($objme) and ($objme->popup)) {
        $target = "target='popup'";
        $popup_t = "on";
} else {
        $target = "";
        $popup_t = "";
}

$cols_spec_retrieve = array();

$mode_ret = "search";

if ($qsearchview and ($qsearchview != "all") and ($action == "retrieve")) $mode_ret = $qsearchview;

/**
 * @var AFWObject $obj 
 */


$obj  = new $cl();

$tentative = "first";
$header_retrieve = AfwUmsPagHelper::getRetrieveHeader($obj, $mode_ret, $lang, false, $forced_retrieve_cols, $hide_retrieve_cols);


/* die(" forced_retrieve_cols = " . var_export($forced_retrieve_cols, true) .
        " hide_retrieve_cols = " . var_export($hide_retrieve_cols, true) .
        " AfwUmsPagHelper::getRetrieveHeader($obj, $mode_ret, $lang, false, forced_retrieve_cols, hide_retrieve_cols) = " . var_export($header_retrieve, true));*/

if (count($header_retrieve) == 0) {
        $tentative = "second";
        // echo "header_retrieve is empty try all :<br>";   
        $header_retrieve = AfwUmsPagHelper::getRetrieveHeader($obj, $mode_ret, $lang, true, $forced_retrieve_cols, $hide_retrieve_cols);
}

// @doc : to make the id or any other attribute is shown for qsearch with view TECH_FIELDS, just put "TECH_FIELDS-RETRIEVE" => true in structure of attribute

// if($mode_ret == "props") die("$tentative tentative for header in mode $mode_ret-retrieve = ".var_export($header_retrieve,true));

if ($genere_xls) {
        $header_excel = AfwUmsPagHelper::getExportExcelHeader($obj, $lang, $forced_retrieve_cols, $hide_retrieve_cols);
        // die("header_excel = " . var_export($header_excel, true));
}



//AFWDebugg::print_str('fin for each '.__LINE__);



if ((count($header_retrieve) > 0) and (count($cols_spec_retrieve) == 0))
        $header = &$header_retrieve;
elseif (count($cols_spec_retrieve) > 0)
        $header = &$cols_spec_retrieve;
else
        $header = array("id" => "id");

if (!$liste_obj) {
        // require_once $file_obj;
        $lang = AfwLanguageHelper::getGlobalLanguage();


        list($arr_sql_conds, $cond_phrase_arr) = AfwSearchHelper::prepareSQLWhereFromPostedFilter($obj, $arr_sql_conds, $lang);

        //die("final debugg the criteria : arr_sql_conds = ".var_export($arr_sql_conds,true)." cond_phrase_arr = ".var_export($cond_phrase_arr,true));
        if (!empty($arr_sql_conds)) {
                $sql_conds = implode(" and ", $arr_sql_conds);
                $sql_conds = trim($sql_conds);
                if (preg_match('and$', $sql_conds))
                        $sql_conds = substr($sql_conds, 0, -2);
                $obj->where($sql_conds);
                // die("please try later ... IT Team is debugging ... (2) DBG-where special sql_conds = $sql_conds arr_sql_conds = ".var_export($arr_sql_conds,true));
                $obj->select_visibilite_horizontale();
                $count_liste_obj = $obj->func("count(*)");
                $obj->where($sql_conds);
        } else {
                $obj->select_visibilite_horizontale();
                $count_liste_obj = $obj->func("count(*)");
        }

        $obj->select_visibilite_horizontale();
        if ($special_filter) $obj->$special_filter();

        // die("DBG-where select_visibilite_horizontale");

        if (($action != "retrieve")) {
                // die(" strange action=$action");
                //$actions_tpl_arr = array();
        } elseif ($count_liste_obj > $MAX_ROW) {
                AfwSession::pushWarning("$count_liste_obj " . $obj->tm("records in the result exceeds the limit allowed by data security to allow executing edit delete actions on"));
                // AfwSession::pushInformation($obj->tm("edit,delete buttons have been disabled"));                
                AfwSession::pushInformation($obj->tm("Please choose more refined criteria"));
                //$actions_tpl_arr = array();
        }
        /*
        if($count_liste_obj>$MAX_ROW)
        {
                AfwSession::pushWarning("$count_liste_obj ".$obj->tm("records in the result exceeds the limit to be loaded in this page"));
                AfwSession::pushInformation($obj->tm("edit,delete buttons have been disabled"));
                AfwSession::pushInformation($obj->tm("Please choose more refined criteria"));
                $actions_tpl_arr = array();
        }*/



        //chargment des objets

        if (!isset($limite)) {
                $limite = 0;
        }
        if (!isset($sql_order_by)) $sql_order_by = "";
        if ((!$genere_xls) and (!$genere_migration)) {
                $the_limit = $limite . ", " . $MAX_ROW;
                // $liste_obj       = $obj->loadMany($limite . ", " . $MAX_ROW, $sql_order_by);
                // die("DBG-loadManyEager normal limited load : liste_obj = obj->loadManyEager($limite, $MAX_ROW, $sql_order_by) = ".var_export($liste_obj,true));
        } else {
                $the_limit = "";
                // $liste_obj       = $obj->loadMany("", $sql_order_by);
                // die("DBG-loadManyEager excel illimited load");    
        }

        $liste_obj       = $obj->loadManyEager($the_limit, $sql_order_by);
        //$dataRetrive = AfwLoadHelper::retrieveMany($obj, $the_limit, $sql_order_by);
}
$qs_options = [];
if (method_exists($cl, "getQsearchDefaultOptions")) {
        $qs_options = $cl::getQsearchDefaultOptions();
}
if (!$qs_options["records-in-page"]) $qs_options["records-in-page"] = 25;

// we can use the checknox select mode only  if all records feet in one page
$liste_obj_count = count($liste_obj);
$records_in_page = $qs_options["records-in-page"];
if ($liste_obj_count < $records_in_page) {
        $feet_in_one_page = true;
        $feet_in_one_page_log = "";
} else {
        $feet_in_one_page = false;
}

if ($show_checkboxes and $obj) {
        if ($feet_in_one_page) {
                $header_retrieve["check-id"] = "<div id='check-all' class='js-check-all'><!-- $liste_obj_count < $records_in_page --></div>";
        } else {
                AfwSession::pushWarning($obj->tm("لا يمكن تفعيل الخيارات المتعددة لتنفيذ بعض الاجراءات الجماعية بسبب تجاوز عدد السجلات العدد الأقصى", $lang));
        }
}


AfwSession::log("End of Data retrieve in afw_handle_default_search");

// if search result is big data we should not keep heavy calculated fields like shortcuts
// in retrieved columns
//$liste_count = count($dataRetrive);
$liste_count = count($liste_obj);
$too_much_records = AfwSession::config("too_much_records", 500);
if ($liste_count > $too_much_records) {
        foreach ($header as $col => $titre) {
                if ($obj->seemsCalculatedField($col)) {
                        unset($header[$col]);
                        AfwSession::pushWarning($obj->tm("تم حجب العمود [$titre] لأجل تسريع الصفحة التي تحتوي على سجلات كثيرة جدا", $lang));
                }
        }
}

// espion-time-0002 : pour afficher le temps d'exec de cette requette non-voulu a l origine 
// mais pour localiser (espioner) la lenteur est avant ou apres
// AfwSession::sqlLog("espion-time-0002", "hzm");

// $ddtt_s = date("H:i:s.u");
// list($data, $isAvail) = AfwLoadHelper::formatRetrievedDataForRetrieveMode($dataRetrive, $header, $obj->fld_ACTIVE(), $lang);
AfwSession::log("Before execute LoadHelper::getRetrieveDataFromObjectList in afw_handle_default_search");
list($data, $isAvail) = AfwLoadHelper::getRetrieveDataFromObjectList($liste_obj, $header, $lang, $newline = "\n<br>", true);
AfwSession::log("After execute LoadHelper::getRetrieveDataFromObjectList in afw_handle_default_search");
// die("data = ".var_export($data,true)." when liste_obj= ".var_export($liste_obj,true));
// $ddtt_e = date("H:i:s.u");
// die("ddtt_e=$ddtt_e ddtt_s=$ddtt_s");
// $actions_tpl_matrix = AfwUmsPagHelper::getActionsMatrixFromData($dataRetrive, $obj->getMyClass(), $obj->getMyModule(), $obj->fld_ACTIVE());
/*
$getActionsMatrix_time_start = hrtime(true);
*/

/*
$getActionsMatrix_time_end = hrtime(true); // nano sec
// time in milli second
$getActionsMatrix_time_t = round(($getActionsMatrix_time_end - $getActionsMatrix_time_start) / 1000000);
die("getActionsMatrix_time_t=$getActionsMatrix_time_t");
*/
if ($genere_xls) {
        // die("header_excel =" . var_export($header_excel, true));
        AfwSession::log("Before execute getRetrieveDataFromObjectList for excel generation in afw_handle_default_search");
        list($data_excel, $isAvail_excel) = AfwLoadHelper::getRetrieveDataFromObjectList($liste_obj, $header_excel, $lang, $newline = "\n", false, true);
        AfwSession::log("After execute getRetrieveDataFromObjectList for excel generation in afw_handle_default_search");
        // die("header_excel =" . var_export($header_excel, true) . " data_excel = " . var_export($data_excel, true));
}



if ($action and ($action != "retrieve") and ($qsearchview == "exec")) {
        AfwSession::log("Before execute action $action in afw_handle_default_search");
        //$actions_tpl_arr = array();
        $methodAction = $action . "RetrieveAction";
        if ($action_params) $actionParamsArr = explode(",", $action_params);
        else $actionParamsArr = array();
        $actionParamsTranslator = implode(".", $actionParamsArr);


        $methodTranslated = $obj->translate('action.' . $action . '.' . $actionParamsTranslator, $lang);
        $nb_errs_action = 0;
        $nb_success_action = 0;
        $log_html = array();
        $log_success_html = array();
        foreach ($liste_obj as $item_obj) {
                list($success, $message) = $item_obj->$methodAction($lang, $actionParamsArr);

                if (!$success) {
                        $nb_errs_action++;
                        if ($nb_errs_action < 20) $log_html[] = $message;
                } else {
                        $nb_success_action++;
                        if ($nb_success_action < 20) $log_success_html[] = $message;
                }
        }

        if (count($liste_obj) == 0) {
                AfwSession::pushWarning($obj->tm("no action to do"));
        } else {
                $single_obj_name =  $obj->transClassSingle($lang);
                if ($nb_errs_action) {
                        AfwSession::pushWarning("$nb_errs_action " . $obj->tm("errors when executing action") . " [" . $methodTranslated . "] : <br>\n" . implode("<br>\n", $log_html));
                }

                if ($nb_success_action) {
                        $warn = $obj->tm("the following action") . " : " . $methodTranslated;
                        $warn .= " " . $obj->tm("has succeeded on") . " $nb_success_action " . $single_obj_name . " >> <br>\n" . implode("<br>\n", $log_success_html);
                        AfwSession::pushInformation($warn);
                }
        }

        AfwSession::log("After execute action $action in afw_handle_default_search");
}

if ($genere_migration) {
        // if($genere_migration) die("genere_migration ... for ".var_export($liste_obj,true));
        $phpCode = Migration::genereUpdateDataMigration($liste_obj);
        die("<textarea class='technical php'>$phpCode</textarea>");
}


//AFWDebugg::log("**************************************\n");
//AFWDebugg::log($cols_retrieve,true);

//AFWDebugg::print_str('foreach  '.__LINE__);

//AFWDebugg::print_str('foreach  '.__LINE__);
ob_start();

if (!$result_page_title) {
        $tr_ = $obj->transClassPlural($lang, false, $maksour = true);
        if ($action == "retrieve")
                $result_page_title = $obj->translate('SEARCH_RESULT', $lang, true) . " " . $tr_;
        elseif ($action == "retrieve-simple")
                $result_page_title = $obj->translate('SEARCH_RESULT_SIMPLE', $lang, true);
        elseif ($qsearchview == "exec")
                $result_page_title = $obj->translate($obj->getTableName(), $lang) . " " . $obj->translate('who.received.action.' . $action, $lang);
        else
                $result_page_title = $obj->translate($obj->getTableName(), $lang) . " " . $obj->translate('who.will.receive.action.' . $action, $lang);
}

list($special_css_tab_search_result,) = explode(".", $current_page);

if (true) {
?>

        <table id="search_result_table" width="<?php echo $pct_tab_search_result ?>" class="search_result_table <?php echo $special_css_tab_search_result ?>">
                <tr>
                        <td>
                                <table width="100%">
                                        <tr>
                                                <td>
                                                        <h5 class='bluetitle search'><i></i><?php echo $result_page_title ?></h5>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td id='bloc_result'>

                                                        <?php
                                                        if (!isset($addHeader)) $addHeader = false;
                                                        if (!isset($takeViewIcon)) $takeViewIcon = $obj->takeViewIcon('search');
                                                        if (!isset($takeEditAction)) $takeEditAction = $obj->takeEditAction('search');
                                                        if (!isset($takeDeleteAction)) $takeDeleteAction = $obj->takeDeleteAction('search');
                                                        if (!isset($takeAuditAction)) $takeAuditAction = $obj->takeAuditAction('search');
                                                        // die("can_action_arr = ".var_export($can_action_arr,true)); 
                                                        $result = AfwRetrieveHelper::showDataRetrieve(
                                                                $obj,
                                                                $data,
                                                                $header,
                                                                $class_db_structure,
                                                                $liste_obj,
                                                                $isAvail,
                                                                $cl_tr,
                                                                $class_td1,
                                                                $class_td2,
                                                                $class_td_off,
                                                                $cl,
                                                                $currmod,
                                                                $popup_t,
                                                                $target,
                                                                $images,
                                                                $objme,
                                                                $fixms,
                                                                $lang,
                                                                $addHeader,
                                                                $takeViewIcon,
                                                                $takeEditAction,
                                                                $takeDeleteAction,
                                                                $takeAuditAction
                                                        );

                                                        $datatable_header = $result["datatable_header"];
                                                        ?>

                                                        <table id="dtbl_<?php echo $cl ?>" class="display" cellpadding="4" cellspacing="3" width="100%">
                                                                <thead>
                                                                        <tr>
                                                                                <?php echo $datatable_header ?>
                                                                        </tr>
                                                                </thead>

                                                                <tbody>
                                                                        <?php

                                                                        echo "<!-- start of search result table body html -->\n";
                                                                        echo $result["html"];
                                                                        echo "\n<!-- end of search result table body html -->\n";
                                                                        $ids = $result["ids"];
                                                                        $ids_count = $result["ids_count"];

                                                                        $data_count = $result["data_count"];
                                                                        $fixmlist = $result["fixmlist"];
                                                                        ?>
                                                                </tbody>
                                                                <?php
                                                                if (count($data) > 20) {
                                                                ?>
                                                                        <tfoot>
                                                                                <tr>
                                                                                        <?php echo $datatable_header ?>
                                                                                </tr>
                                                                        </tfoot>
                                                                <?php
                                                                }
                                                                ?>
                                                        </table>
                                                        <script>
                                                                $(document).ready(function() {
                                                                        $('#dtbl_<?php echo $cl ?>').DataTable({
                                                                                pagingType: "full_numbers",
                                                                                pageLength: -1,
                                                                                lengthMenu: [
                                                                                        [10, 25, 50, -1],
                                                                                        [10, 25, 50, "All"]
                                                                                ]
                                                                        });
                                                                        /*
                                                                        new DataTable('#dtbl_<?php echo $cl ?>', {
                                                                                columnControl: {
                                                                                        target: 1,
                                                                                        content: ['search']
                                                                                }
                                                                        });
                                                                        */


                                                                });
                                                        </script>
                                                        <br>
                                                </td>
                                        </tr>
                                </table>
                        </td>
                </tr>
        </table>
        <input type="hidden" id="all_ids" name="all_ids" value="<?php echo $ids ?>" />
<?php
}
$link = "";
if ($genere_xls) {
        $link = UfwExcel::genereExcel($header_excel, $data_excel, $xls_page_title = 'نتائج البحث', "search-result-$cl-$me-" . date("YmdHis"), true, "purelink");
}


$search_result_html = ob_get_clean();

return ['excel_link' => $link, 'search_result_html' => $search_result_html];
