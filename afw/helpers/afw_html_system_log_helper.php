<?php
    class AfwHtmlSystemLogHelper{

        /**
         * @param Auser $objme
         */
        public static function render($objme)
        {
            $html_log = "";
            if(AfwSession::config("MODE_DEVELOPMENT",false) or AfwSession::hasOption("SQL_LOG"))
            {
                    $html_log .= "<div id='analysis_log'><div id=\"analysis_log\"><div class=\"fleft\"><h1><b>System LOG activated :</b></h1></div><br><br>";
                    $html_log .= "SQL Picture : ".var_export(AfwDatabase::$sql_picture_arr, true)."<br>";
                    $html_log .= AfwSession::getLog();
                    
                    if($objme)
                    {
                            if(AfwSession::hasOption("ICAN_DO_LOG")) $html_log .= $objme->showICanDoLog();
                            if(AfwSession::hasOption("MEMORY_REPORT")) $html_log .= AfwMemoryHelper::memReport();
                    }      
                    $html_log .= "</div>";
            }

            return $html_log;
        }


    }