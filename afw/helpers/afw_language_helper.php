<?php


class AfwLanguageHelper
{
    public static function getGlobalLanguage()
    {
        global $lang;
        if (!$lang) {
            $lang = 'ar';
        }
        return $lang;
    }

    /**
     * @param AFWObject $object
     * 
     * 
     */

    public static final function getAttributeTranslation($object, $attribute, $lang = 'ar', $short = false)
    {
        $return = '';
        if ($short) {
            $return = $object->translate($attribute . '.short', $lang);
        }
        if ($return == $attribute . '.short') {
            $return = '';
        }
        // if($attribute=="cher_id") die("getAttributeTranslation($attribute, $lang, short=$short) = $return");
        if (!$return) {
            $return = $object->translate($attribute, $lang);
        }
        return $return;
    }

    public static function getTranslationPaths($module = "", $parent_module = "")
    {
        $file_dir_name = dirname(__FILE__)."/..";

        $paths = array();

        $paths[] = "$file_dir_name/../../lib";
        $paths[] = "$file_dir_name/../../ums";
        $paths[] = "$file_dir_name/../../hrm";
        $paths[] = "$file_dir_name/../../crm";
        if ($module) $paths[] = "$file_dir_name/../../$module";
        if ($parent_module) $paths[] = "$file_dir_name/../../$parent_module";

        return $paths;
    }

    public static function tt($text, $lang = "ar", $module = "", $parent_module = "")
    {
        global $messages;
        $file_dir_name = dirname(__FILE__)."/..";


        $paths = self::getTranslationPaths($module, $parent_module);
        foreach ($paths as $path) include_once $path . "/messages_$lang.php";

        if ($messages[$text]) return $messages[$text];
        else return $text;
        // else return $text."paths=".var_export($paths,true)." messages=".var_export($messages,true); 
    }


    public static function tarjemText($text, $langue = 'ar')
    {
        return self::tarjem($text, $langue, false, '', '');
    }


    public static function tarjem(
        $nom_col,
        $langue = 'ar',
        $operator = null,
        $nom_table = '',
        $module = ''
    ) 
    {
        global $lang, $trad;
        $company = AfwSession::config("main_company", "");
        $file_dir_name = dirname(__FILE__)."/..";
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }

        $langue = strtolower($langue);
        if (isset($trad) and $trad and (!is_array($trad))) {
            $trad = [];
            // throw new AfwRuntimeException("before any include trad 0 is ".var_export($trad,true));
        }

        if ($trad[$nom_table][$nom_col]) {
            return $trad[$nom_table][$nom_col];
        }



        if (empty($operator)) 
        {
            if ($nom_table) 
            {
                $nom_file  = "$file_dir_name/../../$module/tr/trad_" . $langue . "_$nom_table.php";
                $nom_file2 = "$file_dir_name/../../external/translate-$company/$module/trad_" . $langue . "_$nom_table.php";
                //if($object->MY_DEBUG)
                //        AFWDebugg::log("traduire from file $nom_file ");

                if (file_exists($nom_file2)) {
                    // if(($module=="adm") and ($nom_table=="applicant")) echo("tarjem find 1 the file $nom_file2");
                    //if($object->MY_DEBUG)
                    //    AFWDebugg::log("traduire include_once $nom_file ");
                    include $nom_file2;
                    //if($object->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    if (isset($trad) and $trad and (!is_array($trad))) {
                        throw new AfwRuntimeException("after include_once $nom_file2 trad 2 is " . var_export($trad, true));
                    }

                    if ($trad[$nom_table][$nom_col]) {
                        return $trad[$nom_table][$nom_col];
                    }
                }

                if (file_exists($nom_file)) {
                    //if(($module=="adm") and ($nom_table=="applicant") and ($nom_col=="address_type_enum")) echo(" 2. tarjem find the file nom_file=$nom_file <br>");
                    //if($object->MY_DEBUG)
                    //    AFWDebugg::log("traduire include_once $nom_file ");

                    $classTranslator = AfwStringHelper::tableToClass($nom_table."_".$langue."_translator");
                    if(!class_exists($classTranslator,false))
                    {
                        include $nom_file;
                    }
                    // if(($module=="adm") and ($nom_table=="applicant") and ($nom_col=="address_type_enum")) die(" 2. tarjem : trad[$nom_table][$nom_col] = ".$trad[$nom_table][$nom_col]);
                    // if($object->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    
                    if(class_exists($classTranslator,false))
                    {
                        $trad = $classTranslator::initData();
                    }
                    

                    if (isset($trad) and $trad and (!is_array($trad))) {
                        throw new AfwRuntimeException("after include_once $nom_file trad 1 is " . var_export($trad, true));
                    }

                    if ($trad[$nom_table][$nom_col]) {
                        return $trad[$nom_table][$nom_col];
                    }
                }
            } 
            else 
            {
                $nom_table = '*';
            }
            
            if (
                !isset($trad[$nom_table][$nom_col]) ||
                empty($trad[$nom_table][$nom_col])
            ) 
            {
                $general_nom_file = "$file_dir_name/tr/trad_" .$langue . '_all.php';
                if (file_exists($general_nom_file)) {
                    include $general_nom_file;
                    // if(($module=="adm") and ($nom_table=="applicant") and ($nom_col=="address_type_enum")) die("tarjem find 3 the file general_nom_file=$general_nom_file");
                    if (isset($trad) and $trad and (!is_array($trad))) {
                        throw new AfwRuntimeException("after include_once $general_nom_file trad 3 is " . var_export($trad, true));
                    }

                    //echo "<br>2)translate $nom_table.$nom_col in $langue from general file $general_nom_file"."=".$trad[$nom_table][$nom_col];
                    if (
                        isset($trad['*'][$nom_col]) &&
                        !empty($trad['*'][$nom_col])
                    ) {
                        return $trad['*'][$nom_col];
                    }
                }
                //echo "<br>3)no translate keep $nom_table.$nom_col ";
                return $nom_col;
            } else {
                //echo "<br>4)translate $nom_table.$nom_col in $langue from memory = ".$trad[$nom_table][$nom_col]."=".$trad[$nom_table][$nom_col];
                return $trad[$nom_table][$nom_col];
            }
        } 
        else // case operator translation
        {
            $file_name = "$file_dir_name/tr/trad_" . $langue . '_afw.php';
            
            $classTranslator = AfwStringHelper::tableToClass("afw_operator_".$langue."_translator");
            if(!class_exists($classTranslator,false))
            {
                include $file_name;
            }
                    
            $trad = $classTranslator::initData();
            
            if ($trad['OPERATOR'][$nom_col]) {
                return $trad['OPERATOR'][$nom_col];
            }

            return $nom_col;
        }
    }

    public static function translateKeyword($text, $langue = 'ar', $external = 'obsolete')
    {
            return AfwLanguageHelper::tarjem($text, $langue, true, '', '');
    }

    public static function tarjemMessage($message, $module, $lang = 'ar')
    {
            global $messages;
            $file_dir_name = dirname(__FILE__)."/..";

            include_once "$file_dir_name/../../lib/messages_$lang.php";
            include_once "$file_dir_name/../../$module/messages_$lang.php";

            if ($messages[$message]) {
                    return $messages[$message];
            } else {
                    return $message;
            }
    }

    /**
     * @param AFWObject $object
     * 
     * 
     */

    public static function getTranslatedAttributeProperty(
        $object,
        $attribute,
        $attribute_property,
        $lang,
        $desc = null
    ) {
        if (!$desc) $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        $attribute_property_code = $desc[$attribute_property];

        if (!$attribute_property_code) {
            $attribute_property_code = $attribute . '_' . $attribute_property;
        }

        $attribute_property_trans = $object->translateMessage(
            $attribute_property_code,
            $lang
        );
        if ($attribute_property_trans == $attribute_property_code) {
            $attribute_property_trans = '';
        }
        
        if (!$attribute_property_trans) {
            $attribute_property_code = strtoupper($attribute_property_code);
            $attribute_property_trans = $object->translateMessage(
                $attribute_property_code,
                $lang
            );
            if ($attribute_property_trans == $attribute_property_code) {
                $attribute_property_trans = '';
            }
        }
        
        if (!$attribute_property_trans) {
            $attribute_property_code = strtolower($attribute_property_code);
            $attribute_property_trans = $object->translate(
                $attribute_property_code,
                $lang
            );
        }

        //if(($attribute=="picture_height") and ($attribute_property=="UNIT")) die(" $attribute_property_trans = this->translate($attribute_property_code,$lang) ");

        if ($attribute_property_trans == $attribute_property_code) {
            $attribute_property_trans = '';
        }

        if (!$attribute_property_trans) {
            $attribute_property_trans = $desc[$attribute_property];
        }

        return $attribute_property_trans;
    }
}
