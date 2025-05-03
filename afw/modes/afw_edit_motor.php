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

    if ($editor) {
        $this_dir_name = dirname(__FILE__);
        $buttonTitleMethod = $editor["buttonTitleMethod"];
        $paramsMethod = $editor["paramsMethod"];
        $objectAttribute = $editor["buttonTitleObjectAttribute"];
        $buttonTitle = $obj->$buttonTitleMethod($lang, $objectAttribute);
        $params = $obj->$paramsMethod($objectAttribute);
        $jsFunction = $editor["jsFunction"];
        require($this_dir_name . "/../../../" . $editor["src"]);

        if ($editor["full"]) return "custom";
    }

    $mode_qedit = false;

    if (!$Main_Page) $Main_Page = $_GET["Main_Page"];
    if (!$Main_Page) $Main_Page = $_POST["Main_Page"];

    if (($Main_Page == "afw_mode_ddb.php") or ($Main_Page == "afw_mode_qedit.php") or
        ($Main_Page == "afw_handle_default_ddb.php") or ($Main_Page == "afw_handle_default_qedit.php")) {
        $mode_qedit = true;
    }

    if ($mode_qedit) {
        $qeditCount = $obj->qeditCount;
        $qeditNomCol = $obj->qeditNomCol;
        if (!$qeditNomCol) $qeditNomCol = $col_name;
        $orig_col_name = $qeditNomCol;
        if(AfwStringHelper::stringEndsWith($orig_col_name, "_0")) die("dbg orig_col_name=$fcol_name case 10");
    } else {
        $orig_col_name = $col_name;
        if(AfwStringHelper::stringEndsWith($orig_col_name, "_0")) die("dbg orig_col_name=$fcol_name case 11 [Main_Page=$Main_Page]");
    }

    //$col_title = $obj->getKeyLabel($orig_col_name,$lang);
    $col_title = $obj->translate($orig_col_name, $lang);

    $placeholder_standard_code = "placeholder-$orig_col_name";
    $placeholder_code = $desc["PLACE-HOLDER"];
    if (!$placeholder_code) $placeholder_code = $placeholder_standard_code;

    if ($placeholder_code == $placeholder_standard_code) $placeholder = $obj->getAttributeLabel($placeholder_code, $lang);
    elseif ($placeholder_code) $placeholder = $obj->translateMessage($placeholder_code, $lang);
    else $placeholder = "";

    if ((!$placeholder) or ($placeholder == $placeholder_standard_code)) {
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
    foreach ($themeArr as $theme => $themeValue) {
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

    if ($desc["TITLE_BEFORE"]) {
?>
        <div class='title_before title_<?php echo $col_name; ?>'><?php echo $obj->tm($desc["TITLE_BEFORE"]) ?></div>
        <?php
    }

    if ($desc["REQUIRED"] or $desc["MANDATORY"]) $input_required = "required='true'";
    else $input_required = "";
    if ($desc["REQUIRED"] or $desc["MANDATORY"]) $is_required = true;
    else $is_required = false;


    $input_disabled = $disabled = $desc["DISABLED"];

    switch ($desc["TYPE"]) {
        case 'PK':
            if ($val <= 0) {
                $descHid = array();
                $type_input_ret = hidden_input($col_name, $descHid, $val, $obj);
                break;
            } else {
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

            include("tpl/helper_edit_fk.php");

            break;
        case 'MFK':

            include("tpl/helper_edit_mfk.php");


            break;
        case 'MENUM':
            $fcol_name = $desc["FUNCTION_COL_NAME"];
            if(AfwStringHelper::stringEndsWith($fcol_name, "_0")) die("dbg fcol_name=$fcol_name case -1");
            if (!$fcol_name) $fcol_name = $col_name;
            if(AfwStringHelper::stringEndsWith($fcol_name, "_0")) die("dbg fcol_name=$fcol_name case 0");

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
                if(AfwStringHelper::stringEndsWith($fcol_name, "_0")) die("dbg fcol_name=$fcol_name case 1");
                if (!$fcol_name) $fcol_name = $orig_col_name;
                if(AfwStringHelper::stringEndsWith($fcol_name, "_0")) die("dbg fcol_name=$fcol_name case 2");
                $liste_rep = AfwLoadHelper::getEnumTable($fieldAnsTab, $objTableName, $fcol_name, $obj);
                $answer_case = "AfwLoadHelper::get EnumTable($fieldAnsTab, $objTableName, $fcol_name, obj:$objName)";
            }
            //if(!$liste_rep) 
            //throw new AfwRuntimeE xception("for col $orig_col_name enum liste_rep comes from $answer_case is null or empty  liste_rep = ".var_export($liste_rep,true));


            // die("for enum col : $col_name, $answer_case, liste_rep = ".var_export($liste_rep,true));

            //if($desc["FORMAT-INPUT"]=="hzmtoggle") $obj->_error("enum liste_rep comes from $answer_case : ".var_export($liste_rep,true));
            include("tpl/helper_edit_enum.php");
            break;

        case 'PCTG':
        case 'INT':
        case 'FLOAT':
        case 'AMNT':
            $fromListMethod = $desc["FROM_LIST"];
            if ($fromListMethod) {
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
            } else {
                $input_type_html = "text";
                if ($desc["TYPE"] == 'INT') {
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
            if ($desc["FORMAT"] == "CLOCK") {
                $valaff = $val;
                list($startHH, $increment, $endHH, $startNN, $endNN) = explode("/", $desc["ANSWER_LIST"]);
                if (!$startNN) $startNN = 0;
                if (!$endNN) $endNN = 0;
                $input_name = $col_name;

                $minimum = AfwDateHelper::formatTimeHHNN($startHH, $startNN);
                $maximum = AfwDateHelper::formatTimeHHNN($endHH, $endNN);
                clock($col_name, $input_name, $valaff, $minimum, $maximum, $onchange, $input_style = "", $increment, $is_required, $separator = ':', $duration = false);
            } else {
                if ($desc["FORMAT"] == "CLASS") {
                    $helpClass = $desc["ANSWER_CLASS"];
                    $helpMethod = $desc["ANSWER_METHOD"];

                    $answer_list = $helpClass::$helpMethod();
                } elseif ($desc["FORMAT"] == 'OBJECT') {
                    $helpMethod = $desc["ANSWER_METHOD"];
                    $answer_list = $obj->$helpMethod();
                } else {
                    if ($desc["ANSWER_LIST"]) {
                        list($start, $increment, $end) = explode("/", $desc["ANSWER_LIST"]);
                    } else {
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
            if ((isset($desc["SIZE"])) && (($desc["SIZE"] == "AREA") or ($desc["SIZE"] == "AEREA"))) {
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
            } elseif ($fromListMethod) {
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
            } else {
                $maxlength = $desc["MAXLENGTH"];
                if(!$maxlength) $maxlength = 9999;
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

            if (!$val) {
                if (trim(strtolower($desc["DEFAULT"])) == "today") $val = date("Y-m-d");
                if ($desc["DEFAULT"]) $val = $desc["DEFAULT"];
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

function clock($col_name, $input_name, $valaff, $minimum, $maximum, $onchange, $input_style = "", $precision = 10, $required = false, $separator = ':', $duration = false, $durationNegative = true)
{
    if ($required) {
        $required = 'true';
        $input_required = "required";
    } else {
        $required = 'false';
        $input_required = "";
    }

    if ($duration) {
        $duration = 'true';
    } else {
        $duration = 'false';
    }

    if ($durationNegative) {
        $durationNegative = 'true';
    } else {
        $durationNegative = 'false';
    }
    ?>
    <input type="text" id="<?php echo $input_name ?>" name="<?php echo $col_name ?>" value="<?php echo $valaff ?>" class="form-control form-time <?php echo $input_name ?>" onchange="<?php echo $onchange ?>" <?php echo $input_style ?> <?php echo $input_required ?>>
    <script>
        $(document).ready(function() {
            $("#<?php echo $input_name ?>").clockTimePicker({
                required: <?php echo $required ?>,
                separator: '<?php echo $separator ?>',
                precision: <?php echo $precision ?>,
                duration: <?php echo $duration ?>,
                minimum: '<?php echo $minimum ?>',
                maximum: '<?php echo $maximum ?>',
                durationNegative: <?php echo $durationNegative ?>
            });
        });
    </script>
<?
}

function mobiselector($list_id_val, $selected = array(), $info = array())
{
    global $lang;

    if (count($list_id_val) > 7) {
        $info["enableFiltering"] = true;
        $info["numberDisplayed"] = 3;
        $info["filterPlaceholder"] = "اكتب كلمة للبحث";
    } else {
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

        $(function() {
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
    <script>
        <?php
        if ($info["reloadfn"]) {
            echo "// reload function for attribute : " . $info["name"] . "\n";
            echo $info["reloadfn"] . "\n\n";
        }
        ?>
    </script>
    <select class="<?php echo $info["class"] ?>"
        name="<?php echo $info["name"] ?>"
        id="<?php echo $info["id"] ?>"
        tabindex="<?php echo $info["tabindex"] ?>"
        onchange="<?php echo $info["onchange"] ?>"
        <?php echo $multi ?>
        <?php echo $info["style"] ?>
        <?php if ($info["required"]) echo "required" ?>>
        <?php

        $data_content = "";


        foreach ($list_id_val as $id => $val) {
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
        if ($info["reloadfn"]) {
            echo "// reload function for attribute : " . $info["name"] . "\n";
            echo $info["reloadfn"] . "\n\n";
        }

        // rafik @todo check why I put this below I now disabled it
        // disabled :
        echo $info["onchangefn"] . "\n\n";
        $on_change_standard = $info["name"] . "_onchange()";
        if (!$info["onchange"]) $info["onchange"] = "";
        else {
            $info["onchange"] = trim($info["onchange"]);
            $info["onchange"] = trim($info["onchange"], ";");
        }
        if (!AfwStringHelper::stringContain($info["onchange"], $on_change_standard)) {
            $info["onchange"] .= ";" . $on_change_standard;
        }

        ?>
    </script>

    <select class="<?php echo $info["class"] ?>" name="<?php echo $info["name"] ?>" id="<?php echo $info["id"] ?>" tabindex="<?php echo $info["tabindex"] ?>" onchange="<?php echo $info["onchange"] ?>" <?php echo $multi ?> size=<?php echo $size ?> <?php echo $info["style"] ?> <?php if ($info["disable"] or $info["disabled"]) echo "disabled" ?> <?php if ($info["required"]) echo "required" ?>>
        <?php
        if ($null_val) {
            if ($info["required"]) {
        ?>
                <option></option>
            <?php
            } else {
            ?>
                <option value="0" <?php echo (in_array(0, $selected)) ? " selected" : ""; ?>><?php echo $info["empty_item"] ?></option>
            <?php
            }
        }
        $data_content = "";
        if (count($list_id_val) > 7) {
            $info["enableFiltering"] = true;
            $info["numberDisplayed"] = 3;
            $info["filterPlaceholder"] = "اكتب كلمة للبحث";
        }

        foreach ($list_id_val as $id => $val) {
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
    if ($multi) {
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

function attributeEditDiv($obj, $col, $desc, $fgroup, $lang, $openedInGroupDiv, $info=null, $colErrors=[], $step_show_error=false)
{
    $idObj = $obj->getId();
    if (($col == "id") and (!$idObj)) $class_empty_object = "empty-obj";
    else $class_empty_object = "";

    if(!$info) $info = prepareEditInfoForColumn($obj, $col, $desc, $lang);

    $htmlDiv = "";
    $colspan = "";
    $css_class = "";
    $step_curr = $desc["STEP"];
    $no_fgroup = $desc["NO-FGROUP"];
    $new_fgroup = $desc["FGROUP"];
    $noheader_fgroup = $desc["FGROUP_NOHEADER"];
    $fgroup_behavior = $desc["FGROUP_BEHAVIOR"];
    if (!$new_fgroup) $new_fgroup = "default_fg";
    if ((!$no_fgroup) and ($new_fgroup) and ($fgroup != $new_fgroup)) {
        //if($new_fgroup=="prices_report") die("$fgroup != $new_fgroup : obj::DB_STRUCTURE[$col][FGROUP_BEHAVIOR] = $fgroup_behavior,  data = ".var_export($data,true));
        $fgroup = $new_fgroup;
        if ($fgroup_behavior) {
            if ($fgroup_behavior == "collapsed") {
                $collapse_status = "collapse";
                $collapsed_status = "expand collapsed";
            } else {
                $collapse_status = "collapse in";
                $collapsed_status = "expand";
            }
            $fgroup_toggle_html = " data-toggle='collapse' data-target='#group_$fgroup'";
            $fgroup_expanded_area = " aria-expanded='true'";
        } else {
            $collapse_status = "";
            $collapsed_status = "expanded_fixed";
            $fgroup_toggle_html = "";
            $fgroup_expanded_area = "";
        }

        $fgroupInfos = $obj->getFieldGroupInfos($fgroup);
        $fgroupcss = $fgroupInfos["css"];
        $new_fgroup_tr = $obj->getAttributeLabel($new_fgroup, $lang);
        if(!trim($new_fgroup_tr)) $new_fgroup_tr = $obj->getAttributeLabel("step".$step_curr, $lang);
        // close previous in-group div
        if ($openedInGroupDiv) {
            $htmlDiv .= "</div>";  // internal_group_div_close
            $htmlDiv .= "</div>";
            $openedInGroupDiv = false;
        }
        // echo "\n<tr><th class='fgroup_header' colspan='4'>$new_fgroup_tr</th></tr>\n";
        if ($noheader_fgroup) {
            $header_of_fgroup = "";
        } else {
            $header_of_fgroup = "<div class='$collapsed_status' $fgroup_toggle_html><h5 class='greentitle $new_fgroup'><i></i>$new_fgroup_tr</h5></div>";
        }

        $htmlDiv .= "$header_of_fgroup\n<div class='fgroup in-group-$new_fgroup cssgroup_$fgroupcss' > \n";
        $internal_new_group_div_open = "<div id='group_$fgroup' class='$collapse_status' aria-expanded='true' style=''>\n";
        $openedInGroupDiv = true;
    } else {
        $internal_new_group_div_open = "";
    }
    $css_custom = $desc['CSS'];
    if (!$css_custom) {
        if ($desc["CATEGORY"] == "ITEMS")  $css_custom = "width_pct_100";
        elseif ($desc["TYPE"] == "MFK")  $css_custom = "width_pct_100";
        elseif ($desc["SIZE"] == "AREA")  $css_custom = "width_pct_100";
        elseif ($desc["TYPE"] == "DATE")  $css_custom = "width_pct_50";
        elseif ($desc["TYPE"] == "GDAT")  $css_custom = "width_pct_50";
        elseif ($desc["SIZE"] < 33)  $css_custom = "width_pct_25";
        elseif ($desc["SIZE"] < 43)  $css_custom = "width_pct_33";
        elseif ($desc["SIZE"] < 67)  $css_custom = "width_pct_50";
        elseif ($desc["SIZE"] < 85)  $css_custom = "width_pct_66";
        elseif ($desc["SIZE"] < 101)  $css_custom = "width_pct_75";
        else $css_custom = "";
    }
    $htmlDiv .= $internal_new_group_div_open;
    $htmlDiv .= "<!-- fg-$col start -->";
    $htmlDiv .= '<div id="fg-' . $col . '" class="attrib-' . $col . ' form-group ' . $css_custom . ' ' . $class_empty_object . '">';
    // if ($tr_obj == $class_tr2) $tr_obj = $class_tr1;
    // else $tr_obj = $class_tr2;


    if ($desc["CSS-DISPLAY"]) {
        $css_class = " class='" . $desc["CSS-DISPLAY"] . "'";
    }

    if ($desc["CATEGORY"] == "ITEMS") {
        //if(!$newTr) echo "<th></th><td></td></tr>";
        $colspan = "colspan='3'";
        $newTr = true;
    }
    if ($desc["COLSPAN"]) {
        $colspan = "colspan='" . $desc["COLSPAN"] . "'";
    }

    //if((!$firstTr) and (($desc["NEW-TR"]) or $newTr)) echo "</tr>";

    if ($newTr) {
        $firstTr = false;
        if ($desc["CATEGORY"] == "ITEMS")
            $newTr = true;
        else
            $newTr = false;
    } else {
        $newTr = true;
    }
    $newTr = true;
    if ($desc["OTHER-LINKS-TOP"] or (!$desc["OTHER-LINKS-BOTTOM"])) {
        $htmlDiv .= "<!-- other links top -->\n" . $info["btns"];
    }
    if (!$desc["NO-LABEL"]) {

        if ($info["trad"]) {
            $class_label0 = "hzm_label hzm_label_$col";
            if ($desc["REQUIRED"]) $class_label = "class='$class_label0 label_required'";
            elseif ($desc["MANDATORY"]) $class_label = "class='$class_label0 label_mandatory'";
            else $class_label = "class='$class_label0'";


            if ($info["warning"])  echo '<br><div class="ewarning">' . $info["warning"] . '</div>';
            $htmlDiv .= "<label for='$col' $class_label>" . $info["trad"] . " : \n";
            //if($info["unit"])  echo "<div class='hunit'>".$info["unit"]."</div>";
            //if($info["tooltip"])  echo '<img data-toggle="tooltip" data-placement="top" title="'.$info["tooltip"].'" src="../lib/images/tooltip.png" />';
            if ($info["help"])  echo '<span class="hspan">' . $info["help"] . '</span>';
            $htmlDiv .= "</label>\n";

            /* old code before change 004
                                                        echo "<label for='$col' $class_label>".$info["trad"]."\n";
                                                        if($info["tooltip"])  echo '<img data-toggle="tooltip" data-placement="top" title="'.$info["tooltip"].'" src="../lib/images/tooltip.png" />';
                                                        if($info["unit"])  echo " (الوحدة = ".$info["unit"]." )";
                                                        if($info["help"])  echo '<br><span class="hspan">'.$info["help"].'</span>';
                                                        echo " : </label>\n";
                                                        */
        }
        $br = false;
        if ($info["hint"]) {
            if (!$br) $htmlDiv .= "<br>";
            $br = true;
            $htmlDiv .= "<div class='hint_0'>" . $info["hint"] . "</div>"; //
        }
    }


    $br_if_needed = "";

    if ($info["error"] and $desc["ERROR-SHOW"]) $htmlDiv .="$br_if_needed<div id='attr_error_$col' class='error' for='$col'>" . $info["error"] . "</div>"; //

    if ($info["unit"] or $info["tooltip"] or $info["error"]) {
        $css_input_width_pct = 100;
        if ($info["tooltip"] or $info["error"]) $css_input_width_pct -= 10;


        if ($info["unit"]) $css_input_width_pct -= 20;
        $css_form_control_div_special = "";
        if ($desc["ROWS"]) {
            $rows = $desc["ROWS"];
            if ($rows > 9) $rows = 9;
            if ($rows < 1) $rows = 1;

            $css_form_control_div_special .= " rows$rows";
        }

        $css_unit_tooltip_active = "class_input_width_$css_input_width_pct";
        if ($info["error"]) {
            $errors_in_data = "errors";
            $htmlDiv .="<!-- $col >> err " . str_replace("-->", "", $info["error"]) . " -->";
        } else $errors_in_data = "";
        $col_type = $info["type"];
        $htmlDiv .="<div id=\"form-control-div-$col\" class=\"form-control-div $col_type hzm_control_div_$col $errors_in_data $css_unit_tooltip_active $css_form_control_div_special\">";
        if ($info["tooltip"] or $info["error"]) {
            if ($info["error"] and (!$desc["ERROR-HIDE"])) {
                $htmlDiv .="<div id='attr_error_$col' class=\"hzm_tooltip_error\">" . $info["error"]."</div>";
            } elseif ($info["tooltip"]) $htmlDiv .="<div class=\"hzm_tooltip\"><img data-toggle=\"tooltip\" data-placement=\"left\" class=\"hzm_tt\" title=\"" . $info["tooltip"] . "\" src=\"../lib/images/information.png\" /></div>";
        }



        $htmlDiv .=$info["input"];
        if ($info["unit"] and (!$info["no-hzm-unit"])) $htmlDiv .="<div class=\"hzm_unit\">" . $info["unit"] . "</div>";
        $htmlDiv .="</div>";
    } else $htmlDiv .= $info["input"];
    // if($info["tooltip"])  $htmlDiv .='<a href="#" data-toggle="tooltip" data-placement="top" title="'.$info["tooltip"].'">';
    // if($info["tooltip"])  $htmlDiv .='</a>';

    //$htmlDiv .="BTN-BTN-BTN-BTN-BTN-BTN-BTN-BTN-";
    if ($desc["OTHER-LINKS-BOTTOM"]) {
        $htmlDiv .="<!-- other links bottom -->\n" . $info["btns"];
    }

    if ($info["title_after"]) {
        if (!$br) $htmlDiv .="$br_if_needed";
        $br = true;
        $htmlDiv .="<div class='etitle_after'>" . $info["title_after"] . "</div>"; // 
    }
    if ($info["ehelp"])  $htmlDiv .="$br_if_needed<div class='ehelp'>" . $info["ehelp"] . "</div>"; //



    $htmlDiv .= "</div><!-- fg-$col end -->";

    return [$htmlDiv, $openedInGroupDiv, $fgroup];
}



function prepareEditInfoForColumn($obj, $nom_col, $desc, $lang, $colErrors=[], $step_show_error=false)
{
    $id = $obj->id;
    $separator = $obj->getSeparatorFor($nom_col);
    $col_val = $obj->getVal($nom_col);
    //if($nom_col=="response_templates") die("case not mode_field_read_only nom_col = $nom_col, value = $col_val ");
    $all_form_readonly = false;

    if (($desc['TYPE'] == 'PK') && empty($col_val)) {
        $data_col["trad"]  = "";
    } else {
        $data_col["trad"]  = $obj->getAttributeLabel($nom_col, $lang);
        //$data_col["trad"] .= " : ";
    }
    // no need with bootstrap
    /*if($desc['TYPE'] == 'MFK') $data_col["trad"] .= "<div class='hint_0'>للإختيار المتعدد اضغط زر 'Ctrl' مع الضغط على الزر الأيسر للفأرة</div>";
                                else*/
    $data_loaded = true;

    ob_start();
    if (($desc['TYPE'] == 'PK') && empty($col_val)) {
        $data_loaded = false;
        type_input($nom_col, $desc, $id, $obj, $separator, $data_loaded);
    } else {
        type_input($nom_col, $desc, $col_val, $obj, $separator, $data_loaded, "inputlong", 0, "inputlong");
    }
    $desc_export = var_export($desc, true);
    if (AfwSession::config('MODE_DEVELOPMENT', false)) {
        $data_col["input"] = "<!-- start of input for attrib $nom_col : [$col_val] = obj->val($nom_col) desc=$desc_export-->";
    }
    $data_col["input"] .= ob_get_clean();
    $data_col["input"] .= "<!-- end of input for attrib $nom_col -->";
    $data_col["type"] = $desc["TYPE"];
    $col_help = $nom_col . "_help";
    $val_help = $obj->translate($col_help, $lang);
    if ($val_help != $col_help) $data_col["help"]     = $val_help;

    $data_col["ehelp"]     = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj, $nom_col, "EHELP", $lang, $desc));
    $data_col["hint"]     = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj, $nom_col, "HINT", $lang, $desc));
    $data_col["tooltip"]  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj, $nom_col, "TOOLTIP", $lang, $desc));
    if (!$data_col["tooltip"]) {
        $tltp = AfwInputHelper::getAttributeTooltip($obj, $nom_col, $lang);
        if ($tltp) $data_col["tooltip"] = $tltp;
    }

    $data_col["unit"]  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj, $nom_col, "UNIT", $lang, $desc));
    $data_col["no-hzm-unit"]  = $desc["NO-HZM-UNIT"];
    if ($data_col["unit_explain"]) $data_col["unit"]  = "الوحدة = " . $data_col["unit"];

    $data_col["title_after"]  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj, $nom_col, "TITLE_AFTER", $lang, $desc));

    //if($nom_col=="picture_height") die("data[$nom_col][unit] = ".$data_col["unit"]);

    if ($desc['TYPE'] == 'MFK') {
        if ($desc['FORMAT'] == 'dropdown') {
            $data_col["tooltip"] .= $obj->translateMessage("MULTI CHOICE ALLOWED") . ".\n";
            $data_col["tooltip"] .= $obj->translateMessage("CURRENT CHOICES") . " : \n";
            $data_col["tooltip"] .= str_replace('<br>', " / ", $obj->showAttribute($nom_col));
        } else {
            unset($data_col["tooltip"]);
        }
    }
    //if($nom_col=="booking_comment") die("step_show_error=$step_show_error , obj_errors[$nom_col]=".$colErrors);
    if ($colErrors and $step_show_error) {
        $data_col["error"] = $colErrors;
        //if($nom_col=="booking_comment") die("obj_errors = ".var_export($obj_errors,true));
    } 
    /*elseif ($obj_errors) {
        //die("obj_errors = ".var_export($obj_errors,true));
    }*/

    return $data_col;
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