
<?php
die("handle delete is obsolete");
/*
require_once(dirname(__FILE__)."/../../../config/global_config.php");
$class = $_POST["class_obj"];
$file  = $_POST["file_obj"];
$currmod  = $_POST["currmod"];
$id    = $_POST["id_obj"];
$id_replace    = $_POST["id_replace"];

$file_dir_name = dirname(__FILE__); 
require_once("$file_dir_name/../$currmod/$file");

//AFWDebugg::setEnabled(true);
////AFWObject::setDebugg(true);
//AFWDebugg::initialiser("","afw_debugg.txt");
AfwMainPage::addOutput('<table cellpadding="4" cellspacing="4" class="card"><tr align="center"><td>';
//$cl  = $_REQUEST['cl'];
$obj = new $class();

if($id and $obj->load($id))
{
	if($obj->delete($id_replace))
        {
              AfwMainPage::addOutput( "<br><p class='alert-success'><br><span>تم مسح العنصر بنجاح</span><br></p>";
        }
        else
        {
        
              AfwMainPage::addOutput( "<br><p class='alert-error'><br><span>لم يتم مسح العنصر بنجاح</span><br></p>";
        }
}
else
{

      AfwMainPage::addOutput( "<br><p class='alert-error'><br><span>سجل غير موجود أو تم مسحه سابقا</span><br></p>";
}

// AfwMainPage::addOutput( '<br><br><br><center><a href="main.php?Main_Page=afw_mode_search.php&cl='.$class.'&currmod='.$currmod.'&lastsearch=true"><span class="greenbtn btn">الرجوع إلى البحث السابق</span></a></center>';
AfwMainPage::addOutput( '</td></tr></table>';
//AfwSession::startSession();        
//header("Location: main.php?Main_Page=afw_mode_search.php&cl=$class&lastsearch=true");
//header("Location: main.php");
*/
?>


