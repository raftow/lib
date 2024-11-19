<?php

class AFWDebugg {

    private static $debugg = array();
    private static $fileName;
    private static $pathName;   
    private static $modeBatch; 
    private static $enabled = true;
    private static $nl = "\n";
    private static $sl = "";
    private static $el = "";
    private static $sep = "/";

    public static function initialiser($pathName,$fileName) {
        if(!$pathName) self::defaultLogPath();
        self::$pathName = ($pathName) ? $pathName : '';
        self::$fileName = ($fileName) ? $fileName : 'debugg.txt';
        self::$modeBatch = false;
        self::log(" --------------- Debut session : ".date("Y-m-d H:i:s")." --------------- ");
    }    
    
    public static function log_file_name()
    {
         $logfile = self::$pathName.self::$sep.self::$fileName;
         
         return $logfile;
    } 
    
    public static function log($var, $varexport=false) 
    {       
       
        if(!self::$enabled) return;

        if($varexport) $var = var_export($var,true);
        
        if(self::$modeBatch) 
        {
                echo self::$sl.$var.self::$el.self::$nl;
        } 
        else 
        {
                /*
                echo "log: modeBatch = ".$modeBatch."<br> \n";
                echo "log: var = ".$var."<br> \n";
                echo "log: pathName = ".self::$pathName."<br> \n";
                echo "log: fileName = ".self::$fileName."<br> \n";
                */
                self::createHeaderIfFirstTime();
                $logfile = self::logFilePath();
                
                if($logfile and ($logfile != "/"))
                {
                    $wrt = AfwFileSystem::write($logfile, $var."\n", 'append');
                    if(AfwSession::config("MODE_DEVELOPMENT", false) and ($var and !$wrt)) die("failed to write [$var] to [$logfile]<br>");
                }
                
                
                //@todourgent if($var and !$wrt) die("failed to write [$var] to [$logfile]<br>");
                // else die("success to write [$var] to [$logfile]<br>");
        }
    }
    
    /**
         * print_str
         * Print string switch color
         * @param string $str
         * @param string $color
         */
    public static function print_str($str, $color = "") 
    {
	if(self::$modeBatch) {
                switch ($color) {
        		case "deb" :
        			echo "\033[0;34m";
        			break;
        
        		case "err" :
        			echo "\033[0;31m";
        			break;
        
        		case "inf" :
        			echo "\033[0;36m";
        			break;
        
        		case "obl" :
        			echo "\033[0;32m";
        			break;
        
        		case "std" :
        		default:
        			echo "\033[0;39m";
        			break;
        
        		case "war" :
        			echo "\033[0;33m";
        			break;
        				
        		case "xxx" :
        			echo "\033[0;35m";
        			break;
        	}
        	echo $str;
        	echo "\033[0;39m";
        } else {
                self::log($str);
        }
    }
    
            

    public static function setModeHtml() {
        self::$modeBatch=true;
        self::$sl="<!-- ";
        self::$el=" -->";
        self::$nl="\n---------------------------------------------<br>\n";
    }    


    public static function setModeBatch() {
        self::$modeBatch=true;
        self::$sl="";
        self::$el="";
        self::$nl="\n";
    }    

    public static function setEnabled($enable) {
        self::$enabled=$enable;
    }    




    public static function get() 
    {
        $content = "";
        foreach(self::$debugg as $id=>$log) 
        {
            $content.="$log\n"; 
        }
        return $content;
    }
    
    public static function logFilePath()
    {
        if(trim(self::$fileName) and trim(self::$pathName)) 
        {
            return trim(self::$pathName."/".self::$fileName);
        }
        else return "";
    }

    public static function pathExistsButFileToCreate()
    {
        $fileFullPathName = self::logFilePath();
        if(!$fileFullPathName) return null;
        return ((AfwFileSystem::isDir(self::$pathName)) and (!AfwFileSystem::exists($fileFullPathName)));
    }

    public static function genereHeader($fileFullPathName)
    {
        return "/**
        *
        *  Creation date : ".date('d/m/Y H:i:s')."
        *  Filename      : ".$fileFullPathName."
        *
        **/\n\n";
    }

    public static function createHeaderIfFirstTime() 
    {
            
            if((!self::$modeBatch) and self::pathExistsButFileToCreate())
            {
                $fileFullPathName = self::logFilePath();                
                AfwFileSystem::write($fileFullPathName, self::genereHeader($fileFullPathName)."\n", 'append');
            }     
    }    

    public static function defaultLogPath() 
    {
            $DEBUGG_SQL_DIR = AfwSession::config("DEBUGG_SQL_DIR","");
            return AfwSession::config("LOG_PATH",$DEBUGG_SQL_DIR);
    }

    public static function startDebuggPage() 
    {        
        $dtm = date("YmdHis");
        $logbl = substr(md5($_SERVER["HTTP_USER_AGENT"] . "-" . date("Y-m-d")),0,10);
        $my_debug_file = "debugg_before_login_$logbl"."_$dtm.log";
        //die("AFWDebugg::initialiser(".$DEBUGG_SQL_DIR.$my_debug_file.")");
        AFWDebugg::initialiser("", $my_debug_file);
        AFWDebugg::setEnabled(true);
    }

}

