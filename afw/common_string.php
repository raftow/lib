<?php


          function mas_complete_len($str, $new_len, $complete_with_char=" ")
          {
             //$str = substr(utf8_decode($str),0,$new_len);
             while(strlen_ar($str)<$new_len)
             {
                $str .= $complete_with_char; 
             }
             return $str;  
          }
          
          function strlen_ar($str)
          {
             return strlen(utf8_decode($str));
          }


        /**  
         * Cette fonction permet de vérifier si une chaine donnée commence par un tel préfixe.    
         * @return boolean         
         * @param string $chaine :chaine à vérifier  
         * @param string $prefix :le commencement de la chaine                
         */
        /*
        function AfwStringHelper::stringStartsWith($chaine,$prefix)
        {
            return (strpos($chaine,$prefix)===0);                
        }
        */
        
        function incomplete($str, $signe_incomplete="...")
        {
            if(!trim($str)) return true;
            return contient($str,$signe_incomplete);
        }
        
        
        function contient($str,$substr)
        {
            global $set_debug;
                
                $pos = strpos($str,$substr);
                if (($pos===0) or ($pos>0))
                {
                        return true;  
                }
                else return false;
        }

         /**
         * Cette fonction permet de vérifier si une chaine donnée se termine par un tel suffixe.    
         * @return boolean         
         * @param string $chaine :chaine à vérifier  
         * @param string $suffixe :la terminaison de la chaine                
         */
        function se_termine_par($chaine,$suffixe)
        {
            global $set_debug;
            
                if($set_debug) print("chaine = $chaine suffixe=$suffixe\n"); 
                $ls = strlen($suffixe);
                $lc = strlen($chaine);
                $inc = 1;
                $result = true;
                while ($inc<=$ls and $result)
                {
                        if ($chaine[$lc-$inc]!=$suffixe[$ls-$inc]) $result=false;
                        $inc++;
                }
                return $result;
        }
        
        function uniord($u) 
        {
            // i just copied this function fron the php.net comments, but it should work fine!
            $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
            $k1 = ord(substr($k, 0, 1));
            $k2 = ord(substr($k, 1, 1));
            return $k2 * 256 + $k1;
        }

        function is_arabic($str, $seuil_pct=0.6) 
        {
            if(mb_detect_encoding($str) !== 'UTF-8') {
                $str = mb_convert_encoding($str,mb_detect_encoding($str),'UTF-8');
            }
        
            preg_match_all('/.|\n/u', $str, $matches);
            $chars = $matches[0];
            $arabic_count = 0;
            $latin_count = 0;
            $total_count = 0;
            foreach($chars as $char) 
            {
                //$pos = ord($char); we cant use that, its not binary safe 
                $pos = uniord($char);
                //echo $char ." --> ".$pos.PHP_EOL."<br>";
        
                if($pos >= 1536 && $pos <= 1791) 
                {
                    //echo "$char = > arabic <br>\n";
                    $arabic_count++;
                } 
                else if($pos > 64 && $pos < 123) 
                {
                    //echo "$char = > latin <br>\n";
                    $latin_count++;
                }
                else
                {                         
                    //echo "$char = > symbol <br>\n";
                }
                $total_count++;
            }
            //echo "ar:$arabic_count, en:$latin_count, tot:$total_count <br>\n";
            
            if((($arabic_count)/($latin_count+$arabic_count+1)) > $seuil_pct) 
            {
                // 60% arabic chars, its probably arabic
                return true;
            }
            return false;
        }
        
        function left_complete_len($str, $new_len, $complete_with_char="0")
        {
             while(strlen($str)<$new_len)
             {
                $str = $complete_with_char . $str; 
             }
             return $str;  
        }
        
        function arabic_unchakl($str)
        {
            $str = str_replace("ّ", '',$str);
            $str = str_replace("َ", '',$str);
            $str = str_replace("ً", '',$str);
            $str = str_replace("ُ", '',$str);
            $str = str_replace("ٌ", '',$str);
            $str = str_replace("ِ", '',$str);
            $str = str_replace("ٍ", '',$str);
            $str = str_replace("ْ", '',$str);
            
            return $str;
        }
        
        
        function arabic_to_latin_chars($str)
        {
            $str = arabic_unchakl($str);
            
            $str = str_replace("أ", 'A',$str);
            $str = str_replace("ا", 'E',$str);
            $str = str_replace("إ", 'I',$str);
            $str = str_replace("آ", 'Y',$str);
            $str = str_replace("ب", 'B',$str);
            $str = str_replace("ت", 'T',$str);
            $str = str_replace("ة", 't',$str);
            $str = str_replace("ث", 'X',$str);
            $str = str_replace("ج", 'J',$str);
            $str = str_replace("ح", '7',$str);
            $str = str_replace("خ", 'W',$str);
            $str = str_replace("د", 'D',$str);
            $str = str_replace("ذ", 'd',$str);
            $str = str_replace("ر", 'R',$str);
            $str = str_replace("ز", 'Z',$str);
            $str = str_replace("س", 'S',$str);
            $str = str_replace("ش", 'c',$str);
            $str = str_replace("ص", 's',$str);
            $str = str_replace("ض", 'u',$str);
            $str = str_replace("ظ", 'U',$str);
            $str = str_replace("ط", 'V',$str);
            $str = str_replace("ع", '3',$str);
            $str = str_replace("ف", 'F',$str);
            $str = str_replace("ق", 'K',$str);
            $str = str_replace("ك", 'k',$str);
            $str = str_replace("ل", 'L',$str);
            $str = str_replace("م", 'M',$str);
            $str = str_replace("ن", 'N',$str);
            $str = str_replace("ه", 'H',$str);
            $str = str_replace("و", 'w',$str);
            $str = str_replace("ؤ", 'o',$str);
            $str = str_replace("ي", 'y',$str);
            $str = str_replace("ى", 'i',$str);
            $str = str_replace("ء", 'a',$str);
            $str = str_replace("ئ", 'Y',$str);
            
            return $str;
        }
        
        
        function Allah_names($unchakl = true, $indexed = true)
        {
             $arr = array("اللَّه",
                        "الرَّحْمَنُ",
                        "الرَّحِيمُ",
                        "المَلِكُ",
                        "القُدُّوسُ",
                        "السَّلَامُ",
                        "المُؤْمِنُ",
                        "المُهَيْمِنُ",
                        "العَزِيزُ",
                        "الجَبَّارُ",
                        "المُتَكَبِّرُ",
                        "الخَالِقُ",
                        "البَارِىءُ",
                        "المُصَوِّرُ",
                        "الغَفَّارُ",
                        "القَهَّارُ",
                        "الوَهَّابُ",
                        "الرَّزَّاقُ",
                        "الفَتَّاحُ",
                        "العَلِيمُ",
                        "القَابِضُ",
                        "البَاسِطُ",
                        "الخَافِضُ",
                        "الرَّافِعُ",
                        "المُعِزُّ",
                        "المُذِلُّ",
                        "السَّمِيعُ",
                        "البَصِيرُ",
                        "الحَكَمُ",
                        "العَدْلُ",
                        "اللَّطِيفُ",
                        " الخَبِيرُ",
                        " الحَلِيمُ",
                        "العَظِيمُ",
                        "الغَفُورُ",
                        "الشَّكُورُ",
                        "العَلِيُّ",
                        "الكَبِيرُ",
                        "الحَفِيظُ",
                        " المُقِيتُ",
                        "الحَسِيبُ",
                        "الجَلِيلُ",
                        "الكَرِيمُ",
                        "الرَّقِيبُ",
                        "المُجِيبُ",
                        "الوَاسِعُ",
                        "الحَكِيمُ",
                        "الوَدُودُ",
                        "المَجِيدُ",
                        "البَاعِثُ",
                        "الشَّهِيدُ",
                        "الحَقُّ",
                        "الوَكِيلُ",
                        "القَوِيُّ",
                        "المَتِينُ",
                        "الوَلِيُّ",
                        "الحَمِيدُ",
                        "المُحْصِي",
                        "المُبْدِىءُ",
                        "المُعِيدُ",
                        "المُحْيِي",
                        "المُمِيتُ",
                        "الحَيُّ",
                        "القَيُّومُ",
                        "الوَاجِدُ",
                        "المَاجِدُ",
                        "الوَاحِدُ",
                        "الصَّمَدُ",
                        "القَادِرُ",
                        "المُقْتَدِرُ",
                        "المُقَدِّمُ",
                        "المُؤَخِّرُ",
                        "الأَوَّلُ",
                        "الآخِرُ",
                        "الظَّاهِرُ",
                        "البَاطِنُ",
                        "الوَالِي",
                        "المُتَعَالِ",
                        "البَرُّ",
                        "التَّوَّابُ",
                        "المُنْتَقِمُ",
                        "العَفُوُّ",
                        "الرَّءُوفُ",
                        "مَالِكُ المُلْكِ",
                        "ذُو الجَلَالِ وَالإِكْرَامِ",
                        "المُقْسِطُ",
                        "الجَامِعُ",
                        "الغَنِيُّ",
                        "المُغْنِيُّ",
                        "المَانِعُ",
                        "الضَّارُ",
                        "النَّافِعُ",
                        "النُّورُ",
                        "الهَادِي",
                        "البَدِيعُ",
                        "البَاقِي",
                        "الوَارِثُ",
                        "الرَّشِيدُ",
                        "الصَّبُورُ");
                
                if($unchakl)
                {
                        $arr_final = array();
                        
                        foreach($arr as $name)
                        {
                            $arr_final[] = arabic_unchakl($name);
                        }
                }
                else
                {
                   $arr_final = $arr;
                }
                
                if($indexed)
                {
                        $arr_indexed = array();
                        
                        foreach($arr_final as $name)
                        {
                            $arr_indexed[$name] = true;
                        }
                        
                        $arr_final = $arr_indexed;
                }
                
                return $arr_final;        
        }
        
        
        function arabic_full_name_explode($full_name, $gfather=false)
        {
            $full_name_arr = explode(" ",$full_name);
            
            $prefix_items = array("عبد"=>true,
                                  "عبيد"=>true,
                                  "آل"=>true,
                                  "ال"=>true,
                                  "ابو"=>true,
                                  "ابا"=>true,
                                  "ابي"=>true,
                                  "أبو"=>true,
                                  "أبا"=>true,
                                  "أبي"=>true,
                                   
                                   );
                                   
            $suffix_items = array("الدين"=>true,
                                   
                                   );                       
                                   
                                   
                                   
            $Allah_names = Allah_names();                       
            
            
            $full_name_list = array();
            $k = 0;
            for($i = 0; $i < count($full_name_arr); $i++)
            {
                 if($full_name_arr[$i])
                 {
                         // is prefix
                         if($prefix_items[$full_name_arr[$i]])
                         {
                            $full_name_arr[$i] .= " " . $full_name_arr[$i+1]; 
                            $full_name_arr[$i+1] = "";
                            
                            $full_name_list[$k] = $full_name_arr[$i];
                            $k++;
                         }
                         elseif($Allah_names[$full_name_arr[$i]] or $suffix_items[$full_name_arr[$i]])
                         {
                            // is suffix
                            if($k) $full_name_list[$k-1] = $full_name_list[$k-1]." ".$full_name_arr[$i];
                         }
                         else
                         {
                            // is word
                            $full_name_list[$k] = $full_name_arr[$i];
                            $k++;
                         }
                 }
            }
            
            $first_name = "";
            $father_name = "";
            $last_name = "";
            
            switch(count($full_name_list))
            {
                  case 0 :
                     break;
                  case 1 : 
                     $first_name = $full_name_list[0];
                     break;
                  case 2 : 
                     $first_name = $full_name_list[0];
                     $last_name = $full_name_list[1];
                     break; 
                  case 3 : 
                     $first_name = $full_name_list[0];
                     $father_name = $full_name_list[1];
                     $last_name = $full_name_list[2];
                     break;
                  case 4 : 
                     $first_name = $full_name_list[0];
                     if(!$gfather) 
                     {
                        $father_name = $full_name_list[1]." ".$full_name_list[2];
                        $gfather_name = "";
                     }
                     else
                     {
                        $father_name = $full_name_list[1];
                        $gfather_name = $full_name_list[2];
                     }
                     $last_name = $full_name_list[3];
                     break;
                  case 5 : 
                    if(!$gfather) 
                    {
                        $first_name = $full_name_list[0]." ".$full_name_list[1];
                        $father_name = $full_name_list[2]." ".$full_name_list[3];
                        $gfather_name = "";
                        
                    }
                    else
                    {
                        $first_name = $full_name_list[0]." ".$full_name_list[1];
                        $father_name = $full_name_list[2];
                        $gfather_name = $full_name_list[3];
                    }
                    $last_name = $full_name_list[4]; 
                     break;
                  default :
                    if(!$gfather) 
                    {
                        $first_name = $full_name_list[0]." ".$full_name_list[1];
                        $father_name = $full_name_list[2]." ".$full_name_list[3];
                        $gfather_name = "";
                        
                    }
                    else
                    {
                        $first_name = $full_name_list[0]." ".$full_name_list[1];
                        $father_name = $full_name_list[2];
                        $gfather_name = $full_name_list[3];
                    }
                     for($ii=0;$ii<4;$ii++) unset($full_name_list[$ii]);
                     $last_name = implode(" ",$full_name_list);
                     break;          
            } 
        
            if(!$gfather) return array($first_name, $father_name, $last_name);
            else return array($first_name, $father_name, $gfather_name, $last_name);
        }
        
        
        function truncateArabicJomla($jomla, $maxlen, $etc="...")
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
        
        function trimSpecialChars($string)
        {
             $string = trim($string);
             $string = trim($string,"\n");
             $string = trim($string,",");
             $string = trim($string,".");
             
             $string = trim($string,":");
             $string = trim($string,"/");
             $string = trim($string,"*");
             $string = trim($string,"-");
             $string = trim($string,"+");
             //$string = arTrim($string,"،");
             
             return $string;
        }
        
        function arTrim($string, $strTrimmed)
        {
            $string_after = $string;
            // rafik this below is bugged do not use and we may create another arTrim function later
            // preg_replace('/^['.$strTrimmed.'\s]+|['.$strTrimmed.'\s]+$/u', '', $string);
            //echo "strTrimmed=$strTrimmed   :::: string=$string   => <br>\n string_after = $string_after<br>\n";
            return $string_after;
        }
        
        function arabicSpecialTrim($string)
        {
             $string = arTrim($string,"السلام عليكم");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"وعليكم السلام");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"ورحمة الله");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"ورحمه الله");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"وبركاته");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"أما بعد");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"أما بعد");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"تحية طيبة");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"تحية طيبه");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"تحيه طيبه");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"تحيه طيبة");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"وبعد");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"بعد التحيه");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"بعد التحية");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"شكرا لك على تواصلك");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"شكرا لك");
             $string = trimSpecialChars($string);
             
             $string = arTrim($string,"نشكرك على تواصلك");
             $string = trimSpecialChars($string);
             $string = arTrim($string,"نشكرك");
             $string = trimSpecialChars($string);
             
             return $string;
        }
        
        function arabicSpecialJomlaTrim($string, $counter=30)
        {
             for($c=1;$c<=$counter;$c++) $string = arabicSpecialTrim($string);
             
             return $string;
        }
        
        function arabicStartOfJomlaTrim($jomla, $maxlen=64, $counter=30, $etc="...")
        {
            $trimmed = arabicSpecialJomlaTrim($jomla, $counter);
            //return "tt=".$trimmed; 
            return truncateArabicJomla($trimmed, $maxlen, $etc);
        }
        
        function arabicCounter($cnt, $genre="M", $prefix="ال", $suffix="")
        {
              $array_of_arabicCounters = array();
              
              $array_of_arabicCounters["M"] = array(
                1=>"أول",
                2=>"ثاني",
                3=>"ثالث",
                4=>"رابع",
                5=>"خامس",
                6=>"سادس",
                7=>"سابع",
                8=>"ثامن",
                9=>"تاسع",
                10=>"عاشر",
              );
              
              $array_of_arabicCounters["F"] = array(
                1=>"أولى",
                2=>"ثانية",
                3=>"ثالثة",
                4=>"رابعة",
                5=>"خامسة",
                6=>"سادسة",
                7=>"سابعة",
                8=>"ثامنة",
                9=>"تاسعة",
                10=>"عاشرة",
              );
              
              $word = $array_of_arabicCounters[$genre][$cnt];
              if($word) $word = $prefix . $word . $suffix;
              else $word = "رقم ". $cnt;
        
        
              return $word;
        }
        
        function nbWordsInJomla($jomla, $empty_is_counted=false)
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

        

?>