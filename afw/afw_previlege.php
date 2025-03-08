<?php

    class AfwPrevilege extends AFWRoot 
    {
        public static function loadAllServerModules()
        {
            $company = AfwSession::config("main_company", "");
            $file_modules_all = dirname(__FILE__)."/../../client-$company/modules_all.php"; 
            if(file_exists($file_modules_all))
            {
                include($file_modules_all);
                $found = true;
            }
            else $found = false;

            return [$found, $mod_info, $file_modules_all];
        }

        public static function moduleIdOfModuleCode($module_code)
        {
            list($found, $mod_info) = self::loadAllServerModules();
            if($found) return $mod_info[$module_code]["id"];
            else return 0;
        }

        public static function moduleCodeOfModuleId($module_id)
        {
            list($found, $mod_info, $file_modules_all) = self::loadAllServerModules();
            if($found) return $mod_info['m'.$module_id]["code"];
            else return "";
        }

        public static function loadModulePrevileges($module_code)
        {
            $previlege_sys_file =  dirname(__FILE__)."/../../$module_code/previleges.php";
            if(file_exists($previlege_sys_file))
            {
                include($previlege_sys_file);
                return [true, $role_info, $tab_info, $tbf_info, $previlege_sys_file];        
            }
            return [false, null, null, null, $previlege_sys_file];
        }


        
    }