<?php
session_start();
$file_dir_name = dirname(__FILE__);
require_once "$file_dir_name/../captcha/src/Gregwar/Captcha/CaptchaBuilder.php";
require_once "$file_dir_name/../captcha/src/Gregwar/Captcha/PhraseBuilder.php";

header('Content-type: image/jpeg');
try
{
    $phrb = new PhraseBuilder();
    $cpt_phrase = strtoupper($phrb->build());    
    $cpt = CaptchaBuilder::create($cpt_phrase);
    $cpt->build();
    $_SESSION["cpt"] = $cpt_phrase;
    $cpt->output();    
}
catch(Exception $e)
{
    throw new AfwRuntimeException("CaptchaBuilder error : ".$e->getMessage()." traces : ".$e->getTraceAsString());
}
