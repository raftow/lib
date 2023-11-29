<?php

require_once(dirname(__FILE__)."/../../../external/db.php");


require_once("afw_config.php");
require_once("afw_rights.php");
require_once("afw_search_motor.php");

if(!$objme) $objme = AfwSession::getUserConnected();
if(!$objme) 
{
    AfwSession::pushError("الرجاء تسجيل الدخول أولا");
    header("Location: login.php");
    exit();
}

if(!$currmod)
{
        $currmod = AfwUrlManager::currentWebModule();
}

$out_scr = "";

$session_previous_search = AfwSession::getSessionVar("search-$cl");

if(($session_previous_search) and (!$datatable_on)) 
{
  foreach($session_previous_search as $criteria_arr)
  {
          $nom_col = $criteria_arr["col"];
          $_POST[$nom_col] = $criteria_arr["val"];
          ${$nom_col} = $criteria_arr["val"];
          $_POST["oper_".$nom_col] = $criteria_arr["oper"];
          ${"oper_".$nom_col} = $criteria_arr["oper"];          
  }
  
  $datatable_on = 1;
}

if(!$currmod)
{
    $currmod = $uri_module;
}

$my_class = new $cl();
$my_contextCols = $my_class->getContextCols();
$take_context = true;
$force_context = true;
foreach($my_contextCols as $ccol)
{
       $ccol = trim($ccol);
       $val_col_context = $objme->getContextValue($currmod, $ccol);
       if($_POST[$ccol] and ($val_col_context!=$_POST[$ccol])) $take_context = false; 
}

if($take_context or $force_context)
{
        foreach($my_contextCols as $ccol)
        {
               $ccol = trim($ccol);
               $val_col_context = $objme->getContextValue($currmod, $ccol);
               if(!$_POST[$ccol]) $_POST[$ccol] = $val_col_context;
               ${$ccol} = $_POST[$ccol];
               $_POST["oper_".$ccol] = "=";
               ${"oper_".$ccol} = "=";     
        
        }
}

$actions_tpl_arr = AfwUmsPagHelper::getAllActions($my_class);



// $my_class->debuggObj($_POST);
if($datatable_on) {
	include 'afw_handle_default_search.php';
        $collapse_show = "";
}
else $collapse_show = "show";
     
	

$newo = $my_class->QEDIT_MODE_NEW_OBJECTS_AFTER_SEARCH;
if($newo==0) $newo = $my_class->QEDIT_MODE_NEW_OBJECTS_DEFAULT_NUMBER;
       
//
              
$can = $objme->iCanDoOperationOnObjClass($my_class,"search");
if(!$can)
{
      header("Location: lib/afw/modes/afw_denied_access_page.php?CL=$cl&MODE=search&bf=$bf_id&rsn=$reason");      
      exit();
}


if(!$lang) $lang = 'ar';



//debug
////AFWObject::setDebugg(true);
//AFWDebugg::initialiser($START_TREE.$TMP_DIR,"afw-debugg".$g_array_user["PAGE_NUMBER"].".txt");

// **@todo $hide_cr = AFWObject::traduire('HIDE CRITEREA',$lang,false);
// **@todo $show_cr = AFWObject::traduire('SHOW CRITEREA',$lang,false);

$plural_obj_name =  $my_class->transClassPlural($lang);
$single_obj_name =  $my_class->transClassSingle($lang);

$out_scr_btns = "";
if($datatable_on) 
{
	$fixm_sel_arr = array();
        
        $fixm_arr = explode(",",$fixmlist);
        
        foreach($fixm_arr as $fixm_item)
        {
              $fixm_item_arr = explode("=",$fixm_item);
              $fixm_sel_arr["sel_".$fixm_item_arr[0]] = $fixm_item_arr[1];  
        }
        
        
        $out_scr_btns .= '<center>';
        $out_scr_btns .= '<table class="paddedtable" cellspacing="3" cellpadding="1"><tr><td></td>';

        if(($newo>0) and (!$my_class->OwnedBy))
        {
                $out_scr_btns .= '<td>';        
                $out_scr_btns .= '<form name="qeditForm" id="qeditForm" method="post" action="'."main.php".'">';
                $out_scr_btns .= '<input type="hidden" name="Main_Page" value="afw_mode_qedit.php"/>';
                $out_scr_btns .= '<input type="hidden" name="cl" value="'.$cl.'"/>';
                $out_scr_btns .= '<input type="hidden" name="currmod" value="'.$currmod.'"/>';
                $out_scr_btns .= '<table cellspacing="3" cellpadding="1"><tr><td><input type="submit" class="greenbtn btn fright" name="submit"  id="submit-form" value="'.$qedit_new." ".$plural_obj_name.'" /></td></tr></table>';
                $out_scr_btns .= '<input type="hidden" size="3" name="newo" value="'.$newo.'"/>';
                $out_scr_btns .= '<input type="hidden" name="limit" value=""/>';
                $out_scr_btns .= '<input type="hidden" name="fixm" value="'.$fixmlist.'"/>';
                foreach($fixm_sel_arr as $fixm_sel_item => $fixm_sel_val)
                {
                     $out_scr_btns .= '<input type="hidden" name="'.$fixm_sel_item.'" value="'.$fixm_sel_val.'"/>';
                }
                $out_scr_btns .= '<input type="hidden" name="fixmtit" value="'.$qedit_new." ".$plural_obj_name.'"/>';
                $out_scr_btns .= '<input type="hidden" name="fixmdisable" value="1"/>';
                $out_scr_btns .= '</form>';
                $out_scr_btns .= '</td>';
        }
           
        if(($cl) and (!$my_class->OwnedBy))
        {
                $out_scr_btns .= '<td>'; 
                $out_scr_btns .= '<form name="editForm" id="editForm" method="post" action="'."main.php".'">';
                $out_scr_btns .= '<input type="hidden" name="Main_Page" value="afw_mode_edit.php"/>';
                $out_scr_btns .= '<input type="hidden" name="cl" value="'.$cl.'"/>';
                $out_scr_btns .= '<input type="hidden" name="currmod" value="'.$currmod.'"/>';
                $out_scr_btns .= '<table cellspacing="3" cellpadding="1"><tr><td><input type="submit" class="bluebtn btn fright" name="submit"  id="submit-form" value="'.$new_instance." ".$single_obj_name.'" /><input type="hidden" size="3" name="newo" value="'.$newo.'"/></td></tr></table>';
                $out_scr_btns .= '<input type="hidden" name="limit" value=""/>';
                $out_scr_btns .= '<input type="hidden" name="fixm" value="'.$fixmlist.'"/>';
                foreach($fixm_sel_arr as $fixm_sel_item => $fixm_sel_val)
                {
                     $out_scr_btns .= '<input type="hidden" name="'.$fixm_sel_item.'" value="'.$fixm_sel_val.'"/>';
                }
                $out_scr_btns .= '</form>';
                $out_scr_btns .= '</td>'; 
        }
                 
        if($ids and ($ids_count<101) and (!$my_class->OwnedBy))
        {
                $out_scr_btns .= '<td>'; 
                $out_scr_btns .= '<form name="qedit_updateForm" id="qedit_updateForm" method="post" action="'."main.php".'">';
                $out_scr_btns .= '<input type="hidden" name="Main_Page" value="afw_mode_qedit.php"/>';
                $out_scr_btns .= '<input type="hidden" name="cl" value="'.$cl.'"/>';
                $out_scr_btns .= '<input type="hidden" name="currmod" value="'.$currmod.'"/>';
                        $out_scr_btns .= '<table cellspacing="3" cellpadding="1">
                               <tr>
                                  <td>
                                     <input type="submit" class="yellowbtn btn fright" name="submit"  id="submit-form" value="'.$qedit_update.'" />
                                     <input type="hidden" size="3" name="newo" value="3"/>
                                  </td>
                               </tr>
                             </table>';
                $out_scr_btns .= '<input type="hidden" name="limit" value="'.$data_count.'"/>';
                $out_scr_btns .= '<input type="hidden" name="ids" value="'.$ids.'"/>';
                $out_scr_btns .= '<input type="hidden" name="fixm" value="'.$fixmlist.'"/>';
                foreach($fixm_sel_arr as $fixm_sel_item => $fixm_sel_val)
                {
                     $out_scr_btns .= '<input type="hidden" name="'.$fixm_sel_item.'" value="'.$fixm_sel_val.'"/>';
                }
                $out_scr_btns .= '<input type="hidden" name="fixmdisable" value="1"/>';
                
                $out_scr_btns .= '</form>';
                $out_scr_btns .= '</td>';          
        }
        $out_scr_btns .= '</tr></table>';
        $out_scr_btns .= '</center>';
        $out_scr_btns .= '<br><br>';
}



$click_to_edit_search = $my_class->translate('CLICK-TO-EDIT-SEARCH',$lang,true);
$title_mode_search = $my_class->translate('SEARCH CRITERIA',$lang,true)." ".$my_class->translate($my_class->getTableName(),$lang);
$page_title = $my_class->translate('SEARCH',$lang,true)." ".$plural_obj_name;
if(count($cond_phrase_arr))
{
   $critirea_of_search = "<div class='help_info'>".implode("<br>\n",$cond_phrase_arr)."<br><a data-toggle='collapse' class='collaps' href='#collapse1'>$click_to_edit_search</a></div>";
}
else
{
   $critirea_of_search = ""; //<div class='help_info'><a data-toggle='collapse' class='collaps' href='#collapse1'>$title_mode_search</a></div>";
}



/*
$out_scr .= "<center>";
$out_scr .= "<div align=\"center\" class=\"$class_titre\" style=\"width:80%;font-size:32px\">".$title_mode_search."</div>";
$out_scr .= "</center><br>";
*/
$out_scr .= "<div class='container'>
  <div class='panel-group'>
    <div class='panel panel-default'>
      <div class='panel-heading'>
        <div class='greentitle'><i></i>$page_title</div>
        $critirea_of_search
      </div>
      <div id='collapse1' class='panel-collapse in collapse $collapse_show'>
        <div class='search-panel'>";

$list_of_ret_cols_all = array();
$list_of_ret_cols_default = array();

$class_db_structure = $my_class->getMyDbStructure();

foreach($class_db_structure as $nom_col => $desc)
{
    if($my_class->keyIsToDisplayForUser($nom_col, $objme))
    {
	if(!$desc["NO-RETRIEVE"])
        {
            $list_of_ret_cols_all[$nom_col] = $my_class->translate($nom_col,$lang);
        }
        
        if($desc["RETRIEVE"])
        {
             $list_of_ret_cols_default[] =  $nom_col;
        }
    }   
}

if(!$_POST["ms_ret_cols"]) $_POST["ms_ret_cols"] = $list_of_ret_cols_default;

ob_start();        
select(
        						$list_of_ret_cols_all,
        						( (isset($_POST["ms_ret_cols"])) ? $_POST["ms_ret_cols"]: array()  ),
        						array(
        							"class" => "search_comm_select $class_inputSelect_multi_big",
        							"name"  => "ms_ret_cols[]",
        							"size"  => 5,
        							"multi" => true
        						),
        						"asc",
        						false      
        					);
                                                
$input_select_list_of_ret_cols  = ob_get_clean();

// $out_scr .= '<div align="center" class="aaa" style="width:81%;">';

$out_scr .= '<form name="searchForm" id="searchForm" method="post" action="'."main.php".'">';

$out_scr .= AfwShowHelper::showObject($my_class,"HTML", "afw_template_default_search.php");
$out_scr .= '<table width="100%"><tr><td cellpadding="8px">';
$out_scr .= '<input type="hidden" name="datatable_on"  value="1"/>';
//$out_scr .= '<input type="hidden" name="file_obj"  value="'.$file_obj.'"/>';
$out_scr .= '<input type="hidden" name="cl" value="'.$cl.'"/>';
$out_scr .= '<input type="hidden" name="currmod" value="'.$currmod.'"/>';
$out_scr .= '<input type="hidden" name="limite"    value="0"/>' ;

$out_scr .= '<input type="hidden" name="Main_Page" value="afw_mode_search.php"/>';
$out_scr .= '<div class="panel-heading">
        <h4 class="panel-title">'.$my_class->translate('RETRIEVE-RESULT-ACTIONS',$lang,true).'
          
        </h4>
      </div>';

$out_scr .= '<table  class="search-grid">';
$out_scr .= '<tr class="altitem"><td width="15px">&nbsp;</td><td style="padding-top:22px">'.$my_class->translate('EXCEL-EXPORT',$lang,true).'</td><td>&nbsp;</td>
<td style="width: 2%;padding-left: 8px;">
<input type="checkbox" value="1"  id="genere_xls" name="genere_xls">
</td>
';
/*
<div class="slideThree">
	<input type="checkbox" value="1" id="genere_xls_0" name="genere_xls_0" />
	<label for="genere_xls_0"></label>
</div>
*/

$out_scr .= '</tr>';
$out_scr .= '<tr class="item"><td width="15px">&nbsp;</td><td style="padding-top:22px">'.$my_class->translate('RETRIEVE-COLS',$lang,true).'</td><td>&nbsp;</td>';
$out_scr .= '<td style="padding-top: 10px;">'.$input_select_list_of_ret_cols.'</td>';
$out_scr .= '</tr>';

$out_scr .= '<tr class="altitem"><td width="15px">&nbsp;</td><td style="padding-top:9px"><input type="submit" class="bluebtn smallbtn fright" name="submit"  id="submit-form"   value="'.$my_class->translate('SUBMIT-SEARCH',$lang,true).' " /></td><td>&nbsp;</td><td>&nbsp;</td></tr>';
$out_scr .= '</table>';

$out_scr .= '</td></tr></table>';
// $out_scr .= '</div>';
$out_scr .= "</center>";
$out_scr .= '</form>';

$out_scr .= "</div>
      </div>
    </div>
  </div>
</div>


";  

if($datatable_on) {
        $out_scr .= $search_result_html;
        $out_scr .= $out_scr_btns;
}

// $out_scr .= '</div>'; 

	






?>