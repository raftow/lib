<?
class AfwControllerHelper 
{

    

    public static function showControllerPage($controllerObj, $controllerName, $methodName, $request, $standard_header_and_footer=true, $options = [])
    {
        
        if(!$standard_header_and_footer)
        {
            // default controller templates can be used for all methods
            $default_header_template = AfwSession::config("controller-$controllerName-header-template", "modern"); 
            $default_menu_template = AfwSession::config("controller-$controllerName-menu-template", "modern");
            $default_body_template = AfwSession::config("controller-$controllerName-body-template", "modern");
            $default_footer_template = AfwSession::config("controller-$controllerName-footer-template", "modern");
            // template specific for current method or use the default
            $header_template = $controllerObj->headerTemplate($methodName, $default_header_template); 
            $menu_template =   $controllerObj->menuTemplate($methodName, $default_menu_template);
            $body_template =   $controllerObj->bodyTemplate($methodName, $default_body_template);
            $footer_template = $controllerObj->footerTemplate($methodName, $default_footer_template);
            $lang = $request["lang"];
            if(!$lang) $lang = "ar";
            $need_ob = true;
            /*
            if($methodName=="survey_request") die("will execute AfwHtmlPageConstructHelper::renderPage(lang=$lang, <br>
                                    header_template=$header_template, <br>
                                    menu_template=$menu_template, <br>
                                    body_template=$body_template, <br>
                                    footer_template=$footer_template, <br>
                                    request=".var_export($request,true).",<br>
                                    the_main_section_file='Controller::method',<br>
                                    need_ob=$need_ob,<br>
                                    options=".var_export($options,true).")");*/

            $html_output = AfwHtmlPageConstructHelper::renderPage($lang, 
                                    $header_template,
                                    $menu_template,
                                    $body_template,
                                    $footer_template,
                                    $request,
                                    "Controller::method",
                                    $need_ob,
                                    $options                                    
            );
            if (!$html_output) {
                $html_output = "<div class='afw_tech'><center>";
                if (AfwSession::config("MODE_DEVELOPMENT", false)) {
                    //throw new AfwRuntimeException("<h1>no output from $Main_Page_path/$Main_Page</h1> ($module_dir_name == $file_dir_name)");
                    $html_output .= "<h1>no output from controller $controllerName : methodName=$methodName</h1>";
                }
                $html_output .= "<div style='padding:40px;text-align:center'><center><img src='../lib/images/page_not_found.png'><BR><BR><BR><BR><span class='error'>هذه الصفحة غير موجودة </span></center></div>";
                $html_output .= "</center></div>";
            }

            return $html_output; 
            
        }
        else
        {
            // if($methodName=="survey_request") die("controllerObj($controllerName) will execute methodName=$methodName(request=".var_export($request,true).")");
            $controllerObj->$methodName($request);
        }
    }
}