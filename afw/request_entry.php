<?php
//die("afw_error_handler.php");
include_once("afw_error_handler.php");

if(!$MODULE) throw new AfwRuntimeException("the index of the MVC system require that MODULE be defined");
$file_dir_name = dirname(__FILE__);



$request = array();
foreach($_REQUEST as $key => $value) 
{
        if($key=="cn") $controllerName = $value;
        elseif($key=="mt") $methodName = $value;
        elseif($key=="mp") $Main_Page = $value;
        elseif($key=="pm") $Main_Page_Module = $value;
        elseif($key=="cm") $currmod = $value;
        elseif($key=="cs") $currstep = $value;
        elseif($key=="io") $id_origin = $value;
        elseif($key=="co") $class_origin = $value;
        elseif($key=="mo") $module_origin = $value;
        elseif($key=="so") $step_origin = $value;
        elseif($key=="no") $newo = $value;
        elseif($key=="lm") $limit = $value;
        elseif($key=="xt") $fixmtit = $value;
        elseif($key=="xd") $fixmdisable = $value;
        elseif($key=="xm") $fixm = $value;
        elseif($key=="cl") $cl = $value;
        elseif($key=="md") $currmod = $value;
        elseif($key=="id") $id = $value;
        else $$key = $request[$key] = $value;

        if($key=="mp") 
        {
                if($value=="mb") $Main_Page = "afw_mode_minibox.php";    // modes/ removed from all below
                elseif($value=="ed") $Main_Page = "afw_mode_edit.php";  
                elseif($value=="qe") $Main_Page = "afw_mode_qedit.php";  
                elseif($value=="ds") $Main_Page = "afw_mode_display.php";  
                elseif($value=="st") $Main_Page = "afw_mode_stats.php";  
                elseif($value=="au") $Main_Page = "afw_mode_audit.php";  
                elseif($value=="cn") $Main_Page = "afw_mode_confirm.php";  
                elseif($value=="ce") $Main_Page = "afw_mode_crossed.php";  
                elseif($value=="db") $Main_Page = "afw_mode_ddb.php";  
                elseif($value=="qs") $Main_Page = "afw_mode_qsearch.php";  
                elseif($value=="sr") $Main_Page = "afw_mode_search.php";  
                else $Main_Page = $value; //throw new AfwRuntimeException("afw mode $value unknown");
        }
        
}

//die("after request entry decode : Main_Page=$Main_Page Main_Page_Module=$Main_Page_Module currmod = $currmod id_origin = $id_origin class_origin = $class_origin module_origin = $module_origin step_origin = $step_origin");

if($_FILES and (count($_FILES)>0))
{
        $request["_REQUEST_FILES"] = $_FILES;  
}


set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);




require_once("afw_autoloader.php");
$parent_module = AfwSession::config("main_module", "");
if($MODULE) AfwAutoLoader::addModule($MODULE);
if($currmod) AfwAutoLoader::addModule($currmod);
if($parent_module) AfwAutoLoader::addMainModule($parent_module);
//die("rafik see this : AfwAutoLoader::addMainModule($MODULE)");

// As per security purposes clean data submitted by user 
// to avoid CROSS-Site scripting injection 
foreach($request as $key => $kval) $request[$key] = AfwStringHelper::clean_input($request[$key]);

include_once ("$file_dir_name/../../$MODULE/ini.php");
include_once ("$file_dir_name/../../$MODULE/module_config.php");
//die("rafik see this : will include_once ($file_dir_name/../../$MODULE/module_config.php)");
require_once ("$file_dir_name/../../$MODULE/application_config.php");
AfwSession::initConfig($config_arr);
// die("second initConfig ".AfwSession::log_config());

$parent_module = AfwSession::config("main_module", "");
if($parent_module) AfwAutoLoader::addMainModule($parent_module);

// rafik : should be after the above includes to avoid objme : __PHP_Incomplete_Class Auser  or Employee or Sempl etc ....
AfwSession::startSession();
//setcookie(session_name(), session_id(), NULL, NULL, NULL, 0);
//die("rafik 3002 session table : ".var_export($_SES SION,true));
require_once("$file_dir_name/../../external/db.php");
// 
