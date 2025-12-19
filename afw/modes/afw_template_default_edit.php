<?php
$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}

define("LIMIT_INPUT_SELECT", 10);

$lang = AfwLanguageHelper::getGlobalLanguage();
$fsens = ($lang=="ar") ? "right" : "left";
$objme = AfwSession::getUserConnected();

$otherLink_genereLog = false;


$data = array();
/**
 * @var AFWObject $obj 
 */


$wizObj = new AfwWizardHelper($obj);

$mode_edit_id = $obj->getId();
$clObj = $obj->getMyClass();
$cl_short = strtolower(substr($clObj, 0, 10));



$last_edited_step = $obj->getLastEditedStep(false);

$class_db_structure = $obj->getMyDbStructure();

if ($obj->editByStep) {
        // if(!$obj->currentStep) $obj->currentStep = $objme->curStepFor[$obj->getTableName()][$obj->getId()];   
        if (!$obj->currentStep) $obj->currentStep = 1;
        if (!AfwFrameworkHelper::stepIsEditable($obj, $obj->currentStep)) $obj->currentStep = 1;
}

$isdraft = $obj->isDraft();

$step_show_error = ((!$isdraft) or ($obj->currentStep < $last_edited_step) or $obj->show_draft_errors);
// die("dbg step_show_error=$step_show_error = ((!isdraft=$isdraft) or (currentStep=$obj->currentStep < last_edited_step=$last_edited_step) or show_draft_errors=$obj->show_draft_errors)");        
// or (!$last_edited_step) rafik 5/6/2022 I removed this because new object editing have $last_edited_step = 0 and no sens to show errors

// rafik : for debugg :
/*        
if(($clObj=="Student") and ($obj->currentStep==1))
{
    echo "not draft : [" . (!$obj->isDraft()) ."] ?<br>\n";
    // echo "or no last_edited_step : [" . (!$last_edited_step) ."] ? <br>\n";
    echo "or last_edited_step (" . $last_edited_step .") > current (" .$obj->currentStep.") ? <br>\n";
    echo "or show_draft_errors : [" . $obj->show_draft_errors ."] ?<br>\n";
    die();
}
/* */
$check_error_activated = "";
if ($obj->general_check_errors) $check_error_activated = "general_check_errors";
elseif (AfwSession::hasOption("CHECK_ERRORS")) $check_error_activated = "has option CHECK_ERRORS";
elseif (AfwSession::hasOption("GENERAL_CHECK_ERRORS")) $check_error_activated = "has option GENERAL_CHECK_ERRORS";
//die("check_error_activated=$check_error_activated");
if (!$obj->editByStep) {
        if ($check_error_activated) $obj_errors = AfwDataQualityHelper::getDataErrors($obj, $lang);
} else {
        if ($check_error_activated) $obj_errors = AfwDataQualityHelper::getStepErrors($obj, $obj->currentStep, $lang);
        // die("step=".$obj->currentStep." obj_errors=".var_export($obj_errors,true));
}

//die("obj_errors = ".var_export($obj_errors,true));

$all_form_readonly = true;
$inited_cols = $data_template["inited_cols"];
// die("inited_cols = ".var_export($inited_cols,true));

foreach ($class_db_structure as $nom_col => $desc) {
        // if($nom_col=="updated_at") die("class_db_structure = ".var_export($class_db_structure, true));
        $descOld = $desc;
        $desc = AfwStructureHelper::repareMyStructure($obj, $desc, $nom_col);
        // if($nom_col=="attribute_11") throw new AfwRunti meException(" Old Struct = ".var_export($descOld,true)." New Struct = ".var_export($desc,true));
        $mode_field_read_only = false;
        $mode_field_read_only_log = "";
        if (!$desc["STEP"]) $desc["STEP"] = 1;
        if ($inited_cols[$nom_col]) {
                if ($obj->currentStep != $desc["STEP"]) {
                        // do like if the step of this field si the current and it is readonly
                        // because need to be setted when the user will save the form the value is not lost
                        $old_desc_step = $desc["STEP"];
                        $desc["STEP"] = $obj->currentStep;
                        $mode_field_read_only = true;
                        $the_reason_readonly = "the field is setted with default value and it is not the step of this field (" . $obj->currentStep . " != " . $old_desc_step . ")";
                }
        }

        if (((strtoupper($desc["STEP"]) == 'STEPS') and is_array($desc["STEPS"]) and in_array($obj->currentStep,$desc["STEPS"])) or 
            (strtoupper($desc["STEP"]) == 'ALL') or 
            ($desc["STEP"] == $obj->currentStep) or 
            (!$obj->editByStep)) {
                if (!$mode_field_read_only) list($mode_field_read_only, $the_reason_readonly) = AfwStructureHelper::attributeIsReadOnly($obj, $nom_col, $nom_col_desc = "", $nom_col_submode = "", $nom_col_for_this_instance = true, $returm_me_reason_readonly = true);
                // if($nom_col == "orgunit_id") die("$nom_col attribute Is ReadOnly = [$mode_field_read_only], reason=[$the_reason_readonly], ");
                if ($mode_field_read_only) {
                        $mode_field_read_only_log .= "$nom_col attribute Is ReadOnly, reason=[$the_reason_readonly], ";
                        if (!$the_reason_readonly) $mode_field_read_only_log .= "see you implemtation of surcharge of method attribute-Can-Be-Updated-By it should return array with both boolean and string explaining reason of read-only behavior";
                }
                //(((isset($desc["EDIT"]) &&  $desc["EDIT"])) or ($objme->isSuperAdmin() && isset($desc["EDIT-ADMIN"]) &&  $desc["EDIT-ADMIN"]));
                // @help-attr : EDIT-HIDDEN : if true means that field appear in qedit mode and not in edit mode 
                $mode_field_edit = (AfwStructureHelper::attributeIsEditable($obj, $nom_col)
                        and (!$desc["EDIT-HIDDEN"])
                        and ((!$obj->isEmpty()) or (!$desc["HIDE_IF_NEW"])));
                $mode_field_edit_log = "";
                if ($mode_field_edit) $mode_field_edit_log .= "$nom_col is editable";
                // if(($nom_col=="sci _id") and $mode_field_edit) die("mode_field_edit = $mode_field_edit, mode_field_read_only=$mode_field_read_only : (reason=$mode_field_read_only_log) ".var_export($obj_errors,true));
                //**
                $nom_col_to_see = $desc["EDIT-FOR"];
                if (!$nom_col_to_see) $nom_col_to_see = $nom_col;
                $ican_display_key = AfwPrevilegeHelper::keyIsToDisplayForUser($obj, $nom_col_to_see, $objme, "DISPLAY");
                $ican_display_data = AfwPrevilegeHelper::dataAttributeCanBeDisplayedForUser($obj, $nom_col_to_see, $objme, "DISPLAY", $desc);
                $i_can_see_attribute = ($ican_display_key and $ican_display_data);
                if (!$i_can_see_attribute) {
                        $i_can_not_see_attribute_reason = "ican_display_key = " . var_export($ican_display_key,true) . " ican_display_data = " . var_export($ican_display_data,true);
                }
                //**
                $column_is_authorised_to_be_edited_by_me = AfwPrevilegeHelper::keyIsToDisplayForUser($obj, $nom_col, $objme, "EDIT");

                $horizontal_editability_for_me = AfwPrevilegeHelper::dataAttributeCanBeDisplayedForUser($obj, $nom_col, $objme, "EDIT", $desc);
                //**
                $i_can_edit_attribute = ($column_is_authorised_to_be_edited_by_me and $horizontal_editability_for_me);
                $i_can_not_edit_attribute_log = "";
                if (!$i_can_edit_attribute) {
                        if (!$column_is_authorised_to_be_edited_by_me) $i_can_not_edit_attribute_log .= "column $nom_col is not authorised to be edited by me, ";
                        if (!$horizontal_editability_for_me) $i_can_not_edit_attribute_log .= "column $nom_col is not horizontally editable for me, ";
                }
                $nom_colIsApplicable = $obj->attributeIsApplicable($nom_col);
                $data[$nom_col]["log-na"] = "obj => attributeIsApplicable($nom_col)";
                
        } else {
                $mode_field_read_only = true;
                $mode_field_read_only_log = "$nom_col is not in step " . $obj->currentStep . " but in step " . $desc["STEP"];
                $mode_field_edit = false;
                $mode_field_edit_log = $mode_field_read_only_log;
                $i_can_see_attribute = false;
                $column_is_authorised_to_be_edited_by_me = false;
                $i_can_edit_attribute = false;
                $i_can_not_edit_attribute_log = $mode_field_read_only_log;
                $nom_colIsApplicable = false;
                $data[$nom_col]["log-na"] = $mode_field_read_only_log;
        }

        if ($obj->editByStep) {

                if ($mode_field_edit) {
                        if (strtolower($desc["STEP"]) != "all") {
                                if ($obj->currentStep > $desc["STEP"]) {
                                        $mode_field_edit = false; //$mode_field_read_only = true;
                                        $mode_field_edit_log .= ": $nom_col is editable but is not in step " . $obj->currentStep . " but in step : " . $desc["STEP"];
                                } elseif ($obj->currentStep < $desc["STEP"]) {
                                        $mode_field_edit = false;
                                        $mode_field_edit_log .= ": $nom_col is editable but is not in step " . $obj->currentStep . " but in step : " . $desc["STEP"];
                                } else {
                                        $mode_field_edit_log .= ": $nom_col is editable and is in step " . $obj->currentStep;
                                }
                        }
                }
        } else {
                if ($mode_field_edit) {
                        if (($desc["STEP"]) and (strtolower($desc["STEP"]) != "all") and (intval($desc["STEP"]) != 1)) {
                                $mode_field_edit = false;
                                $mode_field_edit_log .= ": $nom_col is in step " . $desc["STEP"] . " but this step not exists as only step 1 exists (no tabs).";
                        }
                }
        }

        if ($mode_field_edit and (!$mode_field_read_only) and (!$i_can_edit_attribute)) {
                $mode_field_read_only = true;
                $mode_field_read_only_log .= "$nom_col attribute is editable not read-only but for me I can edit it : $i_can_not_edit_attribute_log ";
        }

        if ($mode_field_edit and $mode_field_read_only and (!$i_can_see_attribute)) {
                $mode_field_read_only = false;
                $mode_field_edit = false;
                $mode_field_edit_log .= ": $nom_col i an not see it so cancel mode edit/readonly";
        }
        if (!isset($desc["BUTTONS"])) $desc["BUTTONS"] = true;
        $buttons = $desc["BUTTONS"];
        // if($nom_col=="aconditionList") die("mode_field_edit=$mode_field_edit, mode_field_read_only=$mode_field_read_only, i_can_edit_attribute=$i_can_edit_attribute log=$mode_field_edit_log");
        //echo "$nom_col <br>";
        

        

        if ($nom_colIsApplicable) {

                // if ($nom_col == "created_by") die("mode_field_edit = $mode_field_edit, mode_field_read_only=$mode_field_read_only : (reason=$mode_field_read_only_log) " . var_export($obj_errors, true));
                if ($mode_field_edit) 
                {
                        if (!$mode_field_read_only) 
                        {
                                $colErrors = $obj_errors[$nom_col];
                                // if($nom_col=="passeport_num") die("colErrors = ".var_export($colErrors,true));
                                
                                $data[$nom_col] = AfwEditMotor::prepareEditInfoForColumn($obj, $nom_col, $desc, $lang, $colErrors, $step_show_error);
                                $colErrors_export = var_export($colErrors,true);
                                // if($nom_col=="passeport_num") die("dbg of prepareEdit InfoForColumn : data[$nom_col] = ".var_export($data[$nom_col],true)." = prepare EditInfoForColumn(obj, $nom_col, desc, lang=$lang, colErrors=$colErrors_export, step_show_error=$step_show_error)");
                        } 
                        else 
                        {
                                // if($nom_col=="response_templates") die("case mode_field_read_only nom_col = $nom_col");
                                $obj->showAsDataTable = $desc['DATA_TABLE'];
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


                                        $ro_classes_form = "form-control $lang_input inputreadonly $inputarea";
                                }
                                $col_val_class = "";
                                if (($desc['TYPE'] == 'YN') or ($desc['TYPE'] == 'INT') or ($desc['TYPE'] == 'ENUM') or ($desc['TYPE'] == 'FK')) {
                                        if ($desc['CATEGORY']) $col_val_0 = $obj->calc($nom_col);
                                        else $col_val_0 = $obj->getVal($nom_col);
                                        if(strlen($col_val_0)>30) $col_val_0 = "";
                                        else
                                        {
                                                $col_val_0 = str_replace(",","_",$col_val_0);
                                                $col_val_0 = str_replace("/","_",$col_val_0);
                                                $col_val_0 = str_replace(" ","_",$col_val_0);
                                                $col_val_0 = str_replace(":","_",$col_val_0);
                                                $col_val_0 = str_replace("-","_",$col_val_0);
                                        }
                                        
                                        $col_val_class = "hzm_value_" . $nom_col . "_" . $col_val_0;
                                }

                                if (($desc['TYPE'] == 'GDAT') or ($desc['TYPE'] == 'GDATE') or ($desc['TYPE'] == 'DATE')) {
                                        if (!$obj->getVal($nom_col)) $col_val_class = "hzm_value_empty_date col$nom_col";
                                }
                                $data[$nom_col]["ehelp"]     = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj,$nom_col, "EHELP", $lang, $desc));                                
                                // if($nom_col=="applicationModelConditionList") die("ehelp=".$data[$nom_col]["ehelp"]);
                                $id_div_input = "div_data_$nom_col";
                                $data[$nom_col]["input"] = "<div id='$id_div_input' class='hzm_data hzm_data_$nom_col $col_val_class $ro_classes_form' style='$style_div_form_control'>";
                                if (((!$desc['CATEGORY']) || ($desc['FORCE-INPUT'])) and (!$desc['NO-INPUT'])) {
                                        // if($nom_col=="response_templates") die("case no-CATEGORY or FORCE-INPUT");
                                        $col_val = $obj->getVal($nom_col);
                                        ob_start();
                                        AfwEditMotor::hidden_input($nom_col, $desc, $col_val, $obj);
                                        $data[$nom_col]["input"] .= ob_get_clean();
                                        if (true) // ($objme->isSuperAdmin())
                                        {
                                                $data[$nom_col]["input"] .= "<!-- log : $mode_field_read_only_log -->";
                                        }
                                }
                                else
                                {
                                        // if($nom_col=="response_templates") die("case CATEGORY and no FORCE-INPUT");
                                }
                                if ($i_can_see_attribute) {
                                        $data[$nom_col]["trad"]  = $obj->getAttributeLabel($nom_col, $lang);  // . " :"
                                        if ($desc["EDIT-HIDE-VALUE"] or (isset($desc["DISPLAY"]) and (!$desc["DISPLAY"])))
                                                if ($desc["EDIT-HIDE-VALUE"]) $data[$nom_col]["input"] .=  $desc["EDIT-HIDE-VALUE"];
                                                else $data[$nom_col]["input"] .= $obj->tm("hidden") . "<!-- hidden because desc[DISPLAY] == false -->";
                                        else
                                        {
                                                // if($nom_col=="response_templates") $data[$nom_col]["input"] .= "obj->showAttribute($nom_col) = ";
                                                $data[$nom_col]["input"] .= $obj->showAttribute($nom_col);

                                        }

                                        if ($obj_errors[$nom_col]) $data[$nom_col]["error"] = $obj_errors[$nom_col];
                                        // if($nom_col=="response_templates") die("case i can see attribute : " . $data[$nom_col]["input"]);
                                }
                                else
                                {
                                        if($nom_col=="response_templates") die("case i can not see attribute");
                                }
                                $data[$nom_col]["input"] .= "</div>";
                                // if this column is to show with accordion run the js of accordion
                                if($desc['TEMPLATE'] == 'accordion')
                                {
$data[$nom_col]["input"] .= "<script>
  \$( function() {
    \$(\"#$id_div_input\").accordion({
      collapsible: true
    });
  } );
  </script>";
                                }
                                
                                $data[$nom_col]["tooltip"]  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj,$nom_col, "TOOLTIP", $lang, $desc));
                                if (!$data[$nom_col]["tooltip"]) {
                                        $tltp = AfwInputHelper::getAttributeTooltip($obj,$nom_col, $lang);
                                        if ($tltp) $data[$nom_col]["tooltip"] = $tltp;
                                }
                                $data[$nom_col]["unit"]  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj,$nom_col, "UNIT", $lang, $desc));
                                $data[$nom_col]["no-hzm-unit"]  = $desc["NO-HZM-UNIT"];
                        }

                        $data[$nom_col]["warning"]  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($obj,$nom_col, "WARNING", $lang, $desc));
                        if (!$data[$nom_col]["warning"]) {
                                $col_warning = $nom_col . "_warning";
                                $val_warning = $obj->translate($col_warning, $lang);
                                if ($val_warning != $col_warning) $data[$nom_col]["warning"]     = $val_warning;
                        }


                        if ($buttons and $i_can_edit_attribute) {
                                $key_mod = "mode_$nom_col";
                                $key_mod_tr = $obj->translate($key_mod, $lang);
                                if ($key_mod_tr == $key_mod) $key_mod_tr = $obj->translate($nom_col, $lang);

                                $data[$nom_col]["btns"] = "";
                                // ***********************

                                $pbm_loc_arr = $obj->getPublicMethodsForUser($objme, $key_mod);
                                if (count($pbm_loc_arr) > 0) {
                                        $html_buttons_spec_methods_for_key = "";
                                        foreach ($pbm_loc_arr as $pbm_code => $pbm_item) {
                                                // if we click on the button and have action_lourde css class 
                                                // it will open the loader at the same time the form can not submit because of
                                                // missed required data or the form errors
                                                $action_lourde = (($check_error_activated) and (count($obj_errors) == 0));
                                                $html_buttons_spec_methods_for_key .= AfwHtmlHelper::showSimpleAttributeMethodButton($obj, $pbm_code, $pbm_item, $lang, $action_lourde, $objme->isSuperAdmin());
                                        }
                                        $html_buttons_spec_methods_for_key = trim($html_buttons_spec_methods_for_key);
                                        if ($html_buttons_spec_methods_for_key) {
                                                $data[$nom_col]["btns"] .= "<div class=\"attribute_buttons\">$html_buttons_spec_methods_for_key</div>";
                                                //die("data[$nom_col][btns] = ".$data[$nom_col]["btns"]);
                                        }
                                }

                                // ***************************
                                $other_links = $obj->getOtherLinksForUser($key_mod, $objme, $otherLink_genereLog);
                                // if($key_mod=="mode_responseList") die("Maintenance ongoing : obj->getOtherLinksForUser($key_mod) otherLink_genereLog=$otherLink_genereLog other_links=".var_export($other_links,true));
                                if (count($other_links) > 12) {
                                        // use bootstrap design version if many links
                                        $data[$nom_col]["btns"] .=  "<div class='btn-group'>";
                                        $data[$nom_col]["btns"] .= "  <button type='button' class='btn btn-primary'>$key_mod_tr</button>";
                                        $data[$nom_col]["btns"] .= "  <button type='button' class='btn-primary dropdown-toggle' data-toggle='dropdown'>";
                                        $data[$nom_col]["btns"] .= "    <span class='caret'></span>";
                                        $data[$nom_col]["btns"] .= "  </button>";
                                        $data[$nom_col]["btns"] .= "  <ul class='dropdown-menu' role='menu'>";
                                        foreach ($other_links as $k => $other_link) {
                                                $o_url = $other_link["URL"];
                                                $o_tit = $other_link["TITLE"];
                                                $o_target = $other_link["TARGET"];
                                                if ($o_target) $o_target_html = "target='$o_target'";
                                                else $o_target_html = "";
                                                $data[$nom_col]["btns"] .= "    <li><a href='$o_url' $o_target_html>$o_tit</a></li>";
                                        }
                                        $data[$nom_col]["btns"] .= "  </ul>";
                                        $data[$nom_col]["btns"] .= "</div>";
                                } else {
                                        //$col_num = 0;
                                        foreach ($other_links as $k => $other_link) {
                                                $o_url = $other_link["URL"];
                                                $o_tit = $other_link["TITLE"];
                                                $o_target = $other_link["TARGET"];
                                                if ($o_target) $o_target_html = "target='$o_target'";
                                                else $o_target_html = "";
                                                $o_class = $other_link["CSS-CLASS"];
                                                $o_color = $other_link["COLOR"];
                                                if (!$o_color) $o_color = "gray";
                                                if($o_url=="@help")
                                                {
                                                        $data[$nom_col]["btns"] .= "<div class='otln help $o_class $o_color'>$o_tit</div>";
                                                }
                                                else
                                                {
                                                        $data[$nom_col]["btns"] .= "<a href='$o_url' $o_target_html><div class='${o_color}btn submit-btn fright otln $o_class'>$o_tit</div></a>\n";
                                                }
                                                

                                                //$col_num++;
                                        }
                                }

                                if ($otherLink_genereLog) {
                                        // very bad it erase all log find better solution (named log) 
                                        // $data[$nom_col]["btns"] .= "<div class='consolehzm'>".AfwSession::getLog()."</div>";
                                }
                        }
                } else {
                        // if($nom_col=="father_id") die("for col $nom_col It is not in mode edit : ".$mode_field_edit_log);
                }
        } else {
                $data[$nom_col]["notes"] = "<!-- $nom_col is not applicable ".$data[$nom_col]["log-na"]." -->";
                //
        }
        // if($data["ppp"]) die(var_export($data,true));
}

$css       = $obj->getStyle();
//die("css = $css");
$str_label = ($mode_edit_id) ? $obj->translate('EDIT.CARD', $lang) : $obj->translate('INSERT', $lang, true);
//$str_titre = $obj->getShortDisplay($lang);
$str_new = $obj->translate(strtolower("$cl.new"), $lang);
$str_id = ($mode_edit_id) ? $obj->id : $str_new;
$subType = $obj->mySubType();
$object_status = $obj->myDisplayStatus();
if ($subType)
        $str_name = $subType;
else
        $str_name = $titre_display = $obj->translate("FILE", $lang, true) . " ".AfwStringHelper::arrow($lang)." " . 
                                        $obj->singleTranslation($lang) . " ".AfwStringHelper::arrow($lang)." " . $obj->getShortDisplay($lang);

if (!$file_box_css_class) $file_box_css_class = "filebox";
$wizard_class = $wizObj->getWizardClass();
if ($obj->elevatezoom) {
?>
        <script src='../js/jquery.elevatezoom.js'></script>
<?
}

$table_name = $obj->getMyTable();
$module_code = $obj->getMyModule();
$file_dir_name = dirname(__FILE__);
if (file_exists("$file_dir_name/../$module_code/css/table_$table_name.css")) {
?>
        <link href="../<?php echo $module_code ?>/css/table_<?php echo $table_name ?>.css" rel="stylesheet" type="text/css">
<?php
}
?>
<div class="<?= $file_box_css_class ?> editcard <?php echo $module_code . " " . AfwStringHelper::hzmStringOf($table_name) ." s" . $object_status;  ?> ">
        <div class="panel-heading">
                <h3 class="panel-title col-xs-12"><span><?php echo "$str_name" ?></span></h3>
                <h3 class="panel-title col-xs-0 text-left"><span class='object_id'><?php echo $str_id ?><span></h3>
        </div>
        <div class="<?php echo $wizard_class . " " . $module_code; ?>">
                <?
                if ($obj->editNbSteps > 1){
                        $step_name = array();
                        $nbStepsEditable = 0;
                        for ($kstep = 1; $kstep <= $obj->editNbSteps; $kstep++) {
                                $stepcode = "step" . $kstep;
                                $step_name[$kstep] = $obj->translate($stepcode, $lang);
                                if (AfwFrameworkHelper::stepIsEditable($obj, $kstep)) $nbStepsEditable++;
                        }
                        $curr_step_name = $step_name[$obj->currentStep];
                        $curr_step_order = $obj->currentStep . " من " . $nbStepsEditable;
                        $step_details = " - " . $obj->translate('STEP', $lang, true) . " " . $obj->currentStep . " : $curr_step_name";
                        $wizard_tabs_class = $wizObj->getWizardStepsClass();


                ?>
                        <div class="hideMenuTabs">
                                <span class="tabsBar openTabs">الخطوة <?php echo $curr_step_order ?> : <b><?php echo $curr_step_name ?></b> </span>
                        </div>
                        <div id="edit_mod_tabs" class="<?= $wizard_tabs_class ?>">
                                <ul role="tablist">

                                        <?
                                        // <li class="PlanStep"><a href="#">php echo "$str_name : $str_id"</a></li>
                                        $clObj = $obj->getMyClass();

                                        $clStep = $wizObj->getMyCLStep();
                                        $moduleObj = $obj->getMyModule();
                                        $idObj = $obj->getId();
                                        $step_knum = 0;
                                        for ($kstep = 1; $kstep <= $obj->editNbSteps; $kstep++) {
                                                if (AfwFrameworkHelper::stepIsEditable($obj, $kstep)) {
                                                        $step_knum++;
                                                        // $stepErrorsList = AfwDataQualityHelper::getStepErrors($obj, $kstep);
                                                        // $step_errors_list = implode("\n",$stepErrorsList);

                                                        $step_show_error = ((!$obj->isDraft()) or ($kstep < $obj->currentStep) or $obj->show_draft_errors);
                                                        $step_show_error_why = "";
                                                        if ($step_show_error) {
                                                                if (!$obj->isDraft()) $step_show_error_why = " not draft";
                                                                if ($kstep < $obj->currentStep) $step_show_error_why = "this step $kstep is < current step $obj->currentStep";
                                                                if ($obj->show_draft_errors) $step_show_error_why = "show_draft_errors is active for this class $cl";
                                                        }
                                                        if ($check_error_activated  and $step_show_error) {
                                                                $stepErrorsList = AfwDataQualityHelper::getStepErrors($obj, $kstep);                                                                
                                                                $step_errors_list = implode("\n", $stepErrorsList);
                                                                /*if($kstep==1 and $step_errors_list)
                                                                {
                                                                        die("for $clObj `$obj` step $kstep step_errors_list=$step_errors_list");
                                                                }*/
                                                                $step_erroned = (count($stepErrorsList) > 0);
                                                        } else {
                                                                $stepErrorsList = array();
                                                                $step_errors_list = "";
                                                                $step_erroned = false;
                                                        }

                                                        if ($kstep == $obj->currentStep) {
                                                                if ($step_erroned)
                                                                        $class_step = "CurrentStep ErronedStep ZZW ks$kstep cs" . $obj->currentStep; //." draft".$obj->getVal("draft");
                                                                else
                                                                        $class_step = "CurrentStep";
                                                                $link = "#";
                                                        } elseif (($kstep <= $last_edited_step) or (!$obj->stepsAreOrdered()) or ($obj->stepsAreOrdered()<=$obj->currentStep)) {
                                                                if ($step_erroned)
                                                                        $class_step = "AlreadyStep ErronedStep ZZO LES$last_edited_step";
                                                                elseif($kstep <= $last_edited_step)
                                                                        $class_step = "AlreadyStep LES$last_edited_step";
                                                                else
                                                                        $class_step = "GoingStep LES$last_edited_step";

                                                                $link = "main.php?Main_Page=afw_mode_edit.php&cl=$clObj&id=$idObj&currmod=$moduleObj&currstep=$kstep";
                                                        } else {
                                                                $class_step = "InactiveStep";
                                                                if (!$last_edited_step) {
                                                                        $step_errors_list = "step inactive";
                                                                        $step_show_error_why = "because last edited step not defined and steps are ordered (mth:stepsAreOrdered)";
                                                                }
                                                                $link = "#";
                                                        }
                                                        $stepLiContentHtml = $wizObj->getStepLiContentHtml($step_knum, $step_name[$kstep]);

                                        ?>
                                                        <li class="<?= "wizstep" . $kstep . " " . $clStep . " " . $class_step ?>"><a href="<?= $link ?>"><?php echo $stepLiContentHtml ?></a></li>
                                                        <!-- <?php
                                                                $step_errors_list = str_replace("<!--", " ", $step_errors_list);
                                                                $step_errors_list = str_replace("-->", " ", $step_errors_list);
                                                                $step_show_error_why = str_replace("<!--", " ", $step_show_error_why);
                                                                $step_show_error_why = str_replace("-->", " ", $step_show_error_why);
                                                                if ($step_errors_list) echo $step_errors_list . " why = $step_show_error_why"
                                                                ?> 
    -->
                                        <?
                                                }
                                        }
                                        
                                        ?>
                                </ul>
                        </div>
                        <script>
                                $(document).ready(function() {
                                        $(".tabsBar").click(function() {
                                                $("#edit_mod_tabs").toggleClass("active");
                                        });
                                });
                        </script>

                <?
                }
                
                ?>

                <div class="hzm_form_panel hzm_step_body_<?= $clStep . " step_panel_" . $obj->currentStep ?>">
                        <div class="form_<?php echo $fsens ?> form_wizard_body form_wizard_<?php echo $cl_short; ?> form_right_<?php echo $clStep . " step_body_" . $obj->currentStep; ?>" >
                                <div class="wizard_buttons">
                                <div class='body_nav_hzm'>
                                                <p>
                                                        <?php
                                                        if ($obj->editByStep) {
                                                                $currStep = $obj->currentStep;

                                                                if ($all_form_readonly) $form_readonly = "RO";
                                                                else $form_readonly = "";

                                                                $disabled_prev = "";
                                                                $class_btn_prev = "blightbtn";

                                                                if (AfwFrameworkHelper::findPreviousEditableStep($obj, $currStep, "enable/disable previous btn") <= 0) {
                                                                        $disabled_prev = "disabled";
                                                                        $class_btn_prev = "graybtn";
                                                                }
                                                        ?>
                                                                <input type="submit" name="save_previous" id="save_previous" class="fa previous <?= $class_btn_prev ?> wizardbtn fright <?= $disabled_prev ?> <?= $lang ?>" value="&nbsp;<?= $obj->translate('PREVIOUS' . $form_readonly, $lang, true) ?>&nbsp;" style="margin-right: 5px;" <?= $disabled_prev ?>></input>
                                                                <?
                                                                // to much save buttons (next previous finish ... will see about this save button if need in edit by step mode)
                                                                if ($obj->canSaveOnly($obj->currentStep)) {
                                                                ?>
                                                                        <input type="submit" name="save_only" id="save_only" class="fa save bluebtn wizardbtn" value="&nbsp;<?= $obj->translate('UPDATE', $lang, true) ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                                <?
                                                                }
                                                                // $nextStep will be = -1 if all next steps are R/O not editable, so no next editable step
                                                                $nextStep = AfwFrameworkHelper::findNextEditableStep($obj, $currStep, "show btn ?");
                                                                // no next editable step
                                                                $no_next_editable_step = ($nextStep < 0);

                                                                // all steps are edited before and completed without errors
                                                                $all_steps_are_edited_and_ok = (($last_edited_step == $obj->editNbSteps)  and $obj->isOk());
                                                                // we authorize finish button on any step
                                                                $authorize_finish_button_on_any_step = ($obj->canFinishOnAnyStep);

                                                                $finish_label = $obj->getFinishButtonLabel($lang, $nextStep, $form_readonly);

                                                                if ($nextStep > 0) {
                                                                        // ." ($currStep -> $nextStep)"
                                                                ?>
                                                                        <input type="submit" name="save_next" id="save_next" class="fa next greenbtn wizardbtn fleft" value="&nbsp;<?= $obj->translate('NEXT' . $form_readonly, $lang, true) ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                                <?
                                                                }

                                                                if (
                                                                        $finish_label and ($obj->canFinishOnCurrentStep()  or
                                                                                $obj->canFinishAsSaveAndRemainInCurrentStep()  or
                                                                                $no_next_editable_step or
                                                                                ($all_steps_are_edited_and_ok and $authorize_finish_button_on_any_step)
                                                                        )
                                                                ) {
                                                                ?>
                                                                        <input type="submit" name="save_update" id="save_update" hint="<?= "NextStep:" . $nextStep ?>" class="fa finish save_update yellowbtn wizardbtn fleft" value="&nbsp;<?= $finish_label ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                                <?
                                                                } else {
                                                                ?>
                                                                        <!-- <?= "No Finish BTN, ss/getFinishButtonLabel::canFinishOnCurrentStep::canFinishAsSaveAndRemainInCurrentStep or NextStep:" . $nextStep . " < 0 or some data is not ok or missing" ?> -->
                                                                <?
                                                                }
                                                        } else  // not edit by step
                                                        {
                                                                ?>
                                                                <input type="submit" name="save_update" id="save_update" class="fa finish save_update yellowbtn wizardbtn fleft" value="&nbsp;<?= $obj->translate('FINISH', $lang, true) ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                                <input type="submit" name="save_only" id="save_only" class="fa save bluebtn wizardbtn fright" value="&nbsp;<?= $obj->translate('UPDATE', $lang, true) ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                        <?
                                                        }
                                                        ?>
                                                </p>
                                        </div>
                                </div>
                                <div class="form_content form_content_<?php echo $cl_short ?>">
                                        <div id='body_form_hzm' class='body_form_hzm body_form_<?php echo $cl_short ?>'>
                                                <?

                                                $firstTr = true;
                                                $openedInGroupDiv = false;
                                                $fgroup = "";
                                                $internal_new_group_div_open = "";
                                                // die("data[diploma_approved]=".var_export($data["diploma_approved"],true));
                                                foreach ($data as $col => $info) 
                                                {
                                                        if($info['notes'])
                                                        {
                                                                echo $info["notes"];  
                                                        }
                                                        elseif($info['input'])
                                                        {
                                                                $class_db_structure[$col] = AfwStructureHelper::repareMyStructure($obj, $class_db_structure[$col], $col);
                                                                list($htmlDiv, $openedInGroupDiv, $fgroup) = 
                                                                   AfwEditMotor::attributeEditDiv($obj, $col, $class_db_structure[$col], $fgroup, $lang, $openedInGroupDiv, $info);
                                                                echo $htmlDiv;
                                                        }
                                                        elseif ($info["ehelp"])
                                                        {
                                                                echo "$br_if_needed<div class='ehelp'>" . $info["ehelp"] . "</div>"; //
                                                        }
                                                }

                                                if ($openedInGroupDiv) {
                                                        echo "</div>";
                                                        echo "</div><!-- fgroup -->";
                                                        $openedInGroupDiv = false;
                                                }

                                                if ($tr_obj == $class_tr2) $tr_obj = $class_tr1;
                                                else $tr_obj = $class_tr2;
                                                ?>
                                                <br>
                                        </div>
                                        <?php 
                                        $step_TEMPLATE = $obj->STEP_OPTIONS[$obj->currentStep]['TEMPLATE'];
                                        // echo "<br>get_class(obj) = ".get_class($obj);
                                        // echo "<br>currentStep = ".$obj->currentStep;
                                        // echo "<br>step_TEMPLATE = ".$step_TEMPLATE;
                                        if($step_TEMPLATE == 'accordion')
                                        {
                                                echo "<script>
                                                \$( function() {
                                                \$(\"#body_form_hzm\").accordion({
                                                collapsible: true
                                                });
                                                } );
                                                </script>";
                                        }
                                        
                                        ?>
                                </div>
                                <div class="form_buttons">
                                        <div class="panel_bottom form_bottom_buttons ">
                                                <!-- <h5 class='greentitle'><i></i>وظائف ذات صلة</h5>-->
                                                <?
                                                // echo $html_buttons_spec_methods_bis;
                                                ?>
                                        </div>
                                        <div class='body_nav_hzm'>
                                                <p>
                                                        <?php
                                                        if ($obj->editByStep) {
                                                                $currStep = $obj->currentStep;

                                                                if ($all_form_readonly) $form_readonly = "RO";
                                                                else $form_readonly = "";

                                                                $disabled_prev = "";
                                                                $class_btn_prev = "blightbtn";

                                                                if (AfwFrameworkHelper::findPreviousEditableStep($obj, $currStep, "enable/disable previous btn") <= 0) {
                                                                        $disabled_prev = "disabled";
                                                                        $class_btn_prev = "graybtn";
                                                                }
                                                        ?>
                                                                <input type="submit" name="save_previous" id="save_previous" class="fa previous <?= $class_btn_prev ?> wizardbtn fright <?= $disabled_prev ?> <?= $lang ?>" value="&nbsp;<?= $obj->translate('PREVIOUS' . $form_readonly, $lang, true) ?>&nbsp;" style="margin-right: 5px;" <?= $disabled_prev ?>></input>
                                                                <?
                                                                // to much save buttons (next previous finish ... will see about this save button if need in edit by step mode)
                                                                if ($obj->canSaveOnly($obj->currentStep)) {
                                                                ?>
                                                                        <input type="submit" name="save_only" id="save_only" class="fa save bluebtn wizardbtn" value="&nbsp;<?= $obj->translate('UPDATE', $lang, true) ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                                <?
                                                                }
                                                                // $nextStep will be = -1 if all next steps are R/O not editable, so no next editable step
                                                                $nextStep = AfwFrameworkHelper::findNextEditableStep($obj, $currStep, "show btn ?");
                                                                // no next editable step
                                                                $no_next_editable_step = ($nextStep < 0);

                                                                // all steps are edited before and completed without errors
                                                                $all_steps_are_edited_and_ok = (($last_edited_step == $obj->editNbSteps)  and $obj->isOk());
                                                                // we authorize finish button on any step
                                                                $authorize_finish_button_on_any_step = ($obj->canFinishOnAnyStep);

                                                                $finish_label = $obj->getFinishButtonLabel($lang, $nextStep, $form_readonly);

                                                                if ($nextStep > 0) {
                                                                        // ." ($currStep -> $nextStep)"
                                                                ?>
                                                                        <input type="submit" name="save_next" id="save_next" class="fa next greenbtn wizardbtn fleft <?= $lang ?>" value="&nbsp;<?= $obj->translate('NEXT' . $form_readonly, $lang, true) ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                                <?
                                                                }

                                                                if (
                                                                        $finish_label and ($obj->canFinishOnCurrentStep()  or
                                                                                $obj->canFinishAsSaveAndRemainInCurrentStep()  or
                                                                                $no_next_editable_step or
                                                                                ($all_steps_are_edited_and_ok and $authorize_finish_button_on_any_step)
                                                                        )
                                                                ) {
                                                                ?>
                                                                        <input type="submit" name="save_update" id="save_update" hint="<?= "NextStep:" . $nextStep ?>" class="fa finish save_update yellowbtn wizardbtn fleft" value="&nbsp;<?= $finish_label ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                                <?
                                                                } else {
                                                                ?>
                                                                        <!-- <?= "No Finish BTN, ss/getFinishButtonLabel::canFinishOnCurrentStep::canFinishAsSaveAndRemainInCurrentStep or NextStep:" . $nextStep . " < 0 or some data is not ok or missing" ?> -->
                                                                <?
                                                                }
                                                        } else  // not edit by step
                                                        {
                                                                ?>
                                                                <input type="submit" name="save_update" id="save_update" class="fa finish save_update yellowbtn wizardbtn fleft" value="&nbsp;<?= $obj->translate('FINISH', $lang, true) ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                                <input type="submit" name="save_only" id="save_only" class="fa save bluebtn wizardbtn fright" value="&nbsp;<?= $obj->translate('UPDATE', $lang, true) ?>&nbsp;" style="margin-right: 5px;" ></input>
                                                        <?
                                                        }
                                                        ?>
                                                </p>
                                        </div>
                                        <!-- Other links -->
                                        <!-- old was here -->
                                        <!-- Other links -->        
                                </div>
                        </div> <!-- form_right -->
                        <?
                        // calculate form_left
                        $pbm_arr = $obj->getPublicMethodsForUser($objme, "display");
                        if (count($pbm_arr) > 0) {
                                $html_buttons_spec_methods = "";
                                // $html_buttons_spec_methods_bis = "";
                                foreach ($pbm_arr as $pbm_code => $pbm_item) 
                                {
                                        if(!$pbm_item["HIDE"])
                                        {
                                                // if we click on the button and have action_lourde css class 
                                                // it will open the loader at the same time the form can not submit because of
                                                // missed required data or the form errors
                                                $action_lourde = (($check_error_activated) and (count($obj_errors) == 0));
                                                $html_buttons_spec_methods .= AfwHtmlHelper::showHtmlPublicMethodButton($obj, $pbm_code, $pbm_item, $lang, $action_lourde, $objme->isSuperAdmin());
                                                // $html_buttons_spec_methods_bis .= AfwHtmlHelper::showHtmlPublicMethodButton($obj, $pbm_code, $pbm_item, $lang, $action_lourde, $objme->isSuperAdmin(), "bis");
                                        }
                                        
                                }
                                $html_buttons_spec_methods = trim($html_buttons_spec_methods);
                                // $html_buttons_spec_methods_bis = trim($html_buttons_spec_methods_bis);
                                $orders_to_execute = $obj->translateMessage("Orders to execute", $lang);
                                if ($html_buttons_spec_methods) {
                        ?>
                                        <!-- form_left -->
                                        <div class="form_left form_left_buttons form_left_<?php echo $clStep . "_" . $obj->currentStep ?>" style="/*width: 12%;height:100%;*/">
                                                <h5 class='greentitle'><i></i><?php echo $orders_to_execute ?></h5>
                                                <?php
                                                echo $html_buttons_spec_methods;
                                                ?>
                                        </div>
                                        <!-- form_left -->
                                <?
                                        // $form_right_width = 80;
                                } else {
                                        // $form_right_width = 100;
                                }
                        } else {
                                // $form_right_width = 100;
                        }
?>
<!-- Other links -->
        <div id="all_btns" class="panel_links" style="width: 100%;height:100%">
                <?php
                if ($obj->editByStep) {
                        $getOtherLinkStep = $obj->currentStep;
                } else {
                        $getOtherLinkStep = "all";
                }
                $other_links_label = $obj::gtr("Other links", $lang);
                $other_links = $obj->getOtherLinksForUser("edit", $objme, $otherLink_genereLog, $getOtherLinkStep);
                if (count($other_links) > 0) 
                {
                ?>
                        <h5 class='bluetitle'><i></i><?php echo $other_links_label ?></h5>
                <?php

                        foreach ($other_links as $k => $other_link) {
                                echo AfwHtmlHelper::showOtherLinkButton($obj, $other_link, $lang);
                        }
                }

                if ($otherLink_genereLog) 
                {
                        // very bad it erase all log find better solution (named log) 
                        echo "<div class='consolehzm'>" . AfwSession::getLog("otherLink") . "</div>";
                }
                ?>

                </table>
        </div>
<!-- Other links --> 
<?php                        

                        if (false) { // $form_right_width == 100
                                list($help_picture, $logHelpPic) = AfwHtmlHelper::showHelpPicture($obj, $obj->currentStep);
                                if ($help_picture) {
                                ?>
                                        <div class="form_left form_left_buttons help_picture_<?= $clStep . "_" . $obj->currentStep ?>" style="/*width: 12%;height:100%;*/">
                                                <?
                                                echo $help_picture;
                                                ?>
                                        </div>
                        <?
                                        $form_right_width = 80;
                                } else echo "<!-- " . $logHelpPic . " -->";
                        }
                        // calculate form_left - end
                        ?>
                </div>
        </div>
        <!-- check_error_activated = <?php echo $check_error_activated ?> -->
        <?php
        $file_dir_name = dirname(__FILE__);
        $tb = $obj->getMyTable();
        $md = $obj->getMyModule();
        $file_js = "./js/edit_" . $tb . '.js';
        $file_js_path = "$file_dir_name/../../../$md/js/edit_" . $tb . '.js';

        if (file_exists($file_js_path)) {
        ?>
                <script src='<?php echo $file_js ?>'></script>
        <?php
        }
        else
        {
                echo "<!-- script js $md / $file_js not found in module/js path $file_js_path -->";
        }

