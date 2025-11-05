<?php 

// old require of afw_root 
class AfwFileUploader extends AFWRoot 
{
        public static $picture_types_arr = array('png', 'jpg', 'jpeg', 'gif');
        public static $options = array();

        public static function getDocTypes($module)
        {
                return AfwSession::config("$module-file_types", AfwSession::config("file_types", [10,13]));
        }
        

        public static function init($load_extensions=false, $doc_types=null)
        {
                if(!self::$options["inited"])
                {
                        self::$options["allowed_upload_size"] = AfwSession::config("allowed_upload_size_by_file", 40);  // 40 Mo        
                        self::$options["MAX_ALLOWED_SIZE_FOR_UPLOAD"] = self::$options["allowed_upload_size"] * 1048576; 
                        self::$options["inited"] = true;

                        if($load_extensions)
                        {
                                self::$options["file_types"] = AfwSession::config("file_types", $doc_types);
                                self::$options["default_allowed_exention_list"] = DocType::getExentionsAllowed(self::$options["file_types"], $upper=false);
                        }

                }
        }

        public static function completeUpload($file_title, $file_code, $file_arr, $afileManager=null, $allowed_exentions = null, $doc_type_id = 7, $default_user_id=0, $default_orgunit_id=0)
        {
                $allowed_exentions_policy = "حسب سياسة شاشة رفع الوثائق";
                $objme = AfwSession::getUserConnected();
                if(($default_user_id) and (!$objme))
                {
                        return array("status" => "error", "message" => "no default user and current user is not logged in");
                }

                if($objme) $me = $objme->id;
                else $me = $default_user_id;


                $orgunit_id = $default_orgunit_id;

                self::init((!$allowed_exentions), $doc_type_id);
                if(!$allowed_exentions)
                {
                        $allowed_exentions = self::$options["default_allowed_exention_list"][0];   
                        $allowed_exentions_policy = "حسب سياسة نوع الوثيقة";
                }

                $extension = strtolower(pathinfo($file_arr['name'], PATHINFO_EXTENSION));
                $afile_type = $file_arr['type'];
                $afile_size = $file_arr['size'];

                if(!$file_arr['error'])
                {

                        
                        if(!in_array(strtolower($extension), $allowed_exentions))
                        {
                                return array("status" => "error", 
                                             "message" => 'صيغة الملف '.$extension.' غير مسموح بها يسمح فقط بـ  :'.implode(",",$allowed_exentions)." ".$allowed_exentions_policy,
                                             "afile_object" => null
                                        );
                        }
                        $afile_original_name = $file_arr['name'];
                        $afile_name = $file_title." : ".$afile_original_name;
                        $afile_name = str_replace(".$extension","",$afile_name);
                        $afile_name = str_replace("_"," ",$afile_name);
                        
                        if($afile_size > self::$options["MAX_ALLOWED_SIZE_FOR_UPLOAD"])  
                        {
                                $max_allowed_size_for_upload_explain = self::$options["MAX_ALLOWED_SIZE_FOR_UPLOAD"]." = ".self::$options["allowed_upload_size"]." * 1048576 < $afile_size"; 
                                return array("status" => "error", 
                                             "message" => "حجم الملف تجاوز الحد الأقصى المسموح به '.$max_allowed_size_for_upload_explain.'",
                                             "afile_object" => null
                                        );
                        }
                        
                        
                        if(in_array($extension, self::$picture_types_arr)) $afile_pic = "Y";
                        else  $afile_pic = "N";
                
                        $af = Afile::loadByMainIndex($afile_original_name, $afile_size, $me, $orgunit_id, true);
                        
                        $af->set("afile_name",$afile_name);                        
                        $af->set("afile_type",$afile_type);
                        $af->set("afile_ext",$extension);
                        $af->set("picture",$afile_pic);
                        // intelligent deduce of $doc_type_id
                        if($doc_type_id==9999)
                        {
                             // @todo   
                        }
                        $af->set("doc_type_id",$doc_type_id);
                        $error = "";
                        
                        if($af->commit())
                        {
                                $new_name =  $af->getNewName();
                                $mv_from_file = $file_arr['tmp_name'];
                                $uploads_root_path = AfwSession::config("uploads_root_path","");
                                // $upld_path = AfwSession::config("uploads_http_path","");
                                $mv_to_file = $uploads_root_path.$new_name;
                                // array ( 'upl' => array ( 'name' => 'normalLeaveRamadan1436.jpg', 'type' => 'image/jpeg', 'tmp_name' => 'C:\\wamp\\tmp\\php942C.tmp', 'error' => 0, 'size' => 79454, ), )
                                if(move_uploaded_file($mv_from_file, $mv_to_file))
                                {
                                        if($afileManager)
                                        {
                                                $error = $afileManager->uploadedSuccessfully($file_code, $doc_type_id, $af);      
                                        }
                                        
                                        if($error)
                                        {
                                                return array("status" => "error", 
                                                             "message" => $error,
                                                             "afile_object" => null
                                                        );
                                        }
                                        else
                                        {
                                                return array("status" => "success", 
                                                             "message" => "",
                                                             "afile_object" => $af,
                                                             "size" => $afile_size,
                                                             "debugg" => "file moved successfully from $mv_from_file  to  $mv_to_file",
                                                        );
                                                
                                        }
                                }
                                else 
                                {       
                                        
                                        $upl_error = $file_arr["error"];
                                        
                                        if(file_exists($mv_from_file))
                                        {
                                             $fsize = filesize($mv_from_file);
                                             $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                             $mime = finfo_file($finfo, $mv_from_file);
                                             $file_arr["mime"] = $mime;
                                             $move_error = "file $mv_from_file exists but failed to move to $mv_to_file";
                                             
                                        }
                                        else
                                        {
                                             $move_error = "file $mv_from_file not found";
                                        }
                                        
                                        $error = "can't move file to uploads folder : 
                                                        from $mv_from_file(size:$fsize, mime:$mime) to $mv_to_file, 
                                                        upload error : $upl_error, 
                                                        move error : $move_error
                                                        try manually : move $mv_from_file $mv_to_file";

                                        return array("status" => "error", 
                                                     "message" => $error,
                                                     "afile_object" => null
                                                   );
                                }
                        }
                        else
                        {
                                $error = "can't insert afile error occured when inserting record in DB";                                
                                if($objme->isAdmin() and $af->sql_error) $error .= " : ".$af->sql_error;

                                return array("status" => "error", 
                                                     "message" => $error,
                                                     "afile_object" => null
                                                   );
                        }
                }
                else
                {
                        $error = "حدث خطأ عند تحميل الملف نرجوا التثبت من صيغة الملف وحجمه : ";
                        $error .= "<br> صيغة الملف : $extension ";
                        $error .= "<br> نوع الملف : $afile_type ";
                        // $error .= "<br> الصيغ المسموح بها : ";
                        $error .= "<br> حجم الملف : $afile_size ";                        
                        // $error .= "<br> الحد الأقصى للحجم المسموح به : ";
                        $error_decoded = self::decodeUploadError($file_arr['error']);
                        $error .= "<br> نص الخطأ : " . AfwLanguageHelper::tt($error_decoded);
                        if(($objme and $objme->isSuperAdmin()) or AfwSession::config("MODE_DEVELOPMENT", false))
                        {
                                $error .= "<br> for Admin => _FILES = ".var_export($_FILES,true); //"upload error occured (check file type and format and size)";//;
                        }
                        

                        return array("status" => "error", 
                                   "message" => $error,
                                   "afile_object" => null
                                                   );
                }
        }


        private static function decodeUploadError($fileError)
        {

                switch($fileError) {
                        case UPLOAD_ERR_INI_SIZE:
                                // Exceeds max size in php.ini , Value: 1;
                                return "The uploaded file exceeds the upload max file size directive";

                        case UPLOAD_ERR_FORM_SIZE:
                                // Exceeds max size in html form, Value: 2                            
                                return "The uploaded file exceeds the MAX_FILE_SIZE directive";

                        case UPLOAD_ERR_PARTIAL:
                                // Exceeds max size in html form, Value: 3                           
                                return "The uploaded file was only partially uploaded";                            

                        case UPLOAD_ERR_NO_FILE:
                            // No file was uploaded, Value: 4
                            return "No file was uploaded"
                            ;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            // No /tmp dir to write to, Value: 6
                            return "Missing a temporary folder";

                        case UPLOAD_ERR_CANT_WRITE:
                            // Error writing to disk, Value: 7                            
                            return "Failed to write file to disk";

                        case UPLOAD_ERR_EXTENSION:
                                // Error writing to disk, Value: 7                            
                                return "Bad and not authorized extension stopped the file upload";

                }

                return $fileError;
        }

        public static function fileInputIsFilled($file_arr)
        {
                return (($file_arr["name"]!="") and ($file_arr["tmp_name"]!=""));
        }


}