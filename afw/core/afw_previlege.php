<?php

class AfwPrevilege extends AFWRoot
{
    /**
     * Loads all server modules info from client-company/modules_all.php file
     * @return array [found, mod_info, file_modules_all]
     */
    public static function loadAllServerModules()
    {
        $company = AfwSession::currentCompany();
        $file_modules_all = dirname(__FILE__) . "/../../../client-$company/modules_all.php";
        if (file_exists($file_modules_all)) {
            include($file_modules_all);
            $found = true;
        } else $found = false;

        return [$found, $mod_info, $file_modules_all];
    }

    /**
     * @param string $module_code
     * @return int
     */
    public static function moduleIdOfModuleCode($module_code)
    {
        /**
         * @var array $mod_info
         */
        list($found, $mod_info) = self::loadAllServerModules();
        if ($found) return $mod_info[$module_code]["id"];
        else return 0;
    }

    /**
     * @param string $module_id
     * @return string
     */
    public static function moduleCodeOfModuleId($module_id)
    {
        /**
         * @var array $mod_info
         */
        list($found, $mod_info, $file_modules_all) = self::loadAllServerModules();
        if ($found) return $mod_info['m' . $module_id]["code"];
        else return "";
    }

    /**
     * @param string $module_code
     * @return array
     */
    public static function loadModulePrevileges($module_code)
    {
        $previlege_sys_file =  dirname(__FILE__) . "/../../../$module_code/previleges.php";
        if (file_exists($previlege_sys_file)) {
            include($previlege_sys_file);
            return [true, $role_info, $tab_info, $tbf_info, $previlege_sys_file];
        }
        return [false, null, null, null, $previlege_sys_file];
    }


    /**
     * @param string $module_code
     * @param string $table_name
     * @return array
     */
    public static function loadModuleTablePrevileges($module_code, $table_name)
    {
        $fileName = "previleges_$module_code" . "_table_$table_name"  . ".php";
        $previlege_table_file =  dirname(__FILE__) . "/../../../cache/$module_code/previleges/table/$fileName";
        if (false and file_exists($previlege_table_file)) {
            include($previlege_table_file);
            return [true, $tab_info, $tbf_info, $previlege_table_file];
        } else {
            $previlege_table_file =  dirname(__FILE__) . "/../../../$module_code/previleges/table/$fileName";
            if (file_exists($previlege_table_file)) {
                include($previlege_table_file);
                return [true, $tab_info, $tbf_info, $previlege_table_file];
            }
        }

        list($found, $role_info, $tab_info, $tbf_info, $previlege_sys_file) = self::loadModulePrevileges($module_code);

        return [$found, $tab_info, $tbf_info, $previlege_sys_file . " (should be $previlege_table_file)"];
    }

    /**
     * @param string $module_code
     * @param string $bf_id
     * @return array
     */
    public static function loadModuleBfCache($module_code, $bf_id)
    {
        $fileName = "bf$bf_id"  . ".php";
        $cache_bf_file =  dirname(__FILE__) . "/../../../cache/$module_code/previleges/bf/$fileName";
        if (false and file_exists($cache_bf_file)) {
            $bf_info = include($cache_bf_file);
            return [true, $bf_info, $cache_bf_file];
        } else {
            $cache_bf_file =  dirname(__FILE__) . "/../../../$module_code/previleges/bf/$fileName";
            if (file_exists($cache_bf_file)) {
                $bf_info = include($cache_bf_file);
                return [true, $bf_info, $cache_bf_file];
            }
        }


        $bfItem = Bfunction::loadById($bf_id);
        $bf_info = null;
        if ($bfItem) list($bf_info,) = UmsManager::genereBfCacheFile($module_code, $bfItem, $genereFile = false, $generePhp = false);

        $found = (($bfItem and $bf_info) ? true : false);

        return [$found, $bf_info, "from database"];
    }

    /**
     * @param string $module_code
     * @param string $role_id
     * @return array
     */
    public static function loadModuleRolePrevileges($module_code, $role_id, $full_optimization = false)
    {
        $fileName = "previleges_$module_code" . "_role$role_id"  . ".php";
        $previlege_role_file =  dirname(__FILE__) . "/../../../cache/$module_code/previleges/role/$fileName";
        if (false and file_exists($previlege_role_file)) {
            include($previlege_role_file);
            return [true, $role_info, $previlege_role_file, "cache"];
        } else {
            $previlege_role_file =  dirname(__FILE__) . "/../../../$module_code/previleges/role/$fileName";
            if (file_exists($previlege_role_file)) {
                include($previlege_role_file);
                return [true, $role_info, $previlege_role_file, "permanent"];
            }
        }


        list($found, $role_info, $tab_info, $tbf_info, $previlege_sys_file) = self::loadModulePrevileges($module_code);
        if ($found or $full_optimization) return [$found, $role_info, $previlege_sys_file . " (should be $previlege_role_file)"];

        $roleItem = Arole::loadById($role_id);
        $role_info = null;
        if ($roleItem) list($role_info,) = UmsManager::genereRolePrevilegesFile($module_code, $roleItem, $genereFile = false);

        $found = (($roleItem and $role_info) ? true : false);

        return [$found, $role_info, "from database"];
    }
}
