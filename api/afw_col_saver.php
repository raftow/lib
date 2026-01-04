<?php

$file_dir_name = dirname(__FILE__);
require_once ('../afw/afw_autoloader.php');
include_once ("../afw/afw_error_handler.php");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

AfwSession::startSession();
$update_context = 'afw object column value change with network api';
// echo "here5";
require_once ("$file_dir_name/../../config/global_config.php");
// echo "here6";
// old include of afw.php
$only_members = true;
$debug_name = 'col saver';
// echo "here4";

$cls = trim($_POST['cls']);
$currmod = trim($_POST['currmod']);
$idobj = trim($_POST['idobj']);
$col = trim($_POST['col']);
$val = trim($_POST['val']);
$lang = trim($_POST['lang']);
$data = [];

if ((!$currmod) or (!$idobj) or (!$cls) or (!$col)) {
    $data['status'] = 'error';
    $data['message'] = "afw col saver error : attributes required missed currmod=$currmod idobj=$idobj cls=$cls col=$col";
    die(json_encode($data));
}

$MODULE = $currmod;
include ("$file_dir_name/../lib/afw/afw_check_member.php");
if (!$lang)
    $lang = AfwLanguageHelper::getGlobalLanguage();

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
    $data['message'] = $myObj->tm('OBJECT_NOT_FOUND', $lang) . " : currmod=$currmod idobj=$idobj cls=$cls";
    die(json_encode($data));
}

$can_popupEditCol = $myObj->userCanPopupEditCol($objme, $col);
if (!$can_popupEditCol) {
    $data['status'] = 'error';
    $data['message'] = 'المعذرة هذه عملية تعديل بوباب سريع جدا على هذا الحقل تحتاج صلاحية ! ';
    die(json_encode($data));
}

list($can_edit_me, $can_t_edit_me_reason) = $myObj->userCanEditMe($objme);
if (!$can_edit_me) {
    $data['status'] = 'error';
    $data['message'] = 'المعذرة هذه العملية للتعديل على هذا السجل تحتاج صلاحية : ' . $can_t_edit_me_reason;
}

$old_val = $myObj->getVal($col);
if ($old_val != $val) {
    $myObj->set($col, $val);
    $done = $myObj->commit();
} else {
    $done = true;
}

if ($done) {
    $data['status'] = 'success';
    $data['message'] = '';
    $data['aff'] = $myObj->decode($col);  // "$myObj => decode($col) = " .
} else {
    $data['status'] = 'fail';
    $data['message'] = "check the beforeUpdate/beforeMaj methods in $cls, because the update has been rejected";
}

die(json_encode($data));
