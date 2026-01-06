<?php

class AfwDataUpdateHelper
{
    public static function mail_error_to_administrator($error)
    {
        return AfwBatch::emailError('nicpt_db_update', 'AUTOMATIC Update NIC-PT Database', $error);
    }

    public static function throw_error($error, $email_to_admin = false)
    {

        AfwBatch::print_error($error);
        if ($email_to_admin) {
            mail_error_to_administrator($error);
        }

        die();
    }

    public static function get_last_timestamp($dbserver, $table_config, $forced_job_timestamp, $default_timestamp)
    {
        $table_name             = $table_config['table_name'];
        $destination_timestamp_field = $table_config['destination_timestamp_field'];
        if (! $table_name) {
            throw new AfwRuntimeException('to execute get_last_timestamp table_name should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $destination_timestamp_field) {
            throw new AfwRuntimeException('to execute get_last_timestamp destination_timestamp_field should be provided see your table config : ' . var_export($table_config, true));
        }

        $query_get_last_timestamp = "select max($destination_timestamp_field) as dest_tstamp from $table_name where $destination_timestamp_field <= '$forced_job_timestamp'";

        $dest_tstamp = AfwDB::getValueFromSQL($dbserver, $query_get_last_timestamp, 'dest_tstamp');
        if (! $dest_tstamp) {
            $dest_tstamp = $default_timestamp;
        }

        return $dest_tstamp;
    }

    public static function data_update($dbserver, $items, $table_config, $simul = false, $job_param_source_tstamp)
    {
        $table_name             = $table_config['table_name'];
        $source_api_timestamp_field = $table_config['source_api_timestamp_field'];
        $source_api_deleted_at_field = $table_config['source_api_deleted_at_field'];
        $destination_timestamp_field = $table_config['destination_timestamp_field'];

        $mapping_cols        = $table_config['mapping_cols'];
        if((!$mapping_cols[$source_api_timestamp_field]) or ($mapping_cols[$source_api_timestamp_field] != $destination_timestamp_field))
        {
            throw new AfwRuntimeException('to execute standard etl data_update mapping TSTMP source and destination should be provided correctly see your table config : ' . var_export($table_config, true));
        }
        $pkey_arr            = $table_config['pkey'];
        $rowVersionCol       = $table_config['rowVersionCol'];
        $lastOperationCol    = $table_config['lastOperationCol'];
        $lastOperationValues = $table_config['lastOperationValues'];
        $lastOperationInsert = $lastOperationValues['insert'];
        $lastOperationUpdate = $lastOperationValues['update'];
        $lastOperationDelete = $lastOperationValues['delete'];

        if (! $table_name) {
            throw new AfwRuntimeException('to execute standard etl data_update table_name should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $source_api_timestamp_field) {
            throw new AfwRuntimeException('to execute standard etl data_update source_api_timestamp_field should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $destination_timestamp_field) {
            throw new AfwRuntimeException('to execute standard etl data_update destination_timestamp_field should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $mapping_cols) {
            throw new AfwRuntimeException('to execute standard etl data_update mapping_cols should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $pkey_arr) {
            throw new AfwRuntimeException('to execute standard etl data_update pkey should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $rowVersionCol) {
            throw new AfwRuntimeException('to execute standard etl data_update rowVersionCol should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $lastOperationCol) {
            throw new AfwRuntimeException('to execute standard etl data_update lastOperationCol should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $lastOperationValues) {
            throw new AfwRuntimeException('to execute standard etl data_update lastOperationValues should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $lastOperationInsert) {
            throw new AfwRuntimeException('to execute standard etl data_update lastOperationInsert should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $lastOperationUpdate) {
            throw new AfwRuntimeException('to execute standard etl data_update lastOperationUpdate should be provided see your table config : ' . var_export($table_config, true));
        }

        if (! $lastOperationDelete) {
            throw new AfwRuntimeException('to execute standard etl data_update lastOperationDelete should be provided see your table config : ' . var_export($table_config, true));
        }

        $pkey2_arr            = $table_config['pkey-2'];
        $reset2_offset        = $table_config['reset-2-offset'];
        $new_stamp            = $job_param_source_tstamp;
        $queries              = [];
        $inserted             = 0;
        $updated              = 0;
        $ignored              = 0;
        $errors               = [];
        $continueAndSendAlert = true;
        $items_count          = count($items);
        foreach ($items as $ii => $item) {
            $pkey_cond = '1';
            foreach ($pkey_arr as $pkey_col) {
                $table_column = $mapping_cols[$pkey_col];
                //if ( strlen( $table_column )<10 ) $table_column = "not mapped [$pkey_col]";
                if (! $table_column) {
                    $table_column = "PK-COL-NOT-MAPPED[$pkey_col]";
                }

                //$pkey_cond .= " and $table_column = ".$item->$pkey_col.';';
                $pkey_cond .= " and $table_column = '" . $item->$pkey_col . "'";
            }
            /*
            if ($pkey2_arr) {
                $reset_pk2  = '';
                $pkey2_cond = '1';
                foreach ($pkey2_arr as $pkey2_col) {
                    $table_column2 = $mapping_cols[$pkey2_col];
                    //if ( strlen( $table_column )<10 ) $table_column = "not mapped [$pkey_col]";
                    if (! $table_column2) {
                        $table_column2 = "[$pkey2_col]";
                    }

                    //$pkey_cond .= " and $table_column = ".$item->$pkey_col.';';
                    $pkey2_cond .= " and $table_column2 = '" . $item->$pkey2_col . "'";
                    $reset_pk2 .= ", $table_column2 = $table_column2 + $reset2_offset";

                }
                $pkey1_cond = $pkey_cond;
                $pkey_cond  = "(($pkey1_cond) or ($pkey2_cond))";
            }*/
            try
            {
                // find if this record already exists
                $query_timestamp_of_this_record = "select max($destination_timestamp_field) as dest_tstamp from $table_name \nwhere $pkey_cond";
                $not_recent_reason = "";
                $record_dest_tstamp = AfwDB::getValueFromSQL($dbserver, $query_timestamp_of_this_record, 'dest_tstamp');
                if (! $record_dest_tstamp) {
                    $found = false;  
                    $recently_updated = false;                  
                } else {
                    $found = true;
                    if ($item->$source_api_timestamp_field > $record_dest_tstamp) {
                        $recently_updated = true;
                        if ($item->$source_api_timestamp_field > $new_stamp) {
                            $new_stamp = $item->$source_api_timestamp_field;
                        }

                    } else {
                        $not_recent_reason = $item->$source_api_timestamp_field ." > " . $record_dest_tstamp;
                        $recently_updated = false;
                    }
                }

                

                $queries[] = ['title' => 'find if this record already exists', 'sql' => $query_timestamp_of_this_record, 'type' => 'info'];

                if ($found) {
                    AfwBatch::print_info("record found : $pkey_cond");
                    if ($recently_updated) {
                        // update record
                        $set_sentence = '';
                        foreach ($mapping_cols as $json_column => $table_column) {
                            if (AfwStringHelper::stringContain($json_column, '(')) {
                                // it means formula
                                if ($set_sentence) {
                                    $set_sentence .= ', ';
                                }

                                $set_sentence .= "$table_column = " . $json_column;
                            } else {
                                if (! isset($item->$json_column)) {

                                }

                                if (! in_array($json_column, $pkey_arr)) {
                                    if ($set_sentence) {
                                        $set_sentence .= ', ';
                                    }

                                    $set_sentence .= "$table_column = '" . addslashes($item->$json_column) . "'";
                                }
                            }
                        }
                        $jj = $ii + 1;
                        /*
                        if ( $pkey2_arr )
                        {
                            // This code because when we have 2 unique keys in a table if one of them change it may cause duplicate error and the record
                            $title = "delete logically any changed PK values before update record $jj/$items_count";
                            AfwBatch::print_info( $title );
                            $query_log_del_record = "update $table_name set $rowVersionCol=$rowVersionCol+1, $lastOperationCol='$lastOperationDelete' $reset_pk2 \nwhere $pkey_cond";
                            if ( !$simul ) AfwDB::execQuery( $dbserver, $query_log_del_record, $title, $continueAndSendAlert );
                            $queries[] = array( 'title'=>$title, 'sql'=>$query_log_del_record, 'type'=>'warning' );
                        }
                        */

                        $title = "update record $jj/$items_count";
                        if($item->$source_api_deleted_at_field)
                        {
                            $lastOperationHere = $lastOperationDelete;
                        }
                        else
                        {
                            $lastOperationHere = $lastOperationUpdate;
                        }
                        AfwBatch::print_info($title);
                        $query_update_record = "update $table_name set $set_sentence, $rowVersionCol=$rowVersionCol+1, $lastOperationCol='$lastOperationHere' \nwhere $pkey_cond";
                        if (! $simul) {
                            AfwDB::execQuery($dbserver, $query_update_record, $title, $continueAndSendAlert);
                        }

                        $queries[] = ['title' => $title, 'sql' => $query_update_record, 'type' => 'warning'];
                        $updated++;
                    } else {
                        AfwBatch::print_warning("record will be ignored reason : $not_recent_reason");
                        $ignored_record = '';
                        foreach ($mapping_cols as $json_column => $table_column) {
                            if ($ignored_record) {
                                $ignored_record .= ', ';
                            }

                            $ignored_record .= "$table_column : " . $item->$json_column;
                        }
                        $title     = 'ignore record';
                        $queries[] = ['title' => "$title", 'sql' => "-- $table_name record ignored : $ignored_record :: API record tmstamp=" . $item->$source_api_timestamp_field . ' vs DB record tmstmp : ' . $record_dest_tstamp . ", job_param_timestamp = $job_param_source_tstamp", 'type' => 'error'];
                        $ignored++;
                    }
                } else {
                    // insert record
                    $set_sentence = '';
                    $title        = 'insert record';

                    foreach ($mapping_cols as $json_column => $table_column) {
                        if (AfwStringHelper::stringContain($json_column, '(')) {
                            // it means formula
                            if ($set_sentence) {
                                $set_sentence .= ', ';
                            }

                            $set_sentence .= "$table_column = " . $json_column;
                        } else {
                            if (($table_column == 'STUDENT_ID') and strlen($item->$json_column) <= 3) {
                                $ignored_record = '';
                                foreach ($mapping_cols as $json_column => $table_column) {
                                    if ($ignored_record) {
                                        $ignored_record .= ', ';
                                    }
                                    
                                    $ignored_record .= "$table_column : " . $item->$json_column;
                                }
                                $title     = 'ignore record';
                                $queries[] = ['title' => "$title", 'sql' => "-- $table_name record ignored : $ignored_record because IDN of student is bad", 'type' => 'error'];
                                $ignored++;

                            } else {
                                if ($set_sentence) {
                                    $set_sentence .= ', ';
                                }
                                $json_column_value_escaped = ((string) $item->$json_column);
                                $json_column_value_escaped = str_replace("'", "''", $json_column_value_escaped);
                                $set_sentence .= "$table_column = '$json_column_value_escaped'";
                            }

                        }
                    }
                    if ($title == 'insert record') {
                        if($item->$source_api_deleted_at_field)
                        {
                            $lastOperationHere = $lastOperationDelete;
                        }
                        else
                        {
                            $lastOperationHere = $lastOperationInsert;
                        }
                        $query_insert_record = "insert into $table_name set $set_sentence, $rowVersionCol=1, $lastOperationCol='$lastOperationHere'";
                        if (! $simul) {
                            AfwDB::execQuery($dbserver, $query_insert_record, $title, $continueAndSendAlert);
                        }

                        $queries[] = ['title' => $title, 'sql' => $query_insert_record, 'type' => 'sql'];
                        $inserted++;
                    }

                }
            } catch (Exception $e) {
                $ignored++;
                $error_title = "For record $pkey_cond failed with Exception : " . $e->getMessage();
                AfwBatch::print_error($error_title);
                $errors[] = $error_title;
            } catch (Error $e) {
                $error_title = "For record $pkey_cond failed with Error : " . $e->__toString();
                $ignored++;
                AfwBatch::print_error($error_title);
                $errors[] = $error_title;

            }
        }

        return [$inserted, $updated, $ignored, $queries, $new_stamp, $errors];
    }

    public static function consume_api($apiConfig, $url, $data = null, $recursive = true, $max_tentatives = 20, $print_debugg = true, $force_mode = false, $lang = 'ar', $throwError = true)
    {
        $itemsAttribute = $apiConfig['items-attribute'];
        if (! $itemsAttribute) {
            throw new AfwRuntimeException('items-attribute missed in the apiConfig array : ' . var_export($apiConfig, true));
        }

        $inputPageAttribute = $apiConfig['input-page-attribute'];
        if ($recursive and ! $inputPageAttribute) {
            throw new AfwRuntimeException('in recursive mode we need the page-attribute to be configured in the apiConfig array : ' . var_export($apiConfig, true));
        }

        $data_api_id = $apiConfig['data_api_id'];
        $mapping_job_id = $apiConfig['mapping_job_id'];
        $executionLog = $apiConfig['executionLog'];

        if($executionLog)
        {
            if(!$mapping_job_id) throw new AfwRuntimeException("consume_api : mapping_job_id is mandatory field in apiConfig when executionLog is true");
            if(!$data_api_id) throw new AfwRuntimeException("consume_api : data_api_id is mandatory field in apiConfig when executionLog is true");

            AfwAutoLoader::addModule("etl");
        }
        

        $apiConfigObject = new AfwConfigObject($apiConfig, $data);
        $metaAttribute   = $apiConfig['meta-attribute'];
        if (! $metaAttribute) {
            throw new AfwRuntimeException('meta-attribute name missed in the apiConfig array : ' . var_export($apiConfig, true));
        }

        $pageCountAttribute = $apiConfig['page-count-attribute'];
        if (! $pageCountAttribute) {
            throw new AfwRuntimeException('page-count-attribute name (to find in meta array) missed in the apiConfig array : ' . var_export($apiConfig, true));
        }

        $currentPageAttribute = $apiConfig['current-page-attribute'];
        if (! $currentPageAttribute) {
            throw new AfwRuntimeException('we need the current-page-attribute name (to find in meta array) to be configured in the apiConfig array : ' . var_export($apiConfig, true));
        }

        $max_pages = $apiConfig['max-pages'];
        if (! $max_pages) {
            $max_pages = 60000;
        }

        if (! $data) {
            $data = [];
        }

        if ($max_tentatives < 1) {
            $max_tentatives = 1;
        }

        $tentative = 1;
        while ($tentative <= $max_tentatives) {
            $result = AfwApiConsumeHelper::runAPI($url, $apiConfigObject, 'data', $lang);

            if ($result['success'] and isset($result['result']->$itemsAttribute)) {
                $title_o = "TEN$tentative URL=" . $result['url'] . ' COUNT=' . count($result['result']->$itemsAttribute);
                if ($print_debugg) {
                    AfwBatch::print_debugg($title_o);
                }

                if($mapping_job_id && $data_api_id && $executionLog)
                {
                    ExecutionLog::loadByMainIndex( 
                        $mapping_job_id,
                        $data_api_id,
                        date("Y-m-d H:i:s"),
                        json_encode($data),
                        $result['response'],
                        $title_o,
                        true
                    );
                }
                

                $tentative = $max_tentatives;
            } else {
                sleep(45);
            }

            $tentative++;
        }

        $ok = true;

        if (! $result['success']) {
            $title_o = "After $max_tentatives tentatives the url : " . $result['url'] . ' failed';
            $error_msg = "$title_o and got response : " . $result['response'];
            if($mapping_job_id && $data_api_id && $executionLog)
            {
                ExecutionLog::loadByMainIndex( 
                    $mapping_job_id,
                    $data_api_id,
                    date("Y-m-d H:i:s"),
                    json_encode($data),
                    $result['response'],
                    $title_o,
                    true
                );
            }
            AfwBatch::print_error($error_msg);
            if ($throwError) {
                throw new AfwRuntimeException($error_msg);
            } else {
                $ok = false;
            }

        }

        $items = [];

        if ($ok) {
            $items     = $result['result']->$itemsAttribute;
            $pageCount = $result['result']->$metaAttribute->$pageCountAttribute;

            $currentPage = $result['result']->$metaAttribute->$currentPageAttribute;
            $page        = $currentPage;
            if ($recursive) {

                if ($pageCount > $currentPage + $max_pages - 1) {
                    if (! $force_mode) {
                        throw new AfwRuntimeException("too much pages ($pageCount) max of page $max_pages please use force mode (to-implement)");
                    }
                    elseif ($force_mode=="do-max-pages") {
                        $pageCount = $currentPage + $max_pages - 1;
                    }
                    else {
                        // do all pages keep $pageCount as is
                    }
                    
                }

                for ($page = $currentPage + 1; $page <= $pageCount; $page++) {
                    $apiConfigObject->setData($inputPageAttribute, $page);
                    $tentative = 1;
                    while ($tentative <= $max_tentatives) {
                        $result_2 = AfwApiConsumeHelper::runAPI($url, $apiConfigObject, 'data', $lang);

                        if ($result_2['success'] and isset($result_2['result']->$itemsAttribute)) 
                        {
                            $title_o = "PAGE$page/$pageCount TEN$tentative URL=" . $result_2['url'] . ' COUNT=' . count($result_2['result']->$itemsAttribute);
                            if ($print_debugg) {
                                AfwBatch::print_debugg($title_o);
                            }

                            if($mapping_job_id && $data_api_id && $executionLog)
                            {
                                ExecutionLog::loadByMainIndex( 
                                    $mapping_job_id,
                                    $data_api_id,
                                    date("Y-m-d H:i:s"),
                                    json_encode($data),
                                    $result_2['response'],
                                    $title_o,
                                    true
                                );
                            }

                            $items_2 = $result_2['result']->$itemsAttribute;
                            // die( var_export( $result_2, true ) );
                            // echo 'merging \n';
                            if (! is_array($items)) {
                                $items = [];
                            }

                            // $before = count( $items );
                            if (! is_array($items_2)) {
                                $items_2 = [];
                            }

                            // $new = count( $items_2 );
                            $items = array_merge($items, $items_2);
                            $after = count($items);
                            if ($print_debugg) {
                                AfwBatch::print_debugg("$title_o after merge : $after record(s)");
                            }

                            $tentative = $max_tentatives;
                        }

                        $tentative++;
                    }

                    if (! $result_2['success']) {
                        $title_o = "Processing page $page/$pageCount After $max_tentatives tentatives the url : " . $result_2['url'] . ' failed';
                        $error_msg = "$title_o and got response : " . $result_2['response'];
                        if($mapping_job_id && $data_api_id && $executionLog)
                        {
                            ExecutionLog::loadByMainIndex( 
                                $mapping_job_id,
                                $data_api_id,
                                date("Y-m-d H:i:s"),
                                json_encode($data),
                                $result_2['response'],
                                $title_o,
                                true
                            );
                        }
                        AfwBatch::print_error($error_msg);
                        if ($throwError) {
                            throw new AfwRuntimeException($error_msg);
                        } else {
                            $ok = false;
                        }

                        break;
                    }

                    //echo "merged : $before+$new = $after\n";
                }
            }

        }

        return ['ok' => $ok, 'current_page' => $page, 'last_page' => $pageCount, 'items' => $items];

    }
}
