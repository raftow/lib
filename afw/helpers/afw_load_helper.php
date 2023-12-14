<?php
class AfwLoadHelper extends AFWRoot
{
    private static $lookupMatrix;

    public static function getLookupData($nom_module_fk, $nom_table_fk, $where="--")
    {
        if(!$where) $where="--";
        if(!self::$lookupMatrix["$nom_module_fk.$nom_table_fk.$where"])
        {
            $nom_class_fk   = AFWObject::tableToClass($nom_table_fk);
            $object = new $nom_class_fk();
            self::$lookupMatrix["$nom_module_fk.$nom_table_fk.$where"] = self::loadAllLookupData($object,$where);
        }

        return self::$lookupMatrix["$nom_module_fk.$nom_table_fk.$where"];
    } 

    public static function vhGetListe($obj, $where, $action="default", $lang="ar", $val_to_keep=null, $order_by="", $dropdown = false, $optim = true)
    {
        if($action=="default") 
        {
            if($obj->IS_LOOKUP) $action="liste";
            else $action="loadMany";
        }

        if($action=="loadManyFollowingStructure") 
        {
            if($obj->IS_LOOKUP) $action="liste";
        }

        if($action=="liste") 
        {
            $return = self::getLookupData($obj->getMyModule(), $obj->getMyTable(), $where);
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
                    $return=array();                    
                }

                if($action=="loadManyFollowingStructure") 
                {
                    $desc=[];
                    $desc['LOAD_ALL']=false;
                    $desc['NO_KEEP_VAL'] = $val_to_keep ? false : true;
                    $desc['WHERE'] = $where;
                    $desc['ORDERBY'] = $order_by;
                    $listeRep = self::loadManyFollowingStructureAndValue($obj,$desc,$val_to_keep, null, $dropdown, $optim);
                    
                }
                $return=array();
                return self::constructDropDownItems($listeRep, $lang);
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
            throw new RuntimeException("Too much items to put into dropdown for field : [$table_name.$col_name] (count = $count_liste_rep), 
                    it is recommended to use AUTOCOMPLETE option, report : $report");
        }

        $l_rep = [];
        foreach ($liste_rep as $iditem => $item) {
            if (AfwUmsPagHelper::userCanDoOperationOnObject($item,$objme, 'display')) {
                $l_rep[$iditem] = $item->getDropDownDisplay($lang);
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

    public static function lookupDecodeValues($nom_module_fk, $nom_table_fk, $val, $separator, $emptyMessage)
    {
        if(!$val) return "";
        $where="1";
        self::getLookupData($nom_module_fk, $nom_table_fk, $where);
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

        if(count($val_arr)>0)
        {
            $decodedValues_arr = [];
            foreach($val_arr as $val_item)
            {
                $decodedValues_arr[] = self::$lookupMatrix["$nom_module_fk.$nom_table_fk.$where"][$val_item];
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

    /**
     * loadObjectFK
     * Load into object
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

        $cache_management = $myObject->cacheManagement();

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
                throw new RuntimeException("heavy page halted after $boucle_loadObjectFK enter to method $call_method in one request, " .var_export($boucle_loadObjectFK_arr, true));
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
            $object = $myObject->getMySmallCacheForAttribute($attribute, $object_id);
            if($object)
            {
                $loadObjectFK_step = 1;
            }
            // 2. otherwise try from global cache management
            elseif ($cache_management) {
                $className = self::tableToClass($ansTab);
                if (!$className) throw new RuntimeException("for attribute $attribute of $this_table we can not calc tableToClass(answer table = $ansTab)");

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
                // $object->setMyDebugg($this->MY_DEBUG);

                if ($object->load($object_id,'','', $optim_lookup)) {
                    $object_loaded = true;
                    $return = $object;
                } elseif ($integrity) {
                    $struct = AfwStructureHelper::getStructureOf($myObject, $attribute);
                    if ($struct['MANDATORY']) {
                        throw new RuntimeException(
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
                throw new RuntimeException(
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
                        if (!$col) throw new RuntimeException('header columns erroned, column empty : ' .var_export($header, true));
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
        
        $display_field = trim($object->AUTOCOMPLETE_FIELD);

        if (!$display_field) {
            $display_field = trim($object->DISPLAY_FIELD);
        }

        if (!$display_field) {
            $display_field = trim($object->FORMULA_DISPLAY_FIELD);
        }

        if (!$display_field) {
            throw new RuntimeException('afw class : ' . $object->getMyClass() . ' : method loadLookupData does not work without one of AUTOCOMPLETE_FIELD or DISPLAY_FIELD or FORMULA_DISPLAY_FIELD attributes specified for the object');
        }

        // $orderBy = trim($object->ORDER_BY_FIELDS);

        $module = $object::$MODULE;
        $table = $object::$TABLE;
        $server_db_prefix = AfwSession::config('db_prefix', 'c0');


        return AfwDatabase::db_recup_index("select id, $display_field as val from $server_db_prefix" . $module . ".$table where $where", "id", "val");

    }

    


    
										
}
