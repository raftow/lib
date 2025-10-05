<?php
    if ($desc["TITLE_BEFORE"]) 
    {
?>
        <div class='title_before title_<?php echo $col_name; ?>'><? echo $obj->tm($desc["TITLE_BEFORE"]) ?></div>
<?php
    }
?>
    <input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" value="<?php echo $val ?>">
<?php
    if ($desc["TITLE_AFTER"]) 
    {
?>
    <span><?php echo $desc["TITLE_AFTER"] ?></span>
<?php
    }
?>

