<?php
   //
   die("obsolete hzm_h_r.php file");
   /*
   if(!$file_hzm_dir_name) $file_hzm_dir_name = dirname(__FILE__);
   ?? require_once ("$file_hzm_dir_name/ini.php");
   require_once ("$file_hzm_dir_name/../../../external/db.php");
   // here old require of common.php
   $only_members = false;
   include("$file_hzm_dir_name/../../../../pag/check_member.php");
   include("$file_hzm_dir_name/../../../lib/hzm/web/hzm_header.php");
    
   //include_once("$file_hzm_dir_name/../../../ums/auser.php");
   
   $obj = new Auser();
   
   $class_db_structure = $obj->getMyDbStructure();

   foreach($class_db_structure as $nom_col => $desc)
   {
	// if($nom_col=="arole_mfk") die("arole_mfk -> ".var_export($_POST[$nom_col],true));
        if(isset($_POST[$nom_col]) or ($desc["TYPE"]=="MFK"))
        {
		// if($nom_col=="arole_mfk") die("arole_mfk -> ".var_export($_POST[$nom_col],true));
                if(is_array($_POST[$nom_col]))
			$val = ','.implode(',', $_POST[$nom_col]).',';
		else
			$val = $_POST[$nom_col];
		
                $auto_c = $desc["AUTOCOMPLETE"];
                $auto_c_create = $auto_c["CREATE"];
                $val_atc = trim($_POST[$nom_col."_atc"]);
                
                if((!$val) and ($auto_c_create) and ($val_atc)) 
                {
                    if($desc["TYPE"] != "FK") 
                    {
                        $obj->throwError("auto create should be only on FK attributes $attribute is ".$desc["TYPE"]);
                    }
                    $obj_at = $obj->getEmptyObject($nom_col);
                    
                    foreach($auto_c_create as $attr => $auto_c_create_item)
                    {
                          $attr_val = "";
                          if($auto_c_create_item["CONST"]) $attr_val .= $auto_c_create_item["CONST"];
                          if($auto_c_create_item["FIELD"]) $attr_val .= " ".$obj->getVal($auto_c_create_item["FIELD"]);
                          if($auto_c_create_item["CONST2"]) $attr_val .= " ".$auto_c_create_item["CONST2"];
                          if($auto_c_create_item["INPUT"]) $attr_val .= " ".$val_atc;
                          if($auto_c_create_item["TOKEN"]) $attr_val .= " ".$obj->getTokenVal($auto_c_create_item["TOKEN"]);
                          
                          
                          $attr_val = trim($attr_val);
                          
                          $obj_at->set($attr,$attr_val);
                          
                    }
                    
                    $obj_at->insert();
                    
                    $val = $obj_at->getId();    
                    
                }
                
                $obj->set($nom_col, $val);

	}
}
//password random
$obj->set("pwd", md5("secret"));
// $obj->set("pwd", "secret");

$obj->insert();
$id = $obj->getId();
$new_label = $obj->insertNewLabel("ar");
// $_S ESSION["information"]  = "تمت $new_label بنجاح";

$_info = "تمت $new_label بنجاح وتم إرسال كلمة المرور على جوالك نرجوا  ادخال كلمة المرور  لتفعيل الحساب وتسجيل الدخول";

?>
<?php
if(!$file_hzm_dir_name) $file_hzm_dir_name = dirname(__FILE__); 
?>
<form id="formlogin" name="formlogin" method="post" action="login.php"  dir="rtl" enctype="multipart/form-data">
<center>
<br>
<div class="information"><?php echo $_info;?></div>
<br>
</center>

<div class="smallform">
<center>
    <div class="col-9">
            <label for="pwd" class="formlabel">رمز التحقق<i>*</i></label>
            <input id="mail" type="hidden" name="mail" value="<?php echo $_POST["mobile"]?>" tabindex="1" /><div id="j_idt29" aria-live="polite"></div>
            <input id="pwd" type="password" name="pwd" value="" maxlength="30" tabindex="2"  class="inputmoyen data_loaded"/><div id="j_idt34" aria-live="polite"></div>
    </div>
    <div class="col-9">
            <label for="pwd_new" class="formlabel">تعيين كلمة المرور<i>*</i></label>
            <input id="pwd_new" type="password" name="pwd_new" value="" maxlength="30" tabindex="2" class="inputmoyen data_loaded"/><div id="j_idt34" aria-live="polite"></div>
    </div>
    <div class="col-9">
            <label for="pwd_new2" class="formlabel">تأكيد كلمة المرور<i>*</i></label>
            <input id="pwd_new2" type="password" name="pwd_new2" value="" maxlength="30" tabindex="2" class="inputmoyen data_loaded"/><div id="j_idt34" aria-live="polite"></div>
    </div>
    
    <div class="col-9 btndiv">
            <input type="submit" name="j_idt39" value="تسجيل الدخول" class="greenbtn btn" />
    </div>
</center>
</div>
</form>
<?


include("$file_hzm_dir_name/../lib/hzm/web/hzm_footer.php"); 
*/
?>