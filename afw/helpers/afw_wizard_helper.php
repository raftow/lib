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
            throw new RuntimeException('AfwWizardHelper methods need the afwObject to be setted');
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
            throw new RuntimeException('AfwWizardHelper methods need the afwObject to be setted');
        }

        if (!$structure) {
            $structure = AfwStructureHelper::getStructureOf($this->afwObject,$attribute);
        } else {
            $structure = AfwStructureHelper::repareMyStructure($this->afwObject, $structure, $attribute);
        }
        if (!isset($structure['ICONS']) or $structure['ICONS']) {
            if ($icon == 'EDIT' or $icon == 'DELETE') {
                if ($structure['EDIT_DELETE_IF_WRITEABLE']) {
                    list($writeable, $reason) = $this->afwObject->attributeIsWriteableBy(
                        $attribute,
                        null,
                        $structure
                    );
                    return $writeable ? 1 : 0;
                }
            }

            // rafik : if the minibox for example is called inside tpl
            if ($structure['IN_TEMPLATE']) {
                if (isset($structure["${icon}_ICON_IN_TEMPLATE"])) {
                    return $structure["${icon}_ICON_IN_TEMPLATE"];
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
}