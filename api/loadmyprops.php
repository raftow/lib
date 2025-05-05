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
if(!$cl) die("No orginal class defined");
if(!$attribute) die("No attribute defined");
if(!$attributeval) die("No attribute value defined");

if(!$objme) $objme = AfwSession::getUserConnected();


if($currmod) AfwAutoLoader::addModule($currmod);
if($attributemod) AfwAutoLoader::addModule($attributemod);
$required_modules = AfwSession::config("required_modules", []);
foreach($required_modules as $required_module)
{
    AfwAutoLoader::addModule($required_module);
}

AfwSession::startSession();
require_once("$file_dir_name/../../config/global_config.php");

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
if($objid) $obj = $cl::loadById($objid);
else $obj = new $cl();

$obj->set($attribute, $attributeval);

$attributeObj = $obj->het($attribute);

$response_arr = array();
$response_arr["display"] = $attributeObj->getDisplay($lang);

if(!$attributeObj) $response_arr["error"] = "No object found for attribute=$attribute attributeval=$attributeval";
else
{
        if($debugg or $debug)
        {
                echo "<b>the object is a new $cl</b> <br>";
        }
        
        $desc = AfwStructureHelper::getStructureOf($obj,$attribute);
        if($debugg or $debug)
        {
                echo "<b>the structure of attribute $attribute</b> : <br>";  
                echo var_export($desc,true)."<br><br>";
        }
        
        
        
        if($debugg or $debug)
        {
                echo "<b>the object</b> : <br>";  
                echo var_export($obj,true)."<br><br>";
        }

        $myPropsConfig = AfwJsEditHelper::getAttributeLoadMyPropsItems($desc);

        $loadablePropsArr = $attributeObj->loadablePropsBy($objme);
        if(count($loadablePropsArr)==0)
        {
                $response_arr["error"] = "No attribute is prop-loadable by this user please review the implementation of loadablePropsBy method in class : ".get_class($attributeObj);
        }

        foreach($loadablePropsArr as $loadableAttribute)
        {
                $loadableAttributeDestination = $myPropsConfig[$loadableAttribute];
                if($loadableAttributeDestination) $response_arr[$loadableAttributeDestination] = $attributeObj->getVal($loadableAttribute);
                else
                {
                        if($debugg or $debug) echo "myPropsConfig=".var_export($myPropsConfig,true);
                        $response_arr[$loadableAttribute] = "is loadable but not configured in LoadMyPropsItems setted in DEPENDENT_OFME property in structure of attribute $attribute";
                }
        }
}


/*
foreach($attr_arr as $nom_col => $val)
{
        $obj->set($nom_col,$val);
        if($debugg or $debug)
        {
                echo "<b>the object->set($nom_col, $val)</b> : <br>";
        }
}
*/
/*
if($debugg or $debug)
{
        if(($cl == "Acondition") and ($attribute == "aparameter_id"))
        {
                echo "<br><b>the object->calc(afield_type_id)</b> : <br>"; 
                echo $obj->calc("afield_type_id"); 
        }
}
*/


/*

$nom_table_fk   = $desc["ANSWER"];
$nom_module_fk  = $desc["ANSMODULE"];
if(!$nom_module_fk)
{
        
        $nom_module_fk = AfwUrlManager::currentWebModule();
}
$nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
$nom_fichier_fk = AfwStringHelper::tableToFile($nom_table_fk);*/

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
/*
$obj_rep      = new $nom_class_fk();

if($debugg or $debug)
{
        echo "<br><b>the object ans tab is a new $nom_class_fk</b> <br>";
}

$obj_rep->select_visibilite_horizontale();
if($ans_tab_where) $obj_rep->where($ans_tab_where);

if($debugg or $debug)
{
        echo "<br>SQL = ".$obj_rep->getSQLMany();
        echo "<br>";
}

$liste_rep = $obj_rep->loadMany();


$response_arr = array();

foreach ($liste_rep as $iditem => $item) 
{
		$response_arr[$item->getId()]=$item->getDisplay($lang);
}
*/

//echo AfwSession::getLog();



echo json_encode($response_arr);

?>