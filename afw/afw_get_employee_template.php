<?php
        $employee_info = $_POST["employee_info"];
?>
        <div class="col-md-6">                
                <div class="employee-getter form-group">                        
                        <label>                          
                                أدخل رقم الموظف أو البريد الالكتروني أو رقم الهوية أو رقم الجوال
                        </label>                        		
                        <input type="text" class="form-control inputfull" placeholder="" name="employee_info" value="<?php echo $employee_info; ?>" size="32" maxlength="1000">		                
                </div>        
        </div>                 

