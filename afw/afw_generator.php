<?php

$prod_dir = "C:\\wamp\\www\\pag";

class AfwGenerator extends AFWRoot {

	private static $hostname;
	private static $username;
	private static $password;
	private static $database;
	private static $connect	= false;
	private static $link	= NULL;

	private $DB		= array();
        private $SCIS		= array();
        

	/**
	 * _connect
	 * Connect to DBMS
	 */
	private static function connect() {
            
		if(self::$connect === false || self::$link === NULL) {
			$hostname = AfwSession::config("host","");
			$username = AfwSession::config("user","");
			$password = AfwSession::config("password","");
                        $database = AfwSession::config("database","");
			
                        self::$link = AfwMysql::connection($hostname, $username, $password, $database);
			if(!self::$link) {
				if(AfwSession::config("MODE_DEVELOPMENT", false)) $infos = "with following params :\n host = $hostname, user = $username";
                                AfwRunHelper::simpleError("Failed to connect to server $infos.");
			}
			/*
                        $database = $this->getDatabase();
			
                        if (!@mysql_select_db($database, self::$link)) {
				$this->_error("Impossible de se connecter à la base '$database'.");
			}
                        */
			self::$connect = true;
			
			return self::$link;
		}
	} 
	
        
        /**
	 * generateFromModule
	 * genere le fichier sql de creation/mise a jour des tables d'un systeme/module 
	 * @param string $dir (chemin de generation de fichier)
	 */
        // $table_sub_model = true means generate not only  
	public function generateFromModule($sqldir,$atable_id = null, $module_id=null, $sub_module_id = null, $atable_name = null, $decoupage=true, $table_sub_model=false)
        {
               global $nb, $show_sql, $server_db_prefix;
               
               $file_dir_name = dirname(__FILE__); 
               
               $log_errors = "";
               // die("here 8 : sqldir=$sqldir , show_sql=$show_sql");
               if(AfwFileSystem::isDir($sqldir) or ($show_sql))
               {
                       /*if($show_sql) $file_to_create = false;
                       else */ 
                       $file_to_create = AfwFileSystem::isDir($sqldir); 
                       
                       $footer_sql = "";
                       $sql = "";
                       $fileName = "";
                       if(!$decoupage) $fileName = "sql_".date("YmdHis")."_";
                       
                       $fileName = "gen_php_".date("YmdHis")."_";
                       
                       
                       
                       $at = new Atable();
                       
                       if($atable_id) 
                       {
                           $at->select("id",$atable_id);
                           $at2 = new Atable();
                           if($table_sub_model)
                               $at2->where("(id = $atable_id or id in (select answer_table_id from afield fld where fld.atable_id = $atable_id and fld.avail='Y' and fld.answer_table_id > 0) or id in(select atable_id from afield fld2 where fld2.answer_table_id = $atable_id and fld2.avail='Y'))");
                           else
                               $at2->select("id",$atable_id);
                           $at2->load();
                           if(!$decoupage) $fileName = $at2->getVal("atable_name").".sql";
                       }
                       
                       
                       
                       if($module_id)
                       {
                             $at->where("(id_module = $module_id or id_sub_module = $module_id)");
                             
                             if($atable_name) 
                             {
                                    $at->select("atable_name",$atable_name);
                                    if(!$decoupage) $fileName = $atable_name.".sql";
                             }
                             
                             
                             $module_obj = new Module();
                             $module_obj->select("id",$module_id);
                             $module_obj->load();
                             
                             $module_obj_desc = trim($module_obj->valtitre());
                             if(!$decoupage) 
                             {
                                     $sql .= "-- ***********************************************************************************\n";
                                     $sql .= "-- **                        project/system : $module_id - $module_obj_desc \n";
                                     $sql .= "-- ***********************************************************************************\n";
                                     
                             }
                             // crate and use database
                             // $sql .= "
                             
                             $mc = $module_obj->getVal("module_code");
                             if(!$mc) $mc = "module".$module_obj->getId();
                             if((!$atable_id) and (!$decoupage)) $fileName .= $mc."_";
                             
                             if(!$decoupage) $sql .= "CREATE DATABASE IF NOT EXISTS ${server_db_prefix}$mc;\n";
                             if(!$decoupage) $sql .= "USE ${server_db_prefix}$mc;\n";   
                       }
        
        
                       if($sub_module_id)
                       {
                             $at->select("id_sub_module",$sub_module_id);
                             
                             $submodule_obj = new Module();
                             $submodule_obj->select("id",$sub_module_id);
                             $submodule_obj->load();
                             
                             $submodule_obj_desc = trim($submodule_obj->valtitre());
                             
                             $sql .= "-- ***********************************************************************************\n";
                             $sql .= "-- **                       module : $sub_module_id - $submodule_obj_desc \n";
                             $sql .= "-- ***********************************************************************************\n";
                             $mc = $submodule_obj->getVal("module_code");
                             if(!$mc) $mc = "module".$submodule_obj->getId();
                             if((!$atable_id) and (!$decoupage)) $fileName .= $mc."_";  
                                
                       }
                       if((!$atable_id) and (!$decoupage)) $fileName .= "create.sql";
        
                       $sql .= "-- >> mysql -h 127.0.0.1 -u root -p < $sqldir/$fileName";
        
                       $at->select("avail",'Y');
                       
                       $at_list = $at->loadMany($limit = "", $order_by = "id_module asc, id_sub_module asc");
                       
                       foreach($at_list as $atb_id => $atb_obj)
                       {
                             $atb_obj_desc = $atb_obj->getVal("atable_name") . " - " . $atb_obj->getVal("titre_short");
                             $atb_obj_desc2 = $atb_obj->getVal("titre");
                             list($sql_struct,$sql_fk) = $atb_obj->generateSQLStructure();
                             $footer_sql .= "\n".$sql_fk;
                             $sql .= "-- ----------------------------------------------------------------------------------\n";
                             $sql .= "-- --                        table : $atb_id - $atb_obj_desc \n";
                             $sql .= "-- --                        $atb_obj_desc2 \n";
                             $sql .= "-- ----------------------------------------------------------------------------------\n";
                             $mc =  $atb_obj->getModule()->getVal("module_code");

                             //if($mc) $sql .= "use $mc;\n";   
                             $sql .= $sql_struct;
                             $sql .= "\n\n\n";
                             
                             if($decoupage) 
                             {
                                $fileName = $atb_obj->getVal("atable_name").".sql";
                                if($file_to_create)
                                {
                                  AfwFileSystem::write($sqldir."/".$fileName, $sql.$footer_sql);
                                  $footer_sql = "";
                                } 
                                else $log_errors .= "-- $fileName : \n $sql $footer_sql \n";
                                $sql = "";
                                $footer_sql = "";
                                
                             }   
                       }
                       
                       // die("footer_sql = $footer_sql , decoupage = $decoupage");
                       
                       
                       if(!$decoupage) {
                             if($file_to_create) AfwFileSystem::write($sqldir."/".$fileName, $sql.$footer_sql);
                             else $log_errors .= "not created ";
                             
                             $log_errors .= "$fileName : \n $sql $footer_sql \n ";
                       }
                       else
                       {
                             $log_errors .= "-- fk constraints " . $footer_sql;   
                       }
                       
                       if($file_to_create) $log_errors .= "file $fileName generated under $sqldir \n"; 
               
               }
               else
		       $log_errors .=  "folder for sql : $sqldir not found \n"; 
               
               
               return $log_errors;
        }
        
         /**
	 * generateLookupPhpFiles
	 * genere le fichier sql de creation/mise a jour des tables d'un systeme/module 
	 * @param string $dir (chemin de generation de fichier)
	 */
        public function generateLookupPhpFiles($phpdir,$atable_id = null, $module_id=null, $sub_module_id = null)
        {
               global $nb, $prod_dir;
               
               $phpErrors = "";
               
               $file_dir_name = dirname(__FILE__); 
               
               if(AfwFileSystem::isDir($phpdir))
               {
                       
                       
                       
                       $list_php_files_to_copy = "";
                       
                       $fileName = "gen_lookup_".date("YmdHis")."_";
                       
                       $fileName = "gen_php_".date("YmdHis")."_";
                       
                       
                       
                       $at = new Atable();
                       
                       if($atable_id) 
                       {
                           $at->select("id",$atable_id);
                           $fileName .= "table_${atable_id}_";
                       } 
        
                       
                       
                       if($module_id)
                       {
                             $at->where("(id_module = $module_id or id_sub_module = $module_id)");
                             
                             $module_obj = new Module();
                             $module_obj->select("id",$module_id);
                             $module_obj->load();
                             
                             $module_obj_desc = trim($module_obj->valtitre());
                             
                             $mc = $module_obj->getVal("module_code");
                             if(!$mc) $mc = "module".$module_obj->getId();
                             $fileName .= $mc."_";   
                       }
        
        
                       if($sub_module_id)
                       {
                             $at->select("id_sub_module",$sub_module_id);
                             
                             $submodule_obj = new Module();
                             $submodule_obj->select("id",$sub_module_id);
                             $submodule_obj->load();
                             
                             $submodule_obj_desc = trim($submodule_obj->valtitre());
                             
                             $mc = $submodule_obj->getVal("module_code");
                             if(!$mc) $mc = "module".$submodule_obj->getId();
                             $fileName .= $mc."_";  
                                
                       }
                       $fileName .= "errors.txt";
        
                       $at->select("avail",'Y');
                       $at->select("is_lookup",'Y');
                       
                       $at_list = $at->loadMany();
                       
                       foreach($at_list as $atb_id => $atb_obj)
                       {
                             $php = "<?"."php\n";
                             $php .= "return [\n";
                             $table_name = $atb_obj->getVal("atable_name");
                             $atb_obj_desc = $table_name . " - " . $atb_obj->getVal("titre");
                             $arr_lookup = $atb_obj->getLookupData();
                             
                             foreach($arr_lookup as $lookup_code => $lookup_trad) {
                                       $php .= "    '$lookup_code' => '$lookup_trad',\n";
                             }
                             $php .= "];\n";                                

                             
                             AfwFileSystem::write($phpdir."/$table_name.php", $php);
                             $phpErrors .=  "php lookup file $table_name.php generated under $phpdir \n"; 
                       }
                       

                       
                       //AfwFileSystem::write($phpdir."/log/".$fileName, $phpErrors);
                       //$phpErrors .=  "log file $fileName generated under $phpdir/log <br>"; 
               
               }
               else
			$phpErrors .= "folder for php : $phpdir not found"; 
               
               
               return $phpErrors;
        }
        
        public function generateLookupEnumFile($phpdir,$atable_id = null, $module_id=null, $sub_module_id = null)
        {
               global $nb, $prod_dir;
               
               $phpErrors = "";
               
               $file_dir_name = dirname(__FILE__); 
               
               if(AfwFileSystem::isDir($phpdir))
               {
                       
                       
                       
                       $list_php_files_to_copy = "";
                       
                       $fileName = "gen_enum_".date("YmdHis")."_";
                       
                       $fileName = "gen_php_".date("YmdHis")."_";
                       
                       
                       
                       $at = new Atable();
                       
                       if($atable_id) 
                       {
                           $at->select("id",$atable_id);
                           $fileName .= "table_${atable_id}_";
                       } 
        
                       
                       
                       if($module_id)
                       {
                             $at->where("(id_module = $module_id or id_sub_module = $module_id)");
                             
                             $module_obj = new Module();
                             $module_obj->select("id",$module_id);
                             $module_obj->load();
                             
                             $module_obj_desc = trim($module_obj->valtitre());
                             
                             $mc = $module_obj->getVal("module_code");
                             if(!$mc) $mc = "module".$module_obj->getId();
                             $fileName .= $mc."_";   
                       }
        
        
                       if($sub_module_id)
                       {
                             $at->select("id_sub_module",$sub_module_id);
                             
                             $submodule_obj = new Module();
                             $submodule_obj->select("id",$sub_module_id);
                             $submodule_obj->load();
                             
                             $submodule_obj_desc = trim($submodule_obj->valtitre());
                             
                             $mc = $submodule_obj->getVal("module_code");
                             if(!$mc) $mc = "module".$submodule_obj->getId();
                             $fileName .= $mc."_";  
                                
                       }
                       $fileName .= "errors.txt";
        
                       $at->select("avail",'Y');
                       $at->select("is_lookup",'Y');
                       
                       $at_list = $at->loadMany();

                       $php = "<?"."php\n";
                       $php .= "return [\n";

                       
                       foreach($at_list as $atb_id => $atb_obj)
                       {
                             $table_name = $atb_obj->getVal("atable_name");
                             $atb_obj_desc = $table_name . " - " . $atb_obj->getVal("titre");
                             $arr_lookup = $atb_obj->getLookupEnum();
                             $php .= "    '$table_name' => [\n";
                             foreach($arr_lookup as $lookup_id => $lookup_code) {
                                       $php .= "    '$lookup_id' => '$lookup_code',\n";
                             }
                             $php .= "],\n";

                       }
                       
                       $php .= "];\n";
                       AfwFileSystem::write($phpdir."/enum_codes.php", $php);
                       $phpErrors .=  "enum_codes.php file generated under $phpdir \n";         
                       
                       //$phpErrors .= $new_php_file . $list_php_files_to_copy;
                      
                       //echo $new_php_file;
                       //echo $phpErrors;
                       // AfwFileSystem::write($phpdir."/".$fileName, $new_php_file.$phpErrors);
                       // echo "log file $fileName generated under $phpdir <br>"; 
               
               }
               else
		      $phpErrors .= "folder for enum $phpdir not found";  
               
               
               return $phpErrors;
        }
        
         /**
	 * generateModulePhpClasses
	 * genere le fichier sql de creation/mise a jour des tables d'un systeme/module 
	 * @param string $dir (chemin de generation de fichier)
	 */
	public function generateModulePhpClasses($phpdir,$atable_id = null, $module_id=null, $sub_module_id = null, $atable_name = null, $dbstruct_only = false, $dbstruct_outside = false)
        {
               global $nb, $prod_dir, $ROOT_WWW_PATH,$mv_command, $dir_sep;
               
               if(!$dir_sep) $dir_sep = "/";
               
               $file_dir_name = dirname(__FILE__); 
               
               $phpErrors = "";
               
               if(($phpdir=="no-gen") or AfwFileSystem::isDir($phpdir))
               {
                       
                       $list_php_files_to_copy = "";
                       
                       
                       $fileName = "gen_php_".date("YmdHis")."_";
                       
                       
                       
                       $at = new Atable();
                       
                       if($atable_id) 
                       {
                           $at->select("id",$atable_id);
                           $fileName .= "table_${atable_id}_";
                       } 
        
                       
                       
                       if($module_id)
                       {
                             $at->where("(id_module = $module_id or id_sub_module = $module_id)");
                             
                             if($atable_name) 
                             {
                                    $at->select("atable_name",$atable_name);
                             }
                             
                             
                             $module_obj = new Module();
                             $module_obj->select("id",$module_id);
                             $module_obj->load();
                             
                             $module_obj_desc = trim($module_obj->valtitre());
                             
                             
                             $mc = $module_obj->getVal("module_code");
                             if(!$mc) $mc = "module".$module_obj->getId();
                             $fileName .= $mc."_";   
                       }
        
        
                       if($sub_module_id)
                       {
                             $at->select("id_sub_module",$sub_module_id);
                             
                             $submodule_obj = new Module();
                             $submodule_obj->select("id",$sub_module_id);
                             $submodule_obj->load();
                             
                             $submodule_obj_desc = trim($submodule_obj->valtitre());
                             
                             $mc = $submodule_obj->getVal("module_code");
                             if(!$mc) $mc = "module".$submodule_obj->getId();
                             $fileName .= $mc."_";  
                                
                       }
                       $fileName .= "errors.txt";
        
                       $at->select("avail",'Y');
                       
                       $at_list = $at->loadMany();
                       
                       if(count($at_list)>1)
                       {
                            if($mc) $module_code = $mc;
                            else $module_code = "xxx";
                            
                            $root_module_path = $ROOT_WWW_PATH.$module_code;
                            
                            $phpErrors .=  "Many php will be generated to install them proceed like this :
BEFORE GENERATION :
-------------------
1.   remove old files  > $mv_command -f $phpdir/*.php $phpdir/old
2.   remove old translation files before generation > $mv_command -f $phpdir/../trad/trad_ar_*.php $phpdir/../trad/old

AFTER GENERATION :
-------------------
3.   generation install new php files > $mv_command $phpdir/*.php $root_module_path
4.   and install new translation php files > $mv_command $phpdir/../trad/trad_ar_*.php $root_module_path/tr 
\n
\n"; // 
                       }
                       
                        
                       
                       foreach($at_list as $atb_id => $atb_obj)
                       {
                             $module_code = $atb_obj->myModuleCode();
                             $php = "<?"."php\n";
                             $table_name = $atb_obj->getVal("atable_name");
                             $atb_obj_desc = $table_name . " - " . $atb_obj->getVal("titre");
                             list($php_code, $phpErrors_arr, $new_php_file) = $atb_obj->generatePhpClass($dbstruct_only, $dbstruct_outside);
                             
                             //die("php_code=$php_code");
                             $php .= "// ------------------------------------------------------------------------------------\n";
                             $php .= "// ----             auto generated php class of �table� $table_name : $atb_obj_desc \n";
                             $php .= "// ------------------------------------------------------------------------------------\n";
                             $php .= $php_code;
                             $php .= "?".">";
                             
                             foreach($phpErrors_arr as $php_err) 
                             {
                                   $phpErrors .= "   error : ".$php_err ."\n";
                             }
                             //die("php=[$php]");
                             if($phpdir!="no-gen")
                             {
                                $dir_fileName = $phpdir . $dir_sep . $table_name . ".php";
                                AfwFileSystem::write($phpdir."/$table_name.php", $php);
                                $root_module_path = $ROOT_WWW_PATH.$module_code;
                                $phpErrors .=  "php class file $table_name.php generated under $phpdir php code is :
to install it :
******>    $mv_command $dir_fileName $root_module_path 
\n
$php  \n"; // 
                             }
                             else
                             {
                                $phpErrors .=  $php;    
                             }
                       }
                       
               }
               else
		       $phpErrors .= "folder for php classes : $phpdir not found"; 
               
               
               return $phpErrors;
        }
        
        public function generateTrad($dir, $lang, $module_id=null, $submodule_id=null, $tbn=null)
        {
                global $nb, $ROOT_WWW_PATH,$mv_command, $dir_sep;
                
                if(!$dir_sep) $dir_sep = "/";
                
                $phpErrors = "";
		
		if($tbn) {
                     //// require_once("atable.php");
                        $at = new Atable();
                        $at->select("avail",'Y');
                        $at->select("atable_name",$tbn);
                        $at->select("id_module",$module_id);
                        $at_list = $at->loadMany();
                        $tbl_list = array();
                        $this->DB = array();
                        foreach($at_list as $at_id => $at_item)
                        {
                             $tabName = $at_item->getVal("atable_name");
                             $tbl_list[$tabName] = $at_item->myModuleCode();
                             $this->DB[$tabName] = $at_item->getAFieldList(true);
                             $this->SCIS[$tabName] = $at_item->getScis();
                        }
                        //die(var_export($this->DB,true));
                        
                        $source_fields_to_trad = "all active fields of table name ($tbn) and module id ($module_id)"; 
                } 
                elseif($module_id or $submodule_id)
                {
                        // require_once("atable.php");
                        $at = new Atable();
                        $at->select("avail",'Y');
                        if($module_id) $at->where("(id_module = $module_id or id_sub_module = $module_id)");
                        if($submodule_id) $at->select("id_sub_module",$submodule_id);
                        $at_list = $at->loadMany();
                        $tbl_list = array();
                        $this->DB = array();
                        foreach($at_list as $at_id => $at_item)
                        {
                             $tabName = $at_item->getVal("atable_name");
                             $mdl_code = $at_item->myModuleCode();
                             $tbl_list[$tabName] = $mdl_code;
                             $this->DB[$tabName] = $at_item->getAllFieldList();
                             $this->SCIS[$tabName] = $at_item->getScis();
                        }
                        $source_fields_to_trad = "all active fields of tables of module id or submodule id ($module_id)";
                }
		else 
                {
                    /*
                        $this->analyse_DB();
                    $tbl_list = $this->DB;
                    $source_fields_to_trad = "all active fields of tables after analyse_DB";
                    */
                    
                } 
		require_once "afw.php";
                if(($dir=="no-gen") or AfwFileSystem::isDir($dir))
                {
			$tbl_list_txt = "";
                        //die("tbl_list ".var_export($tbl_list,true));
                        foreach($tbl_list as $tabName => $tabModuleCode)
                        {
                                $tabDesc = $this->DB[$tabName];
                                $tabScis = $this->SCIS[$tabName];
                                
                                $tbl_list_txt .= $tabName . ", ";
                                
                                // require_once("atable.php");
                                $atbl = new Atable();
                                $atbl->select("atable_name",$tabName);
                                if($module_id) $atbl->select("id_module",$module_id);
                                if($submodule_id) $atbl->select("id_sub_module",$submodule_id);
                                $atbl->load();
                                
                                // require_once("afield.php");
                                
                                
                                
				$fileName = "trad_${lang}_".strtolower($tabName).".php";
                        	$className = AfwStringHelper::tableToClass($tabName);
                                $classNameLower = strtolower($className);
                                
                                unset($trad_arr);
                                $trad_arr = array();
                                
                                
                                
                                if($lang=="ar")
                                {
                                        $trad_arr["$classNameLower.single"] = $atbl->getVal("titre_u");
                                        $trad_arr["$classNameLower.new"] = "جديد(ة)";
                                        $trad_arr[$tabName] = $atbl->valTitre_short();
                                }
                                else
                                {
                                        $trad_arr["$classNameLower.single"] = $atbl->getVal("titre_u_en");
                                        $trad_arr["$classNameLower.new"] = "new";
                                        $trad_arr[$tabName] = $atbl->valTitre_short_en();
                                }
                                
                                
				unset($tempTdesc);
                                $tempTdesc = array();
                                
                                foreach($trad_arr as $nom_col => $try_trad)
                                {
                                        if($try_trad)
                                        {
                                             $RDesc = "\t\$trad[\"$tabName\"][\"$nom_col\"] = \"$try_trad\";";
                                             array_push($tempTdesc, $RDesc);
                                             
                                        }
                                }
                                
                                
				foreach($tabDesc as $nom_col => $rowDesc)
                                {
                                    if(!$this->isTechnical($nom_col))
                                    {
					$help_trad = "";
                                        $try_trad = "";
                                        if($atbl->getId())
                                        {
                                                    $afld = new Afield();    
                                                    $afld->select("atable_id",$atbl->getId());
                                                    $afld->select("field_name",$nom_col);
                                                    if($afld->load())
                                                    {
                                                            $try_trad = $afld->getShortDisplay($lang);
                                                            if($lang=="ar") $help_text_field = "help_text";
                                                            else $help_text_field = "help_text_en";
                                                            $help_trad = $afld->getVal($help_text_field);
                                                            
                                                            if($lang=="ar") $unit_field = "unit";
                                                            else $unit_field = "unit_en";
                                                            
                                                            $unit_trad = $afld->getVal($unit_field);
                                                            
                                                            if((!$try_trad) and ($lang=="en"))
                                                            {
                                                                 $afld->set("titre_short_en","--");
                                                                 $afld->update();
                                                            }
                                                    }
                                                    else $try_trad = "table $tabName need to be pagged can't find php column [$nom_col]"; 
                                                    
                                                    
                                        }
                                        
                                        if((!$try_trad) || ($try_trad==$nom_col))
                                        {
                                            $try_trad = AfwLanguageHelper::tarjem($nom_col, $lang, null, $tabName);
                                        } 
                                        
                                        if((!$try_trad) || ($try_trad==$nom_col))
                                        {
                                             if($trad_arr[$nom_col])
                                             {
                                                    $try_trad = $trad_arr[$nom_col];
                                             }
                                             else $try_trad = "";
                                             
                                        }
                                        
                                        
                                        if($try_trad)
                                        {
                                             $RDesc = "\t\$trad[\"$tabName\"][\"$nom_col\"] = \"$try_trad\";";
                                             array_push($tempTdesc, $RDesc);
                                             
                                        }
                                        
                                        if($help_trad)
                                        {
                                             $RDesc = "\t\$trad[\"$tabName\"][\"${nom_col}_help\"] = \"$help_trad\";";
                                             array_push($tempTdesc, $RDesc);
                                             
                                        }
                                        
                                        if($unit_trad)
                                        {
                                             $RDesc = "\t\$trad[\"$tabName\"][\"${nom_col}_unit\"] = \"$unit_trad\";";
                                             array_push($tempTdesc, $RDesc);
                                             
                                        }
                                        
                                        
                                        
                                        
                                        
                                        
                                    }    
					 
				}
                                
                                foreach($tabScis as $tabSci_id => $tabSci_item)
                                {
                                    $step_num = $tabSci_item->getVal("step_num");
                                    $step_name = $tabSci_item->getVal("step_name_$lang");
                                    
                                    $RDesc = "\t\$trad[\"$tabName\"][\"step$step_num\"] = \"$step_name\";";
                                    array_push($tempTdesc, $RDesc);
                                }
                                
				$TDesc = implode("\n", $tempTdesc);
                                $php_code = "<?php\n$TDesc\n?".">";
                                if($dir!="no-gen")
                                {
                                        AfwFileSystem::write($dir."/".$fileName, $php_code);
                                        $root_module_path = $ROOT_WWW_PATH.$tabModuleCode;
                                        $dir_fileName = $dir . $dir_sep . $fileName;
                                        $phpErrors .= "trad file : $fileName generated under $dir php code is :
to install translation ($source_fields_to_trad) to [$tabModuleCode] module :
******>    $mv_command $dir_fileName $root_module_path/tr    
                                
$php_code\n";
                                }
                                else
                                {
                                        $phpErrors .=  $php_code;    
                                }
                                $nb++;
			}
		
		
                
                }
                else
			$phpErrors .= "folder [$dir] for trad not found";
                        
                return $phpErrors;        
	}
        
	
        
        
        
        /*
        
        public function generateSQLInit($dir, $lang)
        {
                global $tbl, $nb;
                
		$this->analyse_DB();
		if($tbl) $tbl_list[$tbl] = true;
		else $tbl_list = $this->DB;
		require_once "afw.php";
		if(AfwFileSystem::isDir($dir))
                {
                        $fileName = "corrections.sql";
                        
                        unset($tempTdesc);
                        $tempTdesc = array();
			
                        foreach($tbl_list as $tabName => $tabOk)
                        {
                                $tabDesc = $this->DB[$tabName];
                                
                        	$className = AfwStringHelper::tableToClass($tabName);
                                $classNameLower = strtolower($className);
                                
                                $RDesc = "   update $tabName set avail = 'Y' where avail is null or avail = '';";
                                array_push($tempTdesc, $RDesc);
                                
                                $RDesc = "   -- update $tabName set version = 0 where version is null;";
                                array_push($tempTdesc, $RDesc);                                        
                                
                                
                                
				foreach($tabDesc as $nom_col => $rowDesc)
                                {
                                    if($rowDesc["TYPE"]=="\"FK\"")
                                    {
                                        $RDesc = "   update $tabName set $nom_col = 0 where $nom_col is null;";
                                        array_push($tempTdesc, $RDesc);
                                    }  
				}
				
                                $nb++;
			}
                        
                        $TDesc = implode("\n", $tempTdesc);
			AfwFileSystem::write($dir."/".$fileName,$TDesc);
                        if($TDesc) echo "$fileName generated under $dir <br>";
		
		
                
                }else
			echo "erreur dir";
	}
        */
        
        /**
	 * generate
	 * genere les fichier .php de description des tables 
	 * @param string $dir (chemin de generation de fichier)
	 */

         /*
	public function generateFKC($dir)
        {
                global $tbl, $nb;
                
		$this->analyse_DB();
		if($tbl) $tbl_list[$tbl] = true;
		else $tbl_list = $this->DB;
                
                $FKC = array();
                
                foreach($tbl_list as $tabName => $tabOk)
                {
                        $className = AfwStringHelper::tableToClass($tabName);
                        $fileName = AFWObject::table ToFile($tabName);
                        if(file_exists($fileName))
                        {
                             include_once $fileName;
                             $tabDesc = $className::getDbStructure();   
                        }
                        else
                        {
                             $tabDesc = array();
                        }
                        
			foreach($tabDesc as $columnName => $rowDesc)
                        {
                                $__type = trim($rowDesc["TYPE"],"\"");
                                $__cat = trim($rowDesc["CATEGORY"],"\"");
                                $__ans = trim($rowDesc["ANSWER"],"\"");
                                $__imp = trim($rowDesc["IMPORTANT"],"\"");
                                if(!$__imp) $__imp = "IN";
                                
                                $__imp_fc = $__imp[0];
                                
                                if(($__type=="FK") and (!$__cat) and ($__ans))
                                {
                                    if($__ans=="auser") $del_action = "not-avail";
                                    else if ($__imp_fc=="I") $del_action = "not-avail";
                                    else if ($__imp_fc=="C") $del_action = "not-allow";
                                    else if ($__imp_fc=="S") $del_action = "delete";
                                    else $del_action = "not-avail";
                                    
                                    $set_del_action = false;
                                    $fk_fileName = "fk_".AFWObject::table ToFile($__ans);
                                    if(file_exists($fk_fileName))
                                    {
                                             unset($FK_CONSTRAINTS);
                                             include $fk_fileName;
                                             //echo "included $fk_fileName<br>\n";
                                              
                                               
                                             if($FK_CONSTRAINTS[$tabName][$columnName]["DEL-ACTION"] != $del_action) 
                                             {
                                                $set_del_action = true;
                                                $to_merge_col = " // to merge";
                                                
                                                //echo "FK_CONSTRAINTS[$tabName][$columnName][DEL-ACTION] : '".$FK_CONSTRAINTS[$tabName][$columnName]["DEL-ACTION"]. "' != than '" . $del_action ."'      <br>\n";
                                             }      
                                    }
                                    else 
                                    {
                                        $set_del_action = true;
                                        $to_merge_col = "";
                                    }     
                                    if($set_del_action) 
                                    {
                                        $FKC[$__ans][$tabName][$columnName]["DEL-ACTION"] = $del_action;
                                        $FKC[$__ans][$tabName][$columnName]["TO-MERGE"] = $to_merge_col;
                                    }    
                                }
                                
			}
                }        
		
		if(AfwFileSystem::isDir($dir))
                {
		    foreach($FKC as $y_table => $FKC_ITEM)  	
                    {
                        $y_fileName = AFWObject::table ToFile($y_table);
			$y_className = AfwStringHelper::tableToClass($y_table);
                        
                        $tempYdesc = array();
                                    
                        foreach($FKC_ITEM as $x_table => $FKC_ITEM_CONSTRAINTS)
                        {
                                $TDesc_header = "\t\$FK_CONSTRAINTS[\"$x_table\"] = array(\n";
				$to_merge_col = "";
				unset($tempTdesc);
                                $tempTdesc = array();
				foreach($FKC_ITEM_CONSTRAINTS as $x_colName => $rowDesc)
                                {
					$RDesc = "\t\t\t\"".$x_colName."\" => array(";
					$tempRdesc = array();
					foreach($rowDesc as $prop => $val)
                                        {
                                                    if($prop=="TO-MERGE")
                                                    {
                                                        $to_merge_col = $val;        
                                                    }
                                                    else
                                                    {
                                                        array_push($tempRdesc, "\"".$prop."\" => '$val'");
                                                    }   
                                        }
					       
					$RDesc .= implode(", ", $tempRdesc).")";
					array_push($tempTdesc, $RDesc);
                                         
				}
				$TDesc = implode(",\n", $tempTdesc);
                                
                                $TDesc_footer = "\n\t); $to_merge_col";
                        
                                $TDesc_total = $TDesc_header . $TDesc . $TDesc_footer;
				
                                array_push($tempYdesc, $TDesc_total); 
			}
                        
                        $YDesc = implode("\n", $tempYdesc); 
                        
                        $y_fk_file_name = "fk_$y_fileName";
                        
                        AfwFileSystem::write("$dir/$y_fk_file_name", 
"<?"."php
$YDesc
?".">"
				);
                                echo "<b>not empty $y_fk_file_name generated under $dir </b><br>";
                                $nb++;
		    }
		
                
                }else
			echo "erreur dir";
	}
	*/
	/**
	 * analyse_DB
	 */
        /* osbo
	private function analyse_DB(){
		$this->connect();
		$query = "SHOW TABLES FROM ".self::$database;
		$res = AfwMysql::query($query, self::$link);
		$DB_show = $this->mysql_fetch_all($res);
		foreach($DB_show as $table_name){
			 $this->DB[$table_name[0]] = array();
			 $query = "DESC ".$table_name[0];
			 $res = mysqli_query($query);
			 $table_desc = $this->mysql_fetch_all($res);
			 foreach($table_desc as $row_desc){
			 	 $colname = $row_desc["Field"];
			 	 $type = $row_desc["Type"];
			 	 $key = $row_desc["Key"]; 
			 	 $this->DB[$table_name[0]][$colname] = $this->row_transformer($colname, $type, $key);
			 }
		}
	}*/
	
	/**
	 * mysql_fetch_all
	 * Retourne un array contenant le typle selectionnee par l'execution d'une requette 
	 * @param mysqli_result $res (resultat de l'execution d'une requette)
	 */
        private function mysql_fetch_all($res) {
		while($row=mysqli_fetch_array($res))
			$return[] = $row;
		return $return;
	}
	
        
        private function isTechnical($colname) {
                if($colname=="id_aut") return true;
                if($colname=="date_aut") return true;
                if($colname=="id_mod") return true;
                if($colname=="date_mod") return true;
                if($colname=="id_valid") return true;
                if($colname=="date_valid") return true;
                if($colname=="avail") return true;
                if($colname=="version") return true;
                if($colname=="update_groups_mfk") return true;
                if($colname=="delete_groups_mfk") return true;
                if($colname=="display_groups_mfk") return true;
                if($colname=="sci_id") return true;
                if($colname=="TIMESTAMP") return true;
                
                
                
        }
        
        
        private function getTechnicalAT($colname) {
                if($colname=="id_aut") return "\"auser\"";
                if($colname=="id_mod") return "\"auser\"";
                if($colname=="id_valid") return "\"auser\"";
                if($colname=="sci_id") return "\"scenario_item\"";
                
                if($colname=="update_groups_mfk") return "\"ugroup\"";
                if($colname=="delete_groups_mfk") return "\"ugroup\"";
                if($colname=="display_groups_mfk") return "\"ugroup\"";
                
                
                return "";
        }
	/**
	 * row_transformer
	 * Retourne un array contenant le info "TYPE" ,"ANSWER" d'unee colonne
	 * @param string $colname
	 * @param string $type
	 * @param string $key
	 */
	private function row_transformer($colname, $type, $key){
		$row = array();
                $row["IMPORTANT"]="\"IN\"";
                if($this->isTechnical($colname))
                {
                        $row["SHOW-ADMIN"] = "true";
                        $row["RETRIEVE"] = "false";
                        $row["EDIT"] = "false";
                        $at = $this->getTechnicalAT($colname);
                        if($at) $row["ANSWER"] = $at; 
                }
                else
                {
                        $row["SHOW"] = "true";
                        $row["RETRIEVE"] = "false";
                        $row["EDIT"] = "true";
                }
                if($colname=="avail")
                {
                        $row["DEFAULT"]="\"Y\"";
                        $row["EDIT-ADMIN"] = "true";
                        $row["QEDIT"] = "false";
                }
                
                if($colname=="titre")
                {
                        $row["UTF8"]="true";      
                        $row["SIZE"]=255;
                }
                
                if($colname=="titre_short")
                {
                        $row["UTF8"]="true";
                        $row["SIZE"]=40;
                        $row["SHORTNAME"]="\"title\"";      
                }
                
                if(se_termine_par($colname,"_ar"))
                {
                        $row["UTF8"]="true";      
                }
                
		if($key == "PRI")
		        $row["TYPE"] = '"PK"';
		else{
			$exp_name = explode("_", $colname);
			if(strpos(strtoupper($type), "INT")!== false && strtoupper($exp_name[0]) == "ID"){
				unset($exp_name[0]);
		        	$row["TYPE"] = '"FK"';
		        	$row["ANSWER"] = $this->getTechnicalAT($colname);
		        	if(!$row["ANSWER"]) $row["ANSWER"] = '"'.strtolower(implode("_", $exp_name)).'"';
			}elseif(strpos(strtoupper($type), "INT")!== false && strtoupper($exp_name[count($exp_name)-1]) == "ID"){ 
				unset($exp_name[count($exp_name)-1]);
		        	$row["TYPE"] = '"FK"';
		        	$row["ANSWER"] = $this->getTechnicalAT($colname);
		        	if(!$row["ANSWER"]) $row["ANSWER"] = '"'.strtolower(implode("_", $exp_name)).'"';
			}elseif(strtoupper($exp_name[0]) == "MFK"){
				unset($exp_name[0]);
		        	$row["TYPE"] = '"MFK"';
		        	$row["ANSWER"] = $this->getTechnicalAT($colname);
		        	if(!$row["ANSWER"]) $row["ANSWER"] = '"'.strtolower(implode("_", $exp_name)).'"';
			}elseif(strtoupper($exp_name[count($exp_name)-1]) == "MFK"){ 
				unset($exp_name[count($exp_name)-1]);
		        	$row["TYPE"] = '"MFK"';
                                $row["ANSWER"] = $this->getTechnicalAT($colname);
		        	if(!$row["ANSWER"]) $row["ANSWER"] = '"'.strtolower(implode("_", $exp_name)).'"';
			}else{
				$type_res = $this->type_transformer($type);
				$row["TYPE"] = '"'.$type_res.'"';
			}		
		}
                if($row["TYPE"] == '"FK"') {
                        $row["SIZE"]=40;
                        $row["DEFAULT"]=0;
                }
		return $row;
	}
	
	/**
	 * type_transformer
	 * Retourne un string contenant le type tuple AFW ("INT", "TEXT", "DATE", "YN") sinon retourne "INCONNU"
	 * @param string $type
	 */
	private function type_transformer($type){
		if(strpos(strtoupper($type), "CHAR(1)")!== false)
			return "YN";
		elseif(strpos(strtoupper($type), "CHAR")!== false || strpos(strtoupper($type), "TEXT")!== false)
			return "TEXT";
		elseif(strpos(strtoupper($type), "DATE")!== false || strpos(strtoupper($type), "TIME")!== false ||
			strpos(strtoupper($type), "YEAR")!== false)
			return "DATE";
		elseif(strpos(strtoupper($type), "INT")!== false || strpos(strtoupper($type), "FLOAT")!== false ||
			strpos(strtoupper($type), "REAL")!== false || strpos(strtoupper($type), "DECIMAL")!== false ||
			strpos(strtoupper($type), "DOUBLE")!== false)
			return "INT";
		else{
			$enum = substr(strtoupper($type), 0, 4);
			if($enum == "ENUM"){
				$choix = substr($type, 5, -1);
				$choix = explode(',', strtoupper($choix));
				if(count(array_diff($choix ,array("'Y'", "'N'", "'W'"))) == 0){
					return "YN";
				}else
					return "TYPE ".strtoupper($type)." INCONNU";
			}else
				return "TYPE ".strtoupper($type)." INCONNU";
		}
	}
        
        
        /**
	 * generateModuleSpecialLanguage
	 * genere le fichier sql de creation/mise a jour des tables d'un systeme/module 
	 * @param string $dir (chemin de generation de fichier)
	 */
	public function generateModuleSpecialLanguage($file_path, $module_id, $special_lang, $atable_id=0,$atable_name="")
        {
               global $nb, $show_res, $show_ignored;
               
               $log_errors = "";

               $file_dir_name = dirname(__FILE__); 
               
               if((!AfwFileSystem::isDir($file_path)) and (!$show_res))
               {
                    $log_errors .=  "// folder $file_path not found \n"; 
               }
                       
               if($show_res) $file_to_create = false;
               else $file_to_create = AfwFileSystem::isDir($file_path); 
               
               $fileName = "gen_php_".date("YmdHis")."_";
               
               
               
               $module_obj = new Module();
               $module_obj->load($module_id);
               $module_code = $module_obj->getVal("module_code");
               $module_obj_desc = trim($module_obj->valtitre());
               $id_module_parent = $module_obj->getVal("id_module_parent");
               
               
               
               // selction des tables
               $at = new Atable();
               $at->where("((id_module = $module_id or id_sub_module = $module_id) or (is_entity='N' and is_lookup='Y' and id_module in (select id from module where id_module_parent=$id_module_parent)))");
               $at->select("avail",'Y');
               if($atable_id) 
               {
                   $at->select("id",$atable_id);
                   $at_restricted = new Atable();
                   $at_restricted->load($atable_id);
                   $atable_name = $at_restricted->getVal("atable_name");
                   $fileName = $special_lang."_table_".$atable_name."_".date("YmdHis")."."; 
               }
               elseif($atable_name) 
               {
                   $at->select("atable_name",$atable_name);
                   $fileName = $special_lang."_table_".$atable_name."_".date("YmdHis").".";      
               }
               else
               {
                   $fileName = $special_lang."_module_".$module_code."_".date("YmdHis").".";
               }
               
               $at_list = $at->loadMany($limit = "", $order_by = "id_module asc, id_sub_module asc, atable_name asc");
               /*
               foreach($at_list as $at_id => $at_item) echo "table ($at_id) : " . $at_item.($at_item->_isEnum() ? "Enum":"NotEnum")."  <br>\n";
               die();*/
               require_once "slang_specification_$special_lang.php";
               
               foreach($slang_syntax_files[$special_lang] as $special_lang_ext)
               {
                   $res = "";
                   $res .= $module_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["module_header"]);
                   if($slang_syntax_extentions[$special_lang][$special_lang_ext]["entity"])
                   {
                           foreach($at_list as $atb_id => $atb_obj)
                           {
                                 $atb_obj->related_module =& $module_obj;
                                 $atb_obj_name = $atb_obj->getVal("atable_name");
                                 $whyTableShouldBeIgnored = tableShouldBeIgnored($special_lang,$special_lang_ext,$atb_obj,$slang_syntax_extentions);
                                 
                                 if(!$whyTableShouldBeIgnored)
                                 {
                                         $res .= $atb_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["table_header"]);
                                         $afld_list = $atb_obj->getAFieldListOriginal();
                                         foreach($afld_list as $afld_id => $afld_obj)
                                         {
                                             $afld_obj_name = $afld_obj->getVal("field_name");
                                             $afld_obj_id = $afld_obj->getId();
                                             if(!fieldShouldBeIgnored($special_lang,$special_lang_ext,$afld_obj,$slang_syntax_extentions))
                                             {
                                                 if($show_ignored) $res .= "// not ignored field $atb_obj_name.$afld_obj_name($afld_obj_id) for lan $special_lang extension $special_lang_ext ! \n";
                                                 $res .= $afld_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["field_header"]);
                                                 if($slang_syntax[$special_lang][$special_lang_ext]["field_body"]) $res .= attributeSyntaxed($afld_obj,$special_lang, $slang_syntax_fld_type_arr, $slang_syntax_fld_type_method_arr);
                                                 $res .= $afld_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["field_footer"]);
                                             }
                                             elseif($show_ignored) $res .= "// ignored field $atb_obj_name.$afld_obj_name($afld_obj_id) for lan $special_lang extension $special_lang_ext ! \n";  
                                         }
                                         
                                         $trim_cars = $slang_syntax[$special_lang][$special_lang_ext]["trim_before_table_footer"];
                                         if($trim_cars)
                                         {
                                             foreach($trim_cars as $trim_car) $res = trim($res,$trim_car);
                                         }
                                         
                                         $res .= $atb_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["table_footer"]);
                                }         
                                elseif($show_ignored) $res .= "// ignored table $atb_obj_name ($atb_obj) for lan $special_lang extension $special_lang_ext : $whyTableShouldBeIgnored! \n";
                           }
                   }
                   
                   if($slang_syntax_extentions[$special_lang][$special_lang_ext]["relation"])
                   {
                           foreach($at_list as $atb_id => $atb_obj)
                           {
                              $atb_obj->related_module =& $module_obj;
                              $rel_list = $atb_obj->getMyInternalRelations();
                              $rel_list_after_ignore = array(); 
                              foreach($rel_list as $rel_id => $rel_obj)
                              {
                                   $rel_field_obj = $rel_obj["field"];
                                   if(!fieldShouldBeIgnored($special_lang,$special_lang_ext,$rel_field_obj,$slang_syntax_extentions))
                                   {
                                       $rel_list_after_ignore[$rel_id] = $rel_obj;
                                   }  
                              }
                              
                              
                              // $res .= "table ($atb_id) : ".$atb_obj."\n\n";
                              if(count($rel_list_after_ignore)>0)
                              {   
                                 $res .= $atb_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["relation_table_header"]);
                                 foreach($rel_list_after_ignore as $rel_id => $rel_obj)
                                 {
                                     $rel_XTable_obj = $rel_obj["totable"];
                                     $rel_YTable_obj = $rel_obj["fromtable"];
                                     $rel_field_obj = $rel_obj["field"];
                                     $afld_obj_name = $rel_field_obj->getVal("field_name");
                                     $afld_obj_id = $rel_field_obj->getId();
                                     
                                     $atb_obj_name = $rel_YTable_obj->getVal("atable_name");
                                     if(!fieldShouldBeIgnored($special_lang,$special_lang_ext,$rel_field_obj,$slang_syntax_extentions))
                                     {
                                         if($show_ignored) $res .= "// not ignored field $atb_obj_name.$afld_obj_name($afld_obj_id) for lan $special_lang extension $special_lang_ext ! \n";
                                         $res .= $rel_field_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["1.1.relation_field_before_YTable_header"]);
                                         $res .= $rel_YTable_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["1.2.relation_YTable_header"]);
                                         $res .= $rel_field_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["1.3.relation_field_after_YTable_header"]);

                                         $res .= $rel_field_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["2.1.relation_field_before_XTable_header"]);
                                         $res .= $rel_XTable_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["2.2.relation_XTable_header"]);
                                         $res .= $rel_field_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["2.3.relation_field_after_XTable_header"]);

                                         $res .= $rel_XTable_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["3.1.relation_XTable_body"]);
                                         $res .= $rel_YTable_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["3.2.relation_YTable_body"]);

                                         $res .= $rel_field_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["4.1.relation_field_before_YTable_footer"]);
                                         $res .= $rel_YTable_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["4.2.relation_YTable_footer"]);
                                         $res .= $rel_field_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["4.3.relation_field_after_YTable_footer"]);
                                         
                                         $res .= $rel_field_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["5.1.relation_field_before_XTable_footer"]);
                                         $res .= $rel_XTable_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["5.2.relation_XTable_footer"]);
                                         $res .= $rel_field_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["5.3.relation_field_after_XTable_footer"]);
                                         
                                     }
                                     elseif($show_ignored) $res .= "// ignored field $atb_obj_name.$afld_obj_name($afld_obj_id) for lan $special_lang extension $special_lang_ext ! \n"; 
                                 }
                                 $trim_cars = $slang_syntax[$special_lang][$special_lang_ext]["trim_before_relation_table_footer"];
                                 if($trim_cars)
                                 {
                                     foreach($trim_cars as $trim_car) $res = trim($res,$trim_car);
                                 } 
                                 
                                 
                                 
                                 $res .= $atb_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["relation_table_footer"]);
                                 
                                     
                                 
                              }
                              /* else $res .= "no internal relation for " . $atb_obj."\n\n";*/   
                           }
                   }
                   
                   $res .= $module_obj->decodeTpl($slang_syntax[$special_lang][$special_lang_ext]["module_footer"]);
                   
                   $full_file_name = $file_path."/".$fileName.$special_lang_ext;

                   if($file_to_create)
                   {
                        AfwFileSystem::write($full_file_name, $res);
                   } 
                   else $log_errors .= "// -- $full_file_name : \n $res \n";     

               }
               
               if($file_to_create) $log_errors .= "file $full_file_name has been successfully generated."; 
               
               
               return $log_errors;
        }
}
 


?>