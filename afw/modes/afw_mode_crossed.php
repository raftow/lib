<?php
require_once(dirname(__FILE__)."/../../../config/global_config.php");

$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}



require_once ('afw_rights.php');

$objme = AfwSession::getUserConnected();
if(!$objme) 
{
    AfwSession::pushError("الرجاء تسجيل الدخول أولا");
    header("Location: login.php");
    exit();
}


if(!$cl)
	$objme->_userError("access denied","the class name parameter is mandatory");



if(!$limit) $limit = $qedit_mode_default_max_edit_rows_nb;


$fixm_list = $fixm;
$myMainObject = new $cl();

$fixm_array0 = explode(",",$fixm_list);        
$fixm_array = array();
foreach($fixm_array0 as $fm0)
{
    list($fm_col0, $fm_val0) = explode("=",$fm0);
    $fixm_array[$fm_col0] = $fm_val0;
    $fixm_array_sub_attributes = $myMainObject->fixModeSubAttributes($fm_col0, $fm_val0);
    foreach($fixm_array_sub_attributes as $attr0 => $attr0val)
    {
        if(!is_array($attr0val)) $fixm_array[$attr0] = $attr0val;
    }
        
}


if(!$currmod)
{
    $currmod = $uri_module;
}

 
//list($can,$bf_id, $reason) = $myMainObject->userCan($objme, $uri_module, "edit");
$can = $objme->iCanDoOperationOnObjClass($myMainObject,"qedit");
if(!$can)
{
      $log_ums_work = ($objme->isAdmin() or (AfwSession::hasOption("UMS_LOG"))) ? 1 : 1;
      header("Location: /lib/afw/modes/afw_denied_access_page.php?CL=$cl&MODE=edit&bf=$bf_id&rsn=$reason&LOG=$log_ums_work");
      exit();
}
// die("qedit 2 : ".var_export($fixm_array,true));
// die("fgroup=$fgroup");
//$myMainObject->fgroup = $fgroup;

/*

fixm technique pour qedit sert pour les new records pour pas que les colonnes fixed mode restent vides dans le handle
ici ce n'est pas applicable car on n'as pas d'insert de new records

*/

//$myMainObject->fixm_array = $fixm_array;
//$myMainObject->fixm_disable = $fixmdisable;
//$myMainObject->fixmtit = $fixmtit;
//$myMainObject->commonFields = $comfld;
if(isset($copypast)) $myMainObject::$copypast = $copypast;

$out_scr .= "<div class=\"filebox editcard\">";
if($fixmtit) 
{
        $out_scr .= "<h3 class=\"bluetitle\"><i></i>$fixmtit</h3>";
}
$out_scr .= '<form method="post" action="main.php">';
$out_scr .= "<div class=\"hzm_table_container\">";
if($ids)
{
        if($ids=="all") { 
                
                // $fld_ACTIVE = $myMainObject->fld_ACTIVE();
                if(!$myMainObject->PK_MULTIPLE) $myMainObject->where(" id > 0");
                $myMainObject->select_visibilite_horizontale();
                $limit = "";
        }        
        else if($ids=="cond") 
        {
            $limit = "";    
            $myMainObject->where(" $cond ");
        }
        else if($ids) 
        {
            $ids_arr = explode(",",$ids);
            $limit = count($ids_arr);    
            $myMainObject->where(" id in ($ids) ");
        }
        foreach($_REQUEST as $item => $item_value)
        {
            if(AfwStringHelper::stringStartsWith($item,"sel_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        $myMainObject->fixModeSelect($item_trimmed, $item_value);
                        $myMainObject->fixModeSet($item_trimmed, $item_value);
                }
            }
            
            if(AfwStringHelper::stringStartsWith($item,"her_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        $myMainObject->$item_trimmed = $item_value;
                }
            }
        }
        $items_objs = $myMainObject->loadMany($limit);
}

foreach($items_objs as $qedit_obj_id => $qedit_obj) 
{
    $items_objs[$qedit_obj_id]->fixm_array = $fixm_array;
    foreach($_REQUEST as $item => $item_value)
    {
            if(AfwStringHelper::stringStartsWith($item,"her_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        $items_objs[$qedit_obj_id]->$item_trimmed = $item_value;
                }
            }
    }    
}



$myMainObject->updatedFromQEdit = $updo;
$myMainObject->id_origin = $id_origin;
$myMainObject->class_origin = $class_origin;
$myMainObject->module_origin = $module_origin;
$myMainObject->step_origin = $step_origin;
$myMainObject->mode_origin = $mode_origin;
$myMainObject->return_mode = $return_mode;

$nb_objs = count($items_objs);

if($nb_objs>0)
{
        reset($items_objs);
        $first_item = current($items_objs);
        // ex : ccol=roombed_enum&cfield=period_title&cvalue=price
        $cross_col = $ccol;
        $crossed_field_col = $cfield;
        $crossed_value_col = $cvalue;
        
        $crossed_value_col_desc = AfwStructureHelper::getStructureOf($first_item, $crossed_value_col);
        $crossed_value_col_mfk_separator = $first_item->getSeparatorFor($crossed_value_col);
        $pk_col = $first_item->getPK();
        $pk_desc = AfwStructureHelper::getStructureOf($first_item, $pk_col);
        
        $info_tooltip  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($first_item, $crossed_value_col, "TOOLTIP", $lang));
        $info_unit  = trim(AfwLanguageHelper::getTranslatedAttributeProperty($first_item,$crossed_value_col, "UNIT", $lang));
        $html_before = "";
        $html_after = "";
        if($info_unit or $info_tooltip)
        { 
                $css_input_width_pct = 100;
                if($info_tooltip) $css_input_width_pct -= 10; 
                
                
                if($info_unit) $css_input_width_pct -= 20; 
                $css_form_control_div_special = "";
                if($crossed_value_col_desc["ROWS"])
                {
                   $rows = $crossed_value_col_desc["ROWS"];
                   if($rows>3) $rows = 3;
                   if($rows<1) $rows = 1;
                   
                   $css_form_control_div_special .= " rows$rows";
                }
                
                $css_unit_tooltip_active = "class_input_width_$css_input_width_pct";
                
                $html_before .= "<div class=\"form-control-div $css_unit_tooltip_active $css_form_control_div_special\">";
                if($info_tooltip) $html_before .= "<div class=\"hzm_tooltip\"><img data-toggle=\"tooltip\" data-placement=\"left\" class=\"hzm_tt\" title=\"".$info_tooltip."\" src=\"../lib/images/information.png\" /></div>";
                
                if($info_unit) $html_after .= "<div class=\"hzm_unit\">".$info_unit."</div>";
                $html_after .=  "</div>";
        }
        
                                
         
        $data = array();
        $index_cross = array();
        
        $indexc = 1;
        $header_trad = array();
        $header_trad[$cross_col] = $first_item->translate($cross_col,$langue);
        $crossed_row_num = 0;
        foreach($items_objs as $objI)
        {
            $cross_val = $objI->showAttribute($cross_col); //$objI->getVal($cross_col);
            
            $crossed_value_col_i = $crossed_value_col . "_" . $crossed_row_num;
            $pk_col_i = $pk_col . "_" . $crossed_row_num;
            
            if(!$index_cross[$cross_val])
            {
                $index_cross[$cross_val] = $indexc;
                $indexc++; 
            }
            $data[$index_cross[$cross_val]-1][$cross_col] = $cross_val;
            
            if(!$objI->attributeIsApplicable($crossed_value_col))
            {
                   list($icon,$textReason, $wd, $hg) = $objI->whyAttributeIsNotApplicable($crossed_value_col);
                   if(!$wd) $wd = 20;
                   if(!$hg) $hg = 20;
                   $data[$index_cross[$cross_val]-1][$objI->calc($crossed_field_col)] = "<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='$textReason'  width='$wd' heigth='$hg'>";
            }
            else
            {
                // here the input
                $data[$index_cross[$cross_val]-1][$objI->calc($crossed_field_col)] = $html_before;
                ob_start();
                $col_val = $objI->getVal($crossed_value_col);
              	$data_loaded=($objI->getId()>0);
                $tab_index = 0;
                
                $type_pkinput_ret = AfwEditMotor::hidden_input($pk_col_i, $pk_desc, $objI->getId(), $objI); 
                
                $type_input_ret = AfwEditMotor::type_input($crossed_value_col_i, $crossed_value_col_desc, $col_val, $objI, $crossed_value_col_mfk_separator, $data_loaded, "", $tab_index);
                
                $data[$index_cross[$cross_val]-1][$objI->calc($crossed_field_col)] .= ob_get_clean();
                $data[$index_cross[$cross_val]-1][$objI->calc($crossed_field_col)] .= $html_after;

                
                
                   
            }                                                            
            
            $header_trad[$objI->calc($crossed_field_col)] = $objI->translate($objI->decode($crossed_field_col),$langue);
            $crossed_row_num++;
        }
        
        
        list($html, ) = AfwShowHelper::tableToHtml($data, $header_trad);


        
        $out_scr .= $header_bloc_crossed;

        $out_scr .= "<div class='hzm_table_container'>$html</div>";
        
        $out_scr .= "<div class='hzm_table_container'>";
        if($myMainObject->return_mode)
        {
                 $submit_qedit_title_code = 'UPDATE_AND_RETURN';
                 $submit_name = "submit_return";
        }
        else
        {
                 $submit_qedit_title_code = 'UPDATE';
                 $submit_name = "submit";
        }
        $submit_title = $myMainObject->translate($submit_qedit_title_code,$lang,true);
        $out_scr .=  "<input type=\"submit\" name=\"$submit_name\"  id=\"$submit_name\" class=\"$class_inputSubmit\" value=\"&nbsp; $submit_title &nbsp;\" width=\"200px\" height=\"30px\" />";
        $out_scr .= "</div>";
        
        
        $hid_sel_ = "";
        
        foreach($_REQUEST as $item => $item_value)
        {
            if(AfwStringHelper::stringStartsWith($item,"sel_"))
            {
                $hid_sel_ .= "<input type=\"hidden\" name=\"$item\"   value=\"$item_value\"/> \n";        
            }
        }            
        
        $out_scr .= '   <input type="hidden" name="id_origin"   value="'.$id_origin.'"/>
        		<input type="hidden" name="class_origin"   value="'.AfwStringHelper::hzmEncrypt($class_origin).'"/>
                        <input type="hidden" name="module_origin"   value="'.$module_origin.'"/>
                        <input type="hidden" name="step_origin"   value="'.$step_origin.'"/>
        		<input type="hidden" name="ids"   value="'.$ids.'"/>
                        <input type="hidden" name="cond"   value="'.$cond.'"/>
        		<input type="hidden" name="updo"   value="'.$updo.'"/>
                        
                        <input type="hidden" name="cross_col"   value="'.$cross_col.'"/>
                        <input type="hidden" name="crossed_field_col"   value="'.$crossed_field_col.'"/>
                        <input type="hidden" name="crossed_value_col"   value="'.$crossed_value_col.'"/>
                        
        		<input type="hidden" name="fixm"   value="'.$fixm.'"/>
        		<input type="hidden" name="fixmtit"   value="'.$fixmtit.'"/>
        		<input type="hidden" name="fixmdisable"   value="'.$fixmdisable.'"/>
                        <input type="hidden" name="cl"   value="'.AfwStringHelper::hzmEncrypt($cl).'"/>
        		'.$hid_sel_.'
                        <input type="hidden" name="currmod"   value="'.$currmod.'"/>
        		<input type="hidden" name="class_obj"  value="'.AfwStringHelper::hzmEncrypt($cl).'"/>
        		<input type="hidden" name="nb_objs"     value="'.$nb_objs.'"/>
                        <input type="hidden" name="popup"   value="'.$popup_2.'"/>
                        <input type="hidden" name="pbmon"   value="'.$method_back.'"/>
                        <input type="hidden" name="Main_Page" id="Main_Page" value="afw_handle_default_crossed.php"/>
        		
        	    ';
        $out_scr .= $footer_bloc_crossed;
        $out_scr .= "</div></form></div>";
        /*
        $datatable_on = true;
        $mode_hijri_edit = true;
        */
}        	    
?>