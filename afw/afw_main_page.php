<?php
class AfwMainPage
{
    public static function resetOutput()
    {
        global $out_scr;
        $out_scr = "";
    }
    
    public static function initOutput($html)
    {        
        self::resetOutput();
        self::addOutput($html);
    }

    public static function addOutput($html)
    {
        global $out_scr;
        // if(AfwStringHelper::stringEndsWith($html,"id")) throw new AfwRuntimeException("rafik is upgrading lib module");
        $out_scr .= $html;
    }

    public static function getOutput($reset=false)
    {
        global $out_scr;        
        if(!$reset) return $out_scr;
        $return =  $out_scr;
        self::resetOutput();
        return $return;
    }


    public static function getDefaultOptions($Main_Page, $Main_Module="lib", $Main_Table="all")
    {
        if(!$Main_Module) $Main_Module="lib"; 
        $options = [];

        if(strpos($Main_Page,"_qedit.php")!==FALSE)
        {
            $options["qedit"]=true;
        }

        if(strpos($Main_Page,"login")!==FALSE)
        {
            $options["menu"]=false;
        }
        $curr_path = dirname(__FILE__);
        $special_options_file = "$curr_path/../../$Main_Module/extra/$Main_Table"."_options_for_$Main_Page";
        if(file_exists($special_options_file))
        {
            $special_options = include($special_options_file);
            $options = array_merge($options, $special_options);
        }
        else
        {
            // throw new AfwRuntimeException("$special_options_file not found");
        }

        return $options;
    }
    public static function echoMainPage($current_module, $Main_Page, $module_path, $options = [])
    {
        if(count($options)==0) $options = AfwMainPage::getDefaultOptions($Main_Page,$current_module);
        $curr_path = dirname(__FILE__);
        include("$curr_path/afw_main_start.php");
        die("rafik is upgrading MainPage librairy code=ADEF202511061552-03 ...");
        // die("echoMainPage($current_module, $Main_Page, $module_path) after afw_main_start lang=$lang");
        // die("echoMainPage 20241119  : after include($curr_path/afw_main_start.php) lang = ".$lang);
        echo self::renderMainPage($Main_Page, $module_path, $header_template, $menu_template, $body_template, $footer_template, $lang, $current_module, $options);
    }

    public static function echoDirectPage($current_module, $direct_page, $direct_page_path, $options = [])
    {
        if(count($options)==0) $options = AfwMainPage::getDefaultOptions($direct_page);
        $direct_page_name = str_replace(".php", "", $direct_page);
        $curr_path = dirname(__FILE__);
        include("$curr_path/afw_direct_start.php");
        // die("echoDirectPage 20241119  : after include($curr_path/afw_direct_start.php) header_template=$header_template, menu_template = $menu_template");
        echo self::renderDirectPage($direct_page, $direct_page_path, $header_template, $menu_template, $body_template, $footer_template, $lang, $current_module, $options);
    }

    private static function renderDirectPage($direct_page, $module_path, $header_template, $menu_template, 
                        $body_template, $footer_template, $lang, $current_module, $options = [])
    {
        // die("dgb::renderDirectPage($direct_page, $module_path, $header_template, $menu_template, $body_template, $footer_template, $current_module)");
        if(!$current_module) $current_module = "ums";
        $the_main_section_file = "$module_path/$direct_page";
        $html_output = (AfwHtmlPageConstructHelper::renderPage($lang, $header_template, 
                        $menu_template, 
                        $body_template, 
                        $footer_template, 
                        $_REQUEST, 
                        $the_main_section_file,
                        $need_ob=true,        
                        $options
                    ));

        return $html_output;  
        
    }


    private static function renderMainPage($Main_Page, $module_path, $header_template, $menu_template, $body_template, $footer_template, $lang, $current_module, $options = [])
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
        
        $html_output = (AfwHtmlPageConstructHelper::renderPage($lang, $header_template, 
                                        $menu_template, 
                                        $body_template, 
                                        $footer_template, 
                                        $_REQUEST, 
                                        $the_main_section_file,
                                        false,
                                        $options));
        
        
        if (!$html_output) {
            $html_output = ("<div class='afw_tech'><center>");
            if (AfwSession::config("MODE_DEVELOPMENT", false)) {
                //throw new AfwRuntimeException("<h1>no output from $Main_Page_path/$Main_Page</h1> ($module_dir_name == $file_dir_name)");
                $html_output .= "<h1>no output from page $the_main_section_file : Main_Page=$Main_Page<br> path=$Main_Page_path</h1> <br>(curmodulepath=$module_path)";
            }
            $html_output .= "<div style='padding:40px;text-align:center'><center><img src='../lib/images/page_not_found.png'><BR><BR><BR><BR><span class='error'>هذه الصفحة غير موجودة </span></center></div>";
            $html_output .= "</center></div>";
        }
        
        return $html_output;        
    }
}