#!/bin/bash
<?php

ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
ini_set('max_execution_time',0);
$lang = "ar";


$project_code = "migrate_test";
$project = "migrate test";

$transform = $argv[1];
$value = $argv[2];
$transformClass = $argv[3];
$transformModule = $argv[4];
if(!$transformClass) $transformClass = "AfwDataMigrator";

$MODE_BATCH_LOURD = true;
$DISABLE_CACHE_MANAGEMENT = true;
$lib_root_path = "/var/www/html/v3/lib";
$batch_root_path = "/var/www/hub_batch";



require_once("$lib_root_path/afw/afw_autoloader.php");
// AfwAutoLoader::addModule("sis");

if($transformModule)
{
        AfwAutoLoader::addMainModule($transformModule);
        require_once ("$lib_root_path/$transformModule/application_config.php");
        AfwSession::initConfig($config_arr);
        AfwSession::setConfig("MODE_BATCH", true);

} 

require_once("$lib_root_path/hzm/alert/hzm_alerts.php");
require_once("$batch_root_path/common/batch_functions.php");

require_once("$lib_root_path/../external/db.php");
require_once("$lib_root_path/afw/common.php");


$start_job_timestamp = date("Y-m-d H:i:s"); 

$print_sql = true;
$print_info = true;
$print_debugg = true;
$print_full_debugg = true;
$print_warning = true;
$print_error = true;
$print_important = true;

AfwBatch::enableEcho();

list($success, $new_value) = $transformClass::$transform($value);
if($success) AfwBatch::print_important("> sucess of $transformClass => $transform ($value) => $new_value");
else AfwBatch::print_error("> fail of $transformClass => $transform ($value) => $new_value");





?>