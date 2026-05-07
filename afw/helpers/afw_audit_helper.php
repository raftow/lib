<?php
class AfwAuditHelper extends AFWRoot
{


    /** audit columns ***/

    public static final function getAuditableCols($object)
    {
        $tableau = [];

        $FIELDS_ALL = $object->getAllAttributes();

        foreach ($FIELDS_ALL as $attribute) {
            if ($object->keyIsAuditable($attribute)) {
                $attribute_to_remove_from_audit = false;
                if (!$attribute_to_remove_from_audit) {
                    $tableau[] = $attribute;
                }
            }
        }
        return $tableau;
    }


    /**
     * @param AFWObject $object
     * @param array $arr_fields_updated : array of the form [attribute1 => new_value1, attribute2 => new_value2,...]
     * @param string $action
     * audit_on_update
     * Insert into _audit table before execute Update Query
     */
    public static final function audit_on_update($object, $arr_fields_updated, $action, $update_context = '')
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        if(!$update_context) {
            $update_context = UfwWorkContext::getAllContextTranslated($lang);
        }
        $table_name = $object->getTableName();
        if ($object->IS_VIRTUAL) {
            throw new AfwRuntimeException('Impossible to do call to the method audit_on_update() with the virtual table ' . $table_name . '.');
        } else {
            $rowsCount = 0;
            if ($object->isByRowAuditable()) {
                $rowsCount += self::byrow_audit($object, $action, $update_context);
            } 
            
            if ($object->isByColumnAuditable()) {
                foreach ($arr_fields_updated as $key => $new_value) {
                    if (AfwStructureHelper::attributeIsAuditable($object, $key)) {
                        $rowsCount += self::bycol_audit($object, $key, $update_context);
                    } else {
                        // if($key=="subject") die("$table_name -> $key : not auditable");
                    }
                }
            }
        }

        return $rowsCount;
    }

    public static function getClientInfos() {
            // Check if client is using shared internet
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_adrs = $_SERVER['HTTP_CLIENT_IP'];
            }
            // Check if client is behind a proxy
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_adrs = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            // Default to the remote address
            else {
                $ip_adrs = $_SERVER['REMOTE_ADDR'];
            }

            $browser = $_SERVER['HTTP_USER_AGENT'];

            return [$browser, $ip_adrs];
    }

    /**
     * @param AFWObject $object
     */
    public static function byrow_audit(&$object, $action, $update_context)
    {
        $action_by = AfwSession::getUserIdActing();
        if(!$action_by) {
            throw new AfwRuntimeException("THe 'action by' information is mandatory to perform audit actions");
        }

        if(!$action) {
            throw new AfwRuntimeException("THe 'action by' information is mandatory to perform audit actions");
        }

        
        if(!$update_context) {
            throw new AfwRuntimeException("THe 'update context' information is mandatory to perform audit actions");
        }

        $table_name = $object->getTableName();
        $table_audit = $table_name . '_braudit';

        $fields_to_insert = AfwStructureHelper::getAllRealFields($object);

        $insert_columns = implode(", ", $fields_to_insert);

        $pk_cond = AfwSqlHelper::getPKCondSQL($object, $object->id);

        
        $action_at = $now = date("Y-m-d H:i:s");
        list($action_browser, $action_ip) = self::getClientInfos();

        $query = 'INSERT INTO ' . $object::_prefix_table($table_audit) . "($insert_columns,  action,  action_by,   action_at,    action_browser,    action_ip,  update_context) 
                                                                    SELECT $insert_columns, '$action', $action_by, '$action_at', '$action_browser', '$action_ip','$update_context' 
                                                                    from ".$object::_prefix_table($table_name)." $pk_cond";

        return $object->execQuery($query);
    }

    /**
     * @param AFWObject $object
     * @param string $attribute
     * @param string $update_context
     */
    public static function bycol_audit(&$object, $attribute, $update_context)
    {
        $table_name = $object->getTableName();
        $table_audit = $table_name . '_' . $attribute . '_bcaudit';
        $id = $object->getId();
        // $version_col = $object->fld_VERSION();
        $old_value = $object->getVal($attribute);
        // $update_date = $object->getUpdateDate();
        // $update_auser_id = $object->getUpdateUserId();

        $update_date_col = $object->fld_UPDATE_DATE();
        $update_auser_id_col = $object->fld_UPDATE_USER_ID();

        

        return $object->execQuery("INSERT INTO $table_audit(id, version, val, update_date, update_auser_id, update_context)
                            select $id, version, $old_value, $update_date_col, $update_auser_id_col, _utf8'$update_context' from $table_name where id = $id");
    }

    /**
     * @param array $initialRow
     * @param array $dataTuple
     * @param AFWObject $object
     * 
     */

    public static function auditDatimeHtml($initialRow, $dataTuple, $object, $lang='ar') {
        $datetime = $initialRow["action_at"];
        return "<div class='audit-cell datetime'>$datetime</div>";
    }    

        /**
     * @param array $initialRow
     * @param array $dataTuple
     * @param AFWObject $object
     * 
     */

    public static function auditByHtml($initialRow, $dataTuple, $object, $lang='ar') {
        $action_by = $initialRow["action_by"];
        $by = AfwLoadHelper::decodeLookupValue("ums", "auser", $action_by, "", "", "id");
        return "<div class='audit-cell by'>$by</div>";
    }    


    /**
     * @param array $initialRow
     * @param array $dataTuple
     * @param AFWObject $object
     * 
     */

    public static function auditActionHtml($initialRow, $dataTuple, $object, $lang='ar') {
        $html = "";
        $id = $initialRow["id"]."_". $initialRow["version"]."_". $initialRow["action"];        
        $version = AfwLanguageHelper::translateKeyword("version", $lang)." ".$initialRow["version"];
        $dtv = "<div class='audit-cell version'>$version</div>";
        $html .= "<div class='audit-row audit-dtv'>$dtv</div>";
        $action = $initialRow["action"];
        // $datetime = $initialRow["action_at"];
        //$dtv .= "<div class='audit-cell dt'>$datetime</div>";
        // $action_by = $initialRow["action_by"];
        // $by = AfwLoadHelper::decodeLookupValue("ums", "auser", $action_by, "", "", "id");
        $context = AfwLanguageHelper::translateKeyword($initialRow["update_context"], $lang);     
        $action_text = AfwLanguageHelper::translateKeyword("action.".$action, $lang);
        $using = AfwLanguageHelper::translateKeyword("using", $lang);
        $action_by_sentence = $action_text." ".$using." ".$context;
        $browser = $initialRow["action_browser"];     
        $fromip = $initialRow["action_ip"];     
        $html .= "<div class='audit-row audit-action'>$action_by_sentence</div>";
        // $html .= "<div class='audit-row audit-context'>$context</div>";
        $html .= "<div class='audit-row audit-fromip'>$fromip</div>";
        $html .= "<div class='audit-row audit-browser'>$browser</div>";
        return "<div id='audit-action-div-$id' class='audit-action-div hide'>$html</div>";
    }

    /**
     * @param array $initialRow
     * @param array $dataTuple
     * @param AFWObject $object
     * 
     */

    public static function auditAdvancedHtml($initialRow, $dataTuple, $object, $lang='ar') {
        $id = $initialRow["id"]."_". $initialRow["version"]."_". $initialRow["action"];        
        $auditId = "V".$initialRow["version"]. strtoupper(substr($initialRow["action"],0,1));
        $icon_advanced = "<span id='icon-audit-$id' class='fa advanced-audit icon-plus' title='".AfwLanguageHelper::translateKeyword("audit_advanced", $lang)."'></span>";
        $icon_advanced .= "<div class='identifier'>$auditId</div>";
        return "<div class='audit-advanced' id='audit-advanced-$id'>$icon_advanced</div>";
    }

}
