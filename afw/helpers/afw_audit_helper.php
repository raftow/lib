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
     * audit_before_update
     * Insert into _audit table before execute Update Query
     */
    public static final function audit_before_update($object, $arr_fields_updated)
    {
        $table_name = $object->getTableName();
        if ($object->IS_VIRTUAL) {
            throw new AfwRuntimeException('Impossible to do call to the method audit_before_update() with the virtual table ' . $table_name . '.');
        } else {
            global $update_context;



            if (!$update_context) {
                $objme = AfwSession::getUserConnected();
                if (
                    $objme and
                    $objme->isAdmin()
                ) {
                    throw new AfwRuntimeException(
                        "update context not specified when auditing table $table_name"
                    );
                }
            }

            $rowsCount = 0;

            foreach ($arr_fields_updated as $key => $new_value) {
                if ($object->keyIsAuditable($key)) {
                    $table_audit = $table_name . '_' . $key . '_haudit';
                    $id = $object->getId();
                    $version = $object->getVersion();
                    $old_value = $object->getVal($key);
                    $update_date = $object->getUpdateDate();
                    $update_auser_id = $object->getUpdateUserId();

                    $update_date_col = $object->fld_UPDATE_DATE();
                    $update_auser_id_col = $object->fld_UPDATE_USER_ID();

                    $rowsAffected = $object->execQuery("INSERT INTO $table_audit(id, version, val, update_date, update_auser_id, update_context)
        					     select $id, version, $key, $update_date_col, $update_auser_id_col, _utf8'$update_context' from $table_name where id = $id");

                    $rowsCount += $rowsAffected;
                } else {
                    // if($key=="subject") die("$table_name -> $key : not auditable");
                }
            }
        }

        return $rowsCount;
    }
}
