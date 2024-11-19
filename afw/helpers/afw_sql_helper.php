<?php
class AfwSqlHelper extends AFWRoot
{
    /*
    public static final function deduire _where($nom_col, $desc, $oper, $val_col, $val_col2 = null)
    {
        $server_db_prefix = AfwSession::config("db_prefix", "default_db_");
        
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
    }*/

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
        // if($table_prefixed=="c 0license.license")  throw new AfwRuntimeException("obj->fieldsHasChanged() = ".var_export($obj->fieldsHasChanged(),true));
        // rafik : since version 2.0.1 we put FIELDS_UPDATED the old value
        $old_val_query_part = "\n";
        // if($obj->getMyClass()=='Afield') throw new AfwRuntimeException("logicDelete after setting active false . hasChanged=".var_export($obj->hasChanged(),true)." fieldsHasChanged=".var_export($obj->fieldsHasChanged(),true));
        foreach ($obj->fieldsHasChanged() as $key => $old_value) {
            $value = $obj->getAfieldValue($key);
            if (is_array($value)) {
                die("how to update value of afield $key when it is an array : " .
                    var_export($value, true));
            }
            $isTechField = $obj->isTechField($key);
            if (!$isTechField) {
                $structure = AfwStructureHelper::getStructureOf($obj, $key);
                /*
                if($obj->getMyClass()=='Afield' and $key=='avail') 
                {
                    throw new AfwRuntimeException("logicDelete after setting $key = $value and it was $old_value . hasChanged=".var_export($obj->hasChanged(),true)." fieldsHasChanged=".var_export($obj->fieldsHasChanged(),true));
                }
                */

                if (
                    isset($structure) and
                    !$structure['CATEGORY'] and
                    !$structure['NO-SAVE']
                ) 
                {
                    $value_desc = implode('>>', explode("\n", $value));
                    $old_value_desc = implode('>>', explode("\n", $old_value));
                    $isNum = is_numeric($value);
                    $isGDate = (($structure['TYPE']=='GDAT') or ($structure['TYPE']=='GDATE'));
                    $isSameDate = false;
                    $isSame = ($value == $old_value);
                    if($isGDate)
                    {
                        $isSameDate = ($isSame or ("$value 00:00:00" == $old_value) or ("$old_value 00:00:00" == $value));
                    }
                    
                    $isCompletelySame = (($value === $old_value) or ($isNum and $isSame) or ($isGDate and $isSameDate));
                    $valueExists =
                        (!$obj->isEmpty() and
                            $obj->isAfieldValueSetted($key));

                    $old_val_query_part .= " -- $key value = [$value_desc], old value = [$old_value_desc] isNum=$isNum isGDate=$isGDate isSame= $isSame isCompletelySame=$isCompletelySame valueExists=$valueExists\n";

                    if(!$isCompletelySame)        
                    {
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
                        }
                        $fields_updated[$key] = $value;
                    }
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
        if($table_prefixed=="c 0btb.travel_seat")
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
     * @return array like object [$result, $row_count, $affected_row_count]
     * 
     */

    final public static function executeQuery(
        $module_server,
        $module,
        $table,
        $sql_query,
        $throw_error = true,
        $throw_analysis_crash = true
    ) 
    {
        
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
    ) 
    {
        
        $server_db_prefix = AfwSession::config('db_prefix', "default_db_");

        $all_oper_arr = [
            'in (.)' => AfwLanguageHelper::translateKeyword('IN', $lang),
            '=' => AfwLanguageHelper::translateKeyword('EQUAL', $lang, true),
            '<' => AfwLanguageHelper::translateKeyword('LESS_THAN', $lang, true),
            '>' => AfwLanguageHelper::translateKeyword('GREATER_THAN', $lang, true),
            '<=' => AfwLanguageHelper::translateKeyword('LESS_OR_EQUAL_THAN', $lang, true),
            '>=' => AfwLanguageHelper::translateKeyword('GREATER_OR_EQUAL_THAN', $lang, true),
            '!=' => AfwLanguageHelper::translateKeyword('NOT_EQUAL', $lang, true),
            'between' => AfwLanguageHelper::translateKeyword('BETWEEN', $lang, true),
            "like X'%.%'" => AfwLanguageHelper::translateKeyword('CONTAIN', $lang, true),
            "like X'.%'" => AfwLanguageHelper::translateKeyword('BEGINS_WITH', $lang, true),
            "like X'%.'" => AfwLanguageHelper::translateKeyword('ENDS_WITH', $lang, true),
            "like X'.'" => AfwLanguageHelper::translateKeyword('EQUAL', $lang, true),
            "not like X'%.%'" => AfwLanguageHelper::translateKeyword('NOT_CONTAIN', $lang, true),
            "=''" => AfwLanguageHelper::translateKeyword('IS_EMPTY', $lang, true),
            "!=''" => AfwLanguageHelper::translateKeyword('IS_NOT_EMPTY', $lang, true),
        ];
        $prefixed_nom_col = $nom_col;
        list($prefix_col, $nom_col) = explode('.', $nom_col);
        if (!$nom_col) {
            $nom_col = $prefix_col;
            $prefix_col = "";
        }
        $original_nom_col = $nom_col;
        // if($val_col=="1007294216") die("getClauseWhere(object,$nom_col,$oper,$val_col,$val_col2,$lang)"); 
        $desc = AfwStructureHelper::getStructureOf($object, $nom_col);
        $mode_clause_where_col = false;
        if($desc["CLAUSE-WHERE-COL"])
        {
            $mode_clause_where_col = true;
            $nom_col = $desc["CLAUSE-WHERE-COL"];
            // die("CLAUSE-WHERE-COL : $original_nom_col => $nom_col");
            $desc = AfwStructureHelper::getStructureOf($object, $nom_col);
            $original_nom_col = $nom_col;
        }
        $desc["FIELD-FORMULA"] = $object->decodeText($desc["FIELD-FORMULA"]);
        if (!$desc) {
            throw new AfwRuntimeException("can't find structure of field $nom_col");
        }

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
            $prefixed_nom_col = $nom_col;
        } elseif ($desc['TYPE'] == 'TEXT') {
            if($desc['MANDATORY'] or $desc['REQUIRED'])
            {
                $prefixed_nom_col = "me.".$nom_col;
            }
            else
            {
                $prefixed_nom_col = "IF(ISNULL(me.$nom_col), '', me.$nom_col)";
            }
            
            
        }
        else {
            // $nom_col = $prefixed_nom_col;
            $prefixed_nom_col = "me.".$nom_col;
        }

        // if($val_col=="1007294216") die("nom_col=$nom_col prefixed_nom_col=$prefixed_nom_col"); 

        //if($original_nom_col=="cvalid") throw new AfwRuntimeException("nom_col = ".$nom_col." because structure=".var_export($desc,true));
        

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
                        $nom_col .
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
                // $phraseLangWhere = $object->translate($original_nom_col, $lang) . " " . $all_oper_arr[$oper] . " : " . $object->get Answer($nom_col,$val_col);
                $phraseLangWhere =
                    "<span class='crit_field_name'>" .
                    $object->translate($original_nom_col, $lang) .
                    "</span> <span class='crit_field_oper'>" .
                    $all_oper_arr[$oper] .
                    "</span> : <span class='crit_field_value'>" .
                    AfwFormatHelper::decodeAnswerOfAttribute($object, $nom_col, $val_col) .
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
                $object->getSQL() .
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
            $query .= "\n WHERE 1" . $object->getSQL();
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

        $server_db_prefix = AfwSession::config('db_prefix', "default_db_");

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
                    $moduleCol = 'ums';
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



    private static function beforeModification($object, $id, $fields_updated)
    {
        $this_db_structure = $object::getDbStructure(
            $return_type = 'structure',
            $attribute = 'all'
        );
        foreach ($this_db_structure as $attribute => $desc) {
            if ($desc['AUTO-CREATE']) {
                $val = $object->getVal($attribute);
                if (!$val) {
                    $auto_c = $desc['AUTOCOMPLETE'];
                    $auto_c_create = $auto_c['CREATE'];
                    $val_atc = ' .....';

                    if ($auto_c_create) {
                        if ($desc['TYPE'] != 'FK') {
                            throw new AfwRuntimeException(
                                "auto create should be only on FK attributes $attribute is " .
                                    $desc['TYPE']
                            );
                        }
                        $obj_at = AfwStructureHelper::getEmptyObject($object, $attribute);

                        foreach ($auto_c_create
                            as $attr => $auto_c_create_item) {
                            $attr_val = '';
                            if ($auto_c_create_item['CONST']) {
                                $attr_val .= $auto_c_create_item['CONST'];
                            }
                            if ($auto_c_create_item['FIELD']) {
                                $attr_val .=
                                    ' ' .
                                    $object->getVal($auto_c_create_item['FIELD']);
                            }
                            if ($auto_c_create_item['CONST2']) {
                                $attr_val .=
                                    ' ' . $auto_c_create_item['CONST2'];
                            }
                            if ($auto_c_create_item['INPUT']) {
                                $attr_val .= ' ' . $val_atc;
                            }

                            $attr_val = trim($attr_val);

                            $obj_at->set($attr, $attr_val);
                        }

                        $obj_at->insert();

                        $val = $obj_at->getId();

                        $object->set($attribute, $val);
                    }
                }
            }
        }
        return true;
    }


    /**
     * _insert_id
     * Return last insert id
     */
    private static function _insert_id($project_link_name)
    {
        return AfwMysql::insert_id(AfwDatabase::getLinkByName($project_link_name));
    }


    /**
     * insert
     * Insert row
     * @param AFWObject $object
     * @param int $pk : Optional, specify the primary key
     */
    public static function insertObject($object, $pk = '', $check_if_exists_by_uk = true)
    {
        global $lang, $print_debugg, $print_sql, $MODE_BATCH_LOURD;

        // if($object::$TABLE == "afield") die("object->insert on : ".var_export($object,true));
        if ($object->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible to do call to the method insert() with the virtual table ' .
                    $object::$TABLE .
                    '.'
            );
        } elseif ($object->isChanged()) {
            //if($object::$TABLE == "practice_cher") die("will insert into ".$object::$TABLE);
            $user_id = AfwSession::getUserIdActing();
            if (!$user_id) {
                $user_id = 0;
            }
            $object->set($object->fld_CREATION_USER_ID(), $user_id);
            $object->set(
                $object->fld_CREATION_DATE(),
                $object->get_CREATION_DATE_value()
            );
            $object->set($object->fld_UPDATE_USER_ID(), $user_id);
            $object->set(
                $object->fld_UPDATE_DATE(),
                $object->get_UPDATE_DATE_value()
            );
            $object->set($object->fld_VERSION(), 1); // was setAfieldValue ??!!

            if ($pk) {
                $object->set($object->getPKField(), $pk);
            }

            $fields_updated = [];
            $fields_to_insert = $object->getAllfieldsToInsert();

            $dbg_rafik = false;
            if ($dbg_rafik and ($object::$TABLE == "period")) {
                die("afw.insert($pk) before before insert die : object->FIELDS_INITED = " . var_export($object->getAllfieldDefaultValues(), true) . ", 
                              object -> FIELDS_UPDATED = " . var_export($object->fieldsHasChanged(), true) . " 
                              after merge => " . var_export($fields_to_insert, true) . " 
                              object -> AFIELD _VALUE =>" . var_export($object->getAllfieldValues(), true));
            }

            self::beforeModification($object,
                $object->getAfieldValue($object->getPKField()),
                $fields_to_insert
            );
            $can_insert = $object->beforeInsert(
                $object->getAfieldValue($object->getPKField()),
                $fields_to_insert
            );
            // throw new AfwRuntimeException(var_export($object,true));
            if (!$can_insert) {
                $debugg_tech_notes =
                    'warning : beforeInsert refused insert operation. declined insert into ' .
                    $object::$TABLE;
                if ($MODE_BATCH_LOURD) {
                    AfwBatch::print_warning($debugg_tech_notes);
                }
                $information = "<div class='sql warning'> $debugg_tech_notes </div>";
                AfwSession::sqlLog($information, 'HZM');
                $object->debugg_tech_notes = $debugg_tech_notes;
                return false;
            }
            // die("rafik 135001 : ".var_export($object,true));
            // may be has been changed in the previous before insert event
            $fields_to_insert = $object->getAllfieldsToInsert();
            /*
            if($object::$TABLE == "academic_term") 
            {
                die("afw.insert($pk) after before insert die : object->FIELDS_ INITED = ".var_export($object->getAllfieldDefaultValues(),true).", 
                            object -> FIELDS_UPDATED = ".var_export($object->fieldsHasChanged(),true)." 
                            after merge fields_to_insert => ".var_export($fields_to_insert,true)." 
                            object->AFIELD _VALUE =>".var_export($object->getAllfieldValues(),true));
            } */
            

            if (!count($fields_to_insert)) {
                $debugg_tech_notes =
                    'warning : insert operation aborted because no field filled to insert declined insert into ' .
                    $object::$TABLE;
                if ($MODE_BATCH_LOURD) {
                    AfwBatch::print_warning($debugg_tech_notes);
                }
                $information = "<div class='sql warning'> $debugg_tech_notes </div>";
                AfwSession::sqlLog($information, 'HZM');
                $object->debugg_tech_notes = $debugg_tech_notes;
                return false;
            }

            if ($object->UNIQUE_KEY and $check_if_exists_by_uk) {
                $unique_key_vals = [];
                $myClass = $object->getMyClass();
                // $this_copy = cl one $object;
                // $this_copy->clearSelect();
                $this_copy = new $myClass();
                foreach ($object->UNIQUE_KEY as $key_col) {
                    $unique_key_vals[] = $object->getVal($key_col);
                    $this_copy->select($key_col, $object->getVal($key_col));
                }
                if ($this_copy->load() and $this_copy->getId() > 0) {
                    $object->debugg_tech_notes =
                        'has existing doublon id = ' . $this_copy->getId();
                    $dbl_message =
                        $object::$TABLE .
                        ' UNIQUE-KEY-CONSTRAINT : (' .
                        implode(',', $object->UNIQUE_KEY) .
                        ") broken id already exists = ('" .
                        implode("','", $unique_key_vals) .
                        "') " .
                        var_export($this_copy, true);
                    //die("rafik 135004 query($query) : ".var_export($object,true));
                    if ($object->ignore_insert_doublon) {
                        $debugg_tech_notes =
                            'doublon ignored declined insert into ' .
                            $object::$TABLE;
                        $information = "<div class='sql warning'> $debugg_tech_notes </div>";
                        AfwSession::sqlLog($information, 'HZM');
                        $object->debugg_tech_notes = $debugg_tech_notes;
                        return false;
                    } elseif ($object->isFromUI and $user_id != 1) {
                        //die("rafik 135006 query($query) : ".var_export($object,true));
                        return AfwRunHelper::simpleError($dbl_message);
                    } else {
                        throw new AfwRuntimeException($dbl_message);
                    }

                    // $object->set($object->getPKField(), $this_copy->getId());
                    // $object->update();
                }
                //die("rafik 135003 query($query) : ".var_export($object,true));
            }

            $query = 'INSERT INTO ' . $object::_prefix_table($object::$TABLE) . ' SET';
            /*
            if($object::$TABLE == "cher_file") 
            {
                die("before query=$query, fields_to_insert[] = ".var_export($fields_to_insert,true));
            }*/
            // rafik : since version 2.0.1 we put FIELDS_UPDATED the old value

            $gdat_null_if_zeros = AfwSession::config("gdat_null_if_zeros",true);
            
            foreach ($fields_to_insert as $key => $old_value) {
                $cotes = true;
                $value = $object->getAfieldValue($key);
                $structure = AfwStructureHelper::getStructureOf($object, $key);
                if (
                    (isset($structure) && !$structure['CATEGORY']) ||
                    $object->isTechField($key)
                ) {
                    if (strcasecmp($value, 'now()') === 0) {
                        $query .= ' ' . $key . ' = now(),';
                    } elseif ($structure['NO-DELIMITER']) {
                        $query .= " $key = $value,";
                    } elseif ($structure['TYPE'] == 'PK') {
                        if ($value) {
                            $query .= " $key = $value,";
                        }
                    } elseif (
                        $structure['TYPE'] == 'FK' or
                        $structure['TYPE'] == 'INT'
                    ) {
                        if (!$value) {
                            $value = '0';
                        }
                        $query .= " $key = $value,";
                    } else {
                        if (($structure['TYPE'] == 'GDAT') or ($structure['TYPE'] == 'GDATE')) 
                        {
                            if ((!$value) or ($value=="0000-00-00 00:00:00") or ($value=="0000-00-00"))
                            {
                                if($gdat_null_if_zeros)
                                {
                                        $value = "NULL";
                                        $cotes = false;
                                }
                                else
                                {
                                        $value = "0000-00-00";
                                }
                            }
                        }
                        if ($structure['UTF8']) {
                            $_utf8 = '_utf8';
                        } else {
                            $_utf8 = '';
                        }

                        if($cotes)
                        {
                            $query .= ' ' . $key . " = $_utf8'" . AfwStringHelper::_real_escape_string($value) . "',";
                        }
                        else
                        {
                            $query .= ' ' . $key . " = " . AfwStringHelper::_real_escape_string($value) . ",";
                        }
                        
                    }
                    $fields_updated[$key] = $value;
                    /*
                    if($key=='field_width')
                    {
                       die("object->getAllfieldValues() = ".var_export($object->getAllfieldValues(),true)." fields_updated=".var_export($fields_updated,true)); 
                    }*/
                }
            }
            $query = trim($query, ',');
            //die("rafik 135002 query($query) : ".var_export($object,true));
            //die($query);
            // throw new AfwRuntimeException("should not query : $query");
            /*
            if(($object::$TABLE == "applicant") and 
               (contient($query, "INSERT INTO"))) 
            {
                   die("INSERT INTO to be executed : $query, "."<br>fields_to_insert[] = ".var_export($fields_to_insert,true));
            }*/
            
            //if(!contient($query, "SELECT")) die("query to be executed : $query");
            $return = $object->execQuery($query);

            $my_pk = $object->getPKField();
            $curr_id = $object->getId();
            //die("rafik 13/5 : $my_pk = $curr_id ");

            if ($return) {
                if ($my_pk) {
                    if (!$curr_id) {
                        $my_id = self::_insert_id($object->getProjectLinkName());
                        $object->set($my_pk, $my_id);
                        $object->debugg_tech_notes = "set PK($my_pk) = $my_id ";
                    } else {
                        $object->debugg_tech_notes = "my PK($my_pk) already setted to $curr_id . ";
                    }
                } else {
                    throw new AfwRuntimeException(
                        'MOMKEN SQL Problem : PK is not defined for table :  ' .
                            $object::$TABLE
                    );
                }
                $my_setted_id = $object->getId();
                if (!$my_setted_id) {
                    throw new AfwRuntimeException(
                        'MOMKEN SQL Problem : insert into ' .
                            $object::$TABLE .
                            " has not been done correctly as id recolted is null, query : $query"
                    );
                }

                $object->debugg_tech_notes .= " value setted for PK($my_pk) = $my_setted_id ";
                $object->afterInsert($my_setted_id, $fields_updated);

                if ($print_debugg and $print_sql) {
                    echo "<br>\n ############################################################################# <br>\n";
                    echo "<br>\n record inserted by query : $query id = $my_setted_id <br>\n";
                    echo "<br>\n ############################################################################# <br>\n";
                }

                return $my_setted_id;
            } else {
                $object->debugg_tech_notes = "Error occured when executing query : $query";
                return false;
            }
        } else {
            throw new AfwRuntimeException(
                "Insert declined because no fields updated, 
                       AFIELD_ VALUE=" .
                    var_export($object->getAllfieldValues(), true) .
                    ", 
                       FIELDS_UPDATED=" .
                    var_export($object->fieldsHasChanged(), true) .
                    ", 
                       FIELDS_ INITED=" .
                    var_export($object->getAllfieldDefaultValues(), true) .
                    ", 
                       object = " .
                    var_export($object, true)
            );
            return false;
        }
    }


    /**
     * update
     * Update row
     */
    public static function updateObject($object, $only_me = true)
    {
        if($object->IS_COMMITING) throw new AfwRuntimeException("To avoid infinite loop avoid to commit inside beforeMaj beforeUpdate beforeInsert context methods");
        $object->IS_COMMITING = true;
        global $AUDIT_DISABLED, $the_last_update_sql;

        $user_id = AfwSession::getUserIdActing();

        if ($object->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible to do call to the method update() with the virtual table ' .
                $object::$TABLE .
                    '.'
            );
        } else {
            //if((static::$TABLE=="student_session") and ($object->getVal("xxxxx")==102937)) throw new AfwRuntimeException("object-> FIELDS_UPDATED = ".var_export($object-> FIELDS_UPDATED,true));
            //if((static::$TABLE=="student_session")) throw new AfwRuntimeException("object-> FIELDS_UPDATED = ".var_export($object-> FIELDS_UPDATED,true));



            if ($only_me) {
                $id_updated = $object->getId();
                if (!$id_updated) {
                    throw new AfwRuntimeException(
                        "$object : if update only one record mode, the Id should be specified ! obj = " .
                            var_export($object, true)
                    );
                }
            } else {
                $id_updated = '';
            }

            if ($only_me) {
                if ($object->CORRECT_IF_ERRORS and !$object->isOk(true)) {
                    $object->repareBeforeUpdate();
                }

                if ($object->isChanged()) {
                    self::beforeModification($object,
                        $id_updated,
                        $object->fieldsHasChanged()
                    );
                    $can_update = $object->beforeUpdate(
                        $id_updated,
                        $object->fieldsHasChanged()
                    );
                    /*
                    if(static::$TABLE == "student_session") 
                    {
                        throw new AfwRuntimeException(static::$TABLE." updating ... fields updated count = ".count($object-> FIELDS_UPDATED)." / beforeUpdate accepted update ? = $can_update / FIELDS_UPDATED = " . var_export($object-> FIELDS_UPDATED,true));
                    }*/
                    if (!$can_update) {
                        $object->debugg_reason_non_update =
                            'beforeUpdate refused update';
                    }
                } else {
                    $object->debugg_reason_non_update = ' no fields updated';
                    $can_update = false;
                }
            } else {
                $can_update = true;
            }

            if ($can_update) {
                if ($object->AUDIT_DATA and !$AUDIT_DISABLED) {
                    //die("call to $object ->audit_before_update(..) : ".var_export($object-> FIELDS_UPDATED,true));
                    $object->audit_before_update($object->fieldsHasChanged());
                } else {
                    // if(....) die("no call to $object ->audit_before_update(..) : ".var_export($object-> FIELDS_UPDATED,true));
                }

                //if((!$arr_tables_without_technical_fields) or (array_search(static::$TABLE, $arr_tables_without_technical_fields) === false)) {

                if (!$user_id) {
                    $user_id = 0;
                }
                $object->set($object->fld_UPDATE_USER_ID(), $user_id);
                $object->set(
                    $object->fld_UPDATE_DATE(),
                    $object->get_UPDATE_DATE_value()
                );
                if ($only_me and $object->getId()) {
                    $ver = $object->getVersion() + 1;
                    $object->set($object->fld_VERSION(), $ver);
                } else {
                    $ver = $object->fld_VERSION() . '+1';
                }
                //}

                /*
                if(static::$TABLE == "student_session") 
                {
                    die(static::$TABLE." updating ... before get S Q L Update(user_id=$user_id,ver=$ver,id_updated=$id_updated) fields updated count = ".count($object-> FIELDS_UPDATED)." / can update = $can_update / FIELDS_UPDATED = " . var_export($object-> FIELDS_UPDATED,true));
                }
                */

                
        

                list($query, $fields_updated, $report) = AfwSqlHelper::getSQLUpdate($object, $user_id, $ver, $id_updated);

                /*
                if(static::$TABLE == "student_session") 
                {
                    die(static::$TABLE." updating ... after get S Q L Update(user_id=$user_id,ver=$ver,id_updated=$id_updated) fields updated count = ".count($fields_updated)." / query = $query / report=$report/ fields_updated = " . var_export($fields_updated,true));
                }
                */


                $return = 0;
                if ($can_update) {
                    $the_last_update_sql .= " --> " . var_export($fields_updated, true) . " SQL = $query";
                    if ($object->showQueryAndHalt) {
                        throw new AfwRuntimeException(
                            'showQueryAndHalt : updated fields = ' .
                                $object->showArr($fields_updated) .
                                '<br> report = ' .  $report .
                                '<br> query = ' .  $query
                        );
                    }
                    if (count($fields_updated) > 0) {

                        $object->execQuery($query);
                        $return = $object->_affected_rows(
                            $object->getProjectLinkName()
                        );
                    } else {
                        $object->debugg_reason_non_update = 'nothing updated';
                        $return = 0;
                    }

                    if ($only_me and (count($fields_updated) > 0)) {
                        $object->IS_COMMITING = false;
                        $object->afterUpdate($id_updated, $fields_updated);
                    }
                    if ($only_me and $return > 1) {
                        throw new AfwRuntimeException(
                            "MOMKEN error affected rows = $return, strang for query : ",
                            $query .
                                '///' .
                                AfwMysql::get_error(
                                    AfwDatabase::getLinkByName(
                                        $object->getProjectLinkName()
                                    )
                                )
                        );
                    } else {
                        if ($only_me and $id_updated) {
                            AfwCacheSystem::getSingleton()->removeObjectFromCache(
                                $object::$MODULE,
                                $object::$TABLE,
                                $id_updated
                            );
                        } else {
                            AfwCacheSystem::getSingleton()->removeTableFromCache(
                                $object::$MODULE,
                                $object::$TABLE
                            );
                        }
                    }
                } else {
                }

                $object->IS_COMMITING = false;
                $object->resetUpdates();
                return $return;
            } else {
                /*
                if(static::$TABLE=="student_session")
                {
                   die("can not update, reason : ".$object->debugg_reason_non_update." : ".static::$TABLE." FIELDS_UPDATED : <br> ".$object->showArr($object-> FIELDS_UPDATED));
                }
                */
                //throw new AfwRuntimeException();
                $the_last_update_sql .= " --> can not update : " . $object->debugg_reason_non_update;
                $object->IS_COMMITING = false;
                return 0;
            }
        }
        $object->IS_COMMITING = false;
    }


    /**
     * hide  different then logicDelete by 2 things
     *     1. hide operate on one record only  and logicDelete can operate many records
     *     2. execute beforeHide and afterHide events
     * Hide row by setting AVAILABLE_IND = 'N'
     */
    public static function hideObject($object)
    {
        $me = AfwSession::getUserIdActing();
        if ($object->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible to do call to the method hide() with the virtual table ' .
                $object::$TABLE .
                    '.'
            );
        } else {
            if ($object->AUDIT_DATA and !$object->AUDIT_DISABLED) {
                $object->audit_before_update([$object->fld_ACTIVE() => 'N']);
            }

            $user_id = $me;
            if (!$user_id) {
                $user_id = 0;
            }

            $return = false;

            if ($object->beforeHide($object->getAfieldValue($object->getPKField()))) {
                $object->set($object->fld_UPDATE_USER_ID(), $user_id);
                $object->set($object->fld_UPDATE_DATE(), 'now()');
                $ver = $object->getVersion() + 1;
                $object->set($object->fld_VERSION(), $ver);

                $query =
                    'UPDATE ' . $object::_prefix_table($object::$TABLE) . ' SET ';

                $query .=
                    $object->fld_UPDATE_USER_ID() .
                    ' = ' .
                    $user_id .
                    ', ' .
                    $object->fld_UPDATE_DATE() .
                    ' = now(), ' .
                    $object->fld_VERSION() .
                    " = $ver, ";

                $query .=
                    $object->fld_ACTIVE() .
                    " = 'N' WHERE " .
                    $object->getPKField() .
                    " = '" .
                    $object->getAfieldValue($object->getPKField()) .
                    "'";
                $return = $object->execQuery($query);

                $object->afterHide($object->getAfieldValue($object->getPKField()));
            }

            return $return;
        }
    }


    public static function force_update_date($object, $update_datetime_greg)
    {
        $my_id = $object->getId();
        if ($my_id and $object->CAN_FORCE_UPDATE_DATE) {
            $table_prefixed = $object::_prefix_table($object::$TABLE);
            $query = 'UPDATE ' . $table_prefixed . ' SET ';
            $query .= $object->fld_UPDATE_DATE() . " = '$update_datetime_greg' ";
            $query .= 'WHERE ' . $object->getPKField() . " = '$my_id'";

            $object->execQuery($query);
            $return = $object->_affected_rows($object->getProjectLinkName());
        } else {
            $return = -1;
        }

        return $return;
    }

    public static function force_creation_date($object, $update_datetime_greg)
    {
        $my_id = $object->getId();
        if ($my_id and $object->CAN_FORCE_UPDATE_DATE) {
            $table_prefixed = $object::_prefix_table($object::$TABLE);
            $query = 'UPDATE ' . $table_prefixed . ' SET ';
            $query .=
                $object->fld_CREATION_DATE() . " = '$update_datetime_greg', ";
            $query .= $object->fld_UPDATE_DATE() . " = '$update_datetime_greg' ";
            $query .= 'WHERE ' . $object->getPKField() . " = '$my_id'";

            $object->execQuery($query);
            $return = $object->_affected_rows($object->getProjectLinkName());
        } else {
            $return = -1;
        }

        return $return;
    }


    public static function simulateUpdate($object, $only_me = true)
    {
        $user_id = AfwSession::getUserIdActing();
        if ($only_me) {
            $id_updated = $object->getId();
        } else {
            $id_updated = '';
        }
        $ver = $object->fld_VERSION() . '+1';

        $can_update = $object->beforeUpdate($id_updated,$object->fieldsHasChanged());

        if (!$can_update) throw new AfwRuntimeException("can't update beforeUpdate refused : object-> FIELDS_UPDATED = " . var_export($object->fieldsHasChanged(), true));

        return AfwSqlHelper::getSQLUpdate($object, $user_id, $ver, $id_updated);
    }


    /**
     * old func on AFWObject become here and renamed as aggregFunction
     * return execute of aggregFunction on table after filter where
     * @param AFWObject $object
     * @param string $function
     */
    public static function aggregFunction($object, 
        $function,
        $group_by = '',
        $throw_error = true,
        $throw_analysis_crash = true
    ) {
        $module_server = $object->getModuleServer();
        if (!$function) {
            $function = 'count(*)';
        }
        if ($group_by) {
            $group_by_tab = explode(',', $group_by);
            $query_select = ', ' . $group_by;
            $query_group_by = ' group by ' . $group_by;
        } else {
            $group_by_tab = [];
            $query_select = '';
            $query_group_by = '';
        }
        $query =
            'select ' .
            $function .
            ' as res' .
            $query_select .
            "\n from " .
            $object->getMyTable(true).
            " me\n where 1" .
            $object->getSQL() .
            $query_group_by;


        $object->clearSelect();
        if (count($group_by_tab)) {
            $return = [];
            $query_res = AfwDatabase::db_recup_rows($query, $throw_error, $throw_analysis_crash, $module_server);
            foreach ($query_res as $row) {
                foreach ($group_by_tab as $index) {
                    $index = trim($index);
                    $return[$row[$index]] = $row['res'];
                }
            }
        } else {
            $return = AfwDatabase::db_recup_value(
                $query,
                $throw_error,
                $throw_analysis_crash,
                $module_server
            );
        }
        
        return $return;
    }

    /**
     * old func on AFWObject become here and renamed as aggregFunction
     * return execute of aggregFunction on table after filter where
     * @param AFWObject $object
     * @param string $function
     */
    public static function multipleAggregFunction($object, 
        $functions,
        $group_by = '',
        $throw_error = true,
        $throw_analysis_crash = true
    ) {
        $module_server = $object->getModuleServer();
        $query = 'select '.$group_by;

        if ($group_by) {
            $group_by_tab = explode(',', $group_by);
            $query_group_by = ' group by ' . $group_by;
        } else {
            $group_by_tab = [];
            $query_group_by = '';
        }
        
        foreach ($functions as $functionCode => $function)
        {
            $query .= ", $function as $functionCode";
        }
            
        $query .= "\n from " .
            $object->getMyTable(true).
            " me\n where 1" .
            $object->getSQL() .
            $query_group_by;


        $object->clearSelect();
        
        return [$query, AfwDatabase::db_recup_rows($query, $throw_error, $throw_analysis_crash, $module_server)];

    }


    /**
     * old count on AFWObject become here and renamed as aggregCount
     * return execute of aggregFunction on table after filter where
     * @param AFWObject $object
     * @param string $function
     */
    public static function aggregCount($object, $throw_error = true, $throw_analysis_crash = true)
    {
        $query =
            "select count(*) as cnt \n from " .
            $object->getMyTable(true).
            " me\n where 1" .
            $object->getSQL();

        $object->clearSelect();

        $module_server = $object->getModuleServer();
        $return = AfwDatabase::db_recup_value(
            $query,
            $throw_error,
            $throw_analysis_crash,
            $module_server
        );

        //if((!$return) or (static::$TABLE == "school_class"))
        //die("query=$query return=$return");
        return $return;
    }

    

}
