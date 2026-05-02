<?php
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
ini_set('zend.exception_ignore_args', 0);

require_once(dirname(__FILE__)."/../core/afw_autoloader.php");
require_once(dirname(__FILE__)."/../../../config/global_config.php");
AfwAutoLoader::addMainModule($MODULE);
// include_once ("$file_dir_name/../$MODULE/ini.php");
include_once (dirname(__FILE__)."/../../../$MODULE/module_config.php");
include_once (dirname(__FILE__)."/../../../$MODULE/application_config.php");
include_once (dirname(__FILE__)."/../../../lib/afw/utilities/ufw_error_handler.php");
AfwSession::initConfig($config_arr, "system", dirname(__FILE__)."/../../../$MODULE/application_config.php");
AfwSession::startSession();