<?php

$file_dir_name = dirname(__FILE__);
require_once('../afw/afw_autoloader.php');
include_once("../afw/afw_error_handler.php");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

AfwSession::startSession();
$update_context = 'afw object column value change with network api';
// echo "here5";
require_once("$file_dir_name/../../config/global_config.php");
// echo "here6";
// old include of afw.php
$only_members = true;
$debug_name = 'status changer';
// echo "here4";
$cls = trim($_POST['cls']);
$currmod = trim($_POST['currmod']);
$idobj = trim($_POST['idobj']);
$csmethod = trim($_POST['csmethod']);
$lang = trim($_POST['lang']);
if (!$lang) $lang = AfwLanguageHelper::getGlobalLanguage();
$data = [];

if ((!$currmod) or (!$idobj) or (!$cls) or (!$csmethod)) {
    $data['status'] = 'error';
    $data['message'] = "afw status changer error : attributes required missed currmod=$currmod idobj=$idobj cls=$cls csmethod=$csmethod";
    die(json_encode($data));
}

$MODULE = $currmod;
include("$file_dir_name/../lib/afw/afw_check_member.php");


// echo "here3";
AfwAutoLoader::addMainModule($currmod);
$required_modules = AfwSession::config('required_modules', []);
foreach ($required_modules as $required_module) {
    AfwAutoLoader::addModule($required_module);
}

/** @var AFWObject $myObj */
$myObj = new $cls();
$myObj_loaded = $myObj->load($idobj);

if (!$myObj_loaded) {
    $data['status'] = 'error';
    $data['message_client'] = $myObj->tm('OBJECT_NOT_FOUND', $lang);
    $data['message'] = "Failed to load load (current-module=$currmod id-object=$idobj class=$cls)";
    die(json_encode($data));
}

$can_changeStatus = $myObj->userCanChangeStatus($objme, $csmethod);
if (!$can_changeStatus) {
    $data['status'] = 'error';
    $data['message'] = 'You need to override userCanChangeStatus method in class ' . $cls . ' to allow changing status with method ' . $csmethod;
    $data['message_client'] = 'المعذرة هذا الإجراء يحتاج صلاحية خاصة ! ';
    die(json_encode($data));
}

list($can_edit_me, $can_t_edit_me_reason) = $myObj->userCanEditMe($objme);
if (!$can_edit_me) {
    $data['status'] = 'error';
    $data['message_client'] = 'المعذرة التعديل على هذا السجل يحتاج صلاحية خاصة ! ';
    $data['message'] = $can_t_edit_me_reason;
    die(json_encode($data));
}

list($err, $inf, $war, $tech, $result_arr) = $myObj->$csmethod($lang);

if (!$err) {
    $data['status'] = 'success';
    $data['message'] = '';
    // ..
} else {
    $data['status'] = 'fail';
    $data['message'] = "check $csmethod method in $cls, because it returns error: $err , inf: $inf , war: $war , tech: $tech";
    $data['message_client'] = 'المعذرة حصل خطأ أثناء القيام بهذا الاجراء ! ';
}

die(json_encode($data));
