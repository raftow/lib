<?php
class AfwReplacement {

    /**
     * 
     * $module can be string as module code or numeric as module Id
     * 
     */
    public static function trans_replace($string, $module_code, $lang)    
    {
        if(!$lang) $lang="ar";
        // $old_module = $module;
        // $module = UmsManager::decodeModuleCodeOrIdToModuleCode($module);
        $company = AfwSession::config("main_company", "");
        if(!$company)
        {
            throw new AfwRuntimeException("company code required to do AfwReplacement::trans_replace (see main_company param in system config file)");
        }

        if(!$module_code)
        {
            throw new AfwRuntimeException("module code required to do AfwReplacement::trans_replace");
        }

        $replace_file = dirname(__FILE__)."/../../client-$company/translate/$module_code/replaces_$lang.php";
        $arr_str = include($replace_file);
        if($arr_str)
        {
            foreach($arr_str as $strFind => $strReplace)
            {
                $string = str_replace($strFind, $strReplace, $string);
            }
            
        }
        //else die("check your replacement file : $replace_file ");
        
        

        return $string;
    }


        

}