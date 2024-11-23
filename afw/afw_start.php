<?php
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
require_once("$file_dir_name/../lib/afw/afw_autoloader.php");
require_once("$file_dir_name/../external/db.php");
AfwAutoLoader::addMainModule($MODULE);
// include_once ("$file_dir_name/../$MODULE/ini.php");
include_once ("$file_dir_name/../$MODULE/module_config.php");
include_once ("$file_dir_name/../$MODULE/application_config.php");
include_once ("$file_dir_name/../lib/afw/afw_error_handler.php");
AfwSession::initConfig($config_arr);
AfwSession::startSession();