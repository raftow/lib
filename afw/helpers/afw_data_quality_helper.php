<?php
class AfwDataQualityHelper
{
    // final because Should never been overwritten
    public static final function getDataErrors($object,
        $lang = 'ar',
        $show_val = true,
        $recheck = false,
        $step = 'all',
        $ignore_fields_arr = null,
        $attribute = null, // if $attribute is set means we ignore all attributes except $attribute
        $stop_on_first_error = false,
        $start_step = null,
        $end_step = null
    ) {
        // global $errors_check_count;
        //, $errors_ check_count _max;
        //if($errors_ check_count>$errors_ check_count _max) throw new AfwRuntimeException("too mauch errors found by getDataErrors (nb=$errors _check_count)");
        // if ($attribute) $errors_check_count[$attribute]++;
        /*
        if((get_class($object)=="Applicant") and ($step==2) and !$start_step and !$end_step)
        {
            $ignore_fields_arr_export = var_export($ignore_fields_arr,true);        
            throw new AfwRuntimeException("what you do here ? entering getDataErrors(
                $lang = lang,
                $show_val = show_val,
                $recheck = recheck,
                $step = step,
                $ignore_fields_arr_export = ignore_fields_arr,
                $attribute = attribute,
                $stop_on_first_error = stop_on_first_error,
                $start_step = start_step,
                $end_step = end_step
            )");
        }
         */

        //rafik this line below is commented since 17/5/2022 because very strange why not saved objects can not be checked if contains errors before save
        //if($object->getId()<=0) return array();
        /*
        if((get_class($object)=="Applicant") and ($step==2) and ($attribute=="passeport_num")) // and !$start_step and !$end_step
        {            
            die(get_class($object)." : dbg before enter getCommonDataErrors : this->arr_erros = ".var_export($object->arr_erros,true));
        }*/
        if (!isset($object->arr_erros[$step]) or $recheck) {
            // we should pass $erroned_attribute = null to get all step attributes for cache and after we we will
            // return only for the attribute requested
            $erroned_attribute = null; 
            $common_e_arr   =   self::getCommonDataErrors($object, $lang, $show_val, $step, $erroned_attribute, $stop_on_first_error, $start_step, $end_step);
            if((get_class($object)=="Applicant") and ($step==2) and ($attribute=="passeport_num")) // and !$start_step and !$end_step
            {            
                die(get_class($object)." : dbg getCommonDataErrors(lang=$lang, show_val=$show_val, step=$step, attribute=$attribute, stop_on_first_error=$stop_on_first_error, start_step=$start_step, end_step=$end_step) => ".var_export($common_e_arr,true));
            }

            if (!$attribute or $object->stepContainAttribute($step, $attribute)) {
                $specific_e_arr = $object->getMySpecificDataErrors($lang, $show_val, $step, $erroned_attribute, $stop_on_first_error, $start_step, $end_step);
            } else {
                $specific_e_arr = [];
            }
            $object->arr_erros[$step] = array_merge(
                $common_e_arr,
                $specific_e_arr
            );

            // if($step==2) die("debugg rafik this->arr_erros = ".var_export($object->arr_erros,true));
        }

        $err_arr = $object->arr_erros[$step];

        

        if($attribute)
        {
            // return only for the attribute requested
            foreach ($err_arr as $field_name) {
                if($field_name != $attribute) unset($err_arr[$field_name]);
            }
        }
        else
        {
            foreach ($ignore_fields_arr as $ignore_field) {
                unset($err_arr[$ignore_field]);
            }
        }

        
        // debugg
        /*
        if((get_class($object)=="Applicant") and ($step==2) and !$start_step and !$end_step)
        {
            $ignore_fields_arr_export = var_export($ignore_fields_arr,true);
            $err_arr_export = var_export($err_arr,true);
            throw new AfwRuntimeException(get_class($object)." : what you do here ? exiting getDataErrors(
                $lang = lang,
                $show_val = show_val,
                $recheck = recheck,
                $step = step,
                $ignore_fields_arr_export = ignore_fields_arr,
                $attribute = attribute,
                $stop_on_first_error = stop_on_first_error,
                $start_step = start_step,
                $end_step = end_step
            ) => $err_arr_export");
        } */
        

        return $err_arr;
    }


    private static function structureCheckable($desc)
    {
        return !$desc['NO-ERROR-CHECK'] and
            (!$desc['CATEGORY'] or $desc['ERROR-CHECK']);
    }

    // Action :
    // Check common known errors
    // 1. Mandatory fields values
    // 2. Format of formatted fields
    // 3. Constraints on values for Constrainted fields
    // 4. Errors eventually in 'pillar-part' fields
    // return array of errors
    private static final function getCommonDataErrors($object,    
        $lang = 'ar',
        $show_val = true,
        $step = 'all',
        $erroned_attribute = null,
        $stop_on_first_error = false,
        $start_step = null,
        $end_step = null
    ) {
        global $errors_check_count;

        $cm_errors = [];

        if (!$erroned_attribute) {
            $object_db_structure = $object::getDbStructure(
                $return_type = 'structure',
                $attrib = 'all',
                $step,
                $start_step,
                $end_step
            );
            // if((get_class($object)=="Applicant") and ($step==2)) throw new AfwRuntimeException(get_class($object).":: dbg : this_db_structure for step=$step and start_step=$start_step, end_step=$end_step => ".var_export($object_db_structure,true));
            // die("showErrorsAsSessionWarnings::getDbStructure($return_type, $attrib, $step, $start_step, $end_step) = ".var_export($object_db_structure,true));
            // if($step!="all") die("static::getDbStructure($return_type, $attrib, $step) = ".var_export($object_db_structure,true));
        } else {
            $attrib_structure = $object::getDbStructure(
                $return_type = 'structure',
                $attrib = $erroned_attribute
            );
            $object_db_structure[$attrib] = $attrib_structure;
            //die("static::getDbStructure($return_type, $attrib) = ".var_export($object_db_structure,true));
        }

        // if((get_class($object)=="Applicant") and (!$erroned_attribute) and ($step=="all") and !$object_db_structure["passeport_num"]) die("dbg 2025/02 this_db_structure = ".var_export($object_db_structure,true));


        foreach ($object_db_structure as $attribute => $desc) {
            // if(!is_array($desc)) die(get_class($object).":: dbg : desc of $attribute = ".var_export($desc,true)." this_db_structure = ".var_export($object_db_structure,true));
            $error_attribute = $desc['ERROR_ATTRIBUTE'];
            if (!$error_attribute) {
                $error_attribute = $attribute;
            }

            $attribute_is_required = $object->attributeIsRequired(
                $attribute,
                $desc
            );

            $attr_sup_categ = $desc['SUPER_CATEGORY'];
            $attr_categ = $desc['CATEGORY'];
            $attr_scateg = $desc['SUB-CATEGORY'];

            if ($attr_categ == 'ITEMS') {
                $desc['TYPE'] = 'MFK';
            }
            if ($attr_scateg == 'ITEMS') {
                $desc['TYPE'] = 'MFK';
            }
            if ($attr_sup_categ == 'ITEMS') {
                $desc['TYPE'] = 'MFK';
            }
            /*
            if($attribute=="passeport_num" and $step=='all') 
            {
                die("debugg-2025-02-10 : Test if attribute $attribute step $step contain attribute $attribute");
            }*/

            if ($object->stepContainAttribute($step, $attribute, $desc)) {
                /*
                if($attribute=="passeport_num" and $step=='all') 
                {
                    die("debugg-2025-02-11 : attribute $attribute step $step contain attribute $attribute");
                }*/
                /*
                if(($step==1) and ($attribute=="first_name_ar"))
                {
                    throw new AfwRuntimeException("step==$step : desc = ".var_export($desc,true));
                }*/
                // DEPENDENCY : for formula fields that are checkable (default not), we can define dependency field
                // so that no error check is performed until DEPENDENCY field has no errors
                if (
                    self::structureCheckable($desc) and
                    !$cm_errors[$desc['DEPENDENCY']]
                ) 
                {
                    /*
                    if($attribute=="concernedGoalList")
                    {
                        throw new AfwRuntimeException("getCommonDataErrors for $attribute is reached at step $step");
                    }
                    */

                    if (!isset($cm_errors[$error_attribute])) {
                        $cm_errors[$error_attribute] = '';
                    }
                    //if($attribute=="tome") throw new AfwRuntimeException("kifech w step = $step w desc = ".var_export($desc,true));
                    $val_attr = $object->getVal($attribute);

                    //if($attribute=="monitoring") throw new AfwRuntimeException("rafik : this->getVal($attribute)=$val_attr", array("FIELDS_UPDATED"=>true, "AFIELD_ VALUE"=>true));
                    if ($show_val) {
                        $showed_val = " = $val_attr";
                    } else {
                        $showed_val = '';
                    }

                    if ($desc['TYPE'] == 'TEXT' or $desc['TYPE'] == 'MTEXT') {
                        $showed_val = '';
                    }

                    //if((static::$TABLE=="practice") and ($attribute=="explain")) throw new AfwRuntimeException("kifech val_attr($attribute) = [$val_attr] w step = $step w desc = ".var_export($desc,true));
                    if($desc['TYPE']=="TEXT")
                    {
                        $desc['CAN_ZERO'] = true;
                    }
                    // 1. required fields values
                    if ($desc['TYPE'] != 'MFK' and $attribute_is_required) {
                        if ($desc['TYPE'] == 'YN' and $val_attr == 'W' and (!$desc['W-IS-VALUE'])) {
                            $val_attr = '';
                        }

                        if (
                            !$val_attr and
                            ((!$desc['CAN_ZERO']) or ($val_attr === ''))
                        ) {
                            $spec_field_manda_token = "$attribute.FIELD_MANDATORY";
                            $spec_field_manda_token_message = $object->translate($spec_field_manda_token, $lang);
                            if ($spec_field_manda_token_message == $spec_field_manda_token) {
                                $tabName = $object->getMyTable();
                                /*$log_canzero = "*************** val_attr = $val_attr *******************
                                                *************** canzero=".$desc['CAN_ZERO'].
                                                "*************** TYPE=".$desc['TYPE'];*/
                                $log_canzero = "";
                                $cm_errors[$error_attribute] .= $object->translateOperator('FIELD MANDATORY', $lang) .
                                    ' : ' .
                                    $object->translate($attribute, $lang).$log_canzero;

                                // below code we can not do because the tooltip can t support html
                                // if(AfwSession::config('MODE_DEVELOPMENT', false)) $cm_errors[$error_attribute] .= "<!-- $tabName.$attribute -->";    
                            } else {
                                $cm_errors[$error_attribute] .=
                                    $spec_field_manda_token_message . ", \n";
                            }

                            if ($stop_on_first_error) break;
                        }
                        //if((static::$TABLE=="practice") and ($attribute=="explain")) throw new AfwRuntimeException("$attribute : kifech val_attr=[$val_attr] w step = $step w cm_errors = ".var_export($cm_errors,true));
                    }

                    // 2. Format of formatted fields
                    if (AfwFormatHelper::isFormatted($desc)) 
                    {
                        list(
                            $correctFormat,
                            $correctFormatMess,
                        ) = AfwFormatHelper::isCorrectFormat($val_attr, $desc);
                        
                        
                        /*if($attribute=="passeport_num" and $step=='all') 
                        {
                            die("attribute $attribute list(correctFormat=$correctFormat,correctFormatMess=$correctFormatMess,) = AfwFormatHelper::isCorrectFormat(value of attribute=[$val_attr], desc of attribute = ".var_export($desc, true).")");
                        }
                        */

                        if (!$correctFormat) {
                            if (!$desc['RESUME_TEXT_ERROR']) {
                                $cm_errors[$error_attribute] .=
                                    $object->translateOperator(
                                        'FIELD VALUE',
                                        $lang
                                    ) .
                                    ' ' .
                                    $object->translate($attribute, $lang) .
                                    $showed_val .
                                    ' : ';
                            }
                            $cm_errors[$error_attribute] .=
                                $object->translateOperator(
                                    $correctFormatMess,
                                    $lang
                                ) . ", \n";

                            if ($stop_on_first_error) break;
                        }

                        // if($attribute=="passeport_num") die("attribute $attribute, errors = ". $cm_errors[$error_attribute]);
                    }

                    // 3. Constraints on values for Constrainted fields
                    if ($desc['CONSTRAINTS']) {
                        // and ($val_attr != "")
                        $halted_constraint = $object->dataFollowConstraints(
                            $val_attr,
                            $desc['CONSTRAINTS']
                        );
                        if ($halted_constraint) {
                            $cm_errors[$error_attribute] .=
                                $object->translateOperator(
                                    'WRONG DATA FOR FIELD',
                                    $lang
                                ) .
                                ' : ' .
                                $object->translate($attribute, $lang) .
                                $showed_val .
                                ", \n <!--" .
                                var_export($halted_constraint, true) . " -->";
                            if ($stop_on_first_error) break;
                        }
                    }
                    // if($attribute=="passeport_num") die("attribute $attribute, errors is ". $cm_errors[$error_attribute]);
                    // 4. Errors eventually in pillar or 'pillar-part' fields
                    //   pole or     is same as pillar but only if applicable
                    //   pillar-part   is same as pillar if attribute Is Required
                    if (
                        $desc['PILLAR'] or
                        $desc['POLE'] and
                        $object->attributeIsApplicable($attribute) or
                        $desc['PILLAR-PART'] and
                        $object->attributeIsRequired($attribute)
                    ) {
                        // only for FK or MFK Fields
                        if ($desc['TYPE'] == 'FK') {
                            if (intval($val_attr) > 0) {
                                $objVal = $object->get(
                                    $attribute,
                                    'object',
                                    '',
                                    false
                                );
                                if (
                                    !$objVal or
                                    !is_object($objVal) or
                                    $objVal->getId() != $val_attr or
                                    $attribute_is_required and !$objVal->getId()
                                ) {
                                    $cm_errors[$error_attribute] .=
                                        $object->translateOperator(
                                            'DELETED OR WRONG MANDATORY OBJECT',
                                            $lang
                                        ) .
                                        ' : ' .
                                        $object->translate($attribute, $lang) .
                                        $showed_val .
                                        ", \n";

                                    if ($stop_on_first_error) break;
                                }

                                if (is_object($objVal)) {
                                    $err_obj_arr = AfwDataQualityHelper::getDataErrors($objVal, 
                                        $lang,
                                        $show_val
                                    );
                                    $objVal_disp = $objVal->getShortDisplay();
                                    $err_count = count($err_obj_arr);
                                    if ($err_count > 0) {
                                        $cm_errors[$error_attribute] .=
                                            $object->translateOperator(
                                                'PILLAR OBJECT',
                                                $lang
                                            ) .
                                            ' ' .
                                            $object->translate(
                                                $attribute,
                                                $lang
                                            ) .
                                            " = $objVal_disp " .
                                            $object->translateOperator(
                                                'CONTAIN',
                                                $lang
                                            ) .
                                            " $err_count " .
                                            $object->translateOperator(
                                                'ERRORS',
                                                $lang
                                            ) .
                                            ' :';
                                        foreach ($err_obj_arr as $err_text) {
                                            $cm_errors[$error_attribute] .=
                                                $err_text . "\n";
                                        }
                                        $cm_errors[$error_attribute] .=
                                            "______________________\n";

                                        if ($stop_on_first_error) break;
                                    }
                                }
                            }
                        }

                        if ($desc['TYPE'] == 'MFK') {
                            $obj_arr = $object->get($attribute);
                            $errors_html = '';
                            $errors_max = 10;
                            $errors_i = 0;

                            foreach ($obj_arr as $obj_id => $objVal) {
                                if (
                                    is_object($objVal) and
                                    $errors_i < $errors_max
                                ) {
                                    if ($errors_check_count[$attribute] > 30) {
                                        $errors_check_count_attr = $errors_check_count[$attribute];
                                        throw new AfwRuntimeException("too mauch error checks called for attribute $attribute (nb=$errors_check_count_attr), be carefull on infinite loops");
                                    }
                                    $err_obj_arr = AfwDataQualityHelper::getDataErrors($objVal, 
                                        $lang,
                                        $show_val
                                    );
                                    $err_count = count($err_obj_arr);
                                    if (
                                        $err_count > 0 and
                                        $errors_i < $errors_max
                                    ) {
                                        $errors_html .=
                                            "\n" .
                                            'السجل : ' .
                                            $objVal->getDisplay($lang);
                                        if ($err_count > 1) {
                                            $errors_html .=
                                                ' ' .
                                                $object->translateOperator(
                                                    'CONTAIN',
                                                    $lang
                                                ) .
                                                " $err_count " .
                                                $object->translateOperator(
                                                    'ERRORS',
                                                    $lang
                                                ) .
                                                ' : ';
                                            foreach (
                                                $err_obj_arr
                                                as $err_text
                                            ) {
                                                $errors_html .=
                                                    "\n       " . $err_text;
                                                $errors_i++;
                                            }
                                        } else {
                                            $errors_html .=
                                                ' : ' .
                                                implode(' ', $err_obj_arr);
                                        }
                                    }
                                }
                            }
                            if ($errors_html) {
                                $fld_desc =
                                    $object->translateOperator(
                                        'PILLAR OBJECT',
                                        $lang
                                    ) .
                                    ' ' .
                                    $object->translate($attribute, $lang);
                                $cm_errors[$error_attribute] .=
                                    "يوجد أخطاء في $fld_desc : \n" .
                                    $errors_html .
                                    ", \n";

                                if ($stop_on_first_error) break;
                            }
                        }
                    }

                    // if($attribute=="passeport_num") die("attribute $attribute, errors is = ". $cm_errors[$error_attribute]);

                    if ($desc['TYPE'] == 'MFK') {
                        $attribute_val0 = $object->calc($attribute);
                        if (!is_array($attribute_val0)) {
                            $attribute_val = trim($attribute_val0, ',');
                        } else {
                            $attribute_val = count($attribute_val0);
                        }

                        if ($attribute_is_required and !$attribute_val) {
                            $cm_errors[$error_attribute] .=
                                $object->translateOperator(
                                    'EMPTY LIST FOR REQUIRED FIELD',
                                    $lang
                                ) .
                                ' : ' .
                                $object->translate($attribute, $lang) .
                                ", \n";

                            if ($stop_on_first_error) break;
                        }
                    }

                    if (!$cm_errors[$error_attribute]) {
                        unset($cm_errors[$error_attribute]);
                    } else {
                        $cm_errors[$error_attribute] = str_replace(
                            "\n",
                            '<br>',
                            $cm_errors[$error_attribute]
                        );
                        $cm_errors[$error_attribute] = str_replace(
                            ',',
                            '،',
                            $cm_errors[$error_attribute]
                        );
                        $cm_errors[$error_attribute] = trim(
                            $cm_errors[$error_attribute],
                            "\n"
                        );
                        $cm_errors[$error_attribute] = trim(
                            $cm_errors[$error_attribute],
                            ' '
                        );
                        $cm_errors[$error_attribute] = trim(
                            $cm_errors[$error_attribute],
                            ','
                        );
                        $cm_errors[$error_attribute] = trim(
                            $cm_errors[$error_attribute],
                            '/'
                        );
                        /*
                        $cm_errors[$attribute] = trim($cm_errors[$attribute],"\n");
                        $cm_errors[$attribute] = trim($cm_errors[$attribute]," ");
                        $cm_errors[$attribute] = trim($cm_errors[$attribute],",");
                        $cm_errors[$attribute] = trim($cm_errors[$attribute],"/");
                        
                        $cm_errors[$attribute] = mas_complete_len($cm_errors[$attribute], 36," ");
                        */
                    }

                    // if($attribute=="passeport_num") die("attribute $attribute, errors equal to ". $cm_errors[$error_attribute]);
                }
            }
        }
        /*
        if($step==1)
        {
            throw new AfwRuntimeException("step==$step : cm_errors = ".var_export($cm_errors,true));
        }


        if(count($cm_errors)>0)
        {
            throw new AfwRuntimeException("There are errors : step==$step : cm_errors = ".var_export($cm_errors,true));
        }*/

        // if($object->id==1056365305) echo ("all step $step errors = ". var_export($cm_errors, true));

        return $cm_errors;
    }


    public static final function getStepErrors($object,    
        $kstep,
        $lang = 'ar',
        $show_val = true,
        $recheck = false,
        $ignore_fields_arr = null,
        $attribute = null
    ) {
        $return = AfwDataQualityHelper::getDataErrors($object, $lang, $show_val, $recheck, $kstep, $ignore_fields_arr, $attribute, false, );
        // if($attribute=="passeport_num") die("dbg inside getStepErrors ".get_class($object)."::getDataErrors(lang=$lang, show_val=$show_val, recheck=$recheck, kstep=$kstep, ignore_fields_arr=$ignore_fields_arr, attribute=$attribute, false, ) => ".var_export($return,true));
        return $return;
    }


    public static final function getAttributeError($object, $attribute)
    {
        global $lang;
        $struct = AfwStructureHelper::getStructureOf($object, $attribute);
        $step = $struct['STEP'];
        if (!$step) {
            $step = 1;
        }

        $stepErrors_arr = AfwDataQualityHelper::getStepErrors($object, $step, $lang, true, false, [], $attribute);
        // if($attribute=="passeport_num") die("dbg :: AfwDataQualityHelper::getStepErrors(this, $step, $lang, true, false, [], $attribute) = ".var_export($stepErrors_arr,true));
        return $stepErrors_arr[$attribute];
    }

    public static final function getNbErrors($object, $step = 'all', $force = false, $ignore_fields_arr = null)
    {
        if (!isset($object->arr_erros) or $force) {
            AfwDataQualityHelper::getDataErrors($object, 'ar', true, $force, $step, $ignore_fields_arr);
        }

        return count($object->arr_erros[$step]);
    }
}