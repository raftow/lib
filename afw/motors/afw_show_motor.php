<?php
class AfwShowMotor
{
    /**
     * @param AFWObject $obj
     */
    public static function prepareShowInfoForColumn($obj, $nom_col, $desc, $lang, $obj_errors = [], $step_show_error = false, $i_can_see_attribute = true, $mode_field_read_only_log = "")
    {
        $tuple = [];
        // if($nom_col=="response_templates") die("case mode_field_read_only nom_col = $nom_col");
        $obj->showAsDataTable = $desc['DATA_TABLE'];
        $col_val = $obj->getVal($nom_col);
        $style_div_form_control = "";

        if ($desc['FORM_HEIGHT']) {
            $style_div_form_control = "height:" . $desc['FORM_HEIGHT'] . " !important";
        }

        if ($desc['STYLE_RO_DIV']) {
            $style_div_form_control = $desc['STYLE_RO_DIV'];
        }

        if ($desc['INPUT_WIDE']) {
            $ro_classes_form = "";
        } else {
            if (($desc['SIZE'] == "AEREA") or
                ($desc['SIZE'] == "AREA") or
                ($desc['CATEGORY'] == "ITEMS") or
                ($desc['SUB-CATEGORY'] == "ITEMS")
            ) $inputarea = "inputarea";
            else $inputarea = "";


            $ro_classes_form = "form-control inputreadonly $inputarea";
        }
        $col_val_class = "";
        if (($desc['TYPE'] == 'YN') or ($desc['TYPE'] == 'INT') or ($desc['TYPE'] == 'ENUM') or ($desc['TYPE'] == 'FK')) {
            if ($desc['CATEGORY']) $col_val_0 = $obj->calc($nom_col);
            else $col_val_0 = $obj->getVal($nom_col);
            if (strlen($col_val_0) > 30) $col_val_0 = "";
            else {
                $col_val_0 = str_replace(",", "_", $col_val_0);
                $col_val_0 = str_replace("/", "_", $col_val_0);
                $col_val_0 = str_replace(" ", "_", $col_val_0);
                $col_val_0 = str_replace(":", "_", $col_val_0);
                $col_val_0 = str_replace("-", "_", $col_val_0);
            }

            $col_val_class = "hzm_value_" . $nom_col . "_" . $col_val_0;
        }

        if (($desc['TYPE'] == 'GDAT') or ($desc['TYPE'] == 'GDATE') or ($desc['TYPE'] == 'DATE')) {
            if (!$obj->getVal($nom_col)) $col_val_class = "hzm_value_empty_date col$nom_col";
        }
        $tuple["ehelp"]     = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj, $nom_col, "EHELP", $lang, $desc));
        // if($nom_col=="applicationModelConditionList") die("ehelp=".$tuple["ehelp"]);
        $id_div_input = "div_data_$nom_col";
        $tuple["input"] = "<div id='$id_div_input' class='hzm_data hzm_data_$nom_col $col_val_class $ro_classes_form' style='$style_div_form_control'>";
        if (((!$desc['CATEGORY']) || ($desc['FORCE-INPUT'])) and (!$desc['NO-INPUT'])) {
            // if($nom_col=="response_templates") die("case no-CATEGORY or FORCE-INPUT");

            ob_start();
            AfwEditMotor::hidden_input($nom_col, $desc, $col_val, $obj);
            $tuple["input"] .= ob_get_clean();
            if (true) // ($objme->isSuperAdmin())
            {
                $tuple["input"] .= "<!-- log : $mode_field_read_only_log -->";
            }
        } else {
            // if($nom_col=="response_templates") die("case CATEGORY and no FORCE-INPUT");
        }
        if ($i_can_see_attribute) {
            $tuple["trad"]  = $obj->getAttributeLabel($nom_col, $lang);  // . " :"
            if ($desc["EDIT-HIDE-VALUE"] or (isset($desc["DISPLAY"]) and (!$desc["DISPLAY"]))) {
                if ($desc["EDIT-HIDE-VALUE"]) $tuple["input"] .=  $desc["EDIT-HIDE-VALUE"];
                else $tuple["input"] .= $obj->tm("hidden", $lang) . "<!-- hidden because desc[DISPLAY] == false -->";
                $tuple["input"] .= "[$nom_col val=$col_val " . $obj->showAttribute($nom_col, $desc, true, $lang) . "]";
            } else {
                // if($nom_col=="response_templates") $tuple["input"] .= "obj->showAttribute($nom_col) = ";
                $tuple["input"] .= $obj->showAttribute($nom_col, $desc, true, $lang);
                // $tuple["input"] .= "[$nom_col val=$col_val " . $obj->showAttribute($nom_col, $desc, true, $lang) . "]";
            }

            if ($obj_errors[$nom_col]) $tuple["error"] = $obj_errors[$nom_col];
            // if($nom_col=="response_templates") die("case i can see attribute : " . $tuple["input"]);
        } else {
            $tuple["input"] .= "<!-- case i can not see attribute $nom_col in mode QSEARCH-->";
        }
        $tuple["input"] .= "</div>";
        // if this column is to show with accordion run the js of accordion
        if ($desc['TEMPLATE'] == 'accordion') {
            $tuple["input"] .= "<script>
                                                \$( function() {
                                                \$(\"#$id_div_input\").accordion({
                                                collapsible: true
                                                });
                                                } );
                                        </script>";
        }



        $tuple["tooltip"]  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj, $nom_col, "TOOLTIP", $lang, $desc));
        if (!$tuple["tooltip"]) {
            $tltp = AfwInputHelper::getAttributeTooltip($obj, $nom_col, $lang);
            if ($tltp) $tuple["tooltip"] = $tltp;
        }
        $tuple["unit"]  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj, $nom_col, "UNIT", $lang, $desc));
        $tuple["no-hzm-unit"]  = $desc["NO-HZM-UNIT"];


        return $tuple;
    }
}
