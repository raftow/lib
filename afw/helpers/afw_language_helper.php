<?php


class AfwLanguageHelper
{
    public static function getGlobalLanguage()
    {
        $langue = AfwSession::getSessionVar("current_lang");
        if (!$langue) 
        {
            global $lang;
            $langue = $lang;
            if (!$langue) $langue = 'ar';
        }    
        
        return $langue;
    }

    public static function getLanguageDir($lang)
    {
        if ($lang == "ar") {
            $dir = 'rtl';
        } else {
            $dir = 'ltr';
        }

        return $dir;
    }

    public static function getLanguageAlign($lang)
    {
        if ($lang == "ar") {
            $dir = 'right';
        } else {
            $dir = 'left';
        }

        return $dir;
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
        //if(($module=="crm") and ($nom_table=="request") and ($nom_col=="archive")) die("debugg $nom_table trad of $nom_col is here 1 ");
        $company = AfwSession::config("main_company", "");
        $file_dir_name = dirname(__FILE__)."/..";
        if (!$langue) {
            throw new AfwRuntimeException("Lang should be defined to be able to translate");
        }

        $langue = strtolower($langue);
        if (isset($trad) and $trad and (!is_array($trad))) {
            $trad = [];
            // throw new AfwRuntimeException("before any include trad 0 is ".var_export($trad,true));
        }

        if ($trad[$nom_table][$nom_col]) {
            // if(($module=="crm") and ($nom_table=="request") and ($nom_col=="archive")) die("debugg $nom_table trad of $nom_col = ".var_export($trad,true));
            return $trad[$nom_table][$nom_col];
        }



        if (empty($operator)) 
        {
            if ($nom_table) 
            {
                $nom_file  = "$file_dir_name/../../$module/tr/trad_" . $langue . "_$nom_table.php";
                $nom_file2 = "$file_dir_name/../../client-$company/translate/$module/trad_" . $langue . "_$nom_table.php";
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
                        $caseTrans = "include $nom_file";
                        include $nom_file;
                    }
                    
                    // if($object->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    
                    if(class_exists($classTranslator,false))
                    {
                        $trad = $classTranslator::initData();
                        $caseTrans = "$classTranslator::initData()";
                    }
                    
                    // if(($langue=="en") and ($module=="adm") and ($nom_table=="program_track") and ($nom_col=="programtrack.single")) die("from caseTrans=$caseTrans / lang=$langue: trad[$nom_table][$nom_col] = ".$trad[$nom_table][$nom_col]);

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
                //die("tarjem with general_nom_file=$general_nom_file");
                if (file_exists($general_nom_file)) {
                    // die("exists general_nom_file=$general_nom_file");
                    include $general_nom_file;
                    // if(($module=="crm") and ($nom_table=="request") and ($nom_col=="archive")) die("tarjem find 3 the file general_nom_file=$general_nom_file");
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
            // if(($module=="crm") and ($nom_table=="request") and ($nom_col=="archive")) die("debugg $nom_table trad of $nom_col will be with classTranslator=$classTranslator in $file_name");
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

    public static function translateKeyword($text, $langue = 'ar')
    {
            return AfwLanguageHelper::tarjem($text, $langue, true, '', '');
    }

    public static function translateYesNo($what, $lang = '')
    {
        $yes = "Y";
        $no = "N";
        if($what=="decodeme")
        {
            if(!$lang) $lang = AfwLanguageHelper::getGlobalLanguage();
            $yes = AfwLanguageHelper::translateKeyword($yes, $lang);
            $no = AfwLanguageHelper::translateKeyword($no, $lang);
        }
        return [$yes,$no];
        
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

    public static function translateCompanyMessage($message, $module, $lang = 'ar', $company = "")
    {
        if (!$module) throw new AfwRuntimeException("\$module param should be defined for translateCompanyMessage method");
        $return = $message;
        $file_dir_name = dirname(__FILE__)."/..";

        include "$file_dir_name/../../$module/messages_$lang.php";
        if ($company) {
            include "$file_dir_name/../../client-$company/translate/$module/messages_$company" . "_$lang.php";
        }

        if ($messages[$message]) {
            $return = $messages[$message];
        } else {
            include "$file_dir_name/../../lib/messages_$lang.php";

            if ($messages[$message]) {
                $return = $messages[$message];
            }
        }

        $return = AfwReplacement::trans_replace($return, $module, $langue);

        return $return;
    }


    public static function translateCols($object, $cols, $lang = 'ar', $short = false)
    {
        $tableau = [];

        foreach ($cols as $attribute) {
            if ($short) {
                $tableau[$attribute] = $object->translate(
                    $attribute . '.short',
                    $lang
                );
            }

            if (
                !$tableau[$attribute] or
                $tableau[$attribute] == $attribute . '.short'
            ) {
                $tableau[$attribute] = $object->translate($attribute, $lang);
            }
        }
        return $tableau;
    }

    public function getTransDisplayField($object, $lang = 'ar')
    {
        if ($lang == 'fr') {
            $lang = 'en';
        }

        if (!$object->DISPLAY_FIELD) {
            $all_real_fields = AfwStructureHelper::getAllRealFields($object);
            $object->DISPLAY_FIELD = $all_real_fields[1];
        }

        if (!$object->DISPLAY_FIELD) {
            $object->DISPLAY_FIELD = $object->getPKField();
        }

        if (
            AfwStringHelper::stringStartsWith($object->DISPLAY_FIELD, '_ar') or
            AfwStringHelper::stringStartsWith($object->DISPLAY_FIELD, '_fr') or
            AfwStringHelper::stringStartsWith($object->DISPLAY_FIELD, '_en')
        ) {
            $disp_fld_std = substr(
                $object->DISPLAY_FIELD,
                0,
                strlen($object->DISPLAY_FIELD) - 3
            );
        } else {
            $disp_fld_std = $object->DISPLAY_FIELD;
        }

        $display_field_trad = $disp_fld_std . '_' . $lang;

        if (AfwStructureHelper::fieldExists($object, $display_field_trad)) {
            return $display_field_trad;
        }

        return $object->DISPLAY_FIELD;
    }
}
