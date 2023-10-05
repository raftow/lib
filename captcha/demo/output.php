<?php

require_once '/var/www/html/lib/captcha/src/Gregwar/Captcha/CaptchaBuilder.php';


header('Content-type: image/jpeg');

$cpt = CaptchaBuilder::create();
$cpt->build();
$cpt->output();

;
