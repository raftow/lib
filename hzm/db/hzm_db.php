<?php

function getIndexedListValues($data, $keyCol, $valueCol)
{
          $new_data = array();
          
          foreach($data as $ir => $row)
          {
                  $key = $row[$keyCol];
                  $value = $row[$valueCol];
                  $new_data[$key] = $value;
          }
        
          return $new_data;
}


function getIndexedList($data, $keyCol)
{
          $new_data = array();
          
          foreach($data as $ir => $row)
          {
                  $key = $row[$keyCol];
                  $new_data[$key] = $row;
          }
        
          return $new_data;
}

function getDoubleIndexedList($data, $keyCol, $key2Col)
{
          $new_data = array();
          
          foreach($data as $ir => $row)
          {
                  $key = $row[$keyCol];
                  $key2 = $row[$key2Col];
                  $new_data[$key][$key2] = $row;
          }
        
          return $new_data;
}


function decodeData($data,$decode_arr)
{
    foreach($data as $ir => $row)
    {
       foreach($row as $nom_champ => $val)
       {
          if($decode_arr[$nom_champ])
          {
                 $data[$ir][$nom_champ] = AFWRoot::tt($decode_arr["table"].".".$nom_champ.".".$val);
          }
       }
    }
    
    return $data;      
}


function raiseError($msg, $throwed_arr=array(), $throwed_vals=array())
{
  global $MODE_BATCH, $project_code, $project;
  
    if($MODE_BATCH) $br = "";
    else $br = "<br>";
  
    if(!$MODE_BATCH) $msg = "$br\n   <pre style=\"direction: ltr;text-align: left;\">".$msg;
        
        
    foreach($throwed_arr as $obj_code => $throwed_obj) 
    {
         $msg .= "$br\n   throwed obj $obj_code = ".var_export($throwed_obj,true)."$br\n";
    }
    
    foreach($throwed_vals as $val_code => $throwed_val) 
    {
         $msg .= "$br\n  $val_code = ".$throwed_val."$br\n";
    }
    
    if(!$MODE_BATCH) $msg .= "</pre>";
    
    if($MODE_BATCH)
    { 
            $res = AfwBatch::emailError($project_code, $project, $msg);
            if(!$res["result"]) $msg .= ", can't send email for this error : ".$res["error"];
    }
    return showError($msg, $call_method = "");
}

function showError($msg, $call_method = "") 
{
    global $_POST, $out_scr, $MODE_BATCH;
	
      if($MODE_BATCH) $br = "";
      else $br = "<br>";
        
        if(!$MODE_BATCH) $ob_html = ob_get_clean();
        else $ob_html = "";
        
        if(!$MODE_BATCH) $message = "$msg\n$br <b>Trace :</b> ";
        else $message = "$msg \n Trace : ";
        
	$backtrace = debug_backtrace();
        if(!$MODE_BATCH)
        {
                $message .= "<table dir='ltr'><tr><th><b>Function </b></th><th><b>File </b></th><th><b>Line </b></th></tr>\n";
        	foreach($backtrace as $entry) 
                {
                        $message .= "<tr>";
        		$message .= "<td>" . $entry['function']."</td>"; 
                        $message .= "<td>" . $entry['file']."</td>"; 
                        $message .= "<td>" . $entry['line']."</td>";
                        $message .= "</tr>";
        	}
                $message .= "</table>";
                $message .= "<hr>";
        }
        else
        {
                $header = array("function"=>25, "file"=> 40, "line"=> 6,);
                AfwBatch::print_data($header,$backtrace, $colors=null);
        }
        
        if($_POST) 
        {
                $message .= "<table dir='ltr'>";
                foreach($_POST as $att => $att_val)
                {
			$message .= "<tr><td>posted <b>$att : </b></td><td>$att_val</td></tr>"; 
		}
                $message .= "</table><hr>";
        }
        
        $message .= $out_scr;
        
        die($ob_html.$message);
        trigger_error($message, E_USER_ERROR);
        
	return false;
}

function getValueFromSQL($database, $sql, $key, $trans_data=array(), $break_if_error=true)
{
    $row = getRowFromSQL($database, $sql, $trans_data, $break_if_error);
    return $row[$key];
}

function getRowFromSQL($database, $sql, $trans_data=array(), $break_if_error=true)
{
    $data = getDataFromSQL($database, $sql, $trans_data, $break_if_error);
    return $data[0];
}

function getDataFromSQL($database, $sql, $trans_data=array(), $break_if_error=true)
{
        $tableau = AfwDatabase::db_recup_rows($sql, true, true, $database);

        $rtab = count($tableau);
        if($rtab>0) $ctab = count($tableau[0]);
        else $ctab = 0;
        
        if(AfwSession::hasOption("SQL_LOG"))
        {
             $information = "<br>\n<pre class='sql hzmlib'><b>DB</b> : $database,\n<b>sql</b> :\n $sql\n <b>rows</b> : $rtab </pre>";
             //throw new AfwRuntimeException($information);
             $_SESSION["analysis_log"].= $information;
        }
        
        return $tableau;
}

function execQuery($database, $query_txt, $titre="", $continueAndSendAlert=false)
{
   // die("je suis la rafik");
   global $analyse_sql, $print_debugg; // $db_arr, 
   
   // $db_arr[$database]->Halt_On_Error="no";
   
   // $query_txt = str_replace('[prefix]', "xx", $query_txt);
   
   $start_q_time = date("Y-m-d H:i:s");
   if($print_debugg) debugg("Start query : $start_q_time");  
   if($print_debugg) debugg("Query : $query_txt");
   //if($print_debugg) debugg("db_arr[$database] = ".var_export($db_arr[$database],true));

   list($res, $project_link_name) =  AfwDatabase::db_query($query_txt, true, true, $database);
   
   if(!$res)
   {
        $alerte = "sql:[$query_txt] ==> " . AfwMysql::get_error(AfwDatabase::getLinkByName($project_link_name));
        
        if($continueAndSendAlert)
        {
                /* @todo fix this
                $user = $db_arr[$database]->User;
                $alerte = "$database/user $user : execution of query : \n $query_txt failed ! \nError : ".$db_arr[$database]->Error."\n";
                if($print_debugg) debugg($alerte);
                internal_alert($alerte);
                //print "debut get_file_details_by_functionName exec_query";
                //list ($id_module,$fichier_appelant,$projet,$alerteTo) = get_file_details_by_functionName("exec_query");
                if(!$alerteTo) $alerteTo = "1602253";
                if(!$id_module) $id_module  = 0;
                if(!$projet) $projet  = 0;                
                
                $id_page = 4000000 + $id_module;
                $alerte_txt = "$alerte Appelée par $fichier_appelant";
                //print "$brVotre administrateur a été informé des paramètres de l'erreur : $projet,$alerte_txt,$alerteTo,$id_page ";
                //insert_alerte($projet,$alerte_txt,$alerteTo,$id_page,false,30);
                $res2 = AfwBatch::emailError("Error happened", $database, $alerte);            
                */
        }
        else
        {
                raiseError($alerte);
        }  
   }

   $end_q_time = date("Y-m-d H:i:s");
   $duree_q = strtotime($end_q_time) - strtotime($start_q_time);
   $row_count = AfwMysql::rows_count($res);
   $affected_rows = AfwMysql::affected_rows(AfwDatabase::getLinkByName($project_link_name));
   
   
   if($print_debugg) debugg("End query : $end_q_time, duree : $duree_q, affected : $affected_rows row(s) \n");  

   if(($analyse_sql=='W') or ($analyse_sql=='Y') or ($duree_q>10))
   {
           $text_time = " ($end_q_time - $start_q_time) : $duree_q s";
           //insert_analyse($query_txt.$text_time,$duree_q);
           $res2 = AfwBatch::emailError("requete lourde", $database, $query_txt.$text_time); 
   }
   
   
   if(AfwSession::hasOption("SQL_LOG"))
   {
       $information = "<br>\n<pre class='sql hzmlib hzmexec'><b>DB</b> : $database,\n<b>sql</b> :\n $query_txt,\n <b>affected</b> : $affected_rows,\n <b>duree</b> : $duree_q</pre>";
       //throw new AfwRuntimeException($information);
       $_SESSION["analysis_log"].= $information;
   }

   return array('result'=>$res, 'affected_rows'=>$affected_rows, 'sql'=>$query_txt);
}

?>