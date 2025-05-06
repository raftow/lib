<?php
$file_dir_name = dirname(__FILE__);
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);


 
if(!$action_page) $action_page = "pwd_reset.php";

$logbl = substr(md5($_SERVER["HTTP_USER_AGENT"] . "-" . date("Y-m-d")),0,10);

$module_auth_dir_name = $module_dir_name = $file_dir_name;
require_once("$file_dir_name/../../../lib/afw/afw_autoloader.php");
$uri_module = AfwUrlManager::currentURIModule();       

require_once("$module_auth_dir_name/../../../$uri_module/ini.php");
require_once("$module_auth_dir_name/../../../$uri_module/module_config.php");

include_once ("$module_auth_dir_name/../../../$uri_module/application_config.php");
AfwSession::initConfig($config_arr, "system", "$module_auth_dir_name/../../../$uri_module/application_config.php");
AfwSession::startSession();
$lang = AfwSession::getSessionVar("lang");
if(!$lang) $lang = "ar";


require_once ("$module_auth_dir_name/../../../config/global_config.php");
// 

$file_dir_name = dirname(__FILE__);


$html_debugg_login = true;  // useful in prod
$debugg_login = false;
$debugg_login_die = true; 
$debugg_after_login = true;
$debugg_after_ldap = true;
$debugg_after_golden_or_db = true;
$debugg_after_session_created = true;
//$check_employee_from_external_system = true;
$login_dbg = array();
$result_message = "";
if(!$login_page_options) $login_page_options = AfwSession::config("login_page_options", array());
if(AfwSession::userIsConnected()) 
{
        header("Location: index.php");
} 
elseif(($_POST["mobile"]) and ($_POST["idn"]) and ($_POST["resetGo"]))
{
        
        $dtm = date("YmdHis");
        $my_debug_file = "debugg_reset_pwd_${logbl}_$dtm.log";
        //die("AFWDebugg::initialiser(".$DEBUGG_SQL_DIR.$my_debug_file.")");
        AFWDebugg::initialiser($DEBUGG_SQL_DIR,$my_debug_file);
        AFWDebugg::log("reset process starting");
        AfwSession::resetSession();
        $idn    = AfwStringHelper::clean_input($_POST["idn"]);
        $mobile = AfwStringHelper::clean_input($_POST["mobile"]);

        list($idn_correct, $idn_type) = AfwFormatHelper::getIdnTypeId($idn);
        
        if(!$idn_correct)
        {
                $result_message = AfwLanguageHelper::tt("FORMAT-SA-IDN", $lang);
        } 
        elseif(!AfwFormatHelper::isCorrectMobileNum($mobile))
        {
                $result_message = AfwLanguageHelper::tt("FORMAT-SA-MOBILE", $lang);
        } 
        else
        {
                list($error, $info, $warning) = AfwLoginUtilities::reset_pwd_for($idn_type, $idn, $mobile, $lang);
                if($error) 
                {
                        $result_message .= $error;
                        if($warning) $result_message .= $warning;
                }
                else
                {
                        //die("list($error, $info, $warning) = AfwLoginUtilities::reset_pwd_for($idn_type, $idn, $mobile, $lang)");
                        //if($info) AfwSession::pushInformation($info);
                        // if($warning) AfwSession::pushWarning($warning);
                        AfwSession::pushInformation(AfwLanguageHelper::tt("A new password has been sent to your mobile number")." : $mobile");
                        //die("information = ".AfwSession::getSessionVar("information"));
                        // strange behavior header seems to reset
                        // header("Location: index.php");
                        // header("Location: login.php");
                        include("$file_dir_name/../../../award/index.php");
                        return;
                }
                
        }

        
}

// @todo should be dynamic 
//if(!$login_by) $login_by = "اسم المستخدم أو الجوال أو البريد الالكتروني  ";
$nom_site = $NOM_SITE[$lang];
$desc_site = $DESC_SITE[$lang];
$welcome_site = $WELCOME_SITE[$lang];
$user_prefix = $USER_PREFIX[$lang];

if(!$user_prefix)  $login_title = $nom_site;
else $login_title = $user_prefix;
if(!$login_by) $login_by = "اسم المستخدم";
$login_by_sentence = "يمكنك تسجيل الدخول إلى $nom_site باستخدام ". $login_by . " ثم كلمة المرور";
$no_menu = AfwSession::config("no_menu_for_login", true);
$body_css_class = "hzm_body hzm_login";



include("$file_dir_name/../web/hzm_header.php");
if($desc_site)
{	
   echo "<div class='hzm_intro modal-dialog'>
              <div class='modal-header'>
                        <div>
                                <h2 class='title_intro'>$welcome_site</h2>        
                        </div>
              </div>
              <div class='modal-body'>
                   $desc_site
              </div>
         </div>";
}
?>
<div class="home_banner register_banner">
<div class="modal-dialog reset_pwd popup-register">
        <div class="modal_login modal-content">
                <div class="modal-header">
                        <div>
                                <a href="index.php" title="الرئيسسة">
                                        <img src="../<?=$MODULE?>/pic/logo<?=$XMODULE?>.png" alt="<?=$login_by_sentence?>" title="<?=$login_by_sentence?>"></a>
                                        
                                <h2 class='title_login'>تصفير كلمة المرور</h2>        
                        </div>
                </div>
                    <?
                       if($result_message)
                       {
                    ?>
                        <div class="quote">
                            <div class="quoteinn">
                               <p><font color='red'><?=$result_message?></font></p>
                            </div>
                        </div>
                    <? 
                       }
                                
                    ?>
                <div class="modal-body"><h1><?=$user_prefix?>قم بادخال البيانات التالية</h1>
                        <form id="formlogin0" name="formlogin0" method="post" action="<?echo $action_page?>"  onSubmit="return checkForm();" dir="ltr" enctype="multipart/form-data">
                        <?php                                
                                if(AfwSession::config("sms-captcha-register",true))
                                {
                                ?>
                                <div class="form-group width_pct_50">
                                        <label class="hzm_label hzm_label_customer_cpt">أدخل الرمز البصري
                                        </label>                                        
                                        <input type="text" class="form-control" id="customer_cpt" name="customer_cpt" value=""  autocomplete="off" required>
                                                                                
                                </div>
                                <div class='form-group width_pct_50 hzm_captcha'>
                                        <label class="hzm_label hzm_label_customer_cpt">الرمز البصري
                                        </label>
                                        <img style="width: 100%;height: 42px;" src="../lib/afw/afw_captcha.php" />                                        
                                </div>
                                <div class="form-group width_pct_100 hzm_help"> 
                                        upper or lower case doesn't matter <br>
                                        لا يهم حجم الحرف صغيرا كان أو كبيرا
                                                                                <br>
                                                قم بتحديث الصفحة إذا لم يكن الرمز واضحا
                                        
                                </div>
                                <?php                                
                                }
                                ?>
                                <div class="form-group width_pct_50">
                                        <label>رقم الهوية</label>
                                        <input class="form-control" type="text" name="idn"  id="idn"  value="" autocomplete="off" required>
                                </div>
                                <div class="form-group width_pct_50">
                                        <label>رقم الجوال</label>
                                        <input class="form-control" type="text" name="mobile" id="mobile" value="" autocomplete="off" required>                                        
                                </div>
                                <!-- logbl:<?php echo $logbl?> -->
                                <div class="form-group submit-login">
                                    <input type="submit" class="btnblogin btnbtsp btn-primary reset_p" value="إرسال كلمة المرور الجديدة" name="resetGo">&nbsp;
                                </div>
                                
                        </form>
                </div>
                <?
                if($login_page_options["register_as"])
                {
                ?>
                <div class="modal-footer">
                        <div class="login-register">
                            <a class="btnbregister btnbtsp btn_link" href="<?php echo $login_page_options["register_as"];?>_register.php">التسجيل لأول مرة</a>
                        </div>
                </div>
                <?
                }
                ?>
        </div>
</div>
</div>
        

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
        $("html, body").animate({ scrollTop: $(document).height() }, "slow");
	document.getElementById("email").focus();
});

function checkForm() 
{
	if($("#email").val() == "" || (($("#pwd").val() == "") && ($("#oublie").val() == "N"))) {
		alert("الرجاء إدخال بيانات تسجيل الدخول كاملة");
		return false;
	} else {
		return true;
	}
}

</script>
<!-- log :
<?php

if($html_debugg_login) echo implode("\n", $login_dbg);



// old gorboj
/* 
        
         

        if($user_connected)
        {
                $_SESS ION["userDn"] = $info["distinguishedname"][0];
                $_SESS ION["userName"] = $info["cn"][0];
                $_SESS ION["userFullName"] = $info["displayname"][0];
                $_SESS ION["userDepartment"] = $info["department"][0];
                $_SESS ION["userOffice"] = $info["physicaldeliveryofficename"][0];
                
                $login_dbg .= '<pre>';
                $login_dbg .= var_export($_SESS ION,true);
                $login_dbg .= '</pre>';

        }
        */
?>        
-->