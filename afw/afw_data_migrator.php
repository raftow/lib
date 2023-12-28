<?php

class AfwDataMigrator extends AFWRoot {


    public static function migrateData($migration_config, $partition=0, $phase="", $lang="ar", $returnLog=false, $forced_print_full_debugg=true)
    {
        global $tab_instances;
        $source = $migration_config["source"];
        $destination = $migration_config["destination"];
        $updateProgressFrequency = $migration_config["updateProgressFrequency"];
        $updateProgressQuery = $migration_config["updateProgressQuery"];
        $updateProgressField = $migration_config["updateProgressField"];
        
        $destinationClass = $migration_config["destinationClass"];
        $destination_table  = $migration_config["destination_table"];
        $destination_db = $migration_config["destination_db"];
        $destination_pk_cols  = $migration_config["destination_pk"];
        $destination_cols  = $migration_config["destination_cols"];
        $last_update_query  = $migration_config["last_update_query"];
        $skip_existing  = $migration_config["skip_existing"];
        $stop_on_error  = $migration_config["stop_on_error"];
        $skip_existing_except_advanced_method  = $migration_config["skip_existing_except_advanced_method"];
        $disable_tab_instances_log  = $migration_config["disable_tab_instances_log"];
        if($last_update_query)
        {
            $last_update_value = AfwDB::getValueFromSQL($destination, $last_update_query, "val");
        }

        $sql_data_from = $migration_config["sql_data_from"];
        $sql_data_from = str_replace("[timestamp]",date("Y-m-d H:i:s"),$sql_data_from);
        $sql_data_from = str_replace("[partition]",$partition,$sql_data_from);
        $sql_data_from = str_replace("[phase]",$phase,$sql_data_from);
        $sql_data_from = str_replace("[last_update]",$last_update_value,$sql_data_from);
        
        $log_arr = array();

        $source_hostname = AfwSession::config("$source"."host","");
        $source_username = AfwSession::config("$source"."user","");
        $source_database = AfwSession::config("$source"."database","");

        $destination_hostname = AfwSession::config("$destination"."host","");
        $destination_username = AfwSession::config("$destination"."user","");
        $destination_database = AfwSession::config("$destination"."database","");

        $log2_arr = [];
        $log2_arr[] = date("Y-m-d H:i:s").">> DB Source $source params are [$source_hostname, $source_username, $source_database]";
        $log2_arr[] = date("Y-m-d H:i:s").">> DB Destination $destination params are [$destination_hostname, $destination_username, $destination_database]";
        $log2_arr[] = date("Y-m-d H:i:s").">> sql to execute is $sql_data_from";


        if($returnLog) 
        {
            array_push($log_arr, $log2_arr);
        }
        else AfwBatch::print_warning(implode("<br>\n",$log2_arr));

        if($destinationClass and $migration_config["startByReset"])
        {
            $whereReset = $migration_config["startByReset"];
            /*
            list($found, $path, $failed_loadings_log_arr) = AfwAutoloader::getClassPath($destinationClass); 
            if(!$found) die("not found".implode("<br>\n",$failed_loadings_log_arr));
            else die("$destinationClass found in $path");*/

            $nb_resetted = $destinationClass::logicDeleteWhere($whereReset);
            $res_log = date("Y-m-d H:i:s")."> $nb_resetted record(s) disabled";
            if($returnLog) $log_arr[] = $res_log; 
            else AfwBatch::print_warning($res_log);
        }
        $data = AfwDB::getDataFromSQL($source,$sql_data_from);
        
        // $rowMigratedLog = array();
        $created_count = 0;
        $updated_count = 0;
        $skipped_count = 0;
        $error_count = 0;
        $mem_picture_counter = 0;
        $total_count = count($data);
        $to_update_prgress = false;

        $log_arr[] = "getDataFromSQL($source,$sql_data_from) => $total_count record(s)";

        $updateProgressField_value = "";

        foreach($data as $irow => $row)
        {
            try
            {
                $log = "";
                // die("stopped by rafik-1 when migrateData for row=".var_export($row,true));
                if($total_count>0) $progress = floor(100*$irow/$total_count);
                else $progress = 100;
                if($destinationClass) 
                {
                    list($created, $updated, $skipped, $log) = self::migrateRow($row, $migration_config["mapping"], $destinationClass, $lang, $migration_config["timestamp_field"], $migration_config["startByReset"], $migration_config["destinationLoadMethod"], $forced_print_full_debugg, $skip_existing, $skip_existing_except_advanced_method);
                    if(!$log) $log = "[$irow/$total_count $progress%] self::migrateRow(row, migration_config[mapping], destinationClass, ...) returned no log";
                    else $log = "[$irow/$total_count $progress%] $log";
                }
                elseif($destination_table)
                {
                    // AfwBatch::print_warning("going to exec migrateRecord(\$row, \$mapping, destination=$destination, destination_db=$destination_db, destination_table=$destination_table,...)");
                    list($created, $updated, $skipped, $log) = self::migrateRecord($row, $migration_config["mapping"], $destination, $destination_db, $destination_table, $lang, $destination_pk_cols, $destination_cols,  $last_update_value, $forced_print_full_debugg);
                    if(!$log) $log = "[$irow/$total_count $progress%] self::migrateRecord(row, migration_config[mapping], $destination, $destination_db, $destination_table, $lang, ...) has returned no log";
                    else $log = "[$irow/$total_count $progress%] $log";
                } 
                if($created) $created_count++;
                if($updated) $updated_count++;
                if($skipped) $skipped_count++;

                if($updateProgressField_value != $row[$updateProgressField])
                {
                    $updateProgressField_value = $row[$updateProgressField];
                    $to_update_prgress = true;
                }

                if($progress>=100)
                {
                    $to_update_prgress = true;
                }

                if($destinationClass)
                {
                    $mem_picture_counter++;

                    if($mem_picture_counter==$updateProgressFrequency)
                    {
                        $to_update_prgress = true;
                        if(!$disable_tab_instances_log)
                        {
                            $res_log = "> tab_instances >> ".var_export($tab_instances,true);
                            AfwBatch::print_warning($res_log);
                            unset($res_log);
                        }                    
                        

                        $mem_picture_counter = 0;
                    }
                }

                if($updateProgressQuery and $to_update_prgress)
                {
                    $updateProgressQuery_exec = str_replace("[partition]",$partition,$updateProgressQuery);
                    $updateProgressQuery_exec = str_replace("[phase]",$phase,$updateProgressQuery_exec);
                    $updateProgressQuery_exec = str_replace("[progress]",$progress,$updateProgressQuery_exec);
                    $updateProgressQuery_exec = str_replace("[$updateProgressField]",$updateProgressField_value,$updateProgressQuery_exec);
                    AfwDB::execQuery($destination,$updateProgressQuery_exec);
                }
            }
            catch(Exception $e)
            {
                $log .= " $destination_db/$destinationClass/$destination_table Exception happened on record : ".var_export($row,true)."\n The message is ".$e->getMessage();
                $res_log = " log >> ".$log;
                if($returnLog) $log_arr[] = $res_log; 
                else AfwBatch::print_debugg($res_log);
                $error_count++;
                if($stop_on_error) break;
            }
            catch(Error $e)
            {
                $log .= " $destination_db/$destinationClass/$destination_table Error happened on record : ".var_export($row,true)."\n The error message is ".$e->__toString();
                $res_log = " log >> ".$log;
                if($returnLog) $log_arr[] = $res_log; 
                else AfwBatch::print_debugg($res_log);
                $error_count++;
                if($stop_on_error) break;
            } 

            
            if($log)
            {
                    $res_log = " log >> ".$log;
                    if($returnLog) $log_arr[] = $res_log; 
                    else AfwBatch::print_debugg($res_log);
                    unset($res_log);
            }
            // $rowMigratedLog[] = $res_log;            
            unset($log);
        }

        if($updateProgressQuery and (($error_count==0) or (!$stop_on_error)))
        {
            $updateProgressQuery_exec = str_replace("[partition]",$partition,$updateProgressQuery);
            $updateProgressQuery_exec = str_replace("[phase]",$phase,$updateProgressQuery_exec);
            $updateProgressQuery_exec = str_replace("[progress]",100,$updateProgressQuery_exec);
            $updateProgressQuery_exec = str_replace("[$updateProgressField]",$updateProgressField_value,$updateProgressQuery_exec);
            AfwDB::execQuery($destination,$updateProgressQuery_exec);
        }

        if($destinationClass)
        {
            $res_log = " tab_instances after $sql_data_from >> ".var_export($tab_instances,true);
            if($returnLog) $log_arr[] = $res_log; 
            else AfwBatch::print_warning($res_log);
            unset($res_log);
        }

        $result_log = "created : $created_count, updated : $updated_count errors : $error_count skipped : $skipped_count total data : $total_count";

        if($returnLog) 
        {
            $log_arr[] = $result_log;

            $log_body = implode("@@<br>\n",$log_arr);
        }
        else
        {
            AfwBatch::print_warning($result_log);
            $log_body = "";
        }

        // $all_log = implode("\n",$rowMigratedLog);
        // unset($rowMigratedLog);
        return array('all_count' => count($data),'created_count' => $created_count, 'updated_count' => $updated_count, 'skipped_count' => $skipped_count, 'errors'=> $error_count, 'log' => $log_body);
    }


    public static function migrateRecord($row, $mapping, $destination, $destination_db, $destination_table, $lang, $destination_pk_cols, $destination_cols,  $last_update_value, $forced_print_full_debugg)
    {
        $rowMapped = self::mapAndTransformRow($row, $mapping, "", $forced_print_full_debugg);
        global $print_full_debugg;
        if($print_full_debugg)
        {
            AfwBatch::print_debugg("row=".var_export($row,true).", rowMapped=".var_export($rowMapped,true));             
        }
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $log = "";
        $sql_wheres = array();
        foreach($destination_pk_cols as $destination_pk_col)
        {
            $destination_pk_val = addslashes($rowMapped[$destination_pk_col]);
            $sql_wheres[] = "$destination_pk_col = '$destination_pk_val'";            
        }

        $sql_exists = "SELECT count(*) AS val FROM $destination_db.$destination_table WHERE " . implode(" AND ", $sql_wheres);

        $exists = getValueFromSQL($destination, $sql_exists, "val");

        if($exists>0)
        {
            $sql_migrate = "UPDATE $destination_db.$destination_table SET ";
            $sql_updates = array();
            foreach($destination_cols as $destination_col)
            {
                $destination_val = addslashes($rowMapped[$destination_col]);
                $sql_updates[] = "$destination_col = '$destination_val'";
            }

            $sql_migrate .= implode(",", $sql_updates);
            $sql_migrate .= " WHERE ";
            $sql_migrate .= implode(" AND ", $sql_wheres);
            $updated++;
        }
        else
        {
            $created++;

            $sql_migrate = "INSERT INTO $destination_db.$destination_table SET ";
            $sql_updates = array();

            foreach($destination_pk_cols as $destination_col)
            {
                $destination_val = addslashes($rowMapped[$destination_col]);
                $sql_updates[] = "$destination_col = '$destination_val'";
            }

            foreach($destination_cols as $destination_col)
            {
                $destination_val = addslashes($rowMapped[$destination_col]);
                $sql_updates[] = "$destination_col = '$destination_val'";
            }



            $sql_migrate .= implode(",", $sql_updates);
        }


        $res = execQuery($destination, $sql_migrate);

        $log = $res['affected_rows']." row(s)";

        return array($created, $updated, $skipped, $log);

    }


    public static function migrateRow($row, $mapping, $destinationClass, $lang="ar", $timestamp_field="", $startByReset=false, $destinationLoadMethod="", $returnLog=false, $skip_existing=false, $skip_existing_except_advanced_method=false, $advanced_log=false)
    {
        
        if($returnLog) ob_start();       
        $rowMapped = self::mapAndTransformRow($row, $mapping, $destinationClass, $returnLog);
        if(!$destinationLoadMethod) $destinationLoadMethod = "loadFromRow";
        $destinationObj = $destinationClass::$destinationLoadMethod($rowMapped);
        if($returnLog) $log_initial = ob_get_clean();
        $created = false;
        $updated = false;
        $skipped = true;
        if(!$destinationObj) return array($created, $updated, $skipped, "skipped : object can't be loaded from ".var_export($rowMapped,true));
        $log_arr = array();
        $log_arr[] = $log_initial;
        $affected_row_count = 0;
        if((!$destinationObj->is_new) and $skip_existing)
        {
            $log_arr[] = "skipped : $destinationClass exists already and option skip_existing is enabled";
        }
        else
        {
            foreach($mapping as $colToMap => $mappingCol)
            {
                $colMapped = $mappingCol["field"];
                if(!$mappingCol["onlyIfNewObject"]) $mappingCol["onlyIfNewObject"] = $mappingCol["onlyIfEmpty"]; // same sens, this is better name
                if((!$mappingCol["onlyIfNewObject"]) or $destinationObj->is_new or $destinationObj->isConsideredEmpty())
                {
                    
                    $value = $rowMapped[$colMapped];
                    if($value or (!$mappingCol["onlyIfFilling"]))
                    {
                        // 
                        $method = $mappingCol["method"];
                        $methodAdvanced = $mappingCol["methodAdvanced"];
                        if(($destinationObj->is_new) or (!$skip_existing_except_advanced_method) or $methodAdvanced)
                        {
                            if($method)
                            {
                                if($method != "nothing")
                                {
                                    $log = $destinationObj->$method($value);                            
                                    if(!$log) $log = "WARNING : destinationClass($destinationClass)->$method($value) returned no log";
                                    elseif($method == "decodeName") $log = "WARNING : destinationClass($destinationClass)->$method($value) returned : $log";
                                }
                                else
                                {
                                    if($advanced_log) $log = "method for [$colToMap/$colMapped] is nothing so skipped,";
                                }
                                
                            }
                            elseif($methodAdvanced and ($methodAdvanced != "nothing"))
                            {                
                                $log = $destinationObj->$methodAdvanced($rowMapped);
                            }
                            else
                            {                    
                                $destinationObj->set($colMapped, $value);
                                if($advanced_log) $log = "$colMapped setted to value $value";
                            }
                        }
                        else
                        {
                            $method_used = $method ? $method : "set";
                            if($advanced_log) $log = "$method_used method skipped because skip_existing_except_advanced_method enabled and '$method_used' is not advanced method";
                        }
                        $log_arr[] = $log;
                    }
                    else
                    {
                        if($advanced_log) $log_arr[] = "work for the column [$colToMap/$colMapped] is skipped because we are unfilling the column when onlyIfFilling option is on";
                    }
                }
                else
                {
                    if($advanced_log) $log_arr[] = "work for the column [$colToMap/$colMapped] is skipped because we are inside a non empty object when onlyIfNewObject=onlyIfEmpty option is on";
                }
            }

            // 
            if(!$skip_existing_except_advanced_method)
            {
                $creation_datetime_greg = $destinationObj->getTimeStampFromRow($row,"create", $timestamp_field);
                if($creation_datetime_greg) $destinationObj->CREATION_DATE_val = $creation_datetime_greg;
                else $log_arr[] = "Warning : creation datetime value not found in row : ".var_export($row,true);
                $update_datetime_greg = $destinationObj->getTimeStampFromRow($row,"update", $timestamp_field);
                if($update_datetime_greg) $destinationObj->UPDATE_DATE_val = $update_datetime_greg;        
                else $log_arr[] = "Warning : creation datetime value not found in row : ".var_export($row,true);
                $reallyUpdated = $destinationObj->reallyUpdated($startByReset);
                list($query, $fields_updated) = AfwSqlHelper::getSQLUpdate($destinationObj, 1, 2, $destinationObj->id);
                if($advanced_log) $log_arr[] = "query=$query, fields_updated=".var_export($fields_updated,true);
                $affected_row_count = $destinationObj->commit();
                if((!$affected_row_count) and $advanced_log) $log_arr[] = "affected row count : 0 , ".$destinationObj->debugg_reason_non_update;
            }
            
            
        }
        /*
        become heavy script all recoprds are always updated even if no change
        use set_creation_date instead of force_creation_date etc.

        if($destinationObj->is_new)
        {
            $update_datetime_greg = $destinationObj->getTimeStampFromRow($row,"create", $timestamp_field);
            if($update_datetime_greg) $destinationObj->force_creation_date($update_datetime_greg);
            else $log_arr[] = "Warning : creation datetime value not found in row : ".var_export($row,true);
        }
        else
        {
            $update_datetime_greg = $destinationObj->getTimeStampFromRow($row,"update", $timestamp_field);
            if($update_datetime_greg) $destinationObj->force_update_date($update_datetime_greg);
            else $log_arr[] = "Warning : creation datetime value not found in row : ".var_export($row,true);
        }
        */



        if($destinationObj->is_new) { $created = true; $skipped = false; }
        elseif($affected_row_count and $reallyUpdated) { $updated = true; $skipped = false; }
        else $skipped = true;

        $theLog = $destinationObj->getDisplay($lang)." : ".implode("\n -> ",$log_arr);
        if(!$returnLog)
        {
            AfwBatch::print_info($theLog);
            $theLog = "";
        }

        unset($destinationObj);
        unset($rowMapped);
        unset($log_arr);
        return array($created, $updated, $skipped, $theLog);

    }

    public static function xtrimer($value)
    {
        $result = trim($value);
        $result_arr = explode("-",$result);
        if(count($result_arr)>1)
        {
            if(is_numeric($result_arr[count($result_arr)-1]))
            {
                unset($result_arr[count($result_arr)-1]);
            }
        }

        $result = implode("-",$result_arr);

        return array(true, $result);
    }
    

    public static function trimer($value)
    {
        return array(true, trim($value));
    }

    public static function fixHijri($value)
    {
        list($y,$m, $d) = explode("-",$value);
        if(!$m or !$d)
        {
            list($y,$m, $d) = explode("/",$value);
        }

        $y = trim($y);
        $y = trim($y,".");
        
        $m = trim($m);
        $m = trim($m,".");

        $d = trim($d);
        $d = trim($d,".");


        $y = intval($y);
        $m = intval($m);
        $d = intval($d);

        if($m>12) 
        {
            $tmp = $m;
            $m = $d;
            $d = $tmp;
        }

        if(($y < 31) and ($d>1200)) 
        {
            $tmp = $y;
            $y = $d;
            $d = $tmp;
        }



        if(($y<1200) or ($y>1500))
        {
            return array(false, $value);
        }

        if(($m<1) or ($m>12))
        {
            return array(false, $value);
        }

        if(($d<1) or ($d>30))
        {
            return array(false, $value);
        }

        if($d<10) $d = "0".$d;
        if($m<10) $m = "0".$m;

        return array(true, "$y-$m-$d");
    }

    public static function fixHijriWithConvert($value)
    {
        list($success, $hdate, $gdate) = self::fixHijriOrMiladi($value, $convertHijri=true,$convertMiladi=false);

        return [$success, $hdate];
    }

    public static function fixMiladiWithConvert($value)
    {
        list($success, $hdate, $gdate) = self::fixHijriOrMiladi($value, $convertHijri=false,$convertMiladi=true);

        return [$success, $gdate];
    }

    public static function fixHijriOrMiladi($value, $convertHijri=true,$convertMiladi=true)
    {
        $gdate = null;
        
        list($success, $hdate) = self::fixHijri($value);
        if($success and $convertMiladi)
        {
            $gdate = AfwDateHelper::hijriToGreg($hdate);
        }
        elseif(!$success)
        {
            $hdate = null;
        }

        if(!$gdate)
        {
            list($success, $gdate) = self::fixMiladi($value);
            if($success and (!$hdate) and $convertHijri)
            {
                $hdate = AfwDateHelper::gregToHijri($gdate);
            }
            elseif(!$success)
            {
                $gdate = null;
            }
        }

        $success = (((!$convertHijri) or $hdate) and ((!$convertMiladi) or $gdate));

        return array($success, $hdate, $gdate);
        
    }

    public static function fixMiladi($value)
    {
        list($y,$m, $d) = explode("-",$value);

        if(!$m or !$d)
        {
            list($y,$m, $d) = explode("/",$value);
        }

        

        $y = trim($y);
        $y = trim($y,".");
        
        $m = trim($m);
        $m = trim($m,".");

        $d = trim($d);
        $d = trim($d,".");
        

        $y = intval($y);
        $m = intval($m);
        $d = intval($d);


        if(($y < 32) and ($d > 1900))
        {
            $tmp = $y;
            $y = $d;
            $d = $tmp;
        }

        if($m > 12)
        {
            $tmp = $m;
            $m = $d;
            $d = $tmp;
        }

        if(($y<1900) or ($y>2050))
        {
            return array(false, $value);
        }

        if(($m<1) or ($m>12))
        {
            return array(false, $value);
        }

        if(($d<1) or ($d>31))
        {
            return array(false, $value);
        }

        if($d<10) $d = "0".$d;
        if($m<10) $m = "0".$m;

        return array(true, "$y-$m-$d");
    }

    public static function fixInteger($value)
    {
        if($value==="") return array(false,  $value);
        if($value===null) return array(false,  $value);
        if(!is_numeric($value)) return array(false,  $value);
        $value = intval($value);

        return array(true, $value);
    }


    public static function fixTelephone($value)
    {
        $mobile_num = AfwFormatHelper::formatMobile($value);
        if(strlen($mobile_num) == 10) return array(true, $mobile_num);
        else return array(false, $value);
    }

    public static function decodeIDNType($value)
    {
        list($idn_correct, $idn_type_id) = AfwFormatHelper::getIdnTypeId($value);
        if($idn_correct)
        {
            return array(true, $idn_type_id);
        } 
        else return array(false, $value);
    }

    public static function decodeCountryOrNationality($value)
    {
        if($value) list($country_id,) = Country::getCountryIdFromName($value);
        else $country_id = 0;
        return array(($country_id>0), $country_id);
    }

    public static function extractMiladiYear($value)
    {
        if($value) list($year,) = explode("-",$value);
        else $year = 0;
        return array(($year>0), $year);
    }

    public static function mapAndTransformRow($row, $mapping, $destinationClass, $forcd_print_full_debugg=false, $advanced_log=false)
    {
        global $print_full_debugg;
        $rowMapped = array();
        // die("rafik mapAndTransformRow debugg mapping=".var_export($mapping,true));
        foreach($mapping as $colToMapX => $mappingCol)
        {
            list($colToMap,$colToMapX2) = explode("-",$colToMapX);
            $value = $row[$colToMap];
            $old_value = $value;
            $transformation = $mappingCol["transformation"];
            $transformation2 = $mappingCol["transformation2"];
            $transformationClass = $mappingCol["transformationClass"];            
            $transformation2Class = $mappingCol["transformation2Class"];            
            if(!$transformationClass) $transformationClass = "AfwDataMigrator";
            if(!$transformation2Class) $transformation2Class = "AfwDataMigrator";

            $destinationClass = $mappingCol["destinationClass"];
            $transformationDestination = $mappingCol["transformationDestination"];
            $transformationDestinationClass = $mappingCol["transformationDestinationClass"];
            if(!$transformationDestinationClass) $transformationDestinationClass = $destinationClass;
            if(!$transformationDestinationClass) $transformationDestinationClass = $transformationClass;
            
            $no_transformation = true;

            
            if($transformation and $transformationClass)
            {
                list($transformed, $value) = $transformationClass::$transformation($value);
                if($advanced_log and ($print_full_debugg or $forcd_print_full_debugg))
                {
                    AfwBatch::print_debugg("[$print_full_debugg or $forcd_print_full_debugg] colToMap=$colToMap : $old_value => $transformationClass::$transformation => $value"); 
                }
                if($transformed) $no_transformation = false;
            }

            if($transformation2 and $transformation2Class)
            {
                list($transformed, $value) = $transformation2Class::$transformation2($value);
                if($advanced_log and ($print_full_debugg or $forcd_print_full_debugg))
                {
                    AfwBatch::print_debugg("[$print_full_debugg or $forcd_print_full_debugg] colToMap=$colToMap : $old_value => $transformation2Class::$transformation2 => $value"); 
                }
                if($transformed) $no_transformation = false;
            }

            if($transformationDestination and $transformationDestinationClass)
            {
                list($transformed, $value) = $transformationDestinationClass::$transformationDestination($value);
                if($advanced_log and ($print_full_debugg or $forcd_print_full_debugg))
                {
                    AfwBatch::print_debugg("[$print_full_debugg or $forcd_print_full_debugg] colToMap=$colToMap : $old_value => $transformationDestinationClass::$transformationDestination => $value"); 
                }
                if($transformed) $no_transformation = false;
            }
            
            if($no_transformation)
            {
                if($advanced_log and ($transformation or $transformation2 or $transformationDestination)) AfwBatch::print_debugg("no transformation done : transformation=$transformation/transformationClass=$transformationClass/transformationDestination=$transformationDestination/transformationDestinationClass=$transformationDestinationClass"); 
                $transformed = true; // because transformation = nothing
            }
            $colMapped = $mappingCol["field"];
            if($transformed) $rowMapped[$colMapped] = $value;

            $transformationAdvanced = $mappingCol["transformationAdvanced"];
            $transformationAdvancedClass = $mappingCol["transformationAdvancedClass"];
            if($transformationAdvanced and $transformationAdvancedClass)
            {
                $rowMapped_old = $rowMapped;
                $rowMapped = $transformationAdvancedClass::$transformationAdvanced($rowMapped, $row);
                if($mappingCol["transformationAdvancedStopAndDebugg"])
                {
                    die("debugg rafik-3 rowMapped before $transformationAdvanced row=".var_export($rowMapped_old,true)."\n after $transformationAdvanced rowMapped=".var_export($rowMapped,true));
                }
            }

            
        }

        //die("debugg rafik-2 rowMapped=".var_export($rowMapped,true));

        return $rowMapped;
    }
}
