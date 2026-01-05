<?php
const MAX_OUTPUT = 50;

class AfwUtils
{


        public static function hzm_start_immediate_output()
        {
                global $immediate_output, $immediate_output_nb;
                $immediate_output = true;
                $immediate_output_nb = 0;
                echo "<div class='immediate-output'>";
        }

        public static function hzm_stop_immediate_output()
        {
                global $immediate_output, $immediate_output_nb;
                $immediate_output = false;
                echo "</div>";
                echo "<script></script>";
        }


        public static function hzm_output($return)
        {
                global $immediate_output, $immediate_output_nb;
                if ($immediate_output) {
                        $immediate_output_nb++;
                        echo "im.output($immediate_output_nb)=" . $return;
                        if ($immediate_output_nb > MAX_OUTPUT) throw new AfwRuntimeException("MAX OUTPUT REACHED");
                }
        }


        public static function hzm_object_command_line($type, $odd_oven, $objId, $objTitle, $dataErrors, $lang, $error_class = "error")
        {
                global $immediate_output, $immediate_output_nb;
                $id_html = AfwUtils::hzm_format_command_line($type, $objId, $lang);
                $title_html = AfwUtils::hzm_format_command_line($type, $objTitle, $lang);
                $errors_html = AfwUtils::hzm_format_command_line($error_class, $dataErrors, $lang);

                $return = "<div class=\"cline-obj obj-$lang bg-$odd_oven\">
                               <div class=\"cline-obj-id obj-id-$lang\">$id_html</div>
                               <div class=\"cline-obj-title obj-title-$lang\">$title_html</div>
                               <div class=\"cline-obj-errors obj-errors-$lang\">$errors_html</div>
                      </div>";

                AfwUtils::hzm_output($return);
                return $return;
        }

        public static function hzm_attribute_command_line($type, $odd_oven, $name_att, $val_att, $lang, $name_class = "success")
        {
                global $immediate_output;
                $val_html = AfwUtils::hzm_format_command_line($type, $val_att, $lang);
                $name_html = AfwUtils::hzm_format_command_line($name_class, $name_att, $lang);
                $return = "<div class=\"cline-att att-$lang bg-$odd_oven\"><div class=\"cline-att-name att-name-$lang\">$name_html</div><div class=\"cline-att-val att-val-$lang\">$val_html</div></div>";
                AfwUtils::hzm_output($return);
                return $return;
        }

        public static function hzm_format_command_line($type, $string, $lang = "en", $pre = false, $coding = false, $kord = 0, $categ = "std")
        {
                global $immediate_output;
                if ($type == "php") $coding = true;
                if ($type == "sql") $coding = true;
                if (!$coding) $string = str_replace("  ", "&nbsp;&nbsp;", $string);
                $type_arr = explode("_", $type);
                $type_css = implode(" ", $type_arr);
                if ($pre) $type_css .= " " . $pre;
                if (!trim($string)) $string = "[EMPTY]";

                if (!$pre) $return = "<span id='cline-$categ-$kord' class=\"cline-$lang cline-message cline-$type\">$string</span>";
                else $return = "<span class=\"cline-$lang cline-message cline-$type\"><textarea class='$type_css'>$string</textarea></span>";

                AfwUtils::hzm_output($return);
                return $return;
        }

        public static function decodeHzmTemplate($tpl_content, $data_tokens)
        {
                $lang = AfwLanguageHelper::getGlobalLanguage();

                $token_arr = $data_tokens;

                $token_arr["[lang]"] = $lang;

                $text_to_decode = $tpl_content;

                foreach ($token_arr as $token => $val_token) {
                        $text_to_decode = str_replace("[" . $token . "]", $val_token, $text_to_decode);
                }

                return $text_to_decode;
        }


        public static function showUsingHzmTemplate($html_template_file, $data_tokens)
        {
                ob_start();
                if (file_exists($html_template_file)) {
                        include($html_template_file);
                        $tpl_content = ob_get_clean();

                        return self::decodeHzmTemplate($tpl_content, $data_tokens);
                } else {
                        return "showUsingHzmTemplate : file $html_template_file not found";
                }
        }
}
