<?php

// old require of afw_root
// alter table hijra_date_base add unique key(HIJRI_YEAR,HIJRI_MONTH);
class AfwDbOci 
{
    private $connection = null;
    private $connection_string = null;
    private $username = null;
    private $password = null;

    private function connect(string $encoding = "", int $session_mode = OCI_DEFAULT)
    {
        $this->connection = oci_connect($this->username, $this->password, $this->connection_string, $encoding, $session_mode);
        
        if (!$this->connection) {
            $e = oci_error();
            throw new AfwRuntimeException($e['message']);
        }

        return true;
    }

    public function connectFromAfwSessionConfig($module_server, string $encoding = "", int $session_mode = OCI_DEFAULT)
    {
        $this->connection_string = AfwSession::config($module_server."connection_string", '');
        $this->username = AfwSession::config($module_server."user", '');
        $this->password = AfwSession::config($module_server."password", '');
        
        // if($module_server=="nartaqi") throw new AfwRuntimeException("params of connection to server [$module_server] are [$hostname, $username, $password, $database] from : ".AfwSession::log_config());
        if (!$this->connection_string or !$this->username) {
            throw new AfwRuntimeException(
                "host or user name param not found in the external config file for server [$module_server]" .
                    AfwSession::log_config()
            );
        }

        return $this->connect($encoding, $session_mode);

        
    }

    public function connectFromConfig($config, $module_server, string $encoding = "", int $session_mode = OCI_DEFAULT)
    {
        $this->connection_string = $config[$module_server."connection_string"];
        $this->username = $config[$module_server."username"];
        $this->password = $config[$module_server."password"];
        
        if (!$this->connection_string or !$this->username) {
            throw new AfwRuntimeException("bad connection_string or user name given");
        }

        return $this->connect($encoding, $session_mode);

        
    }


    private function prepareAndExecuteStatement($sql)
    {
        // Prepare query
        $stid = oci_parse($this->connection, $sql);
        if (!$stid) {
            $e = oci_error($this->connection);
            throw new AfwRuntimeException($e['message']);
        }

        // Execute query logic (detect syntax errors and such as)
        $r = oci_execute($stid);
        if (!$r) {
            $e = oci_error($stid);
            throw new AfwRuntimeException($e['message']);
        }

        return [$stid, $r];

    }

    public function runAndGetRecords($sql)
    {
        list($stid, $r) = $this->prepareAndExecuteStatement($sql);
        $array = [];
        while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) 
        {
            $array[] = $row;            
        }
        oci_free_statement($stid);

        return $array;
    }

    public function runAndCommitStatement($sql)
    {
        list($stid, $r) = $this->prepareAndExecuteStatement($sql);
        $return = oci_num_rows($stid);
        oci_commit($this->connection);
        oci_free_statement($stid);
        return $return;
    }

    public function close()
    {
        if($this->connection) oci_close($this->connection);
    }

    function __destruct() {
        $this->close();
    }
}