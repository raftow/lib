<?php

$file_dir_name = dirname(__FILE__); 



$module = $_POST["module"];
$after_upload = $_POST["afup"];
$after_upload_obj_id = $_POST["afup_objid"];
if(isset($_POST["afup_obj_categ_id"])) $after_upload_obj_categ_id = $_POST["afup_obj_categ_id"];
if(isset($_POST["allowed_exention_list"])) $allowed_exention_list = $_POST["allowed_exention_list"];
else $allowed_exention_list = null;

require_once("afw_autoloader.php");
AfwSession::startSession();
$uri_module = AfwUrlManager::currentURIModule();
if($uri_module) AfwAutoLoader::addModule($uri_module);

include_once("$file_dir_name/../../external/config.php");
include_once("$file_dir_name/../../external/db.php");




$objme = AfwSession::getUserConnected();
if(!$objme)
{
	echo '{"status":"error", "message":"not connected"}';
	exit;
}

$post_count = count($_POST);
if($post_count==0) {
        $post_count_reason = "may be th size of file is too big";
        if($objme->isSuperAdmin()) $post_count_reason .= ", you may need to change post_max_size in php.ini (check by doing grep max /etc/php.ini | grep size) and after change make restart of server ex: systemctl restart httpd,";
}
else $post_count_reason = "";

if(!$module)
{
	echo '{"status":"error", "message":"module not defined post array count ='.$post_count.', '.$post_count_reason.'"}';
	exit;
}
AfwAutoLoader::addModule($module);
$me = $objme->id;
$my_debug_file = "my_upload_".date("Ymd")."_$me.txt";
// AFWDebugg::initialiser($DEBUGG_SQL_DIR,$my_debug_file);



$my_sh = $objme->getMyOrganizationId("");
if(!$module) throw new AfwRuntimeException("MODULE var should be defined for `myUpload` file upload process");
include_once ("$file_dir_name/../$module/module_config.php");
include_once ("$file_dir_name/../$module/application_config.php");
AfwSession::initConfig($config_arr);


// max allowwed size for upload
// ex : 4194304 = 4 Mo = 4 * 1024 * 1024 = 4 * 1048576

if(!$allowed_upload_size)  $allowed_upload_size = 40;  // 40 Mo
$MAX_ALLOWED_SIZE_FOR_UPLOAD = $allowed_upload_size * 1048576; 

$file_types = AfwFileUploader::getDocTypes($module);
if((!$file_types) or (count($file_types)==0)) throw new AfwRuntimeException("file_types for $module is to be defined for file uploads process : add `$module-file_types` param in $module/application_config.php file");
$AfileClass = AfwSession::config("$module-AfileClass",AfwSession::config("AfileClass", "Afile"));

if(!$allowed_exention_list)
{
    list($allowed, $ft_allowed) = DocType::getExentionsAllowed($file_types, $upper=false);
}
else
{
    $allowed = explode(",",$allowed_exention_list);
}

$picture_types_arr = array('png', 'jpg', 'jpeg', 'gif');

if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){

	$extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
	if(!in_array(strtolower($extension), $allowed))
        {
		echo '{"status":"error", "message":"صيغة الملف '.strtolower($extension).' غير مسموح بها يسمح فقط بـ  :'.implode(",",$allowed).'"}';
		exit;
	}
        $afile_original_name = $_FILES['upl']['name'];
        $afile_name = str_replace(".$extension","",$afile_original_name);
        $afile_type = $_FILES['upl']['type'];
        $afile_size = $_FILES['upl']['size'];
        if($afile_size > $MAX_ALLOWED_SIZE_FOR_UPLOAD)  
        {
                $max_allowed_size_for_upload_explain = "$MAX_ALLOWED_SIZE_FOR_UPLOAD = $allowed_upload_size * 1048576 < $afile_size"; 
                echo '{"status":"error","message":"حجم الملف تجاوز الحد الأقصى المسموح به '.$max_allowed_size_for_upload_explain.'"}';
		exit;
        }
        
        
        if(in_array(strtolower($extension), $picture_types_arr)) $afile_pic = "Y";
        else  $afile_pic = "N";

        
        if($AfileClass == "Afile")
        {
                $af = new Afile();
                $af->set("owner_id",$me);
                $af->set("stakeholder_id",$my_sh);
        }
        elseif($AfileClass == "WorkflowFile")
        {
                AfwAutoLoader::addModule("workflow");
                $af = new WorkflowFile();
        }
        else
        {
                throw new AfwRuntimeException("use of AfileClass $AfileClass is not implemented in AfwMyUpload api service");
        }
        
        $af->set("afile_name",$afile_name);
        $af->set("original_name",$afile_original_name);
        $af->set("afile_type",$afile_type);
        $af->set("afile_ext",strtolower($extension));
        $af->set("picture",$afile_pic);
        $af->set("afile_size",$afile_size);
        $af->set("doc_type_id",1);
        
        
        $error = "";
        
        if($af->insert())
        {
                $new_name =  $af->getNewName();
                $mv_from_file = $_FILES['upl']['tmp_name'];
                $uploads_root_path = AfwSession::config("uploads_root_path","");
                // $upld_path = AfwSession::config("uploads_http_path","");
                $mv_to_file = $uploads_root_path.$new_name;
                // array ( 'upl' => array ( 'name' => 'normalLeaveRamadan1436.jpg', 'type' => 'image/jpeg', 'tmp_name' => 'C:\\wamp\\tmp\\php942C.tmp', 'error' => 0, 'size' => 79454, ), )
        	if(move_uploaded_file($mv_from_file, $mv_to_file))
                {
        		if($after_upload)
                        {
                             $after_upload_full_file_name = "$file_dir_name/../$module/afup/after_upload_$after_upload.php";   
                             if(file_exists($after_upload_full_file_name))   
                             {
                                include($after_upload_full_file_name);      
                             }
                             else
                             {
                                throw new AfwRuntimeException("After upload policy file name $after_upload_full_file_name not found");
                             }
                             
                        }
                        
                        if($error)
                        {
                                echo '{"status":"error","message":"'.$error.'"}';
                		exit;
                        }
                        else
                        {
                                echo '{"status":"success",'."\n".'"size":"'.$afile_size.'",'."\n".'"message":"success",'."\n".'"debugg": "file moved successfully from'.$mv_from_file.' to '.$mv_to_file.'"}';
                		exit;
                        }
        	}
                else 
                {       
                        
                        $upl_error = $_FILES['upl']["error"];
                        
                        if(file_exists($mv_from_file))
                        {
                             $fsize = filesize($mv_from_file);
                             $finfo = finfo_open(FILEINFO_MIME_TYPE);
                             $mime = finfo_file($finfo, $mv_from_file);
                             $_FILES['upl']["mime"] = $mime;
                             $move_error = "file $mv_from_file exists but failed to move to $mv_to_file";
                             
                        }
                        else
                        {
                             $move_error = "file $mv_from_file not found";
                        }
                        
                        $error = "can't move file to uploads folder : <br>
                        from $mv_from_file(size:$fsize, mime:$mime) to $mv_to_file, <br>
                        upload error : $upl_error, <br>
                        move error : $move_error<br>
                        try manually : move $mv_from_file $mv_to_file\n<br>";
                        $_FILES['move_error'] = $error;
                }
        }
        else
        {
                $error = "can't insert afile error occured when inserting record in DB";
                
                if($objme->isAdmin() and $af->sql_error) $error .= " : ".$af->sql_error;
                $_FILES['insert'] = $error;
        }
}
else
{
      $error = "حدث خطأ عند تحميل الملف نرجوا التثبت من  صيغة الملف وحجمه : ".$_FILES['upl']['error']." => ".var_export($_FILES,true); //"upload error occured (check file type and format and size)";//;
}

echo '{"status":"error", "message":"خطأ : '.$error.'"}';
exit;