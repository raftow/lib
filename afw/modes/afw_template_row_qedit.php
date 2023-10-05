<?php
/*
require_once("afw_config.php");*/

define("LIMIT_INPUT_SELECT", 30);


global $TMP_DIR,$TMP_ROOT,$lang,$pack,$sub_pack,$id,$aligntd, $Main_Page,
  //$class_tr1,$class_tr2, $class_titre, $class_table, $class_inputSubmit, $class_inputLien, $class_inputNew, 
  $pct_tab_edit_mode, $objme, $first_disp, $first_val, $diff_val, $not_filled, $filled, $nb_objs, $qedit_trad;

if(!$lang) $lang = 'ar';

$obj_id = $obj->getId();
$obj_class = $obj->getMyClass();
$obj_mod = $obj->getMyModule();
$qedit_input = array();
$qedit_orig_nom_col = array();

$fixm_array       = $obj->fixm_array;
$fgroup           = $obj->fgroup;
// die("fgroup=$fgroup");
$submode          = $obj->submode;


$col_num = 1;
$qedit_hidden_pk_input = "";
if($obj->qedit_minibox)
{
        $miniBoxTemplate = $obj->getMiniBoxTemplateArr("qedit");
        if(!$miniBoxTemplate) $miniBoxTemplateArr = array();
        else $miniBoxTemplateArr = $miniBoxTemplate;
}

if(!$class_db_structure) $class_db_structure = $obj->getMyDbStructure();
$nb_cols_qedit = count($class_db_structure);

foreach($class_db_structure as $nom_col => $desc)
{
        $desc = AfwStructureHelper::repareQEditAttributeStructure($nom_col, $desc, $obj);
        $isQuickEditableAttribute = $obj->isQuickEditableAttribute($nom_col, $desc, $submode);
        $isFixmCol = $fixm_array[$nom_col];
        
        // $log_input_qedit = "$submode=[$fgroup] for field=[$nom_col] (isQuickEditableAttribute=$isQuickEditableAttribute) or isFixmCol=$isFixmCol";
        
        if(($submode=="FGROUP") and $fgroup)
        {
               $good_fgroup_for_field = (($desc["FGROUP"]==$fgroup) or ($desc["QEDIT_ALL_FGROUP"]));
               
               $mode_field_qedit = (($isQuickEditableAttribute and $good_fgroup_for_field) or $isFixmCol);
               
               //$log_input_qedit = ""; 
               
               if(false and ($fgroup=="modes_list") and ($nom_col=="answer_module_id"))
               {
                    $log_input_qedit = "$submode=[$fgroup] for field=[$nom_col] (isQuickEditableAttribute=$isQuickEditableAttribute and good_fgroup_for_field=$good_fgroup_for_field) or isFixmCol=$isFixmCol";
               } 
               
                
        }
        else
        {
                $mode_field_qedit = ($isQuickEditableAttribute or $isFixmCol);  // or ($fixm_array[$nom_col]) => because if we fix an attribute we should consider it in Qedit commom columns
        }        
        $mode_show_field_read_only = $obj->isReadOnlyAttribute($nom_col, $desc, $submode);
        $mode_field_qedit_reason = $obj->reasonWhyAttributeNotQuickEditable($nom_col, $desc, $submode);
        
        $data_loaded=($obj->getId()>0);
        
        //echo "$nom_col <br>";
        $separator = $obj->getSeparatorFor($nom_col);
	$isTechField = $obj->isTechField($nom_col);
        if(!$obj->qeditNum) $obj_qeditNum = "0";
        else $obj_qeditNum = $obj->qeditNum;
        $qedit_nom_col = $nom_col . "_" . $obj_qeditNum;
        $qedit_orig_nom_col[$qedit_nom_col] = $nom_col;
        $qedit_orderindex = $col_num + $obj->qeditNum*$nb_cols_qedit;
        $obj->qeditNomCol = $nom_col;
        $attr_IsApplicable = $obj->attributeIsApplicable($nom_col);
        
        if($desc['TYPE'] == 'PK')
        {
            
                ob_start();
                $type_input_ret = hidden_input($qedit_nom_col, $desc, $obj->getId(), $obj);
                $qedit_hidden_pk_input = ob_get_clean();
                //die($qedit_hidden_pk_input);
                if(!$obj->PK_MULTIPLE)
                {
                        $obj_id_display = $obj->getId();
                        if($obj_id_display<=0) $obj_id_display = " + ";
                }
                else $obj_id_display = "";
                $qedit_input[$qedit_nom_col] = $qedit_hidden_pk_input.$obj_id_display;
               
        }        
        elseif(((!$desc['CATEGORY']) || ($desc['FORCE-INPUT'])) || $mode_show_field_read_only)
        {
		if(($mode_field_qedit)  and (!isset($fixm_array[$nom_col])))
                {
                        if($obj->qedit_minibox)
                        {
                                if(!$miniBoxTemplate) $miniBoxTemplateArr[$nom_col] = $desc; 
                        }
                        
                        $obj_val = $obj->getVal($nom_col);
                        if($attr_IsApplicable)
                        {
                                if(!$mode_show_field_read_only)
                		{
                        		ob_start();
                        		$col_val = $obj->getVal($nom_col);
                        		if(($desc['TYPE'] == 'PK') && empty($col_val))
                                        {
                        			$type_input_ret = type_input($qedit_nom_col, $desc, $id, $obj, $separator, $data_loaded, "", $qedit_orderindex);
                        		}
                                        else
                                        {
                        			$type_input_ret = type_input($qedit_nom_col, $desc, $col_val, $obj, $separator, $data_loaded, $desc["FORCE_CSS"], $qedit_orderindex);
                                        }
                        		$qedit_input[$qedit_nom_col] = ob_get_clean();
                                        // 
                                        if($log_input_qedit) $qedit_input[$qedit_nom_col] .= "<pre class='php'>error : ".$log_input_qedit."</pre>";
                                        
                                        //if($qedit_nom_col=="sub_module_id_3")  die("log : $qedit_nom_col => ".$mode_field_qedit_reason);
                                        
                                        if($obj->isActive() and $desc["DYNAMIC-HELP"]) $qedit_input[$qedit_nom_col] .= $obj::tooltipText($obj->getHelpFor($nom_col,$lang)); 
                                        $start_row = $obj->qeditNum;
                                        
                                        $input_html = $qedit_input[$qedit_nom_col];
                                        if(false)   //  ($objme->isAdmin())  //    
                                        {
                                                if($type_input_ret=="text")
                                                {
                                                        $icon_unifyall = "<a id=\"imgUnifyAll$qedit_nom_col\" href=\"#\" class=\"copy_down\" onclick=\"unify_all_text('$nom_col','$obj_val',$start_row,$nb_objs)\">&nbsp;&nbsp;&nbsp;</a>";
                                                }
                                                else
                                                {
                                                        $obj_val_lab = $obj->displayAttribute($nom_col,true,$lang, false);
                                                        $icon_unifyall = "<a id=\"imgUnifyAll$qedit_nom_col\" href=\"#\" class=\"copy_down\" onclick=\"unify_all_select('$nom_col','$obj_val','$obj_val_lab',$start_row,$nb_objs)\">&nbsp;&nbsp;&nbsp;</a>";
                                                }
                                        }
                                        else $icon_unifyall = "";
                                        
                                        if($icon_unifyall) $qedit_input[$qedit_nom_col] = "<table><tr><td>$input_html</td><td>$icon_unifyall</td></tr></table>";
                                        else  $qedit_input[$qedit_nom_col] = $input_html;
                                }
                                else
                                {      
                                        if(($desc["HIDDEN_INPUT"]) or ($obj->inMultiplePK($nom_col)))
                                        {
                                                ob_start();
                                                $col_val = $obj->getVal($nom_col);
                                                $type_input_ret = hidden_input($qedit_nom_col, $desc, $col_val, $obj);
                                                $qedit_hidden_input = ob_get_clean();
                                        }
                                        else $qedit_hidden_input = "";
                                        $display_attribute_RO = $obj->displayAttribute($nom_col,true).$qedit_hidden_input;
                                        
                                        if($desc['FORM_HEIGHT']) {
                                                $style_div_form_control = "height:".$desc['FORM_HEIGHT']." !important";
                                        }
                                        
                                        if($desc['STYLE_RO_DIV']) {
                                                $style_div_form_control = $desc['STYLE_RO_DIV'];
                                        }
                                        
                                        
                                        if($desc['INPUT_WIDE']) 
                                        {
                                            $desc['RO_DIV_CLASS'] = "qedit_wide";
                                        }
                                        
                                        
                                        if($desc['RO_DIV_CLASS']) {
                                                $display_attribute_RO_class = $desc['RO_DIV_CLASS'];
                                        }
                                        else
                                        {
                                                $display_attribute_RO_class = "form-control inputreadonly";
                                        }
                                        
                                        
                                        $qedit_input[$qedit_nom_col] =  "<div class='$display_attribute_RO_class' style='$style_div_form_control'>$display_attribute_RO</div>";
                                }
                        }
                        else
                        {
                                list($icon,$textReason, $wd, $hg) = $obj->whyAttributeIsNotApplicable($nom_col);
                                if(!$wd) $wd = 20;
                                if(!$hg) $hg = 20;
                                //$obj->_error("$obj has Attribute $nom_col NotApplicable : ($icon,$textReason)");
                                $qedit_input[$qedit_nom_col] = "<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='$textReason'  width='$wd' heigth='$hg'>";
                        }
                        
                        
                        if(!$obj_val)
                        {
                                if(!$not_filled[$nom_col]) $not_filled[$nom_col] = 0;
                                $not_filled[$nom_col]++;
                        }
                        else
                        {
                                if(!$filled[$nom_col]) $filled[$nom_col] = 0;
                                $filled[$nom_col]++;
                        }
                        
                        
                        if(!$first_val[$nom_col])
                        {
                                $first_disp[$nom_col] = $obj->displayAttribute($nom_col,true,"ar", false);
                                $first_val[$nom_col] = $obj_val;
                        }
                        else
                        {
                                if($first_val[$nom_col] != $obj_val)
                                {
                                      $diff_val[$nom_col] = true;
                                }
                        }
                }
	}
        $col_num++;
}

/*
because we need the hidden id  ($qedit_hidden_pk_input)
if(!$obj->HIDE_DISPLAY_MODE) 
{ */
        if($obj_id>0) 
        {
                if(!$obj->isActive())
                {
                        $viewIcon = "view_off";
                        $data_errors = "السجل محذوف الكترونيا"; 
                }
                elseif($obj->showQeditErrors)
                { 
                        if(!$obj->isOk(true))
                        {
                                $data_errors_arr = $obj->getDataErrors($lang);
                                $viewIcon = "view_error";
                                $data_errors = implode(' / ', $data_errors_arr);
                                if((strlen($data_errors)>596) or (count($data_errors_arr)>18)) 
                                {
                                        $data_errors = "أخطاء كثيرة";
                                        $viewIcon = "view_error";
                                }
                        } 
                        else
                        {
                                $viewIcon = "view_ok";
                                $data_errors = "لا يوجد أخطاء"; 
                        } 
                }
                else
                {
                        $viewIcon = "view_me";
                        $data_errors = "لم يتم تفعيل التثبت من الأخطاء في التعديل السريع"; 
                }

                if($obj->ENABLE_DISPLAY_MODE_IN_QEDIT)
                { 
                        $qedit_input["show"] = "<a href='main.php?Main_Page=afw_mode_display.php&cl=$obj_class&id=$obj_id&currmod=$obj_mod' ><img src='../lib/images/$viewIcon.png' width='24' heigth='24' data-toggle='tooltip' data-placement='top' title='$data_errors'></a>";
                }
                else
                {
                        if($viewIcon == "view_error") $qedit_input["show"] = "<img src='../lib/images/error-red.png' width='24' heigth='24' data-toggle='tooltip' data-placement='top' title='$data_errors'>";
                        else $qedit_input["show"] = "";
                }
                $qedit_input["show"] .= $qedit_hidden_pk_input;
             
        }
        else
        {
                // $qedit_input["show"] = "<img src='./pic/new_qedit_record.png' >";
                $qedit_input["show"] .= $qedit_hidden_pk_input;
        }
/*}*/


$css       = $obj->getStyle();
if($obj->odd_even_enable) 
{
        $obj_odd_even = $obj->odd_even;
}     
else $obj_odd_even = "";

if(!$obj->qedit_minibox) 
{ 
?>
<tr class="<?=$obj_odd_even?> <?=$obj->getHLClass()?>">
<?php	
        list($is_ok, $arrErrors) = $obj->isOk($force=false, $returnErrors=true);
        //die(var_export($arrErrors,true));
        foreach($qedit_input as $col => $input_html)
        {
             if($tr_obj==$class_tr2) $tr_obj=$class_tr1; else $tr_obj=$class_tr2;
             $odd_even = trim($obj->odd_even);
             
             $orig_nom_col = $qedit_orig_nom_col[$col];
             
             if($obj->showQeditErrors)
             {
                   $myCategory = $obj->myCategory();
                   //if($obj->getId()==106897) die("obj->getDataErrors() = ".var_export($obj->getDataErrors(),true));
                   
                   if(!$obj->isActive())
                   {
                       $odd_even = "dis";
                   }
                   elseif((!$is_ok) and ($arrErrors[$orig_nom_col] or ($orig_nom_col=="id")))
                   {
                       $odd_even = "err";
                   }
                   elseif($myCategory)
                   {
                       $odd_even = "ct$myCategory";
                   }
             }    
             
             if(!$orig_nom_col) $class_xqe_col = "z";
             else 
             {
                $old_class_xqe_col = $class_xqe_col;
                $class_xqe_col = $class_db_structure[$orig_nom_col]["XQE_COL_STYLE"];
                $css_style_0 = $class_db_structure[$orig_nom_col]["CSS-STYLE"];
                if($css_style_0) $css_style="style='$css_style_0'";
                else $css_style="";
                
                if((!$class_xqe_col) and $altern_xy)  
                {
                   if($old_class_xqe_col=="x") $class_xqe_col = "z";
                   else $class_xqe_col = "x";
                } 
             }
             
             if($class_xqe_col) {
               $class_xqe = "xqe_${odd_even}_${class_xqe_col}";
               $class_xqe_prop = "class='$class_xqe'";
             }
             else
             {
               $class_xqe_prop = "";
             }
             
             
             if($class_db_structure[$orig_nom_col]["QEDIT_HIDE"])
             {
                    echo $input_html;
             }
             else
             {
?>
			<td <?=$class_xqe_prop?> align="<?=$aligntd?>" <?=$css_style?> ><?=$input_html?></td>
<?php	
             }
        }
         
?>
</tr>
<?php	
}
else
{
        
        
        // die("qedit_input = ".var_export($qedit_input,true)."\n<br> qedit_trad = ".var_export($qedit_trad,true));
        if($obj->getId()>0)
        {
             $minibox_title = $obj->getShortDisplay($lang);
             $templateNum = "";
             if($obj->isActive()) $is_disabled = "";
             else $is_disabled = "_disabled";
        } 
        else 
        {
             $minibox_title = $obj->translate('INSERT',$lang,true) ." ". $obj->translate(strtolower("$cl.new"),$lang);
             $templateNum = "2";
        }        
        $html_minibox_template = AfwShowHelper::genereMiniBoxTemplate($minibox_title,$miniBoxTemplateArr, $qedit_input, $qedit_trad, $obj->qeditNum, $templateNum, $is_disabled);
        echo $html_minibox_template; 

}
?>