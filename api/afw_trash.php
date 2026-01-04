<?php

$file_dir_name = dirname(__FILE__);
require_once("../afw/afw_autoloader.php");
include_once ("../afw/afw_error_handler.php");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
$lang = $_REQUEST['lang'];

AfwSession::startSession();
$update_context = "delete with afw trash network service";
// echo "here5"; 
require_once("$file_dir_name/../../config/global_config.php");
// echo "here6";
// old include of afw.php
$only_members = true;
$debug_name = "afw trash";
// echo "here4";

$cl = trim($_POST['cl']);
$currmod = trim($_POST['currmod']);
$del_id = trim($_POST['del_id']);
if((!$del_id) or (!$cl)) die("afw error : nothing to delete, set cl and del_id param to non empty value");

$MODULE = $currmod;

if(!$MODULE) die("module not defined to access trahser");
  
include("$file_dir_name/../lib/afw/afw_check_member.php");
$lang = AfwLanguageHelper::getGlobalLanguage();
 
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

$myObj = new $cl();
$myObj_loaded = $myObj->load($del_id);
$can_delete_me = $myObj->userCanDeleteMe($objme);
if($can_delete_me === -1)
{
	$return_message = "المعذرة هذه العملية تحتاج صلاحية ";
   die($return_message);
   // die(AfwSession::getLog("iCanDo")."<br>$return_message<br>can_delete_me = $can_delete_me");
}

if($can_delete_me === -2)
{
	$return_message = "قواعد العمل تمنعك من اجراء هذه العملية";
   die($return_message);
   // die(AfwSession::getLog("iCanDo")."<br>$return_message<br>can_delete_me = $can_delete_me");
}

if((!$can_delete_me) or ($can_delete_me <= 0))
{
	$return_message = "فشلت عملية اخذ اذن المسح";
   die($return_message);
   // die(AfwSession::getLog("iCanDo")."<br>$return_message<br>can_delete_me = $can_delete_me");
}


$deleted = false;

if($myObj_loaded)
{
   
   $deleted = $myObj->delete();
   if($deleted) $deleted_message = "DELETED";
   else $deleted_message = $myObj->tm("DELETE_NOT_ALLOWED",$lang)." : ".$myObj->tm($myObj->deleteNotAllowedReason,$lang);
}
else
{
   $deleted_message = $myObj->tm("OBJECT_NOT_FOUND",$lang);
}

echo $deleted_message;