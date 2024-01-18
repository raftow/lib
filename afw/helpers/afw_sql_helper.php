<?php
class AfwSqlHelper extends AFWRoot
{

    public static final function deduire_where($nom_col, $desc, $oper, $val_col, $val_col2 = null)
    {
        $server_db_prefix = AfwSession::config("db_prefix", "c0");

        if ($desc["UTF8"]) $codage = "_utf8";
        else $codage = "";
        if ($desc["FIELD-FORMULA"]) {
            if ($desc["FORMULA_MODULE"]) {
                $formula_module = $server_db_prefix . $desc["FORMULA_MODULE"] . ".";
            } else {
                $formula_module = "";
            }
            $nom_col = $formula_module . $desc["FIELD-FORMULA"];
        } elseif ($desc["TYPE"] == 'TEXT') {
            $nom_col = "IF(ISNULL($nom_col), '', $nom_col)";
        }

        switch ($desc["TYPE"]) {
            case 'FK':
            case 'ENUM':
            case 'ANSWER':
            case 'YN':
                if (is_array($val_col)) {
                    //AFWDebugg::log("val_col of $nom_col defined array");
                    //AFWDebugg::log($val_col,true);    
                    if (count($val_col) == 1) {
                        $val_col2 = $val_col[0];
                        $oper2 = $oper;
                        if ($oper2 == "in") {
                            $oper2 = "=";
                        }
                        if (($oper2 == "=") and ($val_col2 && ($val_col2 != "0") && ($val_col2 != "W")) and (!$desc["FIELD-FORMULA"]))
                        //if($oper2=="=")
                        {
                            $fixm = $nom_col . $oper2 . $val_col2;
                        } else {
                            $fixm = "";
                        }
                    }

                    return array($nom_col . ' ' . $oper . " ($codage'" . implode("$codage','", $val_col) . "')", $fixm);
                } else {
                    //AFWDebugg::log("val_col of $nom_col defined not array : $val_col");
                }
                if (($oper == "=") and ($val_col && ($val_col != "0")))
                //if($oper=="=")
                {
                    $fixm = $nom_col . $oper . $val_col;
                } else {
                    $fixm = "";
                }

                return array($nom_col . ' ' . $oper . " $codage'" . $val_col . "'", $fixm);
            case 'MENUM':
            case 'MFK':
                $where = array();
                foreach ($val_col as $val)
                    $where[] = $nom_col . ' ' . str_replace(".", $val, $oper);
                return array("(" . implode(" or ", $where) . ")", "");
            case 'TEXT':

                $cond_col = str_replace("X", $codage, $oper);
                $cond_col = str_replace(".", $val_col, $cond_col);

                return array($nom_col . ' ' . $cond_col, "");
            case 'DATE':
                if ($oper == 'between') {
                    $val_col = str_replace("-", "", $val_col);
                    $val_col2 = str_replace("-", "", $val_col2);
                    if (!$val_col2) $val_col2 = '29991230';
                    return  array($nom_col . ' between  \'' . $val_col . '\' and \'' . $val_col2 . '\'', "");
                }
                return array($nom_col . ' ' . $oper . ' \'' . $val_col . '\'', "");;
            case 'PK':
                if ($oper == 'in (.)') {
                    return array($nom_col . ' in(' . $val_col . ')', "");
                } else {
                    return array($nom_col . ' ' . $oper . ' \'' . $val_col . '\'', "");
                }
            default:
                return array($nom_col . ' ' . $oper . ' \'' . $val_col . '\'', "");
        }
    }

    public static final function getSQLUpdate($obj, $user_id = 0, $ver = 0, $id_updated = '')
    {
        $report = "";
        $table_prefixed = $obj->getTableName($with_prefix = true);
        $query = 'UPDATE ' . $table_prefixed . ' me SET ';
        $query .=
            $obj->fld_UPDATE_USER_ID() .
            ' = ' .
            $user_id .
            ', ' .
            $obj->fld_UPDATE_DATE() .
            ' = ' .
            $obj->get_UPDATE_DATE_value(true) .
            ', ' .
            $obj->fld_VERSION() .
            " = $ver, ";
        $fields_updated = [];
        // if($table_prefixed=="c0license.license")  throw new AfwRuntimeException("obj->fieldsHasChanged() = ".var_export($obj->fieldsHasChanged(),true));
        // rafik : since version 2.0.1 we put FIELDS_UPDATED the old value
        $old_val_query_part = "\n";
        foreach ($obj->fieldsHasChanged() as $key => $old_value) {
            $value = $obj->getAfieldValue($key);
            if (is_array($value)) {
                die("how to update value of afield $key when it is an array : " .
                    var_export($value, true));
            }
            $isTechField = $obj->isTechField($key);
            if (!$isTechField) {
                $structure = AfwStructureHelper::getStructureOf($obj, $key);

                if (
                    isset($structure) and
                    !$structure['CATEGORY'] and
                    !$structure['NO-SAVE']
                ) {
                    if (strcasecmp($value, 'now()') === 0) {
                        $query .= ' ' . $key . ' = now(),';
                    } else {
                        if ($structure['TYPE'] == 'GDAT') {
                            if (!$value) $value = "0000-00-00";
                        }
                        $value_up = strtoupper($value);
                        if (AfwStringHelper::stringStartsWith($value_up, 'REPLACE(')) {
                            $query .= " $key = $value ,";
                        } else {
                            if ($structure['UTF8']) {
                                $_utf8 = '_utf8';
                            } else {
                                $_utf8 = '';
                            }
                            $query .= ' ' . $key . " = $_utf8'" . AfwStringHelper::_real_escape_string($value) . "',";
                        }
                        $value_desc = implode('>>', explode("\n", $value));
                        $old_value_desc = implode('>>', explode("\n", $old_value));
                        $isNum = is_numeric($value);
                        $isSame = $value == $old_value;
                        $valueExists =
                            (!$obj->isEmpty() and
                                $obj->isAfieldValueSetted($key));

                        $old_val_query_part .= " -- $key value = [$value_desc], old value = [$old_value_desc] isNum=$isNum isSame= $isSame valueExists=$valueExists\n";
                    }
                    $fields_updated[$key] = $value;
                } else {
                    if (!isset($structure)) $report .=  "structure of attribute $key is not defined, ";
                    if ($structure['CATEGORY']) $report .=  "attribute $key is category field, ";
                    if ($structure['NO-SAVE']) $report .=  "attribute $key is no-save field, ";
                }
                // awf V3.0 : NEW LOGIC OF :
                //    * SETTER AND
                //    * GETTER AND
                //    * STRUCTURE AND
                //    * SHORTNAMES

                // else $obj->simpleError("field $key has value '$value' setted in FIELDS_UPDATED and can not be saved, DB_STRUCTURE : ".var_export($structure,true));
            }
            // if($key=='my_exp') die($query);
        }

        // if($table_prefixed=="sdd.jobrole_application") die($query);

        /*
        if($table_prefixed=="c0btb.travel_seat")
        {
            die("fu = ".$obj->showArr($fields_updated)."<br>FU = ".$obj->showArr($obj->fieldsHasChanged()));
        }
        */
        $query = trim($query);
        $query = trim($query, ',');
        if ($id_updated) {
            if ($obj->PK_MULTIPLE) {
                if ($obj->PK_MULTIPLE === true) {
                    $sep = '-';
                } else {
                    $sep = $obj->PK_MULTIPLE;
                }
                $query .= "\n WHERE 1 ";
                $pk_val_arr = explode($sep, $id_updated);
                foreach ($obj->PK_MULTIPLE_ARR as $pk_col_order => $pk_col) {
                    $structurePKAttribute = AfwStructureHelper::getStructureOf($obj, $pk_col);
                    if ($structurePKAttribute['UTF8']) {
                        $_utf8 = '_utf8';
                    } else {
                        $_utf8 = '';
                    }
                    $query .=
                        " AND $pk_col = $_utf8'" . $pk_val_arr[$pk_col_order] . "'";
                }
            } else {
                $query .=
                    "\n WHERE " . $obj->getPKField() . " = '$id_updated'";
            }
        } else {
            $query .= "\n WHERE 1 ";
        }
        $query .= $obj->getSQL();

        $query .= $old_val_query_part;

        return [$query, $fields_updated, $report];
    }


    /**
     * 
     * @return array like this [$result, $row_count, $affected_row_count]
     * 
     */

    final public static function executeQuery(
        $module_server,
        $module,
        $table,
        $sql_query,
        $throw_error = true,
        $throw_analysis_crash = true
    ) {
        AfwBatch::print_sql("<br>\n ############################################################################# <br>\n");
        AfwBatch::print_sql("<br>\nexecQuery will execute : <br>\n");
        AfwBatch::print_sql("<br>\n$sql_query <br>\n");



        list($result, $project_link_name) = AfwDatabase::db_query(
            $sql_query,
            $throw_error,
            $throw_analysis_crash,
            $module_server,
            $module,
            $table
        );
        if (!$result) {
            $sql_error =
                "sql:[$sql_query] ==> " . AfwMysql::get_error(AfwDatabase::getLinkByName($project_link_name));

            if (!$throw_error) {
                $sql_error .= ' will not be throwed';
            } else {
                $sql_error .= " !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
             SHOULD BE THROWED. HOW I GET HERE 
             !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
             !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
            }

            AfwBatch::print_sql("<br>\nexecQuery failed : <br>\n");
            AfwBatch::print_sql("<br>\n$sql_error <br>\n");
        } else {

            $affected_row_count = AfwMysql::affected_rows(AfwDatabase::getLinkByName($project_link_name));
            /*
            if(AfwStringHelper::stringStartsWith($sql_query,"delete from"))
            {
                throw new AfwRuntimeException("$sql_query has been executed and $affected_row_count record(s) deleted");
            }*/

            $row_count = AfwMysql::rows_count($result);
            AfwBatch::print_sql("<br>\nexecQuery succeeded : <br>\n");
            AfwBatch::print_sql("<br>\nrow count     : $row_count <br>\n");
            AfwBatch::print_sql("<br>\naffected rows : $affected_row_count <br>\n");
        }

        return [$result, $row_count, $affected_row_count];
    }

    public static final function getClauseWhere(
        $object,
        $nom_col,
        $oper,
        $val_col,
        $val_col2 = null,
        $lang
    ) {
        $server_db_prefix = AfwSession::config('db_prefix', 'c0');

        $all_oper_arr = [
            'in (.)' => self::traduireOperator('IN', $lang),
            '=' => self::traduireOperator('EQUAL', $lang, true),
            '<' => self::traduireOperator('LESS_THAN', $lang, true),
            '>' => self::traduireOperator('GREATER_THAN', $lang, true),
            '<=' => self::traduireOperator('LESS_OR_EQUAL_THAN', $lang, true),
            '>=' => self::traduireOperator('GREATER_OR_EQUAL_THAN', $lang, true),
            '!=' => self::traduireOperator('NOT_EQUAL', $lang, true),
            'between' => self::traduireOperator('BETWEEN', $lang, true),
            "like X'%.%'" => self::traduireOperator('CONTAIN', $lang, true),
            "like X'.%'" => self::traduireOperator('BEGINS_WITH', $lang, true),
            "like X'%.'" => self::traduireOperator('ENDS_WITH', $lang, true),
            "like X'.'" => self::traduireOperator('EQUAL', $lang, true),
            "not like X'%.%'" => self::traduireOperator('NOT_CONTAIN', $lang, true),
            "=''" => self::traduireOperator('IS_EMPTY', $lang, true),
            "!=''" => self::traduireOperator('IS_NOT_EMPTY', $lang, true),
        ];
        $prefixed_nom_col = $nom_col;
        list($prefix_col, $nom_col) = explode('.', $nom_col);
        if (!$nom_col) {
            $nom_col = $prefix_col;
        }
        $desc = AfwStructureHelper::getStructureOf($object, $nom_col);
        if (!$desc) {
            throw new AfwRuntimeException("can't find structure of field $nom_col");
        }
        $original_nom_col = $nom_col;

        if ($desc['UTF8']) {
            $codage = '_utf8';
        } else {
            $codage = '';
        }
        if ($desc['FIELD-FORMULA']) {
            if ($desc['FORMULA_MODULE']) {
                $formula_module =
                    $server_db_prefix . $desc['FORMULA_MODULE'] . '.';
            } else {
                $formula_module = '';
            }
            $nom_col = $formula_module . $desc['FIELD-FORMULA'];
        } elseif ($desc['TYPE'] == 'TEXT') {
            $nom_col = "IF(ISNULL(me.$nom_col), '', me.$nom_col)";
        }

        switch ($desc['TYPE']) {
            case 'FK':
            case 'ENUM':
            case 'ANSWER':
            case 'YN':
                if (is_array($val_col)) {
                    //AFWDebugg::log("val_col of $nom_col defined array");
                    //AFWDebugg::log($val_col,true);
                    if (count($val_col) == 1) {
                        $val_col2 = $val_col[0];
                        $oper2 = $oper;
                        if ($oper2 == 'in') {
                            $oper2 = '=';
                        }
                        if (
                            $oper2 == '=' and
                            $val_col2 &&
                            $val_col2 != '0' &&
                            $val_col2 != 'W' and
                            !$desc['FIELD-FORMULA']
                        ) {
                            //if($oper2=="=")
                            $fixm = $nom_col . $oper2 . $val_col2;
                        } else {
                            $fixm = '';
                        }
                    }
                    $phraseLangWhere =
                        "<span class='crit_field_name'>" .
                        $object->translate($original_nom_col, $lang) .
                        "</span> <span class='crit_field_oper'>" .
                        $all_oper_arr[$oper] .
                        "</span> : <span class='crit_field_value'>" .
                        implode(', ', $object->decodeList($nom_col, $val_col)) .
                        '</span>';
                    return [
                        $prefixed_nom_col .
                            ' ' .
                            $oper .
                            " ($codage'" .
                            implode("$codage','", $val_col) .
                            "')",
                        $fixm,
                        $phraseLangWhere,
                    ];
                } else {
                    //AFWDebugg::log("val_col of $nom_col defined not array : $val_col");
                }
                if ($oper == '=' and $val_col && $val_col != '0') {
                    //if($oper=="=")
                    $fixm = $nom_col . $oper . $val_col;
                } else {
                    $fixm = '';
                }
                // $phraseLangWhere = $object->translate($original_nom_col, $lang) . " " . $all_oper_arr[$oper] . " : " . $object->getAnswer($nom_col,$val_col);
                $phraseLangWhere =
                    "<span class='crit_field_name'>" .
                    $object->translate($original_nom_col, $lang) .
                    "</span> <span class='crit_field_oper'>" .
                    $all_oper_arr[$oper] .
                    "</span> : <span class='crit_field_value'>" .
                    $object->getAnswer($nom_col, $val_col) .
                    '</span>';

                return [
                    $prefixed_nom_col .
                        ' ' .
                        $oper .
                        " $codage'" .
                        $val_col .
                        "'",
                    $fixm,
                    $phraseLangWhere,
                ];
            case 'MENUM':
            case 'MFK':
                $where = [];
                foreach ($val_col as $val) {
                    $where[] =
                        $prefixed_nom_col . ' ' . str_replace('.', $val, $oper);
                }
                $phraseLangWhere =
                    $object->translate($original_nom_col, $lang) .
                    ' ' .
                    $all_oper_arr[$oper] .
                    ' : ' .
                    implode(', ', $object->decodeList($nom_col, $val_col));
                $phraseLangWhere =
                    "<span class='crit_field_name'>" .
                    $object->translate($original_nom_col, $lang) .
                    "</span> <span class='crit_field_oper'>" .
                    $all_oper_arr[$oper] .
                    "</span> : <span class='crit_field_value'>" .
                    implode(', ', $object->decodeList($nom_col, $val_col)) .
                    '</span>';
                return [
                    '(' . implode(' or ', $where) . ')',
                    '',
                    $phraseLangWhere,
                ];
            case 'TEXT':
                $val_col = trim($val_col);
                if ($oper == '=') {
                    $cond_col = "='$val_col'";
                } else {
                    $cond_col = str_replace('X', $codage, $oper);
                    $cond_col = str_replace('.', $val_col, $cond_col);
                }

                //$phraseLangWhere = $object->translate($original_nom_col, $lang) . " " . $all_oper_arr[$oper] . " [$val_col] ";
                $phraseLangWhere =
                    "<span class='crit_field_name'>" .
                    $object->translate($original_nom_col, $lang) .
                    "</span> <span class='crit_field_oper'>" .
                    $all_oper_arr[$oper] .
                    "</span> : <span class='crit_field_value'> '$val_col' </span>";

                return [
                    $prefixed_nom_col . ' ' . $cond_col,
                    '',
                    $phraseLangWhere,
                ];
            case 'DATE':
                if ($oper == 'between') {
                    $val_col = str_replace('-', '', $val_col);
                    $val_col2 = str_replace('-', '', $val_col2);
                    if (!$val_col2) {
                        $val_col2 = '29991230';
                    }
                    //$phraseLangWhere = $object->translate($original_nom_col, $lang) . " " . $all_oper_arr[$oper] . " [$val_col,$val_col2] ";
                    $phraseLangWhere =
                        "<span class='crit_field_name'>" .
                        $object->translate($original_nom_col, $lang) .
                        "</span> <span class='crit_field_oper'>" .
                        $all_oper_arr[$oper] .
                        "</span> : <span class='crit_field_value'> [$val_col -> $val_col2] </span>";
                    return [
                        $prefixed_nom_col .
                            ' between  \'' .
                            $val_col .
                            '\' and \'' .
                            $val_col2 .
                            '\'',
                        '',
                        $phraseLangWhere,
                    ];
                }
                $phraseLangWhere =
                    "<span class='crit_field_name'>" .
                    $object->translate($original_nom_col, $lang) .
                    "</span> <span class='crit_field_oper'>" .
                    $all_oper_arr[$oper] .
                    "</span> : <span class='crit_field_value'> '$val_col' </span>";
                // $phraseLangWhere = $object->translate($original_nom_col, $lang) . " " . $all_oper_arr[$oper] . " [$val_col] ";
                return [
                    $prefixed_nom_col . ' ' . $oper . ' \'' . $val_col . '\'',
                    '',
                    $phraseLangWhere,
                ];
            case 'PK':
                if ($oper == 'in (.)') {
                    $phraseLangWhere =
                        "<span class='crit_field_name'>" .
                        $object->translate($original_nom_col, $lang) .
                        "</span> <span class='crit_field_oper'>" .
                        $all_oper_arr[$oper] .
                        "</span> : <span class='crit_field_value'> [$val_col] </span>";
                    //$phraseLangWhere = $object->translate($original_nom_col, $lang) . " " . $all_oper_arr[$oper] . " [$val_col] ";
                    return [
                        $prefixed_nom_col . ' in(' . $val_col . ')',
                        '',
                        $phraseLangWhere,
                    ];
                } else {
                    $phraseLangWhere =
                        "<span class='crit_field_name'>" .
                        $object->translate($original_nom_col, $lang) .
                        "</span> <span class='crit_field_oper'>" .
                        $all_oper_arr[$oper] .
                        "</span> : <span class='crit_field_value'> [$val_col] </span>";
                    //$phraseLangWhere = $object->translate($original_nom_col, $lang) . " " . $all_oper_arr[$oper] . " [$val_col] ";
                    return [
                        $prefixed_nom_col .
                            ' ' .
                            $oper .
                            ' \'' .
                            $val_col .
                            '\'',
                        '',
                        $phraseLangWhere,
                    ];
                }
            default:
                $phraseLangWhere =
                    "<span class='crit_field_name'>" .
                    $object->translate($original_nom_col, $lang) .
                    "</span> <span class='crit_field_oper'>" .
                    $all_oper_arr[$oper] .
                    "</span> : <span class='crit_field_value'> [$val_col] </span>";
                // $phraseLangWhere = $object->translate($original_nom_col, $lang) . " " . $all_oper_arr[$oper] . " [$val_col] ";
                return [
                    $prefixed_nom_col .
                        ' zz ' .
                        var_export($desc, true) .
                        '  ' .
                        $oper .
                        ' \'' .
                        $val_col .
                        '\'',
                    '',
                    $phraseLangWhere,
                ];
        }
    }


    /**
     * getSQLMany
     * @param AFWObject $object
     * @param string  $pk_field
     * @param string  $limit : Optional add limit to query
     * @param string  $order_by : Optional add order by to query
     * @param boolean $optim
     * @param boolean  $eager_joins 
     */

    public static function getSQLMany(
        $object,
        $pk_field = '',
        $limit = '',
        $order_by = '',
        $optim = true,
        $eager_joins = false
    ) {
        if (!$pk_field) {
            $pk_field = $object->getPKField();
        }
        if (!$order_by) {
            $order_by = $object->getOrderByFields();
        }
        if (!$order_by and $pk_field) {
            $order_by = $pk_field;
        }

        if (!$optim and $pk_field) {
            $query =
                "SELECT DISTINCT $pk_field as PK \n FROM " .
                $object::_prefix_table($object::$TABLE) .
                " me\n WHERE 1" .
                $object->SEARCH .
                "\n " .
                ($limit ? ' LIMIT ' . $limit : '');
        } else {
            $all_real_fields = AfwStructureHelper::getAllRealFields($object);
            if ($eager_joins) {
                list(
                    $list_from_join_cols,
                    $join_sentence,
                ) = self::getOptimizedJoin($object);
            } else {
                $list_from_join_cols = '';
                $join_sentence = '';
            }

            $query =
                "SELECT $pk_field as PK, me." .
                implode(', me.', $all_real_fields);
            if ($list_from_join_cols) {
                $query .= ",\n" . $list_from_join_cols;
            }
            $this_class = get_class($object);
            $query .=
                "\n FROM " .
                $object::_prefix_table($object::$TABLE) .
                ' me -- class : ' .
                $this_class;
            if ($join_sentence) {
                $query .= "\n" . $join_sentence;
            }
            $query .= "\n WHERE 1" . $object->SEARCH;
            $query .= "\n ORDER BY " . $order_by;
            $query .= $limit ? "\n LIMIT " . $limit : '';
        }

        //die("getSQLMany : $query");
        //AfwSession::sqlLog($query, "SQL-MANY");
        return $query;
    }

    /**
     * Rafik 10/6/2021 : to prepare joins on lookup tables and any answer table of FK retrieved field
     * to avoid to LoadMany who for example load 1000 objects to load each 1000 FK object each one by separated SQl query
     * which make heavy the script, to be used only when needed because it increase memory loaded by those many objects as it load
     * FK objects as eager loaded objects as for LoadManyEager below method
     */

    public static function getOptimizedJoin($object)
    {
        global $lang;

        $join_sentence_arr = [];
        $join_retrieve_fields = [];

        $server_db_prefix = AfwSession::config('db_prefix', 'c0');

        // add left joins for all retrieved fields with type = FK and category empty (real fields)
        $colsRet = $object->getRetrieveCols(
            $mode = 'display',
            $lang,
            $all = false,
            $type = 'FK'
        );
        $joint_count = 0;
        foreach ($colsRet as $col_ret) {
            $joint_count++;
            $descCol = AfwStructureHelper::getStructureOf($object, $col_ret);
            if (!$descCol['CATEGORY']) {
                $tableCol = $descCol['ANSWER'];
                $moduleCol = $descCol['ANSMODULE'];
                if (!$moduleCol) {
                    $moduleCol = $object::$MODULE;
                }
                if (!$moduleCol) {
                    $moduleCol = 'pag';
                }

                $join_sentence_arr[] = "left join $server_db_prefix" . "$moduleCol.$tableCol join" . $col_ret . "00 on me.$col_ret = join" . $col_ret . "00.id";
                //$join_retrieve_fields[] = "join$col_ret.id as join${col_ret}00_id";
                $col_ret_obj = AfwStructureHelper::getEmptyObject($object, $col_ret);
                $col_fk_retrieve_cols = AfwStructureHelper::getAllRealFields($col_ret_obj);
                foreach ($col_fk_retrieve_cols as $col_fk_sub_ret) {
                    $join_retrieve_fields[] = "join" . $col_ret . "00.$col_fk_sub_ret as join$col_ret" . "00_$col_fk_sub_ret";
                }
            }
        }
        return [
            implode(',', $join_retrieve_fields),
            implode("\n", $join_sentence_arr),
        ];
    }
}
