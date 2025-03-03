<?php 
        list($code_error, $text_error) = explode(":", $error_message);
        $code_error = substr(md5(trim($code_error)),0,5);
        $show_technical_details = "show";
        if(!AfwSession::config("MODE_DEVELOPMENT",false))
        {
                $show_technical_details = "hide";
        }
        $lang = AfwLanguageHelper::getGlobalLanguage();
        $xmodule = AfwSession::getCurrentlyExecutedModule();
        $company = AfwSession::currentCompany();
?>
<link href="/client-<?php echo $company ?>/css/common-<?php echo $xmodule ?>.css" rel="stylesheet" type="text/css" type="text/css">
<link href="/client-<?php echo $company ?>/css/common-<?php echo $xmodule ?>-<?php echo $lang ?>.css" rel="stylesheet" type="text/css" type="text/css">
<div class="body_front_error">     
        <p class="message">
                <?php echo $error_message?>
                <br>                
        </p>
</div>