<?php
class AfwDB extends AFWRoot 
{


    public static function getIndexedListValues($data, $keyCol, $valueCol)
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


    public static function getIndexedList($data, $keyCol)
    {
            $new_data = array();
            
            foreach($data as $ir => $row)
            {
                    $key = $row[$keyCol];
                    $new_data[$key] = $row;
            }
            
            return $new_data;
    }

    public static function getDoubleIndexedList($data, $keyCol, $key2Col)
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


    public static function decodeData($data,$decode_arr)
    {
        foreach($data as $ir => $row)
        {
        foreach($row as $nom_champ => $val)
        {
            if($decode_arr[$nom_champ])
            {
                    $data[$ir][$nom_champ] = AfwLanguageHelper::tt($decode_arr["table"].".".$nom_champ.".".$val);
            }
        }
        }
        
        return $data;      
    }


    public static function raiseError($msg, $throwed_arr=array(), $throwed_vals=array())
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
        return self::showError($msg, $call_method = "");
    }

    public static function showError($msg, $call_method = "") 
    {
        global $_POST, $MODE_BATCH;
        
        if($MODE_BATCH) $br = "";
        else $br = "<br>";
            
            if(!$MODE_BATCH) $ob_html = ob_get_clean();
            else $ob_html = "";
            
            if(!$MODE_BATCH) $message = "$msg\n$br <b>Trace :</b> ";
            else $message = "$msg \n Trace : ";
            
            $backtrace = debug_backtrace();
            if(!$MODE_BATCH)
            {
                    $message .= AfwHtmlHelper::htmlBackTrace($backtrace, AfwSession::config("advanced-back-trace",false));
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
            
            $message .= AfwMainPage::getOutput();
            
            die($ob_html.$message);
            trigger_error($message, E_USER_ERROR);
            
        return false;
    }

    public static function getValueFromSQL($database, $sql, $key, $trans_data=array(), $break_if_error=true)
    {
        $row = self::getRowFromSQL($database, $sql, $trans_data, $break_if_error);
        return $row[$key];
    }

    public static function getRowFromSQL($database, $sql, $trans_data=array(), $break_if_error=true)
    {
        $data = self::getDataFromSQL($database, $sql, $trans_data, $break_if_error);
        return $data[0];
    }

    public static function getDataFromSQL($database, $sql, $trans_data=array(), $break_if_error=true)
    {
            global $db_arr, $last_sql, $objme;
            $j=0;
            $heure=date("H:i:s");


            $tableau = AfwDatabase::db_recup_rows($sql, true, true, $database);

            /*
            $sql = str_replace('[prefix]', $db_arr[$database]->prefix, $sql);
            
            $last_sql = $sql;
            
            if(!$db_arr[$database])
            {
                raiseError("database config for system $database is not defined");
            }

            $res = $db_arr[$database]->query($sql);
            
            
            
            if(!$res)
            {
                $there_is_error = true;
                $desc_error = "$br $sql $br SQL Error : ".$db_arr[$database]->Error."\n";
                if($break_if_error)
                {
                    //if($logObj) echo  $logObj->outTree();
                    raiseError($desc_error);
                }
                else
                {
                    $_SES SION["error"] .= $desc_error;
                }   
            }
            
            if($there_is_error)
            {
                $tableau = array();
                $tableau["ERROR"] = true;
                $tableau["ERROR_DESC"] = $desc_error;
            }
            else
            {
                    $nb = $db_arr[$database]->num_fields();
                    $tableau = array(); 
            
                    while($db_arr[$database]->next_record())
                    {   
                            for($i=0;$i<$nb;$i++)
                            {
                                    $nom_champ=mysql_field_name($res,$i);
                                    $tableau[$j][$nom_champ] = stripslashes($db_arr[$database]->f($nom_champ));
                                    if($trans_data[$nom_champ])
                                    {
                                        //if($nom_champ=="field_investigator") echo "trans_data = ".var_export($trans_data,true);
                                        
                                        $val_to_trans = $trans_data["table"].".".$nom_champ.".".$tableau[$j][$nom_champ];
                                        
                                        $tableau[$j][$nom_champ] = t($val_to_trans);
                                        // if($nom_champ=="field_investigator") die("translation of $nom_champ (val : $val_to_trans) = ".$tableau[$j][$nom_champ]);
                                    }
                            }
                            $j++;
                    }
                    
            }*/
            $rtab = count($tableau);
            if($rtab>0) $ctab = count($tableau[0]);
            else $ctab = 0;
            
            if(AfwSession::hasOption("SQL_LOG"))
            {
                $information = "<br>\n<pre class='sql hzmlib'><b>DB</b> : $database,\n<b>sql</b> :\n $sql\n <b>rows</b> : $rtab </pre>";
                //throw new AfwRuntimeException($information);
            }
            
            return $tableau;
            
            
    }

    

    public static function execQuery($database, $query_txt, $titre="", $continueAndSendAlert=false)
    {
        // die("je suis la rafik");
        global $analyse_sql, $print_debugg; // $db_arr, 
        
        // $db_arr[$database]->Halt_On_Error="no";
        
        // $query_txt = str_replace('[prefix]', "xx", $query_txt);
        
        $start_q_time = date("Y-m-d H:i:s");
        // if($print_debugg) debugg("Start query : $start_q_time");  
        // if($print_debugg) debugg("Query : $query_txt");
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
                        self::raiseError($alerte);
                }  
        }

        $end_q_time = date("Y-m-d H:i:s");
        $duree_q = strtotime($end_q_time) - strtotime($start_q_time);
        $row_count = AfwMysql::rows_count($res);
        $affected_rows = AfwMysql::affected_rows(AfwDatabase::getLinkByName($project_link_name));
        
        
        //if($print_debugg) debugg("End query : $end_q_time, duree : $duree_q, affected : $affected_rows row(s) \n");  

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
            // $SE SSION["analysis_log"].= $information;
        }

        return array('result'=>$res, 'affected_rows'=>$affected_rows, 'sql'=>$query_txt);
    }


}
?>