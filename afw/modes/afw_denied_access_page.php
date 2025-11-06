<?php
$file_dir_name = dirname(__FILE__);
$module_dir_name = $file_dir_name;
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
ini_set('zend.exception_ignore_args', 0);
$lang = "ar";

require_once("$file_dir_name/../afw_autoloader.php");




        
AfwSession::startSession();
$objme = AfwSession::getUserConnected();
//if(!$objme) header("login.php");
$uri_module = AfwUrlManager::currentURIModule();

include_once("$file_dir_name/../../../$uri_module/application_config.php");
AfwSession::initConfig($config_arr, "system", "$file_dir_name/../../../$uri_module/application_config.php");
$NOM_SITE = AfwSession::config("application_name","This Application");
$MODE_DEVELOPMENT = AfwSession::config("MODE_DEVELOPMENT",true);

require_once("$file_dir_name/../../../config/global_config.php");
// 

include("$file_dir_name/../../hzm/web/hzm_header.php");

$message = "Acess denied.    /    عملية غير مسموح بها";

// echo "$file_dir_name/../../hzm/web/hzm_basic_header.php";

include("$module_dir_name/../../hzm/web/hzm_basic_header.php");
?>
<div class='logincontainer'>
<br>
<div class="alert alert-danger alert-dismissable" role="alert" ><?php echo $message;?><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></div>
<div>
للأسف لا توجد عندك صلاحية للتمتع بهذه الخدمة .<br> 
نرجوا منكم التواصل مع المشرف.<br>
Sorry you dont have authorisation to do this action.<br> 
Please contact administrator.<br>
<br>
<br>
<table cellspacing="8px" cellpadding="10px" class="grid" style="background-color: #efefef;border: 1px #000;border-style: solid;padding: 10px;">

<tr><th>المشرف / Administrator</th><td><?php echo AfwSession::config("site_administrator", "not defined")?></td></tr>
<tr><th>تحويلة / Extension</th><td><?php echo AfwSession::config("site_administrator_extension", "not defined")?></td></tr>
<tr><th>البريد الاكتروني/ Email</th><td><?php echo AfwSession::config("site_administrator_email", "not defined")?></td></tr>
<tr><th>معرف المستخدم / User ID</th><td><? if($objme) echo $objme->getId()?></td></tr>
<tr><th>الخدمة المطلوبة / Requested service</th><td><?php echo AfwSession::pullSessionVar("operation")?></td></tr>
<tr><th>النتيجة / Result</th><td><?php echo AfwSession::pullSessionVar("result")?></td></tr>
<tr><th>التقرير / report </th><td style='color:red'><?php echo AfwSession::pullSessionVar("report")?></td></tr>
</table> 
</div>
<?php
if($MODE_DEVELOPMENT or AfwSession::getSessionVar("user_golden"))
{
?>
<table  cellspacing="8px" cellpadding="10px" class="grid" style="background-color: #0f0f0f;color:white;border: 1px #000;border-style: solid;padding: 10px;">
<tr><th>بيانات فنية أخرى </th></tr>
<tr><td>
<?php 
        echo AfwSession::pullSessionVar("other_log");
        echo "<br>session context Ican do LOG : <br>";
        echo AfwSession::getLog("iCanDo");
        echo "<br>userCan table <br>";
        echo var_export($objme->userCanTable,true);
        
        
        echo "<br>iCanDoOperationLog <br>";
        echo $objme->showArr($objme->iCanDoOperationLog);
        
        echo "<br>iCanDoBFLog <br>";
        echo $objme->showArr($objme->iCanDoBFLog);
        
        echo "<br>myBFListOfID table <br>";
        echo $objme->showArr($objme->myBFListOfID);
        
        echo "<br>Me details <br>";
        echo $objme->showMyProps();
        echo "<br>Session details <br>";
        echo AfwSession::logSessionData(true);
        echo "<br>\n".AfwShowHelper::showObject($objme,"html");
}
?>
</td></tr>
</table> 

<br>

</div>
<?php
include("$module_dir_name/../../hzm/web/hzm_basic_footer.php");

