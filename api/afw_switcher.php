<?php

$file_dir_name = dirname(__FILE__);
require_once("../afw/afw_autoloader.php");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
$lang = "en";

AfwSession::startSession();
$update_context = "YN Fields switcher with afw switcher network api";
// echo "here5"; 
require_once("$file_dir_name/../../external/db.php");
// echo "here6";
// old include of afw.php
$only_members = true;
$debug_name = "afw switcher";
// echo "here4";

$cl = trim($_POST['cl']);
$currmod = trim($_POST['currmod']);
$swc_id = trim($_POST['swc_id']);
$swc_col = trim($_POST['swc_col']);

if((!$swc_id) or (!$cl) or (!$swc_col)) die("afw error : nothing to switch, set cl and swc_id and swc_col param to non empty value");

$MODULE = $currmod;

if(!$MODULE) die("module not defined to access switcher");
  
include("$file_dir_name/../lib/afw/afw_check_member.php");
$lang = AfwSession::getSessionVar("lang");
if(!$lang) $lang = "ar";
 
// 

// prevent direct access
/*
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}*/
 
// get what user typed in autocomplete input


// echo "here3";
AfwAutoLoader::addMainModule($currmod);
$required_modules = AfwSession::config("required_modules", []);
foreach($required_modules as $required_module)
{
    AfwAutoLoader::addModule($required_module);
}

/**
 * @var AFWObject $myObj
 */
$myObj = new $cl();
$myObj_loaded = $myObj->load($swc_id);

if(!$myObj_loaded)
{
   $return_message = $myObj->tm("OBJECT_NOT_FOUND");
   die($return_message);
}

$can_switch_me = $myObj->userCanSwitchCol($objme, $swc_col);
if(!$can_switch_me)
{
	$return_message = "المعذرة هذه عملية تقليب  هذا الحقل تحتاج صلاحية ! ";
   die($return_message);
   // die(AfwSession::getLog("iCanDo")."<br>$return_message<br>can_delete_me = $can_delete_me");
}

list($can_edit_me, $can_t_edit_me_reason) = $myObj->userCanEditMe($objme);
if(!$can_edit_me)
{
	$return_message = "المعذرة هذه العملية للتعديل على هذا السجل تحتاج صلاحية : ".$can_t_edit_me_reason;
   die($return_message);
   // die(AfwSession::getLog("iCanDo")."<br>$return_message<br>can_delete_me = $can_delete_me");
}


$switched_message = $myObj->switchCol($swc_col);
echo $switched_message;