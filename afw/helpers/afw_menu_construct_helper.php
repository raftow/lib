<?php
class AfwMenuConstructHelper
{
    private static $current_arole = 0;

    public static function getLangues($lang, $module_languages)
    {
        $LANG_NAMES = [];
        $LANG_NAMES["ar"]["ar"] = "عربي";
        $LANG_NAMES["ar"]["fr"] = "فرنسي";
        $LANG_NAMES["ar"]["en"] = "انجليزي";
        $LANG_NAMES["fr"]["ar"] = "Arabe";
        $LANG_NAMES["fr"]["fr"] = "Francais";
        $LANG_NAMES["fr"]["en"] = "Anglais";
        $LANG_NAMES["en"]["ar"] = "Arabic";
        $LANG_NAMES["en"]["fr"] = "French";
        $LANG_NAMES["en"]["en"] = "English";


        $uri = AfwStringHelper::clean_my_url($_SERVER["REQUEST_URI"]);
        $get_lang = $_GET["lang"];
        if (AfwStringHelper::stringEndsWith($uri, "main.php")) $uri = str_replace("main.php", "index.php?home=1", $uri);
        if (AfwStringHelper::stringEndsWith($uri, ".php")) $uri = str_replace(".php", ".php?abc=1", $uri);
        if (AfwStringHelper::stringEndsWith($uri, "/")) $uri .= "?abc=1";
        if ((!$get_lang) or (strpos($uri, "lang=$get_lang") === false)) {
            $get_lang = $lang;
            if (!$uri) $uri = "index.php?x=1";
            $uri = $uri . "&lang=$get_lang";
        }
        $uri_arr = [];
        $uri_arr["ar"] = "";
        $uri_arr["fr"] = "";
        $uri_arr["en"] = "";


        $active_lang_count = 0;

        if (($module_languages["ar"]) and ($lang != "ar")) {
            $uri_arr["ar"] = str_replace("lang=$get_lang", "lang=ar", $uri);
            $active_lang_count++;
        }

        if (($module_languages["fr"]) and ($lang != "fr")) {
            $uri_arr["fr"] = str_replace("lang=$get_lang", "lang=fr", $uri);
            $active_lang_count++;
        }

        if (($module_languages["en"]) and ($lang != "en")) {
            $uri_arr["en"] = str_replace("lang=$get_lang", "lang=en", $uri);
            $active_lang_count++;
        }

        return [$LANG_NAMES, $uri_arr, $active_lang_count];
    }

    public static function genereControllerMenu($menu_template, $module, $controllerObj, $lang, $module_languages, $role)
    {
        if(!$menu_template) $menu_template = AfwSession::currentMenuTemplate();
        $tpl_path = "";
        
        if($controllerObj) $userAuthenticated = $controllerObj->checkLoggedIn();
        else $userAuthenticated = null;

        $file_helper_dir_name = dirname(__FILE__); 
        $html_hzm_menu = "";
        self::$current_arole = $role;
        if(!self::$current_arole and $_REQUEST["role"])
        {
            self::$current_arole = $_REQUEST["role"];
        }

        list($LANG_NAMES, $uri_arr, $active_lang_count) = self::getLangues($lang, $module_languages);

        $enable_language_switch = AfwSession::config("enable_language_switch", true);
        if (($active_lang_count > 0) and $enable_language_switch) {
            foreach ($uri_arr as $lang_code => $uri_item) {
                $menu_item_title = $LANG_NAMES[$lang][$lang_code];
                $lang_menu_tokens = [];
                $lang_menu_tokens["menu_id"] = "lang";
                $lang_menu_tokens["li_class"] = "lang-menu lang-$lang_code";
                $lang_menu_tokens["menu_page"] = $uri_item;
                $lang_menu_tokens["menu_icon"] = "globe";
                $lang_menu_tokens["menu_item_css"] = "";
                $lang_menu_tokens["menu_title"] = $menu_item_title;
                


                if ($uri_item) 
                {
                    $tpl_path = AfwHtmlHelper::hzmTplPath();
                    $li_template_file = "$tpl_path/$menu_template"."_menu_li_tpl.php";
                    $html_hzm_menu .= "\n" . AfwHtmlHelper::showUsingHzmTemplate($li_template_file, $lang_menu_tokens, $lang);                    
                }
            }
            // $menu_color = $menu_next_color[$menu_color];
        }

        $i = 0;
        $arrMenu = include("$file_helper_dir_name/../../../$module/front_main_menu_arr.php");
        foreach($arrMenu as $mi => $rowMenu)
        {
            if($userAuthenticated or $rowMenu["guest"])
            {
                $menu_folder = [];
                $menu_folder["color_class"] = "menu-color";
                $menu_folder["id"] = $mi;
                $menu_folder["sub-folders"] = [];
                $menu_folder["items"] = [];
                $menu_folder["showme"] = true;
                $menu_folder["menu_name"] = $rowMenu["methodTitle"];
                $menu_folder["page"] = "i.php?cn=".$rowMenu["controller"]."&mt=".$rowMenu["methodName"];
                $menu_folder["css"] = ($methodName === $rowMenu["methodName"]) ? "active" : "";
                $menu_folder["icon"] = $rowMenu["icon"];
                $menu_folder_i_html = AfwFrontMenu::genereFrontMenuItem($tpl_path, $menu_template, $menu_folder, $module, $lang, $r, true, $iamAdmin);
                $html_hzm_menu .= $menu_folder_i_html;
            }
        }

        return $html_hzm_menu;
    }

    public static function genereMenu($menu_template, $module, $objme, $lang, $module_languages, $r)
    {
        $menu_template = AfwSession::currentMenuTemplate();
        $tpl_path = "";
        if (!$module) throw new AfwRuntimeException("module should be defined for AfwMenuConstructHelper::genereMenu");
        // if (!$objme) throw new AfwRuntimeException("objme should be defined for AfwMenuConstructHelper::genereMenu");
        if (!$lang) throw new AfwRuntimeException("lang should be defined for AfwMenuConstructHelper::genereMenu");
        if (!$module_languages) throw new AfwRuntimeException("module_languages should be defined for AfwMenuConstructHelper::genereMenu");

        // $me = $objme->id;
        // if (is_object($objme) or (!$me)) throw new AfwRuntimeException("objme should have id for AfwMenuConstructHelper::geneMenu");

        $file_helper_dir_name = dirname(__FILE__); 
        $html_hzm_menu = "";
        self::$current_arole = $r;
        if(!self::$current_arole and $_REQUEST["r"])
        {
            self::$current_arole = $_REQUEST["r"];
        }
        
        
        // $right_menu = array();
        // if ($objme) $my_firstname = $objme->valFirstname();
        // $my_account_title = AfwLanguageHelper::translateKeyword("MYACCOUNT", $lang);
        // $my_home = AfwLanguageHelper::translateKeyword("HOME", $lang);
        // if ($objme) $right_menu[] = array('href' => "index.php", 'css' => "home", 'title' => $my_home);
        // if ($objme and $objme->isAdmin()) $right_menu[] = array('href' => "data_admin.php",'css' => "data", 'title' => AfwLanguageHelper::translateKeyword("DATA-ADMIN", $lang));
        // if ($objme and $objme->isAdmin() and $PAG) $right_menu[] = array('href' => "panel_analyst.php", 'css' => "analyst", 'title' => AfwLanguageHelper::translateKeyword("ANALYST", $lang));

        /*
        if ($my_account_page) {
            $my_account_page = str_replace("[ME]", $me, $my_account_page);
            $my_account_page = str_replace("[module]", $module, $my_account_page);
            // $my_account_page = str_replace("[EMPL]", $my_employee_id, $my_account_page);
            // $my_account_page = str_replace("[SEMPL]", $sempl_id, $my_account_page);
        }*/

        // if($me) $my_account_page = "main.php?Main_Page=afw_mode_display.php&cl=Auser&id=$me&currmod=ums&no_my_account_page_in_mod=$module";
        

        list($LANG_NAMES, $uri_arr, $active_lang_count) = self::getLangues($lang, $module_languages);
        
        /*
        $my_files = AfwLanguageHelper::translateKeyword("MY-FILES", $lang);
        $right_menu[] = array('href' => "afw_my_files.php?x=1", 'css' => "file", 'title' => "$my_files");

        $my_files = AfwLanguageHelper::translateKeyword("EDIT-MY-FILES", $lang);

        $codeme = substr(md5("code" . $me), 0, 8);
        $right_menu[] = array('href' => "afw_edit_my_files.php?x=$me&y=$codeme", 'css' => "files-o", 'title' => "$my_files");

        if ($my_account_page)
            $right_menu[] = array('href' => $my_account_page, 'css' => "user", 'title' => "$my_account_title ($my_firstname)");
        else
            $right_menu[] = array('href' => "#", 'css' => "myprofile", 'title' => AfwLanguageHelper::translateKeyword("SIGN-UP", $lang));
        */

        //$right_menu[] = array('href' => "#",'css' => "mobile", 'title' => AfwLanguageHelper::translateKeyword("CONTACT_US", $lang));

        //$right_menu[] = array('href' => $login_out_page, 'css' => $login_out_css, 'title' => $login_out_title);

        // $menu_color = "skyblue";
        // $menu_next_color = array("skyblue" => "seeblue", "seeblue" => "skyblue");

        // if we want to customize menu colors for a specific module
        // include "$file_hzm_dir_name/../$module/menu_colors.php";



        


        if ($objme) {
            include "$file_helper_dir_name/../../../ums/module_options.php";
            include "$file_helper_dir_name/../../../$module/special_module_options.php";
        }
        $enable_language_switch = AfwSession::config("enable_language_switch", true);
        if (($active_lang_count > 0) and $enable_language_switch) {
            foreach ($uri_arr as $lang_code => $uri_item) {
                $menu_item_title = $LANG_NAMES[$lang][$lang_code];
                $lang_menu_tokens = [];
                $lang_menu_tokens["menu_id"] = "lang";
                $lang_menu_tokens["li_class"] = "lang-menu lang-$lang_code";
                $lang_menu_tokens["menu_page"] = $uri_item;
                $lang_menu_tokens["menu_icon"] = "globe";
                $lang_menu_tokens["menu_item_css"] = "";
                $lang_menu_tokens["menu_title"] = $menu_item_title;
                


                if ($uri_item) 
                {
                    $tpl_path = AfwHtmlHelper::hzmTplPath();
                    $li_template_file = "$tpl_path/$menu_template"."_menu_li_tpl.php";
                    $html_hzm_menu .= "\n" . AfwHtmlHelper::showUsingHzmTemplate($li_template_file, $lang_menu_tokens, $lang);                    
                }
            }
            //$menu_color = $menu_next_color[$menu_color];
        }

        if($objme and ($objme instanceof Auser))
        {
            $iamAdmin = $objme->isAdmin();

            $application_id = AfwSession::config("application_id", 0);
            $no_cache_use_for_ums = AfwSession::config("no-cache-use-for-ums", false);
            if (!$application_id) {
                throw new AfwRuntimeException("HZM Error : application_id should be defined in application_config.php file");
            } else {
                $me_id = $objme->id;

                if (!$no_cache_use_for_ums) {
                    list($cache_found, $quick_links_arr, $mau_info, $menu, $user_info, $user_cache_file_path) = AfwFrontMenu::loadUmsCacheForUser($me_id, $lang);
                } else {
                    $cache_found = false;
                    $quick_links_arr = null;
                    $mau_info = null;
                    $menu = null;
                    $user_cache_file_path = null;
                }

                if (!$no_cache_use_for_ums and $cache_found) // ncu = get option to say "no cache use" to retrieve roles and menus (ums)
                {
                    $application_code = $mau_info["m$application_id"]["code"];
                    $menu_folders_arr = $menu[$application_code][$lang];

                    if (!$menu_folders_arr) AfwSession::pushWarning("System cache X <!-- $file_cache --> gived application_code=[$application_code] for application id [$application_id] and and no menu for this user for this application code");
                } elseif (!$no_cache_use_for_ums) AfwSession::pushWarning("System need cache optimisation file for user $me_id <!-- file not found $file_cache -->");
                else AfwSession::pushWarning("System cache optimisation disabled");

                if (!$menu_folders_arr) $menu_folders_arr = $objme->getMenuFor($application_id, $lang);

                //die(var_export($menu_folders_arr,true)); 
                //die("objme->getMenuFor($application_id , $lang) = ".var_export($menu_folders_arr,true));
                $i = 0;
                //throw new AfwRuntimeException("objme->getMenuFor($application_id,$lang) = ".var_export($menu_arr,true));
                foreach ($menu_folders_arr as $menu_folder_id => $menu_folder_i) {
                    if (($iamAdmin) or (!$menu_folder_i["need_admin"])) {
                        $menu_color = $menu_next_color[$menu_color];
                        $menu_folder_i["color_class"] = $menu_color;
                        $menu_folder_i_html = AfwFrontMenu::genereFrontMenuItem($tpl_path, $menu_template, $menu_folder_i, $module, $lang, $r, true, $iamAdmin);
                        // if($menu_folder_id=="-1") die($menu_folder_i_html);
                        // if($menu_folder_i_html) die("menu[$menu_folder_id] => ".$menu_folder_i_html." item => ".var_export($menu_folder_i,true));
                        $html_hzm_menu .= $menu_folder_i_html;
                    }
                }
            }


            /*if (false) {
                $html_hzm_menu .= "<l i class=\"front-small-item front-$menu_color-item\"><a href=\"main.php?Main_Page=fm.php&m=control\"><i class=\"fa fa-cogs\"></i>" . AfwLanguageHelper::translateKeyword("CONTROL", $lang) . "</a></l i>";
            }*/
        }
        
        return $html_hzm_menu;
    }
}
