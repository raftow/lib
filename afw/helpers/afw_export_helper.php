<?php

class AfwExportHelper
{

    public static function afwExport($var, $recursive=true)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        $result = [];
        if(is_object($var) and ($var instanceof AFWObject))
        {
            $result = $var->getAllfieldValues();
        }
        elseif(is_array($var))
        {
            foreach($var as $ky => $varItem)
            {
                if($recursive) $result[$ky] = self::afwExport($varItem);
                elseif(is_object($var) and ($var instanceof AFWObject)) $result[$ky] = $varItem->getDisplay($lang);
                else $result = var_export($var, true);
            }
        }
        else
        {
            $result = var_export($var, true);
        }

        return $result;
    }

}