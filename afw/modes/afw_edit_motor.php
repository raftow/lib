<?php
#####################################################################################
####################################  FONCTIONS  ####################################
#####################################################################################

function hidden_input($col_name, $desc, $val, &$obj)
{
    $type_input_ret = "hidden";
    include("tpl/helper_edit_hidden.php");
    return $type_input_ret;
}

/**
 * 
 * @param AFWObject $obj
 */

function type_input($col_name, $desc, $val, &$obj, $separator, $data_loaded = false, $force_css = "", $qedit_orderindex = 0, $data_length_class_default_for_fk = "inputmoyen")
{
    global $Main_Page, $_GET, $_POST,        
        $lang, $mode_hijri_edit,  $objme;

    $editor = $desc["EDITOR"];

    if($editor)
    {
        $this_dir_name = dirname(__FILE__);
        $buttonTitleMethod = $editor["buttonTitleMethod"];
        $paramsMethod = $editor["paramsMethod"];
        $objectAttribute = $editor["buttonTitleObjectAttribute"];
        $buttonTitle = $obj->$buttonTitleMethod($lang, $objectAttribute);
        $params = $obj->$paramsMethod($objectAttribute);
        $jsFunction = $editor["jsFunction"];
        require($this_dir_name."/../../../".$editor["src"]);

        if($editor["full"]) return "custom";
    }

    $mode_qedit = false;

    if (!$Main_Page) $Main_Page = $_GET["Main_Page"];
    if (!$Main_Page) $Main_Page = $_POST["Main_Page"];

    if (($Main_Page == "afw_mode_ddb.php") or ($Main_Page == "afw_mode_qedit.php") or ($Main_Page == "afw_handle_default_qedit.php")) {
        $mode_qedit = true;
    }

    if ($mode_qedit) 
    {
        $qeditCount = $obj->qeditCount;
        $qeditNomCol = $obj->qeditNomCol;
        if (!$qeditNomCol) $qeditNomCol = $col_name;
        $orig_col_name = $qeditNomCol;
    } else {
        $orig_col_name = $col_name;
    }

    //$col_title = $obj->getKeyLabel($orig_col_name,$lang);
    $col_title = $obj->translate($orig_col_name, $lang);

    $placeholder_standard_code = "placeholder-$orig_col_name";
    $placeholder_code = $desc["PLACE-HOLDER"];
    if(!$placeholder_code) $placeholder_code = $placeholder_standard_code;

    if ($placeholder_code==$placeholder_standard_code) $placeholder = $obj->getAttributeLabel($placeholder_code, $lang);
    elseif ($placeholder_code) $placeholder = $obj->translateMessage($placeholder_code, $lang);
    else $placeholder = "";

    if ((!$placeholder) or ($placeholder == $placeholder_standard_code)) 
    {
        if (($desc["MANDATORY"]) and ($desc["TYPE"] != "TEXT")) {
            $instruction_code = "INSTR-" . $desc["TYPE"];
            $instruction = $obj->translateOperator($instruction_code, $lang);
            if ($instruction == $instruction_code) $instruction = $obj->translateOperator("INSTR-STD", $lang);
            $placeholder = $instruction . " " . $col_title;
        } elseif (($desc["EMPTY_IS_ALL"]) or ($desc["FORMAT"] == "EMPTY_IS_ALL")) {
            $placeholder_code = "ALL-$orig_col_name";
            $placeholder = $obj->translate($placeholder_code, $lang);
            if ($placeholder == $placeholder_code) $placeholder = $obj->translateOperator("ALL", $lang);
        } else {
            $placeholder = "";
        }
    }
    if ($desc["INPUT-STYLE"]) $input_style = "style='" . $desc["INPUT-STYLE"] . "'";
    else $input_style = "";


    $themeArr = AfwThemeHelper::loadTheme();
    foreach($themeArr as $theme => $themeValue)
    {
        $$theme = $themeValue;
    }

    $images = AfwThemeHelper::loadTheme();

    $type_input_ret = "";

    if ($data_loaded) $data_loaded_class = "data_loaded";
    else $data_loaded_class = "data_notloaded";

    if (AfwStringHelper::stringStartsWith($col_name, "titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
    if (AfwStringHelper::stringStartsWith($col_name, "titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
    if (AfwStringHelper::stringStartsWith($col_name, "titre") && (!$desc["SIZE"])) $desc["SIZE"] = 255;


    $data_length_class = " inputlong";
    $old_WHERE = $obj->decodeText($desc["WHERE"]);
    $desc["WHERE"] = $obj->getWhereOfAttribute($orig_col_name);
    if (!$desc["WHERE"]) $desc["WHERE"] = $old_WHERE;
    //if($col_name=="id_sh_org_1") die("obj->getWhereOfAttribute($orig_col_name) = ".$desc["WHERE"]);            
    $readonly = "";

    if ($desc["READONLY"]) 
    {
        $readonly = "readonly";
    }

    if ($desc["JS-COMPUTED-READONLY"]) 
    {
        $readonly = "readonly";
    }

    if (true) 
    {
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

    if ($desc["TITLE_BEFORE"]) 
    {
    ?>
        <div class='title_before title_<?php echo $col_name; ?>'><?php echo $obj->tm($desc["TITLE_BEFORE"]) ?></div>
        <?php
    }

    if ($desc["REQUIRED"] or $desc["MANDATORY"]) $input_required = "required='true'";
    else $input_required = "";
    if ($desc["REQUIRED"] or $desc["MANDATORY"]) $is_required = true;
    else $is_required = false;


    $input_disabled = $disabled = $desc["DISABLED"];

    switch ($desc["TYPE"]) 
    {
        case 'PK':
            if ($val <= 0) 
            {
                $descHid = array();
                $type_input_ret = hidden_input($col_name, $descHid, $val, $obj);
                break;
            } 
            else 
            {
                $type_input_ret = "text";
            ?>
                <input type="text" class="form-control form-pk" name="<?php echo $col_name ?>" value="<?php echo $val ?>" size=32 maxlength=255 readonly>
                <?php
            }
            break;
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

            
            $objRep  = new $nom_class_fk;

            $list_count = AfwSession::config("$nom_class_fk::estimated_row_count", 0);

            $auto_c = $desc["AUTOCOMPLETE"];

            $LIMIT_INPUT_SELECT = AfwSession::config("LIMIT_INPUT_SELECT", 20);
            $auto_complete_default = ((!isset($desc["AUTOCOMPLETE"])) and ($list_count > $LIMIT_INPUT_SELECT));
            if ((!$auto_c)  and (!$auto_complete_default)) 
            {
                // list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc, $val, $obj, true);
                // $l_rep = AfwHtmlHelper::constructDropDownItems($liste_rep, $lang, $col_name, "$mdl.$myTbl", var_export($desc,true));
                
                $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;
                $l_rep = AfwLoadHelper::vhGetListe($objRep, $col_name, $obj->getTableName(), $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
                //if(get_class($objRep)=="Module")    die("AfwLoadHelper::vhGetListe=>".var_export($l_rep,true));
                //list($mdl, $myTbl) = $obj->getThisModuleAndAtable();
                // if($col_name=="data_auser_mfk") die("<b> => desc = ".var_export($desc,true));
                // die("<b> => l_rep = ".var_export($l_rep,true)."</b><BR> liste_rep = ".var_export($liste_rep,true));
                // $liste_rep_count = count($liste_rep);
                $l_rep_count = count($l_rep);
                if ($objme and $objme->isAdmin()) echo "<!-- for $col_name : $sql dropdowncount=$l_rep_count -->";

                if ($placeholder != $col_title) {
                    $empty_item = $placeholder;
                } else {
                    $empty_item = "";
                }

                $prop_sel =
                    array(
                        "class" => "form-control form-select",
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "style" => $input_style,
                        "empty_item" => $empty_item,
                        "reloadfn" => AfwJsEditHelper::getJsOfReloadOf($obj, $col_name),
                        "onchange" => $onchange . AfwJsEditHelper::getJsOfOnChangeOf($obj, $col_name),
                        "onchangefn" => AfwJsEditHelper::getJsOfOnChangeOf($obj, $col_name, $descr = "", false),
                        "required" => $is_required,
                        "disabled" => $disabled,
                    );
                    
                if(!$desc["DEPENDENT_OFME"]) unset($prop_sel["onchangefn"]);

                if ($obj->fixm_disable) 
                {
                    $descHid = array();
                    if (!$obj->hideQeditCommonFields) $descHid["TITLE_AFTER"] = $l_rep[$val];
                    $type_input_ret = hidden_input($col_name, $descHid, $val, $obj);
                
                } 
                else 
                {
                    select(
                        $l_rep,
                        array($val),
                        $prop_sel
                    );
                    $type_input_ret = "select";
                }
            } 
            else 
            {
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
                $atc_input_normal = $data_loaded_class . " inputlongmoyen";

                if ($auto_c_create) {
                    $class_icon = "new";
                    $atc_input_modified_class = $data_loaded_class . $data_length_class . " new_record";
                } else {
                    $class_icon = "notfound";
                    $atc_input_modified_class = $data_loaded_class . $data_length_class . " record_not_found";
                }

                if ($obj->fixm_disable) 
                {
                    $descHid = array();
                    if (!$obj->hideQeditCommonFields) $descHid["TITLE_AFTER"] = "[$val_display]";
                    $type_input_ret = hidden_input($col_name, $descHid, $val, $obj);
                } 
                else 
                {
                    $help_atc = $auto_c["HELP"];
                    $depend = AfwJsEditHelper::getDependencyIdsArray($obj, $col_name, $desc);
                    if(!$depend) $depend = "0";
                ?>
                    <div class='hzm_input_atc'>
                        <table cellspacing='0' cellpadding='0' style="width:100%">
                            <tr style="background-color: rgba(255, 255, 255, 0);">
                            <?php
                                if(!$placeholder) $placeholder = "اكتب بعض الكلمات للبحث";
                            ?>
                                <td style="padding:0px;margin:0px;background-color: rgba(255, 255, 255, 0);"><input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" value="<?php echo $val ?>" readonly></td>
                              
                                <td style="padding:0px;margin:0px;">
                                    <input placeholder="<?php echo $placeholder ?>" type="text" id="<?php echo $col_name_atc ?>" name="<?php echo $col_name_atc ?>" class="form-control form-autoc" value="<?php echo $val_display ?>" <?php echo $input_required ?>>
                                </td>
                                <?
                                if ($auto_c_create) {
                                ?>
                                    <th style="padding:0px;margin:0px;"><img src='../lib/images/create_new.png' data-toggle="tooltip" data-placement="top" title='لإضافة عنصر غير موجود في القائمة (بعد التثبت) انقر هنا ثم اكتب المسمى' onClick="empty_atc('<?php echo $col_name ?>');" style="width: 24px !important;height: 24px !important;" /></th>
                                <?
                                }
                                ?>
                                <td style="padding:0px;margin:0px;"><?php echo $help_atc ?></td>
                            </tr>
                        </table>
                    </div>
                    <script>
                        $(function() {
                            $("#<?php echo $col_name_atc ?>").autocomplete({
                                source: "../lib/api/autocomplete.php?cl=<?php echo $nom_class_fk ?>&currmod=<?php echo $nom_module_fk ?>&clp=<?php echo $clp ?>&idp=<?php echo $idp ?>&modp=<?php echo $modp ?>&attp=<?php echo $attp ?>&depend="+<?php echo $depend ?>,
                                minLength: 0,

                                change: function(event, ui) {
                                    if ($("#<?php echo $col_name_atc ?>").val() == "") {
                                        $("#<?php echo $col_name ?>").val("");
                                    }
                                    // $("#<?php echo $col_name_atc ?>").addClass('value_not_found');
                                    // $("#<?php echo $col_name ?>").val("");
                                    // $("#<?php echo $col_name ?>").attr('class', 'inputtrescourt cl_<?php echo $class_icon ?>_id');
                                    // $("#<?php echo $col_name_atc ?>").attr('class', '<?php echo $atc_input_modified_class ?>');
                                },


                                select: function(event, ui) {
                                    //alert(ui.item.id);
                                    $("#<?php echo $col_name ?>").val(ui.item.id);
                                    $("#<?php echo $col_name ?>").attr('class', 'inputtrescourt cl_id');
                                    $("#<?php echo $col_name_atc ?>").attr('class', 'form-control form-autoc');
                                    $("#<?php echo $col_name_atc ?>").addClass('input_changed');
                                },

                                html: true, // optional (jquery.ui.autocomplete.html.js required)

                                // optional (if other layers overlap autocomplete list)
                                open: function(event, ui) {
                                    $(".ui-autocomplete").css("z-index", 1000);
                                }
                            });

                        });
                    </script>

            <?php                    
                }
            }
            break;
        case 'MFK':
            $nom_table_fk   = $desc["ANSWER"];
            $nom_module_fk  = $desc["ANSMODULE"];
            if (!$nom_module_fk) 
            {
                $nom_module_fk = AfwUrlManager::currentWebModule();
            }
            $nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
            
            $objRep  = new $nom_class_fk;
            
            // list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc, $val, $obj);
            // list($mdl, $myTbl) = $obj->getThisModuleAndAtable();
            // $l_rep = AfwHtmlHelper::constructDropDownItems($liste_rep, $lang, $col_name, "$mdl.$myTbl");            
            $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;
            $l_rep = AfwLoadHelper::vhGetListe($objRep, $col_name, $obj->getTableName(), $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
            // if(get_class($objRep)=="Module")    die("AfwLoadHelper::vhGetListe=>".var_export($l_rep,true));

            $type_input_ret = "select";

            include("tpl/helper_edit_mfk.php");
            

            break;
        case 'MENUM':
            $fcol_name = $desc["FUNCTION_COL_NAME"];
            if(!$fcol_name) $fcol_name = $col_name;
        
            $liste_rep = AfwLoadHelper::getEnumTable($desc["ANSWER"], $obj->getTableName(), $fcol_name, $obj);
            //echo "menum val $val with sep $separator : <br>";
            $val_arr = explode($separator, trim($val, $separator));
            //print_r($val_arr);
            //echo "<br>";
            if ($force_css) $data_length_class = " " . $force_css;
            else $data_length_class = " inputmoyen";
            $type_input_ret = "select";

            include("tpl/helper_edit_menum.php");
            
            break;
        /*case 'ANSWER': obsolete
            $liste_rep = AFWObject::getAnswerTable($desc["ANSWER"], $desc["MY_PK"], $desc["MY_VAL"]);
            if ($force_css) $data_length_class = " " . $force_css;
            else $data_length_class = " inputmoyen";
            $LIMIT_INPUT_SELECT = AfwSession::config("LIMIT_INPUT_SELECT", 20);

            if (count($liste_rep) <= $LIMIT_INPUT_SELECT) {
                $type_input_ret = "select";
                select(
                    $liste_rep,
                    array($val),
                    array(
                        "class" => "form-control form-answer",
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "onchange" => $onchange,
                        "style" => $input_style,
                        "required" => $is_required,

                    ),
                    "asc"
                );
            } 
            else 
            {
                $type_input_ret = "text";
                include("tpl/helper_edit_answer_popup.php");
          
            }
            break;*/

        case 'ENUM':
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
            //if(!$liste_rep) 
            //throw new AfwRuntimeException("for col $orig_col_name enum liste_rep comes from $answer_case is null or empty  liste_rep = ".var_export($liste_rep,true));
            

            // die("for enum col : $col_name, $answer_case, liste_rep = ".var_export($liste_rep,true));

            //if($desc["FORMAT-INPUT"]=="hzmtoggle") $obj->_error("enum liste_rep comes from $answer_case : ".var_export($liste_rep,true));
            include("tpl/helper_edit_enum.php");
            break;

        case 'PCTG':
        case 'INT':
        case 'FLOAT':
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
                if ($desc["TYPE"] == 'INT') 
                {
                    $input_type_html = "number";
                    $input_options_html = "";
                    if ($desc["FORMAT"]) {
                        list($format_type, $format_param1, $format_param2, $format_param3) = explode(":", $desc["FORMAT"]);      // ex FORMAT=>"STEP:0:3:1"  or DROPDOWN=>"STEP:0:3:1"
                        if ($format_type == "STEP") {
                            if (!$format_param3) $format_param3 = 1;
                            $input_options_html = " step='$format_param3' min='$format_param1' max='$format_param2' ";
                        } elseif ($format_type == "DROPDOWN") {
                            if (!$format_param3) $format_param3 = 1;
                            $dropdown_min = intval($format_param1);
                            $dropdown_max = intval($format_param2);
                            $dropdown_step = intval($format_param3);
                        }
                    }
                }

                if ($force_css) $data_length_class = " " . $force_css;
                else $data_length_class = " inputcourt";
                $type_input_ret = "text";
                if ($desc["JS-COMPUTED"]) {
                    if ($obj->class_of_input_computed_readonly) $class_of_input = $obj->class_of_input_computed_readonly;

                    if ($obj->class_js_computed) $class_js_computed = $obj->class_js_computed;
                    else $class_js_computed = "js_computed";

                    $data_loaded_class = $class_js_computed;
                }
                include("tpl/helper_edit_numeric.php"); 
            }
            break;
            case 'TIME':
                if ($desc["FORMAT"] == "CLOCK") 
                {
                    $valaff = $val;
                    list($startHH, $increment, $endHH, $startNN, $endNN) = explode("/", $desc["ANSWER_LIST"]);
                    if(!$startNN) $startNN = 0;
                    if(!$endNN) $endNN = 0;
                    $input_name = $col_name;

                    $minimum = AfwDateHelper::formatTimeHHNN($startHH, $startNN);
                    $maximum = AfwDateHelper::formatTimeHHNN($endHH, $endNN);
                    clock($col_name, $input_name, $valaff, $minimum, $maximum, $onchange, $input_style="", $increment, $is_required, $separator=':', $duration=false);
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
                            "class" => "form-control hzm_time",
                            "name"  => $col_name,
                            "id"  => $col_name,
                            "tabindex" => $qedit_orderindex,
                            "onchange" => $onchange,
                            "style" => $input_style,
                            "required" => $is_required,
                        ),
                        "asc"
                    );
                }

            
            break;

        case 'TEXT':
                $utf8 = $desc["UTF8"];
                $fromListMethod = $desc["FROM_LIST"];
                $dir = $desc["DIR"];
                if (!$dir) ($lang != "ar") ? $dir = "ltr" : $dir = "rtl";
                if ($dir == "auto") $dir = ($utf8 ? "rtl" : "ltr");
                if ($desc["INPUT-FORMATTING"] == "addslashes") $val = addslashes($val);
                $css_class = $desc["CSS"];
                if ((isset($desc["SIZE"])) && (($desc["SIZE"] == "AREA") or ($desc["SIZE"] == "AEREA"))) 
                {
                    $rows = $desc["ROWS"];
                    if (!$rows) $rows = 4;
                    $cols = $desc["COLS"];
                    if (!$cols) $cols = 43;
                    $type_input_ret = "text";
                    if ((!$desc["MANDATORY"]) and (!$desc["REQUIRED"])) {
                        $desc["MIN-SIZE"] = 0;
                    }
    
                    if ($desc["MIN-SIZE"] == 1) $desc["MIN-SIZE"] = 0;
    
                    if (!$desc["PLACEHOLDER-NO-CHANGE"]) {
                        if (($desc["MIN-SIZE"]) and ($desc["MAXLENGTH"])) {
                            if ($placeholder) $placeholder .= " : ";
                            $placeholder .= "عدد الكلمات  بين " . $desc["MIN-SIZE"] . " و " . $desc["MAXLENGTH"] . " كلمة";
                        } elseif ($desc["MIN-SIZE"]) {
                            if ($placeholder) $placeholder .= " : ";
                            $placeholder .= "عدد الكلمات  الأدنى " . $desc["MIN-SIZE"] . " كلمة";
                        } elseif ($desc["MAXLENGTH"]) {
                            if ($placeholder) $placeholder .= " : ";
                            $placeholder .= "عدد الكلمات  الأقصى " . $desc["MAXLENGTH"] . " كلمة";
                        }
                    }
                    include("tpl/helper_edit_textarea.php");
                }
                elseif($fromListMethod)
                {
                    $fromList = $obj->$fromListMethod();
                    //echo "val=$val<br>";
                    select(
                        $fromList,
                        array(trim($val)),
                        array(
                            "class" => "form-control form-select",
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
                    $maxlength = $desc["MAXLENGTH"];
                    $fld_size = $desc["SIZE"];
    
                    if ($desc["INPUT-FORMATTING"] == "value-1-cote") $val_sentence = "value='$val'";
                    else $val_sentence = "value=\"$val\"";
    
                    if (($force_css) and (!$desc["WIDTH-FROM-SIZE"])) $data_length_class = " " . $force_css;
                    else if (isset($desc["SIZE"]) && $desc["SIZE"] <= 16)  $data_length_class = " inputcourt";
                    else if (isset($desc["SIZE"]) && $desc["SIZE"] <= 41)  $data_length_class = " inputmoyen";
                    else if (isset($desc["SIZE"]) && $desc["SIZE"] <= 84)  $data_length_class = " inputlong";
                    else if (isset($desc["SIZE"]) && $desc["SIZE"] < 255)  $data_length_class = " inputtreslong";
                    else $data_length_class = " inputultralong";
                    $type_input_ret = "text";
                    include("tpl/helper_edit_text.php");            
                }
                break;

        case 'YN':

            if ($force_css) $data_length_class = " " . $force_css;
            else $data_length_class = "";

            $answer_list = array();

            $remove_options_arr = $desc["REMOVE_OPTIONS"];

            if (!$remove_options_arr["Y"]) $this_yes_label = $obj->showYNValueForAttribute("YES", $col_name, $lang);
            if (!$remove_options_arr["N"]) $this_no_label  = $obj->showYNValueForAttribute("NO", $col_name, $lang);
            if (!$remove_options_arr["W"]) $this_dkn_label = $obj->showYNValueForAttribute("EUH", $col_name, $lang);

            if (!$remove_options_arr["Y"]) $answer_list["Y"] = $this_yes_label;
            if (!$remove_options_arr["W"]) $answer_list["W"] = $this_dkn_label;
            if (!$remove_options_arr["N"]) $answer_list["N"] = $this_no_label;


            if (isset($desc["ANSWER"]) && !empty($desc["ANSWER"])) {
                $temp_answer_val = explode('|', $desc["ANSWER"]);
                if (count($temp_answer_val) == 3) {
                    if (!$remove_options_arr["Y"]) $answer_list["Y"] = $temp_answer_val[0];
                    if (!$remove_options_arr["N"]) $answer_list["N"] = $temp_answer_val[1];
                    if (!$remove_options_arr["W"]) $answer_list["W"] = $temp_answer_val[2];
                }
            }

            include("tpl/helper_edit_yn.php");
            break;

        case 'DATE':
            $mode_hijri_edit = true;
            $type_input_ret = "text";
            $input_name = $col_name;
            $valaff = AfwDateHelper::displayDate($val);
            if ($valaff)
                $valaff_n = "الموافق لـ " . AfwDateHelper::hijriToGreg($valaff) . " نـ";
            else
                $valaff_n = "";
            // echo "date=$valaff / $val <br>";
            include("tpl/helper_edit_date.php");
            break;

        case 'GDAT':
        case 'GDATE':
            // remove time if exists
            list($val,) = explode(" ", $val);

            // default defined or today
            
            if (!$val) 
            {
                if(trim(strtolower($desc["DEFAULT"])) == "today") $val = date("Y-m-d");
                if($desc["DEFAULT"]) $val = $desc["DEFAULT"];
            }

            $val_GDAT = AfwDateHelper::inputFormatDate($val);;

            $input_name = $col_name;

            $min_date = $desc["MIN_DATE"] ? $desc["MIN_DATE"] : -99999;
            $max_date = $desc["MAX_DATE"] ? $desc["MAX_DATE"] : 99999;

            // echo "date=$val_GDAT / $val <br>";
            include("tpl/helper_edit_gdat.php");
            break;

        default:
            $type_input_ret = "text";
            include("tpl/helper_edit_default.php");
            break;
    }

    return $type_input_ret;
}

function clock($col_name, $input_name, $valaff, $minimum, $maximum, $onchange, $input_style="", $precision=10, $required=false, $separator=':', $duration=false, $durationNegative=true)
{
    if($required)
    {
        $required = 'true';  
        $input_required = "required";
    } 
    else
    {
        $required = 'false';  
        $input_required = "";
    }

    if($duration)
    {
        $duration = 'true';  
    } 
    else
    {
        $duration = 'false';  
    }

    if($durationNegative)
    {
        $durationNegative = 'true';  
    } 
    else
    {
        $durationNegative = 'false';  
    }
    ?>
        <input type="text" id="<?php echo $input_name ?>" name="<?php echo $col_name ?>" value="<?php echo $valaff ?>" class="form-control form-time <?php echo $input_name ?>" onchange="<?php echo $onchange ?>" <?php echo $input_style ?> <?php echo $input_required ?>>
        <script>
            $(document).ready(function(){
                $("#<?php echo $input_name ?>").clockTimePicker({
                    required:<?php echo $required?>,
                    separator:'<?php echo $separator?>',
                    precision:<?php echo $precision?>,
                    duration:<?php echo $duration?>, 
                    minimum:'<?php echo $minimum?>', 
                    maximum:'<?php echo $maximum?>',
                    durationNegative:<?php echo $durationNegative?>
                });
            });
        </script>
    <?
}

function mobiselector($list_id_val, $selected = array(), $info = array())
{
    global $lang;

    if (count($list_id_val) > 7) 
    {
        $info["enableFiltering"] = true;
        $info["numberDisplayed"] = 3;
        $info["filterPlaceholder"] = "اكتب كلمة للبحث";
    }
    else
    {
        $info["enableFiltering"] = false;
        $info["filterPlaceholder"] = "اختيار";
    }

    $multi = " multiple";
?>


<script>

mobiscroll.setOptions({
  locale: mobiscroll.localeAr,
  theme: 'ios',
  themeVariant: 'light'
});

$(function () {
  $('#<?php echo $info["id"] ?>')
    .mobiscroll()
    .select({
      inputElement: document.getElementById('<?php echo $info["id"] ?>-input'),
      filter: <?php echo $info["enableFiltering"] ? "true" : "false" ?>,
    });
});
</script>  

<label>
    <input mbsc-input id="<?php echo $info["id"] ?>-input" placeholder="<?php echo $info["filterPlaceholder"] ?>" data-dropdown="true" data-input-style="outline" data-label-style="stacked" data-tags="true" />
</label>
    <select class="<?php echo $info["class"] ?>" 
            name="<?php echo $info["name"] ?>" 
            id="<?php echo $info["id"] ?>" 
            tabindex="<?php echo $info["tabindex"] ?>" 
            onchange="<?php echo $info["onchange"] ?>" 
            <?php echo $multi ?> 
            <?php echo $info["style"] ?> 
            <?php if ($info["required"]) echo "required" ?>
        >
<?php 
        
        $data_content = "";
        

        foreach ($list_id_val as $id => $val) 
        {
            if ($info["bsel_css"]) {
                $opt_css = $info["bsel_css"][$id];
                $data_content = "data-content=\"<span class='$opt_css'>$val</span>\"";
            }
?> 
    <option value="<?php echo $id ?>" <?php echo (in_array($id, $selected)) ? " selected" : ""; ?> <?php echo $data_content ?>><?php echo $val ?></option>
<?php   
        } 
?>
    </select>
<?php

}

function select($list_id_val, $selected = array(), $info = array(), $sort_order = "", $null_val = true, $langue = "")
{
    global $lang;
    if (!$langue) $langue = $lang;
    // @todo not all time should be well studied
    // if(count($list_id_val)==0) return;


    if ($sort_order) {
        $list_val = array();
        foreach ($list_id_val as $id => $val) {
            if ($val instanceof AFWObject) $list_val[$id] = $val->getDropDownDisplay($langue);
            else $list_val[$id] = $val;
        }
        $sort_order = strtolower($sort_order);
        $list_id_val = subval_sort($list_id_val, $list_val, "asc");
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

    if (!$info["empty_item"]) $info["empty_item"] = "&nbsp;";

    ?>

    <script>
        <?php

        echo $info["reloadfn"] . "\n\n";
        // rafik @todo check why I put this below I now disabled it
        // disabled :
        echo $info["onchangefn"] . "\n\n";
        $on_change_standard = $info["name"] . "_onchange()";
        if (!$info["onchange"]) $info["onchange"] = "";
        else 
        {
            $info["onchange"] = trim($info["onchange"]);
            $info["onchange"] = trim($info["onchange"], ";");
        }
        if(!AfwStringHelper::stringContain($info["onchange"], $on_change_standard))
        {
            $info["onchange"] .= ";".$on_change_standard;
        }
        
        ?>
    </script>

    <select class="<?php echo $info["class"] ?>" name="<?php echo $info["name"] ?>" id="<?php echo $info["id"] ?>" tabindex="<?php echo $info["tabindex"] ?>" onchange="<?php echo $info["onchange"] ?>" <?php echo $multi ?> size=<?php echo $size ?> <?php echo $info["style"] ?> <?php if ($info["disable"] or $info["disabled"]) echo "disabled" ?> <?php if ($info["required"]) echo "required" ?>>
        <?php 
        if ($null_val) 
        {
            if ($info["required"]) 
            {
        ?> 
        <option></option>
        <?php
            } 
            else 
            {
        ?>
                <option value="0" <?php echo (in_array(0, $selected)) ? " selected" : ""; ?>><?php echo $info["empty_item"] ?></option>
        <?php
            }
        }
        $data_content = "";
        if (count($list_id_val) > 7) 
        {
            $info["enableFiltering"] = true;
            $info["numberDisplayed"] = 3;
            $info["filterPlaceholder"] = "اكتب كلمة للبحث";
        }

        foreach ($list_id_val as $id => $val) 
        {
            if ($info["bsel_css"]) {
                $opt_css = $info["bsel_css"][$id];
                $data_content = "data-content=\"<span class='$opt_css'>$val</span>\"";
            }
            ?> <option value="<?php echo $id ?>" <?php echo (in_array($id, $selected)) ? " selected" : ""; ?> <?php echo $data_content ?>><?php echo $val ?></option>
        <?php   
        } 
        ?>
    </select>
    <?
    if ($multi) 
    {
    ?>
        <!-- Initialize the plugin: -->
        <script type="text/javascript">
            $(document).ready(function() {
                $('#<?php echo $info["id"] ?>').multiselect({
                    inheritClass: true,

                    <? if ($info["numberDisplayed"]) { ?> numberDisplayed: '<?php echo $info["numberDisplayed"] ?>',
                    <? } ?>
                    <? if ($info["buttonWidth"]) { ?> buttonWidth: '<?php echo $info["buttonWidth"] ?>',
                    <? } ?>
                    <? if ($info["dropRight"]) { ?> dropRight: true,
                    <? } ?>
                    <? if ($info["inheritClass"]) { ?> inheritClass: true,
                    <? } ?>
                    <? if ($info["enableFiltering"]) { ?> enableFiltering: true,
                    <? } ?>
                    <? if ($info["filterBehavior"]) { ?> filterBehavior: '<?php echo $info["filterBehavior"] ?>',
                    <? } ?>
                    <? if ($info["filterPlaceholder"]) { ?> filterPlaceholder: '<?php echo $info["filterPlaceholder"] ?>',
                    <? } ?>
                    <? if ($info["maxHeight"]) { ?> maxHeight: <?php echo $info["maxHeight"] ?>,
                    <? } ?>
                    <? if ($info["includeSelectAllOption"]) { ?> includeSelectAllOption: true<? } ?>
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