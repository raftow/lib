<?php 
        list($code_error, $text_error) = explode(":", $error_message);
        $code_error = substr(md5(trim($code_error)),0,5);
        $show_technical_details = "show";
        if(!AfwSession::config("MODE_DEVELOPMENT",false))
        {
                $show_technical_details = "hide";
        }

        $xmodule = AfwSession::getCurrentlyExecutedModule();
?>
<style>
body{
        font-family:tahoma;
}
.body_front_error {
        float: right;
        width: 60%;
        display: block;
        margin-top: 55px;
        margin-left: 20%;
        margin-right: 20%;
        background-color: #4fab9c;
        color: white;
        padding: 10px;
        margin-bottom: 80px;
}    

.body_front_error>p {
        color: white;
}




</style>
<link href="../../external/css/common-<?php echo $xmodule ?>.css" rel="stylesheet" type="text/css" type="text/css">
<div class="body_front_error">     
        <p>
                انتهت الجلسة. سجل الدخول مرة أخرى
        </p>
        <p class="message">
                <?php echo $error_message?>
                <br>                
        </p>
</div>