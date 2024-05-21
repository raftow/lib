<?php

// old require of afw_root 

class AfwFrameworkHelper extends AFWRoot
{

    public static function displayInEditMode($cl)
    {
        global $display_in_edit_mode, $display_in_display_mode;
        return ($display_in_edit_mode[$cl]) or ($display_in_edit_mode["*"] and (!$display_in_display_mode[$cl]));
    }


    public static final function attributeInMode($obj, $attribute, $mode, $submode = '', $for_this_instance = true)
    {
        $mode = strtolower($mode);

        if ($mode == 'qedit') {
            return AfwStructureHelper::isQuickEditableAttribute($obj, $attribute, '', $submode);
        }
        if ($mode == 'display' or $mode == 'show') {
            return AfwStructureHelper::isShowableAttribute($obj, $attribute, '', $submode);
        }
        if ($mode == 'edit') {
            return AfwStructureHelper::attributeIsEditable($obj,
                $attribute,
                '',
                $submode,
                $for_this_instance
            );
        }
        if ($mode == 'retrieve') {
            return $obj->isRetrieveCol($attribute, $mode);
        }

        if ($mode == 'search') {
            return $obj->isSearchCol($attribute);
        }
        if ($mode == 'minibox') {
            return $obj->isMiniBoxCol($attribute);
        }
        if ($mode == 'qsearch') {
            return $obj->isQSearchCol($attribute);
        }
        if ($mode == 'text-searchable') {
            return $obj->isTextSearchableCol($attribute);
        }

        throw new AfwRuntimeException("unknown mode : $mode may be not implemented !");
    }


    public static final function getAllAttributesInMode(
        $obj,
        $mode,
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
        $alsoNAFields = false,
        $max_elekh_nb_chars = 9999,
        $alsoVirtualFields = true
    ) {
        $tableau = [];

        $FIELDS_ALL = AfwStructureHelper::getAllRealFields($obj,true);

        $nbCols = 0;

        $lenUsed = 0;

        foreach ($FIELDS_ALL as $attribute => $struct) {
            // no need it is already repared (Momken 3.0)
            // $struct = AfwStructureHelper::getStructureOf($this, $attribute);

            $isAdminField = $obj->isAdminField($attribute);
            $isTechField = $obj->isTechField($attribute);
            $hasGoodType = ($typeArr['ALL'] or $typeArr[$struct['TYPE']]);
            if (
                ($step == 'all' or $struct['STEP'] == $step) and
                ($alsoAdminFields or !$isAdminField) and
                ($alsoTechFields or !$isTechField) and
                $hasGoodType and
                ($alsoNAFields or $obj->attributeIsApplicable($attribute)) and
                ($alsoVirtualFields or AfwStructureHelper::attributeIsReel($obj, $attribute)) and
                AfwFrameworkHelper::attributeInMode($obj, $attribute, $mode, $submode, $for_this_instance) and
                (!$implode_char or $nbCols < $elekh_nb_cols) and
                (!$implode_char or $lenUsed < $max_elekh_nb_chars)
            ) {
                $tableau[] = $attribute;
                $nbCols++;
                $lenUsed += strlen(
                    $obj->translate($attribute, $translate_to_lang)
                );
            }
        }

        $result = $tableau;

        if ($translate) {
            $result = $obj->translateCols($result, $translate_to_lang);
        }

        if ($implode_char) {
            $result = implode($implode_char, $result);
            if ($nbCols >= $elekh_nb_cols) {
                $result .=
                    $implode_char .
                    $obj->translateOperator('ETC', $translate_to_lang);
            }
        }

        return $result;
    }

    public static function qfind($obj, $words)
    {
        $parts = explode(' ', $words);
        return self::find($obj,
            $parts,
            $clwhere = '',
            $sql_operator = ' AND ',
            $return_sql_only = false,
            $all_fields_mode = 'SEARCH'
        );
    }

    public static function find($obj,
        $parts,
        $clwhere = '',
        $sql_operator = ' AND ',
        $return_sql_only = false,
        $all_fields_mode = false
    ) {
        $p = count($parts);
        if ($p == 0) {
            return null;
        }

        if ($all_fields_mode) {
            $display_field = AfwFrameworkHelper::getAllAttributesInMode(
                $obj,
                $all_fields_mode,
                $find_step = 'all',
                $find_typeArr = ['TEXT' => true],
                $find_submode = '',
                $find_for_this_instance = true,
                $find_translate = false,
                $find_translate_to_lang = 'ar',
                $find_implode_char = '',
                $find_elekh_nb_cols = 9999,
                $find_alsoAdminFields = false,
                $find_alsoTechFields = false,
                $find_alsoNAFields = false,
                $find_max_elekh_nb_chars = 9999,
                $find_alsoVirtualFields = false
            );
        } else {
            $display_field = $obj->AUTOCOMPLETE_FIELD;
        }
        if (!$display_field) {
            $display_field = trim($obj->DISPLAY_FIELD);
        }

        if (!$display_field) {
            $display_field = trim($obj->FORMULA_DISPLAY_FIELD);
        }

        if (!$display_field) {
            throw new AfwRuntimeException(
                'afw class : ' .
                    $obj->getMyClass() .
                    ' : method find does not work without one of AUTOCOMPLETE_FIELD or DISPLAY_FIELD or FORMULA_DISPLAY_FIELD attributes specified for the object'
            );
        }

        $pk_field = $obj->getPKField();
        if (!$pk_field) {
            $pk_field = 'id';
        }
        $sql_parts = '';

        if (true) {
            if (is_array($display_field)) {
                $display_fields = $display_field;
            } else {
                $display_fields = [];
                $display_fields[] = $display_field;
            }

            for ($i = 0; $i < $p; $i++) {
                $sql_cond_fld = '';

                if ($p == 1 and is_numeric($parts[0])) {
                    $term = $parts[0];
                    $sql_cond_fld .= "$pk_field = $term";
                }

                foreach ($display_fields as $display_fld) {
                    if ($sql_cond_fld) {
                        $sql_cond_fld .= ' or ';
                    }
                    $sql_cond_fld .=
                        "$display_fld like _utf8'%" . $parts[$i] . "%'";
                }

                if ($sql_parts) {
                    $sql_parts .= $sql_operator;
                }
                $sql_parts .= ' (' . $sql_cond_fld . ')';
            }
        }
        $sql_parts = '(' . $sql_parts . ')';

        $obj->select_visibilite_horizontale();
        if ($clwhere) {
            $obj->where($clwhere);
        }
        if ($sql_parts) {
            $obj->where($sql_parts);
        }
        //die("find($parts,$clwhere, $sql_operator, $return_sql_only)");
        //die("sql_parts=$sql_parts, clwhere=$clwhere : sql => ".$obj->getSQL());
        if ($return_sql_only) {
            return 'display_field=' .
                var_export($display_field, true) .
                "sql_parts=$sql_parts, clwhere=$clwhere : sql => " .
                $obj->getSQLMany();
        }
        return $obj->loadMany();
    }

    public static final function rejectHimSelfReason($object, $frameworkAction)
    {
        $main_reason = "override iAcceptAction($frameworkAction) method or override editToDisplay() method to use edit for display mode";
        if (
            $frameworkAction == 'edit' or
            $frameworkAction == 'insert' or
            $frameworkAction == 'update'
        ) {
            if (!AfwFrameworkHelper::stepIsEditable($object,'all')) {
                return "fa=$frameworkAction and all fields of all steps are not editable, override editToDisplay() method to use edit for display mode";
            }

            list($stepIsRO, $stepIsROReason) = AfwStructureHelper::stepIsReadOnly($object,'all',true);
            if ($stepIsRO) {
                return "fa=$frameworkAction and all fields of all steps are readonly : " .
                    $stepIsROReason . ", override editToDisplay() method to use edit for display mode";
            }
            if (!$object->iAcceptAction('edit')) {
                return $main_reason;
            }
        }

        return $main_reason;
    }


    public static final function acceptHimSelf($object, $frameworkAction)
    {
        if (
            $frameworkAction == 'edit' or
            $frameworkAction == 'insert' or
            $frameworkAction == 'update'
        ) {
            return ($object->editToDisplay() or 
                        ($object->iAcceptAction('edit') 
                            and self::stepIsEditable($object, 'all') and (!AfwStructureHelper::stepIsReadOnly($object,'all'))));

            if (!self::stepIsEditable($object, 'all')) {
                return false;
            }
            if (AfwStructureHelper::stepIsReadOnly($object,'all')) {
                return false;
            }
            if (!$object->iAcceptAction('edit')) {
                return false;
            }
        }

        return $object->iAcceptAction($frameworkAction);
    }

    /**
     * 
     * @param AFWObject $object 
     */

    public static final function stepIsEditable($object, $step)
    {
        $class_db_structure = $object->getMyDbStructure();
        foreach ($class_db_structure as $nom_col => $desc) 
        {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $nom_col);
            if ($desc['STEP'] == $step or $step == 'all') {
                if (AfwStructureHelper::attributeIsEditable($object, $nom_col)) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * 
     * @param AFWObject $object 
     */

    public static final function stepIsApplicable($object, $step)
    {
        $class_db_structure = $object->getMyDbStructure();
        foreach ($class_db_structure as $nom_col => $desc) 
        {
            $desc = AfwStructureHelper::repareMyStructure($object, $desc, $nom_col);
            if ($desc['STEP'] == $step) {
                if ($object->attributeIsApplicable($nom_col)) {
                    return true;
                }
            }
        }
        return false;
    }


    public static final function findNextApplicableStep($object, 
        $current_step = 0,
        $reason = ''
    ) {
        $old_current_step = $current_step;
        if (!$current_step) {
            $current_step = $object->currentStep;
        }
        $currstep = $current_step;
        $currstep++;
        while ($currstep > 0 and !AfwFrameworkHelper::stepIsApplicable($object, $currstep)) {
            $currstep++;
            if ($currstep > $object->editNbSteps) {
                $currstep = -1;
            }
        }
        // if($reason=="show btn ?") die("log of findNextEditableStep($old_current_step,$reason) current_step=$current_step, currstep=$currstep, isEd=".$this->step IsEditable($currstep));
        return $currstep;
    }


    public static final function findNextEditableStep($object,
        $current_step = 0,
        $reason = '',
        $pushError = false
    ) {
        $old_current_step = $current_step;
        if (!$current_step) {
            $current_step = $object->currentStep;
        }
        $currstep = $current_step;
        if ($object->stepCanBeLeaved($currstep, $reason, $pushError)) {
            $currstep++;
            while ($currstep > 0 and !AfwFrameworkHelper::stepIsEditable($object, $currstep)) {
                $currstep++;
                if ($currstep > $object->editNbSteps) {
                    $currstep = -1;
                }
            }
        }
        // if($reason=="show btn ?") die("log of findNextEditableStep($old_current_step,$reason) current_step=$current_step, currstep=$currstep, isEd=".$this->stepIs Editable($currstep));
        return $currstep;
    }


    public static final function findPreviousEditableStep($object,
        $current_step = 0,
        $reason = '',
        $pushError = false
    ) {
        if (!$current_step) {
            $current_step = $object->currentStep;
        }
        $currstep = $current_step;
        if ($object->stepCanBeLeaved($currstep, $reason, $pushError)) {
            $currstep--;
            while ($currstep > 0 and !AfwFrameworkHelper::stepIsEditable($object,$currstep)) {
                $currstep--;
                if ($currstep < 1) {
                    $currstep = -1;
                }
            }
        }

        return $currstep;
    }

    

    
}
