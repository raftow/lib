<?php

class AFWRoot {

        public function __toString()
        {
               return "afw-root-imp"; 
        }

        public static function getEnumTable(
                $answer,
                $table = '',
                $attribut = '',
                $obj = null
            ) 
        {
                //echo "call to getEnumTable($answer,$table,$attribut)<br>";
                if ($answer == 'FUNCTION') 
                {
                        if (!$attribut) {
                        self::simpleError(
                                "getEnumTable need attribut name for FUNCTION dynamic answers (table = $table) obj = " .
                                var_export($obj, true)
                        );
                        }
                        $method = "list_of_$attribut";
                        $object_method = "my_list_of_$attribut";
                        if (!$table) {
                        self::simpleError(
                                'table param is mandatory in getEnumTable method'
                        );
                        }
                        $className = self::tableToClass($table);
                        if ($obj) {
                                $return = $obj->$object_method();
                        } else {
                                $return = $className::$method();
                        }
                        // echo "call to $className::$method() return [";
                        // print_r($return);
                        // echo "]";
                        // self::simpleError("getEnumTable($answer,$table,$attribut,obj: ".var_export($obj,true).")");
                } 
                else 
                {
                        $return = self::afw_explode($answer);
                }

                return $return;
        }

        public static function afw_explode($answer, $sep1 = '|', $sep2 = ',')
        {
                $return = [];
                $rows = explode($sep1, $answer);
                $mypk_counter = 1;
                foreach ($rows as $row) {
                list($mypk, $myval) = explode($sep2, $row);

                if (!$myval) {
                        $myval = $mypk;
                        $mypk = $mypk_counter;
                        $mypk_counter++;
                }

                $return[$mypk] = $myval;
                }

                return $return;
        }

        public static function afw_export($arr, $object_class_and_display_only=true)
        {
                $return = "(";
                foreach($arr as $ind => $arr_item)
                {
                        $return .= " $ind => ";  
                        if(is_array($arr_item))      
                        {
                                $arr_item_desc = self::afw_export($arr_item, $object_class_and_display_only);
                        }
                        elseif(is_object($arr_item))
                        {
                                $arr_item_desc = get_class($arr_item)."->id = ".$arr_item->id;
                        }
                        else
                        {
                                $arr_item_desc = var_export($arr_item,true);
                        }
                        $return .= " $arr_item_desc ,";
                }

                $return .= ")";

                return $return;
        }

	/**
	 * tableToClass
	 * Converts tableName to className
	 * @param string $tableName
	 */
	public static function tableToClass($tableName) {
		$dot_position = strpos($tableName, ".");
		if($dot_position !== false) {
			$tableName = substr($tableName, $dot_position);
		}
		$str = str_replace('_', ' ', strtolower($tableName));
		$str = ucwords(strtolower($str));
		$str = str_replace(' ', '', $str);
		return $str;
	}

        public static function classToTable($className) 
        {
                 return self::fileTotable(self::classToFile($className));
        }
        
        /**
	 * classToFile
	 * Convert tableName to PHP FileName
	 * @param string $tableName
	 */
	public static function classToFile($className) 
        {
                $critere = 'A-Z';
                ini_set("pcre.jit", 0);
                $cl_chaines = preg_split('/(?=['.$critere.'])/', $className, -1, PREG_SPLIT_NO_EMPTY);
		$file       = strtolower(implode('_',$cl_chaines).'.php');
                
                return $file;
	}

	/**
	 * fileToTable
	 * Convert fileName to PHP tableName
	 * @param string $fileName
	 */
	public static function fileTotable($fileName) {
		return substr(strtolower($fileName), 0, strlen($fileName)-4);
	}

	/**
	 * tableToFile
	 * Convert tableName to PHP FileName
	 * @param string $tableName
	 */
	public static function tableToFile($tableName) {
		/*if($tableName == "USER") {
			// @FIXME : Exception pour la table USER
			return "b2c_user.php";
		} else {*/
			$dot_position = strpos($tableName, ".");
			if($dot_position === false) {
				return strtolower($tableName) . '.php';
			} else {
				return strtolower(substr($tableName, $dot_position)) . '.php';
			}
		//}
	}

        public static function firstCharLower($str)
        {
                $fc = substr($str,0,1);
                $rest_str = substr($str,1);
		return strtolower($fc).$rest_str;
        }
        
        public static function firstCharUpper($str)
        {
                $fc = substr($str,0,1);
                $rest_str = substr($str,1);
		return strtoupper($fc).$rest_str;
        }
        
        public static function javaNaming($text) 
        {
		$str = str_replace('_', ' ', strtolower($text));
		$str = ucwords(strtolower($str));
		$str = str_replace(' ', '', $str);
                
                return self::firstCharLower($str);
	}
        
        public static function hzmArrayStringFormat($arr)
        {
              return str_replace(" ", '',str_replace("\n", ' ', var_export($arr,true)));
        
        }
        
        public static function codeNaming($text,$length_max=24) 
        {
		$str = str_replace('_', ' ', strtolower($text));
		$str = strtoupper($str);
		$str = str_replace(' ', '-', $str);
                $str = substr($str,0,$length_max);
                return $str;
	}
        
        public static function to_valid_code($text) 
        {
		$str = str_replace(' ', '_', $text);
		$str = str_replace('-', '_', $str);
                return $str;
	}
        
        public static function is_valid_code($text)
        {
                return (($text!="FUNCTION") and ($text!="DEFAULT") and ($text==self::to_valid_code($text)));
        }
        
        public static function toEnglishText($text) 
        {
		if((se_termine_par($text,"name")) and (!se_termine_par($text,"_name")))
                {
                    $text = substr($text, 0, strlen($text)-4)."_name";;
                }
                
                $text = " " .str_replace('_', ' ', strtolower($text))." ";
                $text = str_replace(' id ', ' ', $text);
                return self::firstCharUpper(trim($text));
	}
        
        
        public static function hzmNaming($text) 
        {
		$str = str_replace('_id', '', $text);
                $str = str_replace('_', ' ', $str);
                $str = str_replace('.', ' ', $str);
		$str = ucwords(strtolower($str));
		$str = str_replace(' ', '', $str);
                
                return self::firstCharLower($str);
	}
        
        public static function constNaming($text) 
        {
                $str = str_replace(' ', '_', $text);
                $str = str_replace('.', '_', $str);
                
                return strtoupper($str);
	}
        
        public static function hzm_array_merge($arr1,$arr2)
        {
               $result = array();
               foreach($arr1 as $index1 => $val1)
               {
                    $result[$index1] = $val1;
               }
               
               foreach($arr2 as $index2 => $val2)
               {
                    $result[$index2] = $val2;
               }
               
               return $result;
        }
        
        
        
        public static function inverseRelation($relation)
        {
             if($relation=="parent") return "child";
             if($relation=="father") return "child";
             if($relation=="mother") return "child";
             if($relation=="child") return "parent";
             if($relation=="sub") return "parent";
        
        
             return "inv".ucwords($relation);
        }

        public static function watchdog($source, $message)
        {
                // rafik : to see what to implement here
                // @todo
        }

        public static function watchdog_exception($source, Exception $e)
        {
                // rafik : to see what to implement here
                // @todo
        }

        


        public static function _errorLight($msg)
        {
                return self::simpleError($msg, $call_method = "", $light=true);
        }
        
	/**
	 * _error
	 * Throws an error and display a message
	 * @param string $msg
	 */
        public static function simpleError($msg, $call_method = "", $light=false) 
        {
                throw new RuntimeException($msg." : call_method=$call_method"); 
                /*
                $message = $msg;
                $message .= _back_trace();
                die($message);
                
                
                global $_POST, $out_scr;
                $ob_html = "";//ob_get_clean();
                $file_dir_name = dirname(__FILE__);
                // il faut un header special qui ne plante jamais 
                $nomenu = true;
                include("$file_dir_name/../lib/hzm/web/hzm_min_header.php");
                $message = "<pre style=\"direction: ltr;text-align: left;\">​";
                $message .= "<div class='error_afw'><br> <b>AWF Message :</b> $msg\n";
                $message .= " <b>PhpClass :</b> " . get_called_class();
                $message .= " <b>Method :</b> $call_method\n";  
                $message .= "</div>";
		
                
                $message .= "<hr>\n";
                if($_POST) 
                {
                        $message .= "<table dir='ltr' class=\"display dataTable\">\n";
                        $odd = "odd";
                        foreach($_POST as $att => $att_val)
                        {
                                $message .= "<tr calss='$odd'><td>posted <b>$att : </b></td><td>$att_val</td></tr>\n"; 
                                if($odd=="even") $odd = "odd";
                                else $odd = "even";
			}
                        $message .= "</table>\n<hr>\n";
                }
                $message .= "</pre>\n";
                if(!$light)
                {
                        if(class_exists("AfwSession")) $message .= AfwSession::getLog();
                }
                

                $message .= $out_scr;
                // this line below go into infinite loop and i got out of memory
                //if($cacheSys) $message .= $cacheSys->cache_analysis_to_html();
                
                
                die($ob_html.$message);*/
	}
        
      public static final function hzmEncode($string,$key1="a",$key2="x")
      {
                $mdfive = md5($key1.$string.$key2);   
                return substr($mdfive,11,2)."a".substr($mdfive,22,2)."b".substr($mdfive,1,3);
      }

      public static final function hzmArabicToLatinRepresentation($string)
      {
           $matrixEncrypt = array();
           $matrixEncrypt["إ"] = "e";
           $matrixEncrypt["أ"] = "a";    
           $matrixEncrypt["ا"] = "i";
           $matrixEncrypt["ب"] = "b";
           $matrixEncrypt["ت"] = "t";
           $matrixEncrypt["ة"] = "o";
           $matrixEncrypt["ث"] = "p";
           $matrixEncrypt["ج"] = "j";
           $matrixEncrypt["ح"] = "hh";
           $matrixEncrypt["خ"] = "kh";
           $matrixEncrypt["د"] = "d";
           $matrixEncrypt["ذ"] = "dh";
           $matrixEncrypt["ر"] = "r";
           $matrixEncrypt["ز"] = "z";
           $matrixEncrypt["س"] = "s";
           $matrixEncrypt["ش"] = "w";
           $matrixEncrypt["ص"] = "c";
           $matrixEncrypt["ض"] = "v";
           $matrixEncrypt["ط"] = "q";
           $matrixEncrypt["ظ"] = "p";
           $matrixEncrypt["ع"] = "ae";
           $matrixEncrypt["غ"] = "rr";
           $matrixEncrypt["ف"] = "f";
           $matrixEncrypt["ق"] = "g";
           $matrixEncrypt["ك"] = "k";
           $matrixEncrypt["ل"] = "l";
           $matrixEncrypt["م"] = "m";
           $matrixEncrypt["ن"] = "n";
           $matrixEncrypt["ه"] = "h";
           $matrixEncrypt["و"] = "w";
           $matrixEncrypt["ؤ"] = "u";
           $matrixEncrypt["ي"] = "y";
           $matrixEncrypt["ئ"] = "x";
           $matrixEncrypt[" "] = "";
           $matrixEncrypt["_"] = "";
           $matrixEncrypt["+"] = "";
           $matrixEncrypt["-"] = "";
           $matrixEncrypt["*"] = "";
           $matrixEncrypt["/"] = "";

           
           $string_enc = $string;
           
           foreach($matrixEncrypt as $cc => $cce)
           {
                 $string_enc = str_replace($cc, $cce, $string_enc);
           }
           
           return $string_enc;
      }

      public static final function hzmEncrypt($string)
      {
           $matrixEncrypt = array();
           $matrixEncrypt["q"] = "a";
           $matrixEncrypt["w"] = "s";
           $matrixEncrypt["e"] = "d";
           $matrixEncrypt["r"] = "f";
           $matrixEncrypt["t"] = "g";
           $matrixEncrypt["y"] = "h";
           $matrixEncrypt["u"] = "j";
           $matrixEncrypt["i"] = "k";
           $matrixEncrypt["o"] = "l";
           $matrixEncrypt["p"] = "z";
           $matrixEncrypt["a"] = "x";
           $matrixEncrypt["s"] = "c";
           $matrixEncrypt["d"] = "v";
           $matrixEncrypt["f"] = "b";
           $matrixEncrypt["g"] = "n";
           $matrixEncrypt["h"] = "m";
           $matrixEncrypt["j"] = "q";
           $matrixEncrypt["k"] = "w";
           $matrixEncrypt["l"] = "e";
           $matrixEncrypt["z"] = "r";
           $matrixEncrypt["x"] = "t";
           $matrixEncrypt["c"] = "y";
           $matrixEncrypt["v"] = "u";
           $matrixEncrypt["b"] = "i";
           $matrixEncrypt["n"] = "o";
           $matrixEncrypt["m"] = "p";
           $matrixEncrypt[" "] = "_";
           $matrixEncrypt["_"] = " ";
           $matrixEncrypt["+"] = "-";
           $matrixEncrypt["-"] = "+";
           $matrixEncrypt["*"] = "/";
           $matrixEncrypt["/"] = "*";

           $matrixEncrypt["Q"] = "A";
           $matrixEncrypt["W"] = "S";
           $matrixEncrypt["E"] = "D";
           $matrixEncrypt["R"] = "F";
           $matrixEncrypt["T"] = "G";
           $matrixEncrypt["Y"] = "H";
           $matrixEncrypt["U"] = "J";
           $matrixEncrypt["I"] = "K";
           $matrixEncrypt["O"] = "L";
           $matrixEncrypt["P"] = "Z";
           $matrixEncrypt["A"] = "X";
           $matrixEncrypt["S"] = "C";
           $matrixEncrypt["D"] = "V";
           $matrixEncrypt["F"] = "B";
           $matrixEncrypt["G"] = "N";
           $matrixEncrypt["H"] = "M";
           $matrixEncrypt["J"] = "Q";
           $matrixEncrypt["K"] = "W";
           $matrixEncrypt["L"] = "E";
           $matrixEncrypt["Z"] = "R";
           $matrixEncrypt["X"] = "T";
           $matrixEncrypt["C"] = "Y";
           $matrixEncrypt["V"] = "U";
           $matrixEncrypt["B"] = "I";
           $matrixEncrypt["N"] = "O";
           $matrixEncrypt["M"] = "P";
           
           $string_enc = "";
           
           for($i=0;$i<strlen($string);$i++)
           {
                 $cenc = $matrixEncrypt[$string[$i]];
                 if(!$cenc) $cenc = $string[$i];
                 $string_enc .= $cenc;
           }
           
           return $string_enc;
      }

        public static final function hzmDecrypt($string)
        {
                $matrixDecrypt = array();
                $matrixDecrypt["a"] = "q";
                $matrixDecrypt["s"] = "w";
                $matrixDecrypt["d"] = "e";
                $matrixDecrypt["f"] = "r";
                $matrixDecrypt["g"] = "t";
                $matrixDecrypt["h"] = "y";
                $matrixDecrypt["j"] = "u";
                $matrixDecrypt["k"] = "i";
                $matrixDecrypt["l"] = "o";
                $matrixDecrypt["z"] = "p";
                $matrixDecrypt["x"] = "a";
                $matrixDecrypt["c"] = "s";
                $matrixDecrypt["v"] = "d";
                $matrixDecrypt["b"] = "f";
                $matrixDecrypt["n"] = "g";
                $matrixDecrypt["m"] = "h";
                $matrixDecrypt["q"] = "j";
                $matrixDecrypt["w"] = "k";
                $matrixDecrypt["e"] = "l";
                $matrixDecrypt["r"] = "z";
                $matrixDecrypt["t"] = "x";
                $matrixDecrypt["y"] = "c";
                $matrixDecrypt["u"] = "v";
                $matrixDecrypt["i"] = "b";
                $matrixDecrypt["o"] = "n";
                $matrixDecrypt["p"] = "m";
                $matrixDecrypt["_"] = " ";
                $matrixDecrypt[" "] = "_";
                $matrixDecrypt["-"] = "+";
                $matrixDecrypt["+"] = "-";
                $matrixDecrypt["/"] = "*";
                $matrixDecrypt["*"] = "/";
                                        
                $matrixDecrypt["A"] = "Q";
                $matrixDecrypt["S"] = "W";
                $matrixDecrypt["D"] = "E";
                $matrixDecrypt["F"] = "R";
                $matrixDecrypt["G"] = "T";
                $matrixDecrypt["H"] = "Y";
                $matrixDecrypt["J"] = "U";
                $matrixDecrypt["K"] = "I";
                $matrixDecrypt["L"] = "O";
                $matrixDecrypt["Z"] = "P";
                $matrixDecrypt["X"] = "A";
                $matrixDecrypt["C"] = "S";
                $matrixDecrypt["V"] = "D";
                $matrixDecrypt["B"] = "F";
                $matrixDecrypt["N"] = "G";
                $matrixDecrypt["M"] = "H";
                $matrixDecrypt["Q"] = "J";
                $matrixDecrypt["W"] = "K";
                $matrixDecrypt["E"] = "L";
                $matrixDecrypt["R"] = "Z";
                $matrixDecrypt["T"] = "X";
                $matrixDecrypt["Y"] = "C";
                $matrixDecrypt["U"] = "V";
                $matrixDecrypt["I"] = "B";
                $matrixDecrypt["O"] = "N";
                $matrixDecrypt["P"] = "M";
                
                $string_dec = "";
                
                for($i=0;$i<strlen($string);$i++)
                {
                        $cenc = $matrixDecrypt[$string[$i]];
                        if(!$cenc) $cenc = $string[$i];
                        $string_dec .= $cenc;
                }
                
                return $string_dec; 
        }

        public static function hardSecureCleanString($string, $urldecode=false)         
        {
             return static::clean_input($string, $soft=false, $string_is_secure=false, $urldecode);
        }

        
        public static function clean_input($string, $soft=true, $string_is_secure=false, $urldecode=false) 
        {
                if(is_array($string)) return $string;
                $string2 = stripslashes($string);                
                if($urldecode) $string2 = urldecode($string2);
                $string1 = $string3 = strtolower($string2);
                if(!$string_is_secure) 
                {
                        $string1 = str_replace('<script>', '', $string1);  
                        $string1 = str_replace('<script ', '', $string1);  
                        $string1 = str_replace('</script>', '', $string1);  
                        $string1 = str_replace('script', '', $string1); 
                        $string1 = str_replace('onchange', '', $string1); 
                        $string1 = str_replace('onclick', '', $string1); 
                        $string1 = str_replace('onerror', '', $string1); 
                        $string1 = str_replace('onmouseover', '', $string1); 
                        $string1 = str_replace('onmouseout', '', $string1); 
                        $string1 = str_replace('onkeydown', '', $string1); 
                        $string1 = str_replace('onload', '', $string1); 
                        $string1 = str_replace('onblur', '', $string1); 
                        $string1 = str_replace('onfocus', '', $string1); 
                }

                if($string1 != $string3)
                {
                        $string2 = 'not-allowed-string';     
                }
                else
                {
                        if(!$string_is_secure) $string2 = str_replace('<script>', '', $string2);
                        if(!$string_is_secure) $string2 = str_replace('<script>', '', $string2);
                        if(!$string_is_secure) $string2 = str_replace('<script>', '', $string2);
                        if(!$string_is_secure) $string2 = str_replace('<script>', '', $string2);
                        if(!$string_is_secure) $string2 = str_replace('</script>', '', $string2);
                        if(!$string_is_secure) $string2 = str_replace('<script', '', $string2);
                        if(!$string_is_secure) $string2 = str_replace('/script>', '', $string2);
                        if(!$string_is_secure) $string2 = str_replace('java', 'j a v a', $string2);
                        if(!$string_is_secure) $string2 = str_replace('script', 's c r i p t', $string2);
                        if(!$soft) $string2 = preg_replace("/[`~^²¨%\"]/", '', $string2);
        
                        if(!$soft) $string2 = str_replace('(', '', $string2);
                        if(!$soft) $string2 = str_replace(')', '', $string2);
                }
                
                return $string2;
        }

        

        public static function dd($message, $to_die=true, $to_debugg=false, $trace=true, $light=false)
        {
                if($trace) $message = $message."<br>"._back_trace($light);
                if($to_debugg) AFWDebugg::log($message);
                if($to_die) 
                {
                        $html = ob_get_clean();
                        die($html.$message);
                }
                
        }

        public static function password_encrypt($pwd)
        {
                return md5($pwd);
        }

        public static function password_generate($username, $len=7)
        {
                return substr(md5(rand(4,1000).$username. date("is")),0,$len);;
        }


        public static function pbm_result($err,$info, $warn=null, $sep="<br>\n", $tech="")
        {
                // die(" 1 ==> pbm_result($err, $info, $warn) warn = ".var_export($warn,true));
                if(is_array($err)) $err = implode($sep,$err);
                if(is_array($info)) $info = implode($sep,$info);
                if(is_array($warn)) $warn = implode($sep,$warn);
                if(is_array($tech)) $tech = implode($sep,$tech);
                
                // die(" 2 ==> pbm_result($err, $info, $warn)");
                
                return array($err, $info, $warn, $tech);
        }

        public static function decode_result($obj,$what,$lang="ar")
        {
                if($what=="value") $return = $obj ? $obj->id : 0;
                elseif($what=="decodeme")  $return = $obj ? $obj->getDisplay($lang) : "";
                else $return = $obj;

                return $return;
        }


        
        
        

        public static function lightSafeDie($error_title, $objToExport=null)
        {
                $message = $error_title;
                if($objToExport) $message .= "<br><pre class='code php' style='direction:ltr;text-align:left'>".var_export($objToExport,true)."</pre>";
                throw new RuntimeException($message);
                //return self::safeDie($error_title, $error_description_details="", $analysis_log=false, $objToExport, $light = true);
        }

        public static function unSafeDie($error_title, $light = true, $objToExport=null, $error_description_details="", $analysis_log=true)
        {
                $message = $error_title;
                if($objToExport) $message .= "<br> >> <b>obj</b> = <pre class='code php' style='direction:ltr;text-align:left'>".var_export($objToExport,true)."</pre>";
                if($error_description_details) $message .= "<br> >> <b>more details</b> : ".$error_description_details;
                throw new RuntimeException($message);
                
                // return self::safeDie($error_title, $error_description_details, $analysis_log, $objToExport, $light, $force_mode_dev=true);
        }

        public static function safeDie($error_title, $error_description_details="", $analysis_log=true, $objToExport=null, $light = false, $force_mode_dev=false)
        {
                $message = trim(ob_get_clean());


                $mode_dev = AfwSession::config("MODE_DEVELOPMENT", false);
                $mode_batch = AfwSession::config("MODE_BATCH", false);

                $open_mode = ($force_mode_dev or $mode_dev or $mode_batch);

                if(!$message)
                {
                        $crst = md5("crst".date("YmdHis"));
                        $message = "<html>
                                <head>
                                <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                                <link rel='stylesheet' href='../lib/css/font-awesome.min-4.3.css'>
                                <link rel='stylesheet' href='../lib/css/font-awesome.min.css'>
                                <link rel='stylesheet' href='../lib/css/menu_ar.css'>
                                <link rel='stylesheet' href='../lib/css/front-application.css?crst=$crst'>
                                <link rel='stylesheet' href='../lib/css/hzm-v001.css?crst=$crst'>                                
                                <link rel='stylesheet' href='../lib/css/front_app.css?crst=$crst'>
                                <link rel='stylesheet' href='../lib/css/material-design-iconic-font.min.css'>
                                <link rel='stylesheet' href='../lib/bootstrap/bootstrap-v3.min.css'>
                                <link rel='stylesheet' href='../lib/bsel/css/bootstrap-select.css'>
                                <link rel='stylesheet' href='../lib/css/dropdowntree.css' />
                                <link href='../lib/css/def_ar_front.css' rel='stylesheet' type='text/css'>
                                <link href='../lib/css/simple/style_common.css?crst=$crst' rel='stylesheet' type='text/css'>
                                <link href='../lib/css/simple/style_ar.css?crst=$crst' rel='stylesheet' type='text/css'>
                                <link href='../lib/css/simple/front_menu.css?crst=$crst' rel='stylesheet' type='text/css'>
                                <link href='../../external/css/common.css' rel='stylesheet' type='text/css' type='text/css'>
                                <link href='./css/module.css?crst=$crst' rel='stylesheet' type='text/css' type='text/css'>
                                <link href='../lib/skins/square/green.css' rel='stylesheet' type='text/css'>
                                <link href='../lib/skins/square/red.css' rel='stylesheet' type='text/css'>

                                <script src='../lib/js/jquery-1.12.0.min.js'></script>
                                <script src='../lib/bootstrap/bootstrap-v3.min.js'></script>
                                <script src='../lib/js/jquery-ui-1.11.4.js'></script>

                                <body style='font-family: monospace;'>";

                        if($open_mode)  $application_info = "<div class='logo_application'>
                        <img src='../../external/pic/logo-application.png' alt='' style='margin-top:5px;float: left;height: 90px'>
                        </div>
                        <div class='title_application'>
                        <img src='../../external/pic/title-application.png' alt='' style='margin-top:5px;float: left;height: 90px'>
                        </div>";
                        else $application_info = "";                             

                        $message .= "<div class='medium-12 large-12 columns text-center large-text-right'>
                                <div class='logo_company'>  
                                <img src='../../external/pic/logo-company-pag.png' alt='' style='margin-top:5px;height: px;'> 
                                </div>  
                                <div class='title_company'>  
                                <img src='../../external/pic/title-company-pag.png' alt='' style='margin-top:-10px;height: px;'> 
                                </div>
                                $application_info     
                                </div>";
                }

                if(!function_exists("_back_trace"))
                {
                        include_once("common.php");
                } 
                $back_trace_light = _back_trace($light);

                if($open_mode) 
                {
                        
                        $message .= "<div style='font-family: monospace;float: right;width: 100%;text-align: left;padding-top: 30px;border-top: 2px solid #0d67d8;'>";

                
                        $message .= "<div class='momken_error_title'><b>Only Development and Batch Mode Shown Error :</b> $error_title\n</div>";
                        if($objToExport)
                        {
                                
                                $message .= AfwHtmlHelper::genereAccordion("<pre style='text-align: left;direction: ltr;font-family: monospace !important;'>".$error_description_details."\nExported Object\n".var_export($objToExport,true)."</pre>", "Object exported");
                        }
                        else $message .= "<div><br> no object exported !!</div>";
                        //$message .= "<br> <b>PhpClass :</b> " . get_called_class();
                        
                        
                        $message .= $back_trace_light;

                        

                        if(($analysis_log) and (class_exists("AfwAutoLoader") or class_exists("AfwSession"))) 
                        {
                                $message .= "<br><div id=\"analysis_log\">";
                                $message .= "<div class=\"fleft\"><h1><b>System LOG after safe die :</b></h1></div>";
                                $message .= AfwSession::getLog();
                                $message .= "</div>";
                        }
                        $message .= "</div>";
                }
                else
                {
                        $message .= "<div style='font-family: monospace;float: right;width: 100%;text-align: left;padding-top: 30px;border-top: 2px solid #0d67d8;'>";
                        $message .= "<div class='momken_error_title'>An error happened when executing this request, please contact the administrator <br> .حصل خطأ أثناء تنفيذ هذا الطلب الرجاء التواصل مع مشرف المنصة</div>";
                        $message .= "<br>open_mode:[$open_mode] = (force_mode_dev:[$force_mode_dev] or mode_dev:[$mode_dev] or mode_batch:[$mode_batch])<br>";
                        $message .= AfwSession::log_config();
                        $message .= "</div>";

                        AFWDebugg::log("Momken Framework Error : $error_title");
                        AFWDebugg::log("Back trace : \n $back_trace_light");

                }
                die($message);
                // triggersimpleError($message, E_USER_ERROR);
        }

        public function getMyModule() {
                return "NOT-OVERRIDDEN";
        }

        public function getMyParentModule() {
                return "PM-NOT-OVERRIDDEN";
        }

        

        public static function getTranslationPaths($module="", $parent_module="")
        {
                $file_dir_name = dirname(__FILE__); 

                $paths = array();

                //if((!$module) and isset(static::$MODULE)) $module = self::$MODULE;
                
                $paths[] = "$file_dir_name/../../pag";
                $paths[] = "$file_dir_name/../../ums";
                $paths[] = "$file_dir_name/../../hrm";
                $paths[] = "$file_dir_name/../../crm";
                if($module) $paths[] = "$file_dir_name/../../$module";
                if($parent_module) $paths[] = "$file_dir_name/../../$parent_module";
                
                return $paths;                
        }

        public static function tt($text, $lang = "ar", $module="", $parent_module="") 
        {
                global $messages;
                $file_dir_name = dirname(__FILE__); 
                
                
                $paths = self::getTranslationPaths($module, $parent_module);
                foreach($paths as $path) include_once $path."/messages_$lang.php";
                
                if($messages[$text]) return $messages[$text];
                else return $text; 
                // else return $text."paths=".var_export($paths,true)." messages=".var_export($messages,true); 
        }

        public static function lookIfInfiniteLoop($maxAuthorized=20000,$case="all") 
        {
            global $onces;
            if(!$onces) $onces = array();
            if(!$onces[$case]) $onces[$case] = 1; else $onces[$case]++;
            if($onces[$case]>$maxAuthorized)
            {
                self::safeDie("called $maxAuthorized times seems like infinite loop for case '$case'");
            }
        }

        public static function traduireText($text, $langue = 'ar')
    {
        return self::traduire($text, $langue, false, '', 'pag');
    }

    public static function traduireOperator($text, $langue = 'ar', $external='obsolete')
    {
        return self::traduire($text, $langue, true, '', 'pag');
    }

    public static function traduire(
        $nom_col,
        $langue = 'ar',
        $operator = null,
        $nom_table = '',
        $module = ''
    ) {
        global $lang, $trad;
        $file_dir_name = dirname(__FILE__);
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }

        if (empty($operator)) {
            if ($nom_table) {
                $nom_file =
                    "$file_dir_name/../../$module/tr/trad_" .
                    strtolower($langue) .
                    "_$nom_table.php";
                $nom_file2 =
                    "$file_dir_name/../../external/translate/$module/trad_" .
                    strtolower($langue) .
                    "_$nom_table.php";
                //if($this->MY_DEBUG)
                //        AFWDebugg::log("traduire from file $nom_file ");

                if (file_exists($nom_file2)) {
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire include_once $nom_file ");
                    include_once $nom_file2;
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    if ($trad[$nom_table][$nom_col]) {
                        return $trad[$nom_table][$nom_col];
                    }
                }

                if (file_exists($nom_file)) {
                    // if(($module=="sis") and ($nom_table=="student")) die("nom_file2=$nom_file2 not found");
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire include_once $nom_file ");
                    include_once $nom_file;
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    if ($trad[$nom_table][$nom_col]) {
                        return $trad[$nom_table][$nom_col];
                    }
                }
            } else {
                $nom_table = '*';
            }
            if (
                !isset($trad[$nom_table][$nom_col]) ||
                empty($trad[$nom_table][$nom_col])
            ) {
                $general_nom_file =
                    "$file_dir_name/tr/trad_" .
                    strtolower($langue) .
                    '_all.php';

                if (file_exists($general_nom_file)) {
                    include_once $general_nom_file;
                    //echo "<br>2)translate $nom_table.$nom_col in $langue from general file $general_nom_file"."=".$trad[$nom_table][$nom_col];
                    if (
                        isset($trad['*'][$nom_col]) &&
                        !empty($trad['*'][$nom_col])
                    ) {
                        return $trad['*'][$nom_col];
                    }
                }
                //echo "<br>3)no translate keep $nom_table.$nom_col ";
                return $nom_col;
            } else {
                //echo "<br>4)translate $nom_table.$nom_col in $langue from memory = ".$trad[$nom_table][$nom_col]."=".$trad[$nom_table][$nom_col];
                return $trad[$nom_table][$nom_col];
            }
        } else {
            $langue = strtolower($langue);
            if (!$langue) {
                $langue = 'ar';
            }

            if ($nom_table) {
                $nom_file0 =
                    "$file_dir_name/../../$module/tr/trad_" .
                    strtolower($langue) .
                    "_$nom_table.php";
                //if($this->MY_DEBUG)
                //        AFWDebugg::log("traduire from file $nom_file ");
                if (file_exists($nom_file0)) {
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire include_once $nom_file ");
                    include_once $nom_file0;
                    //if($this->MY_DEBUG)
                    //    AFWDebugg::log("traduire $nom_table.$nom_col in $langue from $nom_file"."=".$trad[$nom_table][$nom_col]);

                    if ($trad['OPERATOR'][$nom_col]) {
                        return $trad['OPERATOR'][$nom_col];
                    }
                }
            }

            $file_name = "$file_dir_name/../../external/translate/$module/trad_" . $langue . '_afw.php';
            if (file_exists($file_name)) {
                $ff = 'file found';
                include_once $file_name;

                $trad_val = $trad['OPERATOR'][$nom_col];
                if ($trad_val) {
                    return $trad_val;
                }
            }
            //else die($file_name);

            $file_name = "$file_dir_name/tr/trad_" . $langue . '_afw.php';
            // if($nom_col=="_DISPLAY") die("self::traduire($nom_col, $langue,$operator,$nom_table, $module) : file_name=$file_name");
            $ff = 'file not found';
            if (file_exists($file_name)) {
                $ff = 'file found';
                include_once $file_name;

                $trad_val = $trad['OPERATOR'][$nom_col];
                if ($trad_val) {
                    return $trad_val;
                }
            } else {
                self::simpleError(
                    "file not exists $file_name for langue $langue"
                );
            }

            if ($nom_col == '_DISPLAY') {
                self::simpleError(
                    "self::traduire($nom_col, $langue,$operator,$nom_table, $module) = '$trad_val' from $file_name ($ff)"
                );
            }
            return $nom_col;
        }
    }

    public static function traduireMessage($message, $module, $lang = 'ar')
    {
        global $messages;
        $file_dir_name = dirname(__FILE__);

        include_once "$file_dir_name/../../pag/messages_$lang.php";
        include_once "$file_dir_name/../../$module/messages_$lang.php";

        if ($messages[$message]) {
            return $messages[$message];
        } else {
            return $message;
        }
    }

        


}

?>