<?php

class AfwStatsHelper
{

    public static function prepareShowRow($myClassInstance, $list_cols, $row)
    {
        foreach ($list_cols as $col_index => $group_col_item) {
            $group_col          = $group_col_item['COLUMN'];
            $col_display_format = $group_col_item['DISPLAY-FORMAT'];
            if ($col_display_format == 'show') {
                $col_display_format = 'decode';
            }

            $col_val = $row[$group_col];
            if ($col_display_format == 'val') {
                $group_col_display = $col_val;
            } elseif ($col_display_format == 'decode') {
                $group_col_display = AfwLoadHelper::decodeFkAttribute($myClassInstance, $group_col, $col_val);
            }

            $group_col_show_arr[$group_col]            = $group_col_display;
            $group_col_show_arr[$group_col . '_value'] = $col_val;
        }

        return $group_col_show_arr;
    }
    public static function statsDataToStatsCrossArrays($myClassInstance, $statsData, $config_stats_display_cols, $config_cross_stats_cols, $config_group_cols, $group_sep, $config_stats_options, $lang = "ar", $curropt = "")
    {
        $stats_data_arr         = [];
        $footer_total_arr       = [];
        $footer_sum_title_arr   = [];
        $stat_trad              = [];
        $stats_big_header       = [];




        $big_col_key = $config_cross_stats_cols["bigcol"];
        $big_col_is_formula = $config_cross_stats_cols["bigcolisformula"];
        $col_key = $config_cross_stats_cols["col"];
        $row_key = $config_cross_stats_cols["row"];
        $val_key = $config_cross_stats_cols["val"];
        $big_color = $config_cross_stats_cols["big_color"];
        if (!$big_color) $big_color = 'blue';

        $footer_sum = $config_stats_display_cols[$val_key]['ROW_SUM'];
        if (!$footer_sum) $footer_sum = $config_stats_display_cols[$val_key]['FOOTER_SUM'];

        $col_sum = $config_stats_display_cols[$val_key]['COL_SUM'];

        $stat_trad[$row_key] = $myClassInstance->transStatsAttribute($row_key, $lang);
        $stat_trad["cross_total"] = $myClassInstance->tm("Total", $lang);
        $stats_big_header[] = ['col_span' => 2, 'title' => '', 'color' => $big_color];
        $footer_sum_title_arr[$row_key]            = $stat_trad["cross_total"];


        $big_category = 0;
        $big_category_display = '';
        $big_category_col_span = 0;
        $big_obj_color = '';

        $objColList = AfwLoadHelper::getAnswerTable($myClassInstance, $col_key, $lang);
        /**
         * @var AFWObject $objColItem
         */
        foreach ($objColList as $objColItemId => $objColItem) {
            if ($objColItem->id and $objColItem->getShortDisplay($lang)) {
                $cross_col = "cross_col_" . $objColItemId;
                $stat_trad[$cross_col] = $objColItem->getShortDisplay($lang);
                $bigDisplay = null;
                $bigValue = null;
                if ($big_col_key) {
                    $bigDisplay = $objColItem->decode($big_col_key, '', false, $lang);
                    $bigValue = $big_col_is_formula ? $objColItem->calc($big_col_key) : $objColItem->getVal($big_col_key);
                } 
                if ($bigDisplay and $bigValue) {
                    if (($bigValue != $big_category)) {
                        if ($big_category and $big_category_display) { // previous group exists close it and create new

                            $stats_big_header[] = ['col_span' => $big_category_col_span, 'title' => $big_category_display, 'color' => $big_obj_color];
                            $big_category_col_span = 0;
                        }
                        $big_obj_color = $myClassInstance->colorOf($big_col_key, $bigValue);
                        $big_category_display = $bigDisplay;
                        $big_category = $bigValue;
                    }
                    $big_category_col_span++;
                }
            }
        }

        if ($big_category_col_span and $big_category_display) {
            $stats_big_header[] = ['col_span' => $big_category_col_span, 'title' => $big_category_display, 'color' => $big_obj_color];
        }





        $bloc_col_end    = [];
        $url_to_show_arr = [];


        foreach ($statsData as $stats_curr_row => $statsRow) {
            $group_col_show_arr = self::prepareShowRow($myClassInstance, $config_group_cols, $statsRow);
            $row_id = $group_col_show_arr[$row_key . "_value"];
            $col_id = $group_col_show_arr[$col_key . "_value"];
            if (!$stats_data_arr[$row_id]) {
                $stats_data_arr[$row_id] = array();
                $stats_data_arr[$row_id][$row_key] = $group_col_show_arr[$row_key];
            }
            reset($objColList);
            foreach ($objColList as $objColItemId => $objColItem) {
                if (($col_id == $objColItemId) and $objColItem->id and $objColItem->getShortDisplay($lang)) {
                    $cross_col = "cross_col_" . $objColItemId;
                    $stats_value = $stats_data_arr[$row_id][$cross_col] = $statsRow[$val_key];
                    // die("col_key=$col_key row_key=$row_key val_key=$val_key statsRow[$val_key] = $stats_value statsRow=" . var_export($statsRow, true) . "<br> stats_data_arr=" . var_export($stats_data_arr, true));
                    if ($footer_sum) {
                        if (! $footer_total_arr[$cross_col]) {
                            $footer_total_arr[$cross_col] = 0;
                        }
                        $footer_total_arr[$cross_col] += $stats_value;
                        // $footer_total_arr[ 'log'.$show_name ] .= '+'.$stats_data_arr[ $stats_curr_row ][ $show_name ];
                    }

                    if ($col_sum) {
                        if (!$stats_data_arr[$row_id]["cross_total"]) {
                            $stats_data_arr[$row_id]["cross_total"] = 0;
                        }
                        $stats_data_arr[$row_id]["cross_total"] += $stats_value;
                    }
                }
            }
        }

        // die("statsData=" . var_export($statsData, true) . "<br> group_col_show_arr=" . var_export($group_col_show_arr, true) . "<br> stats_data_arr=" . var_export($stats_data_arr, true));


        return [$stat_trad, $stats_data_arr, $stats_big_header, $footer_sum_title_arr, $footer_total_arr, $bloc_col_end, $url_to_show_arr];
    }


    public static function statsDataToStatsArrays($myClassInstance, $statsData, $config_stats_display_cols, $config_group_cols, $group_sep, $config_stats_options, $lang = "ar", $curropt = "")
    {
        $stats_data_arr         = [];
        $stats_row_code_arr     = [];
        $footer_total_arr       = [];
        $footer_sum_title_arr   = [];
        $stat_trad              = [];

        foreach ($statsData as $stats_curr_row => $statsRow) {
            $group_value = '';
            unset($group_col_show_arr);
            $group_col_show_arr = [];
            foreach ($config_group_cols as $group_col_index => $group_col_item) {
                $group_col          = $group_col_item['COLUMN'];
                $col_display_format = $group_col_item['DISPLAY-FORMAT'];
                if ($col_display_format == 'show') {
                    $col_display_format = 'decode';
                }

                $group_col_val = $statsRow[$group_col];

                if ($col_display_format == 'val') {
                    $group_col_display = $group_col_val;
                } elseif ($col_display_format == 'decode') {
                    $group_col_display = AfwLoadHelper::decodeFkAttribute($myClassInstance, $group_col, $group_col_val);
                }

                $group_col_show_arr[$group_col]            = $group_col_display;
                $group_col_show_arr[$group_col . '_value'] = $group_col_val;
                $group_value .= $group_sep . $group_col_val;
            }

            $stats_row_code_arr[$group_value] = $stats_curr_row;

            foreach ($config_group_cols as $group_col_index => $group_col_item) {
                $group_col                                   = $group_col_item['COLUMN'];
                $footer_sum_title_arr[$group_col]            = $group_col_item['FOOTER_SUM_TITLE'];
                $stats_data_arr[$stats_curr_row][$group_col] = $group_col_show_arr[$group_col];
                $stats_data_arr[$stats_curr_row][$group_col . '_value'] = $group_col_show_arr[$group_col . '_value'];
                if (! $stat_trad[$group_col]) {
                    $nom_col_short  = "$group_col.stat";
                    $trad_col_short = $myClassInstance->translate($nom_col_short, $lang);
                    if ($trad_col_short == $nom_col_short) {
                        $nom_col_short  = "$group_col.short";
                        $trad_col_short = $myClassInstance->translate($nom_col_short, $lang);
                    }

                    if ($trad_col_short == $nom_col_short) {
                        $stat_trad[$group_col] = $myClassInstance->translate($group_col, $lang);
                    } else {
                        $stat_trad[$group_col] = $trad_col_short;
                    }
                }
            }

            $bloc_col_end    = [];
            $url_to_show_arr = [];

            foreach ($config_stats_display_cols as $config_stats_display_col_index => $config_stats_display_col_item) {
                $stats_display_col = $config_stats_display_col_item['COLUMN'];

                $myobj_struct = AfwStructureHelper::getStructureOf($myClassInstance, $stats_display_col);
                $unit         = $myobj_struct['UNIT'];

                $footer_sum = $config_stats_display_col_item['ROW_SUM'];
                if (!$footer_sum) $footer_sum = $config_stats_display_col_item['FOOTER_SUM'];

                $show_unit_header = $config_stats_display_col_item['SHOW-UNIT-HEADER'];
                if ($show_unit) {
                    $show_unit_val = ' ' . $unit;
                } else {
                    $show_unit_val = '';
                }

                $show_unit = $config_stats_display_col_item['SHOW-UNIT'];

                $show_name = $config_stats_display_col_item['SHOW-NAME'];
                if (! $show_name) {
                    $show_name = $stats_display_col;
                }

                $url_to_show                 = $config_stats_display_col_item['URL'];
                $url_to_show_arr[$show_name] = $url_to_show;

                if ((!$curropt) or $config_stats_options[$curropt][$show_name]) {

                    $bloc_col_end[$show_name]         = $config_stats_display_col_item['BLOC-COL-END'];
                    $footer_sum_title_arr[$show_name] = $config_stats_display_col_item['FOOTER_SUM_TITLE'];

                    $stats_display_col_val = $statsRow[$stats_display_col];

                    if (! $stat_trad[$show_name]) {
                        $nom_col_short  = "$show_name.stat";
                        $trad_col_short = $myClassInstance->translate($nom_col_short, $lang);
                        if ($trad_col_short == $nom_col_short) {
                            $nom_col_short  = "$show_name.short";
                            $trad_col_short = $myClassInstance->translate($nom_col_short, $lang);
                        }
                        if ($trad_col_short == $nom_col_short) {
                            $stat_trad[$show_name] = $myClassInstance->translate($show_name, $lang);
                        } else {
                            $stat_trad[$show_name] = $trad_col_short;
                        }
                    }

                    $stats_data_arr[$stats_curr_row][$show_name] = $stats_display_col_val;

                    if ($footer_sum) {
                        if (! $footer_total_arr[$show_name]) {
                            $footer_total_arr[$show_name] = 0;
                        }

                        $footer_total_arr[$show_name] += $stats_display_col_val;
                        // $footer_total_arr[ 'log'.$show_name ] .= '+'.$stats_data_arr[ $stats_curr_row ][ $show_name ];

                    }
                }
            }
        }

        return [$stat_trad, $stats_data_arr, $footer_sum_title_arr, $footer_total_arr, $bloc_col_end, $url_to_show_arr];
    }



    public static function objectListToStatsArrays($myObj_list, $config_stats_display_cols, $config_group_cols, $group_sep, $config_stats_options, $lang = "ar", $curropt = "")
    {
        $sum_by_col_arr         = [];
        $count_by_col_arr       = [];

        $stats_row_code_arr_len = 0;
        foreach ($myObj_list as $myObj_id => $myObj_item) {
            $group_value = '';
            unset($group_col_show_arr);
            $group_col_show_arr = [];
            foreach ($config_group_cols as $group_col_index => $group_col_item) {
                $group_col          = $group_col_item['COLUMN'];
                $col_display_format = $group_col_item['DISPLAY-FORMAT'];
                $group_col_val      = $myObj_item->getVal($group_col);
                $group_col_dec      = $myObj_item->decode($group_col);
                $group_col_shw      = $myObj_item->showAttribute($group_col);
                if ($col_display_format == 'val') {
                    $group_col_display = $group_col_val;
                } elseif ($col_display_format == 'decode') {
                    $group_col_display = $group_col_dec;
                } elseif ($col_display_format == 'show') {
                    $group_col_display = $group_col_shw;
                }

                $group_col_show_arr[$group_col]            = $group_col_display;
                $group_col_show_arr[$group_col . '_value'] = $group_col_val;
                $group_value .= $group_sep . $group_col_val;
            }

            if (! isset($stats_row_code_arr[$group_value])) {
                $stats_row_code_arr[$group_value] = $stats_row_code_arr_len;
                $stats_row_code_arr_len++;
            }

            $stats_curr_row = $stats_row_code_arr[$group_value];

            foreach ($config_group_cols as $group_col_index => $group_col_item) {
                $group_col                                   = $group_col_item['COLUMN'];
                $footer_sum_title_arr[$group_col]            = $group_col_item['FOOTER_SUM_TITLE'];
                $stats_data_arr[$stats_curr_row][$group_col] = $group_col_show_arr[$group_col];
                $stats_data_arr[$stats_curr_row][$group_col . '_value'] = $group_col_show_arr[$group_col . '_value'];

                if (! $stat_trad[$group_col]) {
                    $nom_col_short  = "$group_col.stat";
                    $trad_col_short = $myObj_item->translate($nom_col_short, $lang);
                    if ($trad_col_short == $nom_col_short) {
                        $nom_col_short  = "$group_col.short";
                        $trad_col_short = $myObj_item->translate($nom_col_short, $lang);
                    }
                    if ($trad_col_short == $nom_col_short) {
                        $stat_trad[$group_col] = $myObj_item->translate($group_col, $lang);
                    } else {
                        $stat_trad[$group_col] = $trad_col_short;
                    }
                }
            }

            $bloc_col_end    = [];
            $url_to_show_arr = [];
            foreach ($config_stats_display_cols as $config_stats_display_col_index => $config_stats_display_col_item) {
                $stats_display_col = $config_stats_display_col_item['COLUMN'];

                $myobj_item_struct = AfwStructureHelper::getStructureOf($myObj_item, $stats_display_col);
                $unit              = $myobj_item_struct['UNIT'];
                $gf                = $config_stats_display_col_item['GROUP-FUNCTION'];
                $footer_sum        = $config_stats_display_col_item['FOOTER_SUM'];

                $show_unit_header = $config_stats_display_col_item['SHOW-UNIT-HEADER'];
                if ($show_unit) {
                    $show_unit_val = ' ' . $unit;
                } else {
                    $show_unit_val = '';
                }

                $show_unit = $config_stats_display_col_item['SHOW-UNIT'];

                $show_name = $config_stats_display_col_item['SHOW-NAME'];
                if (! $show_name) {
                    $show_name = $stats_display_col;
                }

                $url_to_show                 = $config_stats_display_col_item['URL'];
                $url_to_show_arr[$show_name] = $url_to_show;

                if ((! $curropt) or $config_stats_options[$curropt][$show_name]) {

                    $bloc_col_end[$show_name]         = $config_stats_display_col_item['BLOC-COL-END'];
                    $footer_sum_title_arr[$show_name] = $config_stats_display_col_item['FOOTER_SUM_TITLE'];

                    if ($config_stats_display_col_item['COLUMN_IS_FORMULA']) {
                        $stats_display_col_val = $myObj_item->calc($stats_display_col);
                    } else {
                        $stats_display_col_val = $myObj_item->getVal($stats_display_col);
                    }

                    if (! $stat_trad[$show_name]) {
                        $nom_col_short  = "$show_name.stat";
                        $trad_col_short = $myObj_item->translate($nom_col_short, $lang);
                        if ($trad_col_short == $nom_col_short) {
                            $nom_col_short  = "$show_name.short";
                            $trad_col_short = $myObj_item->translate($nom_col_short, $lang);
                        }
                        if ($trad_col_short == $nom_col_short) {
                            $stat_trad[$show_name] = $myObj_item->translate($show_name, $lang);
                        } else {
                            $stat_trad[$show_name] = $trad_col_short;
                        }
                    }
                    $new_val = 0;
                    if ($gf == 'sum') {
                        if (! $sum_by_col_arr[$group_value][$stats_display_col]) {
                            $sum_by_col_arr[$group_value][$stats_display_col] = 0;
                        }

                        $new_val = $stats_display_col_val;

                        $sum_by_col_arr[$group_value][$stats_display_col] += $new_val;

                        // if ( !$new_val ) die( "for $myObj_item value of col $stats_display_col = $new_val, so stats sum_by_col_arr[$group_value][$stats_display_col] = ".$sum_by_col_arr[ $group_value ][ $stats_display_col ] );
                        $stats_data_arr[$stats_curr_row][$show_name] = $sum_by_col_arr[$group_value][$stats_display_col] . $show_unit_val;
                    }

                    if ($gf == 'count') {
                        if (! $count_by_col_arr[$group_value][$stats_display_col]) {
                            $count_by_col_arr[$group_value][$stats_display_col] = 0;
                        }

                        $new_val = 1;
                        $count_by_col_arr[$group_value][$stats_display_col]++;
                        $stats_data_arr[$stats_curr_row][$show_name] = $count_by_col_arr[$group_value][$stats_display_col];
                    }

                    if ($footer_sum) {
                        if (! $footer_total_arr[$show_name]) {
                            $footer_total_arr[$show_name] = 0;
                        }

                        $footer_total_arr[$show_name] += $new_val;
                        // $footer_total_arr[ 'log'.$show_name ] .= '+'.$stats_data_arr[ $stats_curr_row ][ $show_name ];

                    }
                }
            }
        }

        return [$stat_trad, $stats_data_arr, $footer_sum_title_arr, $footer_total_arr, $bloc_col_end, $url_to_show_arr];
    }

    /**
     * @param AFWObject $myClassInstance
     */

    public static function modeStatsInitialization($myClassInstance, $stats_config, $config_stats_options, $lang = "ar", $curropt = "")
    {
        $myClass = get_class($myClassInstance);
        $stats_data_from    = $stats_config['STATS_DATA_FROM'];
        $config_sql_group_by    = $stats_config['SQL_GROUP_BY'];
        $config_stats_display_cols = $stats_config['DISPLAY_COLS'];
        $group_cols_arr = [];
        $config_group_cols      = $stats_config['GROUP_COLS'];
        $config_cross_stats_cols      = $stats_config['CROSS_STATS_COLS'];

        foreach ($config_group_cols as $group_col_index => $group_col_item) {
            $group_cols_arr[] = $group_col_item['COLUMN'];
        }
        $group_cols_list_sql = implode(',', $group_cols_arr);
        $group_sep = $stats_config['GROUP_SEP'];
        if (! $group_sep) {
            $group_sep = '/';
        }

        $config_stats_formula_cols = $stats_config['FORMULA_COLS'];

        $filter_arr = [];
        $sfilter_list = $stats_config["SFILTER"];
        if (!$sfilter_list) $sfilter_list = [];
        foreach ($sfilter_list as $filter_col => $filter) {
            if (isset($_REQUEST[$filter_col])) $filter_arr[$filter_col] = $_REQUEST[$filter_col];
        }
        $case = "None";
        if ($stats_data_from) {

            $params_list = $stats_config["PARAMS"];
            if (!$params_list) $params_list = [];
            $params_arr = [];
            foreach ($params_list as $param_name) {
                if (isset($_REQUEST[$param_name])) $params_arr[$param_name] = $_REQUEST[$param_name];
            }

            $dataFromClass = $stats_data_from['class'];
            $dataFromMethod = $stats_data_from['method'];
            $case = "dataFromMethod : $dataFromClass :: $dataFromMethod";
            list($stats_data_arr, $stat_trad) = $dataFromClass::$dataFromMethod($params_arr, $filter_arr);
        } elseif ($config_sql_group_by) {
            $arrAggregFunctions = [];
            foreach ($config_stats_display_cols as $display_col) {
                $aggCol                      = $display_col['COLUMN'];
                $aggFunction                 = $display_col['GROUP-FUNCTION'];
                $aggSqlFormula               = $display_col['SQL_FORMULA'];
                if (!$aggSqlFormula)
                    $aggSqlFormula           = "$aggFunction($aggCol)";
                $arrAggregFunctions[$aggCol] = $aggSqlFormula;
            }
            list($sql, $statsData) = AfwSqlHelper::multipleAggregFunction($myClassInstance, $arrAggregFunctions, $group_cols_list_sql, true, true, $filter_arr);
            $case = "multipleAggregFunction";
            // die( "sql=$sql => res=".var_export( $statsData, true ) );
            $stats_big_header = null;
            if ($config_cross_stats_cols) {
                list($stat_trad, $stats_data_arr, $stats_big_header, $footer_sum_title_arr, $footer_total_arr, $bloc_col_end, $url_to_show_arr) =
                    self::statsDataToStatsCrossArrays($myClassInstance, $statsData, $config_stats_display_cols, $config_cross_stats_cols, $config_group_cols, $group_sep, $config_stats_options, $lang, $curropt);
                // die("rafik cross stat_trad=" . var_export($stat_trad, true) . ", stats_data_arr=" . var_export($stats_data_arr, true));
            } else { // standard
                list($stat_trad, $stats_data_arr, $footer_sum_title_arr, $footer_total_arr, $bloc_col_end, $url_to_show_arr) = self::statsDataToStatsArrays($myClassInstance, $statsData, $config_stats_display_cols, $config_group_cols, $group_sep, $config_stats_options, $lang, $curropt);
            }
        } else { // standard stats

            //$sqlm = $myClassInstance->getSQLMany();
            // die("rafik getSQLMany=$sqlm");
            $myObj_list = $myClassInstance->loadMany('', $group_cols_list_sql);
            // die("rafik myObj_list=".var_export($myObj_list, true));
            $case = "load-Many-Objects";
            list($stat_trad, $stats_data_arr, $footer_sum_title_arr, $footer_total_arr, $bloc_col_end, $url_to_show_arr) = self::objectListToStatsArrays($myObj_list, $config_stats_display_cols, $config_group_cols, $group_sep, $config_stats_options, $lang, $curropt);
        }

        /*************************************************************************/
        // show statistics doesn't matter the source of data is php, sql or whatever

        // preapre stat_trad and calc value for formula columns
        foreach ($stats_data_arr as $stats_curr_row => $stats_data_row) {
            foreach ($config_stats_formula_cols as $config_stats_formula_col_index => $config_stats_formula_col_item) {
                $show_name = $config_stats_formula_col_item["SHOW-NAME"];
                if ((! $curropt) or $config_stats_options[$curropt][$show_name]) {
                    $method_formula                              = $config_stats_formula_col_item["METHOD"];
                    $stats_data_arr[$stats_curr_row][$show_name] = $myClass::$method_formula($stats_data_row, $formatted = true);
                    if (! $stat_trad[$show_name]) {
                        $stat_trad[$show_name] = AfwLanguageHelper::translateStatsColumn($show_name, $myClass, $myClassInstance,  $lang);
                    }
                }
            }
        }

        return [$stat_trad, $stats_data_arr, $stats_big_header, $case, $footer_sum_title_arr, $footer_total_arr, $bloc_col_end, $url_to_show_arr];
    }


    public static function outputModeStatsHeaderAndFilterPanel($myClassInstance, $stats_config, $stats_code, $currmod, $lang = "ar", $r = 0)
    {
        $please_wait = AFWObject::gtr("PLEASE_WAIT", $lang);
        $loading = AFWObject::gtr("LOADING", $lang);
        $please_wait_loading = $please_wait . " " . $loading;
        $myClass = get_class($myClassInstance);
        $url_settings          = $stats_config['URL_SETTINGS'];
        if ($url_settings) $url_settings .= "&stc=$stats_code&stccl=$myClass&stccurrmod=$currmod";
        $stats_title = $myClassInstance->translate('stats.' . $stats_code, $lang);
        $stats_title = $myClassInstance->decodeText($stats_title, $prefix = '', $add_cotes = false, $sepBefore = '[', $sepAfter = ']');

        $stats_title = str_replace('[seturl]', $url_settings, $stats_title);

        AfwMainPage::addOutput("<h3 class='centertitle bluetitle'>$stats_title</h3>");


        if (!$myClassInstance->isLourde()) {
            $aclourde = '';
        } else {
            $aclourde = 'class="form_lourde"';
        }
        AfwMainPage::addOutput('<form name="sfilterForm" id="sfilterForm" ' . $aclourde . ' method="post" action="' . "main.php" . '">');
        $cl_short = strtolower(substr($myClassInstance->getMyClass(), 0, 10));

        AfwMainPage::addOutput('<div class="row-sfilter row row-' . $cl_short . '">');

        $myClassInstance->stats_config = $stats_config;
        if (!$stats_config["NO-FILTER"]) {
            AfwMainPage::addOutput(AfwShowHelper::showObject($myClassInstance, "HTML", "afw_template_default_sfilter.php"));
        }

        AfwMainPage::addOutput('<input type="hidden" name="cl" value="' . $myClass . '"/>');
        AfwMainPage::addOutput('<input type="hidden" name="currmod" value="' . $currmod . '"/>');
        AfwMainPage::addOutput('<input type="hidden" name="stc" value="' . $stats_code . '"/>');
        AfwMainPage::addOutput('<input type="hidden" name="r" value="' . $r . '"/>');
        AfwMainPage::addOutput('<input type="hidden" id="Main_Page" name="Main_Page" value="afw_mode_stats.php"/>');

        AfwMainPage::addOutput('<script type="text/javascript">
                $(document).ready(function() {       
                        $("#qsearch-submit-form").click(function(){
                                $(".alert-dismissable").fadeOut().remove();
                                $("#search_result_div").html(\'<div class="footer1 hzm-relative-loader-div" id="mySQLloader"><div class="relative hzm-loading-div" id="myloading">
                                ' . $please_wait_loading . '
                                </div></div>\');
                        });
                });
            
        </script>');
        $execute_btn = 'EXECUTE';
        AfwMainPage::addOutput("<div class='btn-group' role='group' aria-label='...'>
                                        <input id='sfilter-submit-form' type='submit' name='submit' class='simple-btn smallbtn fright' value='" . $myClassInstance->translate($execute_btn, $lang, true) . "'>                        
                                </div>");


        AfwMainPage::addOutput('</div>');
        AfwMainPage::addOutput('</form>');
    }


    public static function outputModeStatsFooter($myClassInstance, $stats_config, $stat_trad, $stats_data_arr, $stats_code, $footer_sum_title_arr, $footer_total_arr, $currmod, $lang = "ar")
    {
        $statsClass    = get_class($myClassInstance);
        $show_pie              = $stats_config['SHOW_PIE'];
        $pie_mode              = $stats_config['PIE_MODE'];
        if (!$pie_mode) $pie_mode = "TOTAL";
        $chart_mode              = $stats_config['CHART_MODE'];
        if (!$chart_mode) $chart_mode = "IFRAME";
        $filter                = $stats_config['FILTER'];
        $stats_bottom_help_code = "stats." . $stats_code . ".help";
        $chart_url          = $stats_config['CHART_URL'];

        $stats_bottom_help = $myClassInstance->translate($stats_bottom_help_code, $lang);
        $stats_bottom_help = $myClassInstance->decodeText($stats_bottom_help, $prefix = "", $add_cotes = false, $sepBefore = "[ ", $sepAfter = " ]");

        if ($stats_bottom_help != $stats_bottom_help_code) {
            AfwMainPage::addOutput("<div class = 'stats_bottom_help'>$stats_bottom_help</div>");
        }
        $myClass = get_class($myClassInstance);
        $settings_label = AfwLanguageHelper::translateKeyword("SETTINGS", $lang);
        $url_settings          = $stats_config['URL_SETTINGS'];
        $url_settings = "$url_settings&stc=$stats_code&stccl=$myClass&stccurrmod=$currmod";

        AfwMainPage::addOutput("<h3 class='righttitle specialtitle'><a target='_settings' href='$url_settings'>$settings_label</a></h3>");

        $data_pie = [];

        if ($show_pie == "FOOTER") {
            if ($pie_mode == "TOTAL") {
                foreach ($stat_trad as $col => $info) {
                    if (!$footer_sum_title_arr[$col]) {
                        $data_pie[$info] = $footer_total_arr[$col];
                    }
                }
                $file_dir_name = dirname(__FILE__);

                require_once "$file_dir_name/../graphic/afw_gpie_header.php";
                require_once "$file_dir_name/../graphic/afw_gpie_body.php";
            } elseif ($pie_mode == "FILTER") {

                list($colFilter, $colFilterValue) = explode("=", $filter);
                foreach ($stat_trad as $col => $info) {
                    foreach ($stats_data_arr as $stats_curr_row => $stats_data_item) {
                        if ($stats_data_item[$colFilter] == $colFilterValue) {
                            if (is_numeric($stats_data_item[$col])) $data_pie[$info] = $stats_data_item[$col];
                        }
                    }
                }
                $file_dir_name = dirname(__FILE__);
                // AfwMainPage::initOutput("<div class='var_export'> data_pie = ".var_export($data_pie, true)."</div>");
                // AfwMainPage::addOutput( AfwChartHelper::pieChart($data_pie, "dipe", []);
                if ($chart_mode == "IFRAME") {
                    AfwMainPage::addOutput(AfwChartHelper::modeChartInIFrame($chart_url));
                } else {
                    AfwMainPage::addOutput("chart mode $chart_mode is not implemented");
                }


                /*
        ob_start();
        require_once "$file_dir_name/../graphic/afw_gpie_header.php";
        require_once "$file_dir_name/../graphic/afw_gpie_body.php";
        AfwMainPage::addOutput( ob_get_clean();*/
            }
        }
    }


    public static function outputModeStatsTable(
        $myClassInstance,
        $stats_config,
        $stat_trad,
        $stats_big_header,
        $stats_data_arr,
        $stats_code,
        $footer_sum_title_arr,
        $footer_total_arr,
        $bloc_col_end = [],
        $url_to_show_arr = [],
        $lang = "ar"
    ) {
        AfwMainPage::addOutput("<br><table class='display dataTable stats_table' cellspacing='3' cellpadding='4'>");

        // STEP 1.0 super header
        $config_stats_super_header = $stats_config['SUPER_HEADER'];
        $class_xqe_col = "x";
        if ($config_stats_super_header) {
            AfwMainPage::addOutput("   <tr>");
            foreach ($config_stats_super_header as $config_stats_super_header_col) {
                $config_stats_super_header_col_colspan = $config_stats_super_header_col["colspan"];
                $config_stats_super_header_col_title   = $myClassInstance->translate($config_stats_super_header_col["title"], $lang);

                AfwMainPage::addOutput("      <th colspan='$config_stats_super_header_col_colspan' class='xqe_hf_$class_xqe_col xqe_super_header'>$config_stats_super_header_col_title</th>");
                if ($class_xqe_col == "x") {
                    $class_xqe_col = "z";
                } else {
                    $class_xqe_col = "x";
                }
            }
            AfwMainPage::addOutput("   </tr>");
        }

        AfwMainPage::addOutput("<thead>");
        // STEP 2.0 big header
        if ($stats_big_header) {
            AfwMainPage::addOutput("   <tr>");
            foreach ($stats_big_header as $stats_big_header_group) {
                $col_span = $stats_big_header_group['col_span'];
                $title = $stats_big_header_group['title'];
                $color = $stats_big_header_group['color'];
                AfwMainPage::addOutput("      <th colspan='$col_span' class='xqe_hf_$class_xqe_col big_header stats_$color'>$title</th>");
                if ($class_xqe_col == "x") {
                    $class_xqe_col = "z";
                } else {
                    $class_xqe_col = "x";
                }
            }
            AfwMainPage::addOutput("   </tr>");
        }


        // STEP 3.0  The columns header
        $aligntd       = "center";
        $class_xqe_col = "x";
        $thead_html    = "";
        $thead_html .= "<tr>";
        foreach ($stat_trad as $col => $info) {
            if ($class_xqe_col == "x") {
                $class_xqe_col = "z";
            } else {
                $class_xqe_col = "x";
            }

            if ($bloc_col_end[$col]) {
                $bloc_col_end_class = "xqe_bloc_col_separator";
            } else {
                $bloc_col_end_class = "";
            }

            $col_categ = $myClassInstance->statsColCategory($col, $stats_code);

            if ($class_xqe_col) {
                $class_xqe      = "xqe_hf_${class_xqe_col}";
                $class_xqe_prop = "class='$class_xqe $bloc_col_end_class stats_$col categ_$col_categ'";
            } else {
                $class_xqe_prop = "$bloc_col_end_class stats_$col categ_$col_categ";
            }

            $thead_html .= "      <th $class_xqe_prop align='$aligntd'><div class='stats-header'>$info</div></th>";
        }

        $thead_html .= "   </tr>";

        AfwMainPage::addOutput($thead_html);
        AfwMainPage::addOutput("</thead>");
        

        // STEP 4.0 Data
        $repeat_titles_nb_rows = $stats_config['REPEAT_TITLES_NB_ROWS'];
        if (!$repeat_titles_nb_rows) $repeat_titles_nb_rows = 20;
        $odd_even                    = "odd";
        $nb_rows_before_repeat_thead = 0;
        // die('RAFIK DEBUGG STATS 16/09/25 stats_data_arr = '.var_export($stats_data_arr, true));
        foreach ($stats_data_arr as $stats_curr_row => $stats_data_item) {
            if ($nb_rows_before_repeat_thead > $repeat_titles_nb_rows) {
                $nb_rows_before_repeat_thead = 0;
                AfwMainPage::addOutput($thead_html);
            }
            $class_xqe_col = "x";
            AfwMainPage::addOutput("   <tr>");
            foreach ($stat_trad as $col => $info) {
                $val_stat           = $stats_data_item[$col];
                $url_to_show_before = $url_to_show_arr[$col];
                $url_to_show        = AfwHtmlHelper::decodeHzmTemplate($url_to_show_before, $stats_data_item, $lang);
                /* if ($url_to_show_before and $url_to_show) {
            die("url_to_show_before=$url_to_show_before and url_to_show=$url_to_show decodeHzmTemplate from stats_data_item=" . var_export($stats_data_item, true));
        }*/

                if ($url_to_show) {
                    $val_stat_show = "<a class='stats-cell' target='_stats_details' href='$url_to_show'>" . $val_stat . "</a>";
                } else {
                    $val_stat_show = $val_stat;
                }

                if ($class_xqe_col == "x") {
                    $class_xqe_col = "z";
                } else {
                    $class_xqe_col = "x";
                }

                if ($bloc_col_end[$col]) {
                    $bloc_col_end_class = "xqe_bloc_col_separator";
                } else {
                    $bloc_col_end_class = "";
                }

                $col_categ = $myClassInstance->statsColCategory($col, $stats_code);

                if ($class_xqe_col) {
                    $class_xqe      = "xqe_${odd_even}_${class_xqe_col}";
                    $class_xqe_prop = "class='stats-td $class_xqe $bloc_col_end_class stats_$col categ_$col_categ'";
                } else {
                    $class_xqe_prop = "class='stats-td $bloc_col_end_class stats_$col categ_$col_categ'";
                }

                AfwMainPage::addOutput("      <td $class_xqe_prop align = '$aligntd'><div class='stats-header'>$val_stat_show</div></td>");
            }
            AfwMainPage::addOutput("   </tr>");
            if ($odd_even == "odd") {
                $odd_even = "even";
            } else {
                $odd_even = "odd";
            }

            $nb_rows_before_repeat_thead++;
        }

        $class_xqe_col = "x";

        AfwMainPage::addOutput("<tfoot>");
        $global_footer_sum     = $stats_config['FOOTER_SUM'];
        if (!$global_footer_sum)    $global_footer_sum = $stats_config['ROW_SUM'];
        if ($global_footer_sum) {
            AfwMainPage::addOutput("\n<tr>");
            foreach ($stat_trad as $col => $info) {
                if ($class_xqe_col == "x") {
                    $class_xqe_col = "z";
                } else {
                    $class_xqe_col = "x";
                }

                if ($footer_sum_title_arr[$col]) {
                    $footer_total_val = $footer_sum_title_arr[$col];
                } else {
                    $footer_total_val = $footer_total_arr[$col];
                }

                if ($bloc_col_end[$col]) {
                    $bloc_col_end_class = "xqe_bloc_col_separator";
                } else {
                    $bloc_col_end_class = "";
                }

                $col_categ = $myClassInstance->statsColCategory($col, $stats_code);

                if ($class_xqe_col) {
                    $class_xqe      = "xqe_hf_${class_xqe_col} xqe_sum_footer_$class_xqe_col";
                    $class_xqe_prop = "class = '$class_xqe $bloc_col_end_class stats_$col categ_$col_categ'";
                } else {
                    $class_xqe_prop = "class = '$bloc_col_end_class stats_$col categ_$col_categ'";
                }

                AfwMainPage::addOutput("      <th $class_xqe_prop align = '$aligntd'><div class='stats-footer'>$footer_total_val</div></th>");
            }

            AfwMainPage::addOutput("   </tr>");

            // AfwMainPage::addOutput( "log = ".$footer_total_arr["logcount_pp_reg"];

        }
        $footer_titles         = $stats_config['FOOTER_TITLES'];
        if ($footer_titles) {
            AfwMainPage::addOutput("\n<tr>");
            foreach ($stat_trad as $col => $info) {
                if ($class_xqe_col == "x") {
                    $class_xqe_col = "z";
                } else {
                    $class_xqe_col = "x";
                }

                if ($bloc_col_end[$col]) {
                    $bloc_col_end_class = "xqe_bloc_col_separator";
                } else {
                    $bloc_col_end_class = "";
                }

                $col_categ = $myClassInstance->statsColCategory($col, $stats_code);

                if ($class_xqe_col) {
                    $class_xqe      = "xqe_hf_${class_xqe_col}";
                    $class_xqe_prop = "class = '$class_xqe $bloc_col_end_class stats_$col categ_$col_categ'";
                } else {
                    $class_xqe_prop = "class = '$bloc_col_end_class stats_$col categ_$col_categ'";
                }

                AfwMainPage::addOutput("      <th $class_xqe_prop align = '$aligntd'><div class='stats-header'>$info</div></th>");
            }

            AfwMainPage::addOutput("   </tr>\n");
        }
        AfwMainPage::addOutput("</tfoot>");
        AfwMainPage::addOutput("</table>");
    }
}
