<?php

include_once(dirname(__FILE__)."/request_entry.php");

// throw new AfwRuntimeException("test eh03");
$devMode = AfwSession::config('MODE_DEVELOPMENT', false);
if(!$controllerName) $controllerName = AfwSession::config("default_controller_name", "");
if($controllerName)
{
        $controllerType = AfwSession::config("$controllerName-controller-type", "modern");
        //$module = "unknown-module";
        try{
                $ControllerClass = $controllerName."Controller";
                $controllerObj = new $ControllerClass ($request);
                $old_request = $request;
                
                $defaultMethod = $controllerObj->defaultMethod($request);                
                if(!$methodName)                         
                {                        
                        $methodName = $defaultMethod;
                }        
                $prepareMethodName = "prepare".ucfirst($methodName);
                $initiateMethodName = "initiate".ucfirst($methodName);
                
                if($controllerType=="standard")
                {
                        list($html_hedear, $page_header_file, $page_footer_file) = $controllerObj->myViewSettings($methodName);                        
                }
                /*
                else
                {
                        list($module, $is_direct, $page, $page_path, $options) = $controllerObj->myNewViewSettings($methodName);                        
                }*/

                if($controllerObj->alwaysNeedPrepare($request) or method_exists($controllerObj, $prepareMethodName)) $custom_scripts = $controllerObj->$prepareMethodName($request);
                if(method_exists($controllerObj, $initiateMethodName)) $request = $controllerObj->$initiateMethodName($request);
                // if method can't be initialized
                if(!$request) 
                {
                        // if dev mode
                        if($devMode)
                        {
                                throw new AfwRuntimeException($controllerName."Controller->$initiateMethodName doen't return a correct request array");
                        }
                        else
                        {
                                // we return back to default method
                                $methodName = $defaultMethod;  
                                $request = $old_request;
                        }
                        
                }
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

        if(!$controllerObj)
        {
                throw new AfwRuntimeException("failed to instanciate controller $controllerName : $controllerObjError");
        }
        elseif($controllerObjError)
        {
                throw new AfwRuntimeException("Controller $controllerName (".get_class($controllerObj).") prepare/initiate failed : $controllerObjError");
        }
        elseif(!method_exists($controllerObj,$methodName))
        {
                throw new AfwRuntimeException("Controller $controllerName (".get_class($controllerObj).") does'nt contain method (".$methodName.")");
        }

        $request["controllerObj"] = $controllerObj;
        $request["methodName"] = $methodName;
        
        $options = $controllerObj->prepareOptions($methodName);
        $options["other-js-arr"] = [];
        $options["other-css-arr"] = [];
        foreach ($custom_scripts as $custom_script) {
                if ($custom_script["type"] == "css") {
                        $options["other-css-arr"][] = $custom_script["path"];
                } elseif ($custom_script["type"] == "js") {
                        $options["other-js-arr"][] = $custom_script["path"];
                } else throw new AfwRuntimeException("Custom script ".$custom_script["path"] . " has unknown type, review your controller prepare method : $prepareMethodName ");
        }
        $options["controllerObj"] = $controllerObj;
        if($controllerType=="standard")
        {
                //die("custom_scripts=".var_export($custom_scripts,true));
                //die("rafik see : will include once ".dirname(__FILE__)."/../../$page_header_file with \$config =".var_export($config,true));
                include_once (dirname(__FILE__)."/../../$page_header_file");
                echo AfwControllerHelper::showControllerPage($controllerObj, $controllerName, $methodName, $request, true, $options);
                include_once (dirname(__FILE__)."/../../$page_footer_file");
        }
        else
        {
                echo AfwControllerHelper::showControllerPage($controllerObj, $controllerName, $methodName, $request, false, $options);
        }
        
        

        

        

}
else die("controller name not defined, please define it or define default controller");

?>