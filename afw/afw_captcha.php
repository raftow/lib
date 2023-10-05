<?php
session_start();
$file_dir_name = dirname(__FILE__);
require_once "$file_dir_name/../captcha/src/Gregwar/Captcha/CaptchaBuilder.php";
require_once "$file_dir_name/../captcha/src/Gregwar/Captcha/PhraseBuilder.php";

header('Content-type: image/jpeg');

$phrb = new PhraseBuilder();
$_SESSION["cpt"] = strtoupper($phrb->build());

$cpt = CaptchaBuilder::create($_SESSION["cpt"]);
$cpt->build();
$cpt->output();
