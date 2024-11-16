<?php
// die("DBG-mode main page");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);



$lang = "ar";


$file_dir_name = dirname(__FILE__);
$module_path = "$file_dir_name/../../$MODULE";

require_once("afw_autoloader.php");
include(dirname(__FILE__) . "/../../external/db.php");
// here old require of common.php


if (!$MODULE) {
    throw new RuntimeException("afw main start MODULE not found");
} else {
    if (!$currmod) $currmod = $_GET["currmod"];
    // die("afw main page MODULE is $MODULE currmod is $currmod");
}
require_once("$module_path/ini.php");
require_once("$module_path/module_config.php");
require_once("$module_path/application_config.php");
// die("DBG-begin of session start");
AfwSession::initConfig($config_arr);
AfwSession::startSession();
// die("DBG-session started");
if (!$objme) $objme = AfwSession::getUserConnected();
// die("DBG-User Connected Got");
// $mode_analysis = (AfwSession::config("MODE_DEVELOPMENT", false) or ($objme and $objme->isAdmin() and AfwSession::config("MODE_ANALYSIS", false)));

$parent_module = AfwSession::config("main_module", "");
if ($MODULE) AfwAutoLoader::addModule($MODULE);
if ($currmod) AfwAutoLoader::addModule($currmod);
if ($parent_module) AfwAutoLoader::addMainModule($parent_module);

//$uri_module = AfwUrlManager::currentURIModule();

include("afw_error_handler.php");

if (!$force_allow_access_to_customers) $only_members = true;

//foreach ($_REQUEST as $col => $val) ${$col} = $val;
$Main_Page = $_REQUEST["Main_Page"];
// die(var_export($_REQUEST,true));

$afw_check_member_file = "$module_path/../lib/afw/afw_check_member.php";
if (file_exists($afw_check_member_file)) {
    include($afw_check_member_file);
}

$header_template = AfwSession::config("header-template", "default"); 
$menu_template = AfwSession::config("menu-template", "default");
$body_template = AfwSession::config("body-template", "default");
$footer_template = AfwSession::config("footer-template", "default");

