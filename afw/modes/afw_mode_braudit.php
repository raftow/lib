<?php

require_once dirname(__FILE__) . '/../../../config/global_config.php';

$themeArr = AfwThemeHelper::loadTheme();
foreach ($themeArr as $theme => $themeValue) {
    $$theme = $themeValue;
}

$out_scr_btns = "";

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

$cl_short = strtolower(substr($cl, 0, 10));

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
if (!$current_page) $current_page = "afw_mode_braudit.php";
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


if ($resetcrit) {
        $_POST = array();
        $datatable_on = false;
}

/**
 * @var Auser $objme
 * 
 */

// to be able to do audit you need same rights than do qs-earch
if ($objme) {
        $report_can_braudit = AfwSession::getLog("iCanDo");
        $can = $objme->iCanDoOperationOnObjClass($myClassInstance, "qs"."earch");
        $report_can_braudit = AfwSession::getLog("iCanDo");
} else {
        $can = $myClassInstance->public_audit;
}
// $objme->showICanDoLog();
// $myClassInstance->simpleError("debugg :: iCanDoOperationLog ::");

if (!$can) {
        AfwSession::setSessionVar("operation", "audit by row on $myClass class");
        AfwSession::setSessionVar("result", "failed");
        AfwSession::setSessionVar("report", $report_can_braudit);
        AfwSession::setSessionVar("other_log", $log);
        header("Location: /lib/afw/modes/afw_denied_access_page.php");
        exit();
}

$search_result_html = "";

if ($datatable_on) {
        $handle_return = include 'afw_handle_default_braudit.php';
        $audit_result_html = $handle_return['audit_result_html'];
        AfwSession::log("End of afw_handle_default_search");
        // die("DBG-after afw_handle_default_search");

}

$single_obj_name =  $myClassInstance->transClassSingle($lang);
$page_title = $myClassInstance->translate('AUDIT', $lang, true) . " " . $single_obj_name;
$page_sub_title = $myClassInstance->getDisplay($lang);
$page_action_description = null;
$execute_btn = 'SUBMIT-AUDIT';

CmsMainPage::addOutput("<div id='page-content-wrapper' class='braudit_page'>
                <div class='row row-filter-$cl_short'>
                        <div id='qfilter' class='qfilter col-sm-10 col-md-10 pb10'>");

CmsMainPage::addOutput("<div class=\"qfilter-header\">");
CmsMainPage::addOutput("<h1>$page_title</h1>");
if ($page_sub_title) {
        CmsMainPage::addOutput("<h2>$page_sub_title</h2>");
        CmsMainPage::addOutput("<h3>$page_action_description</h3>");
}
CmsMainPage::addOutput("</div>");


$list_of_ret_cols_all = array();
$list_of_ret_cols_default = array();

$class_db_structure = $myClassInstance->getMyDbStructure();

foreach ($class_db_structure as $nom_col => $desc) {
        if ((($fgroup == "all") or ($desc["FGROUP"]==$fgroup)) and 
            (($fields == "all") or (($fields == "auditable") and ($desc["AUDIT"]))) and
            (!$desc["NO-AUDIT"]) and
            AfwPrevilegeHelper::keyIsToDisplayForUser($myClassInstance, $nom_col, $objme)) 
        {
            $list_of_ret_cols_all[$nom_col] = $myClassInstance->translate($nom_col, $lang);
            $list_of_ret_cols_default[] =  $nom_col;
        }
}

if (!$_POST["ms_ret_cols"]) $_POST["ms_ret_cols"] = $list_of_ret_cols_default;

// for the moment we dont have audit motor
$myMotor = "AfwQse"."archMotor";

ob_start();
$myMotor::select(
        $list_of_ret_cols_all,
        ((isset($_POST["ms_ret_cols"])) ? $_POST["ms_ret_cols"] : array()),
        array(
                "class" => "audit_comm_select $class_inputSelect_multi_big",
                "name"  => "ms_ret_cols[]",
                "size"  => 5,
                "multi" => true
        ),
        "asc",
        false
);

$input_select_list_of_ret_cols  = ob_get_clean();

if (!$myClassInstance->isLourde()) {
        $aclourde = '';
} else {
        $aclourde = 'class="form_lourde"';
}
CmsMainPage::addOutput('<div id="form-container" class="form-container"><form name="auditForm" id="auditForm" ' . $aclourde . ' method="post" action="' . "main.php" . '">');
CmsMainPage::addOutput('<div class="row row-' . $cl_short . '">');
CmsMainPage::addOutput(AfwShowHelper::showObject($myClassInstance, "HTML", "afw_template_default_braudit.php"));

if (!isset($disable_select_view_in_braudit_mode[$cl])) {
    $disable_select_view_in_braudit_mode[$cl] = AfwSession::config("disable_select_view_in_braudit_mode_for_$cl", false);
}

if (!$disable_select_view_in_braudit_mode[$cl]) {
        $all_groups = AFWObject::gtr("all groups", $lang);
        if ($fgroup == "all") $fgroup_all_selected = "selected";
        else $fgroup_all_selected = "";
        $select_view = "<div class='braudit_view_select'>
            <select id='fgroup' name='fgroup' class='form-control lang_$lang'>
            <option value='all' $fgroup_all_selected >$all_groups</option>
        ";
        $qsrch_fgroups = $myClassInstance->getFieldGroupArr($lang);
        $size_what_to_see = 3;
        foreach ($qsrch_fgroups as $fgroupcode => $fgroupname) {
                if ($fgroup == $fgroupcode) $fgroup_selected = "selected";
                else $fgroup_selected = "";
                $select_view .= "<option value='$fgroupcode' $fgroup_selected> $fgroupname</option>";
        }
        $select_view .= "</select></div>";
        $what_to_see = $myClassInstance->translate('WHAT-TO-SEE', $lang, true);
        CmsMainPage::addOutput('<div class="col-md-' . $size_what_to_see . '">                
                <div class="form-group">                        
                        <label>' . $what_to_see . '</label>                        		
                        ' . $select_view . '		                
                </div>        
        </div>');
} else {
        CmsMainPage::addOutput("<input type='hidden' id='fgroup' name='fgroup' value='all' />");
}

CmsMainPage::addOutput('</div>');
CmsMainPage::addOutput('<input type="hidden" name="datatable_on"  value="1"/>');
CmsMainPage::addOutput('<input type="hidden" name="cl" value="' . $cl . '"/>');
CmsMainPage::addOutput('<input type="hidden" name="currmod" value="' . $currmod . '"/>');
CmsMainPage::addOutput('<input type="hidden" name="id" value="' . $id . '"/>');
CmsMainPage::addOutput('<input type="hidden" name="r" value="' . $r . '"/>');
CmsMainPage::addOutput('<input type="hidden" id="Main_Page" name="Main_Page" value="' . $current_page . '"/>');

CmsMainPage::addOutput('<script type="text/javascript">
        $(document).ready(function() {       
                $("#braudit-submit-form").click(function(){
                        $(".alert-dismissable").fadeOut().remove();
                        $("#audit_result_div").html(\'<div class="footer1 hzm-relative-loader-div" id="mySQLloader"><div class="relative hzm-loading-div" id="myloading">' . $please_wait_loading . '</div></div>\');
                });
        });
    
</script>');

if ($datatable_on) {
        $reset_link = "<a href=\"main.php?Main_Page=afw_mode_braudit.php&cl=$cl&currmod=$currmod&resetcrit=1\">
                           <div id='reset-form' type='submit' name='submit-reset-form' class='yellbtn smallbtn fright'>" . $myClassInstance->translate('RESET-CRITEREA', $lang, true) . "</div>
                        </a>";
} else {
        $reset_link = "&nbsp;";
}
CmsMainPage::addOutput("<div class='btn-group' role='group' aria-label='...'>
                <table>
                        <tr>
                                <td width='15px'>&nbsp;</td>
                                <td>
                                <input id='braudit-submit-form' type='submit' name='submit' class='simple-btn smallbtn fright' value='" . $myClassInstance->translate($execute_btn, $lang, true) . "'>
                                </td>
                                <td width='15px'>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td width=\"15px\">&nbsp;</td>
                                <td>$reset_link</td>
                                <td width='15px'>&nbsp;</td>
                        </tr>
                </table>
                </div>
");
CmsMainPage::addOutput("   
                </form>
                </div> <!-- form-container -->
                
                
                ");
CmsMainPage::addOutput("</center>");
CmsMainPage::addOutput('');



CmsMainPage::addOutput("</div>
       </div>
</div>
<script>
        \$( function() {
        \$(\"#qfilter\").accordion({
                collapsible: true, 
                active: $accordion_expanded
                });
        });
</script>
");
CmsMainPage::addOutput("<div id=\"audit_result_div\">");
if ($datatable_on) {
        CmsMainPage::addOutput($audit_result_html);
        CmsMainPage::addOutput($out_scr_btns);
}
CmsMainPage::addOutput("</div>");
