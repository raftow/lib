<?php
class AfwFormatHelper
{
    public static final function isCorrectFormat($val_attr, $desc)
    {
        if (!$val_attr) {
            return [true, ''];
        } // in this cas MANDATORY property that will reject

        if ($desc['FORMAT'] == 'ARABIC-TEXT') {
            if (!AfwStringHelper::is_arabic($val_attr)) {
                return [false, 'FORMAT-ARABIC-TEXT'];
            }
        }

        if ($desc['FORMAT'] == 'EMAIL') {
            if (!self::isCorrectEmailAddress($val_attr)) {
                return [false, 'FORMAT-EMAIL'];
            }
        }

        if ($desc['FORMAT'] == 'SA-MOBILE') {
            if (!self::isCorrectMobileNum($val_attr)) {
                return [false, 'FORMAT-SA-MOBILE'];
            }
        }

        if ($desc['FORMAT'] == 'SA-TRADENUM') {
            if (!self::isCorrectTradeNumber($val_attr)) {
                return [false, 'FORMAT-SA-TRADENUM'];
            }
        }

        if ($desc['FORMAT'] == 'SA-NATIONAL-UNIFIED-NUMBER') {
            if (!self::isCorrectNationalUnifiedNumber($val_attr)) {
                return [false, 'FORMAT-SA-NATIONAL-UNIFIED-NUMBER'];
            }
        }

        if ($desc['FORMAT'] == 'SA-IDN') {
            $check_idn = true;
            /*
            if ($desc['IDN-TYPE-ATTRIBUTE']) {
                $idn_type = $object->getVal($desc['IDN-TYPE-ATTRIBUTE']);
                if ($idn_type and $idn_type != 1 and $idn_type != 2) {
                    $check_idn = false;
                } // other type of IDN than ahwal and iqama so no check
            }*/

            if ($check_idn) {
                list($idn_correct, $type) = AfwFormatHelper::getIdnTypeId($val_attr);
                if (!$idn_correct) {
                    return [false, 'FORMAT-SA-IDN'];
                }
            } else {
                return [true, ''];
            }
        }

        if ($desc['FORMAT'] == 'HTTP') {
            if (!filter_var($val_attr, FILTER_VALIDATE_URL)) {
                return [false, 'FORMAT-HTTP'];
            }
        }

        if (
            strtoupper($desc['TYPE']) == 'TEXT' or
            strtoupper($desc['TYPE']) == 'MTEXT'
        ) {
            if ($desc['SIZE'] == 'AREA' or $desc['SIZE'] == 'AEREA') {
                $desc['TYPE'] = 'MTEXT';
            }
            if (strtoupper($desc['TYPE']) == 'MTEXT') {
                $length = AfwStringHelper::nbWordsInJomla($val_attr);
            } else {
                if ($desc['UTF8']) {
                    $length = AfwStringHelper::strlen_ar($val_attr);
                } else {
                    $length = strlen($val_attr);
                }
            }
            if (!$desc['MANDATORY'] and !$desc['REQUIRED'] and (!is_array($desc['FORMAT']) or !$desc['FORMAT']['STRING-LENGTH'])) {
                $desc['MIN-SIZE'] = 0;
            }
            $min_size = $desc['MIN-SIZE'];
            if (!$min_size) {
                $min_size = 0;
            }

            $max_length = $desc['MAXLENGTH'];
            if (!$max_length) {
                $max_length = 99999;
            }

            if ($length < $min_size) {
                return [false, 'TEXT-MIN-LENGTH'];
            }
            if ($length > $max_length) {
                //die("TEXT-MAX-LENGTH ERROR FOUND $length > $max_length, val_attr=[$val_attr] desc=".var_export($desc,true));
                return [false, 'TEXT-MAX-LENGTH'];
            }
        }

        if (strtoupper($desc['TYPE']) == 'GDAT') {
            $val_GDAT = substr($val_attr, 0, 10);
            if (($val_GDAT != '0000-00-00') and (!AfwDateHelper::isCorrectGregDate($val_GDAT))) {
                return [false, 'FORMAT-GDAT'];
            }
        }
        if (strtoupper($desc['TYPE']) == 'DATE') {
            if (!AfwDateHelper::isCorrectHijriDate($val_attr)) {
                return [false, 'FORMAT-DATE'];
            }
            //if(!AfwDateHelper::isCorrectHijriDate($val_attr)) return array(false,"FORMAT-DATE");
        }
        if (strtoupper($desc['TYPE']) == 'TIME') {
            if (
                !preg_match(
                    '/^[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/',
                    trim($val_attr)
                ) and
                !preg_match(
                    '/^[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/',
                    trim($val_attr)
                )
            ) {
                return [false, 'FORMAT-TIME'];
            }
        }
        if (strtoupper($desc['TYPE']) == 'PCTG') {
            $val_without_decimal_dot = str_replace('.', '', $val_attr);

            if (!ctype_digit($val_without_decimal_dot)) {
                return [false, 'TYPE-PCTG-FORMAT'];
            }
            if (floatval($val_attr) < 0.0) {
                return [false, 'TYPE-PCTG-VALUE'];
            }
            if (floatval($val_attr) > 100.0) {
                return [false, 'TYPE-PCTG-VALUE'];
            }
        }

        if (strtoupper($desc['TYPE']) == 'YN') {
            if ($val_attr != 'Y' and $val_attr != 'N' and $val_attr != 'W') {
                return [false, 'TYPE-YN'];
            }
        }

        if (strtoupper($desc['TYPE']) == 'ENUM') {
            if ($desc['ANSWER'] != 'FUNCTION') {
                $answerTable = AfwLoadHelper::explodeEnumAnswer($desc['ANSWER']);
                if (!isset($answerTable[$val_attr])) {
                    return [false, 'TYPE-ENUM'];
                }
            }
        }

        if (strtoupper($desc['TYPE']) == 'MENUM') {
            // todo
        }

        return [true, ''];
    }

    public static final function isFormatted($desc)
    {
        if ($desc['FORMAT']) {
            return true;
        }

        if (strtoupper($desc['TYPE']) == 'TEXT') {
            return true;
        }
        if (strtoupper($desc['TYPE']) == 'GDAT') {
            return true;
        }
        if (strtoupper($desc['TYPE']) == 'DATE') {
            return true;
        }
        if (strtoupper($desc['TYPE']) == 'TIME') {
            return true;
        }
        if (strtoupper($desc['TYPE']) == 'PCTG') {
            return true;
        }
        if (strtoupper($desc['TYPE']) == 'YN') {
            return true;
        }
        if (strtoupper($desc['TYPE']) == 'ENUM') {
            return true;
        }
        if (strtoupper($desc['TYPE']) == 'MENUM') {
            return true;
        }

        // if($desc["TYPE"]=="MFK") return true;

        return false;
    }

    public static final function formatValue(
        $value,
        $key,
        $structure = '',
        $getFormatLink = true,
        $obj = null
    ) {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        if (!$structure) {
            if ($obj) $structure = AfwStructureHelper::getStructureOf($obj, $key);
        }

        if (!$structure) return $value . " no structure";


        $data_to_display = $value;


        $formatted = true;

        if ($structure['TYPE'] == 'GDAT') {
            //list($data_to_display,) = explode(" ",$value);
            $data_to_display = $value;
            // $data_to_display = AfwDateHelper::justDecodeValue($value, $structure);
            $link_to_display = '';
            list($date_to_display, $time_to_display) = explode(' ', $data_to_display);
            // die("value $value date $data_to_display explode = list($date_to_display, $time_to_display)");

            if ((!$structure['FORMAT']) or ($structure['FORMAT'] == 'DATE')) {
                // throw new RuntimeException("formatValue for ($key - GDAT) called structure['FORMAT'] = ".$structure['FORMAT']);
                $data_to_display = $date_to_display;
            }

            if ($structure['FORMAT'] == 'FRM') {
                $structure['FORMAT'] = 'EXP-WEEKDAY-MONTHNAME';
            }

            if (AfwStringHelper::stringStartsWith($structure['FORMAT'], 'EXP-')) {
                // ex : "FORMAT"=>"EXP-WEEKDAY-MONTHNAME"

                $WeekDayOn = 0;
                $YearOn = 1;
                $MonthNameOn = 0;

                $fparts = explode('-', $structure['FORMAT']);
                foreach ($fparts as $fpart) {
                    if ($fpart == 'WEEKDAY') {
                        $WeekDayOn = 1;
                    }
                    if ($fpart == 'YEAROFF') {
                        $YearOn = 0;
                    }
                    if ($fpart == 'MONTHNAME') {
                        $MonthNameOn = 1;
                    }
                }
                $data_to_display = mysqldate_to_explicit_fr_date_arr(
                    $date_to_display,
                    $WeekDayOn,
                    $YearOn,
                    $MonthNameOn,
                    $Separator = ' ',
                    $return_array = false
                );
                //die("$data_to_display = mysqldate_to_explicit_fr_date_arr(date_to_display=$date_to_display, WeekDayOn=$WeekDayOn, YearOn=$YearOn, MonthNameOn=$MonthNameOn,Separator=' ',return_array=false);");
            } elseif ($structure['FORMAT'] == 'DATETIME') {
            } elseif ($structure['FORMAT'] == 'CONVERT_HIJRI') {
                if ($date_to_display) {
                    $hijri_date = AfwDateHelper::gregToHijri($date_to_display, 'hdate-dashed', $structure['IF-SEEMS-HIJRI-KEEP']);
                    $data_to_display = "<div class='dates_ar_en'>" .
                        "<div class='hzmdate date_en'><div class='dval'>$date_to_display</div><div class='dunit'>م</div></div>" .
                        "<div class='hzmdate date_ar'><div class='dval'>$hijri_date</div><div class='dunit'>هـ</div></div>" .
                        "</div>";
                } else {
                    $data_to_display = '';
                }
            }

            /* see if this old charabia can be useful for some cases anduse it here before

                if ($decode_format == 'FRM') 
                {
                    $date_en = substr(
                        $attribute_value,
                        0,
                        10
                    );
                    $date_en = explode('-', $date_en);
                    $month_name = AfwLanguageHelper::translateKeyword('MONTH_' . $date_en[1],$lang);
                    $return = $date_en[2] .' ' . $month_name . ' ' . $date_en[0];
                } elseif ($decode_format == 'FR') {
                    $date_en = substr(
                        $attribute_value,
                        0,
                        10
                    );
                    $date_en = explode('-', $date_en);

                    $return =
                        $date_en[2] .
                        '/' .
                        $date_en[1] .
                        '/' .
                        $date_en[0];
                } elseif ($decode_format == 'FRH') {
                    $date_en = substr(
                        $attribute_value,
                        0,
                        10
                    );
                    $date_en = explode('-', $date_en);
                    $hr = substr($attribute_value, 11, 8);
                    $hr = explode(':', $hr);

                    $hr_a = $hr[0] . 'h' . $hr[1];

                    $return =
                        $date_en[2] .
                        '/' .
                        $date_en[1] .
                        '/' .
                        $date_en[0] .
                        ' à ' .
                        $hr_a;
                } elseif ($decode_format == 'HEURE') {
                    $hr = substr($attribute_value, 11, 8);
                    $hr = explode(':', $hr);

                    $return = $hr[0] . 'h' . $hr[1];
                } elseif ($decode_format == 'ARABIC-TIME') {
                    $hr = substr($attribute_value, 11, 8);
                    $hr = explode(':', $hr);

                    $return =
                        'س' . $hr[0] . ' و' . $hr[1] . 'دق';
                    if ($hr[2]) {
                        $return .= ' و' . $hr[2] . 'ث';
                    }
                } 
                elseif ($decode_format == 'LONG') {
                    $return = AfwDateHelper::displayGDateLong($attribute_value);
                    
                } else {
                    $return = $attribute_value;
                }

             */
        } elseif ($structure['TYPE'] == 'DATE') {
            $data_to_display = AfwDateHelper::justDecodeValue($value, $structure);
            //if(AfwStringHelper::stringStartsWith($data_to_display,"هـ")) throw new AfwRuntimeException("rafik formatValue twice : $data_to_display");
            $old_data_to_display = $data_to_display;
            $link_to_display = '';
            if ($structure['FORMAT'] == 'HIJRI_UNIT') {
                $structure['HIJRI_UNIT'] = true;
            }
            if ($structure['FORMAT'] == 'HIJRI_UNIT') {
                $structure['HIJRI_UNIT'] = true;
            }

            if ($structure['HIJRI_UNIT']) {
                if ($data_to_display) {
                    $data_to_display .= ' هـ';
                }
            }

            if ($structure['FORMAT'] == 'TOOLTIP_NASRANI') {
                $help_nasrani = htmlentities(
                    'الموافق  ' .
                        AfwDateHelper::hijriToGreg($old_data_to_display)
                );
                if ($data_to_display) {
                    $data_to_display =
                        $data_to_display .
                        " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img data-toggle=\"tooltip\" data-placement=\"top\" width=\"24px\" height=\"24px\" title=\"$help_nasrani\" src=\"../lib/images/information.png\" />";
                } else {
                    $data_to_display = '';
                }
            } elseif ($structure['FORMAT'] == 'CONVERT_NASRANI') {
                if ($data_to_display) {
                    $hijri_date = $data_to_display;
                    $date_to_display = AfwDateHelper::hijriToGreg($old_data_to_display, false);
                    $data_to_display = "<div class='dates_ar_en'>" .
                        "<div class='hzmdate date_en'><div class='dval'>$date_to_display</div><div class='dunit'>م</div></div>" .
                        "<div class='hzmdate date_ar'><div class='dval'>$hijri_date</div><div class='dunit'>هـ</div></div>" .
                        "</div>";
                } else {
                    $data_to_display = '';
                }
            } elseif ($structure['FORMAT'] == 'CONVERT_NASRANI_SIMPLE') {
                if ($data_to_display) {
                    $data_to_display =
                        $data_to_display .
                        ' &nbsp;&nbsp;&nbsp; الموافق  ' .
                        AfwDateHelper::hijriToGreg($old_data_to_display) .
                        ' م';
                } else {
                    $data_to_display = '';
                }
            } elseif ($structure['FORMAT'] == 'CONVERT_NASRANI_2LINES') {
                if ($data_to_display) {
                    $data_to_display =
                        "<div class='date2lines'>" .
                        $data_to_display .
                        ' هـ<br>الموافق  ' .
                        AfwDateHelper::hijriToGreg($old_data_to_display) .
                        ' م </div>';
                } else {
                    $data_to_display = '';
                }
            } elseif ($structure['FORMAT'] == 'CONVERT_SYSTEM_FORMAT') {
                $currSDF = strtolower(AfwSession::currentSystemDateFormat());
                if ($data_to_display) {
                    if ($currSDF == 'greg') {
                        $data_to_display = AfwDateHelper::hijriToGreg($old_data_to_display) . ' م';
                    }
                } else {
                    $data_to_display = '';
                }
            } elseif ($structure['FORMAT'] == 'CONVERT_NASRANI_VERY_SIMPLE') {

                if ($data_to_display) {
                    if (AfwSession::hasOption('HIJRI_TO_GREG')) {
                        $data_to_display =
                            AfwDateHelper::hijriToGreg($old_data_to_display) .
                            ' م';
                    }
                } else {
                    $data_to_display = '';
                }
            } elseif ($structure['FORMAT'] == 'MEDIUM_HIJRI') {
                if ($data_to_display) {
                    $data_to_display = AfwDateHelper::mediumHijriDate(
                        $old_data_to_display
                    );
                } else {
                    $data_to_display = '';
                }
            } elseif ($structure['FORMAT'] == 'FULL_HIJRI') {
                if ($data_to_display) {
                    $data_to_display = AfwDateHelper::fullHijriDate(
                        $old_data_to_display
                    );
                } else {
                    $data_to_display = '';
                }
            }
        } elseif ($structure['TYPE'] == 'TIME') {
            $data_to_display = AfwDateHelper::displayTime($value, $structure, $structure['FORMAT'], $obj);
            $formatted = true;
        } elseif (!in_array(
            $structure['TYPE'],
            [
                'MFK',
                'FK',
                'ANSWER',
                'YN',
                'ENUM',
                'MENUM',
                'PK',
                'DEL',
                'EDIT',
                'SHOW',
            ]
        )) {
            if (($structure['TYPE'] == 'FLOAT') and
                AfwStringHelper::stringStartsWith($structure['FORMAT'], '*.')
            ) {
                list($a, $b) = explode('.', $structure['FORMAT']);
                //if($attribute) die("$attribute : list($a,$b) = ".$structure["FORMAT"]);
                if (!$data_to_display) $data_to_display = 0;
                $old_data_to_display = $data_to_display;
                $data_to_display = number_format(
                    $data_to_display,
                    intval($b),
                    '.',
                    ' '
                );
                //if($key=="price5") die("$key : list($a,$b) = ".$structure["FORMAT"]." $data_to_display = number_format($old_data_to_display, intval($b), '.', ' ') ");
                $zero = number_format(0, intval($b), '.', ' ');
                if ($a == '*' and $data_to_display == $zero) {
                    $data_to_display = '';
                }
                if ($data_to_display and $structure['UNIT']) {
                    $data_to_display .= ' ' . $structure['UNIT'];
                }
            } elseif ($structure['TYPE'] == 'PCTG') {
                // $a = "*";
                $b = 1;
                $data_to_display = number_format(
                    $data_to_display,
                    intval($b),
                    '.',
                    ' '
                );
                //if($key=="price5") die("$key : list($a,$b) = ".$structure["FORMAT"]." $data_to_display = number_format($old_data_to_display, intval($b), '.', ' ') ");
                // $zero = number_format(0, intval($b), '.', ' ');
                // if(($a=="*") and ($data_to_display==$zero)) $data_to_display= "";
                if ($data_to_display) {
                    $data_to_display .= ' %';
                }
            } elseif ($structure['TYPE'] == 'TEXT') {
                //if($attribute=="warning_nb") die("getVal of ($key) is $data_to_display");
                if ($structure['FORMAT'] == 'TOHTML') {
                    $data_to_display = self::toHtml($data_to_display);
                } elseif ($structure['FORMAT'] == 'PARAGRAPH-TOHTML') {
                    $data_to_display = self::toHtml($data_to_display);
                    $data_to_display = "<span class='page_paragraph'>$data_to_display</span>";
                } elseif (
                    $structure['FORMAT'] == 'CSSED' and
                    $structure['CSSED_TO_CLASS']
                ) {
                    $cssed_to_class = $structure['CSSED_TO_CLASS'];
                    if ($data_to_display) {
                        $data_to_display =
                            "<div class='$cssed_to_class'>" .
                            $data_to_display .
                            '</div>';
                    }
                    //if($attribute=="warning_nb") die("CSSED($cssed_to_class) : data_to_display of ($key) is $data_to_display");
                }

                if ($structure['FORMAT'] == 'PRE' or $structure['PRE']) {
                    $pre_class = $structure['PRE'];
                    if ($pre_class) {
                        $pre_class = " class='$pre_class' ";
                    }
                    if ($structure['TEXT-ALIGN']) {
                        if ($structure['TEXT-ALIGN']=='BYLANG') {
                            $text_algn = AfwLanguageHelper::getLanguageAlign($lang);
                        }
                        else $text_algn = $structure['TEXT-ALIGN'];

                        $text_align = 'text-align:' . $text_algn;
                    } elseif ($structure['UTF8']) {
                        $text_align = 'text-align:right';
                    } else {
                        $text_align = 'text-align:left';
                    }

                    if ($structure['DIR']) {
                        if ($structure['DIR']=='BYLANG') {
                            $dir = AfwLanguageHelper::getLanguageDir($lang);
                        }   
                        else $dir = $structure['DIR'];
                    } elseif ($structure['UTF8']) {
                        $dir = 'rtl';
                    } else {
                        $dir = 'ltr';
                    }
                    if ($structure['WIDTH']) {
                        $wd = $structure['WIDTH'];
                    } else {
                        $wd = '100%';
                    }
                    if ($structure['MIN-WIDTH']) {
                        $min_wd = $structure['MIN-WIDTH'];
                    } else {
                        $min_wd = '100px';
                    }

                    $key_struct = var_export($structure, true);
                    $data_to_display = "<pre id='$key-$key' $pre_class style='direction: $dir;padding:8px;height: 100%;overflow: scroll;min-width:${min_wd};width:${wd};$text_align'>$data_to_display </pre>"; // .$key_struct;
                }

                if ($getFormatLink) {
                    if ($structure['FORMAT'] == 'EMAIL') {
                        $link_to_display = 'mailto:' . $data_to_display;
                    } elseif ($structure['FORMAT'] == 'WEB') {
                        $link_to_display = $data_to_display;
                    }
                }
            } elseif (
                $structure['TYPE'] == 'INT' and
                ($structure['FORMAT'] == 'CAN_ZERO' or $structure['CAN_ZERO'])
            ) {
                if ($structure['EMPTY_IS_ALL'] and !$data_to_display) {
                    $all_code = "ALL-$key";
                    if ($obj) {
                        $return = $obj->translate($all_code, $lang);
                        if ($return == $all_code) {
                            $return = $obj->translateOperator('ALL', $lang);
                        }
                    } else $return = $all_code;
                    //die("raf, $key : data_to_display=$data_to_display, return=$return");
                    $data_to_display = $return;
                } else {
                    if (!$data_to_display) {
                        $data_to_display = '0';
                    }
                    if ($structure['UNIT'] and (!$structure['DISPLAY_HIDE_UNIT'])) {
                        $data_to_display .= ' ' . $structure['UNIT'];
                    }
                }
            } elseif ($data_to_display and $structure['UNIT'] and (!$structure['DISPLAY_HIDE_UNIT'])) {
                $data_to_display .= ' ' . $structure['UNIT'];
            }
        } else {
            $data_to_display = null;
            $link_to_display = null;
            $formatted = false;
        }
        //if($key=="price5") die("return of formatValue($value,$key) : $formatted=formatted, data_to_display=$data_to_display, link_to_display=$link_to_display");
        return [$formatted, $data_to_display, $link_to_display];
    }


    public static final function getItemsEmptyMessage($object, $structure, $lang = "ar")
    {
        if ($structure['EMPTY-ITEMS-MESSAGE']) {
            $empty_code = $structure['EMPTY-ITEMS-MESSAGE'];
        } else {
            $empty_code = 'obj-empty';
        }
        return "<div class='empty_message'>" . $object->translate($empty_code, $lang) . '</div>';
    }


    public static function getEnumVal($object, $attribute, $field_value)
    {
        global $lang;

        $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        if (!$field_value and $structure['EMPTY_IS_ALL']) {
            $all_code = "ALL-$attribute";
            $return = $object->translate($all_code, $lang);
            if ($return == $all_code) {
                $return = $object->translateOperator('ALL', $lang);
            }

            return $return;
        }
        // $call_method = "get EnumVal(attribute = $attribute, field_value = $field_value)";

        $answerTable = AfwLoadHelper::getEnumTotalAnswerList($object, $attribute);
        $return = $answerTable[$field_value];
        /*
        if($attribute=='unit_type_id')
        {
            die("attribute=$attribute, answerTable=".var_export($answerTable,true).", return=answerTable[$field_value]=$return");
        }*/

        if ($return == 'INSTANCE_FUNCTION') {
            throw new AfwRuntimeException("INSTANCE_FUNCTION Error happened for attribute $attribute : answerTable = " . var_export($answerTable, true));
        }
        $return = !$return ? $field_value : $return;

        return $return;
    }

    public static final function decodeSimulatedFieldValue($object, $attribute, $field_value)
    {
        $oldval = $object->getVal($attribute);
        $object->simulSet($attribute, $field_value);
        $return = $object->decode($attribute);
        //die("$return = $object -> decode SimulatedFieldValue($attribute, $field_value)");
        $object->simulSet($attribute, $oldval);

        return $return;
    }

    /**
     * 
     * @param AFWObject $obj
     */

    public static final function decode($attribute, $typattr, $decode_format, $attribute_value, $integrity = true, $lang = "ar", $structure = null, $obj = null, $translate_if_needed = true)
    {
        switch ($typattr) {
            case 'INT':
                switch ($decode_format) {
                    case 'MONEY':
                        $return = number_format(
                            $attribute_value,
                            0,
                            ',',
                            ' '
                        );
                        break;
                    case 'LETTRES':
                        // require_once "php ???";
                        $return =
                            'not implemented od using int2strRF (For French I think) but old php code lost';
                        break;
                    case 'CSSED':
                        // rafik 2022/12 obsolete until we understand to avoid infinite loop
                        $return = "CSSED is obsolete in this afw version";
                        //$return = $object->showAttribute($attribute);
                        //if($attribute=="warning_nb") die("showAttribute($attribute) returned $return");
                        break;

                    case 'EMPTY_IS_ALL':
                        $return = $attribute_value;
                        if (!$return) {
                            $all_code = "ALL-$attribute";
                            if ($obj) $return = $obj->translate(
                                $all_code,
                                $lang
                            );
                            else  $return = $all_code;

                            if ($return == $all_code) {
                                $return = AfwLanguageHelper::translateKeyword(
                                    'ALL',
                                    $lang
                                );
                            }
                        }
                        break;

                    default:
                        if ($obj) {
                            $return = $obj->specialDecode(
                                $attribute,
                                $attribute_value,
                                $lang
                            );
                        } else {
                            $return = $attribute_value;
                        }

                        break;
                }
                break;
            case 'FLOAT':
                list($a, $b) = explode('.', $decode_format);
                //if($attribute) die("$attribute : list($a,$b) = ".$structure["FORMAT"]);

                $return = number_format(
                    $attribute_value,
                    intval($b),
                    '.',
                    ' '
                );
                //if($attribute=="price5") die("$attribute : list($a,$b) = ".$structure["FORMAT"]." $return = number_format($attribute_value, intval($b), '.', ' ') ");
                $zero = number_format(
                    0,
                    intval($b),
                    '.',
                    ' '
                );
                if ($a == '*' and $return == $zero) {
                    $return = '';
                }
                break;
            case 'PCTG':
                $return =
                    number_format(
                        $attribute_value,
                        2,
                        '.',
                        ' '
                    ) . '%';
                break;
            case 'YN':
                $decode_format_l = strtolower($decode_format);
                $val = $attribute_value;
                if ($val == 'Y') {
                    $return =
                        ($decode_format_l == 'bool') ? true : 'Yes';
                } elseif ($val == 'N') {
                    $return =
                        ($decode_format_l == 'bool') ? false : 'No';
                } elseif ($val == 'W') {
                    $return =
                        ($decode_format_l == 'bool') ? false : 'EUH';
                } else {
                    $return = $val;
                }
                if ($translate_if_needed and $obj) {
                    $return = $obj->showYNValueForAttribute(strtoupper($return), $attribute);
                }

                break;
            case 'TIME':
                if (!$structure) {
                    if (!$obj) throw new AfwRuntimeException("structure and obj should not be both null if we decode a TIME field");
                    $structure = AfwStructureHelper::getStructureOf($obj, $attribute);
                }
                $return = AfwDateHelper::displayTime($attribute_value, $structure, $decode_format, $obj);
                break;
            case 'GDAT':
            case 'GDATE':
                list($gdatformatted, $return, $gdatlink_to_display) = self::formatValue(
                    $attribute_value,
                    $attribute,
                    $structure,
                    $getFormatLink00 = true,
                    $obj
                );
                break;
            case 'DATE':
                if (!$structure) {
                    if (!$obj) throw new AfwRuntimeException("structure and obj should not be both null if we decode a DATE field");
                    $structure = AfwStructureHelper::getStructureOf($obj, $attribute);
                }
                if ($decode_format) {
                    $structure['FORMAT'] = $decode_format;
                }
                list(
                    $dte_formatted,
                    $return,
                    $return_link_to_display,
                ) = self::formatValue($attribute_value, $attribute, $structure, true, $obj);
                // $return .= " [$decode_format]";
                // $return = $object->showAttribute($attribute);
                break;
            case 'FK':
                if ((!$obj) or (!$structure)) throw new AfwRuntimeException("both structure and obj should not be null if we decode an FK field");
                // as we do only decode consider the answer class as lookup
                if (!$attribute_value) {
                    $return = '';
                    if ($structure['EMPTY_IS_ALL']) {
                        $all_code = "ALL-$attribute";
                        $return = $obj->translate($all_code, $lang);
                        if ($return == $all_code) {
                            $return = AfwLanguageHelper::translateKeyword('ALL', $lang);
                        }
                    }
                    // if($attribute=="customer_id") throw new AfwRuntimeException("AfwFormatHelper::decode($attribute) : case ABC01 return=$return");
                } else {
                    $items_empty_message = AfwFormatHelper::getItemsEmptyMessage($obj, $structure, $lang);
                    $items_separator = $structure['LIST_SEPARATOR'];
                    if (!$items_separator) $items_separator = $structure['MFK-SHOW-SEPARATOR'];
                    if (!$items_separator) $items_separator = "<br>\n";

                    $pk = $structure["ANSWER-PK"];
                    if (!$pk) $pk = "((id))";
                    $ans_table = $structure["ANSWER"];
                    $ans_module = $structure["ANSMODULE"];
                    if (!$ans_module) throw new AfwRuntimeException("strcuture of FK field '$attribute' does not contain ANSMODULE property, structure=" . var_export($structure, true));
                    if (!isset($structure["SMALL-LOOKUP"])) {
                        list($lkp, $issmall) = AfwLoadHelper::getLookupProps($ans_module, $ans_table);
                        $structure["SMALL-LOOKUP"] = ($lkp and $issmall);
                    }
                    $small_lookup  = $structure["SMALL-LOOKUP"];

                    $return = AfwLoadHelper::decodeLookupValue($ans_module, $ans_table, $attribute_value, $items_separator, $items_empty_message, $pk, $small_lookup);
                    // if($attribute=="customer_id") throw new AfwRuntimeException("AfwFormatHelper::decode($attribute) : case ABC02 return=AfwLoadHelper::decodeLookupValue($ans_module, $ans_table, $attribute_value, $items_separator, $items_empty_message, $pk, $small_lookup)=$return");
                }
                /* rafik 16/12/2023 : oboslete code because in Momken v3.0 we use the loader who manage lookups and table-based decodes
                $structure = AfwStructureHelper::getStructureOf($obj,$attribute);
                if($structure["CATEGORY"]=="FORMULA")
                {
                    $object = $obj->calcObject($attribute);
                }
                else
                {
                    $object = $obj->het($attribute);
                }
                
                if (!$object) {
                    $return = '';
                    if ($structure['EMPTY_IS_ALL']) {
                        $all_code = "ALL-$attribute";
                        $return = $obj->translate($all_code,$lang);
                        if ($return == $all_code) {
                            $return = AfwLanguageHelper::translateKeyword('ALL',$lang);
                        }
                    }
                    //if($attribute=="status_id") throw new AfwRuntimeException("this->decode($attribute) : return=$return");
                } else {
                    $return = $object->getDisplay($lang);
                }*/


                //if($attribute=="status_id") die("this->decode($attribute) : return=$return, object->id = ".$object->id);
                break;
            case 'MFK':
                if ((!$obj) or (!$structure)) throw new AfwRuntimeException("both structure and obj should not be null if we decode an FK field");
                $items_empty_message = AfwFormatHelper::getItemsEmptyMessage($obj, $structure, $lang);
                $items_separator = $structure['LIST_SEPARATOR'];
                if (!$items_separator) $items_separator = $structure['MFK-SHOW-SEPARATOR'];
                if (!$items_separator) $items_separator = "<br>\n";


                $pk = $structure["ANSWER-PK"];
                if (!$pk) $pk = "((id))";
                $ans_table = $structure["ANSWER"];
                $ans_module = $structure["ANSMODULE"];

                if (!isset($structure["SMALL-LOOKUP"])) {
                    list($lkp, $issmall) = AfwLoadHelper::getLookupProps($ans_module, $ans_table);
                    $structure["SMALL-LOOKUP"] = ($lkp and $issmall);
                }

                $small_lookup  = $structure["SMALL-LOOKUP"];
                $return = AfwLoadHelper::lookupDecodeValues($ans_module, $ans_table, $attribute_value, $items_separator, $items_empty_message, $pk, $small_lookup);
                // $return = "$return = AfwLoadHelper::lookupDecodeValues($ans_module, $ans_table, $attribute_value, $items_separator, $items_empty_message,$pk, $small_lookup)";

                /* rafik 16/12/2023 : oboslete code because in Momken v3.0 we use the loader who manage lookups and table-based decodes                
                /*
                $objects = $obj->OBJECTS_CACHE[$attribute]
                    ? $obj->OBJECTS_CACHE[$attribute]
                    : $obj->get($attribute, 'object');
                $array = [];
                
                foreach ($objects as $object) {
                    $array[] = $object->getDisplay($lang);
                }
                $seplist = $structure['LIST_SEPARATOR'];
                if (!$seplist) {
                    $seplist =
                        $structure['MFK-SHOW-SEPARATOR'];
                }
                if (!$seplist) {
                    $seplist = "<br>\n";
                }
                $return = implode($seplist, $array);
                */

                break;
            case 'ANSWER':
                if (!$obj) throw new AfwRuntimeException("structure and obj should not be both null if we decode an ANSWER field");
                $valfld = $attribute_value;
                $return = self::decodeAnswerOfAttribute($obj, $attribute, $valfld);
                break;
            case 'ENUM':
                if (!$obj) throw new AfwRuntimeException("structure and obj should not be both null if we decode an ENUM field");
                $valfld = $attribute_value;
                // if($attribute == 'unit_type_id') die("decode of enum field " . get_class($object)  . "->get EnumVal(attribute=$attribute, valfld = $valfld)");
                $return = self::getEnumVal($obj, $attribute, $valfld);
                break;
            case 'MENUM':
                if (!$obj) throw new AfwRuntimeException("structure and obj should not be both null if we decode an MENUM field");
                $sep = $obj->getSeparatorFor($attribute);
                $valfld = $attribute_value;
                $val_arr = explode($sep, $valfld);
                $return = '';
                $array = [];
                foreach ($val_arr as $vv => $valval) {
                    $decvalval = self::getEnumVal($obj, $attribute, $valval);
                    $array[] = $decvalval;
                    // $return .= " " . $decvalval;
                }
                $seplist = $structure['LIST_SEPARATOR'];
                if (!$seplist) {
                    $seplist =
                        $structure['MFK-SHOW-SEPARATOR'];
                }
                if (!$seplist) {
                    $seplist = "<br>\n";
                }
                $return = implode($seplist, $array);
                // $return = trim($return);
                break;
            case 'TEXT':
                // if(!$attribute_value) throw new AfwRuntimeException("decode attribute `$attribute`(TEXT TYPE) value=[$attribute_value]");
            default:
                $return = stripslashes($attribute_value);
                switch ($decode_format) {
                    case 'UCFIRST':
                        $return = ucfirst(
                            strtolower($return)
                        );
                        break;
                    case 'HEURE':
                        $res = strlen($return);
                        if ($res == 4) {
                            $return = substr_replace(
                                $return,
                                ':',
                                2,
                                0
                            );
                        } elseif ($res == 3) {
                            $return = substr_replace(
                                $return,
                                ':',
                                1,
                                0
                            );
                        }
                        break;
                }
                break;
        }

        $unit = $structure['UNIT'];
        $hide_unit = $structure['DISPLAY_HIDE_UNIT'];
        if ($unit and $return and !$hide_unit) {
            $return .= ' ' . $unit;
        }

        $link_url = $structure['LINK-URL'];
        $link_css_class = $structure['LINK-CSS'];
        if (!$link_css_class) {
            $link_css_class = 'nice_link';
        }

        $link_url = $obj->decodeText($link_url, '', false);

        $target = '';
        $popup_t = '';

        if (
            $link_url and
            $return != '' and
            $decode_format != 'NO-URL'
        ) {
            $return = "<a class='$link_css_class' $target href='$link_url&popup=$popup_t'>$return</a>";
        }

        return $return;
    }

    public static function formatHtml($p_text, $target_window = "my_urls", $css_class = 'my_url', $click_here = "انقر هنا")
    {
        $p_text_arr = explode("\n", $p_text);
        $new_p_text_arr = array();

        foreach ($p_text_arr as $p_text_item) {
            $p_text_item = trim($p_text_item);
            if (AfwStringHelper::stringStartsWith($p_text_item, "http://") or AfwStringHelper::stringStartsWith($p_text_item, "https://")) {
                if ($click_here) $p_text_item_label = $click_here;
                else $p_text_item_label = $p_text_item;

                $p_text_item = "<a class='$css_class' target='$target_window' href='$p_text_item'>$p_text_item_label</a>";
            }


            $new_p_text_arr[] = $p_text_item;
        }


        return implode("\n", $new_p_text_arr);
    }

    public static function toHtml($p_text, $target_window = "my_urls", $css_class = 'my_url', $click_here = "انقر هنا")
    {
        global $table_name, $img_field_names, $id;

        $desc = self::formatHtml($p_text, $target_window, $css_class, $click_here);

        $desc = str_replace("[[Û]]", "<b>", $desc);
        $desc = str_replace("[[/Û]]", "</b>", $desc);
        $desc = str_replace("\n", "<br>", $desc);
        $desc = str_replace("\r", "", $desc);
        $desc = str_replace("[[Þ]]", "<ul>", $desc);
        $desc = str_replace("[[", "<b>", $desc);
        $desc = str_replace("]]", "</b>", $desc);
        $desc = str_replace("((", "<strong>", $desc);
        $desc = str_replace("))", "</strong>", $desc);
        $desc = str_replace("[[/Þ]]", "</ul>", $desc);
        $desc = str_replace("[[/Ú]]", "<li>", $desc);
        $desc = str_replace("[[/Ú]]", "</li>", $desc);
        $desc = str_replace("[[æ]]", "<center>", $desc);
        $desc = str_replace("[[/æ]]", "</center>", $desc);
        $desc = str_replace("I - ", "أولا: ", $desc);
        $desc = str_replace("II - ", "ثانيا : ", $desc);
        $desc = str_replace("III - ", "ثالثا : ", $desc);
        $desc = str_replace("IV - ", "رابعا : ", $desc);
        $desc = str_replace("V - ", "خامسا : ", $desc);
        $desc = str_replace("VI - ", "سادسا : ", $desc);
        $desc = str_replace("VII - ", "سابعا: ", $desc);
        $desc = str_replace("VIII - ", "ثامنا: ", $desc);
        $desc = str_replace("  ", "&nbsp;&nbsp;", $desc);

        $desc = str_replace("[[Ê]]", "<p class='page_paragraph_title'>", $desc);
        $desc = str_replace("[[/Ê]]", "</p>", $desc);

        $desc = str_replace("[!!!", "<span class='important'>", $desc);
        $desc = str_replace("!!!]", "</span>", $desc);

        for ($bl = 1; $bl <= 30; $bl++) {
            $desc = str_replace("+++${bl}+++", "<div class=\"bulle03\">${bl}</div>", $desc);
            $desc = str_replace("++${bl}++", "<div class=\"bulle02\">${bl}</div>", $desc);
            $desc = str_replace("+${bl}+", "<div class=\"bulle01\">${bl}</div>", $desc);
            /*
                        $desc = str_replace("//","<div class='bulle_body01'>",$desc);
                        $desc = str_replace("\\\\","</div'>",$desc);
                        */
        }


        if (isset($img_field_names)) {
            foreach ($img_field_names as $field_name) {
                $token = "<$field_name>";
                $desc = str_replace($token, "<br><img src='pic/${table_name}_${field_name}_${id}.png' />", $desc);
            }
        }
        return $desc;
    }

    public static function mobileError($mobile, $lang = "ar", $country = "SA")
    {
        if ($country == "SA") {
            if (!preg_match('/^05[0-9]{8}$/', $mobile)) return "Incorrect mobile number";
        } else return "not implemented mobile format check for country '$country'";

        return "";
    }

    public static function formatPhone($phone_num, $region_id = 1, $country = "SA")
    {
        // 011 XXX XXXX - Riyadh & the greater central region
        // 012 XXX XXXX - Western region, includes Makkah, Jeddah, Taif, Rabigh
        // 013 XXX XXXX- The Eastern Province, which includes, Dammam, Khobar, Qatif, Jubail, Dhahran, Hafar al-Batin & others
        // 014 XXX XXXX - Al-Madinah, Tabuk, Al-Jawf, Yanbu, Turaif, Skaka and Northern Borders Region
        // 016 XXX XXXX - Al-Qassim, Majma & Hail
        // 017 XXX XXXX - Southern regions like Asir, Al-Baha, Jizan, Najran & Khamis Mushait

        // SO :

        // 646 | منطقة الرياض                             |
        $prefix_reg = "011";
        // 648 | منطقة الشرقية                            |
        if ($region_id == 648) $prefix_reg = "013";
        // 651 | منطقة عسير                               |
        if ($region_id == 651) $prefix_reg = "017";
        // 652 | منطقة المدينة المنورة                    |
        if ($region_id == 652) $prefix_reg = "014";
        // 653 | منطقة الجوف                              |
        if ($region_id == 653) $prefix_reg = "014";
        // 655 | منطقة الباحة                             |
        if ($region_id == 655) $prefix_reg = "017";
        // 656 | منطقة حائل                               |
        if ($region_id == 656) $prefix_reg = "016";
        // 657 | منطقة تبوك                               |
        if ($region_id == 657) $prefix_reg = "014";
        // 660 | منطقة جازان                              |
        if ($region_id == 660) $prefix_reg = "017";
        // 661 | منطقة نجران                              |
        if ($region_id == 661) $prefix_reg = "017";
        // 874 | منطقة الحدود الشمالية                    |
        if ($region_id == 874) $prefix_reg = "014";
        // 909 | منطقة القصيم                             |
        if ($region_id == 909) $prefix_reg = "016";
        // 9056 | منطقة مكة المكرمة                        |
        if ($region_id == 9056) $prefix_reg = "012";

        if (strlen($phone_num) == 7) $phone_num = $prefix_reg . $phone_num;



        return AfwFormatHelper::formatMobile($phone_num, $country);
    }

    public static function formatMobile($mobile_num, $country = "SA")
    {
        list($mobile_nummber1, $mobile_nummber2) = explode("/", $mobile_num);
        if ((strlen(trim($mobile_nummber2)) > 0) and (strlen($mobile_nummber1) >= 9)) return AfwFormatHelper::formatMobile($mobile_nummber1, $country);
        $mobile_num = str_replace(' ', '/', $mobile_num);
        list($mobile_nummber1, $mobile_nummber2) = explode("/", $mobile_num);
        if ((strlen(trim($mobile_nummber2)) > 0) and (strlen($mobile_nummber1) >= 9)) return AfwFormatHelper::formatMobile($mobile_nummber1, $country);

        $mobile_num = str_replace('٠', '0', $mobile_num);
        $mobile_num = str_replace('١', '1', $mobile_num);
        $mobile_num = str_replace('٢', '2', $mobile_num);
        $mobile_num = str_replace('٣', '3', $mobile_num);
        $mobile_num = str_replace('٤', '4', $mobile_num);
        $mobile_num = str_replace('٥', '5', $mobile_num);
        $mobile_num = str_replace('٦', '6', $mobile_num);
        $mobile_num = str_replace('٧', '7', $mobile_num);
        $mobile_num = str_replace('٨', '8', $mobile_num);
        $mobile_num = str_replace('٩', '9', $mobile_num);
        $mobile_num = str_replace('+', '', $mobile_num);
        $mobile_num = str_replace('-', '', $mobile_num);
        $mobile_num = str_replace(' ', '', $mobile_num);
        $mobile_num = str_replace('(', '', $mobile_num);
        $mobile_num = str_replace(')', '', $mobile_num);
        $mobile_num = str_replace('[', '', $mobile_num);
        $mobile_num = str_replace(']', '', $mobile_num);
        $mobile_num = str_replace('/', '', $mobile_num);



        $country_prefix["SA"] = "966";
        $mobile_length["SA"] = 10;
        $left_complete["SA"] = array(0 => "0");

        if (AfwStringHelper::stringStartsWith($mobile_num, "00")) {
            $mobile_num = substr($mobile_num, 2);
        }

        if (AfwStringHelper::stringStartsWith($mobile_num, $country_prefix[$country])) {
            $mobile_num = substr($mobile_num, strlen($country_prefix[$country]));
        }
        $missed = $mobile_length[$country] - strlen($mobile_num);

        if ($missed > count($left_complete[$country])) $missed = 0;
        if ($missed < 0) $missed = 0;

        for ($k = 0; $k < $missed; $k++) $mobile_num = $left_complete[$country][$k] . $mobile_num;

        return $mobile_num;
    }

    public static function isCorrectTradeNumber($trade_num, $country = "SA")
    {
        if ($country == "SA") return preg_match('/^1[0-9]{9}$/', $trade_num);
        else return false; // because not implemented
    }

    public static function isCorrectNationalUnifiedNumber($trade_num, $country = "SA")
    {
        if ($country == "SA") return preg_match('/^7[0-9]{9}$/', $trade_num);
        else return false; // because not implemented
    }

    public static function isCorrectPhoneNum($phone_num, $country = "SA")
    {
        if ($country == "SA") return preg_match('/^0[1-2]{1}[0-9]{8}$/', $phone_num);
        else return false; // because not implemented
    }

    public static function isCorrectMobileNum($mobile_num, $country = "SA")
    {
        if ($country == "SA") return preg_match('/^05[0-9]{8}$/', $mobile_num);
        else return false; // because not implemented
    }

    public static function isCorrectEmailAddress($email_address)
    {
        return filter_var($email_address, FILTER_VALIDATE_EMAIL);
    }
    public static function isCorrectIDN($idn, $country = "SA")
    {
        $authorize_other_idns = AfwSession::config('ACCEPT-ANY-OTHER-IDN', false);
        list($idn_correct, $type) = AfwFormatHelper::getIdnTypeId($idn, $authorize_other_idns);
        return $idn_correct;
    }

    public static function getIdnTypeId($id_number, $authorize_other_sa_idns = false, $authorize_nid = true)
    {
        try {
            $id = trim($id_number);
            $type3 = substr($id, 0, 3);
            $type = substr($id, 0, 1);
            if ($type3 == "NID") return array($authorize_nid, 99);
            if ((strlen($id) !== 10) or (($type != 2) and ($type != 1))) {
                if (!$authorize_other_sa_idns) return array(false, 0);
                else return array(true, 3);
            }

            if (!is_numeric($id)) return array(false, 0);
            if (strlen($id) !== 10) return array(false, 0);
            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                if ($i % 2 == 0) {
                    $ZFOdd = str_pad((substr($id, $i, 1) * 2), 2, "0", STR_PAD_LEFT);
                    $sum += substr($ZFOdd, 0, 1) + substr($ZFOdd, 1, 1);
                } else {
                    $sum += substr($id, $i, 1);
                }
            }

            $idn_correct = $sum % 10 ? false : true;
            return array($idn_correct, $type);
        } catch (Exception $e) {
            return array(false, $e->getMessage());
        } catch (Error $e) {
            return array(false, $e->__toString());
        }
    }

    /**
     * 
     * @param AFWObject $object
     */

    public static final function formatITEMS($object, $attribute, $structure, $table_name, $call_method, $max_items)
    {
        /*
        if((!$structure["NO-CACHE"]) and $object->gotItems Cache[$attribute])   
        {
            $return = $object->gotItems Cache[$attribute];
            if($attribute=="requestList") die("return from gotItems Cache = " . var_export($return,true));
        }
        */
        $return = null;
        if (!$return) {
            list($ansTab, $ansMod,) = $object::answerTableAndModuleFor($attribute);
            if ($ansTab) {
                $className = AfwStringHelper::tableToClass($ansTab);
                AfwAutoLoader::addModule($ansMod);
                $objectITEM = new $className();
                // $objectITEM->setMyDebugg($object->MY_DEBUG);
                if ($structure['ITEM']) {
                    $item_oper = $structure['ITEM_OPER'];
                    $item_name = $structure['ITEM'];
                    $this_id = $object->getAfieldValue(
                        $object->getPKField()
                    );

                    if ($item_oper) {
                        $objectITEM->where("me.$item_name $item_oper '$this_id' ");
                    } else {
                        $objectITEM->where("me.$item_name = '$this_id' ");
                    }
                }
                if ($structure['WHERE']) {
                    $sql_where = $object->decodeText($structure['WHERE']);
                    $objectITEM->where($sql_where);
                }
                /* obsolete since v3.0
                            format can not be used for SQL where
                            if($format and ($format!="IMPLODE")) {
                                $objectITEM->where($format);
                            }
                            */

                if (!$structure['LOGICAL_DELETED_ITEMS_ALSO']) {
                    $objectITEM->select($objectITEM->fld_ACTIVE(), 'Y');
                }
                $objectITEM->debugg_tech_notes = "before load Many for Items of attribute : $attribute";
                if ($max_items) {
                    $limit_loadMany = $max_items;
                } else {
                    $limit_loadMany = '';
                }

                $return = $objectITEM->loadMany($limit_loadMany, $structure['ORDER_BY']);
                // if($attribute=="requestList") die("sql_for_loadmany of $attribute = ".$object->debugg_sql_for_loadmany." returned list => ".var_export($return,true));

                // if(!$structure["NO-CACHE"]) $object->gotIte msCache[$attribute] = $return;
            } else {
                throw new AfwRuntimeException(
                    'Check if ANSWER property is defined for attribute ' .
                        $attribute .
                        ' having type ITEMS in DB_STRUCTURE of table ' .
                        $table_name,
                    $call_method
                );
            }
        }

        return $return;
    }


    public static final function formatSHORTCUT($object, $attribute, $what, $format, $table_name, $integrity, $structure, $call_method)
    {
        //if($attribute=="skill_type_id") throw new AfwRuntimeException("$attribute is SHORTCUT");
        //if($object->MY_DEBUG) AFWDebugg::log("Case SHORTCUT");
        $report_arr = [];
        $forced_value = $object->getAfieldValue($attribute);
        $report_arr[] = "forced_value=$forced_value";
        $default_value = $structure['DEFAULT'];
        if (!$default_value) {
            $default_value = '';
        }
        if (
            isset($structure['SHORTCUT']) &&
            $structure['SHORTCUT']
        ) {
            $attribute_shortcut = $structure['SHORTCUT'];
        }
        //die("shortcut 2 = ".$attribute_shortcut);

        // if($attribute_shortcut=="skill_type_id") throw new AfwRuntimeException("$attribute forced_value = $forced_value");
        if (strpos($attribute_shortcut, '.') !== false) {
            //if($object->MY_DEBUG) AFWDebugg::log("Object $attribute exist");
            $fields = explode('.', $attribute_shortcut);
            $sc_cat = $structure['SHORTCUT-CATEGORY'];
            $sc_cat_arr = explode('.', $sc_cat);
            $count = count($fields);
            if ($count > 1) {
                //die("shortcut 3 = ".var_export($fields,true));
                //if($object->MY_DEBUG) AFWDebugg::log("count field = $count");
                if ($sc_cat_arr[0] == "FORMULA")
                    $object = $object->calc($fields[0], true, "object");
                else
                    $object = $object->het($fields[0], '', $optim_lookup = false); // optim=false mandatory because in shortcut we need to load object to get next attribute of shortcut 
                // (just a decode is not enough)
                if ($object) {
                    if (!is_object($object)) {
                        throw new AfwRuntimeException("$object returned by the shortcut[$attribute_shortcut] the shortcut item [" . $fields[0] . "] is not an object");
                    }
                    $report_arr[] =
                        'fields[0]=' .
                        $object->getDisplay('ar');
                    // if($attribute_shortcut=="goal.system_id") die("shortcut($attribute_shortcut) object 0 = ".var_export($object,true));
                    for ($i = 1; $i < $count - 1; $i++) {
                        if ($object === null) {
                            if ($integrity) {
                                throw new AfwRuntimeException(
                                    'Impossible to get [' .
                                        $fields[$i] .
                                        "] à cause d'une valeur NULL of object " .
                                        $fields[$i - 1] .
                                        ", veuillez vérifier attribute " .
                                        $attribute .
                                        ' de type SHORTCUT.'
                                );
                            } else {
                                break;
                            }
                        } else {
                            if ($object->MY_DEBUG and false) {
                                AFWDebugg::log(
                                    'object[' .
                                        ($i - 1) .
                                        ']'
                                );
                            }
                            if ($object->MY_DEBUG and false) {
                                AFWDebugg::log(
                                    $object,
                                    true
                                );
                            }
                            if ($object->MY_DEBUG and false) {
                                AFWDebugg::log(
                                    "befor get fields[$i]=" .
                                        $fields[$i]
                                );
                            }

                            if ($sc_cat_arr[$i] == "FORMULA")
                                $object = $object->calc($fields[$i], true, "object");
                            else
                                $object = $object->het($fields[$i]);

                            if ($object) {
                                $report_arr[] =
                                    "fields[$i]=" .
                                    $object->getDisplay(
                                        'ar'
                                    );
                            }
                        }
                    }
                    //die("short cut analyse for attribute $attribute = ".var_export($object,true));
                    if ($object === null) {
                        if ($object->MY_DEBUG and false) {
                            AFWDebugg::log(
                                'Object is NULL'
                            );
                        }
                        if ($integrity) {
                            throw new AfwRuntimeException(
                                'Impossible to get [' .
                                    $fields[$count - 1] .
                                    "] à cause d'une valeur NULL of object " .
                                    $fields[$count - 2] .
                                    ", veuillez vérifier attribute " .
                                    $attribute .
                                    ' de type SHORTCUT.',
                                $call_method
                            );
                        } else {
                            switch (strtolower($what)) {
                                case 'object':
                                    $return = null;
                                    break;
                                case 'value':
                                case 'decodeme':
                                    $return = $forced_value
                                        ? $forced_value
                                        : $default_value;

                                case 'report':
                                    $return = implode(
                                        "\n<br>",
                                        $report_arr
                                    );
                                    break;
                                    break;
                            }
                        }
                    } else {
                        if ($object->MY_DEBUG and false) {
                            AFWDebugg::log('Object exist');
                        }

                        if ($what == 'report') {
                            $return = $object->get(
                                $fields[$count - 1],
                                'value',
                                $format,
                                $integrity
                            );
                            $report_arr[] = "last : fields[$count-1]=" .
                                $fields[$count - 1] .
                                ' => ' .
                                $return;
                            $return = implode("\n<br>", $report_arr);
                        } else {

                            $return = $object->get(
                                $fields[$count - 1],
                                $what,
                                $format,
                                $integrity
                            );

                            $report_arr[] =
                                "get(fields[$count-1]=" .
                                $fields[$count - 1] .
                                " ,$what) = " .
                                $return;
                        }

                        // if(($fields[0]=="course_session") and ($fields[1]=="attendanceList"))
                        // if(($fields[0]=="cher_id") and ($fields[1]!="emp_num") and ($fields[1]!="orgunit_name") and ($fields[1]!="orgunit_id") and ($fields[1]!="orgunit_id")) 
                        // throw new AfwRuntimeException("fields=".implode("|\n<br>|",$fields)."\n<br> report_arr=".implode("\n<br>",$report_arr)."\n<br> >>> rafik debugg :: get(".$fields[$count-1].", $what, $format) = $return");
                        if ($object->MY_DEBUG and false) {
                            AFWDebugg::log($return, true);
                        }
                    }
                } else {
                    if ($integrity) {
                        throw new AfwRuntimeException(
                            'Impossible to get [' .
                                $fields[1] .
                                "] à cause d'une valeur NULL of object " .
                                $fields[0] .
                                ", veuillez vérifier attribute " .
                                $attribute .
                                ' de type SHORTCUT. ' .
                                $call_method
                        );
                    } else {
                        $return = $forced_value
                            ? $forced_value
                            : $default_value;
                        //if($default_value and ($default_value==$return)) die("rafik test 0013");
                    }
                }
            } else {
                throw new AfwRuntimeException(
                    "Property SHORTCUT of attribute " .
                        $attribute .
                        ' de la table ' .
                        $table_name .
                        " doit avoir plus d'un element.",
                    $call_method
                );
            }
        } else {
            throw new AfwRuntimeException(
                "Property SHORTCUT non définie of attribute " .
                    $attribute .
                    ' dans DB_STRUCTURE de la table ' .
                    $table_name .
                    '.',
                $call_method
            );
        }

        return $return;
    }

    public static final function formatReturnedValue($object, $attribute, $lang, $structure, $return, $what, $format, $attribute_type, $integrity, $this_debugg)
    {
        //if($attribute=="customer_id") throw new AfwRuntimeException("formatting Returned Value $return");
        $attr_sup_categ = $structure['SUPER_CATEGORY'];
        $attr_categ = $structure['CATEGORY'];
        $attr_scateg = $structure['SUB-CATEGORY'];
        $case = "";
        if (strtolower($what) == 'value') {
            if ($return and $return instanceof AFWObject) {
                $return = $return->getId();
                $case = "id value";
            }

            if (
                $attr_categ == 'ITEMS' or
                $attr_scateg == 'ITEMS' or
                $attr_sup_categ == 'ITEMS'
            ) {
                $return_arr = $return;
                $return = '';
                foreach ($return_arr as $return_item) {
                    $return .= ',' . $return_item->getId();
                }
                if ($return) {
                    $return .= ',';
                }
                $case = "ITEMS value";
            }
        } elseif (strtolower($what) == 'decodeme') {
            if (
                $attr_categ == 'ITEMS' or
                $attr_scateg == 'ITEMS' or
                $attr_sup_categ == 'ITEMS'
            ) 
            {
                $format = strtolower($format);

                $arr_items_decoded = [];
                foreach ($return as $return_item) {
                    $arr_items_decoded[] = $return_item->getDisplay($lang);
                }


                if ($format == 'implode') {
                    $return = implode(',', $arr_items_decoded);
                } else {
                    $return = $arr_items_decoded;
                }
                $case = "ITEMS decodeme format=$format";
            } elseif ($return and $return instanceof AFWObject) {
                $return = $return->getDisplay($lang);
                $case = "instanceof AFWObject so ->getDisplay($lang)";
            } else {

                if (!isset($return)) {
                    //if(($attribute=="homework")) die("what=$what, rafik entered in non implemented zone of decode of attribute $attribute formula log = $this_debugg_formula_log  returned : [return=$return, formatted=$formatted, return_formatted=$return_formatted] ");
                    /*
                    if (($attribute == "xxxx")) 
                    {
                        $attribute_value = $object->getVal($attribute);
                        throw new AfwRuntimeException("attribute-action to be not implemented this_debugg_formula_log=$this_debugg attr_categ=$attr_categ attribut=$attribute, attribute_value=$attribute_value, format=$format, what=$what, gettype=" . $attribute_type);
                    }*/
                }
            }

            $unit = $structure['UNIT'];
            $hide_unit = $structure['DISPLAY_HIDE_UNIT'];
            if ($unit and $return and !$hide_unit) {
                $return .= ' ' . $unit;
            }
        } else {
            if ($integrity and !isset($return)) {
                $suggest = "";
                if ($attr_categ == "FORMULA") $suggest = "often this happen when you dont call to return \AfwFormulaHelper::calculateFormulaResult($object,\$attribute, \$what) on your getFormuleResult method";
                throw new AfwRuntimeException(
                    "Erreur : no-return defined for get : what=$what,attribut=$attribute, format=$format, attr_categ=$attr_categ ($suggest), gettype=" .
                        $attribute_type .
                        ' STRUCTURE = ' .
                        var_export($structure, true)
                );
            }
        }
        $link_url = $structure['LINK-URL'];
        $link_css_class = $structure['LINK-CSS'];
        if (!$link_css_class) {
            $link_css_class = 'nice_link';
        }

        $target = '';
        $popup_t = '';

        

        if ($link_url and $return != '' and $format != 'NO-URL') {
            $link_url = $object->decodeText($link_url, '', false);
            $return = "<a class='$link_css_class' $target href='$link_url&popup=$popup_t'>$return</a>";
            $case .=  " ->object->decodeText(link_url)";
        }

        // if(is_string($return) and AfwStringHelper::stringContain($return,"mahamd_78.6@icloud.com")) 
        // if($attribute=="customer_id") throw new AfwRuntimeException("formatting Returned Value case=$case");

        return $return;
    }


    /**
     * decodeAnswerOfAttribute
     * Return Value of Answer Type Field
     * @param string $attribute
     * @param string $field_value
     */
    public static final function decodeAnswerOfAttribute($object, $attribute, $field_value)
    {
        if (!$field_value) return $field_value;
        $object->debugg_last_attribute = $attribute;
        // $call_method = "getAnswer(attribute = $attribute, field_value = $field_value)";
        $structure = AfwStructureHelper::getStructureOf($object, $attribute);
        $answerTable = $structure['ANSWER'];
        $attrtype = $object->getTypeOf($attribute);
        if ($object->MY_DEBUG and false) {
            AFWDebugg::log("[answerTable=$answerTable  , attrtype=$attrtype]");
        }
        $return = false;
        if ($answerTable) {
            if ($attrtype == 'ANSWER') {
                $fc = substr($answerTable, 0, 1);
                if ($fc == ':') {
                    $methodDecode = 'decode' . ucfirst($attribute);
                    return $object->$methodDecode($field_value);
                }

                $answer_id = $structure['MY_PK'] ? $structure['MY_PK'] : 'ANSWER_ID';
                $value_fr = $structure['MY_VAL'] ? $structure['MY_VAL'] : 'VALUE_FR';
                $query = 'select ' . $value_fr . " from " . $object::_prefix_table($answerTable) . " where " . $answer_id . " = '" . $field_value . "'";
                $module_server = $object->getModuleServer();
                $return = AfwDatabase::db_recup_value($query, true, true, $module_server);
            }

            if ($attrtype == 'FK' or $attrtype == 'YN' or $attrtype == 'ENUM' or $attrtype == 'MFK') {
                $return = AfwFormatHelper::decodeSimulatedFieldValue($object, $attribute, $field_value);
            }
        }

        $return = $return === false ? $field_value : $return;
        return $return;
    }

    public static function decodeObjectList($objList, $what, $lang = "ar", $implodeSeparator = ",")
    {
        $returnList = [];
        foreach ($objList as $idobj => $obj) {
            if ($what == "value") $returnList[] = $obj ? $obj->id : 0;
            elseif ($what == "decodeme")  $returnList[] = $obj ? $obj->getDisplay($lang) : "";
            else $returnList[$obj->id] = $obj;
        }
        if (($what == "value") and $implodeSeparator) {
            return $implodeSeparator . implode($implodeSeparator, $returnList) . $implodeSeparator;
        }
        return $returnList;
    }

    public static function decode_result($obj, $what, $lang = "ar")
    {
        if ($what == "value") $return = $obj ? $obj->id : 0;
        elseif ($what == "decodeme")  $return = $obj ? $obj->getDisplay($lang) : "";
        else $return = $obj;

        return $return;
    }

    public static function pbm_result($err, $info, $warn = null, $sep = "<br>\n", $tech = "")
    {
        // die(" 1 ==> pbm_result($err, $info, $warn) warn = ".var_export($warn,true));
        if (is_array($err)) $err = implode($sep, $err);
        if (is_array($info)) $info = implode($sep, $info);
        if (is_array($warn)) $warn = implode($sep, $warn);
        if (is_array($tech)) $tech = implode($sep, $tech);

        // die(" 2 ==> pbm_result($err, $info, $warn)");

        return array($err, $info, $warn, $tech);
    }


    public static function getCategorizedAttribute($object, $attribute, $attribute_category, $attribute_type, $structure, $what, $format, $integrity, $max_items, $lang, $call_method = "")
    {
        /*
        if(($attribute=="customer_id"))
        {
            die("rafik shoof getCategorizedAttribute has been called for $attribute");
        }
        
        
        if (!$structure['NO-CACHE'] and isset($object->gotItemCache[$attribute][$what])) {

            $return = $object->gotItemCache[$attribute][$what];
            $log_getter = 'return from gotItemCache = ' . var_export($return, true);
            if ($attribute == 'requestList0000') {
                die($log_getter);
            } else $afw_getter_log[] = "$log_getter";
        }*/
        $return = null;
        if (!$return) {
            $this_id = $object->getId();

            $b_abstract = false;
            
            switch ($attribute_category) {
                case 'ITEMS':
                    $return = AfwFormatHelper::formatITEMS($object, $attribute, $structure, $object->getMyTable(), $call_method, $max_items);
                    break;

                case 'FORMULA':
                    global $lang;
                    if (!$lang) $lang = 'ar';
                    $return = AfwFormulaHelper::executeFormulaAttribute($object, $attribute, NULL, $lang, $what);
                    $return_isset = isset($return);
                    $this_debugg = "AfwFormulaHelper::executeFormulaAttribute(this, $attribute, NULL, $lang, $what) = [return=$return/isset=$return_isset]";
                    $attribute_value = $return;

                    break;
                case 'VIRTUAL':
                    $b_abstract = true;
                    if (AfwLoadHelper::cacheManagement($object)) {
                        $object->OBJECTS_CACHE[$attribute] = $object->getVirtual($attribute, $what, $format);
                    }

                    break;
                case 'SHORTCUT':
                    $return = AfwFormatHelper::formatSHORTCUT($object, $attribute, $what, $format, $object->getMyTable(), $integrity, $structure, $call_method);
                    break;
            }
            if ((!$structure['NO-CACHE']) and $return) {
                // $object->gotItemCache[$attribute][$what] = $return;
                // die("attribute=$attribute, attribute_category=$attribute_category set in gotItemCache = " . var_export($object->gotItemCache,true));
            }
        }

        $return = AfwFormatHelper::formatReturnedValue($object, $attribute, $lang, $structure, $return, $what, $format, $attribute_type, $integrity, $this_debugg);

        return $return;
    }
}
