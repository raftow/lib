<?php
// old require of afw_root
class AfwDateHelper 
{
    private static $MIN_GREG_YEAR = 1000;
    private static $MAX_GREG_YEAR = 2999;

    private static $MIN_HIJRI_YEAR = 1000;
    private static $MAX_HIJRI_YEAR = 1999;

    private static $englishToArabicGregMonths = 
    [
        'JANUARY' =>        'يناير',
        'FEBRUARY' =>               'فبراير',
        'MARCH' =>                  'مارس',
        'APRIL' =>                  'أبريل',
        'MAYO' =>                   'مايو',
        'JUNE' =>                   'يونيو',
        'JULY' =>                   'يوليو',
        'AUGUST' =>                 'أغسطس',
        'SEPTEMBER' =>              'سبتمبر',
        'OCTOBER' =>                'أكتوبر',
        'NOVEMBER' =>               'نوفمبر',
        'DECEMBER' =>               'ديسمبر',
    ];


    

    private static $enGregMonths = [
        'January',
        'February',
        'March',
        'April',
        'mayo',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    ];

    private static $gregMonths = [
        'يناير',
        'فبراير',
        'مارس',
        'أبريل',
        'مايو',
        'يونيو',
        'يوليو',
        'أغسطس',
        'سبتمبر',
        'أكتوبر',
        'نوفمبر',
        'ديسمبر',
    ];

    private static $hijMonths = [
        'محرم',
        'صفر',
        'ربيع الأول',
        'ربيع الآخر',
        'جمادى الأولى',
        'جمادى الآخرة',
        'رجب',
        'شعبان',
        'رمضان',
        'شوّال',
        'ذو القعدة',
        'ذو الحجة',
    ];

    private static $weekDays = [
        'الأحد',
        'الأثنين',
        'الثلاثاء',
        'الإربعاء',
        'الخميس',
        'الجمعة',
        'السبت',
    ];

    private static $shortWeekDays = [
        'أحد',
        'أثنين',
        'ثلاثاء',
        'إربعاء',
        'خميس',
        'جمعة',
        'سبت',
    ];

    public static function dateToTimestamp($date)
    {
        $arr_dat = explode(' ', $date);
        $arr_day = explode('-', $arr_dat[0]);
        $arr_hour = explode(':', $arr_dat[1]);
        if (!$arr_hour[0]) {
            $arr_hour[0] = 0;
        }
        if (!$arr_hour[1]) {
            $arr_hour[1] = 0;
        }
        if (!$arr_hour[2]) {
            $arr_hour[2] = 0;
        }
        $tmstmp = mktime(
            $arr_hour[0],
            $arr_hour[1],
            $arr_hour[2],
            $arr_day[1],
            $arr_day[2],
            $arr_day[0]
        );

        return $tmstmp;
    }

    public static function weekDayNum($wanted_week_day)
    {
        if (is_numeric($wanted_week_day)) {
            return $wanted_week_day;
        }
        if (strtolower($wanted_week_day) == 'sunday') {
            return 0;
        }
        if (strtolower($wanted_week_day) == 'monday') {
            return 1;
        }
        if (strtolower($wanted_week_day) == 'tuesday') {
            return 2;
        }
        if (strtolower($wanted_week_day) == 'wednesday') {
            return 3;
        }
        if (strtolower($wanted_week_day) == 'thursday') {
            return 4;
        }
        if (strtolower($wanted_week_day) == 'friday') {
            return 5;
        }
        if (strtolower($wanted_week_day) == 'saturday') {
            return 6;
        }

        return $wanted_week_day;
    }

    public static function dayNameForDate($date_greg, $translate_lang = '')
    {
        $php_day_of_week = self::weekDayOf($date_greg);

        return self::dayNameOfDayNum($php_day_of_week, $translate_lang);
    }

    public static function nameDayTranslate($day_en_mame, $translate_lang='')    
    { 
        global $lang;
        if(!$translate_lang) $translate_lang = $lang;
        if(!$translate_lang) $translate_lang = "ar";
        $php_day_of_week = self::weekDayNum($day_en_mame);
        return self::dayNameOfDayNum($php_day_of_week, $translate_lang);
    }    

    public static function nameMonthTranslate($month_en_mame, $translate_lang='')
    {
        global $lang;
        if(!$translate_lang) $translate_lang = $lang;
        if(!$translate_lang) $translate_lang = "ar";
        if($translate_lang != "ar") return $month_en_mame;
        return self::$englishToArabicGregMonths[strtoupper(trim($month_en_mame))];
    }
        
    public static function dayNameOfDayNum($php_day_of_week, $translate_lang = '')
    {        
        // die("ss : $date_greg > $tms_dep > w = $day_of_week");
        if (!$translate_lang) {
            return $php_day_of_week;
        }

        $days_title_arr = [];
        $days_title_arr[1] = [
            'ar' => 'الأحد',
            'en' => 'sunday',
            'fr' => 'dimanche',
        ];
        $days_title_arr[2] = [
            'ar' => 'الاثنين',
            'en' => 'monday',
            'fr' => 'lundi',
        ];
        $days_title_arr[3] = [
            'ar' => 'الثلاثاء',
            'en' => 'tuesday',
            'fr' => 'mardi',
        ];
        $days_title_arr[4] = [
            'ar' => 'الاربعاء',
            'en' => 'wednesday',
            'fr' => 'mercredi',
        ];
        $days_title_arr[5] = [
            'ar' => 'الخميس',
            'en' => 'thursday',
            'fr' => 'jeudi',
        ];
        $days_title_arr[6] = [
            'ar' => 'الجمعة',
            'en' => 'friday',
            'fr' => 'vendredi',
        ];
        $days_title_arr[7] = [
            'ar' => 'السبت',
            'en' => 'saturday',
            'fr' => 'samedi',
        ];

        return $days_title_arr[$php_day_of_week + 1][$translate_lang];
    }

    /**
     *
     * return next coming week day ex Thursday after the date $from_date
     *
     */

    public static function nextWeekDayDate(
        $from_date = '',
        $wanted_week_day = 4
    ) {
        if (!$from_date) {
            $from_date = date('Y-m-d');
        }
        $auj = self::dateToTimestamp($from_date);
        $n = date('w', $auj);
        //if($n>0) $offset = 0; else $offset = -7;
        $previous_sunday = date('d', $auj) - $n;
        if ($n > 0) {
            $sunday = $previous_sunday + 7;
        } else {
            $sunday = $previous_sunday;
        }
        $wanted_week_day = self::weekDayNum($wanted_week_day);

        $wanted_day = $sunday + $wanted_week_day;
        $next_week_day = mktime(
            0,
            0,
            0,
            date('m', $auj),
            $wanted_day,
            date('Y', $auj)
        );
        $next_week_day_date = date('Y-m-d', $next_week_day);
        //die("from_date=$from_date, wanted_week_day=$wanted_week_day, sunday=$sunday, wanted_day=$wanted_day, next_week_day_date=$next_week_day_date");
        return $next_week_day_date;
    }

    public static function inputFormatHijriDate($hdate)
    {
        return implode(
            '-',
            self::splitHijriDate(self::repareHijriDate($hdate))
        );
    }

    public static function inputFormatDate($gdate)
    {
        if ($gdate == '0000-00-00') {
            return '';
        }
        return implode('-', self::splitGregDate($gdate));
    }

    public static function isCorrectHijriDate($hdate)
    {
        try {
            self::splitHijriDate($hdate, $convertToInt = true);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function splitHijriDate($hdate, $convertToInt = false)
    {
        if (strlen($hdate) != 8 or !is_numeric($hdate)) {
            throw new RuntimeException(
                "hijri date '$hdate' is not formatted correctly use format YYYYMMDD without '-' neither '/' nor any separator"
            );
        }
        $hdate_YYYY = substr($hdate, 0, 4);
        $hdate_MM = substr($hdate, 4, 2);
        $hdate_DD = substr($hdate, 6, 2);

        if (is_numeric($hdate_YYYY)) {
            $yyyy = intval($hdate_YYYY);
        } else {
            $yyyy = -1;
        }
        if ($yyyy < self::$MIN_HIJRI_YEAR or $yyyy > self::$MAX_HIJRI_YEAR) {
            throw new RuntimeException(
                "hijri date '$hdate' is not formatted correctly use format YYYYMMDD, incorrect year $hdate_YYYY"
            );
        }
        if (is_numeric($hdate_MM)) {
            $mm = intval($hdate_MM);
        } else {
            $mm = -1;
        }
        if ($mm < 1 and $mm > 12) {
            throw new RuntimeException(
                "hijri date '$hdate' is not formatted correctly use format YYYYMMDD, incorrect month $hdate_MM"
            );
        }

        if (is_numeric($hdate_DD)) {
            $dd = intval($hdate_DD);
        } else {
            $dd = -1;
        }
        if ($dd < 1 and $dd > 30) {
            throw new RuntimeException(
                "hijri date '$hdate' is not formatted correctly use format YYYYMMDD, incorrect day $hdate_DD"
            );
        }

        if ($convertToInt) {
            $hdate_YYYY = $yyyy;
            $hdate_MM = $mm;
            $hdate_DD = $dd;
        }

        return [$hdate_YYYY, $hdate_MM, $hdate_DD];
    }

    public static function splitGregDate($gdate, $convertToInt = false)
    {
        $return = explode('-', $gdate);

        if (count($return) != 3) {
            throw new RuntimeException(
                "gregorian date '$gdate' is not formatted correctly use format YYYY-MM-DD"
            );
        }

        $yyyy = intval($return[0]);
        if ($yyyy < self::$MIN_GREG_YEAR or $yyyy > self::$MAX_GREG_YEAR) {
            throw new RuntimeException(
                "greg date '$gdate' is not formatted correctly use format YYYY-MM-DD, incorrect year"
            );
        }
        $mm = intval($return[1]);
        if ($mm < 1 and $mm > 12) {
            throw new RuntimeException(
                "greg date '$gdate' is not formatted correctly use format YYYY-MM-DD, incorrect month"
            );
        }

        $dd = intval($return[2]);
        if ($dd < 1 and $dd > 31) {
            throw new RuntimeException(
                "greg date '$gdate' is not formatted correctly use format YYYY-MM-DD, incorrect day"
            );
        }

        if ($convertToInt) {
            $return[0] = $yyyy;
            $return[1] = $mm;
            $return[2] = $dd;
        }

        return $return;
    }

    public static function shortHijriDate($hdate)
    {
        return self::formatHijriDate(
            $hdate,
            $format = [
                'separator' => ' ',
                'month_name' => false,
                'show_day_name' => true,
                'short_day_name' => true,
                'show_year' => false,
                'show_month' => false,
            ]
        );
    }

    public static function mediumHijriDate($hdate)
    {
        return self::formatHijriDate(
            $hdate,
            $format = [
                'separator' => ' ',
                'month_name' => true,
                'show_day_name' => true,
                'short_day_name' => false,
                'show_year' => false,
                'show_month' => true,
            ]
        );
    }

    public static function fullHijriDate($hdate)
    {
        return self::formatHijriDate(
            $hdate,
            $format = [
                'separator' => ' ',
                'month_name' => true,
                'show_day_name' => true,
                'short_day_name' => false,
                'show_year' => true,
                'show_month' => true,
            ]
        );
    }

    public static function longHijriDate($hdate)
    {
        return self::formatHijriDate($hdate);
    }

    public static function formatHijriDate(
        $hdate,
        $format = [
            'separator' => ' ',
            'month_name' => true,
            'show_day_name' => true,
            'short_day_name' => false,
            'show_year' => true,
            'show_month' => true,
        ]
    ) {
        if (is_array($hdate)) {
            list($hijri_year, $hijri_month, $hijri_day) = $hdate;
        } else {
            list($hijri_year, $hijri_month, $hijri_day) = self::splitHijriDate(
                self::repareHijriDate($hdate)
            );
        }

        $weekday = self::weekDayOfHijriDate($hdate);
        $myDateFinal = '';

        if ($format['show_day_name']) {
            if ($format['short_day_name']) {
                $day_name = self::$shortWeekDays[$weekday];
            } else {
                $day_name = self::$weekDays[$weekday];
            }
            $myDateFinal .= $format['separator'] . $day_name;
        }
        $myDateFinal .= $format['separator'] . $hijri_day;
        if ($format['show_month']) {
            if ($format['month_name']) {
                $myDateFinal .=
                    $format['separator'] .
                    self::$hijMonths[intval($hijri_month) - 1];
            } else {
                $myDateFinal .= $format['separator'] . $hijri_month;
            }
        }

        if ($format['show_year']) {
            $myDateFinal .= $format['separator'] . $hijri_year;
        }

        return trim($myDateFinal, $format['separator']);
    }

    public static function shortGregDate($gdate)
    {
        return self::formatGregDate(
            $gdate,
            $format = [
                'separator' => ' ',
                'month_name' => false,
                'show_day_name' => true,
                'short_day_name' => true,
                'show_year' => false,
                'show_month' => false,
            ]
        );
    }

    public static function mediumGregDate($gdate)
    {
        return self::formatGregDate(
            $gdate,
            $format = [
                'separator' => ' ',
                'month_name' => true,
                'show_day_name' => true,
                'short_day_name' => false,
                'show_year' => false,
                'show_month' => true,
            ]
        );
    }

    public static function fullGregDate($gdate, $format_customized=array())
    {
        $format = [
            'separator' => ' ',
            'month_name' => true,
            'show_day_name' => true,
            'short_day_name' => false,
            'show_year' => true,
            'show_month' => true,
        ];

        foreach($format_customized as $key => $val)
        {
            $format[$key] = $val;
        }

        return self::formatGregDate($gdate, $format);
    }

    public static function longGregDate($gdate)
    {
        return self::formatGregDate($gdate);
    }

    public static function formatGregDate(
        $gdate,
        $format = [
            'separator' => ' ',
            'month_name' => true,
            'show_day_name' => true,
            'short_day_name' => false,
            'show_year' => true,
            'show_month' => true,
        ]
    ) {
        // remove time if exists
        list($gdate, $gtime) = explode(' ', $gdate);

        if (is_array($gdate)) {
            list($greg_year, $greg_month, $greg_day) = $gdate;
        } else {
            list($greg_year, $greg_month, $greg_day) = self::splitGregDate(
                $gdate
            );
        }

        $weekday = self::weekDayOf($gdate);
        $myDateFinal = '';

        if ($format['show_day_name']) {
            if ($format['short_day_name']) {
                $day_name = self::$shortWeekDays[$weekday];
            } else {
                $day_name = self::$weekDays[$weekday];
            }
            $myDateFinal .= $format['separator'] . $day_name;
        }
        $myDateFinal .= $format['separator'] . $greg_day;
        if ($format['show_month']) {
            if ($format['month_name']) {
                $myDateFinal .=
                    $format['separator'] .
                    self::$gregMonths[intval($greg_month) - 1];
            } else {
                $myDateFinal .= $format['separator'] .$greg_month;
            }
        }

        if ($format['show_year']) {
            $myDateFinal .= $format['separator'] . $greg_year;
        }

        return trim($myDateFinal, $format['separator']);
    }


    public static function addHijriPeriodToHijriDate($hdate,$nb_months, $nb_years=0)
    {
        if(strpos($hdate, '-')===false)
        {
            $hdate = self::add_dashes($hdate);
        }
        
        $hd_arr = explode('-',$hdate);
        $v_y1 = intval($hd_arr[0]);
        $v_m1 = intval($hd_arr[1]);
        $v_d1 = intval($hd_arr[2]);

        $v_m1 += $nb_months;
        
        if($v_m1>12)
        {
            $v_m2 = $v_m1;
            $v_m1 = $v_m2 % 12;
            $nb_years_added = floor($v_m2 / 12);
            
            $v_y1 += $nb_years_added;
        }
        
        $v_y1 += $nb_years;
        
        $mm = str_pad($v_m1, 2, "0", STR_PAD_LEFT);
        $dd = str_pad($v_d1, 2, '0', STR_PAD_LEFT);

        return $v_y1.$mm.$dd;
    }

    public static function genereHijriDates(
        $from_date,
        $to_date,
        $increment_hmonths = 0,
        $increment_hyears = 0,
        $calc_greg = true,
        $gen_desc = false
    ) {
        $my_date = $from_date;

        $arr_hij_period = [];
        if ($increment_hmonths + $increment_hyears > 0) {
            while ($my_date <= $to_date) {
                if ($gen_desc) {
                    $descr = 'xxxxx';
                } else {
                    $descr = '';
                }

                if ($increment_hmonths > 0) {
                    $counter = substr($my_date, 0, 6);
                } else {
                    $counter = substr($my_date, 0, 4);
                }
                if ($calc_greg) {
                    $my_gdate = self::hijriToGreg($my_date);
                } else {
                    $my_gdate = '';
                }
                $arr_hij_period[$my_date] = [
                    'hdate' => $my_date,
                    'greg' => $my_gdate,
                    'counter' => $counter,
                    'descr' => $descr,
                ];

                $my_date = self::addHijriPeriodToHijriDate(
                    $my_date,
                    $increment_hmonths,
                    $increment_hyears
                );
            }
        }

        return $arr_hij_period;
    }

    public static function gregToTimestamp($gdate)
    {
        $arr_dat = explode(' ', $gdate);
        $arr_day = explode('-', $arr_dat[0]);
        $arr_hour = explode(':', $arr_dat[1]);
        if (!$arr_hour[0]) {
            $arr_hour[0] = 0;
        }
        if (!$arr_hour[1]) {
            $arr_hour[1] = 0;
        }
        if (!$arr_hour[2]) {
            $arr_hour[2] = 0;
        }
        $tmstmp = mktime(
            $arr_hour[0],
            $arr_hour[1],
            $arr_hour[2],
            $arr_day[1],
            $arr_day[2],
            $arr_day[0]
        );

        return $tmstmp;
    }

    public static function weekDayOfHijriDate($hdate = '')
    {
        if (!$hdate) {
            $hdate = self::currentHijriDate();
        }

        $gdate = self::hijriToGreg($hdate);
        return self::weekDayOf($gdate);
    }

    public static function weekDayOf($gdate = '')
    {
        if (!$gdate) {
            $gdate = date('Y-m-d');
        }
        $tms_0 = self::gregToTimestamp($gdate);
        return date('w', $tms_0);
    }



    public static function hijri_current_long_date($Separator = ' ')
    {
        return self::currentHijriDate($mode = 'hdate_long', $Separator);
    }

    public static function currentHijriDate($mode = 'hdate', $Separator = ' ')
    {
        return self::to_hijri(date('Ymd'), $mode, $Separator);
    }


    public static function diff_date($madate2,$madate1,$round=true)
    {
        if(strpos($madate2, '-')===false)
        {
                $madate2 = self::add_dashes($madate2);
        }
        
        if(strpos($madate1, '-')===false)
        {
                $madate1 = self::add_dashes($madate1);
        }
        
        
        $stmp2 =   self::dateToTimestamp($madate2);
        $stmp1 =   self::dateToTimestamp($madate1);
        
        
        $result_diff = ($stmp2-$stmp1)/(24*3600);
        if($round) $result_diff = round($result_diff);
        
        return $result_diff;
    }

    /*
        public static function weekDayOfHijriDate($hdate="")
        {
                $MyDays = array("الأحد", "الأثنين", "الثلاثاء", "الإربعاء", "الخميس", 
                                "الجمعة", "السبت");
                                
                if(!$hdate) $hdate = self::currentHijriDate();
                $gdate = self::AfwDateHelper::hijriToGreg($hdate);
                $tms_0 = from_mysql_to_timestamp($gdate);
                $wday = date('w',$tms_0);
                
                return array($wday+1, $MyDays[$wday]);
        }
        */
/*
    public static function add_x_days_to_hijridate($hdate, $xdays)
    {
        if (!$hdate) {
            $hdate = self::currentHijriDate();
        }

        if (strpos($hdate, '/') === false) {
            $hdate_cs = add_slashes($hdate);
        }

        $hd_arr = explode('/', $hdate_cs);
        //echo "<br>hd_arr = ".var_export($hd_arr,true);
        $hijri_year = intval($hd_arr[0]);
        $hijri_month = intval($hd_arr[1]);
        $hijri_day = intval($hd_arr[2]);

        $hijri_day_new = $hijri_day + $xdays;
        if ($hijri_day_new <= 29 and $hijri_day_new > 0) {
            $hd_arr[2] = str_pad($hijri_day_new, 2, '0', STR_PAD_LEFT);
            return $hd_arr[0] . $hd_arr[1] . $hd_arr[2];
        }

        $gdate = self::hijriToGreg($hdate);
        $gdate = add_x_days_to_mysqldate($xdays, $gdate);

        return self::to_hijri($gdate);
    }*/

    public static function genereHijriPeriod(
        $from_date,
        $to_date,
        $we_arr = [6, 7],
        $system = 'GREG',
        $increment_days = 1,
        $increment_months = 0,
        $increment_years = 0,
        $calc_greg = true,
        $include_to_date = true
    ) {
        if (!$from_date or !$to_date) {
            AFWRoot::dd(
                "genereHijriPeriod(from_date=$from_date,to_date=$to_date,,system=$system,increment_days=$increment_days, increment_months=$increment_months, increment_years=$increment_years, calc_greg=$calc_greg, include_to_date=$include_to_date) can't be performed, from and to dates are mandatory!!"
            );
        }

        if ($system == 'HIJRI') {
            $old_from_date = $from_date;
            $old_to_date = $to_date;
            $from_date = self::hijriToGreg($from_date);
            $to_date = self::hijriToGreg($to_date);
            //die("from_date ($old_from_date) => $from_date, to_date($old_to_date) => $to_date");
        }

        $my_date = $from_date;

        $arr_hij_period = [];

        while (
            $include_to_date and $my_date <= $to_date or
            !$include_to_date and $my_date < $to_date
        ) {
            $hdate = self::to_hijri($my_date);
            if (strlen($hdate) != 8) {
                AFWRoot::dd("error : $hdate = to_hijri(my_date='$my_date')");
            }

            $wday = self::weekDayOf($my_date)+1;
            //if($wday==0) $wday = 7;
            // if(($wday==6) or ($wday==7))
            if (in_array($wday, $we_arr)) {
                $free = 'Y';
                $descr = 'نهاية الاسبوع';
            } else {
                $free = 'N';
                $descr = '';
            }

            if ($increment_days > 0) {
                $counter = substr($hdate, 4, 4);
            } elseif ($increment_months > 0) {
                $counter = substr($hdate, 0, 6);
            } elseif ($increment_years > 0) {
                $counter = substr($hdate, 0, 4);
            }

            $arr_hij_period[$my_date] = [
                'hdate' => $hdate,
                'greg' => $my_date,
                'counter' => $counter,
                'wday' => $wday,
                'free' => $free,
                'descr' => $descr,
            ];
            //die("arr_hij_period = ".var_export($arr_hij_period,true));
            $my_date = self::addPeriodToGregDate(
                $increment_days,
                $increment_months,
                $increment_years,
                $my_date
            );
        }

        // die("arr_hij_period = ".var_export($arr_hij_period,true));
        return $arr_hij_period;
    }

    public static function gregToHijri($gdate, $mode = 'hdate', $ifSeemsHijriKeepAsIs = false)
    {
        /*
                list($year,$month,$day) = self::splitGregDate($gdate);
                return self::julianToHijri(self::gregorianToJulian(intval($year), intval($month), intval($day)));
                */
        list($gdate, $gtime) = explode(' ', $gdate);
        if($gdate=="0000-00-00") 
        {
            if($mode == 'hdate') return "00000000";
            if($mode == 'hdate-dashed') return "0000-00-00";
            return "$gdate ? how in mode $mode";
        }

        return self::to_hijri($gdate, $mode, ' ', true, $ifSeemsHijriKeepAsIs);
    }

    public static function repareGorbojHijriDate($hdate, $without_dashes = true)
    {
        $hdate = trim($hdate);
        $hdate = str_replace('/', '-', $hdate);
        $hdate_arr = explode('-', $hdate);

        $is_greg = false;

        $hdate_arr[0] = intval(trim($hdate_arr[0]));
        $hdate_arr[1] = intval(trim($hdate_arr[1]));
        $hdate_arr[2] = intval(trim($hdate_arr[2]));

        // swap day and year if needed
        if ($hdate_arr[0] <= 31 and $hdate_arr[2] > 1000) {
            $tmps = $hdate_arr[2];
            $hdate_arr[2] = $hdate_arr[0];
            $hdate_arr[0] = $tmps;
        }

        // swap day and month if needed
        if ($hdate_arr[1] > 12 and $hdate_arr[2] <= 12) {
            $tmps = $hdate_arr[2];
            $hdate_arr[2] = $hdate_arr[1];
            $hdate_arr[1] = $tmps;
        }

        // 0 leftpad if needed
        $hdate_arr[1] = str_pad($hdate_arr[1], 2, '0', STR_PAD_LEFT);
        $hdate_arr[2] = str_pad($hdate_arr[2], 2, '0', STR_PAD_LEFT);

        $hdate = implode('-', $hdate_arr);

        if ($hdate_arr[0] > 1800) {
            $is_greg = true;
        }

        if ($is_greg) {
            list($hijri_year, $mm, $dd) = self::gregToHijri($hdate, 'hlist');

            $hdate = "$hijri_year-$mm-$dd";
        }

        if ($without_dashes) {
            $hdate = self::repareHijriDate($hdate);
        }

        return $hdate;
    }


    public static function repareGorbojGregDate($gdate)
    {
        $gdate = trim($gdate);
        $gdate = str_replace('/', '-', $gdate);
        $gdate_arr = explode('-', $gdate);

        $is_hijri = false;

        $gdate_arr[0] = intval(trim($gdate_arr[0]));
        $gdate_arr[1] = intval(trim($gdate_arr[1]));
        $gdate_arr[2] = intval(trim($gdate_arr[2]));

        // swap day and year if needed
        if ($gdate_arr[0] <= 31 and $gdate_arr[2] > 1000) {
            $tmps = $gdate_arr[2];
            $gdate_arr[2] = $gdate_arr[0];
            $gdate_arr[0] = $tmps;
        }

        // swap day and month if needed
        if ($gdate_arr[1] > 12 and $gdate_arr[2] <= 12) {
            $tmps = $gdate_arr[2];
            $gdate_arr[2] = $gdate_arr[1];
            $gdate_arr[1] = $tmps;
        }

        // 0 leftpad if needed
        $gdate_arr[1] = str_pad($gdate_arr[1], 2, '0', STR_PAD_LEFT);
        $gdate_arr[2] = str_pad($gdate_arr[2], 2, '0', STR_PAD_LEFT);

        $gdate = implode('-', $gdate_arr);

        if ($gdate_arr[0] < 1800) {
            $is_hijri = true;
        }

        if ($is_hijri) {
            $gdate = self::hijriToGreg($gdate);
        }

        return $gdate;
    }

    public static function repareHijriDate($hdate)
    {
        return implode('', explode('-', $hdate));
    }

    public static function hijriToGreg($hdate)
    {
        /*
                list($year,$month,$day) = self::splitHijriDate(self::repareHijriDate($hdate));
                //die("list($year,$month,$day) = self::splitHijriDate(self::repareHijriDate($hdate))");
                return self::julianToGregorian(self::hijriToJulian(intval($year), intval($month), intval($day)));
                */

        return self::hijri_to_greg($hdate);
    }

    private static function gregdate_of_first_hijri_day(
        $hijri_year,
        $hijri_month
    ) {
        global $hgreg_matrix;
        if (!$hgreg_matrix) {
            $hgreg_matrix = [];
        }
        if ($hgreg_matrix[$hijri_year . $hijri_month]) {
            return $hgreg_matrix[$hijri_year . $hijri_month];
        }

        $hijri_month_full = ($hijri_month<10) ? "0".$hijri_month : $hijri_month;

        $gdfirst = self::hijri_to_greg_from_files($hijri_year . $hijri_month_full . "01");
        if($gdfirst) 
        {
            $hgreg_matrix[$hijri_year . $hijri_month] = $gdfirst;
        }
        else
        {
            //if(count($hgreg_matrix)>0) die("gregdate_of_first_hijri_day($hijri_year, $hijri_month) : ".var_export($hgreg_matrix,true));

            $sql_greg = " select greg_date
                            from c0pag.hijra_date_base 
                            where hijri_year = $hijri_year
                                    and hijri_month = $hijri_month";
            //echo "<br>sql_greg = $sql_greg";

            //$file_dir_name = dirname(__FILE__);
            //include_once "$file_dir_name/../pag/common.php";

            $greg_date = AfwDatabase::db_recup_value($sql_greg);
            $hgreg_matrix[$hijri_year . $hijri_month] = self::add_dashes($greg_date);
        }

        return $hgreg_matrix[$hijri_year . $hijri_month];
    }


    public static function long_hijri_date($hijri_year, $mm, $dd, $TheDay, $WeekDayOn = 1, $YearOn = 1, $MonthNameOn = 1, $Separator = " ")
    {

        $MyMonths = array(
            "محرم", "صفر", "ربيع الأول", "ربيع الآخر", "جمادى الأولى", "جمادى الآخرة",
            "رجب", "شعبان", "رمضان", "شوّال", "ذو القعدة", "ذو الحجة"
        );

        $MyDays = array(
            "الأحد", "الأثنين", "الثلاثاء", "الإربعاء", "الخميس",
            "الجمعة", "السبت"
        );

        $MyDateFinal = $dd . $Separator;
        if ($MonthNameOn)
            $MyDateFinal .= $MyMonths[$mm - 1];
        else
            $MyDateFinal .= $mm;

        if ($WeekDayOn) $MyDateFinal = $MyDays[$TheDay["wday"]] . $Separator . $MyDateFinal;
        if ($YearOn) $MyDateFinal .= $Separator . $hijri_year;

        return $MyDateFinal;
    }

    public static function hdateDecompose($hdate)
    {
            $hdate_YYYY = substr($hdate, 0, 4);
            $hdate_MM = substr($hdate, 4, 2);
            $hdate_DD = substr($hdate, 6, 2);

            return [$hdate_YYYY, $hdate_MM, $hdate_DD];
    }

    public static function hdateWithSeparator($hdate,$sep="-")
    {
            $hdate_YYYY = substr($hdate, 0, 4);
            $hdate_MM = substr($hdate, 4, 2);
            $hdate_DD = substr($hdate, 6, 2);

            return $hdate_YYYY . $sep . $hdate_MM . $sep . $hdate_DD;
    }

    public static function to_hijri(
        $gdate,
        $mode = 'hdate',
        $separator = ' ',
        $emptyIsCurrent = true,
        $ifSeemsHijriKeepAsIs = false
    ) {
        /******* preparations ************/

        // remove time
        list($gdate,) = explode(" ", $gdate);

        if ($emptyIsCurrent and !$gdate) {
            $gdate = date('Y-m-d');
        }

        // without dashes to gdate
        $wd_gdate = self::remove_dashes($gdate);
        if (strlen($wd_gdate) != 8) {
            AFWRoot::dd(
                "to_hijri : gdate($gdate) after self::remove_dashes = $wd_gdate, not ok"
            );
        }

        if(($wd_gdate <= '19700101') and (!$ifSeemsHijriKeepAsIs))
        {
            AFWRoot::dd(
                "to_hijri : gdate($gdate) after self::remove_dashes = $wd_gdate is not greg known greg date"
            );
        }

        // readd dashes to gdate
        $gdate = self::add_dashes($wd_gdate);
        if (strlen($gdate) != 10) {
            AFWRoot::dd(
                "to_hijri : gdate after re-add_dashes($wd_gdate) = $gdate, not ok"
            );
        }

        /******* end of preparations ************/

        if ($mode == "hdate-dashed") 
        {
            $result = self::gregToHijri(
                $gdate,
                'hdate',
                $separator,
                $emptyIsCurrent,
                $ifSeemsHijriKeepAsIs
            );
            return self::hdateWithSeparator($result,$separator);
        }

        if ($mode == "hlist") 
        {
            $result = self::gregToHijri(
                $gdate,
                'hdate',
                $separator,
                $emptyIsCurrent,
                $ifSeemsHijriKeepAsIs
            );
            return self::hdateDecompose($result);
        }

        if ($mode == 'hdate_long') {
            $DF = explode('-', $gdate);
            $df_yyyy = $DF[0];
            $df_mm = $DF[1];
            $df_dd = $DF[2];

            $TheDay = getdate(mktime(0, 0, 0, $df_mm, $df_dd, $df_yyyy));
            // if($gdate=="2020-04-04") die("getdate(mktime(0,0,0,$df_mm,$df_dd,$df_yyyy)) = ".var_export($TheDay,true));
            list($hijri_year, $mm, $dd) = self::to_hijri($gdate, 'hlist');
            return self::long_hijri_date(
                $hijri_year,
                $mm,
                $dd,
                $TheDay,
                1,
                1,
                1,
                $separator
            );
        }

        if(($wd_gdate <= '15100101') and $ifSeemsHijriKeepAsIs)
        {
            return $wd_gdate;
        }

        $result = AfwSession::getVar("hijri-of-$gdate");
        if ($result) return $result;

        // try to use cache greg_to_hijri files
        list($greg_year,) = explode("-", $gdate);
        $hg_cache_file = dirname(__FILE__) . "/../../../external/chsys/dates/greg_$greg_year"."_to_hijri.php";
        $greg_to_hijri_arr = include($hg_cache_file);

        if ($greg_to_hijri_arr) {
            $result = $greg_to_hijri_arr[$gdate];
            if ($result) {
                AfwSession::setVar("hijri-of-$gdate", $result);
                return $result;
            }
        }
        //else die("please check $hg_cache_file");

        $sql_hij = "select hijri_year as HY,
                        hijri_month as HM,
                        greg_date as GD
                        from c0pag.hijra_date_base
                where greg_date = (select max(greg_date) from c0pag.hijra_date_base where greg_date <= '$wd_gdate')";

        $row_hijri = AfwDatabase::db_recup_row($sql_hij);

        $hijri_year = $row_hijri['HY'];
        $hijri_month = $row_hijri['HM'];
        $greg_date = $row_hijri['GD'];
        // die("row_hijri for $wd_gdate = ".var_export($row_hijri,true));
        if (strpos($greg_date, '-') === false) {
            $greg_date = self::add_dashes($greg_date);
        }

        //die("$sql_hij => $greg_date");

        $mm = str_pad($hijri_month, 2, '0', STR_PAD_LEFT);

        $hijri_day = diff_date($gdate, $greg_date) + 1;

        $dd = str_pad($hijri_day, 2, '0', STR_PAD_LEFT);

        //if(intval($dd)>30) die("to_hijri : $hijri_day = diff_date($gdate,$greg_date) + 1 --> padded $dd ");
        AfwSession::setVar("hijri-of-$gdate", "$hijri_year".$mm."$dd");

        $return = AfwSession::getVar("hijri-of-$gdate");
        if (($mode == 'hdate') and (strlen($return) != 8)) {
            AFWRoot::dd(
                "row_hijri for $wd_gdate = " .
                    var_export($row_hijri, true) .
                    " => $hijri_day = diff_date($gdate,$greg_date) + 1 => return=$return"
            );
        }

        if ($return) {
            return $return;
        } else {
            return '?????';
        }
    }

    private static function hijri_to_greg_from_files($original_hdate, $hijri_year="")
    {
        // try to use cache hijri_to_greg files
        if(!$hijri_year) $hijri_year = substr($original_hdate,0,4);
        $hijri_to_greg_file = dirname(__FILE__) . "/../../../external/chsys/dates/hijri_".$hijri_year."_to_greg.php";
        $hijri_to_greg_arr = include($hijri_to_greg_file);
        /*
        if(($original_hdate=="14350101") and (!$hijri_to_greg_arr[$original_hdate]))
        {
            die("$original_hdate not found in hijri_to_greg_file=$hijri_to_greg_file in hijri_to_greg_arr=".var_export($hijri_to_greg_arr,true));
        }*/

        $hdate = self::remove_dashes($original_hdate);
        if(!$hijri_to_greg_arr[$hdate]) AfwSession::hzmLog("failed to find hijri_to_greg[$hdate] ($original_hdate) in file $hijri_to_greg_file ","fail"); // ."hijri_to_greg = ".var_export($hijri_to_greg_arr,true)
        return $hijri_to_greg_arr[$hdate];
    }

    private static function hijri_to_greg($hdate)
    {
        $original_hdate = $hdate;
        if (strpos($hdate, '-') !== false) {
            $hdate = self::remove_dashes($hdate);
        }

        if (strpos($hdate, '/') === false) {
            $hdate = add_slashes($hdate);
        }

        $hd_arr = explode('/', $hdate);

        $hdate = $hd_arr[0].$hd_arr[1].$hd_arr[2];

        $result = AfwSession::getVar("greg-of-$hdate");
        if ($result) return $result;
        

        $hijri_year = intval($hd_arr[0]);
        $hijri_month = intval($hd_arr[1]);
        $hijri_day = intval($hd_arr[2]);

        $result = self::hijri_to_greg_from_files($original_hdate, $hijri_year);
        
        if ($result) {
            AfwSession::setVar("greg-of-$hdate", $result);
            return $result;
        }

        $first_gregdate = self::gregdate_of_first_hijri_day(
            $hijri_year,
            $hijri_month
        );

        $greg_date = self::addXDaysToGregDate($hijri_day - 1, $first_gregdate);

        if ($greg_date) {
            AfwSession::setVar("greg-of-$hdate", $greg_date);
            return $greg_date;
        }

        return $greg_date;
    }

    public static function shiftHijriDate($hdate = '', $offset = 1)
    {
        if (!$hdate) {
            $hdate = self::currentHijriDate();
        }
        $gdate = self::hijriToGreg($hdate);
        $gdate = self::shiftGregDate($gdate, $offset);

        return self::gregToHijri($gdate);
    }

    public static function shiftPeriodHijriDate(
        $hdate = '',
        $days,
        $months,
        $years
    ) {
        if (!$hdate) {
            $hdate = self::currentHijriDate();
        }
        $gdate = self::hijriToGreg($hdate);
        $gdate = self::shiftPeriodGregDate($gdate, $days, $months, $years);

        return self::gregToHijri($gdate);
    }

    public static function shiftGregDate($gdate, $offset)
    {
        return self::shiftPeriodGregDate($gdate, $offset, 0, 0);
    }

    public static function shiftPeriodGregDate($gdate, $days, $months, $years)
    {
        if (!$gdate) {
            $gdate = date('Y-m-d');
        }
        $gdate_tab = explode('-', $gdate);
        $gdate_shifted = date(
            'Y-m-d',
            mktime(
                0,
                0,
                0,
                $gdate_tab[1] + $months,
                $gdate_tab[2] + $days,
                $gdate_tab[0] + $years
            )
        );

        return $gdate_shifted;
    }

    public static function hijriDateTimeDiff(
        $hdate2 = '',
        $time2 = '',
        $hdate1 = '',
        $time1 = '',
        $round = true
    ) {
        if (!$hdate2) {
            $gdate2 = date('Y-m-d');
        } else {
            $gdate2 = self::hijriToGreg($hdate2);
        }

        if (!$hdate1) {
            $gdate1 = date('Y-m-d');
        } else {
            $gdate1 = self::hijriToGreg($hdate1);
        }

        if (!$time2) {
            $time2 = date('H:i:s');
        }
        if (!$time1) {
            $time1 = date('H:i:s');
        }

        $return = self::gregDateDiff(
            $gdate2 . ' ' . $time2,
            $gdate1 . ' ' . $time1,
            $round
        );

        // die("$return = [$hdate2 => $gdate2] - [$hdate1 => $gdate1]");

        return $return;
    }

    public static function hijriDateDiff($hdate2, $hdate1, $round = true)
    {
        $gdate1 = self::hijriToGreg($hdate1);
        $gdate2 = self::hijriToGreg($hdate2);

        $return = self::gregDateDiff($gdate2, $gdate1, $round);

        // die("$return = [$hdate2 => $gdate2] - [$hdate1 => $gdate1]");

        return $return;
    }

    public static function getHijriDateTimeFromGregDateTime(
        $gdatetime,
        $seconds = true
    ) {
        list($gdate, $gtime) = explode(' ', $gdatetime);

        $hdate = self::to_hijri($gdate);
        if (strlen($hdate) != 8) {
            AFWRoot::dd(
                "list($gdate, $gtime) = explode(' ',$gdatetime) => $hdate = to_hijri($gdate)"
            );
        }
        if (!$seconds) {
            $gtime = substr($gtime, 0, 5);
        }

        return [$hdate, $gtime];
    }

    public static function timeDiffInSeconds($gdate2, $gdate1)
    {
        $stmp2 = self::gregToTimestamp($gdate2);
        $stmp1 = self::gregToTimestamp($gdate1);

        return ($stmp2 - $stmp1);
    }

    public static function gregDateDiff($gdate2, $gdate1, $round = true)
    {
        $result_diff = self::timeDiffInSeconds($gdate2, $gdate1);
        $result_diff / (24 * 3600);
        if ($round) {
            $result_diff = round($result_diff);
        }

        return $result_diff;
    }

    /*****************     date and time functions    *************************/

    public static function addDatetimeToGregDatetime(
        $gdate_time = '',
        $years = 0,
        $months = 0,
        $days = 0,
        $hours = 0,
        $minutes = 0,
        $seconds = 0
    ) {
        if (!$gdate_time) {
            $gdate_time = date('Y-m-d H:i:s');
        }

        $arr_dat = explode(' ', $gdate_time);
        $arr_day = explode('-', $arr_dat[0]);
        $arr_hour = explode(':', $arr_dat[1]);

        $tmstmp = mktime(
            $arr_hour[0] + $hours,
            $arr_hour[1] + $minutes,
            $arr_hour[2] + $seconds,
            $arr_day[1] + $months,
            $arr_day[2] + $days,
            $arr_day[0] + $years
        );

        return date('Y-m-d H:i:s', $tmstmp);
    }

    /*****************    time functions    *************************/

    public static function getSplittedTime($time_to_add)
    {
        if (is_array($time_to_add)) {
            return $time_to_add;
        }

        if (is_numeric($time_to_add)) {
            $hh_to_add = floor($time_to_add);
            $ii_to_add = floor(($time_to_add - $hh_to_add) * 60);
            $ss_to_add = round(
                (($time_to_add - $hh_to_add) * 60 - $ii_to_add) * 60
            );
        } else {
            list($hh_to_add, $ii_to_add, $ss_to_add) = explode(
                ':',
                $time_to_add
            );
            $hh_to_add = intval($hh_to_add);
            $ii_to_add = intval($ii_to_add);
            $ss_to_add = intval($ss_to_add);
        }

        return [$hh_to_add, $ii_to_add, $ss_to_add];
    }

    /**
     *
     * add time to time
     *
     */

    public static function addTimeToDayTime(
        $day,
        $time,
        $time_to_add,
        $seconds = false,
        $sign = 1
    ) {
        list($hh_to_add, $ii_to_add, $ss_to_add) = self::getSplittedTime(
            $time_to_add
        );

        if (strlen($time) == 5) {
            $time .= ':00';
        }
        $to_day = date('Y-m-d');
        $date_time_day = $to_day . ' ' . $time;

        $new_date_time = self::addDatetimeToGregDatetime(
            $date_time_day,
            $years = 0,
            $months = 0,
            $days = 0,
            $sign * $hh_to_add,
            $sign * $ii_to_add,
            $sign * $ss_to_add
        );

        list($new_date, $new_time) = explode(' ', $new_date_time);

        if (!$seconds) {
            $new_time = substr($new_time, 0, 5);
        }

        $new_day = $day + diff_date($new_date, $to_day);

        return [$new_day, $new_time];
    }

    public static function getPeriodDefinitionByName($period_name, $margin = 0)
    {
        if ($period_name == 'now') {
            return [['today', 0, 0], ['today', 30, 0]];
        }
        if ($period_name == 'this_week') {
            return [['today', 0, 0], ['today', 6 + $margin, '-w']];
        }
        if ($period_name == 'nextweek') {
            return [['today', 7, '-w'], ['today', 13 + $margin, '-w']];
        }
        if ($period_name == 'after2w') {
            return [['today', 14, '-w'], ['today', 20 + $margin, '-w']];
        }
        if ($period_name == 'nextmonth') {
            return [['today', 21, '-w'], ['today', 81 + $margin, '-w']];
        }

        throw new RuntimeException(
            "AfwDateHelper::getPeriodDefinitionByName:  period name : '$period_name' unknown"
        );
    }

    public static function dateDefinitionToGDate($date_definition)
    {
        list(
            $date_definition_start,
            $date_definition_offset,
            $date_definition_weekday_offset,
        ) = $date_definition;
        if ($date_definition_start == 'today') {
            $gdate_start = date('Y-m-d');
        } else {
            throw new RuntimeException(
                "AfwDateHelper::dateDefinitionToGDate:  date_definition_start : '$date_definition_start' unknown"
            );
        }

        $w = self::weekDayOf($gdate_start);

        $offset = $date_definition_offset;
        if ($date_definition_weekday_offset === '+w') {
            $offset += $w;
        } elseif ($date_definition_weekday_offset === '-w') {
            $offset -= $w;
        } elseif ($date_definition_weekday_offset) {
            throw new RuntimeException(
                "AfwDateHelper::dateDefinitionToGDate:  date_definition_weekday_offset : '$date_definition_weekday_offset' unknown"
            );
        }

        return self::shiftGregDate($gdate_start, $offset);
    }

    public static function getTimingInfosByDefinition($period_definition)
    {
        list($gdate_from_definition, $gdate_to_definition) = $period_definition;

        $gdate_from = self::dateDefinitionToGDate($gdate_from_definition);
        $gdate_to = self::dateDefinitionToGDate($gdate_to_definition);

        $hdate_from = self::gregToHijri($gdate_from);
        $hdate_to = self::gregToHijri($gdate_to);

        return [
            'gfrom' => $gdate_from,
            'gto' => $gdate_to,
            'hfrom' => $hdate_from,
            'hto' => $hdate_to,
        ];
    }

    public static function getTimingInfosByName($period_name, $margin = 0)
    {
        return self::getTimingInfosByDefinition(
            self::getPeriodDefinitionByName($period_name, $margin)
        );
    }

    public static function add_slashes($madate)
    {
        $madate_YYYY = substr($madate, 0, 4);
        $madate_MM = substr($madate, 4, 2);
        $madate_DD = substr($madate, 6, 2);

        return "$madate_YYYY/$madate_MM/$madate_DD";
    }

    public static function add_dashes($madate)
    {
        $madate_YYYY = substr($madate, 0, 4);
        $madate_MM = substr($madate, 4, 2);
        $madate_DD = substr($madate, 6, 2);

        return "$madate_YYYY-$madate_MM-$madate_DD";
    }

    public static function remove_dashes($gdate)
    {
        $arr_gdate = explode('-', $gdate);
        $madate_YYYY = $arr_gdate[0];
        $madate_MM = $arr_gdate[1];
        $madate_DD = $arr_gdate[2];

        return $madate_YYYY . $madate_MM . $madate_DD;
    }

    public static function gdateIsWeekend($gdate, $we_arr = [6, 7])
    {
        $day_of_week = self::weekDayOf($gdate)+1;       

        return in_array($day_of_week,$we_arr);
    }

    public static function calculateTimeInstersection($start_date_time, $end_date_time, $time_frame=['08:00:00','14:00:00'], $we_arr = [6, 7], $leave_gdates=array())
    {
        list($start_date, $start_time) = explode(" ",$start_date_time);
        list($end_date, $end_time) = explode(" ",$end_date_time);
        $curr_date = $start_date;
        $total_time_min = 0;
        while ($curr_date <= $end_date) 
        {
            if(in_array($curr_date,$leave_gdates) or self::gdateIsWeekend($curr_date, $we_arr)) 
            {
                // we ignore this day as it is we or leave
            }
            else
            {
                if($curr_date==$start_date)
                {
                    $time_start1 = $start_time;
                    $time_end1 = "23:59:59";
                }
                elseif($curr_date==$end_date)
                {
                    $time_start1 = "00:00:00";
                    $time_end1 = $end_time;
                }
                else
                {
                    $time_start1 = "00:00:00";
                    $time_end1 = "23:59:59";
                }
                list($time_start2, $time_end2) = $time_frame;

                $total_time_min += round(self::timeIntersectionInSeconds($time_start1, $time_end1, $time_start2, $time_end2)/60);
            }
            $curr_date = self::shiftGregDate($curr_date,1);
        }


        return $total_time_min;
    }

    public static function timeIntersectionInSeconds($time_start1, $time_end1, $time_start2, $time_end2)
    {
        $time_start = ($time_start1 > $time_start2) ? $time_start1 : $time_start2;
        $time_end = ($time_end1 < $time_end2) ? $time_end1 : $time_end2;

        $today = date("Y-m-d");
        $result = self::timeDiffInSeconds($today." ".$time_start, $today." ".$time_end);
        
        return $result;
    }

    public static function getPrayerTimeList()
    {
        $time_arr = array();

        $time_arr["03:01"] = "بعد صلاة الفجر";
        $time_arr["04:01"] = "بعد صلاة الفجر حصة 2";
        $time_arr["12:01"] = "بعد صلاة الظهر";
        $time_arr["13:01"] = "بعد صلاة الظهر حصة 2";
        $time_arr["15:01"] = "بعد صلاة العصر";
        $time_arr["16:01"] = "بعد صلاة العصر حصة 2";
        $time_arr["18:01"] = "بعد صلاة المغرب";
        $time_arr["19:01"] = "بعد صلاة المغرب حصة 2";
        $time_arr["20:01"] = "بعد صلاة العشاء";
        $time_arr["21:01"] = "بعد صلاة العشاء حصة 2";


        return $time_arr;
    }

    public static function getAfterPrayerTimeList()
    {
        $time_arr = array();

        $time_arr["04:01"] = "بعد صلاة الفجر بساعة";
        $time_arr["05:01"] = "بعد صلاة الفجر حصة 2 بساعة";
        $time_arr["13:01"] = "بعد صلاة الظهر بساعة";
        $time_arr["14:01"] = "بعد صلاة الظهر حصة 2 بساعة";
        $time_arr["16:01"] = "بعد صلاة العصر بساعة";
        $time_arr["17:01"] = "بعد صلاة العصر حصة 2 بساعة";
        $time_arr["19:01"] = "بعد صلاة المغرب بساعة";
        $time_arr["20:01"] = "بعد صلاة المغرب حصة 2 بساعة";
        $time_arr["21:01"] = "بعد صلاة العشاء بساعة";
        $time_arr["22:01"] = "بعد صلاة العشاء حصة 2 بساعة";


        return $time_arr;
    }

    public static function getTimeInterval($medium=8,$interval=1,$increment=15)
    {
        return self::getTimeArray($start=$medium-$interval,$increment,$end=$medium+$interval);
    }

    public static function getTimeArray($start=7,$increment=15,$end=21)
    {
        $hh = $start;
        $mm = 0;
        
        $time_arr = array();
        
        while($hh<$end)
        {
            $time = "";
            if($hh<10) $time .= "0";
            $time .= $hh.":";
            if($mm<10) $time .= "0";
            $time .= $mm;
            
            $time_arr[$time] = $time;
            
            $mm += $increment;
            
            if($mm>=60) 
            {
                    $mm = $mm - 60;
                    $hh++;
            }
        }
        
        return $time_arr;
    }


    public static function displayTime($value, $structure, $decode_format, $object=null)
    {

        
        if ($decode_format == 'CLASS') {
            $helpClass = $structure["ANSWER_CLASS"];
            $helpMethod = $structure["ANSWER_METHOD"];

            $answer_list = $helpClass::$helpMethod();
            $hr = $value;
            return $answer_list[$hr];
        } 
        elseif (($decode_format == 'OBJECT') and $object) {
            $helpMethod = $structure["ANSWER_METHOD"];
            $answer_list = $object->$helpMethod();
            $hr = $value;
            return $answer_list[$hr];
        } 
        elseif ($decode_format == 'HEURE') {
            $hr = $value;
            $hr = explode(':', $hr);

            $return = $hr[0] . 'h' . $hr[1];
        } 
        elseif($decode_format == 'ARABIC-TIME') 
        {
            $hr = $value;
            $hr = explode(':', $hr);

            $return =
                'س' . $hr[0] . ' و' . $hr[1] . 'دق';
            if ($hr[2]) {
                $return .= ' و' . $hr[2] . 'ث';
            }
        } 
        else 
        {
            $return = $value;
        }


        return $return;
    }

    public static function justDecodeValue($value, $structure)
    {
        if ($structure['TYPE'] == 'GDAT') {
            return self::displayGDate($value);
        } elseif ($structure['TYPE'] == 'DATE') {
            return self::displayDate($value);
        } else {
            return $value;
        }
    }

    public static function formatDateForDB($value)
    {
        $value_arr = explode('-', trim($value));
        //die("date value [$value] exploded to ".var_export($value_arr,true)."count = ".count($value_arr));
        if (count($value_arr) == 3) {
            $return = $value_arr[0] . $value_arr[1] . $value_arr[2];
            //die("date value [$value] exploded to ".var_export($value_arr,true)."count = ".count($value_arr)."value so = [$value]");
        } else {
            $return = $value;
        }

        return $return;
    }

    public static function formatGDateForDB($value)
    {
        $value_arr = explode('/', trim($value));
        if (count($value_arr) == 3) {
            $return = $value_arr[2] . '-' . $value_arr[0] . '-' . $value_arr[1];
        } else {
            $return = $value;
        }

        return $return;
    }

    public static function displayGDate($val)
    {
        list($val,) = explode(" ",$val);
        
        if (strlen($val) == 10) {
            list($yyyy, $mm, $dd) = explode('-', $val);
            return "$mm/$dd/$yyyy";
        }

        return $val;
    }

    public static function displayGDateLong($val)
    {
        $date_en = substr($val,0,10);
        $date_en = explode('-', $date_en);

        $tmstmp = mktime(
            0,
            0,
            0,
            $date_en[1],
            $date_en[2],
            $date_en[0]
        );

        // Monday 8th of August 2005
        return self::nameDayTranslate(date('l',$tmstmp)).' '.date('j',$tmstmp).' '.self::nameMonthTranslate(date('F',$tmstmp)).' '.date('Y',$tmstmp);
    }


    public static function displayDate($val)
    {
        if (strlen($val) == 8) {
            $yyyy = substr($val, 0, 4);
            $mm = substr($val, 4, 2);
            $dd = substr($val, 6, 2);
            return "$yyyy-$mm-$dd";
        }

        return $val;
    }

    public static function addPeriodToGregDate($nb_days, $nb_months=0, $nb_years=0, $from_date='')
    {
        if(!$from_date) $from_date = date('Y-m-d');
        //echo "<br>from_date = $from_date";
        $from_tab = explode('-',$from_date);
        //echo "<br>from_tab = ".var_export($from_tab,true);
        
        
        $to_date = date("Y-m-d",mktime(0,0,0,intval($from_tab[1])+$nb_months,intval($from_tab[2])+$nb_days,intval($from_tab[0])+$nb_years));

        //echo "<br>from_date + $nb_days = $to_date";

        return($to_date);
    }

    public static function addXDaysToGregDate($nb_days,$from_date='')
    {
        return self::addPeriodToGregDate($nb_days,0,0,$from_date);
    }

}
