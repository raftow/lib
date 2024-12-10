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
            $header_template = AfwSession::config("controller-$controllerName-method-$methodName-header-template", $default_header_template); 
            $menu_template = AfwSession::config("controller-$controllerName-method-$methodName-menu-template", $default_menu_template);
            $body_template = AfwSession::config("controller-$controllerName-method-$methodName-body-template", $default_body_template);
            $footer_template = AfwSession::config("controller-$controllerName-method-$methodName-footer-template", $default_footer_template);
            $lang = $request["lang"];
            if(!$lang) $lang = "ar";
            $out_scr =  AfwHtmlPageConstructHelper::renderPage($lang, 
                                    $header_template,
                                    $menu_template,
                                    $body_template,
                                    $footer_template,
                                    $request,
                                    "Controller::method",
                                    $need_ob = true,
                                    $options                                    
            );
            if (!$out_scr) {
                $out_scr = "<div class='afw_tech'><center>";
                if (AfwSession::config("MODE_DEVELOPMENT", false)) {
                    //throw new AfwRuntimeException("<h1>no output from $Main_Page_path/$Main_Page</h1> ($module_dir_name == $file_dir_name)");
                    $out_scr .= "<h1>no output from controller $controllerName : methodName=$methodName</h1>";
                }
                $out_scr .= "<div style='padding:40px;text-align:center'><center><img src='../lib/images/page_not_found.png'><BR><BR><BR><BR><span class='error'>هذه الصفحة غير موجودة </span></center></div>";
                $out_scr .= "</center></div>";
            }

            return $out_scr; 
            
        }
        else
        {
            $controllerObj->$methodName($request);
        }
    }
}