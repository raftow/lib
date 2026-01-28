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
    AfwMainPage::addOutput( 'Mode Stat : no defined class ');
    exit;

}

$myClass = $cl;

/**
 * @var AFWObject $myClassInstance
 */

$myClassInstance = new $cl();

if (! $stc) {
    $stc = $myClassInstance->STATS_DEFAULT_CODE;
}

if (! $stc) {
    AfwMainPage::addOutput( 'Mode Stat : no defined stat code ');
    exit;

}

$stats_config = $myClassInstance::$STATS_CONFIG[$stc];
//echo( "myClassInstance::STATS_CONFIG[$stc] = stats_config = ".var_export( $stats_config, true ).'<br>\n' );
// die();
$config_stats_options      = $stats_config['OPTIONS'];
global $MAX_MEMORY_BY_REQUEST, $MODE_BATCH_LOURD;
$MAX_MEMORY_BY_REQUEST = $config_stats_options['MAX_MEMORY_BY_REQUEST'];
$MODE_BATCH_LOURD      = $config_stats_options['MODE_BATCH_LOURD'];



if (! $stats_config) {
    AfwMainPage::addOutput( 'Mode Stat : no defined stat config : ' . $stc);
    exit;

}

$stats_code = $stc;

if (! $lang) $lang = 'ar';

// 


$stats_where           = $stats_config['STATS_WHERE'];
$stats_where           = $myClassInstance->decodeText($stats_where, $prefix = '', $add_cotes = true, $sepBefore = '[', $sepAfter = ']');
// where of the stats-report itself
$myClassInstance->where($stats_where);
// where of the horizontal visibility
if (! $stats_config['DISABLE-VH']) {
    $myClassInstance->select_visibilite_horizontale();
}
// where of stats filter
list($arr_sql_conds, $cond_phrase_arr) = AfwSearchHelper::prepareSQLWhereFromPostedFilter($myClassInstance, $lang);
// die("arr_sql_conds=".var_export($arr_sql_conds, true));
$sql_conds = implode(" and ", $arr_sql_conds);
$sql_conds = trim($sql_conds);
if (preg_match('and$', $sql_conds)) $sql_conds = substr($sql_conds, 0, -2);
if($sql_conds) $myClassInstance->where($sql_conds);

$allSql = $myClassInstance->getSQL();

AfwMainPage::addOutput("<!-- sql_conds=$sql_conds allSql=$allSql -->");



list($stat_trad, $stats_data_arr, $case, $footer_sum_title_arr, $footer_total_arr, $bloc_col_end, $url_to_show_arr) = AfwStatsHelper::modeStatsInitialization($myClassInstance, $stats_config, $config_stats_options, $lang, $curropt);

AfwStatsHelper::outputModeStatsHeaderAndFilterPanel($myClassInstance, $stats_config, $stats_code, $currmod, $lang);

// AfwMainPage::addOutput("<pre class='php'>case=$case stat_trad = ".var_export($stat_trad,true)." stats_data_arr = ".var_export($stats_data_arr,true)."</pre>");
AfwStatsHelper::outputModeStatsTable(
        $myClassInstance,
        $stats_config,
        $stat_trad,
        $stats_data_arr,
        $stats_code,
        $footer_sum_title_arr,
        $footer_total_arr,
        $bloc_col_end = [],
        $url_to_show_arr = [],
        $lang
    );


AfwStatsHelper::outputModeStatsFooter($myClassInstance, $stats_config, $stat_trad, $stats_data_arr, $stats_code, $footer_sum_title_arr, $footer_total_arr, $currmod, $lang);
