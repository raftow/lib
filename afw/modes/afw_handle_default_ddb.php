<?php
require_once(dirname(__FILE__)."/../../../config/global_config.php");
$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}

$class = $_POST["class_obj"];
$currmod = $_POST["currmod"];
//$file  = $_POST["file_obj"];
$nb_objs    = $_POST["nb_objs"];

$list_objs_ids = array();

AFWDebugg::setEnabled(true);
//AFWObject::setDebugg(true);
//AFWDebugg::initialiser("C:\\dbg\\debug\\","afw_debugg.txt");
$real_nb_objs = 0;
$updated_nb_objs = 0;

$mainObject = new $class();

$ddb_field_arr = array();

$class_db_structure = $class::getDbStructure($return_type="structure", $attribute = "all");
        
foreach($class_db_structure as $nom_col => $desc)
{
     $mode_field_edit = AfwStructureHelper::attributeIsEditable($mainObject, $nom_col);
     if($mode_field_edit) $ddb_field_arr[$nom_col] = $desc;
}

$objects_to_keep = array();
$objects_to_delete = array();
             

// save all ddb objects
for($i=0;$i<$nb_objs;$i++)
{
        $pki = "id_$i";
        $ddb_actioni = "ddb_action_$i";
        $id    = $_POST[$pki];
        $ddb_action    = $_POST[$ddb_actioni];
        
        $obj = new $class();
        $is_load = false;
        //AFWDebugg::log("obj $i of $class class, id ='$id' isInteger=".isInteger($id));
        if(($id) && (($obj->PK_MULTIPLE) || (is_numeric($id) && ($id>0))))
        {
        	//AFWDebugg::log("try to load $class row $id");
                if($obj->load($id)) $is_load = true;
                else 
                {
                        $return_message = $myObj->tm("Return back", $lang);    
                        $return_page = "main.php?Main_Page=afw_mode_qsearch.php&cl=$class&currmod=$currmod";
                        $die_message = $myObj->tm("Object can not be loaded, seems has been deleted !", $lang);            
                        $technical = "mode edit load by id failed : >> $class load by [id=$id]";
                        throw new AfwBusinessException($die_message, $lang, "be-record-not-found.png", $return_message,$return_page, $technical);                        
                }
        
        }
        else
        {
               $_POST["id_$i"] = "";
        }

        if($ddb_action==2) $objects_to_keep[] = $obj;
        if($ddb_action==1) $objects_to_delete[] = $obj;
        

        //if(!$is_load)  AFWDebugg::log("failed to load $class row $id");
        // if($i==1) die(var_export($obj,true));
        foreach($ddb_field_arr as $nom_col => $desc)
        {
                $ddb_nom_col = $nom_col . "_" . $i;
                
                //if($nom_col=="owner_id" and $i==1) die("owner_id $i = ".$_POST[$ddb_nom_col]);
                $yn_checkbox = (($desc["TYPE"]=="YN") and ($desc["CHECKBOX"]));
                if(isset($_POST[$ddb_nom_col]) or $yn_checkbox) 
                {
        		
                        if(is_array($_POST[$ddb_nom_col]))
        			$val = ','.implode(',', $_POST[$ddb_nom_col]).',';
        		else
        			$val = $_POST[$ddb_nom_col];
                        
                        if($yn_checkbox)
                        {
                            //if(($nom_col=="mode_search") and ($i==3)) die("for col [$nom_col] i=$i : posted_val[$ddb_nom_col]=[$val] from ".var_export($_POST,true));
                            if($val=="1") $val = "Y";
                            else $val = "N";
                        }
                                
                        // if($nom_col=="owner_id" and $i==1) echo "owner_id $i => before set $obj val of $nom_col = ".$obj->getVal($nom_col);
                        if(($nom_col!="id") and ($nom_col!=$obj->getPKField())) $obj->set($nom_col, $val);
                        // if($nom_col=="owner_id" and $i==1) echo "owner_id $i =>  after set $obj val of $nom_col = ".$obj->getVal($nom_col);
                        //if($nom_col=="owner_id" and $i==1) die("owner_id $i => $obj -> setted ($nom_col, $val) ");
                        
                        
        	}
        	
        }

        if(true)
        {
                // if($nom_col=="owner_id") echo("owner_id $i => before update updated_nb_objs = $updated_nb_objs ");
                $obj->sql_action = "update";
                $obj->sql_info = "ddb handle object num $i ";
                $updated_nb_objs += $obj->update();
                // if($nom_col=="owner_id") echo("owner_id $i => after update updated_nb_objs = $updated_nb_objs ");
                // if($nom_col=="owner_id" and $i==4) die(" -- stopped by rafik --");
                //AFWDebugg::log("update row id $id updated_nb_objs become $updated_nb_objs");
        }
        $id = intval($id);
        	
        if($id and ($ddb_action != 3))
        {
              
              $list_objs_ids[$id] = $id;
              $real_nb_objs++;  
        }
        

        unset($obj);
}


// executer l'action ddb
if($_POST["submit_ddb"])
{
    if(count($objects_to_keep) != 1) die("only one object is to keep : ".var_export($objects_to_keep,true));
    if(count($objects_to_delete) == 0) die("at least one object to delete : ".var_export($objects_to_delete,true));
    $objectToKeep = $objects_to_keep[0];
    $objectToKeepId = $objectToKeep->getId();
    if(!$objectToKeepId) die("object to keep is empty: ".var_export($objectToKeep,true));
    
    foreach($objects_to_delete as $objectToDelete)
    {
         $objectToDeleteId = $objectToDelete->getId();
         $objectToDelete->delete($objectToKeepId);
         unset($list_objs_ids[$objectToDeleteId]);    
    }
    

}




if($updated_nb_objs>0) AfwSession::pushSuccess(AfwLanguageHelper::translateKeyword("save_with_sucess", $lang) . " $updated_nb_objs ".AfwLanguageHelper::translateKeyword("record(s)", $lang));
else AfwSession::pushInformation(AfwLanguageHelper::translateKeyword("no_update_found", $lang));


$ids = implode(",",$list_objs_ids);
include("afw_mode_ddb.php");




?>