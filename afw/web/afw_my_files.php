<?php
$r = "control";
$file_dir_name = dirname(__FILE__); 
$Direct_Page = "afw_upload_files.php";

CmsMainPage::echoDirectPage($My_Module, $Direct_Page, $file_dir_name, []);

?>