<?php
$type_input_ret = "select";
if ($obj->fixm_disable) 
{

    $type_input_ret = "hidden";
?>
    <input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" value="<?php echo $val ?>">
    <span><? if (!$obj->hideQeditCommonFields) echo $answer_list[$val] ?></span>
<?php
}
elseif ($desc["SWITCHER"]) 
{
    $onoff = ($val=="Y") ? "on" : "off";
    if($desc["READONLY"]) $switcher_img_class = "";
    else $switcher_img_class = "switcher-btn";
    $switcher_img = "<img class='$switcher_img_class' for='$col_name' id='img-$col_name' src='../lib/images/$onoff.png' width='48' heigth='32'>";                                                
?>
    <div class='form-control form-switcher'>
        <input type="hidden" value="<?php echo $val ?>" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" <?php echo $checkbox_checked ?>>
        <?php echo $switcher_img ?>
    </div>
<?php
} 
elseif ($desc["CHECKBOX"]) 
{
    if ($val == "Y") $checkbox_checked = "checked";
    else $checkbox_checked = "";

    $checkbox_extra_class = $desc["CHECKBOX_CSS_CLASS"];
?>
    <div class='form-control form-ckbox'><input type="checkbox" value="1" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" <?php echo $checkbox_checked ?> class="echeckbox <?php echo $checkbox_extra_class ?>"></div>
<?php
} 
else 
{
    AfwEditMotor::select(
        $answer_list,
        array($val),
        array(
            "class" => "form-control form-yn",
            "name"  => $col_name,
            "id"  => $col_name,
            "tabindex" => $qedit_orderindex,
            "onchange" => $onchange,
            "style" => $input_style,
            "required" => $is_required,
            "disabled" => $disabled,
        ),
        "asc"
    );
}