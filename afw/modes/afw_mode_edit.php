<?php
// die("rafik before anything");
require_once(dirname(__FILE__)."/../../../external/db.php");
// here was old const php

require_once("afw_config.php");
require_once("afw_edit_motor.php");


require_once ('afw_rights.php');

if(!$currmod)
{
    $currmod = $uri_module;
}
if(!$currmod) $currmod = "pag";

$objme = AfwSession::getUserConnected();

$myObj = new $cl();
$default_display_settings = $myObj->getDefautDisplaySettings();
$page_css_file = $default_display_settings["default_css_page"];

if($myObj->datatable_on_for_mode["edit"] or $default_display_settings["datatable_on_for_mode_edit"])
{
   $datatable_on = 1;
}

$development_mode = AfwSession::config("MODE_DEVELOPMENT", false);

if($tech_notes) $myObj->debugg_tech_notes = [$tech_notes];  
$inited_cols = array();

$out_scr = "";
// die("rafik before object load id=$id , method_back=$method_back : => _POST = ".var_export($_POST,true));
if($id)
{
    if(is_numeric($id) or $myObj->PK_MULTIPLE) $myObj_loaded = $myObj->load($id);
    elseif($objme)  //  and $objme->isSuperAdmin()
    {
        $myObj = $cl::loadByCode($id);
        if($myObj) {
            $id = $myObj->id;
            $myObj_loaded = ($id > 0);
        }
        else die("404 bad request code");
    }
    else
    {
        die("404 bad request");
    }

    if($myObj->showErrorsAsSessionWarnings("edit"))
    {
        $err = "";
        $war = "";
        $inf = "";
        list($is_obj_ok,$dataErr) = $myObj->isOk(true,true);
        if(!$is_obj_ok)
        {
            $war = implode("<br>",$dataErr);
        }
        

        if($err or $war or $inf)
        {
            $out_scr .= AfwHtmlHelper::showNotification($err, $war, $inf);
        }
    }
}
else
{
    
    foreach($_REQUEST as $item => $item_value)
    {
            if(AfwStringHelper::stringStartsWith($item,"sel_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) 
                {
                        if($myObj->fieldExists($item_trimmed))
                        {
                            if($item_value or (!$myObj->editIfEmpty())) 
                            {
                                $inited_cols[$item_trimmed] = true;
                                $myObj->fixModeSet($item_trimmed, $item_value, true, true);
                            }
                        }
                        else die("unknown param $item !");
                }        
                 
            }
    }

     
    $myObj_loaded = false;
}
// very bad it erase all log find better solution (named log) 
$log = AfwSession::getLog();
$can = ($objme and $objme->iCanDoOperationOnObjClass($myObj,"edit"));
$report_can_edit = AfwSession::getLog("iCanDo");
if(!$can)
{
        $deniedEditMessage = $myObj->getDeniedEditMessage($lang);
        if($deniedEditMessage)
        {
                $out_scr .= $deniedEditMessage;
                exit(); 
        }
        else
        {
            // @todo : to be changed every where we bad use the session vars
            AfwSession::setSessionVar("operation", "edit on $my_class class");
            AfwSession::setSessionVar("result", "failed");
            AfwSession::setSessionVar("report", $report_can_edit);
            AfwSession::setSessionVar("other_log", $log);
            header("Location: /lib/afw/modes/afw_denied_access_page.php");      
            exit();
        }
}

//print_r($myObj);
//die($id);
if($id)
{
	    if($myObj_loaded)
        {
                list($edit_allowed, $edit_not_allowed_reason) = $myObj->userCanEditMe($objme);
                if(!$edit_allowed)
                {
			        $die_message = "لا يمكنك تحرير السجل : ".$myObj->getDisplay($lang); 
                    if($objme and $objme->isSuperAdmin()) $die_message .= "<br>".$edit_not_allowed_reason;                    
                    elseif($development_mode) $die_message .= "<!-- ".$edit_not_allowed_reason . " -->"; 
                    
                }
	    }
        else
        {
            $die_message = $myObj->tm("object can not be loaded")." >> $cl load by [id=$id]";
        }
}
else
{
    list($edit_allowed, $edit_not_allowed_reason) = $myObj->userCanEditMe($objme);
    
    if(!$edit_allowed)
    {
	    $die_message = "لا يمكنك إنشاء هذا السجل : ".$edit_not_allowed_reason;
    }
    else
    {
        $myObj->prepareNewObjectForEdit();
    }
    
    //die("filled object :".var_export($myObj,true));
}

$out_scr .= $header_bloc_edit;
if($die_message)
{
    $out_scr .= "<div class='die_div'>".$die_message."</div>";
}
else
{
    $out_scr .= '<form id="edit_form" name="edit_form" method="post" action="main.php" enctype="multipart/form-data" >';



    if($myObj->editByStep) 
    {
        if(!$currstep)
        { 
                if($myObj->getId()>0) 
                {
                    // @todo-$currstep = $objme->curStepFor[$myObj->getTableName()][$myObj->getId()];
                    // @todo-$currstep_orig = "curStepFor : cache for me for last-curr-step by object type and id";
                    if(!$myObj->stepIsEditable($currstep))
                    {
                        $currstep = 0;
                        $currstep_orig = "";
                    }
                    if(!$currstep)
                    {
                            $currstep = $myObj->getLastEditedStep(); 
                            $currstep_orig = "get Last Edited Step";
                            if(!$myObj->stepIsEditable($currstep))
                            {
                                $currstep = 0;
                                $currstep_orig = "";
                            }
                    } 
                }
                
                
                if(!$currstep) 
                {
                    $currstep_orig = "default";
                    $currstep = 1;
                    //$out_scr .= $objme->showObjTech();
                }    
                $out_scr .= '<input type="hidden" name="oldcurrstep"   value="'.$currstep.'"/>';
        }
        else $currstep_orig = "defined";
        
        $out_scr .= '<input type="hidden" name="currstep"   value="'.$currstep.'"/>';
        $out_scr .= '<input type="hidden" name="currstep_orig"   value="'.$currstep_orig.'"/>';
        
        $myObj->currentStep = $currstep;
        // @todo-$objme->curStepFor[$myObj->getTableName()][$myObj->getId()] = $currstep;
    }

    $out_scr .= $myObj->showHTML("afw_template_default_edit.php", array("inited_cols"=>$inited_cols));

    $out_scr .= '   <input type="hidden" name="pbmon"     value="1"/>
            <input type="hidden" name="file_obj"   value="_'.$cl.'"/>
            <input type="hidden" name="class_obj"  value="'.$cl.'"/>
            <input type="hidden" name="id_obj"     value="'.$id.'"/>
                    <input type="hidden" name="currmod"   value="'.$currmod.'"/>
                    <input type="hidden" name="popup"   value="'.$popup.'"/>
                    <input type="hidden" name="current_step"   value="'.$currstep.'"/>
            <input type="hidden" name="Main_Page" id="Main_Page" value="afw_handle_default_edit.php"/>
            <input type="hidden" name="My_Module" id="My_Module" value="afw"/>
            
            
            </form>' .
                "<script>
                $().ready(function() {
                    \$(\"#edit_form\").validate();
                });
                </script>";
    $out_scr .= $footer_bloc_edit;
    $mode_hijri_edit = true;	    
}
?>