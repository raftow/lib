<?php

class AfwRuntimeException extends RuntimeException
{
    /**
     * @param AFWObject $object 
     * @return AfwRuntimeException
     */
    public function __construct(string $message, array $throwed_arr = [
                                                                'FIELDS_UPDATED' => true,
                                                                'SQL' => true,
                                                                'DEBUGG' => true,
                                                                'CACHE' => false,
                                                                'ALL' => false,
                                                                'POST' => true,
                                                            ]
                                        , AFWObject $object = null                    
    )
    {
        $msg = "";
        if ($throwed_arr['ALL'] and $object) {
            $msg .=
                "\n   throwed this = " . var_export($object, true) . "<br>\n";
        }

        if ($throwed_arr['FIELDS_UPDATED'] and $object) {
            $msg .=
                "<br>\n   throwed this-> FIELDS_UPDATED = " .
                $object->reallyUpdated() .
                "<br>\n";
        }

        if ($throwed_arr['AFIELD_VALUE'] and $object) {
            $msg .=
                "<br>\n   throwed this->AFIELD_VALUE = " .
                var_export($object->getAllfieldValues(), true) .
                "<br>\n";
        }

        if ($throwed_arr['SQL'] and $object) {
            $msg .= "<br>\nthrowed : ";

            if ($object->debugg_sql_query) {
                $msg .= 'Query     : ' . $object->debugg_sql_query . "<br>\n";
            }
            if ($object->debugg_row_count) {
                $msg .= 'Nb rows       :' . $object->debugg_row_count . "<br>\n";
            }
            if ($object->debugg_affected_row_count) {
                $msg .=
                    'Affected rows : ' .
                    $object->debugg_affected_row_count .
                    "<br>\n";
            }
            if ($object->debugg_tech_notes) {
                $msg .=
                    'Technical infos : ' . $object->debugg_tech_notes . "<br>\n";
            }
            if ($object->debugg_sql_error) {
                $msg .= 'SQL Error : ' . $object->debugg_sql_error . "<br>\n";
            }
        }

        if ($throwed_arr['DEBUGG'] and $object) {
            $msg .= "<br>\ndebugg data : ";
            foreach ($object->debuggs as $dbg_key => $dbg_val) {
                $msg .= "$dbg_key     : $dbg_val<br>\n";
            }
        }

        if ($throwed_arr['CACHE']) {
            $msg .= "<br>\ncache data : ";
            if (class_exists('AfwAutoLoader')) {
                $msg .= AfwCacheSystem::getSingleton()->cache_analysis_to_html($light = true);
            }
        }


        if($msg) $message .= "<div class='technical'>$msg</div>";
        
        $mess_post = "";

        if($_POST and is_array($_POST) and (count($_POST)>0))
        {
            foreach($_POST as $psKey => $psVal) $mess_post .= "<p>$psKey => $psVal</p>";
        }
        
        if($mess_post) $mess_post = "You can below un-hide <b>the POST ARRAY :</b><br><div class='technical post'><BR>$mess_post</div>";


        parent::__construct($mess_post.$message);


    }
}