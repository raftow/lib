<?php

class AfwRetrieveHelper
{
    /**
     * showDataRetrieve
      * show the html of data retrieve table with actions buttons
      * @param AFWObject $obj the class object of the retrieved entity, used to get labels and descriptions of attributes and also to get some configuration from it like maxRecordsUmsCheck and repeatRetrieveHeader
      * @param array $data the data to show in retrieve result, it's an array with id as key and tuple as value, tuple is an array with attribute name as key and value as value, and also must contain "display_object" key with the value to show in action buttons tooltip, and can contain "ca-col" key with the name of column used for coloring the row according to its value
      * @param array $header the header of retrieve result, it's an array with attribute name as key and column label as value, if column label is empty or same as attribute name or same as attribute name with .short suffix then the label will be translated using getAttributeLabel method of object class
      * @param array $class_db_structure the database structure of the retrieved entity class, it's an array with attribute name as key and its description as value, used to get unit of attributes to show it in header if configured in session and also to get importance of attributes to set css class for columns
      * @param array $liste_obj the list of retrieved AFWObjects with id as key and object as value, used to get actions matrix and also to check permissions on each object for each action
      * @param array $isAvail an array with id as key and boolean as value to indicate if the object is available or not, used to set css class for unavailable rows
      
      * @param string $cl_tr the css class for the first row, used to alternate row colors
      * @param string $class_td1 the css class for the first column, used to alternate column colors
      * @param string $class_td2 the css class for the second column, used to alternate column colors
      * @param string $class_td_off the css class for unavailable rows, used to set specific style for unavailable rows

      * @param string $cl the class of retrieved entity, used to check configuration for showing unit in header
      * @param string $currmod the current module, used to check configuration for showing unit in header
      * @param string $popup_t the popup type, used to replace in action links if they contain [popup_t] placeholder
      * @param string $target the target for action links, used to set target attribute in action links
      * @param array $images an array of images paths used in action buttons, with image name as key and path as value, used to set src attribute in action buttons images
      * @param Auser $objme the current user object, used to check permissions on each object for each action
      * @param array $fixms an array of fixed columns to show in retrieve result, used to set css class for fixed columns 
      * @param string $lang the current language, used to translate action item names and also to get attribute labels in case they are not set in header
     */

    public static function showDataRetrieve($obj, $data, $header, $class_db_structure, $liste_obj, $isAvail, 
               $cl_tr, $class_td1, $class_td2, $class_td_off, 
               $cl, $currmod, $popup_t, $target, $images, $objme, $fixms, $lang, $addHeader=false)
    {
        AfwSession::log("Before execute UmsPagHelper::getActionsMatrix in afw_handle_default_search");
        $actions_tpl_matrix = AfwUmsPagHelper::getActionsMatrix($liste_obj);
        AfwSession::log("After execute UmsPagHelper::getActionsMatrix in afw_handle_default_search");

        $actions_tpl_arr = AfwUmsPagHelper::getAllActions($obj, 0, false);
        // throw new AfwRun timeException("debugg :: actions_tpl_arr of $cl = ".var_export($actions_tpl_arr,true));

        $cant_do_action_log_arr = array();
        $can_action_arr = array();
        $datatable_header = "";
        AfwSession::log("Before prepare of header and can_action array matrix in afw_handle_default_search");
        if (count($header) != 0) {            
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
        else {
            throw new AfwBusinessException("For class $cl no header columns defined to retrieve lang=$lang");
        }

        AfwSession::log("After prepare of header and can_action array matrix in afw_handle_default_search");
        AfwSession::log("Before show data retrieve in afw_handle_default_search");
        $html = "";
        if($addHeader) $html .= "<thead><tr>$datatable_header</tr></thead>";
        $ids = "";
        $ids_count = 0;
        $maxRecordsUmsCheck = $obj->maxRecordsUmsCheck();
        $repeat_retrieve_header = $obj->repeatRetrieveHeader();
        $umsCheckDisabledInRetrieveMode = $obj->umsCheckDisabledInRetrieveMode();



        if ($maxRecordsUmsCheck > 100) $maxRecordsUmsCheck = 100;
        foreach ($data as $id => $tuple) {
            $row_class_key = $tuple['ca-col'];
            //if($ids_count<50)
            //{
            if ($ids) $ids .= ",";
            $ids .= $id;
            $ids_count++;
            if ($repeat_retrieve_header and (($ids_count % $repeat_retrieve_header) == 0)) {
                $html .= "<thead><tr>$datatable_header</tr></thead>";
            }
            //}
            if ($cl_tr == $class_td2) $cl_tr = $class_td1;
            else $cl_tr = $class_td2;
            if (!$isAvail[$id]) $cl_tr = $class_td_off;

            $lbl = addslashes($tuple["display_object"]);
            if ($row_class_key) {
                $row_class_key_val = '' . $tuple['ca-' . $row_class_key];
                $row_class_key_val = str_replace('-', '_', $row_class_key_val);
                $row_class_css = $row_class_key . ' hzm_row_' . $row_class_key_val;
                /*if(!trim($row_class_key_val)) {
                                                                                                die("Error: row_class_key_val is empty for id=$id, tuple = ".var_export($tuple,true)." and row_class_key = $row_class_key");
                                                                                        }*/
            } else {
                $row_class_css = 'hzm_row_std';
            }

            $html .= "<tr class='data-row $row_class_css'>";
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

                $html .= "<td class='col-importance-$importance text_$text_direction srch-result-col-$nom_col'>" . $tuple[$nom_col] . "</td>";
            }

            // die("rafik is debugging ... actions_tpl_arr= ".var_export($actions_tpl_arr,true));
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
                        $link = $page_params = $action_item_props["params"];
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
                                $html .= "<td class='btn-class col-importance-$importance $frameworkAction'><a class='btn-micro $btnclass' $target_action href='main.php?$link'>$frameworkAction_tr</a></td>";
                            } elseif ($img) {
                                $tooltip = "";
                                $icon_help = $action_item_props["help"];
                                if ($icon_help) $tooltip = "data-toggle='tooltip' data-placement='bottom' title='$icon_help' data-original-title=' - Tooltip on bottom 0' class='red-tooltip'";

                                if ($ajax_class) {
                                $html .= "
                                    <td class='ajax col-importance-$importance $frameworkAction'>
                                         <a href=\"#\" id=\"$id\" cl=\"$cl\" md=\"$currmod\" lbl=\"$lbl\" class=\"$ajax_class\">
                                            <img lbl='ajax' src=\"$img\" width=\"24\" heigth=\"24\" $tooltip >
                                        </a>
                                    </td>";
                                } else {
                                    if ($link) $the_action_link = "main.php" . "?" . $link;
                                    else $the_action_link = "#";
                                $html .= "
                                    <td class='action-link col-importance-$importance $frameworkAction'><a $target_action href=\"$the_action_link\">
                                            <img lbl='no-ajax' src=\"$img\" width=\"24\" heigth=\"24\" $tooltip >
                                        </a>
                                    </td>";
                            

                                }

                                // die("DBG-after ajax test\n"); 
                            } else {
                                $html .= "<td  class='col-importance-$importance $frameworkAction no-image'>no_image_for_mode_$frameworkAction action_item_props=" . var_export($action_item_props, true) . "</td>";
                            }
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

                            $locked_him_self = $images["locked_him_self"];
                            $html .= "
                            <td class='col-importance-$importance locked-him-self'><img src=\"$locked_him_self\" width=\"24\" heigth=\"24\" $tooltip ></td>
                        ";
                        }
                    } elseif ($can and (!$canOnMe)) {
                        if (($objme and $objme->isSupervisor()) or AfwSession::config("MODE_DEVELOPMENT", false)) 
                            $tooltip = "data-toggle='tooltip' data-placement='bottom' title='عندما تكون نتائج البحث كثيرة يتم ايقاف التعديلات على جزء من السجلات. قم باختيار معايير اكثر دقة للبحث' data-original-title='$action_item -> $cant_do_action_log - Tooltip on bottom 2' class='red-tooltip'";
                        else $tooltip = "";
                        if ($canOnMe === null) {
                            $canCss = 'off';
                        } else {
                            $canCss = 'locked_on_me';
                        }
                        $canImage = $images[$canCss];
                        $html .= "
                        <td class='col-importance-$importance $canCss'><img src=\"$canImage\" width=\"24\" heigth=\"24\" $tooltip ></td>
                    ";
                    } else { // means can't ($can is false)
                        if (($objme and $objme->isSupervisor()) or AfwSession::config("MODE_DEVELOPMENT", false)) $tooltip = "data-toggle='tooltip' data-placement='bottom' title='لا يمكنك التعديل على هذا السجل راجع المشرف للتأكد من الصلاحيات وسياسة التعديل' data-original-title='You have not authorization to do $frameworkAction on this entity : [$action_item -> $cant_do_action_log] - Tooltip on bottom 3' class='red-tooltip'";
                        else $tooltip = "";
                        $image_locked = $images["locked"];
                        $html .= "<td class='col-importance-$importance can-t-case'><img src=\"$image_locked\" width=\"24\" heigth=\"24\" $tooltip ></td>";
                    }
                }
            }

            $html .= "</tr>";
        }
        $data_count = count($data);
        if (is_array($fixms)) $fixmlist = implode(",", $fixms);
        else $fixmlist = "";
        
        
        return [
            "html" => $html,
            "ids" => $ids,
            "ids_count" => $ids_count,
            "datatable_header" => $datatable_header,
            "data_count" => $data_count,
            "fixmlist" => $fixmlist,
        ];
    }
}
?>