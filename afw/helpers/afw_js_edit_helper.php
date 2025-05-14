<?php 

class AfwJsEditHelper extends AFWRoot 
{
    /**
     * @var AFWObject $object
     */
    public static function getJsOfLoadMyProps($object,    
        $attribute,
        $desc = '',
        $original_attribute = ''
    )     
    {
        if (!$original_attribute) {
            $original_attribute = $attribute;
        }

        $qedit_suffix = substr($attribute, strlen($original_attribute));

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $original_attribute);
        }

        $objectid = $object->getId();
        $className = $object->getMyClass();
        $currmod = $object->getMyModule();
        $js_source = '';
        $attribute_loadMyProps_fn = $attribute . '_loadMyProps';

        $props_dependencies_setting = "";

        $js_source .= "function $attribute_loadMyProps_fn() {  \n";
        $js_source .= "     
                    \$.getJSON(\"../lib/api/loadmyprops.php\", 
                    {
                    cl:\"$className\",
                    currmod:\"$currmod\",
                    objid:\"$objectid\",
                    attribute: \"$original_attribute\",
                    attributeval: \$(\"#$attribute\").val(), 
                    },
                    
                    function(result)
                    {
                        // var \$select = \$('#$attribute'); 
                        \$.each(result, function(prop, value) {
                            \$(\"#\"+prop).val(value);
                        });
                    });
                   }  
                   /*******************************  end of  $attribute_loadMyProps_fn  *****************************/  ";
        return $js_source;
    }
        

    
    /**
     * @var AFWObject $object
     */
    public static function getJsOfReloadOf($object,    
        $attribute,
        $desc = '',
        $original_attribute = ''
    ) 
    {
        // $lang = AfwLanguageHelper::getGlobalLanguage();
        // $objme = AfwSession::getUserConnected();
        if (!$original_attribute) {
            $original_attribute = $attribute;
        }

        $qedit_suffix = substr($attribute, strlen($original_attribute));

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $original_attribute);
        }
        if ($desc['REQUIRED'] or $desc['MANDATORY']) {
            $option_empty_value = '';
        } else {
            $option_empty_value = ' value=0';
        }

        $objectid = $object->getId();
        $className = $object->getMyClass();
        $currmod = $object->getMyModule();
        $js_source = '';
        $attribute_reload_fn = $attribute . '_reload';

        if ($desc['DEPENDENCY']) {
            $desc['DEPENDENCIES'] = [$desc['DEPENDENCY']];
        }

        $dependencies_values = '';
        $fld_deps = '';
        $fld_deps_vals = '';
        foreach ($desc['DEPENDENCIES'] as $fld) {
            $fld_suffixed = $fld . $qedit_suffix;
            //  $fld_deps .= "/".$fld;
            //  $fld_deps_vals .= "+";
            //  $fld_deps_vals .= "'/'+\$(\"#$fld\").val()";
            if ($dependencies_values) {
                $dependencies_values .= ",\n";
            }
            $dependencies_values .= "                    post_attr_$fld: \$(\"#$fld_suffixed\").val()";
        }

        $js_source .= "function $attribute_reload_fn() {  \n";
        $js_source .= "     // alert(\"\"+\$(\"#$fld\").val());
                    // fld_deps_vals = '' $fld_deps_vals ;
                    // alert(\"$attribute_reload_fn running deps = [$fld_deps] = [\"+fld_deps_vals+\"] \");
                    \$.getJSON(\"../lib/api/anstab.php\", 
                    {
                    keepCurrent: 1,
                    cl:\"$className\",
                    currmod:\"$currmod\",
                    objid:\"$objectid\",
                    attribute: \"$original_attribute\",
                    attributeval: \$(\"#$attribute\").val(), 
                    
$dependencies_values
                        
                    },
                    
                    function(result)
                    {
                    var \$select = \$('#$attribute'); 
                    \$select.find('option').remove();
                    \$select.append('<option$option_empty_value></option>');
                    \$.each(result, function(i, field) {
                         \$select.append('<option value=' + i + '>' + field + '</option>');
                    });
                    });
                   }  
                   /*******************************  end of  $attribute_reload_fn  *****************************/  ";
        return $js_source;
    }


    public static function isLoadMyPropsField($fld)
    {
        $fld_items = explode("/", $fld);
        $isLoadMyPropsField = ((count($fld_items)>1) and ($fld_items[0]=="LOAD-MY-PROPS"));
        return $isLoadMyPropsField;
    }

    public static function getLoadMyPropsItems($fld)
    {
        $fld_items = explode("/", $fld);
        $isLoadMyPropsField = ((count($fld_items)>1) and ($fld_items[0]=="LOAD-MY-PROPS"));
        if(!$isLoadMyPropsField) $fld_items = [];
        else unset($fld_items[0]);

        $items = [];

        foreach($fld_items as $fld_item)
        {
            list($source_item, $dest_item) = explode(":",$fld_item);
            if(!$dest_item) $dest_item = $source_item;
            $items[$source_item] = $dest_item;
        }

        return $items;
    }

/**
     * @var AFWObject $object
     */
    public static function getAttributeLoadMyPropsItems($desc) 
    {
        foreach ($desc['DEPENDENT_OFME'] as $fld) 
        {
            
            if(self::isLoadMyPropsField($fld))
            {
                return self::getLoadMyPropsItems($fld);
            }
        }
    }

    /**
     * @var AFWObject $object
     */
    public static function getJsOfOnChangeOf($object,
        $attribute,
        $desc = '',
        $name_only = true,
        $original_attribute = ''
    ) {
        // $lang = AfwLanguageHelper::getGlobalLanguage();
        $attribute_onchange_fn = $attribute . '_onchange';
        if ($name_only) {
            return "$attribute_onchange_fn()";
        }
        // $objme = AfwSession::getUserConnected();
        if (!$original_attribute) {
            $original_attribute = $attribute;
        }
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $original_attribute);
        }
        $qedit_suffix = substr($attribute, strlen($original_attribute));
        $js_source = '';

        $js_source .= "function $attribute_onchange_fn() { \n";
        foreach ($desc['DEPENDENT_OFME'] as $fld) 
        {
            
            if(self::isLoadMyPropsField($fld))
            {
                $attribute_load_my_props_fn = $attribute . '_loadMyProps';
                $js_source .= "   $attribute_load_my_props_fn(); \n";
            }
            else
            {
                $fld_suffixed = $fld . $qedit_suffix;

                $js_source .= "   " . $fld_suffixed . "_reload(); \n";
                $js_source .= "   " . $fld_suffixed . "_onchange(); \n";
            }
            
        }
        $js_source .= "\n} \n/*******************************  end of  $attribute_onchange_fn  *****************************/  ";

        return $js_source;
    }


    /**
     * @var AFWObject $object
     */
    public static function getDependencyIdsArray($object,$attribute, $desc = null, $implode = true, $js = true)
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        }

        if ($desc['DEPENDENCY']) {
            $desc['DEPENDENCIES'] = [$desc['DEPENDENCY']];
        }



        if ($js) {
            $dependencies_value_arr = [];
            foreach ($desc['DEPENDENCIES'] as $fld) {
                $dependencies_value_arr[] = "\$(\"#$fld\").val()";
            }

            if ($implode)  $return = implode(". ',' .", $dependencies_value_arr);
            else  $dependencies_value_arr;
        } else {
            $dependencies_value_arr = [];
            foreach ($desc['DEPENDENCIES'] as $fld) {
                $dependencies_value_arr[] = $object->getVal($fld);
                /*
                if($attribute=="training_unit_id") 
                {
                    die(" $object => getVal($fld) = ".$object->getVal($fld));
                }*/
            }

            if ($implode)  $return = implode(",", $dependencies_value_arr);
            else $return = $dependencies_value_arr;
        }



        // if($attribute=="training_unit_id") die("getDependencyIdsArray($attribute) = [$return] : desc['DEPENDENCIES'] = ".var_export($desc['DEPENDENCIES'],true)." ");

        return $return;
    }
}