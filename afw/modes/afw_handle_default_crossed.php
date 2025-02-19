<?php
require_once(dirname(__FILE__)."/../../../external/db.php");
$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}


//if(!$objme) die("no crossed handle without connected user");

// no fixm
$fixm_array = array();

// @todo : class name reversible encryption
$class = AfwStringHelper::hzmDecrypt($_POST["class_obj"]);
$currmod = $_POST["currmod"];

$list_objs_ids = "";

$nb_objs    = $_POST["nb_objs"];
$crossed_value_col = $_POST["crossed_value_col"];
$array_fetched_cols = array();

$objTemplate = new $class();

$array_fetched_cols[$crossed_value_col] = AfwStructureHelper::getStructureOf($objTemplate,$crossed_value_col);


AFWDebugg::setEnabled(true);
//AFWObject::setDebugg(true);

$real_nb_objs = 0;
$updated_nb_objs = 0;
for($i=0;$i<$nb_objs;$i++)
{
        $pki = "id_$i";
        $id    = $_POST[$pki];
        
        $obj = new $class();
        $is_load = false;
        //AFWDebugg::log("obj $i of $class class, id ='$id' isInteger=".isInteger($id));
        if(($id) && (($obj->PK_MULTIPLE) || (is_numeric($id) && ($id>0))))
        {
        	//AFWDebugg::log("try to load $class row $id");
                if($obj->load($id)) $is_load = true;
                else $obj->_error("can't load obj with id = $id");
        
        }
        else
        {
               $_POST["id_$i"] = "";
        }
        
        //if(!$is_load)  AFWDebugg::log("failed to load $class row $id");
        // if($i==1) die(var_export($obj,true));
        
        
        
        foreach($array_fetched_cols as $nom_col => $desc)
        {
                if(!isset($fixm_array[$nom_col]))
                {
                        $crossed_edit_nom_col = $nom_col . "_" . $i;
                        $is_fixm_col = false;
                }
                else
                {
                        $crossed_edit_nom_col = $nom_col;
                        $is_fixm_col = true;
                }
                
                //if($nom_col=="price" and $i==0) die("$nom_col [$i] : _POST[$crossed_edit_nom_col] => ".$_POST[$crossed_edit_nom_col]);
                
                $yn_checkbox = (($desc["TYPE"]=="YN") and ($desc["CHECKBOX"]));
                if(isset($_POST[$crossed_edit_nom_col]) or $yn_checkbox) 
                {
        		//die("rafik is here 001");
                        if(is_array($_POST[$crossed_edit_nom_col]))
        			$val = ','.implode(',', $_POST[$crossed_edit_nom_col]).',';
        		else
        			$val = $_POST[$crossed_edit_nom_col];
                        
                        if($yn_checkbox)
                        {
                            //if(($nom_col=="mode_search") and ($i==3)) die("for col [$nom_col] i=$i : posted_val[$crossed_edit_nom_col]=[$val] from ".var_export($_POST,true));
                            if($val=="1") $val = "Y";
                            else $val = "N";
                        }
                                
                        if((!isset($fixm_array[$nom_col])) 
                            or $is_load // les records loaded (not new) doivent etre mis a jour si un des cols du fixe mode change
                          )  
                        {
                                /*
                                $col_debugg = "price";
                                if($nom_col==$col_debugg and $i==0) echo "$nom_col [$i]  => before set $obj val of $nom_col = ".$obj->getVal($nom_col);
                                */
                                if(($nom_col!="id") and ($nom_col!=$obj->getPKField())) $obj->set($nom_col, $val);
                                /*
                                if($nom_col==$col_debugg and $i==0) echo "$nom_col [$i] =>  after set $obj val of $nom_col = ".$obj->getVal($nom_col);
                                if($nom_col==$col_debugg and $i==0) die("$nom_col [$i] => $obj -> setted ($nom_col, $val) ");
                                */
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
        	
        }
        // recalculer $fixm en fonction de $fixm_array
        $fixm_arr = array();
        foreach($fixm_array as $nom_col_1 => $val_1) 
        {
              $fixm_arr[] = "$nom_col_1=$val_1";
        }
        $fixm = implode(",",$fixm_arr);
         
        // if($i==1) die("i=$i => fixm = $fixm, fixm_array = ".var_export($fixm_array,true));
        /*
        if(!$is_load)
        {
                if($obj->isChanged())
                {
                        // if(($obj->getMyClass()=="Afield") and (!$obj->getVal("field_name"))) throw new AfwRuntimeE xception("afield insert with field_name empty : ".var_export($obj,true));
                        
                        
                        $obj->sql_action = "insert";
                        $obj->sql_info = "qedit handle row num $i ";

                        $obj->isFromUI = true;
                        $inserted = $obj->insert();
                        if(!$inserted)
                        {
                            if(($MODE_DEVELOPMENT) and ($objme) and ($objme->isSuperAdmin())) throw new AfwRuntimeEx ception($obj->sql_info ." insert failed : ".$obj->tech_notes, array("FIELDS_UPDATED"=>true));
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
                        //AFWDebugg::log("insert row id $id updated_nb_objs become $updated_nb_objs");
                }
        }
        else
        */
        if($is_load)
        {
                // if($nom_col=="owner_id") echo("owner_id $i => before update updated_nb_objs = $updated_nb_objs ");
                $obj->sql_action = "update";
                $obj->sql_info = "qedit handle row num $i ";
                $old_update_context = $update_context;
                $update_context = "من خلال شاشة التعديل المتقاطعة";
                $updated_nb_objs += $obj->update();
                $update_context = $old_update_context;
                // if($nom_col=="owner_id") echo("owner_id $i => after update updated_nb_objs = $updated_nb_objs ");
                // if($nom_col=="owner_id" and $i==4) die(" -- stopped by rafik --");
                //AFWDebugg::log("update row id $id updated_nb_objs become $updated_nb_objs");
                
                if($id)
                {
                      if($list_objs_ids) $list_objs_ids .= ",";
                      $list_objs_ids .= $id;
                      $real_nb_objs++;  
                }
        }
        else
        {
               // @todo here ex-ception
        }
        	
        
        

        unset($obj);
}


if($updated_nb_objs>0) AfwSession::pushSuccess(AfwLanguageHelper::translateKeyword("save_with_sucess", $lang) . " $updated_nb_objs ".AfwLanguageHelper::translateKeyword("record(s)", $lang));
else AfwSession::pushInformation(AfwLanguageHelper::translateKeyword("no_update_found", $lang));
if($submit_return)
{
    $id = $_POST["id_origin"];
    $cl = AfwStringHelper::hzmDecrypt($_POST["class_origin"]);
    $currmod = $_POST["module_origin"];
    $currstep = $_POST["step_origin"];
    if($updated_nb_objs>0)
    {
        
        $pbMethodBackCode = $_POST["pbmon"];        
        if($pbMethodBackCode)
        {
                AfwAutoLoader::addMainModule($currmod);
                $myObj = new $cl();
                $myObj->load($id);
                // die("rafik before executePublicMethodForUser($objme, $pbMethodBackCode, $lang) on ".var_export($myObj,true));
                list($error,$info,$warn,$technical) = $myObj->executePublicMethodForUser($objme, $pbMethodBackCode, $lang);
                // die("rafik after executePublicMethodForUser(objme, $pbMethodBackCode, $lang) : (err=$error,info=$info) ");
        }
        // else die("no pbm : _POST".var_export($_POST,true));
        

    }
    include("afw_mode_display.php");
}
else
{
    include("afw_mode_crossed.php");
}



?>