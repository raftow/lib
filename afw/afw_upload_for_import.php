<?php
$file_dir_name = dirname(__FILE__); 
// include_once("$file_dir_name/../../ext ernal/con fig.php");
// include_once("$file_dir_name/../../ext ernal/d b.php");
// 
AfwSession::startSession();
$me = AfwSession::getSessionVar("user_id");
$uploads_root_path = AfwSession::config("uploads_root_path","");
if(!$me)
{
	echo '{"status":"error", "message":"not connected"}';
	exit;
}

$my_debug_file = "my_upload_for_import_".date("Ymd")."_$me.txt";
AFWDebugg::initialiser($DEBUGG_SQL_DIR,$my_debug_file);



$objme = new Auser();
$objme->load($me);

$my_orgunit_id = $_POST["my_orgunit_id"];

$allowed = array('xls', 'xlsx');

if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){

	$extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
	if(!in_array(strtolower($extension), $allowed))
        {
		echo '{"status":"error", "message":"extension not allowed"}';
		exit;
	}
        $afile_original_name = $_FILES['upl']['name'];
        $afile_name = str_replace(".$extension","",$afile_original_name);
        $afile_type = $_FILES['upl']['type'];
        $afile_size = $_FILES['upl']['size'];
        $afile_pic = "N";

        
        
        $af = new Afile();
        
        $af->set("afile_name",$afile_name);
        $af->set("original_name",$afile_original_name);
        $af->set("afile_type",$afile_type);
        $af->set("afile_ext",strtolower($extension));
        $af->set("picture",$afile_pic);
        $af->set("afile_size",$afile_size);
        $af->set("doc_type_id",9);
        $af->set("owner_id",$me);
        $af->set("stakeholder_id",$my_orgunit_id);
        if($af->insert())
        {
                $new_name =  $af->getNewName();
        
                // array ( 'upl' => array ( 'name' => 'normalLeaveRamadan1436.jpg', 'type' => 'image/jpeg', 'tmp_name' => 'C:\\wamp\\tmp\\php942C.tmp', 'error' => 0, 'size' => 79454, ), )
        	if(move_uploaded_file($_FILES['upl']['tmp_name'], $uploads_root_path."imports/".$new_name))
                {
        		echo '{"status":"success","size":"'.$afile_size.'"}';
        		exit;
        	}
                else 
                {       
                        $mv_from_file = $_FILES['upl']['tmp_name'];
                        $mv_to_file = $uploads_root_path."imports/".$new_name;
                        $mv_error = $_FILES['upl']["error"];
                        $_FILES['move'] = "can't move file to uploads folder : from $mv_from_file to $mv_to_file, error : $mv_error ";
                }
        }
        else
        {
                if($objme->isAdmin()) $_FILES['insert'] = $af->sql_error;
                else $_FILES['insert'] = "error occured";
        }
}

echo '{"status":"error", "message":"error : '.var_export($_FILES,true).'"}';
exit;