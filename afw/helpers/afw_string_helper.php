<?php

class AfwStringHelper 
{
        public static function stringStartsWith($stringbody, $string_start)
        {
                if(!$stringbody) $stringbody = "";
                if(!is_string($stringbody))
                {
                        throw new AfwRuntimeException(var_export($stringbody,true)." is not a valid string for stringStartsWith($stringbody, $string_start)");
                }
                return (strpos($stringbody, $string_start) === 0);
        }

        public static function removePrefix($string, $prefix)
        {
                $prefix_len = strlen($prefix);
                if (self::stringStartsWith($string, $prefix)) {
                        return substr($string, $prefix_len);
                }

                return $string;
        }


        public static function stringEndsWith($string, $endString)
        {
                $len = strlen($endString);
                if ($len == 0) {
                        return true;
                }
                return (substr($string, -$len) === $endString);
        }

        public static function clean_my_url($string)
        {
                $string2 = str_replace('<', '', $string);
                $string2 = str_replace('>', '', $string2);
                $string2 = str_replace('(', '', $string2);
                $string2 = str_replace(')', '', $string2);

                return $string2;
        }

        /* old version
        public static function stringEndsWith($string, $end)
        {
                $ls = strlen($end);
                $lc = strlen($string);
                
                if($ls > $lc) return false;
                $inc = 1;

                while ($inc<=$ls)
                {
                        if ($string[$lc-$inc] != $end[$ls-$inc]) return false;
                        $inc++;
                }
                return true;
        }*/

        public static function stringContain($string, $substr)
        {
                return (strpos($string, $substr) !== false);
        }

        public static function arabicTaarif($name)
        {
                $name_words_arr = explode(' ', $name);
                if (count($name_words_arr) == 1) {
                        return 'ال' . $name;
                }
                if (count($name_words_arr) == 2) {
                        $name_words_arr[1] = 'ال' . $name_words_arr[1];
                }
                return implode(' ', $name_words_arr);
        }

        public static function formatArabicNumericToLatin($num_val)
        {
                $num_val = str_replace('٠', '0', $num_val);
                $num_val = str_replace('١', '1', $num_val);
                $num_val = str_replace('٢', '2', $num_val);
                $num_val = str_replace('٣', '3', $num_val);
                $num_val = str_replace('٤', '4', $num_val);
                $num_val = str_replace('٥', '5', $num_val);
                $num_val = str_replace('٦', '6', $num_val);
                $num_val = str_replace('٧', '7', $num_val);
                $num_val = str_replace('٨', '8', $num_val);
                $num_val = str_replace('٩', '9', $num_val);

                return $num_val;
        }

        public static function hzmStringOf($str)
        {
                $str = str_replace("/", "x", $str);
                $str = str_replace(".", "-", $str);
                $str = str_replace("_", "y", $str);
                return $str;
        }


        public static function stripCotes($str)
        {
                $str = str_replace("'",  "", $str);
                $str = str_replace("\"", "", $str);
                $str = str_replace("`",  "", $str);
                return $str;
        }


        public static function splitArabicFromLatinSentences($string, $latinWordsInArabicMax = 2, $trim_chars = "\"[]()'<>{}")
        {
                global $print_full_debugg;
                $current = "";
                $arabic_words = array();
                $latin_words = array();
                $latinWordsInArabic = 0;

                $string = str_replace("\"", " ", $string);
                $string = str_replace("'", " ", $string);
                $string = str_replace("[", " ", $string);
                $string = str_replace("]", " ", $string);
                $string = str_replace("(", " ", $string);
                $string = str_replace(")", " ", $string);
                $string = str_replace("'", " ", $string);
                $string = str_replace("<", " ", $string);
                $string = str_replace(">", " ", $string);
                $string = str_replace("{", " ", $string);
                $string = str_replace("}", " ", $string);

                $words = explode(" ", $string);

                foreach ($words as $word) {
                        $word = trim($word, $trim_chars);
                        $word = trim($word);
                        if ($word) {
                                if (self::is_arabic($word, 0.7)) {
                                        if (($current == "") or ($current == "arabic") or (count($arabic_words) == 0)) {
                                                $arabic_words[] = $word;
                                                if ($print_full_debugg) AfwBatch::print_debugg("$word taked as arabic");
                                                $current = "arabic";
                                        } else {
                                                $log = "split failed arabic and latin are mixed. manually split needed ! cur=$current and ecountered arabic word '$word'";
                                                if ($print_full_debugg) AfwBatch::print_error($log);
                                                return array($string, "", false, $log);
                                        }
                                } elseif (self::is_latin($word, 0.7)) {
                                        if (($current == "") or ($current == "latin") or (count($latin_words) == 0)) {
                                                $latin_words[] = $word;
                                                if ($print_full_debugg) AfwBatch::print_debugg("$word taked as latin as current=$current or count(latin_words) = " . count($latin_words));
                                                $current = "latin";
                                        } elseif (($current == "arabic") and ($latinWordsInArabic < $latinWordsInArabicMax)) {
                                                $arabic_words[] = $word;
                                                $latinWordsInArabic++;
                                                if ($print_full_debugg) AfwBatch::print_debugg("$word is latin but taked as arabic $latinWordsInArabic latin_words=" . var_export($latin_words, true));
                                        } else {
                                                $log = "split failed arabic and latin are mixed. manually split needed ! cur=$current, latinWordsInArabic=$latinWordsInArabic and ecountered latin word '$word' ";
                                                if ($print_full_debugg) AfwBatch::print_error($log);
                                                return array($string, "", false, $log);
                                        }
                                } else {
                                        if (($current == "") or ($current == "latin")) {
                                                $latin_words[] = $word;
                                                $current = "latin";
                                                if ($print_full_debugg) AfwBatch::print_warning("$word language unknown taked as latin");
                                        } else {
                                                $arabic_words[] = $word;
                                                $current = "arabic";
                                                if ($print_full_debugg) AfwBatch::print_warning("$word language unknown taked as arabic");
                                        }
                                }
                        }
                }

                return array(implode(" ", $arabic_words), implode(" ", $latin_words), true, "");
        }
        /*
        public static function is_latin($string) 
        {
                $result = false;
                
                if (preg_match("/^[\w\d\s.,-]*$/", $string)) {
                        $result = true;
                }
                
                return $result;
        }*/

        
        public static function is_latin($str, $seuil_pct = 0.6)
        {
                if (mb_detect_encoding($str) !== 'UTF-8') {
                        $str = mb_convert_encoding($str, mb_detect_encoding($str), 'UTF-8');
                }

                preg_match_all('/.|\n/u', $str, $matches);
                $chars = $matches[0];
                $arabic_count = 0;
                $latin_count = 0;
                $total_count = 0;
                foreach ($chars as $char) {
                        //$pos = ord($char); we cant use that, its not binary safe 
                        $pos = self::uniord($char);
                        //echo $char ." --> ".$pos.PHP_EOL."<br>";

                        if ($pos >= 1536 && $pos <= 1791) {
                                //echo "$char = > arabic <br>\n";
                                $arabic_count++;
                        } else if ($pos > 64 && $pos < 123) {
                                //echo "$char = > latin <br>\n";
                                $latin_count++;
                        } else {
                                //echo "$char = > symbol <br>\n";
                        }
                        $total_count++;
                }
                //echo "ar:$arabic_count, en:$latin_count, tot:$total_count <br>\n";
                if (!$total_count) $total_count = 1;
                $pct_prob = $latin_count / $total_count;
                if ($pct_prob > $seuil_pct) {
                        // 60% latin chars, its probably latin
                        return true;
                }
                return false;
        }


        public static function uniord($u) 
        {
            // i just copied this function fron the php.net comments, but it should work fine!
            $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
            $k1 = ord(substr($k, 0, 1));
            $k2 = ord(substr($k, 1, 1));
            return $k2 * 256 + $k1;
        }

        public static function is_arabic($str, $seuil_pct = 0.6)
        {
                global $print_full_debugg;
                if (mb_detect_encoding($str) !== 'UTF-8') {
                        $str = mb_convert_encoding($str, mb_detect_encoding($str), 'UTF-8');
                }

                preg_match_all('/.|\n/u', $str, $matches);
                $chars = $matches[0];
                $arabic_count = 0;
                $latin_count = 0;
                $total_count = 0;
                foreach ($chars as $char) {
                        //$pos = ord($char); we cant use that, its not binary safe 
                        $pos = self::uniord($char);
                        if ($print_full_debugg) AfwBatch::print_debugg($char . " --> " . $pos . PHP_EOL . "<br>");

                        if ($pos >= 1536 && $pos <= 1791) {
                                $arabic_count++;
                                if ($print_full_debugg) AfwBatch::print_debugg("$char = > arabic so $arabic_count arabic char(s) <br>\n");
                        } else if ($pos > 64 && $pos < 123) {
                                $latin_count++;
                                if ($print_full_debugg) AfwBatch::print_debugg("$char = > latin so $latin_count latin char(s) <br>\n");
                        } else {
                                if ($print_full_debugg) AfwBatch::print_debugg("$char = > symbol <br>\n");
                        }
                        $total_count++;
                }
                if ($print_full_debugg) AfwBatch::print_debugg("ar:$arabic_count, en:$latin_count, tot:$total_count <br>\n");
                if (!$total_count) $total_count = 1;
                $pct_prob = $arabic_count / $total_count;

                if ($pct_prob > $seuil_pct) {
                        $seuil_pct2 = $seuil_pct * 100;
                        if ($print_full_debugg) AfwBatch::print_info("more than $seuil_pct2 % arabic chars, its probably arabic");
                        return true;
                }
                return false;
        }

        public static function left_complete_len($str, $new_len, $complete_with_char = "0")
        {
                while (strlen($str) < $new_len) {
                        $str = $complete_with_char . $str;
                }
                return $str;
        }

        public static function arabic_unchakl($str)
        {
                $str = str_replace("ّ", '', $str);
                $str = str_replace("َ", '', $str);
                $str = str_replace("ً", '', $str);
                $str = str_replace("ُ", '', $str);
                $str = str_replace("ٌ", '', $str);
                $str = str_replace("ِ", '', $str);
                $str = str_replace("ٍ", '', $str);
                $str = str_replace("ْ", '', $str);

                return $str;
        }

        public static function similarArabicWords($str)
        {
                $str = trim($str);
                $arr_result = [];
                $arr_result[] = $str;
                $str2 = self::arabic_unchakl($str);
                if($str2 != $str) $arr_result[] = $str2;
                $arr_result2 = [];
                do
                {
                        if(count($arr_result2)>0) $arr_result = $arr_result2;
                        $arr_result2 = self::similarArabicWordsGenerator($arr_result);
                } 
                while(count($arr_result2)>count($arr_result));

                return $arr_result;
        }

        public static function similarArabicWordsGenerator($arr_str)
        {
                $arr_similar_chars = [
                        'ة'=>'ه',
                        'ه'=>'ة',
                        'ا'=>'أ',
                        'أ'=>'ا',
                        'ا'=>'إ',
                        'إ'=>'ا',
                        'ي'=>'ى',
                        'ى'=>'ي',
                ];
                $arr_result = $arr_str;
                foreach($arr_str as $str)
                {
                        foreach($arr_similar_chars as $c1 => $c2)
                        {
                                $str2 = str_replace($c1, $c2, $str);
                                if($str2 != $str)
                                {
                                        if(!in_array($str2,$arr_result))
                                        {
                                                $arr_result[] = $str2; 
                                        }
                                }
                        }  
                }
                
                return $arr_result;
        }

        public static function arabic_to_latin_chars($str)
        {
                $str = self::arabic_unchakl($str);

                $str = str_replace("أ", 'A', $str);
                $str = str_replace("ا", 'E', $str);
                $str = str_replace("إ", 'I', $str);
                $str = str_replace("آ", 'Y', $str);
                $str = str_replace("ب", 'B', $str);
                $str = str_replace("ت", 'T', $str);
                $str = str_replace("ة", 't', $str);
                $str = str_replace("ث", 'X', $str);
                $str = str_replace("ج", 'J', $str);
                $str = str_replace("ح", '7', $str);
                $str = str_replace("خ", 'W', $str);
                $str = str_replace("د", 'D', $str);
                $str = str_replace("ذ", 'd', $str);
                $str = str_replace("ر", 'R', $str);
                $str = str_replace("ز", 'Z', $str);
                $str = str_replace("س", 'S', $str);
                $str = str_replace("ش", 'c', $str);
                $str = str_replace("ص", 's', $str);
                $str = str_replace("ض", 'u', $str);
                $str = str_replace("ظ", 'U', $str);
                $str = str_replace("ط", 'V', $str);
                $str = str_replace("ع", '3', $str);
                $str = str_replace("ف", 'F', $str);
                $str = str_replace("ق", 'K', $str);
                $str = str_replace("ك", 'k', $str);
                $str = str_replace("ل", 'L', $str);
                $str = str_replace("م", 'M', $str);
                $str = str_replace("ن", 'N', $str);
                $str = str_replace("ه", 'H', $str);
                $str = str_replace("و", 'w', $str);
                $str = str_replace("ؤ", 'o', $str);
                $str = str_replace("ي", 'y', $str);
                $str = str_replace("ى", 'i', $str);
                $str = str_replace("ء", 'a', $str);
                $str = str_replace("ئ", 'Y', $str);

                return $str;
        }


        public static function containsNumbers($str)
        {
                $pattern = "/\d/";
                return preg_match($pattern, $str);
        }

        public static function containsSpecialChars($str)
        {
                $pattern = "/[`!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?~]/";
                return preg_match($pattern, $str);
        }

        public static function isCorrectName($str)
        {
                if (self::containsNumbers($str)) return false;
                if (self::containsSpecialChars($str)) return false;
                return true;
        }

        public static function isCorrectMobileNumber($str)
        {
                $pattern = "/\^(05)([0-9]{8})\$/";
                return preg_match($pattern, $str);
        }

        public static function isCorrectHijriDate($str)
        {
                $pattern = "/\^(1)(3|4|5)([0-9]{2})-(01|02|03|04|05|06|07|08|09|10|11|12)-(01|02|03|04|05|06|07|08|09|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30)\$/";
                return preg_match($pattern, $str);
        }

        public static function isCorrectEmail($str)
        {
                $pattern = "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))\$/";
                return preg_match($pattern, $str);
        }

        /**
         * _real_escape_string
         * Return escaped string
         * @param string $string
         */
        public static function _real_escape_string($string)
        {
                return addslashes($string);
        }


        public static final function parseAttribute(
                $object,
                $attribute,
                $val_to_parse,
                $lang,
                $set_to_object = true
        ) 
        {
                $desc = AfwStructureHelper::getStructureOf($object,$attribute);

                if ($desc['TYPE'] == 'GDAT' or $desc['TYPE'] == 'DATE') {
                        $alt_separator = '/';
                        $separator = '-';
                        if ($desc['TYPE'] == 'GDAT') {
                                $std_separator = '-';
                                $thousand = 1000;
                                $big_thousand = 2000;
                        }

                        if ($desc['TYPE'] == 'DATE') {
                                $std_separator = '';
                                $thousand = 1000;
                                $big_thousand = 1000;
                        }

                        if (strpos($val_to_parse, $alt_separator) !== false) {
                                $separator = $alt_separator;
                        }

                        list($val1, $val2, $val3) = explode($separator, $val_to_parse);

                        if ($val1 > 31) {
                                $old_val3 = $val3;
                                $val3 = $val1;
                                $val3 = $old_val3;
                        }

                        if ($val3 > 31) {
                                if ($val3 > $thousand) {
                                        $yyyy = $val3;
                                } else {
                                        $yyyy = $val3 + $thousand;
                                }
                        } else {
                                $yyyy = $val3 + $big_thousand;
                        }

                        if ($val2 > 12) {
                                $dd = $val2;
                                $mm = $val1;
                        } else {
                                $dd = $val1;
                                $mm = $val2;
                        }

                        $val = $yyyy . $std_separator . $mm . $std_separator . $dd;
                } elseif ($desc['TYPE'] == 'FK' or $desc['TYPE'] == 'ENUM') {
                        // gender
                        if (
                                AfwStringHelper::stringStartsWith($attribute, 'gender_') or
                                AfwStringHelper::stringStartsWith($attribute, 'genre_')
                        ) {
                                $fc = substr(strtoupper($val_to_parse), 0, 1);
                                if ($fc == 'F') {
                                        $val = 2;
                                } elseif ($fc == 'M') {
                                        $val = 1;
                                } else {
                                        $val = 0;
                                }
                        }
                        // @todo parse selon attribute type
                        // FK / Enum : using synonyms data lookup table
                        $val = $val_to_parse;
                } else {
                        $val = $val_to_parse;
                }

                if ($set_to_object) {
                        $object->set($attribute, $val);
                }
                return [true, $val];
        }

        public static function getFileNameFullPath($file_name, $module)
        {
                $file_dir_name = dirname(__FILE__);
                return "$file_dir_name/../../../$module/$file_name";
        }

        public static function getHisFactory($table_name, $module, $prefix = '')
        {
                $className = AfwStringHelper::tableToClass($table_name);
                $file_name = "$table_name.php";
                $file_path = self::getFileNameFullPath($file_name, $module);
                return [$file_path, $className];
        }

        public static function strlen_ar($str)
        {
                return strlen(mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8'));
        }

        public static function truncateArabicJomla($jomla, $maxlen, $etc="...")
        {
             $jomla = trim($jomla);
             
             $jomlaWords = explode(" ", $jomla);
             
             $result = "";
             $jomla_broken = false;
             
             foreach($jomlaWords as $word)
             {
                //if($word == "بناء") die("jomla=$jomla,  jomlaWords = ".var_export($jomlaWords,true));
                if($result)
                 {
                     $pref = " ";
                     $pref_len = 1;
                 }
                 else
                 {
                     $pref = "";
                     $pref_len = 0;
                 }
                  
                 if($maxlen>=(self::strlen_ar($result)+self::strlen_ar($word)+$pref_len))
                 {
                       $result .= $pref . $word;
                 }
                 else
                 {
                       $jomla_broken = true;
                       break; 
                 }
             
             }
             
             if($jomla_broken) $result .= $etc;
             
             return $result;
        
        }

        public static function arrow($lang)
        {
                if($lang=="ar") return "&raquo;";
                else return "&laquo;";
        }

        public static function nbWordsInJomla($jomla, $empty_is_counted=false)
        {
             $jomla = str_replace("\n", " ",$jomla);
             $jomla = trim($jomla);
             
             $jomlaWords = explode(" ", $jomla);
             
             $result = 0;
             
             foreach($jomlaWords as $word)
             {
                 $word = trim($word);
                 if($word or $empty_is_counted)
                 {
                     $result++;
                 }
             }
             
             return $result;
        
        }

        public static function intelligentArabicPlural($word, $plural_word, $nb, $female=false, $add_wahid_word = true)
        {
            if($add_wahid_word)
            {
                if($female) $wahid = "واحدة";
                else $wahid = "واحد";
            }
            else $wahid = "";
            
            if($nb == 1) return trim($word." ".$wahid);
            if($nb == 2) return trim($word)."ين";
            if($nb > 10) return $nb." ".trim($word);
            return $nb." ".trim($plural_word);
        }

        public static function isNameOfAllah($first_name)
        {
                return (($first_name=="الله") or ($first_name=="الرحمن") or ($first_name=="الكريم") or ($first_name=="الرحيم") or ($first_name=="العزيز"));            
        }


        public static function intelligentDecodeName($string) 
        {
            $string = trim($string, " ");
            $string = str_replace("  ", " ", $string);
            $string = str_replace("  ", " ", $string);
            $string = str_replace("  ", " ", $string);
            $name_arr = explode(" ", $string);

            $first_name = $name_arr[0];
            unset($name_arr[0]);

            if(($name_arr[1]=="بن") or ($name_arr[1]=="بنت") or ($name_arr[1]=="ابن"))
            {
                $father_name = $name_arr[1]." ".$name_arr[2];
                unset($name_arr[1]);
                unset($name_arr[2]);
            }
            elseif(self::isNameOfAllah($name_arr[1]))
            {
                $first_name = trim($first_name, " ");
                $first_name = ltrim($first_name,$name_arr[1]);
                $first_name .= " ".$name_arr[1];
                $father_name = "";
                unset($name_arr[1]);
            }
            else
            {
                $father_name = $name_arr[1];
                unset($name_arr[1]);
            }

            $last_name = implode(" ", $name_arr);

            return [$first_name, $father_name, $last_name];

        }

        /**
	 * tableToClass
	 * Converts tableName to className
	 * @param string $tableName
	 */
	public static final function tableToClass($tableName) {
		$dot_position = strpos($tableName, ".");
		if($dot_position !== false) {
			$tableName = substr($tableName, $dot_position);
		}
		$str = str_replace('_', ' ', strtolower($tableName));
		$str = ucwords(strtolower($str));
		$str = str_replace(' ', '', $str);
		return $str;
	}


        public static final function classToTable($className) 
        {
                 return self::fileTotable(AfwStringHelper::classToFile($className));
        }


        /**
	 * classToFile
	 * Convert tableName to PHP FileName
	 * @param string $tableName
	 */
	public static final function classToFile($className) 
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
                $dot_position = strpos($tableName, ".");
                if($dot_position === false) {
                        return strtolower($tableName) . '.php';
                } else {
                        return strtolower(substr($tableName, $dot_position)) . '.php';
                }
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
                                $arr_item_desc = AfwStringHelper::afw_export($arr_item, $object_class_and_display_only);
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
                
                return AfwStringHelper::firstCharLower($str);
	}
        
        public static function initialsOfName($text) 
        {
                list($str,) = explode('@', $text);
		$str = str_replace('_', ' ', strtolower($str));
                $str = str_replace('.', ' ', strtolower($str));
                $str = str_replace('-', ' ', strtolower($str));

                $str_arr = explode(" ", $str);
                $fc0 = substr($str_arr[0],0,1);
                if($str_arr[1])
                {
                        $fc1 = substr($str_arr[1],0,1);
                }
                else
                {
                        $fc1 = substr($str_arr[0],1,1);
                        // $fc1 = substr($str_arr[0],strlen($str_arr[1])-1,1);
                }
                
		
                
                return strtoupper($fc0.$fc1);
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
                return (($text!="FUNCTION") and ($text!="DEFAULT") and ($text==AfwStringHelper::to_valid_code($text)));
        }

        public static function toEnglishText($text, $upperCaseFirst=true) 
        {
		if((AfwStringHelper::stringStartsWith($text,"name")) and (!AfwStringHelper::stringStartsWith($text,"_name")))
                {
                    $text = substr($text, 0, strlen($text)-4)."_name";;
                }
                
                $text = " " .str_replace('_', ' ', strtolower($text))." ";
                $text = str_replace(' id ', ' ', $text);
                if($upperCaseFirst) $text = AfwStringHelper::firstCharUpper(trim($text));

                return trim($text);
	}

        public static function hzmNaming($text) 
        {
		$str = str_replace('_id', '', $text);
                $str = str_replace('_', ' ', $str);
                $str = str_replace('.', ' ', $str);
		$str = ucwords(strtolower($str));
		$str = str_replace(' ', '', $str);
                
                return AfwStringHelper::firstCharLower($str);
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
             return AfwStringHelper::clean_input($string, $soft=false, $string_is_secure=false, $urldecode);
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


}
