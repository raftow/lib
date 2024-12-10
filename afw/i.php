<?php

include_once(dirname(__FILE__)."/request_entry.php");

// throw new AfwRuntimeException("test eh03");

if(!$controllerName) $controllerName = AfwSession::config("default_controller_name", "");
if($controllerName)
{
        $controllerTemplate = AfwSession::config("$controllerName-controller-template", "modern");
        $module = "unknown-module";
        try{
                $ControllerClass = $controllerName."Controller";
                $controllerObj = new $ControllerClass ($request);
                $old_request = $request;
                
                
                if(!$methodName)                         
                {
                        $defaultMethod = $controllerObj->defaultMethod($request);                
                        $methodName = $defaultMethod;
                }        
                $prepareMethodName = "prepare".ucfirst($methodName);
                $initiateMethodName = "initiate".ucfirst($methodName);
                
                if($controllerTemplate=="standard")
                {
                        list($html_hedear, $page_header_file, $page_footer_file) = $controllerObj->myViewSettings($methodName);                        
                }
                else
                {
                        list($module, $is_direct, $page, $page_path, $options) = $controllerObj->myNewViewSettings($methodName);                        
                }

                if($controllerObj->alwaysNeedPrepare($request) or method_exists($controllerObj, $prepareMethodName)) $custom_scripts = $controllerObj->$prepareMethodName($request);
                if(method_exists($controllerObj, $initiateMethodName)) $request = $controllerObj->$initiateMethodName($request);
        }
        catch(Exception $e) 
        {
                // throw new AfwRuntimeException("test eh04");
                //$controllerObj = null;
                $controllerObjError = $e->getMessage()." ".$e->getTraceAsString();
                /*
                if(AfwSession::config("MODE_DEVELOPMENT", false)) 
                else $controllerObjError = "Disabled because of non dev mode";*/
        }

        if($controllerTemplate=="standard")
        {
                //die("custom_scripts=".var_export($custom_scripts,true));
                //die("rafik see : will include once ".dirname(__FILE__)."/../../$page_header_file with \$config =".var_export($config,true));
                include_once (dirname(__FILE__)."/../../$page_header_file");
                if(!$controllerObj)
                {
                        AfwRunHelper::safeDie("failed to instanciate controller $controllerName : $controllerObjError");
                }
                elseif($controllerObjError)
                {
                        AfwRunHelper::safeDie($controllerName."Controller => failed : $controllerObjError");
                }
                elseif(!method_exists($controllerObj,$methodName))
                {
                        AfwRunHelper::safeDie(get_class($controllerObj)." does'nt contain method ".$methodName);
                }
                if($request !== null) $controllerObj->$methodName($request);
                else $controllerObj->$defaultMethod($old_request);  // if method can't be initialized we return back to default method

                include_once (dirname(__FILE__)."/../../$page_footer_file");
        }
        else
        {
                $file_dir_name = dirname(__FILE__);                
                require("$file_dir_name/afw_main_page.php"); 
                if($is_direct)
                {
                        AfwMainPage::echoDirectPage($module, $page, $page_path, $header_template = "direct", $menu_template = "direct", $body_template = "direct", $footer_template = "direct", $options);
                }
                else
                {
                        AfwMainPage::echoMainPage($module, $page, $page_path, $options);
                }
        }
        
        

        

        

}
else die("controller name not defined, please define it or define default controller");

?>