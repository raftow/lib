<?php
// die("DBG-mode handle search");
require_once(dirname(__FILE__) . "/../../../config/global_config.php");

$lang = 'en';

$themeArr = AfwThemeHelper::loadTheme("handle-braudit");
foreach ($themeArr as $theme => $themeValue) {
    $$theme = $themeValue;
}
$images = AfwThemeHelper::loadTheme();

if (!$objme) $objme = AfwSession::getUserConnected();
$me =  $objme->id;

$MAX_ROW_DEFAULT = AfwSession::config("MAX_ROW", 500);
$MAX_ROW = AfwSession::config("MAX_ROW-$cl", $MAX_ROW_DEFAULT);
if (!$objme->isAdmin()) $MAX_ROW = AfwSession::config("MAX_ROW-$cl-not-admin", $MAX_ROW);
$lang = AfwLanguageHelper::getGlobalLanguage();
$target = "";
$popup_t = "";

$cols_spec_retrieve = array();

/**
 * @var AFWObject $myClassInstance 
 */


$header = AfwUmsPagHelper::getAuditHeader($myClassInstance, $agroup, $fields, $lang);

if (count($header) == 0) {
    throw new AfwBusinessException("For class $cl no audit columns defined to retrieve for fields=$fields groups=$agroup lang=$lang");
}

$myBRAuditTableName = $myClassInstance->getMyTable(true) . "_braudit";

$audit_nb_rows_by_header = $myClassInstance->AUDIT_NB_ROWS_BY_HEADER;
if (!$audit_nb_rows_by_header) $audit_nb_rows_by_header = 12;

$sql_braudit = "select * from $myBRAuditTableName " . $myClassInstance->sqlCondPK();

$rows_braudit = AfwDatabase::db_recup_rows($sql_braudit);

$newColumnsRules = [];
$newColumnsRules["audit_action"] = ["calcClass" => "AfwAuditHelper", "calcMethod" => "auditActionHtml"];
$newColumnsRules["audit_by"] = ["calcClass" => "AfwAuditHelper", "calcMethod" => "auditByHtml"];
$newColumnsRules["audit_datetime"] = ["calcClass" => "AfwAuditHelper", "calcMethod" => "auditDatimeHtml"];
$newColumnsRules["audit_advanced"] = ["calcClass" => "AfwAuditHelper", "calcMethod" => "auditAdvancedHtml"];


$data_braudit = AfwShowHelper::formatDataRows($rows_braudit, $cl, $header, $myClassInstance, $lang, $newColumnsRules);

list($html, $ids) =
    AfwShowHelper::tableToHtml(
        $data_braudit,
        $header,
        false,
        null,
        null,
        'grid',
        'altitem',
        'item',
        [],
        $lang,
        '',
        '',
        'bigtitle',
        [],
        '',
        0,
        '',
        '',
        'audit',
        'off',
        '',
        null,
        [],
        $audit_nb_rows_by_header,
        "audit_action",
        ["changed" => [
            'css' => "cell-changed",
            'exceptions' => ["audit_by", "audit_datetime"]
        ]]
    );

$html .= "<script>
            $(document).ready(function() {
                $('.advanced-audit').click(function() {
                    var row_id = $(this).attr(\"id\");
                    console.log('row_id = '+row_id);
                    var arr_data = row_id.split(\"-\");
                    var id = arr_data[2];
                    var action_div_id = 'audit-action-div-'+id;
                    console.log('action_div_id = '+action_div_id);
                    if($('#'+action_div_id).hasClass('hide')) {
                        $('#'+action_div_id).removeClass('hide');                    
                        $(this).removeClass('icon-plus').addClass('icon-minus');
                    } else {
                        $('#'+action_div_id).addClass('hide');                    
                        $(this).removeClass('icon-minus').addClass('icon-plus');
                    }
                });
            });
        </script>";

if ($me == 1) {
    $html .= "<hr class='separator'><pre class='sql hide'>$sql_braudit</pre>";
}



return ['audit_result_html' => $html];
