<?php
session_start();

require_once dirname(__FILE__)."/../captcha/src/Gregwar/Captcha/CaptchaBuilder.php";
require_once dirname(__FILE__)."/../captcha/src/Gregwar/Captcha/PhraseBuilder.php";
// die("rafik start captcha");
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
