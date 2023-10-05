<?php
require_once(dirname(__FILE__)."/../../../external/db.php");
$class = $_POST["class_obj"];
$file  = $_POST["file_obj"];
$id    = $_POST["id_obj"];
/* $currmod = $_POST["currmod"];
$currstep = $_POST["currstep"];*/
if(!$lang) $lang = "ar";
$file_dir_name = dirname(__FILE__); 


//AFWDebugg::setEnabled(true);
////AFWObject::setDebugg(true);
//AFWDebugg::initialiser("C:\\dbg\\debug\\","afw_debugg.txt");

/////////////////////////////
$obj = new $class();
//$currmod = $obj->getMyModule();

$is_loaded_from_db = false;
if($id)
{
	if($obj->load($id)) $is_loaded_from_db = true;
}

$class_db_structure = $obj::getDbStructure($return_type="structure", $attribute = "all");

if(!$is_loaded_from_db)
{
        if($obj->UNIQUE_KEY and $obj->tryToLoadWithUniqueKeyForEditMode())
        {
           $ukey_array = array();
           
           foreach($obj->UNIQUE_KEY as $ukey)
           {
               $ukey_array[$ukey] = $_POST[$ukey];
           }
           
           if($obj->loadWithUniqueKey($ukey_array))
           {
               $id = $obj->getId();               
               //if((!$id) and $objme and $objme->isSuperAdmin()) die("rafik loadWithUniqueKey failed object still without ID ".var_export($obj,true));
               $is_loaded_from_db = true;
           }
           
        }

}

// shornames to fieldnames
foreach($class_db_structure as $nom_col => $desc)
{
     if($desc["SHORTNAME"] and $desc["USE_SHORTNAME_FOR_EDIT"])   
     {
        $attribute = $desc["SHORTNAME"];
        if($_POST[$attribute])
        {
                $_POST[$nom_col] = $_POST[$attribute];
                unset($_POST[$attribute]);
        }
     }
}


/////////////////////////////
foreach($class_db_structure as $nom_col => $desc){
	//if($nom_col=="trip1_room_mfk") die("trip1_room_mfk -> ".var_export($_POST[$nom_col],true));
        if(!$desc["STEP"]) $desc["STEP"] = 1;
        
        // !$desc["READONLY"] car a ce moment la hidden mouch checkbox
        $yn_checkbox = (($desc["TYPE"]=="YN") and ($desc["CHECKBOX"]) and (!$desc["READONLY"]) and ((!$obj->editByStep) or ($currstep==$desc["STEP"])));
        
        if(isset($_POST[$nom_col]) or
           $yn_checkbox or
             (($desc["TYPE"]=="MFK") and (!$desc["CATEGORY"]) and (!$desc["READONLY"]) and ((!$obj->editByStep) or ($currstep==$desc["STEP"])))
           )
        {
		// if($nom_col=="arole_mfk") die("arole_mfk -> ".var_export($_POST[$nom_col],true));
                if(is_array($_POST[$nom_col]))
			$val = ','.implode(',', $_POST[$nom_col]).',';
		else
			$val = $_POST[$nom_col];
                        
                //if($nom_col=="trip1_room_mfk") die("val=$val");        
                if($yn_checkbox)
                {
                    //if(($nom_col=="mode_search") and ($i==3)) die("for col [$nom_col] i=$i : posted_val[$qedit_nom_col]=[$val] from ".var_export($_POST,true));
                    if($val=="1") $val = "Y";
                    else $val = "N";
                }        
		
                $auto_c = $desc["AUTOCOMPLETE"];
                $auto_c_create = $auto_c["CREATE"];
                $auto_c_uk = $auto_c["UK"];
                $val_atc = trim($_POST[$nom_col."_atc"]);
                
                if((!$val) and ($auto_c_create) and ($val_atc)) 
                {
                    if($desc["TYPE"] != "FK") 
                    {
                        $obj->simpleError("auto create should be only on FK attributes $nom_col is ".$desc["TYPE"]);
                    }
                    
                    $obj_at = $obj->getEmptyObject($nom_col);
                    $obj_by_uk = null;
                    if($auto_c_uk)
                    {
                        $uk_vals = array();
                        foreach($auto_c_uk as $uk_col) $uk_vals[$uk_col] = $obj->getVal($uk_col);
                        $obj_by_uk = $obj_at::loadByUK($uk_vals);
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
                            $obj_at->insert();
                    }
                    
                    
                    $val = $obj_at->getId();    
                    
                }
                /*
                if(($nom_col=="homework_start_paragraph_num") and ($val=="1")) 
                {
                    die("before set $nom_col val = $val -> _POST : ".var_export($_POST,true));                
                }
                 
                */
                if($nom_col != $obj->getPK()) $obj->set($nom_col, $val);
                
                //if(($nom_col=="lastname_en") and ($val!="")) die("after set $nom_col val = $val -> obj : ".var_export($obj,true));


	}
}



if($obj->editByStep)
{
        if($_POST["save_next"]) 
        {
             $old_currstep = $currstep;
             $currstep = $obj->findNextEditableStep($currstep,"after save_next", true);
             if($currstep < 0) 
             {
                if(($MODE_DEVELOPMENT) and ($objme) and ($objme->isSuperAdmin())) die("$obj -> findNextEditableStep($old_currstep,after save_next) = $currstep");
                $currstep = $old_currstep;
             }   
        }
        if($_POST["save_previous"])
        {
             $old_currstep = $currstep;
             $currstep = $obj->findPreviousEditableStep($currstep,"after save_previous", true);
             if($currstep < 0) $currstep = $old_currstep;
        } 
        
        if($obj->stepsAreOrdered() and ($currstep > $obj->getLastEditedStep(false))) $obj->setLastEditedStep($currstep);        
}
$new_label = $obj->insertNewLabel("ar");
$successful_save = AFWObject::traduireOperator("save_with_sucess", $lang). " " . AFWObject::traduireOperator("changes", $lang);        

$case_of_handle = "unknown";

if(!$is_loaded_from_db)
{
	    $obj->isFromUI = true;
        //die(var_export($obj,true));
        $obj->insert();
	    $id = $obj->getId();
        $can_show_info = ((!$obj->editByStep) or ($save_update));
        
        if($id>0) 
        {
            if($can_show_info)
            {
                AfwSession::pushInformation("تمت $new_label بنجاح  برقم تسلسلي $id");
                // if($objme and $objme->isAdmin()) 
                
                $case_of_handle = "insert new and goto other step";
            }    
        }
        elseif(($MODE_DEVELOPMENT) and ($objme) and ($objme->isSuperAdmin()))
        {
            AfwSession::pushError("وقع خطأ أثناء الاضافة : ".var_export($obj,true));                
            $case_of_handle = "error inserting new : ".$obj->tech_notes;
        }
        else
        {
            $case_of_handle = "hidden error inserting new : ".$obj->tech_notes;
        }
        
}
else
{
        $old_update_context = $update_context;
        $update_context = "من خلال شاشة التعديل";
        //die("rafik 3000 before obj->update() obj = ".var_export($obj,true));
        if($obj->update())
        {
            $case_of_handle = "update existing successfull";
            if($can_show_info) AfwSession::pushSuccess($successful_save);
        }
        else
        {
            
            // $obj->simpleError(var_export($obj,true));
            if(!$obj->VIEW) 
            {
                $case_of_handle = "nothing updated";
                if($can_show_info) AfwSession::pushInformation("لا شيء تم تعديله");
                //if($obj->reason_non_update and $objme and $objme->isSuperAdmin()) $_S ESSION["information"] .= "reason : ".$obj->reason_non_update; 
            }      
            else 
            {
                $case_of_handle = "nothing updated but it is a view";
                if($can_show_info) AfwSession::pushSuccess($successful_save);           // because view is updated via updating composing tables not itself
            }
        }
        
        $update_context = $old_update_context;
        
        
}

if($_POST["pbmon"])
{
    if($obj and ($obj->getId()>0))
    {
        $id = $obj->getId();
        $pbMethodCode = "";
        foreach($_POST as $name => $value)
        {
                   if(AfwStringHelper::stringStartsWith($name,"submit-"))
                   {
                         $pbMethodCode = substr($name,7);
                   }
                   
                   if(AfwStringHelper::stringStartsWith($name,"pbmconfirm-"))
                   {
                         $pbMethodCode = substr($name,11);
                   }
                   
                   if(AfwStringHelper::stringStartsWith($name,"pbmcancel-"))
                   {
                         $pbMethodCode = substr($name,10);
                   }   
        }
        
        if($pbMethodCode)
        {
            // die("rafik-debugg : I will getPublicMethodForUser($pbMethodCode) ");
            $pMethodItem = $obj->getPublicMethodForUser($objme, $pbMethodCode);
             
            if($pMethodItem["CONFIRMATION_NEEDED"])
            {
                    if((!$_POST["pbmconfirm-$pbMethodCode"]) and (!$_POST["pbmcancel-$pbMethodCode"]))
                    {
                         $pbm_confirmed = false;
                         $pbm_cancelled = false;
                         $confirmation_warning = $pMethodItem["CONFIRMATION_WARNING"][$lang];
                         $confirmation_question = $pMethodItem["CONFIRMATION_QUESTION"][$lang];

                         // die("confirmation_warning=$confirmation_warning , confirmation_question=$confirmation_question, pMethodItem=".var_export($pMethodItem,true));
                         include("afw_mode_confirm.php");
                         $header_bloc_edit .= $confirm_html;
                    }     
                    elseif($_POST["pbmconfirm-$pbMethodCode"])
                    {
                         $pbm_confirmed = true;
                         $pbm_cancelled = false;
                    }
                    else
                    {
                         $pbm_confirmed = false;
                         $pbm_cancelled = true;
                    }
            }     
            else
            {
                 //die("pMethodItem found for code : [$pbMethodCode] for user $objme is pMethodItem = ".var_export($pMethodItem,true));
                 $pbm_confirmed = true;
            }
            
            // die("pbm_confirmed=$pbm_confirmed");
            if($pbm_confirmed)
            {     
                    $old_update_context = $update_context;
                    $update_context = "من خلال زر ". $pMethodItem["LABEL_AR"]."-".$pMethodItem["METHOD"];
                    if($_POST["pbmpbis_$pbMethodCode"])
                    {
                        $obj->pbmethod_main_param = $_POST["pbmpbis_$pbMethodCode"];
                    }
                    elseif($_POST["pbmp_$pbMethodCode"])
                    {
                        $obj->pbmethod_main_param = $_POST["pbmp_$pbMethodCode"];
                    }
                    else
                    {
                        // die("pbmp_$pbMethodCode and pbmpbis_$pbMethodCode not found in pbmethod_main_param__POST = ".var_export($_POST,true));
                    }
                    //if($obj->pbmethod_main_param) die("obj->pbmethod_main_param = ".$obj->pbmethod_main_param.", _POST = ".var_export($_POST,true));
                    list($error, $info, $warn, $technical) = $obj->executePublicMethodForUser($objme, $pbMethodCode, $lang);
                    //die("list($error, $info, $warn, $technical) = obj->executePublicMethodForUser($objme, $pbMethodCode, $lang) update_context=$update_context;");

                    if($technical)
                    {
                            // die("here warn = $warn");
                            if($warn) $warn .= "<br>";
                            $warn .= $obj->tm("There are more technical details with administrator",$lang);
                            $warn .= "<div class='technical'>$technical</div>";
                    }

                    $update_context = $old_update_context;
                    
                    if(!$info and !$error and !$warn)
                    {
                        if($objme and $objme->isAdmin())
                            $info = "execute of $pbMethodCode has been successfully terminated for mc=".$pMethodItem["METHOD"];
                        else
                            $info = "action successfully terminated for mc=".$pMethodItem["METHOD"];
                    }  
                    
                    if($info) AfwSession::pushInformation($info, "method-$pbMethodCode"); 
                    if($error) AfwSession::pushError($error); 
                    if($warn) AfwSession::pushWarning($warn); 
                            
                    // reload object if needed (default yes) 
                    if(!$obj->noRelaodAfterRunOfMethod($pbMethodCode)) 
                    {
                            unset($obj);
                            $obj = new $class();
                            $obj->load($id);                            
                    }
            }
            else
            {
                 if($pbm_cancelled) AfwSession::pushInformation("بحمد الله تم إلغاء الإجراء بكل أمان");
            }     
        }   
    }
    else
    {
        //AfwSession::pushError("execution of method on empty object");
    }
}


// the global after save action override the local one.
if($global_after_save_edit[$class])
{
    $obj->after_save_edit = $global_after_save_edit[$class]; 
}

if($save_update and $obj->after_save_edit)
{
    $file = $obj->after_save_edit["file"];
    $cl = $obj->after_save_edit["class"];
    
    if($cl) 
    {
        if((!$obj->after_save_edit["attribute"]) and (!$obj->after_save_edit["formulaAttribute"])) $obj::simpleError("bad configration for after_save_edit option : ".var_export($obj->after_save_edit,true));
        if($obj->after_save_edit["formulaAttribute"]) $id = $obj->calc($obj->after_save_edit["formulaAttribute"]);
        else $id = $obj->getVal($obj->after_save_edit["attribute"]);
        $currmod = $obj->after_save_edit["currmod"];
        $currstep = $obj->after_save_edit["currstep"];
        
        include("afw_mode_display.php");
    }
    else if($file) include($file);
}
elseif($id)
{
        $cl = $class;
        $tech_notes = $obj->tech_notes;
        // if($tech_notes) die(var_export($tech_notes,true));
        
        if($save_update)
        {
               $currstep = $obj->getNextStepAfterFinish($current_step);
               //$test_rafik = true;   // looking for reason of this error : AH00052: child pid 31733 exit signal Segmentation fault (11)
               //echo $obj->showMe();
               //if($obj->test_rafik) die("save_update before afw_mode_display.php cl=[$cl] tech_notes=[$tech_notes] obj=".var_export($obj,true));
               include("afw_mode_display.php");
        }    
        else
        {
               include("afw_mode_edit.php");
        }
} 
else
{
    // nothing to save just we are navigating
    $cl = $class;
    include("afw_mode_edit.php");
    // if(($objme) and ($objme->isSuperAdmin())) die("handle error : case : $case_of_handle : object : ".var_export($obj,true))
}
     
?>