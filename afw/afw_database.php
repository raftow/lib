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
            $hostname = AfwSession::config($module_server . "host", '', "the_database", 'yes');
            $username = AfwSession::config($module_server . "user", '', "the_database", 'yes');
            $password = AfwSession::config($module_server . "password", '', "the_database", 'yes');
            $database = AfwSession::config($module_server . "database", '', "the_database", 'yes');
            $port = AfwSession::config($module_server . "port", '', "the_database", 'yes');
            if(!$port) $port = null;
            // if($module_server=="nartaqi") throw new AfwRuntimeException("params of connection to server [$module_server] are [$hostname, $username, $password, $database] from : ".AfwSession::log_config());
            if (!$hostname or !$username) {
                throw new AfwRuntimeException(
                    "host or user name param not found in the database_config.php file for server [$module_server]" .
                        AfwSession::log_config()
                );
            }

            self::$link[$project_link_name] = AfwMysql::connection(
                $hostname,
                $username,
                $password,
                $database,
                $port
            );
            if (!self::$link[$project_link_name]) {
                if (AfwSession::config('MODE_DEVELOPMENT', false)) {
                    $infos = "with following params :\n host = $hostname, user = $username";
                }
                throw new AfwRuntimeException("Failed to connect to server $infos.");
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
        $need_utf8 = null,
        $is_update = true
    ) {
        global $global_need_utf8;

        if (!isset($global_need_utf8)) $global_need_utf8 = true;
        if ($need_utf8 === null) $need_utf8 = $global_need_utf8;



        $project_link_name = AfwDatabase::_connect($module_server);
        if (true) // $need_utf8
        {
            AfwMysql::query("set character_set_results='utf8' ", AfwDatabase::getLinkByName($project_link_name));
            // seems below is not ok because we need to do set character_set_results='utf8' for each mysqli session otherwise we will see 
            // arabi text as ??????
            //$global_need_utf8 = false;
        }

        //die("mysql_query($sql_query, AfwDatabase::getLinkByName($project_link_name))");
        $arrPre = AfwQueryAnalyzer::preAnalyseQuery($sql_query, $is_update);
        $result = AfwMysql::query($sql_query, AfwDatabase::getLinkByName($project_link_name), $is_update);
        // var_dump($result);
        // die("AfwMysql::query($sql_query) result above");
        if (!$result) {
            $sql_error = "SQL Error on [$project_link_name] query :[$sql_query] ==> " . AfwMysql::get_error(AfwDatabase::getLinkByName($project_link_name));
            throw new AfwRuntimeException($sql_error);
        }

        $row_count = AfwMysql::rows_count($result);
        $affected_row_count = AfwMysql::affected_rows(AfwDatabase::getLinkByName($project_link_name));
        // $result_log = "rows count : $row_count, affected rows : $affected_row_count";
        $arrPre[] = $row_count;
        $arrPre[] = $affected_row_count;
        
        AfwQueryAnalyzer::postAnalyseQuery($sql_query,$arrPre);

        $sql_capture_and_backtrace = AfwSession::config("sql_to_capture","");
        if ($sql_capture_and_backtrace) {
            if (AfwStringHelper::stringContain($sql_query, $sql_capture_and_backtrace)) {
                throw new AfwRuntimeException('sql ' . $sql_capture_and_backtrace . ' captured');
            }
        }

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
        global $print_row;
        list($result, $project_link_name) = self::db_query(
            $query,
            $throw_error,
            $throw_analysis_crash,
            $module_server,
            'hzm',
            'hzm',
            null,
            false
        );
        $num_rows = self::_num_rows($result);
        // die("query : [$query] returned $num_rows row(s)");
        if ($num_rows) {
            $return = AfwMysql::fetch_array($result);
            if ($print_row) {
                AfwBatch::print_simpler_row($return);
            }

            return $return;
        } else {
            return [];
        }
    }

    public static function db_recup_liste($query, $listeCol)
    {
        $data = self::db_recup_rows($query);
        return self::data_to_liste($data, $listeCol);
    }

    public static function db_recup_index($query, $keyCol, $valueCol)
    {
        $data = self::db_recup_rows($query);
        return self::data_to_index($data, $keyCol, $valueCol);
    }

    public static function db_recup_bi_index($query, $key1Col, $key2Col, $valueCol)
    {
        $data = self::db_recup_rows($query);
        return self::data_to_bi_index($data, $key1Col, $key2Col, $valueCol);
    }

    public static function data_to_bi_index($data, $key1Col, $key2Col, $valueCol)
    {
        $new_data = [];

        foreach ($data as $ir => $row) {
            $key1 = $row[$key1Col];
            $key2 = $row[$key2Col];
            $value = $row[$valueCol];
            $new_data[$key1][$key2] = $value;
        }

        return $new_data;
    }

    public static function data_to_index($data, $keyCol, $valueCol)
    {
        $new_data = [];

        foreach ($data as $ir => $row) {
            $key = $row[$keyCol];
            $value = $row[$valueCol];
            $new_data[$key] = $value;
        }

        return $new_data;
    }

    public static function data_to_liste($data, $listeCol)
    {
        $new_data = [];

        foreach ($data as $ir => $row) {
            $value = $row[$listeCol];
            $new_data[] = $value;
        }

        return $new_data;
    }
    

    public static function data_by_id($data, $keyCol)
    {
        $new_data = [];

        foreach ($data as $ir => $row) {
            $key = $row[$keyCol];
            $new_data[$key] = $row;
        }

        return $new_data;
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
        global $print_row;
        list($result, $project_link_name) = self::db_query(
            $query,
            $throw_error,
            $throw_analysis_crash,
            $module_server,
            'hzm',
            'hzm',
            null,
            false
        );
        //echo "RAFIK self::_num_rows(result) = ".self::_num_rows($result)."<br>";
        $nbRows = self::_num_rows($result);
        if ($nbRows > 0) {
            $array = [];
            while ($row = AfwMysql::fetch_array($result)) {
                //echo "RAFIK row : <br>";
                //if (AfwSession::config('LOG_SQL', true)) {
                //// AFWDebugg::log($row,true);
                //}
                /*
                $rowCleaned = [];
                foreach($row as $col => $val)
                {
                    if(!is_numeric($col)) $rowCleaned[$col] = $val;
                }
                $array[] = $rowCleaned;
                
*/

                foreach ($row as $col => $val) {
                    if (is_integer($col)) unset($row[$col]);
                }
                // die("rafik row 123 = ".var_export($row,true));
                $array[] = $row;
                // $last_row = $row;
            }
        } else {
            $array = [];
        }

        //die("RAFIK :   : db_recup_rows : ".var_export($array,true)." <br>");
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


    /**
     * max_update_date
     * Return number of rows
     * @param string $className model AFWObject subclass 
     * @param string $where clause where
     */
    public static function max_update_date($className, $where = '')
    {
        $inst = new $className();
        if ($where) {
            $inst->where($where);
        }
        $fld_update_date = $inst->fld_UPDATE_DATE();
        $func = "max($fld_update_date)";
        return $inst->func($func);
    }
}
