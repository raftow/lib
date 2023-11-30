<?php
class AfwFormulaHelper extends AFWRoot
{
    public static final function isTechnicalCalculatedAttribute($attribute)
    {
        if($attribute=='tech_notes') return true;
        if($attribute=='is_ok') return true;
        if($attribute=='pctFinished') return true;
        return false;
    }

    public static final function calcTechnicalAttribute($attribute, $obj,$lang="ar")
    {
        $return = "";
        switch ($attribute) {
            case 'tech_notes':
                if ($obj->debugg_tech_notes) {
                    $return = implode("<br>\n", $obj->debugg_tech_notes);
                }
                if (!$return) {
                    $return = 'nothing';
                }
                //die("tech_notes return = $return");
                break;

            case 'is_ok':
                if (
                    $obj->getId() > 0 and
                    AfwSession::hasOption('CHECK_ERRORS')
                ) {
                    $obj_errors = $obj->getDataErrors();
                    $obj_errors_txt = implode(",\n", $obj_errors);
                    if (count($obj_errors) > 0) {
                        $img = 'error';
                    } else {
                        $img = 'is_ok';
                    }

                    $return = "<img src='../lib/images/$img.png' data-toggle='tooltip' data-placement='top' title='$obj_errors_txt'  width='20' heigth='20'>";
                } else {
                    $option = $obj->translateMessage('CHECK_ERRORS',$lang);
                    $message =
                        $obj->translateMessage('SHOULD-ACTIVATE-THE-OPTION',$lang) .' : ' .$option;

                    $return = "<img src='../lib/images/tooltip.png' data-toggle='tooltip' data-placement='top' title='$message'  width='20' heigth='20'>";
                }
                break;
            case 'pctFinished':
                $return = $obj->getPercentEdited();
                break;
        }

        return $return;
    }


    public static final function calcFormulaFromItems($extraction_field, $list_item_col, $list_item_type, $obj, $extract_only_ok_items = true, $lang="ar")
    {
            $list_items_attr = $obj->get($extraction_field);
            if (!$list_items_attr) {
                $list_items_arr = [];
            }

            foreach ($list_items_attr as $list_item) 
            {
                if ($list_item->getVal($list_item_col) > 0) {
                    $list_item_target_obj = $list_item->het($list_item_col);
                    if($list_item_target_obj)
                    {
                        if ((!$extract_only_ok_items) or $list_item_target_obj->isOk()) 
                        {
                            if ($list_item_type == 'ID_MFK') {
                                if (
                                    $list_item_target_obj and
                                    $list_item_target_obj->getId() > 0
                                ) {
                                    $list_items_arr[] = $list_item_target_obj->getId();
                                }
                            } 
                            elseif (($list_item_type == 'OBJ_LIST') or(!$list_item_type)) 
                            {
                                if (
                                    $list_item_target_obj and
                                    $list_item_target_obj->getId() > 0
                                ) {
                                    $list_items_arr[
                                        $list_item_target_obj->getId()
                                    ] = $list_item_target_obj;
                                }
                            } 
                            else 
                            {
                                $list_items_arr[$list_item_target_obj->getId()] = 'LIST_ITEM_TYPE ' . $list_item_type . ' not implemented';
                            }
                        }
                    }
                } 
                else 
                {
                }
            }

            if ($list_item_type == 'ID_MFK') {
                $return = ',' . implode(',', $list_items_arr) . ',';
            } else {
                $return = $list_items_arr;
            }

            return $return;
    }

    public static final function calcPhpFormula($formula, $obj, $lang="ar")
    {
        list(
            $formulaFunction,
            $formulaAttribute1,
            $formulaAttribute2,
            $formulaAttribute3,
        ) = explode('.', $formula);
        //$obj_formula_log[$attribute] = "use of php formula ($formulaFunction,$formulaAttribute1,$formulaAttribute2,$formulaAttribute3)";

        if ($formulaFunction == 'pic_info') {
            $title = $obj->getVal($formulaAttribute1);
            $body = $obj->getVal($formulaAttribute2);
            $picture = $obj->getVal($formulaAttribute3);

            $html = "<div class='divParagraphCard'>
                        <div class=\"divParagraph\">
                            <h3 class=\"divParagraphTitleHeader\">
                                $title
                            </h3>
                            <span class=\"divParagraphText\">
                            $body 
                            </span>
                        </div>
                        <div class=\"divParagraphTitlePicture\">
                            $picture
                        </div>
        </div>";

            // $obj->throwError($html);
            return $html;
        } elseif ($formulaFunction == '3cols') {
            $tit1 = $obj->translate($formulaAttribute1, $lang);
            $tit2 = $obj->translate($formulaAttribute2, $lang);
            $tit3 = $obj->translate($formulaAttribute3, $lang);

            $val1 = $obj->displayAttribute(
                $formulaAttribute1,
                true,
                $lang
            );
            $val2 = $obj->displayAttribute(
                $formulaAttribute2,
                true,
                $lang
            );
            $val3 = $obj->displayAttribute(
                $formulaAttribute3,
                true,
                $lang
            );

            $html = "<div class='hzm_attribute hzm_wd4'>
    <div class='cols3'>
    <div class='cols3_title hzm_wd4 fright'><b>$tit1</b> :</div> <div class='cols3_value hzm_wd4 fright'>$val1</div>
    <div class='cols3_title hzm_wd4 fright'><b>$tit2</b> :</div> <div class='cols3_value hzm_wd4 fright'>$val2</div>
    <div class='cols3_title hzm_wd4 fright'><b>$tit3</b> :</div> <div class='cols3_value hzm_wd4 fright'>$val3</div>
    </div>
    </div>";

            // $obj->throwError($html);
            return $html;
        } 
        elseif ($formulaFunction == 'link_desc') 
        {
            $link = $obj->getVal($formulaAttribute1);
            $title = $obj->getVal($formulaAttribute2);
            $body = $obj->getVal($formulaAttribute3);

            $html = "<div class='hzm_attribute hzm_wd4'>
    <a class='title_link' href='$link'><b>$title</b></a><br>
    <span class='title_help'>$link</span><br>
    <span class='urgent_info'>$body</span>
    </div>";

            // $obj->throwError($html);
            return $html;
        } 
        elseif ($formulaFunction == 'paragraph') 
        {
            $title = $obj->getVal($formulaAttribute1);
            $body = $obj->getVal($formulaAttribute2);

            return "<div class='hzm_attribute hzm_wd4'>
    <label class='hzm_label'>$title</label>
    <div class='hzm_data'>$body</div>
    </div>";
        } 
        elseif ($formulaFunction == 'count') 
        {
            $listObj = $obj->get($formulaAttribute1);
            return count($listObj);
        } 
        elseif ($formulaFunction == 'list_extract') 
        {
            $listObj = $obj->get($formulaAttribute1);
            $listObjResult = [];
            foreach ($listObj as $itemObj) {
                $itemResultObj = $itemObj->het($formulaAttribute2);
                if ($itemResultObj) {
                    if ($formulaAttribute3) {
                        $itemResultObjFinal = $itemResultObj->het(
                            $formulaAttribute2
                        );
                    } else {
                        $itemResultObjFinal = $itemResultObj;
                    }

                    if ($itemResultObjFinal) {
                        $listObjResult[$itemResultObjFinal->getId()] = $itemResultObjFinal;
                    }
                }
            }

            return $listObjResult;
        } 
        elseif ($formulaFunction == 'method') 
        {
            $formulaMethod = $formulaAttribute3;
            if ($formulaMethod) {
                if ($formulaAttribute1 and $formulaAttribute2) {
                    $formulaAttribute1Value = $obj->getVal(
                        $formulaAttribute1
                    );
                    $formulaAttribute2Value = $obj->getVal(
                        $formulaAttribute2
                    );
                    return $obj->$formulaMethod(
                        $formulaAttribute1Value,
                        $formulaAttribute2Value
                    );
                } elseif ($formulaAttribute1) {
                    $formulaAttribute1Value = $obj->getVal(
                        $formulaAttribute1
                    );
                    return $obj->$formulaMethod($formulaAttribute1Value);
                } else {
                    return $obj->$formulaMethod();
                }
            } 
            else 
            {
                $obj->simpleError("formulaMethod in formula $formula not defined (3rd param)");
            }
        }
    }


    public static final function executeFormulaAttribute($object, $attribute, $struct=null, $lang="ar", $what="value")
    {
        if (!$struct) {
            $struct = AfwStructureHelper::getStructureOf($object,$attribute);
        } else {
            $struct = AfwStructureHelper::repareMyStructure($object, $struct, $attribute);
        }

        $formula_cache_attribute = "debugg_formula_cache_$attribute";

        $object_formula_log = [];
        $object_formula_log[$attribute] = "calculate Formula($attribute)";

        if ($struct['PHP_FORMULA']) {
            $formulaPHP = $struct['PHP_FORMULA'];
            $object_formula_log[$attribute] = "calcPhpFormula($formulaPHP)";
            $return = AfwFormulaHelper::calcPhpFormula($formulaPHP, $object, $lang);
        } 
        elseif ($struct['FORMULA_USE_CACHE'] and $object->$formula_cache_attribute) 
        {
            $object_formula_log[$attribute] = 'use of formula cache';
            $return = $object->$formula_cache_attribute;
        } 
        elseif ($struct['FOR_HELP']) {
            $object_formula_log[$attribute] = 'FOR_HELP';
            $attribute_original = str_replace('_help', '', $attribute);
            $return = $object->getHelpFor($attribute_original, $lang);
            if (!$return) {
                $return = "no help defined for $attribute_original lang=$lang";
            }
        } 
        elseif ($struct['EXTRACTION'] == 'FROM_ITEMS') 
        {
            $list_item_col = $struct['LIST_ITEM_COL'];
            $extraction_field = $struct['EXTRACTION_FIELD'];
            $object_formula_log[$attribute] = "EXTRACTION  FROM_ITEMS ($list_item_col, $extraction_field)";
            $extract_only_ok_items = $struct['EXTRACT_IS_OK_ONLY'];
            $list_item_type = $struct['LIST_ITEM_TYPE'];
            $return = AfwFormulaHelper::calcFormulaFromItems($extraction_field, $list_item_col, $list_item_type, $object, $extract_only_ok_items, $lang);
            
        } 
        elseif ($struct['DATE_CONVERT'] == 'NASRANI') 
        {
            $object_formula_log[$attribute] = 'DATE_CONVERT NASRANI';
            $attribute_original = $struct['ORIGINAL_ATTRIBUTE'];
            if (!$attribute_original) {
                $attribute_original = str_replace('nasrani_', '', $attribute);
            }

            $val_hijri = $object->getVal($attribute_original);
            if ($val_hijri) {
                $return = AfwDateHelper::hijriToGreg($val_hijri);
            } else {
                $return = '';
            }
            //die("($attribute_original,$attribute) => AfwDateHelper::hijriToGreg($val_hijri) = '$return'");
        }
        elseif(self::isTechnicalCalculatedAttribute($attribute))
        {
            $object_formula_log[$attribute] = 'look on technical formula';
            $return = AfwFormulaHelper::calcTechnicalAttribute($attribute, $object, $lang);
        }
        else
        {
            $access_formula = true;
            $option_key = $struct['OPTION_KEY'];
            if ($option_key) {
                if (!AfwSession::hasOption($option_key)) {
                    $access_formula = false;
                }
            }
            if ($access_formula) {
                $object_formula_log[$attribute] = "authorized getFormuleResult($attribute, $what)";
                $return = $object->getFormuleResult($attribute, $what);
                /*
                if($attribute=="school_class_id") 
                {
                    $objectdis = $object->getDisplay($lang);
                    die("rafik shoof $objectdis => getFormuleResult($attribute, $what) => [$return]");
                }*/
            } else {
                $option = $object->translateMessage($option_key, $lang);
                $message_no_access_formula = $object->translateMessage('SHOULD-ACTIVATE-THE-OPTION',$lang) . ' : ' . $option;
                $object_formula_log[$attribute] = "not authorized getFormuleResult($attribute, $what='value') " . $message_no_access_formula;

                $return = "<img src='../lib/images/fields.png' data-toggle='tooltip' data-placement='top' title='$message_no_access_formula'  width='20' heigth='20'>";
            }

            //if($attribute == "real_book_id") die("boub-2020-04-26 : get forumla  for $attribute return = [$return] formula_log : ".$object_formula_log[$attribute]);
        }

        $object->debugg_formula_log = $object_formula_log;

        if ($struct['FORMULA_USE_CACHE'] and $return) {
            $object->$formula_cache_attribute = $return;
        }

        return $return;
    }
}
