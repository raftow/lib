<?php 
// old require of afw_root 
class AfwInputHelper extends AFWRoot 
{
        public static function hidden_input($col_name, $desc, $val, &$obj)
        {
                $type_input_ret = "hidden";
                
                return array("<input type=\"hidden\" name=\"$col_name\" value=\"$val\">", $type_input_ret);
        }

        public static function text_input($col_name, $desc, $val, &$obj, $separator, $data_loaded=false, $force_css="",$qedit_orderindex=0,$data_length_class_default_for_fk = "inputmoyen", $debugg=false)
        {
                global $lang;
                $html = "";
                $orig_col_name = $col_name;
                $dir = "ltr";
                if($obj)
                {
                        $col_title = $obj->getAttributeLabel($orig_col_name,$lang);
                        $placeholder_standard_code = "placeholder-$orig_col_name";
                        $placeholder_code = $desc["PLACE-HOLDER"];
                        if(!$placeholder_code) $placeholder_code = $placeholder_standard_code;
                        if ($placeholder_code==$placeholder_standard_code) $placeholder = $obj->getAttributeLabel($placeholder_code, $lang);
                        elseif ($placeholder_code) $placeholder = $obj->translateMessage($placeholder_code, $lang);
                        else $placeholder = "";
                        
                        
                        if((!$placeholder) or ($placeholder==$placeholder_standard_code)) 
                        {
                                if(($desc["MANDATORY"]) and ($desc["TYPE"] != "TEXT"))
                                {
                                        $instruction_code = "INSTR-".$desc["TYPE"];
                                        $instruction = $obj->translateOperator($instruction_code,$lang);
                                        if($instruction==$instruction_code) $instruction = $obj->translateOperator("INSTR-STD",$lang);
                                        $placeholder = $instruction." ".$col_title;
                                }
                                elseif(($desc["EMPTY_IS_ALL"]) or ($desc["FORMAT"]=="EMPTY_IS_ALL"))
                                {
                                        $placeholder_code = "ALL-$orig_col_name";
                                        $placeholder = $obj->getAttributeLabel($placeholder_code,$lang);
                                        if($placeholder==$placeholder_code) $placeholder = $obj->translateOperator("ALL",$lang);
                                }
                                else
                                {
                                        $placeholder = "";
                                }        
                        }
                }
                else $placeholder = "";
                
                if($desc["INPUT-STYLE"]) $input_style = "style='".$desc["INPUT-STYLE"]."'";
                else $input_style = "";
                
                
                $type_input_ret = "";
                if($data_loaded) $data_loaded_class = " data_loaded";
                else $data_loaded_class= " data_notloaded";
                
                if(AfwStringHelper::stringStartsWith($col_name,"titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
                if(se_termine_par($col_name,"titre_short") && (!$desc["SIZE"])) $desc["SIZE"] = 40;
                if(AfwStringHelper::stringStartsWith($col_name,"titre") && (!$desc["SIZE"])) $desc["SIZE"] = 255;
                
                
                $data_length_class = " inputlong";
                
                if($obj) $desc["WHERE"] = $obj->getWhereOfAttribute($orig_col_name);

                if($desc["STYLE"]) $style_input = " style='".$desc["STYLE"]."' ";
                else $style_input = "";
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
                        // $onchange = str_replace("§row§",$obj->qeditNum,$onchange);
                        // $onchange = str_replace("§rowcount§",$qeditCount,$onchange);
                        
                        // if($desc["FOOTER_SUM"]) $onchange .= "qedit_col_total('$qeditNomCol',$qeditCount); ";
                        
                        $after_change = $desc["AFTER-CHANGE"];
                        // $after_change = str_replace("§row§",$obj->qeditNum,$after_change);
                        // $after_change = str_replace("§rowcount§",$qeditCount,$after_change);
                        
                        $onchange .= $after_change;
                        // if($mode_qedit) $onchange .= "iHaveBeenChanged('$col_name'); ";
                        // else 
                        $onchange .= "iHaveBeenEdited('$col_name'); "; 
                }
                
                if($desc["REQUIRED"] or $desc["MANDATORY"]) $input_required = "required='true'"; else $input_required = "";
                if($desc["REQUIRED"] or $desc["MANDATORY"]) $is_required = true; else $is_required = false;
                
                switch ($desc["TYPE"]) {
                        case 'PK'     : 
                                if($val<=0)
                                { 
                                        $type_input_ret = "hidden";
                                        $html .= "<input type=\"hidden\" name=\"$col_name\" value=\"$val\">";
                                }
                                else
                                {
                                        $type_input_ret = "text";
                                        $html .= "<input type=\"text\" class=\"form-control\" name=\"$col_name\" value=\"$val\" size=32 maxlength=255 readonly>";
                                }
                                break;
                        case 'FK'     : 
                                        $objRep  = $obj->getEmptyObject($col_name);
                                                
                                        $list_count = AfwSession::config($objRep->getMyClass()."::estimated_row_count", 0);
                                        $auto_c = $desc["AUTOCOMPLETE"];
                                        if((!$auto_c) and ($list_count <= LIMIT_INPUT_SELECT))
                                        {
                                                // list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc,$val, $obj, true);
                                                // if(AfwSession::config("MODE_DEVELOPMENT", false)) echo "<!-- for $col_name : $sql -->";
                                                // list($mdl, $myTbl) = $obj->getThisModuleAndAtable();
                                                // $l_rep = AfwHtmlHelper::constructDropDownItems($liste_rep, $lang, $col_name, "$mdl.$myTbl");                                                
                                                $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;
                                                $l_rep = AfwLoadHelper::vhGetListe($objRep, $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
                                                
                                                if($placeholder != $col_title)
                                                {
                                                        $empty_item = $placeholder;
                                                }
                                                else
                                                {
                                                        $empty_item = "";
                                                }
                                                
                                                
                                                $prop_sel = array(  "class" => "form-control",
                                                                        "name"  => $col_name,
                                                                        "id"  => $col_name,
                                                                        "tabindex" => $qedit_orderindex,
                                                                        "style" => $input_style,
                                                                        "empty_item" => $empty_item,
                                                                        "reloadfn" => $obj->getJsOfReloadOf($col_name),
                                                                        "onchange" => $onchange.$obj->getJsOfOnChangeOf($col_name),
                                                                        "required" => $is_required,
                                                                        );
                                                
                                                $html .= self::drop_down($l_rep, array($val),$prop_sel );
                                                $type_input_ret = "select";
                                        }
                                        else
                                        {
                                                $type_input_ret = "autocomplete";
                                                $col_name_atc = $col_name."_atc"; 
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

                                                $nom_table_fk   = $desc["ANSWER"];
                                                $nom_module_fk  = $desc["ANSMODULE"];
                                                if(!$nom_module_fk)
                                                {
                                                        $nom_module_fk = AfwUrlManager::currentWebModule();
                                                }
                                                $nom_class_fk   = AFWObject::tableToClass($nom_table_fk);
                                                // $nom_fichier_fk = AFWObject::table ToFile($nom_table_fk);

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
                                                
                                                if(true)
                                                {
                                                        $help_atc = $auto_c["HELP"];
                                                        $html .= "
                                                        <table cellspacing='0' cellpadding='0' style='width:100%'>
                                                                <tr style='background-color: rgba(255, 255, 255, 0);'>
                                                                        <td style='padding:0px;margin:0px;background-color: rgba(255, 255, 255, 0);'>
                                                                                <input type='hidden' id='$col_name' name='$col_name' value='$val' readonly>
                                                                        </td>
                                                                        <!-- do not put placeholder=$placeholder because it disable required behavior  -->
                                                                        <td style='padding:0px;margin:0px;'>
                                                                                <input placeholder='اكتب بعض الكلمات للبحث' type='text' id='$col_name_atc name='$col_name_atc' class='form-control' value='$val_display'  $input_required >
                                                                        </td>
                                                                        <td style='padding:0px;margin:0px;'>$help_atc</td>
                                                                </tr>
                                                        </table>
                                                        <script>
                                                        $(function() {
                                                        
                                                        $(\"#$col_name_atc\").autocomplete({
                                                                source: \"../lib/api/autocomplete.php?cl=$nom_class_fk&currmod=$nom_module_fk&clp=$clp&idp=$idp&modp=$modp&attp=$attp \",
                                                                minLength: 0,
                                                                
                                                                change:function(event, ui) {
                                                                        if($(\"#$col_name_atc\").val()==\"\")
                                                                        {
                                                                                $(\"#$col_name\").val(\"\");
                                                                        }                                                                
                                                                },
                                                                

                                                                select: function(event, ui) {
                                                                        //alert(ui.item.id);
                                                                        $(\"#$col_name\").val(ui.item.id);
                                                                        $(\"#$col_name\").attr('class', 'inputtrescourt cl_id');
                                                                        $(\"#$col_name_atc\").attr('class', 'form-control');
                                                                        $(\"#$col_name_atc\").addClass('input_changed');
                                                                },
                                                        
                                                                html: true, // optional (jquery.ui.autocomplete.html.js required)
                                                        
                                                                // optional (if other layers overlap autocomplete list)
                                                                open: function(event, ui) {
                                                                        $(\".ui-autocomplete\").css(\"z-index\", 1000);
                                                                }
                                                        });
                                                        
                                                        });
                                                        </script>";

                			        }
                                        }
                                        break;
                        case 'MFK'    : 
                                        $objRep  = $obj->getEmptyObject($col_name);
                                        
                                        // list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep,$desc,$val,$obj);
                                        // list($mdl, $myTbl) = $obj->getThisModuleAndAtable();
                                        // $l_rep = AfwHtmlHelper::constructDropDownItems($liste_rep, $lang, $col_name, "$mdl.$myTbl");
                                        $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;
                                        $l_rep = AfwLoadHelper::vhGetListe($objRep, $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
                                                
                                        
                                        $type_input_ret = "select";
                                        
                                        // $class_of_input_select_multi = $class_inputSelect_multi_big;
                                        // if($desc["MEDIUM_DROPDOWN_WIDTH"]) $class_of_input_select_multi = $class_inputSelect_multi;
                                        $infos_arr = array(
                                                        "class" => "form-control",
                                                        "name"  => $col_name."[]",
                                                        "id"  => $col_name,
                                                        "size"  => 5,
                                                        "multi" => true,
                                                        "tabindex" =>$qedit_orderindex,
                                                        "onchange" => $onchange,
                                                        "style" => $input_style,

                                                );
                                        if($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr,$desc["SEL_OPTIONS"]);
                                        
                                        if($desc["SEL_CSS_CLASS"]) $infos_arr["class"] = $desc["SEL_CSS_CLASS"];         
                                        
                                        $html .= self::drop_down(
                                                $l_rep,
                                                explode($separator, trim($val, $separator)),
                                                $infos_arr,
                                                "",
                                                false
                                        );

                                        break;
                        case 'MENUM' :  $liste_rep = $obj->getEnumAnswerList($col_name, $desc["ANSWER"]);
                                        
                                        //echo "menum val $val with sep $separator : <br>";
                                        $val_arr = explode($separator, trim($val, $separator));
                                        //print_r($val_arr);
                                        //echo "<br>";
                                        if($force_css) $data_length_class = " ".$force_css;
                                        else $data_length_class = " inputmoyen";
                                        $type_input_ret = "select";
                                        
                                        // $class_of_input_select_multi = $class_inputSelect_multi_big;
                                        // if($desc["MEDIUM_DROPDOWN_WIDTH"]) $class_of_input_select_multi = $class_inputSelect_multi;
                                        
                                        $infos_arr = array(
                                                        "class" => "form-control",
                                                        "name"  => $col_name."[]",
                                                        "id"  => $col_name,
                                                        "size"  => 5,
                                                        "multi" => true,
                                                        "tabindex" =>$qedit_orderindex,
                                                        "onchange" => $onchange,
                                                        "style" => $input_style,

                                                );
                                        if($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr,$desc["SEL_OPTIONS"]);
                                        
                                        
                                        $html .= self::drop_down($liste_rep, $val_arr, $infos_arr,"",false);
                                        break;		
                        case 'ANSWER' : $liste_rep = AFWObject::getAnswerTable($desc["ANSWER"], $desc["MY_PK"], $desc["MY_VAL"]);
                                        if($force_css) $data_length_class = " ".$force_css;
                                        else $data_length_class = " inputmoyen";
                                        if(true) // count($liste_rep) <= LIMIT_INPUT_SELECT
                                        {
                                                $type_input_ret = "select";
                                                $html .= self::drop_down(
                                                        $liste_rep,
                                                        array($val),
                                                        array(
                                                                "class" => "form-control",
                                                                "name"  => $col_name,
                                                                "id"  => $col_name,
                                                                "tabindex" =>$qedit_orderindex,
                                                                "onchange" => $onchange,
                                                                "style" => $input_style,
                                                                "required" => $is_required,
                                                                
                                                        ),
                                                        "asc"
                                                );
                                        }
                                        break;
                        case 'ENUM'   : if($force_css) $data_length_class = " ".$force_css;
                                        else $data_length_class = " inputmoyen";
                                        
                                        if((!$desc["ENUM_ALPHA"]) and ((!$val) or (!intval($val)))) $val = 0;
                                        
                                        if($desc["ANSWER"]=="INSTANCE_FUNCTION")
                                        {
                                                $liste_rep = $obj->getEnumAnswerList($orig_col_name);
                                                $answer_case = "INSTANCE_FUNCTION so obj->getEnumAnswerList($orig_col_name) ";
                                        }
                                        else
                                        {
                                                $liste_rep = AFWObject::getEnumTable($desc["ANSWER"],$obj->getTableName(),$orig_col_name,$obj);
                                                $answer_case = "AFWObject::getEnumTable(".$desc["ANSWER"].",".$obj->getTableName().",".$orig_col_name.",".$obj.")";
                                        }

                                        if($desc["FORMAT-INPUT"])                                        
                                        {
                                                
                                                if($desc["FORMAT-INPUT"]=="hzmtoggle")
                                                {
                                                        $display_val = $liste_rep[$val];
                                                        if(!$display_val) $display_val = "...<!-- $val from ".var_export($liste_rep,true)." -->";
                                                        $css_arr = $obj::afw_explode($desc["HZM-CSS"]);
                                                        $css_val = $css_arr[$val];
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
                                                                if(isset($liste_codeOrdres[$oo])) $listeOrdres[$oo] = $liste_codeOrdres[$oo]; 
                                                                //else $listeOrdres[$oo] = -1;
                                                        }
                                                        
                                                        //if($col_name=="coming_status_id_0") $obj->throwError($log_echo);
                                                        if(!$css_val) $css_val = $desc["DEFAULT-CSS"];
                                                        if(!$css_val) $css_val = $liste_css[0];
                                                        
                                                        $liste_choix_text = "['".implode("','",$liste_choix)."']";
                                                        $liste_codes_text = "['".implode("','",$liste_codes)."']";
                                                        $listeOrdres_text = "['".implode("','",$listeOrdres)."']";
                                                        
                                                        $liste_css_text = "['".implode("','",$liste_css)."']";
                                                        $liste_choix_count = count($liste_choix);

                                                        $html .= "<input type='hidden' name='$col_name' id='$col_name' value='$val'>
                                                                  <button type='button' id='btn_$col_name' class='toggle-hzm-btn $css_val' 
                                                                                onClick=\"toggleHzmBtn('$col_name', $liste_choix_text , $liste_codes_text,  
                                                                                                $listeOrdres_text , $liste_css_text, $liste_choix_count) \">
                                                                                                $display_val</button>";
                                                }
                                                else
                                                {
                                                        $type_input_ret = "select";
                                                        
                                                        if($desc["FORMAT-INPUT"]=="hzmsel")
                                                        {
                                                                $css_arr = $obj::afw_explode($desc["HZM-CSS"]);
                                                                $css_class = "selectpicker";//." ".$data_loaded_class.$data_length_class
                                                        }
                                                        else
                                                        {
                                                                $css_arr = null;
                                                                $css_class = "comm_select inputselect".$data_loaded_class.$data_length_class;
                                                        }   
                                                        
                                                        $info = array(
                                                                        "class" => "form-control",
                                                                        "name"  => $col_name,
                                                                        "id"  => $col_name,
                                                                        "tabindex" =>$qedit_orderindex,
                                                                        "onchange" => $onchange,
                                                                        "bsel_css" => [],
                                                                        "style" => $input_style,
                                                                        "required" => $is_required,

                                                                );
                                                        
                                                        //if(!in_array($val, $liste_rep)) $liste_rep[$val] = $val;
                                                        if(!$val) $val = 0;
                                                        if($desc["EMPTY_IS_ALL"]) $info["empty_item"] = $placeholder;
                                                        else $info["empty_item"] = "";
                                                        
                                                        
                                                        // to be shown it is not in list add it (and after see what's the problem) 
                                                        if(($val) and (!$liste_rep[$val])) $liste_rep[$val] = $val;
                                                        
                                                        $html .= self::drop_down(
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
                        case 'FLOAT'     :
                        case 'AMNT'     : 
                                        $input_type_html = "text";
                                        if($desc["TYPE"]=='INT')
                                        {
                                                $input_type_html = "number";
                                                $input_options_html = "";
                                                if($desc["FORMAT"])
                                                {
                                                        list($format_type,$format_param1,$format_param2,$format_param3) = explode(":",$desc["FORMAT"]);      // ex FORMAT=>"STEP:0:3:1"  or DROPDOWN=>"STEP:0:3:1"
                                                        if($format_type=="STEP")
                                                        {
                                                                if(!$format_param3) $format_param3 = 1;
                                                                $input_options_html = " step='$format_param3' min='$format_param1' max='$format_param2' ";
                                                        }
                                                        elseif($format_type=="DROPDOWN")
                                                        {
                                                                if(!$format_param3) $format_param3 = 1;
                                                                $dropdown_min = intval($format_param1);
                                                                $dropdown_max = intval($format_param2);
                                                                $dropdown_step = intval($format_param3);
                                                        }
                                                
                                                }
                                        }
                                        
                                        if($force_css) $data_length_class = " ".$force_css;
                                        else $data_length_class = " inputcourt";
                                        $type_input_ret = "text";
                                        $class_of_input = "inputtext inputtrescourt";
                                        if($desc["JS-COMPUTED"]) 
                                        {
                                                if($obj->class_of_input_computed_readonly) $class_of_input = $obj->class_of_input_computed_readonly;
                                                
                                                if($obj->class_js_computed) $class_js_computed = $obj->class_js_computed; 
                                                else $class_js_computed = "js_computed";
                                                
                                                $data_loaded_class = $class_js_computed;
                                        
                                        }
                                        
                                        if(true) 
                                        {
                                                if($input_type_html=="text")
                                                {      
                                                        return "<input type='text' tabindex='$qedit_orderindex' class='form-control' name='$col_name' id='$col_name' value='$val' size=6 maxlength=6 $readonly 
                                                                                onchange=\"$onchange \" placeholder=\"$placeholder\" $input_options_html $style_input $input_required >";
                                                }
                                                else
                                                {
                                                        if($format_type=="DROPDOWN")
                                                        {
                                                                $answer_list = array();
                                                                for($k=$dropdown_min; $k<=$dropdown_max; $k += $dropdown_step)
                                                                {
                                                                        $answer_list[$k] = $k; 
                                                                }
                                                        
                                                                $html .= self::drop_down(
                                                                                $answer_list,
                                                                                array($val),
                                                                                array(
                                                                                        "class" => "form-control hzm_numeric", 
                                                                                        "name"  => $col_name,
                                                                                        "id"  => $col_name,
                                                                                        "tabindex" =>$qedit_orderindex,
                                                                                        "onchange" => $onchange,
                                                                                        "style" => $input_style,
                                                                                        "required" => $is_required,
                                                                                ),
                                                                                "asc"
                                                                        );
                                                        }
                                                        else
                                                        {   
                                                                $html .= "<input type=\"$input_type_html\" tabindex='$qedit_orderindex' class='form-control hzm_numeric' name='$col_name' id='$col_name' value='$val' $input_options_html $input_required >";
                                                        }
                                                }
                                        }
                                        break;
                                        
                        case 'TIME'   :
                                if ($desc["FORMAT"] == "CLASS") 
                                {
                                        $helpClass = $desc["ANSWER_CLASS"];
                                        $helpMethod = $desc["ANSWER_METHOD"];

                                        $answer_list = $helpClass::$helpMethod();
                                }
                                elseif ($desc["FORMAT"] == 'OBJECT') {
                                        $helpMethod = $desc["ANSWER_METHOD"];
                                        $answer_list = $obj->$helpMethod();
                                } 
                                else
                                {
                                        if ($desc["ANSWER_LIST"]) 
                                        {
                                        list($start, $increment, $end) = explode("/", $desc["ANSWER_LIST"]);
                                        }
                                        else 
                                        {
                                        $start = 6;
                                        $increment = 30;
                                        $end = 22;
                                        }

                                        $answer_list = AfwDateHelper::getTimeArray($start, $increment, $end);
                                        
                                }
                                if(!$answer_list[$val]) $answer_list[$val] = $val;
                                // die(var_export($answer_list,true));
                                $html .= self::drop_down(
                                                                        $answer_list,
                                                                        array($val),
                                                                        array(
                                                                                "class" => "form-control hzm_time", 
                                                                                "name"  => $col_name,
                                                                                "id"  => $col_name,
                                                                                "tabindex" =>$qedit_orderindex,
                                                                                "onchange" => $onchange,
                                                                                "style" => $input_style,
                                                                                "required" => $is_required,
                                                                        ),
                                                                        "asc"
                                                                );
                                break;
                        case 'GDAT'   :
                                        $val_GDAT = substr($val,0,10);
                                        $html .= "<input placeholder=\"$placeholder\" type=\"text\" tabindex=\"$qedit_orderindex\" class=\"form-control\" name=\"$col_name\" id=\"$col_name\"  dir=\"$dir\" value=\"$val_GDAT\" size=10 maxlength=10 onchange=\"$onchange\" $input_style $input_required>";
                                break;
                        case 'TEXT'   : $utf8 = $desc["UTF8"];
                                        $dir = $desc["DIR"];
                                        if(!$dir) ($lang!="ar") ? $dir = "ltr" : $dir = "rtl";
                                        if($dir=="auto") $dir = ($utf8 ? "rtl":"ltr");
                                        if($desc["INPUT-FORMATTING"]=="addslashes") $val = addslashes($val);
                                        $css_class = $desc["CSS"];
                                        if((isset($desc["SIZE"])) && (($desc["SIZE"] == "AREA") or ($desc["SIZE"] == "AEREA")))
                                        {
                                                $rows = $desc["ROWS"];
                                                if(!$rows) $rows = 4;
                                                $cols = $desc["COLS"];
                                                if(!$cols) $cols = 43;
                                                $type_input_ret = "text";
                                                if((!$desc["MANDATORY"]) and (!$desc["REQUIRED"]))
                                                {
                                                $desc["MIN-SIZE"] = 0;
                                                }
                                                
                                                if($desc["MIN-SIZE"] == 1) $desc["MIN-SIZE"] = 0;
                                                
                                                if(!$desc["PLACEHOLDER-NO-CHANGE"])
                                                {
                                                        if(($desc["MIN-SIZE"]) and ($desc["MAXLENGTH"]))
                                                        {
                                                        if($placeholder) $placeholder .= " : ";
                                                        $placeholder .= "عدد الكلمات  بين " . $desc["MIN-SIZE"] . " و " . $desc["MAXLENGTH"] . " كلمة";
                                                        }
                                                        elseif($desc["MIN-SIZE"])
                                                        {
                                                        if($placeholder) $placeholder .= " : ";
                                                        $placeholder .= "عدد الكلمات  الأدنى " . $desc["MIN-SIZE"] . " كلمة";
                                                        }
                                                        elseif($desc["MAXLENGTH"])
                                                        {
                                                        if($placeholder) $placeholder .= " : ";
                                                        $placeholder .= "عدد الكلمات  الأقصى " . $desc["MAXLENGTH"] . " كلمة";
                                                        }
                                                }        
                                                $html .= "<textarea placeholder=\"$placeholder\" class=\"form-control $css_class\" cols=\"$cols\" rows=\"$rows\" id=\"$col_name\" name=\"$col_name\" dir=\"$dir\" onchange=\"$onchange\" $input_style $input_required >$val</textarea>";
                                        }        
                                        else
                                        {
                                                $maxlength = $desc["MAXLENGTH"];
                                                $fld_size = $desc["SIZE"];  
                                                
                                                if($desc["INPUT-FORMATTING"]=="value-1-cote") $val_sentence = "value='$val'";
                                                else $val_sentence = "value=\"$val\""; 

                                                if(($force_css) and (!$desc["WIDTH-FROM-SIZE"])) $data_length_class = " ".$force_css;
                                                else if(isset($desc["SIZE"]) && $desc["SIZE"] <= 16)  $data_length_class = " inputcourt";
                                                else if(isset($desc["SIZE"]) && $desc["SIZE"] <= 41)  $data_length_class = " inputmoyen";
                                                else if(isset($desc["SIZE"]) && $desc["SIZE"] <= 84)  $data_length_class = " inputlong";
                                                else if(isset($desc["SIZE"]) && $desc["SIZE"] < 255)  $data_length_class = " inputtreslong";
                                                else $data_length_class = " inputultralong";
                                                $type_input_ret = "text";  
                                                $html .= "<input placeholder=\"$placeholder\" type=\"text\" tabindex=\"$qedit_orderindex\" class=\"form-control $css_class\" name=\"$col_name\" id=\"$col_name\"  dir=\"$dir\" $val_sentence size='$fld_size' maxlength='$maxlength' onchange=\"$onchange\" $input_style $input_required >";
                                        }
                                        break;
                        case 'YN'     :
                                        if($force_css) $data_length_class = " ".$force_css;
                                        else $data_length_class = "";

                                        $remove_options_arr = $desc["REMOVE_OPTIONS"];

                                        if(!$remove_options_arr["Y"]) $this_yes_label = $obj->showYNValueForAttribute("YES", $col_name, $lang);
                                        if(!$remove_options_arr["N"]) $this_no_label  = $obj->showYNValueForAttribute("NO", $col_name, $lang);
                                        if(!$remove_options_arr["W"]) $this_dkn_label = $obj->showYNValueForAttribute("EUH", $col_name, $lang);

                                        if(!$remove_options_arr["Y"]) $answer_list["Y"] = $this_yes_label;
                                        if(!$remove_options_arr["W"]) $answer_list["W"] = $this_dkn_label;
                                        if(!$remove_options_arr["N"]) $answer_list["N"] = $this_no_label;
                                        
                                        
                                        if(isset($desc["ANSWER"]) && !empty($desc["ANSWER"]))
                                        {
                                                $temp_answer_val = explode('|', $desc["ANSWER"]);
                                                if(count($temp_answer_val) == 3)
                                                {
                                                        if(!$remove_options_arr["Y"]) $answer_list["Y"] = $temp_answer_val[0];
                                                        if(!$remove_options_arr["N"]) $answer_list["N"] = $temp_answer_val[1];
                                                        if(!$remove_options_arr["W"]) $answer_list["W"] = $temp_answer_val[2];
                                                }
                                        }
                                        $type_input_ret = "select";
                                        
                                        if($desc["CHECKBOX"])  
                                        {
                                                if($val=="Y") $checkbox_checked = "checked";
                                                else $checkbox_checked = "";
                                        
                                                $checkbox_extra_class = $desc["CHECKBOX_CSS_CLASS"];               
                                                $html .= "<div class='form-control'><input type='checkbox' value='1'  id='$col_name' name='$col_name' $checkbox_checked class='echeckbox $checkbox_extra_class></div>";
                                        }
                                        else 
                                        {
                                                $html .= self::drop_down(
                                                                $answer_list,
                                                                array($val),
                                                                array(
                                                                        "class" => "form-control", 
                                                                        "name"  => $col_name,
                                                                        "id"  => $col_name,
                                                                        "tabindex" =>$qedit_orderindex,
                                                                        "onchange" => $onchange,
                                                                        "style" => $input_style,
                                                                        "required" => $is_required,
                                                                        
                                                                ),
                                                                "asc"
                                                        );
                                        }
                                        
                                        break;
                        case 'DATE'   :
                                        $mode_hijri_edit = true;
                                        $type_input_ret = "text";
                                        $input_name = $col_name;
                                        $valaff = AfwDateHelper::displayDate($val);
                                        if($valaff)
                                                $valaff_n = "الموافق لـ ".AfwDateHelper::hijriToGreg($valaff)." نـ";
                                        else     
                                                $valaff_n = "";
                                        $html .= "<input placeholder='$placeholder' type=\"text\" id=\"$input_name\" name=\"$col_name\" value=\"$valaff\" class=\"form-control\" onchange=\"$onchange \" $input_style $input_required >
                                                <script type=\"text/javascript\">
                                                $('#$input_name').calendarsPicker({calendar: $.calendars.instance('UmmAlQura')});
                                                </script>";
                                	break;
                        default       :
                                        $type_input_ret = "text";   
                                        $html .= "<input placeholder='$placeholder' type=\"text\" tabindex=\"$qedit_orderindex\" class=\"form-control\" name=\"$col_name\" id=\"$col_name\" value=\"$val\" size=32 maxlength=255  onchange=\"$onchange \" $input_style $input_required >";
                			break;
                }
                
                return array($html, $type_input_ret);
        }

        // list id val can be afw objects or strings
        public static function drop_down($list_id_val, $selected = array(), $info = array(), $sort_order = "", $null_val = true, $langue="", $data_images=null)
        {
                global $lang;
                if(!$langue) $langue = $lang;


                if(!$list_id_val) $list_id_val = array();

                // @todo not all time should be well studied
                // if(count($list_id_val)==0) return;

                
                if($sort_order)
                {
                        $list_val = array();
                        foreach ($list_id_val as $id => $val)
                        {
                                if($val instanceof AFWObject) $list_val[$id] = $val->getDropDownDisplay($langue);
                                else $list_val[$id] = $val;
                        }
                        $sort_order = strtolower($sort_order);			
                        $list_id_val = self::subval_sort($list_id_val, $list_val, "asc");
                }
                
                $multi = "";
                if(isset($info["multi"]) && $info["multi"]) $multi = " multiple";
                $size = 1;
                if(isset($info["size"])) $size = intval($info["size"]);
                $count = count($list_id_val);
                if(!empty($multi) && $count < $size) $size = $count;
                if(!$info["id"]) $info["id"] = trim(trim($info["name"],"]"),"[");
                
                if(!$info["empty_item"]) $info["empty_item"] = "&nbsp;";
                
                $info["onchange"] .= $info["name"]."_onchange()";                  

                if($info["disable"]) $info["disable"] = "disabled"; else $info["disable"] = "";
                if($info["required"]) $info["required"] = "required"; else $info["required"] = "";
                if($null_val)
                {
                        if($info["required"])
                        {
                                if(!$info["default_value"]) $option_empty_html = "<option></option>";
                        }
                        else
                        {
                                $empty_selected = (in_array(0, $selected))? " selected" : "";
                                $option_empty_html = "<option value=\"0\" $empty_selected >".$info["empty_item"]."</option>";
                        }
                }
                $input_id = $info["id"];
                $is_required = ($info["required"]) ? "required='required'" : "";
                $is_readonly = ($info["readonly"]) ? "disabled='true'" : "";
                $is_readonly_badil = ($info["readonly"]) ? "<input type=\"hidden\" name='".$info["name"]."' value='".$selected[0]."'/>" : "";
                $name_ext = ($info["readonly"]) ? "ro_afw_select" : "";
                $html = "<script>\n\n".$info["reloadfn"]."
                        // rafik @todo check why I put this below I now disabled it
                        // disabled :
                        // echo ".$info["onchange"]."\n\n
                </script>                
                <select
                        class='".$info["class"]."'
                        name='".$info["name"].$name_ext."'
                        id='$input_id'
                        tabindex='".$info["tabindex"]."'
                        onchange=\"".$info["onchange"]."\"
                        $multi 
                        size='$size'
                        ".$info["style"]."
                        ".$info["disabled"]."
                        ".$is_required."
                        ".$is_readonly."
                        
                        >
                $option_empty_html";

                $data_content = "";
                if(count($list_id_val)>7)
                {
                        $info["enableFiltering"] = true;
                        $info["numberDisplayed"] = 3;
                        $info["filterPlaceholder"] = "اكتب كلمة للبحث";                        
                }
                
                foreach ($list_id_val as $id => $val)
                {
                        $item_selected = (in_array($id, $selected)) ? " selected" : "";
                        if($info["bsel_css"])
                        {
                                $opt_css = $info["bsel_css"][$id];
                                $data_content = "data-content=\"<span class='$opt_css'>$val</span>\"";
                        }
                        if($data_images)
                        {
                                $data_image_html = "";
                                if(is_array($data_images))
                                {
                                        $data_images_arr = $data_images;
                                        $data_image_html = "data-image='".$data_images_arr[$id]."'";
                                }
                                else
                                {
                                        $data_image_html = "data-image='".$val->getMyPicture()."'";
                                }
                        }
                        if($val instanceof AFWObject) $val_option = $val->getDropDownDisplay($langue);
                        else $val_option = $val;

                        $html .= "<option id='$input_id"."_$id' value=\"$id\" $item_selected $data_content $data_image_html >$val_option</option>";
                }
        
                $html .= "</select>" . $is_readonly_badil;

                $multi_select_options = "";
                if($info["numberDisplayed"]) $multi_select_options .= " numberDisplayed: '".$info["numberDisplayed"]."',";
                if($info["buttonWidth"]) $multi_select_options .= " buttonWidth: '".$info["buttonWidth"]."',";
                if($info["dropRight"]) $multi_select_options .= " dropRight: true,";
                if($info["inheritClass"]) $multi_select_options .= " inheritClass: true,";
                if($info["enableFiltering"]) $multi_select_options .= " enableFiltering: true,";
                if($info["filterBehavior"]) $multi_select_options .= " filterBehavior: '".$info["filterBehavior"]."',";
                if($info["filterPlaceholder"]) $multi_select_options .= " filterPlaceholder: '".$info["filterPlaceholder"]."',";
                if($info["maxHeight"]) $multi_select_options .= " maxHeight: ".$info["maxHeight"].",";
                if($info["includeSelectAllOption"]) $multi_select_options .= " includeSelectAllOption: true,";

                if($multi)
                {
                        $html .= "<!-- Initialize the plugin: -->
        <script type=\"text/javascript\">
        $(document).ready(function() {
                $('#".$info["id"]."').multiselect(
                        {
                        inheritClass: true,
                        
                        $multi_select_options        
                        }
                );
        });
        </script>";        
                }
                elseif($data_images)
                {
                        $id_input = $info["id"];
                        $html .= "<script>
$(\"#$id_input\").msDropdown({roundedBorder:false, visibleRows:4, rowHeight:90});
$(\"#$id_input\").data(\"dd\");
</script>"; 
                }

                return $html;
                // if($info["id"]=="presence_mfk") die("html of $id => ".$html);
        }
        
        
        public static function picture_dropdown($list_id_val, $name, $selected, $id, $data_images=true, $width=250, $css="", $sort_order = "", $null_val = true, $langue="", $required=null, $onchange=null, $props = array())
        {
                if($width>0) $props["style"]="style='width:${width}px'";
                $props["class"] = $css;
                $props["name"] = $name;
                $props["id"] = $id;
                $props["onchange"] = $onchange;
                $props["required"] = ((!$null_val) or $required);
                //if($data_images) self::safeDie("rafik here dd data_images = ".var_export($data_images,true));

                return self::drop_down($list_id_val, $selected, $props, $sort_order, $null_val, $langue, $data_images);
        }


        public static function subval_sort($table_a_trie, $table_ref, $ord = "desc")
        {
                $res = array();
                if($ord == "asc")
                        asort($table_ref);
                else
                        arsort($table_ref);
                foreach($table_ref as $key => $val)
                        $res[$key] = $table_a_trie[$key];
                return $res;
        }

        public static function inputErrorsInRequest($attribute, $data_error, $suffix="_error")
        {
                $error_message = $data_error[$attribute.$suffix];
                if($error_message) return "<div id=\"attr_error_$attribute\" class=\"$attribute front error\" for=\"$attribute\">$error_message</div>";


                return "";
        }
}