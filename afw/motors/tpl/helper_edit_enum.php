<?php

            if($obj->fixm_disable) 
            {
                $type_input_ret = "hidden";
?>
                <input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" value="<?php echo $val ?>">
                <span><? if (!$obj->hideQeditCommonFields) echo $liste_rep[$val] ?></span>
<?php
            } 
            else 
            {
                if(!$desc["FORMAT-INPUT"]) $desc["FORMAT-INPUT"] = $desc["FORMAT"];
                if ($desc["FORMAT-INPUT"] == "hzmtoggle") 
                {
                    $display_val = $liste_rep[$val];
                    if (!$display_val) $display_val = "...<!-- $val from ".var_export($liste_rep,true)." -->";
                    $css_arr = AfwStringHelper::afw_explode($desc["HZM-CSS"]);
                    $css_val = $css_arr[$val];



                    $liste_choix = array();
                    $liste_css = array();
                    $liste_codes = array();
                    $liste_codeOrdres = array();
                    $listeOrdres = array();

                    $log_echo = "log of hzm enum toggle : ";
                    $log_echo .= "<br>\n liste_rep = " . var_export($liste_rep, true);
                    $max_rep_id = 0;
                    $oord = 0;
                    foreach ($liste_rep as $rep_id => $rep_val) 
                    {
                        if($rep_val) 
                        {
                            $liste_choix[$oord] = $rep_val;
                            $liste_codes[$oord] = $rep_id;
                            $liste_codeOrdres[$rep_id] = $oord;
                            if ($max_rep_id < $rep_id) $max_rep_id = $rep_id;
                            $liste_css[$oord] = $css_arr[$rep_id];
                            $log_echo .= "<br>\n $rep_id => $rep_val , " . var_export($liste_css, true);
                            $oord++;
                        }
                    }

                    for ($rep_i = 0; $rep_i <= $max_rep_id; $rep_i++) 
                    {
                        if (!isset($liste_codeOrdres[$rep_i])) $listeOrdres[$rep_i] = -1;
                        else $listeOrdres[$rep_i] = $liste_codeOrdres[$rep_i];
                    }

                    //if($col_name=="coming_status_id_0") $obj->_error($log_echo);
                    if (!$css_val) $css_val = $desc["DEFAULT-CSS"];
                    if (!$css_val) $css_val = $liste_css[0];

                    $liste_choix_text = "['" . implode("','", $liste_choix) . "']";
                    $liste_codes_text = "['" . implode("','", $liste_codes) . "']";
                    $listeOrdres_text = "['" . implode("','", $listeOrdres) . "']";

                    $liste_css_text = "['" . implode("','", $liste_css) . "']";
?>
                    <input type='hidden' name='<?php echo $col_name ?>' id='<?php echo $col_name ?>' value='<?php echo $val ?>'>
                    <button type='button' id="btn_<?php echo $col_name ?>" 
                                class='toggle-hzm-btn <?php echo $css_val ?>"  <?php echo $input_disabled ?> 
                                onClick="toggleHzmBtn('<?php echo $col_name ?>', <?php echo $liste_choix_text ?>, <?php echo $liste_codes_text ?>, <?php echo $listeOrdres_text ?>, <?php echo $liste_css_text ?>,<?php echo count($liste_choix) ?>)"><?php echo $display_val ?></button>
<?php
                } 
                elseif ($desc["FORMAT-INPUT"] == "stars") 
                {
?>
                    <input type='hidden' name='<?php echo $col_name ?>' id='<?php echo $col_name ?>' value='<?php echo $val ?>'>
                    <div class="stars-list answers-list" aria-hidden="true">
<?php
                    $c=0;
                    foreach($liste_rep as $val_i => $title_i)
                    {
                        // star-rated star-rated-on
?>
                        <div class="star-<?php echo $val_i ?> star-rating star " data-inputname="<?php echo $col_name ?>" data-star="<?php echo $val_i ?>" title="<?php echo $title_i ?>"><i class="fa fa-star ri-star-fill"></i></div>
<?php
                    }
?>
                    <div id="rating-label-<?php echo $col_name ?>" class="rating-label">---</div>
                    </div>
<?php
                }
                elseif ($desc["FORMAT-INPUT"] == "btn-bootstrap") 
                {
                    $arr_classes = ["primary","secondary","success","danger","warning","info","light","dark",];
                    $js_classes = "['".implode("','", $arr_classes)."']";
                    $nb_classes = $desc["NB-CSS"];
                    $offset_classes = $desc["OFFSET-CSS"];
                    if(!$offset_classes) $offset_classes = 0;
                    if(!$nb_classes) $nb_classes = 1;//count($arr_classes)-$offset_classes;
                    if(($nb_classes+$offset_classes)>count($arr_classes))
                    {
                        echo "For btn-bootstrap `NB-CSS`+`OFFSET-CSS` should be less or equal than cout of btn-bootstrap classes = ".count($arr_classes);
                        // throw new AfwRuntim eException("For btn-bootstrap `NB-CSS`+`OFFSET-CSS` should be less or equal than cout of btn-bootstrap classes = ".count($arr_classes));
                    }
?>
                    <input type='hidden' name='<?php echo $col_name ?>' id='<?php echo $col_name ?>' value='<?php echo $val ?>'>
<?php
                    $c=0;
                    foreach($liste_rep as $val_i => $title_i)
                    {
                        $css_name = $arr_classes[$c+$offset_classes];
                        $btn_off = ($val==$val_i) ? "" : "btn-off";
?>
                        <button type='button' id="btsp_btn_<?php echo $col_name."_".$val_i ?>" 
                                 class='btn btn-enum col-<?php echo $col_name?> btn-<?php echo $css_name." ".$btn_off?>' 
                                 <?php echo $input_disabled ?> 
                                 onClick="bootstrapHzmBtn('<?php echo $col_name ?>', '<?php echo $val_i ?>', <?php echo $js_classes ?>)"><?php echo $title_i?></button>
<?php
                        $c++;
                        $c = $c % $nb_classes;
                    }    
                }
                else
                {
                    $type_input_ret = "select";

                    if ($desc["FORMAT-INPUT"] == "hzmsel") {
                        $css_arr = AfwStringHelper::afw_explode($desc["HZM-CSS"]);
                        $css_class = "selectpicker"; //." ".$data_loaded_class.$data_length_class
                    } else {
                        $css_arr = null;
                        $css_class = $class_inputSelect . $data_loaded_class . $data_length_class;
                    }

                    $info = array(
                        "class" => "form-control form-enum",
                        "name"  => $col_name,
                        "id"  => $col_name,
                        "tabindex" => $qedit_orderindex,
                        "onchange" => $onchange,
                        "bsel_css" => [],
                        "style" => $input_style,
                        "required" => $is_required,
                        "disabled" => $disabled,
                    );

                    //if(!in_array($val, $liste_rep)) $liste_rep[$val] = $val;
                    if (!$val) $val = 0;
                    if ($desc["EMPTY_IS_ALL"]) $info["empty_item"] = $placeholder;
                    else $info["empty_item"] = "";


                    // to be shown it is not in list add it (and after see what's the problem) 
                    if (($val) and (!$liste_rep[$val])) $liste_rep[$val] = $val;

                    AfwEditMotor::select(
                        $liste_rep,
                        array($val),
                        $info,
                        ""
                    );
                }
            }