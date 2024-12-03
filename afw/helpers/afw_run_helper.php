<?php
class AfwRunHelper
{
        public static function show_back_trace($light = false)
        {
                global $lang;
                // $light=true; // otherwise sometime loop infite
                if ($light) $max_trace = 20;
                else $max_trace = 50;
                $backtrace = debug_backtrace(1, $max_trace);
                $html = "<table dir='ltr' style='width:100%' class='display dataTable display_ltr back_trace'>
                        <tr>
                                <th><b>Function </b>
                                </th><th><b>File </b></th>
                                <th><b>Line </b></th>
                        </tr>
                        ";
                $odd_even = "odd";
                $i = 1;
                foreach ($backtrace as $entry) {
                        $i++;
                        $html .= "<tr class='$odd_even'>";
                        $html .= "<td  style='border-top:1px solid #000;'>" . $entry['function'] . "</td>";
                        $html .= "<td  style='border-top:1px solid #000;'>" . $entry['file'] . "</td>";
                        $html .= "<td  style='border-top:1px solid #000;'>" . $entry['line'] . "</td>";
                        $html .= "</tr>
                ";
                        if (($entry['function'] != "safeDie") and ($entry['function'] != "show_back_trace")) {
                                if (($entry['object']) or (count($entry['args']) > 0)) {

                                        $html .= "<tr class='backtrace_tech_details $odd_even'>";
                                        $html .= "<td>id=" . $entry['object']->id . "</td>";
                                        if ($entry['object']) {
                                                if (class_exists("AFWObject") and ($entry['object'] instanceof AFWObject) and (!$light)) {
                                                        // Warning rafik : This do infinite loop seems do not call methods in error handlers
                                                        // $shdisp = $entry['object']->getShortDisplay($lang);
                                                        $shdisp = "class=" . get_class($entry['object']);
                                                } else $shdisp = get_class($entry['object']) . "-> display object";
                                        } else {
                                                $shdisp = "no-object";
                                        }

                                        if (!$light) {
                                                $html .= "<td colspan='2'>" . $shdisp . "</td>";
                                        } else {
                                                $html .= "<td colspan='2'>light-mode : $shdisp </td>"; // rafik may be create this->getLightDisplay ??
                                        }

                                        $html .= "</tr>\n";
                                        $html .= "<tr class='backtrace_tech_details $odd_even'>";
                                        $html .= "<td colspan='3'  style='border-bottom:1px solid #000;'>";
                                        if (class_exists("AfwHtmlHelper")) {
                                                $html .= AfwHtmlHelper::genereAccordion("<pre>" . var_export($entry['args'], true) . "</pre>", "Arguments", "Arguments$i");
                                        }
                                        $html .= "</td>";
                                        $html .= "</tr>\n";
                                }
                        }
                        if ($odd_even == "odd") $odd_even = "even";
                        else $odd_even = "odd";
                }
                $html .= "</table>\n";

                return $html;
        }

        public static function afw_guard($source, $message)
        {
                // rafik : to see what to implement here
                // @todo
        }

        public static function afw_guard_exception($source, Exception $e)
        {
                // rafik : to see what to implement here
                // @todo
        }

        public static function lightError($msg)
        {
                return AfwRunHelper::simpleError($msg, $call_method = "", $light = true);
        }


        public static function simpleError($msg, $call_method = "", $light = false)
        {
                throw new AfwRuntimeException($msg . " : call_method=$call_method");
        }

        public static function lightSafeDie($error_title, $objToExport = null)
        {
                $message = $error_title;
                if ($objToExport) $message .= "<br><pre class='code php' style='direction:ltr;text-align:left'>" . var_export($objToExport, true) . "</pre>";
                throw new AfwRuntimeException($message);
                //return AfwRunHelper::safeDie($error_title, $error_description_details="", $analysis_log=false, $objToExport, $light = true);
        }


        public static function unSafeDie($error_title, $light = true, $objToExport = null, $error_description_details = "", $analysis_log = true)
        {
                $message = $error_title;
                if ($objToExport) $message .= "<br> >> <b>obj</b> = <pre class='code php' style='direction:ltr;text-align:left'>" . var_export($objToExport, true) . "</pre>";
                if ($error_description_details) $message .= "<br> >> <b>more details</b> : " . $error_description_details;
                throw new AfwRuntimeException($message);

                // return AfwRunHelper::safeDie($error_title, $error_description_details, $analysis_log, $objToExport, $light, $force_mode_dev=true);
        }

        public static function safeDie($error_title, $error_description_details = "", $analysis_log = true, $objToExport = null, $light = false, $force_mode_dev = false)
        {
                $message = trim(ob_get_clean());


                $mode_dev = AfwSession::config("MODE_DEVELOPMENT", false);
                $mode_batch = AfwSession::config("MODE_BATCH", false);
                $xmodule = AfwSession::getCurrentlyExecutedModule();
                $open_mode = ($force_mode_dev or $mode_dev or $mode_batch);

                if (!$message) {
                        $crst = md5("crst" . date("YmdHis"));
                        $message = "<html>
                                <head>
                                <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                                <link rel='stylesheet' href='../lib/css/font-awesome.min-4.3.css'>
                                <link rel='stylesheet' href='../lib/css/font-awesome.min.css'>
                                <link rel='stylesheet' href='../lib/css/menu_ar.css'>
                                <link rel='stylesheet' href='../lib/css/front-application.css?crst=$crst'>
                                <link rel='stylesheet' href='../lib/css/hzm-v001.css?crst=$crst'>                                
                                
                                <link rel='stylesheet' href='../lib/css/front_screen_pc.css?crst=$crst'>
                                <link rel='stylesheet' href='../lib/css/front_tablet.css?crst=$crst'>
                                <link rel='stylesheet' href='../lib/css/front_mobile.css?crst=$crst'>
                                <link rel='stylesheet' href='../lib/css/front_mobile_thin.css?crst=$crst'>
                                
                                <link rel='stylesheet' href='../lib/css/material-design-iconic-font.min.css'>
                                <link rel='stylesheet' href='../lib/bootstrap/bootstrap-v3.min.css'>
                                <link rel='stylesheet' href='../lib/bsel/css/bootstrap-select.css'>
                                <link rel='stylesheet' href='../lib/css/dropdowntree.css' />
                                <link href='../lib/css/def_ar_front.css' rel='stylesheet' type='text/css'>
                                <link href='../lib/css/simple/style_common.css?crst=$crst' rel='stylesheet' type='text/css'>
                                <link href='../lib/css/simple/style_ar.css?crst=$crst' rel='stylesheet' type='text/css'>
                                <link href='../lib/css/simple/front_menu.css?crst=$crst' rel='stylesheet' type='text/css'>
                                <link href='../../external/css/common-$xmodule.css' rel='stylesheet' type='text/css' type='text/css'>
                                <link href='./css/module.css?crst=$crst' rel='stylesheet' type='text/css' type='text/css'>
                                <link href='../lib/skins/square/green.css' rel='stylesheet' type='text/css'>
                                <link href='../lib/skins/square/red.css' rel='stylesheet' type='text/css'>

                                <script src='../lib/js/jquery-1.12.0.min.js'></script>
                                <script src='../lib/bootstrap/bootstrap-v3.min.js'></script>
                                <script src='../lib/js/jquery-ui-1.11.4.js'></script>

                                <body style='font-family: monospace;'>";

                        if ($open_mode)  $application_info = "<div class='logo_application'>
                        <img src='../../external/pic/logo-application.png' alt='' style='margin-top:5px;float: left;height: 90px'>
                        </div>
                        <div class='title_application'>
                        <img src='../../external/pic/title-application.png' alt='' style='margin-top:5px;float: left;height: 90px'>
                        </div>";
                        else $application_info = "";

                        $message .= "<div class='medium-12 large-12 columns text-center large-text-right'>
                                <div class='logo_company'>  
                                <img src='../../external/pic/logo-company.png' alt='' style='margin-top:5px;height: px;'> 
                                </div>  
                                <div class='title_company'>  
                                <img src='../../external/pic/title-company.png' alt='' style='margin-top:-10px;height: px;'> 
                                </div>
                                $application_info     
                                </div>";
                }

                $back_trace_light = self::show_back_trace($light);

                if ($open_mode) {

                        $message .= "<div style='font-family: monospace;float: right;width: 100%;text-align: left;padding-top: 30px;border-top: 2px solid #0d67d8;'>";


                        $message .= "<div class='momken_error_title'><b>Only Development and Batch Mode Shown Error :</b> $error_title\n</div>";
                        if ($objToExport) {

                                $message .= AfwHtmlHelper::genereAccordion("<pre style='text-align: left;direction: ltr;font-family: monospace !important;'>" . $error_description_details . "\nExported Object\n" . var_export($objToExport, true) . "</pre>", "Object exported");
                        } else $message .= "<div><br> no object exported !!</div>";
                        //$message .= "<br> <b>PhpClass :</b> " . get_called_class();


                        $message .= $back_trace_light;



                        if (($analysis_log) and (class_exists("AfwAutoLoader") or class_exists("AfwSession"))) {
                                $message .= "<br><div id=\"analysis_log\">";
                                $message .= "<div class=\"fleft\"><h1><b>System LOG after safe die :</b></h1></div>";
                                $message .= AfwSession::getLog();
                                $message .= "</div>";
                        }
                        $message .= "</div>";
                } else {
                        $message .= "<div style='font-family: monospace;float: right;width: 100%;text-align: left;padding-top: 30px;border-top: 2px solid #0d67d8;'>";
                        $message .= "<div class='momken_error_title'>An error happened when executing this request : $error_title .";
                        $message .= "<br>Please contact the administrator <br> .حصل خطأ أثناء تنفيذ هذا الطلب الرجاء التواصل مع مشرف المنصة</div>";
                        $message .= "<br>open_mode:[$open_mode] = (force_mode_dev:[$force_mode_dev] or mode_dev:[$mode_dev] or mode_batch:[$mode_batch])<br>";
                        $message .= AfwSession::log_config();
                        $message .= "</div>";

                        AFWDebugg::log("Momken Framework Error : $error_title");
                        AFWDebugg::log("Back trace : \n $back_trace_light");
                }
                die($message);
                // triggersimpleError($message, E_USER_ERROR);
        }
}
