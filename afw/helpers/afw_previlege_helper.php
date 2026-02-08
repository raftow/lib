<?php
class AfwPrevilegeHelper
{
    // هنا نتكلم  عن البيانات في العمود بحسب السجل
    public static final function dataAttributeCanBeDisplayedForUser($object,
        $attribute,
        $auser,
        $mode = 'DISPLAY',
        $structure
    ) {
        // until we develop an optimized use of $mode-UGROUPS 
        // we will always authorize 
        return $structure;

        $mode = strtoupper($mode);

        if (!$structure) {
            $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $structure = AfwStructureHelper::repareMyStructure($object, $structure, $attribute);
        }

        $ugroups = $structure["$mode-UGROUPS"];

        if ($auser and $ugroups) {
            $auser_belong_to_ugroups = $auser->i_belong_to_one_of_ugroups(
                $ugroups,
                $object
            );
        }

        // if(($attribute=="idn") and ($auser->getId()==621)) die("this=$object, mode=$mode, auser=$auser, ugroups = ".var_export($ugroups,true)." -> auser_belong_to_ugroups=$auser_belong_to_ugroups");
        ($canDisplay = !$ugroups) or $auser_belong_to_ugroups;

        return $canDisplay ? $structure : false;
    }

    // هنا نتكلم عن العمود ككل وليس البيانات في العمود بحسب السجل
    public static final function keyIsToDisplayForUser($object, $key, $auser, $mode = 'DISPLAY')
    {
        $mode = strtoupper($mode);
        $mode_code = $mode;
        if ($mode == 'DISPLAY') {
            $mode_code = 'SHOW';
        }
        $structure = AfwStructureHelper::getStructureOf($object, $key);

        if ($structure['MINIBOX']) {
            $structure['SHOW'] = true;
        }

        global $display_in_edit_mode;
        if ($display_in_edit_mode['*'] and $structure['SHOW']) {
            if (
                !$structure['EDIT'] and
                $structure['CATEGORY'] != 'FORMULA' and
                $structure['TYPE'] != 'PK'
            ) {
                $structure['EDIT'] = true;
                $structure['READONLY'] = 'EDIT=false+SHOW=true';
            }
        }
        $user_can_see_attribute =
            ((!$structure["$mode-BFS"] or
                $auser and
                $auser->i_have_one_of_bfs($structure["$mode-BFS"])) and
                (!$structure["$mode-ROLES"] or
                    $auser and
                    $auser->i_have_one_of_roles($structure["$mode-ROLES"])));

        return ($user_can_see_attribute and
            ($structure["$mode-BFS"] or
                $structure["$mode_code-BFS"] or
                $structure["$mode-ROLES"] or
                $structure["$mode_code-ROLES"] or
                $structure[$mode] or
                $structure[$mode_code] or
                ($structure["$mode_code-ADMIN"] and $auser and $auser->isAdmin()) or
                ($object->arr_erros['all'][$key] and $structure["$mode_code-ERROR"])))
            ? $structure
            : false;
    }

    public static final function getReasonAttributeNotRetrievableOrRetrievable($object,
        $attribute,
        $mode = 'display',
        $lang = 'ar',
        $all = false,
        $desc = null
    ) {
        $attributeIsToDisplayForMe = $attributeIsToDisplayForAll = AfwPrevilegeHelper::keyIsToDisplayForUser($object,
            $attribute,
            null
        );

        if (!$attributeIsToDisplayForMe) {
            $objme = AfwSession::getUserConnected();
            $attributeIsToDisplayForMe = AfwPrevilegeHelper::keyIsToDisplayForUser($object,
                $attribute,
                $objme
            );
        }
        if (!$attributeIsToDisplayForMe) {
            return "when I can't see attribute $attribute how can I retrieve it";
        }

        $RETRIEVE_LANG = 'RETRIEVE-' . strtoupper($lang);

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        $is_force_retrieve =
            ($object->force_retrieve_cols and
                is_array($object->force_retrieve_cols) and
                in_array($attribute, $object->force_retrieve_cols));

        $is_general_retrieve =
            (isset($desc['RETRIEVE']) and $desc['RETRIEVE'] or
                isset($desc[$RETRIEVE_LANG]) and $desc[$RETRIEVE_LANG]);

        $retForMode = self::isRetrieveColForMode($object,
            $attribute,
            $mode,
            $lang,
            $all,
            $desc
        );

        ($generalRetrieveModeAllowed = $retForMode == 'W') and
            (($mode == 'display' or !$mode) and $is_general_retrieve);

        $reason =
            "retForMode($attribute, $mode)=[$retForMode] generalRetrieveModeAllowed = ($generalRetrieveModeAllowed) is_force_retrieve=($is_force_retrieve) this->force_retrieve_cols = " .
            var_export($object->force_retrieve_cols, true);

        return [$reason, $desc];
    }



    /**
     * getExcelCols
     * return array of columns for excel export
     * @param array $array
     */

     public static final function getExcelCols($object, $lang = 'ar') {

        $objme = AfwSession::getUserConnected();
        $all_nom_cols = $object->getAllAttributes();

        $cols_excel = [];

        foreach ($all_nom_cols as $nom_col) {
            $desc = AfwStructureHelper::getStructureOf($object, $nom_col);

            if (AfwPrevilegeHelper::keyIsToDisplayForUser($object, $nom_col, $objme)) {
                if ($desc['EXCEL'] or (!isset($desc['EXCEL']) && $desc['RETRIEVE']))
                        $cols_excel[$nom_col] = $object->translate($nom_col, $lang);
            }
        }

        return $cols_excel;

     }

    /**
     * getRetrieveCols
     * for display mode get retrieve columns
     * @param array $array
     */

     public static final function getRetrieveCols($object,
        $mode = 'display',
        $lang = 'ar',
        $all = false,
        $type = 'all',
        $debugg = false,
        $hide_retrieve_cols = null,
        $force_retrieve_cols = null,
        $category = 'all'
    ) {
        if (!$hide_retrieve_cols and !$force_retrieve_cols) {
            list(
                $hide_retrieve_cols,
                $force_retrieve_cols,
            ) = $object->setSpecialRetrieveCols();
        }

        // die("setSpecialRetrieveCols returned hide_retrieve_cols = ".var_export($hide_retrieve_cols,true).", force_retrieve_cols = ".var_export($force_retrieve_cols,true));
        $tableau = [];
        $tableau_final = [];
        $db_struct_all = $object->getAllMyDbStructure();

        foreach ($db_struct_all as $attribute => $descAttr) {
            if (AfwPrevilegeHelper::isRetrieveCol($object, $attribute, $mode, $lang, $all, $descAttr, $force_retrieve_cols)) 
            {
                if (
                    !$hide_retrieve_cols or
                    !is_array($hide_retrieve_cols) or
                    !count($hide_retrieve_cols) or
                    !in_array($attribute, $hide_retrieve_cols)
                ) {
                    // debugg why $attribute is shown when it should be hidden
                    // if($attribute=="man" and $hide_retrieve_cols) throw new AfwRuntimeException("$attribute is not in hide_retrieve_cols = ".var_export($hide_retrieve_cols,true));
                    $take = false;
                    if ($type == 'all') {
                        $take = true;
                    } else {
                        // if(!$descAttr) $descAttr = AfwStructureHelper::getStructureOf($object,$attribute);
                        if ($descAttr['TYPE'] == $type) {
                            $take = true;
                        }
                    }

                    $takeCateg = false;
                    if ($category == 'all') {
                        $takeCateg = true;
                    } else {
                        // if(!$descAttr) $descAttr = AfwStructureHelper::getStructureOf($object,$attribute);
                        if ($descAttr['CATEGORY'] == $category) {
                            $takeCateg = true;
                        } elseif ((!$descAttr['CATEGORY']) and ($category == "empty")) {
                            $takeCateg = true;
                        }
                    }

                    if ($take and $takeCateg) {
                        if($descAttr["RETRIEVE_LAST"]) $tableau_final[] = $attribute;
                        else $tableau[] = $attribute;
                    }
                }
                if ($debugg) {
                    // list($AttributeRetrievableWhy, $descAttr2)  = AfwPrevilegeHelper::getReasonAttributeNotRetrievableOrRetrievable($object, $attribute);
                    // if($attribute == "currentRequests") die("$attribute is RetrieveCol in mode $mode reason=$AttributeRetrievableWhy descAttr2=".var_export($descAttr2,true));
                }
            } elseif ($debugg) {
                // list($AttributeNotRetrievableWhy, $descAttr2)  = AfwPrevilegeHelper::getReasonAttributeNotRetrievableOrRetrievable($object, $attribute);
                // if($attribute == "ongoing_requests_count") die("$attribute is not RetrieveCol in mode $mode reason=$AttributeNotRetrievableWhy descAttr2=".var_export($descAttr2,true));
            }
        }

        $tableau = array_merge($tableau, $tableau_final);
        /*
        if(static::$TABLE=="practice")
        {
            $message = "tableau = ".var_export($tableau,true);
            throw new AfwRuntimeException("get RetrieveCols : debugg : $message");
        }
        */
        return $tableau;
    }


    public static final function prepareAfwTokens($object,
        $text_to_decode,
        $lang = 'ar',
        $trad_erase = [],
        $token_arr = [],
        $toLower=false,
        $fieldsTokenAlways=false,
    ) {

        //throw new AfwRuntimeException("token_arr = ".var_export($token_arr,true)." text_to_decode=$text_to_decode");
        if (is_array($object->otherTokens)) {
            foreach ($object->otherTokens as $tok => $tok_val) {
                $token_arr["[$tok]"] = $tok_val;
            }
        }
        $token_arr['[LANG]'] = $lang;
        $token_arr['[OBJECT_ID]'] = $object->getId();
        $token_arr['[OBJECT_DISPLAY]'] = $object->getDisplay($lang);
        $token_arr['[OBJECT_WIDE_DISPLAY]'] = $object->getWideDisplay($lang);
        $token_arr['[OBJECT_SHORT_DISPLAY]'] = $object->getShortDisplay($lang);
        $token_arr['[OBJECT_NODE_DISPLAY]'] = $object->getNodeDisplay($lang);
        $token_arr['[OBJECT_RETRIEVE_DISPLAY]'] = $object->getRetrieveDisplay(
            $lang
        );
        if (strpos($text_to_decode, '[ADMIN_START]') !== false) {
            $objme = AfwSession::getUserConnected();
            if ($objme and $objme->isAdmin()) {
                $token_arr['[ADMIN_START]'] = '';
                $token_arr['[ADMIN_END]'] = '';
            } else {
                $token_arr['[ADMIN_START]'] = '<!-- ';
                // if($objme) $token_arr["[ADMIN_START]"] .= "because ".$objme->getDisplay($lang)." id = " .$objme->getId()." is not admin";
                $token_arr['[ADMIN_END]'] = ' -->';
            }
        }

        if (strpos($text_to_decode, '[OBJECT_ERRORS]') !== false) {
            list($is_ok, $dataErr) = $object->isOk($force = true, true);
            if ($is_ok) {
                $token_arr['[ERROR_STATUS]'] = 'ok';
                $token_arr['[OBJECT_ERRORS]'] = '';
                $token_arr['[OBJECT_ERRORS_START]'] = '<!-- ';
                $token_arr['[OBJECT_ERRORS_END]'] = ' -->';
            } else {
                $errors_html = implode("<br>\n", $dataErr);
                $errors_html = trim($errors_html, "<br>\n");
                $token_arr['[ERROR_STATUS]'] = 'err';
                $token_arr['[OBJECT_ERRORS]'] = $errors_html;
                $token_arr['[OBJECT_ERRORS_START]'] = '';
                $token_arr['[OBJECT_ERRORS_END]'] = '';
            }
        } elseif (strpos($text_to_decode, '[ERROR_STATUS]') !== false) {
            if ($object->isOk($force = true)) {
                $token_arr['[ERROR_STATUS]'] = 'ok';
            } else {
                $token_arr['[ERROR_STATUS]'] = 'err';
            }
        }

        $object_db_structure = $object::getDbStructure(
            $return_type = 'structure',
            $attribute = 'all'
        );

        foreach ($object_db_structure as $fieldname => $struct_item) {
            $token_fcl = '[fcl:' . $fieldname . ']';

            $token_is = '[is:' . $fieldname . ']';
            $token_is_en = '[is-en:' . $fieldname . ']';
            $token_value = '[value:' . $fieldname . ']';
            $token_data = '[' . $fieldname . ']';
            $token_label = '[' . $fieldname . '_label]';
            $token_showme = '[' . $fieldname . '_showme]';

            if ($struct_item['TYPE'] == 'DATE') {
                $token_full_date = '[' . $fieldname . '.full]';
                $token_medium_date = '[' . $fieldname . '.medium]';
            }

            if ($struct_item['CATEGORY'] == 'ITEMS') {
                $token_data_no_icons = '[' . $fieldname . '.no_icons]';
            }

            if ($struct_item['TO_TRANSLATE']) {
                $token_to_translate = '[' . $fieldname . '.translate]';
            }

            if (($struct_item['TYPE'] == 'TEXT') and strpos($text_to_decode, $token_fcl) !== false) {
                $token_fcl_val = AfwStringHelper::firstCharLower(
                    $object->getVal($fieldname)
                );
                $token_arr[$token_fcl] = $token_fcl_val;
            }

            if($struct_item['TYPE'] == 'YN')
            {
                if (($fieldsTokenAlways or (strpos($text_to_decode, $token_is) !== false))) {
                    $object_token_is_arr = $object->token_is_arr;
                    $object_token_not_is_arr = $object->token_not_is_arr;
                    $object_token_null_is_arr = $object->token_null_is_arr;
    
                    if (!$object_token_is_arr[$fieldname]) {
                        $object_token_is_arr[$fieldname] = 'YES';
                    }
                    if (!$object_token_not_is_arr[$fieldname]) {
                        $object_token_not_is_arr[$fieldname] = 'NO';
                    }
                    if (!$object_token_null_is_arr[$fieldname]) {
                        $object_token_null_is_arr[$fieldname] = 'NOT YET';
                    }
    
                    $field_val = $object->getVal($fieldname);
                    if ($field_val == 'Y') {
                        $token_is_val = $object->translateOperator(
                            $object_token_is_arr[$fieldname],
                            $lang
                        );
                    } elseif ($field_val == 'N') {
                        $token_is_val = $object->translateOperator(
                            $object_token_not_is_arr[$fieldname],
                            $lang
                        );
                    } else {
                        $token_is_val = $object->translateOperator(
                            $object_token_null_is_arr[$fieldname],
                            $lang
                        );
                    }
    
                    $token_arr[$token_is] = $token_is_val;
                }
    
                if ($fieldsTokenAlways or (strpos($text_to_decode, $token_is_en) !== false)) {
                    $object_token_is_en_arr = $object->token_is_en_arr;
                    $object_token_not_is_en_arr = $object->token_not_is_en_arr;
                    $object_token_null_is_en_arr = $object->token_null_is_en_arr;
    
                    if (!$object_token_is_en_arr[$fieldname]) {
                        $object_token_is_en_arr[$fieldname] = 'required'; // YES
                    }
                    if (!$object_token_not_is_en_arr[$fieldname]) {
                        $object_token_not_is_en_arr[$fieldname] = ''; // NO
                    }
                    if (!$object_token_null_is_en_arr[$fieldname]) {
                        $object_token_null_is_en_arr[$fieldname] = ''; // NOT-YET
                    }
    
                    $field_val = $object->getVal($fieldname);
                    if ($field_val == 'Y') {
                        $token_is_en_val = $object_token_is_en_arr[$fieldname];
                    } elseif ($field_val == 'N') {
                        $token_is_en_val = $object_token_not_is_en_arr[$fieldname];
                    } else {
                        $token_is_en_val = $object_token_null_is_en_arr[$fieldname];
                    }
    
                    $token_arr[$token_is_en] = $token_is_en_val;
                }
            }
            

            if ($fieldsTokenAlways or (strpos($text_to_decode, $token_data) !== false)) {
                // if($fieldname=="prices_buttons") AfwRunHelper::safeDie("this->tokens = ".var_export($object->tokens,true));
                $struct_item['IN_TEMPLATE'] = true;
                $token_arr[$token_data] = $object->showAttribute(
                    $fieldname,
                    $struct_item
                );
                // if($fieldname=="prices_buttons") AfwRunHelper::safeDie("token value of token $token_data = ".var_export($token_arr[$token_data],true));
            }

            if ($fieldsTokenAlways or (strpos($text_to_decode, $token_value) !== false)) {
                $token_arr[$token_value] = $object->getVal($fieldname);
            }

            if ($struct_item['CATEGORY'] == 'ITEMS' and
                ($fieldsTokenAlways or strpos($text_to_decode, $token_data_no_icons) !== false)
            ) {
                $struct_item['ICONS'] = false;
                $token_arr[$token_data_no_icons] = $object->showAttribute(
                    $fieldname,
                    $struct_item
                );
                //die("token_arr[$token_data_no_icons] = this->showAttribute($fieldname, struct_item) with struct_item = ".var_export($struct_item,true)." = ".var_export($token_arr[$token_data_no_icons],true));
            }

            if ((strpos($text_to_decode, $token_showme) !== false)) {
                $token_arr[$token_showme] = '';
                $objToShowIt = $object->het($fieldname);
                if ($objToShowIt) {
                    $token_arr[$token_showme] = $objToShowIt->showMe('', $lang);
                }
            }

            if ($fieldsTokenAlways or (strpos($text_to_decode, $token_label) !== false)) {
                $trad_col = $trad_erase[$fieldname];
                if (!$trad_col) {
                    $trad_col = $object->getAttributeLabel($fieldname, $lang);
                }

                $token_arr[$token_label] = $trad_col;
            }

            if ($struct_item['TYPE'] == 'DATE' and
                ($fieldsTokenAlways or (strpos($text_to_decode, $token_full_date) !== false))
            ) {
                $token_arr[$token_full_date] = $object->fullHijriDate($fieldname);
            }

            if (
                $struct_item['TYPE'] == 'DATE' and
                ($fieldsTokenAlways or (strpos($text_to_decode, $token_medium_date) !== false))
            ) {
                $token_arr[$token_medium_date] = $object->mediumHijriDate(
                    $fieldname
                );
            }

            if ($struct_item['TO_TRANSLATE'] and
                ($fieldsTokenAlways or (strpos($text_to_decode, $token_to_translate) !== false))
            ) {
                $token_arr[$token_to_translate] = $object->translateValue(
                    $fieldname
                );
            }
        }
        if($toLower)
        {
            foreach($token_arr as $token => $token_value)
            {
                unset($token_arr[$token]);
                $token_arr[strtolower($token)] = $token_value;
            }
        }
        

        return $token_arr;
    }


    public static final function isRetrieveCol($object,
        $attribute,
        $mode = 'display',
        $lang = 'ar',
        $all = false,
        $desc = null,
        $force_retrieve_cols = null
    ) {

        $attributeIsToDisplayForMe = $attributeIsToDisplayForAll = AfwPrevilegeHelper::keyIsToDisplayForUser($object,
            $attribute,
            null
        );

        if (!$attributeIsToDisplayForMe) {
            $objme = AfwSession::getUserConnected();
            $attributeIsToDisplayForMe = AfwPrevilegeHelper::keyIsToDisplayForUser($object,
                $attribute,
                $objme
            );
        }

        if (!$attributeIsToDisplayForMe) {
            return false;
        }
        if (!$lang) throw new AfwRuntimeException("lang param is required for isRetrieveCol method");
        $RETRIEVE_LANG = 'RETRIEVE-' . strtoupper($lang);

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        if(!$force_retrieve_cols) $force_retrieve_cols = $object->force_retrieve_cols;

        $is_force_retrieve =
            ($force_retrieve_cols and
                is_array($force_retrieve_cols) and
                in_array($attribute, $force_retrieve_cols));

        $is_general_retrieve =
            (isset($desc['RETRIEVE']) and $desc['RETRIEVE'] or
                isset($desc[$RETRIEVE_LANG]) and $desc[$RETRIEVE_LANG]);

        $retForMode = self::isRetrieveColForMode($object,
            $attribute,
            $mode,
            $lang,
            $all,
            $desc
        );
        // if($is_general_retrieve) die("attribute=$attribute, retForMode=$retForMode, mode=$mode, is_force_retrieve=$is_force_retrieve");
        // if($attribute == "ongoing_requests_count") die("attribute=$attribute, retForMode=$retForMode, mode=$mode, is_force_retrieve=$is_force_retrieve, is_general_retrieve=$is_general_retrieve, this->force_retrieve_cols=".var_export($object->force_retrieve_cols,true));

        // rafik : @todo need more explanation
        $generalRetrieveModeAllowed =
            ($retForMode == 'W' and
                (($mode == 'display' or !$mode) and $is_general_retrieve));
        $return =
            ($retForMode == 'Y' or
                $generalRetrieveModeAllowed or
                $is_force_retrieve);

        /*
         if((static::$TABLE=="practice_vote") and ($attribute=="id"))
         {
        $message = "return = $return";
        $message .= "<br>desc[RETRIEVE] = ".$desc["RETRIEVE"];
        $message .= "<br>desc[$RETRIEVE_LANG] = $desc[$RETRIEVE_LANG]";
        $message .= "<br>retForMode = $retForMode";
        $message .= "<br>is_general_retrieve = $is_general_retrieve";
        throw new AfwRuntimeException("isRetrieveCol : debugg : $message");
         }
         */

        return $return;
    }


    // return Y : yes,
    //    N: no,
    //    W: undefined
    public static final function isRetrieveColForMode($object, 
        $attribute,
        $mode,
        $lang = 'ar',
        $all = false,
        $desc = null
    ) {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        if ($desc['ALL-RETRIEVE']) {
            return true;
        }
        $mode_up = strtoupper($mode);

        // @doc : to make the id or any other attribute is shown for qsearch with view TECH_FIELDS, just put "TECH_FIELDS-RETRIEVE" => true in structure of attribute
        //
        //if($mode_up=="TECH_FIELDS") echo("mode TECH_FIELDS : $attribute desc = ".var_export($desc,true)."<br><br><br>");
        if (
            $all and
            $desc['SHOW'] and
            !$desc['NO-RETRIEVE'] and
            strtoupper($desc['FGROUP']) == $mode_up
        ) {
            return true;
        }

        $retrieve_att = "$mode_up-RETRIEVE";
        if ($retrieve_att == 'DISPLAY-RETRIEVE') {
            $retrieve_att = 'RETRIEVE';
        }
        if ($retrieve_att == 'SEARCH-RETRIEVE') {
            $retrieve_att = 'RETRIEVE';
        }
        if ($retrieve_att == '-RETRIEVE') {
            $retrieve_att = 'RETRIEVE';
        }
        $retrieve_lang = "$retrieve_att-" . strtoupper($lang);

        if (!isset($desc[$retrieve_att]) and !isset($desc[$retrieve_lang])) {
            return 'W';
        }
        if (!$desc[$retrieve_att] and !$desc[$retrieve_lang]) {
            return 'N';
        }
        return 'Y';
    }


    public static final function getQsearchCols($object)
    {
        $tableau = [];

        $FIELDS_ALL = $object->getAllAttributes();

        foreach ($FIELDS_ALL as $attribute) {
            if (AfwPrevilegeHelper::isQSearchCol($object, $attribute)) {
                $attribute_to_exclude = false;
                if (!$attribute_to_exclude) {
                    $tableau[] = $attribute;
                }
            }
        }
        return $tableau;
    }


    public static final function isImportantField($object, $fieldname, $desc)
    {
        if (($desc['IMPORTANT'] == "HIGH") or ($desc['IMPORTANT'] == "NORMAL")) return true;
        if ((!$desc['IMPORTANT']) or ($desc['IMPORTANT'] == "IN") or ($desc['IMPORTANT'] == "MEDIUM")) {
            $uk_arr = $object->UNIQUE_KEY ? $object->UNIQUE_KEY : [];
            return ($desc['TYPE'] == 'PK' or $desc['PILLAR'] or $desc['POLE'] or in_array($fieldname, $uk_arr));
        }
        return false;
    }

    public static final function getAfwImportantFields($object)
    {
        $object_db_structure = $object::getDbStructure(
            $return_type = 'structure',
            $attribute = 'all'
        );

        $data = [];

        foreach ($object_db_structure as $fieldname => $struct_item) {
            if (self::isImportantField($object, $fieldname, $struct_item)) {
                $data[] = $fieldname;
            }
        }

        return $data;
    }

    // returns array key value containing list of important fields of
    // this object
    public static final function importants($object)
    {
        $ifields = $object->getAfwImportantFields();
        $result = [];
        foreach ($ifields as $ifield) {
            $result[$ifield] = $object->getVal($ifield);
        }

        return $result;
    }

    public static final function getAllTextSearchableCols($object)
    {
        return AfwFrameworkHelper::getAllAttributesInMode(
            $object,
            'text-searchable',
            $step = 'all',
            $typeArr = ['ALL' => true],
            $submode = '',
            $for_this_instance = true,
            $translate = false,
            $translate_to_lang = 'ar',
            $implode_char = '',
            $elekh_nb_cols = 9999,
            $alsoAdminFields = false,
            $alsoTechFields = false,
            $alsoNAFields = true,
            $max_elekh_nb_chars = 9999,
            $alsoVirtualFields = true
        );
    }

    public static final function getTextSearchableCols($object)
    {
        return AfwFrameworkHelper::getAllAttributesInMode($object, 'text-searchable');
    }


    public static final function isTextSearchableCol($object, $attribute, $desc = '')
    {
        //$objme = AfwSession::getUserConnected();

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        // INTERNAL-QSEARCH means we search inside Text Searchable Cols inside the FK object
        return ($desc['TYPE'] == 'TEXT' or
            self::isInternalSearchableCol($object, $attribute, $desc)) and
            !$desc['TEXT-SEARCHABLE-SEPARATED'] and
            AfwPrevilegeHelper::isSearchCol($object, $attribute, $desc);
    }


    public static final function isInternalSearchableCol($object, $attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
        return $desc['TYPE'] == 'FK' and $desc['INTERNAL_QSEARCH'];
    }

    public static final function isSFilterCol($object, $attribute, $desc = '')
    {
        // $objme = AfwSession::getUserConnected();
        if(!$object->attributeIsApplicable($attribute)) return false;
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
        $can_sfilter = $desc['SFILTER'];
        $is_sfilterable =
            ($can_sfilter and
                ($desc['TYPE'] == 'PK' or
                    $desc['TYPE'] == 'FK' or
                    $desc['TYPE'] == 'ENUM' or
                    $desc['TYPE'] == 'YN' or
                    $desc['TYPE'] == 'DATE'
                ));
        // if($attribute=="academic_program_id") die("attribute $attribute is_searchable=$is_searchable, can_qsearch=$can_qsearch, is_qsearchable=$is_qsearchable, desc=".var_export($desc,true));
        $return = $is_sfilterable;
        return $return;
    }

    public static final function isQSearchCol($object, $attribute, $desc = '')
    {
        // $objme = AfwSession::getUserConnected();
        if(!$object->attributeIsApplicable($attribute)) return false;
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }
        $is_searchable = AfwPrevilegeHelper::isSearchCol($object, $attribute, $desc);
        $can_qsearch =
            ($desc['QSEARCH'] or
                !isset($desc['QSEARCH']) and $desc['SEARCH-BY-ONE']);
        $is_qsearchable =
            ($can_qsearch and
                (($desc['TYPE'] == 'PK' or
                    $desc['TYPE'] == 'FK' or
                    $desc['TYPE'] == 'ENUM' or
                    $desc['TYPE'] == 'YN' or
                    $desc['TYPE'] == 'DATE' // or $desc['TYPE'] == 'TEXT' => strange it make all TEXT fields SEARCHABLE-SEPARATED
                )
                    or
                    $desc['TEXT-SEARCHABLE-SEPARATED']));
        // if($attribute=="academic_program_id") die("attribute $attribute is_searchable=$is_searchable, can_qsearch=$can_qsearch, is_qsearchable=$is_qsearchable, desc=".var_export($desc,true));
        $return = ($is_searchable and $is_qsearchable);

        return $return;
    }


    public static final function isSearchCol($object, $attribute, $desc = '')
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();


        $SEARCH_LANG = 'SEARCH-' . strtoupper($lang);

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        $is_searchable =
            ($desc['TYPE'] == 'PK' or
                $desc['SEARCH'] or
                $desc['SEARCH-BY-ONE'] or
                $desc[$SEARCH_LANG] or
                $attribute == $object->fld_ACTIVE() and ($objme = AfwSession::getUserConnected()) and $objme->isAdmin());

        //@todo : rafik implemeter cas d'un shortcut et le parent du shortcut est un PART-JOIN
        //    "SHORTCUT-PART-JOIN" est un attribue temporaire n'as aucun sens sauf activer le QSearch pour un shortcut
        //    pour mes print screen pour ecriture des specifications
        $can_be_searched_technically =
            ($desc['CATEGORY'] == '' or
                $desc['FIELD-FORMULA'] or
                $desc['SHORTCUT'] and $desc['SHORTCUT-PART-JOIN']);

        $attributeIsToDisplayForMe = $attributeIsToDisplayForAll = AfwPrevilegeHelper::keyIsToDisplayForUser($object,
            $attribute,
            null
        );

        if (!$attributeIsToDisplayForMe) {
            if (!$objme) $objme = AfwSession::getUserConnected();
            $attributeIsToDisplayForMe = AfwPrevilegeHelper::keyIsToDisplayForUser($object,
                $attribute,
                $objme
            );
        }



        $return =
            ($attributeIsToDisplayForMe and
                $can_be_searched_technically and
                $is_searchable);
        //die("$attribute : return=$return = $attributeIsToDisplayForMe and $can_be_searched_technically and $is_searchable ".var_export($desc,true));
        return $return;
    }


    public static final function isToShowCol($object, $attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        return !isset($desc['SHOW']) and $desc['EDIT'] or $desc['SHOW'];
    }

    public static final function getToShowCols($object)
    {
        $tableau = [];

        $all_FIELDS = $object->getAllAttributes();

        foreach ($all_FIELDS as $attribute) {
            if (self::isToShowCol($object, $attribute)) {
                $tableau[] = $attribute;
            }
        }
        return $tableau;
    }


    public static final function isMiniBoxCol($object, $attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($object, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $attribute);
        }

        return (!isset($desc['MINIBOX']) and $desc['RETRIEVE'] and !$desc['MINIBOX-PREVENT']) or $desc['MINIBOX'];
    }

    /**
     * @param AFWObject $object
     */

    public static final function getMiniBoxCols($object, $only_applicable=true)
    {
        $tableau = [];

        $FIELDS_ALL = $object->getAllAttributes();

        foreach ($FIELDS_ALL as $attribute) {
            if (AfwPrevilegeHelper::isMiniBoxCol($object, $attribute) and
                ((!$only_applicable) or ($object->attributeIsApplicable($attribute)))) {
                $tableau[] = $attribute;
            }
        }
        if (count($tableau) == 0) die("no MiniBoxCols => FIELDS_ALL = " . var_export($FIELDS_ALL, true));
        // if (self::$TABLE == "school") die(self::$TABLE . " => FIELDS_ALL = " . var_export($FIELDS_ALL, true));
        return $tableau;
    }
    
}