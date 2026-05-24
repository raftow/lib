<?php
class UfwQueryAnalyzer
{

    /**
     * var int
     */
    // public static $old_mode_sql_process_lourd;

    /**
     * @var int
     */
    public static $mode_sql_process_lourd = 0;
    /**
     * @var array
     */
    public static $_sql_analysis;
    /**
     * @var string
     */
    public static $the_last_sql;

    /**
     * @var int
     */
    public static $old_nb_queries_executed = 0;

    /**
     * @var int
     */
    public static $nb_queries_executed = 0;
    /**
     * @var bool
     */
    public static $print_debugg;
    /**
     * @var bool
     */
    public static $print_sql;
    /**
     * @var bool
     */
    public static $print_row;
    /**
     * @var int
     */
    public static $duree_sql_total;
    /**
     * @var array
     */
    public static $sql_picture_arr;

    private static $excluded_tables = array(
        "words" => 1,
    );

    public static function resetQueriesExecuted()
    {
        self::$nb_queries_executed = 1;
    }

    /**
     * @return bool
     */
    public static function isProcessLourdMode()
    {
        return (self::$mode_sql_process_lourd > 0);
    }
    // we have changed mode_sql_process_lourd from boolean to int 
    // to store the multuple mode_sql_process_lourd imbrique
    // each start is new deep level and each stop is a close of this level
    public static function startProcessLourdMode()
    {
        self::$old_nb_queries_executed = self::$nb_queries_executed;
        // self::$old_mode_sql_process_lourd = self::$mode_sql_process_lourd;
        if (!self::$mode_sql_process_lourd) self::$mode_sql_process_lourd = 0;
        self::$mode_sql_process_lourd++;
    }

    public static function stopProcessLourdMode()
    {
        self::$mode_sql_process_lourd--;
        // self::$mode_sql_process_lourd = self::$old_mode_sql_process_lourd;
        if (!self::$mode_sql_process_lourd) {
            self::$nb_queries_executed = 0;
            self::$mode_sql_process_lourd = 0;
        } else self::$nb_queries_executed = self::$old_nb_queries_executed;
    }


    public static function preAnalyseQuery($sql_query, $is_update)
    {
        // coming bad from outside so I will reparse
        $this_module = 'hzm';
        $this_table = 'hzm';



        if ($this_table == 'hzm') {
            // analyse sql query and parse table name
            // first remove -- method xxxxxx  dohtem -- line
            list($pre_sql_dohtem, $anal_sql_query) = explode(
                "dohtem --\n",
                $sql_query
            );
            if (!$anal_sql_query) {
                $anal_sql_query = $sql_query;
            }
            $anal_sql_query = trim($anal_sql_query);
            $anal_sql_query_orig = $anal_sql_query = '@@XX' . strtoupper($anal_sql_query);

            // try to understand type of query delete, update, insert into or select
            list($anal_sql_query1, $anal_sql_query2) = explode(
                '@@XXDELETE FROM ',
                $anal_sql_query
            );
            if ($anal_sql_query2) {
                $anal_sql_query = trim($anal_sql_query2);
            } else {
                list($anal_sql_query1, $anal_sql_query2) = explode(
                    '@@XXSELECT ',
                    $anal_sql_query
                );
                if ($anal_sql_query2) {
                    $anal_sql_query = trim($anal_sql_query2);
                    list($anal_sql_query1, $anal_sql_query2) = explode(
                        ' FROM ',
                        $anal_sql_query
                    );
                    $anal_sql_query = trim($anal_sql_query2);
                } else {
                    list($anal_sql_query1, $anal_sql_query2) = explode(
                        '@@XXUPDATE ',
                        $anal_sql_query
                    );
                    if ($anal_sql_query2) {
                        $anal_sql_query = trim($anal_sql_query2);
                    } else {
                        list($anal_sql_query1, $anal_sql_query2) = explode(
                            '@@XXINSERT INTO ',
                            $anal_sql_query
                        );
                        if ($anal_sql_query2) {
                            $anal_sql_query = trim($anal_sql_query2);
                        } else {
                            // if other cases of SQL queries
                        }
                    }
                }
            }

            list($this_table_all) = explode(' ', $anal_sql_query);
            list($db_or_table, $this_table) = explode('.', $this_table_all);
            $db_or_table = trim($db_or_table);
            $this_table = trim($this_table);
            if (!$this_table) {
                $this_table = $db_or_table;
                $db = '';
            } else {
                $db = $db_or_table;
                if ($this_module == 'hzm') {
                    $this_module = $db;
                }
            }
            /*
            if($this_table != strtoupper($this_table))
            {
                die("not upper $this_table from explode('.', $this_table_all) w from $anal_sql_query w from $anal_sql_query_orig");
            }
            */
        } else {
            $this_module = strtoupper($this_module);
            $this_table = strtoupper($this_table);
        }

        $_sql_analysis_total_seuil_calls = AfwSession::config(
            '_sql_analysis_total_seuil_calls',
            3000
        );
        $_sql_analysis_seuil_calls = AfwSession::config(
            '_sql_analysis_seuil_calls',
            1200
        );



        /*try {*/

        if (!self::$duree_sql_total) {
            $duree_sql_total = 0;
        }

        $this_table_lower = strtolower($this_table);

        if (!self::$excluded_tables[$this_table_lower]) {
            if (!self::$nb_queries_executed) {
                self::$nb_queries_executed = 1;
            } else {
                self::$nb_queries_executed++;
            }
        }



        $we_can_not_throw_analysis_exception = self::isProcessLourdMode();
        $we_should_throw_analysis_exception = (AfwSession::config('MODE_DEVELOPMENT', false) and (self::$nb_queries_executed > $_sql_analysis_seuil_calls));
        if ($we_should_throw_analysis_exception and !$we_can_not_throw_analysis_exception) {
            $backtrace = debug_backtrace(1, 20);
            throw new AfwRuntimeException("Too much queries executed when mode is not lourd process mode !<br>
                                           Nb Queries Executed = " . self::$nb_queries_executed . " > $_sql_analysis_seuil_calls = Max <br> 
                                           Sql Picture = " . var_export(self::$sql_picture_arr, true) .
                "<!-- Backtrace = " . var_export($backtrace, true) . " -->");
        }

        $sql_info_class = 'sqlinfo';
        if ($is_update) $sql_info_class .= ' sqlupdate';
        $start_q_time = date('Y-m-d H:i:s');
        $start_m_time = microtime();

        return [$this_module, $this_table, $sql_info_class, $start_q_time, $start_m_time];
    }

    public static function postAnalyseQuery($sql_query, $preArr)
    {

        list($this_module, $this_table, $sql_info_class, $start_q_time, $start_m_time, $row_count, $affected_row_count) = $preArr;

        $end_m_time = 0;
        $end_m_time = microtime();
        $end_q_time = date('Y-m-d H:i:s');

        $duree_q = round(($end_m_time - $start_m_time) * 10000) / 10;
        if ($duree_q < 0) {
            $duree_q += 1000;
        } // because counter microtime() return to 0 after each 1 second

        self::$duree_sql_total += $duree_q;

        $title_duration = '';
        $this_table_lower = strtolower($this_table);

        if ((!self::$excluded_tables[$this_table_lower]) and AfwSession::config('MODE_DEVELOPMENT', false) and (!self::isProcessLourdMode()) and (!AfwSession::config('MODE_MEMORY_OPTIMIZE', true))) {
            if (!self::$_sql_analysis[$this_module][$this_table][$sql_query]) {
                self::$_sql_analysis[$this_module][$this_table][$sql_query] = 1;
            } else {
                self::$_sql_analysis[$this_module][$this_table][$sql_query]++;
                if (self::$_sql_analysis[$this_module][$this_table][$sql_query] > 50) {
                    if (AfwSession::config('MODE_DEVELOPMENT', false)) {
                        throw new AfwRuntimeException(
                            "Query Analysis Crash for : $this_module / $this_table / $sql_query : has been called more than 50 times, <br>
                            May be because the result is empty so no cache working, <br>\nYou can below un-hide <b>the TECHNICAL SQL analysis :</b> <br><hr><pre class='technical sql'>"
                                . var_export(self::$_sql_analysis, true) .
                                "</pre><br> Or if should be managed by AfwLoadHelper::getLookupMatrix(), <br>\nYou can below un-hide <b>the TECHNICAL CACHE OF Lookup-Matrix :</b> : <br><hr><pre class='technical php'>"
                                . var_export(AfwLoadHelper::getLookupMatrix(), true) . "</pre>"
                        );
                    }
                }
            }
            /*
                if($this_table != strtoupper($this_table))
                {
                    die("not upper $this_table from explode('.', $this_table_all) w from $anal_sql_query w from $anal_sql_query_orig");
                }*/

            if (!self::$sql_picture_arr[$this_module][$this_table]) {
                self::$sql_picture_arr[$this_module][$this_table] = 1;
            } else {
                self::$sql_picture_arr[$this_module][$this_table]++;
            }
            $this_table_lower = strtolower($this_table);
            $_sql_analysis_seuil_calls_by_table = AfwSession::config(
                "$this_table_lower-sql-analysis-max-calls",
                AfwSession::config(
                    '_sql_analysis_seuil_calls_by_table',
                    600
                )
            );

            if (self::$sql_picture_arr[$this_module][$this_table] > $_sql_analysis_seuil_calls_by_table) {
                if (!self::$excluded_tables[$this_table_lower]) {
                    throw new AfwRuntimeException(
                        "<p>static analysis crash : The table $this_module-$this_table has been invoked more than $_sql_analysis_seuil_calls_by_table times ($this_table_lower-sql-analysis-max-calls)</p>
                             <h5>$sql_query</h5><br> 
                             If this is absolutely needed please start the process lourd mode
                             <div class='technical'>
                             So it is to be optimized sql_picture => " . var_export(self::$sql_picture_arr, true) .
                            " all_vars => " . AfwSession::log_all_data() .
                            "</div>"
                    );
                }
            }
        }

        $sql_time_max_in_milli_sec = AfwSession::config(
            'sql_time_max_in_milli_sec',
            50.0
        );

        $sql_capture_and_backtrace = AfwSession::config("sql_to_capture", "");

        if (((!self::isProcessLourdMode())) or $sql_capture_and_backtrace) {
            if (!$sql_time_max_in_milli_sec) {
                $sql_time_max_in_milli_sec = 30.0;
            }
            if ($duree_q > $sql_time_max_in_milli_sec) {
                $sql_info_class .= ' lourde';
                $title_duration = 'heavy';
            }

            if (self::$duree_sql_total > 500 * $sql_time_max_in_milli_sec) {
                $sql_info_class .= ' stop';
                $title_duration = "heavy stop " . self::$duree_sql_total . " > 500*[$sql_time_max_in_milli_sec]";
            }

            $backtrace = debug_backtrace(1, 20);


            $backtrace_html = AfwHtmlHelper::htmlBackTrace($backtrace, AfwSession::config("advanced-back-trace", false));
            $nb_queries_exec = self::$nb_queries_executed;
            $duree_total = self::$duree_sql_total;

            $analyses_log = "<b>start time</b> : $start_q_time,\n
            <b>end_time</b> : $end_q_time,\n
            <b>duration $title_duration</b> : $duree_q milli-sec
            <b>duration total</b> : $duree_total milli-sec
            <b>Query number</b> : $nb_queries_exec\n   
            <b>back trace</b>\n<br> : $backtrace_html\n                                             
                                                    ";
        } else {
            $analyses_log = "";
        }

        $information = "<div class='$sql_info_class'>
                                                    <b>Module</b> : $this_module,\n
                                                    <b>Table</b> : $this_table,\n
                                                    <b>sql</b> :\n $sql_query\n 
                                                    <b>rows</b> : $row_count,\n 
                                                    <b>affected</b> : $affected_row_count,\n 
                                                    $analyses_log                                                     
                                                    
                                                    </div>";
        // die("will sql log :<br>$information, $this_module");

        AfwSession::sqlLog($information, $this_module);
        /*
        if($this_table=="APPLICANT")
        {
            die("has sql logged :<br>$sql_query <br>Table : $this_table<br> log = ".AfwSession::getLog());
        }*/



        /*
        $log_end = '_qry end (' . date('H:i:s') . ')';
        $log_end .= "\n row count = $row_count";
        $log_end .= "\n affected rows = $affected_row_count";

        if ($result) {
            $log_end .=
                "\n mysql query result = " . var_export($result, true);
        }*/
    }
}
