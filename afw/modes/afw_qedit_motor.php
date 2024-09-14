<?php 
#####################################################################################
####################################  FONCTIONS  ####################################
#####################################################################################

function hidden_input($col_name, $desc, $val, &$obj)
{
    $type_input_ret = "hidden";
?>
    <input type="hidden" id="<?php  echo $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
    <?
    return $type_input_ret;
}

function type_input($col_name, $desc, $val, &$obj, $separator, $data_loaded = false, $force_css = "", $qedit_orderindex = 0, $data_length_class_default_for_fk = "inputmoyen")
{
    global $TMP_DIR, $_SERVER, $Main_Page, $_GET, $_POST,
        $header_bloc_edit, $footer_bloc_edit,
        $aligntd, $lang, $mode_hijri_edit, $yes_label, $no_label, $dkn_label, $objme;

    $development_mode = AfwSession::config("MODE_DEVELOPMENT", false);

    $mode_qedit = true;

    if (!$Main_Page) $Main_Page = $_GET["Main_Page"];
    if (!$Main_Page) $Main_Page = $_POST["Main_Page"];

    // die("qed motor Main_Page = $Main_Page");
    if ((AfwStringHelper::stringEndsWith($Main_Page,"afw_mode_qedit.php")) or (AfwStringHelper::stringEndsWith($Main_Page,"afw_handle_default_qedit.php"))) 
    {
        $mode_qedit = true;
    }

    if ($mode_qedit) {
        $qeditCount = $obj->qeditCount;
        $qeditNomCol = $obj->qeditNomCol;
        if (!$qeditNomCol) $qeditNomCol = $col_name;
        $orig_col_name = $qeditNomCol;
    } else {
        $orig_col_name = $col_name;
    }
    if($orig_col_name=="coming_status_id_0") die("Main_Page=$Main_Page mode_qedit=$mode_qedit qeditCount=$qeditCount qeditNomCol=$qeditNomCol col_name=$col_name orig_col_name=$orig_col_name");
    $col_title = $obj->translate($qeditNomCol, $lang);
    $placeholder = $desc["PLACE-HOLDER"];
    if (!$placeholder) $placeholder = $col_title;

    include("afw_config.php");

    global $images;

    $type_input_ret = "";

    if ($data_loaded) $data_loaded_class = " $class_xqe"."data_loaded";
    else $data_loaded_class = " $class_xqe"."data_notloaded";

    if (AfwStringHelper::stringStartsWith($col_name, "titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
    if (se_termine_par($col_name, "titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
    if (AfwStringHelper::stringStartsWith($col_name, "titre") && (!$desc["SIZE"])) $desc["SIZE"] = 255;


    $data_length_class = " inputlong";

    $desc["WHERE"] = $obj->getWhereOfAttribute($orig_col_name);
    // if(($orig_col_name=="level_class_id_0") or ($col_name=="level_class_id")) die("qeditNomCol=$qeditNomCol orig_col_name=$orig_col_name obj->getWhereOfAttribute($orig_col_name) = ".$desc["WHERE"]);            
    $readonly = "";

    if ($desc["READONLY"]) {
        $readonly = "readonly";
    }

    if ($desc["JS-COMPUTED-READONLY"]) {
        $readonly = "readonly";
    }

    if (true) {
        $onchange = $desc["ON-CHANGE"];
        $onchange = str_replace("§row§", $obj->qeditNum, $onchange);
        $onchange = str_replace("§rowcount§", $qeditCount, $onchange);

        if ($desc["FOOTER_SUM"]) $onchange .= "qedit_col_total('$qeditNomCol',$qeditCount); ";

        $after_change = $desc["AFTER-CHANGE"];
        $after_change = str_replace("§row§", $obj->qeditNum, $after_change);
        $after_change = str_replace("§rowcount§", $qeditCount, $after_change);

        $onchange .= $after_change;
        if ($mode_qedit) $onchange .= "iHaveBeenChanged('$col_name'); ";
        else $onchange .= "iHaveBeenEdited('$col_name'); ";
    }

    switch ($desc["TYPE"]) {
        case 'PK':
            if ($val <= 0) $val = "سجل جديد";
            $type_input_ret = "text";
    ?>
            <input placeholder="<?= $placeholder ?>" type="text" class="<?= $class_inputPK ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>" size=32 maxlength=255 readonly>
            <?php  break;
        case 'FK':
            $nom_table_fk   = $desc["ANSWER"];
            $nom_module_fk  = $desc["ANSMODULE"];
            if (!$nom_module_fk) {
                $nom_module_fk = AfwUrlManager::currentWebModule();
            }

            $nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
            // $nom_fichier_fk = AFWObject::table ToFile($nom_table_fk);
            if ($force_css) $data_length_class = " " . $force_css;
            else $data_length_class = " " . $data_length_class_default_for_fk;

            if ($force_css != "inputlong") $class_select = $class_inputSelect;
            else $class_select = $class_inputSelectLong;

            /*
                                $file_dir_name = dirname(__FILE__); 
                                if($nom_module_fk)
                                     require_once $file_dir_name."/../$nom_module_fk/".$nom_fichier_fk;
                                else
                                     require_once $file_dir_name."/".$nom_fichier_fk;*/

            $objRep  = new $nom_class_fk;

            $list_count = AfwSession::config($objRep->getMyClass() . "::estimated_row_count", 0);;

            $auto_c = $desc["AUTOCOMPLETE"];

            if ($desc["STYLE"]) $style_input = " style='" . $desc["STYLE"] . "' ";
            else $style_input = "";

            $LIMIT_INPUT_SELECT = AfwSession::config("LIMIT_INPUT_SELECT", 20); 
            //die("$col_name $nom_class_fk $list_count/".$LIMIT_INPUT_SELECT);   
            if ((!$auto_c) and ($list_count <= $LIMIT_INPUT_SELECT)) {

                $obj_className = $obj->getMyClass();
                if ($development_mode or $objme->isAdmin()) echo "<!-- for ($obj_className).$col_name : [$objRep] -> loadMany FollowingStructureAndValue($col_name, $desc,$val, $obj) -->";
                //list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc, $val, $obj);
                $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;
                $l_rep = AfwLoadHelper::vhGetListe($objRep, $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
                
                
                if ($obj->qedit_minibox)
                    $css_class = "form-control";
                else $css_class = $class_select . $data_loaded_class . $data_length_class;

                $prop_sel =
                    array(
                        "class" => $css_class,
                        "style" => $desc["STYLE"],
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "reloadfn" => $obj->getJsOfReloadOf($col_name, "", $orig_col_name),
                        "onchange" => $onchange . $obj->getJsOfOnChangeOf($col_name, $desc = "", true, $orig_col_name),
                        "onchangefn" => $obj->getJsOfOnChangeOf($col_name, $desc = "", false, $orig_col_name),
                    );

                if ($obj->fixm_disable) {

                    $type_input_ret = "hidden";
            ?>
                    <input type="hidden" id="<?php  echo $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                    <span class='momken-fk'><?php if (!$obj->hideQeditCommonFields) echo $l_rep[$val] ?></span>
                <?php 
                } else {
                    select(
                        $l_rep,
                        array($val),
                        $prop_sel
                    );
                    $type_input_ret = "select";
                }
            } else {
                $type_input_ret = "autocomplete";
                $col_name_atc = $col_name . "_atc";
                if (($val)) // and ((!$obj->fixm_disable) or (!$obj->fixmtit))) 
                {
                    $objRep->load($val);
                    $val_display = $objRep->getDisplay($lang);
                } else {
                    $val_display = "";
                }
                //$clwhere = $desc["WHERE"];
                $attp = $col_name;
                $clp = $obj->getMyClass();
                $idp = $obj->getId();
                $modp = $obj->getMyModule();
                $auto_c_create = $auto_c["CREATE"];

                if ($obj->qedit_minibox)
                    $atc_input_normal = "form-control";
                else $atc_input_normal = $data_loaded_class . " inputshort";

                if ($auto_c_create) {
                    $class_icon = "new";
                    $atc_input_modified_class = $data_loaded_class . $data_length_class . " new_record";
                } else {
                    $class_icon = "notfound";
                    $atc_input_modified_class = $data_loaded_class . $data_length_class . " record_not_found";
                }

                if ($obj->fixm_disable) {

                    $type_input_ret = "hidden";
                ?>
                    <input type="hidden" id="<?php  echo $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                    <span class='momken-fk-autoc'><?php if (!$obj->hideQeditCommonFields) echo "[$val_display]" ?></span>
                <?php 
                } else {
                    $help_atc = $auto_c["HELP"];
                    $depend = $obj->getDependencyIdsArray($col_name, $desc);
                ?>
                    <table cellspacing='0' cellpadding='0'>
                        <tr style="background-color: rgba(255, 255, 255, 0);">
                            <td style="padding:0px;margin:0px;background-color: rgba(255, 255, 255, 0);"><input type="hidden" id="<?= $col_name ?>" name="<?= $col_name ?>" class="inputtrescourt cl_id" value="<?= $val ?>" readonly></td>
                            <td style="padding:0px;margin:0px;"><input placeholder="<?= $placeholder ?>" type="text" id="<?= $col_name_atc ?>" name="<?= $col_name_atc ?>" class="inputqe <?= $atc_input_normal ?>" value="<?= $val_display ?>"></td>
                            <?
                            if ($auto_c_create) {
                            ?>
                                <th style="padding:0px;margin:0px;"><img src='../lib/images/create_new.png' data-toggle="tooltip" data-placement="top" title='لإضافة عنصر غير موجود في القائمة (بعد التثبت) انقر هنا ثم اكتب المسمى' onClick="empty_atc('<?= $col_name ?>');" style="width: 24px !important;height: 24px !important;" /></th>
                            <?
                            }
                            ?>
                            <td style="padding:0px;margin:0px;"><?= $help_atc ?></td>
                        </tr>
                    </table>
                    <script>
                        $(function() {

                            $("#<?= $col_name_atc ?>").autocomplete({
                                source: "../lib/api/autocomplete.php?cl=<?= $nom_class_fk ?>&currmod=<?= $nom_module_fk ?>&clp=<?= $clp ?>&idp=<?= $idp ?>&modp=<?= $modp ?>&attp=<?= $attp ?>&depend=<?php echo $depend ?>",
                                minLength: 0,

                                change: function(event, ui) {
                                    if ($("#<?= $col_name_atc ?>").val() == "") {
                                        $("#<?= $col_name ?>").val("");
                                    }
                                    // $("#<?= $col_name_atc ?>").addClass('value_not_found');
                                    // $("#<?= $col_name ?>").val("");
                                    // $("#<?= $col_name ?>").attr('class', 'inputtrescourt cl_<?= $class_icon ?>_id');
                                    // $("#<?= $col_name_atc ?>").attr('class', '<?= $atc_input_modified_class ?>');
                                },


                                select: function(event, ui) {
                                    //alert(ui.item.id);
                                    $("#<?= $col_name ?>").val(ui.item.id);
                                    $("#<?= $col_name ?>").attr('class', 'inputtrescourt cl_id');
                                    $("#<?= $col_name_atc ?>").attr('class', '<?= $atc_input_normal ?>');
                                    $("#<?= $col_name_atc ?>").addClass('input_changed');
                                },

                                html: true, // optional (jquery.ui.autocomplete.html.js required)

                                // optional (if other layers overlap autocomplete list)
                                open: function(event, ui) {
                                    $(".ui-autocomplete").css("z-index", 1000);
                                }
                            });

                        });
                    </script>

                <?php                     }
            }
            break;
        case 'MFK':
            $nom_table_fk   = $desc["ANSWER"];
            $nom_module_fk  = $desc["ANSMODULE"];
            if (!$nom_module_fk) {
                $nom_module_fk = AfwUrlManager::currentWebModule();
            }
            $nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
            /*
            $nom_fichier_fk = AFWObject::table ToFile($nom_table_fk);

            $file_dir_name = dirname(__FILE__);
            if ($nom_module_fk)
                require_once $file_dir_name . "/../$nom_module_fk/" . $nom_fichier_fk;
            else
                require_once $file_dir_name . "/" . $nom_fichier_fk;
            */

            $objRep  = new $nom_class_fk;
            // list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc, $val, $obj);
            // $l_rep = array();
            /* foreach ($liste_rep as $iditem => $item) {
                if (AfwUmsPagHelper::userCanDoOperationOnObject($item,$objme, 'display'))
                    $l_rep[$iditem] = $item->getDropDownDisplay($lang);
            }
            */
            $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;
            $l_rep = AfwLoadHelper::vhGetListe($objRep, $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
                                                
            $type_input_ret = "select";

            if ($desc["STYLE"]) $style_input = " style='" . $desc["STYLE"] . "' ";
            else $style_input = "";

            $class_of_input_select_multi = $class_inputSelect_multi_big;
            if ($desc["MEDIUM_DROPDOWN_WIDTH"] or ($desc["SIZE"] < 64)) $class_of_input_select_multi = $class_inputSelect_multi;

            if ($obj->qedit_minibox)
                $css_class = "form-control";
            else $css_class = $class_of_input_select_multi . $data_loaded_class . $data_length_class;




            $infos_arr = array(
                "class" => $css_class,
                "style" => $desc["STYLE"],
                "name"  => $col_name . "[]",
                "id"  => $col_name,
                "size"  => 5,
                "multi" => true,
                "tabindex" => $qedit_orderindex,
                "onchange" => $onchange,

            );
            if ($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr, $desc["SEL_OPTIONS"]);

            if ($desc["SEL_CSS_CLASS"]) $infos_arr["class"] = $desc["SEL_CSS_CLASS"];

            select(
                $l_rep,
                explode($separator, trim($val, $separator)),
                $infos_arr,
                "",
                false
            );

            break;
        case 'MENUM':
            if ($desc["STYLE"]) $style_input = " style='" . $desc["STYLE"] . "' ";
            else $style_input = "";

            if ($desc["ANSWER"] == "INSTANCE_FUNCTION") {
                $liste_rep = AfwStructureHelper::getEnumAnswerList($obj, $orig_col_name);
                $answer_case = "INSTANCE_FUNCTION so obj-> get EnumAnswerList";
            } else {
                $fcol_name = $desc["FUNCTION_COL_NAME"];
                if(!$fcol_name) $fcol_name = $orig_col_name;
                $liste_rep = AfwLoadHelper::getEnumTable($desc["ANSWER"], $obj->getTableName(), $fcol_name, $obj);
                $answer_case = "AfwLoadHelper::get EnumTable(" . $desc["ANSWER"] . ")";
            }


            //echo "menum val $val with sep $separator : <br>";
            $val_arr = explode($separator, trim($val, $separator));
            //print_r($val_arr);
            //echo "<br>";
            if ($force_css) $data_length_class = " " . $force_css;
            else $data_length_class = " inputmoyen";
            $type_input_ret = "select";

            $class_of_input_select_multi = $class_inputSelect_multi_big;
            if ($desc["MEDIUM_DROPDOWN_WIDTH"]) $class_of_input_select_multi = $class_inputSelect_multi;

            if ($obj->qedit_minibox)
                $css_class = "form-control";
            else $css_class = $class_of_input_select_multi . $data_loaded_class . $data_length_class;


            $infos_arr = array(
                "class" => $css_class,
                "style" => $desc["STYLE"],
                "name"  => $col_name . "[]",
                "id"  => $col_name,
                "size"  => 5,
                "multi" => true,
                "tabindex" => $qedit_orderindex,
                "onchange" => $onchange,

            );
            if ($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr, $desc["SEL_OPTIONS"]);


            select(
                $liste_rep,
                $val_arr,
                $infos_arr,
                ""
            );
            break;
        /* case 'ANSWER': obsolete
            if ($desc["STYLE"]) $style_input = " style='" . $desc["STYLE"] . "' ";
            else $style_input = "";

            $liste_rep = AFWObject::getAnswerTable($desc["ANSWER"], $desc["MY_PK"], $desc["MY_VAL"]);
            if ($force_css) $data_length_class = " " . $force_css;
            else $data_length_class = " inputmoyen";
            $LIMIT_INPUT_SELECT = AfwSession::config("LIMIT_INPUT_SELECT", 20);
            if (count($liste_rep) <= $LIMIT_INPUT_SELECT) {
                $type_input_ret = "select";

                if ($obj->qedit_minibox)
                    $css_class = "form-control";
                else $css_class = $class_inputSelect . $data_loaded_class . $data_length_class;

                select(
                    $liste_rep,
                    array($val),
                    array(
                        "class" => $css_class,
                        "style" => $desc["STYLE"],
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "onchange" => $onchange,
                    ),
                    "asc"
                );
            } else {
                if ($obj->qedit_minibox)
                    $css_class = "form-control";
                else $css_class = $class_inputTextFk . $data_loaded_class;


                $type_input_ret = "text";
                ?> <input placeholder="<?= $placeholder ?>" type="text" tabindex="<?= $qedit_orderindex ?>" class="inputqe <?= $css_class ?>" name="<?php  echo $col_name ?>" id="<?php  echo $col_name ?>" value="<?php  echo $val ?>" size=33 maxlength=255>
                <input type="button" class="<?= $class_inputButton ?>" name="" value="<?= $obj->translate('SEARCH', $lang, true) ?>" onclick="popup('<?php  echo "main.php" ?>?Main_Page=afw_mode_search.php&cl=<?php  echo $desc["ANSWER"] ?>')">
                <script language="javascript">
                    function popup(page) {
                        window.open(page, "<?= $obj->translate('SEARCH', $lang, true) ?>", "fullscreen='yes',menubar='no',toolbar='no',location='no',status='no'");
                    }
                </script>
            <?php             }
            break;*/
        case 'ENUM':
            if ($desc["STYLE"]) $style_input = " style='" . $desc["STYLE"] . "' ";
            else $style_input = "";

            if ($force_css) $data_length_class = " " . $force_css;
            else $data_length_class = " inputmoyen";

            if ((!$desc["ENUM_ALPHA"]) and ((!$val) or (!intval($val)))) $val = 0;

            if ($desc["ANSWER"] == "INSTANCE_FUNCTION") {
                $liste_rep = AfwStructureHelper::getEnumAnswerList($obj, $orig_col_name);
                $answer_case = "INSTANCE_FUNCTION so obj->get Enum AnswerList($orig_col_name) ";
            } else {
                $objTableName = $obj->getTableName();
                $objName = $obj->__toString();
                $fieldAnsTab = $desc["ANSWER"];
                $fcol_name = $desc["FUNCTION_COL_NAME"];
                if(!$fcol_name) $fcol_name = $orig_col_name;
                $liste_rep = AfwLoadHelper::getEnumTable($fieldAnsTab, $objTableName, $fcol_name, $obj);
                $answer_case = "AfwLoadHelper::get EnumTable($fieldAnsTab, $objTableName, $fcol_name, obj:$objName)";
            }
            if(!$liste_rep) throw new AfwRuntimeException("for col $orig_col_name enum liste_rep comes from $answer_case is null or empty  liste_rep = ".var_export($liste_rep,true));
            

            // if($orig_col_name=="level_enum") throw new AfwRuntimeException("for col $orig_col_name enum liste_rep comes from $answer_case : ".var_export($liste_rep,true));
            // if($desc["FORMAT-INPUT"]=="hzmtoggle") throw new AfwRuntimeException("for enum col $orig_col_name liste_rep comes from $answer_case : ".var_export($liste_rep,true));

            if ($obj->fixm_disable) {

                $type_input_ret = "hidden";
            ?>
                <input type="hidden" id="<?php  echo $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                <span class='momken-enum'>
                    <?php 
                        $val_decoded = $liste_rep[$val];
                        if(!$val_decoded) $val_decoded = $val. "[enum-decode-failed] from LRP=".var_export($liste_rep,true) ;
                        if(!$obj->hideQeditCommonFields) echo $val_decoded;
                        
                    ?>
            
                </span>
                <?php 
            } else {

                if ($desc["FORMAT-INPUT"] == "hzmtoggle") {
                    $display_val = $liste_rep[$val];
                    if (!$display_val) $display_val = "...";
                    $css_arr = AfwStringHelper::afw_explode($desc["HZM-CSS"]);
                    $css_val = $css_arr[$val];



                    $liste_choix = array();
                    $liste_css = array();
                    $liste_codes = array();
                    $liste_codeOrdres = array();
                    $listeOrdres = array();

                    $log_echo = "log of hzm enum toggle : ";
                    $log_echo .= "<br>\n liste_rep = " . var_export($liste_rep, true);
                    $max_rep_id = 0;
                    $oord = 0;
                    foreach ($liste_rep as $rep_id => $rep_val) {
                        if ($rep_val) {
                            $liste_choix[$oord] = $rep_val;
                            $liste_codes[$oord] = $rep_id;
                            $liste_codeOrdres[$rep_id] = $oord;
                            if ($max_rep_id < $rep_id) $max_rep_id = $rep_id;
                            $liste_css[$oord] = $css_arr[$rep_id];
                            $log_echo .= "<br>\n $rep_id => $rep_val , " . var_export($liste_css, true);
                            $oord++;
                        }
                    }

                    for ($rep_i = 0; $rep_i <= $max_rep_id; $rep_i++) 
                    {
                        if (!isset($liste_codeOrdres[$rep_i])) $listeOrdres[$rep_i] = -1;
                        else $listeOrdres[$rep_i] = $liste_codeOrdres[$rep_i];
                    }

                    //if($col_name=="coming_status_id_0") throw new AfwRuntimeException($log_echo);
                    if (!$css_val) $css_val = $desc["DEFAULT-CSS"];
                    if (!$css_val) $css_val = $liste_css[0];

                    $liste_choix_text = "['" . implode("','", $liste_choix) . "']";
                    $liste_codes_text = "['" . implode("','", $liste_codes) . "']";
                    $listeOrdres_text = "['" . implode("','", $listeOrdres) . "']";

                    $liste_css_text = "['" . implode("','", $liste_css) . "']";
                ?>
                    <input type='hidden' name='<?php  echo $col_name ?>' id='<?php  echo $col_name ?>' value='<?php  echo $val ?>'>
                    <button type="button" id="btn_<?php  echo $col_name ?>" class="toggle-hzm-btn <?php  echo $css_val ?>" onClick="toggleHzmBtn('<?php  echo $col_name ?>', <?php  echo $liste_choix_text ?>, <?php  echo $liste_codes_text ?>, <?php  echo $listeOrdres_text ?>, <?php  echo $liste_css_text ?>,<?php  echo count($liste_choix) ?>)"><?php  echo $display_val ?></button>
                <?php 
                } else {
                    if ($desc["STYLE"]) $style_input = " style='" . $desc["STYLE"] . "' ";
                    else $style_input = "";

                    $type_input_ret = "select";

                    if ($desc["FORMAT-INPUT"] == "hzmsel") {
                        $css_arr = AfwStringHelper::afw_explode($desc["HZM-CSS"]);
                        $css_class = "selectpicker"; //." ".$data_loaded_class.$data_length_class
                    } else {
                        $css_arr = null;
                        if ($obj->qedit_minibox)
                            $css_class = "form-control";
                        else $css_class = $class_inputSelect . $data_loaded_class . $data_length_class;
                    }

                    $info = array(
                        "class" => $css_class,
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "onchange" => $onchange,
                        "bsel_css" => [],
                    );



                    select(
                        $liste_rep,
                        array($val),
                        $info,
                        ""
                    );
                }
            }
            break;
        case 'PCTG':
        case 'INT':
        case 'AMNT':
            $fromListMethod = $desc["FROM_LIST"];
            if($fromListMethod)
            {
                $fromList = $obj->$fromListMethod();
                //echo "val=$val<br>";
                select(
                    $fromList,
                    array(trim($val)),
                    array(
                        "class" => "comm_select inputselect",
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "onchange" => $onchange,
                    ),
                    ""
                );
            }
            else
            {
                $input_type_html = "text";
                if ($desc["TYPE"] == 'INT') {
                    $input_type_html = "number";
                    $input_options_html = "";
                    if ($desc["FORMAT"]) {
                        list($format_type, $format_param1, $format_param2, $format_param3) = explode(":", $desc["FORMAT"]);
                        if ($format_type == "STEP") {
                            if (!$format_param3) $format_param3 = 1;
                            $input_options_html = " step='$format_param3' min='$format_param1' max='$format_param2' ";
                        }
                    }
                }
                if ($desc["STYLE"]) $style_input = " style='" . $desc["STYLE"] . "' ";
                else $style_input = "";

                if ($force_css) $data_length_class = " " . $force_css;
                else $data_length_class = " inputcourt";
                $type_input_ret = "text";
                $class_of_input = $class_inputInt;
                if ($desc["JS-COMPUTED"]) {
                    if ($obj->class_of_input_computed_readonly) $class_of_input = $obj->class_of_input_computed_readonly;

                    if ($obj->class_js_computed) $class_js_computed = $obj->class_js_computed;
                    else $class_js_computed = "js_computed";

                    $data_loaded_class = $class_js_computed;
                }

                if ($obj->fixm_disable) {

                    $type_input_ret = "hidden";
                    ?>
                    <input type="hidden" fw="momken-1" id="<?= $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                    <span class='fw-momken-numeric'><?php if (!$obj->hideQeditCommonFields) echo $val ?></span>
                    <?php 
                } else {
                    if ($obj->qedit_minibox)
                        $css_class = "form-control";
                    else $css_class = $class_of_input . $data_loaded_class . $data_length_class;
                    if ($input_type_html == "text") {
                    ?>
                        <input type="<?= $input_type_html ?>" tabindex="<?= $qedit_orderindex ?>" class="inputqe <?= $css_class ?>" name="<?php  echo $col_name ?>" id="<?php  echo $col_name ?>" value="<?php  echo $val ?>" size=6 maxlength=6 <?php  echo $readonly ?> onchange="<?php  echo $onchange ?>" placeholder="<?= $placeholder ?>" <?php  echo $input_options_html . " " . $style_input ?>>
                    <?php 
                    } else {
                    ?>
                        <input type="<?= $input_type_html ?>" tabindex="<?= $qedit_orderindex ?>" class="inputqe <?= $css_class ?>" name="<?php  echo $col_name ?>" id="<?php  echo $col_name ?>" value="<?php  echo $val ?>" <?php  echo $input_options_html ?>>
                    <?php 
                    }
                }
            }
            // echo (isset($desc["UNIT"]) && !empty($desc["UNIT"])  && (strlen($desc["UNIT"])<6)) ? $desc["UNIT"] : "";
            // echo (isset($desc["TITLE_AFTER"]) && !empty($desc["TITLE_AFTER"]) && (strlen($desc["TITLE_AFTER"])<6)) ? $desc["TITLE_AFTER"] : "";
            break;

        case 'TEXT':
            $utf8 = $desc["UTF8"];
            $fromListMethod = $desc["FROM_LIST"];
            $dir = $desc["DIR"];
            if (!$dir) $dir = ($utf8 ? "rtl" : "ltr");
            
            if ($obj->fixm_disable) {

                $type_input_ret = "hidden";
                ?>
                <input type="hidden" fw="momken-text" id="<?= $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                <span class='fw-momken-text'><?php if (!$obj->hideQeditCommonFields) echo $val ?></span>
                <?php 
            } 
            elseif ((isset($desc["SIZE"])) && (($desc["SIZE"] == "AREA") or ($desc["SIZE"] == "AEREA"))) {
                $rows = $desc["ROWS"];
                if (!$rows) $rows = 4;
                $cols = $desc["COLS"];
                if (!$cols) $cols = 43;
                $type_input_ret = "text";

                if ($obj->qedit_minibox)
                    $css_class = "form-control";
                else $css_class = $class_inputText . $data_loaded_class;


                ?>
                <textarea placeholder="<?= $placeholder ?>" class="<?= $css_class ?>" cols="<?= $cols ?>" rows="<?= $rows ?>" id="<?php  echo $col_name ?>" name="<?php  echo $col_name ?>" dir="<?php  echo $dir ?>" onchange="<?php  echo $onchange ?>"><?php  echo $val ?></textarea>
            <?php            
            }
            elseif($fromListMethod)
            {
                $fromList = $obj->$fromListMethod();
                //echo "val=$val<br>";
                select(
                    $fromList,
                    array(trim($val)),
                    array(
                        "class" => $css_class,
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "onchange" => $onchange,
                    ),
                    "asc"
                );
            } 
            else 
            {

                if (!$desc["SHORT_SIZE"])  $desc["SHORT_SIZE"] = $desc["SIZE"];

                if ($desc["STYLE"]) $style_input = " style='" . $desc["STYLE"] . "' ";
                else $style_input = "";
                //if($orig_col_name=="titre_u") die("desc = ".var_export($desc,true)); 
                if (($force_css) and (!$desc["WIDTH-FROM-SIZE"])) $data_length_class = " " . $force_css;
                else if ($desc["SHORT_SIZE"] < 32)  $data_length_class = " inputcourt";
                else if ($desc["SHORT_SIZE"] <= 64)  $data_length_class = " inputmoyen";
                else if ($desc["SHORT_SIZE"] <= 84)  $data_length_class = " inputlong";
                else if ($desc["SHORT_SIZE"] < 255)  $data_length_class = " inputtreslong";
                else $data_length_class = " inputultralong";
                $type_input_ret = "text";

                if ($obj->qedit_minibox)
                    $css_class = "form-control";
                else $css_class = $class_inputText . $data_loaded_class . $data_length_class;
            ?>
                <input placeholder="<?= $placeholder ?>" type="text" tabindex="<?= $qedit_orderindex ?>" class="inputqe <?= $css_class ?>" name="<?php  echo $col_name ?>" id="<?php  echo $col_name ?>" dir="<?php  echo $dir ?>" value="<?php  echo $val ?>" size=32 maxlength=255 onchange="<?php  echo $onchange ?>" <?php  echo $style_input ?>>
            <?
            }
            break;
        case 'YN':
            if ($force_css) $data_length_class = " " . $force_css;
            else $data_length_class = "";

            $this_yes_label = $obj->showYNValueForAttribute("YES", $orig_col_name, $lang);
            $this_no_label  = $obj->showYNValueForAttribute("NO", $orig_col_name, $lang);
            $this_dkn_label = $obj->showYNValueForAttribute("EUH", $orig_col_name, $lang);

            $answer_list = array("Y" => $this_yes_label, "W" => $this_dkn_label, "N" => $this_no_label);

            //die("answer_list of $col_name($orig_col_name) and lang=$lang is ".var_export($answer_list,true));

            if (isset($desc["ANSWER"]) && !empty($desc["ANSWER"])) {
                $temp_answer_val = explode('|', $desc["ANSWER"]);
                if (count($temp_answer_val) == 3) {
                    $answer_list["Y"] = $temp_answer_val[0];
                    $answer_list["N"] = $temp_answer_val[1];
                    $answer_list["W"] = $temp_answer_val[2];
                }
            }
            $type_input_ret = "select";
            if ($obj->fixm_disable) {

                $type_input_ret = "hidden";
            ?>
                <input type="hidden" id="<?= $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                <span class='momken-yn'><?php if (!$obj->hideQeditCommonFields) echo $answer_list[$val] ?></span>
            <?php 
            } elseif ($desc["CHECKBOX"]) {
                if ($val == "Y") $checkbox_checked = "checked";
                else $checkbox_checked = "";

                $checkbox_extra_class = $desc["CHECKBOX_CSS_CLASS"];
            ?>
                <input type="checkbox" value="1" id="<?= $col_name ?>" name="<?= $col_name ?>" <?= $checkbox_checked ?> class="inputqe echeckbox <?= $checkbox_extra_class ?>">
            <?php 
            } else {
                if ($obj->qedit_minibox)
                    $css_class = "form-control";
                else $css_class = $class_inputSelectcourt . $data_loaded_class . $data_length_class;

                select(
                    $answer_list,
                    array($val),
                    array(
                        "class" => $css_class,
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "onchange" => $onchange,
                    ),
                    "asc"
                );
            }

            break;

        case 'TIME':
            if ($obj->fixm_disable) {

                $type_input_ret = "hidden";
                ?>
                <input type="hidden" fw="momken-1" id="<?= $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                <span class='fw-momken-date'><?php if (!$obj->hideQeditCommonFields) echo $val ?></span>
                <?php 
            } 
            else 
            {
                if ($desc["FORMAT"] == "CLASS") 
                {
                    $helpClass = $desc["ANSWER_CLASS"];
                    $helpMethod = $desc["ANSWER_METHOD"];

                    $answer_list = $helpClass::$helpMethod();
                }
                elseif ($desc["FORMAT"] == 'OBJECT') {
                    $helpMethod = $desc["ANSWER_METHOD"];
                    $answer_list = $obj->$helpMethod();
                }
                else
                {
                    if ($desc["ANSWER_LIST"]) 
                    {
                        list($start, $increment, $end) = explode("/", $desc["ANSWER_LIST"]);
                    }
                    else 
                    {
                        $start = 6;
                        $increment = 30;
                        $end = 22;
                    }

                    $answer_list = AfwDateHelper::getTimeArray($start, $increment, $end);
                    
                }
                if (!$answer_list[$val]) $answer_list[$val] = $val;
                // die(var_export($answer_list,true));
                select(
                    $answer_list,
                    array($val),
                    array(
                        "class" => "form-control",
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "onchange" => $onchange,
                        "style" => $input_style,
                    ),
                    "asc"
                );
            }
            break;
        case 'GDAT':
        case 'GDATE':
            if ($obj->fixm_disable) {

                $type_input_ret = "hidden";
                ?>
                <input type="hidden" fw="momken-1" id="<?= $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                <span class='fw-momken-date'><?php if (!$obj->hideQeditCommonFields) echo $val ?></span>
                <?php 
            } 
            else 
            {
                // remove time if exists
                list($val,) = explode(" ", $val);

                // default defined or today
                $today = date("Y-m-d");
                if (strtolower($desc["DEFAULT"]) == "today") $desc["DEFAULT"] = $today;
                if (!$val) $val = $desc["DEFAULT"] ? $desc["DEFAULT"] : $today;

                $val_GDAT = AfwDateHelper::inputFormatDate($val);;

                $input_name = $col_name;

                $min_date = $desc["MIN_DATE"] ? $desc["MIN_DATE"] : -99999;
                $max_date = $desc["MAX_DATE"] ? $desc["MAX_DATE"] : 99999;
                include("tpl/helper_edit_gdat.php");
            }
            break;
        case 'DATE':
            if ($obj->fixm_disable) {

                $type_input_ret = "hidden";
                ?>
                <input type="hidden" fw="momken-1" id="<?= $col_name ?>" name="<?php  echo $col_name ?>" value="<?php  echo $val ?>">
                <span class='fw-momken-date'><?php if (!$obj->hideQeditCommonFields) echo $val ?></span>
                <?php 
            } 
            else 
            {
                $mode_hijri_edit = true;
                $type_input_ret = "text";
                $input_name = $col_name;
                $valaff = AfwDateHelper::displayDate($val);
                if ($valaff)
                    $valaff_n = "الموافق لـ " . AfwDateHelper::hijriToGreg($valaff) . " نـ";
                else
                    $valaff_n = "";
                ?>
                <table class="table_no_border">
                    <tr class="table_no_border_tr">
                        <td><input placeholder="<?= $placeholder ?>" type="text" id="<?= $input_name ?>" name="<?= $col_name ?>" value="<?= $valaff ?>" class="inputqe <?= $class_inputDate . $data_loaded_class . " inputcourt" ?>" onchange="<?php  echo $onchange ?>"> </td>
                        <td><span>هـ</span></td>
                        <!-- <td><input type="text" id="<?= $input_name . "_n" ?>" name="<?= $col_name . "_n" ?>" value="<?= $valaff_n ?>" class="inputtext_disabled inputcourt" disabled></input></td>-->
                        <script type="text/javascript">
                            $('#<?= $input_name ?>').calendarsPicker({
                                calendar: $.calendars.instance('UmmAlQura')
                            });
                        </script>
                    </tr>
                </table>
        <?php  
            }
            break;
        default:
            $type_input_ret = "text";
            if ($obj->qedit_minibox)
                $css_class = "form-control";
            else $css_class = $class_inputText . $data_loaded_class;

        ?> <input placeholder="<?= $placeholder ?>" type="text" tabindex="<?= $qedit_orderindex ?>" class="inputqe <?= $css_class ?>" name="<?php  echo $col_name ?>" id="<?php  echo $col_name ?>" value="<?php  echo $val ?>" size=32 maxlength=255 onchange="<?php  echo $onchange ?>">
    <?php  break;
    }

    return $type_input_ret;
}



function select($list_id_val, $selected = array(), $info = array(), $ordre = "", $null_val = true)
{
    global $lang;

    // @todo not all time should be well studied
    // if(count($list_id_val)==0) return;

    if(!is_array($list_id_val)) throw new AfwRuntimeException("qedit motor select method should receive as first parameter an array of id => value but got `$list_id_val` value");

    switch (strtolower($ordre)) {
        case 'asc':
            $list_val = array();
            foreach ($list_id_val as $id => $val)
                $list_val[$id] = '' . $val;
            $list_id_val = subval_sort($list_id_val, $list_val, "asc");
            break;
        case 'desc':
            $list_val = array();
            foreach ($list_id_val as $id => $val)
                $list_val[$id] = '' . $val;
            $list_id_val = subval_sort($list_id_val, $list_val, "desc");
            break;
        default:
            break;
    }
    $multi = "";
    if (isset($info["multi"]) && $info["multi"])
        $multi = " multiple";
    $size = 1;
    if (isset($info["size"]))
        $size = intval($info["size"]);
    $count = count($list_id_val);
    if (!empty($multi) && $count < $size)
        $size = $count;
    if (!$info["id"]) $info["id"] = trim(trim($info["name"], "]"), "[");
    ?>
    <script>
        <?php 

        echo $info["reloadfn"] . "\n\n";
        // rafik @todo check why I put this below I now disabled it
        // disabled :
        echo $info["onchangefn"] . "\n\n";

        if (!$info["onchange"]) $info["onchange"] = $info["name"] . "_onchange()";

        ?>
    </script>
    <?php 
        // $selected_ve = var_export($selected,true);
        // echo "<!-- selected = $selected_ve -->";
    ?>
    <select class="inputqe <?php  echo $info["class"] ?>" style="<?php  echo $info["style"] ?>" name="<?php  echo $info["name"] ?>" id="<?php  echo $info["id"] ?>" tabindex="<?php  echo $info["tabindex"] ?>" onchange="<?php  echo $info["onchange"] ?>" <?php  echo $multi ?> size=<?php  echo $size ?> <?php  if ($info["disable"]) echo "disabled" ?>>
        <?php  if ($null_val) {
        ?> <option value="0" <?php  echo (in_array(0, $selected)) ? " selected" : ""; ?>>&nbsp;</option>
        <?php    }
        $data_content = "";
        foreach ($list_id_val as $id => $val) {
            if ($info["bsel_css"]) {
                $opt_css = $info["bsel_css"][$id];
                $data_content = "data-content=\"<span class='$opt_css'>$val</span>\"";
            }
            // <!-- '$id' not in selected -->
        ?> <option value="<?php  echo $id ?>" <?php  echo (in_array($id, $selected)) ? " selected" : ""; ?> <?php  echo $data_content ?>><?php  echo $val ?></option> 
        <?php    } ?>
    </select>
    <?
    if ($multi) {
    ?>
        <!-- Initialize the plugin: -->
        <script type="text/javascript">
            $(document).ready(function() {
                $('#<?php  echo $info["id"] ?>').multiselect({
                    inheritClass: true,

                    <?php  if ($info["numberDisplayed"]) { ?> numberDisplayed: '<?= $info["numberDisplayed"] ?>',
                    <?php  } ?>
                    <?php  if ($info["buttonWidth"]) { ?> buttonWidth: '<?= $info["buttonWidth"] ?>',
                    <?php } ?>
                    <?php if ($info["dropRight"]) { ?> dropRight: true,
                    <?php } ?>
                    <?php if ($info["inheritClass"]) { ?> inheritClass: true,
                    <?php } ?>
                    <?php if ($info["enableFiltering"]) { ?> enableFiltering: true,
                    <?php } ?>
                    <?php if ($info["filterBehavior"]) { ?> filterBehavior: '<?= $info["filterBehavior"] ?>',
                    <?php } ?>
                    <?php if ($info["filterPlaceholder"]) { ?> filterPlaceholder: '<?= $info["filterPlaceholder"] ?>',
                    <?php } ?>
                    <?php if ($info["maxHeight"]) { ?> maxHeight: <?= $info["maxHeight"] ?>,
                    <?php } ?>
                    <?php if ($info["includeSelectAllOption"]) { ?> includeSelectAllOption: true<?php } ?>
                });
            });
        </script>
    <?
    }
    ?>
<?php 
}

function subval_sort($table_a_trie, $table_ref, $ord = "desc")
{
    $res = array();
    if ($ord == "asc")
        asort($table_ref);
    else
        arsort($table_ref);
    foreach ($table_ref as $key => $val)
        $res[$key] = $table_a_trie[$key];
    return $res;
}

function calendar_translations($lang)
{

    if ($lang == "ar") return 'monthNames: ["يناير","فبراير","مارس","أبريل","ماي","يونيو", "يوليو","أغسطس","سبتمير","أكتوبر","نوفمبر","ديسمبر"],
monthNamesShort: ["يناير","فبراير","مارس","أبريل","ماي","يونيو", "يوليو","أغسطس","سبتمير","أكتوبر","نوفمبر","ديسمبر"],
dayNames: ["الأحد", "الأثنين", "الثلاثاء", "الاربعاء", "الخميس", "الجمعة", "السبت" ],
dayNamesShort: ["أحد", "أثنين", "ثلاثاء", "اربعاء", "خميس", "جمعة", "سبت" ],
dayNamesMin: [ "أحد","اثن","ثلث","ربع","خمس","جمع","سبت" ],';

    return 'monthNames: ["January","February","March","April","May","June", "July","August","September","October","November","December"],
monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],
dayNamesShort: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
dayNamesMin: [ "Su","Mo","Tu","We","Th","Fr","Sa" ],';
}
?>