                <input placeholder="<?php echo $placeholder ?>" type="text" tabindex="<?php echo $qedit_orderindex ?>" class="form-control <?php echo $lang ?>" name="<?php echo $col_name ?>" id="<?php echo $col_name ?>" value="<?php echo $val ?>" size=33 maxlength=255>
                <input type="button" class="<?php echo $class_inputButton ?>" name="" value="<?php echo $obj->translate('SEARCH', $lang, true) ?>" <?php echo $input_disabled ?> onclick="popup('<?php echo "main.php" ?>?Main_Page=afw_mode_search.php&cl=<?php echo $desc["ANSWER"] ?>')">
                <script language="javascript">
                    function popup(page) {
                        window.open(page, "<?php echo $obj->translate('SEARCH', $lang, true) ?>", "fullscreen='yes',menubar='no',toolbar='no',location='no',status='no'");
                    }
                </script>