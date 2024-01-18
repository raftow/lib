<?php
        // common_die("rafik-2 I think it is obsolete now : 10 nov 2021");
        $objme = AfwSession::getUserConnected();  
          
        $html_hzm_menu = "";
        global $r;
        if(!isset($r)) $r = $_REQUEST["r"];
        // die("r=$r , _REQUEST = ".var_export($_REQUEST,true));

        $right_menu = array();
        if($objme) $my_firstname = $objme->valFirstname();
        $my_account_title = AFWObject::traduireOperator("MYACCOUNT", $lang);
        $my_home = $MY_HOME[$lang];
        if(!$my_home) $my_home = AFWObject::traduireOperator("HOME", $lang);
        if($objme) $right_menu[] = array('href' => "index.php",'css' => "home", 'title' => $my_home);
        
        //if($objme and $objme->isAdmin()) $right_menu[] = array('href' => "data_admin.php",'css' => "data", 'title' => AFWObject::traduireOperator("DATA-ADMIN", $lang));
        if($objme and $objme->isAdmin() and $PAG) $right_menu[] = array('href' => "panel_analyst.php",'css' => "analyst", 'title' => AFWObject::traduireOperator("ANALYST", $lang));

        
        if($my_account_page)
        {
                $my_account_page = str_replace("[ME]", $me, $my_account_page);
                $my_account_page = str_replace("[MODULE]", $MODULE, $my_account_page);
                $my_account_page = str_replace("[EMPL]", $my_employee_id, $my_account_page);
                $my_account_page = str_replace("[SEMPL]", $sempl_id, $my_account_page);
        
        
        }
        
        if(($me) and (!$my_account_page))
        {
                $my_account_page = "main.php?Main_Page=afw_mode_display.php&cl=Auser&id=$me&currmod=ums&no_my_account_page_in_mod=$MODULE";
        }
        
        if(!$LANG_NAMES)
        {
                $LANG_NAMES["ar"]["ar"] = "عربي";
                $LANG_NAMES["ar"]["fr"] = "فرنسي";
                $LANG_NAMES["ar"]["en"] = "انجليزي";
                $LANG_NAMES["fr"]["ar"] = "Arabe";
                $LANG_NAMES["fr"]["fr"] = "Francais";
                $LANG_NAMES["fr"]["en"] = "Anglais";
                $LANG_NAMES["en"]["ar"] = "Arabic";
                $LANG_NAMES["en"]["fr"] = "French";
                $LANG_NAMES["en"]["en"] = "English";        
        }
        
        
        $my_files = AFWObject::traduireOperator("MY-FILES", $lang);
        $right_menu[] = array('href' => "afw_my_files.php?x=1",'css' => "file", 'title' => "$my_files");
        
        $my_files = AFWObject::traduireOperator("EDIT-MY-FILES", $lang);
        
        $codeme = substr(md5("code".$me),0,8);
        $right_menu[] = array('href' => "afw_edit_my_files.php?x=$me&y=$codeme",'css' => "files-o", 'title' => "$my_files");

        if($my_account_page) 
                $right_menu[] = array('href' => $my_account_page,'css' => "user", 'title' => "$my_account_title ($my_firstname)");
        else 
                $right_menu[] = array('href' => "#",'css' => "myprofile", 'title' => AFWObject::traduireOperator("SIGN-UP", $lang));
        
        
        //$right_menu[] = array('href' => "#",'css' => "mobile", 'title' => AFWObject::traduireOperator("CONTACT_US", $lang));

        //$right_menu[] = array('href' => $login_out_page, 'css' => $login_out_css, 'title' => $login_out_title);

        $menu_color = "skyblue";
        $menu_next_color = array("skyblue"=>"seeblue","seeblue"=>"skyblue");

        // if we want to customize menu colors for a specific module
        include "$file_hzm_dir_name/../$MODULE/menu_colors.php";

          

        $uri = AfwStringHelper::clean_my_url($_SERVER["REQUEST_URI"]);
        $get_lang = $_GET["lang"];
        if(se_termine_par($uri,"main.php")) $uri = str_replace("main.php", "index.php?home=1", $uri);
        if(se_termine_par($uri,".php")) $uri = str_replace(".php", ".php?abc=1", $uri);
        if(se_termine_par($uri,"/")) $uri .= "?abc=1";
        if((!$get_lang) or (strpos($uri, "lang=$get_lang") === false))
        {
                $get_lang = $lang;
                if(!$uri) $uri = "index.php?x=1";
                $uri = $uri."&lang=$get_lang";
        }
        
        $uri_arr["ar"] = "";
        $uri_arr["fr"] = "";
        $uri_arr["en"] = "";
        
        if(!$LANGS_MODULE["ar"]) Auser::simpleError("LANGS_MODULE not defined");
        
        $active_lang_count = 0;
        
        if(($LANGS_MODULE["ar"]) and ($lang!="ar"))
        {
                $uri_arr["ar"] = str_replace("lang=$get_lang", "lang=ar", $uri);
                $active_lang_count++;
        }
        
        if(($LANGS_MODULE["fr"]) and ($lang!="fr"))
        {
                $uri_arr["fr"] = str_replace("lang=$get_lang", "lang=fr", $uri);
                $active_lang_count++;
        }
        
        if(($LANGS_MODULE["en"]) and ($lang!="en")) 
        {
                $uri_arr["en"] = str_replace("lang=$get_lang", "lang=en", $uri);
                $active_lang_count++;
        }


        if($objme)
        {
                include "$file_hzm_dir_name/../pag/module_options.php";
                include "$file_hzm_dir_name/../$MODULE/special_module_options.php";
        }
        $enable_language_switch = AfwSession::config("enable_language_switch",false);
        if(($active_lang_count>0) and $enable_language_switch)
        {
                foreach($uri_arr as $lang_code => $uri_item)
                {
                        $menu_item_title = $LANG_NAMES[$lang][$lang_code];
                        if($uri_item) $html_hzm_menu .= "<li class='lang-menu lang-$lang_code'><a href='$uri_item'><i class='fa fa-globe' aria-hidden='true'></i>$menu_item_title</a></li>\n"; 
                }
                $menu_color = $menu_next_color[$menu_color];     
                        
        }
    
        if((is_object($objme)) and ($objme->id>0))
        {
                $iamAdmin = $objme->isAdmin();

                $application_id = AfwSession::config("application_id",0);
                $ncu = AfwSession::config("no-cache-use-for-ums",false);
                if(!$application_id) 
                {
                        throw new AfwRuntimeException("HZM Error : application_id should be defined in application_config.php file");
                }
                else
                { 
                        $me_id = $objme->id;
                        $file_lib_hzm_web_dir_name = dirname(__FILE__); 
                        $file_cache = "$file_lib_hzm_web_dir_name/../../../external/chusers/user_${me_id}_data.php";
                        if(file_exists($file_cache) and (!$ncu)) // ncu = get option to say "no cache use" to retrieve roles and menus (ums)
                        {
                            include($file_cache);
                            $application_code = $mau_info["m$application_id"]["code"];
                            $menu_folders_arr = $menu[$application_code][$lang];

                            if(!$menu_folders_arr) AfwSession::pushWarning("System cache X <!-- $file_cache --> gived application_code=[$application_code] for application id [$application_id] and and no menu for this user for this application code");    
                            
                        }
                        elseif(!$ncu) AfwSession::pushWarning("System need cache optimisation file for user $me_id <!-- file not found $file_cache -->");    
                        else AfwSession::pushWarning("System cache optimisation disabled");    

                        if(!$menu_folders_arr) $menu_folders_arr = $objme->getMenuFor($application_id, $lang);  
                        
                        //die(var_export($menu_folders_arr,true)); 
                        //die("objme->getMenuFor($application_id , $lang) = ".var_export($menu_folders_arr,true));
                        $i = 0;
                        //throw new AfwRuntimeException("objme->getMenuFor($application_id,$lang) = ".var_export($menu_arr,true));
                        foreach($menu_folders_arr as $menu_folder_id => $menu_folder_i)
                        {
                                if(($iamAdmin) or (!$menu_folder_i["need_admin"]))
                                {   
                                        $menu_color = $menu_next_color[$menu_color];
                                        $menu_folder_i["color_class"] = $menu_color; 
                                        $menu_folder_i_html = AfwFrontMenu::genereFrontMenuItem($menu_folder_i, $lang, true, $iamAdmin);
                                        // if($menu_folder_id=="-1") die($menu_folder_i_html);
                                        // if($menu_folder_i_html) die("menu[$menu_folder_id] => ".$menu_folder_i_html." item => ".var_export($menu_folder_i,true));
                                        $html_hzm_menu .= $menu_folder_i_html;
                                }   
                        }
                }
                
                
                if(false)
                {
                        $html_hzm_menu .= "<li class=\"front-small-item front-$menu_color-item\"><a href=\"main.php?Main_Page=fm.php&m=control\"><i class=\"fa fa-cogs\"></i>".AFWObject::traduireOperator("CONTROL", $lang)."</a></li>";
                }
        
        }
        else
        {
                // die("rafik debugg ::::: <br>".$html_hzm_menu);
                // here we can add GUEST (logged out user) menu
                // @todo
        }
        
        
?>  