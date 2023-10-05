<?php
class AfwLoadHelper extends AFWRoot
{

    public static final function loadManyFollowingStructureAndValue(
        $answerTableObj,
        $desc,
        $val,
        $obj,
        $dropdown = false,
        $optim = true
    ) 
    {
        if ($desc['LOAD_ALL']) {
            $sql = 'LOAD_ALL :: this->loadLookupData()';
            $liste_rep = $answerTableObj->loadLookupData($desc['ORDERBY']);
        } elseif ($desc['AT_METHOD']) {
            $at_method = $desc['AT_METHOD'];
            $sql = "obj->$at_method()";
            $liste_rep = $obj->$at_method();
        } else {
            if (!$desc['NO_KEEP_VAL']) {
                $val_to_keep = $val;
            } else {
                $val_to_keep = '';
            }

            if ($desc['WHERE']) {
                $answerTableObj->where($desc['WHERE'], $val_to_keep);
                $nowhere = ' : where = ' . $desc['WHERE'];
            } else {
                $nowhere = ' : nowhere';
            }

            $answerTableObj->select_VH($val_to_keep, $dropdown);

            $sql =
                $answerTableObj->getSQLMany('', '', $desc['ORDERBY'], $optim) .
                $nowhere .
                " : optim=$optim";
            $answerTableObj->debugg_sql_for_loadmany = $sql;
            $liste_rep = $answerTableObj->loadMany('', $desc['ORDERBY'], $optim);
            //die("liste_rep=".var_export($liste_rep,true));
            unset($answerTableObj->debugg_sql_for_loadmany);
        }

        return [$sql, $liste_rep];
    }

    public static function getRetrieveDataFromObjectList(
        $liste_obj,
        $header,
        $lang = 'ar',
        $newline = "\n<br>"
    ) 
    {
        $objme = AfwSession::getUserConnected();

        $data = [];
        $isAvail = [];

        foreach ($liste_obj as $id => $objItem) {
            if (is_object($objItem) and AfwUmsPagHelper::userCanDoOperationOnObject($objItem,$objme,'display')) 
            {
                $objIsActive = $objItem->isActive();
                $tuple = [];
                $tuple['display_object'] = $objItem->getDisplay($lang);
                if (count($header) != 0) {
                    foreach ($header as $col => $titre) {
                        $desc = AfwStructureHelper::getStructureOf($objItem,$col);
                        if (!$objItem->attributeIsApplicable($col)) {
                            list(
                                $icon,
                                $textReason,
                                $wd,
                                $hg,
                            ) = $objItem->whyAttributeIsNotApplicable($col);
                            if (!$wd) {
                                $wd = 20;
                            }
                            if (!$hg) {
                                $hg = 20;
                            }
                            $tuple[
                                $col
                            ] = "<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='$textReason'  width='$wd' heigth='$hg'>";
                        } elseif (
                            $objItem->dataAttributeCanBeDisplayedForUser(
                                $col,
                                $objme,
                                'DISPLAY',
                                $desc
                            )
                        ) {
                            if (!$col) {
                                $objItem->simpleError(
                                    'header columnds erroned, column empty : ' .
                                        var_export($header, true)
                                );
                            }
                            if ($desc == 'AAA') {
                                $tuple['description'] = $objItem->__toString();
                            } else {
                                switch ($desc['TYPE']) {
                                    case 'FK':
                                        $hetMethod = "het$col";
                                        if ($desc['CATEGORY'] === 'ITEMS') {
                                            $objs = $objItem->get(
                                                $col,
                                                'object',
                                                '',
                                                false
                                            );
                                        } 
                                        elseif ($desc['CATEGORY'] == 'FORMULA') 
                                        {
                                            $objs = $objItem->calc($col,true,"object");
                                            // die("for categ = formula, obj = $objItem => calc($col,true, object) = ".var_export($objs));
                                        }
                                        else {
                                            $objs = $objItem->$hetMethod();
                                        }
                                        if ($objs and is_object($objs)) {
                                            $tuple[
                                                $col
                                            ] = $objs->getRetrieveDisplay(
                                                $lang
                                            );
                                        } elseif ($objs and is_array($objs)) {
                                            if (count($objs)) {
                                                $str = '';
                                                foreach ($objs as $instance) {
                                                    if (
                                                        $instance and
                                                        is_object($instance)
                                                    ) {
                                                        $str .=
                                                            $instance->getShortDisplay(
                                                                $lang
                                                            ) . $newline;
                                                    }
                                                }
                                                $tuple[$col] = $str;
                                            }
                                        } else {
                                            if ($objs) {
                                                $tuple[$col] =
                                                    'strange FK object(s) export for colum : ' . $col . ' => ' .
                                                    var_export($objs, true);
                                                throw new RuntimeException($tuple[$col]);
                                            }
                                        }
                                        break;
                                    case 'MFK':
                                        $objs = $objItem->get(
                                            $col,
                                            'object',
                                            '',
                                            false
                                        );

                                        if (count($objs)) {
                                            //echo "$col : <br>";
                                            //die("rafik 14380523 - ".var_export($objs,true));
                                            $str = '';
                                            foreach ($objs as $instance) {
                                                if (
                                                    $instance and
                                                    is_object($instance)
                                                ) {
                                                    $str .=
                                                        $instance->getShortDisplay(
                                                            $lang
                                                        ) . $newline;
                                                }
                                            }
                                            $tuple[$col] = $str;
                                        }
                                        break;
                                    case 'ANSWER':
                                        $tuple[$col] = $objItem->decode($col);
                                        break;
                                    case 'YN':
                                        $tuple[
                                            $col
                                        ] = $objItem->showYNValueForAttribute(
                                            strtoupper($objItem->decode($col)),
                                            $col,
                                            $lang
                                        );
                                        break;
                                    default:
                                        if ($desc['RETRIEVE-VALUE']) {
                                            $tuple[$col] = $objItem->getVal($col);
                                        } else {
                                            $tuple[$col] = $objItem->decode($col);
                                        }
                                        break;
                                }
                            }
                        } else {
                            $textReason = $objItem->translateMessage(
                                'DATA_PROTECTED',
                                $lang
                            );
                            $tuple[$col] = "<img src='../lib/images/lock.png' data-toggle='tooltip' data-placement='top' title='$textReason'  width='20' heigth='20'>";
                        }
                    }
                }
                $data[$id] = $tuple;
                $isAvail[$id] = $objIsActive;
                // $count_liste_obj++;
            }
        }

        return [$data, $isAvail];
    }
}