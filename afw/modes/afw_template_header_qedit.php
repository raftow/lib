<?php

global $lang;
if(!$lang) $lang = 'ar';

$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}
define("LIMIT_INPUT_SELECT", 30);


global $TMP_DIR,$TMP_ROOT,$cl,$pack,$sub_pack,$id,$aligntd, $Main_Page, $qedit_trad;
$objme = AfwSession::getUserConnected();

$qedit_trad = array();
$fixm_input = array();
$fixm_array       = $obj->fixm_array;
$fgroup           = $obj->fgroup;
$submode          = $obj->submode;
// die("fixm_array = ".var_export($fixm_array,true));

$fixm_cols =  array();
$fixm_vals =  array();
$fixm_trad =  array();

$js_for_compute = "";

$cols_for_compute = array();

$class_db_structure = $obj->getMyDbStructure();

foreach($class_db_structure as $nom_col => $desc)
{
    if($desc["IN-FORMULA"]) $cols_for_compute[] = $nom_col;
}
$column_order=0;
foreach($class_db_structure as $nom_col => $desc)
{
        $column_order++;
        if(($submode=="FGROUP") and $fgroup)
        {
               /*
               if($nom_col=="jobrole_id")
               {
                    $qeditable = AfwStructureHelper::isQuickEditableAttribute($obj, $nom_col, $desc, $submode);
                    die("$nom_col : submode=$submode , fgroup=$fgroup , qeditable=$qeditable , desc[FGROUP]=".$desc["FGROUP"]);
               }*/
               
               $mode_field_qedit = ((AfwStructureHelper::isQuickEditableAttribute($obj, $nom_col, $desc, $submode) and (($desc["FGROUP"]==$fgroup) or ($desc["QEDIT_ALL_FGROUP"]))) or ($fixm_array[$nom_col]));
        }
        else
        {
                $mode_field_qedit = (AfwStructureHelper::isQuickEditableAttribute($obj, $nom_col, $desc, $submode) or ($fixm_array[$nom_col]));  // or ($fixm_array[$nom_col]) => because if we fix and attribute we should consider it in Qedit commom columns
        }
        
        $mode_show_field_read_only = AfwStructureHelper::isReadOnlyAttribute($obj, $nom_col, $desc, $submode);
        $mode_field_qedit_reason = AfwStructureHelper::reasonWhyAttributeNotQuickEditable($obj, $nom_col, $desc, $submode);
        
        $mode_field_qedit_arr[$nom_col] = array("qed"=>$mode_field_qedit,"reason"=>$mode_field_qedit_reason);
        
        /*
        if(($obj->valAtable_name=="medical_visit") and ($nom_col == "jobrole_id")) die("$nom_col : attr_IsApplicable $attr_IsApplicable scis = ".var_export($obj->getScis(),true));
        if(($nom_col == "answer_module_id") and (!$mode_field_qedit)) die("why nom_col=$nom_col is not quick editable: <br> mode_field_qedit=$mode_field_qedit, <br>mode_field_qedit_reason=$mode_field_qedit_reason, <br>attr_IsApplicable=$attr_IsApplicable");
        */
        
        
        //echo "$nom_col <br>";
        $separator = $obj->getSeparatorFor($nom_col);
	//$isTechField = AFWObject::isTechField($nom_col);
	// if(!$desc["CATEGORY"])
        $nom_col_short = "$nom_col.short";
        $trad_col_short  = $obj->translate($nom_col_short,$lang);

        if(($column_order==1) and $obj->PK_MULTIPLE)
        {
                $qedit_trad["id"] = $obj->translate("id",$lang);                
        }
                        
        if($desc['TYPE'] == 'PK')
        {
                if($trad_col_short == $nom_col_short) $qedit_trad[$nom_col] = $obj->translate($nom_col,$lang);
                else $qedit_trad[$nom_col] = $trad_col_short;
        }
        elseif(((!$desc['CATEGORY']) || ($desc['FORCE-INPUT'])) || $mode_show_field_read_only)           
        {
                //$obj->simpleError("fixm_array = ".var_export($fixm_array,true));
        	if($mode_field_qedit)
		{
                    //if($nom_col=="symbol") die(var_export($fixm_array,true));
                    if(!isset($fixm_array[$nom_col]))
                    {
                        if($trad_col_short == $nom_col_short) $qedit_trad[$nom_col] = $obj->translate($nom_col,$lang);
                        else $qedit_trad[$nom_col] = $trad_col_short;
                         
                        $unit  = $desc["UNIT"];
                        $hide_unit  = $desc["RETREIVE_HIDE_UNIT"];
                        if($unit and (!$hide_unit)) $qedit_trad[$nom_col] .= " ($unit)";
                        
                    }
                    else
                    {
                        $fixm_trad[$nom_col]  = $obj->translate($nom_col,$lang);            
                        $fixm_cols[] = $nom_col;
                        
        		$col_val = $fixm_array[$nom_col];
                        $fixm_vals[] = $col_val;
                        
                        ob_start();
                        // if($nom_col=="symbol") die("type_input($nom_col, $desc, $col_val, $obj, $separator, true, inputlong);");
                	type_input($nom_col, $desc, $col_val, $obj, $separator, true, "inputlong");
                        
        		$fixm_input[$nom_col] = ob_get_clean();
                        
                        // if($nom_col=="symbol") die("type_input($nom_col, $desc, $col_val, obj, '$separator', true, inputlong) = ".var_export($fixm_input,true));
                    }
                }
	}
        else $ignored_qedit_arr[$nom_col] = array("TYPE"=>$desc['TYPE'], "CATEGORY"=>$desc['CATEGORY'], "FORCE-INPUT"=>$desc['FORCE-INPUT'], "R/O"=>$mode_show_field_read_only );
                          
        
        
        
        if($desc['JS-COMPUTED']) {
           
           $js_formula = $desc["JS-FORMULA"];
           foreach($cols_for_compute as $colcompute) $js_formula = str_replace("§$colcompute"."§","parsePagFloat($('#'+'$colcompute"."_'+row).val())",$js_formula);
           
           $js_for_compute .= "\nfunction compute_$nom_col"."(row) { 
           
                      $('#'+'$nom_col"."_'+row).val($js_formula);
           }
           
           ";
        }
}

// die(var_export($fixm_trad,true));



/*if(!$obj->HIDE_DISPLAY_MODE) */ 

if($obj->ENABLE_DISPLAY_MODE_IN_QEDIT)
{
   $qedit_trad["show"] = AfwLanguageHelper::translateKeyword("show", $lang);
}
else
{
   $qedit_trad["show"] =  "";
}

// @todo if($obj->QEDIT_SHOW_EDIT_MODE) $qedit_trad["edit"] = AfwLanguageHelper::translateKeyword("edit", $lang);

/*
$obj->simpleError("fixm_input = ".var_export($fixm_input,true)."    
     ||   ignored   = ".var_export($ignored_qedit_arr,true)."
     ||   fixm_trad = ".var_export($fixm_trad,true)."
     ||   mode_field_qedit_arr = ".var_export($mode_field_qedit_arr,true));*/
// die(var_export($fixm_input,true));
$css       = $obj->getStyle();
//die("css = $css");
//$str_label = ($mode_edit)? $obj->translate('EDIT',$lang,true) : $obj->translate('INSERT',$lang,true);
//$str_titre = $obj->__toString();
//$str_new = $obj->translate(strtolower("$cl.new"),$lang);
//$str_id = ($mode_edit)? "$mode_edit - $str_titre" : $str_new;
//$str_name = $obj->translate("FILE",$lang,true)." ".$obj->singleTranslation($lang);
//echo "rafik1"; print_r($obj);

if($obj->updatedFromQEdit)
{
    $obj_updatedFromQEdit = AfwLanguageHelper::translateKeyword("records_updated", $lang)." (".$obj->updatedFromQEdit.") ".AfwLanguageHelper::translateKeyword("record(s)", $lang);
}
else
{
    $obj_updatedFromQEdit = "";
}

//die($obj->fixmtit);

if($obj->fixmtit)
{
    $fixmtit = $obj->fixmtit;
}
else
{
    $fixmtit = AfwLanguageHelper::translateKeyword("qedit_some_records", $lang).AfwUmsPagHelper::getPluralTitle($obj, $lang,false);
}

if($js_for_compute) echo "<script>\n $js_for_compute \n</script>\n";

     if(!$obj->qedit_minibox) 
     {  

        if($obj->elevatezoom)
        {
?>
<script src='../js/jquery.elevatezoom.js'></script>
<?
        }
?>
<div class="hzm_panel_link_bar header">
<?
if($fixmtit) 
{
?>
<h3 class="bluetitle"><i></i> <?php echo $fixmtit?></h3>
<?
}
else
{
?>
<h3 class="bluetitle">وصف السجلات التي يتم العمل عليها</h3>
<?
}

if((count($fixm_trad)) and (!$obj->hideQeditCommonFields)) 
{
?>
<!-- ************* fixmgrid as hideQeditCommonFields option disabled *********** -->
<table class="fixmgrid" style="width: 100%;">
<?php	foreach($fixm_trad as $col => $info)
        {
           echo "<tr><th>$info</th><td>".$fixm_input[$col]."</td><tr>";

	}
?>
</table>
<?
}
else
{
        echo "<!-- ************* list of inputs as hideQeditCommonFields option is enabled *********** -->";
        //$obj->simpleError(var_export($fixm_input,true));
        //echo("obj->fixmtit=$obj->fixmtit");
        foreach($fixm_input as $col => $input)
        {
           echo "<!-- fixminput of $col -->".$input;

	}
}
?>
<input type="hidden" name="fixm_cols" value="<?=implode(",",$fixm_cols)?>">
<input type="hidden" name="fixm_vals" value="<?=implode(",",$fixm_vals)?>">

<table class="<?=$display_grid?>" style="width: 100%;" cellspacing="3" cellpadding="4">
<thead>
<tr>
<?php	foreach($qedit_trad as $col => $info)
        {
             $old_class_xqe_col = $class_xqe_col;
             if($col=="show") $class_xqe_col = "z";
             else $class_xqe_col = $class_db_structure[$col]["XQE_COL_STYLE"];
             
             if((!$class_xqe_col) and $altern_xy)  
             {
                   if($old_class_xqe_col=="x") $class_xqe_col = "z";
                   else $class_xqe_col = "x";
             } 
             $desc = $class_db_structure[$col];
             $importance = AfwHtmlHelper::importanceCss($obj, $col, $desc);
             
             if($class_xqe_col) {
               $class_xqe = "xqe_hf_${class_xqe_col}";
               $class_xqe_prop = "class='col-importance-$importance $class_xqe col-qe col-qe-$col'";
             }
             else
             {
               $class_xqe_prop = "class='col-importance-$importance col-qe col-qe-$col'";
             }

             if($class_db_structure[$col]["QEDIT_HIDE"])
             {
                    echo " ";
             }
             else
             {
?>
                    <th <?=$class_xqe_prop?> <?=$style?> align="<?=$aligntd?>"><?=$info?><input type="hidden" name="<?=$col?>_on" value="1"></th>
<?php	
	     }
        }
       
?>
</tr>
</thead>
<?php
        }
        else
        {
                if($fixmtit) 
                {
                ?>
                <h3 class="bluetitle"><i></i> <?php echo $fixmtit?></h3>
                <?
                }
                else
                {
                ?>
                <h4 class="th_data">وصف السجلات التي يتم العمل عليها</h4>
                <?
                }
        }
?>