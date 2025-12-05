<?php
$file_dir_name = dirname(__FILE__); 

require_once("afw_rights.php");
$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}



if(!$currmod)
{
    $currmod = $uri_module;
}

if(!$lang) $lang = 'ar';

$myObj = new $class();
$myObj->popup = $popup;

if($myObj->datatable_on_for_mode["display"])
{
   $datatable_on = 1;
}
if(!$objme) $objme = AfwSession::getUserConnected();

if($tech_notes) $myObj->tech_notes = $tech_notes;  
// die(var_export($objme,true));
// list($can,$bf_id, $reason) = $myObj->userCan($objme, $uri_module, "display");
//$can = $objme->iCanDoOperationOnObjClass($myObj,"display");
//$iCanDoOperationLog = var_export($objme->iCanDoOperationLog,true);
//$iCanDoBFLog = var_export($objme->iCanDoBFLog,true);
//$confirm_html = "<!--iCanDo : $iCanDoOperationLog  ,  $iCanDoBFLog -->";
$can = true;
$confirm_html = "";
if(!$can)
{
    $confirm_html .="<center>لا يوجد عندك صلاحية لهذا الإجراء</center>";  
}

if($myObj->load($id))
{
    $html_hidden_inputs = '<input type="hidden" name="currstep"   value="'.$currstep.'"/>';
    $html_hidden_inputs .= '<input type="hidden" name="currstep_orig"   value="'.$currstep_orig.'"/>';
    if(!$main_page) $main_page = "afw_handle_default_edit.php";
    $pbmpbis_input_name = "pbmpbis_".$pbMethodCode;
    $pbmp_input_name = "pbmp_".$pbMethodCode;
    $html_hidden_inputs .= '   <input type="hidden" name="pbmon"     value="1"/>
		<input type="hidden" name="file_obj"   value="'.$file.'"/>
		<input type="hidden" name="class_obj"  value="'.$class.'"/>
		<input type="hidden" name="id_obj"     value="'.$id.'"/>
                <input type="hidden" name="currmod"   value="'.$currmod.'"/>
                <input type="hidden" name="popup"   value="'.$popup.'"/>
                <input type="hidden" name="current_step"   value="'.$currstep.'"/>
                <input type="hidden" name="context_action"  value="'.$context_action.'"/>
		<input type="hidden" name="Main_Page" id="Main_Page" value="'.$main_page.'"/>
                <input type="hidden" name="'.$pbmpbis_input_name.'"   value="'.$_POST[$pbmpbis_input_name].'"/>
                <input type="hidden" name="'.$pbmp_input_name.'"   value="'.$_POST[$pbmp_input_name].'"/>
                <!-- '.var_export($_POST,true).' -->';
        
        
        $confirm_html .= "<div class='swal-overlay swal-overlay--show-modal' tabindex='-1'>  
        <div class='swal-modal'>
                <div class='swal-icon swal-icon--warning'>    
                        <span class='swal-icon--warning__body'>      
                                <span class='swal-icon--warning__dot'>
                                </span>    
                        </span>  
                </div>
                <div class='swal-title' style=''>$confirmation_question
                </div>
                <div class='swal-text' style=''>$confirmation_warning
                </div>
                <form id='edit_form' name='edit_form' method='post' action='main.php'> 
                <div class='swal-footer'>
                        <div class='swal-button-container'>    
                                <input name='pbmcancel-$pbMethodCode' type='submit' class='swal-button swal-button--cancel' tabindex='0' value='إلغاء'>
                        </div>
                        <div class='swal-button-container'>
                                <input name='pbmconfirm-$pbMethodCode' type='submit' class='swal-button swal-button--confirm swal-button--danger' tabindex='0' value='موافق'>                                    
                        </div>
                </div>
                $html_hidden_inputs
                </form>
        </div>
</div>";

        
}
else 
	AfwMainPage::addOutput("<center><table><tr><td><img src='image/warning.png' alt=''></td><td class='error'>لا يمكن تحميل هذا السجل، يبدوا أنه غير موجود أو حصل خطأ أثناء التحميل</td></tr></table></center>");

?>