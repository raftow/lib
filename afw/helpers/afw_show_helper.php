<?php

class AfwShowHelper
{
    // @todo : rafik : we need here to add attribute for tables having grouped fields (FGROUP) to say
    // what fgroup view we want to see in many objects table view attribute
    // desc["FGROUP-VIEW"] : "[special fgroup]" => to see only columns of this special fgroup,
    //                       "DEFAULT" => by default to see all retieve columns,
    //                       "ALL-BY-TAB" => to see all retieve columns but splitted into tabs each fgroup in a special tab,
    // it is very useful for afield for example which have ITEMS attribute in atable but we can't see all attributes (too much)

    public static function showMany($obj, $cols, $objme, $options = [])
    {
        foreach ($options as $option => $option_value) {
            ${ $option} = $option_value;
        }

        $liste_obj = $obj->loadMany();

        $arr_col = explode(',', $cols);

        return self::showManyObj($liste_obj, $obj, $objme, $options);
    }

    public static function manyMiniBoxes(
        $liste_obj,
        $obj,
        $objme,
        $structure,
        $options = [],
        $public_show = false
        )
    {
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
            ${ $option} = $option_value;
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
        }
        else {
            $mode_force_cols = true;
        }

        // die("getMiniBoxCols = ".var_export($arr_col,true));

        $cols_minibox = [];
        $data = [];
        
        $report_arr = [];
        $isAvail = [];
        $dataImportance = [];
        

        if (count($arr_col) == 0) {
            $obj->throwError('afw-shower error : no mini-box cols');
        }

        foreach ($arr_col as $cc => $nom_col) {
            if ($public_show) {
                $desc = AfwStructureHelper::getStructureOf($obj,$nom_col);
            }
            else {
                $desc = $obj->keyIsToDisplayForUser($nom_col, $objme);
            }
            if ($desc) {
                if ($nom_col != $obj->getPKField() or $obj->showId) {
                    // if($nom_col != $obj->getPKField()) die("keyIsToDisplayForUser($nom_col) = ".var_export($desc,true));
                    $cols_minibox[$nom_col] = $desc;
                }
            }
            else {
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
            $header = & $cols_minibox;
        }
        else {
            $header = ['description' => 'AAA'];
        }

        if (!$mode_force_cols) {
            $del_level = $obj->del_level;
            $show_as = 'SHOW-AS-ICON';

            if ($obj->viewIcon) {
                $header['عرض'] = [
                    'CODE' => 'view',
                    'TYPE' => 'SHOW',
                    $show_as => true,
                ];
            }
            if ($obj->editIcon) {
                $header['تعديل'] = [
                    'CODE' => 'edit',
                    'TYPE' => 'EDIT',
                    $show_as => true,
                ];
            }
            if ($obj->deleteIcon) {
                $header['حذف'] = [
                    'CODE' => 'del',
                    'TYPE' => 'DEL',
                    $show_as => true,
                    'DEL_LEVEL' => $del_level,
                ];
            }
            if ($obj->attachIcon) {
                $header['مرفقات'] = [
                    'CODE' => 'attach',
                    'TYPE' => 'ATTACH',
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

            if ($public_show or($objme and $objme->iCanDoOperationOnObjClass($liste_obj[$id], 'display'))) 
            {
                $objIsActive = $liste_obj[$id]->isActive();
                $obj_class = $liste_obj[$id]->getMyClass();
                $obj_currmod = $liste_obj[$id]->getMyModule();

                $tuple = [];
                if (count($header) != 0) {
                    $tuple['description'] = $liste_obj[$id]->__toString();
                    foreach ($header as $col => $desc) {
                        if ($desc == 'AAA') {
                        // $tuple["description"] = $liste_obj[$id]->__toString();
                        }
                        elseif ($col == 'عرض') {
                            $tuple[
                                'عرض'
                                ] = "<a $target href='main.php?Main_Page=afw_mode_display.php&popup=&cl=$obj_class&currmod=$obj_currmod&id=$id' ><img src='../lib/images/view_ok.png' width='24' heigth='24'></a>";
                        }
                        elseif ($col == 'تعديل') {
                            $tuple[
                                'تعديل'
                                ] = "<a target=\"_new\" href='main.php?Main_Page=afw_mode_edit.php&popup=&cl=$obj_class&currmod=$obj_currmod&id=$id' ><img src='../lib/images/square.png' width='24' heigth='24'></a>";
                        }
                        elseif ($col == 'مرفقات') {
                            $attach_url = $liste_obj[$id]->getAttachUrl();
                            $tuple[
                                'مرفقات'
                                ] = "<a target=\"_new\" href='$attach_url' ><img src='../lib/images/attach.png' width='24' heigth='24'></a>";
                        }
                        elseif ($col == 'حذف') {
                            $val_id = $liste_obj[$id]->getId();
                            $val_class = $liste_obj[$id]->getMyClass();
                            $val_currmod = $liste_obj[$id]->getMyModule();
                            $lbl = $liste_obj[$id]->getDisplay($lang);
                            $lvl = $desc['DEL_LEVEL'];
                            if (!$lvl) {
                                $lvl = 2;
                            }
                            if ($liste_obj[$id]->userCanDeleteMe($objme) > 0) {
                                // <a target='del_record' href='main.php?Main_Page=afw_mode_delete.php&cl=$val_class&currmod=$currmod&id=$val_id' >
                                $tuple[
                                    $col
                                    ] = "<a href='#' here='afw_shwr' id='$val_id' cl='$val_class' md='$val_currmod' lbl='$lbl' lvl='$lvl' div_to_del='${obj_table}${id}_minibox_container' class='trash manyminiboxes'><img src='../lib/images/delete-button.png' style='height: 22px !important;'></a>";
                                $tuple['del_status'] = 'OK';
                            }
                            else {
                                $tuple[$col] =
                                    "<a href='#'><img src='../lib/images/lock.png' data-toggle='tooltip' data-placement='top' title='لا يسمح في الوضع الحالي بمسح هذا السجل' width='24' heigth='24' ></a>";
                                $tuple['del_status'] = 'locked';
                            }
                        }
                        else {
                            $tuple[$col] = $liste_obj[$id]->showAttribute(
                                $col,
                                $desc
                            );
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
            else 
            {
                if ($structure['MINIBOX-HEADER']) 
                {
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
                    }
                    else {
                        $status_collapsed = 'collapsed';
                        $collapse_in = '';
                        $is_expanded = 'false';
                    }

                    if ($is_ok_arr[$id]) {
                        $obj_i_status = 'ok';
                        $errors_html = '';
                    }
                    else {
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
                }
                else 
                {
                    $html .= "<div id='${obj_table}${id}_minibox_container' class='hzm_${obj_table}_container hzm_minibox_container'>";
                }

                $html .= $html_header;
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $id;
                if ($cl_tr == $class_tr2) {
                    $cl_tr = $class_tr1;
                }
                else {
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
                    }
                    elseif ($desc['SHOW-AS-ROW']) {
                        $html .= "<br><table class='simple_grid'><tr>";
                        $html .= "   <th><span class='titre_0'>$trad_col</span></th>\n";
                        $html .= "   <td style='background-color:#fff;'><p style='padding-top: 0px;'>$data_col</p></td>\n";
                        $html .= '</tr></table>';
                    //<div style='padding-left: 10px;width:180px; float: left'></div>
                    }
                    else {
                        if (
                        $desc['SIZE'] == 'AEREA' or
                        $desc['SIZE'] == 'AREA' or
                        $desc['CATEGORY'] == 'ITEMS' or 
                        $desc['SUB-CATEGORY'] == 'ITEMS' 
                        ) {
                            $inputarea = 'inputarea';
                        }
                        else {
                            $inputarea = 's'.$desc['SIZE'].' c'.$desc['CATEGORY'].' b'.$desc['SUB-CATEGORY'];
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
                }
                else {
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
        $iframe_height = 600)
    {
        global $treePlugin;

        if ($treePlugin == 'jqtree') {
            //die(var_export($itemsList,true));
            $html = "<div id=\"$tree_id\"></div>";
            $js = "var data_$tree_id = [\n";
            $js_items = [];
            foreach ($itemsList as $itemId => $itemObj) {
                $item_parent_id = intval($itemObj->getVal($link_col));
                if (!$item_parent_id or $all_items) {
                    $js_item = AfwHtmlHelper::toJsArray($itemObj,
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
        }
        else {
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
            }
            else {
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
        v_url = \"main.php?My_Module=pag&Main_Page=hzm_view.php&popup=1&a=\"+data.selected[0];
        console.log(v_url);
        console.log(\$('#view_$tree_id').attr(\"id\"));
        \$('#view_$tree_id').attr(\"src\", v_url);
        });

        });";
        }
        //die($js);
        return [$html, $js, $countNodes];
    }

    public static function showManyObj($liste_obj, $obj, $objme, $options = [])
    {
        $arr_col = 0;
        $trad_erase = [];
        $limit = '';
        $order_by = '';
        $optim = true;
        $class_table = 'grid';
        $class_tr1 = 'altitem';
        $class_tr2 = 'item';
        $class_td_off = 'off';
        $lang = 'ar';
        $dir = 'rtl';
        $bigtitle = '';
        $bigtitle_tr_class = 'bigtitle';
        $width_th_arr = [];
        $img_width = '';
        $rows_by_table = 0;
        $hide_retrieve_cols = null;
        $force_retrieve_cols = null;
        $cl_tr = '';

        // if($options and (count($options)>0)) AFWRoot::dd("rafik options not empty");

        foreach ($options as $option => $option_value) {
            ${ $option} = $option_value;
        // if($option == "hide_retrieve_cols") AFWRoot::dd("رفيق قل اللهم لا سهل الا ما جعلته سهلا : option hide_retrieve_cols found in ".var_export($options,true));
        }

        // if($options and (count($options)>0)) AFWRoot::dd("rafik look اللهم لا سهل الا ما جعلته سهلا : hide_retrieve_cols is ".var_export($hide_retrieve_cols,true)." where options is ".var_export($options,true));

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

        $mode = 'display';
        if ($obj->mode_retieve) {
            $mode = $obj->mode_retieve;
        }

        if (!$arr_col) {
            $arr_col = $obj->getRetrieveCols(
                $mode,
                'ar',
                $all = false,
                $type = 'all',
                $debugg = true,
                $hide_retrieve_cols,
                $force_retrieve_cols
            );
            $mode_force_cols = false;
        }
        else {
            $mode_force_cols = true;
        }

        // debugg some column hidden and should not
//if($obj instanceof CrmEmployee) die("arr_col = ".var_export($arr_col,true)." force_retrieve_cols :".var_export($force_retrieve_cols,true));

        // debugg some column not hidden and should be
/*
if($obj instanceof Request) 
{
AFWRoot::dd("getRetrieveCols($mode) with hide_retrieve_cols :".var_export($hide_retrieve_cols,true)." has returned arr_col = ".var_export($arr_col,true)." where options :".var_export($options,true));        
}
*/

        //if($mode=="field_rules") die("arr_col = ".var_export($arr_col,true));

        $cols_retrieve = [];
        $data = [];
        $dataValue = [];
        $isAvail = [];

        if (count($arr_col) == 0) {
            throw new RuntimeException("afw-shower error : no retrieve cols");
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
                    //@doc / afw / attribute-type / ITEMS / retrieve-cols / note / if you want to show Id in retrieve cols define in the items answer class constructor $this->setOptionValue("showId",true); or define in the application_config.php file the param [answer_class]_showId => true, ex : practice_showId => true,
                    $cols_retrieve[$nom_col] = $desc;
                }
                else {
                //$mcls = $obj->getMyClass();
//$cols_retrieve[$nom_col."_debugg"] = "obj->showId=".$obj->showId." and class_config_exists[${mcls}_showId] = ".AfwSession::class_config_exists($mcls, "showId");
                }
            }
            else {
                $obj->throwError(
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
            $header = & $cols_retrieve;
        }
        else {
            $header = ['description' => 'AAA'];
        }

        if (!$mode_force_cols) {
            $del_level = $obj->del_level;
            if ($obj->viewIcon) {
                $header['عرض'] = ['TYPE' => 'SHOW'];
            }
            if ($obj->deleteIcon) {
                $header['حذف'] = ['TYPE' => 'DEL', 'DEL_LEVEL' => $del_level];
            }
            if ($obj->editIcon) {
                $header['تعديل'] = ['TYPE' => 'EDIT'];
            }
        }
        //else $obj::lightSafeDie("mode_force_cols");
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
        $small_liste = ($liste_obj_count<30);
        $mode_show_all_records =
            ($small_liste or // عدد قليل من الكينات للعرض
            !AfwSession::hasOption('BIG_DATA_SHOW_ONLY_ERRORS') or // خيار اظهار الأخطاء فقط في حال بيانات كثيرة غير مفعل
            !$obj->showRetrieveErrors);
        $j = 0;

        foreach ($liste_obj as $id => $val) 
        {
            $j++;
            if (is_object($val) and AfwUmsPagHelper::userCanDoOperationOnObject($val,$objme, 'display')) 
            {
                // we force errors test only if we are not in mode mode_show_all_records
                $check_errors_needed_in_object = $val->canCheckErrors($small_liste, AfwSession::hasOption('CHECK_ERRORS'));
                $force_test_errors =
                    (!$mode_show_all_records or $check_errors_needed_in_object);
                $val_isOk = $val->isOk($force_test_errors); //

                if ($mode_show_all_records or !$val_isOk) {
                    $objIsActive = $val->isActive() ? 'active' : 'inactive';
                    $viewIcon = $val->isActive() ? 'view_me' : 'view_off';
                    // die("showManyObj, val = ".var_export($val,true));
                    if ($val->isActive()) {
                        if ($check_errors_needed_in_object) {
                            if ($val_isOk) {
                                $objIsActive = 'active';
                                $viewIcon = 'view_ok';
                            //die("$val_isOk = $val ->isOk($mode_show_all_records)");
                            }
                            else {
                                $objIsActive = 'error';
                                $viewIcon = 'view_err';
                            }
                        }
                        else {
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
                            }
                            elseif (
                            $val->dataAttributeCanBeDisplayedForUser(
                            $col,
                            $objme,
                            'DISPLAY',
                            $desc
                            )
                            ) {
                                if ($desc == 'AAA') {
                                    $tuple['description'] = $val->__toString();
                                }
                                else {
                                    $lbl = $val->getShortDisplay($lang);
                                    switch ($desc['TYPE']) {
                                        case 'PK':
                                            $val_id = $val->getId();
                                            $tuple[$col] = $val_id;
                                            break;
                                        case 'DEL':
                                            $val_id = $val->getId();
                                            $val_class = $val->getMyClass();
                                            $val_currmod = $val->getMyModule();
                                            $lvl = $desc['DEL_LEVEL'];
                                            if (!$lvl) {
                                                $lvl = 2;
                                            }
                                            if (
                                            $val->userCanDeleteMe($objme) >
                                            0
                                            ) {
                                                // <a target='del_record' href='main.php?Main_Page=afw_mode_delete.php&cl=$val_class&currmod=$currmod&id=$val_id' >
                                                $tuple[
                                                    $col
                                                    ] = "<a href='#' here='afw_shwr' id='$val_id' cl='$val_class' md='$val_currmod' lbl='$lbl' lvl='$lvl' class='trash showmany'><img src='../lib/images/delete-button.png' style='height: 22px !important;'></a>";
                                            }
                                            else {
                                                $tuple[$col] =
                                                    "<a href='#'><img src='../lib/images/lock.png' data-toggle='tooltip' data-placement='top' title='لا يسمح في الوضع الحالي بمسح هذا السجل'  width='24' heigth='24'></a>";
                                            }
                                            // if($obj instanceof Atable) die("tuple = ".var_export($tuple, true));
                                            break;
                                        case 'SHOW':
                                            if ($val->canCheckErrors($small_liste, AfwSession::hasOption('CHECK_ERRORS'))) 
                                            {
                                                if (!$val->isActive()) {
                                                    $data_errors =
                                                        'تم حذفها الكترونيا';
                                                }
                                                elseif (
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
                                                }
                                                else {
                                                    $data_errors =
                                                        'لا يوجد أخطاء';
                                                }
                                            }
                                            else {
                                                if (!$val->isActive()) {
                                                    $data_errors = 'تم حذفها الكترونيا';
                                                }
                                                else {
                                                        $data_errors = 'لم يتم تفعيل التثبت من الأخطاء لهذا الكيان';
                                                }
                                            }

                                            $val_id = $val->getId();
                                            $val_class = $val->getMyClass();
                                            $val_currmod = $val->getMyModule();
                                            $tuple[$col] =
                                                "<a href='main.php?Main_Page=afw_mode_display.php&cl=$val_class&currmod=$val_currmod&id=$val_id' ><img src='../lib/images/$viewIcon.png' width='24' heigth='24' data-toggle='tooltip' data-placement='top' title='" .
                                                htmlentities($data_errors) .
                                                "'></a>";
                                            break;
                                        case 'EDIT':
                                            $currstep = $val->getDefaultStep();
                                            if(!$currstep) $currstep = 1;
                                            $val_id = $val->getId();
                                            // if(!is_numeric($val_id)) die("val object export = ".var_export($val,true).", val->getId() => $val_id");
                                            $val_class = $val->getMyClass();
                                            $val_currmod = $val->getMyModule();
                                            list(
                                                $canEdit,
                                                $cantEditReason,
                                            ) = $val->userCanEditMe($objme);
                                            if ($canEdit) {
                                                $tuple[
                                                    $col
                                                    ] = "<a href='main.php?Main_Page=afw_mode_edit.php&cl=$val_class&currmod=$val_currmod&id=$val_id&currstep=$currstep' class='editme showmany'><img src='../lib/images/modifier.png' width='17' heigth='17'></a>";
                                            }
                                            else {
                                                $tuple[
                                                    $col
                                                    ] = "<a href='#'><img src='../lib/images/lock.png'  data-toggle='tooltip' data-placement='top' title='$cantEditReason' width='24' heigth='24'></a>";
                                            }

                                            break;
                                        case 'FK':
                                            $obj_col = $val->het($col);
                                            if (empty($desc['CATEGORY'])) {
                                                if ($obj_col) {
                                                    $tuple[
                                                        $col
                                                        ] = $obj_col->showMe(
                                                        'retrieve',
                                                        $lang
                                                    );
                                                }
                                                else {
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
                                                    }
                                                    else {
                                                        $tuple[$col] = '';
                                                    }
                                                }
                                            }
                                            else {
                                                if (is_object($obj_col)) {
                                                    $tuple[
                                                        $col
                                                        ] = $obj_col->showMe(
                                                        'retrieve',
                                                        $lang
                                                    );
                                                }
                                                elseif (is_array($obj_col)) {
                                                    $mfk_show_sep =
                                                        $desc['LIST_SEPARATOR'];
                                                    if (!$mfk_show_sep) {
                                                        $mfk_show_sep =
                                                            $desc[
                                                            'MFK-SHOW-SEPARATOR'
                                                            ];
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
                                                }
                                                elseif (!$obj_col) {
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
                                                    }
                                                    else {
                                                        $tuple[$col] = '';
                                                    }
                                                }
                                                else {
                                                    $obj->throwError(
                                                        "strange value for FK field : $col => " .
                                                        var_export(
                                                        $obj_col,
                                                        true
                                                    )
                                                    );
                                                }
                                            }
                                            break;
                                        case 'MFK':
                                            $objs = $val->get(
                                                $col,
                                                'object',
                                                '',
                                                false
                                            );
                                            if(!is_array($objs))
                                            {
                                                throw new RuntimeException("How $val => get($col,'object','',false) return ".var_export($objs,true));
                                            }
                                            if (count($objs)) 
                                            {
                                                $mfk_show_sep =
                                                    $desc['LIST_SEPARATOR'];
                                                if (!$mfk_show_sep) {
                                                    $mfk_show_sep =
                                                        $desc[
                                                        'MFK-SHOW-SEPARATOR'
                                                        ];
                                                }
                                                if (!$mfk_show_sep) {
                                                    $mfk_show_sep = "<br>\n";
                                                }
                                                $str = '';
                                                foreach ($objs as $instance) {
                                                    if ($str) {
                                                        $str .= $mfk_show_sep;
                                                    }
                                                    $str .= $instance;
                                                }

                                                $tuple[$col] = $str;
                                            }
                                            break;
                                        case 'ANSWER':
                                            $tuple[$col] = $val->decode($col);
                                            break;
                                        case 'YN':
                                            $col_decoded = $val->decode($col);
                                            $tuple[$col] = $col_decoded;
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
                                                // die("key=$key, val=$val, display_val=$display_val, HZM-CSS=".$structure["HZM-CSS"]);
                                                $css_arr = $val::afw_explode(
                                                    $desc['HZM-CSS']
                                                );
                                                $css_val =
                                                    $css_arr[$value] .
                                                    '_display';
                                                $tuple[
                                                    $col
                                                    ] = "<div class='$css_val'>$display_val</div>";
                                            }
                                            else {
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
                        if($categoryAttributeCATEGORY)
                        {
                            $tuple[$categoryAttribute] = $val->calc($categoryAttribute);
                            //die("tuple[$categoryAttribute] = ".$tuple[$categoryAttribute]." = $val-->calc($categoryAttribute)");
                        }
                        else
                            $tuple[$categoryAttribute] = $val->getVal($categoryAttribute);
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
            if (!$trad_col) 
            {
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
            substr($obj->getTableName(), 0, 5)
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
        $lang = 'ar',
        $dir = 'rtl',
        $bigtitle = '',
        $bigtitle_tr_class = 'bigtitle',
        $width_th_arr = [],
        $img_width = '',
        $rows_by_table = 0,
        $showWidthedTable = '',
        $row_class_key = '',
        $col_class_key = '',
        $class_td_off = 'off'
        )
    {
        //die("dataImportance=".var_export($dataImportance,true));
        global $datatable_on_components,
        $datatable_on,
        $styled_data_arr,
        $total_cols,
        $datatables_arr;

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
                }
                else {
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
                if ($row_class_key) {
                    $row_class_css = 'hzm_row_' . $tuple[$row_class_key];
                }
                else {
                    $row_class_css = '';
                }
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $id;
                //if($nom_col=="sms_sent_date") die("nowrap_cols for $nom_col = ".var_export($nowrap_cols,true));
                $old_cl = $cl_tr;
                if ($cl_tr == $class_tr2) {
                    $cl_tr = $class_tr1;
                }
                else {
                    $cl_tr = $class_tr2;
                }
                if ($isAvail[$id] == 'inactive') {
                    $cl_tr = $class_td_off;
                }
                elseif ($isAvail[$id] == 'error') {
                    if ($old_cl == $class_tr2 or $old_cl == 'err') {
                        $cl_tr = 'alterr';
                    }
                    else {
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
                    }
                    else {
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
        }
        else {
            $html = $html_header;
            $html .= '<tbody>';
            $ids = '';

            $sum_cols_total = [];
            $my_class_name = '';
            $cl_tr = '';
            foreach ($data as $id => $tuple) {
                if ($row_class_key) {
                    $row_class_css = 'hzm_row_' . $tuple[$row_class_key];
                }
                else {
                    $row_class_css = '';
                }
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $id;
                $old_cl = $cl_tr;
                if ($cl_tr == $class_tr2) {
                    $cl_tr = $class_tr1;
                }
                else {
                    $cl_tr = $class_tr2;
                }
                // die("isAvail = ".var_export($isAvail,true));
                if ($isAvail[$id] == 'inactive') {
                    $cl_tr = $class_td_off;
                }
                elseif ($isAvail[$id] == 'error') {
                    if ($old_cl == $class_tr2 or $old_cl == 'err') {
                        $cl_tr = 'alterr';
                    }
                    else {
                        $cl_tr = 'err';
                    }
                }
                $html .= "   <tr class='$cl_tr $row_class_css' alt='old_cl=$old_cl'>\n";
                foreach ($header_trad as $nom_col => $desc) {
                    $importance = ($dataImportance and is_array($dataImportance)) ? $dataImportance[$nom_col] : "";
                    $nom_col_ltn = arabic_to_latin_chars($nom_col);
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
                    }
                    else {
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
                    }
                    else {
                        $data_aff = $tuple[$nom_col];
                    }
                    $html .=
                        "         <td class='col-importance-$importance $col_class_css' $nowrap_col>" .
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
                    }
                    else {
                        $total_disp = $sum_cols_total[$nom_col];
                    }

                    if ($total_cols[$nom_col]) {
                        $html .=
                            "         <th $nowrap_col>" .
                            $total_disp .
                            "</th>\n";
                    }
                    elseif ($col_ord == 0) {
                        $html .= "         <th>المجموع</th>\n";
                    }
                    else {
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

            if ($showAsDataTable and !$datatables_arr[$showAsDataTable]) {
                $html .= "<script type=\"text/javascript\">
$(document).ready(function() {
$('#$showAsDataTable').DataTable( {
\"pagingType\": \"full_numbers\"
} );
} );
</script>";
            }
        }

        return [$html, $ids];
    }


    /**
     * show
     * Show
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
        $class_db_structure=null
    ) {
        global $lang;
        //if($this->test_rafik) die("test_rafik 5 start of show ($mode_affichage ...) for : " . $this->getDisplay($lang));
        $mode = strtoupper($mode_affichage);
        if ($mode == 'TPL') {
            return $object->showUsingTpl($html_template);
        } elseif ($mode == 'HTML') 
        {
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
            include dirname(__FILE__).'/../modes/'.$template;
            return ob_get_clean();
        } elseif ($mode == 'EDIT') {
            $template = 'afw_template_default_edit.php';
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
            //if($object->test_rafik) die("test_rafik 400 before require $template (cl=$cl,id=$id) obj = ".var_export($obj,true));
            require_once dirname(__FILE__).'/../modes/afw_edit_motor.php';
            require dirname(__FILE__).'/../modes/'.$template;
            return ob_get_clean();
        } elseif ($mode == 'STR') {
            return $object->showMe('', $lang);
        }
    }


    public static function genereMiniBoxTemplate($nameObj,$miniboxTemplateArr, $qeditInputsArr,$qeditTranslationArr,$qeditNum,$templateNum="",$is_disabled="")
    {
        $rows_arr = array();
        
        $curr_row = 0;
        $curr_col = 0;
        $used_hzm_width = 0;
        $idInput = $qeditInputsArr["id"."_".$qeditNum];
        
        foreach($miniboxTemplateArr as $col => $desc)
        {
            $remain_hzm_width = 12 - $used_hzm_width;
            if(($desc["HZM-WIDTH"]) > $remain_hzm_width)
            {
                if($remain_hzm_width>0)
                {
                    if($curr_col==0) return "first col $col has hzm size too big : more than remain size = $remain_hzm_width";
                    // put the remain hzm cells in the previous col
                    $rows_arr[$curr_row][$curr_col-1]["used"] += $remain_hzm_width;
                }
                
                $curr_row++;
                $used_hzm_width = 0;
                $curr_col = 0;
            }
            $rows_arr[$curr_row][$curr_col] = ["col"=>$col, "used"=>$desc["HZM-WIDTH"]];
            $used_hzm_width += $desc["HZM-WIDTH"];
            $curr_col++;
        }
        if(($remain_hzm_width>0) and ($curr_col>0))
        {
            // put the remain hzm cells in the previous col
            $rows_arr[$curr_row][$curr_col-1]["used"] += $remain_hzm_width;
        }
        
        
        $miniBoxTemplate = "<div class='minibox_hzm_panel panel_qedit${is_disabled}'>
                        <div class='label_title_minibox${is_disabled}$templateNum'><div class='minibox_id${is_disabled}'>$idInput</div>$nameObj</div>
                        <table class='table_minibox hzm_table${is_disabled}'>\n";

            
        $nb_trs = 2*count($rows_arr);
        foreach($rows_arr as $curr_row => $rowArr)
        {
            $miniBoxTemplateSecondRow = "<tr>\n";
            
            $miniBoxTemplate .= "<tr>\n";
            
            foreach($rowArr as $curr_col => $colArr)
            {
                $colName = $colArr["col"];
                $used = $colArr["used"];
                $trad = $qeditTranslationArr[$colName];
                $input = $qeditInputsArr[$colName."_".$qeditNum];
                $miniBoxTemplate .= "<th colspan='$used'><b><span class='label_minibox'>$trad</span></b></th>\n";
                
                $miniBoxTemplateSecondRow .= "<td colspan='$used'>$input</td>\n";
            
            }
            
            
            $miniBoxTemplateSecondRow .= "</tr>\n";
            $miniBoxTemplate .= "</tr>\n";
            $miniBoxTemplate .= $miniBoxTemplateSecondRow . "\n\n";
        }
        $miniBoxTemplate .= "</table></div>\n";
        
        
        return $miniBoxTemplate;
        
        
        
            

    }
}