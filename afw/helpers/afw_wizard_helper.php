<?php

class AfwWizardHelper extends AFWRoot 
{
    private $afwObject;
    public function __construct($afwObject)
    {
        $this->afwObject = $afwObject;
    }

    public function getMyCLStep()
    {
        if(!$this->afwObject)
        {
            throw new AfwRuntimeException('AfwWizardHelper methods need the afwObject to be setted');
        }
        if ($this->afwObject->getMyTheme() == 'default') {
            return 'wizardv1_li';
        } else {
            $clObj = $this->afwObject->getMyClass();
            return 'cl_' .
                substr($clObj, 0, 3) .
                substr($clObj, strlen($clObj) - 3, 3);
        }
    }

    public function getWizardStepsClass()
    {
        if ($this->afwObject->getMyTheme() == 'default') {
            return 'steps_wizardv1 clearfix';
        } else {
            return 'hzmSteps';
        }
    }

    public function getStepLiContentHtml($step_num, $step_name)
    {
        if ($this->afwObject->getMyTheme() == 'default') {
            return "<span class=\"number\">${step_num}.</span> ${step_name}";
        } else {
            return "<div class='step_num'>${step_num}&nbsp;</div><div class='step_name'>${step_name}</div>";
        }
    }

    public function getWizardClass()
    {
        if ($this->afwObject->getMyTheme() == 'default') {
            return 'wizardv1';
        } else {
            return 'panel-body';
        }
    }

    /**
     * @param AFWObject $object
     */

    final public static function standardEnabledIcon(
        $object,
        $attribute,
        $icon,
        $structure = null
    ) {
        if(!$object)
        {
            throw new AfwRuntimeException('AfwWizardHelper methods need the afwObject to be setted');
        }

        if (!$structure) {
            $structure = AfwStructureHelper::getStructureOf($object,$attribute);
        } else {
            $structure = AfwStructureHelper::repareMyStructure($object, $structure, $attribute);
        }
        if (!isset($structure['ICONS']) or $structure['ICONS']) {
            if ($icon == 'EDIT' or $icon == 'DELETE') {
                if ($structure['EDIT_DELETE_IF_WRITEABLE']) {
                    list($writeable, $reason) = AfwStructureHelper::attributeIsWriteableBy($object,
                        $attribute,
                        null,
                        $structure
                    );
                    return $writeable ? 1 : 0;
                }
            }

            // rafik : if the minibox for example is called inside tpl
            if ($structure['IN_TEMPLATE']) {
                if (isset($structure[$icon."_ICON_IN_TEMPLATE"])) {
                    return $structure[$icon."_ICON_IN_TEMPLATE"];
                }
            }

            if (!isset($structure["$icon-ICON"])) {
                return (($icon == 'EDIT' or $icon == 'VIEW' or $icon == 'DELETE') ? 1 : 0);
            }
            if ($structure["$icon-ICON"]) {
                return ($structure["$icon-ICON"]>1) ? $structure["$icon-ICON"] : 1; // "icon=$icon struct[$attribute]=".var_export($structure,true);
            }
            //@@@todo if($structure["SHOW-ID"]) $first_item->showId = true;
        }

        return 0;
    }


    /*
        afwListObjInstersection compare 2 hzm list (indexed with ID of object) of afw objects each list with same type of object
        it will execute a compare method with each object of each list and take the couples of objects that return same result
        this result is compared with == operator so it is recommended that th result is numeric or string or boolean
     */

    public static function afwListObjInstersection(
        $listObj1,
        $listObj2,
        $compareMethod = 'getDisplay',
        $keepEmpty = false
    ) {
        $arrResult = [];

        $arr1Result = [];
        foreach ($listObj1 as $id1 => $itemObj1) {
            $arr1Result[$id1] = $itemObj1->$compareMethod();
        }

        $arr2Result = [];
        foreach ($listObj2 as $id2 => $itemObj2) {
            $arr2Result[$id2] = $itemObj2->$compareMethod();
        }

        // first($listObj2);

        foreach ($arr1Result as $id1 => $res1) {
            foreach ($arr2Result as $id2 => $res2) {
                if ($res1 == $res2) {
                    if ($keepEmpty or $res1) {
                        $arrResult[] = [
                            'item1' => $listObj1[$id1],
                            'item2' => $listObj2[$id2],
                        ];
                    }
                }
            }
        }
        return $arrResult;
    }


    public static function listToArray(
        $listObj,
        $attribute,
        $keepEmpty = false,
        $cond = 'isActive'
    ) {
        $arrResult = [];
        foreach ($listObj as $itemObj) {
            if ($itemObj->$cond()) {
                $val = $itemObj->getVal($attribute);
                if ($keepEmpty or $val) {
                    $arrResult[] = $val;
                }
            }
        }

        return $arrResult;
    }

    /**
     * @param AFWObject $object
     */

    public static final function getOtherLinksArrayStandard($object, $mode, $genereLog = false, $step = "all")
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        $objme = AfwSession::getUserConnected();
        // $me = $objme ? $objme->id : 0;
        // $genereLog = true;

        $otherLinksArray = [];
        $my_id = $object->getId();
        $object_otherLinkLog = [];
        if ($mode == 'display' or $mode == 'edit') {
            $FIELDS_ALL = $object->getAllMyDbStructure();
            $log = "mode=$mode, FIELDS_ALL=" . var_export($FIELDS_ALL, true);
            if ($genereLog) {
                $object_otherLinkLog[] = $log;
            }

            foreach ($FIELDS_ALL as $attribute => $struct) {
                $link_label = null;
                // rafik optimization : 23/12/2023
                // Momken V3.0 the getAllMyDbStructure method now return the structure repared 
                // no need below to re-call getStructureOf 
                // $struct = AfwStructureHelper::getStructureOf($object, $attribute);

                // $isAdminField = $object->isAdminField($attribute);
                // $isTechField = $object->isTechField($attribute);

                //if($attribute=="mainwork_start_paragraph_num") die("strange case, step = $step struct = ".var_export($struct,true));
                //if($attribute=="previous_paragraph_id") die("strange case, step = $step struct = ".var_export($struct,true)." struct['STEPS'][$step] = ".$struct['STEPS'][$step]);
                $relation_is_super = ($struct['RELATION'] == 'OneToMany' or $struct['RELATION'] == 'OneToOneB' or $struct['RELATION'] == 'OneToOneU' or $struct['RELATION-SUPER'] == 'IMPORTANT');
                if (
                    $struct['TYPE'] == 'FK' and
                    $relation_is_super and
                    ($step == "all" or $struct['STEP'] == "all" or $struct['STEP'] == $step or $struct['STEPS'][$step])
                ) {
                    // if($attribute=="previous_paragraph_id") die("case of OneToXX or RELATION-SUPER is IMPORTANT, struct of $attribute = ".var_export($struct,true));
                    $log = "$attribute attribute is FK RELATION is OneToXX or RELATION-SUPER is IMPORTANT: " . $struct['RELATION'];
                    if ($struct['RELATION'] == 'OneToMany') {
                        $parent_struct = AfwStructureHelper::getParentStruct($object, $attribute, $struct);
                        $parent_step = $parent_struct['STEP'];
                        if ($parent_step) {
                            $log =
                                "$attribute attribute has parent step : " .
                                $parent_step;
                            if ($genereLog) {
                                $object_otherLinkLog[] = $log;
                            }
                            list($displ2, $link_url2) = $object->displayAttribute(
                                $attribute,
                                false,
                                $lang,
                                "&currstep=$parent_step"
                            );
                            $displ2 = trim($displ2);
                            if (!$displ2) {
                                $displ2 = "case 1 : ".get_class($object)."->displayAttribute($attribute,false, $lang, &currstep=$parent_step)";
                            } else {
                                $displ2 .= "<!-- case 1: ".get_class($object)."->displayAttribute($attribute,false, $lang, &currstep=$parent_step) -->";
                            }

                            if (!$struct['NO-RETURNTO']) {
                                $struct['OTM-RETURNTO'] = true;
                            }
                        } else {
                            $log = "$attribute attribute has no parent step ";
                            if ($genereLog) {
                                $object_otherLinkLog[] = $log;
                            }
                            list($displ2, $link_url2) = $object->displayAttribute(
                                $attribute,
                                false,
                                $lang
                            );
                            $displ2 = trim($displ2);
                            if (!$displ2) {
                                $displ2 = "case 2 : ".get_class($object)."->displayAttribute($attribute,false, $lang)";
                            } else {
                                $displ2 .= "<!-- case 2 : ".get_class($object)."->displayAttribute($attribute,false, $lang) -->";
                            }
                        }

                        if (!isset($struct['OTM-TITLE'])) {
                            $struct['OTM-TITLE'] = true;
                        }

                        if (!isset($struct['OTM-NO-LABEL'])) {
                            if (!isset($struct['OTM-REMOVE-AUTO-LABEL'])) {
                                $struct['OTM-NO-LABEL'] = false;
                            } elseif ($struct['OTM-LABEL']) {
                                $link_label = $struct['OTM-LABEL'];
                            } else {
                                $struct['OTM-NO-LABEL'] = true;
                            }
                        }

                        if (!$struct['OTM-NO-LABEL'] and !$link_label) {
                            $link_label = $object->getAttributeLabel(
                                $attribute,
                                $lang,
                                $short = true
                            );
                        }
                    } else {
                        if (!isset($struct['OTM-TITLE'])) {
                            $struct['OTM-TITLE'] = true;
                        }
                        list($displ2, $link_url2) = $object->displayAttribute($attribute,false,$lang);
                        $displ2 = trim($displ2);
                        if (!$displ2) {
                            $displ2 = "case 3 : this->displayAttribute($attribute,false, $lang) return nothing";
                        } else {
                            $displ2 .= "<!-- case 3 : this->displayAttribute($attribute,false, $lang) returned ($displ2, $link_url2) -->";
                        }
                        if (!isset($struct['OTM-NO-LABEL'])) {
                            if (!isset($struct['OTM-REMOVE-AUTO-LABEL'])) {
                                $struct['OTM-NO-LABEL'] = false;
                            } elseif ($struct['OTM-LABEL']) {
                                $link_label = $struct['OTM-LABEL'];
                            } else {
                                $struct['OTM-NO-LABEL'] = true;
                            }
                        }

                        if (!$struct['OTM-NO-LABEL'] and !$link_label) {
                            $link_label = $object->getAttributeLabel(
                                $attribute,
                                $lang,
                                $short = true
                            );
                        }
                    }

                    $displ2 = trim($displ2);

                    if ($displ2 and $link_url2) {
                        // if((!$struct["OTM-NO-LABEL"]) and (!$link_label)) $link_label = $object->getAttributeLabel($attribute, $lang, $short=true);
                        unset($link);
                        $link = [];
                        $title = '';
                        if ($struct['OTM-SHOW']) {
                            $title .= AfwLanguageHelper::translateKeyword("DISPLAY").' ';
                        }
                        elseif ($struct['OTM-CARD']) {
                            $title .= AfwLanguageHelper::translateKeyword("PROFILE").' ';
                        }
                        elseif ($struct['OTM-FILE']) {
                            $title .= AfwLanguageHelper::translateKeyword("FILE").' ';
                        }
                        elseif ($struct['OTM-RETURNTO']) {
                            $title .= AfwLanguageHelper::translateKeyword("TO").' ';
                        }
                        else{
                            $title .= AfwLanguageHelper::translateKeyword("DATA-OF").' ';
                        }

                        if (!$struct['OTM-NO-LABEL']) {
                            $title .= $link_label . ' : ';
                        }
                        // else $title .= "debugg_rafik : ".var_export($struct,true);
                        if ($struct['OTM-TITLE']) {
                            $title .= "<br>".$displ2;
                        }
                        $title = trim($title);

                        $title_detailed = $title . ' : ' . $displ2;
                        $link['URL'] = $link_url2;
                        $link['TITLE'] = $title;
                        if ($struct["STEP"] or $struct["STEPS"]) {
                            // rafik 28/9/2022
                            // if the field cause of this OTM relation that has generated this other link standard
                            // is in a defined step the other link standard also should be related to this step
                            $link['STEP'] = $struct["STEP"];
                            $link['STEPS'] = $struct["STEPS"];
                        }
                        // no public opened like this in new UMS
                        // $link["PUBLIC"] = true;
                        $otherLinksArray[] = $link;
                        // if($attribute=="mainwork_start_paragraph_num") die("otherLinksArray = ".var_export($otherLinksArray,true));
                    } else {
                        $log = "for $attribute attribute display-title or link is missed ($displ2,$link_url2)";
                        // if($attribute=="mainwork_start_paragraph_num") die($log);
                        if ($genereLog) {
                            $object_otherLinkLog[] = $log;
                        }
                    }
                } elseif (
                    $struct['TYPE'] == 'MFK' and
                    $struct['LINK_TO_MFK_ITEMS']
                ) {
                    list($displ_arr, $link_url_arr) = $object->displayAttribute(
                        $attribute,
                        false,
                        $lang
                    );
                    foreach ($displ_arr as $displ_id => $displ2) {
                        unset($link);
                        $link = [];
                        $title = '';
                        if ($struct['OTM-SHOW']) {
                            $title .= AfwLanguageHelper::translateKeyword("DISPLAY").' ';
                        } else {
                            $title .=
                                $object->tf($struct['LINK_TO_MFK_ITEMS']) . ' ';
                        }
                        $title .= $displ2;
                        $title = trim($title);

                        $title_detailed = $title . ' : ' . $displ2;
                        $link['URL'] = $link_url_arr[$displ_id];
                        $link['TITLE'] = $title;

                        // no public opened like this in new UMS
                        // $link["PUBLIC"] = true;
                        $otherLinksArray[] = $link;
                    }
                } else {
                    if ($genereLog) {

                        $object_otherLinkLog[] = "Attribute $attribute has not been selected as OneToXXX relation because ";
                        $object_otherLinkLog[] = "TYPE = " . $struct['TYPE'];
                        $object_otherLinkLog[] = "RELATION = " . $struct['RELATION'];
                        $object_otherLinkLog[] = "STEP = " . $struct['STEP'];
                        $object_otherLinkLog[] = "step = " . $step;
                    }
                    //if($attribute=="courses_template_id") AfwStructureHelper::dd(var_export($object_otherLinkLog,true));
                }
            }
        } else {
            $log = "mode is not edit or display : mode=$mode";
            if ($genereLog) {
                $object_otherLinkLog[] = $log;
            }
        }
        if ($genereLog) {
            /*
            foreach ($object_otherLinkLog as $object_otherLinkLogItem) {
                AfwSession::contextLog($object_otherLinkLogItem, 'otherLink');
            }
            */
            /*
            if(($mode == 'display' or $mode == 'edit') and ($object->getMyClass()=="TrainingUnit"))
            {
                die("otherLinksArray = ".var_export($otherLinksArray,true)." this_otherLinkLog : ".implode("<br>",$object_otherLinkLog));
            }
            */
        }

        return $otherLinksArray;
    }

    public final static function getFieldGroupDefaultInfos($fgroup)
    {
        $css_fg = 'none';
        if (AfwStringHelper::stringEndsWith($fgroup, 'List')) {
            $css_fg = 'pct_100';
        }
        /* bizarre below
        if (
            AfwStringHelper::stringStartsWith($fgroup, 'Group') or
            AfwStringHelper::stringStartsWith($fgroup, 'Group50')
        ) {
            $css_fg = 'pct_50';
        }

        if (AfwStringHelper::stringStartsWith($fgroup, 'Group66')) {
            $css_fg = 'pct_66';
        }

        if (AfwStringHelper::stringStartsWith($fgroup, 'Group33')) {
            $css_fg = 'pct_33';
        }

        if (AfwStringHelper::stringStartsWith($fgroup, 'Group25')) {
            $css_fg = 'pct_25';
        }*/

        return ['name' => $fgroup, 'css' => $css_fg];
    }

    public static function classIsDisplayedInEditMode($className)
    {
        global $display_in_edit_mode, $display_in_display_mode;
        return $display_in_edit_mode[$className] or
            $display_in_edit_mode['*'] and
            !$display_in_display_mode[$className];
    }

    public final static function getFinishButtonLabelDefault($object,
        $lang,
        $nextStep,
        $form_readonly = 'RO'
    ) {
        $className = $object->getMyClass();
        if (self::classIsDisplayedInEditMode($className) and (!$object->after_save_edit)) {
            if ($form_readonly != 'RO') {
                return $object->translate('SAVE', $lang, true);
            } else {
                $ret = $object->getReadOnlyFormFinishButtonLabel();
                if ($ret) return $object->translate($ret, $lang, true);
                return '';
            }
        }

        if ($object->editByStep and $nextStep > 0 and $object->isDraft()) {
            return $object->translate('COMPLETE_LATER' . $form_readonly,$lang,true);
        }
        //$this->editNbSteps

        return $object->translate('FINISH' . $form_readonly, $lang, true);
    }


}