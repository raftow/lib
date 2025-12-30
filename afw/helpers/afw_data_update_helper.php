<?php
class AfwDataUpdateHelper
{
    public static function mail_error_to_administrator($error)
    {
            return AfwBatch::emailError("nicpt_db_update", "AUTOMATIC Update NIC-PT Database", $error);
    }

    public static function throw_error($error, $email_to_admin=false)
    {        
            AfwBatch::print_error($error);
            if($email_to_admin) mail_error_to_administrator($error);
            die();
    }

    public static function get_last_timestamp($dbserver, $table_config, $forced_job_timestamp, $default_timestamp)
    {
            $table_name = $table_config["table_name"];
            $source_tstamp = $table_config["source_timestamp"];
            $query_get_last_timestamp = "select max($source_tstamp) as source_tstamp from $table_name where $source_tstamp <= '$forced_job_timestamp'";

            $source_tstamp = AfwDB::getValueFromSQL($dbserver, $query_get_last_timestamp, "source_tstamp");
            if(!$source_tstamp) $source_tstamp = $default_timestamp;
            
            return $source_tstamp;
    }

    public static function data_update($dbserver, $items, $table_config, $simul = false, $job_param_source_tstamp)
    {
            $table_name = $table_config["table_name"];
            $source_tstamp_field = $table_config["source_timestamp_field"];
            $mapping_cols = $table_config["mapping_cols"];
            $pkey_arr = $table_config["pkey"];
            $pkey2_arr = $table_config["pkey-2"];
            $reset2_offset = $table_config["reset-2-offset"];
            $new_stamp = $job_param_source_tstamp;
            $queries = array();
            $inserted = 0;
            $updated = 0;
            $ignored = 0;
            $errors = [];
            $continueAndSendAlert = true;
            $items_count = count($items);
            foreach($items as $ii => $item)
            {
                    $pkey_cond = "1";
                    foreach($pkey_arr as $pkey_col)
                    {
                            $table_column = $mapping_cols[$pkey_col];
                            //if(strlen($table_column)<10) $table_column = "not mapped [$pkey_col]";
                            if(!$table_column) $table_column = "[$pkey_col]";
                            //$pkey_cond .= " and $table_column = ".$item->$pkey_col.";";
                            $pkey_cond .= " and $table_column = '".$item->$pkey_col."'";
                    }

                    if($pkey2_arr)
                    {
                            $reset_pk2 = "";
                            $pkey2_cond = "1";
                            foreach($pkey2_arr as $pkey2_col)
                            {
                                    $table_column2 = $mapping_cols[$pkey2_col];
                                    //if(strlen($table_column)<10) $table_column = "not mapped [$pkey_col]";
                                    if(!$table_column2) $table_column2 = "[$pkey2_col]";
                                    //$pkey_cond .= " and $table_column = ".$item->$pkey_col.";";
                                    $pkey2_cond .= " and $table_column2 = '".$item->$pkey2_col."'";
                                    $reset_pk2 .= ", $table_column2 = $table_column2 + $reset2_offset";                        
                            }
                            $pkey1_cond = $pkey_cond;
                            $pkey_cond = "(($pkey1_cond) or ($pkey2_cond))";
                    }
                    try
                    {
                            // find if this record already exists
                            $query_timestamp_of_this_record = "select max($source_tstamp_field) as source_tstamp from $table_name \nwhere $pkey_cond";

                            $record_source_tstamp = AfwDB::getValueFromSQL($dbserver, $query_timestamp_of_this_record, "source_tstamp");
                            if(!$record_source_tstamp) $found = false;
                            else $found = true;
                            
                            if($item->source_tstamp > $record_source_tstamp) 
                            {
                                    $recent = true;
                                    if($item->source_tstamp > $new_stamp) $new_stamp = $item->source_tstamp; 
                            }    
                            else $recent = false;
                            
                            $queries[] = array("title"=>"find if this record already exists", "sql"=>$query_timestamp_of_this_record, "type"=>"info");
                            
                            if($found)
                            {
                                    if($recent)
                                    {
                                    // update record
                                    $set_sentence = "";
                                    foreach($mapping_cols as $json_column => $table_column)
                                    {
                                            if(AfwStringHelper::stringContain($json_column,"("))
                                            {
                                                    // it means formula
                                                    if($set_sentence) $set_sentence .= ", ";
                                                    $set_sentence .= "$table_column = ".$json_column;
                                            }
                                            else
                                            {
                                                    if(!isset($item->$json_column))
                                                    {
                                                    
                                                    }
                                                    
                                                    if(!in_array($json_column, $pkey_arr))
                                                    {
                                                            if($set_sentence) $set_sentence .= ", ";
                                                            $set_sentence .= "$table_column = '".addslashes($item->$json_column)."'";
                                                    }
                                            }
                                    }
                                    $jj = $ii+1;
                                    if($pkey2_arr)
                                    {
                                            // This code because when we have 2 unique keys in a table if one of them change it may cause duplicate error and the record 
                                            $title = "delete logically any changed PK values before update record $jj/$items_count";
                                            AfwBatch::print_info($title);
                                            $query_log_del_record = "update $table_name set ROW_VERSION=ROW_VERSION+1, LastOperation='D' $reset_pk2 \nwhere $pkey_cond";
                                            if(!$simul) AfwDB::execQuery($dbserver, $query_log_del_record, $title, $continueAndSendAlert);
                                            $queries[] = array("title"=>$title, "sql"=>$query_log_del_record, "type"=>"warning");
                                    }
                                    
                                    $title = "update record $jj/$items_count";
                                    AfwBatch::print_info($title);
                                    $query_update_record = "update $table_name set $set_sentence, ROW_VERSION=ROW_VERSION+1, LastOperation='U' \nwhere $pkey_cond";
                                    if(!$simul) AfwDB::execQuery($dbserver, $query_update_record, $title, $continueAndSendAlert);
                                    $queries[] = array("title"=>$title, "sql"=>$query_update_record, "type"=>"warning");
                                    $updated++;
                                    }
                                    else
                                    {
                                    $ignored_record = "";
                                    foreach($mapping_cols as $json_column => $table_column)
                                    {
                                            if($ignored_record) $ignored_record .= ", ";
                                            $ignored_record .= "$table_column : ".$item->$json_column;
                                    }
                                    $title = "ignore record";
                                    $queries[] = array("title"=>"$title", "sql"=>"-- $table_name record ignored : $ignored_record ::: API record tmstamp=".$item->source_tstamp . " vs DB record tmstmp : " . $record_source_tstamp.", job_param_timestamp = $job_param_source_tstamp", "type"=>"error");
                                    $ignored++;
                                    }
                            }
                            else
                            {
                                    // insert record
                                    $set_sentence = "";
                                    $title = "insert record"; 
                                    foreach($mapping_cols as $json_column => $table_column)
                                    {
                                            if(AfwStringHelper::stringContain($json_column,"("))
                                            {
                                                    // it means formula
                                                    if($set_sentence) $set_sentence .= ", ";
                                                    $set_sentence .= "$table_column = ".$json_column;
                                            }
                                            else
                                            {
                                                    if(($table_column=="STUDENT_ID") and strlen($item->$json_column)<=3)
                                                    {
                                                            $ignored_record = "";
                                                            foreach($mapping_cols as $json_column => $table_column)
                                                            {
                                                                    if($ignored_record) $ignored_record .= ", ";
                                                                    $ignored_record .= "$table_column : ".$item->$json_column;
                                                            }
                                                            $title = "ignore record";
                                                            $queries[] = array("title"=>"$title", "sql"=>"-- $table_name record ignored : $ignored_record because IDN of student is bad", "type"=>"error");
                                                            $ignored++;  
                                                    }
                                                    else
                                                    {
                                                            if($set_sentence) $set_sentence .= ", ";
                                                            $set_sentence .= "$table_column = '".$item->$json_column."'";
                                                    }                                
                                            }
                                    }
                                    if($title == "insert record")   
                                    {
                                            $query_insert_record = "insert into $table_name set $set_sentence, ROW_VERSION=1, LastOperation='I'";
                                            if(!$simul) AfwDB::execQuery($dbserver, $query_insert_record, $title, $continueAndSendAlert);
                                            $queries[] = array("title"=>$title, "sql"=>$query_insert_record, "type"=>"sql");
                                            $inserted++;
                                    }
                                    
                            }
                    }
                    catch(Exception $e)
                    {
                            $ignored++;
                            $error_title = "For record $pkey_cond Exception : ".$e->getMessage();
                            AfwBatch::print_error($error_title);
                            $errors[] = $error_title;
                    }
                    catch(Error $e)
                    {
                            $error_title = "For record $pkey_cond Error : ".$e->__toString();
                            $ignored++;
                            AfwBatch::print_error($error_title);
                            $errors[] = $error_title; 
                    }
            }
        
            return array($inserted, $updated, $ignored, $queries, $new_stamp, $errors);
    }

    public static function consume_api($apiConfig, $url, $data = null, $recursive=true, $max_tentatives=20, $print_debugg=true, $force_mode=false, $lang="ar", $throwError=true)
    {
            $itemsAttribute = $apiConfig["items-attribute"];
            if(!$itemsAttribute) throw new AfwRuntimeException("items-attribute missed in the apiConfig array : ".var_export($apiConfig, true));
            $inputPageAttribute = $apiConfig["input-page-attribute"];
            if($recursive and !$inputPageAttribute) throw new AfwRuntimeException("in recursive mode we need the page-attribute to be configured in the apiConfig array : ".var_export($apiConfig, true));
            $apiConfigObject = new AfwConfigObject($apiConfig, $data);
            $metaAttribute = $apiConfig["meta-attribute"];
            if(!$metaAttribute) throw new AfwRuntimeException("meta-attribute name missed in the apiConfig array : ".var_export($apiConfig, true));
            $pageCountAttribute = $apiConfig["page-count-attribute"];
            if(!$pageCountAttribute) throw new AfwRuntimeException("page-count-attribute name (to find in meta array) missed in the apiConfig array : ".var_export($apiConfig, true));
            
            $currentPageAttribute = $apiConfig["current-page-attribute"];
            if(!$currentPageAttribute) throw new AfwRuntimeException("we need the current-page-attribute name (to find in meta array) to be configured in the apiConfig array : ".var_export($apiConfig, true));
                     
            $max_pages = $apiConfig["max-pages"];
            if(!$max_pages) $max_pages = 60000;            
            
            if(!$data) $data = array();
            if($max_tentatives<1) $max_tentatives = 1;
            $tentative = 1;
            while($tentative <= $max_tentatives)
            {
                    $result = AfwApiConsumeHelper::runAPI($url, $apiConfigObject, "data", $lang);        
                    if($result['success'] and isset($result['result']->items))
                    {
                            if($print_debugg) AfwBatch::print_debugg("tentative $tentative > url success : ".$result['url']." got record count : ".count($result['result']->items));
                            $tentative= $max_tentatives;
                    }
                    else sleep(45);
                    $tentative++;
            }

            $ok = true;

            if(!$result['success'])
            {
                    $error_msg = "after $max_tentatives tentatives the url : ".$result['url']." failed and  got result : ".var_export($result);
                    AfwBatch::print_error($error_msg);
                    if($throwError) throw new AfwRuntimeException($error_msg);
                    else $ok = false;
            }

            $items = [];

            if($ok)
            {
                $items = $result['result']->itemsAttribute;
                $pageCount = $result['result']->$metaAttribute->$pageCountAttribute;            
                $currentPage = $result['result']->$metaAttribute->$currentPageAttribute;
                $page=$currentPage;
                if($recursive)
                {                    
                        if($pageCount>$currentPage+$max_pages-1)
                        {
                            if(!$force_mode)
                            {
                                    throw new AfwRuntimeException("too much pages ($pageCount) max of page $max_pages please use force mode (to-implement)");
                            }
                            $pageCount = $currentPage+$max_pages-1;
                        }

                        for($page=$currentPage+1;$page<=$pageCount;$page++)
                        {
                                $apiConfigObject->setData($inputPageAttribute, $page);
                                $tentative = 1;
                                while($tentative <= $max_tentatives)
                                {
                                        $result_2 = AfwApiConsumeHelper::runAPI($url, $apiConfigObject, "data", $lang);                                            
                                        if($result_2['success'] and isset($result_2['result']->itemsAttribute))
                                        {
                                                $items_2 = $result_2['result']->itemsAttribute;
                                                // die(var_export($result_2,true));
                                                // echo "merging \n";
                                                if(!is_array($items)) $items = [];
                                                // $before = count($items);
                                                if(!is_array($items_2)) $items_2 = [];
                                                // $new = count($items_2);
                                                $items = array_merge($items,$items_2);
                                                $after = count($items);
                                                if($print_debugg) AfwBatch::print_debugg("tentative $tentative > url success : ".$result_2['url']." (page $page/$pageCount) got record count : ".count($result_2['result']->items)." after merge : $after record(s)");
                                                $tentative = $max_tentatives;
                                        }  
                                        $tentative++;
                                }

                                if(!$result_2['success'])
                                {
                                        $error_msg = "Processing page $page/$pageCount after $max_tentatives tentatives the url : ".$result_2['url']." failed and  got result : ".var_export($result_2);
                                        AfwBatch::print_error($error_msg);
                                        if($throwError) throw new AfwRuntimeException($error_msg);
                                        else $ok = false;
                                        break;
                                }
                            
                            
                            //echo "merged : $before+$new = $after\n";
                        }
                }
            
            }
            
            
            return array("ok" =>$ok, "current_page"=>$page, "last_page"=>$pageCount, "items"=>$items);

    }
}
?>