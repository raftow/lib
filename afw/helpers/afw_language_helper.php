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

    
    public static final function getAttributeTranslation($object,
        $attribute,
        $lang = 'ar',
        $short = false
    ) {
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
}

?>
