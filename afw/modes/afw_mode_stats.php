<?php

require_once dirname(__FILE__) . '/../../../config/global_config.php';

$themeArr = AfwThemeHelper::loadTheme();
foreach ($themeArr as $theme => $themeValue) {
    $$theme = $themeValue;
}



if (! $currmod) {
    $currmod = AfwUrlManager::currentWebModule();
}

$datatable_on = true;

if (! $currmod) {
    $currmod = $uri_module;
}

if (! $cl) {
    $out_scr .= 'Mode Stat : no defined class ';
    exit;

}

/**
 * @var AFWObject $myObj
 */

$myObj = new $cl();

if (! $stc) {
    $stc = $myObj->STATS_DEFAULT_CODE;
}

if (! $stc) {
    $out_scr .= 'Mode Stat : no defined stat code ';
    exit;

}

$stats_config = $myObj::$STATS_CONFIG[$stc];
//echo( "myObj::STATS_CONFIG[$stc] = stats_config = ".var_export( $stats_config, true ).'<br>\n' );
// die();

if (! $stats_config) {
    $out_scr .= 'Mode Stat : no defined stat config : ' . $stc;
    exit;

}

if (! $lang) {
    $lang = 'ar';
}

$stats_where           = $stats_config['STATS_WHERE'];
$footer_titles         = $stats_config['FOOTER_TITLES'];
$repeat_titles_nb_rows = $stats_config['REPEAT_TITLES_NB_ROWS'];
$show_pie              = $stats_config['SHOW_PIE'];
$global_footer_sum     = $stats_config['FOOTER_SUM'];
$stats_where           = $myObj->decodeText($stats_where, $prefix = '', $add_cotes = true, $sepBefore = '[', $sepAfter = ']');
$myObj->where($stats_where);
// die( "stats_where=$stats_where" );
if (! $stats_config['DISABLE-VH']) {
    $myObj->select_visibilite_horizontale();
}

$group_sep = $stats_config['GROUP_SEP'];
if (! $group_sep) {
    $group_sep = '/';
}

$config_sql_group_by    = $stats_config['SQL_GROUP_BY'];
$config_group_cols      = $stats_config['GROUP_COLS'];
$stats_data_arr         = [];
$stats_row_code_arr     = [];
$stats_row_code_arr_len = 0;
$footer_total_arr       = [];
$footer_sum_title_arr   = [];
$sum_by_col_arr         = [];
$count_by_col_arr       = [];
$stat_trad              = [];

$config_stats_display_cols = $stats_config['DISPLAY_COLS'];
$config_stats_formula_cols = $stats_config['FORMULA_COLS'];
$config_stats_options      = $stats_config['OPTIONS'];
//die( 'stats_config[OPTIONS] = config_stats_options = '.var_export( $config_stats_options, true ) );

global $MAX_MEMORY_BY_REQUEST, $MODE_BATCH_LOURD;

$MAX_MEMORY_BY_REQUEST = $config_stats_options['MAX_MEMORY_BY_REQUEST'];
$MODE_BATCH_LOURD      = $config_stats_options['MODE_BATCH_LOURD'];

$config_stats_super_header = $stats_config['SUPER_HEADER'];

$group_cols_arr = [];

foreach ($config_group_cols as $group_col_index => $group_col_item) {
    $group_cols_arr[] = $group_col_item['COLUMN'];
}

$group_cols_list_sql = implode(',', $group_cols_arr);

if ($config_sql_group_by) {
    $arrAggregFunctions = [];
    foreach ($config_stats_display_cols as $display_col) {
        $aggCol                      = $display_col['COLUMN'];
        $aggSqlFormula               = $display_col['SQL_FORMULA'];
        $arrAggregFunctions[$aggCol] = $aggSqlFormula;
    }
    list($sql, $statsData) = AfwSqlHelper::multipleAggregFunction($myObj, $arrAggregFunctions, $group_cols_list_sql);

    // die( "sql=$sql => res=".var_export( $statsData, true ) );

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
                $group_col_display = AfwLoadHelper::decodeFkAttribute($myObj, $group_col, $group_col_val);
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
            $stats_data_arr[$stats_curr_row][$group_col. '_value'] = $group_col_show_arr[$group_col. '_value'];
            if (! $stat_trad[$group_col]) {
                $nom_col_short  = "$group_col.stat";
                $trad_col_short = $myObj->translate($nom_col_short, $lang);
                if ($trad_col_short == $nom_col_short) {
                    $nom_col_short  = "$group_col.short";
                    $trad_col_short = $myObj->translate($nom_col_short, $lang);
                }

                if ($trad_col_short == $nom_col_short) {
                    $stat_trad[$group_col] = $myObj->translate($group_col, $lang);
                } else {
                    $stat_trad[$group_col] = $trad_col_short;
                }

            }

        }

        $bloc_col_end    = [];
        $url_to_show_arr = [];

        foreach ($config_stats_display_cols as $config_stats_display_col_index => $config_stats_display_col_item) {
            $stats_display_col = $config_stats_display_col_item['COLUMN'];

            $myobj_struct = AfwStructureHelper::getStructureOf($myObj, $stats_display_col);
            $unit         = $myobj_struct['UNIT'];

            $footer_sum = $config_stats_display_col_item['FOOTER_SUM'];

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

                $stats_display_col_val = $statsRow[$stats_display_col];

                if (! $stat_trad[$show_name]) {
                    $nom_col_short  = "$show_name.stat";
                    $trad_col_short = $myObj->translate($nom_col_short, $lang);
                    if ($trad_col_short == $nom_col_short) {
                        $nom_col_short  = "$show_name.short";
                        $trad_col_short = $myObj->translate($nom_col_short, $lang);
                    }
                    if ($trad_col_short == $nom_col_short) {
                        $stat_trad[$show_name] = $myObj->translate($show_name, $lang);
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
} else {

    $stats_title = $myObj->translate('stats.' . $stc, $lang);

    $stats_title = $myObj->decodeText($stats_title, $prefix = '', $add_cotes = false, $sepBefore = '[', $sepAfter = ']');

    $myObj_list = $myObj->loadMany('', $group_cols_list_sql);

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
            $stats_data_arr[$stats_curr_row][$group_col. '_value'] = $group_col_show_arr[$group_col. '_value'];
            
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
}

/*************************************************************************/
// show statistics doesn't matter the source of data is php, sql or whatever

foreach ($stats_data_arr as $stats_curr_row => $stats_data_row) {
    foreach ($config_stats_formula_cols as $config_stats_formula_col_index => $config_stats_formula_col_item) {
        $show_name = $config_stats_formula_col_item["SHOW-NAME"];
        if ((! $curropt) or $config_stats_options[$curropt][$show_name]) {
            $method_formula                              = $config_stats_formula_col_item["METHOD"];
            $stats_data_arr[$stats_curr_row][$show_name] = $cl::$method_formula($stats_data_row, $formatted = true);
            if (! $stat_trad[$show_name]) {
                $nom_col_short  = "$show_name.stat";
                $trad_col_short = $myObj->translate($nom_col_short, $lang);
                if ($trad_col_short == $nom_col_short) {
                    $nom_col_short  = "$show_name.short";
                    $trad_col_short = $myObj->translate($nom_col_short, $lang);
                }
                if ($trad_col_short == $nom_col_short) {
                    $stat_trad[$show_name] = $myObj->translate($show_name, $lang);
                } else {
                    $stat_trad[$show_name] = $trad_col_short;
                }

            }
        }
    }
}

// die(var_export($stat_trad,true));

// display stats

$out_scr .= "<h3 class='centertitle bluetitle'>$stats_title</h3>";

$out_scr .= "<br>
<table class='display dataTable stats_table' cellspacing='3' cellpadding='4'>";

$class_xqe_col = "x";

if ($config_stats_super_header) {
    $out_scr .= "   <tr>";
    foreach ($config_stats_super_header as $config_stats_super_header_col) {
        $config_stats_super_header_col_colspan = $config_stats_super_header_col["colspan"];
        $config_stats_super_header_col_title   = $myObj->translate($config_stats_super_header_col["title"], $lang);

        $out_scr .= "      <th colspan='$config_stats_super_header_col_colspan' class='xqe_hf_$class_xqe_col xqe_super_header'>$config_stats_super_header_col_title</th>";
        if ($class_xqe_col == "x") {
            $class_xqe_col = "z";
        } else {
            $class_xqe_col = "x";
        }

    }
    $out_scr .= "   </tr>";
}

$aligntd       = "center";
$class_xqe_col = "x";
$thead_html    = "";
$thead_html .= "<thead>
   <tr>";
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

    if ($class_xqe_col) {
        $class_xqe      = "xqe_hf_${class_xqe_col}";
        $class_xqe_prop = "class='$class_xqe $bloc_col_end_class stats_$col'";
    } else {
        $class_xqe_prop = "$bloc_col_end_class stats_$col";
    }

    $thead_html .= "      <th $class_xqe_prop align='$aligntd'>$info</th>";

}

$thead_html .= "   </tr>
</thead>";

$out_scr .= $thead_html;

$odd_even                    = "odd";
$nb_rows_before_repeat_thead = 0;
// die('RAFIK DEBUGG STATS 16/09/25 stats_data_arr = '.var_export($stats_data_arr, true));
foreach ($stats_data_arr as $stats_curr_row => $stats_data_item) {
    if ($nb_rows_before_repeat_thead > $repeat_titles_nb_rows) {
        $nb_rows_before_repeat_thead = 0;
        $out_scr .= $thead_html;
    }
    $class_xqe_col = "x";
    $out_scr .= "   <tr>";
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

        if ($class_xqe_col) {
            $class_xqe      = "xqe_${odd_even}_${class_xqe_col}";
            $class_xqe_prop = "class='$class_xqe $bloc_col_end_class stats_$col'";
        } else {
            $class_xqe_prop = "class='$bloc_col_end_class stats_$col";
        }

        $out_scr .= "      <td class = 'stats-td' $class_xqe_prop align = '$aligntd'>$val_stat_show</td>";

    }
    $out_scr .= "   </tr>";
    if ($odd_even == "odd") {
        $odd_even = "even";
    } else {
        $odd_even = "odd";
    }

    $nb_rows_before_repeat_thead++;
}

$class_xqe_col = "x";

$out_scr .= "<tfoot>";

if ($global_footer_sum) {
    $out_scr .= "
<tr>";
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

        if ($class_xqe_col) {
            $class_xqe = "xqe_sum_footer_$ {
    class_xqe_col}
    ";
            $class_xqe_prop = "class = '$class_xqe $bloc_col_end_class stats_$col'";
        } else {
            $class_xqe_prop = "class = '$bloc_col_end_class stats_$col'";
        }

        $out_scr .= "      <th $class_xqe_prop align = '$aligntd'>$footer_total_val</th>";

    }

    $out_scr .= "   </tr>

    ";

    // $out_scr .= "log = ".$footer_total_arr["logcount_pp_reg"];

}

if ($footer_titles) {
    $out_scr .= "
    <tr>";
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

        if ($class_xqe_col) {
            $class_xqe = "xqe_hf_$ {
        class_xqe_col}
        ";
            $class_xqe_prop = "class = '$class_xqe $bloc_col_end_class'";
        } else {
            $class_xqe_prop = "$bloc_col_end_class";
        }

        $out_scr .= "      <th $class_xqe_prop align = '$aligntd'>$info</th>";

    }

    $out_scr .= "   </tr>

        ";
}
$out_scr .= "</tfoot>";
$out_scr .= "</table>";

$stats_bottom_help_code = "stats." . $stc . ".help";

$stats_bottom_help = $myObj->translate($stats_bottom_help_code, $lang);
$stats_bottom_help = $myObj->decodeText($stats_bottom_help, $prefix = "", $add_cotes = false, $sepBefore = "[ ", $sepAfter = " ]");

if ($stats_bottom_help != $stats_bottom_help_code) {
    $out_scr .= "<div class = 'stats_bottom_help'>$stats_bottom_help</div>";
}

if ($show_pie) {
    if ($show_pie == "FOOTER") {
        foreach ($stat_trad as $col => $info) {
            if (! $footer_sum_title_arr[$col]) {
                $data_pie[$info] = $footer_total_arr[$col];
            }

        }
    }
    $file_dir_name = dirname(__FILE__);
    $statsClass    = $cl;
    require_once "$file_dir_name/../afw_gpie_header.php";
    require_once "$file_dir_name/../afw_gpie_body.php";
}
