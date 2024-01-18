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
        if(!AfwStringHelper::stringStartsWith($sql_lower,"update c0crm.request")) return false;
        if((!AfwStringHelper::stringContain($sql_lower,"employee_id = '0'")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id = 0")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id='0'")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id=0")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id = null")) and
           (!AfwStringHelper::stringContain($sql_lower,"employee_id=null"))
        ) return false;

        return true;*/

    }

    public static function query($sql, $link)
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
                $aff_rows = mysqli_affected_rows($link);
            }
            catch(Exception $e)
            {
                throw new AfwRuntimeException("Error happened when query : $sql : ".$e->getMessage());
            }
            AfwBatch::print_sql(date("H:i:s")." > ".$sql." > $aff_rows affected rows");
            return $return;
        } 
    }

    public static function connection($hostname, $username, $password, $database)
    {
        if(self::php_is_old()) 
        {
            // return mysql_pconnect($hostname, $username, $password);
        }
        else
        {
            
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            return mysqli_connect($hostname, $username, $password, $database);
        }
    }


    public static function php_is_old()
    {
        // return (PHP_VERSION_ID < 70000);
        return false;
    }
    
    


}