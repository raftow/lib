<?php
/* rafik 17/11/2021 obsolete after coming of new AfwBatch class
if(!function_exists('print_debugg')) 
{

    function print_debugg($string,$echo=true)
    {
        global $batch_log_errors_arr, $batch_log_warnings_arr, $batch_log_infos_arr;
        $batch_log_infos_arr[] = $string;
    }

    function print_custom($type,$string,$echo=true)
    {
        global $batch_log_errors_arr, $batch_log_warnings_arr, $batch_log_infos_arr;
        $batch_log_infos_arr[] = $type.":".$string;
    }

    function print_comment($string,$echo=true, $prefix_comment="-- ")
    {
        global $batch_log_errors_arr, $batch_log_warnings_arr, $batch_log_infos_arr;
        $batch_log_infos_arr[] = $prefix_comment.$string;
    }

    function print_sql($string,$echo=true)
    {
        print_custom("sql",$string,$echo=true);
    }
    
    function print_error($string,$echo=true)
    {
        print_custom("error",$string,$echo=true);
    }
    
    
    function print_warning($string,$echo=true)
    {
        print_custom("warning",$string,$echo=true);
    }
    
    function print_info($string,$echo=true)
    {
        print_custom("info",$string,$echo=true);
    }
    
    function print_important($string,$echo=true)
    {
        print_custom("important",$string,$echo=true);
    }
} */   

?>