<?php
class AfwLoadHelper extends AFWRoot
{
    private static $lookupMatrix;

    public static function getLookupProps($nom_module_fk, $nom_table_fk)
    {
        $nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
        $object = new $nom_class_fk();


        return [$object->IS_LOOKUP, $object->IS_SMALL];
    }

    public static function getLookupData($nom_module_fk, $nom_table_fk, $where="--")
    {
        if(!$nom_module_fk) throw new AfwRuntimeException("nom_module_fk is mandatory attribute for AfwLoadHelper::getLookupData");
        if(!$where) $where="--";
        
        if(!self::$lookupMatrix["$nom_module_fk.$nom_table_fk.$where"])
        {
            $nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
            $object = new $nom_class_fk();
            $where_cleaned = str_replace("((id))",$object->getPKField(),$where);
            $dataLookup = self::loadAllLookupData($object,$where_cleaned);
            // merge into global lookup data
            foreach($dataLookup as $lkp_id => $lkp_val)
            {
                self::$lookupMatrix["$nom_module_fk.$nom_table_fk"][$lkp_id] = $lkp_val;
            }
            self::$lookupMatrix["$nom_module_fk.$nom_table_fk.$where"] = $dataLookup;
        }

        //if($nom_table_fk=="module") die("getLookupData($nom_module_fk, $nom_table_fk, $where) using self::lookupMatrix=".var_export(self::$lookupMatrix,true));

        return self::$lookupMatrix["$nom_module_fk.$nom_table_fk.$where"];
    } 

    public static function vhGetListe($obj, $where, $action="default", $lang="ar", $val_to_keep=null, $order_by="", $dropdown = false, $optim = true)
    {
        if(!$where) $where = "1";
        if($action=="default") 
        {
            if($obj->IS_LOOKUP) $action="liste";
            else $action="loadMany";
        }

        // die("vhGetListe($obj, $where, $action) => action=$action");

        if($action=="loadManyFollowingStructure") 
        {
            if($obj->IS_LOOKUP) $action="liste";
        }

        if($action=="liste") 
        {
            
            if($val_to_keep)
            {
                $val_to_keep = trim($val_to_keep);
                $val_to_keep = trim($val_to_keep, ',');
                if($val_to_keep)
                {
                    $pk = $obj->getPKField();
                    $where = "($where) or ($pk in ($val_to_keep))";
                }                
            }
            $return = self::getLookupData($obj->getMyModule(), $obj->getMyTable(), $where, $val_to_keep);
        }
        else
        {
            $obj->select_visibilite_horizontale();
            if($where) $obj->where($where);

            if($action=="count") $return = $obj->func("count(*)");
            if(($action=="loadManyFollowingStructure") or ($action=="loadMany"))
            {
                if($action=="loadMany") 
                {
                    $listeRep = $obj->loadMany('',$order_by, $optim);
                    // die("$obj --> loadMany('',$order_by, $optim) => ".var_export($listeRep,true));
                }

                if($action=="loadManyFollowingStructure") 
                {
                    $desc=[];
                    $desc['LOAD_ALL']=false;
                    $desc['NO_KEEP_VAL'] = $val_to_keep ? false : true;
                    $desc['WHERE'] = $where;
                    $desc['ORDERBY'] = $order_by;
                    list($sql,$listeRep) = self::loadManyFollowingStructureAndValue($obj,$desc,$val_to_keep, null, $dropdown, $optim);
                    
                }
                $return = self::constructDropDownItems($listeRep, $lang, 'fk-somewhere-on:'. $obj->getTableName(),'table-somewhere');
            }
        }

        return $return;
    }

    public static final function constructDropDownItems($liste_rep, $lang, $col_name = '', $table_name = '', $report='')
    {
        $objme = AfwSession::getUserConnected();
        $MAX_DROPDOWN_ITEMS = AfwSession::config('max_dropdown_items', 300);
        $count_liste_rep = count($liste_rep);
        if ($count_liste_rep > $MAX_DROPDOWN_ITEMS) {
            // $first_item = current($liste_rep);
            throw new AfwRuntimeException("Too much items to put into dropdown for field : [$table_name.$col_name] (count = $count_liste_rep), 
                    it is recommended to use AUTOCOMPLETE and AUTOCOMPLETE-SEARCH options, report : $report");
        }

        $l_rep = [];
        foreach ($liste_rep as $iditem => $item) {
            if (AfwUmsPagHelper::userCanDoOperationOnObject($item,$objme, 'display')) {
                if(is_object($item)) $l_rep[$iditem] = $item->getDropDownDisplay($lang);
                else $l_rep[$iditem] = $item;
            }
            /*
            idea of rafik to create another method returning refused items and show reason inside html @todo
            else
            {
                $userCanNotDoOperationReason = AfwUmsPagHelper::userCanNotDoOperationOnObjectReason($item,$objme, 'display');
                echo "<!-- drop down item-option hidden : $item reason $userCanNotDoOperationReason -->";
            }*/
        }

        return $l_rep;
    }

    public static function lookupDecodeValues($nom_module_fk, $nom_table_fk, $val, $separator, $emptyMessage, $pk, $small_lookup=false)
    {
        if(!$val) return "";
        
        if(is_string($val) and AfwStringHelper::stringContain($val,",")) // it is mfk
        {
            $val_0 = trim($val,",");
            $val_arr = explode(",", $val);
            $is_array = true;
        }
        else
        {
            $val_arr = [];
            $val_arr[] = $val;
            $is_array = false;
        }

        foreach($val_arr as $k => $kval)
        {
            if(!$kval) unset($val_arr[$k]);
        }

        if((!$small_lookup) and (count($val_arr)>0)) $where="$pk in (".implode(",",$val_arr).")";
        else $where="1";
        self::getLookupData($nom_module_fk, $nom_table_fk, $where);

        if(count($val_arr)>0)
        {
            $decodedValues_arr = [];
            foreach($val_arr as $val_item)
            {
                $decodedValues_arr[] = self::$lookupMatrix["$nom_module_fk.$nom_table_fk"][$val_item];
            }

            return implode($separator,$decodedValues_arr);
        }
        elseif($is_array) return "<div class='empty_message'>" . $emptyMessage .'</div>';
        else 
        {
            //if($nom_table_fk=="country" and $val=="183") die("rafik 231214-1842 ".var_export(self::$lookupMatrix,true));
            return "";
        }
    } 



    private static function getTheObjectCacheForAttribute($theObject, $attribute, $object_id)
    {
        // 1. try from local small cache for attribute
        if (
            is_object($theObject->OBJECTS_CACHE[$attribute]) and
            $theObject->OBJECTS_CACHE[$attribute]->getId() > 0
        ) {
            $object = $theObject->OBJECTS_CACHE[$attribute];
            // si old cache a supprimer
            if ($object->getId() != $object_id) {
                unset($theObject->OBJECTS_CACHE[$attribute]);
                $object = null;
            }
        }

        return $object;
    }


    public static final function cacheManagement($myObject)
    {

        global $DISABLE_CACHE_MANAGEMENT;

        if (!$DISABLE_CACHE_MANAGEMENT) $cache_management = AfwSession::config('cache_management', true);
        else $cache_management = false;

        $cache_management = true;

        return $cache_management;
    }

    /**
     * loadObjectFK
     * Load attribute's object
     * @param string $attribute
     * @param boolean $integrity : Optional specify if throws an exception when we have no result or non existing object result (ie. FK constraint broken)
     */

    public static function loadObjectFKFor($myObject, $attribute, $integrity = true, $optim_lookup=true)
    {
        global $MODE_BATCH_LOURD,
            $boucle_loadObjectFK,
            $boucle_loadObjectFK_arr,
            $object_id_to_check_repeat_of_load,
            $object_attribute_to_check_repeat_of_load,
            $object_table_to_check_repeat_of_load,
            $repeat_of_load_of_audited_object;

        $cache_management = self::cacheManagement($myObject);

        $this_getId = $myObject->getId();
        $this_table = $myObject->getTableName();
        $call_method = "loadObjectFKFor(object[$this_getId], attribute = $attribute, integrity = $integrity)";
        if (!$MODE_BATCH_LOURD) 
        {
            if (!$boucle_loadObjectFK) {
                $boucle_loadObjectFK = 0;
                $boucle_loadObjectFK_arr = [];
            }
            $boucle_loadObjectFK_arr[$boucle_loadObjectFK] = "loadObjectFK of attribute $attribute from [$this_table,$this_getId] object";
            $boucle_loadObjectFK++;

            if ($boucle_loadObjectFK > 20000) {
                // 20000 because many calls are just to get data from cache so very quick
                throw new AfwRuntimeException("heavy page halted after $boucle_loadObjectFK enter to method $call_method in one request, " .var_export($boucle_loadObjectFK_arr, true));
            }
        }
        


        list($ansTab, $ansMod) = $myObject::answerTableAndModuleFor($attribute);
        /*
         if(($attribute=="campaign") or ($attribute=="practice_campaign_id"))
         {
             die("degugg 1 rafik3 attribute=$attribute ansTab=$ansTab ansMod=$ansMod ");
         }*/
        
        $object_id = $myObject->getAfieldValue($attribute);

        if (isset($ansTab) && intval($object_id) > 0) {
            $object = null;
            $object_loaded = false;

            $loadObjectFK_step = 0;
            // 1. try from local small cache for attribute
            $object = self::getTheObjectCacheForAttribute($myObject, $attribute, $object_id);
            if($object)
            {
                $loadObjectFK_step = 1;
            }
            // 2. otherwise try from global cache management
            elseif ($cache_management) {
                $className = AfwStringHelper::tableToClass($ansTab);
                if (!$className) throw new AfwRuntimeException("for attribute $attribute of $this_table we can not calc tableToClass(answer table = $ansTab)");

                $loadObjectFK_step = 2;
                $object = &AfwCacheSystem::getSingleton()->getFromCache(
                    $ansMod,
                    $ansTab,
                    $object_id
                );

                if (!$object) {
                    $object = null;
                } else {
                    $return = $object;
                }
            }

            // 3. otherwise load object
            if (!$object) {
                $loadObjectFK_step = 3;
                $object = new $className();
                // $object->setMyDebugg($object->MY_DEBUG);

                if ($object->load($object_id,'','', $optim_lookup)) {
                    $object_loaded = true;
                    $return = $object;
                } elseif ($integrity) {
                    $struct = AfwStructureHelper::getStructureOf($myObject, $attribute);
                    if ($struct['MANDATORY']) {
                        throw new AfwRuntimeException(
                            "The mandatory attribute $attribute of " .
                                $className .
                                ' has empty object for value : ' .
                                $object_id .
                                '.'
                        );
                    } else {
                        $return = $object;
                    }
                } else {
                    $return = $object;
                }
            } else {
                $return = $object;
            }


            if ($cache_management and $object_loaded) 
            {
                if (is_object($return) and $return->getId() > 0) 
                {
                    $object_id = $return->getId();

                    AfwCacheSystem::getSingleton()->putIntoCache(
                        $ansMod,
                        $ansTab,
                        $return,
                        '',
                        $myObject->getMyModule() .
                        '.' .
                        $myObject->getMyTable() .
                        '.' .
                        $attribute
                    );
                }
            }

            if ($cache_management and $return and ($return->getId() == $object_id)) {
                $myObject->setMySmallCacheForAttribute($attribute, $return);
            }
        } 
        else 
        {
            if ($integrity) {
                $this_id = $myObject->getId();
                throw new AfwRuntimeException(
                    'For object [' .
                        $myObject->getMyTable() .
                        ":(id=$this_id)] => loadObject FK(attribute=$attribute,integrity = true) has failed. \nPlease check data integrity.\n Answer table of [" .
                        $attribute .
                        '] = ' .
                        $ansMod .
                        '.' .
                        $ansTab .
                        ',value of field [' .
                        $attribute .
                        '] = [' .$object_id .'].'
                );
            } else {
                $return = null;
            }
        }

        return $return;
    }


    private static final function loadManyFollowingStructureAndValue(
        $answerTableObj,
        $desc,
        $val,
        $parentObj,  // used only if $desc['AT_METHOD'] otherwise put null
        $dropdown = false,
        $optim = true
    ) 
    {
       
        if ($desc['LOAD_ALL']) {
            $sql = 'LOAD_ALL :: this->loadLookupData()';
            $liste_rep = $answerTableObj->loadLookupObjects($desc['ORDERBY']);
        } elseif ($desc['AT_METHOD']) {
            $at_method = $desc['AT_METHOD'];
            $sql = "parentObj->$at_method()";
            $liste_rep = $parentObj->$at_method();
        } else {
            if (!$desc['NO_KEEP_VAL']) {
                $val_to_keep = $val;
            } else {
                $val_to_keep = '';
            }

            if ($desc['WHERE']) {
                $answerTableObj->where($desc['WHERE'], $val_to_keep);
                $nowhere = ' : where = ' . $desc['WHERE'];
            } else {
                $nowhere = ' : nowhere';
            }

            $answerTableObj->select_VH($val_to_keep, $dropdown);

            $sql =
                $answerTableObj->getSQLMany('', '', $desc['ORDERBY'], $optim) .
                $nowhere .
                " : optim=$optim";
            $answerTableObj->debugg_sql_for_loadmany = $sql;
            $liste_rep = $answerTableObj->loadMany('', $desc['ORDERBY'], $optim);
            //die("liste_rep=".var_export($liste_rep,true));
            unset($answerTableObj->debugg_sql_for_loadmany);
        }

        return [$sql, $liste_rep];
    }

    public static function getRetrieveDataFromObjectList($liste_obj, $header, $lang = 'ar', $newline = "\n<br>") 
    {
        $objme = AfwSession::getUserConnected();

        $data = [];
        $isAvail = [];
        

        foreach ($liste_obj as $id => $objItem) 
        {
            if (is_object($objItem) and AfwUmsPagHelper::userCanDoOperationOnObject($objItem, $objme, 'display')) 
            {
                $objIsActive = $objItem->isActive();
                $tuple = [];
                $tuple['display_object'] = $objItem->getDisplay($lang);
                if (count($header) != 0) 
                {
                    foreach ($header as $col => $titre) 
                    {
                        
                        if (!$col) throw new AfwRuntimeException('header columns erroned, column empty : ' .var_export($header, true));
                        $desc = AfwStructureHelper::getStructureOf($objItem, $col);
                        if (!$objItem->attributeIsApplicable($col)) {
                            list($icon,$textReason,$wd,$hg,) = $objItem->whyAttributeIsNotApplicable($col);
                            if (!$wd) $wd = 20;
                            if (!$hg) $hg = 20;
                            $tuple[$col] = "<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='$textReason'  width='$wd' heigth='$hg'>";
                        } 
                        elseif ($objItem->dataAttributeCanBeDisplayedForUser($col,$objme,'DISPLAY',$desc)) 
                        {
                            $tuple[$col] = AfwShowHelper::quickShowAttribute($objItem, $col, $lang, $desc, $newline);
                        } 
                        else 
                        {
                            $textReason = $objItem->translateMessage('DATA_PROTECTED',$lang);
                            $tuple[$col] = "<img src='../lib/images/lock.png' data-toggle='tooltip' data-placement='top' title='$textReason'  width='20' heigth='20'>";
                        }
                    }
                }
                $data[$id] = $tuple;
                $isAvail[$id] = $objIsActive;
                // $count_liste_obj++;
            }
        }

        return [$data, $isAvail];
    }

    /**
     * @param AFWObject $object : afw object instance
     * @param string $attribute : attribute to decode should be of type FK
     * 
     */
    
    public static function decodeFkAttribute($object, $attribute, $value, $separator = ",", $emptyMessage = "no-data-decoded")
    {
        if(!$object) throw new AfwRuntimeException("decodeFkAttribute function : \$object attribute should not be null");
        if(!($object instanceof AFWObject)) throw new AfwRuntimeException("decodeFkAttribute function : \$object attribute should be subclass of AFWObject");
        $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        if($structure["TYPE"] != "FK") throw new AfwRuntimeException("$attribute attribute is not of type FK from class ".$object->getMyClass());
        $ans_module = $structure["ANSMODULE"];
        $ans_table = $structure["ANSWER"];
        $pk = $object->getPKField();
        

        return self::decodeLookupValue($ans_module, $ans_table, $value, $separator, $emptyMessage, $pk, $structure["SMALL-LOOKUP"]);
    }

    public static function decodeLookupValue($ans_module, $ans_table, $value, $separator, $emptyMessage, $pk, $small_lookup=false)
    {

        if(self::$lookupMatrix["$ans_module.$ans_table"][$value]) return self::$lookupMatrix["$ans_module.$ans_table"][$value];
        if(self::$lookupMatrix["$ans_module.$ans_table.--"][$value]) return self::$lookupMatrix["$ans_module.$ans_table.--"][$value];
        if(self::$lookupMatrix["$ans_module.$ans_table.++"][$value]) return self::$lookupMatrix["$ans_module.$ans_table.++"][$value];
        if(self::$lookupMatrix["$ans_module.$ans_table.1"][$value]) return self::$lookupMatrix["$ans_module.$ans_table.1"][$value];
        
        self::lookupDecodeValues($ans_module, $ans_table, $value, $separator, $emptyMessage, $pk, $small_lookup);
        return self::$lookupMatrix["$ans_module.$ans_table"][$value];
    }

    public static function loadAllLookupData($object, $where = "--")
    {
        if(!$where)  $where = "--";
        if($where == "--")
        {
            $active_fld = $object->fld_ACTIVE();
            $where = "$active_fld = 'Y'";
        }

        if($where == "++")
        {
            $where =  $object->get_visibilite_horizontale();            
        }
        
        if(isset($object->DISPLAY_FIELD)) 
        {
            if(is_array($object->DISPLAY_FIELD))
            {
                $display_field = "concat(".implode(",'-',",$object->DISPLAY_FIELD).")";
            }
            else $display_field = trim($object->DISPLAY_FIELD);
        }

        if (!$display_field) {
            if(isset($object->FORMULA_DISPLAY_FIELD)) $display_field = trim($object->FORMULA_DISPLAY_FIELD);
        }

        if (!$display_field)  {
            if(isset($object->AUTOCOMPLETE_FIELD)) 
            {
                if(is_array($object->AUTOCOMPLETE_FIELD))
                {
                    $display_field = "concat(".implode(",'-',",$object->AUTOCOMPLETE_FIELD).")";
                }
                else $display_field = trim($object->AUTOCOMPLETE_FIELD);
            }
            
        }

        

        if (!$display_field) {
            throw new AfwRuntimeException('afw class : ' . $object->getMyClass() . ' : method loadLookupData does not work without one of AUTOCOMPLETE_FIELD or DISPLAY_FIELD or FORMULA_DISPLAY_FIELD attributes specified for the object');
        }

        // $orderBy = trim($object->ORDER_BY_FIELDS);

        $module = $object::$MODULE;
        $table = $object::$TABLE;
        $server_db_prefix = AfwSession::config('db_prefix', 'c0');

        $pk = $object->getPKField();


        return AfwDatabase::db_recup_index("select $pk, $display_field as __val from $server_db_prefix" . $module . ".$table where $where", $pk, "__val");

    }

    
    /**
     * loadData
     * Load into an array of values returned rows
     * @param object $object : afw object instance
     * @param array $attribute_arr : liste attributes to retrieve
     * @param string $limit : Optional add limit to query
     * @param string $order_by : Optional add order by to query
     * @param bool $distinct : Make the select distinct to avoid duplicated records (same value for all columns)
     */
    public static function loadData($object, $attribute_arr, $limit = '', $order_by = '', $distinct=false)
    {
        if(!$order_by) $order_by = $object->ORDER_BY_FIELDS;
        $query =
            'SELECT ' . ($distinct ? 'DISTINCT ' : '') .
            implode(",",$attribute_arr) .
            " FROM " . $object->getMyPrefixedTable() .
            " me\n WHERE 1" . $object->getSQL() .
            ($order_by ? "\n ORDER BY " . $order_by : '') .
            ($limit ? ' LIMIT ' . $limit : '');

        return [$object::sqlRecupRows($query), $query];
    }



    /**
     * load
     * Load into object a specified row
     * @param AFWObject $object
     * @param string $value : Optional, specify the value of primary key
     */
    public static function loadAfwObject($object, $value = '', $result_row = '', $order_by_sentence = '', $optim_lookup=true) 
    {
        global $MODE_BATCH_LOURD;
        // $time_start = microtime(true);


        // if($value == 6082) die("load case cache_management=$cache_management loading $className[$value] result_row=".var_export($result_row));
        //$result_row_from = 'load call as result_row = ' . var_export($result_row, true);

        $className = $object->getMyClass();
        $classNameModule = $object->getMyModule();
        $classNameTable = $object->getMyTable();
        
        if ((!$result_row) and $optim_lookup and $object->IS_LOOKUP)  // may be to add : and $object->IS_SMALL
        {
            global $load_count;
            
            if (!$load_count[$className]["any"]) $load_count[$className]["any"] = 0;
            $load_count[$className]["any"]++;
            if ((!$MODE_BATCH_LOURD) and ($load_count[$className]["any"] > 3)) {
                throw new AfwRuntimeException("All the lookup table $className should be loaded once, not record by record");
            }
        }

        if ($value) {
            if (!$load_count[$className][$value]) $load_count[$className][$value] = 0;
            $load_count[$className][$value]++;
            if ($load_count[$className][$value] > 3) {
                throw new AfwRuntimeException("same table $className same id $value too much loaded");
            }
        }

        // $myId = $object->getId();
        $loaded_by = null;
        if (!$result_row) {
            if ($value) {
                $loaded_by = $value;
            } else {
                $loaded_by = $object->getTheLoadByIndex();
            }
        }


        // $time_end1 = microtime(true);
        $object->resetValues();
        // $time_end2 = microtime(true);        
        $cache_management = AfwLoadHelper::cacheManagement($object);

        // if($loaded_by == 6082) die("load case cache_management=$cache_management loading $className[$loaded_by] result_row=".var_export($result_row));
        if ($cache_management and $loaded_by) {
            // if($loaded_by == 6082) die("trying to get object $className [$loaded_by] from cache");
            $objectCache = &AfwCacheSystem::getSingleton()->getFromCache(
                $classNameModule,
                $classNameTable,
                $loaded_by
            );
            if ($objectCache and $objectCache->id) {
                // because now we store empty objects in cache
                // so construct $result_row from object found in cache
                $result_row = [];

                $all_fv = $objectCache->getAllfieldValues();
                foreach ($all_fv as $attribute => $attribute_value) {
                    $result_row[$attribute] = $attribute_value;
                }
                $result_row['debugg_source'] = 'system cache';
                /*$result_row_from =
                    'getFromCache(' .
                    $object::$MODULE .
                    ', ' .
                    $object::$TABLE .
                    ', ' .
                    $loaded_by .
                    ')';*/
            }
            unset($objectCache);
        }
        // $time_end3 = microtime(true);
        if ($value and !$result_row) {
            if ($object->PK_MULTIPLE) {
                if ($object->PK_MULTIPLE === true) {
                    $sep = '-';
                } else {
                    $sep = $object->PK_MULTIPLE;
                }

                $pk_val_arr = explode($sep, $value);
                // die("explode($sep, $value) = ".var_export($pk_val_arr,true));
                foreach ($object->PK_MULTIPLE_ARR as $pk_col_order => $pk_col) {
                    $object->select($pk_col, $pk_val_arr[$pk_col_order]);
                }
            } else {
                $object->select($object->getPKField(), $value);
            }
        }
        // $time_end4_1 = microtime(true);
        if ($object->getSQL() or $result_row) {
            if ($object->IS_VIRTUAL) {
                $return = $object->loadVirtualRow();
                $object->ME_VIRTUAL = true;
            } else {
                if (!$result_row) {
                    if (!$order_by_sentence) {
                        $order_by_sentence = $object->getOrderByFields();
                    }
                    if ($order_by_sentence == 'asc') {
                        throw new AfwRuntimeException('order_by_sentence=asc, ORDER_BY_FIELDS=' .$object->ORDER_BY_FIELDS);
                    }
                    $all_real_fields = AfwStructureHelper::getAllRealFields($object);
                    // $time_end4_2 = microtime(true);
                    $query =
                        'SELECT ' .
                        implode(', ', $all_real_fields) .
                        "\n FROM " .
                        $className::_prefix_table($classNameTable) .
                        " me\n WHERE 1" .
                        $object->getSQL() .
                        "\n ORDER BY " .
                        $order_by_sentence .
                        " -- oo \n LIMIT 1";
                    if ($object->MY_DEBUG and false) {
                        AFWDebugg::log(
                            "query to load afw object value = $value "
                        );
                    }
                    $module_server = $object->getModuleServer();
                    $result_row = AfwDatabase::db_recup_row(
                        $query,
                        true,
                        true,
                        $module_server
                    );
                    // $time_end4_3 = microtime(true);
                    /*
                    $result_row_from = "from sql : $query";
                    if (
                        $object::$TABLE == 'module_auser' and
                        !$object->getAfieldValue('id') and
                        $object->getAfieldValue('id_module')
                    ) {
                        throw new AfwRuntimeException(
                            "test_rafik 1001 <br>\n query=$query <br>\n result_row from($result_row_from) <br>\n result_row here is => " .
                                var_export($result_row, true)
                        );
                    }
                    */
                    $object->clearSelect();
                    $object->debugg_last_sql = $query;
                } else {
                    //
                }
                // $time_end4_4 = microtime(true);

                if (count($result_row) > 1) {
                    $debugg_res_row = '';
                    foreach ($result_row as $attribute => $attribute_value) {
                        if (!is_numeric($attribute)) {
                            // if($attribute=="PK") die("rafik-20240204-01 result_row=".var_export($result_row,true));
                            if(($attribute!="PK") and 
                               ($attribute!="debugg_source") and
                               (!self::isJoinEagerAttribute($attribute))
                               )
                            {
                                try
                                {
                                    $object->set($attribute, $attribute_value);
                                    $object->unsetAfieldDefaultValue($attribute);
                                }
                                catch(Exception $e)
                                {
                                    throw new AfwRuntimeException($e->getMessage().". The set is from result_row = ".var_export($result_row,true));
                                }
                                
                            }                            
                        } else {
                            $debugg_res_row .= ",$attribute";
                        }
                    }



                    /*
                    if(($object::$TABLE=="cher_file"))
                    {
                         die("load from result_row ($result_row_from) => ".var_export($result_row,true));   
                    }
                    */
                    //if($object::$TABLE=="auser") die("test_rafik 1004 : debugg_res_row=$debugg_res_row<br>\n this->getAllfieldValues()".var_export($object->getAllfieldValues(),true)." <br>\n result_row=".var_export($result_row,true));
                    // some time load return true and no id found
                    // very strange to debugg here
                    /*
                    if(($object::$TABLE=="module_auser") and (!$object->getAfieldValue("id")) and ($object->getAfieldValue("id_module"))) 
                    {
                        throw new AfwRuntimeException("test_rafik 1005 : query=$query debugg_res_row=$debugg_res_row<br>\n this->getAllfieldValues()=".var_export($object->getAllfieldValues(),true)." <br>\n result_row from ($result_row_from) <br>\n result_row here is => ".var_export($result_row,true));
                    }
                    */

                    $return = $object->id > 0;
                } else {
                    // die("test_rafik 1003 : count(result_row) = ".count($result_row));
                    $return = false;
                }
            }

            // die("test_rafik 1002 this->IS_VIRTUAL = [$object->IS_VIRTUAL] this->getAllfieldValues()=".var_export($object->getAllfieldValues(),true));
        } else {
            throw new AfwRuntimeException($classNameTable . ' : Unable to use the method load() without any research criteria (' . $object->getSQL() . "), use select() or where() before.");
        }

        // $time_end4 = microtime(true);
        if ($return) {
            $object->afterLoad();
            // -- $className = AfwStringHelper::tableToClass($object::$TABLE);

        } else {
            // even if load is empty store the empty object into cache than the query is not repeated            
        }

        $object->resetUpdates();

        if ($cache_management) {
            if ($loaded_by) {
                AfwCacheSystem::getSingleton()->putIntoCache(
                    $classNameModule,
                    $classNameTable,
                    $object,
                    $loaded_by
                );
            }
        } else {
            /*
            AfwCacheSystem::getSingleton()->skipPutIntoCache(
                $object::$MODULE,
                $object::$TABLE,
                $object->getId(),
                'cache management disabled'
            );*/
        }

        // die("rafik debugg 20210920 : this->getAllfieldValues() = ".var_export($object->getAllfieldValues(),true));
        /*
        
        // above put $time_start = microtime(true);
        
        $time_end = microtime(true);
        $time_1 = 1000*($time_end1 - $time_start);
        $time_2 = 1000*($time_end2 - $time_end1);        
        $time_3 = 1000*($time_end3 - $time_end2);        
        $time_4 = 1000*($time_end4 - $time_end3);        
        $time_5 = 1000*($time_end  - $time_end4);        
        $time_t = 1000*($time_end - $time_start);

        $time_4_1 = "N/A";
        $time_4_2 = "N/A";
        $time_4_3 = "N/A";
        $time_4_4 = "N/A";
        $time_4_5 = "N/A";
        

        if($time_end4_1)                  $time_4_1 = 1000*($time_end4_1 - $time_end3  );        
        if($time_end4_2 and $time_end4_1) $time_4_2 = 1000*($time_end4_2 - $time_end4_1);        
        if($time_end4_3 and $time_end4_2) $time_4_3 = 1000*($time_end4_3 - $time_end4_2);        
        if($time_end4_4 and $time_end4_3) $time_4_4 = 1000*($time_end4_4 - $time_end4_3);        
        if($time_end4_4)                  $time_4_5 = 1000*($time_end4   - $time_end4_4);        

        $time_end4_2 = microtime(true);

        
        $css = "hzm";
        if($time_t>=1) $css = "error";
        $time_log = " time-$css time_t=$time_t time_1=$time_1 time_2=$time_2 time_3=$time_3 time_4=$time_4 
        
        <br>time_4_1=$time_4_1 time_4_2=$time_4_2 time_4_3=$time_4_3 time_4_4=$time_4_4 time_4_5=$time_4_5
        
        <br>time_5=$time_5";
        

        //$time_log = "";

        // espion-time-0001 : pour afficher le temps d'exec de cette requette non-voulu a l origine 
        // mais pour localiser (espioner) la lenteur est avant ou apres
        AfwSession::sqlLog("espion-time-0001 loaded class=" . get_class($object) . " id=" . $object->id . $time_log, $css);*/

        return $return;
    }

    private static function isJoinEagerAttribute($attribute)
    {
        return (AfwStringHelper::stringStartsWith($attribute, 'join') and AfwStringHelper::stringContain($attribute, '00'));
    }


    /**
     * loadMany
     * Load into an array of objects returned rows
     * @param AFWObject $object
     * @param string $limit : Optional add limit to query
     * @param string $order_by : Optional add order by to query
     * $optim=true param obsolete here to remove when we develop the AfwLoaderService that extends AfwService
     */
    public static function loadMany(
        $object,
        $limit = '',
        $order_by = '',
        $optim = true,
        $result_rows = null,
        $query_special = null,
        $eager_joins = false
    ) 
    {
        // $method_time_start = microtime(true);


        // DISABLED EAGER to check lenteur from there or no ?
        // Now it is ok it is not from eager find the reason elsewhere
        // $eager_joins = false;

        global $lang, $_lmany_analysis, $loadMany_max, $MODE_DEVELOPMENT;

        $cache_management = AfwLoadHelper::cacheManagement($object);

        $this_cl = get_class($object);
        $call_method = "$this_cl::loadMany(limit = $limit, order_by = $order_by)";
        /*
        if ($object->MY_DEBUG and false) {
            AFWDebugg::log(
                '----------------------------------------------------------------------------------------'
            );
            AFWDebugg::log("call : $call_method");
            AFWDebugg::log(
                '----------------------------------------------------------------------------------------'
            );
        }*/

        $module_server = $object->getModuleServer();

        $pk_field = $object->getPKField($add_me = 'me.');
        if (!$result_rows) {
            if (!$query_special) {
                // force $optim = true because otherwise data_rows returned is not ready to load
                $optim = true;
                $query =
                    "-- method $call_method : dohtem --\n" .
                    $object->getSQLMany(
                        $pk_field,
                        $limit,
                        $order_by,
                        $optim,
                        $eager_joins
                    );
            } else {
                $query = "-- special query : \n" . $query_special;
            }

            $query_code = $object::$TABLE . '/' . md5($query);

            if (!$_lmany_analysis[$object::$MODULE][$query_code]) {
                $_lmany_analysis[$object::$MODULE][$query_code] = 0;
            }

            $_lmany_analysis[$object::$MODULE][$query_code]++;

            if (
                $_lmany_analysis[$object::$MODULE][$query_code] > $loadMany_max
            ) {
                throw new AfwRuntimeException(
                    'afw class : ' .
                        $object->getMyClass() .
                        ' : loadMany accessed more than ' .
                        $loadMany_max .
                        ' times, query is : ' .
                        $query
                );
            }
            if (AfwSession::config('MODE_DEVELOPMENT', false) and (!AfwSession::config('MODE_MEMORY_OPTIMIZE', true))) {
                $object->debugg_sql_for_loadmany = $query;
            }
            $result_rows = AfwDatabase::db_recup_rows($query, $module_server);

            /*
            if(contient($query,".module_type"))
            {
                AfwRunHelper::safeDie("result_rows ($query) optim=$optim ".var_export($result_rows,true));
            }*/
            //$object->debuggObj($result_rows);
        }
        if (count($result_rows) > 0) {
            $array_many = [];
            $className = $object->getMyClass();
            //list($fileName, $className) = $object->getMyFactory();
            // require_once $fileName;
            $object_ref = new $className();
            // chakek sbab lenteur => is ok
            $colsFK = $object_ref->getRetrieveCols(
                'display',
                $lang,
                false,
                'FK',
                $debugg = false,
                $hide_retrieve_cols = null,
                $force_retrieve_cols = null,
                $category = 'empty'
            );
            $loop_time_start = microtime(true);
            foreach ($result_rows as $rr => $result_row) {
                unset($objectCache);
                $objectCache = null;

                if ($cache_management and !$optim) {
                    /*chakek sbab lenteur => is ok*/
                    $objectCache = &AfwCacheSystem::getSingleton()->getFromCache(
                        $object::$MODULE,
                        $object::$TABLE,
                        $result_row['PK']
                    );
                }

                if (!$objectCache) {
                    //$object = cl one $object_ref;
                    $objectCache = new $className();
                    if ($pk_field) {
                        $objectCache->setPKField($pk_field);
                    } else {
                        $objectCache->setPKField('NO_ID_AS_PK');
                    }
                    // $object->setMyDebugg($object->MY_DEBUG);
                    // $time_start = microtime(true);
                    // $log_actions = "for object $className $rr";
                    if ($objectCache->loadMeFromRow($result_row)) 
                    {
                        // $log_actions .= " loadMeFromRow success";
                        if ($eager_joins) {
                            // chakek sbab lenteur => is ok
                            self::loadAllFkRetrieve($objectCache, $result_row, $colsFK);

                            // $log_actions .= " load All Fk Retrieve done";
                        }
                        /*
                        if($eager_joins and $object instanceof Module) 
                        {
                            throw new AfwRuntimeException("example of data of this class", $object);
                        }*/

                        /*chakek sbab lenteur => is ok */
                        if($cache_management and $objectCache)
                        {
                            AfwCacheSystem::getSingleton()->putIntoCache(
                                $objectCache::$MODULE,
                                $objectCache::$TABLE,
                                $objectCache
                            );

                            // $log_actions .= " AfwCacheSystem->putIntoCache done";
                        }
                        else
                        {
                            // $log_actions .= " AfwCacheSystem::disabled";
                        }
                    }
                    else
                    {
                        // $log_actions .= " loadMeFromRow fail";
                    }

                    // $time_end = microtime(true);
                    /*$time_1 = 1000*($time_end1 - $time_start);
                    $time_2 = 1000*($time_end2 - $time_end1);        
                    $time_3 = 1000*($time_end3 - $time_end2);        
                    $time_4 = 1000*($time_end4 - $time_end3);        
                    $time_5 = 1000*($time_end  - $time_end4);        */
                    // $time_t = 1000*($time_end - $time_start);
                    // $css = "hzm";
                    // if($time_t>=2) $css = "error";
                    // $time_log = " time-$css time_t=$time_t $log_actions";
                    // $time_log = "";
                    // espion-time-0003 : pour afficher le temps d'exec de ce traitement 
                    // AfwSession::sqlLog("espion-time-0003 loaded class=" . get_class($object) . " id=" . $object->id . $time_log, $css);

                    
                } else {
                    // $object->setMyDebugg($object->MY_DEBUG);
                }
                if ($pk_field != 'id') {
                    // die($object->TABLE." debugg rafik 20220912 result_row = ".var_export($result_row,true));
                }
                if ($result_row['PK']) {
                    $obj_index = $result_row['PK'];
                } elseif ($pk_field) {
                    $obj_index = $result_row[$pk_field];
                } else {
                    $obj_index = count($array_many);
                }

                if ($objectCache->dynamicVH()) {
                    $array_many[$obj_index] = $objectCache;
                }
            }
            $loop_time_end = microtime(true);
            $loop_time_t = 1000*($loop_time_end - $loop_time_start);
            if($loop_time_t>500)
            {
                $nb_rows = count($result_rows);
                AfwSession::sqlLog("espion-time-0006 loadMany in class=" . get_class($object) . " id=" . $object->id . " => $loop_time_t times = 1000*($loop_time_end - $loop_time_start) => $nb_rows", "error");
            }

            $return = $array_many;
        } else {
            $return = [];
        }

        if ($object->MY_DEBUG and false) {
            AFWDebugg::log(
                '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'
            );
            AFWDebugg::log(
                'End of method ' .
                    get_class($object) .
                    "->$call_method : return = " .
                    print_r($return, true)
            );
            AFWDebugg::log(
                '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'
            );
        }
        $object->clearSelect();
        /*
        $method_time_end = microtime(true);
        $method_time_t = 1000*($method_time_end - $method_time_start);
        if($method_time_t>500)
        {
            AfwSession::sqlLog("espion-time-0004 loaded class=" . get_class($object) . " id=" . $object->id . " => $method_time_t = 1000*($method_time_end - $method_time_start) => $query", "error");
        }*/
        return $return;
    }

    /**
     * loadListe
     * Load into an array of values returned rows
     * @param AFWObject $object
     * @param string $limit : Optional add limit to query
     * @param string $order_by : Optional add order by to query
     */
    public static function loadListe($object, $limit = '', $order_by = '')
    {
        $call_method = "loadListe(limit = $limit, order_by = $order_by)";
        if ($object->MY_DEBUG and false) {
            AFWDebugg::log(
                '----------------------------------------------------------------------------------------'
            );
            AFWDebugg::log(
                'Start of method ' . get_class($object) . "->$call_method"
            );
            AFWDebugg::log(
                '----------------------------------------------------------------------------------------'
            );
        }
        $query =
            'SELECT ' .
            $object->getPKField() .
            " as PK \n FROM " .
            $object::_prefix_table($object::$TABLE) .
            " me\n WHERE 1" .
            $object->getSQL() .
            ($order_by ? "\n ORDER BY " . $order_by : '') .
            ($limit ? ' LIMIT ' . $limit : '');
        $module_server = $object->getModuleServer();
        $result_rows = AfwDatabase::db_recup_rows(
            $query,
            true,
            true,
            $module_server
        );
        $object->clearSelect();
        if (count($result_rows) > 0) {
            $array = [];
            foreach ($result_rows as $result_row) {
                $array[] = $result_row['PK'];
            }
            $return = $array;
        } else {
            $return = [];
        }
        if ($object->MY_DEBUG and false) {
            AFWDebugg::log(
                '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'
            );
            AFWDebugg::log(
                'End of method ' .
                    get_class($object) .
                    "->$call_method : return = " .
                    print_r($return, true)
            );
            AFWDebugg::log(
                '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'
            );
        }
        return $return;
    }


    /**
     * loadCol
     * @param AFWObject $object
     * @param string  $col_name
     * @param boolean $distinct
     * @param string  $limit : Optional add limit to query
     * @param string  $order_by : Optional add order by to query
     */
    public static function loadCol($object, 
        $col_name,
        $distinct = false,
        $limit = '',
        $order_by = ''
    ) {
        $call_method = "loadCol(limit = $limit, order_by = $order_by)";
        if ($object->MY_DEBUG and false) {
            AFWDebugg::log(
                '----------------------------------------------------------------------------------------'
            );
            AFWDebugg::log(
                'Start of method ' . get_class($object) . "->$call_method"
            );
            AFWDebugg::log(
                '----------------------------------------------------------------------------------------'
            );
        }
        $query =
            'SELECT ' .
            ($distinct ? 'DISTINCT ' : '') .
            $col_name .
            "\n FROM " .
            $object::_prefix_table($object::$TABLE) .
            " me\n WHERE 1" .
            $object->getSQL() .
            ($order_by ? "\n ORDER BY " . $order_by : '') .
            ($limit ? ' LIMIT ' . $limit : '');
        $module_server = $object->getModuleServer();
        $result_rows = AfwDatabase::db_recup_rows(
            $query,
            true,
            true,
            $module_server
        );
        $return = [];
        foreach ($result_rows as $value) {
            $return[] = $value[$col_name];
        }
        $object->clearSelect();
        if ($object->MY_DEBUG and false) {
            AFWDebugg::log(
                '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'
            );
            AFWDebugg::log(
                'End of method ' .
                    get_class($object) .
                    "->$call_method : return = " .
                    print_r($return, true)
            );
            AFWDebugg::log(
                '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'
            );
        }
        return $return;
    }


    /**
     * loadCol
     * @param AFWObject $object
     * @param string  $term
     * @param boolean $return_sql_only
     */

    public static function findExact($object, $term, $return_sql_only = false)
    {
        if (!$term) {
            return null;
        }

        $display_field = trim($object->AUTOCOMPLETE_FIELD);

        if (!$display_field) {
            $display_field = trim($object->DISPLAY_FIELD);
        }

        if (!$display_field) {
            $display_field = trim($object->FORMULA_DISPLAY_FIELD);
        }

        if (!$display_field) {
            throw new AfwRuntimeException('afw class : ' . $object->getMyClass() . ' : method find Exact does not work without one of AUTOCOMPLETE_FIELD or DISPLAY_FIELD or FORMULA_DISPLAY_FIELD attributes specified for the object');
        }

        $object->select_visibilite_horizontale();
        $object->select($display_field, $term);


        if ($return_sql_only) {
            return 'display_field=' .
                $term .
                " : sql => " .
                $object->getSQLMany();
        }

        return $object->loadMany();
    }


    public static function loadManyIds($object)
    {
        $query = $object->getSQLMany('', '', '', false);
        $module_server = $object->getModuleServer();

        $result_rows = AfwDatabase::db_recup_rows(
            $query,
            true,
            true,
            $module_server
        );

        $res_arr = [];

        foreach ($result_rows as $result_row) {
            $res_arr[] = $result_row['PK'];
        }

        return $res_arr;
    }

    public function getItemsIds($object, $attribute)
    {
        list($ansTab, $ansMod) = $object::answerTableAndModuleFor($attribute);
        if ($ansTab and $ansMod) {
            $structure = AfwStructureHelper::getStructureOf($object, $attribute);

            list($fileName, $className) = AfwStringHelper::getHisFactory($ansTab, $ansMod);

            $object = new $className();
            // $object->setMyDebugg($object->MY_DEBUG);

            if ($structure['ITEM']) {
                $item_oper = $structure['ITEM_OPER'];
                $item_ = $structure['ITEM'];
                $this_id = $object->getAfieldValue($object->getPKField());

                if ($item_oper) {
                    $object->where("$item_ $item_oper '$this_id'");
                } else {
                    $object->select($item_, $this_id);
                }
            }
            if (isset($structure['WHERE']) && $structure['WHERE'] != '') {
                $sql_where = $object->decodeText($structure['WHERE']);
                $object->where($sql_where);
            }

            if (!$structure['LOGICAL_DELETED_ITEMS_ALSO']) {
                $object->select($object->fld_ACTIVE(), 'Y');
            }
            $object->debugg_tech_notes = "before load ids for Items of attribute : $attribute";

            $return = AfwLoadHelper::loadManyIds($object);
        } else {
            throw new AfwRuntimeException(
                "check structure of attribute $attribute ANSWER TABLE not found"
            );
        }

        return $return;
    }

    public static final function loadAllFkRetrieve($object, $row, $colsRet = null)
    {
        global $lang;
        // load objects from added left joins for all retrieved fields with type = FK and category empty (real fields)
        if (!$colsRet) {
            $colsRet = $object->getRetrieveCols(
                $mode = 'display',
                $lang,
                $all = false,
                $type = 'FK',
                $debugg = false,
                $hide_retrieve_cols = null,
                $force_retrieve_cols = null,
                $category = 'empty'
            );
        }
        foreach ($colsRet as $col_ret) {
            AfwLoadHelper::loadObjectFKFromRow($object, $col_ret, $row);
        }
    }



    public static final function loadObjectFKFromRow($object, $attribute, $row)
    {
        $cache_management = AfwLoadHelper::cacheManagement($object);

        $from_join_row = [];
        foreach ($row as $col => $val) {
            if ((!is_numeric($col)) and AfwStringHelper::stringStartsWith($col, "join" . $attribute . "00_")) {
                $attrib_real = AfwStringHelper::removePrefix($col, "join" . $attribute . "00_");
                // die("AfwStringHelper::removePrefix($attribute, join${attribute}00_) = $attrib_real");
                $from_join_row[$attrib_real] = $val;
            }
        }
        if (count($from_join_row) > 0) {
            /* why we need to load it from cache when we will load it from the row itself
            if ($cache_management) {
                list($ansTab, $ansMod) = $object->getMyAnswerTableAndModuleFor($attribute);
                $objFromJoin = AfwCacheSystem::getSingleton()->getFromCache(
                    $ansMod,
                    $ansTab,
                    $from_join_row['id']
                );
            }
            else $objFromJoin = null;*/
            $objFromJoin = null;

            if (!$objFromJoin) {
                $objFromJoin = AfwStructureHelper::getEmptyObject($object, $attribute);
            }
            $objFromJoin->load($v = '', $from_join_row);

            if ($cache_management) {
                if (is_object($objFromJoin) and ($objFromJoin->id)) {
                    //$object_id = $objFromJoin->getId();
                    list($ansTab, $ansMod) = $object->getMyAnswerTableAndModuleFor($attribute);
                    AfwCacheSystem::getSingleton()->putIntoCache(
                        $ansMod,
                        $ansTab,
                        $objFromJoin,
                        '',
                        $object::$MODULE . '.' . $object::$TABLE . '.' . $attribute
                    );
                }

                if ($objFromJoin->id) {
                    $object->OBJECTS_CACHE[$attribute] = $objFromJoin;
                }
            }
        } else {
            throw new AfwRuntimeException("not convenient FK row to load object from attribute $attribute => row = " . var_export($row, true));
        }
    }


    /**
     * getIndex returns the list indexed by ID of records verifing the $filter condition and with the format specified
     * @param string $module
     * @param string $tableName
     * @param string $format : Optional
     * @param array $filtre : Optional
     * @return array 
     */
    public static function getIndex(
        $module,
        $tableName,
        $format = 'DROPDOWN',
        $filtre = [],
        $langue = ''
    ) 
    {
        global $lang;
        if (!$langue) { $langue = $lang; }
        $call_method = "getIndex(tableName = $tableName, format = $format, filtre = " . print_r($filtre, true) .')';
        
        if ($tableName != '') {
            list($fileName, $className) = AfwStringHelper::getHisFactory($tableName, $module);
            $object = new $className();

            if (is_array($filtre)) {
                foreach ($filtre as $col => $val) {
                    $object->select($col, $val);
                }
            } else {
                AfwRunHelper::simpleError(
                    "The filter parameter should be an array for AFWObject::getIndex method.",
                    $call_method
                );
            }
            $array = $object->loadMany();
            switch ($format) {
                case 'DROPDOWN':
                    foreach ($array as $key => $obj) {
                        $array[$key] = $obj->getDropDownDisplay($langue);
                        unset($obj);
                    }
                    $return = $array;
                    break;
                case 'OBJECTS':
                    $return = $array;
                    break;
                default:
                    AfwRunHelper::simpleError("The format [$format] is not supported by AFWObject::getIndex method.",$call_method);
                    break;
            }
        } else {
            AfwRunHelper::simpleError('Check that the param $tableName is correctly filled in call to AfwLoadHelper::getIndex().',$call_method);
        }

        
        return $return;
    }

    public static function loadList($object, $attribute)
    {
        $listObj = $object->loadMany();

        $listItems = [];

        foreach ($listObj as $obj) {
            $val = $obj->getVal($attribute);
            if (!$listItems[$val]) {
                $listItems[$val] = $obj->het($attribute);                
            }
            unset($obj);
        }

        return $listItems;
    }


/**
     * getAttributeData
     * Load into an array of values returned rows
     * @param object $object : afw object instance
     * @param string $attribute 
     * @param string $what 
     * @param string $format
     * @param bool $integrity
     * @param bool $max_items
     * @param bool $optim_lookup
     */

    public static function getAttributeData($object,
        $attribute,
        $what = 'object',
        $format = '',
        $integrity = true,
        $max_items = false,
        $optim_lookup = true
    ) 
    {
        global $lang, $get_stats_analysis, $MODE_BATCH_LOURD, $MODE_SQL_PROCESS_LOURD;

        // $cache_management = AfwLoadHelper::cacheManagement($object);
        // $target = '';
        // $popup_t = '';

        $lang = strtolower(trim($lang));
        if (!$lang) {
            $lang = AfwLanguageHelper::getGlobalLanguage();
        }
        $object->debugg_last_attribute = $attribute;
        $call_method = "get(attribute = $attribute, what = $what, format = $format, integrity = $integrity)";
        if (!$attribute) {
            $message = "get can not be performed without attribute name in : $call_method <br>\n";
            throw new AfwRuntimeException($message);
        }

        $old_attribute = $attribute;
        $attribute = AfwStructureHelper::shortNameToAttributeName($object, $attribute);

        if (!$object->seemsCalculatedField($attribute)) {
            if ($what == 'value') {
                return $object->getAfieldValue($attribute);
            }
        }

        $afw_getter_log = array();
        $afw_getter_log[] = "start get($attribute,$what,$format,$integrity,$max_items)";

        if ($what == 'calc') {
            if ($format) $what = $format;
            else $what = 'value';
            // if($attribute=="school_class_id") die("for $attribute what was calc now = $what");
        }

        $return = '';
        

        $structure = AfwStructureHelper::getStructureOf($object, $attribute);

        if (strpos($attribute, '.') !== false) {
            $structure['CATEGORY'] = 'SHORTCUT';
            $structure['SHORTCUT'] = $attribute;
        }
        /*
        if ($attribute == 'schoollist') {
            die(
                "degugg 1 rafik getting $what from attribute=$old_attribute new=$attribute structure : " .
                    var_export($structure, true)
            );
        }*/

        if (($what == 'object') and (!$structure)) {
            return null;
            // I dont know why this exception is not thrown below
            //throw new AfwRuntimeException("to get object from attribute $attribute it should have defined structure");
        }

        $attribute_category = $structure['CATEGORY'];
        $attribute_type = $structure['TYPE'];
        $fieldReallyExists = AfwStructureHelper::fieldReallyExists($object, $attribute, $structure);

        $afw_getter_log[] = "attribute_category=$attribute_category, fieldReallyExists($attribute) = $fieldReallyExists";
        if ($attribute_category or $fieldReallyExists) 
        {
            if ($attribute_category) {
                if($attribute_category=="SHORTCUT") $integrity = false;
                $return = $object->getCategorizedAttribute($attribute, $attribute_category, $attribute_type, $structure, $what, $format, $integrity, $max_items, $lang, $call_method);
            } else {
                $return = self::getReallyExistsNonCategorizedAttribute($object, $attribute, $attribute_type, $optim_lookup, $structure, $what, $format, $integrity, $lang, $call_method);
            }
        } 
        else {
            $return = $object->getNonExistingAttribute($attribute, $what);
        }
        // if("arole_mfk" == $attribute) throw new AfwRuntimeException("strange get($attribute) = $return details ".implode("\n<br>",$afw_getter_log));
        /*
        $this_TABLE = $object->getMyTable();
        $this_id = $object->id;
        if (!$get_stats_analysis[$this_TABLE][$attribute][$this_id][$what]) {
            $get_stats_analysis[$this_TABLE][$attribute][$this_id][$what] = 0;
        }


        $called_times = $get_stats_analysis[$this_TABLE][$attribute][$this_id][$what];
        if ($called_times > 1 and $structure['OPTIM']) {
            throw new AfwRuntimeException(
                "same $this_TABLE (record id = $this_id) ->get($attribute,$what) called more than once when optim mode is enabled"
            );
        }

        if ((($called_times > 50) and (!$MODE_BATCH_LOURD)  and (!$MODE_SQL_PROCESS_LOURD)) or ($called_times > 500)) 
        {
            throw new AfwRuntimeException(
                "same $this_TABLE (record id = $this_id) ->get($attribute,$what) called " .
                    $called_times .
                    ' time should be optimized'
            );
        }
        if ($return) $get_stats_analysis[$this_TABLE][$attribute][$this_id][$what]++;
        */
        return $return;
    }

    public static function getAnObject($object, $attribute, $integrity, $optim_lookup, $structure = null, $attribute_type = null, $call_method = "", $b_abstract = false)
    {
        $cache_management = AfwLoadHelper::cacheManagement($object);

        if (!$structure) $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        if (!$attribute_type) $attribute_type = $structure['TYPE'];
        if (isset($structure)) {
            if ($attribute_type == 'ANSWER') {
                throw new AfwRuntimeException(
                    "the method get() ne retourne pas d'objet pour le type ANSWER, veuillez vérifier la définition of attribute " .
                        $attribute .
                        ' dans DB_STRUCTURE de la table ' .
                        $object->getMyTable() .
                        '.',
                    $call_method
                );
            } else {
                if ($attribute_type == 'MFK') {
                    $ids_mfk = trim($object->getAfieldValue($attribute), ',');

                    $ids = explode(',', $ids_mfk);

                    $afw_getter_log[] = "here is MFK field and ids_mfk=$ids_mfk count = " . count($ids);

                    list($ansTab, $ansMod,) = $object->getMyAnswerTableAndModuleFor($attribute, $structure);
                    list($fileName, $className,) = AfwStringHelper::getHisFactory($ansTab, $ansMod);

                    $afw_getter_log[] = "for MFK field factory is ($ansTab,$ansMod) and ($fileName,$className)";

                    $reload_objects_cache = false;

                    if ((!$object->OBJECTS_CACHE[$attribute]) or (!is_array($object->OBJECTS_CACHE[$attribute]))) {
                        $reload_objects_cache = true;
                    } elseif ($object->{"debugg_mfk_val_$attribute"} != $ids_mfk) {
                        $reload_objects_cache = true;
                    }

                    if ($reload_objects_cache) {
                        $afw_getter_log[] = "cache ignored and reloading objects";
                        unset($object->OBJECTS_CACHE[$attribute]);
                        $object->OBJECTS_CACHE[$attribute] = [];
                        foreach ($ids as $id) {
                            if ($id) {
                                $object = new $className();
                                // $object->setMyDebugg($object->MY_DEBUG);
                                if ($object->load($id)) {
                                    $afw_getter_log[] = "success of laoding of instance id = $id";
                                    $object->OBJECTS_CACHE[$attribute][$id] = $object;
                                } else {
                                    $afw_getter_log[] = "fail of laoding of instance id = $id";
                                    $object->OBJECTS_CACHE[$attribute][$id] = null;
                                }
                            }
                        }
                        $object->{"debugg_mfk_val_$attribute"} = $ids_mfk;
                    }

                    $return = $object->OBJECTS_CACHE[$attribute];
                    if (!$cache_management) {
                        unset($object->OBJECTS_CACHE[$attribute]);
                    }
                    $afw_getter_log[] = "laoded : " . var_export($return, true);
                    if (!is_array($return)) {
                        throw new AfwRuntimeException("MFK should never return-back non array result, rlch=$reload_objects_cache, className=$className, ids=" . var_export($ids, true));
                    }
                } elseif ($attribute_type == 'FK') {
                    // if($old_attribute=="campaign") die("degugg 3 rafik2 old=$old_attribute new=$attribute attribute_value=$attribute_value getTypeOf($attribute) == FK, structure : ".var_export($structure, true));
                    if ($b_abstract) {
                        $return = null; // not implemented yet for virtual/abstract objects
                    } else $return = AfwLoadHelper::loadObjectFKFor($object, $attribute, $integrity, $optim_lookup);

                    // if(($old_attribute=="campaign") and ($return instanceof PracticeDomain)) die($object."<br>degugg 4 rafik2 attribute=$attribute,<br>b_abstract=$b_abstract,<br>integrity=$integrity,<br>return=$return<br>");
                    // if(!$return) throw new AfwRuntimeException($object."<br>here:attribute=$attribute,<br>b_abstract=$b_abstract,<br>integrity=$integrity,<br>return=$return<br>");
                } else {
                    throw new AfwRuntimeException(
                        "Try to get object value for strange non implemented object type=[$attribute_type] for attribute " .
                            $attribute .
                            ' of table ' .
                            $object->getMyTable() .
                            '.'
                    );
                }
            }
        } else {
            throw new AfwRuntimeException(
                'Unable to return-back an object for attribute ' .
                    $attribute .
                    ' not defined in DB_STRUCTURE of table ' .
                    $object->getMyTable() .
                    '.',
                $call_method .
                    ' structure => ' .
                    var_export($structure, true)
            );
        }

        return $return;
    }

    public static function getReallyExistsNonCategorizedAttribute($object, $attribute, $attribute_type, $optim_lookup, $structure, $what, $format, $integrity, $lang, $call_method="")
    {
        $b_abstract = false;
        $return = $attribute_value = $object->getAfieldValue($attribute);
        // if(($attribute=="aaa") and ($what != "value")) die("no categ and what=[$what]");
        $afw_getter_log[] = "no categ and attribute_value=[$attribute_value], what=$what, this->getAfieldValue($attribute) = " . $attribute_value;
        switch (strtolower($what)) {
            case 'object':
                $return = self::getAnObject($object, $attribute, $integrity, $optim_lookup, $structure, $attribute_type, $call_method, $b_abstract);
                break;
            case 'value':
                if ($b_abstract) {
                    // Not implemented see suggestion of implementation below to check
                    $return = 0;
                    /*  suggestion of implementation
                    $object = &$object->OBJECTS_CACHE[$attribute];
                    if ($object === null) {
                            $return = 0;
                    } else {
                        $return = $object->getId();
                    }
                    */
                } elseif (isset($structure)) {
                    $return = stripslashes($attribute_value);
                } elseif (isset($attribute_value)) {
                    $return = $attribute_value;
                } else {
                    throw new AfwRuntimeException("Attribute $attribute not defined in DB_STRUCTURE of table " . $object->getMyTable() . '.', $call_method);
                }
                break;
            case 'decodeme':
                $decode_format = $format ? $format : $structure['FORMAT'];
                //if($attribute=="school_class_id") die("for : $attribute decode with format = $format, decode_format = $decode_format, str = ".var_export($structure,true));
                $typattr = $attribute_type;
                // if($attribute=="updated_by") die("for : $attribute decode with format = $format, decode_format = $decode_format, gettype = $typattr, value=$valattr, str = ".var_export($structure,true));
                if ($typattr) {
                    $return = AfwFormatHelper::decode($attribute, $typattr, $decode_format, $attribute_value, $integrity, $lang, $structure, $object, $translate_if_needed = true);
                    //if($attribute=="application_end_date") die("$return = AfwFormatHelper::decode($attribute, $typattr, $decode_format, $attribute_value, $integrity, $lang, ....)");
                } else {
                    throw new AfwRuntimeException("The Attribute $attribute of table " . $object->getMyTable() . " has structure property TYPE not defined.", $call_method);
                }
                break;
        }

        return $return;
    }

    

    public static final function explodeEnumAnswer($answer)
    {
        return AfwStringHelper::afw_explode($answer);
    }
    public static final function getEnumTable(
        $answer,
        $table = '',
        $fattribut = '',
        $obj = null
    ) 
    {
            //echo "call to get EnumTable($answer,$table,$attribut)<br>";
            if ($answer == 'FUNCTION') 
            {
                    if (!$fattribut) {
                        throw new AfwRuntimeException("get EnumTable need attribut name for FUNCTION dynamic answers (table = $table) obj = " .var_export($obj, true));
                    }
                    $method = "list_of_$fattribut";
                    $object_method = "my_list_of_$fattribut";
                    if (!$table) {
                        throw new AfwRuntimeException('table param is mandatory in get EnumTable method');
                    }
                    $return = NULL;
                    if ($obj) {
                            $return = $obj->$object_method();
                            $case = "obj->$object_method()";
                    } 
                    
                    if(!$return)
                    {
                            $className = AfwStringHelper::tableToClass($table);
                            $return = $className::$method();
                            $case = "$className :: $method()";
                    }
                    // echo "call to $className::$method() return [";
                    // print_r($return);
                    // echo "]";
                    
            } 
            else 
            {
                    $return = self::explodeEnumAnswer($answer);
                    $case = "self::explodeEnumAnswer($answer)";
            }


            if(!is_array($return)) throw new AfwRuntimeException("get EnumTable($answer,$table,$fattribut,obj: ".var_export($obj,true).") used case $case and returned : $return");

            return $return;
    }

    
    public static final function getEnumTotalAnswerList($object, $attribute, $enum_answer_list = '')
    {
        $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        if (!$enum_answer_list) {
            $enum_answer_list = $structure['ANSWER'];
        }
        if ($enum_answer_list == 'INSTANCE_FUNCTION') {
            $enum_answer_list = 'FUNCTION';
        }
        $fcol_name = $structure["FUNCTION_COL_NAME"];
        if(!$fcol_name) $fcol_name = $attribute;
        $liste_rep = AfwLoadHelper::getEnumTable($enum_answer_list,$object->getTableName(),$fcol_name,$object);
        return $liste_rep;
    }

    /*
    it is the same as getEnumAnswerList

    public function getMyEnumTableOf($attribute,$answer="")
    {
        if(!$answer)
        {
            $desc = AfwStructureHelper::getStructureOf($object,$attribute);
            $answer = $desc["ANSWER"];
        }
        $fcol_name = $structure["FUNCTION_COL_NAME"];
        if(!$fcol_name) $fcol_name = $attribute;
        return AfwLoadHelper::getEnumTable($answer, $object->getTableName(), $fcol_name, $object);

    }
    */

    /**
     * getAnswerTable obsolete
     * Return Array of rows table
     * @param string $tableName : Spefify name of answer table
     * @param string $primaryKey : Optional, specify name of primary Key
     * @param string $valueField : Optional, specify name of field containing value
     * @param string $selected : Optional, specify selected row
     */

     /*
    public static function getAnswerTable(
        $tableName,
        $primaryKey = 'ANSWER_ID',
        $valueField = 'VALUE_FR',
        $selected = '',
        $where = '',
        $module_server = ''
    ) {
        $return = [];
        $sqlat =
            "select $primaryKey, $valueField \n from $tableName" .
            ($selected != ''
                ? "\n where $primaryKey = '$selected'"
                : "\n where 1");
        if ($where) {
            $sqlat .= "\n and $where";
        }

        $rows = AfwDatabase::db_recup_rows($sqlat, true, true, $module_server);
        foreach ($rows as $row) {
            $return[$row[$primaryKey]] = $row[$valueField];
        }
        return $return;
    }*/



    
										
}
