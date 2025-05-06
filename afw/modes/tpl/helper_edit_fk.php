<?php
$objRep  = new $nom_class_fk;

$list_count = AfwSession::config("$nom_class_fk::estimated_row_count", 0);

$auto_c = $desc["AUTOCOMPLETE"];

$LIMIT_INPUT_SELECT = AfwSession::config("LIMIT_INPUT_SELECT", 20);
$auto_complete_default = ((!isset($desc["AUTOCOMPLETE"])) and ($list_count > $LIMIT_INPUT_SELECT));
if ((!$auto_c)  and (!$auto_complete_default)) 
{
    if(!$desc['ORDERBY']) $desc['ORDERBY'] = $objRep->ORDER_BY_FIELDS;
    $l_rep = AfwLoadHelper::vhGetListe($objRep, $col_name, $obj->getTableName(), $desc["WHERE"], $action="loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);
    if ($desc["FORMAT-INPUT"] == "btn-bootstrap") 
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
            // throw new AfwRuntimeE xception("For btn-bootstrap `NB-CSS`+`OFFSET-CSS` should be less or equal than cout of btn-bootstrap classes = ".count($arr_classes));
        }
?>
        <input type='hidden' name='<?php echo $col_name ?>' id='<?php echo $col_name ?>' value='<?php echo $val ?>'>
<?php
        $c=0;
        foreach($l_rep as $val_i => $title_i)
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
        // list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc, $val, $obj, true);
        // $l_rep = AfwHtmlHelper::constructDropDownItems($liste_rep, $lang, $col_name, "$mdl.$myTbl", var_export($desc,true));
        
        $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;        
        //if(get_class($objRep)=="Module")    die("AfwLoadHelper::vhGetListe=>".var_export($l_rep,true));
        //list($mdl, $myTbl) = $obj->getThisModuleAndAtable();
        // if($col_name=="data_auser_mfk") die("<b> => desc = ".var_export($desc,true));
        // die("<b> => l_rep = ".var_export($l_rep,true)."</b><BR> liste_rep = ".var_export($liste_rep,true));
        // $liste_rep_count = count($liste_rep);
        $l_rep_count = count($l_rep);
        if ($objme and $objme->isAdmin()) echo "<!-- for $col_name : $sql dropdowncount=$l_rep_count -->";

        if ($placeholder != $col_title) {
            $empty_item = $placeholder;
        } else {
            $empty_item = "";
        }

        $prop_sel =
            array(
                "class" => "form-control form-select",
                "name"  => $col_name,
                "id"  => $col_name,
                "tabindex" => $qedit_orderindex,
                "style" => $input_style,
                "empty_item" => $empty_item,
                "reloadfn" => AfwJsEditHelper::getJsOfReloadOf($obj, $col_name),
                "loadmyprops" => AfwJsEditHelper::getJsOfLoadMyProps($obj, $col_name),
                "onchange" => $onchange . AfwJsEditHelper::getJsOfOnChangeOf($obj, $col_name),
                "onchangefn" => AfwJsEditHelper::getJsOfOnChangeOf($obj, $col_name, $descr = "", false),
                "required" => $is_required,
                "disabled" => $disabled,
            );
            
        if(!$desc["DEPENDENT_OFME"]) unset($prop_sel["onchangefn"]);

        if ($obj->fixm_disable) 
        {
            $descHid = array();
            if (!$obj->hideQeditCommonFields) $descHid["TITLE_AFTER"] = $l_rep[$val];
            $type_input_ret = hidden_input($col_name, $descHid, $val, $obj);
        
        } 
        else 
        {
            select(
                $l_rep,
                array($val),
                $prop_sel
            );
            $type_input_ret = "select";
        }
    }
} 
else 
{
    $type_input_ret = "autocomplete";
    $col_name_atc = $col_name . "_atc";
    if (($val)) // and ((!$obj->fixm_disable) or (!$obj->fixmtit))) 
    {
        $objRep->load($val);
        $val_display = $objRep->getDisplay($lang);
    } else {
        $val_display = "";
    }
    //$clwhere = $desc["WHERE"];
    $attp = $col_name;
    $clp = $obj->getMyClass();
    $idp = $obj->getId();
    $modp = $obj->getMyModule();
    $auto_c_create = $auto_c["CREATE"];
    $atc_input_normal = $data_loaded_class . " inputlongmoyen";

    if ($auto_c_create) {
        $class_icon = "new";
        $atc_input_modified_class = $data_loaded_class . $data_length_class . " new_record";
    } else {
        $class_icon = "notfound";
        $atc_input_modified_class = $data_loaded_class . $data_length_class . " record_not_found";
    }

    if ($obj->fixm_disable) 
    {
        $descHid = array();
        if (!$obj->hideQeditCommonFields) $descHid["TITLE_AFTER"] = "[$val_display]";
        $type_input_ret = hidden_input($col_name, $descHid, $val, $obj);
    } 
    else 
    {
        $help_atc = $auto_c["HELP"];
        $depend = AfwJsEditHelper::getDependencyIdsArray($obj, $col_name, $desc);
        if(!$depend) $depend = "0";
    ?>
        <div class='hzm_input_atc'>
            <table cellspacing='0' cellpadding='0' style="width:100%">
                <tr style="background-color: rgba(255, 255, 255, 0);">
                <?php
                    if(!$placeholder) $placeholder = "اكتب بعض الكلمات للبحث";
                ?>
                    <td style="padding:0px;margin:0px;background-color: rgba(255, 255, 255, 0);"><input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" value="<?php echo $val ?>" readonly></td>
                  
                    <td style="padding:0px;margin:0px;">
                        <input placeholder="<?php echo $placeholder ?>" type="text" id="<?php echo $col_name_atc ?>" name="<?php echo $col_name_atc ?>" class="form-control form-autoc" value="<?php echo $val_display ?>" <?php echo $input_required ?>>
                    </td>
                    <?
                    if ($auto_c_create) {
                    ?>
                        <th style="padding:0px;margin:0px;"><img src='../lib/images/create_new.png' data-toggle="tooltip" data-placement="top" title='لإضافة عنصر غير موجود في القائمة (بعد التثبت) انقر هنا ثم اكتب المسمى' onClick="empty_atc('<?php echo $col_name ?>');" style="width: 24px !important;height: 24px !important;" /></th>
                    <?
                    }
                    ?>
                    <td style="padding:0px;margin:0px;"><?php echo $help_atc ?></td>
                </tr>
            </table>
        </div>
        <script>
            $(function() {
                $("#<?php echo $col_name_atc ?>").autocomplete({
                    source: "../lib/api/autocomplete.php?cl=<?php echo $nom_class_fk ?>&currmod=<?php echo $nom_module_fk ?>&clp=<?php echo $clp ?>&idp=<?php echo $idp ?>&modp=<?php echo $modp ?>&attp=<?php echo $attp ?>&depend="+<?php echo $depend ?>,
                    minLength: 0,

                    change: function(event, ui) {
                        if ($("#<?php echo $col_name_atc ?>").val() == "") {
                            $("#<?php echo $col_name ?>").val("");
                        }
                        // $("#<?php echo $col_name_atc ?>").addClass('value_not_found');
                        // $("#<?php echo $col_name ?>").val("");
                        // $("#<?php echo $col_name ?>").attr('class', 'inputtrescourt cl_<?php echo $class_icon ?>_id');
                        // $("#<?php echo $col_name_atc ?>").attr('class', '<?php echo $atc_input_modified_class ?>');
                    },


                    select: function(event, ui) {
                        //alert(ui.item.id);
                        $("#<?php echo $col_name ?>").val(ui.item.id);
                        $("#<?php echo $col_name ?>").attr('class', 'inputtrescourt cl_id');
                        $("#<?php echo $col_name_atc ?>").attr('class', 'form-control form-autoc');
                        $("#<?php echo $col_name_atc ?>").addClass('input_changed');
                    },

                    html: true, // optional (jquery.ui.autocomplete.html.js required)

                    // optional (if other layers overlap autocomplete list)
                    open: function(event, ui) {
                        $(".ui-autocomplete").css("z-index", 1000);
                    }
                });

            });

            $("#<?php echo $col_name_atc ?>").keypress(function(){
                $("#<?php echo $col_name ?>").val("");
            });

            $("#<?php echo $col_name_atc ?>").blur(function(){
                if($("#<?php echo $col_name ?>").val()=="")
                {
                    $("#<?php echo $col_name_atc ?>").val("");
                }
            });

            
        </script>

<?php                    
    }
}    
        
