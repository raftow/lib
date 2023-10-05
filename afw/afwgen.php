<?php
require_once(dirname(__FILE__)."/../../external/db.php");
require_once("afw_generator.php");
// old require of afw_debugg
// old require of afw_ini


$only_admin = true;
$debug_name = "debugg_afwgen";
require_once("afw_check_member.php");

include("hzm_header.php");
        
$tbl = $_GET["tbl"];
$nb = 0;
$gen = new AfwGenerator();
$dir = "C:\\dbg\\etude";
$lang = "ar";
$list_php_files_to_copy = $gen->generateModulePhpClasses($dir);
$gen->generateTrad($dir, $lang);
$gen->generateLookupEnumFile($dir);
$gen->generateModulePhpClasses($dir);

echo "$nb fichier(s) php generated(s)\n<br>";
echo "<textarea rows='10' cols='120'>$list_php_files_to_copy</textarea>";
include("hzm_footer.php");

?>
