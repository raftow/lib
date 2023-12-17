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
                throw new RuntimeException("Momken 3.0 Error : [Class=$cl,Attribute=$field_name] is shortcut but not declared in overridden shouldBeCalculatedField method, do like this : <pre><code>".$object->suggestAllCalcFields()."</code></pre>");
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

}