            <!--
                        <table class="table_no_border"><tr class="table_no_border_tr">
                        <td>
                        -->
            <input placeholder="<?php echo $placeholder ?>" type="text" id="<?php echo $input_name ?>" name="<?php echo $col_name ?>" value="<?php echo $valaff ?>" class="form-control <?php echo $lang_input ?> form-date" onchange="<?php echo $onchange ?>" <?php echo $input_style ?> <?php echo $input_required ?> <?php echo $input_disabled ?>>
            <!-- </td> <td><span>هـ</span></td>-->
            <script type="text/javascript">
                $('#<?php echo $input_name ?>').calendarsPicker({
                    calendar: $.calendars.instance('UmmAlQura')
                });
            </script>
            <!--</tr></table>-->
