<?php

$file_dir_name = dirname(__FILE__);

try {
        $module = $_POST["module"];
        $doc_type_id = $_POST["doc_type_id"];
        if(!$doc_type_id) $doc_type_id = 1;
        $doc_attach_id = $_POST["doc_attach_id"];
        if(!$doc_attach_id) $doc_attach_id = 0;
        $after_upload = $_POST["afup"];
        $after_upload_obj_id = $_POST["afup_objid"];
        if (isset($_POST["afup_obj_categ_id"])) $after_upload_obj_categ_id = $_POST["afup_obj_categ_id"];
        if (isset($_POST["allowed_exention_list"])) $allowed_exention_list = $_POST["allowed_exention_list"];
        else $allowed_exention_list = null;

        require_once("afw_autoloader.php");
        AfwSession::startSession();
        $uri_module = AfwUrlManager::currentURIModule();
        if ($uri_module) AfwAutoLoader::addModule($uri_module);

        // include_once("$file_dir_name/../../exte rnal/co nfig.php");
        // include_once("$file_dir_name/../../exte rnal/d b.php");




        $objme = AfwSession::getUserConnected();
        if (!$objme) {
                echo '{"status":"error", "message":"not connected"}';
                exit;
        }

        $post_count = count($_POST);
        if ($post_count == 0) {
                $post_count_reason = "may be th size of file is too big";
                if ($objme->isSuperAdmin()) $post_count_reason .= ", you may need to change post_max_size in php.ini (check by doing grep max /etc/php.ini | grep size) and after change make restart of server ex: systemctl restart httpd,";
        } else $post_count_reason = "";

        if (!$module) {
                echo '{"status":"error", "message":"module not defined post array count =' . $post_count . ', ' . $post_count_reason . '"}';
                exit;
        }
        AfwAutoLoader::addModule($module);
        $me = $objme->id;
        $my_debug_file = "my_upload_" . date("Ymd") . "_$me.txt";
        // AFWDebugg::initialiser($DEBUGG_SQL_DIR,$my_debug_file);



        $my_sh = $objme->getMyOrganizationId("");
        if (!$module) throw new AfwRuntimeException("MODULE var should be defined for `myUpload` file upload process");
        include_once("$file_dir_name/../$module/module_config.php");
        include_once("$file_dir_name/../$module/application_config.php");
        AfwSession::initConfig($config_arr, "system", "$file_dir_name/../$module/application_config.php");


        // max allowwed size for upload
        // ex : 4194304 = 4 Mo = 4 * 1024 * 1024 = 4 * 1048576

        if (!$allowed_upload_size)  $allowed_upload_size = 40;  // 40 Mo
        $MAX_ALLOWED_SIZE_FOR_UPLOAD = $allowed_upload_size * 1048576;
        $devMode = AfwSession::config("MODE_DEVELOPMENT", false);
        $file_types = AfwFileUploader::getDocTypes($module);
        if ((!$file_types) or (count($file_types) == 0)) throw new AfwRuntimeException("file_types for $module is to be defined for file uploads process : add `$module-file_types` param in $module/application_config.php file");
        $AfileClass = AfwSession::config("$module-AfileClass", AfwSession::config("AfileClass", "WorkflowFile"));

        if (!$allowed_exention_list) {
                list($allowed, $ft_allowed) = DocType::getExentionsAllowed($file_types, $upper = false);
        } else {
                $allowed = explode(",", $allowed_exention_list);
        }

        $picture_types_arr = array('png', 'jpg', 'jpeg', 'gif');

        if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {

                $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
                if (!in_array(strtolower($extension), $allowed)) {
                        echo '{"status":"error", "message":"صيغة الملف ' . strtolower($extension) . ' غير مسموح بها يسمح فقط بـ  :' . implode(",", $allowed) . '"}';
                        exit;
                }
                $afile_original_name = $_FILES['upl']['name'];
                $afile_name = str_replace(".$extension", "", $afile_original_name);
                $afile_type = $_FILES['upl']['type'];
                $afile_size = $_FILES['upl']['size'];
                if ($afile_size > $MAX_ALLOWED_SIZE_FOR_UPLOAD) {
                        $max_allowed_size_for_upload_explain = "$MAX_ALLOWED_SIZE_FOR_UPLOAD = $allowed_upload_size * 1048576 < $afile_size";
                        echo '{"status":"error","message":"حجم الملف تجاوز الحد الأقصى المسموح به ' . $max_allowed_size_for_upload_explain . '"}';
                        exit;
                }


                if (in_array(strtolower($extension), $picture_types_arr)) $afile_pic = "Y";
                else  $afile_pic = "N";


                if ($AfileClass == "Afile") {
                        $af = new Afile();
                        $af->set("stakeholder_id", $my_sh);
                        $af->set("owner_id", $me);
                        $af->set("original_name", $afile_original_name);
                        $af->set("afile_size", $afile_size);
                } elseif ($AfileClass == "WorkflowFile") {
                        AfwAutoLoader::addModule("workflow");
                        $owner_type = $after_upload;
                        $owner_id = $after_upload_obj_id;
                        $af = WorkflowFile::loadByMainIndex($afile_original_name, $owner_type, $owner_id, $afile_size,$create_obj_if_not_found=true);
                        if(!$af->is_new)
                        {
                                echo '{"status":"error","message":"تم تحميل هذا الملف مسبقا يرجى استخدامه من القائمة وعدم تكرار التحميل"}';        
                                exit;
                        }
                        
                } else {
                        throw new AfwRuntimeException("use of AfileClass $AfileClass is not implemented in AfwMyUpload api service");
                }

                $af->set("afile_name", $afile_name);
                $af->set("afile_type", $afile_type);
                $af->set("afile_ext", strtolower($extension)); 
                $new_name =  $af->getNewName();
                $af->set("stored_file_name", $af->getNewName());               
                $af->set("picture", $afile_pic);
                $af->set("doc_type_id", $doc_type_id);
                

                $error = "";

                if ($af->commit()) {                        
                        $mv_from_file = $_FILES['upl']['tmp_name'];
                        $uploads_root_path = AfwSession::config("uploads_root_path", "");
                        if(!$uploads_root_path) throw new AfwRuntimeException("uploads_root_path is not defined correctly in system config");
                        // $upld_path = AfwSession::config("uploads_http_path","");
                        $mv_to_file = $uploads_root_path . $new_name;
                        // array ( 'upl' => array ( 'name' => 'normalLeaveRamadan1436.jpg', 'type' => 'image/jpeg', 'tmp_name' => 'C:\\wamp\\tmp\\php942C.tmp', 'error' => 0, 'size' => 79454, ), )
                        if (move_uploaded_file($mv_from_file, $mv_to_file)) {
                                if ($after_upload) {
                                        $after_upload_full_file_name = "$file_dir_name/../$module/afup/after_upload_$after_upload.php";
                                        if (file_exists($after_upload_full_file_name)) {
                                                include($after_upload_full_file_name);
                                        } else {
                                                throw new AfwRuntimeException("After upload policy file name $after_upload_full_file_name not found");
                                        }
                                }

                                if ($error) {
                                        echo '{"status":"error","message":"' . $error . '"}';
                                        exit;
                                } else {
                                        if(!$devMode)                                         
                                        {
                                                $mv_from_file0 = "***";
                                                $mv_to_file0 = "***";
                                        }
                                        else
                                        {
                                                $mv_from_file0 = str_replace('\\','/', $mv_from_file);
                                                $mv_to_file0 = str_replace('\\','/', $mv_from_file);
                                        }
                                        
                                        echo '{"status":"success",' . "\n" . '"size":"' . $afile_size . '",' . "\n" . '"message":"success",' . "\n" . '"debugg": "file moved successfully from ' . $mv_from_file0 . ' to ' . $mv_to_file0 . '"}';
                                        exit;
                                }
                        } else {

                                $upl_error = $_FILES['upl']["error"];

                                if (file_exists($mv_from_file)) {
                                        $fsize = filesize($mv_from_file);
                                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                        $mime = finfo_file($finfo, $mv_from_file);
                                        $_FILES['upl']["mime"] = $mime;
                                        $move_error = "file $mv_from_file exists but failed to move to $mv_to_file";
                                } else {
                                        $move_error = "file $mv_from_file not found";
                                }

                                $error = "can't move file to uploads folder : <br>
                                from $mv_from_file(size:$fsize, mime:$mime) to $mv_to_file, <br>
                                upload error : $upl_error, <br>
                                move error : $move_error<br>
                                try manually : move $mv_from_file $mv_to_file\n<br>";
                                $_FILES['move_error'] = $error;
                        }
                } else {
                        $error = "error occured when committing $AfileClass record in DB";

                        if ($objme->isAdmin() and $af->sql_error) $error .= " : " . $af->sql_error;
                        $_FILES['insert'] = $error;
                }
        } else {
                $error = "حدث خطأ عند تحميل الملف نرجوا التثبت من  صيغة الملف وحجمه : " . $_FILES['upl']['error'] . " => " . var_export($_FILES, true); //"upload error occured (check file type and format and size)";//;
        }

        echo '{"status":"error", "message":"خطأ : ' . $error . '"}';
        exit;
} catch (Exception $e) {
        echo '{"status":"error", "message":"An exception happened : ' . $e->getMessage() . ' ", "debugg":"' . $e->getTraceAsString() . '"}';
        exit;
} catch (Error $e) {
        echo '{"status":"error", "message":"An Error happened : ' . $e->__toString() . ' "}';
        exit;
}
