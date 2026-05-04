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