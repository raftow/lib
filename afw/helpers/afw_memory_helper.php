<?php
class AfwMemoryHelper extends AFWRoot 
{
    public static final function memReport($mm = '')
    {
        global $nb_instances,
            $tab_instances,
            $tab_cached_instances,
            $nb_cached_instances,
            $nb_instances_total;

        //$objme = AfwSession::getUserConnected();
        $html_log = "";
        if (!$mm) {
            $mm = memory_get_usage(true);
        }
        if (
            AfwSession::config('MODE_DEVELOPMENT', false) or
            AfwSession::hasOption('SQL_LOG')
        ) {
            // die("rafik 2021 123456");
            $mm_used = memory_get_usage(false);
            $mm_unused = $mm - $mm_used;
            $html_log .= "<pre class=\"mem hzm log\">";
            $html_log .= "\n Usage    : " . $mm_used;
            $html_log .= "\n Not used : " . $mm_unused;
            $html_log .= "\n Total    : " . $mm;
            $html_log .= "\n Peak :" . memory_get_peak_usage();

            $html_log .= "\n report of objects created : " .
                var_export($tab_instances, true);
            $html_log .= "\n report of objects cached : " .
                var_export($tab_cached_instances, true);
            $average_used_by_object = round($mm_used / $nb_instances);
            $html_log .= "\n created : $nb_instances_total, should be used in memory : $nb_instances object(s)";
            $html_log .= "\n average-memory-by afw object : $average_used_by_object";
            $html_log .= '</pre>';
            

            return $html_log;
        }
    }


    public static final function checkMemoryBeforeInstanciating($objInstanciating)
    {
        global $nb_instances_total, $nb_instances,$tab_instances, $MODE_DEVELOPMENT, $MODE_BATCH_LOURD, $MAX_MEMORY_BY_REQUEST;
        if (!$tab_instances) {
            $tab_instances = [];
        }
        $this_cl = get_class($objInstanciating);
        if (!$tab_instances[$this_cl]) {
            $tab_instances[$this_cl] = 0;
        }
        $tab_instances[$this_cl]++;
        $objInstanciating->instanciated($tab_instances[$this_cl]);
        if (!$nb_instances) {
            $nb_instances = 1;
        } else {
            $nb_instances++;
        }

        if (!$nb_instances_total) {
            $nb_instances_total = 1;
        } else {
            $nb_instances_total++;
        }
        if(!$MAX_MEMORY_BY_REQUEST) $MAX_MEMORY_BY_REQUEST = 100000000;
        $mm = memory_get_usage(true);
        if (($mm > $MAX_MEMORY_BY_REQUEST) and $MODE_DEVELOPMENT) 
        {
            // @rafik.framework.v2.0 obsolete until we found a more professional way to do it
            gc_collect_cycles();
            $mm = memory_get_usage(true);
            
            if ($mm > $MAX_MEMORY_BY_REQUEST) {
                throw new AfwRuntimeException("MOMKEN OUT OF MEMORY ($mm > $MAX_MEMORY_BY_REQUEST)".var_export($tab_instances,true));
                //throw new AfwRuntimeException("MOMKEN OUT OF MEMORY", $throwed_arr=array("ALL"=>true, "FIELDS_UPDATED"=>true, "SQL"=>true, "DEBUGG"=>true, "CACHE"=>true));
            }
        }
        if ($nb_instances > 10000 and (!$MODE_BATCH_LOURD)) 
        {
            throw new AfwRuntimeException("too much objects created : $nb_instances : " .var_export($tab_instances, true));
        }
    }
}