
<?php
class AfwDynamicPublicMethodHelper
{
    public static function checkMethodAllowed($methodProps, $methodName, $object)
    {
        if (is_array($methodProps)) {
            $one_is_sufficiant = $methodProps[0];
            unset($methodProps[0]);
            $checkMethods = $methodProps;
        } else {
            $one_is_sufficiant = true;
            $checkMethods = array($methodProps);
        }

        $method_allowed = (!$one_is_sufficiant);
        if (!$method_allowed)
            $method_not_allowed_reason = "$methodName need at least one condition succeeded";
        else
            $method_not_allowed_reason = "$methodName need all conditions succeed";

        foreach ($checkMethods as $checkMethod) {
            if ($one_is_sufficiant) {
                if ($object->$checkMethod()) {
                    $method_allowed = true;
                    $method_not_allowed_reason .= " $checkMethod succeeded";
                    break;
                } else {
                    $method_not_allowed_reason .= " $checkMethod failed";
                }
            } else {
                if (!$object->$checkMethod()) {
                    $method_allowed = false;
                    $method_not_allowed_reason .= " $checkMethod failed";
                    break;
                } else {
                    $method_not_allowed_reason .= " $checkMethod succeeded";
                }
            }
        }

        if ($method_allowed)
            $method_not_allowed_reason .= ' so method allowed';
        else
            $method_not_allowed_reason .= ' so method not allowed';

        return [$method_allowed, $method_not_allowed_reason];
    }

    public static function splitMethodToMethodItems($pbms, $publicDynamicMethodProps, $methodName0, $object, $log, $adminOnly = false, $public = true)
    {
        $itemsMethod = $publicDynamicMethodProps['items'];
        $itemsList = $object->executeItemsMethod($itemsMethod);
        return self::splitMethodWithItems($pbms, $publicDynamicMethodProps, $methodName0, $object, $log, $itemsList, $adminOnly, $public);
    }

    public static function splitMethodWithItems($pbms, $publicDynamicMethodProps, $methodName0, $object, $log, $itemsList, $adminOnly = true, $public = false, $step = "all", $defined_color = 'yellow')
    {
        foreach ($itemsList as $itemId => $itemPbm) {
            if ($itemId != 'none')
                $methodName = $methodName0 . $itemId;
            else
                $methodName = $methodName0;
            if ($itemPbm and is_array($itemPbm)) {
                $itemTitleAr = $itemPbm['ar'];
                $itemTitleEn = $itemPbm['en'];
            } elseif ($itemPbm and is_object($itemPbm)) {
                $itemTitleAr = $itemPbm->getDisplay('ar');
                $itemTitleEn = $itemPbm->getDisplay('en');
            } else {
                $itemTitleAr = '???';
                $itemTitleEn = '???';
            }

            $methodTitleAr = $object->getMethodTitle($methodName0, 'ar');
            $methodTitleAr = str_replace('[item]', $itemTitleAr, $methodTitleAr);
            $methodTitleAr = AfwReplacement::trans_replace($methodTitleAr, "workflow", "ar");
            $methodTooltipAr = $object->getMethodTooltip($methodName0, 'ar');
            $methodTitleEn = $object->getMethodTitle($methodName0, 'en');
            $methodTitleEn = AfwReplacement::trans_replace($methodTitleEn, "workflow", "en");
            $methodTitleEn = str_replace('[item]', $itemTitleEn, $methodTitleEn);
            $methodTooltipEn = $object->getMethodTooltip($methodName0, 'ar');
            $methodColor = $publicDynamicMethodProps['color'];
            $can_if = $publicDynamicMethodProps['can_if'];
            $roles = $publicDynamicMethodProps['roles'];
            $published = $publicDynamicMethodProps['published'];
            $titlelength = $publicDynamicMethodProps['title-length'];


            if (!$methodColor) {
                if (AfwStringHelper::stringStartsWith($defined_color, '::')) {
                    if (is_object($itemPbm)) {
                        $methodToGetColor = substr($defined_color, 2);
                        $methodColor = $itemPbm->$methodToGetColor();

                        // die("$methodColor = $itemPbm -> $methodToGetColor()");
                    } //else die('itemPbm = ' . var_export($itemPbm, true) . ' defined_color=' . $defined_color);
                }
            }
            if (!$methodColor) $methodColor = $defined_color;

            if ($methodColor == 'random') {
                $rd =  $itemPbm->id % count(AfwFormatHelper::$COLORS);
                $methodColor = AfwFormatHelper::$COLORS[$rd];
            }
            $methodConfirmationNeeded = $publicDynamicMethodProps["confirmation_needed"];
            $methodConfirmationWarning = $object->decodeTpl($publicDynamicMethodProps['confirmation_warning']);
            $methodConfirmationWarningEn = $object->decodeTpl(AfwLanguageHelper::tt($publicDynamicMethodProps['confirmation_warning']), 'en');
            $methodConfirmationQuestion = $object->decodeTpl($publicDynamicMethodProps['confirmation_question']);
            $methodConfirmationQuestionEn = $object->decodeTpl(AfwLanguageHelper::tt($publicDynamicMethodProps['confirmation_question']), 'en');

            $pbmDynItem = array(
                'METHOD' => $methodName,
                'TOOLTIP_AR' => $methodTooltipAr,
                'TOOLTIP_EN' => $methodTooltipEn,
                'LOG' => $log,
                'COLOR' => $methodColor,
                'LABEL_AR' => $methodTitleAr,
                'LABEL_EN' => $methodTitleEn,
                'ADMIN-ONLY' => $adminOnly,
                'ONLY-ADMIN' => $adminOnly,
                'PUBLIC' => $public,
                'STEP' => $step,
                'TITLE-LENGTH' => $titlelength,

                'CAN_IF' => $can_if,
                'ROLES' => $roles,
                'PUBLISHED' => $published,

                'BF-ID' => '',
                'confirmation_needed' => $methodConfirmationNeeded,
                'CONFIRMATION_WARNING' => array('ar' => $methodConfirmationWarning, 'en' => $methodConfirmationWarningEn),
                'CONFIRMATION_QUESTION' => array('ar' => $methodConfirmationQuestion, 'en' => $methodConfirmationQuestionEn),
            );

            if (!$pbmDynItem['ROLES']) unset($pbmDynItem['ROLES']);
            if (!$pbmDynItem['CAN_IF']) unset($pbmDynItem['CAN_IF']);
            if (!$pbmDynItem['PUBLISHED']) unset($pbmDynItem['PUBLISHED']);


            $pbms[substr(md5($methodName . $itemId), 1, 5)] = $pbmDynItem;
        }

        return $pbms;
    }
}
