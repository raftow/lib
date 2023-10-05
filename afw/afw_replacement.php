<?php
class AfwReplacement {

    /**
     * 
     * $module can be string as module code or numeric as module Id
     * 
     */
    public static function replace($string, $module, $lang="ar")    
    {
        if(!$lang) $lang="ar";
        $old_module = $module;
        $module = UmsManager::decodeModuleCodeOrIdToModuleCode($module);

        if($module)
        {
            $replace_file = dirname(__FILE__)."/../../external/translate/$module/replaces_$lang.php";
            $arr_str = include($replace_file);
            if($arr_str)
            {
                foreach($arr_str as $strFind => $strReplace)
                {
                    $string = str_replace($strFind, $strReplace, $string);
                }
                
            }
            //else die("check your replacement file : $replace_file ");
        }
        //else die("$module = UmsManager::decodeModuleCodeOrIdToModuleCode($old_module)");

        return $string;
    }


        

}