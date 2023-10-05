<div class='swal-overlay swal-overlay--show-modal' tabindex='-1'>  
        <div class='swal-modal'>
                <div class='swal-icon swal-icon--warning'>    
                        <span class='swal-icon--warning__body'>      
                                <span class='swal-icon--warning__dot'>
                                </span>    
                        </span>  
                </div>
                <div class='swal-title' style=''><?php echo $confirmation_question ?>
                </div>
                <div class='swal-text' style=''><?php echo $confirmation_warning ?>
                </div>
                <form id='confirm_frm' name='confirm_frm' method='post' action='i.php'> 
                    <input type="hidden" name="cn" id="cn" value="<?php echo $controller ?>">
                    <input type="hidden" name="mt" id="mt" value="<?php echo $method ?>">
                    <?php
                    foreach($hidden_list as $hidden_item)
                    {
                    ?>
                        <input type="hidden" name="<?php echo $hidden_item ?>" id="<?php echo $hidden_item ?>" value="<?php echo $$hidden_item ?>">
                    <?php
                    }
                    ?>

                <div class='swal-footer'>
                        <div class='swal-button-container'>    
                                <input name='pbmcancel-<?php echo $pbMethodCode ?>' type='submit' class='swal-button swal-button--cancel' tabindex='0' value='إلغاء'>
                        </div>
                        <div class='swal-button-container'>
                                <input name='pbmconfirm-<?php echo $pbMethodCode ?>' type='submit' class='swal-button swal-button--confirm swal-button--danger' tabindex='0' value='موافق'>                                    
                        </div>
                </div>
                
                </form>
        </div>
</div>