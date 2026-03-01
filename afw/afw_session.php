<?php

// old require of afw_root 

class AfwSession extends AFWRoot
{
        private static $singleton = null;

        private $data = array();
        private $userConnected = null;
        private $customerConnected = null;
        private $studentConnected = null;
        private $lastLogTime = null;

        public static function getSingleton()
        {
                if (!self::$singleton) self::$singleton = new AfwSession();
                return self::$singleton;
        }

        public function unsetUser()
        {
                unset($this->userConnected);
                $this->userConnected = null;
        }

        public function unsetCustomer()
        {
                unset($this->customerConnected);
                $this->customerConnected = null;
        }

        public function unsetStudent()
        {
                unset($this->studentConnected);
                $this->studentConnected = null;
        }


        public static function setMyLogTime($time)
        {
                self::getSingleton()->setLogTime($time);
        }

        public static function getMyLogTime()
        {
                return self::getSingleton()->getLogTime();
        }

        public static function devMode()
        {
                return self::config("development_mode", AfwSession::config("MODE_DEVELOPMENT", false));
        }



        public static function setUser($user_id)
        {
                if ($user_id > 0) {
                        self::getSingleton()->unsetCustomer();
                        self::setSessionVar("customer_id", null);
                        self::getSingleton()->unsetStudent();
                        self::setSessionVar("student_id", null);
                        if (self::getSessionVar("user_id") != $user_id) self::getSingleton()->unsetUser();
                        self::setSessionVar("user_id", $user_id);
                }
        }

        public static function setCustomer($customer_id)
        {
                if ($customer_id > 0) {
                        self::getSingleton()->unsetUser();
                        self::setSessionVar("user_id", null);
                        self::getSingleton()->unsetStudent();
                        self::setSessionVar("student_id", null);
                        if (self::getSessionVar("customer_id") != $customer_id) self::getSingleton()->unsetCustomer();
                        self::setSessionVar("customer_id", $customer_id);
                }
        }

        public static function setStudent($student_id)
        {
                if ($student_id > 0) {
                        self::getSingleton()->unsetUser();
                        self::setSessionVar("user_id", null);
                        self::getSingleton()->unsetCustomer();
                        self::setSessionVar("customer_id", null);
                        if (self::getSessionVar("student_id") != $student_id) self::getSingleton()->unsetStudent();
                        self::setSessionVar("customer_id", $student_id);
                }
        }

        private function getUser()
        {
                if (!$this->userConnected) {
                        $me = self::getSessionVar("user_id");
                        // die(" $me = self::getSessionVar(user_id)");
                        if ($me) {
                                $this->userConnected = Auser::loadById($me);
                        }
                }
                return $this->userConnected;
        }



        private function getCustomer($throwError = true, $customerClass = "CrmCustomer")
        {
                if (!$this->customerConnected) {
                        $me = self::getSessionVar("customer_id");
                        // die(" $me = self::getSessionVar(customer_id)");
                        if ($me) {
                                $this->customerConnected = $customerClass::loadById($me);
                                if (!$this->customerConnected) {
                                        $me = 0;
                                        self::setSessionVar("customer_id", 0);
                                }
                        } elseif (self::config("consider_user_as_customer", false)) {
                                $objme = self::getUserConnected();
                                if ($objme) {

                                        $mobile = $objme->getVal("mobile");
                                        $idn = $objme->getVal("idn");
                                        $first_name = $objme->getVal("firstname");
                                        $last_name = $objme->getVal("lastname");
                                        $customer_gender_id = $objme->getVal("genre_id");
                                        $city_id = $objme->getVal("city_id");

                                        //@todo below should be more intelligent
                                        $customer_type_id  = self::config("default_customer_type", 1);
                                        try {
                                                if ($mobile and $idn) $this->customerConnected = $customerClass::createOrUpdateCustomer($mobile, $idn, $first_name, $last_name, $customer_gender_id, $city_id, $customer_type_id);
                                                else $this->customerConnected = null;
                                        } catch (Exception $e) {
                                                if ($throwError) throw $e;
                                                $this->customerConnected = null;
                                        }
                                }
                        }
                }

                return $this->customerConnected;
        }


        private function getStudent($throwError = true, $studentClass = "Student")
        {
                if (!$this->studentConnected) {
                        $me = self::getSessionVar("student_id");
                        if ($me) {
                                $this->studentConnected = $studentClass::loadById($me);
                        }
                }
                return $this->studentConnected;
        }

        private function getLogTime()
        {
                return $this->lastLogTime;
        }


        private function setLogTime($time)
        {
                $this->lastLogTime = $time;
        }

        private function setData($var, $value)
        {
                $this->data[$var] = $value;
                // if(AfwStringHelper::stringStartsWith($var,'bf-')) die("data bf- setted by setData => ".self::log_all_data());
        }

        private function getData($var)
        {
                return $this->data[$var];
        }

        private function isSetData($var)
        {
                return isset($this->data[$var]);
        }

        private function getAllData()
        {
                return $this->data;
        }

        public static function getSessionVar($var)
        {
                return $_SESSION[$var];
        }

        public static function initSession($var_data, $prefix = "")
        {
                foreach ($var_data as $col => $val) {
                        self::setSessionVar($col, $val);
                }
        }

        public static function initUserSession($var_data)
        {
                self::initSession($var_data, "user_");
        }

        public static function setSessionVar($var, $value)
        {
                if (empty($value)) self::emptingVar($var, "setSessionVar");
                $_SESSION[$var] = $value;
                /*
                if(($var=="main_company") and ($value!="nauss"))
                {
                        throw new AfwRuntimeException("main_company attempt to be set to [$value] value");
                }
                */
                return $value;
        }



        public static function setSessionVarIfNotSet($var, $value)
        {
                if (!$_SESSION[$var]) {
                        return self::setSessionVar($var, $value);
                } else {
                        return null;
                }
        }

        public static function pushIntoSessionArray($key, $value_to_push)
        {
                $arr = self::getSessionVar($key);
                if ((!$arr) or (!is_array($arr))) $arr = array();
                array_push($arr, $value_to_push);
                self::setSessionVar($key, $arr);
        }

        public static function pullSessionVar($var, $source = "pullSessionVar")
        {
                $val = $_SESSION[$var];
                self::emptingVar($var, $source);
                unset($_SESSION[$var]);
                return $val;
        }

        public static function emptingVar($var, $source)
        {
                if ($var == "main_company") {
                        throw new AfwRuntimeException("main_company attempt to be emptied");
                }
                if (($var == "user_id") and
                        ($source != "resetSession") and
                        ($source != "header") and
                        ($_SESSION[$var])
                ) {
                        AfwRunHelper::lightSafeDie("$var is being emptied from its value " . $_SESSION[$var]);
                }
        }

        public static function hasOption($optionCode)
        {
                $optionCodes = self::getOptions();
                if (!$optionCodes) $optionCodes = array();
                return $optionCodes[$optionCode];
        }

        public static function getOptions()
        {
                return self::getSessionVar("optionCodes");
        }

        public static function toggleOption($optionCode)
        {
                $optionCodes = self::getOptions();
                if (!$optionCodes) $optionCodes = array();
                $optionCodes[$optionCode] = (!$optionCodes[$optionCode]);

                return self::setSessionVar("optionCodes", $optionCodes);
        }

        public static function resetSession($except_var = "")
        {
                foreach ($_SESSION as $colsess => $val) {
                        if ($except_var != $colsess) {
                                self::emptingVar($colsess, "resetSession");
                                unset($_SESSION[$colsess]);
                        }
                }
        }

        public static function getVar($var)
        {
                global $getDataCounter;
                if (!$getDataCounter) $getDataCounter = 0;
                else $getDataCounter++;

                if (($getDataCounter > 3) and AfwStringHelper::stringStartsWith($var, 'bf-')) die("data bf- getting $var by getVar => " . self::log_all_data());

                return self::getSingleton()->getData($var);
        }

        public static function setVar($var, $value)
        {
                // if(($var=="log") and (!$value)) throw new AfwRuntimeException("emptying log ...".self::getSingleton()->getData($var));                       
                self::getSingleton()->setData($var, $value);
        }

        public static function config($key, $default, $configContext = "system", $loadContextConfig = 'no', $force_main_company = "")
        {
                $doLoadContextConfig = ($loadContextConfig != 'no');
                if ($doLoadContextConfig) {
                        $loadClientConfig = (($loadContextConfig == "client") or ($loadContextConfig == "force-client"));
                        $reload = (($loadContextConfig == "force") or ($loadContextConfig == "force-client"));
                        self::loadContextConfig($configContext, $loadClientConfig, $reload, $force_main_company);
                }

                $var = $configContext . "|" . $key;
                return self::getSingleton()->isSetData("creg-" . $var) ? self::getSingleton()->getData("creg-" . $var) : $default;
        }

        public static function log_config($lineSep = "<br>")
        {
                $return = "";
                $all_data = self::getSingleton()->getAllData();
                foreach ($all_data as $var_0 => $val) {
                        if (AfwStringHelper::stringStartsWith($var_0, "creg-")) {
                                $var = substr($var_0, 5);
                                $return .= "$var = $val \n $lineSep";
                        }
                }

                return $return;
        }

        public static function log_all_data($lineSep = "<br>")
        {
                $return = "";
                $all_data = self::getSingleton()->getAllData();
                foreach ($all_data as $var => $val) {
                        $return .= "log var $var => value = $val \n $lineSep";
                }

                return $return;
        }

        public static function class_config_exists($classe, $param, $default = false)
        {
                return self::config("${classe}_$param", $default);
        }

        public static function setConfig($key, $value, $configContext = "system")
        {
                $var = $configContext . "|" . $key;
                self::getSingleton()->setData("creg-" . $var, $value);
        }

        public static function initConfig($config_arr, $configContext = "system", $fromFile = "")
        {
                if (!$fromFile) throw new AfwRuntimeException("AfwSession::initConfig (with context `$configContext`) without specifying the config file name");
                /*
                if(AfwStringHelper::stringEndsWith($fromFile,"application_config.php"))
                {
                        throw new AfwRuntimeException("initing application config from context $configContext from the file $fromFile");
                }*/

                foreach ($config_arr as $key => $value) {
                        if (AfwStringHelper::stringContain($key, "|")) {
                                die("the config parameters names should never contain prohibted `|` charachter, parameter $key in context $configContext doesn't respect this rule");
                        }
                        self::setConfig($key, $value, $configContext);
                }
        }

        public static function contextLog($string, $context)
        {
                return self::log($string, $css_class = "log hzmlog", $separator = "<br>\n", $show_time = true, $context);
        }

        public static function currTime()
        {
                return round(hrtime(true) / 100000000) / 10;
        }

        public static function currMilliSeconds()
        {
                return round(hrtime(true) / 1000000);
        }

        public static function log($string, $css_class = "paglog hzmlog", $separator = "<br>\n", $show_time = true, $context = "log")
        {

                global $log_counter, $MODE_BATCH_LOURD;
                if (!$log_counter) $log_counter = 0;
                $log_counter++;

                $MODE_DEV = self::config("MODE_DEVELOPMENT", false);

                if ($MODE_DEV) {
                        $log_counter_limit = 100;
                } else {
                        $log_counter_limit = 300;
                }

                if ($MODE_BATCH_LOURD) $log_counter_limit = 5 * $log_counter_limit;

                if ($log_counter > $log_counter_limit) {
                        return;
                }

                AfwBatch::print_debugg($string);
                if ($context == "log") {
                        $oldLastLogTime = self::getMyLogTime();
                        $now_time = self::currTime();
                        self::setMyLogTime($now_time);
                        $critical = "";
                        if ($oldLastLogTime) {
                                $durationSinceLastLog = $now_time - $oldLastLogTime;
                                if ($durationSinceLastLog > 3) $critical = "top critical";
                                elseif ($durationSinceLastLog > 2) $critical = "critical";
                                elseif ($durationSinceLastLog > 1) $critical = "bad";
                                elseif ($durationSinceLastLog > 0.5) $critical = "require-attention";
                        }

                        if ($critical) {
                                $icdLog = self::getLog("iCanDo");
                                if ($icdLog) $string .= "<br>\niCanDo LOG : <br>\n" . $icdLog;
                        }
                }
                $html = trim(self::getVar($context));
                if ($html) $html .= $separator;
                if ($show_time) $string .= $separator . " [" . date("Y-m-d H:i:s") . "." . self::currMilliSeconds() . "] <b>(d=$durationSinceLastLog)</b>";
                // if($css_class == "hzm") 
                $html .= "<pre class='$css_class $context $critical'>$string</pre>"; //  N$now_time O$oldLastLogTime D$durationSinceLastLog
                //if($css_class != "hzm") die("[[[[[$string]]]]]");
                self::setVar($context, $html);
        }

        public static function warning($string, $separator = "<br>\n")
        {
                self::log($string, $css_class = "warning hzmlog", $separator);
        }

        public static function success($string, $separator = "<br>\n")
        {
                self::log($string, $css_class = "success hzmlog", $separator);
        }

        public static function sqlLog($string, $module_info, $separator = "<br>\n")
        {
                self::log($string, $css_class = "sql $module_info", $separator);
        }

        public static function hzmLog($string, $module_info, $separator = "<br>\n")
        {
                // die("here hzmLog");
                self::log($string, $css_class = "hzmlog $module_info", $separator);
        }


        public static function logSessionData($get_log = false)
        {
                self::warning("_SESSION = " . var_export($_SESSION, true));
                if ($get_log) return self::getLog();
        }


        public static function sqlError($string, $module_info, $separator = "<br>\n")
        {
                self::log($string, $css_class = "sql error $module_info", $separator);
        }

        public static function getLog($context = "log")
        {
                $return = self::getVar($context);
                self::setVar($context, "");
                return $return;
        }

        public static function debuggLog($title = "debugging ...")
        {
                $return = self::getVar("log");
                self::setVar("log", "");
                AfwRunHelper::safeDie($title, $return);
        }



        public static function logHzm($my_module, $my_class, $hzm_log)
        {
                $log = "<pre class='sql hzmlog'><b>module</b> : $my_module,\n<b>class</b> : $my_class,\n<b>hzm log</b> :\n $hzm_log\n </pre>";
                self::log($log);
        }

        /**
         * @return Auser
         *  */

        public static function getUserConnected()
        {
                return self::getSingleton()->getUser();
        }

        public static function getCustomerConnected($throwError = true, $customerClass = "CrmCustomer")
        {
                $custObj = self::getSingleton()->getCustomer($throwError, $customerClass);

                return $custObj;
        }

        public static function getStudentConnected()
        {
                return self::getSingleton()->getStudent();
        }


        public static function getCurrentModuleTemplate()
        {
                return self::config("module-template", "new");
        }

        public static function currentMenuTemplate()
        {
                return self::config("menu-template", "modern");
        }

        public static function currentLoginTemplate()
        {
                return self::config("login-template", "right-left");
        }

        public static function currentSystemDateFormat()
        {
                return self::config("system-date-format", "hijri");
        }


        public static function getCurrentFrontSiteName($frontApp, $lang)
        {
                $application_nameArr = self::config("$frontApp-application-name", self::config("application_name", []));
                return $application_nameArr[$lang];
        }


        public static function getCurrentSiteName($lang)
        {
                $application_nameArr = self::config("application_name", []);
                return $application_nameArr[$lang];
        }

        public static function setCurrentCompany($main_company)
        {
                // if($main_company!="nauss") throw new AfwRuntimeException("debugg rafik main_company=uoh should be nauss");
                self::setSessionVar("main_company", $main_company);
        }

        public static function currentDBPrefix()
        {
                $dbSwicthWithCompany = self::config("db-swicth-with-company", true);
                $main_company = self::getSessionVar("main_company");
                if ($dbSwicthWithCompany and $main_company) return $main_company . "_";
                return AfwSession::config("db_prefix", "default_db_");
        }

        public static function companiesList()
        {
                return AfwSession::config("companies", ["company-1", "company-2", "etc"]);
        }

        public static function currentCompany()
        {
                $main_company = self::getSessionVar("main_company");
                if ($main_company) return $main_company;
                return self::config("main_company", "");
        }

        public static function getCurrentlyExecutedModule()
        {
                $exec_module = self::currentCompany();
                if ((!$exec_module) and self::config("x_module_means_company", false)) {
                        $objme = self::getUserConnected();
                        if ($objme) {
                                $myOrg = null;
                                $myOrgId = $objme->getMyOrganizationId("");
                                if ($myOrgId) $myOrg = Orgunit::loadById($myOrgId);
                                // we simulate our application as a specific application of the user company 
                                // to load specific css, pictures and may be business rules etc...
                                if ($myOrg) $exec_module = $myOrg->getVal("hrm_code");
                        }
                }


                if (!$exec_module) {
                        $exec_module = AfwUrlManager::currentURIModule();
                }

                return $exec_module;
        }

        public static function unsetUserConnected()
        {
                return self::getSingleton()->unsetUser();
        }

        public static function unsetCustomerConnected()
        {
                return self::getSingleton()->unsetCustomer();
        }

        public static function getUserIdActing()
        {
                // rafik 3-oct-2022 I think prio for user not customer
                // so I put this bloc first before it was second
                $me = self::getSessionVar("user_id");
                if ($me) return $me;
                // user and customer if they are both connected the acting user that will be 
                // considered is the customer and better if we develop the logic of only one of them 
                // is connected at the same time (depending on the application => up to you system architect)
                $me = self::getSessionVar("customer_id");
                if ($me) {
                        $customer_virtual_user_id = $me + Auser::$MAX_USERS_CRM_START;
                        return $customer_virtual_user_id;
                }
        }

        public static function userIsAuthenticated()
        {
                return (self::getSessionVar("user_id") > 0);
        }

        public static function customerIsConnected()
        {
                return (self::getSessionVar("customer_id") > 0);
        }

        public static function pushString($text, $string_to_push)
        {
                $string_to_push = trim($string_to_push);
                $new_string_to_push = str_replace("<br>", "", $string_to_push);
                if ($new_string_to_push) {
                        $text = trim($text);
                        if ($text) return $text . "<br>" . $string_to_push;
                        else return $string_to_push;
                } else return $text;
        }




        public static function pushSuccess($success, $css_class = "")
        {
                self::setSessionVar("success", self::pushString(self::getSessionVar("success"), $success));
                if ($css_class) self::setSessionVar("success-class", $css_class);
        }

        public static function pushLog($slog, $css_class = "")
        {
                self::setSessionVar("slog", self::pushString(self::getSessionVar("slog"), $slog));
                if ($css_class) self::setSessionVar("slog-class", $css_class);
        }


        public static function pushInformation($information, $css_class = "")
        {
                self::setSessionVar("information", self::pushString(self::getSessionVar("information"), $information));
                if ($css_class) self::setSessionVar("information-class", $css_class);
        }

        public static function pushWarning($warning, $css_class = "")
        {
                // throw new AfwRuntimeException("who sent this warning");
                self::setSessionVar("warning", self::pushString(self::getSessionVar("warning"), $warning));
                if ($css_class) self::setSessionVar("warning-class", $css_class);
        }

        public static function pushError($error, $css_class = "")
        {
                self::setSessionVar("error", self::pushString(self::getSessionVar("error"), $error));
                if ($css_class) self::setSessionVar("error-class", $css_class);
        }

        public static function pushPbmResult($lang, $error, $info, $warn, $technical, $pbMethodCode = "mainpage")
        {
                if ($technical) {
                        // die("here warn = $warn");
                        if ($warn) $warn .= "<br>";
                        $warn .= AfwLanguageHelper::tarjemMessage("There are more technical details with administrator", $lang);
                        $warn .= "<div class='technical'>$technical</div>";
                }
                if ($info) AfwSession::pushInformation($info, "method-$pbMethodCode");
                if ($error) AfwSession::pushError($error);
                if ($warn) AfwSession::pushWarning($warn);
        }


        public static function pullSuccess()
        {
                return self::pullSessionVar("success");
        }

        public static function pullInformation()
        {
                return self::pullSessionVar("information");
        }

        public static function pullWarning()
        {
                return self::pullSessionVar("warning");
        }

        public static function pullError()
        {
                return self::pullSessionVar("error");
        }

        public static function startSession()
        {
                session_start();
                AfwSession::setSessionVar("started", true);
                AfwSession::pullSessionVar("was_customer");
                AfwSession::pullSessionVar("was_user");
        }


        public static function closeSession()
        {
                foreach ($_SESSION as $key => $value) {
                        if ("main_company" != $key) {
                                if (empty($value)) self::emptingVar($key, "closeSession");
                                unset($_SESSION[$key]);
                        }
                }
        }

        public static function sessionStarted()
        {
                return ($_SESSION["started"]);
        }

        public static function die_and_export_session()
        {
                die(var_export($_SESSION, true));
        }

        public static function setWasCustomer()
        {
                self::setSessionVar("was_customer", true);
                self::setSessionVar("was_user", false);
        }

        public static function setWasUser()
        {
                self::setSessionVar("was_customer", false);
                self::setSessionVar("was_user", true);
        }

        public static function wasCustomer()
        {
                return self::getSessionVar("was_customer");
        }

        public static function wasUser()
        {
                return self::getSessionVar("was_user");
        }


        public static function logout()
        {
                self::closeSession();
                if (self::getSingleton()->unsetCustomer()) self::setWasCustomer();
                elseif (self::getSingleton()->unsetUser()) self::setWasUser();
        }


        public static function loadContextConfig($configContext, $loadClientConfig = false, $reload = false, $force_main_company = "")
        {
                $contextAlreadyLoaded = self::config("$configContext-config-already-loaded", false, $configContext);
                if ($reload or !$contextAlreadyLoaded) {
                        $this_dir_name = dirname(__FILE__);
                        $context_config_file = "$this_dir_name/../../config/" . $configContext . "_config.php";
                        if (file_exists($context_config_file)) {
                                $the_config_arr = include($context_config_file);
                                if (!$the_config_arr or (!is_array($the_config_arr)) or (count($the_config_arr) == 0)) die("$context_config_file file should return a correct config array");
                                $the_config_arr["$configContext-config-already-loaded"] = true;
                                AfwSession::initConfig($the_config_arr, $configContext, $context_config_file);
                        }
                        if ($force_main_company) $the_config_arr["main_company"] = $force_main_company;
                        if ($loadClientConfig) {
                                if (!$the_config_arr["main_company"]) die($configContext . "_config.php file should define the main_company param to be able to load $configContext config for client");
                                $main_company = $the_config_arr["main_company"];
                                $contextClientAlreadyLoaded = self::config("$configContext-client-$main_company-config-already-loaded", false, $configContext);
                                if ($reload or !$contextClientAlreadyLoaded) {
                                        $client_config_file = "$this_dir_name/../../client-$main_company/" . $configContext . "_config.php";
                                        if (file_exists($client_config_file)) {
                                                $client_config_arr = include($client_config_file);
                                                if (!$client_config_arr or (!is_array($client_config_arr)) or (count($client_config_arr) == 0)) die($configContext . "_config.php file of client $main_company should return a correct config array");
                                                if ($client_config_arr["main_company"] and ($main_company != $client_config_arr["main_company"])) die($configContext . "_config.php file should define the same main_company param, avoid bad copy-past in config files");
                                                $client_config_arr["$configContext-client-$main_company-config-already-loaded"] = true;
                                                AfwSession::initConfig($client_config_arr, $configContext, $client_config_file);
                                                // if($configContext=="display") die(" $client_config_file found, client_config_arr = ".var_export($client_config_arr,true));
                                        } else {
                                                // die("$client_config_file not found");
                                        }
                                }
                        }
                }
        }
}

AfwSession::loadContextConfig("system", true);
global $global_need_utf8;
$global_need_utf8 = AfwSession::config('global_need_utf8', true);
// die("first initConfig ".AfwSession::log_config());