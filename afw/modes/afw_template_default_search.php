<?php
require_once(dirname(__FILE__)."/../../../external/db.php");
require_once 'afw_config.php';
require_once 'afw_rights.php';
global  $TMP_DIR,$cl,$pk,$spk,$TMP_ROOT, $lang, $class_table, $class_tr1, $class_tr2, $pct_tab_search_criteria, $class_tr1_sel, $class_tr2_sel ;
$objme = AfwSession::getUserConnected();

if(!$lang) $lang = 'ar';

//echo "langue = $lang <br>";

//$lab_id = AfwLanguageHelper::tarjem("id",$lang,true);
define("LIMIT_INPUT_SELECT", 30);
$data = array();
if(isset($class_obj))
{
	require $file_obj;
	$obj=new $class_obj();
}

$class_db_structure = $obj->getMyDbStructure();

foreach($class_db_structure as $nom_col => $desc)
{
      list($is_category_field, $is_settable_attribute) = AfwStructureHelper::isSettable($obj, $nom_col);
      if(($_POST[$nom_col]) and ($_POST["oper_$nom_col"] == "=") and $is_settable_attribute)
      {
            $obj->set($nom_col, $_POST[$nom_col]); 
      }
}

foreach($class_db_structure as $nom_col => $desc)
{
	
   if($obj->isSearchCol($nom_col, $desc))
   {
      $filled_val = $_POST[$nom_col];
      $filled_val2 = $_POST[$nom_col."_2"];
      $filled_oper = (($_POST["oper_$nom_col"] == "=''") or ($_POST["oper_$nom_col"] == "!=''"));
      $data[$nom_col]["filled_criteria"] = ($filled_val or $filled_oper or $filled_val2);
      //if($nom_col=="year") die("filled_criteria = ($filled_val or $filled_oper or $filled_val2) . _POST=".var_export($_POST,true));

      $data[$nom_col]["trad"]  = $obj->translate($nom_col,$lang);
      //if($obj->getPKField()==$nom_col) $data[$nom_col]["trad"].=" ($lab_id)";
      if($nom_col==$obj->fld_ACTIVE())
      {
         if(!isset($desc["SEARCH-BY-ONE"])) $desc["SEARCH-BY-ONE"] = true; 
      } 

      ob_start();
      type_input($nom_col, $desc, $obj,$data[$nom_col]["filled_criteria"]);

      $data[$nom_col]["input"] = ob_get_clean();
      ob_start();
      type_oper($nom_col, $desc,$obj,$data[$nom_col]["filled_criteria"]);

      $data[$nom_col]["oper"] = ob_get_clean();
                
	}
}

?>

<? 
   $obj->filtreVertical = true;
?>   
<table  width="100%"><tr><td>
<? 
   if(!$obj->filtreVertical) $class_table = "filtreH";
?>

<? 
   $tr_obj=$class_tr1;
   $qsearch_by_text = $_POST["qsearch_by_text"];
   $desc_qsearch_by_text = array('TYPE'=>'TEXT', 'SIZE'=>64, 'UTF8'=>true);
   ob_start();
   type_input("qsearch_by_text", $desc_qsearch_by_text, $obj, $qsearch_by_text);
   $trad_qsearch_by_text_input = ob_get_clean();
    
   $trad_qsearch_by_text = $obj->translate("qsearch_by_text",$lang);
   $trad_qsearch_by_help = $obj->translate("qsearch_by_help",$lang);
   
   $translated_text_searchable_cols_arr = $obj->translateCols($obj->getAllTextSearchableCols(),$lang); 
   
   $translated_text_searchable_cols_txt = $trad_qsearch_by_help." : ".implode("? ", $translated_text_searchable_cols_arr);
   
?>
   
<table class="<?=$class_table?>" cellspacing="3" cellpadding="4" style="min-width: 66% !important;">

	<? 
             $numFiltre = 0;
             $xFiltre = 0;
             $colFiltre = 0;
             foreach($data as $col => $info)
             {
                if($info["trad"])
                { 
                        if($info["filled_criteria"])
                        {
                                if(($tr_obj==$class_tr2_sel) or ($tr_obj==$class_tr2))
                                   $tr_obj=$class_tr1_sel; 
                                else 
                                   $tr_obj=$class_tr2_sel;
                        }
                        else
                        {
                                if($tr_obj==$class_tr2) 
                                   $tr_obj=$class_tr1; 
                                else 
                                   $tr_obj=$class_tr2;
                        }
                        
                        if($obj->filtreVertical)
                        {
                ?>
        		<tr class="<?=$tr_obj?>" align="right">
        			<td width="15px" ></td>
        			<td><?php echo $info["trad"]; ?></td>
        			<td><?php echo $info["oper"];?></td>
        			<td><?php echo $info["input"];?></td>
        		</tr>
        	<? 
                        }
                        else
                        {
                            
                        
                            $xFiltre = intval($numFiltre/4);
                            
                            $data_4col_ordered[$xFiltre][$colFiltre] = $info;
                            $numFiltre++;
                            $colFiltre++;  if($colFiltre==4) $colFiltre = 0;
                        }
                } 
             }
             
             // input for qsearch by text
             
             if($qsearch_by_text)
             {
                        if(($tr_obj==$class_tr2_sel) or ($tr_obj==$class_tr2))
                           $tr_obj=$class_tr1_sel; 
                        else 
                           $tr_obj=$class_tr2_sel;
             }
             else
             {
                        if($tr_obj==$class_tr2) 
                           $tr_obj=$class_tr1; 
                        else 
                           $tr_obj=$class_tr2;
             } 
             
?>             
             <tr class="<?=$tr_obj?>" align="right">
        			<td width="15px" ></td>
        			<td><?php echo $trad_qsearch_by_text; ?></td>
        			<td><img src='../lib/images/tooltip.png' class='tooltip-icon' data-toggle='tooltip' data-placement='top' title='<?=$translated_text_searchable_cols_txt?>'  width='20' heigth='20'></td>
        			<td><?php echo $trad_qsearch_by_text_input;?></td>
   	     </tr>
<?
             if(!$obj->filtreVertical)
             {
                     foreach($data_4col_ordered as $xFiltre => $info_4col_row)
                     {
                        for($col=0;$col<4;$col++) {
                                if(!$info_4col_row[$col]["filled_criteria"]) {
                                     ${"th_cls_$col"} = "filtre_not_filled_th";
                                     ${"td_cls_$col"} = "filtre_not_filled_td";
                                }
                                else
                                {
                                     ${"th_cls_$col"} = "filtre_filled_th";
                                     ${"td_cls_$col"} = "filtre_filled_td";
                                } 
                        }
                     
                ?>
        		<tr align="right">
        	<? if($info_4col_row[0]["trad"]) { ?>		<th class="<?=$th_cls_0?>"><center><?php echo $info_4col_row[0]["trad"]; ?></center></th> <? } ?>
        	<? if($info_4col_row[1]["trad"]) { ?>		<th class="<?=$th_cls_1?>"><center><?php echo $info_4col_row[1]["trad"]; ?></center></th> <? } ?>
        	<? if($info_4col_row[2]["trad"]) { ?>		<th class="<?=$th_cls_2?>"><center><?php echo $info_4col_row[2]["trad"]; ?></center></th> <? } ?>
        	<? if($info_4col_row[3]["trad"]) { ?>		<th class="<?=$th_cls_3?>"><center><?php echo $info_4col_row[3]["trad"]; ?></center></th> <? } ?>
        		</tr>
                        <tr class="<?=$tr_obj?>" align="right">
        	<? if($info_4col_row[0]["trad"]) { ?>		<td class="<?=$td_cls_0?>"><center><?php echo $info_4col_row[0]["oper"]; ?></center></td> <? } ?>
        	<? if($info_4col_row[1]["trad"]) { ?>		<td class="<?=$td_cls_1?>"><center><?php echo $info_4col_row[1]["oper"]; ?></center></td> <? } ?>
        	<? if($info_4col_row[2]["trad"]) { ?>		<td class="<?=$td_cls_2?>"><center><?php echo $info_4col_row[2]["oper"]; ?></center></td> <? } ?>
        	<? if($info_4col_row[3]["trad"]) { ?>		<td class="<?=$td_cls_3?>"><center><?php echo $info_4col_row[3]["oper"]; ?></center></td> <? } ?>
        		</tr>
                        <tr class="<?=$tr_obj?>" align="right">
        	<? if($info_4col_row[0]["trad"]) { ?>		<td class="<?=$td_cls_0?>"><center><?php echo $info_4col_row[0]["input"]; ?></center></td> <? } ?>
        	<? if($info_4col_row[1]["trad"]) { ?>		<td class="<?=$td_cls_1?>"><center><?php echo $info_4col_row[1]["input"]; ?></center></td> <? } ?>
        	<? if($info_4col_row[2]["trad"]) { ?>		<td class="<?=$td_cls_2?>"><center><?php echo $info_4col_row[2]["input"]; ?></center></td> <? } ?>
        	<? if($info_4col_row[3]["trad"]) { ?>		<td class="<?=$td_cls_3?>"><center><?php echo $info_4col_row[3]["input"]; ?></center></td> <? } ?>
        		</tr>
        	<?
                     }
             } 
        ?>
</table>
</td></tr></table>


