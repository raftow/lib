<?php
const CONST_URI_ORDER = 0;
// old require of afw_root

class AfwUrlManager extends AFWRoot
{

    private static function encodeUrlParam($param)
    {
            if($param == "Main_Page") return "mp";
            if($param == "Main_Page_Module") return "pm";
            if($param=="currmod") return "cm";
            if($param=="currstep") return "cs";
            if($param=="id_origin") return "io";
            if($param=="class_origin") return "co";
            if($param=="module_origin") return "mo";
            if($param=="step_origin") return "so";
            if($param=="newo") return "no";
            if($param=="limit") return "lm";
            if($param=="fixmtit") return "xt";
            if($param=="fixmdisable") return "xd";
            if($param=="fixm") return "xm";
    


            if($param == "afw_mode_minibox.php") return "mb";
            if($param == "afw_mode_edit.php") return "ed";
            if($param == "afw_mode_qedit.php") return "qe";
            if($param == "afw_mode_display.php") return "ds";
            if($param == "afw_mode_stats.php") return "st";
            if($param == "afw_mode_audit.php") return "au";
    
            if($param == "afw_mode_confirm.php") return "cn";
            if($param == "afw_mode_crossed.php") return "ce";
            if($param == "afw_mode_ddb.php") return "db";
            if($param == "afw_mode_qsearch.php") return "qs";
            if($param == "afw_mode_search.php") return "sr";        



    
            return $param;
    }


    public static function encodeMainUrl($url)
    {
        list($script_name, $all_params) = explode('?', $url);

        // die("rafik CDF 123456 = $script_name, $all_params");

        if(($script_name != "main.php") and ($script_name != "m.php"))
        {
            return $url;
        }
        else $script_name = "m.php";

        $param_arr = explode('&', $all_params);
        $param_encoded_arr = [];
        foreach ($param_arr as $param_definition) {
            $arr_param_def = explode('=', $param_definition);
            $param_key = $arr_param_def[0];
            $param_values = $arr_param_def;
            unset($param_values[0]);
            $param_value = implode("=",$param_values);
            $param_key_encoded = self::encodeUrlParam($param_key);
            $param_value_encoded = self::encodeUrlParam($param_value);
            $param_encoded_arr[] = $param_key_encoded . "=". $param_value_encoded;
        }

        return $script_name."?".implode('&', $param_encoded_arr);
    }

    // $params_is_spec = true means if params of url change the BF change also it is not anymore the same
    public static function decomposeUrl(
        $module_caller,
        $url,
        $create_not_found_bf = true,
        $create_if_not_found_with_name = null,
        $params_is_spec = false
    ) {
        if (!$create_not_found_bf) {
            $create_if_not_found_with_name = null;
        }
        $params = [];
        $main_page = '';
        $afw_action = '';
        $module_code = '';
        $object_class = '';
        $object_table = '';
        $bf_id = null;
        list($script_name, $all_params) = explode('?', $url);

        $param_arr = explode('&', $all_params);
        foreach ($param_arr as $param_definition) {
            list($param_key, $param_value) = explode('=', $param_definition);
            if ($param_key == 'Main_Page') {
                $main_page = $param_value;
            }
            if ($param_key == 'act') {
                $afw_action = $param_value;
            } elseif ($param_key == 'cl') {
                $object_class = $param_value;
            } elseif ($param_key == 'currmod') {
                $module_code = $param_value;
            } else {
                $params[$param_key] = $param_value;
            }
        }

        if (!$module_code) {
            $module_code = $module_caller;
        }

        if ($script_name == 'main.php') {
            // file_specification
            if(AfwStringHelper::stringStartsWith($main_page, "afw_mode_"))
            {
                $afw_action = rtrim($main_page, '.php');
                $afw_action = substr($afw_action, 9);
                if ($afw_action == 'crossed') {
                    $afw_action = 'qedit';
                } // same BF but different interface

                $direct_access = 'N';
            }
            else
            {
                $direct_access = 'Y';    
            }
        } else {
            $direct_access = 'Y';
        }

        if ($object_class) {
            $object_table = AfwStringHelper::classToTable($object_class);
        }

        if (true) {
            if ($direct_access == 'N') {
                if ($module_code and $object_table and $afw_action) 
                {
                        list($found, $role_info, $tab_info, $tbf_info, $module_sys_file) = AfwPrevilege::loadModulePrevileges($module_code);
                        if($found)
                        {
                            $bf_id = $tbf_info[$object_table][$afw_action]["id"];
                            if(!$bf_id) AfwSession::pushWarning("The previlege file of module $module_code does not contain a definition for BF[$object_table][$afw_action]");
                        }
                        else AfwSession::pushWarning("System need cache optimisation by creating previleges.php file for module $module_code <!-- file not found $module_sys_file -->");    

                        
                        if((!$bf_id) and AfwSession::config("MODE_DEVELOPMENT",false))
                        {                            
                            AfwSession::pushInformation("BF [$object_table][$afw_action] not found in module previleges cache file<br> 
                                                        You can resolve this by doing :
                                                           <b>reverse table $object_table.$module_code</b> and then do :<br> 
                                                           <b>generate-chsys module $module_code</b> <br> 
                                                           and <u><b>merge</b></u> its content in ../$module_code/previleges.php file");
                            AfwAutoLoader::addModule("p"."ag");
                            $bf_id = UmsManager::getBunctionIdForOperationOnTable(
                                        $module_code,
                                        $object_table,
                                        $afw_action,
                                        $create_if_not_found_with_name
                                    );
                        }
                    
                } 
                else 
                {
                    throw new AfwRuntimeException("can't find BF because triplet(module_code=$module_code,object_table=$object_table, afw_action=$afw_action) is incomplete from url $url ");
                }

            } 
            else 
            {
                if ($params_is_spec) {
                    $bf_spec = $all_params;
                } else {
                    $bf_spec = '';
                }
                $bf_id = UmsManager::getBunctionForScript(
                    $module_caller,
                    $script_name,
                    $bf_spec,
                    $create_if_not_found_with_name
                );
            }
        }

        return [$bf_id, $params];
    }


    public static function getUriContext()
    {
        $uri_items = explode("/", $_SERVER['REQUEST_URI']);
        $url = $uri_items[count($uri_items)-1];
        $return = strtolower(self::encodeMainUrl($url));
        $return = str_replace(".php","",$return);
        $return = str_replace("&popup=","+p",$return);
        $return = str_replace("?","+",$return);
        $return = str_replace("&","+",$return);


        return $return;
    }
    
    

    public static function currentURIModule()
    {        
        $uri_items = explode('/', $_SERVER['REQUEST_URI']);
        if ($uri_items[CONST_URI_ORDER]) {
            $uri_module = $uri_items[CONST_URI_ORDER];
        } else {
            $uri_module = $uri_items[CONST_URI_ORDER+1];
        }

        return $uri_module;
    }

    public static function currentWebModule()
    {
        global $_SERVER;

        $phpself = trim($_SERVER['PHP_SELF'], '/');
        $phpself_arr = explode('/', $phpself);
        return strtolower($phpself_arr[0]);
    }

    private static function defaultCurrentPageCode($theModule, $theClass, $uri_items, $original_serv_uri, $serv_uri, $ignored_vars)
    {
        $currentPageCodeArr = [];
        $acceptedCodeArr = [];
        $rejectedCodeArr = [];

        $previous_item = "";
        foreach($uri_items as $uri_item) 
        {
            if(!$theModule and ($previous_item=="currmod")) 
            {
                $theModule = $uri_item; 
            }

            if(!$theClass and ($previous_item=="cl")) 
            {
                $theClass = $uri_item;
            }

            if(!AfwStringHelper::stringContain($uri_item,'_'))
            {
                $uri_item = AfwStringHelper::classToTable($uri_item); 
            }
            $uri_item = strtolower($uri_item);
            if(AfwStringHelper::stringStartsWith($uri_item,'afw_mode_'))
            {
                $uri_item = str_replace('afw_mode_','',$uri_item);
            }

            if(($uri_item=="main")) $uri_item = "m";
            if(($uri_item=="main_page")) $uri_item = "mp";
            if(($uri_item=="ed") and ($previous_item=="mp")) $uri_item = "edit";
            
            if(
                (strlen($uri_item)>=3) 
                and (strlen($uri_item)<=20) 
                and (!is_numeric($uri_item)) 
                and (!AfwStringHelper::stringStartsWith($uri_item,'sel_'))
                and ($uri_item != "currmod")
                and ($uri_item != "currstep")
                and ($uri_item != "php")
                and ($uri_item != "submit")
                and ($uri_item != "newo")
                and ($uri_item != "limit")
                and ($uri_item != "main_page")
                and (!AfwStringHelper::is_arabic($uri_item,0.4))
            )
            {
                $currentPageCodeArr[] = $uri_item;
                $acceptedCodeArr[] = $uri_item." not arabic, not numeric, not mp, not limit, not newo, not submit, not php, not currstep, not currmod, not sel_, not too short or too long,";
            }
            else
            {
                $rejectedCodeArr[] = $uri_item." arabic or numeric or mp or limit or newo or submit or php or currstep or currmod or sel_ or too short(<3) or too long (>20)";
            }
            $previous_item = $uri_item;
        }   

        $log_explain = "with uri items but explain disabled";
        $log_explain = implode("\n<br>",$acceptedCodeArr)."\n<br>".implode("\n<br>",$rejectedCodeArr)."\n ignored_vars=$ignored_vars \n_POST = ".var_export($_POST,true)." \nserv_uri=$serv_uri \noriginal_serv_uri=$original_serv_uri\nuri_items = ".var_export($uri_items,true);

        $pageCode = implode("_",$currentPageCodeArr);
        return [$uri_items, $pageCode, $log_explain];
    }


    public static function analyseCurrentUrl()
    {
        
        $original_serv_uri = $serv_uri = trim(strtolower($_SERVER['REQUEST_URI']));
        $serv_uri = trim($_SERVER['REQUEST_URI']);
        $serv_uri = str_replace('.php','',$serv_uri);
        $serv_uri = str_replace('?','/', $serv_uri);
        $serv_uri = str_replace('\\','/', $serv_uri);
        $serv_uri = str_replace('=','/', $serv_uri);
        $serv_uri = str_replace('.','/', $serv_uri);
        $serv_uri = str_replace('&','/', $serv_uri);
        $uri_items = explode('/', $serv_uri);
        unset($uri_items[0]);
        unset($uri_items[1]);
        if($uri_items[2]=="main") 
        {
            unset($uri_items[2]);
        }
        $uriModule = self::currentURIModule();
        $post_i = 0;
        $POST_MAX = 3;
        $ignored_vars = "";
        $theModule = "";
        $theClass = "";
        foreach($_REQUEST as $var => $varval)
        {
            $var = trim(strtolower($var));
            $varval = str_replace('afw_mode_','',$varval);
            $varval = str_replace('afw_handle_default_','',$varval);
            $varval = str_replace('.php','',$varval);
            if($var=="class_obj") $var = "cl";
            if($var=="cl") 
            {
                $theClass = $varval;
                $varval = substr($varval,0,20);
            }
            if($var=="currmod") $theModule = $varval; 
            if($var=="cm") $theModule = $varval; 
            if(($var=="my_module") and !$theModule) $theModule = $varval; 
            if(
                ((strlen($var)>=3) or (strlen($varval)>=3))
                and (!is_numeric($var)) 
                and (is_string($varval))
                and (!AfwStringHelper::stringStartsWith($varval,'['))
                and (!AfwStringHelper::stringStartsWith($var,'sel_'))                
                and (!AfwStringHelper::stringEndsWith($var,'go'))
                and ($var != "main")
                and ($var != "curstep")
                and ($varval != "main")
                and ($var != "my_module")
                and ($varval != "afw")
                and ($var != "pbmon")
                and ($var != "file_obj")
                and ($var != "class_parent")
                and ($var != "class_obj")
                and ($var != "id_obj")
                and ($var != "php")
                and ($var != "submit")
                and ($var != "newo")
                and ($var != "popup")
                and ($var != "limit")
                and (!AfwStringHelper::is_arabic($var,0.4))
            )
            {
                if(is_string($varval) and (strlen($varval)>= 3) and (strlen($varval)<= 20) and ($post_i < $POST_MAX))
                {
                    if(($var != "main_page") and (is_string($var) and (strlen($var)>= 3) and (strlen($var)<= 20)))  $uri_items[] = $var;
                    $uri_items[] = $varval;            
                    $post_i++;
                }
                else $ignored_vars .= "  ($var/$varval/$post_i)  ,";
            }
            else $ignored_vars .= "  [$var/$varval]  ,";
        }

        if(!$theModule) 
        {
            $theModule = $uriModule; 
        }


        $previous_item = "";
        foreach($uri_items as $uri_item) 
        {
            if(!$theModule and ($previous_item=="currmod")) 
            {
                $theModule = $uri_item; 
            }

            if(!$theClass and ($previous_item=="cl")) 
            {
                $theClass = $uri_item;
            }

            $previous_item = $uri_item;
        } 
        
        return [$theModule, $theClass, $uri_items, $original_serv_uri, $serv_uri, $ignored_vars];
        // die("curr page code => ignored_vars = $ignored_vars uri_items = ".var_export($uri_items,true));
    }
        
    public static function currentPageCode()
    {
        list($theModule, $theClass, $uri_items, $original_serv_uri, $serv_uri, $ignored_vars) = self::analyseCurrentUrl();
        
        $pageCode = null;
        $log_explain_advanced = "";
        if($theClass and $theModule)
        {
            $theStructureClass = ucfirst($theModule).$theClass."AfwStructure";
            if(method_exists($theStructureClass, "pageCode"))
            {
                $pageCode = $theStructureClass::pageCode($uri_items);
                $log_explain = "from $theStructureClass::pageCode(...) method";
            }
            else $log_explain_advanced = "$theStructureClass::pageCode(...) not found";
        }
        else $log_explain_advanced = "theClass=$theClass and theModule=$theModule";

        if(!$pageCode)
        {
            list($uri_items, $pageCode, $log_explain) = self::defaultCurrentPageCode($theModule, $theClass, $uri_items, $original_serv_uri, $serv_uri, $ignored_vars);
                        
        }
        

        return [$pageCode, $log_explain." >> ".$log_explain_advanced];
    }


    
}
