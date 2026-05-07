<?php


class UfwWorkContext
{

    /**
     * @param string $work_context
     */
    public static function setWorkContext($work_context)
    {
        AfwSession::setSessionVar("work_context", $work_context);        
    }

    /**
     * @param string $sub_context
     */
    public static function setSubContext($sub_context)
    {
        AfwSession::setSessionVar("sub_context", $sub_context);        
    }

    public static function getWorkContext()
    {
        $work_context = AfwSession::getSessionVar("work_context");
        if (!$work_context) 
        {
            global $current_context;
            $work_context = $current_context;
            if (!$work_context) $work_context = 'global-context';
        }    
        
        return $work_context;
    }

    public static function getAllContextTranslated($lang="ar") {
        $work_context = self::getWorkContext();
        $sub_context = AfwSession::getSessionVar("sub_context");
        $translated_work_context = AfwLanguageHelper::translateKeyword($work_context, $lang);
        if ($sub_context) {
            $translated_sub_context = AfwLanguageHelper::translateKeyword($sub_context, $lang);
            return "$translated_work_context > $translated_sub_context";
        }
        else {
            return $translated_work_context;
        }
    }
}