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
$header_imbedded = $_POST["header_imbedded"];
$fixm_cols    = explode(",",$_POST["fixm_cols"]);
$fixm_vals    = explode(",",$_POST["fixm_vals"]);
$fixm_array = array();
$fixm = "";
for($i=0;$i<count($fixm_cols);$i++)
{
      $fixm_array[$fixm_cols[$i]]=$fixm_vals[$i];
      if($fixm) $fixm .= ",";
      $fixm .= $fixm_cols[$i]."=". $fixm_vals[$i];
}

$list_objs_ids = "";
// die("hand def qed _POST = ".var_export($_POST,true));

// AFWDebugg::setEnabled(true);
// //AFWObject::setDebugg(true);
// AFWDebugg::initialiser("C:\\dbg\\debug\\","afw_debugg.txt");
$real_nb_objs = 0;
$updated_nb_objs = 0;

$class_db_structure = $class::getDbStructure($return_type="structure", $attribute = "all");

for($i=0;$i<$nb_objs;$i++)
{
        $pki = "id_$i";
        $id    = $_POST[$pki];
        /**
         * @var AFWObject $obj
         */
        $obj = new $class(); 
               
        $record_is_loaded = false;
        $unique_pk_id = (is_numeric($id) && ($id>0));
        // if($id == 6082) die("obj $i of $class class, id ='$id' will be loaded unique_pk_id = $unique_pk_id");
        if($id and ($unique_pk_id or $obj->PK_MULTIPLE))
        {
        	//AFWDebugg::log("try to load $class row $id");
                // if($id == 6082) die("obj $i of $class class, id ='$id' will be loaded just now");
                
                if($obj->load($id) and ($obj->id == $id)) $record_is_loaded = true;
                else 
                {
                        $return_message = $myObj->tm("Return back", $lang);    
                        $return_page = "main.php?Main_Page=afw_mode_qsearch.php&cl=$class&currmod=$currmod";
                        $die_message = $myObj->tm("Object can not be loaded, seems has been deleted !", $lang);            
                        $technical = "mode edit load by id failed : >> $class load by [id=$id]";
                        throw new AfwBusinessException($die_message, $lang, "be-record-not-found.png", $return_message,$return_page, $technical);                        
                }
                
                // if($id == 6082) die("obj $i of $class class, id ='$id' has been loaded obj = ".var_export($obj,true));
        }
        else
        {
               $_POST[$pki] = "";
        }

        
        
        //if(!$record_is_loaded)  AFWDebugg::log("failed to load $class row $id");
        // if($i==1) die(var_export($obj,true));
        foreach($class_db_structure as $nom_col => $desc)
        {
                if(!isset($fixm_array[$nom_col]))
                {
                        $qedit_nom_col = $nom_col . "_" . $i;
                        $is_fixm_col = false;
                }
                else
                {
                        $qedit_nom_col = $nom_col;
                        $is_fixm_col = true;
                }
                
                //if($nom_col=="owner_id" and $i==1) die("owner_id $i = ".$_POST[$qedit_nom_col]);
                $yn_checkbox = (($desc["TYPE"]=="YN") and ($desc["CHECKBOX"]));
                $nom_col_on = $_POST[$nom_col."_on"];
                if((isset($_POST[$qedit_nom_col]) or $yn_checkbox) and ($nom_col_on or $is_fixm_col or $header_imbedded)) 
                {
        		
                        if(is_array($_POST[$qedit_nom_col]))
        			$val = ','.implode(',', $_POST[$qedit_nom_col]).',';
        		else
        			$val = $_POST[$qedit_nom_col];
                        
                        if($yn_checkbox)
                        {
                            //if(($nom_col=="mode_search") and ($i==3)) die("for col [$nom_col] i=$i : posted_val[$qedit_nom_col]=[$val] from ".var_export($_POST,true));
                            if($val=="1") $val = "Y";
                            else $val = "N";
                        }
                                
                        $auto_c = $desc["AUTOCOMPLETE"];
                        $auto_c_create = $auto_c["CREATE"];
                        $auto_c_uk = $auto_c["UK"];
                        $val_atc = trim($_POST[$qedit_nom_col."_atc"]);
                        
                        if((!$val) and ($auto_c_create) and ($val_atc)) 
                        {
                            if($desc["TYPE"] != "FK") 
                            {
                                throw new AfwModeException("auto create should be only on FK attributes $attribute is ".$desc["TYPE"]);
                            }
                            
                            $obj_at = AfwStructureHelper::getEmptyObject($obj, $nom_col);
                            $obj_by_uk = null;
                            if($auto_c_uk)
                            {
                                $uk_vals = array();
                                foreach($auto_c_uk as $uk_col) $uk_vals[$uk_col] = $obj->getVal($uk_col);
                                $obj_by_uk = $obj_at->loadWithUniqueKey($uk_vals);
                            }
                            
                            if($obj_by_uk)
                            {
                                     $obj_at = $obj_by_uk;
                                     $obj_at->activate();
                            }
                            else
                            {
                                    foreach($auto_c_create as $attr => $auto_c_create_item)
                                    {
                                          $attr_val = "";
                                          if($auto_c_create_item["CONST"]) $attr_val .= $auto_c_create_item["CONST"];
                                          if($auto_c_create_item["FIELD"]) $attr_val .= " ".$obj->getVal($auto_c_create_item["FIELD"]);
                                          if($auto_c_create_item["CONST2"]) $attr_val .= " ".$auto_c_create_item["CONST2"];
                                          if($auto_c_create_item["INPUT"]) $attr_val .= " ".$val_atc;
                                          if($auto_c_create_item["TOKEN"]) $attr_val .= " ".$obj->getTokenVal($auto_c_create_item["TOKEN"]);
                                          
                                          
                                          $attr_val = trim($attr_val);
                                          
                                          $obj_at->set($attr,$attr_val);
                                          
                                    }

                                    if($obj_at->canInsert())
                                    {
                                        $obj_at->insert();
                                    }
                                    
                            }
                            
                            /*
                            $obj_at = AfwStructureHelper::getEmptyObject($obj, $nom_col);
                            
                            foreach($auto_c_create as $attr => $auto_c_create_item)
                            {
                                  $attr_val = "";
                                  if($auto_c_create_item["CONST"]) $attr_val .= $auto_c_create_item["CONST"];
                                  if($auto_c_create_item["FIELD"]) $attr_val .= " ".$obj->getVal($auto_c_create_item["FIELD"]);
                                  if($auto_c_create_item["CONST2"]) $attr_val .= " ".$auto_c_create_item["CONST2"];
                                  if($auto_c_create_item["INPUT"]) $attr_val .= " ".$val_atc;
                                  if($auto_c_create_item["TOKEN"]) $attr_val .= " ".$obj->getTokenVal($auto_c_create_item["TOKEN"]);
                                  
                                  $attr_val = trim($attr_val);
                                  
                                  $obj_at->set($attr,$attr_val);
                                  
                            }
                            
                            $obj_at->insert();
                            */
                            $val = $obj_at->getId();    
                            
                        }
                        
                                
                                
        		if((!isset($fixm_array[$nom_col])) or $record_is_loaded) // les records loaded (not new) doivent etre mis a jour si un des cols du fixe mode change
                        {
                                // if($nom_col=="owner_id" and $i==1) echo "owner_id $i => before set $obj val of $nom_col = ".$obj->getVal($nom_col);
                                if(($nom_col!="id") and ($nom_col!=$obj->getPKField())) $obj->set($nom_col, $val);
                                // if($nom_col=="owner_id" and $i==1) echo "owner_id $i =>  after set $obj val of $nom_col = ".$obj->getVal($nom_col);
                                // if($nom_col=="main_chapter_id" and $i==0) die("$nom_col $i => obj => setted ($nom_col, $val) ");
                        }
                        else
                        {
                                $obj->fixModeSet($nom_col, $val);
                        }
                        
                        
                        
                        // if($nom_col=="id_module") die(var_export($obj,true));
                        // au cas ou un des cols du fixe mode a ete change
                        if(isset($fixm_array[$nom_col]))
                        {
                            $fixm_array[$nom_col] = $val;
                        }
                        
                        
        
        	}
                else
                {
                        // if($nom_col=="main_chapter_id" and $i==0) die("(nom_col_on=$nom_col_on or is_fixm_col=$is_fixm_col) and (yn_checkbox=$yn_checkbox or _POST[$qedit_nom_col]=".$_POST[$qedit_nom_col].")");
                }
        	
        }
        // recalculer $fixm en fonction de $fixm_array
        $fixm_arr = array();
        foreach($fixm_array as $nom_col_1 => $val_1) 
        {
              $fixm_arr[] = "$nom_col_1=$val_1";
        }
        $fixm = implode(",",$fixm_arr);
         
        // if($i==1) die("i=$i => fixm = $fixm, fixm_array = ".var_export($fixm_array,true));
        
        if(!$record_is_loaded)
        {
                if($obj->isChanged())
                {
                	/*if($obj->is("is_lookup")) {
                           die(var_export($obj,true));
                        }*/

                        // if((!$record_is_loaded)) die("i=$i pki=$pki id=$id => record_is_loaded = $record_is_loaded, obj = ".var_export($obj,true));
                        // if(($obj->getMyClass()=="Afield") and (!$obj->getVal("field_name"))) throw new AfwRun timeException("afield insert with field_name empty : ".var_export($obj,true));
                        
                        
                        $obj->sql_action = "insert";
                        $obj->sql_info = "qedit handle row num $i ";
                        /*
                        if($i==46)
                        {
                             die("case 46 afield : " . var_export($obj,true));
                        
                        } */
                        $obj->isFromUI = true;
                        if($obj->canInsert())
                        {
                                $inserted = $obj->insert();
                                if(!$inserted)
                                {
                                        if(($MODE_DEVELOPMENT) and ($objme) and ($objme->isSuperAdmin())) throw new AfwModeException($obj->sql_info ." insert failed : ".$obj->tech_notes, array("FIELDS_UPDATED"=>true));
                                }
                                
                                $id = $obj->getId();
                                if($inserted and $id) 
                                {
                                        $updated_nb_objs++;
                                        if($ids!="all")
                                        {
                                                if(!$ids) $ids = "0";
                                                $ids .= ",$id";
                                        }
                                }         
                        }
                        //AFWDebugg::log("insert row id $id updated_nb_objs become $updated_nb_objs");
                }
                elseif($i==0)
                {
                        // die(var_export($obj, true));
                }
        }
        else
        {
                // if($nom_col=="owner_id") echo("owner_id $i => before update updated_nb_objs = $updated_nb_objs ");
                $obj->sql_action = "update";
                $obj->sql_info = "qedit handle row num $i ";
                $nb_rec_updated = $obj->update();
                $updated_nb_objs += $nb_rec_updated;
                // if(!$nb_rec_updated and $i==0) die(" -- stopped by rafik -- obj = ".var_export($obj,true));
                //AFWDebugg::log("update row id $id updated_nb_objs become $updated_nb_objs");
        }
        	
        if($id)
        {
              if($list_objs_ids) $list_objs_ids .= ",";
              $list_objs_ids .= $id;
              $real_nb_objs++;  
        }
        

        unset($obj);
}


if($updated_nb_objs>0) AfwSession::pushSuccess(AfwLanguageHelper::translateKeyword("save_with_sucess", $lang) . " $updated_nb_objs ".AfwLanguageHelper::translateKeyword("record(s)", $lang));
else AfwSession::pushInformation(AfwLanguageHelper::translateKeyword("no_update_found", $lang));
if($submit_return)
{
    $id = $_POST["id_origin"];
    $cl = $_POST["class_origin"];
    $currmod = $_POST["module_origin"];
    if($_POST["step_origin"]) $step = $_POST["step_origin"];
    
    include("afw_mode_display.php");
}
else
{
    include("afw_mode_qedit.php");
}

/*
$fixm_list_arr = array();
for($i=0;$i<count($fixm_cols);$i++)
{
      $fixm_list_arr[] =  $fixm_cols[$i]."=".$fixm_array[$fixm_cols[$i]];
}
$fixm_list = implode(",",$fixm_list_arr);

$nb_new_objs = $nb_objs - $real_nb_objs;
if($nb_new_objs<0) $nb_new_objs = 0;

$out_scr .= '<center>';
$out_scr .= '<table cellspacing="3" cellpadding="1">';
$out_scr .= "<tr><td align='center'><br>تم حفظ $updated_nb_objs من السجلات بنجاح<br><br></td></tr>";

$out_scr .= '<tr>';
$out_scr .= '<td><br>'; 
$out_scr .= '<form name="editForm" id="editForm" method="post" action="main.php">';
$out_scr .= '<input type="hidden" name="Main_Page" value="afw_mode_search.php"/>';
$out_scr .= '<input type="hidden" name="cl" value="'.$class.'"/>';
$out_scr .= '<input type="submit" class="yellowbtn btn fright" name="submit"  id="submit-form" value="'.$other _search.'" />';
$out_scr .= '</form><br>';
$out_scr .= '</td>';
$out_scr .= '</tr>';

$out_scr .= '<tr>';
$out_scr .= '<td><br>'; 
$out_scr .= '<form name="editForm" id="editForm" method="post" action="main.php">';
$out_scr .= '<input type="hidden" name="Main_Page" value="afw_mode_qedit.php"/>';
$out_scr .= '<input type="hidden" name="cl" value="'.$class.'"/>';
$out_scr .= '<input type="hidden" name="newo" value="3"/>';
$out_scr .= '<input type="hidden" name="limit" value="30"/>';
$out_scr .= '<input type="hidden" name="ids" value="all"/>';
$out_scr .= '<input type="hidden" name="fixmdisable" value="1"/>';
$out_scr .= '<input type="hidden" name="fixm" value="'.$fixm.'"/>';

foreach($fixm_array as $fixm_col => $fixm_val) {
       if($fixm_col) $out_scr .= '<input type="hidden" name="sel_'.$fixm_col.'" value="'.$fixm_val.'"/>';
}

$out_scr .= '<input type="submit" class="bluebtn btn fright" name="submit"  id="submit-form" value="'.$back _to_last_form.'" />';
$out_scr .= '</form><br>';
$out_scr .= '</td>';
$out_scr .= '</tr>';

$out_scr .= '</table>';
$out_scr .= '</center>'; */

?>