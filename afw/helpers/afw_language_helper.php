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

        //if((!$module) and isset(static::$MODULE)) $module = self::$MODULE;

        $paths[] = "$file_dir_name/../../pag";
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
        return self::tarjem($text, $langue, false, '', 'pag');
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
        $file_dir_name = dirname(__FILE__)."/..";
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }


        if (isset($trad) and $trad and (!is_array($trad))) {
            $trad = [];
            // throw new AfwRuntimeException("before any include trad 0 is ".var_export($trad,true));
        }

        if (empty($operator)) {
            if ($nom_table) {
                $nom_file =
                    "$file_dir_name/../../$module/tr/trad_" .
                    strtolower($langue) .
                    "_$nom_table.php";
                $nom_file2 =
                    "$file_dir_name/../../external/translate/$module/trad_" .
                    strtolower($langue) .
                    "_$nom_table.php";
                //if($this->MY_DEBUG)
                //        AFWDebugg::log("traduire from file $nom_file ");

                if (file_exists($nom_file2)) {
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire include_once $nom_file ");
                    include_once $nom_file2;
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    if (isset($trad) and $trad and (!is_array($trad))) {
                        throw new AfwRuntimeException("after include_once $nom_file2 trad 2 is " . var_export($trad, true));
                    }

                    if ($trad[$nom_table][$nom_col]) {
                        return $trad[$nom_table][$nom_col];
                    }
                }

                if (file_exists($nom_file)) {
                    // if(($module=="sis") and ($nom_table=="student")) die("nom_file2=$nom_file2 not found");
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire include_once $nom_file ");
                    include_once $nom_file;
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    if (isset($trad) and $trad and (!is_array($trad))) {
                        throw new AfwRuntimeException("after include_once $nom_file trad 1 is " . var_export($trad, true));
                    }

                    if ($trad[$nom_table][$nom_col]) {
                        return $trad[$nom_table][$nom_col];
                    }
                }
            } else {
                $nom_table = '*';
            }
            if (
                !isset($trad[$nom_table][$nom_col]) ||
                empty($trad[$nom_table][$nom_col])
            ) {
                $general_nom_file =
                    "$file_dir_name/tr/trad_" .
                    strtolower($langue) .
                    '_all.php';

                if (file_exists($general_nom_file)) {
                    include_once $general_nom_file;

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
        } else {
            $langue = strtolower($langue);
            if (!$langue) {
                $langue = 'ar';
            }

            if ($nom_table) {
                $nom_file0 =
                    "$file_dir_name/../../$module/tr/trad_" .
                    strtolower($langue) .
                    "_$nom_table.php";
                //if($this->MY_DEBUG)
                //        AFWDebugg::log("traduire from file $nom_file ");
                if (file_exists($nom_file0)) {
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire include_once $nom_file ");
                    include_once $nom_file0;
                    if (isset($trad) and $trad and (!is_array($trad))) {
                        throw new AfwRuntimeException("after include_once $nom_file0 trad 4 is " . var_export($trad, true));
                    }
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    if ($trad['OPERATOR'][$nom_col]) {
                        return $trad['OPERATOR'][$nom_col];
                    }
                }
            }

            $file_name = "$file_dir_name/../../external/translate/$module/trad_" . $langue . '_afw.php';
            if (file_exists($file_name)) {
                $ff = 'file found';
                include_once $file_name;
                if (isset($trad) and $trad and (!is_array($trad))) {
                    throw new AfwRuntimeException("after include_once $file_name trad 5 is " . var_export($trad, true));
                }

                $trad_val = $trad['OPERATOR'][$nom_col];
                if ($trad_val) {
                    return $trad_val;
                }
            }
            //else die($file_name);

            $file_name = "$file_dir_name/tr/trad_" . $langue . '_afw.php';
            // if($nom_col=="_DISPLAY") die("AfwLanguageHelper::tarjem($nom_col, $langue,$operator,$nom_table, $module) : file_name=$file_name");
            $ff = 'file not found';
            if (file_exists($file_name)) {
                $ff = 'file found';
                include_once $file_name;
                if (isset($trad) and $trad and (!is_array($trad))) {
                    throw new AfwRuntimeException("after include_once $file_name trad 6 is " . var_export($trad, true));
                }
                $trad_val = $trad['OPERATOR'][$nom_col];
                if ($trad_val) {
                    return $trad_val;
                }
            } else {
                AfwRunHelper::simpleError(
                    "file not exists $file_name for langue $langue"
                );
            }

            if ($nom_col == '_DISPLAY') {
                AfwRunHelper::simpleError(
                    "AfwLanguageHelper::tarjem($nom_col, $langue,$operator,$nom_table, $module) = '$trad_val' from $file_name ($ff)"
                );
            }
            return $nom_col;
        }
    }

    public static function tarjemOperator($text, $langue = 'ar', $external = 'obsolete')
    {
            return AfwLanguageHelper::tarjem($text, $langue, true, '', 'pag');
    }

    public static function tarjemMessage($message, $module, $lang = 'ar')
    {
            global $messages;
            $file_dir_name = dirname(__FILE__)."/..";

            include_once "$file_dir_name/../../pag/messages_$lang.php";
            include_once "$file_dir_name/../../$module/messages_$lang.php";

            if ($messages[$message]) {
                    return $messages[$message];
            } else {
                    return $message;
            }
    }
}
