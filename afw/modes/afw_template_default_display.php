<?php
global $lang;
if(!$lang) $lang = 'ar';

$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}
require_once 'afw_rights.php';

global $currmod,$sub_pack,$cl,$TMP_ROOT, $uri_module, $popup, $file_box_css_class;

$objme = AfwSession::getUserConnected();

$next_color_arr = array("red"=>"yellow","yellow"=>"blue","blue"=>"green","green"=>"darkblue","darkblue"=>"gray","gray"=>"red");
$last_color = "yellow";

$data = array();
$link = array();
$sub_pack = str_replace('_', '', $sub_pack);
$pack= str_replace('_', '', $pack);

if($obj->editByStep)
{
   if((!$obj->currentStep) or $obj->getForceDefaultStep()) $obj->currentStep = $obj->getDefaultStep();  
   // @todo-if(!$obj->currentStep) $obj->currentStep = $objme->curStepFor[$obj->getTableName()][$obj->getId()];
   // @todo-if(!$obj->currentStep) $obj->currentStep = $objme->curStepFor[$obj->getTableName()][-1];
   if(!$obj->currentStep) $obj->currentStep = 1;
   
   if($obj->currentStep)
   {
        $page_editable = (AfwFrameworkHelper::stepIsEditable($obj, $obj->currentStep) and (!AfwStructureHelper::stepIsReadOnly($obj,$obj->currentStep)));
   }
   else $page_editable = false;
}
else
{
   $page_editable = true;
}

if(!$obj->editByStep)
{
    if(AfwSession::hasOption("CHECK_ERRORS")) $obj_errors = AfwDataQualityHelper::getDataErrors($obj, $lang);
}
else
{
    if(AfwSession::hasOption("CHECK_ERRORS")) $obj_errors = AfwDataQualityHelper::getStepErrors($obj, $obj->currentStep, $lang);
}    

$class_db_structure = $obj->getMyDbStructure();

foreach($class_db_structure as $key => $structure)
{	
        $mode_display = AfwPrevilegeHelper::keyIsToDisplayForUser($obj, $key, $objme);
        $key_is_applicable = $obj->attributeIsApplicable($key);
        if($obj->editByStep)
        {
           if(!isset($structure["STEP"])) $structure["STEP"] = 1;
           if($mode_display)
           {
                     if(strtolower($structure["STEP"])!="all")
                     {
                               if($obj->currentStep > $structure["STEP"]) $mode_display = false; //$mode_field_read_only = true;
                               if($obj->currentStep < $structure["STEP"]) $mode_display = false;
                     }    
           }
        }
        if(!$popup) $popup = $obj->popup;
        $buttons = ($structure["BUTTONS"] and (!$popup));
        if($mode_display and $key_is_applicable)
	{
	       
               list($data_to_display, $link_to_display) = $obj->displayAttribute($key);
               if(is_array($data_to_display) and (!is_array($link_to_display)) and $objme->isSuperAdmin())
               {
                     throw new AfwModeException("displayAttribute($key) should return both elements same type array or not array : data_to_display = ".var_export($data_to_display,true)." link_to_display=$link_to_display");
               }
               
	       $data[$key] = $data_to_display;
	       $link[$key] = $link_to_display;
               
               $key_mod = "mode_$key";
               $key_mod_tr = $obj->translate($key_mod,$lang);
               if($key_mod_tr == $key_mod) $key_mod_tr = $obj->translate($key,$lang);
               if($buttons)
               {
                   $other_links = $obj->getOtherLinksForUser($key_mod, $objme);
                   
                   $auth_links = array();
                   foreach($other_links as $k => $other_link)
                   {
                        // if OTHER LINKS CHECK RIGHTS
                        $o_mode = $other_link["MODE"];
                        $o_module = $other_link["MODULE"];
                        $o_class = $other_link["CLASS"];
                        
                        // rafik : disable this check because too much called and so become slow
                        // @todo : find another more easy solution for this check (very called)
                        /*
                        if($o_module and $o_mode and $o_class)
                        {
                            $myObj = new $o_class();
                            $can_see_menu = $objme->iCanDoOperationOnObjClass($myObj,$o_mode);
                            // list($can_see_menu,$bf_id, $reason) = $myObj->userCan($objme, $o_module, $o_mode);    
                        }
                        else */
                        
                         $can_see_menu = true;
                       
                        if($can_see_menu)
                        {
                             $auth_links[] = $other_link;
                        }
                        
                   }
                   
                              
                   if(count($auth_links)>2)
                   {
                           // use bootstrap design version if many links
                           $btns[$key] =  "<div class='btn-group'>";
                           $btns[$key] .= "  <button type='button' class='btn btn-primary'>$key_mod_tr</button>";
                           $btns[$key] .= "  <button type='button' class='btn-primary dropdown-toggle' data-toggle='dropdown'>";
                           $btns[$key] .= "    <span class='caret'></span>";
                           $btns[$key] .= "  </button>";
                           $btns[$key] .= "  <ul class='dropdown-menu' role='menu'>";
                           foreach($auth_links as $k => $other_link)
                           {
                                $o_url = $other_link["URL"];
                                $o_tit = $other_link["TITLE"];
                                $btns[$key] .= "    <li><a href='$o_url'>$o_tit</a></li>";
                           }   
                           $btns[$key] .= "  </ul>";
                           $btns[$key] .= "</div>";
                   }
                   else
                   {
                           //$last_color = "darkblue";
                           // table design version
                           
                           $col_num = 0;
                           // if($key=="children") die(var_export($auth_links,true));
                           foreach($auth_links as $k => $other_link)
                           {
                                $xs_width = $other_link["XS-WIDTH"];
                                if(!$xs_width) $xs_width = round(strlen($other_link["TITLE"]/16));
                                if($xs_width<1) $xs_width = 1;
                                $o_url = $other_link["URL"];
                                $o_tit = $other_link["TITLE"];
                                if(!$other_link["COLOR"]) $other_link["COLOR"] = $next_color_arr[$last_color];
                                $last_color = $other_link["COLOR"];
                                $btns[$key] .= "<a class='col-xs-$xs_width' href='$o_url'><span class='${last_color}btn submit-btn fright'>$o_tit</span></a>";
                                $col_num++;
                          }
                           
                  }
                  
                  $pbm_local_arr = $obj->getPublicMethodsForUser($objme,$key_mod);

                  $pbm_count = count($pbm_local_arr);
                  
                  if($pbm_count==1) $col_xs = 12;
                  elseif($pbm_count==2) $col_xs = 6;
                  elseif($pbm_count==3) $col_xs = 4;
                  else $col_xs = 3;
                  
                  foreach($pbm_local_arr as $pbm_code => $pbm_item)
                  {
                       $col_xs_pbm = $pbm_item["HZM-SIZE"];
                       if(!$col_xs_pbm) $col_xs_pbm = $col_xs;
                       
                       $btns[$key] .=  "<div class='btns-$key col-xs-$col_xs_pbm'>";  
                       $btns[$key] .= showPublicMethodButton($obj, $pbm_code, $pbm_item, $lang);
                       $btns[$key] .= "</div>"; 
                  }

                  
               }
	}
}

//if($obj->test_rafik)  die("rafik 500");

?>
<script src='../lib/js/jquery.elevatezoom.js'></script>

<?
$idobj = $obj->getId();

$titre_display = $obj->translate("FILE",$lang,true)." > ".$obj->singleTranslation($lang)." > ".$obj->getShortDisplay($lang);

if($lang=="ar") $titre_display = AfwStringHelper::truncateArabicJomla($titre_display, 115, $etc="...");

if(!$file_box_css_class) $file_box_css_class = "filebox";
?>

<form method="post" action="main.php">
<input type="hidden" name="Main_Page" id="Main_Page" value="afw_mode_display.php"/>
<input type="hidden" name="currmod"  value="<?=$currmod?>"/>
<input type="hidden" name="id"     value="<?=$idobj?>"/>
<input type="hidden" name="cl"     value="<?=$cl?>"/>
<input type="hidden" name="pbmon"     value="1"/>
<?
$rest_params = (isset($currmod)) ? "&currmod=$currmod":"";
$all_params = "&popup=$popup&cl=$cl&id=$idobj".$rest_params;

//list($oper_label,$bf_id) = $obj->userCan($objme, $uri_module,"edit");

$can = $objme->iCanDoOperationOnObjClass($obj,"edit");
list($canme, $can_t_me_reason) = $obj->userCanEditMe($objme);

if(($can) and ($canme) and ($page_editable))
{
      $btn_edit_html = "<a href=\"main.php?Main_Page=afw_mode_edit.php$all_params\"><span class=\"submit-btn dark_editbtn\">تعديل</span></a>";

}
else
{
      $cl_edit_lock = "";
      if($can) $cl_edit_lock .= "edit-can ";
      else $cl_edit_lock .= "edit-cant ";
      
      if($canme) $cl_edit_lock .= "edit-canme ";
      else $cl_edit_lock .= "edit-cantme ";
      
      if($page_editable) $cl_edit_lock .= "page-editable ";
      else $cl_edit_lock .= "page-edit-locked ";
      
      
      $btn_edit_html = "<span hint='$can_t_me_reason' class=\"submit-btn lockbtn $cl_edit_lock\">&nbsp</span>";
}
?>
<div class="<?=$file_box_css_class?> card">

<div class="panel-heading">
	<h3 class="panel-title col-xs-9"><span><?php echo "$titre_display"?></span></h3>
	<h3 class="panel-edit-btn col-xs-3 text-left btn_in_header"><?=$btn_edit_html?></h3>
</div>

<div class="panel-body">

<?
$modeOfPage = "display";
include("hzm_tabs_bloc_header.php");

$styleStepWidth = $obj->styleStep[$obj->currentStep]["width"];
if($styleStepWidth) $style = " style='width: ${styleStepWidth} !important;'";
?>
<div class="hzm_form_panel hzm_step_body_<?=$clStep."_".$obj->currentStep?>">
<div class="paragraph_hzm_ar minibox_hzm_very_large">
<div class="hzm_grid"> 
<?
	$firstTr = true;
        $newTr = true;
        $fgroup = "";
        foreach($data as $key => $value)
        {
            
            if($tr_class_display=="tr_obj") $tr_class_display = "tr_obj2"; else $tr_class_display = "tr_obj";
            
            $structure = AfwStructureHelper::getStructureOf($obj,$key);
            
            $colspan = "";
            $css_class = "";
            
             
            $new_fgroup = $structure["FGROUP"];
            
            $mfk_show_sep = $structure["LIST_SEPARATOR"];
            if(!$mfk_show_sep) $mfk_show_sep = $structure["MFK-SHOW-SEPARATOR"];
            
            $question = "";
            if(!$question)
            {
                    if(!$structure["QUESTION"]) $structure["QUESTION"] = $key."_question";
                    $trans_code = $structure["QUESTION"]; 
                    $question  = trim($obj->translateMessage($trans_code));
                    if($question==$trans_code) $question = "";
            }
            
            $help = "";
            // No Help in mode display only mode edit use tooltip instead
            /*
            if(!$help)
            {
                    if(!$structure["HELP"]) $structure["HELP"] = $key."_help";
                    $trans_code = $structure["HELP"]; 
                    $help  = trim($obj->translateMessage($trans_code));
                    if($help==$trans_code) $help  = trim($obj->translate($trans_code,$lang));
                    if($help==$trans_code) $help = "";
            }
            
            if(!$help)
            {
                    $trans_code = $key."_help_text"; 
                    $help  = trim($obj->translateMessage($trans_code));
                    if($help==$trans_code) $help  = trim($obj->translate($trans_code,$lang));
                    if($help==$trans_code)
                    {
                        //if($key=="categ") throw new AfwRuntime Exception("can not translate $trans_code");
                        $help = "";
                    } 
            }
            */
            if(!$structure["HINT"]) $structure["HINT"] = $key."_hint";
            $trans_code = $structure["HINT"]; 
            $hint  = trim($obj->translateMessage($trans_code));
            if($hint==$trans_code) $hint = "";
            
            if(!$structure["TOOLTIP"]) $structure["TOOLTIP"] = $key."_tooltip";
            $trans_code = $structure["TOOLTIP"]; 
            $tooltip  = trim($obj->translateMessage($trans_code));
            if($tooltip==$trans_code) $tooltip  = trim($obj->translate($trans_code,$lang));
            if($tooltip==$trans_code) $tooltip = "";
            
            
            $unit  = trim($obj->translateMessage($structure["UNIT"]));
            $title_after  = trim($obj->translateMessage($structure["TITLE_AFTER"]));
            
            
            if(!$mfk_show_sep) $mfk_show_sep = "<br>\n";
            if(($new_fgroup) and ($fgroup != $new_fgroup))
            {
                 $fgroup = $new_fgroup;
                 
                 if($fgroup=="tech_fields")
                 {
                     $collapse_status = "collapse";
                     $collapsed_status = " collapsed";
                 } 
                 else
                 {
                     $collapse_status = "collapse in";
                     $collapsed_status = "";
                 } 
                 
                 $new_fgroup_tr = $obj->translate($new_fgroup,$lang);
                 // -- BTSRAP rafik : if(!$newTr) echo "<th></th><td></td></tr>";
                 //  echo "\n<tr><th class='fgroup_header' colspan='4'>$new_fgroup_tr</th></tr>\n";
                 // -- BTSRAP rafik : echo "\n<tr><td colspan='4'><h5 class='greentitle'><i></i>$new_fgroup_tr</h5></td></tr>\n";
                 if($newDivGroup) echo "\n</div>\n";
                 echo "\n<div class='hzm_attribute hzm_wd4'>
                                 <div class='hzm_label greentitle expand$collapsed_status' data-toggle='collapse' data-target='#group_$fgroup'><i></i>$new_fgroup_tr </div>
                         </div><div id='group_$fgroup' class='hzm_wd4 $collapse_status' aria-expanded='true' style=''>\n";
                 $newDivGroup = true;
            }
            
            if($structure["CSS-DISPLAY"])
            {
                 $css_class = " class='".$structure["CSS-DISPLAY"]."'";
            }
            
            if($structure["COLSPAN"])
            {
                $colspan = $structure["COLSPAN"];
            }
            
            if($structure["HZM-WIDTH"])
            {
                $colspan = $structure["HZM-WIDTH"];
            }
            
            if(!$colspan)
            {
                    if($structure["SIZE"]>0)
                    {
                        $colspan = round($structure["SIZE"]/32);
                    }
                    
                    if($structure["CATEGORY"]=="ITEMS")
                    {
                        $colspan = 4;
                    }
                    
                    if($structure["PRE"])
                    {
                        $colspan = 4;
                    }
                    
                    if($structure["SIZE"]=="AREA")
                    {
                        $colspan = 4;
                    }
                    
                    
                    
                    if($structure["TYPE"]=="DATE")
                    {
                        if(($structure["FORMAT"]=="CONVERT_NASRANI_2LINES") or ($structure["FORMAT"]=="CONVERT_NASRANI_VERY_SIMPLE")or ($structure["FORMAT"]=="HIJRI_UNIT"))
                            $colspan = 1;
                        else
                            $colspan = 2;
                    }
                    
                    
                    
                    
            }
            
            if($colspan>4) $colspan = 4;
            
            //if((!$firstTr) and ($newTr)) echo "</tr>";
            // -- BTSRAP rafik : if((!$firstTr) and (($structure["NEW-TR"]) or $newTr)) echo "</tr>";
            
            if($newTr) 
            {
                // -- BTSRAP rafik : echo "<tr>";
                $firstTr =false;
                if($structure["CATEGORY"]=="ITEMS")
                    $newTr = true;
                else
                    $newTr = false;
            }
            else
            {
                $newTr = true;
            }
            $newTr = true;
            if(!$structure["NO-LABEL"])		
            {
                 $attr_label = $obj->getAttributeLabel($key, $lang);
            }
            else
            {
                 $attr_label = "";
            }
            
            
            $attr_html = "";
            
            if($obj->attributeIsApplicable($key))
            {
                        if(!is_array($value))
                        {
				$attr_html .= ($link[$key])? '<a id="b'.$k.'" href="'.$link[$key].'">' : "";
				$attr_html .= $value;
				$attr_html .= ($link[$key])? '</a>' : "";
		        }
                        else
                        {
                		$first_k = true;
                                foreach ($value as $k => $v) 
                                {
                			if(!$first_k) $attr_html .= $mfk_show_sep;
                                        $attr_html .= ($link[$key][$k])? '<a id="a'.$k.'" href="'.$link[$key][$k].'">' : "";
                			$attr_html .= $v;
                			$attr_html .= ($link[$key][$k])? '</a>' : "";
                                        $first_k = false;
                		}
                                
                        }
                        
                        if($title_after) $attr_html .= " <div class='fright'>".$title_after."</div>";
                        if($help)     $attr_html .= '<div class="fright" style="margin-left: 6px;margin-right: 6px; background-color:#4792d0; border-radius: 12px;">
                                                <img data-toggle="tooltip" data-placement="top" width="24px" height="24px" title="'.htmlentities($help).'" src="../lib/images/help.png" />
                                                
                                        </div>';
                        
                        //" <div class='div_help fright' style='padding-left: 6px;padding-right: 6px;'>$help </div>";
                        /*
                        if($help)     $attr_html .= "<div class='tooltip'><img src='../lib/images/help.png' />
  <div class='tooltiptext'>$question
               <br><span class='tooltipbody'>$help</span><br>
  </div>
</div>";*/
                        if($hint)     $attr_html .= " <div class='fright'><h4>$hint </h4></div>";
                        if($tooltip)  $attr_html .= ' <div class="fright" style="padding-left: 6px;padding-right: 6px;"><img data-toggle="tooltip" data-placement="top" title="'.htmlentities($tooltip).'" src="../lib/images/tooltip.png" /></div>';
                        if($obj_errors[$key])  $attr_html .= "<div class='data_error'>".$obj_errors[$key]."</div>"; //
                        

                        //if($unit)     echo "<div class='fright'>(الوحدة = $unit )</div>";
                                    
                        // echo "btns($key):<br>";
                        $attr_html .= $btns[$key];
                        // echo "end-btns($key):<br>";
                        if(!$attr_html) $attr_html = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
             }
             else
             {
                 list($icon,$textReason, $wd, $hg) = $obj->whyAttributeIsNotApplicable($key);
                 if(!$wd) $wd = 20;
                 if(!$hg) $hg = 20;
                 $attr_html .= "<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='$textReason'  width='$wd' heigth='$hg'>";
             }
             
             
             if(!$colspan) $colspan = 1;   
		  
                
?>
	   <div class="hzm_attribute hzm_wd<?=$colspan?>">
              <label class="hzm_label hzm_label_<?=$key?>"><?=$attr_label?></label>
              <div class="hzm_data hzm_data_<?=$key?>"><?=$attr_html?></div>
           </div>
<?php
               
        }
        if($newDivGroup) echo "\n</div>\n";
?>
	</div>
<?php
        
        $next_step = AfwFrameworkHelper::findNextApplicableStep($obj, $obj->currentStep);
        if($next_step>0)
        {
                $can_go = $obj->canGoToNextStep($next_step);
                
                if($obj->editByStep and ($obj->currentStep < $obj->editNbSteps) and $can_go)
                {
           
?>
        <center><a href="main.php?Main_Page=afw_mode_display.php<?=$all_params?>&currstep=<?=$next_step?>">
          <span class="graybtn dnextbtn"><?=$obj->getNextTabButtonLabel($next_step, $lang)?></span><br><br>
        </a></center>
<?php
                }
        
        }
?>
</div>
<?php

    
    if($obj->hideToolbar)
    {
            //list($oper_label,$bf_id) = $obj->userCan($objme, $uri_module,"edit");
            $can = $objme->iCanDoOperationOnObjClass($obj,"edit");
            
            list($canme, $can_t_me_reason) = $obj->userCanEditMe($objme);
            if(($can) and ($canme))
            {
                    $update_btn_link = array();
                    
                    $title = "تعديل";
                    $update_btn_link["URL"] = "main.php?Main_Page=afw_mode_edit.php$all_params";
                    $update_btn_link["TITLE"] = $title;
                    $update_btn_link["COLOR"] = "blue";
                    $update_btn_link["UGROUPS"] = array();
            }
            else $update_btn_link = null;
    }                
    else
    {
        $tool_bar_arr = array();
        
        $new_record = $obj->translate("NEW",$lang, true);
        if($can and $obj->successiveInserts)
        {
            $rest_params = ((isset($currmod)) ? "&currmod=$currmod":"").((isset($sub_pack)) ? "&spk=$sub_pack":"");
            $all_params = "&popup=$popup&cl=$cl".$rest_params;
            
            $tool_bar_arr[] = array('href' => "main.php?Main_Page=afw_mode_edit.php$all_params", 'css_class'=>$class_inputNew, 'label' => $new_record);    

        }
        else
        {
           //
        }
        
        $can = $objme->iCanDoOperationOnObjClass($obj,"search");
        $oper_label = $obj->translate("SEARCH",$lang, true);
        if($can and $obj->enableOtherSearch)
        {
            $rest_params = ((isset($currmod)) ? "&currmod=$currmod":"").((isset($sub_pack)) ? "&spk=$sub_pack":"");
            $all_params = "&cl=$cl".$rest_params;
            
            $tool_bar_arr[] = array('href' => "main.php?Main_Page=afw_mode_search.php$all_params", 'css_class' =>"yellowbtn submit-btn fright", 'label' => $oper_label);                     
        }
        else
        {
           //
        }


        if(count($tool_bar_arr)>0)
        {
?>        
              <div class="afw_toolbar">
		<? 
                   foreach($tool_bar_arr as $tool_bar_item)
                   {
                       $href = $tool_bar_item["href"];
                       $css_class = $tool_bar_item["css_class"];
                       $oper_label = $tool_bar_item["label"];
                       echo "<a href='$href'><span class='$css_class'>$oper_label</span></a>";
                   }                               
                ?>
                </div>
<?
        }
      }
?>        
</div>
<?
    include("hzm_tabs_bloc_footer.php");
    
    $other_links = $obj->getOtherLinksForUser("display", $objme);
    
    $auth_links = array();
    //die("update_btn_link=".var_export($update_btn_link,true));
    if($obj->hideToolbar and $update_btn_link) $auth_links[] = $update_btn_link;
    
    foreach($other_links as $k => $other_link)
    {
        // if OTHER LINKS CHECK RIGHTS
        $o_mode = $other_link["MODE"];
        $o_module = $other_link["MODULE"];
        $o_class = $other_link["CLASS"];
        
        if($o_module and $o_mode and $o_class)
        {
            $myObj = new $o_class();
            $can_see_menu = $objme->iCanDoOperationOnObjClass($myObj,$o_mode);
            // list($can_see_menu,$bf_id, $reason) = $myObj->userCan($objme, $o_module, $o_mode);    
        }
        else $can_see_menu = true;
       
        if($can_see_menu)
        {
             $auth_links[] = $other_link;
        }
        
   }
   // die("auth_links=".var_export($auth_links,true)); 
   
   if(count($auth_links)>0)
   {             
           if(!$obj->noBtnGroup)
           {
                   $btns_all = "<br>";
                   // use bootstrap design version if many links
                   $btns_all .= "<div class='btn-group' style='width: 500px !important;'>";
                   $btns_all .= "  <button type='button' class='btn btn-primary'>إستمارات أخرى ذات صلة</button>";
                   $btns_all .= "  <button type='button' class='btn-primary dropdown-toggle' data-toggle='dropdown'>";
                   $btns_all .= "    <span class='caret'></span>";
                   $btns_all .= "  </button>";
                   $btns_all .= "  <ul class='dropdown-menu' role='menu'>";
                   foreach($auth_links as $k => $other_link)
                   {
                        $o_url = $other_link["URL"];
                        $o_tit = $other_link["TITLE"];
                        $btns_all .= "    <li><a href='$o_url'>$o_tit</a></li>";
                   }   
                   $btns_all .= "  </ul>";
                   $btns_all .= "</div>";
                   echo $btns_all;
           }
           else
           {
        ?>
        <table class="table_obj">        
                <tr class="table_obj">
                <?
                   
                   $col_num = 0;
                   foreach($auth_links as $k => $other_link)
                   {
                      if(!$other_link["COLOR"]) $other_link["COLOR"] = "gray";
                      if($col_num == 3)    
                      {
                ?>
                </tr>
                <tr class="table_obj">
                <?
                          $col_num = 0;  
                      }
                ?>
                    <td>
                         <a href="<?=$other_link["URL"]?>">
                            <span class="<?=$other_link["COLOR"]?>btn submit-btn fright"><?=$other_link["TITLE"]?></span>
                         </a>
                    </td>
                <?
                     $col_num++;
                   }
                ?>        
        	</tr>
        </table>
        <?
            }
    }
    
    $pbm_arr = $obj->getPublicMethodsForUser($objme,"display");
    if(count($pbm_arr) >0)
    {
?>
<div style="float: left;">
<p>
        <?
           
           foreach($pbm_arr as $pbm_code => $pbm_item)
           {
               echo showPublicMethodButton($obj, $pbm_code, $pbm_item, $lang);
           }
        ?>        
</p>
</div>
<?
    }
    
    
?>
</div>
</div>

</form>
<?
function showPublicMethodButton($obj, $pbm_code, $pbm_item, $lang)
{
        global $next_color_arr;
        $objme = AfwSession::getUserConnected();

        $show_pbm = true;
        if($obj->editByStep and $pbm_item["STEP"] and ($obj->currentStep != $pbm_item["STEP"])) 
        {
            if((!$pbm_item["STEP2"]) or ($obj->currentStep != $pbm_item["STEP2"]))
            {
                if((!$pbm_item["STEPS"]) or (!in_array($obj->currentStep, $pbm_item["STEPS"])))
                {
                    $show_pbm = false;
                }                
            }
            
        } 
        if($show_pbm)
        {
                // if(!$pbm_item["COLOR"]) $pbm_item["COLOR"] = $next_color_arr[$last_color];
                $last_color = $pbm_item["COLOR"];
                $method_name = $pbm_item["METHOD"];
                $pbm_item_translation = $obj->translate($method_name,$lang);
                $pbm_item_help = $pbm_item["LABEL_".strtoupper($lang)];
                if(($pbm_item_translation==$method_name) or (!$pbm_item_translation)) $pbm_item_translation = $pbm_item_help;
                if($objme and $objme->isSuperAdmin()) $method_name_help = $method_name;
        
                return "<input type=\"submit\" name=\"submit-$pbm_code\" title=\"$method_name_help\" id=\"submit-$pbm_code\" class=\"${last_color}btn submit-btn fright method-$method_name\" value=\"&nbsp;$pbm_item_translation&nbsp;\" style=\"margin-right: 5px;\" onclick=\"return open_loading();\" />";
        }

}

?>