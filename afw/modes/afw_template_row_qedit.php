<?php
/*
require_once("afw_config.php");*/

define("LIMIT_INPUT_SELECT", 30);


global $TMP_DIR,$TMP_ROOT,$lang,$pack,$sub_pack,$id,$aligntd, $Main_Page,
  //$class_tr1,$class_tr2, $class_titre, $class_table, $class_inputSubmit, $class_inputLien, $class_inputNew, 
  $pct_tab_edit_mode, $objme, $first_disp, $first_val, $diff_val, $not_filled, $filled, $nb_objs, $qedit_trad;

if(!$lang) $lang = 'ar';
if(!$obj) die("row-qedit-error : no object sent to the template");
$header_imbedded = $obj->qeditHeaderFooterEmbedded();
$obj_id = $obj->getId();
$obj_class = $obj->getMyClass();
$obj_mod = $obj->getMyModule();
$qedit_input_arr = array();
$qedit_orig_nom_col = array();
$qedit_trad_arr = array();

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

$qerow_exists = [0=>false, 1=>true, 2=>false];

if(!$class_db_structure) $class_db_structure = $obj->getMyDbStructure();
$nb_cols_qedit = count($class_db_structure);

$column_order = 0;

foreach($class_db_structure as $nom_col => $desc)
{
        if($desc['QEDIT-TYPE']) $desc['TYPE'] = $desc['QEDIT-TYPE'];
        if($desc['QEDIT-FROM_LIST']) $desc['FROM_LIST'] = $desc['QEDIT-FROM_LIST'];
        
        $nom_col_short = "$nom_col.short";
        $trad_col_short  = $obj->translate($nom_col_short,$lang);
        if($trad_col_short == $nom_col_short) $qedit_trad_arr[$nom_col] = $obj->translate($nom_col,$lang);
        else $qedit_trad_arr[$nom_col] = $trad_col_short;
        $column_order++;
        $desc = AfwStructureHelper::repareQEditAttributeStructure($nom_col, $desc, $obj);
        $isQuickEditableAttribute = $obj->isQuickEditableAttribute($nom_col, $desc, $submode);
        $isFixmCol = $fixm_array[$nom_col];
        
        // $log_input_qedit = "rafik 00125 $submode=[$fgroup] for field=[$nom_col] (isQuickEditableAttribute=$isQuickEditableAttribute) or isFixmCol=$isFixmCol";
        
        if(($submode=="FGROUP") and $fgroup)
        {
               $good_fgroup_for_field = (($desc["FGROUP"]==$fgroup) or ($desc["QEDIT_ALL_FGROUP"]));
               
               $mode_field_qedit = (($isQuickEditableAttribute and $good_fgroup_for_field) or $isFixmCol);
               
               //$log_input_qedit = ""; 
               /*
               if((!$mode_field_qedit) and ($fgroup=="mainwork") and ($nom_col=="study_program_id"))
               {
                    $mode_field_qedit_reason = $obj->reasonWhyAttributeNotQuickEditable($nom_col, $desc, $submode);
                    $log_input_qedit = "rafik 00126 $submode=[$fgroup] for field=[$nom_col] (isQuickEditableAttribute=$isQuickEditableAttribute and good_fgroup_for_field=$good_fgroup_for_field) or isFixmCol=$isFixmCol mode_field_qedit_reason=$mode_field_qedit_reason";
                    die($log_input_qedit);
               }*/ 
               
                
        }
        else
        {
                $mode_field_qedit = ($isQuickEditableAttribute or $isFixmCol);  // or ($fixm_array[$nom_col]) => because if we fix an attribute we should consider it in Qedit commom columns
        }        
        
        
        
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
        
        if(($column_order==1) and $obj->PK_MULTIPLE)
        {
                $qedit_orig_nom_col["id_" . $obj_qeditNum] = "id";
                ob_start();
                $type_input_ret = hidden_input("id_" . $obj_qeditNum, [], $obj->id, $obj);
                $qedit_hidden_pk_input = ob_get_clean();
                if($obj->isConsideredEmpty()) $obj_id_display = "☆";
                else $obj_id_display = "★";
                $qedit_input_arr[1]["id_" . $obj_qeditNum] = ["input"=>$qedit_hidden_pk_input.$obj_id_display, "cols"=>0];
        }

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
                $qedit_input_arr[1][$qedit_nom_col] = ["input"=>$qedit_hidden_pk_input.$obj_id_display, "cols"=>0];
               
        }        
        else
        {
                $mode_show_field_read_only = $obj->isReadOnlyAttribute($nom_col, $desc, $submode);
                /*
                if($nom_col=="student_id" and (!$mode_show_field_read_only))
                {
                        check if there's $submode and if READONLY_$submode is true
                }*/
                if(((!$desc['CATEGORY']) || ($desc['FORCE-INPUT'])) || $mode_show_field_read_only)
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
                                        $qerow = 1;
                                        $qecols = $desc['QEDIT-COLS'];
                                        if($desc['QEDIT-BEFORE-COLS'])
                                        {
                                                $qerow = 0;
                                                $qecols = $desc['QEDIT-BEFORE-COLS'];
                                        }
        
                                        if($desc['QEDIT-AFTER-COLS'])
                                        {
                                                $qerow = 2;
                                                $qecols = $desc['QEDIT-AFTER-COLS'];
                                        }
                                        $qerow_exists[$qerow] = true;
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
                                                
                                                
                                                $qedit_input_arr[$qerow][$qedit_nom_col] = ["input"=>ob_get_clean(), "cols"=>$qecols];
                                                // 
                                                if($log_input_qedit) $qedit_input_arr[$qerow][$qedit_nom_col]["input"] .= "<pre class='php'>error : ".$log_input_qedit."</pre>";
                                                
                                                if($obj->isActive() and $desc["DYNAMIC-HELP"]) $qedit_input_arr[$qerow][$qedit_nom_col]["input"] .= $obj::tooltipText($obj->getHelpFor($nom_col,$lang)); 
                                                $start_row = $obj->qeditNum;
                                                /*
                                                $input_html = $qedit _input[$qedit_nom_col];
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
                                                
                                                if($icon_unifyall) $qedit _input[$qedit_nom_col] = "<table><tr><td>$input_html</td><td>$icon_unifyall</td></tr></table>";
                                                else  $qedit_ input[$qedit_nom_col] = $input_html;*/
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
                                                
                                                
                                                $qedit_input_arr[$qerow][$qedit_nom_col] =  ["input"=>"<div class='$display_attribute_RO_class' style='$style_div_form_control'>$display_attribute_RO</div>", "cols"=>$qecols];
                                        }
                                }
                                else
                                {
                                        list($icon,$textReason, $wd, $hg) = $obj->whyAttributeIsNotApplicable($nom_col);
                                        if(!$wd) $wd = 20;
                                        if(!$hg) $hg = 20;
                                        //$obj->_error("$obj has Attribute $nom_col NotApplicable : ($icon,$textReason)");
                                        $qedit_input_arr[$qerow][$qedit_nom_col] = ["input"=>"<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='$textReason'  width='$wd' heigth='$hg'>", "cols"=>$qecols];
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
        }
        
        $col_num++;
}

/*
because we need the hidden id  ($qedit_hidden_pk_input)
if(!$obj->HIDE_DISPLAY_MODE) 
{ */
        if(($obj->PK_MULTIPLE and $obj_id) or ($obj_id>0)) 
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
                                        $data_errors_sub = array_values($data_errors_arr);
                                        $data_errors = "أخطاء كثيرة ومنها : " . implode(' / ', array_slice($data_errors_sub, 0, 3));
                                        $viewIcon = "view_error";
                                }
                        } 
                        else
                        {
                                $viewIcon = "view_ok";
                                if(!$header_imbedded) $data_errors = "لا يوجد أخطاء"; 
                        } 
                }
                else
                {
                        $viewIcon = "view_me";
                        if(!$header_imbedded) $data_errors = "لم يتم تفعيل التثبت من الأخطاء في التعديل السريع"; 
                }

                if($obj->ENABLE_DISPLAY_MODE_IN_QEDIT)
                { 
                        $qedit_input_arr[1]["show"] = ["input"=>"<a href='main.php?Main_Page=afw_mode_display.php&cl=$obj_class&id=$obj_id&currmod=$obj_mod' ><img src='../lib/images/$viewIcon.png' width='24' heigth='24' data-toggle='tooltip' data-placement='top' title='$data_errors'></a>", "cols"=>1];
                }
                else
                {
                        if($viewIcon == "view_error") $qedit_input_arr[1]["show"] = ["input"=>"<img src='../lib/images/error-red.png' width='24' heigth='24' data-toggle='tooltip' data-placement='top' title='$data_errors'>", "cols"=>1];
                        else $qedit_input_arr[1]["show"] = ["input"=>"", "cols"=>1];
                }
                $qedit_input_arr[1]["show"]["input"] .= $qedit_hidden_pk_input;
             
        }
        else
        {
                
                $qedit_input_arr[1]["show"]["input"] .= $qedit_hidden_pk_input;
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
        if($data_errors and $header_imbedded)
        {
             $total_cols = count($qedit_input_arr[1]);
?>
             <tr>
                     <td class='error' colspan='<?php echo $total_cols ?>'>
                             <?php echo $data_errors ?>
                     </td>
             </tr>
<?php        
        }    
            
   for($qerow_num=0;$qerow_num<=2;$qerow_num++)     
   {
        if($qerow_exists[$qerow_num])
        {
                if($header_imbedded)
                {
                        if(false)
                        {
                        ?>
                        <tr class="qe-header qerow<?=$qerow_num?> <?=$obj_odd_even?> <?=$obj->getHLClass()?>">
                        <?php	
                        }
                                $total_sahm = 0;
                                foreach($qedit_input_arr[$qerow_num] as $col => $input_html_row)
                                {
                                        $input_html_colspan = $input_html_row["cols"];
                                        $input_html_colspan_html = "";
                                        if($input_html_colspan>1) $input_html_colspan_html = "colspan='$input_html_colspan'";
                                        $orig_nom_col = $qedit_orig_nom_col[$col];
                                        if($orig_nom_col and ($orig_nom_col!="id"))
                                        {
                                                $total_sahm += $input_html_row["cols"];
                                                $desc = $class_db_structure[$orig_nom_col];
                                                $importance = $obj->importanceCss($orig_nom_col, $desc);                                        
                                                $class_xqe_prop = "class='col-importance-$importance col-qe header-qe-$orig_nom_col'";
                                                $col_translated = $qedit_trad_arr[$orig_nom_col];
                                                if(false)
                                                {
                        ?>
                                     <td <?=$input_html_colspan_html?> <?=$class_xqe_prop?> align="<?=$aligntd?>" >
                                        <?=$col_translated?>
                                        <!-- input type="hidden" name="<?=$orig_nom_col?>_on" value="1" -->
                                     </td>
                        <?php  
                                                }
                                        }                                   
                                }
                                if(!$total_sahm) $total_sahm = 1;
                        if(false)
                        {
                        ?>
                        </tr>
                        <?php	
                        }
                }
?>
<tr class="qerow<?=$qerow_num?> <?=$obj_odd_even?> <?=$obj->getHLClass()?> <?=get_class($obj)?>">
<?php	


        list($is_ok, $arrErrors) = $obj->isOk($force=false, $returnErrors=true);
        if(!$total_sahm)
        {
                foreach($qedit_input_arr[$qerow_num] as $col => $input_html_row)
                {
                        $orig_nom_col = $qedit_orig_nom_col[$col];
                        if($orig_nom_col and ($orig_nom_col!="id"))
                        {
                                $total_sahm += $input_html_row["cols"];                                                
                        }                                   
                }
        }
        if(!$total_sahm) $total_sahm = count($qedit_input_arr[$qerow_num]);
        if(!$total_sahm) $total_sahm = 5;
        //die(var_export($arrErrors,true));
        foreach($qedit_input_arr[$qerow_num] as $col => $input_html_row)
        {
             $input_html = $input_html_row["input"];
             $input_html_colspan = $input_html_row["cols"];
             $col_sahm = 5*round($input_html_colspan * 20 / $total_sahm); 
             if($tr_obj==$class_tr2) $tr_obj=$class_tr1; else $tr_obj=$class_tr2;
             $odd_even = trim($obj->odd_even);
             
             $orig_nom_col = $qedit_orig_nom_col[$col];
             // die("qedit_orig_nom_col = ".var_export($qedit_orig_nom_col,true));
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

             $desc = $class_db_structure[$orig_nom_col];
             $importance = $obj->importanceCss($orig_nom_col, $desc);
             
             if($class_xqe_col) {
               $class_xqe = "xqe_${odd_even}_${class_xqe_col}";
               $class_xqe_prop = "class='col-importance-$importance $class_xqe col-qe col-qe-$col_sahm col-qe-$orig_nom_col'";
             }
             else
             {
               $class_xqe_prop = "class='col-importance-$importance col-qe col-qe-$col_sahm col-qe-$orig_nom_col'";
             }
             
             
             if(($class_db_structure[$orig_nom_col]["QEDIT_HIDE"]) or ($orig_nom_col=="id"))
             {
                    echo $input_html;
             }
             else
             {
                $input_html_colspan_html = "";
                if($input_html_colspan>1) $input_html_colspan_html = "colspan='$input_html_colspan'";
                if($orig_nom_col and ($orig_nom_col!="id"))
                {
                        $col_translated = $qedit_trad_arr[$orig_nom_col];
                        
?>
			<td <?=$input_html_colspan_html?> <?=$class_xqe_prop?> align="<?=$aligntd?>" <?=$css_style?> >
                        <?php 
                                
                                if($header_imbedded)
                                {
?>
                        <label class='imbedded-label'><?=$col_translated?></label>
<?php 
                                }
                                echo $input_html;
                        ?>
                        </td>
<?php	
                }
             }
        }
         
?>
</tr>
<?php	
        }
   }

   
}
else
{
        $qedit_input_for_mb = [];
        for($qerow_num=0;$qerow_num<=2;$qerow_num++)     
        {
                foreach($qedit_input_arr[$qerow_num] as $ncol => $qedit_input_row)
                {
                        $qedit_input_for_mb[$ncol] = $qedit_input_row["input"];
                }
        }
        
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
        $html_minibox_template = AfwShowHelper::genereMiniBoxTemplate($minibox_title,$miniBoxTemplateArr, $qedit_input_for_mb, $qedit_trad, $obj->qeditNum, $templateNum, $is_disabled);
        echo $html_minibox_template; 

}
?>