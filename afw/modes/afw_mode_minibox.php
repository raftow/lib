<?php

$file_dir_name = dirname(__FILE__); 

require_once("afw_rights.php");
$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}


if(!$objme) $objme = AfwSession::getUserConnected();
if(!$currmod)
{
    $currmod = $uri_module;
}

if(!$lang) $lang = 'ar';

$myObj = new $cl();
$myObj->popup = $popup;

if($myObj->datatable_on_for_mode["display"])
{
   $datatable_on = 1;
}


if($tech_notes) $myObj->tech_notes = $tech_notes;  
// die(var_export($objme,true));
// list($can,$bf_id, $reason) = $myObj->userCan($objme, $uri_module, "display");
$can = $objme->iCanDoOperationOnObjClass($myObj,"display");
$iCanDoOperationLog = var_export($objme->iCanDoOperationLog,true);
$iCanDoBFLog = var_export($objme->iCanDoBFLog,true);
$out_scr = "<!--iCanDo : $iCanDoOperationLog  ,  $iCanDoBFLog -->";
if(!$can)
{
    $out_scr .="<center>لا يوجد عندك صلاحية لرؤية هذه المعلومات</center>";  
}

if($myObj->load($id))
{
        $lv_obj =& $myObj;
        include_once("afw_save_last_visit.php");
        
        //$out_scr .= "<table class='$class_table' cellpadding='4' cellspacing='3'><tr><td colspan='2' align='center' class='$class_bloc'>";

        
	if(AfwUmsPagHelper::userCanDoOperationOnObject($myObj,$objme,'display'))
        {
		$out_scr .= $myObj->showMinibox();
	}
	else
		$out_scr .= "لا يوجد عندك صلاحية لعرض هذا السجل";
	//$out_scr .= "</td></tr></table>";
        //$out_scr .= "</div></div></div>";
        //$out_scr .= "</td></tr></table>";
}
else 
	$out_scr .="<center><table><tr><td><img src='image/warning.png' alt=''></td><td class='error'>لا يمكن تحميل هذا السجل، يبدوا أنه غير موجود أو حصل خطأ أثناء التحميل</td></tr></table></center>";

?>