<?php

//die("this page is obsolete to reimplement using MVC");
$file_dir_name = dirname(__FILE__);
// set_time_limit(8400);
// ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

//// 
/*
require_once("$file_dir_name/../cms/menu_functions.php");
require_once("$file_dir_name/../../config/global_config.php");  




*/
require_once("afw_autoloader.php");
require_once("$file_dir_name/../../config/global_config.php");  
require_once("afw_session.php");
AfwSession::startSession();
// $home_path_browser = $menu_title." ".AfwStringHelper::arrow($lang)." ".$path_title;
$force_allow_access_to_customers = true;

$check_depending_user_type="NO-CHECK";

$r = "control";
// die("here rafik 20210321 - 0");
$objToShow = AfwSession::getUserConnected();
//die("rafik dbg 11122244 ".var_export($objToShow,true));
/*if($objToShow and $objToShow->isAdmin() and false)
{
        die("here rafik 20210321");
        $_GET["Main_Page"] = "afw_mode_edit.php";
        $_GET["My_Module"] = "ums";
        $_GET["cl"] = "Auser";
        $_GET["currmod"]="ums";
        $_GET["id"] = $objToShow->getId();
        include("main.php");        
}
else*/
if($objToShow)
{
        $theme_name = AfwSession::config('theme','modern'); 
        $images = AfwThemeHelper::loadTheme();
        //die("theme=$theme_name images=".var_export($images,true));
        $direct_dir_name = dirname(__FILE__); 
        $Direct_Page = "show_object.php";
        //die("$Direct_Page to run under$file_dir_name/../$MODULE/afw_direct_page.php");
        include("afw_direct_page.php");

        
}
else
{
        echo "<div class='error'>No account object found, You need to login before</div>";
}
?>