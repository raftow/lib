<?php
$this_file_dir_name = dirname(__FILE__);
require_once ("afw_autoloader.php");
// die("afw_autoloader loaded");
AfwSession::startSession();
$objme = AfwSession::getUserConnected();
// die("getUserConnected found = [$objme]");
if(!$objme) 
{
    AfwSession::pushError("الرجاء تسجيل الدخول أولا");
    header("Location: login.php");
    exit();
}


if(!isset($MODULE) or (!$MODULE)) 
{
        $MODULE = "ums"; 
        require_once("$this_file_dir_name/../../$MODULE/ini.php"); 
        require_once("$this_file_dir_name/../../$MODULE/module_config.php"); 

}
if(!$_REQUEST["x"]) $_REQUEST["x"] = "u".$objme->id;
$me = $_REQUEST["x"];
$codeme = $_REQUEST["y"];
$display_deleted = isset($_REQUEST["dd"]) ? $_REQUEST["dd"] : false;
$correct_codeme = substr(md5("code".$me),0,8);
// die("$correct_codeme==$codeme , correct_codeme==codeme ?");
if($correct_codeme==$codeme)
{
        // 
        
        
        require_once("$this_file_dir_name/../../$MODULE/application_config.php");
        AfwSession::initConfig($config_arr, "system", "$this_file_dir_name/../../$MODULE/application_config.php");
        
        $doc_types = $module_config_token["file_types"];
        if(!$doc_types) $doc_types = "'define them in module_config.php'";
        
        $allowed_extensions = isset($module_config_token["file_exts"])? $module_config_token["file_exts"] : "";
        
        $AfileClass = AfwSession::config("$MODULE-AfileClass", AfwSession::config("AfileClass", "WorkflowFile"));
        
        $_REQUEST["Main_Page"]="afw_mode_qedit.php";
        $_REQUEST["cl"] = $AfileClass;
        if($AfileClass=="Afile") 
        {
                $_REQUEST["currmod"]="ums";
                $col_active = "avail";
        }
        else 
        {
                $_REQUEST["currmod"]="workflow";
                $col_active = "active";
        }
        $limit="200";
        $popup="";
        $ids="cond";
        $_REQUEST["limit"] = $limit;
        $_REQUEST["popup"] = $popup;
        $_REQUEST["ids"] = $ids;
        if(!$display_deleted) $cond_display_deleted = "$col_active='Y'";
        else $cond_display_deleted = "$col_active in ('N','W')";
        
        if($allowed_extensions) $cond_allowed_extensions = "and (afile_ext in ($allowed_extensions) )";
        else  $cond_allowed_extensions = "";
        
        $cond = "(doc_type_id is null or doc_type_id in (0,1,$doc_types)) $cond_allowed_extensions and $cond_display_deleted" ;
        $_REQUEST["cond"] = $cond;
        $fixm="owner_id=$me";
         
        $sel_owner_id=$me;
        $_REQUEST["fixm"] = $fixm;
        $_REQUEST["sel_owner_id"] = $sel_owner_id;
        
        
        $fixmtit="كل المرفقات";
        $fixmdisable="1";
        $not_found_mess="لا يوجد عندك ملفات تم تحميلها سابقا قم بتحميل ملفاتك عبر  شاشة ملفاتي من الصفحة الرئيسية";
                           
        
        
        // die("will include $this_file_dir_name/../../$MODULE/main.php");
        
        include("$this_file_dir_name/../../$MODULE/main.php");           
}
else
{
        include("$this_file_dir_name/modes/afw_denied_access_page.php");
}           
?>