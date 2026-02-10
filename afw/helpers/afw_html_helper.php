<?php

// old require of afw_root 

class AfwHtmlHelper extends AFWRoot
{

        public static function hzmTplPath()
        {
                return dirname(__FILE__) . "/../../hzm/web";
        }

        public static function arrayToSelectOptions($arr, $selection)
        {
                $html_options = '';
                foreach ($arr as $option) {
                        $selected = "";
                        if ($selection == $option) $selected = "selected";
                        $html_options .= "<option value='$option' $selected>$option</option>";
                }

                return $html_options;
        }

        public static function arrayToHtml($arr_key_vals, $keyDecodeArr = null, $wdKey = "1", $wdVal = "3", $odd = "odd", $even = "even")
        {
                $html_rows = '';
                $cls = $odd;
                foreach ($arr_key_vals as $key => $val) {
                        if ($keyDecodeArr) {
                                $key_disp = $keyDecodeArr[$key];
                        } else $key_disp = $key;
                        $html_rows .= "   <div class='cols3 cols3_title hzm_wd$wdKey $cls fright'><b>$key_disp</b> :</div> <div class='cols3 cols3_value hzm_wd$wdVal $cls fright'>$val</div>\n";
                        if ($cls == $odd) $cls = $even;
                        else $cls = $odd;
                }

                return "<div class='hzm_attribute hzm_wd4'>
                <div class='cols3'>
                $html_rows
                </div>
        </div>";
        }

        public static function array_keysToHeader($row)
        {
                $header_trad = [];
                if ($row and is_array($row) and (count($row) > 0)) {
                        $header_keys = array_keys($row);
                        foreach ($header_keys as $header_col) {
                                $header_trad[$header_col] = $header_col;
                        }
                }

                return $header_trad;
        }

        public static function tableToHtml($data, $header_trad = null, $decoderArr = null)
        {
                $data = self::resetIndexesToDataArray($data);
                // die("tableToHtml data = ".var_export($data, true));
                // echo("tableToHtml old header_trad = ".var_export($header_trad, true));
                // $header_trad=null;
                if (!$header_trad) $header_trad = self::array_keysToHeader($data[0]);
                // die("tableToHtml new header_trad = ".var_export($header_trad, true));
                list($html, $ids) = AfwShowHelper::tableToHtml($data, $header_trad, $showAsDataTable = false, $isAvail = null, $nowrap_cols = null, $class_table = "grid", $class_tr1 = "altitem", $class_tr2 = "item", $class_td_off = "asttdoff", $lang = "ar", $dir = "rtl", $bigtitle = "", $bigtitle_tr_class = "bigtitle", $width_th_arr = array(), $img_width = "", $rows_by_table = 0, $showWidthedTable = "", $row_class_key = "", $col_class_key = "", $class_td_off = 'off', $order_key = '', $decoderArr);
                return $html;
        }

        public static function htmlBackTrace($backtrace, $advanced = true)
        {
                $message = "<table dir='ltr' class='hide'>
                                <tr class='btrace header'>
                                        <th><b>Function </b></th>
                                        <th><b>File </b></th>
                                        <th><b>Line </b></th>
                                        <th><b>Object </b></th>
                                        <th class='params'><b>Params </b></th>
                                </tr>
                                ";
                $odd                = "";
                foreach ($backtrace as $entry) {
                        $args_desc = "Disabled <!-- to enable use change config attribute advanced-back-trace to `true` value -->";
                        $object_desc = "Disabled <!-- to enable use change config attribute advanced-back-trace to `true` value -->";
                        if ($advanced) {
                                if ($entry['object']) $object_desc = get_class($entry['object']) . "-" . $entry['object']->id;
                                else $object_desc = "N/A";
                                if (count($entry['args'])) {
                                        $args_desc = AfwStringHelper::afw_export($entry['args'], true);
                                } else {
                                        $args_desc = "()";
                                }
                        }
                        $message .= "<tr class='btrace $odd'>";
                        $message .= "<td>" . $entry['function'] . "</td>";
                        $message .= "<td>" . $entry['file'] . "</td>";
                        $message .= "<td>" . $entry['line'] . "</td>";
                        $message .= "<td>" . $object_desc . "</td>";
                        $message .= "<td class='params'>" . $args_desc . "</td>";
                        $message .= "</tr>";
                        if (!$odd) $odd                = "odd";
                        else      $odd                = "";
                }
                $message .= "</table>";

                return $message;
        }



        public static function resetIndexesToDataArray($arr)
        {
                $data_arr = array();
                foreach ($arr as $index => $row) {
                        $data_arr[] = $row;
                }
                return $data_arr;
        }

        /**
         * @param AFWObject $obj
         * 
         */

        public static function showHelpPicture($obj, $currentStep, $modulo = 2)
        {
                $id = $obj->id;
                $ii = date("ss");
                $num = $ii % $modulo;
                if (!$currentStep) $currentStep = 1;
                $helppic = $obj->getMyTable() . "_" . $currentStep . "_" . $num;
                $moduleCode = $obj->getMyModule();
                $picFile = "pic/$helppic.png";
                $file_dir_name = dirname(__FILE__);
                $picture_path = "$file_dir_name/../../../$moduleCode/" . $picFile;
                $showAlways = false; // put true to debugg why picture doesn't appear
                $picture_help_html = "<img class='helppic pic$id' src='$picFile'>";
                if (file_exists($picture_path) or $showAlways) {
                        return [$picture_help_html, ""];
                } else return ["", $picture_help_html];
        }


        public static function genereBulles($arr_bulles)
        {
                $pre_html = "";
                $bulle_num = 1;
                foreach ($arr_bulles as $bulle) {
                        $pre_html .= "+$bulle_num+ $bulle\n";
                        $bulle_num++;
                }

                return $pre_html;
        }


        public static function genereAccordion($html, $title, $div_id = "")
        {
                if (!$div_id) $div_id = "accord_" . date("YmdHis");
                return
                        "<div class='hzm_label object_status_ok greentitle expand collapsed' data-toggle='collapse' data-target='#$div_id'>
                        $title   
                </div>
                <div id='$div_id' class='hzm_wd4 collapse hzm_minibox_body' aria-expanded='true' style=''> 
                        $html
                </div>";
        }

        public static function genereAccordionForLogArray($arr_logs, $title, $div_id = "")
        {
                return self::genereAccordion(implode("<br>\n", $arr_logs), $title, $div_id);
        }


        public static function getHtmlMethodsButtons($obj, $pbm_arr, $lang, $action_lourde = true, $isAdmin = false)
        {
                $html_buttons_spec_methods = "";

                foreach ($pbm_arr as $pbm_code => $pbm_item) {
                        if (!$pbm_item["HIDE"]) {
                                $action_lourde = true;
                                $html_buttons_spec_methods .= self::showHtmlPublicMethodButton($obj, $pbm_code, $pbm_item, $lang, $action_lourde, $isAdmin, "bis");
                        }
                }

                return $html_buttons_spec_methods;
        }


        /**
         * @param AFWObject $obj
         */

        public static function showHtmlOfStatusChangeApiButton(
                $obj,
                $api,
                $method_name,
                $color,
                $swal_title,
                $swal_text,
                $method_icon = 'run',
                $lang = 'ar',
                $action_lourde = true,
                $isAdmin = false,
                $ver = "",
                $max_title = 38,
                $method_log = ''
        ) {
                $swal_title = str_replace('"', "'", $swal_title);
                $swal_text = str_replace('"', "'", $swal_text);
                if (!$obj) return "NO-OBJECT-FOR-API-BUTTON";
                $id = $obj->id;
                $afwClass = get_class($obj);
                $module = $obj->getMyModule();
                // title / tooltip or help
                $btn_title = $obj->translate($method_name, $lang);
                $method_tooltip = $obj->translate($method_name . ".tooltip", $lang);
                if (($btn_title == $method_name) or (!$btn_title)) $btn_title = "";
                if (!$btn_title) {
                        $btn_title = AfwStringHelper::methodToTitle($method_name);
                }
                $btn_title_original = $btn_title;
                if (strlen($btn_title) > $max_title) $btn_title = AfwStringHelper::truncateArabicJomla($btn_title, $max_title);

                $method_help = $btn_title_original . " : " . $method_tooltip;
                if ($isAdmin) $method_help .= " [$method_name]";

                return "        <button name=\"api-$method_name\" id=\"api-$method_name\" data-toggle=\"tooltip\" data-placement=\"bottom\" 
                                        module=\"$module\" afwclass=\"$afwClass\" oid=\"$id\" ttl=\"$swal_title\" txt=\"$swal_text\"
                                        type=\"submit\" 
                                        class=\"bf bf-$color $afwClass $action_lourde api-method $api hzm-$method_name theme-new\">                                
                                        <div class=\"hzm-width-100 hzm-text-center hzm_margin_bottom theme-new\">                                      
                                                <div class=\"hzm-vertical-align hzm-container-center hzm-api-$api hzm-otherlink hzm-otherlink-icon-container border-primary theme-new\">                                        
                                                        <i class=\"hzm-container-center hzm-vertical-align-middle hzm-icon-$method_icon theme-new\"></i>
                                                </div>                                    
                                                <div class='pbm-tr'>$btn_title</div>
                                        </div>  
                                </button><!-- method $method_name status-log : $method_log -->
                                <script>
                                        $(document).ready(function(){
                                                
                                });
                                </script>";
        }

        /**
         * @param AFWObject $obj
         */

        public static function showHtmlPublicMethodButton($obj, $pbm_code, $pbm_item, $lang, $action_lourde = true, $isAdmin = false, $ver = "", $max_pbm_title = 38)
        {
                // global $next_color_arr;
                $cls = $obj ? $obj->getMyClass() : "";
                $theme = $pbm_item['THEME'];
                if (!$theme) $theme = "default";
                $condition = $pbm_item['CONDITION'];
                if ($condition) {
                        $show_pbm = $obj->$condition();
                        if ($show_pbm) $pbm_item['LOG'] .= " > $condition applied successfully";
                        else $pbm_item['LOG'] .= " > $condition not applied successfully";
                } else {
                        $pbm_item['LOG'] .= " No condition";
                        $show_pbm = true;
                }


                if ($action_lourde) $action_lourde = "action_lourde";
                else $action_lourde = "";
                if ($obj->editByStep and $pbm_item["STEP"] and (strtoupper($pbm_item["STEP"]) != "ALL") and ($obj->currentStep != $pbm_item["STEP"])) {
                        if ((!$pbm_item["STEP2"]) or ($obj->currentStep != $pbm_item["STEP2"])) {
                                if ((!$pbm_item["STEPS"]) or (!in_array($obj->currentStep, $pbm_item["STEPS"]))) {
                                        $show_pbm = false;
                                }
                        }
                }
                $last_color = "green";
                if ($show_pbm and $pbm_item["METHOD"]) {
                        // if(!$pbm_item["COLOR"]) $pbm_item["COLOR"] = $next_color_arr[$last_color];
                        $last_color = $pbm_item["COLOR"];
                        $method_name = $pbm_item["METHOD"];
                        $method_icon = $pbm_item["ICON"];
                        if (!$method_icon) $method_icon = "run";

                        // tooltip or help
                        $method_tooltip = $pbm_item["TOOLTIP_" . strtoupper($lang)];
                        if (!$method_tooltip) $method_tooltip = $pbm_item["HELP_" . strtoupper($lang)];
                        if (!$method_tooltip) $method_tooltip = $pbm_item["TOOLTIP"];
                        if (!$method_tooltip) $method_tooltip = $pbm_item["HELP"];

                        $method_log = $pbm_item["LOG"];
                        $pbm_item_tr = $pbm_item["LABEL_" . strtoupper($lang)];

                        // translation
                        $pbm_item_translation = $obj->translate($method_name, $lang);
                        if (($pbm_item_translation == $method_name) or (!$pbm_item_translation)) $pbm_item_translation = $pbm_item_tr;
                        if (!$pbm_item_translation) {
                                $pbm_item_translation = AfwStringHelper::methodToTitle($method_name);
                        }
                        $pbm_original_translation = $pbm_item_translation;
                        if (strlen($pbm_item_translation) > $max_pbm_title) $pbm_item_translation = AfwStringHelper::truncateArabicJomla($pbm_item_translation, $max_pbm_title);

                        $method_help = $pbm_original_translation . " : " . $method_tooltip;
                        if ($isAdmin) $method_help .= " [$method_name]";

                        $input_main_param_html = "";
                        if ($pbm_item["MAIN_PARAM"]) {
                                $obj->input_main_param = $pbm_item["DEFAULT"];
                                list($input_main_param_html,) = AfwInputHelper::text_input("pbmp$ver" . "_$pbm_code", $pbm_item["MAIN_PARAM"]["structure"], $obj->input_main_param, $obj, "<br>");
                        }

                        if ($theme == "default") {
                                return "$input_main_param_html \n 
                                <button name=\"submit-$pbm_code\" id=\"submit-$pbm_code\" data-toggle=\"tooltip\" data-placement=\"bottom\" type=\"submit\" class=\"bf bf-$last_color $cls $action_lourde new-specialmethod hzm-$method_name theme-new\">                                
                                        <div class=\"hzm-width-100 hzm-text-center hzm_margin_bottom theme-new\">                                      
                                                <div class=\"hzm-vertical-align hzm-container-center hzm-otherlink-$method_name hzm-otherlink hzm-otherlink-icon-container border-primary theme-new\">                                        
                                                        <i class=\"hzm-container-center hzm-vertical-align-middle hzm-icon-$method_icon theme-new\"></i>                                      
                                                </div>                                    
                                                <div class='pbm-tr'>$pbm_item_translation</div>
                                        </div>  
                                </button><!-- method $method_name status-log : $method_log -->
                                <script>
                                        $(document).ready(function(){
                                                $('#submit-$pbm_code').tooltip({
                                                placement: \"bottom\",
                                                title: \"$method_help\"
                                        });
                                });
                                </script>";
                        } else {
                                return "$input_main_param_html \n 
                                                <button name=\"submit-$pbm_code\" data-toggle=\"tooltip\" data-placement=\"left\" title=\"" . $method_tooltip . "\" id=\"submit-$pbm_code\" type=\"submit\" class=\"bf bf-$last_color $action_lourde hzm-specialmethod hzm-$method_name\">                                
                                                                <div class=\"hzm-width-100 hzm-text-center hzm_margin_bottom \">                                      
                                                                        <div class=\"hzm-vertical-align hzm-container-center hzm-otherlink-$method_name hzm-otherlink hzm-otherlink-icon-container only-border border-primary\">                                        
                                                                                <i class=\"hzm-container-center hzm-vertical-align-middle hzm-icon-$method_icon\"></i>                                      
                                                                        </div>                                    
                                                                </div>  
                                                                $pbm_item_translation                                
                                                </button><!-- method $method_name status-log : $method_log -->";
                        }
                } else {
                        $method_name0 = $pbm_item["METHOD"];
                        $method_name = $pbm_item["LOG-FOR-METHOD"];
                        $method_log = $pbm_item['LOG'];
                        return "<!-- method $method_name/$method_name0 disabled, reason-log : $method_log -->";
                }
        }

        public static function showSimpleAttributeMethodButton($obj, $pbm_code, $pbm_item, $lang, $action_lourde = true, $isAdmin = false)
        {
                global $next_color_arr;

                $show_pbm = true;
                if ($action_lourde) $action_lourde = "action_lourde";
                else $action_lourde = "";
                if ($obj->editByStep and $pbm_item["STEP"] and ($obj->currentStep != $pbm_item["STEP"]))  $show_pbm = false;
                $last_color = "green";
                if ($show_pbm) {
                        if (!$pbm_item["COLOR"]) $pbm_item["COLOR"] = $next_color_arr[$last_color];
                        $last_color = $pbm_item["COLOR"];
                        $method_name = $pbm_item["METHOD"];
                        $method_icon = $pbm_item["ICON"];
                        $method_tooltip = $pbm_item["TOOLTIP"];

                        if (!$method_icon) $method_icon = "run";
                        $pbm_item_translation = $obj->translate($method_name, $lang);
                        $pbm_item_help = $pbm_item["LABEL_" . strtoupper($lang)];
                        if (($pbm_item_translation == $method_name) or (!$pbm_item_translation)) $pbm_item_translation = $pbm_item_help;
                        $method_name_help = $method_tooltip;
                        if ($isAdmin) $method_name_help .= " [$method_name]";


                        return "<button name=\"submit-$pbm_code\" title=\"$method_name_help\" id=\"submit-$pbm_code\" type=\"submit\" class=\"bf bf-$last_color $action_lourde hzm-simplemethod hzm-$method_name\">                                                        
                                $pbm_item_translation                                
                                </button>";
                }
        }
        /**
         * @param AFWObject $obj
         */

        public static function showOtherLinkButton($obj, $other_link, $lang, $action_lourde = true, $isAdmin = false)
        {
                global $next_color_arr;

                $show_link = true;
                if ($obj->editByStep and $other_link["STEP"] and ($obj->currentStep != $other_link["STEP"]))  $show_link = false;
                $last_color = "green";
                if ($show_link) {
                        $bf_id = $other_link["BF-ID"];
                        $auth_type = $other_link["AUTH_TYPE"];
                        if (!$bf_id) $bf_id = 0;
                        if (!$other_link["COLOR"]) $other_link["COLOR"] = $next_color_arr[$last_color];
                        $last_color = $other_link["COLOR"];
                        $ol_url = $other_link["URL"];
                        $ol_code = $other_link["CODE"];
                        $lang_u = strtoupper($lang);
                        if ($lang == "ar") {
                                $ol_title = $other_link["TITLE_AR"];
                                if (!$ol_title) $ol_title = $obj->tm($other_link["TITLE"], "ar");
                        } else {
                                $ol_title = $other_link["TITLE_$lang_u"];
                                if (!$ol_title) $ol_title = $obj->tm($other_link["TITLE"], $lang);
                        }
                        $ol_icon = $other_link["ICON"];
                        if (!$ol_icon) $ol_icon = "link";


                        $ol_help = $other_link["HELP"];

                        return "<button name=\"link-$ol_code\" title=\"$ol_help\" id=\"link-$ol_code\" type=\"button\" class=\"bf bf-$bf_id hzm-otherlink hzm-$ol_code\">                                
                                <a class=\"auth-$auth_type\" href='$ol_url'>$ol_title</a> 
                                <div class=\"hzm-width-100 hzm-text-center hzm_margin_top \">                                      
                                        <div class=\"hzm-vertical-align hzm-container-center hzm-link-$ol_code hzm-otherlink hzm-otherlink-icon-container only-border border-primary\">                                        
                                                <i class=\"hzm-container-center hzm-vertical-align-middle hzm-icon-$ol_icon\"></i>                                      
                                        </div>                                    
                                </div>  
                                                        
                </button>";
                }
        }



        public static final function objToLIForTree(
                $object,
                $items_col,
                $feuille_col,
                $feuille_cond_method,
                $lang = 'ar',
                $tabs = "\t"
        ) {
                $feuilleObjs = $object->get($feuille_col);
                $detailObjs = $object->get($items_col);
                $html_tree = '';
                $disp_me = $object->getNodeDisplay($lang);

                $iconType = $object->getIconType();
                $full_id = $object->getFullId();
                if (count($detailObjs) + count($feuilleObjs) > 0) {
                        $html_tree .= "$tabs<li id=\"$full_id\" data-jstree='{\"type\" : \"$iconType\" }'>$disp_me\n";

                        $html_tree .= "$tabs\t<ul>\n";
                        $firstNode = true;
                        $valid_SubFolderCount = 0;
                        foreach ($detailObjs as $detailObjId => $detailObj) {
                                if (
                                        $items_col != $feuille_col or
                                        $feuille_cond_method and !$detailObj->$feuille_cond_method()
                                ) {
                                        $html_tree .= self::objToLIForTree(
                                                $detailObj,
                                                $items_col,
                                                $feuille_col,
                                                $feuille_cond_method,
                                                $lang,
                                                $tabs . "\t"
                                        );
                                        $firstNode = false;
                                        $valid_SubFolderCount++;
                                }
                        }
                        $valid_feuillesCount = 0;
                        foreach ($feuilleObjs as $feuilleObjId => $feuilleObj) {
                                if (
                                        !$feuille_cond_method or $feuilleObj->$feuille_cond_method()
                                ) {
                                        // if(!$firstNode) $js_array .= ", ";
                                        $feuilleFullId = $feuilleObj->getFullId();

                                        $disp_feuille = $feuilleObj->getNodeDisplay($lang);
                                        $iconType = $feuilleObj->getIconType();
                                        $html_tree .= "<li id=\"$feuilleFullId\" data-jstree='{\"type\" : \"$iconType\" }'>$disp_feuille</li>";
                                        $firstNode = false;
                                        $valid_feuillesCount++;
                                }
                        }

                        if (!$valid_SubFolderCount and !$valid_feuillesCount) {
                                $html_tree .= '<li>no valid feuille<li>';
                        }
                        $html_tree .= "$tabs\t</ul>\n";
                        $html_tree .= "$tabs</li>";
                } else {
                        $html_tree .= "$tabs<li id=\"$full_id\" data-jstree='{\"type\" : \"$iconType\" }'>$disp_me\n";
                        $html_tree .= "$tabs</li>";
                        //$html_tree .= "$tabs : no child found for me [$disp_me] with items_col=$items_col, feuille_col=$feuille_col\n";
                }

                return $html_tree;
        }


        public static final function phpArrayToJsArray($phpArr)
        {
                return "['" . implode("','", $phpArr) . "']";
        }

        public static final function toJsArray(
                $object,
                $items_col,
                $feuille_col,
                $feuille_cond_method,
                $lang = 'ar',
                $tabs = "\t"
        ) {
                $feuilleObjs = $object->get($feuille_col);
                $detailObjs = $object->get($items_col);
                $js_array = '';
                $disp_me = $object->getShortDisplay($lang);

                if (count($detailObjs) + count($feuilleObjs) > 0) {
                        $js_array .= "$tabs{\tname: '$disp_me'";

                        $js_array .= ",\n$tabs\tchildren: [\n";
                        $firstNode = true;
                        $valid_SubFolderCount = 0;
                        foreach ($detailObjs as $detailObjId => $detailObj) {
                                if (
                                        $items_col != $feuille_col or
                                        $feuille_cond_method and !$detailObj->$feuille_cond_method()
                                ) {
                                        if (!$firstNode) {
                                                $js_array .= ', ';
                                        }
                                        $js_array .= self::toJsArray(
                                                $detailObj,
                                                $items_col,
                                                $feuille_col,
                                                $feuille_cond_method,
                                                $lang,
                                                $tabs . "\t"
                                        );
                                        $firstNode = false;
                                        $valid_SubFolderCount++;
                                }
                        }
                        $valid_feuillesCount = 0;
                        foreach ($feuilleObjs as $feuilleObjId => $feuilleObj) {
                                if (
                                        !$feuille_cond_method or $feuilleObj->$feuille_cond_method()
                                ) {
                                        if (!$firstNode) {
                                                $js_array .= ', ';
                                        }
                                        $disp_feuille = $feuilleObj->getShortDisplay($lang);
                                        $js_array .= "{ name: '$disp_feuille' }";
                                        $firstNode = false;
                                        $valid_feuillesCount++;
                                }
                        }

                        if (!$valid_SubFolderCount and !$valid_feuillesCount) {
                                $js_array .= "{ name: 'no valid feuille' }";
                        }

                        $js_array .= "$tabs]\n";
                        $js_array .= "$tabs\n";
                        $js_array .= " }\n\n\n";
                } else {
                        //die(" for me [$disp_me] no details neither feuilles for toJsArray(items_col=$items_col, feuille_col=$feuille_col, feuille_cond_method=$feuille_cond_method)");
                        $js_array .= "$tabs{\tname: '$disp_me', children: [{name: 'no child found for me [$disp_me] with items_col=$items_col, feuille_col=$feuille_col'},] }\n";
                }

                return $js_array;
        }

        public static final function showNotification($err, $war, $inf)
        {
                $html_notification  = "";

                if ($err or $war or $inf) $html_notification .=  "<div class='notification_message_container'>";

                if ($err) {

                        $html_notification .=  "
                                <div class='alert messages messages--error alert-dismissable' role='alert' >
                                <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                                <div class='swal2-hzm swal2-icon swal2-error swal2-icon-show' style='display: flex;'>
                                        <span class='swal2-x-mark'>
                                                <span class='swal2-x-mark-line-left'></span>
                                                <span class='swal2-x-mark-line-right'></span>
                                        </span>
                                </div>
                                $err
                                </div>\n";
                }

                if ($war) {

                        $html_notification .=  "
                                <div class='alert messages messages--warning alert-dismissable' role='alert' >
                                <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                                <!--<div class='swal2-hzm swal2-icon swal2-warning swal2-icon-show' style='display: flex;color: orange;border-color: transparent !important;'>
                                        <div class='swal2-icon-content'>!</div>
                                </div>-->
                                $war
                                </div>\n";
                }

                if ($inf) {
                        $html_notification .=  "
                                <div class='alert messages messages--status alert-dismissable' role='alert' >
                                <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                                <!--<div class='swal2-hzm swal2-icon swal2-info swal2-icon-show' style='display: flex;'>
                                        <div class='swal2-icon-content'>i</div>
                                </div>-->
                                $inf
                                </div>\n";
                }

                if ($err or $war or $inf) $html_notification .=  "</div>";


                return $html_notification;
        }

        public static function decodeHzmTemplate($tpl_content, $data_tokens, $lang)
        {
                $token_arr = $data_tokens;

                $token_arr["[lang]"] = $lang;

                $text_to_decode = $tpl_content;

                foreach ($token_arr as $token => $val_token) {
                        if ((!isset($val_token)) or ($val_token === null)) {
                                $val_token = "";
                        }
                        if (is_array($val_token)) {
                                throw new AfwRuntimeException("Any token of tpl should be a string, found token [$token] value is : " . var_export($val_token, true));
                        }
                        $text_to_decode = str_replace("[" . $token . "]", $val_token, $text_to_decode);
                }

                return $text_to_decode;
        }


        public static function showUsingHzmTemplate($html_template_file, $data_tokens, $lang)
        {
                ob_start();
                if (file_exists($html_template_file)) {
                        include($html_template_file);
                        $tpl_content = ob_get_clean();

                        return self::decodeHzmTemplate($tpl_content, $data_tokens, $lang);
                } else {
                        return "showUsingHzmTemplate : file $html_template_file not found";
                }
        }

        public static function getLightDownloadUrl($file_path, $extension, $icon_size = "")
        {
                return "<a target='_download' href='$file_path' class='download-icon $icon_size download-$extension fright' title='[title]'>&nbsp;</a>";
        }

        public static function getTooltipDownloadUrl($file_path, $extension)
        {
                return "<a target='_download' href='$file_path' class='tooltip download-icon download-$extension fright' data-toggle='tooltip' data-placement='top' title='[title]'>&nbsp;</a>";
        }


        public static function importanceCss($object, $fieldname, $desc)
        {
                $uk_arr = $object->UNIQUE_KEY ? $object->UNIQUE_KEY : [];
                if (is_array($desc)) {
                        $importance = strtolower($desc["IMPORTANT"]);
                        if (!$importance) $importance = "in";
                        if (($importance == "in") and in_array($fieldname, $uk_arr)) $importance = "high";
                        elseif (($importance == "in") and ($desc['TYPE'] == 'PK' or $desc['PILLAR'] or $desc['POLE'])) $importance = "normal";
                        elseif ($importance == "in") $importance = "small";
                } else $importance = "in";

                if (($fieldname == "عرض") or ($fieldname == "view") or ($fieldname == "display")) $importance = "small";
                if (($fieldname == "تعديل") or ($fieldname == "edit") or ($fieldname == "update")) $importance = "high";

                return $importance;
        }


        /*
        
        public static function addActionMatrixToRowData($obj)
        {
                // action columns is based on all actions
                $actions_tpl_arr = AfwUmsPagHelper::getAllActions($obj);
                // but each row can have different previleges on actions object why we retrieve the matrix
                $actions_tpl_matrix = AfwUmsPagHelper::getActionsMatrix($liste_obj);
                
                foreach($actions_tpl_arr as $action_item => $action_item_props)
                {
                        if($actions_tpl_matrix[$id][$action_item]) $action_item_props = $actions_tpl_matrix[$id][$action_item];
                        
                        $frameworkAction = $action_item_props["framework_action"];
                        $bf_code = $action_item_props["bf_code"];
                        $bf_system = $action_item_props["bf_system"];
                        if(!$frameworkAction) $frameworkAction = $action_item;
                        
                        $page = $action_item_props["page"];
                        if($page)
                        {
                                $page_params = $action_item_props["params"];
                        }
                        else
                        {
                                $link = $action_item_props["link"];
                                $link = str_replace("[id]", $id, $link);
                                $link = str_replace("[popup_t]", $popup_t, $link);
                        }
                        
                        
                        if($action_item_props["target"]) $target_action = "target='".$action_item_props["target"]."'";
                        else $target_action = $target;
                        
                        $img = $action_item_props["img"];
                        
                        $ajax_class = $action_item_props["ajax_class"];
                        
                        $frameworkAction_tr = $liste_obj[$id]->translateOperator(strtoupper("_".$action_item),$lang);
                        $btnclass = $action_item_props["btnclass"]; 
                        $canOnMe = false;
                        
                        $can = $can_action_arr[$action_item];
                        
                        $cant_do_action_log = "";
                        
                        if(!$can) $cant_do_action_log .= $cant_do_action_log_arr[$action_item];
                        
                        if($can) 
                        {
                                if($objme) 
                                {
                                        if(($frameworkAction=="display") and (AfwFrameworkHelper::displayInEditMode($cl)))
                                        {
                                                list($canOnMe, $edit_not_allowed_reason) = $liste_obj[$id]->userCanEditMe($objme);
                                        }
                                        elseif(($frameworkAction=="edit") or ($frameworkAction=="update")) 
                                        {
                                                //die("frameworkAction=$frameworkAction");
                                                list($canOnMe, $edit_not_allowed_reason) = $liste_obj[$id]->userCanEditMe($objme);
                                        }
                                        elseif(($frameworkAction=="delete")) 
                                        {
                                                //die("frameworkAction=$frameworkAction");
                                                $canOnMe = ($liste_obj[$id]->userCanDeleteMe($objme,$notify=false)>0);
                                        }
                                        else $canOnMe = AfwUmsPagHelper::userCanDoOperationOnObject($liste_obj[$id],$objme,$frameworkAction);
                                }
                                else $canOnMe = false;
                                
                        }
                        if($can and (!$canOnMe))
                        {
                                if($cant_do_action_log) $cant_do_action_log .= "\n<br>";
                                $cant_do_action_log .= $liste_obj[$id]->user_have_access_log;
                                //die("case can and ! canOnMe exists : ".$liste_obj[$id]. " log = $cant_do_action_log");
                        }
                        // $canOnMe = true;
                        // $can = true;
                        if($can and $canOnMe)
                        {
                        $accept HimSelf = $liste_obj[$id]->accept HimSelf($frameworkAction);
                        if($accept HimSelf)
                        {

                                
                                if($btnclass) 
                                {
                ?>
                                        <td><a class="btn-micro <?=$btnclass?>" <?=$target_action?> href="<?="main.php"."?".$link ?>"><?=$frameworkAction_tr?></a></td>
                <?
                                }
                                elseif($img) 
                                {
                                        $tooltip = "";
                                        $icon_help = $action_item_props["help"];
                                        if($icon_help) $tooltip = "data-toggle='tooltip' data-placement='bottom' title='$icon_help' data-original-title=' - Tooltip on bottom' class='red-tooltip'";
                                        
                                        if($ajax_class)
                                        {
                ?>
                                        <td><a href="#" id="<?=$id?>" cl="<?=$cl?>" md="<?=$currmod?>" lb l="<?=$lbl?>" class="<?=$ajax_class?>">
                                                <img src="<?=$img?>" width="24" heigth="24" <?=$tooltip?>> 
                                        </a>
                                        </td>
                <?                      
                                        }
                                        else
                                        {
                ?>
                                        <td><a <?=$target_action?> href="<?="main.php"."?".$link ?>"  >
                                                <img src="<?=$img?>" width="24" heigth="24" <?=$tooltip?>> 
                                        </a>
                                        </td>
                <?
                                        
                                        }
                                }
                                else echo "<td>no_image_for_mode_$frameworkAction</td>";
                                }
                                else
                                {
                                if($objme and $objme->isAdmin()) 
                                {
                                        $tooltip_text = "locked him self on $frameworkAction";
                                        $tooltip = "data-toggle='tooltip' data-placement='bottom' title='$tooltip_text' data-original-title=' - Tooltip on bottom' class='red-tooltip'";
                                } 
                                ?>
                                <td><img src="<?=$images['locked_him_self']?>" width="24" heigth="24" <?=$tooltip?>></td>
                                <?
                                }      
                        
                        }
                        elseif($can and (!$canOnMe))
                        {
                                if($objme and $objme->isAdmin()) $tooltip = "data-toggle='tooltip' data-placement='bottom' title='You have not authorization to do $frameworkAction on object record : [$action_item -> $cant_do_action_log]' data-original-title=' - Tooltip on bottom' class='red-tooltip'";
        ?>
                                <td><img src="<?=$images['locked_on_me']?>" width="24" heigth="24" <?=$tooltip?>></td>
        <?
                        }
                        else
                        {
                                if($objme and $objme->isAdmin()) $tooltip = "data-toggle='tooltip' data-placement='bottom' title='You have not authorization to do $frameworkAction on object entity : [$action_item -> $cant_do_action_log]' data-original-title=' - Tooltip on bottom' class='red-tooltip'";
        ?>
                                <td><img src="<?=$images['locked']?>" width="24" heigth="24" <?=$tooltip?> alt="<?=""?>"></td>
        <?
                        }                                           
                }
        }
        */
}
