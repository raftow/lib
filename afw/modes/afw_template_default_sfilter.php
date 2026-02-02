<?php
require_once(dirname(__FILE__)."/../../../config/global_config.php");
$themeArr = AfwThemeHelper::loadTheme();
foreach($themeArr as $theme => $themeValue)
{
    $$theme = $themeValue;
}
require_once 'afw_rights.php';
//global  $TMP_DIR,$cl,$pk,$spk,$TMP_ROOT, $lang, $class_table, $class_tr1, $class_tr2, $pct_tab_search_criteria, $class_tr1_sel, $class_tr2_sel ;
$objme = AfwSession::getUserConnected();
$lang = AfwLanguageHelper::getGlobalLanguage();
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

$stats_config_sfilter_col = $obj->stats_config['SFILTER'];

/**
 * @var AFWObject $obj
 */

foreach($class_db_structure as $nom_col => $desc)
{
        if(AfwPrevilegeHelper::isSFilterCol($obj, $nom_col, $desc) and $stats_config_sfilter_col[$nom_col])
        {
		if($total_qsize<$max_total_qsize)
                {
                        $filled_val = $_POST[$nom_col];
                        
                        $data[$nom_col]["filled_criteria"] = ($filled_val);                        
                        $data[$nom_col]["trad"]  = $obj->translate($nom_col, $lang);
        
                        if(!$desc["FSIZE"]) $desc["FSIZE"] = $desc["QSIZE"];
                        $data[$nom_col]["qsize"] = $desc["FSIZE"];
                        
                        if(!$data[$nom_col]["qsize"]) $data[$nom_col]["qsize"] = 12;
                        $total_qsize += $data[$nom_col]["qsize"];
        
                        $desc["SEARCH-BY-ONE"] = true;
        
                        
        		ob_start();
        		AfwQsearchMotor::type_input($nom_col, $desc, $obj, $data[$nom_col]["filled_criteria"]);
        		$data[$nom_col]["input"] = ob_get_clean();
                        $oper_qsearch = $desc["SFILTER_OPER"];
                        if(!$oper_qsearch) $oper_qsearch = $desc["QSEARCH_OPER"];
                        if(!$oper_qsearch)
                        {
                                if(($desc["TYPE"]=="DATE") or ($desc["TYPE"]=="GDAT")) $oper_qsearch = "between";
                                else $oper_qsearch = "=";
                        } 
                        ob_start();
        		AfwQsearchMotor::hidden_input("oper_".$nom_col, null, $oper_qsearch, null);
        		$data[$nom_col]["oper"] = ob_get_clean();
                }
                
                //if($nom_col=="id_domain")  $obj->_error("data[$nom_col] = ".var_export($data[$nom_col],true));

	}
        //elseif($nom_col=="id_domain")  $obj->_error("desc [$nom_col] = ".var_export($desc,true));
}

?>

 


<? 
   $qsearch_by_text_cols = [];
   
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
                                        <label><?php echo $info["trad"]; ?></label>
                                        <?php echo "<!-- start input -->".$info["input"]."<!-- end input start oper-->".$info["oper"]."<!-- end oper-->";?>
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
<div class="row-sfilter row row-buttons">                
                <?                           
                        }
                } 
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
   

   $file_js = "sfilter_".$obj->getTableName() . '.js';
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