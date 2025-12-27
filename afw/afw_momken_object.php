<?php
class AfwMomkenObject extends AFWObject {
   
        public static function code_of_language_enum($lkp_id=null)
        {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            if($lkp_id) return self::language()['code'][$lkp_id];
            else return self::language()['code'];
        }

        public function fld_CREATION_USER_ID()
        {
                return "created_by";
        }
 
        public function fld_CREATION_DATE()
        {
                return "created_at";
        }
 
        public function fld_UPDATE_USER_ID()
        {
        	return "updated_by";
        }
 
        public function fld_UPDATE_DATE()
        {
        	return "updated_at";
        }
 
        public function fld_VALIDATION_USER_ID()
        {
        	return "validated_by";
        }
 
        public function fld_VALIDATION_DATE()
        {
                return "validated_at";
        }
 
        public function fld_VERSION()
        {
        	return "version";
        }
 
        public function fld_ACTIVE()
        {
        	return  "active";
        }
 
        
        public function getTimeStampFromRow($row,$context="update", $timestamp_field="")
        {
                if(!$timestamp_field) return $row["synch_timestamp"];
                else return $row[$timestamp_field];
        }        

        public static function list_of_language_enum()
        {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            return self::language()[$lang];
        }
        
        public static function language()
        {
                $arr_list_of_language = array();
                
                
                $arr_list_of_language["en"][1] = "Arabic";
                $arr_list_of_language["ar"][1] = "العربية";
                $arr_list_of_language["code"][1] = "ar";

                $arr_list_of_language["en"][2] = "English";
                $arr_list_of_language["ar"][2] = "الإنجليزية";
                $arr_list_of_language["code"][2] = "en";

                
                
                
                return $arr_list_of_language;
        } 

        public static function list_of_genre_enum()
        {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            return self::genre()[$lang];
        }
        
        public static function genre()
        {
                $arr_list_of_gender = array();
                
                
                $arr_list_of_gender["en"][1] = "Male";
                $arr_list_of_gender["ar"][1] = "بنين";
                $arr_list_of_gender["code"][1] = "M";

                $arr_list_of_gender["en"][2] = "Female";
                $arr_list_of_gender["ar"][2] = "بنات";
                $arr_list_of_gender["code"][2] = "F";

                
                return $arr_list_of_gender;
        }


        public static function list_of_gender_id()
        {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            return self::gender()[$lang];
        }
        
        public static function gender()
        {
                $arr_list_of_gender = array();
                
                
                $arr_list_of_gender["en"][1] = "Male";
                $arr_list_of_gender["ar"][1] = "ذكر";
                $arr_list_of_gender["code"][1] = "M";

                $arr_list_of_gender["en"][2] = "Female";
                $arr_list_of_gender["ar"][2] = "أنثى";
                $arr_list_of_gender["code"][2] = "F";

                
                return $arr_list_of_gender;
        }


        public static function list_of_stars()
        {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            return self::stars()[$lang];
        }
        
        public static function stars()
        {
                $arr_list_of_stars = array();
                



                
                $arr_list_of_stars["en"][1] = "Very dissatisfied";
                $arr_list_of_stars["ar"][1] = "غير راضي إطلاقًا";
                $arr_list_of_stars["code"][1] = "*";

                $arr_list_of_stars["en"][2] = "Dissatisfied";
                $arr_list_of_stars["ar"][2] = "غير راضي";
                $arr_list_of_stars["code"][2] = "**";

                $arr_list_of_stars["en"][3] = "Neutral";
                $arr_list_of_stars["ar"][3] = "محايد";
                $arr_list_of_stars["code"][3] = "***";

                $arr_list_of_stars["en"][4] = "Satisfied";
                $arr_list_of_stars["ar"][4] = "راضي";
                $arr_list_of_stars["code"][4] = "****";

                $arr_list_of_stars["en"][5] = "Very satisfied";
                $arr_list_of_stars["ar"][5] = "راضي جدًا";
                $arr_list_of_stars["code"][5] = "*****";

                
                return $arr_list_of_stars;
        }

        public static function list_of_aparameter_type_enum()
        {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            
            $return = self::afield_type()[$lang];

            unset($return[5]);
            unset($return[6]);
            return $return;
        }

        public static function list_of_afield_type_enum()
        {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            return self::afield_type()[$lang];
        }

        public static function field_type_code($fieldTypeId)        
        {
            return self::afield_type()["code"][$fieldTypeId];
        }

        public static function afield_type_by_code($the_code)        
        {
            $arr = self::afield_type();
            foreach($arr['code'] as $eid => $code)
            {
                if($the_code==$code) return $eid;
            }
            return 0;
        }

        public static function need_decode($fieldTypeId)        
        {
            return self::afield_type()["need_decode"][$fieldTypeId];
        }
        
        public static function fromAFWtoAfieldType($afwType, $afwCat, $structure)
        {
            // $file_dir_name = dirname(__FILE__);
            // 
            $afwType = strtoupper($afwType);
            if ($afwType == 'FK') {
                if ($afwCat == 'ITEMS') {
                    return self::afield_type_by_code("items");
                }
                return self::afield_type_by_code("list");
            } elseif ($afwType == 'MFK') {
                return self::afield_type_by_code("mlst");
            } elseif ($afwType == 'MENUM') {
                return self::afield_type_by_code("menum");
            } elseif ($afwType == 'MTEXT') {
                return self::afield_type_by_code("mtxt");
            } elseif ($afwType == 'YN') {
                return self::afield_type_by_code("yn");
            } elseif ($afwType == 'TEXT') {
                if ($structure['SIZE'] == 'AREA' or $structure['SIZE'] == 'AEREA') {
                    return self::afield_type_by_code("mtxt");
                } else {
                    return self::afield_type_by_code("text");
                }
            } elseif ($afwType == 'DATE') {
                return self::afield_type_by_code("date");
            } elseif ($afwType == 'GDAT') {
                return self::afield_type_by_code("Gdat");
            } elseif ($afwType == 'GDATE') {
                return self::afield_type_by_code("Gdat");
            } elseif ($afwType == 'DATETIME') {
                return self::afield_type_by_code("Gdat");
            } elseif ($afwType == 'INT') {
                return self::afield_type_by_code("int");
            } elseif ($afwType == 'BIGINT') {
                return self::afield_type_by_code("bigint");
            } elseif ($afwType == 'INT') {
                return self::afield_type_by_code("nmbr");
            } elseif ($afwType == 'ENUM') {
                return self::afield_type_by_code("enum");
            } elseif ($afwType == 'AMNT') {
                return self::afield_type_by_code("amnt");
            } elseif ($afwType == 'PCTG') {
                return self::afield_type_by_code("pctg");
            } elseif ($afwType == 'TIME') {
                return self::afield_type_by_code("time");
            } elseif ($afwType == 'FLOAT') {
                return self::afield_type_by_code("float");
            } else {
                return -1;
                // throw new AfwRuntimeException("[$afwType] afw type is strange and can not be converted to a known application field type");
            }
        }


        public static function afield_type()
        {
                $arr_list_of_afield_type = array();

                
                // DATE -  هجري تاريخ  
                // AFIELD_TYPE_DATE = 2; 
                $arr_list_of_afield_type["en"]  [2] = "Date hijri";
                $arr_list_of_afield_type["ar"]  [2] = "تاريخ هجري";
                $arr_list_of_afield_type["code"][2] = "date";
                
                

                // AMNT - مبلغ من المال  
                // AFIELD_TYPE_AMNT = 3; 
                $arr_list_of_afield_type["en"]  [3] = "Amount";
                $arr_list_of_afield_type["ar"]  [3] = "مبلغ من المال";
                $arr_list_of_afield_type["code"][3] = "amnt";
                $arr_list_of_afield_type["numeric"][3] = true;

                

                // SMALLINT - قيمة عددية صغيرة  
                // AFIELD_TYPE_SMALLINT = 13; 
                $arr_list_of_afield_type["en"]  [13] = "Small Numeric Value";
                $arr_list_of_afield_type["ar"]  [13] = "قيمة عددية صغيرة";
                $arr_list_of_afield_type["code"][13] = "smallnmbr";
                $arr_list_of_afield_type["numeric"][13] = true;

                // BIGINT - قيمة عددية كبيرة  
                // AFIELD_TYPE_BIGINT = 14; 
                $arr_list_of_afield_type["en"]  [14] = "Big Numeric Value";
                $arr_list_of_afield_type["ar"]  [14] = "قيمة عددية كبيرة";
                $arr_list_of_afield_type["code"][14] = "bignmbr";
                $arr_list_of_afield_type["numeric"][14] = true;

                // NMBR - قيمة عددية متوسطة  
                // AFIELD_TYPE_NMBR = 1; 
                $arr_list_of_afield_type["en"]  [1] = "Medium Numeric Value";
                $arr_list_of_afield_type["ar"]  [1] = "قيمة عددية متوسطة";
                $arr_list_of_afield_type["code"][1] = "nmbr";
                $arr_list_of_afield_type["numeric"][1] = true;


                // LIST - اختيار من قائمة  
                // AFIELD_TYPE_LIST = 5; 
                $arr_list_of_afield_type["en"]  [5] = "Choose from list";
                $arr_list_of_afield_type["ar"]  [5] = "اختيار من قائمة";
                $arr_list_of_afield_type["code"][5] = "list";
                $arr_list_of_afield_type["need_decode"][5] = true;

                // MFK - اختيار متعدد من قائمة  
                // AFIELD_TYPE_MLST = 6;                 
                $arr_list_of_afield_type["en"]  [6] = "multiple choice from list";
                $arr_list_of_afield_type["ar"]  [6] = "اختيار متعدد من قائمة";
                $arr_list_of_afield_type["code"][6] = "mfk";
                $arr_list_of_afield_type["need_decode"][6] = true;
                
                // PCTG - نسبة مائوية  
                // AFIELD_TYPE_PCTG = 7; 
                $arr_list_of_afield_type["en"]  [7] = "Percentage";
                $arr_list_of_afield_type["ar"]  [7] = "نسبة مائوية";
                $arr_list_of_afield_type["code"][7] = "pctg";
                $arr_list_of_afield_type["numeric"][7] = true;

                // GDAT - تاريخ ميلادي  
                // AFIELD_TYPE_GDAT = 9; 
                $arr_list_of_afield_type["en"]  [9] = "G. Date";
                $arr_list_of_afield_type["ar"]  [9] = "تاريخ ميلادي";
                $arr_list_of_afield_type["code"][9] = "Gdat";

                // YN - نعم/لا  
                // AFIELD_TYPE_YN = 8;
                $arr_list_of_afield_type["en"]  [8] = "Yes/No";
                $arr_list_of_afield_type["ar"]  [8] = "نعم/لا";
                $arr_list_of_afield_type["code"][8] = "yn";

                // ENUM - إختيار من قائمة قصيرة  
                // AFIELD_TYPE_ENUM = 12; 
                $arr_list_of_afield_type["en"]  [12] = "Short list - one choice";
                $arr_list_of_afield_type["ar"]  [12] = "إختيار من قائمة قصيرة";
                $arr_list_of_afield_type["code"][12] = "enum";
                $arr_list_of_afield_type["need_decode"][12] = true;

                // MENUM - إختيار متعدد من قائمة قصيرة  
                // AFIELD_TYPE_MENUM = 15; 
                $arr_list_of_afield_type["en"]  [15] = "Short list - multiple choice";
                $arr_list_of_afield_type["ar"]  [15] = "إختيار متعدد من قائمة قصيرة";
                $arr_list_of_afield_type["code"][15] = "menum";
                $arr_list_of_afield_type["need_decode"][15] = true;

                // FLOAT - قيمة عددية كسرية  
                // AFIELD_TYPE_FLOAT = 16;
                $arr_list_of_afield_type["en"]  [16] = "float value";
                $arr_list_of_afield_type["ar"]  [16] = "قيمة عددية كسرية";
                $arr_list_of_afield_type["code"][16] = "float";
                $arr_list_of_afield_type["numeric"][16] = true;

                // 	10	نص قصير
                // $afield_type_text = 10;
                $arr_list_of_afield_type["en"]  [10] = "short text";
                $arr_list_of_afield_type["ar"]  [10] = "نص قصير";
                $arr_list_of_afield_type["code"][10] = "text";

                return $arr_list_of_afield_type;
        } 

        public static function list_of_answer_table_id()
        {
            $lang = AfwLanguageHelper::getGlobalLanguage();
            return self::answer_table()[$lang];
        }
        

        public static function answer_table_code($ansTabId)        
        {
            return self::answer_table()["code"][$ansTabId];
        }

        public static function answer_table_module($ansTabId)        
        {
            return self::answer_table()["module"][$ansTabId];
        }

        public static function answer_table()
        {
            // to be defined in sub-classes not here because depend on context and module
                $arr_list_of_answer_table = array();
                /*
                $arr_list_of_answer_table["ar"][1] = "yyyy yyy";
                $arr_list_of_answer_table["en"][1] = "yyyy yyy";
                $arr_list_of_answer_table["code"][1] = "yyyy yyy"
                $arr_list_of_answer_table["module"][1] = "ums";
                
                
                $arr_list_of_answer_table["ar"][2] = "xxxx";
                $arr_list_of_answer_table["en"][2] = "xxxx xxxx";
                $arr_list_of_answer_table["code"][2] = "xxxx";
                $arr_list_of_answer_table["module"][2] = "crm";
                */


                return $arr_list_of_answer_table;
        }
}