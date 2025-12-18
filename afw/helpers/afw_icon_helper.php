<?php

class AfwIconHelper 
{

        private static $awesome_icon_map = array(
                "service" => "f1b6",
                "stat statistics" => "f683",
                "report" => "f201",
                "chart" => "f080",
                "gear" => "f013",
                "transformation" => "f013",
                "job" => "f085",
                "task" => "f085",
                "work" => "f085",
                "servicestack" => "brand-f3ec",
                "server"=>"f233",
                "shapes" => "f61f",
                "endpoint" => "f090",
                "envelope" => "f0e0",
                "database" => "f1c0",
                "teeth" => "f62e",
                "teethOpen" => "f62f",
                "telegram" => "brand-f2c6",
                "telegramPlane" => "brand-f3fe",
                "temperatureHigh" => "f769",
                "temperatureLow" => "f76b",
                "tencentWeibo" => "brand-f1d5",
                "tenge" => "f7d7",
                "terminal" => "f120",
                "textHeight" => "f034",
                "textWidth" => "f035",
                "th" => "f00a",
                "large" => "f009",
                "list" => "f00b",
                "theRedYeti" => "brand-f69d",
                "theaterMasks" => "f630",
                "themeco" => "brand-f5c6",
                "themeisle" => "brand-f2b2",
                "thermometer" => "f491",
                "thermometerEmpty" => "f2cb",
                "thermometerFull" => "f2c7",
                "thermometerHalf" => "f2c9",
                "thermometerQuarter" => "f2ca",
                "thermometerThreeQuarters" => "f2c8",
                "thinkPeaks" => "brand-f731",
                "thumbsDown" => "f165",
                "solidThumbsDown" => "f165",
                "thumbsUp" => "f164",
                "solidThumbsUp" => "f164",
                "thumbtack" => "f08d",
                "ticketAlt" => "f3ff",
                "times" => "f00d",
                "timesCircle" => "f057",
                "solidTimesCircle" => "f057",
                "tint" => "f043",
                "tintSlash" => "f5c7",
                "tired" => "f5c8",
                "solidTired" => "f5c8",
                "toggleOff" => "f204",
                "toggleOn" => "f205",
                "toilet" => "f7d8",
                "toiletPaper" => "f71e",
                "toolbox" => "f552",
                "tools" => "f7d9",
                "tooth" => "f5c9",
                "torah" => "f6a0",
                "toriiGate" => "f6a1",
                "tractor" => "f722",
                "tradeFederation" => "brand-f513",
                "trademark" => "f25c",
                "trafficLight" => "f637",
                "train" => "f238",
                "tram" => "f7da",
                "transgender" => "f224",
                "transgenderAlt" => "f225",
                "trash" => "f1f8",
                "trashAlt" => "f2ed",
                "solidTrashAlt" => "f2ed",
                "trashRestore" => "f829",
                "trashRestoreAlt" => "f82a",
                "tree" => "f1bb",
                "trello" => "brand-f181",
                "tripadvisor" => "brand-f262",
                "trophy" => "f091",
                "truck" => "f0d1",
                "truckLoading" => "f4de",
                "truckMonster" => "f63b",
                "truckMoving" => "f4df",
                "truckPickup" => "f63c",
                "tshirt" => "f553",
                "tty" => "f1e4",
                "tumblr" => "brand-f173",
                "tumblrSquare" => "brand-f174",
                "tv" => "f26c",
                "twitch" => "brand-f1e8",
                "twitter" => "brand-f099",
                "twitterSquare" => "brand-f081",
                "typo3" => "brand-f42b",
                "uber" => "brand-f402",
                "ubuntu" => "brand-f7df",
                "uikit" => "brand-f403",
                "umbrella" => "f0e9",
                "umbrellaBeach" => "f5ca",
                "underline" => "f0cd",
                "undo" => "f0e2",
                "undoAlt" => "f2ea",
                "uniregistry" => "brand-f404",
                "universalAccess" => "f29a",
                "university" => "f19c",
                "unlink" => "f127",
                "unlock" => "f09c",
                "unlockAlt" => "f13e",
                "untappd" => "brand-f405",
                "upload" => "f093",
                "ups" => "brand-f7e0",
                "usb" => "brand-f287",
                "user" => "f007",
                "solidUser" => "f007",
                "contact" => "f007",
                "person" => "f007",
                "userAlt" => "f406",
                "userAltSlash" => "f4fa",
                "userAstronaut" => "f4fb",
                "userCheck" => "f4fc",
                "userCircle" => "f2bd",
                "solidUserCircle" => "f2bd",
                "userClock" => "f4fd",
                "userCog" => "f4fe",
                "userEdit" => "f4ff",
                "userFriends" => "f500",
                "userGraduate" => "f501",
                "userInjured" => "f728",
                "userLock" => "f502",
                "userMd" => "f0f0",
                "userMinus" => "f503",
                "userNinja" => "f504",
                "userNurse" => "f82f",
                "userPlus" => "f234",
                "userSecret" => "f21b",
                "userShield" => "f505",
                "userSlash" => "f506",
                "userTag" => "f507",
                "userTie" => "f508",
                "userTimes" => "f235",
                "users" => "f0c0",                
                "usersCog" => "f509",
                "link" => "f0c1",
                "relation" => "f0c1",
                "attach" => "f0c1",
                "url" => "f0c1",
                "endpoint" => "f0c1",
                "usps" => "brand-f7e1",
                "ussunnah" => "brand-f407",
                "utensilSpoon" => "f2e5",
                "utensils" => "f2e7",
                "vaadin" => "brand-f408",
                "vectorSquare" => "f5cb",
                "venus" => "f221",
                "venusDouble" => "f226",
                "venusMars" => "f228",
                "viacoin" => "brand-f237",
                "viadeo" => "brand-f2a9",
                "viadeoSquare" => "brand-f2aa",
                "vial" => "f492",
                "vials" => "f493",
                "viber" => "brand-f409",
                "video" => "f03d",
                "videoSlash" => "f4e2",
                "vihara" => "f6a7",
                "vimeo" => "brand-f40a",
                "vimeoSquare" => "brand-f194",
                "vimeoV" => "brand-f27d",
                "vine" => "brand-f1ca",
                "vk" => "brand-f189",
                "vnv" => "brand-f40b",
                "voicemail" => "f897",
                "volleyballBall" => "f45f",
                "volumeDown" => "f027",
                "volumeMute" => "f6a9",
                "volumeOff" => "f026",
                "volumeUp" => "f028",
                "voteYea" => "f772",
                "vrCardboard" => "f729",
                "vuejs" => "brand-f41f",
                "walking" => "f554",
                "wallet" => "f555",
                "warehouse" => "f494",
                "water" => "f773",
                "waveSquare" => "f83e",
                "waze" => "brand-f83f",
                "weebly" => "brand-f5cc",
                "weibo" => "brand-f18a",
                "weight" => "f496",
                "weightHanging" => "f5cd",
                "weixin" => "brand-f1d7",
                "whatsapp" => "brand-f232",
                "whatsappSquare" => "brand-f40c",
                "wheelchair" => "f193",
                "whmcs" => "brand-f40d",
                "wifi" => "f1eb",
                "wikipediaW" => "brand-f266",
                "wind" => "f72e",
                "windowClose" => "f410",
                "solidWindowClose" => "f410",
                "windowMaximize" => "f2d0",
                "solidWindowMaximize" => "f2d0",
                "windowMinimize" => "f2d1",
                "solidWindowMinimize" => "f2d1",
                "windowRestore" => "f2d2",
                "solidWindowRestore" => "f2d2",
                "windows" => "brand-f17a",
                "wineBottle" => "f72f",
                "wineGlass" => "f4e3",
                "wineGlassAlt" => "f5ce",
                "wix" => "brand-f5cf",
                "wizardsOfTheCoast" => "brand-f730",
                "wolfPackBattalion" => "brand-f514",
                "wonSign" => "f159",
                "wordpress" => "brand-f19a",
                "wordpressSimple" => "brand-f411",
                "wpbeginner" => "brand-f297",
                "wpexplorer" => "brand-f2de",
                "wpforms" => "brand-f298",
                "wpressr" => "brand-f3e4",
                "wrench" => "f0ad",
                "xRay" => "f497",
                "xbox" => "brand-f412",
                "xing" => "brand-f168",
                "xingSquare" => "brand-f169",
                "yCombinator" => "brand-f23b",
                "yahoo" => "brand-f19e",
                "yammer" => "brand-f840",
                "yandex" => "brand-f413",
                "yandexInternational" => "brand-f414",
                "yarn" => "brand-f7e3",
                "yelp" => "brand-f1e9",
                "yenSign" => "f157",
                "yinYang" => "f6ad",
                "yoast" => "brand-f2b1",
                "youtube" => "brand-f167",
                "youtubeSquare" => "brand-f431",
                "zhihu" => "brand-f63f",

        );
        
        /*
        public static function proposeIcon($strings)
        {
                $icon = "fa fa-file";

                foreach ($strings as $str) {
                        $str_lower = strtolower($str);
                        foreach (self::$awesome_icon_map as $key => $mapped_icon) {
                                if ((strpos($str_lower, $key) !== false) or
                                    (strpos($key, $str_lower) !== false))
                                {  
                                        return $mapped_icon;
                                }
                        }
                }

                return $icon;
        }*/


        public static function proposeIcons($strings, $returnKeys=false, $debugg=false)
        {
                
                $icons = []; 

                foreach ($strings as $str) {
                        $str_parts = explode(" ", $str);
                        foreach($str_parts as $part) {
                                $str_lower = strtolower($part);
                                $str_synonyms_arr = PagSynonymHelper::getSynonyms($str_lower);
                                foreach ($str_synonyms_arr as $str_synonym) {
                                        foreach (self::$awesome_icon_map as $key => $mapped_icon) 
                                        {
                                                $key = strtolower($key);
                                                if (((strlen($key)>4) and (strpos($str_synonym, $key) !== false)) or
                                                    ((strlen($str_synonym)>4) and (strpos($key, $str_synonym) !== false)) or
                                                    ($str_synonym==$key) or 
                                                    ($str_synonym==$key."s") or 
                                                    ($str_synonym."s"==$key) 
                                                    )
                                                {  
                                                        if($returnKeys) $icons[] = "f:$key (s:$str_synonym)";
                                                        else $icons[] = $mapped_icon;
                                                }
                                                elseif($returnKeys and $debugg)
                                                {
                                                      $icons[] = "$str_synonym vs $key rejected";  
                                                }
                                        }
                                }
                                
                        }                        
                }

                // $icons[] = "fa fa-file";

                return $icons; 
        }


        public static function getIconHtml($icon_class)
        {
                return "<i class='" . htmlspecialchars($icon_class) . "'></i>";
        }

        public static function getIconHtmlFromStrings($strings)
        {
                $icon_class_arr = self::proposeIcons($strings);
                $return = "";
                foreach($icon_class_arr as $icon_class) $return .= "\n".self::getIconHtml($icon_class);
        }

        public static function findAwesomeWord($word, $brand=true, $partial=false)
        {
                // exact search
                if (array_key_exists($word, self::$awesome_icon_map)) {
                        $return = self::$awesome_icon_map[$word];

                        if(AfwStringHelper::stringStartsWith($return, "brand-")) {
                                if ($brand) {
                                        return substr($return, 6);
                                }
                                else {
                                        if(!$partial) return null;
                                }
                        }
                        else {
                                return $return;
                        }
                }

                // partial search
                if ($partial) {
                        foreach (self::$awesome_icon_map as $key => $icon_code) {
                                if (AfwStringHelper::stringContain($key, $word) or AfwStringHelper::stringContain($word, $key)) {
                                        $return = $icon_code;

                                        if(AfwStringHelper::stringStartsWith($return, "brand-")) {
                                                if ($brand) {
                                                        return substr($return, 6);
                                                }
                                                else {
                                                        return null;
                                                }
                                        }
                                        else {
                                                return $return;
                                        }
                                }
                        }
                }


                return null;
        }


        public static function defaultIcon()
        {
                return "xxxx";
        }

        public static function aweSomeIconNameToCode($name)
        {
                $name = strtolower($name);
                $name_arr = explode(" ", $name);

                foreach($name_arr as $i => $word) {
                        $word_synonym_arr = PagSynonymHelper::getSynonyms($word);
                        foreach ($word_synonym_arr as $syn_word) {
                                $icon_code = self::findAwesomeWord($syn_word);
                                if ($icon_code) {
                                        return $icon_code;
                                }
                        }
                }

        }


}
