<?php
require_once(dirname(__FILE__)."/../../../external/db.php");


require_once("afw_edit_motor.php");
require_once ('afw_rights.php');
$theme_name = AfwSession::config('theme','modern'); $file_dir_name = dirname(__FILE__);include("$file_dir_name/../modes/".$theme_name.'_config.php');
$objme = AfwSession::getUserConnected();
if(!$objme) 
{
    AfwSession::pushError("الرجاء تسجيل الدخول أولا");
    header("Location: login.php");
    exit();
}


if(!$cl)
	$objme->_userError("access denied","the class name parameter is mandatory");





if(!$currmod)
{
    $currmod = $uri_module;
}
$mainObject = new $cl(); 
$can = $objme->iCanDoOperationOnObjClass($mainObject,"edit");
if(!$can)
{
      $log_ums_work = ($objme->isAdmin() or (AfwSession::hasOption("UMS_LOG"))) ? 1 : 1;
      header("Location: lib/afw/modes/afw_denied_access_page.php?CL=$cl&MODE=edit&bf=$bf_id&rsn=$reason&LOG=$log_ums_work");
      exit();
}

if($ids)
{
        if($ids=="all") { 
                
                // $fld_ACTIVE = $mainObject->fld_ACTIVE();
                if(!$mainObject->PK_MULTIPLE) $mainObject->where(" id > 0");
                $mainObject->select_visibilite_horizontale();
                $limit = "";
        }        
        else if($ids=="cond") 
        {
            $limit = "";    
            $mainObject->where(" $cond ");
        }
        else if($ids) 
        {
            $ids_arr = explode(",",$ids);
            $limit = count($ids_arr);    
            $mainObject->where(" id in ($ids) ");
        }
        foreach($_REQUEST as $item => $item_value)
        {
            if(AfwStringHelper::stringStartsWith($item,"sel_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        $mainObject->fixModeSelect($item_trimmed, $item_value);
                        $mainObject->fixModeSet($item_trimmed, $item_value);
                }
            }
            
            if(AfwStringHelper::stringStartsWith($item,"her_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        $mainObject->$item_trimmed = $item_value;
                }
            }
        }
        $ddb_objs = $mainObject->loadMany($limit);
}

foreach($ddb_objs as $ddb_obj_id => $ddb_obj) 
{
    foreach($_REQUEST as $item => $item_value)
    {
            if(AfwStringHelper::stringStartsWith($item,"her_"))
            {
                $item_trimmed = substr($item,4);
                if($item_trimmed) {
                        $ddb_objs[$ddb_obj_id]->$item_trimmed = $item_value;
                }
            }
    }    
}

$nb_objs = count($ddb_objs);

//echo "factory_result";
//print_r($mainObject);
//die();

$out_scr = $header_bloc_edit;
$out_scr .= '<form method="post" action="main.php">';
$mainObject->id_origin = $id_origin;
$mainObject->class_origin = $class_origin;
$mainObject->module_origin = $module_origin;
$mainObject->mode_origin = $mode_origin;
$mainObject->return_mode = $return_mode;

// die("qedit 1 : ".var_export($mainObject->fixm_array,true));

if(!$nb_objs)
{
    if($mainObject->no_row_to_qedit_message)
        $out_scr .= $mainObject->no_row_to_qedit_message;
    elseif($not_found_mess)
        $out_scr .= $not_found_mess;
    else
        $out_scr .= "لا يوجد سجلات";
        
    $datatable_on = true;
    $mode_hijri_edit = true;       	
}
else
{
        $objects_ids_arr = array();
        $tr_head = "<tr><th>الحقل</th>";
        $tr_action_head = "<tr><th style=\"min-width: 250px;\">الإجراء</th>";
        $k=0;
        
        $ids_to_keep = array();
        $ids_to_delete = array();
        
        foreach($ddb_objs as $ddb_obj_id => $ddb_obj0)
        {
             $ddb_action = ${"ddb_action_$k"};
             if($ddb_action==2) $ids_to_keep[] = $ddb_obj_id;
             if($ddb_action==1) $ids_to_delete[] = $ddb_obj_id;
             for($l=0;$l<=3;$l++)
             {
                 if($ddb_action==$l) ${"ddb_action_${l}_selected"} = "selected"; else ${"ddb_action_${l}_selected"} = "";
             }
             $ddb_actions_drop_down = "
                                <input type='hidden' name='id_$k'  id='id_$k' value='$ddb_obj_id' />
                                <select name='ddb_action_$k' id='ddb_action_$k' >
                                        <option value='0' $ddb_action_0_selected>لا شيء</option>
                                        <option value='1' $ddb_action_1_selected>حذف المكرر</option>
                                        <option value='2' $ddb_action_2_selected>إبقاء المكرر</option>
                                        <option value='3' $ddb_action_3_selected>تجاهل هذا ليس مكررا</option>
                                 </select>";
             $objects_ids_arr[$k] = $ddb_obj_id;
             $ddb_obj0_name = $ddb_obj0->getShortDisplay($lang);
             $tr_head .= "<th>$ddb_obj0_name</th>";
             $tr_action_head .= "<th>$ddb_actions_drop_down</th>";
             $k++;
        }
        $tr_head .= "</tr>";     
        $tr_action_head .= "</tr>";
             
        
        $form_html_ddb = "<div class=\"hzm_panel_link_bar header\">
        <table class='$display_grid' style='width: 100%;' cellspacing='3' cellpadding='4'>
        $tr_head
        $tr_action_head";
        $num = 0;
        $tr_odd_even = "odd";
        
        $ddb_field_arr = array();
        
        $class_db_structure = $mainObject::getDbStructure($return_type="structure", $attribute = "all");
        
        foreach($class_db_structure as $nom_col => $desc)
        {
             $mode_field_edit = AfwStructureHelper::attributeIsEditable($mainObject,$nom_col);
             $mode_field_read_only = AfwStructureHelper::attributeIsReadOnly($mainObject,$nom_col);
             if(($mode_field_edit and (!$mode_field_read_only) and ($nom_col != "lookup_code") and (!$mainObject->isIndexAttribute($nom_col))) or ($mainObject->getPK()==$nom_col))
             {
                 $ddb_field_arr[$nom_col] = $desc;
                 if($mainObject->getPK()==$nom_col) $ddb_field_arr[$nom_col]["NO_DDB"] = true;
             }
        }
        $diff_exists = false;
        foreach($ddb_field_arr as $nom_col => $desc)
        {
            //if(($nom_col=="id_sh_org") and (!$desc["WHERE"])) die("$nom_col => desc = ".var_export($desc, true));
            
            $col_label = $mainObject->getAttributeLabel($nom_col, $lang);
            $diff_css = "";
            $old_col_val = "";
            $tr_html_ddb = "";
            for($i=0; $i<count($objects_ids_arr); $i++)
            {    
                    $ddb_obj_id = $objects_ids_arr[$i]; 
                    $ddb_obj =&  $ddb_objs[$ddb_obj_id];
                    $separator = $ddb_obj->getSeparatorFor($nom_col);
                    $col_val = $ddb_obj->{"val$nom_col"}();
                    if(($i>0) and ($old_col_val != $col_val))
                    {
                        $diff_css = "ddb_different ddb_${old_col_val}_different_${col_val}";
                        $diff_exists = true;
                    }
                    $data_loaded = true;
                    // no required for mode DDB
                    $desc["MANDATORY"] = false;
                    $desc["REQUIRED"] = false;
                    if(!$desc["NO_DDB"])
                    {   
                            ob_start();
                            if(($nom_col=="id_sh_org") and (!$desc["WHERE"])) die("$nom_col => desc = ".var_export($desc, true));
                 	        type_input($nom_col."_$i", $desc, $col_val, $ddb_obj, $separator, $data_loaded,"inputlong",0,"inputlong");
                            $input_html = ob_get_clean();
                    }
                    else
                    {
                            $input_html = $ddb_obj->showAttribute($nom_col);
                            $diff_css = "";
                            $diff_exists = false;
                    }
                    $tr_html_ddb .= "<td>$input_html</td>";
                    $num++;
                    
                    $old_col_val = $col_val;
            }
            $tr_html_ddb = "<tr class=\"$tr_odd_even $diff_css\"><th>$col_label</th>$tr_html_ddb</tr>";
            if($tr_odd_even == "odd") $tr_odd_even = "even"; else $tr_odd_even = "odd";
            
            $form_html_ddb .=  $tr_html_ddb;
        }
        $form_html_ddb .= $tr_head;
        $form_html_ddb .= "</table></div>";
        
        $hid_sel_ = "";
        
        foreach($_REQUEST as $item => $item_value)
        {
            if(AfwStringHelper::stringStartsWith($item,"sel_"))
            {
                $hid_sel_ .= "<input type=\"hidden\" name=\"$item\"   value=\"$item_value\"/> \n";        
            }
        }            
        $out_scr .= $form_html_ddb;
        $out_scr .= "<div class='hzm_panel_link_bar footer'><div class='fright full-right-width'>";
        $submit_title = $mainObject->translate('UPDATE',$lang,true);
        $submit_name = "submit";
        $out_scr .= "<input type=\"submit\" name=\"$submit_name\"  id=\"submit-ddb-form\" class=\"$class_inputSubmit\" value=\"&nbsp;$submit_title&nbsp;\" width=\"200px\" height=\"30px\" />";
        if($diff_exists)
        {
             $out_scr .= "<div class='$class_ddbSubmit'>يوجد فوارق تمنع حذف السجلات المكررة يجب دمج البيانات</div>";
        }
        elseif(count($ids_to_keep) < 1)
        {
             $out_scr .= "<div class='$class_ddbSubmit'>يجب إختيار السجل الوحيد الذي يتم ابقاؤه</div>";
        }
        elseif(count($ids_to_keep) > 1)
        {
             $out_scr .= "<div class='$class_ddbSubmit'> السجل  الذي يتم ابقاؤه يجب يكون سجلا واحدا</div>";
        }
        elseif(count($ids_to_delete) == 0)
        {
             $out_scr .= "<div class='$class_ddbSubmit'>يجب إختيار على الأقل سجل واحد مكرر لحذفه</div>";
        }
        else
        {
              $submit_ddb_title = $mainObject->translate('RUN_DDB',$lang,true);
              $submit_ddb_name = "submit_ddb";
        
              $out_scr .= "<input type=\"submit\" name=\"$submit_ddb_name\"  id=\"submit-ddb-form\" class=\"$class_ddbSubmit\" value=\"&nbsp;$submit_ddb_title&nbsp;\" width=\"200px\" height=\"30px\" />";
        }
        
        $out_scr .= "</div></div>";
        $out_scr .= '   <input type="hidden" name="id_origin"   value="'.$id_origin.'"/>
        		<input type="hidden" name="class_origin"   value="'.$class_origin.'"/>
        		<input type="hidden" name="module_origin"   value="'.$module_origin.'"/>
        		<input type="hidden" name="ids"   value="'.$ids.'"/>
                        <input type="hidden" name="cond"   value="'.$cond.'"/>
        		<input type="hidden" name="updo"   value="'.$updo.'"/>
                        <input type="hidden" name="cl"   value="'.$cl.'"/>
        		'.$hid_sel_.'
                        <input type="hidden" name="currmod"   value="'.$currmod.'"/>
                        <input type="hidden" name="file_obj"   value="_'.$cl.'"/>
                        <input type="hidden" name="class_obj"  value="'.$cl.'"/>
        		<input type="hidden" name="nb_objs"     value="'.$nb_objs.'"/>
        		<input type="hidden" name="popup"   value="'.$popup_2.'"/>
                        <input type="hidden" name="Main_Page" id="Main_Page" value="afw_handle_default_ddb.php"/>
        		
        	    </form>';
        //$out_scr .= $footer_bloc_ddb;

        $datatable_on = true;
        $mode_hijri_edit = true;
}        	    
?>