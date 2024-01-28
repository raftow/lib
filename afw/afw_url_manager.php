<?php

// old require of afw_root

class AfwUrlManager extends AFWRoot
{

    private static function encodeUrlParam($param)
    {
            if($param == "Main_Page") return "mp";
            if($param == "Main_Page_Module") return "pm";
            if($param=="currmod") return "cm";
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
            $afw_action = rtrim($main_page, '.php');
            $afw_action = substr($afw_action, 9);
            if ($afw_action == 'crossed') {
                $afw_action = 'qedit';
            } // same BF but different interface

            $direct_access = 'N';
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
                        $file_lib_afw_dir_name = dirname(__FILE__); 
                        if(file_exists("$file_lib_afw_dir_name/../../external/chsys/module_$module_code.php"))
                        {
                            include("$file_lib_afw_dir_name/../../external/chsys/module_$module_code.php");
                            $bf_id = $tbf_info[$object_table][$afw_action]["id"];
                        }
                        else AfwSession::pushWarning("System need cache optimisation by creating module_$module_code file <!-- file not found $file_lib_afw_dir_name/../../external/chsys/module_$module_code.php -->");    
                        if(!$bf_id)
                        {
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
                    AfwRunHelper::safeDie(
                        'can find BF',
                        "can find BF because triplet(module_code=$module_code,object_table=$object_table, afw_action=$afw_action) is incomplete"
                    );
                }
            } else {
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

    public static function currentURIModule()
    {
        $uri_items = explode('/', $_SERVER['REQUEST_URI']);
        if ($uri_items[0]) {
            $uri_module = $uri_items[0];
        } else {
            $uri_module = $uri_items[1];
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
}
