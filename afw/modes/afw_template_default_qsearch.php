<?php
require_once(dirname(__FILE__)."/../../../external/db.php");
$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}
require_once 'afw_rights.php';
global  $TMP_DIR,$cl,$pk,$spk,$TMP_ROOT, $lang, $class_table, $class_tr1, $class_tr2, $pct_tab_search_criteria, $class_tr1_sel, $class_tr2_sel ;
$objme = AfwSession::getUserConnected();
if(!$lang) $lang = 'ar';

//echo "langue = $lang <br>";

//$lab_id = AfwLanguageHelper::tarjem("id",$lang,true);
define("LIMIT_INPUT_SELECT", 30);
$data = array();

if(!$obj)
{
        if(isset($class_obj))
        {
        	require $file_obj;
        	$obj = new $class_obj();
        }
        else die("object class not defined");
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

$total_qsize = 0;
$max_total_qsize = 99;//$obj->max_total_qsize;
if(!$max_total_qsize) $max_total_qsize = 10; 

foreach($class_db_structure as $nom_col => $desc)
{
        if($obj->isQSearchCol($nom_col, $desc))
        {
		if($total_qsize<$max_total_qsize)
                {
                        $filled_val = $_POST[$nom_col];
                        
                        $data[$nom_col]["filled_criteria"] = ($filled_val);
                        
                        $data[$nom_col]["trad"]  = $obj->translate($nom_col,$lang);
        
                        $data[$nom_col]["qsize"] = $desc["QSIZE"];
                        if(!$data[$nom_col]["qsize"]) $data[$nom_col]["qsize"] = 3;
                        $total_qsize += $data[$nom_col]["qsize"];
        
                        $desc["SEARCH-BY-ONE"] = true;
        
                        
        		ob_start();
        		type_input($nom_col, $desc, $obj, $data[$nom_col]["filled_criteria"]);
        		$data[$nom_col]["input"] = ob_get_clean();
                        $oper_qsearch = $desc["QSEARCH_OPER"];
                        if(!$oper_qsearch)
                        {
                                if(($desc["TYPE"]=="DATE") or ($desc["TYPE"]=="GDAT")) $oper_qsearch = "between";
                                else $oper_qsearch = "=";
                        } 
                        ob_start();
        		hidden_input("oper_".$nom_col, null, $oper_qsearch, null);
        		$data[$nom_col]["oper"] = ob_get_clean();
                }
                
                //if($nom_col=="id_domain")  $obj->_error("data[$nom_col] = ".var_export($data[$nom_col],true));

	}
        //elseif($nom_col=="id_domain")  $obj->_error("desc [$nom_col] = ".var_export($desc,true));
}

?>

 


<? 
   if($obj->qsearchByTextEnabled())
   {
        $qs_by_txt_qsize = 99 - $total_qsize;
        if($qs_by_txt_qsize>3) $qs_by_txt_qsize = 3;
        if($qs_by_txt_qsize>0)
        {
                $tr_obj=$class_tr1;
                $qsearch_by_text = $_POST["qsearch_by_text"];
                $desc_qsearch_by_text = array('TYPE'=>'TEXT', 'SIZE'=>64, 'UTF8'=>true, 'PLACEHOLDER'=>"any_word");
                ob_start();
                type_input("qsearch_by_text", $desc_qsearch_by_text, $obj, $qsearch_by_text);
                $trad_qsearch_by_text_input = ob_get_clean();
                 
                $trad_qsearch_by_text = $obj->translate("qsearch_by_text",$lang);
                $trad_qsearch_by_help = $obj->translate("qsearch_by_help",$lang);
                $qsearch_by_text_cols = $obj->getAllTextSearchableCols();
                $translated_text_searchable_cols_arr = $obj->translateCols($qsearch_by_text_cols,$lang, true); 
                
                $translated_text_searchable_cols_txt = $trad_qsearch_by_help." : ".implode("، ", $translated_text_searchable_cols_arr);
        }
   }
   else $qsearch_by_text_cols = [];
   
   if(true)
   {        
?>   
	<? 
             $numFiltre = 0;
             $xFiltre = 0;
             $colFiltre = 0;
             $totqsize = 0;
             foreach($data as $col => $info)
             {
                if($info["trad"])
                { 
                        $qsize = $info["qsize"];
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
                        
                ?>
        		<div class="col-md-<?=$qsize." col-filter-".$col ?>">
                                <div class="form-group">
                                        <label><?php echo $info["trad"]; ?>
                                        </label>
                                        <?php echo $info["input"].$info["oper"];?>
                                </div>
                        </div>
        	<? 
                        $need_to_close_div = true;// false;
                        $totqsize += $qsize;
                        if($totqsize>=12)
                        {
                            $totqsize = 0;
                            $need_to_close_div = true;
                ?>
</div>
<div class="row">                
                <?                           
                        }
                } 
             }
             // echo "rafik !!!!!!!!!!!!!!!!!! : ".var_export($qsearch_by_text_cols,true);
             if(count($qsearch_by_text_cols)>0)
             {
        ?>
        <div class="col-md-<?=$qs_by_txt_qsize?>">
                <div class="form-group">
                        <label><?php echo $trad_qsearch_by_text; ?>
                          <img src='../lib/images/tooltip.png' class='tooltip-icon' data-toggle='tooltip' data-placement='top' title='<?=$translated_text_searchable_cols_txt?>'  width='20' heigth='20'>
                        </label>

                        <?php echo $trad_qsearch_by_text_input;?>
                </div>
        </div>
<?
              }
              if($need_to_close_div)
              {
?>

<?
              }
   }
   else
   {
?>
         
<?
   }
   
   //$can = $objme->iCanDoOperationOnObjClass($obj,"search");
   $can = false;
   if($can and false)
   {
?>                        
        <div class="col-md-3">
                <div class="form-group">
                        <label><?php echo $obj->translate('SUBMIT-SEARCH-ADVANCED',$lang,true); ?>
                        </label>
                        <input id="submit_advanced" type="button" name="submit_advanced" class="form-control togglebtn" onclick="avancedSubmitToggle();" value="إستعلام فقط">
                </div>
        </div>
<?php
   }

   $file_js = "search_".$obj->getTableName() . '.js';
   $file_dir_name = dirname(__FILE__);
   $md = $obj->getMyModule();
   $file_js_path = "$file_dir_name/../$md/js/$file_js";

   if (file_exists($file_js_path)) 
   {
?>                
<script src="./js/<?=$file_js?>"></script>
<?php
   }
?>                




