<?php
// die("DBG-mode handle search");
require_once(dirname(__FILE__) . "/../../../config/global_config.php");
require_once(dirname(__FILE__) . '/../modes/afw_rights.php');

$themeArr = AfwThemeHelper::loadTheme("handle-qsearch");
foreach ($themeArr as $theme => $themeValue) {
        $$theme = $themeValue;
}
$images = AfwThemeHelper::loadTheme();

if (!$objme) $objme = AfwSession::getUserConnected();

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


// die("AfwUmsPagHelper::getRetrieveHeader($obj, $mode_ret, $lang) = ".var_export($header_retrieve,true));

if (count($header_retrieve) == 0) {
        $tentative = "second";
        // echo "header_retrieve is empty try all :<br>";   
        $header_retrieve = AfwUmsPagHelper::getRetrieveHeader($obj, $mode_ret, $lang, true, $forced_retrieve_cols, $hide_retrieve_cols);
}

// @doc : to make the id or any other attribute is shown for qsearch with view TECH_FIELDS, just put "TECH_FIELDS-RETRIEVE" => true in structure of attribute

// if($mode_ret == "props") die("$tentative tentative for header in mode $mode_ret-retrieve = ".var_export($header_retrieve,true));

if ($genere_xls) {
        $header_excel = AfwUmsPagHelper::getExportExcelHeader($obj, $lang);
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
        if (!$lang) $lang = 'ar';


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
                $obj->select_visibilite_horizontale();
        } else {
                $obj->select_visibilite_horizontale();
                $count_liste_obj = $obj->func("count(*)");
                $obj->select_visibilite_horizontale();
        }

        // die("DBG-where select_visibilite_horizontale");

        if (($action != "retrieve")) {
                // die(" strange action=$action");
                $actions_tpl_arr = array();
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
                        AfwSession::pushWarning("تم حجب العمود [$titre] لأجل تسريع الصفحة التي تحتوي على سجلات كثيرة جدا");
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
AfwSession::log("Before execute UmsPagHelper::getActionsMatrix in afw_handle_default_search");
$actions_tpl_matrix = AfwUmsPagHelper::getActionsMatrix($liste_obj);
AfwSession::log("After execute UmsPagHelper::getActionsMatrix in afw_handle_default_search");

/*
$getActionsMatrix_time_end = hrtime(true); // nano sec
// time in milli second
$getActionsMatrix_time_t = round(($getActionsMatrix_time_end - $getActionsMatrix_time_start) / 1000000);
die("getActionsMatrix_time_t=$getActionsMatrix_time_t");
*/
if ($genere_xls) {
        AfwSession::log("Before execute getRetrieveDataFromObjectList for excel generation in afw_handle_default_search");
        list($data_excel, $isAvail_excel) = AfwLoadHelper::getRetrieveDataFromObjectList($liste_obj, $header_excel, $lang, $newline = "\n", false, true);
        AfwSession::log("After execute getRetrieveDataFromObjectList for excel generation in afw_handle_default_search");
        //die("header_excel =".var_export($header_excel,true)." data_excel = ".var_export($data_excel,true));
}



if ($action and ($action != "retrieve") and ($qsearchview == "exec")) {
        AfwSession::log("Before execute action $action in afw_handle_default_search");
        $actions_tpl_arr = array();
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



if (true) {
?>

        <table id="search_result_table" width="<?= $pct_tab_search_result ?>" class="search_result_table">
                <tr>
                        <td>
                                <table width="100%">
                                        <tr>
                                                <td>
                                                        <h5 class='bluetitle search'><i></i><?= $result_page_title ?></h5>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td id='bloc_result'>

                                                        <?
                                                        AfwSession::log("Before prepare of header and can_action array matrix in afw_handle_default_search");
                                                        if (count($header) != 0) {
                                                                $datatable_header = "";
                                                                /*
                                if(($cl=="Module"))
                                {
                                        $message .= "<br>header = ".var_export($header,true);
                                        throw new AfwRun timeException($message);
                                }*/

                                                                foreach ($header as $nom_col => $tr_col) {
                                                                        // if(!is_array($desc)) throw new AfwRun timeException("desc is not an array : ".var_export($desc,true));
                                                                        $nom_col_short = "$nom_col.short";
                                                                        if (!$tr_col or ($nom_col == $tr_col) or ($nom_col_short == $tr_col)) {
                                                                                $col_trad = $obj->getAttributeLabel($nom_col, $lang, true);
                                                                                /*                                                                                
                                                                                $trad_col_short  = $obj->translate($nom_col_short, $lang);
                                                                                if ($trad_col_short == $nom_col_short) $col_trad = $obj->translate($nom_col, $lang);
                                                                                else $col_trad = $trad_col_short;*/
                                                                        } else $col_trad = $tr_col;

                                                                        $desc = $class_db_structure[$nom_col];
                                                                        $show_unit_in_header = AfwSession::config("show_unit_in_header", false);
                                                                        $show_unit_here_in_header = AfwSession::config("show_unit_in_header_for_" . $cl, $show_unit_in_header);
                                                                        if (is_array($desc) and $show_unit_here_in_header) {
                                                                                $unit  = $desc["UNIT"];
                                                                                $hide_unit  = $desc["RETREIVE_HIDE_UNIT"];
                                                                        } else {
                                                                                $unit = "";
                                                                                $hide_unit = "";
                                                                        }


                                                                        $importance = AfwHtmlHelper::importanceCss($obj, $nom_col, $desc);

                                                                        if ($unit and (!$hide_unit)) $col_trad .= " ($unit)";
                                                                        $datatable_header .= "<th class='col-importance-$importance srch-result-col-$nom_col'>" . $col_trad . "</th>";
                                                                }

                                                                // echo "actions_tpl_arr = ".var_export($actions_tpl_arr,true);

                                                                foreach ($actions_tpl_arr as $action_item => $action_item_props) {
                                                                        $frameworkAction = $action_item_props["framework_action"];
                                                                        $importance = $action_item_props["importance"];
                                                                        if (!$importance) {
                                                                                if ($frameworkAction == "display") $importance = "small";
                                                                                if ($frameworkAction == "delete") $importance = "medium";
                                                                                if ($frameworkAction == "edit") $importance = "high";
                                                                        }
                                                                        if (!$importance) $importance = "high";

                                                                        $bf_code = $action_item_props["bf_code"];
                                                                        $bf_system = $action_item_props["bf_system"];
                                                                        $datatable_header .= "<th width='1%' class='col-importance-$importance bfc$bf_code fwa$frameworkAction' id='fwa-$frameworkAction'>" . $obj->translate($action_item, $lang) . "</th>";
                                                                        if (!$frameworkAction) $frameworkAction = $action_item;

                                                                        if ($bf_code) {
                                                                                $can_action_arr[$action_item] = ($objme and $objme->iCanDoBFCode($bf_system, $bf_code));
                                                                                $can_case = "objme->iCanDoBFCode($bf_system, $bf_code)";
                                                                        } else {
                                                                                $can_action_arr[$action_item] = ($objme and $objme->iCanDoOperationOnObjClass($obj, $frameworkAction));
                                                                                $can_case = "objme->iCanDoOperationOnObjClass(obj, $frameworkAction)";
                                                                        }
                                                                        if ($objme and (!$can_action_arr[$action_item])) $cant_do_action_log_arr[$action_item] = $objme->getICantDoReason();
                                                                        if (!$cant_do_action_log_arr[$action_item]) $cant_do_action_log_arr[$action_item] = "but reason not explained";
                                                                        $cant_do_action_log_arr[$action_item] .= " ($can_case)";
                                                                }
                                                        }

                                                        AfwSession::log("After prepare of header and can_action array matrix in afw_handle_default_search");

                                                        // die("can_action_arr = ".var_export($can_action_arr,true)); 
                                                        ?>

                                                        <table id="example" class="display" cellpadding="4" cellspacing="3" width="100%">
                                                                <thead>
                                                                        <tr>
                                                                                <?= $datatable_header ?>
                                                                        </tr>
                                                                </thead>
                                                                <?php
                                                                if (count($data) > 50) {
                                                                ?>
                                                                        <tfoot>
                                                                                <tr>
                                                                                        <?= $datatable_header ?>
                                                                                </tr>
                                                                        </tfoot>
                                                                <?
                                                                }
                                                                ?>
                                                                <tbody>
                                                                        <?
                                                                        AfwSession::log("Before show data retrieve in afw_handle_default_search");
                                                                        $ids = "";
                                                                        $ids_count = 0;
                                                                        $maxRecordsUmsCheck = $obj->maxRecordsUmsCheck();
                                                                        $repeat_retrieve_header = $obj->repeatRetrieveHeader();
                                                                        $umsCheckDisabledInRetrieveMode = $obj->umsCheckDisabledInRetrieveMode();
                                                                        if ($maxRecordsUmsCheck > 100) $maxRecordsUmsCheck = 100;
                                                                        foreach ($data as $id => $tuple) {
                                                                                //if($ids_count<50)
                                                                                //{
                                                                                if ($ids) $ids .= ",";
                                                                                $ids .= $id;
                                                                                $ids_count++;
                                                                                if ($repeat_retrieve_header and (($ids_count % $repeat_retrieve_header) == 0)) {
                                                                        ?>
                                                                                        <thead>
                                                                                                <tr>
                                                                                                        <?= $datatable_header ?>
                                                                                                </tr>
                                                                                        </thead>
                                                                                <?php
                                                                                }
                                                                                //}
                                                                                if ($cl_tr == $class_td2) $cl_tr = $class_td1;
                                                                                else $cl_tr = $class_td2;
                                                                                if (!$isAvail[$id]) $cl_tr = $class_td_off;

                                                                                $lbl = addslashes($tuple["display_object"]);
                                                                                ?>
                                                                                <tr>
                                                                                        <?
                                                                                        foreach ($header as $nom_col => $tr_col) {
                                                                                                $desc = $class_db_structure[$nom_col];
                                                                                                $importance = $desc["IMPORTANT"];
                                                                                                $text_direction = $desc["DIRECTION"];
                                                                                                if (!$text_direction) {
                                                                                                        if ($desc["UTF8"]) $text_direction = "rtl";
                                                                                                        else $text_direction = "ltr";
                                                                                                }
                                                                                                if ($importance == "IN") $importance = "high";
                                                                                                //if($importance == "IN") $importance = "high";
                                                                                                if (!$importance) $importance = "high";

                                                                                                echo "<td class='col-importance-$importance text_$text_direction srch-result-col-$nom_col'>" . $tuple[$nom_col] . "</td>";
                                                                                        }

                                                                                        // die(var_export($actions_tpl_arr,true));
                                                                                        if ($ids_count < 3000) {

                                                                                                foreach ($actions_tpl_arr as $action_item => $action_item_props) {
                                                                                                        if ($actions_tpl_matrix[$id][$action_item]) $action_item_props = $actions_tpl_matrix[$id][$action_item];

                                                                                                        $frameworkAction = $action_item_props["framework_action"];
                                                                                                        $importance = $action_item_props["importance"];
                                                                                                        if (!$importance) {
                                                                                                                if ($frameworkAction == "display") $importance = "small";
                                                                                                                if ($frameworkAction == "delete") $importance = "medium";
                                                                                                                if ($frameworkAction == "edit") $importance = "high";
                                                                                                        }
                                                                                                        if (!$importance) $importance = "high";

                                                                                                        $bf_code = $action_item_props["bf_code"];
                                                                                                        $bf_system = $action_item_props["bf_system"];
                                                                                                        if (!$frameworkAction) $frameworkAction = $action_item;

                                                                                                        $page = $action_item_props["page"];
                                                                                                        if ($page) {
                                                                                                                $page_params = $action_item_props["params"];
                                                                                                        } else {
                                                                                                                $link = $action_item_props["link"];
                                                                                                                $link = str_replace("[id]", $id, $link);
                                                                                                                $link = str_replace("[popup_t]", $popup_t, $link);
                                                                                                        }


                                                                                                        if ($action_item_props["target"]) $target_action = "target='" . $action_item_props["target"] . "'";
                                                                                                        else $target_action = $target;

                                                                                                        $img = $action_item_props["img"];

                                                                                                        $ajax_class = $action_item_props["ajax_class"];

                                                                                                        $frameworkAction_tr = $liste_obj[$id]->translateOperator(strtoupper("_" . $action_item), $lang);
                                                                                                        $btnclass = $action_item_props["btnclass"];
                                                                                                        $canOnMe = false;

                                                                                                        $can = $can_action_arr[$action_item];

                                                                                                        $cant_do_action_log = "action $action_item not allowed ";

                                                                                                        if (!$can) {
                                                                                                                $cant_do_action_log .= $cant_do_action_log_arr[$action_item] . " ";
                                                                                                        }

                                                                                                        if (($frameworkAction == "display") and AfwFrameworkHelper::displayInEditMode($cl)) $frameworkConsideredAction = "edit";
                                                                                                        else $frameworkConsideredAction = $frameworkAction;

                                                                                                        if ($can) {

                                                                                                                if ((!$maxRecordsUmsCheck) or ($umsCheckDisabledInRetrieveMode)) {
                                                                                                                        $canOnMe = true;
                                                                                                                } elseif ($objme and ($ids_count <= $maxRecordsUmsCheck)) {
                                                                                                                        if (($frameworkConsideredAction == "edit") or ($frameworkConsideredAction == "update")) {
                                                                                                                                //die("frameworkConsideredAction=$frameworkConsideredAction");
                                                                                                                                list($canOnMe, $edit_not_allowed_reason) = $liste_obj[$id]->userCanEditMe($objme);
                                                                                                                                if (!$canOnMe) {
                                                                                                                                        if (!$edit_not_allowed_reason) $edit_not_allowed_reason = "userCanEditMe has not returned reason";
                                                                                                                                        $cant_do_action_log .= $edit_not_allowed_reason . " ";
                                                                                                                                        // die("DBG-2 202504061900 - ".$liste_obj[$id]->getDisplay("ar")." canOnMe=$canOnMe, edit_not_allowed_reason=$edit_not_allowed_reason");
                                                                                                                                } else {
                                                                                                                                        // die("DBG-1 202504061900 - ".$liste_obj[$id]->getDisplay("ar")." canOnMe=$canOnMe, edit_not_allowed_reason=$edit_not_allowed_reason");
                                                                                                                                }
                                                                                                                        } elseif (($frameworkConsideredAction == "delete")) {
                                                                                                                                //die("frameworkConsideredAction=$frameworkConsideredAction");
                                                                                                                                $canOnMe = ($liste_obj[$id]->userCanDeleteMe($objme, $notify = false) > 0);
                                                                                                                                if (!$canOnMe) $cant_do_action_log .= "see userCanDeleteMe IMP ";
                                                                                                                        } else {
                                                                                                                                $canOnMe = AfwUmsPagHelper::userCanDoOperationOnObject($liste_obj[$id], $objme, $frameworkConsideredAction);
                                                                                                                                if (!$canOnMe) $cant_do_action_log .= "see userCanDoOperationOnObject IMP ";
                                                                                                                        }
                                                                                                                } else {
                                                                                                                        $canOnMe = null;
                                                                                                                        $cant_do_action_log .= "Too much records. count=$ids_count > $maxRecordsUmsCheck ";
                                                                                                                }
                                                                                                        }
                                                                                                        if ($can and (!$canOnMe)) {
                                                                                                                if ($cant_do_action_log) $cant_do_action_log .= "\n<br>";
                                                                                                                $cant_do_action_log .= $liste_obj[$id]->user_have_access_log . " ";
                                                                                                                //die("case can and ! canOnMe exists : ".$liste_obj[$id]. " log = $cant_do_action_log");
                                                                                                        }
                                                                                                        // $canOnMe = true;
                                                                                                        // $can = true;
                                                                                                        if ($can and $canOnMe) {
                                                                                                                $accept_HimSelf = AfwFrameworkHelper::acceptHimSelf($liste_obj[$id], $frameworkAction, "retrieve");
                                                                                                                if ($accept_HimSelf) {
                                                                                                                        /* @note rafik/17/6/2021 obsolete and will fill the session of user so better to remove
                                                                                                                        if($page)
                                                                                                                        $sess_link = savePageInSession($page,$page_params);
                                                                                                                        else
                                                                                                                        $sess_link = saveLinkInSession("main.php"."?".$link);*/

                                                                                                                        if ($btnclass) {
                                                                                        ?>
                                                                                                                                <td class='btn-class col-importance-<?= $importance ?> <?= $frameworkAction ?>'><a class="btn-micro <?= $btnclass ?>" <?= $target_action ?> href="<?= "main.php" . "?" . $link ?>"><?= $frameworkAction_tr ?></a></td>
                                                                                                                                <?
                                                                                                                        } elseif ($img) {
                                                                                                                                $tooltip = "";
                                                                                                                                $icon_help = $action_item_props["help"];
                                                                                                                                if ($icon_help) $tooltip = "data-toggle='tooltip' data-placement='bottom' title='$icon_help' data-original-title=' - Tooltip on bottom 0' class='red-tooltip'";

                                                                                                                                if ($ajax_class) {
                                                                                                                                ?>
                                                                                                                                        <td class='ajax col-importance-<?= $importance ?> <?= $frameworkAction ?>'><a href="#" id="<?= $id ?>" cl="<?= $cl ?>" md="<?= $currmod ?>" lbl="<?= $lbl ?>" class="<?= $ajax_class ?>">
                                                                                                                                                        <img lbl='ajax' src="<?= $img ?>" width="24" heigth="24" <?= $tooltip ?>>
                                                                                                                                                </a>
                                                                                                                                        </td>
                                                                                                                                <?
                                                                                                                                } else {
                                                                                                                                        if ($link) $the_action_link = "main.php" . "?" . $link;
                                                                                                                                        else $the_action_link = "#";
                                                                                                                                ?>
                                                                                                                                        <td class='action-link col-importance-<?= $importance ?> <?= $frameworkAction ?>'><a <?= $target_action ?> href="<?php echo $the_action_link; ?>">
                                                                                                                                                        <img lbl='no-ajax' src="<?= $img ?>" width="24" heigth="24" <?= $tooltip ?>>
                                                                                                                                                </a>
                                                                                                                                        </td>
                                                                                                                        <?

                                                                                                                                }

                                                                                                                                // die("DBG-after ajax test\n"); 
                                                                                                                        } else echo "<td  class='col-importance-$importance $frameworkAction no-image'>no_image_for_mode_$frameworkAction action_item_props=" . var_export($action_item_props, true) . "</td>";
                                                                                                                        // die("DBG-accept_HimSelf true finished\n"); 
                                                                                                                } else {
                                                                                                                        $rejectHimSelfReason = AfwStringHelper::stripCotes(AfwFrameworkHelper::rejectHimSelfReason($liste_obj[$id], $frameworkAction));
                                                                                                                        $tooltip_text = "locked him self on $frameworkAction, the reason is : $rejectHimSelfReason";
                                                                                                                        if (($objme and $objme->isSupervisor()) or AfwSession::config("MODE_DEVELOPMENT", false)) {
                                                                                                                                // die("DBG-accept_HimSelf false => $tooltip_text\n");  
                                                                                                                                $tooltip = "data-toggle='tooltip' data-placement='bottom' title='$tooltip_text' data-original-title=' - Tooltip on bottom 1' class='red-tooltip'";
                                                                                                                        } else {
                                                                                                                                $tooltip = "> <!-- $tooltip_text --";
                                                                                                                        }

                                                                                                                        ?>
                                                                                                                        <td class='col-importance-<?= $importance ?> locked-him-self'><img src="<?= $images['locked_him_self'] ?>" width="24" heigth="24" <?= $tooltip ?>></td>
                                                                                                                <?
                                                                                                                }
                                                                                                        } elseif ($can and (!$canOnMe)) {
                                                                                                                if (($objme and $objme->isSupervisor()) or AfwSession::config("MODE_DEVELOPMENT", false)) $tooltip = "data-toggle='tooltip' data-placement='bottom' title='عندما تكون نتائج البحث كثيرة يتم ايقاف التعديلات على جزء من السجلات. قم باختيار معايير اكثر دقة للبحث' data-original-title='$action_item -> $cant_do_action_log - Tooltip on bottom 2' class='red-tooltip'";
                                                                                                                if ($canOnMe === null) {
                                                                                                                        $canCss = 'off';
                                                                                                                } else {
                                                                                                                        $canCss = 'locked_on_me';
                                                                                                                }
                                                                                                                $canImage = $images[$canCss];
                                                                                                                ?>
                                                                                                                <td class='col-importance-<?php echo $importance . " " . $canCss ?>'><img src="<?= $canImage ?>" width="24" heigth="24" <?= $tooltip ?>></td>
                                                                                                        <?
                                                                                                        } else { // means can't ($can is false)
                                                                                                                if (($objme and $objme->isSupervisor()) or AfwSession::config("MODE_DEVELOPMENT", false)) $tooltip = "data-toggle='tooltip' data-placement='bottom' title='لا يمكنك التعديل على هذا السجل راجع المشرف للتأكد من الصلاحيات وسياسة التعديل' data-original-title='You have not authorization to do $frameworkAction on this entity : [$action_item -> $cant_do_action_log] - Tooltip on bottom 3' class='red-tooltip'";
                                                                                                        ?>
                                                                                                                <td class='col-importance-<?= $importance ?> can-t-case'><img src="<?= $images['locked'] ?>" width="24" heigth="24" <?= $tooltip ?> alt="<?= "" ?>"></td>
                                                                                        <?
                                                                                                        }
                                                                                                }
                                                                                        }

                                                                                        ?>
                                                                                </tr>
                                                                        <?
                                                                        }
                                                                        $data_count = count($data);
                                                                        if (is_array($fixms)) $fixmlist = implode(",", $fixms);
                                                                        else $fixmlist = "";
                                                                        // die("DBG-final fixmlist $fixmlist\n"); 
                                                                        ?>
                                                                </tbody>
                                                        </table><br>
                                                </td>
                                        </tr>
                                </table>
                        </td>
                </tr>
        </table>

<?php
}
$link = "";
if ($genere_xls) {
        $link = AfwExcel::genereExcel($header_excel, $data_excel, $xls_page_title = 'نتائج البحث', "search-result-" . date("YmdHis"), true, "purelink");
}


$search_result_html = ob_get_clean();

return ['excel_link' => $link, 'search_result_html' => $search_result_html];
