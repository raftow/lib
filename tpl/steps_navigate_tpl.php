        <div class="body_nav_front">     
                <p>
                        <input type="hidden" name="current_step" id="current_step" value="<?php echo $current_step ?>">
                        <?php
                                if(!$save_previous_title) $save_previous_title = "سابق";                                   
                                if(!$save_next_title) $save_next_title = "استمر";
                                if($current_step<=0) 
                                {
                                        $save_previous_disabled = "disabled"; 
                                        $class_btn_prev = "graybtn";
                                }
                                else
                                {
                                        $save_previous_disabled = ""; 
                                        $class_btn_prev = "blightbtn";
                                }

                                if($current_step>=$max_steps) 
                                {
                                        $save_next_disabled = "disabled"; 
                                        $class_btn_next = "graybtn";
                                }
                                else
                                {
                                        $save_next_disabled = ""; 
                                        $class_btn_next = "greenbtn";
                                }

                                if(!$save_previous_hidden)
                                {
                        ?>                                
                        <input type="submit" name="save_previous" id="submit-form" class="<?=$class_btn_prev?> wizardbtn fright" value="&nbsp;      < <?php echo $save_previous_title ?> &nbsp;" style="margin-right: 5px;" <?php echo $save_previous_disabled ?>>
                        <?php
                                }

                                if(!$save_next_hidden)
                                {
                        ?>
                        <input type="submit" name="save_next"     id="submit-form" class="<?=$class_btn_next?> wizardbtn fleft" value="&nbsp;      <?php echo $save_next_title ?> >&nbsp;"       style="margin-right: 5px;"  <?php echo $save_next_disabled ?>
                        <?php
                                }
                        ?>
                </p>
        </div>