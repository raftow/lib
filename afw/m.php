<?php
$m_file_dir_name = dirname(__FILE__);
include_once("$m_file_dir_name/request_entry.php");

if($Main_Page)
{
        if(!$MODULE) throw new AfwRuntimeException("m.php : MODULE not defined");
        $file_module_path = "$m_file_dir_name/../../$MODULE";
        /*
        

        if(!$Main_Page_Module)
        {
                $main_toks = explode("_",$Main_Page);
                if($main_toks[0] == "modes/afw") $Main_Page_Module = "lib/afw";
        }
        if(!$Main_Page_Module) $Main_Page_Module = $MODULE;                
        $file_page_path = "$m_file_dir_name/../../$Main_Page_Module";
        
        */

        include_once ("$file_module_path/ini.php");
        include_once ("$file_module_path/module_config.php"); 
        require("$m_file_dir_name/afw_main_page.php"); 
        // die("before AfwMainPage::echoMainPag Main_Page=$Main_Page MODULE=$MODULE");
        $table = null;
        if(isset($_REQUEST["cl"])) $table = strtolower($_REQUEST["cl"]); 
        // $table = AfwStringHelper::classToTable($_REQUEST["cl"]);
        if(!$table) $table = "all";

        $options = AfwMainPage::getDefaultOptions($Main_Page, $MODULE, $table);
        AfwMainPage::echoMainPage($MODULE, $Main_Page, $file_module_path,$options);

}
else throw new AfwRuntimeException("m.php : Main_Page not defined");
