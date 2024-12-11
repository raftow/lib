<?php
class AfwHtmlMenuHelper extends AfwHtmlHelper
{

    /**
     * @param Auser $objme
     */
    private static function prepareTokens(
        $lang,
        $objme,
        $role,
        $selected_menu = "",
        $options = [],
    ) {
        $login_template = AfwSession::currentLoginTemplate();
        $xmodule = AfwSession::getCurrentlyExecutedModule();
        $module = AfwUrlManager::currentURIModule();
        $login_out_css = "sign-in";
        $login_out_cl = "login $login_template";
        $login_page = "login-$login_template.php";

        $login_title = AfwLanguageHelper::translateKeyword("LOGIN", $lang);
        $logout_title = AfwLanguageHelper::translateKeyword("LOGOUT", $lang);  

        $login_out_css = "sign-out";
        $login_out_cl = "logout $login_template";
        $logout_page = "logout.php";

        if (!$options["system_date_format"]) $options["system_date_format"] = AfwSession::currentSystemDateFormat();

        if (($options["system_date_format"] != "greg") and ($lang == "ar")) {
            $hijri_date = AfwDateHelper::currentHijriDate("hdate_long", $DateSeparator = "/");
            // die("hijri_date=$hijri_date");
            $hijri_date_arr = explode($DateSeparator, $hijri_date);
            $display_date_year = $hijri_date_arr[3];
            $display_date_day = $hijri_date_arr[1];
            $display_date_month = $hijri_date_arr[2];
        } else {
            $display_date_day = date("d");
             $display_date_month = date("m"); 
             $display_date_year = date("Y");
        }
        $system_date = "Date : " . date("d/m/Y");

        $welcome_user = "";
        if ($objme) {
            $me_id = $objme->id;

            list($cache_found, $quick_links_arr, $mau_info, $menu, $user_info, $user_cache_file_path) = AfwFrontMenu::loadUmsCacheForUser($me_id, $lang);

            if ($cache_found) {
                $user_full = $user_info["user_full_name"][$lang];
                $user_dep = $user_info["user_department"][$lang];
                $user_job = $user_info["user_job"][$lang];
                if (!$user_full) AfwSession::pushWarning("System cache $user_cache_file_path gived user_full_name=[$user_full] for uid=$me_id");
            } else {
                AfwSession::pushWarning("System need cache optimisation file for user $me_id <!-- file not found $user_cache_file_path -->");

                $user_full = $objme->getShortDisplay($lang);
                $user_dep = $objme->getMyDepartmentName($lang);
                $user_job = $objme->getMyJob($lang);
            }

            $welcome = $objme->translate("welcome", $lang);
            $welcome_user = "<span> $welcome </span><br>$user_full<p>$user_job</p><p>$user_dep</p>";
            $user_picture = $objme->getUserPicture();
            $user_account_page = "user_account.php";
            $ord = $objme->id % 5;
            $user_bg_class = "ubg".$ord;
        }
        else
        {
            $user_picture = '<i class="hzm-container-center hzm-vertical-align-middle hzm-icon-std hzm-user-account fa-user"></i></a>';
            $user_account_page = "login.php";
            $user_bg_class = "ubg0";
        }

        $welcome_div = "";
        if ($objme) {
            $welcome_div = "<div class=\"title_company_user\">$welcome_user</div>";
        }
        $module_languages = AfwSession::config("languages", ["ar"=>true, "en"=>true]);
        $run_mode_var = AfwSession::config("run_mode_var", "run_mode");
        $run_mode = AfwSession::config($run_mode_var, "");
        if ($run_mode) $run_mode = "-" . $run_mode;

        
        

        $data_tokens = array();
        
        $data_tokens["user_picture"] = $user_picture;
        $enable_search_box = AfwSession::config("enable_search_box", false);
        if($enable_search_box)
        {
            $data_tokens["enable_search_box_s"] = "";
            $data_tokens["enable_search_box_e"] = "";
        }
        else
        {
            $data_tokens["enable_search_box_s"] = "<!-- ";
            $data_tokens["enable_search_box_e"] = " -->";
        }
        
        
        $data_tokens["user_bg_class"] = $user_bg_class;
        $data_tokens["user_account_page"] = $user_account_page;        
        $data_tokens["search_here"] = AfwLanguageHelper::translateKeyword("Search here", $lang);;        
        
        
        $data_tokens["run_mode"] = $run_mode;
        $data_tokens["welcome_div"] = $welcome_div;
        if(!$options["img-path"]) $options["img-path"] = "pic/";
        if(!$options["img-company-path"]) $options["img-company-path"] = "../external/pic";        
        $data_tokens["img-path"] = $options["img-path"];
        $data_tokens["img-company-path"] = $options["img-company-path"];
        
        if ($xmodule) $data_tokens["xmodule"] = "-" . $xmodule;
        else $data_tokens["xmodule"] = "";

        $data_tokens["calendar_class"] = "calendar_bloc_g";
        $data_tokens["display_date_year"] = $display_date_year;
        $data_tokens["display_date_day"] = $display_date_day;
        $data_tokens["display_date_month"] = $display_date_month;
        if ($options["no_menu"]) {
            $data_tokens["no_menu_s"] = "<!---";
            $data_tokens["no_menu_e"] = "--->";
        } else {
            $data_tokens["no_menu_s"] = "";
            $data_tokens["no_menu_e"] = "";
        }
        $data_tokens["banner_height"] = $options["banner_height"];
        $data_tokens["title_app_height"] = $options["title_app_height"];
        $data_tokens["logo_app_height"] = $options["logo_app_height"];
        $data_tokens["title_comp_height"] = $options["title_comp_height"];
        $data_tokens["logo_comp_height"] = $options["logo_comp_height"];

        $HEADER_PIC_HEIGHT = 80;

        if (!$data_tokens["logo_app_height"]) $data_tokens["logo_app_height"] = AfwSession::config("LOGO_APP_HEIGHT", $HEADER_PIC_HEIGHT);
        if (!$data_tokens["logo_app_margin_top"]) $data_tokens["logo_app_margin_top"] = AfwSession::config("LOGO_APP_MARGIN_TOP", 5);

        if (!$data_tokens["title_app_height"]) $data_tokens["title_app_height"] = AfwSession::config("TITLE_APP_HEIGHT", $HEADER_PIC_HEIGHT);
        if (!$data_tokens["title_app_margin_top"]) $data_tokens["title_app_margin_top"] = AfwSession::config("TITLE_APP_MARGIN_TOP", 5);

        if (!$data_tokens["title_comp_height"]) $data_tokens["title_comp_height"] = AfwSession::config("TITLE_COMP_HEIGHT", $HEADER_PIC_HEIGHT);
        if (!$data_tokens["title_comp_margin_top"]) $data_tokens["title_comp_margin_top"] = AfwSession::config("TITLE_COMP_MARGIN_TOP", 5);
        if (!$data_tokens["logo_comp_height"]) $data_tokens["logo_comp_height"] = AfwSession::config("LOGO_COMP_HEIGHT", $HEADER_PIC_HEIGHT);
        if (!$data_tokens["logo_comp_margin_top"]) $data_tokens["logo_comp_margin_top"] = AfwSession::config("LOGO_COMP_MARGIN_TOP", 5);


        if (!$data_tokens["banner_height"]) $data_tokens["banner_height"] = 100;
        if (!$data_tokens["logo_app_height"]) $data_tokens["logo_app_height"] = 90;
        if (!$data_tokens["title_app_height"]) $data_tokens["title_app_height"] = 90;
        if (!$options["show-banner"]) {
            $data_tokens["no_banner_s"] = "<!-- no banner for current module";
            $data_tokens["no_banner_e"] = "-->";
        } else {
            $data_tokens["no_banner_s"] = "<!-- banner active for current module -->";
            $data_tokens["no_banner_e"] = "";
        }

        if (!$options["show-scroll-banner"]) {
            $data_tokens["no_scroll_banner_s"] = "<!-- no scroll banner for current module";
            $data_tokens["no_scroll_banner_e"] = "-->";
        } else {
            $data_tokens["no_scroll_banner_s"] = "<!-- scroll banner active for current module -->";
            $data_tokens["no_scroll_banner_e"] = "";
        }
        $no_menu = (AfwSession::config("disable-menu", false) and (!$options["no-menu"]));
        if($no_menu)  {
            $data_tokens["main_menu_item_s"] = "<!--";
            $data_tokens["main_menu_item_e"] = "-->";
        } else {
            $data_tokens["main_menu_item_s"] = "";
            $data_tokens["main_menu_item_e"] = "";
        }


        if ($selected_menu) $data_tokens["main_item_css_class"] = "class='home_page'";
        else $data_tokens["main_item_css_class"] = "class='home_page active'";

        $data_tokens["register_css_class"] = "class='registerme'";
        $data_tokens["register_file"] = AfwSession::config("register_file", "user_register");




        $data_tokens["login_out_cl"] = $login_out_cl;
        $data_tokens["login_page"] = $login_page;
        $data_tokens["logout_page"] = $logout_page;
        $data_tokens["login_out_css"] = $login_out_css;
        $data_tokens["login_title"] = $login_title;
        $data_tokens["logout_title"] = $logout_title;

        $menu_template = AfwSession::currentMenuTemplate();

        $data_tokens["hzm_front_menu"] = AfwMenuConstructHelper::genereMenu($menu_template, $module, $objme, $lang, $module_languages, $role);

        if ((!$objme) and (!$no_menu)) {
            $data_tokens["me_connected_s"] = "<!--";
            $data_tokens["me_connected_e"] = "-->";
            $data_tokens["me_not_connected_s"] = "";
            $data_tokens["me_not_connected_e"] = "";
        } elseif ((!$objme) and ($no_menu)) {
            $data_tokens["me_connected_s"] = "";
            $data_tokens["me_connected_e"] = "";
            $data_tokens["me_not_connected_s"] = "";
            $data_tokens["me_not_connected_e"] = "";
        } else {
            $data_tokens["me_connected_s"] = "";
            $data_tokens["me_connected_e"] = "";
            $data_tokens["me_not_connected_s"] = "<!--";
            $data_tokens["me_not_connected_e"] = "-->";
        }

        $data_tokens["dark_mode"] = AfwLanguageHelper::translateKeyword("dark mode", $lang);

        $data_tokens["site_name"] = AfwSession::getCurrentSiteName($lang);
        if (!$options["bg_height"]) $options["bg_height"] = 400;
        $data_tokens["bg_height"] = $options["bg_height"];
        if (!$options["out_index_page"]) $options["out_index_page"] = "#";
        $data_tokens["out_index_page"] = $options["out_index_page"];

        return $data_tokens;
    }

    

    public static function renderMenu($menu_template,
            $lang,
            $role,
            $tpl_path = "",
            $selected_menu = "",
            $options = [],
    ) 
    {
        if($options["controllerObj"])
        {
            $data_tokens = $options["controllerObj"]->prepareMenuTokens(
                $lang,
                $role,
                $selected_menu,
                $options
            );
            // die("DGB241210 data_tokens=".var_export($data_tokens,true));
        }
        else
        {
            // die("DGB241210 options=".var_export($options,true));
            $objme = AfwSession::getUserConnected();

            $data_tokens = self::prepareTokens(
                $lang,
                $objme,
                $role,
                $selected_menu,
                $options
            );
        }
        
        if(!$tpl_path) $tpl_path = self::hzmTplPath();
        $html_template_file = "$tpl_path/$menu_template"."_menu_tpl.php";
                             
        return self::showUsingHzmTemplate($html_template_file, $data_tokens, $lang);

    }


    public static function renderHeader($header_template,
            $lang,
            $role,
            $tpl_path = "",
            $selected_menu = "",
            $options = [],
    ) 
    {
        $objme = AfwSession::getUserConnected();

        $data_tokens = self::prepareTokens(
            $lang,
            $objme,
            $role,
            $selected_menu,
            $options
        );
        if(!$tpl_path) $tpl_path = self::hzmTplPath();
        $html_template_file = "$tpl_path/$header_template"."_header_tpl.php";
                             
        return self::showUsingHzmTemplate($html_template_file, $data_tokens, $lang);

    }
}
