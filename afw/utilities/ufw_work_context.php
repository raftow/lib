<?php


class UfwWorkContext
{

    public static function setWorkContext($lang)
    {
        AfwSession::setSessionVar("work_context", $lang);        
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
}