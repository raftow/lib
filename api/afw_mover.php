<?php

$file_dir_name = dirname(__FILE__);
require_once("../afw/afw_autoloader.php");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
$lang = "en";

AfwSession::startSession();
$update_context = "main order field can be moved up or down with afw mover network api";
// echo "here5"; 
require_once("$file_dir_name/../../external/db.php");
// echo "here6";
// old include of afw.php
$only_members = true;
$debug_name = "afw mover";
// echo "here4";

$cl = trim($_POST['cl']);
$currmod = trim($_POST['currmod']);
$mv_id = trim($_POST['mv_id']);
$mv_sens = trim($_POST['mv_sens']);
$limitd = trim($_POST['limitd']);



if((!$currmod) or (!$mv_id) or (!$cl) or (!$mv_sens)) die("afw error : nothing to move, set cl and mv_id and mv_sens params to non empty values currmod=$currmod mv_id=$mv_id cl=$cl mv_sens=$mv_sens");

$MODULE = $currmod;
  
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
$myObj_loaded = $myObj->load($mv_id);


if(!$myObj_loaded)
{
   $return_message = $myObj->tm("OBJECT_NOT_FOUND");
   die($return_message);
}

list($can_move_me, $no_move_title, $no_move_reason) = $myObj->userCanMoveMe($objme, $mv_sens);
if(!$can_move_me)
{
	$return_message = "$no_move_title : $no_move_reason";
   die($return_message);
}

list($can_edit_me, $can_t_edit_me_reason) = $myObj->userCanEditMe($objme);
if(!$can_edit_me)
{
	$return_message = "المعذرة التعديل على هذا السجل يحتاج صلاحية : ".$can_t_edit_me_reason;
   die($return_message);
}


list($moved, $moved_message, $switchedMovedObj) = $myObj->moveMe($mv_sens);
echo $moved_message;

