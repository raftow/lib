<?php
if(!class_exists('AfwAutoLoader'))
{
        $autol_file_dir_name = dirname(__FILE__); 
        require_once("$autol_file_dir_name/afw_root.php"); 
        require_once("$autol_file_dir_name/helpers/afw_string_helper.php"); 
        
        class AfwAutoLoader extends AFWRoot 
        {
                private static $modules_autoload_by_prio = ["/","/../ums/", "/../hrm/", ];            // "/../crm/","/../p-ag/", "/../bau/",

                public static function addModule($module)
                {
                        array_push(self::$modules_autoload_by_prio, "/../$module/");
                }

                public static function addMainModule($module)
                {
                        $tmp_modules_arr = self::$modules_autoload_by_prio;
                        self::$modules_autoload_by_prio = array();
                        self::$modules_autoload_by_prio[] = "/../$module/";
                        self::$modules_autoload_by_prio = array_unique(array_merge(self::$modules_autoload_by_prio, $tmp_modules_arr));
                }

                public static function getClassPath($class)
                {
                        $autol_file_dir_name = dirname(__FILE__);
                        $failed_loadings_arr = array();
                        if (!class_exists($class, FALSE))
                        {
                                if($class == "AFWObject")
                                {
                                        $file_path_to_load = $autol_file_dir_name."/afw.php";

                                        require_once($file_path_to_load);
                                        if(class_exists($class, FALSE)) return array(true, $file_path_to_load, $failed_loadings_arr);
                                        else return array(true, "failed to find $class into $file_path_to_load", $failed_loadings_arr);

                                }

                                if(AfwStringHelper::stringStartsWith ($class, "AFW"))
                                {
                                        $file_name = "afw_".AfwStringHelper::classToFile(substr($class,3));  
                                }
                                else $file_name = AfwStringHelper::classToFile($class);

                                if(AfwStringHelper::stringStartsWith ($file_name, "afw_"))
                                {
                                        if(AfwStringHelper::stringEndsWith ($file_name, "_helper.php"))
                                        {
                                                $file_path_to_load = $autol_file_dir_name."/helpers/$file_name";
                                        }
                                        else
                                        {
                                                $file_path_to_load = $autol_file_dir_name."/$file_name";
                                        }

                                        require_once($file_path_to_load);
                                        if(class_exists($class, FALSE)) return array(true, $file_path_to_load, $failed_loadings_arr);
                                        else return array(true, "failed to find $class into $file_path_to_load", $failed_loadings_arr);
                                }

                                if(AfwStringHelper::stringEndsWith ($class, "Translator"))
                                {
                                        $arrParts = explode("_", $file_name); 
                                        $moduleCurr = $arrParts[0];
                                        $cnt = count($arrParts);
                                        // echo ("arrParts=".var_export($arrParts,true));
                                        unset($arrParts[0]); // remove module
                                        // echo ("arrParts(2)=".var_export($arrParts,true));
                                        unset($arrParts[$cnt-1]); // remove translator.php word
                                        // echo ("arrParts(3)=".var_export($arrParts,true));
                                        $cleaned_file_name = implode("_", $arrParts); 
                                        // die("$cleaned_file_name = implode('_', arrParts) = $cleaned_file_name");
                                        $file_path_to_load = $autol_file_dir_name."/../../".$moduleCurr."/tr"."/trad_ar_".$cleaned_file_name.".php";
                                        if(!file_exists($file_path_to_load))
                                        {
                                                throw new AfwRuntimeException("when loading $class from $file_path_to_load it failed because file does not exists");
                                        }
                                        require_once($file_path_to_load);
                                        if(class_exists($class, FALSE)) return array(true, $file_path_to_load, $failed_loadings_arr);
                                        else return array(true, "failed to find $class into $file_path_to_load", $failed_loadings_arr);
                                }

                                if(AfwStringHelper::stringEndsWith ($class, "AfwStructure"))
                                {
                                        $arrParts = explode("_", $file_name); 
                                        $moduleCurr = $arrParts[0];
                                        unset($arrParts[0]);
                                        $cleaned_file_name = implode("_", $arrParts); 

                                        $file_path_to_load = $autol_file_dir_name."/../../".$moduleCurr."/struct"."/".$cleaned_file_name;
                                        if(!file_exists($file_path_to_load))
                                        {
                                                throw new AfwRuntimeException("when loading $class from $file_path_to_load it failed because file does not exists");
                                        }
                                        require_once($file_path_to_load);
                                        if(class_exists($class, FALSE)) return array(true, $file_path_to_load, $failed_loadings_arr);
                                        else return array(true, "failed to find $class into $file_path_to_load", $failed_loadings_arr);

                                }
                                
                                
                                $modules_to_fetch = array();
                                if(AfwStringHelper::stringEndsWith ($class, "Controller"))
                                {
                                        foreach (self::$modules_autoload_by_prio as $module_relative_path)
                                        {
                                                $modules_to_fetch[] = $module_relative_path."controllers/"; 
                                        }
                                }
                                elseif(AfwStringHelper::stringEndsWith ($class, "Service"))
                                {
                                        foreach (self::$modules_autoload_by_prio as $module_relative_path)
                                        {
                                                $modules_to_fetch[] = $module_relative_path."services/"; 
                                        }
                                }
                                elseif(AfwStringHelper::stringEndsWith ($class, "Helper"))
                                {
                                        foreach (self::$modules_autoload_by_prio as $module_relative_path)
                                        {
                                                $modules_to_fetch[] = $module_relative_path."helpers/"; 
                                        }
                                }
                                else
                                {
                                        foreach (self::$modules_autoload_by_prio as $module_relative_path)
                                        {
                                                $modules_to_fetch[] = $module_relative_path."models/"; 
                                        }
                                }

                                
                                
                                $modules_to_fetch = array_merge($modules_to_fetch, self::$modules_autoload_by_prio);
                                

                                foreach ($modules_to_fetch as $module_relative_path)
                                {
                                        $file_path_to_load = $autol_file_dir_name."/..".$module_relative_path.$file_name;
                                        
                                        if (!file_exists($file_path_to_load))
                                        {
                                                $failed_loadings_arr[] = "failed to find ".$file_path_to_load;
                                                continue;
                                        }

                                        require_once($file_path_to_load);
                                        if(class_exists($class, FALSE)) return array(true, $file_path_to_load, $failed_loadings_arr);
                                        else 
                                        {
                                                $failed_loadings_arr[] = $file_path_to_load." exists, but doesn't declare class ".$class;
                                                continue;
                                        }
                                }

                                return array(false, "", $failed_loadings_arr);
                        }
                        else return array(true,"already exists", ["no need"]);
                }

                public static function classAutoLoader($class)
                {

                        if (!class_exists($class, FALSE))
                        {
                                list($found, $path, $failed_loadings_log_arr) = self::getClassPath($class);

                                if (!$found)
                                {
                                        throw new AfwRuntimeException('Unable to locate the class you have specified ['.$path.']: failed_loadings_arr = '.var_export($failed_loadings_log_arr,true));
                                        // rafik : I commented this below because not clear for me what other possible autoloaders ?
                                        // should not throw exception but give the hand to other possible autoloaders ...
                                        // return FALSE;
                                }
                        }
                        /*
                        elseif (!is_subclass_of($class, 'AFWRoot'))
                        {
                                // throw new AfwRuntimeException("Class ".$class." already exists and doesn't extend AFWRoot");
                                // should not throw exception but give the hand to other possible autoloaders ...
                                return FALSE;
                        }

                        return;*/
                }

                public static function init()
                {
                        spl_autoload_register('AfwAutoLoader::classAutoLoader');                        
                }
        }
        
        AfwAutoLoader::init();
        
}

?>