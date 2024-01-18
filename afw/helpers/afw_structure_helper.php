<?php
class AfwStructureHelper extends AFWRoot 
{


    public static final function getStructureOf($object, $field_name)
    {
        $orig_field_name = $field_name;
        $field_name = $object->shortNameToAttributeName($field_name);
        if(!$field_name) $field_name = $orig_field_name;
        $struct = $object->getMyDbStructure(
            $return_type = 'structure',
            $field_name
        );

        if ($struct) {
            $struct = AfwStructureHelper::repareMyStructure($object,$struct, $field_name);
        }

        if($struct["CATEGORY"]=="SHORTCUT")
        {
            if(!$object->shouldBeCalculatedField($field_name))
            {
                $cl = get_class($object);
                throw new AfwRuntimeException("Momken 3.0 Error : [Class=$cl,Attribute=$field_name] is shortcut but not declared in overridden shouldBeCalculatedField method, do like this : <pre><code>".$object->suggestAllCalcFields()."</code></pre>");
            }
            
        }
        
        return $struct;
    }


    public static final function constructDBStructure($module_code, $class_name, $attribute)
    {
        // $start_m_time = microtime();
        $start_m_time = 0;
        $my_db_structure = null;
        if (STRUCTURE_IN_CACHE) {
            if (class_exists('AfwAutoLoader') or class_exists('AfwCacheSystem')) {
                $my_db_structure = AfwCacheSystem::getSingleton()->getStructureFromCache($class_name);
                // if(($class_name=="Invester") and ($attribute=="city_id")) die("my_db_structure=>getStructureFromCache=>".var_export($my_db_structure,true));
            }
        }

        if (!$my_db_structure) {
            $file_dir_name = dirname(__FILE__);

            // include_once("$file_dir_name/../$module_code/struct/struct_$table_name.php"); //
            // $my_db_structure = $GLOBAL_DB_STRUCTURE[$table_name];
            $moduleDomain = ucfirst($module_code);
            $class_name_strcucture =
                $moduleDomain . $class_name . 'AfwStructure';
            if (PHP_VERSION_ID < 80000) {
                if (!$my_db_structure) {
                    $my_db_structure = $class_name::$DB_STRUCTURE;
                    $origin = " < 8.0 from $class_name::DB_STRUCTURE";
                }
                if (!$my_db_structure) {
                    $my_db_structure = $class_name_strcucture::$DB_STRUCTURE;
                    $origin = " < 8.0 from $class_name_strcucture::DB_STRUCTURE";
                }
            } else {
                if (!$my_db_structure) {
                    $my_db_structure = $class_name_strcucture::$DB_STRUCTURE;
                    $origin = " >= 8.0 from $class_name_strcucture::DB_STRUCTURE";
                }
                if (!$my_db_structure) {
                    $my_db_structure = $class_name::$DB_STRUCTURE;
                    $origin = " >= 8.0 from $class_name::DB_STRUCTURE";
                }
            }
            //if(($class_name=="Invester") and ($attribute=="city_id")) die("my structure => [$origin] => ".var_export($my_db_structure,true));
            if (STRUCTURE_IN_CACHE) {
                if (class_exists('AfwAutoLoader') or class_exists('AfwCacheSystem')) {
                    AfwCacheSystem::getSingleton()->setStructureIntoCache(
                        $class_name,
                        $my_db_structure
                    );
                }
            }

            $got_first_time = true;
        }
        /*
        if(($class_name=="Invester") and ($attribute=="city_id"))
        {
            $my_db_structure2 = $my_db_structure; //LicenseLicenseAfwStructure::$DB_STRUCTURE;
            $origin2 = $origin; // "raf-test";
            $attribute_2 = 'city_id'; // "region_id";            
            echo("my_db_structure => [$origin2] => ".var_export($my_db_structure2,true));
            echo("\nmy_db_structure[$attribute_2] => [$origin2] => ".var_export($my_db_structure2[$attribute_2],true));
            echo("\nmy_db_structure[$attribute] => [$origin2] => ".var_export($my_db_structure2["$attribute"],true));
        } */
        if ($attribute == 'all') {
            $debugg_db_structure = $my_db_structure;
        } else {
            $debugg_db_structure = [];
            $debugg_db_structure[$attribute] = $my_db_structure[$attribute];
        }
        // if(($class_name=="Invester") and ($attribute=="city_id")) die("\n\n\ndebugg_db_structure => [$origin] => ".var_export($debugg_db_structure,true));
        if ($got_first_time and false) {
            $max_calls_of_structure = 500;
            // $end_m_time = microtime();
            // $duree_min_accepted = 0.1;
            // $duree_ms = round(($end_m_time - $start_m_time)*100000)/100;
            $end_m_time = 0;
            $duree_min_accepted = 0;
            $duree_ms = 0;

            if ($duree_ms >= $duree_min_accepted) {
                $this_cl = $class_name . '_structure';
                global $tab_instances;
                if (!$tab_instances) {
                    $tab_instances = [];
                }

                if (!$tab_instances[$this_cl]) {
                    $tab_instances[$this_cl] = 0;
                }
                $tab_instances[$this_cl]++;
                if ($tab_instances[$this_cl] == $max_calls_of_structure) {
                    //  and ($this_cl == "module_structure")
                    self::lightSafeDie(
                        "duree_ms=$duree_ms $this_cl reached " .
                            $tab_instances[$this_cl],
                        $tab_instances
                    );
                }
            }
        }

        return $debugg_db_structure;
    }

    public static function repareStructure($struct)
    {
        if ($struct['RETRIEVE']) {
            $struct['SHOW'] = true;
        }

        if ($struct['RETRIEVE_FGROUP']) {
            $struct[strtoupper($struct['FGROUP']) . '-RETRIEVE'] = true;
        }
        if ($struct['ALL_FGROUP']) {
            $struct['ALL-RETRIEVE'] = true;
        }

        if (!isset($struct['SEARCH-BY-ONE'])) {
            $struct['SEARCH-BY-ONE'] = $struct['QSEARCH'];
        }
        if (!isset($struct['DISPLAY'])) {
            $struct['DISPLAY'] = $struct['SHOW'];
        }
        if (!isset($struct['STEP'])) {
            $struct['STEP'] = 1;
        }
        if (!isset($struct['DISPLAY-UGROUPS'])) {
            $struct['DISPLAY-UGROUPS'] = $struct['DATA_UGROUPS'];
        }
        if (!isset($struct['DISPLAY-UGROUPS'])) {
            $struct['DISPLAY-UGROUPS'] = $struct['UGROUPS'];
        }
        if (!isset($struct['EDIT-UGROUPS'])) {
            $struct['EDIT-UGROUPS'] = $struct['DATA_UGROUPS'];
        }
        if (!isset($struct['EDIT-UGROUPS'])) {
            $struct['EDIT-UGROUPS'] = $struct['UGROUPS'];
        }
        if ($struct['PILLAR-PART']) {
            $struct['ERROR-CHECK'] = true;
        }
        if ($struct['PILLAR']) {
            $struct['ERROR-CHECK'] = true;
        }
        if ($struct['POLE']) {
            $struct['ERROR-CHECK'] = true;
        }
        if ($struct['REQUIRED']) {
            $struct['MANDATORY'] = true;
        }
        if ($struct['MANDATORY']) {
            $struct['ERROR-CHECK'] = true;
        }
        if (!isset($struct['DEFAUT'])) {
            $struct['DEFAUT'] = $struct['DEFAULT'];
        }
        else
        {
            $struct['DEFAULT'] = $struct['DEFAUT'];
        }

        if (isset($struct['HIDE_COLS']) and !$struct['DO-NOT-RETRIEVE-COLS']) {
            $struct['DO-NOT-RETRIEVE-COLS'] = $struct['HIDE_COLS'];
        }
        if (isset($struct['FORCE_COLS']) and !$struct['FORCE-RETRIEVE-COLS']) {
            $struct['FORCE-RETRIEVE-COLS'] = $struct['FORCE_COLS'];
        }

        if ($struct['FORCE-DISABLE-ERROR-CHECK']) {
            $struct['ERROR-CHECK'] = false;
        }
        if ($struct['FORMAT'] == 'EMPTY_IS_ALL') {
            $struct['EMPTY_IS_ALL'] = true;
        }

        return $struct;
    }

    public static final function repareMyStructure($object, $struct, $field_name)
    {
        //if($field_name == "nomcomplet") die("in getStructureOf($field_name) run of this->getMyDbStructure($return_type, $field_name) = ".var_export($struct,true));
        if (
            $object->editByStep and
            !$object->disableTechnicalFieldsDefaultBehavior and
            ($object->isAdminField($field_name) or
                $object->isTechField($field_name))
        ) {
            $struct['SHOW'] = true;
            $struct['STEP'] = 999;
            $struct['EDIT'] = true;
            $struct['READONLY'] = true;
        }

        foreach($struct as $col_struct => $value_struct)
        {
            if(is_string($value_struct))
            {
                if(AfwStringHelper::stringStartsWith($value_struct,'::'))
                {
                    $methodStructEval = substr($value_struct,2);
                    $struct[$col_struct] = $object->$methodStructEval();
                }
            }
            
        }

        return AfwStructureHelper::repareStructure($struct);
    }


    public static final function repareQEditAttributeStructure($col_name, $desc)
    {
            $cell_size = 11;
            
            if(AfwStringHelper::stringStartsWith($col_name,"titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
            if(se_termine_par($col_name,"titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
            if(AfwStringHelper::stringStartsWith($col_name,"titre") && (!$desc["SIZE"])) $desc["SIZE"] = 255;
            
            if(!$desc["SIZE"]) 
            {
                if($desc["TYPE"] == "YN") $desc["SIZE"] = 3*$cell_size;
            
                $desc["SIZE"] = 4*$cell_size; 
            } 
            
            if(intval($desc["SIZE"])>0) $desc["HZM-WIDTH"] = round(intval($desc["SIZE"])/$cell_size);
            if($desc["SIZE"]=="AREA") $desc["HZM-WIDTH"] = 12;
            if($desc["SIZE"]=="AEREA") $desc["HZM-WIDTH"] = 12;
            if($desc["HZM-WIDTH"] > 12) $desc["HZM-WIDTH"] = 12;
            if($desc["TYPE"]=="MFK") $desc["HZM-WIDTH"] = 12;
            
            return  $desc; 

    }

    /**
     * @param AFWObject $object 
     * @param boolean $structure 
     * @return array
     */


    public static final function getAllRealFields($object, $structure=false)
    {
        $class_db_structure = $object->getMyDbStructure();
        $result_arr = [];
        foreach ($class_db_structure as $attribute => $desc) {
            if (AfwStructureHelper::attributeIsReel($object, $attribute)) {
                if(!$structure) $result_arr[] = $attribute;
                else $result_arr[$attribute] = $desc;
            }
        }
        return $result_arr;
    }


    public static final function fixStructureOf($object, $attribute, $desc=null)
    {
        if (!$desc) {
            return AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            return AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
    }


    public static final function editIfEmpty($object, $attribute, $desc = null)
    {
        $desc = AfwStructureHelper::fixStructureOf($object, $attribute, $desc);
        
        return $desc['READONLY'] and $desc['EDIT_IF_EMPTY'];
    }


    // attribute can be modified by user in standard HZM-UMS model
    public static function itemsEditableBy($object, $attribute, $user = null, $desc = null)
    {
        $desc = AfwStructureHelper::fixStructureOf($object, $attribute, $desc);

        if (!isset($desc['ITEMS-EDITABLE']) or $desc['ITEMS-EDITABLE']) {
            return [true, ''];
        } else {
            return [false, "$attribute items not editable"];
        }
    }


    // attribute can be modified by user in standard HZM-UMS model
    public static final function attributeCanBeModifiedBy($object, $attribute, $user, $desc)
    {
        self::lookIfInfiniteLoop(30000, "attribute-CanBeModifiedBy-$attribute");
        global $display_in_edit_mode;
        // $objme = AfwSession::getUserConnected();
        $desc = AfwStructureHelper::fixStructureOf($object, $attribute, $desc);

        //if($attribute == "orgunit_id") die("desc of $attribute ". var_export($desc,true));

        if (AfwStructureHelper::editIfEmpty($object, $attribute, $desc) and $object->isEmpty()) {
            // die("desc of $attribute ". var_export($desc,true));
            $desc['EDIT'] = true;
            $desc['READONLY'] = false;
        } elseif ($display_in_edit_mode['*'] and $desc['SHOW']) {
            if (!$desc['EDIT']) {
                $desc['EDIT'] = true;
                $desc['READONLY'] = true;
                $desc['READONLY_REASON'] = 'SHOW and not EDIT';
            }
        }

        if ($desc['CATEGORY'] == 'ITEMS') {
            list($desc['EDIT'], $reason) = AfwStructureHelper::itemsEditableBy($object, $attribute, $user, $desc);
            $desc['READONLY'] = true;
            if ($desc['EDIT']) {
                // this is bug ITEMS attribute should remain readonly
                // $desc["DISABLE-READONLY-ITEMS"] = true;
            } else {
                $desc['READONLY_REASON'] = $reason;
            }
        }

        if ($desc['CATEGORY']) {
            if ($desc['EDIT-OTHERWAY']) {
                return [true, ''];
            }
        }

        if ($desc['READONLY'] and $desc['READONLY-MODULE']) {
            if ($user->canDisableRO($desc, $desc['READONLY-MODULE'])) {
                $desc['READONLY'] = false;
            }
        }

        if ($desc['READONLY']) {
            if ($desc['DISABLE-READONLY-ITEMS']) {
                return [true, ''];
            } elseif ($desc['DISABLE-READONLY-ADMIN']) {
                if (!$user) {
                    return [
                        false,
                        'the attribute is set readonly for all except admin and you are not logged',
                    ];
                }
                if (!$user->isSuperAdmin()) {
                    return [
                        false,
                        'the attribute is set readonly for all except super-admin and you are not super-admin',
                    ];
                }
                return [true, ''];
            } else {
                return [
                    false,
                    "the attribute $attribute is set readonly absolutely or for this user roles in the system, desc =" .
                        var_export($desc, true),
                ];
            }
        } else {
            return [true, ''];
        }
    }


    public static final function attributeIsWriteableBy(
        $object,
        $attribute,
        $user = null,
        $desc = null
    ) {
        if (!$user) {
            $user = AfwSession::getUserConnected();
        }
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
        if ($desc['CATEGORY'] == 'ITEMS') {
            return AfwStructureHelper::itemsEditableBy($object, $attribute, $user, $desc);
        }

        list($readonly, $reason) = $object->attributeIsReadOnly(
            $attribute,
            $desc
        );

        return [!$readonly, $reason];
    }

    public static final function attributeIsReadOnly(
        $object,
        $attribute,
        $desc = '',
        $submode = '',
        $for_this_instance = true,
        $reason_readonly = false
    ) {
        // self::safeDie("attributeIsReadOnly($attribute)");
        /*
        This is not logic attributes R/O or no it is not mandatory to have relation with user authenticated
        $objme = AfwSession::getUserConnected();
        if (!$objme) {
            if (!$reason_readonly) {
                return true;
            } else {
                return [true, 'no user connected'];
            }
        }*/
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
        if ($desc['TYPE'] == 'PK') {
            if (!$reason_readonly) {
                return true;
            } else {
                return [
                    true,
                    "this is PK : $attribute => " . var_export($desc, true),
                ];
            }
        }

        // attribute est editable or no by his definition by default
        $attributeIsEditable = AfwStructureHelper::attributeIsEditable($object,$attribute);

        $canBeUpdated = true;
        if ($attributeIsEditable) {
            // attribute est editable or no in some specific context or for specific user
            $objme = AfwSession::getUserConnected();
            list(
                $canBeUpdated,
                $the_reason_readonly,
            ) = $object->attributeCanBeUpdatedBy($attribute, $objme, $desc);
            if (!$the_reason_readonly) {
                $the_reason_readonly =
                    'Error : attribute Can Be Updated By returned empty reason and should not';
            }
            // if($attribute=="doc_type_id") die("list(canBeUpdated=$canBeUpdated, reason=$the_reason_readonly) = this->attributeCanBeUpdatedBy($attribute, $objme, $desc)");
        } else {
            // if attribute is not editable by his definition by default no need to check the context or rights of authenticated user
            $the_reason_readonly =
                'attribute is not editable by his definition by default';
        }
        $attrIsSetReadonly = !$canBeUpdated;

        $is_attributeIsReadOnly = (!$attributeIsEditable or $attrIsSetReadonly);

        //if($attribute=="trips_html") die("attributeIsReadOnly($attribute)=$is_attributeIsReadOnly : attributeIsEditable=$attributeIsEditable attrIsSetReadonly=$attrIsSetReadonly, the_reason_readonly=$the_reason_readonly");

        if (!$reason_readonly) {
            return $is_attributeIsReadOnly;
        } else {
            return [$is_attributeIsReadOnly, $the_reason_readonly];
        }
    }


    public final static function attributeIsAuditable($object, $attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        }
        $return = intval($desc['AUDIT']);
        if (!$return and $desc['AUDIT']) {
            $return = 1;
        } // nb_months of audit

        return $return;
    }

    public static final function stepIsReadOnly($object, $step, $reason_readonly = false)
    {
        $class_db_structure = $object->getMyDbStructure();
        $isROReason_arr = [];
        foreach ($class_db_structure as $nom_col => $desc) {
            if ($desc['STEP'] == $step or $step == 'all') {
                list($isRO, $isROReason) = $object->attributeIsReadOnly(
                    $nom_col,
                    '',
                    '',
                    true,
                    true
                );

                if (!$reason_readonly) {
                    if (!$isRO) {
                        return false;
                    }
                } else {
                    if (!$isRO) {
                        return [false, ''];
                    } else {
                        $isROReason_arr[] =
                            "stepIsReadOnly($step) for column name " .
                            $nom_col .
                            ' : ' .
                            $isROReason;
                    }
                }
            }
        }

        if (!$reason_readonly) {
            return true;
        } else {
            return [true, ' + ' . implode("\n + ", $isROReason_arr)];
        }
    }

    public static final function attributeIsEditable(
        $object,
        $attribute,
        $desc = '',
        $submode = '',
        $for_this_instance = true
    ) {
        global $display_in_edit_mode;
        /*
        This is not logic attributes editable or no it is not mandatory to have relation with user authenticated
        $objme = AfwSession::getUserConnected();

        if (!$objme) {
            return false;
        }*/
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        if ($display_in_edit_mode['*'] and $desc['SHOW']) {
            // <-- be careful getStructureOf make SHOW=true if RETRIEVE=true
            //if($attribute=="response_date") throw new AfwRuntimeException("rafik here 20200310 desc= ".var_export($desc,true));
            if (
                !$desc['EDIT'] and
                $desc['CATEGORY'] != 'FORMULA' and
                $desc['TYPE'] != 'PK'
            ) {
                $desc['EDIT'] = true;
                $desc['READONLY'] = true;
            }
        }
        // rafik 20/12/2019 not needed id_obj hidden always exists and for all steps not only step=1 so the line below is nomore usefull
        // if($desc['TYPE'] == 'PK') return true;

        if (!$submode) {
            $mode_code = 'EDIT';
        } else {
            $mode_code = "EDIT_$submode";
        }

        $applicable =
            (!$for_this_instance or $object->attributeIsApplicable($attribute));
        $mode_activated = isset($desc[$mode_code]) && $desc[$mode_code];
        $mode_activated_otherway =
            isset($desc["$mode_code-OTHERWAY"]) && $desc["$mode_code-OTHERWAY"];


        $is_attributeEditable =
            ($applicable and
                ($mode_activated or
                    $mode_activated_otherway or
                    (($objme = AfwSession::getUserConnected()) && $objme->isSuperAdmin() &&
                        isset($desc["$mode_code-ADMIN"]) &&
                        $desc["$mode_code-ADMIN"]) or
                    ($applicable and
                        $desc["$mode_code-ROLES"] and ($objme = AfwSession::getUserConnected()) and
                        $objme->i_have_one_of_roles($desc["$mode_code-ROLES"]))));
        //if($attribute=="trips_html") die("attributeIsEditable($attribute) : is_attributeEditable=$is_attributeEditable : applicable=$applicable and <br>(mode_activated=$mode_activated or mode_activated_for_me_as_admin=$mode_activated_for_me_as_admin or mode_activated_for_me_as_i_have_role=$mode_activated_for_me_as_i_have_role)");

        return $is_attributeEditable;
    }


    public static final function isQuickEditableAttribute($object, 
        $attribute,
        $desc = '',
        $submode = ''
    ) {

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        if ($desc['TYPE'] == 'PK') {
            return true;
        }

        if (!$submode) {
            $qedit_mode_code = 'QEDIT';
        } else {
            $qedit_mode_code = "QEDIT_$submode";
        }

        return AfwStructureHelper::attributeIsEditable(
            $object,
            $attribute,
            $desc,
            $submode,
            false
        ) and
            (!isset($desc[$qedit_mode_code]) or
                $desc[$qedit_mode_code] or
                ($desc["$qedit_mode_code-ADMIN"] and ($objme = AfwSession::getUserConnected()) and $objme->isAdmin()));
    }

    public static final function reasonWhyAttributeNotQuickEditable($object, 
        $attribute,
        $desc = '',
        $submode = ''
    ) {
        // @todo rafik according to the above method
        // $objme = AfwSession::getUserConnected();
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
        if (!$submode) {
            $qedit_mode_code = 'QEDIT';
        } else {
            $qedit_mode_code = "QEDIT_$submode";
        }

        $attributeIsEditable = AfwStructureHelper::attributeIsEditable($object,
            $attribute,
            $desc,
            $submode
        );
        if (!isset($desc[$qedit_mode_code])) {
            $val_of_qedit_mode_code = 'not set';
        } else {
            $val_of_qedit_mode_code = $desc[$qedit_mode_code];
        }

        if (!isset($desc["$qedit_mode_code-ADMIN"])) {
            $val_of_admin_qedit_mode_code = 'not set';
        } else {
            $val_of_admin_qedit_mode_code = $desc["$qedit_mode_code-ADMIN"];
        }

        $mode_field_qedit_reason = "qedit_mode_code={desc[$qedit_mode_code]:$val_of_qedit_mode_code,desc[$qedit_mode_code-ADMIN]:$val_of_admin_qedit_mode_code}, attributeIsEditable=$attributeIsEditable, ";

        return $mode_field_qedit_reason;
    }

    public static final function isShowableAttribute($object,
        $attribute,
        $desc = '',
        $submode = ''
    ) {

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
        if ($desc['TYPE'] == 'PK') {
            return true;
        }
        if (!$submode) {
            $mode_code = 'SHOW';
        } else {
            $mode_code = "SHOW_$submode";
        }

        return $desc[$mode_code] or ($desc["$mode_code-ADMIN"]  && ($objme = AfwSession::getUserConnected()) && $objme->isAdmin());
    }

    public static final function isReadOnlyAttribute($object, $attribute, $desc = '', $submode = '')
    {

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
        if ($desc['TYPE'] == 'PK') {
            return true;
        }

        if (!$submode) {
            $mode_code = 'READONLY';
        } else {
            $mode_code = "READONLY_$submode";
        }

        return self::isShowableAttribute($object, $attribute, $desc, $submode) &&
            ($desc[$mode_code] and
                (!$desc["DISABLE-$mode_code-ADMIN"] or
                    !($objme = AfwSession::getUserConnected()) or
                    !$objme->isSuperAdmin()));
    }

    public static function getEmptyObject($object, $attribute)
    {
        global $lang;

        list($fileName, $className, $ansTab, $ansModule,) = AfwStructureHelper::getFactoryForFk($object, $attribute);
        if (!$className) {
            throw new AfwRuntimeException("Failed to getEmptyObject from this -> getFactoryForFk($attribute) => list($fileName, $className, $ansTab, $ansModule) with this = " . $object->getDefaultDisplay($lang));
        }

        return new $className();
    }


    public static function classIsLookupTable($className)
    {
        $obj = new $className();
        $ret = $obj->IS_LOOKUP;
        unset($obj);
        return $ret;
    }

    /** 
        * @param AFWObject $object
        * @param string $attribute
        * @param array $desc
        * @return bool
    */

    public static function isLookupAttribute($object, $attribute, $desc=null)
    {
        if(!$desc) $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        if($desc["ANSWER-IS-LOOKUP"]) return true;
        list($fileName, $className) = AfwStructureHelper::getFactoryForFk($object, $attribute, $desc);
        return self::classIsLookupTable($className);        
    }


    /** getFactoryForFk
         * @param AFWObject $object
         * @param string $attribute
         * @param array $desc
         * @return array
    */

    public static function getFactoryForFk($object, $attribute, $desc=null)
    {
        list($ansTab, $ansModule) = $object->getMyAnswerTableAndModuleFor($attribute, $desc);
        // die("list($ansTab, $ansModule) = $this => getMyAnswerTableAndModuleFor($attribute)");
        list($fileName, $className) = AfwStringHelper::getHisFactory($ansTab, $ansModule);
        $return_arr = [];
        $return_arr[] = $fileName;
        $return_arr[] = $className;
        $return_arr[] = $ansTab;
        $return_arr[] = $ansModule;
        return $return_arr;
    }


    /** getAnswerModule
         * @param AFWObject $object
         * @param string $attribute
         * @param array $desc
         * @return array
    */
    public static function getAnswerModule($object, $attribute)
    {
        list($ansTab, $ansModule) = $object::answerTableAndModuleFor($attribute);
        return $ansModule;
    }




    /** getParentStruct
         * @param AFWObject $object
         * @param string $attribute
         * @return array
    */
    public static function getParentStruct($object, $attribute, $struct)
    {
        if (!$struct) $struct = AfwStructureHelper::getStructureOf($object, $attribute);
        $this_table = $object::$TABLE;
        list($fileName, $className) = AfwStructureHelper::getFactoryForFk($object, $attribute, $struct);
        list($attribParent, $structParent) = $className::getParentOf($this_table, $attribute);
        return $structParent;
    }

    /*
        really exists even if it is not real but virtual (category not empty)
    */
    public static function fieldReallyExists($object, $attribute, $structure=null)
    {
        if(!$structure) $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        return ($structure["TYPE"] or $object->isTechField($attribute)); //  or $this->getAfieldValue($attribute)
    }


    public static function attributeIsReel($object, $attribute, $structure = null)
    {
        if (is_numeric($attribute)) {
            return false;
        }
        if (!$structure) {
            $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $structure = AfwStructureHelper::repareMyStructure($object, $structure, $attribute);
        }
        // if($attribute=="nomcomplet") die("structure of $attribute =".var_export($structure,true));
        return $structure and !$structure['CATEGORY'];
    }

    public static final function getEnumAnswerList($object, $attribute, $enum_answer_list = '')
    {
        $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        if ($structure['ANSWER'] == 'INSTANCE_FUNCTION') {
            $method = "at_of_$attribute";

            $liste_rep = $object->$method();
        } else {
            $liste_rep = $object->getEnumTotalAnswerList(
                $attribute,
                $enum_answer_list
            );
        }

        return $liste_rep;
    }


}