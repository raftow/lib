<?php

  class AfwHtmlPageConstructHelper extends AfwHtmlHelper{

        private static $header_entry_counter = 0;
        private static $html = "";

        private static function initLanguage()
        {
                /*
                $lang = AfwLanguageHelper::getGlobalLanguage(); 
                $lang = AfwSession::getSessionVar("current_lang");
                if(!$lang) $lang = "ar";
                $lang = strtolower($lang);
                */
        }
        
        private static function addHtml($bloc, $title="")
        {
                if($title) self::$html .= "<!-- $title start -->\n";
                self::$html .= $bloc ."\n";
                if($title) self::$html .= "<!-- $title end --> \n\n\n";
                
                
        }

        private static function renderHtml()
        {
                $return = self::$html;
                self::$html = "";
                return $return;
        }


        private static function prepareNeededTranslations($lang)
        {
                if(!AfwSession::getSessionVar("user_id"))
                {
                        // $page_label = AfwLanguageHelper::translateKeyword("page", $lang);
                        // $record_label = AfwLanguageHelper::translateKeyword("record", $lang);
                        // $page_of_label = AfwLanguageHelper::translateKeyword("page_of", $lang); 
                        // $yes_label = AfwLanguageHelper::translateKeyword("Y", $lang);
                        // $no_label = AfwLanguageHelper::translateKeyword("N", $lang);
                        // $dkn_label = AfwLanguageHelper::translateKeyword("W", $lang);                         
                        
                          
                }
        }

        public static function renderPage($lang, 
                $header_template,
                $menu_template,
                $body_template,
                $footer_template,
                $arrRequest,
                $the_main_section_file,
                $need_ob = false,
                $options = [],
                $custom_scripts = [],
                $selected_menu = "", 
                $tpl_path = "", 
                $my_font = "front",
                $dir = "",
                $page_charset = "UTF-8",
                $docType = "<!DOCTYPE html>",
                $needUserObject = true,
                $my_afw_theme = "simple"
             )
        {
                // throw new AfwRuntimeException("why use page constructor ?");
                $role = $arrRequest["r"];
                //self::initLanguage();
                // die("dbg-002 rafik 20241119 lang = ".$lang);
                $current_module = AfwUrlManager::currentURIModule();
                $the_main_section_file_arr = explode("/",$the_main_section_file);
                $main_section_file_name = $the_main_section_file_arr[count($the_main_section_file_arr)-1];
                list($main_section_file_name, ) = explode(".",$main_section_file_name);
                // die("main_section_file_name=$main_section_file_name");
                $f3c = substr($main_section_file_name,0,3);
                $f7c = substr($main_section_file_name,0,7);
                list($pagecode, $log_explain) = AfwUrlManager::currentPageCode();
                // die("pagecode=$pagecode");
                $pagecode_splitted = implode(" ",explode("_",$pagecode));
                // to avoid infinite mirroring
                self::$header_entry_counter++; 
                if(self::$header_entry_counter>1)
                {
                        throw new AfwRuntimeException("HzmHeader called twice, in general this happen when you include main.php or afw main_page.php when you are inside body of MainPage");
                }

                if($needUserObject) $objme = AfwSession::getUserConnected();
                // die("rafik is upgrading MainPage librairy code=ADEF202511061552-04 ...");
                self::addHtml($docType);
                self::addHtml("<html>");
                

                if(!$dir)
                {
                        if($lang=="ar") $dir = "rtl";
                        else $dir = "ltr";
                }
                

                self::addHtml(AfwHtmlIncluderHelper::outputHeader($lang, $page_charset, $my_afw_theme, $options, $custom_scripts, $my_font));

                self::addHtml("<body class=\"hzm_body $f7c $pagecode_splitted\" dir=\"$dir\" >");

                // die("rafik is upgrading MainPage librairy code=ADEF202511061552-05 ...");
                if(($header_template!="no-header") and (!$options["disable_header"]) and (!AfwSession::hasOption("FULL_SCREEN")))
                {
                        $the_header = AfwHtmlMenuHelper::renderHeader($header_template, $lang, $role, $tpl_path, $selected_menu, $options);
                }

                if(($menu_template!="no-menu") and (!$options["disable_menu"]) and (!AfwSession::hasOption("FULL_SCREEN")))
                {
                        $the_menu = AfwHtmlMenuHelper::renderMenu($menu_template, $lang, $role, $tpl_path, $selected_menu, $options);
                }


                // the_menu and the_header each one can contain token with the other
                $tok_arr = []; 
                $tok_arr["the_header"] = "<!-- prebuilt with header template $header_template -->\n".$the_header;
                $tok_arr["the_menu"] = "<!-- prebuilt with menu template $menu_template -->\n".$the_menu;

                $the_menu = self::decodeHzmTemplate($the_menu,$tok_arr, $lang);
                // die("rafik is upgrading MainPage librairy code=ADEF202511061552-07 ...");
                $the_header = self::decodeHzmTemplate($the_header,$tok_arr, $lang);

                $the_header = "<!-- built with header template $header_template -->\n".$the_header;
                $the_menu = "<!-- built with menu template $menu_template -->\n".$the_menu;


                $the_section = "<!-- built with main section file $the_main_section_file need_ob=$need_ob -->\n";
                if($need_ob)
                {
                        $the_section .= self::obRenderMainSection($the_main_section_file, $arrRequest, $lang);
                }
                else
                {
                        $the_section .= self::renderMainSection($the_main_section_file, $arrRequest, $lang);
                }

                $notifications = [];
                die("rafik is upgrading MainPage librairy code=ADEF202511061552-08 ...");
                $notifications["warning"] = AfwHtmlNotificationHelper::getWarningNotification();
                // die("rafik is upgrading MainPage librairy code=ADEF202511061552-06 ...");
                $notifications["info"] = AfwHtmlNotificationHelper::getInfoNotification();
                $notifications["error"] = AfwHtmlNotificationHelper::getErrorNotification();
                $notifications["success"] = AfwHtmlNotificationHelper::getSuccessNotification();
                $notifications["slog"] = AfwHtmlNotificationHelper::getSLogNotification();

                $the_footer = "";
                $the_footer .= AfwHtmlFooterJsHelper::render($objme, $lang, $options);
                $the_footer .= "<!-- built with footer template $footer_template -->\n";
                $the_footer .= AfwHtmlFooterHelper::renderFooter($footer_template, $lang, $current_module, $tpl_path, $options);


                $notifications_html = "";
                foreach($notifications as $notification_code => $notification_html)
                {
                        if($notification_html) $notifications_html .= $notification_html;
                }

                // die("notifications_html=$notifications_html");
                
                $the_body = "<!-- built with _body template $body_template -->\n";
                $the_body .= self::constructBodyWithTemplate($body_template, 
                        $the_header, 
                        $the_menu, 
                        $the_section, 
                        $notifications_html,
                        $lang,
                        $current_module,
                        $pagecode,
                        $the_footer
                );

                self::addHtml($the_body, "the body");     
                self::addHtml("</body>");
                self::addHtml("</html>");
                
                
                return self::renderHtml();
        }

        public static function constructBodyWithTemplate($body_template,
                                $the_header, 
                                $the_menu, 
                                $the_section, 
                                $notifications,
                                $lang, 
                                $module,
                                $page_code,
                                $the_footer="",
                                $template_path="", 
                                $container_class="",
                                $main_section_class = "",
                                $notifications_class="notification_message_container")
        {
                $my_page_code = $page_code;
                $tpl_content = "";
                if(!$template_path) $template_path = self::hzmTplPath();
                $the_body_template_file = $template_path."/$body_template"."_body_tpl.php";
                if (file_exists($the_body_template_file)) 
                {
                        ob_start();
                        include($the_body_template_file);
                        $tpl_content = ob_get_clean();
                }
                if(!$container_class) $container_class = $module." $my_page_code";
                if(!$main_section_class) $main_section_class = "hzm_body $my_page_code";
                
                $section_tokens = [];
                $section_tokens["container-class"] = $container_class;
                $section_tokens["notifications-class"] = $notifications_class;
                $section_tokens["main-section-class"] = $main_section_class;
                $section_tokens["header"] = $the_header;
                $section_tokens["notifications"] = $notifications;
                if($notifications)
                {
                        $section_tokens["notifications_s"] = "";
                        $section_tokens["notifications_e"] = "";
                }
                else
                {
                        $section_tokens["notifications_s"] = "<!-- ";
                        $section_tokens["notifications_e"] = " -->";
                }
                
                $section_tokens["menu"] = $the_menu;
                $section_tokens["main-section"] = $the_section;
                $section_tokens["footer"] = $the_footer;

                return self::decodeHzmTemplate($tpl_content, $section_tokens, $lang);                
        }

        public static function renderMainSection($the_main_section_file, $arrRequest, $lang)
        {
                $html_output = self::executeMainSection($the_main_section_file, $arrRequest, $lang);
                return $html_output;
        } 

        public static function executeMainSection($the_main_section_file, $arrRequest, $lang)
        {
                if($the_main_section_file == "Controller::method")
                {
                        $methodName = $arrRequest["methodName"];
                        // unset($arrRequest["methodName"]);
                        $controllerObj = $arrRequest["controllerObj"];
                        unset($arrRequest["controllerObj"]);
                        return $controllerObj->$methodName($arrRequest);
                }
                else
                {
                        if(!file_exists($the_main_section_file))
                        {
                                throw new AfwRuntimeException("main section file [$the_main_section_file] not found");
                        }
                        foreach ($arrRequest as $col => $val) ${$col} = $val;
                        global $out_scr;
                        include($the_main_section_file);
                        return $out_scr;
                }
                
        }

        public static function obRenderMainSection($the_main_section_file, $arrRequest, $lang)
        {
                foreach ($arrRequest as $col => $val) ${$col} = $val;
                ob_start();
                self::executeMainSection($the_main_section_file, $arrRequest, $lang);
                $html_output = ob_get_clean();
                if(!$html_output)
                {
                        throw new AfwRuntimeException("file main section [$the_main_section_file] should return html it is called with arrRequest=<pre class='php'>".var_export($arrRequest,true)."</pre>");
                }
                return $html_output;
        } 



  } 
  
