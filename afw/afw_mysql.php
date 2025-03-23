<?php
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

class AfwMysql
{
    public static function get_error($link)
    {
        if(self::php_is_old()) return null; // mysql_error($link);    
        else return mysqli_error($link);    
    }

    public static function insert_id($link)
    {
        if(self::php_is_old()) return null; // mysql_insert_id($link);    
        else return mysqli_insert_id($link);    
    }


    public static function rows_count($result)
    {
        if(self::php_is_old()) return null; // mysql_num_rows($link);    
        else return $result->num_rows;
    }

    public static function fetch_array($result)
    {
        if(self::php_is_old()) return null; // mysql_fetch_array($result);
        else return mysqli_fetch_array($result);
    }

    public static function affected_rows($link)
    {
        if(self::php_is_old()) return null; // mysql_affected_rows($link);    
        else return mysqli_affected_rows($link);    
    }

    public static function queryToCapture($sql)
    {
        return false;
        /*
        $sql = trim($sql);
        $sql_lower = strtolower($sql);
        if(!AfwStringHelper::stringStartsWith($sql_lower,"update c 0crm.request")) return false;
        if((!AfwStringHelper::stringContain($sql_lower,"employee_id = '0'")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id = 0")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id='0'")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id=0")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id = null")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id=null"))
        ) return false;

        return true;*/

    }

    public static function query($sql, $link, $is_update=false)
    {
        if(self::php_is_old()) return null; // mysql_query($sql, $link);    
        else
        {
            if(self::queryToCapture($sql))
            {
                throw new AfwRuntimeException("queryToCapture : $sql");
            }
            try{
                $return = mysqli_query($link, $sql);
                $sql_html = strip_tags($sql);
                if(strlen($sql_html)>1000)
                {
                    $sql_html = substr($sql_html, 0, 997). "...";
                }
                $aff_rows = mysqli_affected_rows($link);
            }
            catch(Exception $e)
            {                
                throw new AfwRuntimeException("Exception happened when query : $sql_html : ".$e->getMessage());
            }
            $log = date("H:i:s")." > ".$sql_html." > $aff_rows affected rows";
            if($is_update) AfwBatch::print_hard_sql($log);
            else AfwBatch::print_sql($log);
            return $return;
        } 
    }

    public static function connection($hostname, $username, $password, $database, $port=null)
    {
        if(self::php_is_old()) 
        {
            // return mysql_pconnect($hostname, $username, $password);
        }
        else
        {
            try{
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                return mysqli_connect($hostname, $username, $password, $database, $port);
            }
            catch(Exception $e)
            {
                die("failed to do connection($hostname, $username, *****, $database)");
                // if you do throw new AfwRuntimeException it will show stack trace containing password
            }
            catch(Error $e)
            {
                die("error when doing connection($hostname, $username, *****, $database)");
                // if you do throw new AfwRuntimeException it will show stack trace containing password
            } 
        }
    }


    public static function php_is_old()
    {
        // return (PHP_VERSION_ID < 70000);
        return false;
    }
    
    


}