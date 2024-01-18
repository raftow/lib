<?php
$m_file_dir_name = dirname(__FILE__);
include_once("$m_file_dir_name/request_entry.php");

if($Main_Page)
{
        if(!$Main_Page_Module)
        {
                $main_toks = explode("_",$Main_Page);    
                if($main_toks[0] == "modes/afw") $Main_Page_Module = "lib/afw";                
        }
        if(!$Main_Page_Module) $Main_Page_Module = $MODULE;
        
        $Main_Page_Path = "$m_file_dir_name/../../$Main_Page_Module/$Main_Page";
        if(!file_exists($Main_Page_Path))
        {
                throw new AfwRuntimeException("main page file not found : $Main_Page_Path Main_Page=[$Main_Page], Main_Page_Module=[$Main_Page_Module]");
        }
        else include ($Main_Page_Path);

        
        require("$m_file_dir_name/../hzm/web/hzm_header.php");
        
        echo $out_scr;
        require("$m_file_dir_name/../hzm/web/hzm_footer.php");
        

}
else die("Main_Page not defined");