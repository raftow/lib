<?php
// die("DBG-mode handle search");
require_once(dirname(__FILE__) . "/../../../config/global_config.php");


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

$target = "";
$popup_t = "";

$cols_spec_retrieve = array();

/**
 * @var AFWObject $obj 
 */

$obj  = new $cl();
$header_retrieve = AfwUmsPagHelper::getAuditHeader($obj, $fgroup, $fields, $lang);
