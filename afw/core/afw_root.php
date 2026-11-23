<?php

// use PhpOffice\PhpSpreadsheet\RichText\Run;

class AFWRoot
{
        /**
         * @param int $boolean   (0 or 1)
         */
        public static function name_of_boolean($boolean, $lang = 'ar')
        {
                if (($lang != 'ar') and ($lang != 'fr')) $lang = 'en';
                return self::boolean()[$lang][$boolean];
        }

        public static function list_of_boolean($lang = null)
        {
                if (!$lang)
                        $lang = AfwLanguageHelper::getGlobalLanguage();
                return self::boolean()[$lang];
        }

        public static function boolean()
        {
                $arr_list_of_boolean = array();

                $arr_list_of_boolean['code'][0] = 'N';
                $arr_list_of_boolean['ar'][0] = 'لا';
                $arr_list_of_boolean['en'][0] = 'no';
                $arr_list_of_boolean['fr'][0] = 'non';

                $arr_list_of_boolean['code'][1] = 'Y';
                $arr_list_of_boolean['ar'][1] = 'نعم';
                $arr_list_of_boolean['en'][1] = 'yes';
                $arr_list_of_boolean['fr'][1] = 'oui';

                return $arr_list_of_boolean;
        }

        public function __toString()
        {
                return "afw-root-imp";
        }


        public function getMyModule()
        {
                return "NOT-OVERRIDDEN";
        }

        public function getMyParentModule()
        {
                return "PM-NOT-OVERRIDDEN";
        }

        public static function lookIfInfiniteLoop($maxAuthorized = 20000, $case = "all", $tracePickerModulo=77, $debugg_loop=true)
        {
                global $onces;
                if (!$onces) $onces = array();
                if (!$onces[$case]) $onces[$case] = 1;
                else $onces[$case]++;

                $debugg = false;

                $maxBeforePick = round(0.7*$maxAuthorized);
                if ($onces[$case] > $maxBeforePick) {
                        $debugg = $debugg_loop;
                }
                if ($onces[$case] > $maxAuthorized) {
                        throw new AfwRuntimeException("called $maxAuthorized times seems like infinite loop for case '$case'");
                }

                if($debugg_loop) {
                        if($tracePickerModulo) $pick_modulo = $onces[$case] % $tracePickerModulo;
                        else $pick_modulo = 1;                        
                        $debugg = ($pick_modulo==1);
                }

                if($debugg) {

                        if($onces[$case]==1) {
                                AFWDebugg::initialiser('', "case-$case.log");
                                AFWDebugg::setEnabled(true);
                        }

                        $log = "case : " . $onces[$case];
                        AFWDebugg::log($log, false, true, true);

                        $trace_arr = AfwRunHelper::show_trace_as_string($max_trace = 50, $returnArray = true);
                        foreach($trace_arr as $trace_log) {
                              AFWDebugg::log($trace_log, false, true, false);
                        }
                }
        }
}
