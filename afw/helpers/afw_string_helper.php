<?php

class AfwStringHelper extends AFWRoot
{
        public static function stringStartsWith($string, $start)
        {
                return (strpos($string, $start) === 0);
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

        public static function uniord($u) 
        {
            // i just copied this function fron the php.net comments, but it should work fine!
            $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
            $k1 = ord(substr($k, 0, 1));
            $k2 = ord(substr($k, 1, 1));
            return $k2 * 256 + $k1;
        }

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
                        $pos = uniord($char);
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
                $className = self::tableToClass($table_name);
                $file_name = "$table_name.php";
                $file_path = self::getFileNameFullPath($file_name, $module);
                return [$file_path, $className];
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
                  
                 if($maxlen>=(strlen_ar($result)+strlen_ar($word)+$pref_len))
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


        public static function intelligentDecodeName($string) 
        {
            $string = trim($string);
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
            else
            {
                $father_name = $name_arr[1];
                unset($name_arr[1]);
            }

            $last_name = implode(" ", $name_arr);

            return [$first_name, $father_name, $last_name];

        }


}
