<?php
// obsolete
/*
set_time_limit(8400);
ini_set('error_reporting', E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);


require_once("$file_dir_name/../lib/afw/afw_autoloader.php");
$uri_module = AfwUrlManager::currentURIModule();       


include_once ("$direct_dir_name/ini.php");
include_once ("$direct_dir_name/module_config.php");
include_once ("$direct_dir_name/application_config.php");
AfwSession::initConfig($config_arr,.....);




// rafik : should be after the above includes to avoid objme : __PHP_Incomplete_Class Auser  or Employee or Sempl etc ....
AfwSession::startSession();
//setcookie(session_name(), session_id(), NULL, NULL, NULL, 0);
//die("rafik 3002 session table : ".var_export($_SES SION,true));
require_once("$direct_dir_name/../config/global_config.php");
// 

$only_members = false;



foreach($_GET as $col => $val) ${$col} = $val;
foreach($_POST as $col => $val) ${$col} = $val;

include("$direct_dir_name/../lib/afw/afw_check_member.php");

include("tmkn_model_drawing.php");
$width_canvas = 2900;
$height_canvas = 1400;
$zoom=1;
$square_nb_small=4;
$width_small=28;

// Create image
$canvas = imagecreatetruecolor($width_canvas, $height_canvas);
$bgc = imagecolorallocate($canvas, 255, 255, 255);


imagefilledrectangle($canvas, 0, 0, $width_canvas, $height_canvas, $bgc);
drawGrid($canvas,  $width_canvas, $height_canvas, $square_nb_small, $width_small);

$ap_id = 1272;
$mid = 1301;
$tid = 13775;


$moduleObj = Module::loadById($mid);

$tableList = $moduleObj->get("tables");
$originX = array();
$originY = array();

$diagram_config = $moduleObj->getVal("web");

$diagram_config_rows = explode("\n", $diagram_config);
$diagram_config_row_log = "";
$tableListByName = array();
$atable_id_list_arr = array_keys($tableList);
$exclude_field_matrix = array();
$pos_field_matrix = array();

foreach($diagram_config_rows as $diagram_config_row)
{
    list($param,$value) = explode("=", $diagram_config_row);
    $value = trim($value);
    $param = trim($param);
    if(!$param)
    {
    
    }
    elseif($param=="exclude_fields")  // case of excluded fields
    {
          $exclude_fields_arr = explode(",",$value);
          foreach($exclude_fields_arr as $exclude_field)
          {
              list($exclude_field_table, $exclude_field_name) = explode(".",$exclude_field);
              $exclude_field_matrix[$exclude_field_table][$exclude_field_name] = true;
          }
    }
    elseif($param=="pos_fields")  // case of position of arrow for fields
    {
          $pos_fields_arr = explode(",",$value);
          foreach($pos_fields_arr as $pos_field)
          {
              list($pos_field_table, $pos_field_name, $pos) = explode(".",$pos_field);
              $pos_field_matrix[$pos_field_table][$pos_field_name] = $pos;
          }
    }
    else  // case of table location in diagram
    {
          $table_name = $param;
          $tableListByName[$table_name] = array('enabled'=>true, 'internal'=>false, 'item'=>null);
          if($value=="center") $value = "c,m";
          list($value_x, $value_y, $size_X, $nb_Cols_Max) = explode(",", $value);
          $param = trim($param);
          $value_x = trim($value_x);
          $value_y = trim($value_y);
          
          $size_X = trim($size_X);
          if(!$size_X) $size_X = 12;
          $sizeX[$table_name] = $size_X;
          
          $nb_Cols_Max = trim($nb_Cols_Max);
          if(!$nb_Cols_Max) $nb_Cols_Max = 7;
          $nbColsMax[$table_name] = $nb_Cols_Max;
          
          
          if(!is_numeric($value_x)) $value_x = convert_position_name($value_x);
          if(!is_numeric($value_y)) $value_y = convert_position_name($value_y);
          
          $originX[$table_name] = $value_x * $width_small;
          $originY[$table_name] = $value_y * $width_small;
          
          $diagram_config_row_log .= "diagram_config_row=$diagram_config_row : value=$value value_y=$value_y value_x=$value_x, sizeX=$size_X, nbColsMax=$nb_Cols_Max <br>\n";
    }
}
// die("originX = ".var_export($originX,true)." originY = ".var_export($originY,true)." diagram_config_row_log = ".$diagram_config_row_log);

$rands_done = array();

// Make index by name and load FKs
foreach($tableList as $tableItem)
{
   $table_name = $tableItem->getVal("atable_name");
   $tableListByName[$table_name]["enabled"] = true;
   $tableListByName[$table_name]["internal"] = true;
   $tableListByName[$table_name]["item"] = $tableItem;
   $tableListByName[$table_name]["exclude_fields"] = $exclude_field_matrix[$table_name];
   $tableListByName[$table_name]["pos_fields"] = $pos_field_matrix[$table_name];
   
    
}


// Complete Table list with external Tables 

foreach($tableListByName as $table_name => $tableRow)
{
        if(!$tableRow["item"])
        {
             $tableListByName[$table_name]["item"] = Atable::loadByMainIndex($ap_id, $table_name);
             $tableListByName[$table_name]["exclude_fields"] = $exclude_field_matrix[$table_name];
             $tableListByName[$table_name]["pos_fields"] = $pos_field_matrix[$table_name];
             $atable_id_list_arr[] = $tableListByName[$table_name]["item"]->getId();
        }
}

foreach($tableListByName as $table_name => $tableRow)
{
        $tableItem = $tableRow["item"];
        if($tableItem)
        {
            $tableListByName[$table_name]["relatedFieldsToMe"] = $tableItem->getDetailTablesRelationFields($entity_relation_type_id = 0, $atable_id_list_arr);
        }
        
        
        $origin_x = $originX[$table_name]; 
        $origin_y = $originY[$table_name];
        $nb_cols_max = $nbColsMax[$table_name];
        $size_x = $sizeX[$table_name];
        
        $x_rand = round(rand(1,30));
        $y_rand = round(rand(1,20));
        if(!$origin_x) $origin_x = 40 * $x_rand;
        if(!$origin_y) $origin_y = 40 * $y_rand;
        
        if($tableItem) list($tableListByName[$table_name]["showedFieldList"], $otherFields) = drawTable($canvas, $tableItem, $origin_x, $origin_y, "lt", $width_small, $zoom, $size_x, $nb_cols_max);
        else die("$table_name not loaded");
}        

drawRelatedFKs($canvas, $tableListByName, $originX, $originY, $sizeX, $nbColsMax, "lt", $width_small, $zoom);
 

// Output and free from memory
header('Content-Type: image/jpeg');

imagejpeg($canvas);
imagedestroy($canvas);
*/
