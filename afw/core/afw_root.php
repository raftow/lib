<?php

// use PhpOffice\PhpSpreadsheet\RichText\Run;

class AFWRoot
{

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

        public static function lookIfInfiniteLoop($maxAuthorized = 20000, $case = "all")
        {
                global $onces;
                if (!$onces) $onces = array();
                if (!$onces[$case]) $onces[$case] = 1;
                else $onces[$case]++;
                if ($onces[$case] > $maxAuthorized) {
                        AfwRunHelper::safeDie("called $maxAuthorized times seems like infinite loop for case '$case'");
                }
        }

}
