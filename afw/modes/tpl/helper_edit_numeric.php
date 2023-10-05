<?php
            if ($obj->fixm_disable) 
            {
                $type_input_ret = "hidden";
?>
                <input type="hidden" id="<?php echo $col_name ?>" name="<?php echo $col_name ?>" value="<?php echo $val ?>">
                <span><? if (!$obj->hideQeditCommonFields) echo $val ?></span>
<?php
            } 
            else 
            {
                if ($input_type_html == "text") 
                {
?>
                    <input type="text" tabindex="<?php echo $qedit_orderindex ?>" class="form-control" name="<?php echo $col_name ?>" id="<?php echo $col_name ?>" value="<?php echo $val ?>" size=6 maxlength=6 <?php echo $readonly ?> onchange="<?php echo $onchange ?>" placeholder="<?php echo $placeholder ?>" <?php echo $input_options_html . " " . $style_input ?> <?php echo $input_required ?>>
<?php
                } 
                else 
                {
                    if ($format_type == "DROPDOWN") {
                        $answer_list = array();
                        for ($k = $dropdown_min; $k <= $dropdown_max; $k += $dropdown_step) {
                            $answer_list[$k] = $k;
                        }

                        select(
                            $answer_list,
                            array($val),
                            array(
                                "class" => "form-control hzm_numeric",
                                "name"  => $col_name,
                                "id"  => $col_name,
                                "tabindex" => $qedit_orderindex,
                                "onchange" => $onchange,
                                "style" => $input_style,
                                "required" => $is_required,
                            ),
                            "asc"
                        );
                    } 
                    else 
                    {
?>
                        <input type="<?php echo $input_type_html ?>" tabindex="<?php echo $qedit_orderindex ?>" class="form-control hzm_numeric" name="<?php echo $col_name ?>" id="<?php echo $col_name ?>" value="<?php echo $val ?>" <?php echo $input_options_html ?> <?php echo $input_required ?>>
<?php
                    }
                }
            }