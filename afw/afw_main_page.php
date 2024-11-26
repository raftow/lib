<?php
class AfwMainPage
{
    public static function echoMainPage($current_module, $Main_Page, $module_path)
    {
        $curr_path = dirname(__FILE__);
        include("$curr_path/afw_main_start.php");
        // die("echoMainPage($current_module, $Main_Page, $module_path) after afw_main_start lang=$lang");
        // die("echoMainPage 20241119  : after include($curr_path/afw_main_start.php) lang = ".$lang);
        echo self::renderMainPage($Main_Page, $module_path, $header_template, $menu_template, $body_template, $footer_template, $lang, $current_module);
    }

    public static function echoDirectPage($current_module, $direct_page, $direct_page_path, $header_template="direct", $menu_template="direct", $body_template="direct", $footer_template="direct")
    {
        $curr_path = dirname(__FILE__);
        include("$curr_path/afw_direct_start.php");
        // die("echoDirectPage 20241119  : after include($curr_path/afw_main_start.php) lang = ".$lang);
        echo self::renderDirectPage($direct_page, $direct_page_path, $header_template, $menu_template, $body_template, $footer_template, $lang, $current_module);
    }

    private static function renderDirectPage($direct_page, $module_path, $header_template="direct", $menu_template="direct", 
                        $body_template="direct", $footer_template="direct", $lang, $current_module)
    {
        // die("dgb::renderDirectPage($direct_page, $module_path, $header_template, $menu_template, $body_template, $footer_template, $current_module)");
        if(!$current_module) $current_module = "ums";
        $the_main_section_file = "$module_path/$direct_page";
        $out_scr =  AfwHtmlPageConstructHelper::renderPage($lang, $header_template, 
                        $menu_template, 
                        $body_template, 
                        $footer_template, 
                        $_REQUEST, 
                        $the_main_section_file,
                        $need_ob=true,        
                    );

        return $out_scr;  
        
    }


    private static function renderMainPage($Main_Page, $module_path, $header_template, $menu_template, $body_template, $footer_template, $lang, $current_module)
    {
        if(!$Main_Page) throw new AfwRuntimeException("Main Page not defined in renderMainPage");;
        if(!$module_path) throw new AfwRuntimeException("Module path not defined in renderMainPage");;

        if ((AfwStringHelper::stringStartsWith($Main_Page, "afw_mode_"))) $My_Module = "lib/afw/modes";
        elseif ((AfwStringHelper::stringStartsWith($Main_Page, "afw_handle_"))) $My_Module = "lib/afw/modes";
        elseif ((AfwStringHelper::stringStartsWith($Main_Page, "afw_template_"))) $My_Module = "lib/afw/modes";
        
        if ((!$My_Module) and (AfwStringHelper::stringStartsWith($Main_Page, "afw_"))) $My_Module = "lib/afw";
        // if((!$My_Module) and (AfwStringHelper::stringStartsWith($Main_Page,"r fw_"))) $My_Module = "lib/r fw";
        if (!$My_Module) $My_Module = $current_module;
        if ($My_Module == "afw") $My_Module = "lib/afw";
        // die("for renderMainPage($Main_Page, $module_path, $header_template, ... , $lang, $current_module) My_Module = $My_Module");
        if ($My_Module)
            $Main_Page_path = "$module_path/../$My_Module";
        else
            $Main_Page_path = "$module_path";
        
        $the_main_section_file = "$Main_Page_path/$Main_Page";
        if(!file_exists($the_main_section_file))
        {
            throw new AfwRuntimeException("Main Section File $the_main_section_file not found module_path=$module_path");
        }
        
        $out_scr =  AfwHtmlPageConstructHelper::renderPage($lang, $header_template, 
        $menu_template, 
        $body_template, 
        $footer_template, 
        $_REQUEST, $the_main_section_file);
        
        
        if (!$out_scr) {
            $out_scr = "<div class='afw_tech'><center>";
            if (AfwSession::config("MODE_DEVELOPMENT", false)) {
                //throw new AfwRuntimeException("<h1>no output from $Main_Page_path/$Main_Page</h1> ($module_dir_name == $file_dir_name)");
                $out_scr .= "<h1>no output from page $the_main_section_file : Main_Page=$Main_Page<br> path=$Main_Page_path</h1> <br>(curmodulepath=$module_path)";
            }
            $out_scr .= "<div style='padding:40px;text-align:center'><center><img src='../lib/images/page_not_found.png'><BR><BR><BR><BR><span class='error'>هذه الصفحة غير موجودة </span></center></div>";
            $out_scr .= "</center></div>";
        }
        
        return $out_scr;        
    }
}