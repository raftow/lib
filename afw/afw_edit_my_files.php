<?php

require_once ("afw_autoloader.php");
// die("afw_autoloader loaded");
$objme = AfwSession::getUserConnected();
die("getUserConnected found = [$objme]");
if(!$objme) 
{
    AfwSession::pushError("الرجاء تسجيل الدخول أولا");
    header("Location: login.php");
    exit();
}


if(!isset($MODULE) or (!$MODULE)) 
{
        $MODULE = "ums"; 
        require_once("$file_dir_name/../$MODULE/ini.php"); 
        require_once("$file_dir_name/../$MODULE/module_config.php"); 

}
if(!$_REQUEST["x"]) $_REQUEST["x"] = "u".$objme->id;
$me = $_REQUEST["x"];
$codeme = $_REQUEST["y"];
$display_deleted = isset($_REQUEST["dd"]) ? $_REQUEST["dd"] : false;
$correct_codeme = substr(md5("code".$me),0,8);
die("$correct_codeme==$codeme , correct_codeme==codeme ?");
if($correct_codeme==$codeme)
{
        // 
        if(!isset($file_dir_name)) $file_dir_name = dirname(__FILE__);
        
        require_once("$file_dir_name/../$MODULE/application_config.php");
        AfwSession::initConfig($config_arr);
        
        $doc_types = $module_config_token["file_types"];
        if(!$doc_types) $doc_types = "'define them in module_config.php'";
        
        $allowed_extensions = isset($module_config_token["file_exts"])? $module_config_token["file_exts"] : "";
        
        
        
        
                
        
        
        $Main_Page="afw_mode_qedit.php";
        $cl = "Afile";
        $currmod="ums";
        $limit="200";
        $popup="";
        $ids="cond";
        if(!$display_deleted) $cond_display_deleted = "avail='Y'";
        else $cond_display_deleted = "avail in ('N','W')";
        
        if($allowed_extensions) $cond_allowed_extensions = "and (afile_ext in ($allowed_extensions) )";
        else  $cond_allowed_extensions = "";
        
        $cond = "(doc_type_id is null or doc_type_id in (0,1,$doc_types)) $cond_allowed_extensions and $cond_display_deleted" ;
        $fixm="owner_id=$me";
         
        $sel_owner_id=$me;
        
        $_REQUEST["fixm"] = $fixm;
        $_REQUEST["sel_owner_id"] = $sel_owner_id;
        
        
        $fixmtit="كل المرفقات";
        $fixmdisable="1";
        $not_found_mess="لا يوجد عندك ملفات تم تحميلها سابقا قم بتحميل ملفاتك عبر  شاشة ملفاتي من الصفحة الرئيسية";
                           
        
        
        
        
        include("$file_dir_name/../$MODULE/main.php");           
}
else
{
        include("$file_dir_name/lib/afw/modes/afw_denied_access_page.php");
}           
?>