<?php
$r = "control";
$file_dir_name = dirname(__FILE__); 
$Direct_Page = "afw_upload_files.php";
require("$file_dir_name/afw_main_page.php");
AfwMainPage::echoDirectPage($My_Module, $Direct_Page, $file_dir_name, []);

?>