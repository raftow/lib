<?php
// die("DBG-mode main page");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
global $loops;
if(!$loops) $loops = 1;
else $loops++;

if($loops>3) throw new AfwRuntimeException("Seems infinite loop call of afw direct page");


if (!$MODULE) $MODULE = $current_module;
$file_dir_name = dirname(__FILE__);
$module_path = "$file_dir_name/../../$MODULE";

require_once("afw_autoloader.php");
include(dirname(__FILE__) . "/../../external/db.php");
// 


if (!$MODULE) {
    throw new RuntimeException("MODULE not defined in afw direct start");
} else {
    if (!$currmod) $currmod = $_GET["currmod"];
    // die("afw main page MODULE is $MODULE currmod is $currmod");
}
require_once("$module_path/ini.php");
require_once("$module_path/module_config.php");
// throw new AfwRuntimeException("application_config is MODULE = $MODULE , current_module=$current_module config path = $module_path/application_config.php");
require_once("$module_path/application_config.php");
// die("DBG-begin of session start");
AfwSession::initConfig($config_arr);
AfwSession::startSession();
// die("DBG-session started");
if (!$objme) $objme = AfwSession::getUserConnected();
// die("DBG-User Connected Got");
// $mode_analysis = (AfwSession::config("MODE_DEVELOPMENT", false) or ($objme and $objme->isAdmin() and AfwSession::config("MODE_ANALYSIS", false)));

$lang = $_GET["lang"];
if(!$lang) $lang = AfwSession::config("default_lang", "ar");


$parent_module = AfwSession::config("main_module", "");
if ($MODULE) AfwAutoLoader::addModule($MODULE);
if ($currmod) AfwAutoLoader::addModule($currmod);
if ($parent_module) AfwAutoLoader::addMainModule($parent_module);
$uri_module = AfwUrlManager::currentURIModule();
$xmodule = AfwSession::getCurrentlyExecutedModule();
$company = AfwSession::currentCompany();
$site_name = AfwSession::getCurrentSiteName($lang);

include("afw_error_handler.php");

if (!$force_allow_access_to_customers) $only_members = true;

// die(var_export($_REQUEST,true));
/*
$afw_check_member_file = "$module_path/../lib/afw/afw_check_member.php";
if (file_exists($afw_check_member_file)) {
    include($afw_check_member_file);
}*/

if(!$header_template) $header_template = AfwSession::config("$direct_page_name-header-template", ""); 
if(!$menu_template) $menu_template = AfwSession::config("$direct_page_name-menu-template", "");
if(!$body_template) $body_template = AfwSession::config("$direct_page_name-body-template", "");
if(!$footer_template) $footer_template = AfwSession::config("$direct_page_name-footer-template", "");

// die("direct_page_name=$direct_page_name header_template=$header_template");

if(!$header_template) $header_template = AfwSession::config("header-template", "modern"); 
if(!$menu_template) $menu_template = AfwSession::config("menu-template", "modern");
if(!$body_template) $body_template = AfwSession::config("body-template", "modern");
if(!$footer_template) $footer_template = AfwSession::config("footer-template", "modern");

