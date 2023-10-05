<?php
// This class DisplayerFactory is obsolete starting from v2.0 of momken framework
/* class Displayer Factory
{
	
        public static function getCurWebModule()
        {
              global $_SERVER;
                        
                $phpself = trim($_SERVER['PHP_SELF'],'/');
                $phpself_arr = explode('/',$phpself);
                return strtolower($phpself_arr[0]);
                
        }
        
        
        public static function getInstance($name, $pk, $spk,$CurrModule='', $critere = 'A-Z')
        {
                $objme = AfwSession::getUserConnected();
            
		$OriginCurrModule = $CurrModule;
                // die("static function getInstance($name, $pk, $spk,$CurrModule, $critere)");
                $file_dir_name = dirname(__FILE__); 
                if(!$CurrModule)
                {
                        $CurrModule = self::getCurWebModule();
                }
                if(!$CurrModule) $CurrModule = "pag";
                
                //require_once "afw.php";
		$cl_chaines = preg_split('/(?=['.$critere.'])/', $name, -1, PREG_SPLIT_NO_EMPTY);
		$file       = strtolower(implode('_',$cl_chaines).'.php');
		if(!empty($spk))
			$file = $spk.$file;
		if(!empty($pk))
			$file = $pk.$file;
                if($CurrModule)
                    $Main_Page_path = "$file_dir_name/../$CurrModule";
                else
                    $Main_Page_path = "$file_dir_name";

                $file_full_path = "$Main_Page_path/$file";
                
                $try_pag_module = false;
                
		//die("file_full_path : $file_full_path");
                if(!(file_exists($file_full_path)))
                {
			if($try_pag_module and ($CurrModule!="pag"))
                        {
                             return self::getInstance($name, $pk, $spk, 'pag', $critere);
                        }
                        else
                        {   
                                
                                $objme->_error("getInstance(cl=$name, pk=$pk, spk=$spk, $OriginCurrModule, $critere) Le  Fichier $file_full_path n'existe pas dans le module ($OriginCurrModule or $CurrModule)<br/>");
        			return null;
                        }
		}

		
                require_once "$Main_Page_path/$file";

		$template = "";

		if(!class_exists($name)){
			//print("Cette classe n'existe pas <br/>");
			return null;
		}
		$return = array(
				'file'     => $file,
				'class'    => $name,
				'template' => $template
			);
		return $return;
	}
}
*/
?>