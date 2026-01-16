<?php
class AfwMomkenObject extends AFWObject
{
    public static function code_of_language_enum($lkp_id = null)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        if ($lkp_id)
            return self::language()['code'][$lkp_id];
        else
            return self::language()['code'];
    }

    public function fld_CREATION_USER_ID()
    {
        return 'created_by';
    }

    public function fld_CREATION_DATE()
    {
        return 'created_at';
    }

    public function fld_UPDATE_USER_ID()
    {
        return 'updated_by';
    }

    public function fld_UPDATE_DATE()
    {
        return 'updated_at';
    }

    public function fld_VALIDATION_USER_ID()
    {
        return 'validated_by';
    }

    public function fld_VALIDATION_DATE()
    {
        return 'validated_at';
    }

    public function fld_VERSION()
    {
        return 'version';
    }

    public function fld_ACTIVE()
    {
        return 'active';
    }

    public function getTimeStampFromRow($row, $context = 'update', $timestamp_field = '')
    {
        if (!$timestamp_field)
            return $row['synch_timestamp'];
        else
            return $row[$timestamp_field];
    }

    public static function list_of_language_enum()
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        return self::language()[$lang];
    }

    public static function language()
    {
        $arr_list_of_language = array();

        $arr_list_of_language['en'][1] = 'Arabic';
        $arr_list_of_language['ar'][1] = 'العربية';
        $arr_list_of_language['code'][1] = 'ar';

        $arr_list_of_language['en'][2] = 'English';
        $arr_list_of_language['ar'][2] = 'الإنجليزية';
        $arr_list_of_language['code'][2] = 'en';

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

        $arr_list_of_gender['en'][1] = 'Male';
        $arr_list_of_gender['ar'][1] = 'بنين';
        $arr_list_of_gender['code'][1] = 'M';

        $arr_list_of_gender['en'][2] = 'Female';
        $arr_list_of_gender['ar'][2] = 'بنات';
        $arr_list_of_gender['code'][2] = 'F';

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

        $arr_list_of_gender['en'][1] = 'Male';
        $arr_list_of_gender['ar'][1] = 'ذكر';
        $arr_list_of_gender['code'][1] = 'M';

        $arr_list_of_gender['en'][2] = 'Female';
        $arr_list_of_gender['ar'][2] = 'أنثى';
        $arr_list_of_gender['code'][2] = 'F';

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

        $arr_list_of_stars['en'][1] = 'Very dissatisfied';
        $arr_list_of_stars['ar'][1] = 'غير راضي إطلاقًا';
        $arr_list_of_stars['code'][1] = '*';

        $arr_list_of_stars['en'][2] = 'Dissatisfied';
        $arr_list_of_stars['ar'][2] = 'غير راضي';
        $arr_list_of_stars['code'][2] = '**';

        $arr_list_of_stars['en'][3] = 'Neutral';
        $arr_list_of_stars['ar'][3] = 'محايد';
        $arr_list_of_stars['code'][3] = '***';

        $arr_list_of_stars['en'][4] = 'Satisfied';
        $arr_list_of_stars['ar'][4] = 'راضي';
        $arr_list_of_stars['code'][4] = '****';

        $arr_list_of_stars['en'][5] = 'Very satisfied';
        $arr_list_of_stars['ar'][5] = 'راضي جدًا';
        $arr_list_of_stars['code'][5] = '*****';

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
        return self::afield_type()['code'][$fieldTypeId];
    }

    public static function afield_type_by_code($the_code)
    {
        $arr = self::afield_type();
        foreach ($arr['code'] as $eid => $code) {
            if ($the_code == $code)
                return $eid;
        }
        return 0;
    }

    public static function need_decode($fieldTypeId)
    {
        return self::afield_type()['need_decode'][$fieldTypeId];
    }

    public static function fromAFWtoAfieldType($afwType, $afwCat, $structure)
    {
        // $file_dir_name = dirname(__FILE__);
        //
        $afwType = strtoupper($afwType);
        if ($afwType == 'FK') {
            if ($afwCat == 'ITEMS') {
                return self::afield_type_by_code('items');
            }
            return self::afield_type_by_code('list');
        } elseif ($afwType == 'MFK') {
            return self::afield_type_by_code('mlst');
        } elseif ($afwType == 'MENUM') {
            return self::afield_type_by_code('menum');
        } elseif ($afwType == 'MTEXT') {
            return self::afield_type_by_code('mtxt');
        } elseif ($afwType == 'YN') {
            return self::afield_type_by_code('yn');
        } elseif ($afwType == 'TEXT') {
            if ($structure['SIZE'] == 'AREA' or $structure['SIZE'] == 'AEREA') {
                return self::afield_type_by_code('mtxt');
            } else {
                return self::afield_type_by_code('text');
            }
        } elseif ($afwType == 'DATE') {
            return self::afield_type_by_code('date');
        } elseif ($afwType == 'GDAT') {
            return self::afield_type_by_code('Gdat');
        } elseif ($afwType == 'GDATE') {
            return self::afield_type_by_code('Gdat');
        } elseif ($afwType == 'DATETIME') {
            return self::afield_type_by_code('Gdat');
        } elseif ($afwType == 'SMLINT') {
            return self::afield_type_by_code('smallnmbr');
        } elseif ($afwType == 'BIGINT') {
            return self::afield_type_by_code('bignmbr');
        } elseif ($afwType == 'INT') {
            return self::afield_type_by_code('nmbr');
        } elseif ($afwType == 'ENUM') {
            return self::afield_type_by_code('enum');
        } elseif ($afwType == 'AMNT') {
            return self::afield_type_by_code('amnt');
        } elseif ($afwType == 'PCTG') {
            return self::afield_type_by_code('pctg');
        } elseif ($afwType == 'TIME') {
            return self::afield_type_by_code('time');
        } elseif ($afwType == 'FLOAT') {
            return self::afield_type_by_code('float');
        } else {
            return -1;
            // throw new AfwRuntimeException("[$afwType] afw type is strange and can not be converted to a known application field type");
        }
    }

    public static function afield_type()
    {
        $arr_list_of_afield_type = array();

        // 2 - DATE -  هجري تاريخ
        $arr_list_of_afield_type['en'][2] = 'Date hijri';
        $arr_list_of_afield_type['ar'][2] = 'تاريخ هجري';
        $arr_list_of_afield_type['code'][2] = 'date';

        // 3 - AMNT - مبلغ من المال
        $arr_list_of_afield_type['en'][3] = 'Amount';
        $arr_list_of_afield_type['ar'][3] = 'مبلغ من المال';
        $arr_list_of_afield_type['code'][3] = 'amnt';
        $arr_list_of_afield_type['numeric'][3] = true;

        // 13 - SMALLINT - قيمة عددية صغيرة
        $arr_list_of_afield_type['en'][13] = 'Small Numeric Value';
        $arr_list_of_afield_type['ar'][13] = 'قيمة عددية صغيرة';
        $arr_list_of_afield_type['code'][13] = 'smallnmbr';
        $arr_list_of_afield_type['numeric'][13] = true;

        // 14 - BIGINT - قيمة عددية كبيرة
        $arr_list_of_afield_type['en'][14] = 'Big Numeric Value';
        $arr_list_of_afield_type['ar'][14] = 'قيمة عددية كبيرة';
        $arr_list_of_afield_type['code'][14] = 'bignmbr';
        $arr_list_of_afield_type['numeric'][14] = true;

        // 1 = NMBR - قيمة عددية متوسطة  =
        $arr_list_of_afield_type['en'][1] = 'Medium Numeric Value';
        $arr_list_of_afield_type['ar'][1] = 'قيمة عددية متوسطة';
        $arr_list_of_afield_type['code'][1] = 'nmbr';
        $arr_list_of_afield_type['numeric'][1] = true;

        // 5 - LIST - اختيار من قائمة
        $arr_list_of_afield_type['en'][5] = 'Choose from list';
        $arr_list_of_afield_type['ar'][5] = 'اختيار من قائمة';
        $arr_list_of_afield_type['code'][5] = 'list';
        $arr_list_of_afield_type['need_decode'][5] = true;

        // 6 - MFK - اختيار متعدد من قائمة
        $arr_list_of_afield_type['en'][6] = 'multiple choice from list';
        $arr_list_of_afield_type['ar'][6] = 'اختيار متعدد من قائمة';
        $arr_list_of_afield_type['code'][6] = 'mfk';
        $arr_list_of_afield_type['need_decode'][6] = true;

        // 7 - PCTG - نسبة مائوية
        $arr_list_of_afield_type['en'][7] = 'Percentage';
        $arr_list_of_afield_type['ar'][7] = 'نسبة مائوية';
        $arr_list_of_afield_type['code'][7] = 'pctg';
        $arr_list_of_afield_type['numeric'][7] = true;

        // 9 - GDAT - تاريخ ميلادي
        $arr_list_of_afield_type['en'][9] = 'G. Date';
        $arr_list_of_afield_type['ar'][9] = 'تاريخ ميلادي';
        $arr_list_of_afield_type['code'][9] = 'Gdat';

        // 8 - YN - نعم/لا
        $arr_list_of_afield_type['en'][8] = 'Yes/No';
        $arr_list_of_afield_type['ar'][8] = 'نعم/لا';
        $arr_list_of_afield_type['code'][8] = 'yn';

        // 12 - ENUM - إختيار من قائمة قصيرة
        $arr_list_of_afield_type['en'][12] = 'Short list - one choice';
        $arr_list_of_afield_type['ar'][12] = 'إختيار من قائمة قصيرة';
        $arr_list_of_afield_type['code'][12] = 'enum';
        $arr_list_of_afield_type['need_decode'][12] = true;

        // 15 - MENUM - إختيار متعدد من قائمة قصيرة
        $arr_list_of_afield_type['en'][15] = 'Short list - multiple choice';
        $arr_list_of_afield_type['ar'][15] = 'إختيار متعدد من قائمة قصيرة';
        $arr_list_of_afield_type['code'][15] = 'menum';
        $arr_list_of_afield_type['need_decode'][15] = true;

        // 16 - FLOAT - قيمة عددية كسرية
        $arr_list_of_afield_type['en'][16] = 'float value';
        $arr_list_of_afield_type['ar'][16] = 'قيمة عددية كسرية';
        $arr_list_of_afield_type['code'][16] = 'float';
        $arr_list_of_afield_type['numeric'][16] = true;

        // 	10 - TEXT -	نص قصير
        $arr_list_of_afield_type['en'][10] = 'short text';
        $arr_list_of_afield_type['ar'][10] = 'نص قصير';
        $arr_list_of_afield_type['code'][10] = 'text';

        // 	11	نص طويل
        $arr_list_of_afield_type['en'][11] = 'long text';
        $arr_list_of_afield_type['ar'][11] = 'نص طويل';
        $arr_list_of_afield_type['code'][11] = 'mtext';

        return $arr_list_of_afield_type;
    }

    public static function list_of_answer_table_id()
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        return self::answer_table()[$lang];
    }

    public static function answer_table_code($ansTabId)
    {
        return self::answer_table()['code'][$ansTabId];
    }

    public static function answer_table_module($ansTabId)
    {
        return self::answer_table()['module'][$ansTabId];
    }

    public static function answer_table()
    {
        // to be defined in sub-classes not here because depend on context and module
        $arr_list_of_answer_table = array();

        /*
         * $arr_list_of_answer_table["ar"][1] = "yyyy yyy";
         * $arr_list_of_answer_table["en"][1] = "yyyy yyy";
         * $arr_list_of_answer_table["code"][1] = "yyyy yyy"
         * $arr_list_of_answer_table["module"][1] = "ums";
         *
         *
         * $arr_list_of_answer_table["ar"][2] = "xxxx";
         * $arr_list_of_answer_table["en"][2] = "xxxx xxxx";
         * $arr_list_of_answer_table["code"][2] = "xxxx";
         * $arr_list_of_answer_table["module"][2] = "crm";
         */

        return $arr_list_of_answer_table;
    }

    /* obsolete should be defined in extra/domains-$main_company.php
    public static function domain()
    {
        // to be defined in sub-classes not here because depend on context and module
        $arr_list_of_domain = array();
        $arr_list_of_domain['ar'][12] = 'إدارة الأعمال';
        $arr_list_of_domain['en'][12] = 'Business';
        $arr_list_of_domain['code'][12] = 'BM';

        $arr_list_of_domain['ar'][18] = 'إدارة المحتوى';
        $arr_list_of_domain['en'][18] = 'Content Management';
        $arr_list_of_domain['code'][18] = 'CMS';

        $arr_list_of_domain['ar'][25] = 'التسجيل والقبول';
        $arr_list_of_domain['en'][25] = 'application & admission';
        $arr_list_of_domain['code'][25] = 'adm';

        $arr_list_of_domain['ar'][10] = 'الموارد البشرية';
        $arr_list_of_domain['en'][10] = 'Human Ressource';
        $arr_list_of_domain['code'][10] = 'HR';

        return $arr_list_of_domain;
    }*/

    public static function domain()
    {
        $main_company = AfwSession::currentCompany();
        $file_dir_name = dirname(__FILE__);
        $domains_file_name = $file_dir_name . "/../../client-$main_company/extra/domains-$main_company.php";
        if (file_exists($domains_file_name)) {
            return include($domains_file_name);
        }

        throw new AfwRuntimeException("Domain file missed : $domains_file_name");
    }

    public static function list_of_domain_enum()
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        $return = self::domain()[$lang];
        if (!$return)
            throw new AfwRuntimeException("lang = AfwLanguageHelper::getGlobalLanguage()=$lang, self::domain()[$lang] failed");
        return $return;
    }

    public static function domain_code($ansTabId)
    {
        return self::domain()['code'][$ansTabId];
    }

    public static function list_of_hierarchy_level_enum()
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        return self::hierarchy_level()[$lang];
    }

    public static function hierarchy_level()
    {
        $arr_list_of_hierarchy_level = array();

        $main_company = AfwSession::currentCompany();
        $current_domain = 25;
        $file_dir_name = dirname(__FILE__);
        include($file_dir_name . "/../../client-$main_company/extra/hierarchy_level-$main_company.php");

        foreach ($hierarchy_level as $id => $lookup_row) {
            $arr_list_of_hierarchy_level['ar'][$id] = $lookup_row['ar'];
            $arr_list_of_hierarchy_level['en'][$id] = $lookup_row['en'];
        }

        return $arr_list_of_hierarchy_level;
    }
}
