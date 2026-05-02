<?php
// die("DBG-mode qsearch");
require_once(dirname(__FILE__) . "/../../../config/global_config.php");

$lang = AfwLanguageHelper::getGlobalLanguage();
$please_wait = AFWObject::gtr("PLEASE_WAIT", $lang);
$loading = AFWObject::gtr("LOADING", $lang);
$please_wait_loading = $please_wait . " " . $loading;

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

// CmsMainPage::addOutput("_POST=".var_export($_POST, true));

$checked_ids =  trim(trim($_POST['checked_ids']), ",");
if (!$checked_ids) {
    CmsMainPage::addOutput("<div class='error'>لم يتم اختيار أي عنصر لتنفيذ الاجراءات عليه</div>");
} else {
    $cl =  $_POST['cl'];
    $currmod =  $_POST['currmod'];
    $pMethodCode = "";
    foreach ($_POST as $var000 => $val000) {
        if (AfwStringHelper::stringStartsWith($var000, "submit_rpbm_")) {
            $pMethodCode = substr($var000, 12);
        }
    }

    if (!$pMethodCode) {
        CmsMainPage::addOutput("<div class='error'>لم يتم اختيار أي اجراء ترغب في تنفيذه. يرجى مراجعة المشرف بخصوص هذا الخلل</div>");
    } else {
        /**
         * @var AFWObject $myClassInstance;
         * 
         */
        $myClassInstance = new $cl();
        if ($myClassInstance->PK_MULTIPLE) {
            $myObjList = $myClassInstance->loadManyIdsOneByOne($checked_ids);
        } else {
            $myClassInstance->where("id in ($checked_ids)");
            $myObjList = $myClassInstance->loadMany();
        }


        $err_count = 0;
        $war_count = 0;
        $inf_count = 0;
        $all_count = count($myObjList);

        $pMethodMainProps = $myClassInstance->getPublicMethod($pMethodCode);
        $lang_upper = strtoupper($lang);
        $pbmTitle = $pMethodMainProps["LABEL_$lang_upper"];
        if (!$pbmTitle) $pbmTitle = $pMethodMainProps["LABEL_AR"];

        CmsMainPage::addOutput("<h5 class=\"bluetitle search\"><i></i>تنفيذ الإجراء التالي [$pbmTitle] على مجموعة من السجلات</h5>");

        foreach ($myObjList as $myObjItem) {
            /**
             * @var AFWObject $myObjItem;
             */
            $record_display = $myObjItem->getDisplay($lang);
            $pMethodProps = $myObjItem->getPublicMethodForUser($objme, $pMethodCode);
            if (!$pMethodProps) {
                CmsMainPage::addOutput("<div class='rpbm message warning'>لا يمكنك تنفيذ هذا الاجراء على السجل : $record_display بسبب عدم وجود الصلاحية أو بسبب مخالفة قواعد العمل</div>");
            } elseif ($pMethodProps["COLOR"] == "denied") {
                $log = $pMethodProps["LOG"];
                CmsMainPage::addOutput("<div class='rpbm message warning'>لا يمكنك تنفيذ هذا الاجراء على السجل : $record_display لأحد الأسباب التالية : <br>\n عدم وجود الصلاحية للمستخدم <br>\n $log</div>");
            } else {
                $pbmethod = $pMethodProps["METHOD"];
                list($err, $inf, $war) = $myObjItem->$pbmethod($lang);
                if ($err) {
                    CmsMainPage::addOutput("<div class='rpbm message error'>خطأ أثناء تنفيذ هذا الاجراء على السجل : $record_display : $err</div>");
                    $err_count++;
                }
                if ($war) {
                    CmsMainPage::addOutput("<div class='rpbm message warning'>تنبيه أثناء تنفيذ هذا الاجراء على السجل : $record_display : $war</div>");
                    $war_count++;
                }
                if ($inf) {
                    CmsMainPage::addOutput("<div class='rpbm message information'>تم تنفيذ هذا الاجراء على السجل : $record_display : $inf</div>");
                    $inf_count++;
                }
            }
        }

        $err_cls = ($err_count > 0) ? "error" : "nothing";
        $war_cls = ($war_count > 0) ? "warning" : "nothing";
        $inf_cls = ($inf_count > 0) ? "information" : "nothing";
        CmsMainPage::addOutput("<div class='rpbm message nothing'>عدد الحالات الاجمالي : $all_count</div>");
        CmsMainPage::addOutput("<div class='rpbm message $inf_cls'>عدد الحالات المنفذة : $inf_count</div>");
        CmsMainPage::addOutput("<div class='rpbm message $err_cls'>عدد الأخطاء          : $err_count</div>");
        CmsMainPage::addOutput("<div class='rpbm message $war_cls'>عدد التنبيهات       : $war_count</div>");
    }
}
