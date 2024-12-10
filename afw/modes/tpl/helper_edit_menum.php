<?php
if ($desc["CHECKBOX"]) 
{
    foreach($liste_rep as $repId => $repTitle)
    {
        if (in_array($repId, $val_arr)) $checkbox_checked = "checked";
        else $checkbox_checked = "";

        $checkbox_css_class = $desc["CHECKBOX_CSS_CLASS"];
        $size_css_class = $desc["SIZE_CSS_CLASS"];
?>
    <div class='form-control form-ckbox <?php echo $size_css_class ?>'>
        <input type="checkbox" value="<?php echo $repId ?>" name="<?php echo $col_name ?>[]" id="<?php echo $col_name."_".$repId ?>" <?php echo $checkbox_checked ?> class="echeckbox <?php echo $checkbox_css_class ?>">
        <label class='label_for_mchk' id="label_for_<?php echo $col_name."_".$repId ?>"><?php echo $repTitle ?></label>
    </div>
<?php
    }
}
else
{
    $infos_arr = array(
        "class" => "form-control form-menum",
        "name"  => $col_name . "[]",
        "id"  => $col_name,
        "size"  => 5,
        "multi" => true,
        "tabindex" => $qedit_orderindex,
        "onchange" => $onchange,
        "style" => $input_style,
        "disabled" => $disabled,

    );
    if ($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr, $desc["SEL_OPTIONS"]);


    if ($desc["FORMAT"]=="dropdown")
    {
        select(
            $liste_rep,
            $val_arr,
            $infos_arr,
            "",
            false
        );
    }
    else
    {
        mobiselector(
            $liste_rep,
            $val_arr,
            $infos_arr
        );
    }

    
}