<?php
// die("DBG-mode main page");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);



$lang = "ar";


$file_dir_name = dirname(__FILE__);
$module_dir_name = "$file_dir_name/../../$MODULE";

require_once ("afw_autoloader.php");
require_once(dirname(__FILE__)."/../../external/db.php");        
// here old require of common.php


if(!$MODULE)
{
    die("afw main page MODULE not found");
}
else
{
    if(!$currmod) $currmod = $_GET["currmod"];
    // die("afw main page MODULE is $MODULE currmod is $currmod");
}
require_once ("$module_dir_name/ini.php"); 
require_once ("$module_dir_name/module_config.php");
require_once ("$module_dir_name/application_config.php");
// die("DBG-begin of session start");
AfwSession::initConfig($config_arr);
AfwSession::startSession();
// die("DBG-session started");
if(!$objme) $objme = AfwSession::getUserConnected();
// die("DBG-User Connected Got");
$mode_analysis = (AfwSession::config("MODE_DEVELOPMENT", false) or ($objme and $objme->isAdmin() and AfwSession::config("MODE_ANALYSIS", false)));

$parent_module = AfwSession::config("main_module", "");
if($MODULE) AfwAutoLoader::addModule($MODULE);
if($currmod) AfwAutoLoader::addModule($currmod);
if($parent_module) AfwAutoLoader::addMainModule($parent_module);
// die("DBG-AfwAutoLoader started");

if($mode_analysis)
{
    AfwSession::hzmLog("begin of session start", $MODULE);
    $start_main_time = microtime();
}



if($mode_analysis)
{
    $end_main_time = microtime();
    $duree_ms = round(($end_main_time - $start_main_time)*100000)/100;
    if($duree_ms<0) $duree_ms += 1000;
    AfwSession::hzmLog("end of session start $duree_ms milli-sec", $MODULE);
    if($duree_ms>100)
    {
        AfwSession::logSessionData();        
    }
}



/* 
@note rafik/17/6/2021 obsolete and fill the session of user better to remove
if($_GET["sslnk"])
{
        
        die("case of get sslnk");
        $sslnk = $_GET["sslnk"];
        unset($_GET["sslnk"]);
        $ss_link = $_SESS ION["SessionLink"][$sslnk];
        if($ss_link)
        {
            $page = $ss_link["page"];
            $page_params = $ss_link["params"]; 
            foreach($page_params as $param_name => $param_val)
            {
                $_GET[$param_name] = $param_val;
                $_REQUEST[$param_name] = $param_val;
            }
            // die("_GET = ".var_export($_GET,true));
            include("$module_dir_name/../$uri_module/$page");
            //die("$module_dir_name/../$uri_module/$page");
            resetLinkInSession($sslnk);
        }
        else
        {
            $out_scr = "Error : session link not found for Id = $sslnk : ".var_export($_SESS ION["SessionLink"],true);
        } 

}
else*/
if(true)
{
        if($mode_analysis)
        {
            AfwSession::hzmLog("begin of header-checks", $MODULE);
            $start_main_time = microtime();
        }
        
        $uri_module = AfwUrlManager::currentURIModule();

        include("afw_error_handler.php");    
        
        if(!$force_allow_access_to_customers) $only_members = true;

        /* obsolete
        if((!$_GET["Main_Page"]) && (!$_POST["Main_Page"]) && ($_SE SSION["To_Main_Page"]))
        {
           $_GET["Main_Page"]=$_SES SION["To_Main_Page"];
           $_GET["cl"]=$_SES SION["LAST_EDIT_CL"];
           $_GET["id"]=$_SES SION["LAST_EDIT_ID"];
        }*/
        
        foreach($_GET as $col => $val) ${$col} = $val;
        foreach($_POST as $col => $val) ${$col} = $val;
        //die("fgroup=$fgroup");
        $time_stamp_start = intval(date("His"));
        
        // die(var_export($_POST,true));
        // call afw check member.php
        // may need to be reviewed
        $afw_check_member_file = "$module_dir_name/../lib/afw/afw_check_member.php";
        if(file_exists($afw_check_member_file))
        {
            include($afw_check_member_file);
        }
        
       

           
        if((AfwStringHelper::stringStartsWith($Main_Page,"afw_mode_"))) $My_Module = "lib/afw/modes";
        elseif((AfwStringHelper::stringStartsWith($Main_Page,"afw_handle_"))) $My_Module = "lib/afw/modes";
        elseif((AfwStringHelper::stringStartsWith($Main_Page,"afw_template_"))) $My_Module = "lib/afw/modes";

        if((!$My_Module) and (AfwStringHelper::stringStartsWith($Main_Page,"afw_"))) $My_Module = "lib/afw";
        // if((!$My_Module) and (AfwStringHelper::stringStartsWith($Main_Page,"r fw_"))) $My_Module = "lib/r fw";

        if($My_Module=="afw") $My_Module = "lib/afw";
        
        if($My_Module)
            $Main_Page_path = "$module_dir_name/../$My_Module";
        else
            $Main_Page_path = "$module_dir_name";
        
        if($mode_analysis)
        {
            $end_main_time = microtime();
            $duree_ms = round(($end_main_time - $start_main_time)*100000)/100;
            if($duree_ms<0) $duree_ms += 1000;
            AfwSession::hzmLog("end of header-checks $duree_ms milli-sec", $MODULE);

            AfwSession::hzmLog("begin of including-main-page", $MODULE);
            $start_main_time = microtime();
        }
        // die("DBG-je suis avant include $Main_Page_path/$Main_Page");
        if(AfwSession::config("MODE_DEVELOPMENT", false)) $dbg_text = "$Main_Page_path/$Main_Page";
        else $dbg_text = "XXXX";
        $out_scr_prefix = "<!-- start of including of main page : $dbg_text body -->";
        $page_name = $Main_Page;
        $page_name = AfwStringHelper::hzmStringOf($page_name);
        $out_scr = "";
        $main_file_to_run = "$Main_Page_path/$Main_Page";
        if(file_exists($main_file_to_run))
        {
            include($main_file_to_run);
        }
        else throw new AfwRuntimeException("failed to open : main_file_to_run = $main_file_to_run");
        
        // die("DBG-je suis apres include $Main_Page_path/$Main_Page _POST = ".var_export($_POST,true));
        // if previous include fail you will not find this below and also :
        // @doc / faq / my page is shown without header / the include of main page failed see errors not shown in source of html page
        $out_scr_suffix = "<!-- end of including of main file page body -->";

        // die("DBG-My_Module=$My_Module => include of \"$Main_Page_path/$Main_Page\",  check_depending_user_type = $check_depending_user_type");
        
        //die("je suis apres include $Main_Page_path/$Main_Page");   
        
        if(!$out_scr)
        {
            $out_scr = "<div class='afw_tech'><center>";
            if(AfwSession::config("MODE_DEVELOPMENT", false)) 
            {
                //throw new AfwRuntimeException("<h1>no output from $Main_Page_path/$Main_Page</h1> ($module_dir_name == $file_dir_name)");
                $out_scr .= "<h1>no output from page $main_file_to_run : Main_Page=$Main_Page<br> path=$Main_Page_path</h1> <br>(curmodulepath=$module_dir_name)";
            }    
            $out_scr .= "<div style='padding:40px;text-align:center'><center><img src='../lib/images/page_not_found.png'><BR><BR><BR><BR><span class='error'>هذه الصفحة غير موجودة </span></center></div>";
            $out_scr .= "</center></div>";
        }
        else
        {
            $out_scr = $out_scr_prefix . $out_scr . $out_scr_suffix;
        }
        //else die("out_scr=$out_scr"); 
        if($mode_analysis)
        {
            $end_main_time = microtime();
            $duree_ms = round(($end_main_time - $start_main_time)*100000)/100;
            if($duree_ms<0) $duree_ms += 1000;
            AfwSession::hzmLog("end of including-main-page $duree_ms milli-sec", $MODULE);

            AfwSession::hzmLog("begin of header-include", $MODULE);
            $start_main_time = microtime();
        }

        $uri_module = AfwUrlManager::currentURIModule();

        if($popup)
        {
            // die("DBG-mode popup header => $module_dir_name/../lib/hzm/web/hzm_popup_header.php");
            include("$module_dir_name/../lib/hzm/web/hzm_popup_header.php");
        } 
        elseif((!$nohf) and (!AfwSession::config("no_header_and_footer", false)))
        {
            // if(!$jstree_activate) throw new AfwRuntimeException("jstree not activated");
            // die("DBG-hzm_header=> $module_dir_name/../lib/hzm/web/hzm_header.php");
            include("$module_dir_name/../lib/hzm/web/hzm_header.php");
           
        } 
        else
        {
            $no_header_and_footer = AfwSession::config("no_header_and_footer", false);
            // die("DBG-mode no header : nohf=$nohf, no_header_and_footer=$no_header_and_footer");
        }   

        if($mode_analysis)
        {
            $end_main_time = microtime();
            $duree_ms = round(($end_main_time - $start_main_time)*100000)/100;
            if($duree_ms<0) $duree_ms += 1000;
            AfwSession::hzmLog("end of header-include $duree_ms milli-sec", $MODULE);

            AfwSession::hzmLog("begin of footer-include", $MODULE);
            $start_main_time = microtime();
        }

        echo $out_scr;
        
        $time_stamp_end = intval(date("His"));
        
        $time_stamp_page_duration = $time_stamp_end - $time_stamp_start;
        // die("الله المستعان ".date("H:i:s")." $time_stamp_page_duration = $time_stamp_end - $time_stamp_start");
        if($popup) 
        {
            include_once("$module_dir_name/../lib/hzm/web/hzm_popup_footer.php");
        }
        elseif($simple_footer or AfwSession::config("simple_footer", false))
        {            
            include_once("$module_dir_name/../lib/hzm/web/hzm_simple_footer.php");
        }            
        elseif((!$nohf) and (!AfwSession::config("no_header_and_footer", false)))
        {
            include_once("$module_dir_name/../lib/hzm/web/hzm_footer.php");
        }


        

          
}

?>