<?php

include_once(dirname(__FILE__)."/request_entry.php");

// throw new RuntimeException("test eh03");

if(!$controllerName) $controllerName = AfwSession::config("default_controller_name", "");
if($controllerName)
{
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
                

                list($html_hedear, $page_header_file, $page_footer_file) = $controllerObj->myViewSettings($methodName);
                if($controllerObj->alwaysNeedPrepare($request) or method_exists($controllerObj, $prepareMethodName)) $custom_scripts = $controllerObj->$prepareMethodName($request);
                if(method_exists($controllerObj, $initiateMethodName)) $request = $controllerObj->$initiateMethodName($request);
        }
        catch(Exception $e) 
        {
                // throw new RuntimeException("test eh04");
                //$controllerObj = null;
                $controllerObjError = $e->getMessage()." ".$e->getTraceAsString();
                /*
                if(AfwSession::config("MODE_DEVELOPMENT", false)) 
                else $controllerObjError = "Disabled because of non dev mode";*/
        }
        

        //die("custom_scripts=".var_export($custom_scripts,true));
        //die("rafik see : will include once ".dirname(__FILE__)."/../../$page_header_file with \$config =".var_export($config,true));
        include_once (dirname(__FILE__)."/../../$page_header_file");
        if(!$controllerObj)
        {
                AFWRoot::safeDie("failed to instanciate controller $controllerName : $controllerObjError");
        }
        elseif($controllerObjError)
        {
                AFWRoot::safeDie($controllerName."Controller => failed : $controllerObjError");
        }
        elseif(!method_exists($controllerObj,$methodName))
        {
                AFWRoot::safeDie(get_class($controllerObj)." does'nt contain method ".$methodName);
        }
        if($request !== null) $controllerObj->$methodName($request);
        else $controllerObj->$defaultMethod($old_request);  // if method can't be initialized we return back to default method

        include_once (dirname(__FILE__)."/../../$page_footer_file");

        

}
else die("controller name not defined, please define it or define default controller");

?>