<?php

require_once(dirname(__FILE__)."/../../external/db.php");


$theme_name = AfwSession::config('theme','modern'); $file_dir_name = dirname(__FILE__);include("$file_dir_name/modes/".$theme_name.'_config.php');
require_once("afw_rights.php");
require_once("afw_qsearch_motor.php");

if(!$currmod)
{
        $currmod = AfwUrlManager::currentWebModule();
}

$out_scr = "";
$objme = AfwSession::getUserConnected();
if(!$objme) 
{
    AfwSession::pushError("الرجاء تسجيل الدخول أولا");
    header("Location: login.php");
    exit();
}


$cl_short = "employee";

$obj = new Employee();

if($objme)
{

}

$actions_tpl_arr = AfwUmsPagHelper::getAllActions($obj);

if($goon) 
{
    include 'afw_get_employee_handle.php';
    $collapse_show = "";
}
else $collapse_show = "show";
     
	
$can = true;

if(!$can)
{
        AfwSession::setSessionVar("operation", "quick search on $obj class");
        AfwSession::setSessionVar("result", "failed");
        AfwSession::setSessionVar("report", $report_can_qsearch);
        AfwSession::setSessionVar("other_log", $log);
        header("Location: /lib/afw/modes/afw_denied_access_page.php");      
        exit();
}

if(!$lang) $lang = 'ar';

$plural_obj_name =  $obj->transClassPlural($lang);
$plural_obj_name_short =  $obj->transClassPlural($lang,true);
$single_obj_name =  $obj->transClassSingle($lang);

if((!$context_action) and $coac) 
{
        $context_action = $coac;
        
}
AfwAutoLoader::addModule($context_action);
if(!$context_action) $context_action = "view_only";

$page_title = $obj->translate('QSEARCH',$lang,true)." ".$single_obj_name;

$out_scr .= "<div id='page-content-wrapper' class='qsearch_page'>
                <div class='row row-filter-$cl_short'>
                        <div class='qfilter col-sm-10 col-md-10 pb10'>
                                <h1>$page_title</h1>";


$out_scr .= '<form name="searchForm" id="searchForm" method="post" action="'."main.php".'">';

$out_scr .= '<div class="row row-'.$cl_short.'">';
$out_scr .=  AfwShowHelper::showObject($obj,"HTML", "afw_get_employee_template.php");
$out_scr .= '</div>';

$out_scr .= '<input type="hidden" name="goon"  value="1"/>';
$out_scr .= '<input type="hidden" name="context_action"  value="'.$context_action.'"/>';
$out_scr .= '<input type="hidden" id="Main_Page" name="Main_Page" value="afw_get_employee.php"/>';

$out_scr .= "<div class='btn-group' role='group' aria-label='...'>
                        <table>
                                <tr>
                                        <td width='15px'>&nbsp;</td>
                                        <td>
                                        <input id='submit-form' type='submit' name='submit' class='bluebtn smallbtn fright' value='".$obj->translate('SUBMIT-SEARCH',$lang,true)."'>
                                        </td>
                                        <td width='15px'>&nbsp;</td>
                                </tr>
                        </table>
                </div>
";                
if($goon) {
        $out_scr .= $search_result_html;
}
$out_scr .= '</form>';



$out_scr .= "</div>
       </div>
</div>";  



?>



