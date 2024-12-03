<?php
// die("afw direct start");
// session_start();
// die("session table : ".var_export($_SESSION,true));
$file_dir_name = dirname(__FILE__); 

function myErrorHandler($errno, $errstr, $errfile, $errline) 
{
   global $out_scr;
    //if($errno != 8)
    {
        $out_scr .= "<b>Custom error:</b> [$errno] $errstr<br>";
        $out_scr .= " Error on line $errline in $errfile<br>";
    }
    
}

set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);



require_once("afw_autoloader.php");
AfwSession::startSession();


$uri_module = AfwUrlManager::currentURIModule();
$direct_dir_name = dirname(__FILE__)."/../../$uri_module";
include_once ("$direct_dir_name/ini.php");
// die("$direct_dir_name/application_config.php");
include_once ("$direct_dir_name/application_config.php");
AfwSession::initConfig($config_arr);
$parent_module = AfwSession::config("main_module", "");
if($parent_module) AfwAutoLoader::addMainModule($parent_module);

include_once (dirname(__FILE__)."/../../$uri_module/module_config.php");

include("afw_error_handler.php");

//die("$direct_dir_name/ini.php");
/*
include_once ("$direct_dir_name/ini.php");
include_once ("$direct_dir_name/module_config.php");
include_once ("$direct_dir_name/application_config.php");
AfwSession::initConfig($config_arr);
*/

// rafik : should be after the above includes to avoid objme : __PHP_Incomplete_Class Auser  or Employee or Sempl etc ....

//setcookie(session_name(), session_id(), NULL, NULL, NULL, 0);
// if($Direct_Page == "main_page.php") die("rafik 3002 session table : ".var_export($_SESS ION,true));
require_once(dirname(__FILE__)."/../../external/db.php");
// 

/*

$a = new Auser();
$a->load(1);
if($a->isSuperAdmin()) die(" a isSuperAdmin ".var_export($a,true));
*/

if(!$force_allow_access_to_customers) $only_members = true;



foreach($_GET as $col => $val) ${$col} = $val;
foreach($_POST as $col => $val) ${$col} = $val;

include(dirname(__FILE__)."/../../lib/afw/afw_check_member.php");

if($check_depending_user_type!="NO-CHECK")
    include(dirname(__FILE__)."/../../$uri_module/$check_depending_user_type.php"); 

if((!$Direct_Page) and ($tplp))  $Direct_Page = "tpl/tpl_$tplp.php";

$page_name = $Direct_Page;
$page_name = AfwStringHelper::hzmStringOf($page_name);    

if($popup) 
  include(dirname(__FILE__)."/../hzm/web/hzm_popup_header.php");
elseif((!$nohf) and (!AfwSession::config("no_header_and_footer", false)))
  include(dirname(__FILE__)."/../hzm/web/hzm_header.php"); 


//die("$direct_dir_name/$Direct_Page");

if($prepare_direct_page) include($prepare_direct_page);
if((count($_POST)>0) and ($Direct_Page == "cline.php"))
{
    // die("command_line = $command_line _POST = ".var_export($_POST,true)." will include : $direct_dir_name/$Direct_Page");
}

//die(dirname(__FILE__)."/../../$uri_module/$Direct_Page");
include(dirname(__FILE__)."/../../$uri_module/$Direct_Page");

if($popup)
{
    include_once(dirname(__FILE__)."/../hzm/web/hzm_popup_footer.php");
} 
elseif($simple_footer or AfwSession::config("simple_footer", false))          
{
    include_once(dirname(__FILE__)."/../hzm/web/hzm_simple_footer.php");
} 
elseif((!$nohf) and (!AfwSession::config("no_header_and_footer", false)))
{
    include_once(dirname(__FILE__)."/../hzm/web/hzm_footer.php");
} 



?>