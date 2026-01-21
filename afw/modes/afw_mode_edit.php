<?php
//die("rafik before anything $currstep = $currstep");
require_once(dirname(__FILE__)."/../../../config/global_config.php");
// here was old const php

$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}

require_once ('afw_rights.php');

if(!$currmod)
{
    $currmod = $uri_module;
}
if(!$currmod) $currmod = "ums";

$objme = AfwSession::getUserConnected();
if(!$objme) 
{
    AfwSession::pushError("الرجاء تسجيل الدخول أولا");
    header("Location: login.php");
    exit();
}

/**
 * @var AFWObject $myObj
 */

$myObj = new $cl();

$options = [];
$default_display_settings = $myObj->getDefautDisplaySettings();
$options["page_css_file"] = $default_display_settings["default_css_page"];

if($myObj->datatable_on_for_mode["edit"] or $default_display_settings["datatable_on_for_mode_edit"])
{
   $datatable_on = 1;
}

$development_mode = AfwSession::config("MODE_DEVELOPMENT", false);

if($tech_notes) $myObj->debugg_tech_notes = [$tech_notes];  
$inited_cols = array();

AfwMainPage::initOutput("");
if(!$id and $key) $id = "key-$key";
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
    /**
     * @var AFWObject $myObj
     */
    if($myObj->showErrorsAsSessionWarnings("edit"))
    {
        $err = "";
        $war = "";
        $inf = "";
        $dataErrStep = "all";
        if($myObj->editByStep) 
        {
            if($currstep>1)
            {
                $dataErrStepStart = 1;
                $dataErrStepEnd = $currstep-1;
            }
            else
            {
                $dataErrStepStart = 99;
                $dataErrStepEnd = 88;
            }
            
        }
        else 
        {
            $dataErrStepStart = null;
            $dataErrStepEnd = null;
        }
        list($is_obj_ok,$dataErr) = $myObj->isOk(true,$dataErrStep, $lang, [], $dataErrStepStart, $dataErrStepEnd);
        // die("showErrorsAsSessionWarnings::myObj::isOk(true,$dataErrStep, $lang, [], $dataErrStepStart, $dataErrStepEnd) => ".var_export($dataErr,true));
        if(!$is_obj_ok)
        {
            $war = implode("<br>",$dataErr);
        }
        

        if($err or $war or $inf)
        {
            AfwMainPage::addOutput(AfwHtmlHelper::showNotification($err, $war, $inf));
        }
    }

    $need_commit = false;
    foreach($_REQUEST as $item => $item_value)
    {
            if($myObj->id and AfwStringHelper::stringStartsWith($item,"force_")) // we can only use force_ mode if object is new and empty
            {
                $item_trimmed = substr($item,6);
                if($item_trimmed) 
                {
                        if(AfwStructureHelper::fieldExists($myObj, $item_trimmed))
                        {
                            if(AfwStructureHelper::canBeForced($myObj, $item_trimmed)) 
                            {
                                $myObj->setForce($item_trimmed, $item_value, is_numeric($item_value));
                                $need_commit = true;
                                // AfwSession::pushSuccess("$item_trimmed has been forced to [$item_value] value");
                            }
                            else AfwSession::pushWarning("$item_trimmed can't be forced");
                        }
                        else AfwSession::pushWarning("unknown param $item !");
                }
            }
    }
    if($need_commit) $myObj->commit();
}
else
{
    $need_commit = false;
    foreach($_REQUEST as $item => $item_value)
    {
            
            
            if((!$myObj->id) and AfwStringHelper::stringStartsWith($item,"sel_"))  // we can only use sel_ mode if object is new and empty
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) 
                {
                        if(AfwStructureHelper::fieldExists($myObj, $item_trimmed))
                        {
                            if($item_value or (!AfwStructureHelper::editIfEmpty($myObj, $item_trimmed))) 
                            {
                                $inited_cols[$item_trimmed] = true;
                                $myObj->fixModeSet($item_trimmed, $item_value, true, true);
                            }
                            else AfwSession::pushWarning("$item_trimmed is not usable in fix-mode as it is not `edit if empty` attribute");
                        }
                        else AfwSession::pushWarning("unknown param $item !");
                }        
                 
            }
    }

    
    $myObj_loaded = false;
}

/**
 * @var AFWObject $myObj
 */

// very bad it erase all log find better solution (named log) 
$log = AfwSession::getLog("iCanDo");
$can = ($objme and $objme->iCanDoOperationOnObjClass($myObj,"edit"));
$myObjClass = get_class($myObj); 
$report_can_edit = AfwSession::getLog("iCanDo");
if(!$can)
{
        $deniedEditMessage = $myObj->getDeniedEditMessage($lang);
        if($deniedEditMessage)
        {
                AfwMainPage::addOutput($deniedEditMessage);
                exit(); 
        }
        else
        {
            // @todo : to be changed every where we bad use the session vars
            AfwSession::setSessionVar("operation", "edit on $myObjClass class");
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
                $myObj->repareExistingObjectForEdit();
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
            $return_message = $myObj->tm("Return back", $lang);    
            $return_page = "main.php?Main_Page=afw_mode_qsearch.php&cl=$myObjClass&currmod=$currmod";
            $die_message = $myObj->tm("Object can not be loaded, seems has been deleted !", $lang);            
            $technical = "mode edit load by id failed : >> $cl load by [id=$id]";
            throw new AfwBusinessException($die_message, $lang, "be-record-not-found.png", $return_message,$return_page, $technical);
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

AfwMainPage::addOutput($header_bloc_edit);
if($die_message)
{
    AfwMainPage::addOutput("<div class='die_div'>".$die_message."</div>");
}
else
{
    AfwMainPage::addOutput('<form id="edit_form" name="edit_form" method="post" action="main.php" enctype="multipart/form-data" >');



    if($myObj->editByStep) 
    {
        if(!$currstep)
        { 
                if($myObj->getId()>0) 
                {
                    // @todo-$currstep = $objme->curStepFor[$myObj->getTableName()][$myObj->getId()];
                    // @todo-$currstep_orig = "curStepFor : cache for me for last-curr-step by object type and id";
                    if(!AfwFrameworkHelper::stepIsEditable($myObj, $currstep))
                    {
                        $currstep = 0;
                        $currstep_orig = "";
                    }
                    
                    if(!$currstep)
                    {
                            $currstep = $myObj->getLastEditedStep(); 
                            $currstep_orig = "get Last Edited Step";
                            if(!AfwFrameworkHelper::stepIsEditable($myObj, $currstep))
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
                    //AfwMainPage::addOutput( $objme->showObjTech();
                }    
                AfwMainPage::addOutput('<input type="hidden" name="oldcurrstep"   value="'.$currstep.'"/>');
        }
        else $currstep_orig = "defined";
        
        AfwMainPage::addOutput('<input type="hidden" name="currstep"   value="'.$currstep.'"/>');
        AfwMainPage::addOutput('<input type="hidden" name="currstep_orig"   value="'.$currstep_orig.'"/>');
        
        $myObj->currentStep = $currstep;
        // @todo-$objme->curStepFor[$myObj->getTableName()][$myObj->getId()] = $currstep;
        
    }
    AfwMainPage::addOutput($myObj->showHTML("afw_template_default_edit.php", array("inited_cols"=>$inited_cols)));

    AfwMainPage::addOutput('   <input type="hidden" name="pbmon"     value="1"/>
            <input type="hidden" name="file_obj"   value="_'.$cl.'"/>
            <input type="hidden" name="class_obj"  value="'.$cl.'"/>
            <input type="hidden" name="class_parent"  value="'.$clp.'"/>
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
                </script>");
    AfwMainPage::addOutput($footer_bloc_edit);
    $mode_hijri_edit = true;	    
}