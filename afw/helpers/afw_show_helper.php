<?php

class AfwShowHelper
{
    // @todo : rafik : we need here to add attribute for tables having grouped fields (FGROUP) to say
    // what fgroup view we want to see in many objects table view attribute
    // desc["FGROUP-VIEW"] : "[special fgroup]" => to see only columns of this special fgroup,
    //                       "DEFAULT" => by default to see all retieve columns,
    //                       "ALL-BY-TAB" => to see all retieve columns but splitted into tabs each fgroup in a special tab,
    // it is very useful for afield for example which have ITEMS attribute in atable but we can't see all attributes (too much)

    public static function showMany($obj, $cols, $objme, $lang, $options = [])
    {
        foreach ($options as $option => $option_value) {
            ${$option} = $option_value;
        }

        $liste_obj = $obj->loadMany();

        $arr_col = explode(',', $cols);

        return self::showManyObj($liste_obj, $obj, $objme, $lang, $options);
    }

    /**  
     *   @param array
     *   @param object
     *   @param object
     *   @param array
     *   @param array
     *   @param boolean
     *   @return string
     */

    public static function manyMiniBoxes(
        $liste_obj,
        $obj,
        $objme,
        $structure,
        $options = [],
        $public_show = false
    ) {
        $images = AfwThemeHelper::loadTheme();
        foreach($images as $theme => $themeValue)
        {
            $$theme = $themeValue;
        }
        // options
        $arr_col = 0;
        $trad_erase = [];
        $limit = '';
        $order_by = '';
        $optim = true;
        $class_table = '';
        $class_tr1 = 'altitem';
        $class_tr2 = 'item';
        $class_td_off = 'off';
        $lang = 'ar';
        $dir = 'rtl';
        $bigtitle = '';
        $bigtitle_tr_class = 'bigtitle';

        foreach ($options as $option => $option_value) {
            ${$option} = $option_value;
        }

        $obj_table = $obj->getTableName();
        $class_table = $structure['MINIBOX_CLASS'];
        // rafik 27/09/2023 :
        // table _rtv i broked with space table[space]_rtv  because this table_rtv make css bad and I dont remember why we add this
        // bad css remarked in step 5 of SchoolYear class
        if (!$class_table) {
            $class_table = "table _rtv mb_$obj_table " . $obj->mb_context;
        }
        $html = '';
        $html_header = '';
        $cl_tr = '';

        if (count($liste_obj) == 0) {
            return [$html, $liste_obj, "", 'no-object'];
        }

        if (!$arr_col) {
            $arr_col = $obj->getMiniBoxCols();
            $mode_force_cols = false;
        } else {
            $mode_force_cols = true;
        }

        // die("getMiniBoxCols = ".var_export($arr_col,true));

        $cols_minibox = [];
        $data = [];

        $report_arr = [];
        $isAvail = [];
        $dataImportance = [];


        if (count($arr_col) == 0) {
            throw new AfwRuntimeException('afw-shower error : no mini-box cols');
        }

        foreach ($arr_col as $cc => $nom_col) {
            if ($public_show) {
                $desc = AfwStructureHelper::getStructureOf($obj, $nom_col);
            } else {
                $desc = $obj->keyIsToDisplayForUser($nom_col, $objme);
            }
            if ($desc) {
                if ($nom_col != $obj->getPKField() or $obj->showId) {
                    // if($nom_col != $obj->getPKField()) die("keyIsToDisplayForUser($nom_col) = ".var_export($desc,true));
                    $cols_minibox[$nom_col] = $desc;
                }
            } else {
                // if($nom_col=="firstname")
                // die("$nom_col is not a keyToDisplayForUser : ".$objme->getDisplay($lang)." check SHOW Attribute for the '$nom_col' column");
                // die(var_export($objme,true));
                // die("UGROUPS = '".$desc["UGROUPS"]."'");
                $report_arr[] = "$nom_col is not to show";
            }
        }

        // die("cols_minibox = ".var_export($cols_minibox,true));
        // die(var_export($cols_minibox,true));
        if (count($cols_minibox) != 0) {
            $header = &$cols_minibox;
        } else {
            $header = ['description' => 'AAA'];
        }

        if (!$mode_force_cols) {
            $del_level = $obj->del_level;
            $show_as = 'SHOW-AS-ICON';

            if ($obj->viewIcon) {
                $header['DISPLAY'] = [
                    'CODE' => 'view',
                    'TYPE' => 'SHOW',
                    $show_as => true,
                ];
            }
            if ($obj->editIcon) {
                $header['EDIT'] = [
                    'CODE' => 'edit',
                    'TYPE' => 'EDIT',
                    $show_as => true,
                ];
            }
            if ($obj->deleteIcon) {
                $header['DELETE'] = [
                    'CODE' => 'del',
                    'TYPE' => 'DEL',
                    $show_as => true,
                    'DEL_LEVEL' => $del_level,
                ];
            }
            if ($obj->attachIcon) {
                $header['ATTACH'] = [
                    'CODE' => 'attach',
                    'TYPE' => 'ATTACH',
                    $show_as => true,
                ];
            }

            if ($obj->MOVE_UP_ACTION) {
                $header['MOVE_UP'] = [
                    'CODE' => 'move_up',
                    'TYPE' => 'MOVE_UP',
                    $show_as => true,
                ];
            }

            if ($obj->MOVE_DOWN_ACTION) {
                $header['MOVE_DOWN'] = [
                    'CODE' => 'move_down',
                    'TYPE' => 'MOVE_DOWN',
                    $show_as => true,
                ];
            }

            //die("$obj header = ".var_export($header,true));
        }

        $j = 0;
        $target = '';
        $is_ok_arr = [];
        $errors_html_arr = [];
        foreach ($liste_obj as $id => $val) {
            $liste_obj[$id]->del_level = $obj->del_level;
            $liste_obj[$id]->viewIcon = $obj->viewIcon;
            $liste_obj[$id]->editIcon = $obj->editIcon;
            $liste_obj[$id]->deleteIcon = $obj->deleteIcon;
            $liste_obj[$id]->id_origin = $obj->id_origin;
            $liste_obj[$id]->class_origin = $obj->class_origin;
            $liste_obj[$id]->module_origin = $obj->module_origin;

            list($is_ok_arr[$id], $dataErr) = $liste_obj[$id]->isOk(
                $force_i = true,
                $return_i_errors = true
            );
            $errors_html_arr[$id] = implode("<br>\n", $dataErr);
            $j++;

            if ($public_show or ($objme and $objme->iCanDoOperationOnObjClass($liste_obj[$id], 'display'))) {
                $objIsActive = $liste_obj[$id]->isActive();
                $obj_class = $liste_obj[$id]->getMyClass();
                $obj_currmod = $liste_obj[$id]->getMyModule();

                $tuple = [];
                if (count($header) != 0) {
                    // below is old code should now be obsolete
                    // $tuple['description'] = $liste_obj[$id]->__toString();
                    foreach ($header as $col => $desc) {
                        $currstep = $desc["GO-TO-STEP"];
                        if (!$currstep) $currstep = $val->getDefaultStep();
                        if (!$currstep) $currstep = 1;
                        if ($desc == 'AAA') {
                            // $tuple["description"] = $liste_obj[$id]->__toString();
                        } elseif ($col == 'DISPLAY') {
                            $tuple[AfwLanguageHelper::translateKeyword("DISPLAY", $lang)] = "<a $target href='main.php?Main_Page=afw_mode_display.php&popup=&cl=$obj_class&currmod=$obj_currmod&id=$id&currstep=$currstep' ><img src='../lib/images/view_ok.png' width='24' heigth='24'></a>";
                        } elseif ($col == 'EDIT') {
                            $edit_button_path = $images['modifier'];
                            $tuple[AfwLanguageHelper::translateKeyword("EDIT", $lang)] = "<a target=\"_new\" href='main.php?Main_Page=afw_mode_edit.php&popup=&cl=$obj_class&currmod=$obj_currmod&id=$id&currstep=$currstep' ><img src='$edit_button_path' width='24' heigth='24'></a>";
                        } elseif ($col == 'ATTACH') {
                            $attach_url = $liste_obj[$id]->getAttachUrl();
                            $tuple[AfwLanguageHelper::translateKeyword("ATTACH", $lang)] = "<a target=\"_new\" href='$attach_url' ><img src='../lib/images/attach.png' width='24' heigth='24'></a>";
                        } elseif ($col == 'MOVE_UP') {
                            $icon_button_path = $images['move-up'];
                            $tuple[AfwLanguageHelper::translateKeyword("MOVE_UP", $lang)] = "<a href='#' here='afw_shwr-up' id='$val_id' cl='$val_class' md='$val_currmod' lbl='$lbl' sens=-1 class='move-up'><img src='$icon_button_path' style='height: 22px !important;'></a>";

                        } elseif ($col == 'MOVE_DOWN') {
                            $icon_button_path = $images['move-down'];
                            $tuple[AfwLanguageHelper::translateKeyword("MOVE_DOWN", $lang)] = "<a href='#' here='afw_shwr-down' id='$val_id' cl='$val_class' md='$val_currmod' lbl='$lbl' sens=1 class='move-down'><img src='$icon_button_path' style='height: 22px !important;'></a>";

                        }elseif ($col == 'DELETE') {
                            $val_id = $liste_obj[$id]->getId();
                            $val_class = $liste_obj[$id]->getMyClass();
                            $val_currmod = $liste_obj[$id]->getMyModule();
                            $lbl = $liste_obj[$id]->getDisplay($lang);
                            $lvl = $desc['DEL_LEVEL'];
                            if (!$lvl) {
                                $lvl = 2;
                            }
                            $userCanDel = $liste_obj[$id]->userCanDeleteMe($objme);
                            if ($userCanDel > 0) {
                                $delete_button_path = $images['delete'];

                                // <a target='del_record' href='main.php?Main_Page=afw_mode_delete.php&cl=$val_class&currmod=$currmod&id=$val_id' >
                                $tuple[AfwLanguageHelper::translateKeyword("DELETE", $lang)] = "<a href='#' here='afw_shwr' id='$val_id' cl='$val_class' md='$val_currmod' lbl='$lbl' lvl='$lvl' div_to_del='${obj_table}${id}_minibox_container' class='trash manyminiboxes'><img src='$delete_button_path' style='height: 22px !important;'></a>";
                                $tuple['del_status'] = 'OK';
                            } else {
                                if ($userCanDel == -1) {
                                    $explanation = "لا يوجد لديك صلاحية لمسح هذا النوع من السجلات";
                                } else {
                                    $explanation = "انك تحتاج لصلاحية خاصة لمسح هذا السجل بعينه";
                                }
                                $tuple[AfwLanguageHelper::translateKeyword("DELETE")] =
                                    "<a href='#'><img src='../lib/images/lock.png' data-toggle='tooltip' data-placement='top' title='$explanation' width='24' heigth='24' ></a>";
                                $tuple['del_status'] = 'locked';
                            }
                        } else {
                            $get_val_col = $liste_obj[$id]->getVal($col);
                            $tuple[$col] = $liste_obj[$id]->showAttribute(
                                $col,
                                $desc
                            ) . "<!-- showAttribute of $col val [$get_val_col] -->";
                            // if($col=="المسح") die("tuple[$col] = ".$tuple[$col]);
                        }
                    }
                }
                $data[$id] = $tuple;
                $isAvail[$id] = $objIsActive;
                // $count_liste_obj++;
            }
        }

        $html_header .= "<div dir='$dir' class='$class_table'>\n";
        $html_header .= "<span>$bigtitle</span>\n";
        //die("data=".var_export($data,true));
        $html = '';
        //$html = 'data-count='.count($data);
        $ids = '';
        $is_first_minibox = true;
        foreach ($data as $id => $tuple) {
            if ($structure['MINIBOX-TPL'] or $structure['MINIBOX-TEMPLATE']) {
                $file_tpl = $structure['MINIBOX-TEMPLATE'];
                if (!$file_tpl) {
                    $file_tpl = "tpl/tpl_mb_$obj_table.php";
                }
                //$html .= "rafik dbg show minibox ".$liste_obj[$id]->getDisplay('ar');
                $html .= $liste_obj[$id]->showUsingTpl($file_tpl, $trad_erase);
            }
            // die("structure=".var_export($structure,true));
            else {
                if ($structure['MINIBOX-HEADER']) {
                    $header_to_display = $liste_obj[$id]->getDisplay($lang);

                    /*
                     $border_size = $structure["MINIBOX-BORDER"]; 
                     if(!$border_size) $border_size = 2;
                     $border_color = $structure["MINIBOX-BORDER-COLOR"];
                     if(!$border_color) $border_color = "#fff";
                     $background_color = $structure["MINIBOX-BACKGROUND-COLOR"];
                     if(!$background_color) $background_color = "#efefef";
                     $header_background_color = $structure["MINIBOX-HEADER-BACKGROUND-COLOR"];
                     if(!$header_background_color) $header_background_color = "#e0e0e0";*/

                    if (
                        $structure['MINIBOX-HEADER'] == 'all_open' or
                        $structure['MINIBOX-HEADER'] == 'first_open' and
                        $is_first_minibox
                    ) {
                        $status_collapsed = '';
                        $collapse_in = 'in';
                        $is_expanded = 'true';
                    } else {
                        $status_collapsed = 'collapsed';
                        $collapse_in = '';
                        $is_expanded = 'false';
                    }

                    if ($is_ok_arr[$id]) {
                        $obj_i_status = 'ok';
                        $errors_html = '';
                    } else {
                        $obj_i_status = 'err';
                        $errors_html = $errors_html_arr[$id];
                    }

                    $html .= "<div id='${obj_table}${id}_minibox_container' class='hzm_${obj_table}_container hzm_minibox_container'>        
                                <div class='home_bloc hzm_wd4 hzm_minibox_header0'>                
                                <div class='hzm_label hzm_${obj_table}_label object_status_$obj_i_status greentitle expand $status_collapsed' data-toggle='collapse' data-target='#${obj_table}${id}_minibox'>
                                <i></i>$header_to_display
                                <!-- $errors_html -->
                                </div>        
                                </div>        
                                <div id='${obj_table}${id}_minibox' class='${obj_table}_bloc home_bloc hzm_wd4 hzm_minibox_body collapse $collapse_in' aria-expanded='$is_expanded' style=''>";

                    /*
                 $html .= "<table style='border-width:px; border-color:$border_color;width:100%;'>
                 <tr style='border-width:${border_size}px;border-color:$border_color;border-style: solid;'>
                 <td style='background-color:$header_background_color;text-align:center;font-weight:bold'>$header_to_display</td>
                 </tr>
                 <tr style='border-width:${border_size}px;border-color:$border_color;border-style: solid;'>
                 <td style='background-color:$background_color;'>"; */
                } else {
                    $html .= "<div id='${obj_table}${id}_minibox_container' class='hzm_${obj_table}_container hzm_minibox_container'>";
                }

                $html .= $html_header;
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $id;
                if ($cl_tr == $class_tr2) {
                    $cl_tr = $class_tr1;
                } else {
                    $cl_tr = $class_tr2;
                }
                if (!$isAvail[$id]) {
                    $cl_tr = $class_td_off;
                }

                foreach ($header as $nom_col => $desc) {
                    $code_col = $desc['CODE'];
                    $status_del = $tuple["${code_col}_status"];
                    if (!$status_del) {
                        $status_del = 'OK';
                    }
                    $trad_col = $trad_erase[$nom_col];
                    $data_col = $tuple[$nom_col];
                    if (!$trad_col) {
                        $trad_col = $liste_obj[$id]->getAttributeLabel(
                            $nom_col,
                            $lang
                        );
                    }
                    if ($desc['SHOW-AS-ICON']) {
                        $html .= "<div class='minibox_icon icon_$code_col icon_$status_del'>\n";
                        $html .= "<p style='padding-top: 0px;'>$data_col</p>\n";
                        $html .= '</div>';
                        //<div style='padding-left: 10px;width:180px; float: left'></div>
                    } elseif ($desc['SHOW-AS-ROW']) {
                        $html .= "<br><table class='simple_grid'><tr>";
                        $html .= "   <th><span class='titre_0'>$trad_col</span></th>\n";
                        $html .= "   <td style='background-color:#fff;'><p style='padding-top: 0px;'>$data_col</p></td>\n";
                        $html .= '</tr></table>';
                        //<div style='padding-left: 10px;width:180px; float: left'></div>
                    } else {
                        if (
                            $desc['SIZE'] == 'AEREA' or
                            $desc['SIZE'] == 'AREA' or
                            $desc['CATEGORY'] == 'ITEMS' or
                            $desc['SUB-CATEGORY'] == 'ITEMS'
                        ) {
                            $inputarea = 'inputarea';
                        } else {
                            $inputarea = 's' . $desc['SIZE'] . ' c' . $desc['CATEGORY'] . ' b' . $desc['SUB-CATEGORY'];
                        }
                        $css_custom = $desc['MB_CSS'];
                        if (!$css_custom) {
                            $css_custom = $desc['CSS'];
                        }
                        $html .= "

                        <div id=\"fg-$nom_col\" class=\"attrib-$nom_col form-group $css_custom\">
                            <label for=\"$nom_col\" class=\"hzm_label hzm_label_$nom_col \"><b>$trad_col : </b> 
                            </label>
                            <div class=\"hzm_data hzm_data_$nom_col form-control inputreadonly $inputarea\" style=\"\">            
                            $data_col       
                            </div>                        
                        </div>";
                        /*
                     $html .= "   <span class='titre_0'>$trad_col</span>\n";
                     $html .= "   <p style='padding-right: 10px;'>$data_col</p>\n";
                     */
                    }
                }
                $html .= "<br>\n";
                $html .= "</div>\n";
                if ($structure['MINIBOX-HEADER']) {
                    $html .= '</div></div>';
                } else {
                    $html .= '</div>';
                }
            }

            $is_first_minibox = false;
        }

        return [$html, $liste_obj, $ids];
    }

    // $all_items (default = false) means that we take all items in TreeView as main nodes even those having parents
    // by default any item having parent can not be a 'main node' in treeView and will be as child somewhere in the tree
    public static function showTree(
        $tree_id,
        $itemsList,
        $link_col,
        $items_col,
        $feuille_col,
        $feuille_cond_method,
        $objme,
        $lang = 'ar',
        $all_items = false,
        $face_to_face = false,
        $iframe_height = 600
    ) {
        global $treePlugin;

        if ($treePlugin == 'jqtree') {
            //die(var_export($itemsList,true));
            $html = "<div id=\"$tree_id\"></div>";
            $js = "var data_$tree_id = [\n";
            $js_items = [];
            foreach ($itemsList as $itemId => $itemObj) {
                $item_parent_id = intval($itemObj->getVal($link_col));
                if (!$item_parent_id or $all_items) {
                    $js_item = AfwHtmlHelper::toJsArray(
                        $itemObj,
                        $items_col,
                        $feuille_col,
                        $feuille_cond_method,
                        $lang,
                        "\t"
                    );
                    if ($js_item) {
                        $js_items[] = $js_item;
                    }
                }
            }
            $countNodes = count($js_items);

            $js .= implode(",\n\t", $js_items); //
            $js .= '];';
            $js .= "\n\n\t\$(function() {
            \$('#$tree_id').tree({
            data: data_$tree_id,
            rtl: true,
            openFolderDelay: 10,
            autoOpen: false,
            closedIcon: '+',
            openedIcon: '-'
            });
            });";
        } else {
            $html_arr = [];
            $arr_icon_types = [];

            foreach ($itemsList as $itemId => $itemObj) {
                list($icon_name, $icon_path) = $itemObj->getMySpecialIcon();
                $arr_icon_types[$icon_name] = ['icon' => $icon_path];

                $item_parent_id = intval($itemObj->getVal($link_col));
                if (!$item_parent_id or $all_items) {
                    $html_item = AfwHtmlHelper::objToLIForTree(
                        $itemObj,
                        $items_col,
                        $feuille_col,
                        $feuille_cond_method,
                        $lang,
                        "\t"
                    );
                    if ($html_item) {
                        $html_arr[] = $html_item;
                    }
                }
            }

            if ($face_to_face) {
                $col_xs_tree = 6;
                $col_xs_iframe = 6;
            } else {
                $col_xs_tree = 12;
                $col_xs_iframe = 12;
            }

            $all_html = implode("\n", $html_arr);
            $html = "<div class=\"col-xs-$col_xs_tree full_height\" id=\"$tree_id\"><ul>$all_html</ul></div><div class=\"col-xs-$col_xs_iframe full_height\"><iframe id=\"view_$tree_id\" src='' style='border:none;width:100%;height:100%;min-height:${iframe_height}px;' width='100%' height='100%'></iframe></div>";

            $arr_icon_types['employee'] = [
                'icon' => '../lib/images/icon-employee.png',
            ];
            $arr_icon_types['orgunit'] = [
                'icon' => '../lib/images/icon-orgunit.png',
            ];
            $arr_icon_types['jobsdd'] = [
                'icon' => '../lib/images/icon-jobsdd.png',
            ];
            $arr_icon_types['jobrole'] = [
                'icon' => '../lib/images/icon-jobrole.png',
            ];
            $arr_icon_types['module'] = [
                'icon' => '../lib/images/icon-module.png',
            ];
            $arr_icon_types['arole'] = [
                'icon' => '../lib/images/icon-arole.png',
            ];
            $arr_icon_types['arole_bf'] = [
                'icon' => '../lib/images/icon-menu.png',
            ];
            $arr_icon_types['bfunction'] = [
                'icon' => '../lib/images/icon-bfunction.png',
            ];
            $arr_icon_types['atable'] = [
                'icon' => '../lib/images/icon-atable.png',
            ];
            $arr_icon_types['afield'] = [
                'icon' => '../lib/images/icon-afield.png',
            ];
            $arr_icon_types['afield_group'] = [
                'icon' => '../lib/images/icon-afield_group.png',
            ];
            $arr_icon_types['user_story'] = [
                'icon' => '../lib/images/icon-user_story.png',
            ];
            $arr_icon_types['goal'] = ['icon' => '../lib/images/icon-goal.png'];
            $arr_icon_types['domain'] = [
                'icon' => '../lib/images/icon-domain.png',
            ];
            $arr_icon_types['collegue'] = [
                'icon' => '../lib/images/icon-collegue.png',
            ];
            $arr_icon_types['institute'] = [
                'icon' => '../lib/images/icon-institute.png',
            ];

            $list_of_custom_icon_types = '';

            foreach ($arr_icon_types as $icon_type => $icon_type_infos) {
                $icon_path = $icon_type_infos['icon'];
                $list_of_custom_icon_types .= ", \"$icon_type\" : {\"icon\" : \"$icon_path\"}";
            }

            $countNodes = count($html_arr);
            // removed from plugins \"checkbox\",
            $js = "\n\n\t\$(function () {
        \$('#$tree_id').jstree({ plugins : [\"sort\",\"types\",\"wholerow\"], \"types\" : { \"file\" : { \"icon\" : \"jstree-file\" }, \"folder\" : {\"icon\" : \"../lib/images/gf_58.png\"}$list_of_custom_icon_types } });
        \$('#$tree_id').on(\"changed.jstree\", function (e, data) {
        v_url = \"main.php?My_Module=ums&Main_Page=hzm_view.php&popup=1&a=\"+data.selected[0];
        console.log(v_url);
        console.log(\$('#view_$tree_id').attr(\"id\"));
        \$('#view_$tree_id').attr(\"src\", v_url);
        });

        });";
        }
        //die($js);
        return [$html, $js, $countNodes];
    }

    /**  
     *   @param array of AFWObject $liste_obj
     *   @param AFWObject $obj
     *   @param Auser $objme
     *   @param array 
     *   @return array
     */

    public static function showManyObj($liste_obj, $obj, $objme, $lang, $options = [])
    {
        $images = AfwThemeHelper::loadTheme();
        foreach($images as $theme => $themeValue)
        {
            $$theme = $themeValue;
        }
        $arr_col = 0;
        $trad_erase = [];
        $limit = '';
        $order_by = '';
        $optim = true;
        $class_table = 'grid';
        $class_tr1 = 'altitem';
        $class_tr2 = 'item';
        $class_td_off = 'off';
        $dir = '';
        $bigtitle = '';
        $bigtitle_tr_class = 'bigtitle';
        $width_th_arr = [];
        $img_width = '';
        $rows_by_table = 0;
        $hide_retrieve_cols = null;
        $force_retrieve_cols = null;
        $cl_tr = '';

        // if($options and (count($options)>0)) AfwStructureHelper::dd("rafik options not empty");

        foreach ($options as $option => $option_value) {
            ${$option} = $option_value;
            // if($option == "hide_retrieve_cols") AfwStructureHelper::dd("رفيق قل اللهم لا سهل الا ما جعلته سهلا : option hide_retrieve_cols found in ".var_export($options,true));
        }

        // if($options and (count($options)>0)) AfwStructureHelper::dd("rafik look اللهم لا سهل الا ما جعلته سهلا : hide_retrieve_cols is ".var_export($hide_retrieve_cols,true)." where options is ".var_export($options,true));

        $ids = '';

        if (count($liste_obj) == 0) {
            return ['', $liste_obj, $ids];
        }
        /*
         $objret = null;
         foreach($liste_obj as $id => $val)
         {
         if(!$objret) 
         {
         $objret = $liste_obj[$id];
         break;
         }
         }*/

        $id_origin = $obj->id_origin;
        $class_origin = $obj->class_origin;
        $module_origin = $obj->module_origin;

        $mode = 'display';
        if ($obj->mode_retieve) {
            $mode = $obj->mode_retieve;
        }

        if (!$arr_col) {
            $arr_col = $obj->getRetrieveCols(
                $mode,
                $lang,
                $all = false,
                $type = 'all',
                $debugg = true,
                $hide_retrieve_cols,
                $force_retrieve_cols
            );
            $mode_force_cols = false;
        } else {
            $mode_force_cols = true;
        }

        // debugg some column hidden and should not
        // if($obj instanceof ApplicationModelBranch) die("arr_col = getRetrieveCols($mode) = ".var_export($arr_col,true)." force_retrieve_cols :".var_export($force_retrieve_cols,true));

        // debugg some column not hidden and should be
        /*
            if($obj instanceof Request) 
            {
            AfwStructureHelper::dd("getRetrieveCols($mode) with hide_retrieve_cols :".var_export($hide_retrieve_cols,true)." has returned arr_col = ".var_export($arr_col,true)." where options :".var_export($options,true));        
            }
            */

        //if($mode=="field_rules") die("arr_col = ".var_export($arr_col,true));

        $cols_retrieve = [];
        $data = [];
        $dataValue = [];
        $isAvail = [];

        if (count($arr_col) == 0) {
            throw new AfwRuntimeException("afw-shower error : no retrieve cols for " . get_class($obj) . " instance=" . $obj->id);
        }

        foreach ($arr_col as $cc => $nom_col) {
            $desc = $obj->keyIsToDisplayForUser($nom_col, $objme);
            if ($desc) {
                if (
                    $nom_col != $obj->getPKField() or
                    $obj->getOptionValue('showId') or
                    AfwSession::class_config_exists(
                        $obj->getMyClass(),
                        'showId'
                    )
                ) {
                    //@doc / afw / attribute-type / ITEMS / retrieve-cols / note / if you want to show Id in retrieve cols define in the items answer class constructor $object->setOptionValue("showId",true); or define in the application_config.php file the param [answer_class]_showId => true, ex : practice_showId => true,
                    $cols_retrieve[$nom_col] = $desc;
                } else {
                    //$mcls = $obj->getMyClass();
                    //$cols_retrieve[$nom_col."_debugg"] = "obj->showId=".$obj->showId." and class_config_exists[${mcls}_showId] = ".AfwSession::class_config_exists($mcls, "showId");
                }
            } else {
                throw new AfwRuntimeException(
                    "column $nom_col is not to display for me=" .
                        var_export($objme, true)
                );
                // die("UGROUPS = '".$desc["UGROUPS"]."'");
            }
        }
        /*
         if($obj->getMyClass() == "PracticeVote") 
         {
         die("obj->getMyClass() = ".$obj->getMyClass()." and cols_retrieve => ".var_export($cols_retrieve,true));
         }
         */

        if (count($cols_retrieve) != 0) {
            $header = &$cols_retrieve;
        } else {
            $header = ['description' => 'AAA'];
        }

        if (!$mode_force_cols) {
            $del_level = $obj->del_level;
            if ($obj->viewIcon) {
                $col_trans = AfwLanguageHelper::translateKeyword("DISPLAY", $lang);
                $header[$col_trans] = ['TYPE' => 'SHOW', 'GO-TO-STEP' => $obj->viewIcon];
            }
            if ($obj->editIcon) {
                $col_trans = AfwLanguageHelper::translateKeyword("EDIT", $lang);
                $header[$col_trans] = ['TYPE' => 'EDIT', 'GO-TO-STEP' => $obj->editIcon];
            }
            if ($obj->deleteIcon) {
                $col_trans = AfwLanguageHelper::translateKeyword("DELETE", $lang);
                $header[$col_trans] = ['TYPE' => 'DEL', 'DEL_LEVEL' => $del_level];
            }
            if ($obj->MOVE_UP_ACTION) {
                $col_trans = AfwLanguageHelper::translateKeyword("MOVE_UP", $lang);
                $header[$col_trans] = [
                    'CODE' => 'move_up',
                    'TYPE' => 'MOVE_UP',
                    'MOVE-QUESTION' => $obj->MOVE_QUESTION,
                    $show_as => true,
                ];
            }

            if ($obj->MOVE_DOWN_ACTION) {
                $col_trans = AfwLanguageHelper::translateKeyword("MOVE_DOWN", $lang);
                $header[$col_trans] = [
                    'CODE' => 'move_down',
                    'TYPE' => 'MOVE_DOWN',
                    'MOVE-QUESTION' => $obj->MOVE_QUESTION,
                    $show_as => true,
                ];
            }
            
        }
        //else AfwRunHelper::lightSafeDie("mode_force_cols");
        /*
if(!$obj->deleteIcon) die("obj = ".var_export($obj, true));
if($obj instanceof Atable) die("header of Atable = ".var_export($header, true));
*/
        // لا إله إلا الله
        // show all detail records only if one of these conditions is verified
        //  1. record count is small < 30    ===> mode mode_show_all_records
        //  2. option show only errors if big data not activated for the current session ===> mode mode_show_all_records
        //  3. option show errors in retrieve mode disabled for this class ===> mode mode_show_all_records
        // or :
        //  4. the record contain errors
        $liste_obj_count = count($liste_obj);
        $small_liste = ($liste_obj_count < 30);
        $mode_show_all_records =
            ($small_liste or // عدد قليل من الكينات للعرض
                !AfwSession::hasOption('BIG_DATA_SHOW_ONLY_ERRORS') or // خيار اظهار الأخطاء فقط في حال بيانات كثيرة غير مفعل
                !$obj->showRetrieveErrors);
        $j = 0;

        foreach ($liste_obj as $id => $val) {
            $j++;
            if (is_object($val) and ($val instanceof AFWObject) and AfwUmsPagHelper::userCanDoOperationOnObject($val, $objme, 'display')) {
                // we force errors test only if we are not in mode mode_show_all_records
                $check_errors_needed_in_object = $val->canCheckErrors($small_liste, AfwSession::hasOption('CHECK_ERRORS'));
                $force_test_errors =
                    (!$mode_show_all_records or $check_errors_needed_in_object);
                $val_isOk = $val->isOk($force_test_errors); //

                if ($mode_show_all_records or !$val_isOk) {
                    $objIsActive = $val->isActive() ? 'active' : 'inactive';
                    $viewIcon = $val->isActive() ? 'view_me' : 'view_off';
                    // die("show ManyObj, val = ".var_export($val,true));
                    if ($val->isActive()) {
                        if ($check_errors_needed_in_object) {
                            if ($val_isOk) {
                                $objIsActive = 'active';
                                $viewIcon = 'view_ok';
                                //die("$val_isOk = $val ->isOk($mode_show_all_records)");
                            } else {
                                $objIsActive = 'error';
                                $viewIcon = 'view_err';
                            }
                        } else {
                        }
                    }
                    $tuple = [];
                    $tupleValue = [];
                    if (count($header) != 0) {
                        // if($obj instanceof Atable) die("header = ".var_export($header, true));
                        foreach ($header as $col => $desc) {
                            if (!$val->attributeIsApplicable($col)) {
                                list(
                                    $icon,
                                    $textReason,
                                    $wd,
                                    $hg,
                                ) = $val->whyAttributeIsNotApplicable($col);
                                if (!$wd) {
                                    $wd = 20;
                                }
                                if (!$hg) {
                                    $hg = 20;
                                }
                                $tuple[$col] =
                                    "<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='" .
                                    htmlentities($textReason) .
                                    "'  width='$wd' heigth='$hg'>";
                            } elseif ($val->dataAttributeCanBeDisplayedForUser($col, $objme, 'DISPLAY', $desc)) {
                                if ($desc == 'AAA') {
                                    $tuple['description'] = $val->__toString();
                                } else {
                                    $val_id = $val->getId();
                                    $ord = $val->getMoveOrder();
                                    switch ($desc['TYPE']) {
                                        case 'PK':                                            
                                            $tuple[$col] = $val_id;
                                            break;
                                        case 'DEL':
                                            $col_trans = AfwLanguageHelper::translateKeyword("DELETE", $lang);
                                            $val_id = $val->getId();
                                            $val_class = $val->getMyClass();
                                            $val_currmod = $val->getMyModule();
                                            $lvl = $desc['DEL_LEVEL'];
                                            if (!$lvl) {
                                                $lvl = 2;
                                            }
                                            $userCanDel = $val->userCanDeleteMe($objme);
                                            if ($userCanDel > 0) {
                                                $delete_button_path = $images['delete'];
                                                $lbl = $val->getShortDisplay($lang);
                                                // <a target='del_record' href='main.php?Main_Page=afw_mode_delete.php&cl=$val_class&currmod=$currmod&id=$val_id' >
                                                $tuple[$col_trans] = "<a href='#' here='afw_shwr' id='$val_id' cl='$val_class' md='$val_currmod' lbl='$lbl' lvl='$lvl' class='trash showmany'><img src='$delete_button_path' style='height: 22px !important;'></a>";
                                            } else {
                                                if ($userCanDel == -1) {
                                                    $explanation = "لا يوجد لديك صلاحية لمسح هذا النوع من السجلات";
                                                } else {
                                                    $explanation = "انك تحتاج لصلاحية خاصة لمسح هذا السجل بعينه";
                                                }
                                                $tuple[$col_trans] =
                                                    "<a href='#'><img src='../lib/images/lock.png' data-toggle='tooltip' data-placement='top' title='$explanation'  width='24' heigth='24'></a>";
                                            }
                                            // if($obj instanceof Atable) die("tuple = ".var_export($tuple, true));
                                            break;
                                        case 'MOVE_UP' :
                                                $bswal = $desc['MOVE-QUESTION'];
                                                $col_trans = AfwLanguageHelper::translateKeyword("MOVE_UP", $lang);
                                                $icon_button_path = $images['move-up'];
                                                $tuple[$col_trans] = "<a href='#' id='mover-up-$val_id' here='afw_shwr' oid='$val_id' ord='$ord' cl='$val_class' md='$val_currmod' lbl='$lbl' afworder='$afworder' bswal='$bswal' class='move-up'><img src='$icon_button_path' style='height: 22px !important;'></a>";
                                            break;

                                        case 'MOVE_DOWN' :
                                                $bswal = $desc['MOVE-QUESTION'];
                                                $col_trans = AfwLanguageHelper::translateKeyword("MOVE_DOWN", $lang);
                                                $icon_button_path = $images['move-down'];
                                                $tuple[$col_trans] = "<a href='#' id='mover-down-$val_id' here='afw_shwr' oid='$val_id' ord='$ord' cl='$val_class' md='$val_currmod' lbl='$lbl' afworder='$afworder' bswal='$bswal' class='move-down'><img src='$icon_button_path' style='height: 22px !important;'></a>";
                                        break;
                                             
                                        case 'SHOW':
                                            $col_trans = AfwLanguageHelper::translateKeyword("DISPLAY", $lang);
                                            // die("for col $col and lang=$lang col_trans=$col_trans");
                                            if ($val->canCheckErrors($small_liste, AfwSession::hasOption('CHECK_ERRORS'))) {
                                                if (!$val->isActive()) {
                                                    $data_errors =
                                                        'تم حذفها الكترونيا';
                                                } elseif (
                                                    !$val->isOk(
                                                        $force_check = true
                                                    )
                                                ) {
                                                    $data_errors_arr = $val->getDataErrors(
                                                        $lang
                                                    );
                                                    $data_errors = implode(
                                                        ' / ',
                                                        $data_errors_arr
                                                    );
                                                    if (
                                                        strlen($data_errors) >
                                                        596 or
                                                        count(
                                                            $data_errors_arr
                                                        ) >
                                                        18
                                                    ) {
                                                        $data_errors =
                                                            'أخطاء كثيرة';
                                                        $viewIcon =
                                                            'view_error';
                                                    }
                                                } else {
                                                    $data_errors =
                                                        'لا يوجد أخطاء';
                                                }
                                            } else {
                                                if (!$val->isActive()) {
                                                    $data_errors = 'تم حذفها الكترونيا';
                                                } else {
                                                    $data_errors = 'لم يتم تفعيل التثبت من الأخطاء لهذا الكيان';
                                                }
                                            }
                                            $currstep = $desc["GO-TO-STEP"];
                                            if (!$currstep) $currstep = $val->getDefaultStep();
                                            if (!$currstep) $currstep = 1;
                                            $val_id = $val->getId();
                                            $val_class = $val->getMyClass();
                                            $val_currmod = $val->getMyModule();
                                            $tuple[$col_trans] =
                                                "<a href='main.php?Main_Page=afw_mode_display.php&cl=$val_class&currmod=$val_currmod&id=$val_id&currstep=$currstep' ><img src='../lib/images/$viewIcon.png' width='24' heigth='24' data-toggle='tooltip' data-placement='top' title='" .
                                                htmlentities($data_errors) . // var_export($desc,true).
                                                "'></a>";
                                            break;
                                        case 'EDIT':
                                            $col_trans = AfwLanguageHelper::translateKeyword($col, $lang);
                                            $currstep = $desc["GO-TO-STEP"];
                                            if (!$currstep) $currstep = $val->getDefaultStep();
                                            if (!$currstep) $currstep = 1;
                                            $val_id = $val->getId();
                                            // if(!is_numeric($val_id)) die("val object export = ".var_export($val,true).", val->getId() => $val_id");
                                            $val_class = $val->getMyClass();
                                            $val_currmod = $val->getMyModule();
                                            list(
                                                $canEdit,
                                                $cantEditReason,
                                            ) = $val->userCanEditMe($objme);
                                            if ($canEdit) {
                                                $edit_button_path = $images['modifier'];                                                
                                                $tuple[$col_trans] = "<a href='m.php?mp=ed&cl=$val_class&cm=$val_currmod&id=$val_id&cs=$currstep&clp=$class_origin' class='editme showmany'><img src='$edit_button_path' width='22' heigth='22'></a>";
                                            } else {
                                                $tuple[$col_trans] = "<a href='#'><img src='../lib/images/lock.png'  data-toggle='tooltip' data-placement='top' title='$cantEditReason' width='24' heigth='24'></a>";
                                            }

                                            break;
                                        case 'FK':
                                            if (AfwStructureHelper::isLookupAttribute($val, $col, $desc)) {
                                                $val_decoded = $val->getVal($col);
                                                $tuple[$col] = $val->decode($col) . "<!-- val decoded is $val_decoded -->";
                                            } else {
                                                $obj_col = $val->het($col);
                                                if (empty($desc['CATEGORY'])) {
                                                    if ($obj_col) {
                                                        $tuple[$col] = $obj_col->showMe('retrieve', $lang);
                                                    } else {
                                                        if (
                                                            $desc['EMPTY_IS_ALL'] or
                                                            $desc['FORMAT'] ==
                                                            'EMPTY_IS_ALL'
                                                        ) {
                                                            $all_code = "ALL-$col";
                                                            $return = $val->translate(
                                                                $all_code,
                                                                $lang
                                                            );
                                                            if (
                                                                $return == $all_code
                                                            ) {
                                                                $return = $val->translateOperator(
                                                                    'ALL',
                                                                    $lang
                                                                );
                                                            }
                                                            $tuple[$col] = $return;
                                                        } else {
                                                            $tuple[$col] = '';
                                                        }
                                                    }
                                                } else {
                                                    if (is_object($obj_col)) {
                                                        $tuple[$col] = $obj_col->showMe(
                                                            'retrieve',
                                                            $lang
                                                        );
                                                    } elseif (is_array($obj_col)) {
                                                        $mfk_show_sep =
                                                            $desc['LIST_SEPARATOR'];
                                                        if (!$mfk_show_sep) {
                                                            $mfk_show_sep =
                                                                $desc['MFK-SHOW-SEPARATOR'];
                                                        }
                                                        if (!$mfk_show_sep) {
                                                            $mfk_show_sep =
                                                                "<br>\n";
                                                        }
                                                        //$str  = "Strange returned list of objects !! : ".'<br>';
                                                        $str = '';
                                                        foreach (
                                                            $obj_col
                                                            as $instance
                                                        ) {
                                                            if ($str) {
                                                                $str .= $mfk_show_sep;
                                                            }
                                                            $str .= $instance->showMe(
                                                                'retrieve',
                                                                $lang
                                                            );
                                                        }
                                                        //$str .= var_export($obj_col,true);
                                                        $tuple[$col] = $str;
                                                    } elseif (!$obj_col) {
                                                        if (
                                                            $desc['EMPTY_IS_ALL'] or
                                                            $desc['FORMAT'] ==
                                                            'EMPTY_IS_ALL'
                                                        ) {
                                                            $all_code = "ALL-$col";
                                                            $return = $val->translate(
                                                                $all_code,
                                                                $lang
                                                            );
                                                            if (
                                                                $return == $all_code
                                                            ) {
                                                                $return = $val->translateOperator(
                                                                    'ALL',
                                                                    $lang
                                                                );
                                                            }
                                                            $tuple[$col] = $return;
                                                        } else {
                                                            $tuple[$col] = '';
                                                        }
                                                    } else {
                                                        throw new AfwRuntimeException(
                                                            "strange value for FK field : $col => " .
                                                                var_export(
                                                                    $obj_col,
                                                                    true
                                                                )
                                                        );
                                                    }
                                                }
                                            }
                                            break;
                                        case 'MFK':
                                            $objs = $val->get($col,'object','',false);
                                            
                                            if (!is_array($objs)) {
                                                throw new AfwRuntimeException("How $val => get($col,'object','',false) return " . var_export($objs, true));
                                            }
                                            $nbc = count($objs);
                                            /*
                                            if(($col=="show_field_mfk") and $nbc<2)
                                            {
                                                die("rafik 20240923 : $val => get($col,'object','',false) = ".var_export($objs,true));
                                            }*/
                                            if ($nbc>0) 
                                            {
                                                $mfk_show_sep =
                                                    $desc['LIST_SEPARATOR'];
                                                if (!$mfk_show_sep) {
                                                    $mfk_show_sep =
                                                        $desc['MFK-SHOW-SEPARATOR'];
                                                }
                                                if (!$mfk_show_sep) {
                                                    $mfk_show_sep = "<br>\n";
                                                }
                                                $str_arr = [];
                                                foreach ($objs as $instance) {
                                                    if($instance) $str_arr[] = $instance->getShortDisplay($lang);
                                                    unset($instance);
                                                }
                                                
                                                $tuple[$col] = implode($mfk_show_sep, $str_arr); // ." nbc=".$nbc;
                                                unset($objs);
                                            }
                                            break;
                                        case 'ANSWER':
                                            $tuple[$col] = $val->decode($col);
                                            break;
                                        case 'YN':
                                            // if(($val->id==476) and ($col=="active")) echo("see FORMAT in desc = ".var_export($desc,true));
                                            if ($desc['FORMAT'] == 'icon') {
                                                $onoff = $val->sureIs($col) ? "on" : "off";
                                                list($switcher_authorized, $switcher_title, $switcher_text) = $val->switcherConfig($col, $objme);
                                                if($switcher_authorized)
                                                {
                                                    $switcher_img_style = "";
                                                }
                                                else
                                                {
                                                    $switcher_img_style = "style='opacity: 0.6;'";
                                                }
                                                
                                                $img_onoff = "<img src='../lib/images/$onoff.png' width='30' heigth='20' $switcher_img_style>";                                                

                                                if($switcher_authorized)
                                                {
                                                    $val_class = $val->getMyClass();
                                                    $currm = $val->getMyModule();
                                                    $val_id = $val->id;
                                                    $tuple[$col] = "<span case='1' id='$currm-$val_class-$val_id-$col' oid='$val_id' cl='$val_class' md='$currm' col='$col' ttl='$switcher_title' txt='$switcher_text' class='switcher afw-authorised'>$img_onoff</span>";
                                                }
                                                else
                                                {
                                                    $tuple[$col] = $img_onoff;
                                                }
                                                
                                            } else {
                                                $col_decoded = $val->decode($col);
                                                $tuple[$col] = $col_decoded;
                                            }

                                            // if(($val->id==476) and ($col=="active"))  echo("tuple[$col] = ".$tuple[$col]);

                                            /*
                                             $yn_decoded = $col.strtoupper($col_decoded);
                                             $yn_translated = $val->translate($yn_decoded,$lang);
                                             //die("yn_translated=$yn_translated"); 
                                             if((!$yn_translated) or ($yn_translated==$yn_decoded)) 
                                             {
                                             $yn_decoded = strtoupper($col_decoded);
                                             $yn_translated = $val->translate($yn_decoded,$lang);
                                             }
                                             if((!$yn_translated) or ($yn_translated==$yn_decoded))
                                             {
                                             $yn_decoded = strtoupper($col_decoded);
                                             $yn_translated = $val->translateOperator($yn_decoded,$lang);
                                             }
                                             $tuple[$col] = $yn_translated;*/

                                            break;
                                        case 'ENUM':
                                            $value = $val->getVal($col);
                                            $display_val = $val->decode($col);
                                            if (
                                                $display_val and
                                                $desc['FORMAT-INPUT'] ==
                                                'hzmtoggle'
                                            ) {
                                                //if(!$display_val) $display_val = "...";
                                                // die("key=$attribute, val=$val, display_val=$display_val, HZM-CSS=".$structure["HZM-CSS"]);
                                                $css_arr = AfwStringHelper::afw_explode(
                                                    $desc['HZM-CSS']
                                                );
                                                $css_val =
                                                    $css_arr[$value] .
                                                    '_display';
                                                $tuple[$col] = "<div class='$css_val'>$display_val</div>";
                                            } else {
                                                $tuple[$col] = $display_val;
                                            }
                                            break;
                                        default:
                                            $tuple[$col] = $val->decode($col);
                                            //if($col=="homework") die("$val -> decode($col) = [".$tuple[$col]."]");
                                            break;
                                    }
                                }
                            }
                            $dataValue[$id][$col] = $val->getVal($col);
                            $dataImportance[$col] = $obj->importanceCss($col, $desc);
                        }
                    }
                    if ($val->rowCategoryAttribute()) {
                        list($categoryAttribute, $categoryAttributeCATEGORY) = explode(":", $obj->rowCategoryAttribute());
                        //die("list(attr=$categoryAttribute, cat=$categoryAttributeCATEGORY)");
                        if ($categoryAttributeCATEGORY) {
                            $tuple["ca-".$categoryAttribute] = $val->calc($categoryAttribute);
                            //die("tuple[$categoryAttribute] = ".$tuple[$categoryAttribute]." = $val-->calc($categoryAttribute)");
                        } else
                            $tuple["ca-".$categoryAttribute] = $val->getVal($categoryAttribute);
                    }
                    $data[$id] = $tuple;
                    $isAvail[$id] = $objIsActive;
                    // $count_liste_obj++;
                }
            }
        }

        $header_trad = [];

        foreach ($header as $nom_col => $desc) {
            $trad_col = $trad_erase[$nom_col];
            if (!$trad_col) {
                /*
                $nom_col_short = "$nom_col.short";
                $trad_col_short = $obj->translate($nom_col_short, $lang);
                if ($trad_col_short == $nom_col_short) {
                    $trad_col = $obj->translate($nom_col, $lang);
                }
                else {
                    $trad_col = $trad_col_short;
                }*/
                $trad_col = $obj->getAttributeLabel($nom_col, $lang, true);
            }

            $header_trad[$nom_col] = $trad_col;
        }

        $order_key = $obj->moveColumn();

        //die($obj->getMyClass()." >> nowrap_cols for $obj = ".var_export($obj->nowrap_cols,true));
        list($categoryAttribute, $categoryAttributeCATEGORY) = explode(":", $obj->rowCategoryAttribute());
        list($html, $ids) = self::tableToHtml(
            $data,
            $header_trad,
            $obj->showAsDataTable,
            $isAvail,
            $nowrap_cols,
            $class_table,
            $class_tr1,
            $class_tr2,
            $dataImportance,
            $lang,
            $dir,
            $bigtitle,
            $bigtitle_tr_class,
            $width_th_arr,
            $img_width,
            $rows_by_table,
            $obj->detailModeWidthedTable,
            $categoryAttribute,
            $obj->getCssClassName(),'off', $order_key
        );

        if (!$mode_show_all_records) {
            $message = $obj->translateMessage(
                'only_show_errors_mode_is_activated',
                $lang
            );
            $html .=
                "<div class='alert alert-warning alert-dismissable' role='alert'>$message !</div>" .
                var_export($data, true);
        }

        return [$html, $liste_obj, $ids];
    }

    public static function tableToHtml(
        $data,
        $header_trad,
        $showAsDataTable = false,
        $isAvail = null,
        $nowrap_cols = null,
        $class_table = 'grid',
        $class_tr1 = 'altitem',
        $class_tr2 = 'item',
        $dataImportance = [],
        $lang = '',
        $dir = '',
        $bigtitle = '',
        $bigtitle_tr_class = 'bigtitle',
        $width_th_arr = [],
        $img_width = '',
        $rows_by_table = 0,
        $showWidthedTable = '',
        $row_class_key = '',
        $css_class_name = '',
        $class_td_off = 'off',
        $order_key = ''
    ) {
        //die("dataImportance=".var_export($dataImportance,true));
        global $datatable_on_components,
            $datatable_on,
            $styled_data_arr,
            $datatables_arr;



        if(!$lang) $lang = AfwLanguageHelper::getGlobalLanguage();
        if(!$dir)
        {
            if($lang=="ar") $dir = "rtl";
            else $dir = "ltr";
        }

        $total_cols = [];

        $id_prop = '';
        $html = '';
        $html_header = '';
        if ($showAsDataTable) {
            $datatable_on = true;
            $datatable_on_components[] = $showAsDataTable;
            $id_prop = "id='$showAsDataTable'";
            $class_table = 'display';
            $tab_style = 'width: 100%;';
            //if(!$showWidthedTable) $showWidthedTable = "85%";
        }

        if ($showWidthedTable) {
            $html_header .= "<table style='width: $showWidthedTable;'><tr><td>";
        }

        $html_header .= "<table $id_prop float='right' dir='$dir' class='$class_table' cellpadding='4' cellspacing='3' style='$tab_style'>\n"; // style='background-color: #fff !important;'

        $count_header = count($header_trad);

        if ($count_header > 0) {
            $html_header .= "   <thead>\n";
            if (!$showAsDataTable) {
                if ($bigtitle) {
                    $html_header .= "   <tr class='$bigtitle_tr_class'>\n";
                    $html_header .= "        <td class='col-importance-high' colspan='$count_header'>$bigtitle</td>\n";
                    $html_header .= "   </tr>\n";
                }

                if ($img_width) {
                    $html_header .= "   <tr>\n";
                    $html_header .= "        <td class='col-importance-high' colspan='$count_header'><img src='../lib/images/barre.png' style='width:$img_width' ></td>\n";
                    $html_header .= "   </tr>\n";
                }
            }
            $html_header .= "   <tr>\n";
            foreach ($header_trad as $nom_col => $trad_col) {
                $importance = ($dataImportance and is_array($dataImportance)) ? $dataImportance[$nom_col] : "";
                if ($width_th_arr[$nom_col]) {
                    $width_th = "width='" . $width_th_arr[$nom_col] . "'";
                } else {
                    $width_th = '';
                }

                $html_header .= "      <th class='col-importance-$importance th-$nom_col' $width_th align='center'>$trad_col</th>\n";
            }
            $html_header .= "   </tr>\n";
            $html_header .= "   </thead>\n";
        }

        if ($rows_by_table > 0 and !$showAsDataTable) {
            $html_arr = [];
            $rows_count_table = 0;
            $html = $html_header;
            $ids = '';
            $cl_tr = '';
            $my_class_name = '';
            foreach ($data as $id => $tuple) {
                $row_class_css = $css_class_name;
                if ($row_class_key) {
                    $row_class_key_val = "".$tuple['ca-'.$row_class_key];
                    $row_class_key_val = str_replace("-","_", $row_class_key_val);
                    $row_class_css .= ' '.$row_class_key.' hzm_row_' . $row_class_key_val;
                } else {
                    $row_class_css .= ' hzm_row_std';
                }
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $id;
                //if($nom_col=="sms_sent_date") die("nowrap_cols for $nom_col = ".var_export($nowrap_cols,true));
                $old_cl = $cl_tr;
                if ($cl_tr == $class_tr2) {
                    $cl_tr = $class_tr1;
                } else {
                    $cl_tr = $class_tr2;
                }
                if ($isAvail[$id] == 'inactive') {
                    $cl_tr = $class_td_off;
                } elseif ($isAvail[$id] == 'error') {
                    if ($old_cl == $class_tr2 or $old_cl == 'err') {
                        $cl_tr = 'alterr';
                    } else {
                        $cl_tr = 'err';
                    }
                }
                $html .= "   <tr class='$cl_tr $row_class_css' alt='old_cl=$old_cl'>\n";
                foreach ($header_trad as $nom_col => $trad_col) {
                    $importance = ($dataImportance and is_array($dataImportance)) ? $dataImportance[$nom_col] : "";
                    $type_col = substr($nom_col, 0, 5);
                    if (!$my_class_name) {
                        $my_class_name = 'afw';
                    }
                    $col_class_css =
                        "hzm_head hzm_head_$my_class_name hzm_head_" .
                        $my_class_name .
                        '_' .
                        $type_col .
                        ' hzm_head_' .
                        $my_class_name .
                        '_' .
                        $nom_col;

                    if ($nowrap_cols[$nom_col]) {
                        $nowrap_col = "nowrap='true'";
                    } else {
                        $nowrap_col = '';
                    }
                    $html .=
                        "         <td class='col-importance-$importance $col_class_css' $nowrap_col>" .
                        $tuple[$nom_col] .
                        "</td>\n";
                }
                $html .= "   </tr>\n";

                $rows_count_table++;
                if ($rows_count_table == $rows_by_table) {
                    $html .= "</table><br>\n";
                    $html_arr[] = str_replace(
                        '_XXX_',
                        count($html_arr) + 1,
                        $html
                    );
                    $html = $html_header;
                    $rows_count_table = 0;
                }
            }

            if ($rows_count_table > 0) {
                $html .= "</table><br>\n";
                $html_arr[] = str_replace('_XXX_', count($html_arr) + 1, $html);
            }

            $html = [];

            foreach ($html_arr as $html0) {
                $html[] = str_replace('_NNN_', count($html_arr), $html0);
            }
        } else {
            $html = $html_header;
            $html .= '<tbody>';
            $ids = '';

            $sum_cols_total = [];
            $my_class_name = '';
            $cl_tr = '';
            foreach ($data as $id => $tuple) {
                $row_class_css = $css_class_name;
                if ($row_class_key) {
                    $row_class_key_val = "".$tuple['ca-'.$row_class_key];
                    $row_class_key_val = str_replace("-","_", $row_class_key_val);
                    $row_class_css .= ' csr_'.$row_class_key.' hzm_row_' . $row_class_key_val;
                } else {
                    $row_class_css .= ' hzm_row_std';
                }
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $id;
                $old_cl = $cl_tr;
                if ($cl_tr == $class_tr2) {
                    $cl_tr = $class_tr1;
                } else {
                    $cl_tr = $class_tr2;
                }
                // die("isAvail = ".var_export($isAvail,true));
                if ($isAvail[$id] == 'inactive') {
                    $cl_tr = $class_td_off;
                } elseif ($isAvail[$id] == 'error') {
                    if ($old_cl == $class_tr2 or $old_cl == 'err') {
                        $cl_tr = 'alterr';
                    } else {
                        $cl_tr = 'err';
                    }
                }
                if($order_key) $order = $tuple[$order_key];
                else $order = $tuple["id"];
                $html .= "   <tr id='tr-object-$order' class='$cl_tr $row_class_css' alt='old_cl=$old_cl'>\n";
                foreach ($header_trad as $nom_col => $desc) {
                    $importance = ($dataImportance and is_array($dataImportance)) ? $dataImportance[$nom_col] : "";
                    $nom_col_ltn = AfwStringHelper::arabic_to_latin_chars($nom_col);
                    $type_col = substr($nom_col_ltn, 0, 5);
                    if (!$my_class_name) {
                        $my_class_name = 'afw';
                    }
                    $col_class_css =
                        "hzm_col hzm_col_$my_class_name hzm_col_" .
                        $my_class_name .
                        '_' .
                        $type_col .
                        ' hzm_col_' .
                        $my_class_name .
                        '_' .
                        $nom_col_ltn;
                    //if($nom_col=="sms_sent_date") die("nowrap_cols for $nom_col = ".var_export($nowrap_cols,true));

                    if ($nowrap_cols[$nom_col]) {
                        $nowrap_col = "nowrap='true'";
                    } else {
                        $nowrap_col = '';
                    }

                    if ($styled_data_arr[$nom_col]) {
                        $tuple_copy = $tuple;
                        $data_aff = $styled_data_arr[$nom_col];
                        foreach ($tuple_copy as $colx => $valx) {
                            $data_aff = str_replace(
                                "[$colx]",
                                $valx,
                                $data_aff
                            );
                        }
                        $data_aff = str_replace(
                            '[value]',
                            $tuple[$nom_col],
                            $data_aff
                        );
                    } else {
                        $data_aff = $tuple[$nom_col];
                    }
                    if($nom_col==$order_key)
                    {
                        $td_id = 'order-'.$id;
                    }
                    else
                    {
                        $td_id = $nom_col.'-'.$id;
                    }
                    $html .=
                        "         <td id='$td_id' class='col-importance-$importance $col_class_css' $nowrap_col>" .
                        $data_aff .
                        "</td>\n";
                    if ($total_cols[$nom_col]) {
                        //if($nom_col == "perf_total") die("summing $nom_col : currval = ".$tuple[$nom_col]." data = ".var_export($data,true));
                        if (!$sum_cols_total[$nom_col]) {
                            $sum_cols_total[$nom_col] = 0;
                        }
                        $sum_cols_total[$nom_col] += $tuple[$nom_col];
                    }
                    //else die("not summing $nom_col data = ".var_export($data,true));
                }
                $html .= "   </tr>\n";
            }

            if ($total_cols and count($total_cols) > 0) {
                $html .= "   <tr class='$cl_tr' alt='old_cl=$old_cl'>\n";
                $col_ord = 0;
                foreach ($header_trad as $nom_col => $desc) {
                    if ($styled_data_arr[$nom_col]) {
                        $total_col = $sum_cols_total[$nom_col];
                        $total_disp = $styled_data_arr[$nom_col];
                        /*
                         $tuple_copy = $tuple;
                         foreach($tuple_copy as $colx => $valx)
                         {
                         $data_aff = str_replace("[$colx]", $valx, $data_aff);
                         }
                         */
                        $total_disp = str_replace(
                            '[value]',
                            $total_col,
                            $total_disp
                        );
                    } else {
                        $total_disp = $sum_cols_total[$nom_col];
                    }

                    if ($total_cols[$nom_col]) {
                        $html .=
                            "         <th $nowrap_col>" .
                            $total_disp .
                            "</th>\n";
                    } elseif ($col_ord == 0) {
                        $html .= "         <th>المجموع</th>\n";
                    } else {
                        $html .= "         <th>&nbsp;</th>\n";
                    }
                    $col_ord++;
                }
                $html .= "   </tr>\n";
            }

            $html .= '</tbody>';
            $html .= "</table><br>\n";
            if ($showWidthedTable) {
                $html .= '</td></tr></table>';
            }

            if ($showAsDataTable and !$datatables_arr[$showAsDataTable]) 
            {
                $html .= "<script type=\"text/javascript\">
$(document).ready(function() {
$('#$showAsDataTable').DataTable( {
\"pagingType\": \"full_numbers\"
} );
} );
</script>";
            }
            else
            {
                $html .= "<!-- show As Data Table off -->";
            }
        }

        return [$html, $ids];
    }


    /**
     * showObject
     * @param AFWObject $object
     * @param boolean $color : Optional, display output with color
     * @param boolean $childrens : Optional, specify if we show or not object's childrens
     * @param string $virtuals : Optional, specify list of virtual fields to show
     * @param string $indent : Optional, specify the indent to put in start line
     */
    public static function showObject(
        $object,
        $mode_affichage = 'STR',
        $html_template = '',
        $color = false,
        $childrens = false,
        $decode = true,
        $virtuals = '',
        $indent = '',
        $data_template = null,
        $class_db_structure = null
    ) {
        global $lang;
        //if($object->test_rafik) die("test_rafik 5 start of show ($mode_affichage ...) for : " . $object->getDisplay($lang));
        $mode = strtoupper($mode_affichage);
        if ($mode == 'TPL') {
            return $object->showUsingTpl($html_template);
        } elseif ($mode == 'HTML') {
            if (!empty($html_template)) {
                $template = $html_template;
            } else {
                $template = 'afw_template_default_display.php';
            }
            ob_start();
            $obj = &$object;
            // global $cl, $afw_class_name, $id, $afw_object_id, $mode_edit, $currmod;
            $cl = $object->getMyClass();
            $currmod = $object->getMyModule();
            $afw_class_name = $cl;
            $id = $object->getId();
            $afw_object_id = $id;
            //if($object->test_rafik) die("test_rafik 400 before require $template (cl=$cl,id=$id) obj = ".var_export($obj,true));
            include dirname(__FILE__) . '/../modes/' . $template;
            return ob_get_clean();
        } elseif ($mode == 'EDIT') {
            //$template = 'afw_template_default_edit.php';
            $template = 'afw_mode_edit.php';
            if (!empty($html_template)) {
                $template = $html_template;
            }
            ob_start();
            $obj = &$object;
            global $cl,
                $afw_class_name,
                $id,
                $afw_object_id,
                $mode_edit,
                $currmod;
            $cl = $object->getMyClass();
            $currmod = $object->getMyModule();
            $afw_class_name = $cl;
            $id = $object->getId();
            $afw_object_id = $id;
            if ($object->debugg_curr_step) {
                $currstep = $object->debugg_curr_step;
            }
            $currstep = 4;
            //if($object->test_rafik) die("test_rafik 400 before require $template (cl=$cl,id=$id) obj = ".var_export($obj,true));
            //require_once dirname(__FILE__).'/../modes/afw_edit_motor.php';
            include_once dirname(__FILE__) . '/../modes/' . $template; //."?currstep=".$currstep;
            //return ob_get_clean();
            return $out_scr;
        } elseif ($mode == 'STR') {
            return $object->showMe('', $lang);
        }
    }


    public static function genereMiniBoxTemplate($nameObj, $miniboxTemplateArr, $qeditInputsArr, $qeditTranslationArr, $qeditNum, $templateNum = "", $is_disabled = "")
    {
        $rows_arr = array();

        $curr_row = 0;
        $curr_col = 0;
        $used_hzm_width = 0;
        $idInput = $qeditInputsArr["id" . "_" . $qeditNum];

        foreach ($miniboxTemplateArr as $col => $desc) {
            $remain_hzm_width = 12 - $used_hzm_width;
            if (($desc["HZM-WIDTH"]) > $remain_hzm_width) {
                if ($remain_hzm_width > 0) {
                    if ($curr_col == 0) return "first col $col has hzm size too big : more than remain size = $remain_hzm_width";
                    // put the remain hzm cells in the previous col
                    $rows_arr[$curr_row][$curr_col - 1]["used"] += $remain_hzm_width;
                }

                $curr_row++;
                $used_hzm_width = 0;
                $curr_col = 0;
            }
            $rows_arr[$curr_row][$curr_col] = ["col" => $col, "used" => $desc["HZM-WIDTH"]];
            $used_hzm_width += $desc["HZM-WIDTH"];
            $curr_col++;
        }
        if (($remain_hzm_width > 0) and ($curr_col > 0)) {
            // put the remain hzm cells in the previous col
            $rows_arr[$curr_row][$curr_col - 1]["used"] += $remain_hzm_width;
        }


        $miniBoxTemplate = "<div class='minibox_hzm_panel panel_qedit${is_disabled}'>
                        <div class='label_title_minibox${is_disabled}$templateNum'><div class='minibox_id${is_disabled}'>$idInput</div>$nameObj</div>
                        <table class='table_minibox hzm_table${is_disabled}'>\n";


        $nb_trs = 2 * count($rows_arr);
        foreach ($rows_arr as $curr_row => $rowArr) {
            $miniBoxTemplateSecondRow = "<tr>\n";

            $miniBoxTemplate .= "<tr>\n";

            foreach ($rowArr as $curr_col => $colArr) {
                $colName = $colArr["col"];
                $used = $colArr["used"];
                $trad_col = $qeditTranslationArr[$colName];
                $input = $qeditInputsArr[$colName . "_" . $qeditNum];
                $miniBoxTemplate .= "<th colspan='$used'><b><span class='label_minibox'>$trad_col</span></b></th>\n";

                $miniBoxTemplateSecondRow .= "<td colspan='$used'>$input</td>\n";
            }


            $miniBoxTemplateSecondRow .= "</tr>\n";
            $miniBoxTemplate .= "</tr>\n";
            $miniBoxTemplate .= $miniBoxTemplateSecondRow . "\n\n";
        }
        $miniBoxTemplate .= "</table></div>\n";


        return $miniBoxTemplate;
    }

    public static function quickShowOneOrListOfObjects($objs, $lang = "ar", $newline = "\n<br>")
    {
        $return = '';
        if ($objs and is_object($objs) and ($objs instanceof AFWObject)) {
            $return = $objs->getRetrieveDisplay($lang);
        } elseif ($objs and is_array($objs)) {
            if (count($objs) > 0) {
                $return = '';
                foreach ($objs as $instance) {
                    if ($instance and is_object($instance)) {
                        $return .= $instance->getShortDisplay($lang) . $newline;
                    }
                }
            }
        } else {
            if ($objs) {
                $return = 'strange FK object(s) to show => ' . var_export($objs, true);
                throw new AfwRuntimeException($return);
            }
        }

        return $return;
    }

    /**
     * @param AFWObject $objItem
     * 
     */

    public static function quickShowAttribute($objItem, $col, $lang = "ar", $desc = null, $newline = "\n<br>", $objme=null)
    {
        // $htr_s = hrtime()[1];
        if (!$desc) $desc = AfwStructureHelper::getStructureOf($objItem, $col);
        $return = "???";
        switch ($desc['TYPE']) {
            case 'FK':
                if ($desc['CATEGORY'] === 'ITEMS') {
                    /*
                    $objs = $objItem->get(
                        $col,
                        'object',
                        '',
                        false
                    );*/
                    $return = "no quick show for [items] attribute";
                } elseif (($desc['CATEGORY'] == 'FORMULA') or ($desc['CATEGORY'] == 'SHORTCUT')) {
                    $objs = $objItem->calc($col, true, "object");
                    // die("for categ = formula, obj = $objItem => calc($col,true, object) = ".var_export($objs));
                    $return = self::quickShowOneOrListOfObjects($objs, $lang, $newline);
                } else {

                    // to optimize
                    //@tooptimize $objs = $objItem->het($col);
                    //@tooptimize $return = self::quickShowOneOrListOfObjects($objs, $lang, $newline);
                    $nom_table_fk   = $desc["ANSWER"];
                    $nom_module_fk  = $desc["ANSMODULE"];
                    if (!isset($desc["SMALL-LOOKUP"])) {
                        list($lkp, $issmall) = AfwLoadHelper::getLookupProps($nom_module_fk, $nom_table_fk);
                        $desc["SMALL-LOOKUP"] = ($lkp and $issmall);
                    }
                    $small_lookup  = $desc["SMALL-LOOKUP"];
                    $pk = $desc["ANSWER-PK"];
                    if (!$pk) $pk = "((id))";
                    $val = $objItem->getVal($col);
                    $emptyMessage = $objItem->translate('obj-empty', $lang);
                    $return = AfwLoadHelper::decodeLookupValue($nom_module_fk, $nom_table_fk, $val, $separator = $newline, $emptyMessage, $pk, $small_lookup);
                    if ($val and (!$return)) $return = $val . "<!-- val only -->";
                    /* $htr_e = hrtime()[1];
                    $htr = $htr_e - $htr_s;
                    if($htr < 4000000) $htr = "";*/
                    $return .= "<!-- quickShowAttribute case FK $nom_module_fk / $nom_table_fk -->"; //  / htr = $htr
                }


                break;

            case 'MFK':
                /*
                $objs = $objItem->get($col, 'object', '', false);
                if (count($objs)) 
                {
                    //echo "$col : <br>";
                    //die("rafik 14380523 - ".var_export($objs,true));
                    $str = '';
                    foreach ($objs as $instance) 
                    {
                        if ($instance and is_object($instance)) 
                        {
                            $str .= $instance->getShortDisplay($lang) . $newline;
                        }
                    }
                    $return = $str;
                }
                else $return = "<div class='empty_message'>" . $objItem->translate('obj-empty', $lang) .'</div>';
                */
                $nom_table_fk   = $desc["ANSWER"];
                $nom_module_fk  = $desc["ANSMODULE"];
                if (!isset($structure["SMALL-LOOKUP"])) {
                    list($lkp, $issmall) = AfwLoadHelper::getLookupProps($nom_module_fk, $nom_table_fk);
                    $structure["SMALL-LOOKUP"] = ($lkp and $issmall);
                }
                $small_lookup  = $desc["SMALL-LOOKUP"];
                $pk = $desc["ANSWER-PK"];
                if (!$pk) $pk = "((id))";
                $val = $objItem->getVal($col);
                $emptyMessage = $objItem->translate('obj-empty', $lang);
                $return = AfwLoadHelper::lookupDecodeValues($nom_module_fk, $nom_table_fk, $val, $separator = $newline, $emptyMessage, $pk, $small_lookup);
                break;

            case 'ANSWER':
                $return = $objItem->decode($col);
                break;

            case 'YN':
                if ($desc['FORMAT'] == 'icon') {
                    $onoff = $objItem->sureIs($col) ? "on" : "off";
                    list($switcher_authorized, $switcher_title, $switcher_text) = $objItem->switcherConfig($col, $objme);
                    if($switcher_authorized)
                    {
                        $switcher_img_style = "";
                    }
                    else
                    {
                        $switcher_img_style = "style='opacity: 0.6;'";
                    }
                    
                    $img_onoff = "<img src='../lib/images/$onoff.png' width='30' heigth='20' $switcher_img_style>";

                    if($switcher_authorized)
                    {
                        $val_class = $objItem->getMyClass();
                        $currm = $objItem->getMyModule();
                        $val_id = $objItem->id;
                        $return = "<span id='$currm-$val_class-$val_id-$col' oid='$val_id' cl='$val_class' md='$currm' col='$col' ttl='$switcher_title' txt='$switcher_text' class='switcher afw-authorised'>$img_onoff</span>";
                    }
                    else
                    {
                        $return = $img_onoff;
                    }

                } 
                else 
                {
                    $return = $objItem->showYNValueForAttribute(strtoupper($objItem->decode($col)), $col, $lang);
                }

                break;

            default:
                if ($desc['RETRIEVE-VALUE']) {
                    $return = $objItem->getVal($col);
                } else {
                    $return = $objItem->decode($col);
                }
                break;
        }

        return $return;
    }

    public static function tooltipText($text)
    {
        if ($text) {
            return "<img src='../lib/images/tooltip.png' class='tooltip-icon' data-toggle='tooltip' data-placement='top' title='$text'  width='20' heigth='20'>";
        } else {
            return '';
        }
    }

    /**
     * showVirtual
     * @param AFWObject $object
     * */
    public static function showVirtualAttribute($object, $attribute, $intelligent_category, $value, $id_origin, $class_origin, $module_origin, $lang = "ar", $structure = null, $getlink = false)

    {
        switch ($intelligent_category) {
            case 'VIRTUAL':
                $data_to_display = $object->getVirtual($attribute, 'value', '');
                if ($getlink) {
                    $link_to_display = $object->getLinkForAttribute(
                        $structure['ANSWER'],
                        $value,
                        'display',
                        $structure['ANSMODULE']
                    );
                }
                break;

            case 'ITEMS':
                if ($structure['SHOW_DATA'] != 'EXAMPLE') {
                    $items_objs = $object->get($attribute, 'object', '', false);
                    // if($attribute=="attendanceList") throw new AfwRuntimeException("$object - > get($attribute) = ".var_export($items_objs,true));
                } else {
                    $max_items_to_show = $structure['SHOW_MAX_DATA'];
                    if (!$max_items_to_show) {
                        $max_items_to_show = 600;
                    }
                    $items_objs = $object->get(
                        $attribute,
                        'object',
                        '',
                        false,
                        $max_items_to_show
                    );
                }
                if (strtoupper($structure['FORMAT']) == 'TREE') {
                    reset($items_objs);
                    $first_item = current($items_objs);
                    $data_to_display = '';
                    if ($first_item) {
                        //$objme = AfwSession::getUserConnected();
                        $first_item->deleteIcon = $object->enabledIcon(
                            $attribute,
                            'DELETE',
                            $structure
                        );
                        $first_item->editIcon = $object->enabledIcon(
                            $attribute,
                            'EDIT',
                            $structure
                        );
                        $first_item->viewIcon = $object->enabledIcon(
                            $attribute,
                            'VIEW',
                            $structure
                        );
                        $first_item->attachIcon = $object->enabledIcon(
                            $attribute,
                            'ATTACH',
                            $structure
                        );
                        $first_item->showId = $structure['SHOW-ID'];

                        list(
                            $html_tree,
                            $js_tree,
                            $countNodes,
                        ) = AfwShowHelper::showTree(
                            $attribute . 'tree',
                            $items_objs,
                            $structure['LINK_COL'],
                            $structure['ITEMS_COL'],
                            $structure['FEUILLE_COL'],
                            $structure['FEUILLE_COND_METHOD'],
                            $objme = AfwSession::getUserConnected(),
                            $lang,
                            $structure['ALL_ITEMS'],
                            !$structure['IFRAME_BELOW']
                        );

                        //die("showTree($attribute tree = $countNodes, $html_tree");

                        if (!$countNodes) {
                            if ($structure['EMPTY-ITEMS-MESSAGE']) {
                                $empty_code =
                                    $structure['EMPTY-ITEMS-MESSAGE'];
                            } else {
                                $empty_code = 'atr-empty';
                            }

                            $data_to_display =
                                "<div class='empty_message'>" .
                                $object->translate($empty_code, $lang) .
                                '</div>';
                        } else {
                            $data_to_display =
                                $html_tree .
                                "\n<script>\n$js_tree\n</script>\n\n\n";
                        }
                    }
                } elseif (strtoupper($structure['FORMAT']) == 'CROSSED') {
                    reset($items_objs);
                    $first_item = current($items_objs);
                    $data_to_display = '';
                    if ($first_item) {
                        // $ret_cols_arr = $first_item->getRetrieveCols($mode);

                        $cross_col = $structure['CROSS_COL'];
                        $crossed_field_col =
                            $structure['CROSSED_FIELD_COL'];
                        $crossed_value_col =
                            $structure['CROSSED_VALUE_COL'];
                        $data = [];
                        $index_cross = [];

                        $indexc = 1;
                        $header_trad = [];
                        $header_trad[$cross_col] = $first_item->translate($cross_col, $lang);
                        foreach ($items_objs as $objI) {
                            $cross_val = $objI->showAttribute($cross_col); //$objI->getVal($cross_col);
                            if (!$index_cross[$cross_val]) {
                                $index_cross[$cross_val] = $indexc;
                                $indexc++;
                            }
                            $data[$index_cross[$cross_val] - 1][$cross_col] = $cross_val;

                            if (
                                !$objI->attributeIsApplicable(
                                    $crossed_value_col
                                )
                            ) {
                                list(
                                    $icon,
                                    $textReason,
                                    $wd,
                                    $hg,
                                ) = $objI->whyAttributeIsNotApplicable(
                                    $crossed_value_col
                                );
                                if (!$wd) {
                                    $wd = 20;
                                }
                                if (!$hg) {
                                    $hg = 20;
                                }
                                $data[$index_cross[$cross_val] - 1][$objI->calc($crossed_field_col)] = "<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='$textReason'  width='$wd' heigth='$hg'>";
                            } else {
                                $data[$index_cross[$cross_val] - 1][$objI->calc($crossed_field_col)] = $objI->showAttribute(
                                    $crossed_value_col
                                );
                            }

                            $header_trad[$objI->calc($crossed_field_col)]
                                = $objI->translate($objI->decode($crossed_field_col), $lang);
                        }

                        list($html, $ids) = AfwShowHelper::tableToHtml($data, $header_trad);

                        $data_to_display = $html; //." data=".var_export($data,true)." header=".var_export($header_trad,true)
                    }
                } elseif (strtoupper($structure['FORMAT']) == 'RETRIEVE') {
                    reset($items_objs);
                    $first_item = current($items_objs);
                    $data_to_display = '';
                    if ($first_item) {
                        $first_item->id_origin = $object->getId();
                        $first_item->class_origin = $object->getMyClass();
                        $first_item->module_origin = $object->getMyModule();
                        //$objme = AfwSession::getUserConnected();
                        $first_item->deleteIcon = $object->enabledIcon(
                            $attribute,
                            'DELETE',
                            $structure
                        );
                        $first_item->editIcon = $object->enabledIcon(
                            $attribute,
                            'EDIT',
                            $structure
                        );
                        $first_item->viewIcon = $object->enabledIcon(
                            $attribute,
                            'VIEW',
                            $structure
                        );
                        $first_item->attachIcon = $object->enabledIcon(
                            $attribute,
                            'ATTACH',
                            $structure
                        );
                        $first_item->showId = $structure['SHOW-ID'];
                        //if(isset($structure["ICONS"]) and (!$structure["ICONS"])) die("first_item = ".var_export($first_item,true));

                        // if($attribute=="allEmployeeList") die("structure = ".var_export($structure,true));
                        $hide_retrieve_cols =
                            $structure['DO-NOT-RETRIEVE-COLS'];
                        if (!$hide_retrieve_cols) {
                            $hide_retrieve_cols = [];
                        }
                        if ($structure['ITEM']) {
                            $hide_retrieve_cols[] = $structure['ITEM'];
                        }

                        // if($attribute=="currentRequests") die("structure = ".var_export($structure,true)." first_item->hide_retrieve_cols = ".var_export($first_item->hide_retrieve_cols,true)." structure[DO-NOT-RETRIEVE-COLS]=".var_export($structure["DO-NOT-RETRIEVE-COLS"],true));

                        $force_retrieve_cols =
                            $structure['FORCE-RETRIEVE-COLS'];
                        $nowrap_cols = $structure['NOWRAP-COLS'];

                        $group_retieve_arr = $structure['RETRIEVE-GROUPS']
                            ? $structure['RETRIEVE-GROUPS']
                            : $structure['RETRIEVE_GROUPS'];

                        if (!$group_retieve_arr) {
                            $group_retieve_arr = [];
                            $group_retieve_arr[] = 'display';
                            $no_tabs = true;
                        } else {
                            $no_tabs = false;
                        }

                        $html_display = [];

                        foreach ($group_retieve_arr as $group_retieve) {
                            $first_item->mode_retieve = $group_retieve;
                            $first_item->showAsDataTable =
                                count($items_objs) > 20 ? ($structure['DATA_TABLE'] ? $structure['DATA_TABLE'] : "dtb_$attribute") : '';
                            if ($first_item->showAsDataTable) {
                                $first_item->showAsDataTable .= '_' . $group_retieve;
                            }
                            $options = [];
                            $options['hide_retrieve_cols'] = $hide_retrieve_cols;
                            $options['force_retrieve_cols'] = $force_retrieve_cols;
                            $options['nowrap_cols'] = $nowrap_cols;
                            list(
                                $html_display[$group_retieve],
                                $items_objs,
                                $ids,
                            ) = AfwShowHelper::showManyObj(
                                $items_objs,
                                $first_item,
                                $objme = AfwSession::getUserConnected(),
                                $lang,
                                $options
                            );
                            if ($html_display[$group_retieve] == '') {
                                if ($structure['EMPTY-ITEMS-MESSAGE']) {
                                    $empty_code =
                                        $structure['EMPTY-ITEMS-MESSAGE'];
                                } else {
                                    $empty_code = 'atr-empty';
                                }

                                $html_display[$group_retieve] =
                                    "<div class='empty_message'>" .
                                    $object->translate(
                                        $empty_code,
                                        $lang
                                    ) .
                                    '</div>';
                            }
                        }

                        //if(isset($structure["ICONS"]) and (!$structure["ICONS"])) die("html_display = ".var_export($html_display,true));

                        if ($no_tabs) {
                            $data_to_display = $html_display['display'];
                        } else {
                            $data_to_display =
                                "<ul class='nav nav-tabs'>\n";
                            $div_tabs = "<div class='tab-content'>\n";

                            $itab = 0;
                            foreach (
                                $html_display
                                as $group_retieve =>
                                $html_group_retrieve
                            ) {
                                if ($first_item) {
                                    $group_retieve_label = $first_item->translate(
                                        $group_retieve,
                                        $lang
                                    );
                                } else {
                                    $group_retieve_label = $object->translate(
                                        $group_retieve,
                                        $lang
                                    );
                                }
                                if ($itab == 0) {
                                    $tab_active =
                                        " class='hzm-tab active'";
                                } else {
                                    $tab_active = " class='hzm-tab'";
                                }
                                if ($itab == 0) {
                                    $div_tab_active = ' in active';
                                } else {
                                    $div_tab_active = '';
                                }

                                $data_to_display .= "   <li $tab_active><a data-toggle='tab' href='#tab${attribute}$itab' class='hzm-tab-link'>$group_retieve_label</a></li>\n";
                                $div_tabs .= "<div id='tab${attribute}$itab' class='tab-pane fade $div_tab_active'>\n";
                                $div_tabs .=
                                    $html_group_retrieve . "\n";
                                $div_tabs .= "</div>\n";
                                $itab++;
                            }
                            $data_to_display .= "</ul>\n";
                            $div_tabs .= "</div>\n";
                            $data_to_display .= $div_tabs;
                        }
                        // if(isset($structure["ICONS"]) and (!$structure["ICONS"])) die("data_to_display : <br> ".var_export($data_to_display,true));
                    }
                } elseif (
                    strtoupper($structure['FORMAT']) == 'MINIBOX'
                ) {
                    reset($items_objs);
                    $first_item = current($items_objs);
                    $data_to_display = '';
                    if ($first_item) {

                        $first_item->deleteIcon = $object->enabledIcon(
                            $attribute,
                            'DELETE',
                            $structure
                        );
                        // if($first_item->deleteIcon) die("attribute $attribute has in minibox mode the icon delete, see structure : ".var_export($structure,true));
                        $first_item->editIcon = $object->enabledIcon(
                            $attribute,
                            'EDIT',
                            $structure
                        );
                        // if($first_item->editIcon) die("attribute $attribute has in minibox mode the icon edit, see structure : ".var_export($structure,true));
                        $first_item->viewIcon = $object->enabledIcon(
                            $attribute,
                            'VIEW',
                            $structure
                        );
                        // if($first_item->viewIcon) die("attribute $attribute has in minibox mode the icon view, see structure : ".var_export($structure,true));
                        $first_item->attachIcon = $object->enabledIcon(
                            $attribute,
                            'ATTACH',
                            $structure
                        );

                        $first_item->showId = $structure['SHOW-ID'];

                        $first_item->id_origin = $id_origin;
                        $first_item->class_origin = $class_origin;
                        $first_item->module_origin = $module_origin;
                        $first_item->del_level =
                            $structure['ITEMS_DEL_LEVEL'];

                        list(
                            $data_to_display,
                            $items_objs,
                            $ids,
                        ) = AfwShowHelper::manyMiniBoxes(
                            $items_objs,
                            $first_item,
                            $objme = AfwSession::getUserConnected(),
                            $structure
                        );
                    }

                    if ($data_to_display == '') {
                        if ($structure['EMPTY-ITEMS-MESSAGE']) {
                            $empty_code =
                                $structure['EMPTY-ITEMS-MESSAGE'];
                        } else {
                            $empty_code = 'atr-empty';
                        }

                        $data_to_display =
                            "<div class='empty_message'>" .
                            $object->translate($empty_code, $lang) .
                            '</div>';
                    }
                } elseif (strtoupper($structure['FORMAT']) == 'CUSTOM') {

                    $methodCustom = $structure['CUSTOM_FORMAT'];
                    $data_to_display = '';
                    reset($items_objs);
                    $first_item = current($items_objs);
                    if ($first_item) {
                        $first_item->deleteIcon = $object->enabledIcon(
                            $attribute,
                            'DELETE'
                        );
                        $first_item->editIcon = $object->enabledIcon(
                            $attribute,
                            'EDIT'
                        );
                        $first_item->viewIcon = $object->enabledIcon(
                            $attribute,
                            'VIEW'
                        );
                        $first_item->showId = $structure['SHOW-ID'];
                        $first_item->id_origin = $id_origin;
                        $first_item->class_origin = $class_origin;
                        $first_item->module_origin = $module_origin;
                        $first_item->del_level =
                            $structure['ITEMS_DEL_LEVEL'];
                    }
                    list($data_to_display, $ids) = $object->$methodCustom(
                        $items_objs,
                        $first_item,
                        $objme = AfwSession::getUserConnected(),
                        $structure
                    );

                    if ($data_to_display == '') {
                        if ($structure['EMPTY-ITEMS-MESSAGE']) {
                            $empty_code =
                                $structure['EMPTY-ITEMS-MESSAGE'];
                        } else {
                            $empty_code = 'atr-empty';
                        }

                        $data_to_display =
                            "<div class='empty_message'>" .
                            $object->translate($empty_code, $lang) .
                            '</div>';
                    }
                } else {
                    $data_to_display = '';
                    foreach ($items_objs as $objs_item) {
                        if ($getlink) {
                            $data_to_display .=
                                "<a href=\"" .
                                $object->getLinkForAttribute(
                                    $structure['ANSWER'],
                                    $objs_item->getId(),
                                    'display',
                                    $structure['ANSMODULE']
                                ) .
                                "\" >";
                        }
                        $data_to_display .= (string) $objs_item;
                        if ($getlink) {
                            $data_to_display .= '</a><br/>';
                        }
                    }
                }
                break;
            case 'SHORTCUT':
                $data_to_display = $object->decode($attribute);
                break;
            case 'FORMULA':
                if (!$structure['DISPLAY']) {
                    $structure['DISPLAY'] = $structure['FORMAT'];
                }
                if (strtoupper($structure['DISPLAY']) == 'MINIBOX') {
                    $data_to_display = $object->get($attribute)->showMinibox(
                        $structure['STYLE']
                    );
                    $link_to_display = '';
                } else {
                    $data_to_display = $object->decode($attribute);
                    // if($attribute == "session_status_id") die("$data_to_display = this->decode($attribute)");
                    if ($getlink) {
                        if (
                            !$structure['ANSWER'] or
                            !$structure['ANSMODULE']
                        ) {
                            throw new AfwRuntimeException(
                                " cannot get link for attribute $attribute , ANSWER table and ANSMODULE should be specified"
                            );
                        }
                        $link_to_display = $object->getLinkForAttribute(
                            $structure['ANSWER'],
                            $value,
                            'display',
                            $structure['ANSMODULE']
                        );
                    }
                }
                break;
            default:
                $data_to_display = $object->decode($attribute);
                /*foreach ($temp_obj as $val)
                    $str .= $val."<br/>";
                    $data_to_display = $str;*/
                break;
        }

        return [$data_to_display, $link_to_display];
    }



    /**
     * showDeleteButton
     * @param AFWObject $object
     * */
    public static function showDeleteButton($object, $attribute, $lang = "ar", $structure = null)
    {
        $link_to_display = "";
        $objme = AfwSession::getUserConnected();
        $val_id = $object->getId();
        if ($object->userCanDeleteMe($objme) > 0) {
            $val_class = $object->getMyClass();
            $currmod = $object->getMyModule();
            $lbl = $object->getDisplay($lang);
            $lvl = $structure['DEL_LEVEL'];
            if (!$lvl) {
                $lvl = 2;
            }
            /*
            if ($attribute == 'atr') {
                die('structure = ' . var_export($structure, true));
            }*/
            //$data_to_display = "<a target='popup' href='main.php?Main_Page=afw_mode_delete.php&popup=1&id_origin=$id_origin&class_origin=$class_origin&module_origin=$module_origin;&cl=$val_class&currmod=$currmod&id=$val_id' ><img src='../lib/images/delete.png' width='24' heigth='24'></a>";
            $data_to_display = "<a href='#' here='afw_shwr' id='$val_id' cl='$val_class' md='$currmod' lbl='$lbl' lvl='$lvl' class='trash afw-authorised'><img id='del_from_mfk_${val_id}_$attribute' src='../lib/images/trash.png' width='24' heigth='24'></a>";
        } else {
            $data_to_display = "<img id='del_not_authorised_" . $val_id . "_" . $attribute . "' src='../lib/images/lockme.png' width='24' heigth='24'></a>";
        }

        return [$data_to_display, $link_to_display];
    }


    /**
     * showDisplayButton
     * @param AFWObject $object
     * */
    public static function showDisplayButton($object, $attribute, $lang = "ar", $structure = null)
    {
        $val_class = $object->getMyClass();
        $link_to_display = "";
        $data_to_display = "";
        $val_id = $object->getId();
        $currmod = $object->getMyModule();
        if ($structure['LABEL']) {
            $my_label = $structure['LABEL'];
        }
        if ($structure['ICON']) {
            $my_icon = $structure['ICON'];
        }
        if (!$my_icon) {
            $my_icon = 'view_ok';
        }
        if (!$my_label) {
            $my_label = "<img src='../lib/images/$my_icon.png' width='24' heigth='24'>";
        }
        if ($structure['TARGET']) {
            $target = "target='" . $structure['TARGET'] . "'";
        }

        $data_to_display = "<a $target href='m.php?mp=ds&cl=$val_class&cm=$currmod&id=$val_id' >$my_label</a>";
        return [$data_to_display, $link_to_display];
    }

    /**
     * @param AFWObject $object
     * 
     */

    public static function showFK($object, $attribute, $value, $lang = "ar", $structure = null, $getlink = false, $debugg=false)
    {
        // $val_class = $object->getMyClass();
        $link_to_display = "";
        $data_to_display = "";

        if ($value) {
            if (!$structure['DISPLAY']) {
                $structure['DISPLAY'] = $structure['FORMAT'];
            }
            if (
                strtoupper($structure['DISPLAY']) == 'SHOW' and
                $value > 0
            ) {
                $valObj = $object->get($attribute);
                if ($valObj) {
                    $data_to_display = $valObj->showMe(
                        $structure['STYLE']
                    );
                    if($debugg) $data_to_display .= " from valObj->showMe";
                } else {
                    $data_to_display = '';
                }
                $link_to_display = '';
            } elseif (strtoupper($structure['DISPLAY']) == 'MINIBOX') {
                $valObj = $object->get($attribute);
                if ($valObj) {
                    $data_to_display = $valObj->showMinibox(
                        $structure['STYLE']
                    );
                    if($debugg) $data_to_display .= " from valObj->showMinibox";
                } else {
                    $data_to_display = '';
                }
                $link_to_display = '';
            } elseif (strtoupper($structure['DISPLAY']) == 'SHORT') {
                $valObj = $object->get($attribute);
                if ($valObj) {
                    $data_to_display = $valObj->getShortDisplay($lang);
                    if($debugg) $data_to_display .= " from valObj->getShortDisplay";
                } else {
                    $data_to_display = '';
                }
                $link_to_display = '';
            } else {
                $data_to_display = $object->decode($attribute);
                if($debugg) $data_to_display .= " from object->decode($attribute)";
                // if(($attribute == "cher_id") and (!contient(trim(strtolower($data_to_display)),"<img"))) die($object->getDisplay("ar")."rafik::data_to_display=$data_to_display");
                // if(($attribute == "cher_id") and (!trim($data_to_display))) die($object->getDisplay("ar")."->decode($attribute) empty ->getVal($attribute) = ".$object->getVal($attribute));
                if ($getlink) {
                    $link_to_display = $object->getLinkForAttribute(
                        $structure['ANSWER'],
                        $value,
                        'display',
                        $structure['ANSMODULE'],
                        false,
                        $getlink
                    );
                }
            }
        } else {
            $data_to_display = '';
            if ($structure['EMPTY_IS_ALL']) {
                $all_code = "ALL-$attribute";
                $data_to_display = $object->translate($all_code, $lang);
                if ($data_to_display == $all_code) {
                    $data_to_display = $object->translateOperator(
                        'ALL',
                        $lang
                    );
                }
            }
        }
        return [$data_to_display, $link_to_display];
    }
    /**
     * showEditButton
     * @param AFWObject $object
     * */
    public static function showEditButton($object, $attribute, $class_origin, $lang = "ar", $structure = null)
    {
        $val_class = $object->getMyClass();
        $link_to_display = "";
        $data_to_display = "";

        $val_id = $object->getId();
        $currmod = $object->getMyModule();
        if ($structure['LABEL']) {
            $my_label = $structure['LABEL'];
        }
        if ($structure['ICON']) {
            $my_icon = $structure['ICON'];
        }
        if (!$my_icon) {
            $my_icon = 'modifier';
        }
        if (!$my_label) {
            $my_label = "<img src='../lib/images/$my_icon.png' width='24' heigth='24'>";
        }
        if ($structure['TARGET']) {
            $target = "target='" . $structure['TARGET'] . "'";
        }

        $data_to_display = "<a $target href='m.php?mp=ed&cl=$val_class&cm=$currmod&id=$val_id&clp=$class_origin' >$my_label</a>";

        return [$data_to_display, $link_to_display];
    }

    /**
     * showEnum
     * @param AFWObject $object
     * */
    public static function showEnum($object, $attribute, $value, $lang = "ar", $structure = null)
    {
        // $val_class = $object->getMyClass();
        $link_to_display = "";
        $data_to_display = "";

        $val = $value;
        $display_val = $object->decode($attribute);
        if ($display_val and $structure['FORMAT-INPUT'] == 'hzmtoggle') {
            //if(!$display_val) $display_val = "...";
            // die("key=$attribute, val=$val, display_val=$display_val, HZM-CSS=".$structure["HZM-CSS"]);
            $css_arr = AfwStringHelper::afw_explode($structure['HZM-CSS']);
            $css_val = $css_arr[$val];
            $data_to_display = "<div class='$css_val'>$display_val</div>";
        } else {
            $data_to_display = $display_val;
        } // ." ==> ".$structure["FORMAT-INPUT"]

        return [$data_to_display, $link_to_display];
    }


    /**
     * xxxxx
     * @param AFWObject $object
     * */
    public static function mergeDisplayWithLinks($data_to_display, $link_to_display, $structure, $val_class, $mfk_show_sep = "", $key="")
    {
        if (!is_array($data_to_display)) {
            $data_to_display_arr = [];
            $data_to_display_arr[] = $data_to_display;
            $link_to_display_arr = [];
            $link_to_display_arr[] = $link_to_display;
        } else {
            $data_to_display_arr = $data_to_display;
            $link_to_display_arr = $link_to_display;
        }

        $disp_attr = '';

        if (!$mfk_show_sep) $mfk_show_sep = $structure['LIST_SEPARATOR'];
        if (!$mfk_show_sep) $mfk_show_sep = $structure['MFK-SHOW-SEPARATOR'];
        if (!$mfk_show_sep) $mfk_show_sep = "<br>\n";

        foreach ($data_to_display_arr as $ii => $data_to_display_item) {
            if ($disp_attr) {
                $disp_attr .= $mfk_show_sep;
            }
            $disp_attr .= $link_to_display_arr[$ii] ? '<a class=\'afw cl_' . $val_class . '\' href="' . $link_to_display_arr[$ii] . '">'
                : '';
            $disp_attr .= $data_to_display_arr[$ii];
            $disp_attr .= $link_to_display_arr[$ii] ? '</a>' : '';
        }

        if ($disp_attr and $structure['TITLE_AFTER']) {
            $disp_attr .= ' ' . $structure['TITLE_AFTER'];
        }

        // if($key == "response_templates") throw new AfwRuntimeException("For key $key : AfwShowHelper::mergeDisplayWithLinks($data_to_display, $link_to_display, $structure, $val_class) = $disp_attr");

        return $disp_attr;
    }

    /**
     * showMFK
     * @param AFWObject $object
     * */
    public static function showMFK($object, $attribute, $lang = "ar", $structure = null, $getlink = false)
    {
        $temp_obj = $object->get($attribute, 'object', '', false);
        
        // if($attribute=="attendanceList") throw new AfwRuntimeException("$object - > get($attribute) = ".var_export($temp_obj,true));
        if (!$structure) {
            $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        }

        if($structure["CATEGORY"]=="SHORTCUT")
        {
            if (!$temp_obj) $temp_obj = []; 
        }


        if (!is_array($temp_obj)) 
        {
            $cls00 = get_class($object);
            throw new AfwRuntimeException("[$cls00]->get($attribute, object) returned non array type => ".var_export($temp_obj,true));
        }

        if (strtoupper($structure['FORMAT']) == 'RETRIEVE') {
            reset($temp_obj);
            $first_item = current($temp_obj);
            if ($first_item) {
                if (!isset($structure['ICONS']) or $structure['ICONS']) {
                    // DELETE-ICON is not allowed for MFK as the items are not owned by this object (not like ITEMS)
                    if ($structure['EDIT-ICON']) {
                        $first_item->editIcon = $structure['EDIT-ICON'];
                    }
                    if (
                        !isset($structure['VIEW-ICON']) or
                        $structure['VIEW-ICON']
                    ) {
                        $first_item->viewIcon = true;
                    }
                    if ($structure['SHOW-ID']) {
                        $first_item->showId = true;
                    }
                }

                $objme = AfwSession::getUserConnected();

                $options = [];
                $options['hide_retrieve_cols'] =
                    $structure['DO-NOT-RETRIEVE-COLS'];
                $options['force_retrieve_cols'] =
                    $structure['FORCE-RETRIEVE-COLS'];
                $options['nowrap_cols'] = $structure['NOWRAP-COLS'];

                list($data_to_display) = AfwShowHelper::showManyObj(
                    $temp_obj,
                    $first_item,
                    $objme,
                    $lang,
                    $options
                ); //todo ici il faut utiliser un mode a developper qui n'affiche pas les boutons edit/delete
                $link_to_display = '';
                //die("rafik : [$data_to_display] ".var_export($temp_obj,true));
            }
        } else {
            unset($data_to_display);
            unset($link_to_display);
            $data_to_display = [];
            $link_to_display = [];
            foreach ($temp_obj as $id => $val) {
                // if(!is_object($val)) throw new AfwRuntimeException("strang non object in mfk array ".var_export($temp_obj,true));
                if (is_object($val)) {
                    $data_to_display[$id] = $val->getDisplay($lang);
                    if ($getlink) {
                        $link_to_display[$id] = $object->getLinkForAttribute(
                            $structure['ANSWER'],
                            $val->getId(),
                            'display',
                            $structure['ANSMODULE']
                        );
                    }
                }
            }

            //if($attribute=="arole_mfk") die("data_to_display ($attribute) = ".var_export($data_to_display));
        }

        return [$data_to_display, $link_to_display];
    }
}

