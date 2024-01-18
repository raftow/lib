<?php

$file_dir_name = dirname(__FILE__);
require_once("$file_dir_name/../afw/afw_autoloader.php");
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
$lang = "en";


        
AfwSession::startSession();

require_once("$file_dir_name/../../external/db.php");
// old include of afw.php
$only_members = true;
$debug_name = "autocomplete";
require("$file_dir_name/../lib/afw/afw_check_member.php");

if(!$objme) $objme = AfwSession::getUserConnected();
$lang = AfwSession::getSessionVar("lang");
if(!$lang) $lang = "ar";
 
// 

// prevent direct access
/*
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}*/
 
// get what user typed in autocomplete input
$cl = trim($_GET['cl']);
$currmod = trim($_GET['currmod']);
$clwhere = trim($_GET['clwhere']);
if($clwhere) die("use of clwhere in autocomplete is removed");

$clp = trim($_GET['clp']);
$idp = trim($_GET['idp']);
$modp = trim($_GET['modp']);
$attp = trim($_GET['attp']);
$debugg = trim($_GET['debugg']);

if($currmod) AfwAutoLoader::addMainModule($currmod);
if($modp and ($modp != $currmod)) AfwAutoLoader::addModule($modp);

$myObjParent = new $clp();
if($idp>0) $myObjParent->load($idp);
$clause_where = $myObjParent->getWhereOfAttribute($attp);



$term = trim($_GET['term']);
 
$a_json = array();
$a_json_row = array();
 
$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "فقط الحروف..."));
$json_invalid = json_encode($a_json_invalid);
 
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
 
// SECURITY HOLE ***************************************************************
// allow space, any unicode letter and digit, underscore and dash
/*
if(preg_match("/[^\040\pL\pN_-]/u", $term)) {
  print $json_invalid;
  exit;
}*/
// *****************************************************************************
 
$myObj = new $cl();

if($debugg)
{
    if($myObj->AUTOCOMPLETE_EXACT_SEARCH)
    {
          $allSQL = AfwLoadHelper::findExact($myObj, $term, true);
    }
    else
    {
          $parts = explode(' ', $term);
          $allSQL = AfwFrameworkHelper::find($myObj, $parts, $clause_where, $sql_operator = " AND ", true);
    }
    
} 



if($debugg and $objme and ($objme->isAdmin() or ($objme->id==4))) die("debugg mode : object $myObjParent for attribute $attp clause_where is [$clause_where] allSQL = $allSQL");

if($myObj->AUTOCOMPLETE_EXACT_SEARCH)
{
        $listObj = AfwLoadHelper::findExact($myObj, $term);
        if($debugg) echo "count(find Exact result) = ".count($listObj);
}
else
{
        $parts = explode(' ', $term);
        $p = count($parts);
        $listObj = AfwFrameworkHelper::find($myObj, $parts,$clause_where);
        if($debugg) echo "count(find result) = ".count($listObj);
}
 

 
foreach($listObj as $idObj => $iObj) 
{
  $a_json_row["id"] = $idObj;
  $a_json_row["value"] = $iObj->getDropDownDisplay($lang);
  $a_json_row["label"] = $a_json_row["value"];
  array_push($a_json, $a_json_row);
}
 
// highlight search results
//$a_json = apply_highlight($a_json, $parts);
 
$json = json_encode($a_json);
print $json;
?>