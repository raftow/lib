<?php

class AfwDatabase extends AFWRoot
{
    /**
     *
     * Flag for connection
     * @var boolean
     */
    private static $connect = false;
    private static $first_query = true;
    /**
     *
     * Link of connection
     * @var resource
     */
    private static $link = [];

    public static function getLinkByName($project_link_name)
    {
        return self::$link[$project_link_name];
    }

    /**
     * _connect
     * Connect to DBMS
     */
    public static function _connect($module_server = '')
    {
        $project_link_name = 'server' . $module_server;
        if (!self::$link[$project_link_name] or !self::$connect) {
            $hostname = AfwSession::config("${module_server}host", '');
            $username = AfwSession::config("${module_server}user", '');
            $password = AfwSession::config("${module_server}password", '');
            $database = AfwSession::config("${module_server}database", '');
            // if($module_server=="nartaqi") common_die("params of connection to server [$module_server] are [$hostname, $username, $password, $database] from : ".AfwSession::log_config());
            if (!$hostname or !$username) {
                common_die(
                    "host or user name param not found in the external config file for server [$module_server]" .
                        AfwSession::log_config()
                );
            }

            self::$link[$project_link_name] = AfwMysql::connection(
                $hostname,
                $username,
                $password,
                $database
            );
            if (!self::$link[$project_link_name]) {
                if (AfwSession::config('MODE_DEVELOPMENT', false)) {
                    $infos = "with following params :\n host = $hostname, user = $username";
                }
                common_die("Failed to connect to server $infos.");
            }

            self::$connect = true;
        }
        return $project_link_name;
    }

    /**
     * _query
     * Return result of an execution's query
     * @param string $sql_query
     */
    public static function db_query(
        $sql_query,
        $throw_error = true,
        $throw_analysis_crash = true,
        $module_server = '',
        $this_module = 'hzm',
        $this_table = 'hzm',
        $need_utf8=true
    ) {
        global $_sql_analysis,
            $_sql_picture,
            $the_last_sql,
            $nb_queries_executed,
            $print_debugg,
            $print_sql,
            $sql_capture_and_backtrace,
            $MODE_BATCH_LOURD,
            $MODE_SQL_PROCESS_LOURD,
            $MODE_DEVELOPMENT,
            $duree_sql_total;

        // coming bad from outside so I will reparse
        $this_module = 'hzm';    
        $this_table = 'hzm';

        if ($this_table == 'hzm') 
        {
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
        }
        else
        {
            $this_module = strtoupper($this_module);
            $this_table = strtoupper($this_table);
        }

        $_sql_analysis_total_seuil_calls = AfwSession::config(
            '_sql_analysis_total_seuil_calls',
            3000
        );
        $_sql_analysis_seuil_calls = AfwSession::config(
            '_sql_analysis_seuil_calls',
            400
        );
        $_sql_analysis_seuil_calls_by_table = AfwSession::config(
            '_sql_analysis_seuil_calls_by_table',
            160
        );
        $sql_time_max_in_milli_sec = AfwSession::config(
            'sql_time_max_in_milli_sec',
            50.0
        );

        /*try {*/

            if (!$duree_sql_total) {
                $duree_sql_total = 0;
            }
            if (!$nb_queries_executed) {
                $nb_queries_executed = 1;
            } else {
                $nb_queries_executed++;
            }

            if (((!$MODE_BATCH_LOURD) and ($nb_queries_executed > $_sql_analysis_total_seuil_calls)) or
                ((!($MODE_SQL_PROCESS_LOURD or $MODE_BATCH_LOURD)) and ($nb_queries_executed > $_sql_analysis_seuil_calls))
            ) {
                if ($MODE_DEVELOPMENT) {
                    self::simpleError(
                        "MODE_BATCH_LOURD=$MODE_BATCH_LOURD empty ? and $nb_queries_executed > $_sql_analysis_total_seuil_calls ?<br>
                        MODE_SQL_PROCESS_LOURD=$MODE_SQL_PROCESS_LOURD empty ? and $nb_queries_executed > $_sql_analysis_seuil_calls ?<br>
                        Too much queries executed ! ",
                        'sql_picture => ' . var_export($_sql_picture, true)
                    );
                }
            }

            $project_link_name = AfwDatabase::_connect($module_server);
            if($need_utf8)
            {
                AfwMysql::query("set character_set_results='utf8'",AfwDatabase::getLinkByName($project_link_name));
            }
            
            //die("mysql_query($sql_query, AfwDatabase::getLinkByName($project_link_name))");
            $sql_info_class = 'sqlinfo';
            $start_q_time = date('Y-m-d H:i:s');
            $start_m_time = microtime();
            $result = AfwMysql::query($sql_query,AfwDatabase::getLinkByName($project_link_name));
            // var_dump($result);
            // die("AfwMysql::query($sql_query) result above");
            $end_m_time = microtime();
            $end_q_time = date('Y-m-d H:i:s');

            $duree_q = round(($end_m_time - $start_m_time) * 10000) / 10;
            if ($duree_q < 0) {
                $duree_q += 1000;
            } // because counter microtime() return to 0 after each 1 second
            $duree_sql_total += $duree_q;
            $title_duration = '';

            if (!$result) {
                $sql_error =
                    "sql error on [$project_link_name] query :[$sql_query] ==> " .
                    AfwMysql::get_error(
                        AfwDatabase::getLinkByName($project_link_name)
                    );
                if ($print_debugg and $print_sql) {
                    echo $sql_error;
                }
                AFWDebugg::log($sql_error);
                if ($throw_error) {
                    self::lightSafeDie($sql_error);
                    /*
                                                if($MODE_DEVELOPMENT) self::simpleError("sql_error",$sql_error);
                                        else self::simpleError("خطأ","حدث خطأ [$me] أثناء القيام باجراء على قاعدة البيانات  بتاريخ ".date("Y-m-d H:i:s"). " query : $sql_error");
                                        */
                } else {
                    self::lightSafeDie(
                        'see why this error is not thrown and manage this well : ' .
                            $sql_error
                    );
                    AfwSession::sqlError($sql_error, 'hzm');
                }
            } else {
                /*   else
                                {
                                $this_module = "hzm";
                                $this_table = extract_table_from_query($sql_query);
                                if(!$this_table) $this_table = "all";
                                }*/

                if ((!$MODE_BATCH_LOURD) and (!$MODE_SQL_PROCESS_LOURD) and (!AfwSession::config('MODE_MEMORY_OPTIMIZE', true))) 
                {
                    if (!$_sql_analysis[$this_module][$this_table][$sql_query]) 
                    {
                        $_sql_analysis[$this_module][$this_table][$sql_query] = 1;
                    } 
                    else 
                    {
                        $_sql_analysis[$this_module][$this_table][$sql_query]++;
                        if ($_sql_analysis[$this_module][$this_table][$sql_query] > 50) 
                        {
                            if ($throw_error and $throw_analysis_crash and $MODE_DEVELOPMENT) 
                            {
                                self::simpleError(
                                    'static analysis crash',
                                    "query $sql_query : has been called more than 50 times, may be because the result is empty so no cache working",
                                    '_sql_analysis => ' . var_export($_sql_analysis, true)
                                );
                            }
                        }
                    }
                    /*
                    if($this_table != strtoupper($this_table))
                    {
                        die("not upper $this_table from explode('.', $this_table_all) w from $anal_sql_query w from $anal_sql_query_orig");
                    }*/

                    if (!$_sql_picture[$this_module][$this_table]) 
                    {
                        $_sql_picture[$this_module][$this_table] = 1;
                    } 
                    else 
                    {
                        $_sql_picture[$this_module][$this_table]++;
                    }

                    if($_sql_picture[$this_module][$this_table]>$_sql_analysis_seuil_calls_by_table)
                    {
                        
                        
                        if ($throw_error and $throw_analysis_crash and $MODE_DEVELOPMENT) 
                        {
                            throw new RuntimeException(
                                "<p>static analysis crash : The table $this_table has been invoked more than $_sql_analysis_seuil_calls_by_table times</p>
                                 <h5>$sql_query</h5><br> 
                                 <div class='technical'>
                                 So it is to be optimized sql_picture => ". var_export($_sql_picture, true) .
                                " all_vars => " . AfwSession::log_all_data(). 
                                "</div>"
                            );
                        }
                    }
                }

                $row_count = AfwMysql::rows_count($result);
                $affected_row_count = AfwMysql::affected_rows(
                    AfwDatabase::getLinkByName($project_link_name)
                );
                $result_log = "rows count : $row_count, affected rows : $affected_row_count";
                /* already printed by AfwMysql::query() function
                if ($print_sql) {
                    AfwBatch::print_sql($sql_query);
                }
                if ($print_debugg) {
                    AfwBatch::print_debugg($result_log);
                }*/
                $the_last_sql =
                    "<br>\n " .
                    $sql_query .
                    "\n <br> " .
                    $result_log .
                    " <br>\n";

                if (!$MODE_BATCH_LOURD or $sql_capture_and_backtrace) {
                    if (!$sql_time_max_in_milli_sec) {
                        $sql_time_max_in_milli_sec = 30.0;
                    }
                    if ($duree_q > $sql_time_max_in_milli_sec) {
                        $sql_info_class .= ' lourde';
                        $title_duration = 'heavy';
                    }

                    if ($duree_sql_total > 500 * $sql_time_max_in_milli_sec) {
                        $sql_info_class .= ' stop';
                        $title_duration = "heavy stop $duree_sql_total > 500*[$sql_time_max_in_milli_sec]";
                    }

                    $backtrace = debug_backtrace(1, 20);
                    $backtrace_html = AfwHtmlHelper::htmlBackTrace($backtrace);

                    $information = "<div class='$sql_info_class'>
                                                        <b>Module</b> : $this_module,\n
                                                        <b>Table</b> : $this_table,\n
                                                        <b>Query number</b> : $nb_queries_executed,\n                                                
                                                        <b>sql</b> :\n $sql_query\n 
                                                        <b>rows</b> : $row_count,\n 
                                                        <b>affected</b> : $affected_row_count,\n 
                                                        <b>start time</b> : $start_q_time,\n
                                                        <b>end_time</b> : $end_q_time,\n
                                                        <b>duration $title_duration</b> : $duree_q milli-sec
                                                        <b>duration total</b> : $duree_sql_total milli-sec
                                                        <b>back trace</b>\n<br> : $backtrace_html 
                                                        
                                                        </div>";
                    AfwSession::sqlLog($information, $this_module);
                    //if(contient($sql_query, "INSERT INTO")) die("INSERT INTO logged"); // AfwSession::debuggLog();

                    if($nb_queries_executed==3) die("nb_queries_executed = $nb_queries_executed information = $information log = ".AfwSession::getLog());
                } else {
                    //AfwSession::sqlLog("LOG-QUERY : ".$sql_query, $this_module);
                    
                    /*
                    if(AfwStringHelper::stringContain($sql_query, 'update ')) 
                    {
                        
                    }*/
                }

                // $sql_capture_and_backtrace can be setted in application_config.php file
                if ($sql_capture_and_backtrace) {
                    if (contient($sql_query, $sql_capture_and_backtrace)) {
                        throw new RuntimeException('sql '.$sql_capture_and_backtrace.' captured');
                    }
                }

                $log_end = '_qry end (' . date('H:i:s') . ')';
                $log_end .= "\n row count = $row_count";
                $log_end .= "\n affected rows = $affected_row_count";

                if ($result) {
                    $log_end .=
                        "\n mysql query result = " . var_export($result, true);
                }
            }
            /*
        } catch (Exception $e) {
            $sql_error = $e->getMessage();
            if ($throw_error) 
            {
                throw $e;
            } 
            else 
            {
                self::lightSafeDie("For SQL $sql_query on Server/ProjectLink[$module_server/$project_link_name] : 
                    <div class='sql-error'>See why this error is not thrown and manage this well : " .$sql_error . "</div>");
                AfwSession::sqlError($sql_error, 'hzm');
            }
        }*/

        return [$result, $project_link_name];
    }

    /**
     * db_recup_value
     * Return value containing fetched column
     * @param string $query
     */
    public static function db_recup_value(
        $query,
        $throw_error = true,
        $throw_analysis_crash = true,
        $module_server = ''
    ) {
        $result = self::db_recup_row(
            $query,
            $throw_error,
            $throw_analysis_crash,
            $module_server
        );
        if (count($result)) {
            $return = $result[0];
        } else {
            $return = false;
        }
        // die("RAFIK :   : db_recup_value($query) returned [$return] <br>");
        return $return;
    }

    /**
     * db_recup_row
     * Return an array containing a fetched row
     * @param string $query
     */
    public static function db_recup_row(
        $query,
        $throw_error = true,
        $throw_analysis_crash = true,
        $module_server = ''
    ) {
        list($result, $project_link_name) = self::db_query(
            $query,
            $throw_error,
            $throw_analysis_crash,
            $module_server
        );
        $num_rows = self::_num_rows($result);
        // die("query : [$query] returned $num_rows row(s)");
        if ($num_rows) {
            return AfwMysql::fetch_array($result);
        } else {
            return [];
        }
    }

    /**
     * db_recup_rows
     * Return an array containing fetched rows
     * @param string $query
     */
    public static function db_recup_rows(
        $query,
        $throw_error = true,
        $throw_analysis_crash = true,
        $module_server = ''
    ) {
        list($result, $project_link_name) = self::db_query(
            $query,
            $throw_error,
            $throw_analysis_crash,
            $module_server
        );
        //echo "RAFIK self::_num_rows(result) = ".self::_num_rows($result)."<br>";
        if (self::_num_rows($result)) {
            $array = [];
            while ($row = AfwMysql::fetch_array($result)) {
                //echo "RAFIK row : <br>";
                if (AfwSession::config('LOG_SQL', true)) {
                    //AFWDebugg::log($row,true);
                }
                $array[] = $row;
            }
        } else {
            $array = [];
        }

        // die("RAFIK :   : db_recup_rows : ".var_export($array,true)." <br>");
        return $array;
    }

    /**
     * _num_rows
     * Return number of rows
     * @param resource $result
     */
    private static function _num_rows($result)
    {
        $count = AfwMysql::rows_count($result);
        return $count;
    }
}
