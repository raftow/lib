<?php
require_once(dirname(__FILE__)."/../../../external/db.php");

//require_once("afw_config.php");
require_once("afw_qedit_motor.php");
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
 


// this method is bad we should find a way to make a 'special log' inside another var other than log var
// because it clear important sql log
$log = AfwSession::getLog("iCanDo");
$can = $objme->iCanDoOperationOnObjClass($myMainObject,"qedit");
$myMainObjectClass = get_class($myMainObject); 
$report_can_qedit = AfwSession::getLog("iCanDo");
if(!$can)
{
        // die("quick edit on $myMainObject class report $report_can_edit $log ");
        AfwSession::setSessionVar("operation", "quick edit on $myMainObjectClass class");
        AfwSession::setSessionVar("result", "failed");
        AfwSession::setSessionVar("report", $report_can_qedit);
        AfwSession::setSessionVar("other_log", $log);
        header("Location: /lib/afw/modes/afw_denied_access_page.php");      
        exit();
}
// die("qedit 2 : ".var_export($fixm_array,true));
// die("fgroup=$fgroup");
$myMainObject->fgroup = $fgroup;
$myMainObject->fixm_array = $fixm_array;
$myMainObject->submode = strtoupper($submode);
$myMainObject->fixm_disable = $fixmdisable;
$myMainObject->fixmtit = $fixmtit;
$myMainObject->commonFields = $comfld;
if(isset($copypast)) $myMainObject->copypast = $copypast;
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
            $ids_select = implode("','",$ids_arr);
            $limit = count($ids_arr);    
            $myMainObject->where($myMainObject->getPKField()." in ('$ids_select') ");
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
        // die("qedit::getSQLMany=".$myMainObject->getSQLMany());
        $qedit_objs = $myMainObject->loadMany($limit);
        
}

foreach($qedit_objs as $qedit_obj_id => $qedit_obj) 
{
    $qedit_objs[$qedit_obj_id]->fixm_array = $fixm_array;
    // die("obj=".$qedit_objs[$qedit_obj_id]." ->fixm_array = ".var_export($qedit_objs[$qedit_obj_id]->fixm_array,true));
    $qedit_objs[$qedit_obj_id]->submode = $submode;
    $qedit_objs[$qedit_obj_id]->fgroup = $fgroup;
    foreach($_REQUEST as $item => $item_value)
    {
            if(AfwStringHelper::stringStartsWith($item,"her_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        $qedit_objs[$qedit_obj_id]->$item_trimmed = $item_value;
                }
            }
    }    
}

if(!$newo) $newo = $myMainObject->QEDIT_MODE_NEW_OBJECTS_DEFAULT_NUMBER;         

for($i=0; $i<$newo; $i++) 
{
    $qedit_objs[-$i] = new $cl();
    $qedit_objs[-$i]->setId(-$i);
    $qedit_objs[-$i]->setOrder($i+1);
    $qedit_objs[-$i]->fixm_array = $fixm_array;
    // die("obj[-$i]=".$qedit_objs[-$i]." ->fixm_array = ".var_export($qedit_objs[-$i]->fixm_array,true));
    $qedit_objs[-$i]->submode = $submode;
    $qedit_objs[-$i]->fgroup = $fgroup; 
    foreach($_REQUEST as $item => $item_value)
    {
            if(AfwStringHelper::stringStartsWith($item,"sel_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        if($qedit_objs[-$i]->fieldExists($item_trimmed))
                        {
                              $qedit_objs[-$i]->fixModeSet($item_trimmed, $item_value);
                        }
                        else die("unknown param $item_trimmed trimmed from $item param!");
                }
            }
            
            if(AfwStringHelper::stringStartsWith($item,"her_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        $qedit_objs[-$i]->$item_trimmed = $item_value;
                }
            }
    }
}

$nb_objs = count($qedit_objs);

//echo "factory_result";
//print_r($myMainObject);
//die();

$out_scr = $header_bloc_edit;
$out_scr .= '<form method="post" action="main.php">';
$myMainObject->updatedFromQEdit = $updo;
$myMainObject->id_origin = $id_origin;
$myMainObject->class_origin = $class_origin;
$myMainObject->module_origin = $module_origin;
$myMainObject->step_origin = $step_origin;
$myMainObject->mode_origin = $mode_origin;
$myMainObject->return_mode = $return_mode;

$myMainObject->optimizeQEditLookups($submode, $fgroup);
$header_imbedded_arr = $myMainObject->qeditHeaderFooterEmbedded($submode, $fgroup);
if($header_imbedded_arr)
{
    $header_imbedded = "1";
}
else
{
    $header_imbedded = "";
}


// die("qedit myMainObject->fixm_array : ".var_export($myMainObject->fixm_array,true));
if(($myMainObject) and (!$header_imbedded))
{
    $out_scr .=  AfwShowHelper::showObject($myMainObject,"HTML", "afw_template_header_qedit.php");        
}
else 
{/*
    if($myMainObject->fixmtit)
    {
        $fixmtit = $myMainObject->fixmtit;
    }
    else
    {
        $fixmtit = AFWObject::traduireOperator("qedit_some_records", $lang).AfwUmsPagHelper::getPluralTitle($myMainObject, $lang,false);
    }*/
    if($fixmtit) 
    {
        $out_scr .= "<h3 class='bluetitle'><i></i>$fixmtit</h3>";
    }
    else
    {
        $out_scr .= "<h3 class='bluetitle'>وصف السجلات التي يتم العمل عليها</h3>";
    }
    $out_scr .= "<table class=\"display dataTable afwgrid\" style=\"width: 100%;\" cellspacing=\"3\" cellpadding=\"4\">";
}

if(!$nb_objs)
{
    if($myMainObject->no_row_to_qedit_message)
        $out_scr .= $myMainObject->no_row_to_qedit_message;
    elseif($not_found_mess)
        $out_scr .= $not_found_mess;
    else
        $out_scr .= "لا يوجد سجلات";
    



    if(($myMainObject) and (!$header_imbedded))
    {
        $out_scr .=  AfwShowHelper::showObject($myMainObject,"HTML", "afw_template_footer_qedit.php");
    }

    
    
    $datatable_on = true;
    $mode_hijri_edit = true;       	
}
else
{
        $num = 0;
        $tr_odd_even = "odd";
        $myMainObject->qeditSum = array();
        $data_template = array();
        $data_template["nb_records"] = count($qedit_objs);
        $class_db_structure = null;
        foreach($qedit_objs as $qedit_obj_id => $qedit_obj)
        {
            $qedit_obj->repareExistingObjectForEdit();
            $qedit_obj->qeditNum = $num;
            $qedit_obj->qeditCount = count($qedit_objs);
            if($qedit_obj->getId()<=0) $qedit_obj->odd_even = "new"; 
            else $qedit_obj->odd_even = $tr_odd_even;    
            // AfwSession::hzmLog("الله المستعان ".date("H:i:s")." before AfwShowHelper::showObject( $qedit_obj ... afw_template_row_qedit.php)","FOOTER");  
            if(!$class_db_structure) $class_db_structure = $qedit_obj::getDbStructure($return_type="structure", $attribute = "all");
            $out_scr .= AfwShowHelper::showObject($qedit_obj,"HTML", "afw_template_row_qedit.php", $color = false, $childrens = false, $decode = true, $virtuals = "", $indent = "", $data_template, $class_db_structure);
            $num++;
            if($tr_odd_even == "odd") $tr_odd_even = "even"; else $tr_odd_even = "odd";
            
            foreach($class_db_structure as $nom_col_obj => $desc_obj)
            {
               if($desc_obj["FOOTER_SUM"])
               {
                   if(!isset($myMainObject->qeditSum[$nom_col_obj])) $myMainObject->qeditSum[$nom_col_obj] = 0;
                   
                    $myMainObject->qeditSum[$nom_col_obj] += floatval($qedit_obj->getVal($nom_col_obj));
               }
            }
        }

        //AfwSession::hzmLog("الله المستعان ".date("H:i:s")." before AfwShowHelper::showObject( ... afw_template_footer_qedit.php)","FOOTER");        
        if($myMainObject)
        {
            $out_scr .= AfwShowHelper::showObject($myMainObject,"HTML", "afw_template_footer_qedit.php", $color = false, $childrens = false, $decode = true, $virtuals = "", $indent = "", $data_template);
        }
        else $out_scr .= "</table>";
        
        
        
        
        
        if(isset($copypast)) $copypast_field = "copypast";
        else $copypast_field = "copypast_notdefined";
        
        $hid_sel_ = "";
        
        foreach($_REQUEST as $item => $item_value)
        {
            if(AfwStringHelper::stringStartsWith($item,"sel_"))
            {
                $hid_sel_ .= "<input type=\"hidden\" name=\"$item\"   value=\"$item_value\"/> \n";        
            }
        }            
        
        
        $out_scr .= '   <input type="hidden" name="id_origin"   value="'.$id_origin.'"/>
        		<input type="hidden" name="class_origin"   value="'.$class_origin.'"/>
        		<input type="hidden" name="module_origin"   value="'.$module_origin.'"/>
                <input type="hidden" name="step_origin"   value="'.$step_origin.'"/>
        		<input type="hidden" name="limit"   value="'.$limit.'"/>
        		<input type="hidden" name="newo"   value="'.$newo.'"/>
        		<input type="hidden" name="ids"   value="'.$ids.'"/>
                <input type="hidden" name="cond"   value="'.$cond.'"/>
        		<input type="hidden" name="updo"   value="'.$updo.'"/>
        		<input type="hidden" name="fixm"   value="'.$fixm.'"/>
        		<input type="hidden" name="fixmtit"   value="'.$fixmtit.'"/>
                <input type="hidden" name="header_imbedded"   value="'.$header_imbedded.'"/>
        		<input type="hidden" name="'.$copypast_field.'"   value="'.$copypast.'"/>
        		<input type="hidden" name="fixmdisable"   value="'.$fixmdisable.'"/>
                        <input type="hidden" name="cl"   value="'.$cl.'"/>
                        <input type="hidden" name="submode"   value="'.$submode.'"/>
                        <input type="hidden" name="fgroup"   value="'.$fgroup.'"/>
        		'.$hid_sel_.'
                        <input type="hidden" name="currmod"   value="'.$currmod.'"/>
        		<input type="hidden" name="class_obj"  value="'.$cl.'"/>
        		<input type="hidden" name="nb_objs"     value="'.$nb_objs.'"/>
        		<input type="hidden" name="popup"   value="'.$popup_2.'"/>
                        <input type="hidden" name="Main_Page" id="Main_Page" value="afw_handle_default_qedit.php"/>
        		
        	    </form>';
        $out_scr .= $footer_bloc_edit;

        $datatable_on = true;
        $mode_hijri_edit = true;
}        	    
?>