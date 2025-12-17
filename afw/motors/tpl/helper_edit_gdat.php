<input placeholder="<?php echo $placeholder ?>" type="text" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" class="form-control <?php echo $lang_input ?> hasCalendarsPicker" tabindex="<?php echo $qedit_orderindex ?>" value="<?php echo $val_GDAT ?>" onchange="<?php echo $onchange ?>" <?php echo $input_style ?> <?php echo $input_required ?> <?php echo $input_disabled ?> autocomplete="off">
<?php
if(!$col_name) $col_name = "XXX";
            $js_cal_script = "
<script>
    \$(document).ready(function() {
        \$(\"#$col_name\").datepicker({ 
                showAnim: \"fold\",
                dateFormat: \"yy-mm-dd\",
                changeMonth: true,
                changeYear: true,
                minDate: $min_date,
        " . AfwEditMotor::calendar_translations($lang) . "
                });
        });
</script>
        ";
            echo $js_cal_script;