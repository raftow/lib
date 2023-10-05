obsolete hzm_register.php
<!--
<link rel="stylesheet" type="text/css" href="afw_style.css">
<?php
if(!$file_hzm_dir_name) $file_hzm_dir_name = dirname(__FILE__); 
?>
<form method="post" action="hzm_h_r.php">                                                                
        <div class="filebox editcard">                
                <div>                        
                        <h4 class="green">فتح حساب جديد</h4>                        
                        <div>                                
                                <table width="100%">                                        
                                        <tbody>                                                
                                                <tr><td>                                                                
                                                                <table class="table_obj" cellspacing="3" cellpadding="4">                                                                             
                                                                        <tbody>                                                                                
                                                                                <tr>                                                                                        
                                                                                        <th class="fgroup_header" colspan="4">البيانات الشخصية                                                                                         
                                                                                        </th>                                                                                
                                                                                </tr>                                                                                
                                                                                <tr>                                                                                        
                                                                                        <th align="right" width="23%">الجنس :                                                                                         
                                                                                        </th>                                                                                        
                                                                                        <td align="right" width="27%">                                                                                                
                                                                                                <select class="inputselectlong data_notloaded inputlong" name="genre_id" id="genre_id" tabindex="0" size="1">                                                                                                        
                                                                                                        <option value="0">&nbsp;                                                                                                         
                                                                                                        </option>                                                                                                        
                                                                                                        <option value="2">انثى                                                                                                         
                                                                                                        </option>                                                                                                        
                                                                                                        <option value="1" selected="">ذكر                                                                                                         
                                                                                                        </option>                                                                                                
                                                                                                </select></td>                                                                                        
                                                                                        <th align="right" width="23%">الاسم الأول :                                                                                         
                                                                                        </th>                                                                                        
                                                                                        <td align="right" width="27%">                                                                                                
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="firstname" id="firstname" value="" size="32" maxlength="255">
                                                                                                </td>		                                                                                  
                                                                                </tr>                                                                                
                                                                                <tr>                                                                                        
                                                                                        <th align="right" width="23%">اسم الأب :                                                                                         
                                                                                        </th>                                                                                        
                                                                                        <td align="right" width="27%">                                                                                                
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="f_firstname" id="f_firstname" value="" size="32" maxlength="255">				</td>		 		 			                                                                                         
                                                                                        <th align="right" width="23%">الاسم الأخير :                                                                                         
                                                                                        </th>                                                                                        
                                                                                        <td align="right" width="27%">                                                                                                
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="lastname" id="lastname" value="" size="32" maxlength="255"></td>		                                                                                  
                                                                                </tr> 
                                                                                <tr>		 			                                                                                         
                                                                                        <th align="right" width="23%">نوع الهوية : 			                                                                                         
                                                                                        </th>			                                                                                         
                                                                                        <td align="right" width="27%">	                                                                                                 
                                                                                                <select class="inputselectlong data_notloaded inputlong" name="idn_type_id" id="idn_type_id" tabindex="0" size="1">			                                                                                                         
                                                                                                        <option value="0" selected="">&nbsp;                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="3">إقامة                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="7">إقامة خمس سنوات قبائل المصعبين                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="5">إقامة خمس سنوات قبائل بلحارث                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="4">إقامة خمس سنوات قبائل نازحة                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="6">إقامة خمس سنوات قبائل همام                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="8">إقامة لأبناء السعوديات                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="2">بطاقة عائلية                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="1">هوية قبلية                                                                                                         
                                                                                                        </option>		                                                                                                 
                                                                                                </select>			</td>		 		 			                                                                                         
                                                                                        <th align="right" width="23%">رقم الهوية : 			                                                                                         
                                                                                        </th>			                                                                                         
                                                                                        <td align="right" width="27%">        				                                                                                                 
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="idn" id="idn" value="" size="32" maxlength="255">
                                                                                                </td>		                                                                                      
                                                                                </tr>                                                                                
                                                                                <tr>                                                                                        
                                                                                        <th class="fgroup_header" colspan="4">معلومات الاتصال                                                                                         
                                                                                        </th>                                                                                
                                                                                </tr>                                                                                
                                                                                <tr>		 			                                                                                         
                                                                                        <th align="right" width="23%">الجوال :
                                                                                        <br>                                                                                                
                                                                                                <div class="ehelp">تأكد من صحته حتى تصلك الرسالة القصيرة                                                                                                 
                                                                                                </div> 			                                                                                         
                                                                                        </th>			                                                                                         
                                                                                        <td align="right" class="mobile" width="27%">        				                                                                                                 
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="mobile" id="mobile" value="" size="32" maxlength="255">				</td>		 		 			                                                                                         
                                                                                        <th align="right" width="23%">البريد الالكتروني : 			                                                                                         
                                                                                        </th>			                                                                                         
                                                                                        <td align="right" width="27%">        				                                                                                                 
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="email" id="email" value="" size="32" maxlength="255"></td>		                                                                                  
                                                                                </tr>                                                                                
                                                                                <tr>                                                                                        
                                                                                        <th class="fgroup_header" colspan="4">العنوان الوطني                                                                                         
                                                                                        </th>                                                                                
                                                                                </tr>                                                                                
                                                                                <tr>		 			                                                                                         
                                                                                        <th align="right" width="23%">العنوان في السعودية :
                                                                                                                                                                                          
                                                                                        </th>			                                                                                         
                                                                                        <td align="right" width="27%">        				                                                                                                 
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="address" id="address" value="" size="32" maxlength="255">	                                                                                                 
                                                                                                <br>
                                                                                                <div>المملكة العربية السعودية</div>                                                                                                
                                                                                        </td>
                                                                                        <th COLSPAN='2'>
                                                                                               <div class="ehelp">إذا لم تكن قد قمت به سابقا فبادر الآن بتسجيل عنوانك الوطني تنفيذاً لقرار مجلس الوزراء رقم (252) بتاريخ 1434/7/24هـ من                                                                                                          
                                                                                                        <a target="_na" href="https://www.sp.com.sa/ar/NationalAddress/Pages/NationalAddress.aspx">هنا</a>                                                                                                
                                                                                                </div> 
                                                                                        </th>
                                                                                        		 		 			                                                                                         
                                                                                        		                                                                                  
                                                                                </tr>                                                                                
                                                                                <tr>		 			                                                                                         
                                                                                        <th align="right" width="23%">الحي : 			                                                                                         
                                                                                        </th>			                                                                                         
                                                                                        <td align="right" width="27%">        				                                                                                                 
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="quarter" id="quarter" value="" size="32" maxlength="255">				</td>		 		 			                                                                                         
                                                                                        <th align="right" width="23%">المدينة : 			                                                                                         
                                                                                        </th>			                                                                                         
                                                                                        <td align="right" width="27%">	                                                                                                 
                                                                                                <select class="inputselectlong data_notloaded inputlong" name="city_id" id="city_id" tabindex="0" size="1">			                                                                                                         
                                                                                                        <option value="0" selected="">&nbsp;                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="1">الرياض                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="2">المدينة                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="3">جدة                                                                                                         
                                                                                                        </option>		                                                                                                 
                                                                                                </select>			</td>		                                                                                  
                                                                                </tr>                                                                                
                                                                                <tr>		 			                                                                                         
                                                                                        <th align="right" width="23%">البلد (الدولة) : 			                                                                                         
                                                                                        </th>			                                                                                         
                                                                                        <td align="right" width="27%">	                                                                                                 
                                                                                                <select class="inputselectlong data_notloaded inputlong" name="country_id" id="country_id" tabindex="0" size="1">			                                                                                                         
                                                                                                        <option value="0" selected="">&nbsp;                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="2">الجمهورية التونسية                                                                                                         
                                                                                                        </option>			                                                                                                         
                                                                                                        <option value="1">المملكة العربية السعودية                                                                                                         
                                                                                                        </option>		                                                                                                 
                                                                                                </select>
                                                                                        </td>
                                                                                        <th align="right" width="23%">الترقيم البريدي : 			                                                                                         
                                                                                        </th>
                                                                                        <td align="right" width="27%">
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="cp" id="cp" value="" size="32" maxlength="255">
                                                                                        </td>		                                                                                  
                                                                                </tr> 
                                                                                <tr>		 			                                                                                         
                                                                                        <th align="right" width="23%">أدخل الرمز الذي تراه مجاورا : 			                                                                                         
                                                                                        </th>			                                                                                         
                                                                                        <th align="right" width="27%">	                                                                                                 
                                                                                                <input type="text" tabindex="0" class="inputtext data_notloaded inputlong" name="captcha" id="captcha" value="" size="32" maxlength="255">
                                                                                        </th>
                                                                                        <th align="right" colspan='2'>
                                                                                        <?php
                                                                                                                AfwSession::startSession();
                                                                                                                include("$file_hzm_dir_name/../lib/lib/hzm/web/hzm_cap/simple-php-captcha.php");
                                                                                                                $simple_php_captcha = AfwSession::setSessionVar('captcha', simple_php_captcha());

                                                                                                                echo '<img style="float: right;" width="100px" height="100px" src="' . $simple_php_captcha['image_src'] . '" alt="CAPTCHA" />';
                                                                                         ?> 			                                                                                         
                                                                                        </th>
                                                                                </tr>
                                                                                <tr>          
                                                                                    <td colspan="4" align="center">
                                                                                                <input type="submit" name="submit" id="submit-form" class="bluebtn btn" value="&nbsp;إنشاء  الحساب&nbsp;" style="margin-right: 5px;" />          
                                                                                    </td>                                                                                                               
                                                                                </tr>                                                                       
                                                                        </tbody>                                                                
                                                                </table>                                                                
                                                        </td>                                                
                                                </tr>                                        
                                        </tbody>                                
                                </table>                        
                        </div>                
                </div>        
        </div>
</form>-->