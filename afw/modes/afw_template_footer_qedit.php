<?php
echo "<!-- footer qedit start ".date("Y-m-d H:i:s")." -->";
$altern_xy = true;
global $lang;
if(!$lang) $lang = 'ar';

include("afw_config.php");

define("LIMIT_INPUT_SELECT", 30);



global $TMP_DIR,$TMP_ROOT,$lang,$cl,$pack,$sub_pack,$id,$aligntd, $first_disp, $first_val, $diff_val, $not_filled, $filled, $nb_objs,
// $class_tr1, $class_tr2, $class_titre, $class_table, $class_inputSubmit, $class_inputLien, $class_inputNew, 
$pct_tab_edit_mode, $qedit_other_search, $Main_Page, 
$objme, $qedit_trad, $popup;



$col_count = 1;

$class_db_structure = $obj->getMyDbStructure();

foreach($class_db_structure as $nom_col => $desc)
{
      $mode_field_read_only = (isset($desc["READONLY"]) && ($desc["READONLY"]));
      $mode_field_edit = (isset($desc["EDIT"]) &&  $desc["EDIT"]);
      $mode_field_show = (isset($desc["SHOW"]) &&  $desc["SHOW"]);
      $isTechField = $obj->isTechField($nom_col);
      $mode_field_read_only = ($mode_field_show && (isset($desc["READONLY"]) && ($desc["READONLY"])));

      if((((!$desc["CATEGORY"]) || $mode_field_read_only)) && empty($isTechField))
      {

            if($mode_field_edit || $mode_field_show)
            {
                $col_count++;        
            }
      }
}


?>
       
<tfoot>
<?
if(!$obj->qedit_minibox) 
{

if($obj->QEDIT_FOOTER_SUM)
{
?>
       <tr class='footer_sum' >
<?php	foreach($qedit_trad as $col => $info)
        {
           if($class_db_structure[$col]["FOOTER_SUM"])
           {
               $col_sum_total = $obj->qeditSum[$col];
               if($obj->class_footer_sum_input) $footer_sum_input = $obj->class_footer_sum_input;
               else $footer_sum_input = "footer_sum_input";
               
               $class_input = "inputtext inputtrescourt data_loaded $footer_sum_input";
               $text_input = "<input type='text' class='$class_input' name='${col}_total' id='${col}_total' value='$col_sum_total' size=6 maxlength=6 readonly>";
           }
           else
           {
               $text_input = "";
           }
           
           if($class_db_structure[$col]["FOOTER_SUM_TITLE"])
           {
              $text_input = $class_db_structure[$col]["FOOTER_SUM_TITLE"];
              $aligntd = "left";
           }
           else
           {
              $aligntd = "right";
           }
           
           $old_class_xqe_col = $class_xqe_col;
           
           if($col=="show") $class_xqe_col = "z";
           else $class_xqe_col = $class_db_structure[$col]["XQE_COL_STYLE"];
           if((!$class_xqe_col) and $altern_xy)  
           {
                   if($old_class_xqe_col=="x") $class_xqe_col = "z";
                   else $class_xqe_col = "x";
           } 
           
             
           if($class_xqe_col) {
               $class_xqe = "xqe_sum_footer_${class_xqe_col}";
               $class_xqe_prop = "class='$class_xqe col-qe col-qe-$col'";
           }
           else
           {
               $class_xqe_prop = "class='col-qe col-qe-$col'";
           }
           
           $th_props = "style='text-align:$aligntd !important' $class_xqe_prop";
?>
             <th hint='for-sum' <?=$th_props?>><?=$text_input?></th>
		
<?php	}
?>
       </tr>
<?

}
?>



<tr>
<?php	
    if($data_template["nb_records"]>8)
    {
      foreach($qedit_trad as $col => $info)
      {
          $old_class_xqe_col = $class_xqe_col;
          if($col=="show") $class_xqe_col = "z";
          else $class_xqe_col = $class_db_structure[$col]["XQE_COL_STYLE"];
          
          if((!$class_xqe_col) and $altern_xy)  
          {
                if($old_class_xqe_col=="x") $class_xqe_col = "z";
                else $class_xqe_col = "x";
          } 
          
          
          if($class_xqe_col) {
            $class_xqe = "xqe_hf_${class_xqe_col}";
            $class_xqe_prop = "class='$class_xqe col-qe col-qe-$col'";
          }
          else
          {
            $class_xqe_prop = "class='col-qe col-qe-$col'";
          }
          
          if($class_db_structure[$col]["QEDIT_HIDE"])
          {
                echo " ";
          }
          else
          {
?>
                <th <?=$class_xqe_prop?> <?=$style?> align="<?=$aligntd?>"><?=$info?></th>
<?php	
          }
             
   	  }
    }
?>
</tr>
<?
} 

  if($obj->copypast) 
  {
?>  
<tr>  
<?php	foreach($qedit_trad as $col => $info)
        {

?>
                <th align="<?=$aligntd?>">
<?
                if(false) //(($col!="id") and (($not_filled[$col]>=4) or ($diff_val[$col])) and ($first_disp[$col]))
                {
                      $disp_unify = substr($first_disp[$col],0,24) . "..";
                 
?>                
                   <input type="button" name="unify_all_<?=$col?>"  id="unify_all_<?=$col?>" class="yellowbtn submit-btn fright" value="تعميم إلى '<?=$disp_unify?>'" height="30px" onClick="unify_all_select('<?=$col?>','<?=$first_val[$col]?>','<?=$disp_unify?>',0,<?=$nb_objs?>);"/>
<?
                }

                if(($col!="id") and ($col != "show")) //($col!="id")     
                {
?> 
                    <a href="#" class="copy_paste" onClick="paste_col('<?=$col?>',0);">&nbsp;&nbsp;&nbsp;</a> 
<?
                }
?>
               </th>

<?php	}
?>
</tr>
<? 
  }
?>
</table>
<?
  if($obj->copypast) 
  {
?>        
<table cellspacing="3" cellpadding="4" style="width: 100% !important;">
<tr class="table_obj">
  <th align="center" colspan="<?=$col_count?>" height="72" valign="middle" >
                <div id="accordion">
                  <h3 class='th_data'>وسيلة لتسهيل نسخ - لصق للنصوص المستوردة من ملفات أخرى</h3>
                  <div>
                    <textarea id="cbrd" name="cbrd" cols='120' rows='20'></textarea>
                  </div>
                </div>
</th>
</tr>
</table>
<? 
  }
  $col_num = 0;
?>
</div>
<div class="hzm_panel_link_bar footer">
<div class='fright full-right-width'>
<?php
   if($obj->return_mode)
   {
         $submit_qedit_title_code = 'UPDATE_AND_RETURN';
         $submit_name = "submit_return";
   }
   else
   {
         $submit_qedit_title_code = 'UPDATE';
         $submit_name = "submit";
   }
?>    
    <input type="submit" name="<?=$submit_name?>"  id="submit-form" class="<?=$class_inputSubmit?>" value="&nbsp;<?=$obj->translate($submit_qedit_title_code,$lang,true)?>&nbsp;" width="200px" height="30px" />
        
<?php
  if(!$popup)
  {
          $col_num++;
          if($obj->id_origin and (!$obj->return_mode)) 
          {  
               if($obj->mode_origin) $mode_origin = $obj->mode_origin; else $mode_origin = "display";  
?>        
        <a href="main.php?Main_Page=afw_mode_<?=$mode_origin?>.php&cl=<?=$obj->class_origin?>&id=<?=$obj->id_origin?>&currmod=<?=$obj->module_origin?>&currstep=<?=$obj->step_origin?>"><span class="yellowbtn submit-btn fright"><?=$back_to_last_form?></span></a>
        
<?php 
                $col_num++;
          }
          
          //@todo : not implemented 
          if($obj->coming_from_search) 
          { 
?>
     <a href="main.php?Main_Page=afw_mode_search.php&cl=<?=$cl?>"><span class="bluebtn submit-btn fleft"><?=$new_search_operation?></span></a>
<?php
          }
   }
?>
</div>   
<?
   $col_num++;
   $other_links = $obj->getOtherLinksForUser("qedit", $objme);
   $parent = $obj->getParentObject();
   if($parent)
   {
        $attribute_arr = $parent->getAttributesFriendOf($obj);
        foreach($attribute_arr as $attribute_parent)
        {
            $other_links_parent = $parent->getOtherLinksForUser("mode_".$attribute_parent, $objme);
            if(count($other_links_parent)>0) $other_links = array_merge($other_links,$other_links_parent);
        }
        
   }

   if(false)
   {
           if(!$obj->nbQeditLinksByRow) $obj->nbQeditLinksByRow = 3;
           foreach($other_links as $k => $other_link)
           {
              if($col_num == ($obj->nbQeditLinksByRow+1))
              {
                  $col_num = 0;
              }
              $falign = "f".strtolower($other_link["FLOAT"]);
              if($falign=="f") $falign = "fleft";
              $link_color = $other_link["COLOR"];
              if(!$link_color) $link_color = "red";
              
        ?>
                 <a class="<?=$falign?>" href="<?=$other_link["URL"]?>">
                    <span class="<?=$link_color?>btn submit-btn <?=$falign?>"><?=$other_link["TITLE"]?></span>
                 </a>
        <?
             $col_num++;
           }
   }
   elseif(count($other_links)>0)
   {
           $key_mod_tr = $obj->translateOperator("other_functions",$lang);
           
           $html_btns =  "<div class='fleft'><div class='btn-group'>";
           $html_btns .= "  <button type='button' class='btn btn-primary'>$key_mod_tr</button>";
           $html_btns .= "  <button type='button' class='btn-primary dropdown-toggle' data-toggle='dropdown'>";
           $html_btns .= "    <span class='caret'></span>";
           $html_btns .= "  </button>";
           $html_btns .= "  <ul class='dropdown-menu' role='menu'>";
           foreach($other_links as $k => $other_link)
           {
                $o_url = $other_link["URL"];
                $o_tit = $other_link["TITLE"];
                $html_btns .= "    <li><a href='$o_url'>$o_tit</a></li>";
           }   
           $html_btns .= "  </ul>";
           $html_btns .= "</div></div>";
           
           echo $html_btns;   
   }
?>        
</div>

<script>
  $(function() {
           /*$( "#datepicker" ).datepicker();*/
           $( "#accordion" ).accordion({
              collapsible: true
            });

        });
</script>
<?php
  echo "<!-- footer qedit end ".date("Y-m-d H:i:s")." -->";

?>