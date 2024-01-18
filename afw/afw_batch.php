<?php
    class AfwBatch 
	{
        private static $errors_arr = array();
        private static $warnings_arr = array();
        private static $infos_arr = array();

        private static $echo=false;

        public static function bt($string,$lang="ar")
        {
            global $book_translation;
                
            if($book_translation[$lang][$string])  return $book_translation[$lang][$string];
            
            $string_tr = "[[$string]]";
            //echo $string_tr;
            return $string_tr;
        }

        public static function disableEcho()
        {
            return self::$echo=false;
        }

        public static function enableEcho()
        {
            return self::$echo=true;
        }

        public static function debugg($string)
        {
            // -- hzmc
            $echo_text = AfwBatchColors::singleton()->getColoredString($string, $foreground_color = "white")."\n";
            self::echo_batch($echo_text);
            return $echo_text; 
        }

        public static function debuggArray($arr)
        {
            self::print_debugg("[");
            foreach($arr as $key => $str)
            {
                $string = "  $key => $str ";
                self::print_debugg($string);
            }
            self::print_debugg("]");
        }
        
        public static function print_debugg($string)
        {
            return self::debugg($string);
        }

        public static function print_custom($type,$string)
        {
            if($type=="debugg") return self::print_debugg($string);
            if($type=="sql") return self::print_sql($string);
            if($type=="error") return self::print_error($string);
            if($type=="warning") return self::print_warning($string);
            if($type=="info") return self::print_info($string);
            if($type=="important") return self::print_important($string);
            if($type=="comment") return self::print_comment($string);
        }

        public static function print_comment($string, $prefix_comment="-- ")
        {
            // -- hzmc
            $echo_text = AfwBatchColors::singleton()->getColoredString($prefix_comment.$string, $foreground_color = "black", $background_color = "green")."\n";
            self::echo_batch($echo_text);
            return $echo_text; 
        }

        public static function print_sql($string)
        {
            // -- hzmc
            $echo_text = AfwBatchColors::singleton()->getColoredString($string, $foreground_color = "light_purple")."\n";
            self::echo_batch($echo_text);
            return $echo_text; 
        }
        
        public static function print_error($string)
        {
            self::$errors_arr[] = $string;
            // -- hzmc
            $echo_text = AfwBatchColors::singleton()->getColoredString($string, $foreground_color = "white", $background_color = "red")."\n";
            self::echo_batch($echo_text);
            return $echo_text;
        }
        
        
        public static function print_warning($string)
        {
            self::$warnings_arr[] = $string;
            // -- hzmc
            $echo_text = AfwBatchColors::singleton()->getColoredString($string, $foreground_color = "yellow")."\n";
            self::echo_batch($echo_text);
            return $echo_text;
        }
        
        public static function print_info($string)
        {
            self::$infos_arr[] = $string;
            // -- hzmc
            $echo_text = AfwBatchColors::singleton()->getColoredString($string, $foreground_color = "light_green")."\n";
            self::echo_batch($echo_text);
            return $echo_text;
        }

        public static function getInfos($sep=null)
        {
            if($sep) return implode($sep, self::$infos_arr);
            else return self::$infos_arr;
        }

        public static function getErrors($sep=null)
        {
            if($sep) return implode($sep, self::$errors_arr);
            else return self::$errors_arr;
        }

        public static function getWarnings($sep=null)
        {
            if($sep) return implode($sep, self::$warnings_arr);
            else return self::$warnings_arr;
        }
        
        public static function print_important($string)
        {
            // -- hzmc
            $echo_text = AfwBatchColors::singleton()->getColoredString($string, $foreground_color = "white", $background_color = "blue")."\n";
            self::echo_batch($echo_text);
            return $echo_text;
        }

        public static function echo_batch($string)
        {
            if(self::$echo) echo date("Y-m-d H:i:s")." : ".$string;
        }


        /*****************     TABLES AND HTML */
        
        public static function print_separator($header, $colors=null)
        {
            foreach($header as $col => $size)
            {
            echo "+".str_pad("-", $size,"-");
            }
            echo "+\n";
        }
        
        public static function print_header($header, $colors=null)
        {
            self::print_separator($header, $colors);
            foreach($header as $col => $size)
            {
            echo "|".str_pad($col, $size);
            }
            echo "|\n";
            self::print_separator($header, $colors);
        }
        
        public static function print_row($header,$row, $colors=null)
        {
            // -- hzmc
            
            list($color_row, $bg_color_row) = self::get_row_color_from_color_rules($colors, $row);
            
            $row_text = "";
            
            foreach($header as $col => $size)
            {
            $row_text .= "|".str_pad($row[$col], $size);
            }
            $row_text .= "|";
            
            if($color_row) echo AfwBatchColors::singleton()->getColoredString($row_text, $color_row, $bg_color_row)."\n";
            else echo $row_text."\n";
        }
        
        public static function get_row_color_from_color_rules($colors, $row)
        {
            global $print_debugg;
            
            $color = null;
            $bg_color = null;
            
            foreach($colors as $rule => $color_rule)
            {
                //if($print_debugg) self::print_debugg("rule $rule colors => ".var_export($color_rule["colors"],true));
                
                $val_coloring = $row[$color_rule["col"]];
                if($color_rule["code"]=="val_of_col")
                {
                    $color = isset($color_rule["colors"][$val_coloring]) ? $color_rule["colors"][$val_coloring] : $color;
                    $bg_color = isset($color_rule["bg_colors"][$val_coloring]) ? $color_rule["bg_colors"][$val_coloring] : $bg_color;
                }
                
                if($color_rule["code"]=="min_val_of_col")
                {
                    
                    foreach($color_rule["bg_colors"] as $minval_for_color => $minval_color)
                    {
                        if($val_coloring >= $minval_for_color) 
                        {
                            $bg_color = $minval_color;
                        }
                    }
                    
                    foreach($color_rule["colors"] as $minval_for_color => $minval_color)
                    {
                        if($val_coloring >= $minval_for_color) 
                        {
                            $color = $minval_color;
                        }
                    }
                }
                
                if($color_rule["code"]=="max_val_of_col")
                {
                    foreach($color_rule["bg_colors"] as $maxval_for_color => $maxval_color)
                    {
                        if($val_coloring <= $maxval_for_color) $bg_color = $maxval_color;
                    }
                    
                    foreach($color_rule["colors"] as $maxval_for_color => $maxval_color)
                    {
                        if($val_coloring <= $maxval_for_color) $color = $maxval_color;
                        
                    }
                }
            }
            
            return array($color, $bg_color);
        
        }
        
        
        public static function print_data($header,$data, $colors=null)
        {
            self::print_header($header, $colors);
            foreach($data as $row)
            {
                self::print_row($header,$row, $colors);
            }
            self::print_separator($header, $colors);
        }
        
        
        /* html showing */
        public static function html_header($header, $colors=null)
        {
            $html_header = "<tr>";
            foreach($header as $col => $size)
            {
                $html_header .= "<th style='color: white;background-color: #1283cf;text-align: center;'><b>$col</b></th>";
            }
            $html_header .= "</tr>";
            
            return $html_header;
        }
        
        public static function html_row($header, $row, $colors=null)
        {
            // -- hzmc
            
            list($color_row, $bg_color_row) = self::get_row_color_from_color_rules($colors, $row);
            
            $row_text = "<tr style='color=[color];background-color:[bgcolor]'>";
            
            foreach($header as $col => $size)
            {
            $val_col = $row[$col];
            $row_text .= "<td>$val_col</td>";
            }
            $row_text .= "</tr>";
            
            if($color_row or $bg_color_row) $row_text = AfwBatchColors::singleton()->getColoredHtml($row_text, $color_row, $bg_color_row);
            
            return $row_text;
            
        }
        
        public static function html_data($header,$data, $colors=null)
        {
            $html = "<table cellpadding=\"4\" cellspacing=\"3\" width=\"80%\" style='font-size:18px;font-family:calibri'>";
            $html .= "<header>";
            $html .= self::html_header($header, $colors);
            $html .= "</header>";
            $html .= "<body>";
            foreach($data as $row)
            {
                $html .= self::html_row($header,$row, $colors);
            }
            $html .= "</body>";
            $html .= "</table>";
            
            return $html;
        }


        public static function emailError($project_code, $project, $error, $language="en", $dir="ltr")
        {
            global $send_from, $email_admin, $email_errors;
            if($email_errors)
            {
                    $subject = "FAILED : $project, Please support !";
                    $now_timestamp = date("Y-m-d H:i:s");
                    
                    $body = array();
                    $body[] = headerMail($dir);
                    $body[] = "<h3>$subject</h3>";
                    $body[] = "time of run : $now_timestamp";
                    $body[] = "<h4><b>$project failed with the following error :</b></h4>";
                    $body[] = "<h5 style='color:red'>$error</h5>";
                    $body[] = footerMail();
                    
                    $res = AfwMailer::hzmMail($project_code,"$project_code-error-$now_timestamp",$email_admin,$subject,$body, $send_from, $format="html", $language);
            }
            else
            {
                    $res = array();
                    $res["result"] = false;
                    $res["error"] = "option email_errors is not enabled in configuration";
            }

            return $res;
            
        }

        public static final function retrieve($object, $array)
        {
            $tableau = [];
            $k = 0;
            $all_real_fields = AfwStructureHelper::getAllRealFields($object);
            foreach ($array as $object) {
                foreach ($all_real_fields as $attribute) {
                    $structure = AfwStructureHelper::getStructureOf($object, $attribute);
                    if ($structure['RETRIEVE']) {
                        $tableau[$k][$attribute] = $object->decode($attribute);
                    }
                }
                $k++;
            }
            $separator = '';
            $header = '';
            $tabColSizes = [];
            foreach ($tableau as $i => $row) {
                foreach ($tableau[$i] as $col => $val) {
                    $tabHeader[$col] = $col;
                    if (!$tabColSizes[$col]) {
                        $tabColSizes[$col] = 1;
                    }
                    if ($tabColSizes[$col] < strlen($val) + 2) {
                        $tabColSizes[$col] = strlen($val) + 2;
                    }
                    if ($tabColSizes[$col] < strlen($col) + 2) {
                        $tabColSizes[$col] = strlen($col) + 2;
                    }
                }
            }
            foreach ($tabHeader as $champ => $colTit) {
                $separator .= '+' . str_repeat('-', $tabColSizes[$champ]);
                $header .= '|' . str_pad($colTit, $tabColSizes[$champ], ' ');
            }
            $separator .= '+';
            $header .= '|';
            $print = "\n" . $separator . "\n" . $header . "\n" . $separator . "\n";
            foreach ($tableau as $ii => $rowData) {
                $content = '';
                foreach ($tabHeader as $champ => $colTit) {
                    $content .=
                        '|' . str_pad($rowData[$champ], $tabColSizes[$champ], ' ');
                }
                $content .= "|\n";
                $print .= $content;
            }
            $print .= $separator . "\n";
            AFWDebugg::print_str($print, 'inf');
        }
    }
    
    
    
?>