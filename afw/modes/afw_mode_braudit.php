<?php

require_once dirname(__FILE__) . '/../../../config/global_config.php';

$themeArr = AfwThemeHelper::loadTheme();
foreach ($themeArr as $theme => $themeValue) {
    $$theme = $themeValue;
}



if (! $currmod) {
    $currmod = UfwUrlManager::currentWebModule();
}

$datatable_on = true;

if (! $currmod) {
    $currmod = $uri_module;
}

if (!$cl) {
    CmsMainPage::addOutput( 'Mode Audit By Row : no defined class ');
    exit;

}

if (!$id) {
    CmsMainPage::addOutput( 'Mode Audit By Row : object id not defined');
    exit;
}


$myClass = $cl;

/**
 * @var AFWObject $myClassInstance
 */

$myClassInstance = $myClass::loadById($id);


if (!$myClassInstance) {
    CmsMainPage::addOutput('Mode Audit By Row : object not found');
    exit;
}


if (!$myClassInstance->isByRowAuditable()) {
    CmsMainPage::addOutput("Mode Audit By Row : This object doesn't allow the audit by row mode");
    exit;
}

require_once(dirname(__FILE__) . "/../../../config/global_config.php");

$lang = AfwLanguageHelper::getGlobalLanguage();
$please_wait = AFWObject::gtr("PLEASE_WAIT", $lang);
$loading = AFWObject::gtr("LOADING", $lang);
$please_wait_loading = $please_wait . " " . $loading;
if (!$current_page) $current_page = "afw_mode_qsearch.php";
$themeArr = AfwThemeHelper::loadTheme();
foreach ($themeArr as $theme => $themeValue) {
        $$theme = $themeValue;
}

if (!$currmod) {
        $currmod = UfwUrlManager::currentWebModule();
} else AfwAutoLoader::addModule($currmod);

CmsMainPage::initOutput("");
$objme = AfwSession::getUserConnected();
if (!$objme) {
        AfwSession::pushError("الرجاء تسجيل الدخول أولا");
        header("Location: login.php");
        exit();
}

/**
 * @var Auser $objme
 * 
 */

// to be able to do audit you need same rights than do qsearch
if ($objme) {
        $report_can_qsearch = AfwSession::getLog("iCanDo");
        $can = $objme->iCanDoOperationOnObjClass($myClassInstance, "qsearch");
        $report_can_qsearch = AfwSession::getLog("iCanDo");
} else {
        $can = $myClassInstance->public_search;
}
// $objme->showICanDoLog();
// $myClassInstance->simpleError("debugg :: iCanDoOperationLog ::");

if (!$can) {
        AfwSession::setSessionVar("operation", "audit by row on $myClass class");
        AfwSession::setSessionVar("result", "failed");
        AfwSession::setSessionVar("report", $report_can_qsearch);
        AfwSession::setSessionVar("other_log", $log);
        header("Location: /lib/afw/modes/afw_denied_access_page.php");
        exit();
}



