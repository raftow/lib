<?php

        $module_dir_name = $file_dir_name;
        
        $uri_module = AfwUrlManager::currentURIModule();
        
        function myErrorHandler($errno, $errstr, $errfile, $errline) 
        {
           global $out_scr;
            //if($errno != 8)
            {
                $out_scr .= "<b>Custom error:</b> [$errno] $errstr<br>";
                $out_scr .= " Error on line $errline in $errfile<br>";
            }
            
        }
        
        include("afw_error_handler.php");
        
        require_once("$module_dir_name/../../external/db.php");
        // here old require of common.php
        
        $only_members = true;


        /*
        if((!$_GET["Main_Page"]) && (!$_POST["Main_Page"]) && ($_SES SION["To_Main_Page"]))
        {
           $_GET["Main_Page"]=$_SES SION["To_Main_Page"];
           $_GET["cl"]=$_SESS ION["LAST_EDIT_CL"];
           $_GET["id"]=$_SES SION["LAST_EDIT_ID"];
        }
        */

        foreach($_GET as $col => $val) ${$col} = $val;
        foreach($_POST as $col => $val) ${$col} = $val;
        // die(var_export($_POST,true));
        // trouver le package
        include("$module_dir_name/../lib/afw/afw_check_member.php");
        if(!$htmview)
        {
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment;Filename=job_list.doc");
        }
        echo "<html>";
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
?>  
        <link href="../lib/hijra/jquery.calendars.picker.css" rel="stylesheet" type="text/css">
        <link href="../lib/css/autocomplete.css" rel="stylesheet" type="text/css">
	<link href="../lib/css/simple/style.less" rel="stylesheet" type="text/css">
	<link href="../lib/css/simple/style.css" rel="stylesheet" type="text/css">
        <link href="../lib/css/simple/front_menu.css" rel="stylesheet" type="text/css">
        <link href="../lib/afw/afw_style.css" rel="stylesheet" type="text/css">  

<body dir='rtl'>