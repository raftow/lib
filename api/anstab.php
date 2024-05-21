<?php

$file_dir_name = dirname(__FILE__);
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
if(!$lang) $lang = "ar";

// old include of afw.php


$attr_arr = array();
$relative_path = "../";
require_once("$file_dir_name/../afw/afw_error_handler.php");
require_once("$file_dir_name/../afw/afw_autoloader.php");
foreach($_GET as $col => $val) 
{
        if(AfwStringHelper::stringStartsWith($col,"post_attr_"))
        {
           $nom_col = substr($col,10);
           $attr_arr[$nom_col] = $val; 
        }
        else ${$col} = $val;
}
foreach($_POST as $col => $val)
{
        if(AfwStringHelper::stringStartsWith($col,"post_attr_"))
        {
           $nom_col = substr($col,10);
           $attr_arr[$nom_col] = $val; 
        }
        else ${$col} = $val;
}


if($currmod) AfwAutoLoader::addModule($currmod);
AfwSession::startSession();
require_once("$file_dir_name/../../external/db.php");

// die(var_export($attr_arr,true));


if(!$MODULE) 
{
        $MODULE = $currmod;
}

if($MODULE != $currmod)
{
        if($MODULE) AfwAutoLoader::addModule($MODULE);
}

$debug_name = "get_dropdown_elements";
$obj = new $cl();
$desc = AfwStructureHelper::getStructureOf($obj,$attribute);
if(!$obj->answerTableForAttributeIsPublic($attribute,$desc))
{
        $only_members = true;
        include("$file_dir_name/../pag/check_member.php");
}


foreach($attr_arr as $nom_col => $val)
{
   $obj->set($nom_col,$val);
}

$ans_tab_where = $obj->getSearchWhereOfAttribute($attribute);

if($debugg or $debug)
{
        echo var_export($obj,true);  
        die(" ::: ans_tab_where = ".var_export($ans_tab_where,true));
}




$nom_table_fk   = $desc["ANSWER"];
$nom_module_fk  = $desc["ANSMODULE"];
if(!$nom_module_fk)
{
        
        $nom_module_fk = AfwUrlManager::currentWebModule();
}
$nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
$nom_fichier_fk = AfwStringHelper::tableToFile($nom_table_fk);
/*
if($nom_module_fk)
{
     $full_file_path = $file_dir_name."/../$nom_module_fk/".$nom_fichier_fk;
     
}
else
{
     $full_file_path = $file_dir_name."/".$nom_fichier_fk;
}

if(!file_exists($full_file_path))
{
     throw new AfwRuntimeException("Impossible de charger $full_file_path in type_input($col_name) for $obj");
}

require_once $full_file_path;*/
//AfwSession::getLog();
$obj_rep      = new $nom_class_fk();
$obj_rep->select_visibilite_horizontale();
if($ans_tab_where) $obj_rep->where($ans_tab_where);
if($dbg)
{
        die("ans_tab_where=$ans_tab_where SQL = ".$obj_rep->getSQLMany());
}

$liste_rep = $obj_rep->loadMany();


$response_arr = array();

foreach ($liste_rep as $iditem => $item) 
{
        /* if(AfwUmsPagHelper::userCanDoOperationOnObject($item,$objme,'display'))*/
		$response_arr[$item->getId()]=$item->getDisplay($lang);
}

//echo AfwSession::getLog();

echo json_encode($response_arr);

?>