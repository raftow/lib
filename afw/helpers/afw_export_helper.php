<?php

class AfwExportHelper
{

    /**
     * afwExport : nice display of variables in a momken project
     * @param mixed $var
     */
    public static function afwExport($var, $recursive = true, $insideObjects = false, $bigArraysMaxShow = 30)
    {
        try {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            $result = [];
            if (is_object($var)) {
                if ($var instanceof AFWObject) {
                    if (!$insideObjects) {
                        $result = $var->getDisplay($lang);
                    } else {
                        $result = $var->getAllfieldValues();
                    }
                } else $result = $var->__toString();
            } elseif (is_array($var)) {
                $counter = 0;
                foreach ($var as $ky => $varItem) {
                    if ($counter < $bigArraysMaxShow) {
                        $counter++;
                        $new_recursive =  $recursive;
                        if (is_integer($new_recursive)) $new_recursive--;

                        if ($recursive) {
                            $result[$ky] = self::afwExport($varItem, $new_recursive, $insideObjects);
                        } elseif (is_object($varItem) and ($varItem instanceof AFWObject)) {
                            $result[$ky] = $varItem->getDisplay($lang);
                        } elseif (is_object($varItem)) $result[$ky] = $varItem->__toString();
                        else $result[$ky] = $varItem;
                    } else {
                        $result["limit-reached"] = $bigArraysMaxShow . " item(s) reached skip the rest of items ...";
                    }
                }
            } else {
                $result = $var;
            }

            if (is_array($result)) return self::displayArray($result, $recursive);
            else return $result;
        } catch (Exception $e) {
            return "Error doing afwExport";
        }
    }

    /**
     * displayArray : display a simple array (key, value)
     * @param array $result
     */
    public static function displayArray($result, $recursive = true, $cssClass = "debugg", $keyName = "")
    {
        $tbl = new HtmlyTableau();
        $tbl->addClass($cssClass);
        foreach ($result as $key => $val) {
            if (($recursive !== true) and is_integer($recursive)) $recursive--;
            if (is_array($val)) $val_display = self::displayArray($val, $recursive, $cssClass);
            else $val_display = $val;
            $keyCode = (($key != "") and ($key != 0)) ? $key : $keyName . "[$key]";
            $cells = ['key' => $keyCode, 'val' => $val_display,];
            $tbl->addElement(new HtmlyRowBody("", "", "", $cells));
        }

        return $tbl->renderHtml();
    }
}
