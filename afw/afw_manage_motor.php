<?php 
#####################################################################################
####################################  FONCTIONS  ####################################
#####################################################################################

function hidden_input($col_name, $desc, $val, &$obj)
{
        $type_input_ret = "hidden";
	?>
            <input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" value="<?php echo $val ?>">
        <?
        return $type_input_ret;
}

function genereInputForAttribute($col_name, &$obj, $lang, $formInputName="", $desc = "", $val = null)
{
	$objme = AfwSession::getUserConnected();
        
        if(!$desc) $desc = AfwStructureHelper::getStructureOf($obj,$col_name);
        if($val===null) $val = $obj->getVal($col_name);
        $prefix = $obj->translate($col_name.".prefix",$lang);
        if($prefix==$col_name.".prefix") $prefix = "";
        $prefix = trim($prefix);
        $suffix = $obj->translate($col_name.".suffix",$lang);
        if($suffix==$col_name.".suffix") $suffix = "";
        
        $suffix .= " ".((isset($desc["UNIT"]) and !empty($desc["UNIT"])  and (strlen($desc["UNIT"])<6)) ? $desc["UNIT"] : "");
        $suffix .= " ".((isset($desc["TITLE_AFTER"]) && !empty($desc["TITLE_AFTER"]) && (strlen($desc["TITLE_AFTER"])<6)) ? $desc["TITLE_AFTER"] : "");
        
        $suffix = trim($suffix);
        $attribute_error = $obj->getDataErrorForAttribute($col_name);
        /*
        if($attribute_error) echo "<div class='form-group error'>";
        else */ 
        
        echo "<div class='form-group'>";
        
        echo "<label>$prefix ".$obj->translate($col_name,$lang)." $suffix</label>";
                                                        
        
        $mode_qedit = false;
             
        if(!$formInputName) $formInputName = $col_name;
        
        //$col_title = $obj->getKeyLabel($orig_col_name,$lang);
        $col_title = $obj->translate($col_name, $lang);

        $placeholder_standard_code = "placeholder-$col_name";
        $placeholder_code = $desc["PLACE-HOLDER"];
        if(!$placeholder_code) $placeholder_code = $placeholder_standard_code;

        if ($placeholder_code==$placeholder_standard_code) $placeholder = $obj->getAttributeLabel($placeholder_code, $lang);
        elseif ($placeholder_code) $placeholder = $obj->translateMessage($placeholder_code, $lang);
        else $placeholder = "";

        if ((!$placeholder) or ($placeholder == $placeholder_standard_code)) 
        {
                if (($desc["MANDATORY"]) and ($desc["TYPE"] != "TEXT")) {
                $instruction_code = "INSTR-" . $desc["TYPE"];
                $instruction = $obj->translateOperator($instruction_code, $lang);
                if ($instruction == $instruction_code) $instruction = $obj->translateOperator("INSTR-STD", $lang);
                $placeholder = $instruction . " " . $col_title;
                } elseif (($desc["EMPTY_IS_ALL"]) or ($desc["FORMAT"] == "EMPTY_IS_ALL")) {
                $placeholder_code = "ALL-$col_name";
                $placeholder = $obj->translate($placeholder_code, $lang);
                if ($placeholder == $placeholder_code) $placeholder = $obj->translateOperator("ALL", $lang);
                } else {
                        $placeholder = "";
                }
        }    

        if($desc["INPUT-STYLE"]) $input_style = "style='".$desc["INPUT-STYLE"]."'";
        else $input_style = "";

        
        include("afw_config.php");     
             
	global $images;
        
        $type_input_ret = "";
        
        if($data_loaded) $data_loaded_class = " ${class_xqe}data_loaded";
        else $data_loaded_class= " ${class_xqe}data_notloaded";
        
        if(AfwStringHelper::stringStartsWith($col_name,"titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
        if(se_termine_par($col_name,"titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
        if(AfwStringHelper::stringStartsWith($col_name,"titre") && (!$desc["SIZE"])) $desc["SIZE"] = 255;
        
        
        $data_length_class = " inputlong";
        
        $desc["WHERE"] = $obj->getWhereOfAttribute($col_name);
                    
        $readonly = "";        
        
        if($desc["READONLY"])
        {
             $readonly = "readonly";
        }
        
        if($desc["JS-COMPUTED-READONLY"])
        {
             $readonly = "readonly";
        }
        
        if(true)
        {
             $onchange = $desc["ON-CHANGE"];
             $onchange = str_replace("§row§",$obj->qeditNum,$onchange);
             $onchange = str_replace("§rowcount§",$qeditCount,$onchange);
             
             if($desc["FOOTER_SUM"]) $onchange .= "qedit_col_total('$qeditNomCol',$qeditCount); ";
             
             $after_change = $desc["AFTER-CHANGE"];
             $after_change = str_replace("§row§",$obj->qeditNum,$after_change);
             $after_change = str_replace("§rowcount§",$qeditCount,$after_change);
             
             $onchange .= $after_change;
             //$onchange .= "iHaveBeenEdited('$col_name'); "; 
        }
	switch ($desc["TYPE"]) {
		case 'PK'     : 
                if($val<=0) $val = "سجل جديد";
                $type_input_ret = "text";
	?>			
                                <input placeholder="<?=$placeholder?>" type="text" class="form-control" name="<?php echo $formInputName ?>" value="<?php echo $val ?>" size=32 maxlength=255 readonly>
	<?php			break;
		case 'FK'     : $nom_table_fk   = $desc["ANSWER"];
                                $nom_module_fk  = $desc["ANSMODULE"];
                                if(!$nom_module_fk)
                                {
                                        $nom_module_fk = AfwUrlManager::currentWebModule();
                                }

				$nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
				//$nom_fichier_fk = AFWObject::table ToFile($nom_table_fk);
                                
                                $file_dir_name = dirname(__FILE__); 
                                
				
                                $objRep  = new $nom_class_fk;
                                            
                                $list_count = AfwSession::config($objRep->getMyClass()."::estimated_row_count", 0);
                                
                                $auto_c = $desc["AUTOCOMPLETE"];
                                
				if((!$auto_c) and ($list_count <= LIMIT_INPUT_SELECT))
                                {
                                        /*
                                        list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc,$val, $obj);                                        
                                        $l_rep=array();
					foreach ($liste_rep as $iditem => $item) 
                                        {
                                                if(AfwUmsPagHelper::userCanDoOperationOnObject($item,$objme,'display'))
							$l_rep[$iditem]=$item->getDisplay($lang);
					}
                                        */
                                        $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;
                                        $l_rep = AfwLoadHelper::vhGetListe($objRep, $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
                                                
                                        $prop_sel =
                                              array(  "class" => "form-control",
						"name"  => $formInputName,
                                                "id"  => $formInputName,
                                                "tabindex" => $qedit_orderindex,
                                                "onchange" => $onchange,
                                                "style" => $input_style,
                                                "mandatory" => $desc["MANDATORY"],
						);
                                        
                                        if($desc["HIDDEN_INPUT"]) 
                                        {
                                                    
                                                    $type_input_ret = "hidden";
	?>
                                                    <input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $formInputName ?>" value="<?php echo $val ?>" >
                                                    <span><?echo $l_rep[$val]?></span>        
        <?php                                        
                                        }
                                        else {
        					select(
        						$l_rep,
        						array($val),
        						$prop_sel
        					);
                                                $type_input_ret = "select";
                                        }        
				}
                                else
                                {
                                        $type_input_ret = "autocomplete";
                                        $col_name_atc = $formInputName."_atc"; 
                                        if(($val)) // and ((!$obj->fixm_disable) or (!$obj->fixmtit))) 
                                        {
                                                $objRep->load($val);
                                                $val_display = $objRep->getDisplay($lang);
                                        }
                                        else
                                        {
                                                $val_display = "";
                                        }
                                        //$clwhere = $desc["WHERE"];
                                        $attp = $col_name;
                                        $clp = $obj->getMyClass();
                                        $idp = $obj->getId();
                                        $modp = $obj->getMyModule();
                                        $auto_c_create = $auto_c["CREATE"];
                                        $atc_input_normal = $data_loaded_class." inputlongmoyen";
                                        
                                        if($auto_c_create) 
                                        {
                                              $class_icon = "new";
                                              $atc_input_modified_class = $data_loaded_class.$data_length_class." new_record";
                                        }
                                        else 
                                        {
                                              $class_icon = "notfound";
                                              $atc_input_modified_class = $data_loaded_class.$data_length_class." record_not_found";
                                        }
                                        
                                        if($obj->fixm_disable) 
                                        {
                                                    
                                                    $type_input_ret = "hidden";
	?>
                                                    <input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $formInputName ?>" value="<?php echo $val ?>">
                                                    <span><?if(!$obj->hideQeditCommonFields) echo "[$val_display]"?></span>        
        <?php                                        
                                        }
                                        else
                                        {
                                           $help_atc = $auto_c["HELP"];
	?>				
                                        <table cellspacing='0' cellpadding='0' style="width:100%">
                                        <tr style="background-color: rgba(255, 255, 255, 0);">
                                                <td style="padding:0px;margin:0px;background-color: rgba(255, 255, 255, 0);"><input type="hidden" id="<?=$formInputName?>"     name="<?=$formInputName?>" value="<?=$val?>" readonly></td>
                                                <td style="padding:0px;margin:0px;"><input placeholder="<?=$placeholder?>"  type="text" id="<?=$col_name_atc?>" name="<?=$col_name_atc?>" class="form-control" value="<?=$val_display?>"></td>
                                                <?
                                                if($auto_c_create) 
                                                {
                                                ?>
                                                    <th style="padding:0px;margin:0px;"><img src='../lib/images/create_new.png' data-toggle="tooltip" data-placement="top" title='لإضافة عنصر غير موجود في القائمة (بعد التثبت) انقر هنا ثم اكتب المسمى' onClick="empty_atc('<?=$formInputName?>');" style="width: 24px !important;height: 24px !important;"/></th>
                                                <?
                                                }
                                                ?>
                                                <td style="padding:0px;margin:0px;"><?=$help_atc?></td>
                                        </tr>
                                        </table>
                                        <script>
                                        $(function() {
                                         
                                            $("#<?=$col_name_atc?>").autocomplete({
                                                source: "../lib/api/autocomplete.php?cl=<?=$nom_class_fk?>&currmod=<?=$nom_module_fk?>&clp=<?=$clp?>&idp=<?=$idp?>&modp=<?=$modp?>&attp=<?=$attp?>",
                                                minLength: 0,
                                                
                                                change:function(event, ui) {
                                                    if($("#<?=$col_name_atc?>").val()=="")
                                                    {
                                                        $("#<?=$formInputName?>").val("");
                                                    }
                                                },
                                                

                                                select: function(event, ui) {
                                                    //alert(ui.item.id);
                                                    $("#<?=$formInputName?>").val(ui.item.id);
                                                    // $("#<?=$formInputName?>").attr('class', 'inputtrescourt cl_id');
                                                    // $("#<?=$col_name_atc?>").attr('class', '<?=$atc_input_normal?>');
                                                    $("#<?=$col_name_atc?>").addClass('input_changed');
                                                },
                                         
                                                html: true, // optional (jquery.ui.autocomplete.html.js required)
                                         
                                              // optional (if other layers overlap autocomplete list)
                                                open: function(event, ui) {
                                                    $(".ui-autocomplete").css("z-index", 1000);
                                                }
                                            });
                                         
                                        });
                                        </script>

	<?php			        }
                                }
				break;
		case 'MFK'    : $nom_table_fk   = $desc["ANSWER"];
                                $nom_module_fk  = $desc["ANSMODULE"];
                                if(!$nom_module_fk)
                                {
                                        $nom_module_fk = AfwUrlManager::currentWebModule();
                                }
				$nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
				//$nom_fichier_fk = AFWObject::table ToFile($nom_table_fk);
                                
                                
                                $objRep  = new $nom_class_fk;
                                /*
                                list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc,$val,$obj);                                
                                $l_rep=array();
				foreach ($liste_rep as $iditem => $item) 
                                {
                                        if(AfwUmsPagHelper::userCanDoOperationOnObject($item,$objme,'display'))
						$l_rep[$iditem]=$item->getDisplay($lang);
				}
                                */
                                $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;
                                $l_rep = AfwLoadHelper::vhGetListe($objRep, $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
                                                
                                $type_input_ret = "select";
                                
                                $class_of_input_select_multi = $class_inputSelect_multi_big;
                                if($desc["MEDIUM_DROPDOWN_WIDTH"]) $class_of_input_select_multi = $class_inputSelect_multi;
                                $infos_arr = array(
						"class" => "form-control",
						"name"  => $formInputName."[]",
                                                "id"  => $formInputName,
						"size"  => 5,
						"multi" => true,
                                                "tabindex" =>$qedit_orderindex,
                                                "onchange" => $onchange,
                                                "style" => $input_style,
                                                "mandatory" => $desc["MANDATORY"],

					);
                                if($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr,$desc["SEL_OPTIONS"]);
                                
                                if($desc["SEL_CSS_CLASS"]) $infos_arr["class"] = $desc["SEL_CSS_CLASS"];         
                                
				select(
					$l_rep,
					explode($separator, trim($val, $separator)),
					$infos_arr,
					"",
					false
				);

				break;
		case 'MENUM' :  $liste_rep = AfwLoadHelper::getEnumTable($desc["ANSWER"],$obj->getTableName(),$col_name,$obj);
				//echo "menum val $val with sep $separator : <br>";
				$val_arr = explode($separator, trim($val, $separator));
				//print_r($val_arr);
				//echo "<br>";
                                if($force_css) $data_length_class = " ".$force_css;
                                else $data_length_class = " inputmoyen";
                                $type_input_ret = "select";
                                
                                $class_of_input_select_multi = $class_inputSelect_multi_big;
                                if($desc["MEDIUM_DROPDOWN_WIDTH"]) $class_of_input_select_multi = $class_inputSelect_multi;
                                
                                $infos_arr = array(
						"class" => "form-control",
						"name"  => $formInputName."[]",
                                                "id"  => $formInputName,
						"size"  => 5,
						"multi" => true,
                                                "tabindex" =>$qedit_orderindex,
                                                "onchange" => $onchange,
                                                "style" => $input_style,
                                                "mandatory" => $desc["MANDATORY"],

					);
                                if($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr,$desc["SEL_OPTIONS"]);
                                
                                
                                select(
					$liste_rep,
					$val_arr,
					$infos_arr,
					""
				);
                                break;		
		
                /* case 'ANSWER' : obsolete
                                $liste_rep = AFWObject::getAnswerTable($desc["ANSWER"], $desc["MY_PK"], $desc["MY_VAL"]);
                                if($force_css) $data_length_class = " ".$force_css;
                                else $data_length_class = " inputmoyen";
				if(count($liste_rep) <= LIMIT_INPUT_SELECT)
                                {
                                        $type_input_ret = "select";
					select(
						$liste_rep,
						array($val),
						array(
							"class" => "form-control",
							"name"  => $formInputName,
                                                        "id"  => $formInputName,
                                                        "tabindex" =>$qedit_orderindex,
                                                        "onchange" => $onchange,
                                                        "style" => $input_style,
                                                        "mandatory" => $desc["MANDATORY"],
						),
						"asc"
					);
				}
                                else{
                                        $type_input_ret = "text";
	?>				<input placeholder="<?=$placeholder?>"  type="text" tabindex="<?=$qedit_orderindex?>" class="form-control" name="<?php echo $formInputName ?>" id="<?php echo $formInputName ?>" value="<?php echo $val ?>" size=33 maxlength=255>
					<input type="button"   class="<?=$class_inputButton?>" name="" value="<?=$obj->translate('SEARCH',$lang,true)?>" onclick="popup('<?php echo "main.php"?>?Main_Page=afw_mode_search.php&cl=<?php echo $desc["ANSWER"]?>')">
					<script language="javascript">
						function popup(page) 
                                                {
							window.open(page, "<?=$obj->translate('SEARCH',$lang,true)?>", "fullscreen='yes',menubar='no',toolbar='no',location='no',status='no'");
						}
					</script>
	<?php			}
				break;*/
		case 'ENUM'   : if($force_css) $data_length_class = " ".$force_css;
                                else $data_length_class = " inputmoyen";
                                
                                if((!$desc["ENUM_ALPHA"]) and ((!$val) or (!intval($val)))) $val = 0;
                                
                                if($desc["ANSWER"]=="INSTANCE_FUNCTION")
                                {
                                     $liste_rep = AfwStructureHelper::getEnumAnswerList($obj, $formInputName);
                                     $answer_case = "INSTANCE_FUNCTION so obj -> get EnumAnswerList";
                                }
                                else
                                {
                                     $liste_rep = AfwLoadHelper::getEnumTable($desc["ANSWER"],$obj->getTableName(),$formInputName,$obj);
                                     $answer_case = "AfwLoadHelper::getEnumTable(".$desc["ANSWER"].")";
                                }
                                
                                //if($desc["FORMAT-INPUT"]=="hzmtoggle") $obj->_error("enum liste_rep comes from $answer_case : ".var_export($liste_rep,true));
                                
                                if($obj->fixm_disable) 
                                {
                                                    
                                                    $type_input_ret = "hidden";
	?>
                                                    <input type="hidden" id="<?php echo $formInputName ?>" name="<?php echo $formInputName ?>" value="<?php echo $val ?>">
                                                    <span><?if(!$obj->hideQeditCommonFields) echo $liste_rep[$val]?></span>        
        <?php                                        
                                }
                                else 
                                {
					
                                        if($desc["FORMAT-INPUT"]=="hzmtoggle")
                                        {
                                             $display_val = $liste_rep[$val];
                                             if(!$display_val) $display_val = "...<!-- $val from ".var_export($liste_rep,true)." -->";
                                             $css_arr = AfwStringHelper::afw_explode($desc["HZM-CSS"]);
                                             $css_val = $css_arr[$val];
                                             
                                             
                                             
                                             $liste_choix = array();
                                             $liste_css = array();
                                             $liste_codes = array();
                                             $liste_codeOrdres = array();
                                             $listeOrdres = array();
                                             
                                             $log_echo = "log of hzm enum toggle : ";
                                             $log_echo .= "<br>\n liste_rep = ".var_export($liste_rep,true);
                                             $max_rep_id = 0;
                                             $oord = 0;
                                             foreach($liste_rep as $rep_id => $rep_val)
                                             {
                                                 if($rep_val)
                                                 {   
                                                    $liste_choix[$oord] = $rep_val;
                                                    $liste_codes[$oord] = $rep_id;
                                                    $liste_codeOrdres[$rep_id] = $oord;
                                                    if($max_rep_id<$rep_id) $max_rep_id = $rep_id;
                                                    $liste_css[$oord] = $css_arr[$rep_id];
                                                    $log_echo .= "<br>\n $rep_id => $rep_val , ".var_export($liste_css,true);
                                                    $oord++;
                                                 }   
                                             }
                                             
                                             for($oo=0;$oo<=$max_rep_id;$oo++)
                                             {
                                                  if(!isset($liste_codeOrdres[$oo])) $listeOrdres[$oo] = -1;
                                                  else $listeOrdres[$oo] = $liste_codeOrdres[$oo]; 
                                             }
                                             
                                             //if($col_name=="coming_status_id_0") $obj->_error($log_echo);
                                             if(!$css_val) $css_val = $desc["DEFAULT-CSS"];
                                             if(!$css_val) $css_val = $liste_css[0];
                                             
                                             $liste_choix_text = "['".implode("','",$liste_choix)."']";
                                             $liste_codes_text = "['".implode("','",$liste_codes)."']";
                                             $listeOrdres_text = "['".implode("','",$listeOrdres)."']";
                                             
                                             $liste_css_text = "['".implode("','",$liste_css)."']";
?>                                        
<input type='hidden' name='<?php echo $formInputName ?>' id='<?php echo $formInputName ?>' value='<?php echo $val ?>'>
<button type="button" id="btn_<?php echo $formInputName ?>" class="toggle-hzm-btn <?php echo $css_val ?>" onClick="toggleHzmBtn('<?php echo $formInputName ?>', <?php echo $liste_choix_text ?>, <?php echo $liste_codes_text ?>, <?php echo $listeOrdres_text ?>, <?php echo $liste_css_text ?>,<?php echo count($liste_choix) ?>)"><?php echo $display_val ?></button>
        <?php                                        
                                        }
                                        else
                                        {
                                                $type_input_ret = "select";
                                                
                                                if($desc["FORMAT-INPUT"]=="hzmsel")
                                                {
                                                    $css_arr = AfwStringHelper::afw_explode($desc["HZM-CSS"]);
                                                    $css_class = "selectpicker";//." ".$data_loaded_class.$data_length_class
                                                }
                                                else
                                                {
                                                    $css_arr = null;
                                                    $css_class = $class_inputSelect.$data_loaded_class.$data_length_class;
                                                }   
                                                
                                                $info = array(
        							"class" => "form-control",
        							"name"  => $formInputName,
                                                                "id"  => $formInputName,
                                                                "tabindex" =>$qedit_orderindex,
                                                                "onchange" => $onchange,
                                                                "bsel_css" => [],
                                                                "style" => $input_style,
                                                                "mandatory" => $desc["MANDATORY"],
        						);
                                                
                                                
                                                
                                                select(
        						$liste_rep,
        						array($val),
        						$info,
        						""
        					);
                                        }
                                }        
				break;
		case 'PCTG'    : 
		case 'INT'     :
                case 'TIME'     : 
		case 'AMNT'     : 
                                $type_input_ret = "text";
                                
                                if($obj->fixm_disable) 
                                {
                                                    $val_decoded = $obj->showAttribute($col_name);
                                                    $type_input_ret = "hidden";
	?>
                                                    <input type="hidden" id="<?php echo $formInputName ?>" name="<?php echo $formInputName ?>" value="<?php echo $val ?>">
                                                    <span><?if(!$obj->hideQeditCommonFields) echo $val_decoded?></span>
        <?php                                        
                                }
                                else 
                                {      
	?>
                                <input placeholder="<?=$placeholder?>" type="text" tabindex="<?=$qedit_orderindex?>" class="form-control" name="<?php echo $formInputName ?>" id="<?php echo $formInputName ?>" value="<?php echo $val ?>" size=6 maxlength=6 <?php echo $readonly?> onchange="<?php echo $onchange?>" <?=$input_style?>>
	<?php			
                                }
                                
				break;
                                
		case 'TEXT'   : $utf8 = $desc["UTF8"];
                                $dir = $desc["DIR"];
                                if(!$dir) $dir = ($utf8 ? "rtl":"ltr");

                                if((isset($desc["SIZE"])) && (($desc["SIZE"] == "AREA") or ($desc["SIZE"] == "AEREA")))
                                {
                                        $rows = $desc["ROWS"];
                                        if(!$rows) $rows = 4;
                                        $cols = $desc["COLS"];
                                        if(!$cols) $cols = 43;
                                        $type_input_ret = "text";
                                        
	?>                              
        				<textarea placeholder="<?=$placeholder ?>" class="form-control" cols="<?=$cols?>" rows="<?=$rows?>" id="<?php echo $formInputName ?>" name="<?php echo $formInputName ?>" dir="<?php echo $dir ?>" onchange="<?php echo $onchange?>" <?=$input_style?>><?php echo $val ?></textarea>
	<?			}
                                else
                                {
                                    if(($force_css) and (!$desc["WIDTH-FROM-SIZE"])) $data_length_class = " ".$force_css;
                                    else if(isset($desc["SIZE"]) && $desc["SIZE"] <= 16)  $data_length_class = " inputcourt";
                                    else if(isset($desc["SIZE"]) && $desc["SIZE"] <= 41)  $data_length_class = " inputmoyen";
                                    else if(isset($desc["SIZE"]) && $desc["SIZE"] <= 84)  $data_length_class = " inputlong";
                                    else if(isset($desc["SIZE"]) && $desc["SIZE"] < 255)  $data_length_class = " inputtreslong";
                                    else $data_length_class = " inputultralong";
                                    $type_input_ret = "text";  
	?>
        				<input placeholder="<?=$placeholder ?>" type="text" tabindex="<?=$qedit_orderindex?>" class="form-control" name="<?php echo $formInputName ?>" id="<?php echo $formInputName ?>"  dir="<?php echo $dir ?>" value="<?php echo $val ?>" size=32 maxlength=255 onchange="<?php echo $onchange?>" <?=$input_style?>>
	<?
        			}
				break;
		case 'YN'     :
				if($force_css) $data_length_class = " ".$force_css;
                                else $data_length_class = "";

				$this_yes_label = $obj->showYNValueForAttribute("YES", $col_name, $lang);
                                $this_no_label  = $obj->showYNValueForAttribute("NO", $col_name, $lang);
                                $this_dkn_label = $obj->showYNValueForAttribute("EUH", $col_name, $lang);

				$answer_list = array("Y"=>$this_yes_label, "N"=>$this_no_label, "W"=>$this_dkn_label);
				if(isset($desc["ANSWER"]) && !empty($desc["ANSWER"]))
                                {
					$temp_answer_val = explode('|', $desc["ANSWER"]);
					if(count($temp_answer_val) == 3)
                                        {
						$answer_list["Y"] = $temp_answer_val[0];
						$answer_list["N"] = $temp_answer_val[1];
						$answer_list["W"] = $temp_answer_val[2];
                                        }
				}
                                $type_input_ret = "select";
                                if($obj->fixm_disable) 
                                {
                                                    
                                                    $type_input_ret = "hidden";
	?>
                                                    <input type="hidden" id="<?php echo $formInputName ?>" name="<?php echo $formInputName ?>" value="<?php echo $val ?>">
                                                    <span><?if(!$obj->hideQeditCommonFields) echo $answer_list[$val]?></span>        
        <?php                                        
                                }
                                else 
                                {
                                                select(
                						$answer_list,
                						array($val),
                						array(
                							"class" => "form-control", 
                							"name"  => $formInputName,
                                                                        "id"  => $formInputName,
                                                                        "tabindex" =>$qedit_orderindex,
                                                                        "onchange" => $onchange,
                                                                        "style" => $input_style,
                                                                        "mandatory" => $desc["MANDATORY"],
                						),
                						"asc"
                					);
                                }
                                
				break;
		case 'DATE'   :
				        $type_input_ret = "text";
                                        $input_name = $formInputName;
                                        $valaff = AfwDateHelper::displayDate($val);
        				?>
                                        
                                        <input placeholder="<?=$placeholder ?>" type="text" id="<?=$input_name?>" name="<?=$formInputName?>" value="<?=$valaff?>" class="form-control" onchange="<?php echo $onchange?>" <?=$input_style?>>
                                        
                                        <script type="text/javascript">
                                          $('#<?=$input_name?>').calendarsPicker({calendar: $.calendars.instance('UmmAlQura')});
                                        </script>
                                        
        		<?php		break;
		case 'GDAT'   :
				        $type_input_ret = "text";
                                        $input_name = $formInputName;
                                        $valaff = AfwDateHelper::displayGDate($val);
        				?>
                                        
                                        <input placeholder="<?=$placeholder ?>" type="text" id="<?=$input_name?>" name="<?=$formInputName?>" value="<?=$valaff?>" class="form-control" onchange="<?php echo $onchange?>" <?=$input_style?>>
                                        <script type="text/javascript">
                                            $("#<?=$input_name?>").datepicker({
                                              changeMonth: true,
                                              changeYear: true,
                                              minDate: "-40Y", 
                                              maxDate: "-1Y"
                                            });
                                        </script>
                                        
        		<?php		break;
		default       :
                              $type_input_ret = "text";   
	?>			<input placeholder="<?=$placeholder ?>" type="text" tabindex="<?=$qedit_orderindex?>" class="form-control" name="<?php echo $formInputName ?>" id="<?php echo $formInputName ?>" value="<?php echo $val ?>" size=32 maxlength=255  onchange="<?php echo $onchange?>" <?=$input_style?>>
	<?php			break;
	}
	if($attribute_error) echo "<div class='help-inline alert alert-danger alert-dismissable'>$attribute_error</div>\n";
        echo "</div>\n";
        return $type_input_ret;
}



function select($list_id_val, $selected = array(), $info = array(), $ordre = "", $null_val = true)
{
	global $lang;
        
        // @todo not all time should be well studied
        // if(count($list_id_val)==0) return;
	
        switch (strtolower($ordre)) 
	{
		case 'asc' : $list_val = array();
			     foreach ($list_id_val as $id => $val)
				$list_val[$id] = ''.$val;
			     $list_id_val = subval_sort($list_id_val, $list_val, "asc");
			     break;
		case 'desc': $list_val = array();
			     foreach ($list_id_val as $id => $val)
				$list_val[$id] = ''.$val;
			     $list_id_val = subval_sort($list_id_val, $list_val, "desc");
			     break;
		default    : break;
	}
	$multi = "";
	if(isset($info["multi"]) && $info["multi"])
		$multi = " multiple";
	$size = 1;
	if(isset($info["size"]))
		$size = intval($info["size"]);
	$count = count($list_id_val);
	if(!empty($multi) && $count < $size)
		$size = $count;
	if(!$info["id"]) $info["id"] = trim(trim($info["name"],"]"),"[");
        ?>
	<select
		class="<?php echo $info["class"] ?>"
		name="<?php echo $info["name"] ?>"
                id="<?php echo $info["id"] ?>"
                tabindex="<?php echo $info["tabindex"] ?>"
                onchange="<?php echo $info["onchange"] ?>"
		<?php echo $multi ?>
		size=<?php echo $size ?>
                <?=$info["style"]?>
                <?php if($info["disable"]) echo "disabled" ?>
		
                >
	<?php   if($null_val and (!$info["mandatory"])){
	?>		<option value="0"<?php echo (in_array(0, $selected))? " selected" : "";?>>&nbsp;</option>
	<?php   }
                $data_content = "";
		foreach ($list_id_val as $id => $val)
                {
                       if($info["bsel_css"])
                       {
                               $opt_css = $info["bsel_css"][$id];
                               $data_content = "data-content=\"<span class='$opt_css'>$val</span>\"";
                       }
	?>		<option value="<?php echo $id ?>"<?php echo (in_array($id, $selected))? " selected" : "";?> <?php echo $data_content ?>><?php echo $val ?></option>
	<?php   } ?>
	</select>
        <?
        if($multi)
        {
        ?>
<!-- Initialize the plugin: -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#<?php echo $info["id"] ?>').multiselect(
                {
                    inheritClass: true,
                    
<?if($info["numberDisplayed"]) {?>    numberDisplayed: '<?=$info["numberDisplayed"]?>',<?}?>
<?if($info["buttonWidth"]) {?>    buttonWidth: '<?=$info["buttonWidth"]?>',<?}?>
<?if($info["dropRight"]) {?>    dropRight: true,<?}?>
<?if($info["inheritClass"]) {?>    inheritClass: true,<?}?>
<?if($info["enableFiltering"]) {?>    enableFiltering: true,<?}?>
<?if($info["filterBehavior"]) {?>    filterBehavior: '<?=$info["filterBehavior"]?>',<?}?>
<?if($info["filterPlaceholder"]) {?>    filterPlaceholder: '<?=$info["filterPlaceholder"]?>',<?}?>
<?if($info["maxHeight"]) {?>    maxHeight: <?=$info["maxHeight"]?>,<?}?>
<?if($info["includeSelectAllOption"]) {?>    includeSelectAllOption: true<?}?>
                }
        );
    });
</script>        
        <?
        }
        ?>
<?php
}
function subval_sort($table_a_trie, $table_ref, $ord = "desc"){
	$res = array();
	if($ord == "asc")
		asort($table_ref);
	else
		arsort($table_ref);
	foreach($table_ref as $key => $val)
		$res[$key] = $table_a_trie[$key];
	return $res;
}
?>