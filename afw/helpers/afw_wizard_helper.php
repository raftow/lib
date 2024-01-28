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

    final public function standardEnabledIcon(
        $attribute,
        $icon,
        $structure = null
    ) {
        if(!$this->afwObject)
        {
            throw new AfwRuntimeException('AfwWizardHelper methods need the afwObject to be setted');
        }

        if (!$structure) {
            $structure = AfwStructureHelper::getStructureOf($this->afwObject,$attribute);
        } else {
            $structure = AfwStructureHelper::repareMyStructure($this->afwObject, $structure, $attribute);
        }
        if (!isset($structure['ICONS']) or $structure['ICONS']) {
            if ($icon == 'EDIT' or $icon == 'DELETE') {
                if ($structure['EDIT_DELETE_IF_WRITEABLE']) {
                    list($writeable, $reason) = AfwStructureHelper::attributeIsWriteableBy($this->afwObject,
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
}