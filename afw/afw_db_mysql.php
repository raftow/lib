<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998,1999 SH Online Dienst GmbH
 *                    Boris Erdmann, Kristian Koehntopp
 *
 *
 */ 

class AfwDbMysql {
  
  /* public: connection parameters */
  var $Host     = "";
  var $Database = "";
  var $User     = "";
  var $Password = "";

  /* public: configuration parameters */
  var $Auto_Free     = 0;     ## Set to 1 for automatic mysqli_free_result()
  var $Debug         = 1;     ## Set to 1 for debugging messages.
  var $Halt_On_Error = "yes"; ## "yes" (halt with message), "no" (ignore errors quietly), "report" (ignore errror, but spit a warning)
			      ## "zorg" (save the error message in a global variable of ZORG's software)
  var $Seq_Table     = "SEQUENCE";

  /* public: result array and current row number */
  var $Record   = array();
  var $Row;

  /* public: current error number and error text */
  var $Errno    = 0;
  var $Error    = "";

  /* public: this is an api revision, not a CVS revision. */
  var $type     = "mysql";
  var $revision = "1.2";

  /* private: link and query handles */
  var $Link_ID  = null;
  var $QueryResult = null;
  
  public static function getInstance()
  {
      return new AfwDbMysql();
  }

  /* public: constructor */
  public function execQuery($query = "") {
      $this->query($query);
  }

  /* public: some trivial reporting */
  public function link_id() {
    return $this->Link_ID;
  }

  public function QueryResult() {
    return $this->QueryResult;
  }

  /* public: connection management */
  public function connect($Database = "", $Host = "", $User = "", $Password = "") {
    /* Handle defaults */
    if ("" == $Database)
      $Database = $this->Database;
    if ("" == $Host)
      $Host     = $this->Host;
    if ("" == $User)
      $User     = $this->User;
    if ("" == $Password)
      $Password = $this->Password;
      
    /* establish connection, select database */
    if ( 0 == $this->Link_ID ) {
    
        $this->Link_ID = mysqli_connect($Host, $User, $Password);
        
        if (!$this->Link_ID) 
        {
                global $MODE_BATCH;
                $objme = null; // AfwSession::getUserConnected();
                $me = ($objme) ? $objme->id : 0;

                if(($objme and $objme->isAdmin()) or true) $tech_infos = "Host : $Host $User, $Password";
                else $tech_infos = "Host : $Host $User, [Password]";
                
                $this->halt("database connection failed : $tech_infos");
                return 0;
        }

        if (!mysqli_select_db($this->Link_ID, $Database))
        {
                $this->halt("cannot use database  [host=".$this->Host."] [db=".$this->Database."]");
                return 0;
        }
    }
     
     if (!mysqli_query($this->Link_ID, "SET CHARACTER SET 'utf8';")) {
        $this->halt("cannot set CHARACTER set utf8 ".$this->Database);
        return 0;
     }
     
    return $this->Link_ID;
  }

  /* public: discard the query result */
  public function free() {
      mysqli_free_result($this->QueryResult);
      $this->QueryResult = 0;
  }
  
  public function complete_len($str, $new_len, $complete_with_char="0",$complete_adroite=true)
  {
     //$str = substr(utf8_decode($str),0,$new_len);
     while(strlen($str)<$new_len)
     {
        if($complete_adroite)
           $str = $complete_with_char . $str;
        else
           $str .= $complete_with_char; 
     }
     return $str;  
  }
  

  /* public: perform a query */
  public function query($Query_String) 
  {
    $this->Error = "";
    /* No empty queries, please, since PHP4 chokes on them. */
    if ($Query_String == "")
    {
      /* The empty query string is passed on from the constructor,
       * when calling the class without a query, e.g. in situations
       * like these: '$db = new DB_Sql_Subclass;'
       */
      $this->Error = "Query is empty : ". $this->Error;
      return 0;
    }

    if (!$this->connect()) 
    {
        $this->Error = "connection failed : " . $this->Error;
        return 0; /* we already complained in connect() about that. */
    };

    // die("My query $Query_String this ".var_export($this,true));

    # New query, discard previous result.
    if ($this->QueryResult) {
      $this->free();
    }
    
    if ($this->Debug) 
    {
          $this->Error = "Logging error";
          global $DEBUGG_SQL_DIR;
          $debug_done=false;
          $me = AfwSession::getSessionVar("user_id");
          if ($me) 
          {
              $debug_file = date("Ymd")."_".$this->complete_len($me,6).".txt";
            
              if ($fp=fopen($DEBUGG_SQL_DIR.$debug_file, "a"))
              {
                  fwrite($fp, ltrim($Query_String) . "\n");
                  fclose($fp);
                  $debug_done=true;
              }
          }
          if (($debug_done==false) && ($this->Halt_On_Error != "zorg") ) 
          {
                //printf("Debug: query = %s<br>\n", $Query_String);
          }
          $this->Error = "";
    }
    // $old_err = $this->Error;
    // $this->Error = "selecting database ".$this->Database." from dblink ".$this->Link_ID;
    $choosen_db = mysqli_select_db($this->Link_ID, $this->Database);
    // $this->Error = $old_err;
    $this->QueryResult = mysqli_query($this->Link_ID, $Query_String);
    $this->Row   = 0;
    $this->Errno = mysqli_errno($this->Link_ID);
    $this->Error = mysqli_error($this->Link_ID);
     
    if (!$this->QueryResult) 
    {
        $this->halt("Invalid SQL: ".$Query_String);
    }

    # Will return nada if it fails. That's fine.
    return $this->QueryResult;
  }

  /* public: walk result set */
  public function next_record() 
  {
    if (!$this->QueryResult) {
      $this->halt("next_record called with no query pending.");
      return 0;
    }

    $this->Record = @mysqli_fetch_array($this->QueryResult);
    $this->Row   += 1;
    $this->Errno  = mysqli_errno($this->Link_ID);
    $this->Error  = mysqli_error($this->Link_ID);

    $stat = is_array($this->Record);
    if (!$stat && $this->Auto_Free) {
      $this->free();
    }
    return $stat;
  }

  /* public: position in result set */
  public function seek($pos = 0) {
    $status = @mysqli_data_seek($this->QueryResult, $pos);
    if ($status)
      $this->Row = $pos;
    else {
      $this->halt("seek($pos) failed: result has ".$this->num_rows()." rows");

      /* half assed attempt to save the day, 
       * but do not consider this documented or even
       * desireable behaviour.
       */
      @mysqli_data_seek($this->QueryResult, $this->num_rows());
      $this->Row = $this->num_rows();
      return 0;
    }

    return 1;
  }

  /* public: table locking */
  /*
  public function lock($table, $mode="write") 
  {
      $this->connect();
      
      $query="lock tables ";
      if (is_array($table)) {
        while (list($key,$value)=each($table)) {
          if ($key=="read" && $key!=0) {
            $query.="$value read, ";
          } else {
            $query.="$value $mode, ";
          }
        }
        $query=substr($query,0,-2);
      } else {
        $query.="$table $mode";
      }
      $res = @mysqli_query($this->Link_ID, $query);
      if (!$res) {
        $this->halt("lock($table, $mode) failed.");
        return 0;
      }
      return $res;
  }*/
  
  public function unlock() 
  {
      $this->connect();

      $res = @mysqli_query($this->Link_ID, "unlock tables");
      if (!$res) {
        $this->halt("unlock() failed.");
        return 0;
      }
      return $res;
  }


  /* public: evaluate the result (size, width) */
  public function affected_rows() {
    return @mysqli_affected_rows($this->Link_ID);
  }

  public function num_rows() {
    return @mysqli_num_rows($this->QueryResult);
  }

  public function num_fields() {
    return @mysqli_num_fields($this->QueryResult);
  }

  /* public: shorthand notation */
  public function nf() {
    return $this->num_rows();
  }

  public function np() {
    print $this->num_rows();
  }

  public function f($Name) {
    return $this->Record[$Name];
  }

  public function p($Name) {
    print $this->Record[$Name];
  }

  
  public function mysqlInsertedId()
  {
        return mysqli_insert_id($this->Link_ID);
  }

  

  

  /* private: error handling */
  // Daniel 09 fev 2001
  public function halt($msg) 
  {
     global $mode_batch;

    /*
    if(!$this->Link_ID)
    {
        $this->Error = @mysqli_error();
        $this->Errno = @mysqli_errno();
    }
    else
    {
        
    }*/

    $this->Error = @mysqli_error($this->Link_ID);
    $this->Errno = @mysqli_errno($this->Link_ID);

    if(!$mode_batch)
    {
            $msg  = sprintf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
            $msg .= sprintf("<b>MySQL Error</b>: %s (%s)<br>\n", $this->Errno, $this->Error);
            
            $backtrace = debug_backtrace();
            $msg .= "<br> <b>Trace :</b> ";
        
            $msg .= "<table dir='ltr'>
                  <tr>
                    <th><b>Function </b></th>
                    <th><b>File </b></th>
                    <th><b>Line </b></th>
                    <th><b>Object </b></th>
                    <th><b>Params </b></th>
                  </tr>\n";
            foreach($backtrace as $entry)             
            {
                    if($entry['object']) $object_desc = get_class($entry['object'])."-".$entry['object']->__toString();
                    else $object_desc = "N/A";
                    if(count($entry['args']))
                    {
                      $args_desc = var_export($entry['args'],true);
                    }
                    else
                    {
                      $args_desc = "()";
                    }
                    $msg .= "<tr>";
               	    $msg .= "<td>" . $entry['public function']."</td>"; 
                    $msg .= "<td>" . $entry['file']."</td>"; 
                    $msg .= "<td>" . $entry['line']."</td>";
                    $msg .= "<td>" . $object_desc."</td>";
                    $msg .= "<td>" . $args_desc."</td>";
                    $msg .= "</tr>";
            }
            $msg .= "</table>";
            
            switch ($this->Halt_On_Error) {
        	case "no":
              		return;
        		break;
        
                case "report" :
        		echo $msg;
        		return;
        		break;
        
        	case "zorg" :
        		global $gMsgWarning;
        		$gMsgWarning.=$msg;
        		break;
        
        	case "yes" :
        		echo $msg;
              		die("Session halted.");
        		break;
            }
    }
    else
    {
            $error_msg = "MySQL Error, Message : $msg ". $this->Errno . " / error no = " . $this->Error;
            raiseError($error_msg);
    }
    

    
  }

}