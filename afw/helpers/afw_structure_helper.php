<?php
class AfwStructure
{
    public static $DB_STRUCTURE = null;
}

class AfwStructureHelper extends AFWRoot
{
    private static $shortNamesArray = [];
    private static $structuresArray = [];
    public static function dd($message, $to_die = true, $to_debugg = false, $trace = true, $light = false)
    {
        if ($trace) $message = $message . "<br>" . AfwRunHelper::show_back_trace($light);
        if ($to_debugg) AFWDebugg::log($message);
        if ($to_die) {
            $html = ob_get_clean();
            die($html . $message);
        }
    }


    /**
     * @param AFWObject $object
     */
    public static final function getStructureOf(&$object, $field_name, $repare = true)
    {
        $cl = get_class($object);
        $struct = null;
        if ($repare) $struct = self::$structuresArray[$cl][$field_name];
        if (!$struct) {
            // if($field_name=="level_enum") throw new RuntimeException("rafik 240927");
            $orig_field_name = $field_name;
            $field_name = AfwStructureHelper::shortNameToAttributeName($object, $field_name);
            if (!$field_name) $field_name = $orig_field_name;
            $struct = $object->getMyDbStructure(
                $return_type = 'structure',
                $field_name,
                false
            );

            if ($struct and $repare) {
                $struct = AfwStructureHelper::repareMyStructure($object, $struct, $field_name);
            }

            if (($struct["CATEGORY"] == "SHORTCUT") /* or (($struct["CATEGORY"]=="FORMULA") and ($field_name != "tech_notes"))*/) {
                if (!$object->shouldBeCalculatedField($field_name)) {
                    throw new AfwRuntimeException("Momken 3.0 Error : [Class=$cl,Attribute=$field_name] is shortcut but not declared in overridden shouldBeCalculatedField method, do like this : <pre><code>" . self::suggestAllCalcFields($object) . "</code></pre>");
                }
            }

            if ($repare) self::$structuresArray[$cl][$field_name] = $struct;
        }
        return $struct;
    }


    public static function getDbStructure(
        $module_code,
        $class_name,
        $table_name = 'obsolete',
        $return_type = 'structure',
        $attribute = 'all',
        $step = 'all',
        $start_step = null,
        $end_step = null,
        $repare = true
    ) {
        if ($return_type == 'shortnames') {
            $attribute = 'all';
            $this_short_names = [];
        }

        if ($return_type == 'shortcuts') {
            $attribute = 'all';
            $this_shortcuts = [];
        }

        if ($return_type == 'formulas') {
            $attribute = 'all';
            $this_formulas = [];
        }



        $got_first_time = false;

        if (!$module_code) {
            $module_code = AfwUrlManager::currentURIModule();
        }


        $debugg_db_structure = AfwStructureHelper::constructDBStructure($module_code, $class_name, $attribute, $step, $start_step, $end_step);
        //if(($table_name=="invester") and ($attribute=="city_id")) die($table_name." AfwStructureHelper::constructDBStructure($module_code, $class_name, $attribute) returned debugg_db_structure = ".var_export($debugg_db_structure,true));
        if (isset($debugg_db_structure)) {
            foreach ($debugg_db_structure as $key => $value) {
                if ($value['ANSWER'] and $value['TYPE'] != 'ANSWER') {
                    if (!$value['ANSMODULE']) {
                        $debugg_db_structure[$key]['ANSMODULE'] = $value['ANSMODULE'] = $module_code;
                    }
                }
                if ($value['SHORTCUT'] and $return_type == 'shortcuts') {
                    $this_shortcuts[$key] = true;
                }

                if (($value['CATEGORY'] == "FORMULA") and $return_type == 'formulas') {
                    if ($key != "tech_notes") $this_formulas[$key] = true;
                }



                if ($value['SHORTNAME'] and $return_type == 'shortnames') {
                    // first be sure the this short name is not already used as attribute
                    if ($debugg_db_structure[$value['SHORTNAME']]) {
                        throw new AfwRuntimeException("the short name '" . $value['SHORTNAME'] . "' for attribute $key already used in the same class as attribute name.");
                    }
                    $this_short_names[$value['SHORTNAME']] = $key;
                }
            }
        } else {
            throw new AfwRuntimeException("Check if DB_STRUCTURE is defined for $attribute attribute(s) for class " . $class_name);
        }
        // if(($table_name=="invester") and ($attribute=="city_id")) die($table_name." AfwStructureHelper::constructDBStructure($module_code, $class_name, $attribute) returned debugg_db_structure = ".var_export($debugg_db_structure,true));
        if ($return_type == 'structure') {
            if (true) {
                if ($attribute != 'all') {
                    $struct = $debugg_db_structure[$attribute];
                    // if(($table_name=="school") and ($attribute=="roomList")) die($table_name.", struct of $attribute (before repare) = ".var_export($struct,true)." debugg_db_structure = ".var_export($debugg_db_structure, true));
                    if ($struct and $repare) {
                        $struct = AfwStructureHelper::repareStructure($struct);
                    }
                    // if(($table_name=="school") and ($attribute=="roomList")) die($table_name.", struct of $attribute (after repare) = ".var_export($struct,true)." debugg_db_structure = ".var_export($debugg_db_structure, true));
                    return $struct;
                } else {
                    foreach ($debugg_db_structure as $key => $struct) {
                        if (($key != 'all') and $repare) {
                            $debugg_db_structure[$key] = AfwStructureHelper::repareStructure($struct);
                        }
                    }

                    return $debugg_db_structure;
                }
            }
        } elseif ($return_type == 'shortnames') {
            return $this_short_names;
        } elseif ($return_type == 'shortcuts') {
            return $this_shortcuts;
        } elseif ($return_type == 'formulas') {
            return $this_formulas;
        }


        return ["momken" => "unknown-requested-return_type $return_type"];
    }

    public static final function attributeBelongToStep($attribute, $desc, $step)
    {
        if (
            $step == 'all' or
            $desc['STEP'] == 'all' or
            $desc['STEP'] == $step
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $moduleDomain 
     * @param string $class_name 
     * 
     * @return AfwStructure
     */

    public static final function getStructureClassName($moduleDomain, $class_name)
    {
        return $moduleDomain . $class_name . 'AfwStructure';
    }


    public static final function constructDBStructure($module_code, $class_name, $attribute, $step = "all", $start_step = null, $end_step = null)
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
            $class_name_strcucture = self::getStructureClassName($moduleDomain, $class_name);
            if ((PHP_VERSION_ID < 80000) or true) {
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
        if ($step == "all") {
            if ($attribute == 'all') {
                if ($start_step and $end_step) {
                    $debugg_db_structure = [];
                    foreach ($my_db_structure as $my_attrib => $my_struct) {
                        if ($my_struct["STEP"] >= $start_step and $my_struct["STEP"] <= $end_step) {
                            $debugg_db_structure[$my_attrib] = $my_struct;
                        }
                    }
                } else $debugg_db_structure = $my_db_structure;
            } else {
                $debugg_db_structure = [];
                $debugg_db_structure[$attribute] = $my_db_structure[$attribute];
            }
        } else {
            $debugg_db_structure = [];
            foreach ($my_db_structure as $my_attrib => $my_struct) {
                if (self::attributeBelongToStep($my_attrib, $my_struct, $step)) {
                    $debugg_db_structure[$my_attrib] = $my_struct;
                }
            }
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
                    AfwRunHelper::lightSafeDie(
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

        if ($struct['SHOW-ADMIN']) {
            $struct['EDIT-ADMIN'] = true;
            if (!$struct['DISPLAY']) {
                $struct['DISPLAY'] = true;
                $struct['READONLY'] = true;
            }
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
        } else {
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

        foreach ($struct as $col_struct => $value_struct) {
            if (is_string($value_struct)) {
                if (AfwStringHelper::stringStartsWith($value_struct, '>>config::')) {
                    $configStructEval = substr($value_struct, 10);
                    list($configVar, $defaultValue) = explode($configStructEval, ",");
                    $struct[$col_struct] = AfwSession::config($configVar, $defaultValue);
                }
            }
        }

        return $struct;
    }

    /**
     * @param AFWObject $object
     * 
     */

    public static final function repareMyStructure(&$object, $struct, $field_name)
    {
        if (!($object instanceof AFWObject)) {
            throw new AfwRuntimeException("repareMyStructure first parameter should be AFWObject instance");
        }

        //if($field_name == "nomcomplet") die("in getStructureOf($field_name) run of this->getMyDbStructure($return_type, $field_name) = ".var_export($struct,true));
        if (
            $object->editByStep and
            !$object->disableTechnicalFieldsDefaultBehavior and
            ($object->isAdminField($field_name) or
                $object->isTechField($field_name))
        ) {
            $struct['SHOW'] = true;
            if (!$struct['STEP-CUSTOMIZED']) $struct['STEP'] = 99;
            $struct['EDIT'] = true;
            $struct['READONLY'] = true;
        }

        if (($struct["TYPE"] == "YN") and (strtoupper($struct["FORMAT"]) == "ICON") and (!$struct["SWITCHER"])) {
            $struct["SWITCHER"] = "onoff";
        }


        if ($struct['READONLY-AFTER-INSERT']) {
            if (!$object->isEmpty()) {
                $struct['READONLY'] = true;
            }
        }

        foreach ($struct as $col_struct => $value_struct) {
            if (is_string($value_struct)) {
                if (AfwStringHelper::stringStartsWith($value_struct, '>>config::')) {
                    $configStructEval = substr($value_struct, 10);
                    list($configVar, $defaultValue) = explode($configStructEval, ",");
                    $struct[$col_struct] = AfwSession::config($configVar, $defaultValue);
                } elseif (AfwStringHelper::stringStartsWith($value_struct, '::')) {                    
                    $methodStructEval = substr($value_struct, 2);
                    // if(($field_name=="qsearch") and ($col_struct=="READONLY")) die("rafik-20240916-field_name=$field_name col_struct=$col_struct value_struct=$value_struct methodStructEval=$methodStructEval");
                    $struct[$col_struct] = $object->$methodStructEval($field_name, $col_struct);
                    $struct["$col_struct-from"] = "method::$methodStructEval";
                    $objectClass = get_class($object);
                    //  and ($object->getVal("field_name")=="qsearch")
                    if(($objectClass=="ApplicationField") and ($object->id>0) and ($field_name=="qsearch") and ($col_struct=="READONLY")) die("rafik-20240916-field_name=$field_name col_struct=$col_struct value_struct=$value_struct methodStructEval=$methodStructEval, struct[$col_struct] = object($object->id, fname=".$object->getVal("field_name").")->$methodStructEval($field_name, $col_struct) = ".var_export($struct[$col_struct],true)); 
                    if(($objectClass=="ApplicationField") and (!$object->id) and ($field_name=="qsearch") and ($col_struct=="READONLY"))
                    {
                        throw new AfwRuntimeException("ان شاء الله وجدناها");
                    }
                    /*
                    if($field_name=="value") die("rafik-20240916-field_name=[$field_name], 
                                                    <br> col_struct=[$col_struct], 
                                                    <br> value_struct=$value_struct, 
                                                    <br> object class is $objectClass,
                                                    <br> calculated struct[$col_struct] = object-> $methodStructEval($field_name, $col_struct) 
                                                    <br> result : struct[$col_struct] = ".$struct[$col_struct]);*/
                }
            }
        }

        return AfwStructureHelper::repareStructure($struct);
    }


    public static final function repareQEditAttributeStructure($col_name, $desc)
    {
        $cell_size = 11;

        if (AfwStringHelper::stringStartsWith($col_name, "titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
        if (AfwStringHelper::stringStartsWith($col_name, "titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
        if (AfwStringHelper::stringStartsWith($col_name, "titre") && (!$desc["SIZE"])) $desc["SIZE"] = 255;

        if (!$desc["SIZE"]) {
            if ($desc["TYPE"] == "YN") $desc["SIZE"] = 3 * $cell_size;

            $desc["SIZE"] = 4 * $cell_size;
        }

        if (intval($desc["SIZE"]) > 0) $desc["HZM-WIDTH"] = round(intval($desc["SIZE"]) / $cell_size);
        if ($desc["SIZE"] == "AREA") $desc["HZM-WIDTH"] = 12;
        if ($desc["SIZE"] == "AEREA") $desc["HZM-WIDTH"] = 12;
        if ($desc["HZM-WIDTH"] > 12) $desc["HZM-WIDTH"] = 12;
        if ($desc["TYPE"] == "MFK") $desc["HZM-WIDTH"] = 12;

        return  $desc;
    }

    public static $allRealFields = null;

    /**
     * @param AFWObject $object 
     * @param boolean $structure 
     * @return array
     */

    public static final function getAllRealFields(&$object, $structure = false)
    {
        if (!$structure) {
            $cls = $object->getMyClass();
            if (self::$allRealFields[$cls]) return self::$allRealFields[$cls];
        }


        $class_db_structure = $object->getMyDbStructure();
        $result_arr = [];
        foreach ($class_db_structure as $attribute => $desc) {
            if (AfwStructureHelper::attributeIsReel($object, $attribute, $desc)) {
                if (!$structure) $result_arr[] = $attribute;
                else $result_arr[$attribute] = $desc;
            }
        }
        if (!$structure) {
            self::$allRealFields[$cls] = $result_arr;
        }

        return $result_arr;
    }


    public static final function fixStructureOf(&$object, $attribute, $desc = null)
    {
        if (!$desc) {
            return AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            return AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
    }


    public static final function editIfEmpty(&$object, $attribute, $desc = null)
    {
        $desc = AfwStructureHelper::fixStructureOf($object, $attribute, $desc);

        return $desc['READONLY'] and $desc['EDIT_IF_EMPTY'];
    }


    // attribute can be modified by user in standard HZM-UMS model
    public static function itemsEditableBy(&$object, $attribute, $user = null, $desc = null)
    {
        $desc = AfwStructureHelper::fixStructureOf($object, $attribute, $desc);

        if (!isset($desc['ITEMS-EDITABLE']) or $desc['ITEMS-EDITABLE']) {
            return [true, ''];
        } else {
            return [false, "$attribute items not editable"];
        }
    }


    // attribute can be modified by user in standard HZM-UMS model
    public static final function attributeCanBeModifiedBy(&$object, $attribute, $user, $desc)
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

        list($readonly, $reason) = AfwStructureHelper::attributeIsReadOnly(
            $object,
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
        // AfwRunHelper::safeDie("attributeIsReadOnly($attribute)");
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
        $attributeIsEditable = AfwStructureHelper::attributeIsEditable($object, $attribute);

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


    public final static function attributeIsAuditable(&$object, $attribute, $desc = '')
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

    public static final function stepIsReadOnly(&$object, $step, $reason_readonly = false)
    {
        $class_db_structure = $object->getMyDbStructure();
        $isROReason_arr = [];
        foreach ($class_db_structure as $nom_col => $desc) {
            if ($desc['STEP'] == $step or $step == 'all') {
                list($isRO, $isROReason) = AfwStructureHelper::attributeIsReadOnly(
                    $object,
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
        if (!$object) return false;
        if (!$object->attributeIsApplicable($attribute)) return false;
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


    public static final function isQuickEditableAttribute(
        &$object,
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
            ( /* !isset($desc[$qedit_mode_code]) or // Rafik This at left is old logic should be obsolete now and QEDIT mode should be explicit */
                $desc[$qedit_mode_code] or
                ($desc["$qedit_mode_code-ADMIN"] and ($objme = AfwSession::getUserConnected()) and $objme->isAdmin()));
    }

    public static final function reasonWhyAttributeNotQuickEditable(
        &$object,
        $attribute,
        $desc = '',
        $submode = ''
    ) {
        if (!$object) return "no object to quick edit";
        if (!$object->attributeIsApplicable($attribute)) return "attribute $attribute is not applicable";

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

        $attributeIsEditable = AfwStructureHelper::attributeIsEditable(
            $object,
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

    public static final function isShowableAttribute(
        &$object,
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

    public static final function isReadOnlyAttribute(&$object, $attribute, $desc = '', $submode = '')
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

    /**
     * 
     * @return AFWObject
     */

    public static function getEmptyObject(&$object, $attribute)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();

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

    public static function isLookupAttribute(&$object, $attribute, $desc = null)
    {
        if (!$desc) $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        if ($desc["ANSWER-IS-LOOKUP"]) return true;
        list($fileName, $className) = AfwStructureHelper::getFactoryForFk($object, $attribute, $desc);
        return self::classIsLookupTable($className);
    }


    /** getFactoryForFk
     * @param AFWObject $object
     * @param string $attribute
     * @param array $desc
     * @return array
     */

    public static function getFactoryForFk(&$object, $attribute, $desc = null)
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
    public static function getAnswerModule(&$object, $attribute)
    {
        list($ansTab, $ansModule) = $object::answerTableAndModuleFor($attribute);
        return $ansModule;
    }


    public static function getParentOf($className, $this_table, $attribute)
    {
        $this_db_structure = $className::getDbStructure($return_type = 'structure', 'all');
        foreach ($this_db_structure as $attrib => $desc) {
            if (
                $desc['CATEGORY'] == 'ITEMS' and
                $desc['ANSWER'] == $this_table and
                $desc['ITEM'] == $attribute
            ) {
                return [$attrib, $desc];
            }
        }

        return [null, null];
    }

    /** getParentStruct
     * @param AFWObject $object
     * @param string $attribute
     * @return array
     */
    public static function getParentStruct(&$object, $attribute, $struct)
    {
        if (!$struct) $struct = AfwStructureHelper::getStructureOf($object, $attribute);
        $this_table = $object::$TABLE;
        list($fileName, $className) = AfwStructureHelper::getFactoryForFk($object, $attribute, $struct);
        list($attribParent, $structParent) = AfwStructureHelper::getParentOf($className, $this_table, $attribute);
        return $structParent;
    }

    /*
        really exists even if it is not real but virtual (category not empty)
    */
    public static function fieldReallyExists(&$object, $attribute, $structure = null)
    {
        if (!$structure) $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        return ($structure["TYPE"] or $object->isTechField($attribute)); //  or $this->getAfieldValue($attribute)
    }


    public static function attributeIsReel(&$object, $attribute, $structure = null)
    {
        if (is_numeric($attribute)) {
            return false;
        }
        if ((!$structure) or (AfwStringHelper::stringStartsWith($structure['CATEGORY'], "::"))) {
            $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        }
        // if($attribute=="nomcomplet") die("structure of $attribute =".var_export($structure,true));
        return ($structure and !$structure['CATEGORY'] and !$structure['OBSOLETE']);
    }

    public static final function getEnumAnswerList(&$object, $attribute, $enum_answer_list = '')
    {
        $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        if ($structure['ANSWER'] == 'INSTANCE_FUNCTION') {
            $method = "at_of_$attribute";

            $liste_rep = $object->$method();
        } else {
            $liste_rep = AfwLoadHelper::getEnumTotalAnswerList($object, $attribute, $enum_answer_list);
        }

        return $liste_rep;
    }


    public static final function getDefaultValue(&$object, $attribute, $struct = null)
    {
        if (!$struct) $struct = AfwStructureHelper::getStructureOf($object, $attribute);
        return $struct['DEFAULT'];
    }

    public static final function getHelpFor(&$object, $attribute_original, $lang = 'ar')
    {
        if (!$object->dynamicHelpCondition($attribute_original)) {
            return '';
        }
        $struct = AfwStructureHelper::getStructureOf($object, $attribute_original);

        $this_help_text = $attribute_original . '_help_text';
        $this_help_text = $object->translate($this_help_text, $lang);
        $this_help_text = $object->decodeTpl($this_help_text);

        $instance_help_text = '';

        if ($struct['TYPE'] == 'FK') {
            $obj = $object->het($attribute_original);
            if ($obj) {
                $obj_id = $obj->getId();
                $instance_help_text = $obj->translate("instance_" . $obj_id . "_help_text", $lang);
                $instance_help_text = $object->decodeTpl($instance_help_text);
                $instance_help_text = $obj->decodeTpl($instance_help_text);
            }
        }

        return trim($this_help_text . $instance_help_text);
    }

    public static function fieldExists(&$object, $attribute)
    {
        $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        // if($attribute=="draft" and ($this instanceof CrmOrgunit)) die("structure for attribute $attribute = ".var_export($structure,true));
        // if(!$structure) die(get_class($this)." structure for attribute $attribute dos not exists ");
        if ($structure['TYPE']) {
            return true;
        }
        if ($object->isTechField($attribute)) {
            return true;
        }
        return false;
    }

    public static function isRealAttribute(&$object, $attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        }

        return !$desc['CATEGORY'];
    }

    public static function isSettable(&$object, $attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        }

        $is_category_field = $desc['CATEGORY'];
        $can_be_setted_field = $desc['CAN-BE-SETTED'];
        return [
            $is_category_field,
            $desc and (!$is_category_field or $can_be_setted_field),
        ];
    }

    public static function suggestAllCalcFields(&$object)
    {
        $result = "public function shouldBeCalculatedField(\$attribute){\n";

        $shortcuts = $object::getShortcutFields();
        foreach ($shortcuts as $attribute => $is_shortcut) {
            if ($is_shortcut) $result .= "   if(\$attribute==\"$attribute\") return true;\n";
        }
        $formulas = $object::getFormulaFields();
        foreach ($formulas as $attribute => $is_formula) {
            if ($is_formula) $result .= "   if(\$attribute==\"$attribute\") return true;\n";
        }
        $result .= "   return false;\n";
        $result .= "}";

        return $result;
    }

    public static function suggestAllShortNames(&$object)
    {
        $short_names = $object::getShortNames();
        $result = "public function myShortNameToAttributeName(\$attribute){\n";

        foreach ($short_names as $short_name => $attribute) {
            $result .= "   if(\$attribute==\"$short_name\") return \"$attribute\";\n";
        }
        $result .= "   return \$attribute;\n";
        $result .= "}";

        return $result;
    }

    public static function shortNameToAttributeName(&$object, $attribute)
    {
        $cl = get_class($object);
        $attribute_reel = self::$shortNamesArray[$cl][$attribute];
        if (!$attribute_reel) {
            $attribute_reel = $object->myShortNameToAttributeName($attribute);
        }

        if (!$attribute_reel) $attribute_reel = $attribute;

        self::$shortNamesArray[$cl][$attribute] = $attribute_reel;

        return $attribute_reel;
    }

    public static final function containItems(&$object, $attribute)
    {
        $attribute = AfwStructureHelper::shortNameToAttributeName($object, $attribute);
        return $object->getCategoryOf($attribute) == 'ITEMS';
    }

    public static final function containObjects(&$object, $attribute)
    {
        $attribute = AfwStructureHelper::shortNameToAttributeName($object, $attribute);
        $typeOfAtt = $object->getTypeOf($attribute);
        return AfwUmsPagHelper::isObjectType($typeOfAtt);
        //return (array_key_exists($attribute, $this->AFIELD _VALUE) and (( == "MFK") or ($this->getTypeOf($attribute) == "FK")));
    }

    public static final function containData(&$object, $attribute)
    {
        $attribute = AfwStructureHelper::shortNameToAttributeName($object, $attribute);
        $typeOfAtt = $object->getTypeOf($attribute);
        return AfwUmsPagHelper::isValueType($typeOfAtt);
        //return ((array_key_exists($attribute, $this->AFIELD _VALUE) or $this->attributeIsFormula($attribute)) and (($this->getTypeOf($attribute) != "MFK") and ($this->getTypeOf($attribute) != "FK")));
    }

    public static function isEasyAttribute(&$object, $attribute)
    {
        if (!$object->easyModeNotOptim()) {
            return false;
        } else {
            return AfwStructureHelper::isEasyAttributeNotOptim($object, $attribute);
        }
    }

    public static final function isEasyAttributeNotOptim(&$object, $attribute, $struct = null)
    {
        if (!$struct) $struct = AfwStructureHelper::getStructureOf($object, $attribute);
        return $struct and
            !$struct['CATEGORY'] and
            $struct['TYPE'] != 'MFK' and
            $struct['TYPE'] != 'FK';
    }


    public static final function isFormulaEasyAttributeNotOptim(&$object, $attribute)
    {
        $struct = AfwStructureHelper::getStructureOf($object, $attribute);

        return $struct and $struct['CATEGORY'] == 'FORMULA';
    }

    public static function isFormulaEasyAttribute(&$object, $attribute)
    {
        if (!$object->easyModeNotOptim()) {
            return false;
        } else {
            return AfwStructureHelper::isFormulaEasyAttributeNotOptim($object, $attribute);
        }
    }


    public static function isObjectEasyAttribute(&$object, $attribute)
    {
        if (!$object->easyModeNotOptim()) {
            return false;
        } else {
            return AfwStructureHelper::isObjectEasyAttributeNotOptim($object, $attribute);
        }
    }

    public static final function isObjectEasyAttributeNotOptim(&$object, $attribute)
    {
        $struct = AfwStructureHelper::getStructureOf($object, $attribute);

        $return =
            ($struct and !$struct['CATEGORY'] and $struct['TYPE'] == 'FK');

        //if($attribute=="bus") die("isObjectEasy=$return getStructureOf($attribute) = ".var_export($struct,true));

        return $return;
    }

    public static final function isListObjectEasyAttributeNotOptim(&$object, $attribute)
    {
        $struct = AfwStructureHelper::getStructureOf($object, $attribute);

        return $struct and
            (!$struct['CATEGORY'] and $struct['TYPE'] == 'MFK' or
                $struct['CATEGORY'] == 'ITEMS' and $struct['TYPE'] == 'FK');
    }

    public static function isListObjectEasyAttribute(&$object, $attribute)
    {
        if (!$object->easyModeNotOptim()) {
            return false;
        } else {
            return AfwStructureHelper::isListObjectEasyAttributeNotOptim($object, $attribute);
        }
    }

    public static function isMultipleObjectsAttribute(&$object, $attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        $attr_sup_categ = $desc['SUPER_CATEGORY'];
        $attr_categ = $desc['CATEGORY'];
        $attr_scateg = $desc['SUB-CATEGORY'];

        if ($attr_categ == 'ITEMS') {
            return true;
        }
        if ($attr_scateg == 'ITEMS') {
            return true;
        }
        if ($attr_sup_categ == 'ITEMS') {
            return true;
        }
        if ($desc['TYPE'] == 'MFK') {
            return true;
        }

        return false;
    }
}
