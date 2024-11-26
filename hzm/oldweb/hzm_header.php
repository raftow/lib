<?php
  if(!$my_theme)  $my_theme = "simple";
  $file_hzm_dir_name = dirname(__FILE__); 
  
  require_once("$file_hzm_dir_name/../../afw/afw_utils.php");
  
  $f3c = substr($Main_Page,0,3);
  $f7c = substr($Main_Page,0,7);
  if(!$pagecode) $pagecode = AfwUrlManager::currentPageCode();
  $pagecode_splitted = implode(" ",explode("_",$pagecode));
  // die("rafik-adm-001 : config=".var_export($config,true));
  
?>
<!DOCTYPE html>
<?php
if(!$header_entry_counter) $header_entry_counter = 1;
else $header_entry_counter++; 

if($header_entry_counter>1)
{
        AfwRunHelper::simpleError("HzmHeader included twice, in general this happen when you include main.php or afw_main_page.php when you are inside body of MainPage");
}
if(!$objme) $objme = AfwSession::getUserConnected();
$lang = AfwSession::getSessionVar("lang");
if(!$lang) $lang = "ar";

$lang = strtolower($lang);

if($imposed_charset) $page_charset = $imposed_charset;
else 
{
    $page_charset = "UTF-8";
}
if($lang=="ar") $dir = "rtl";
else $dir = "ltr";

  if(!$NOM_SYSTEM) $NOM_SYSTEM = $NOM_SITE[$lang];

 if(!AfwSession::getSessionVar("user_id"))
 {
     $login_out_css = "sign-in";
     $login_out_classe = "login";
     $login_out_cl = "login";
     $login_out_page = $pages_arr["login"][$MODULE];
     if(!$login_out_page) $login_out_page = "login.php";
     if(!$login_button_title)
         $login_out_title = AfwLanguageHelper::translateKeyword("LOGIN", $lang);
     else
         $login_out_title = $login_button_title; 
 }
 else
 {
     $login_out_css = "sign-out";
     $login_out_classe = "login";
     $login_out_cl = "logout";
     $login_out_page = $pages_arr["logout"][$MODULE];
     if(!$login_out_page) $login_out_page = "logout.php";
     
     $login_out_title = AfwLanguageHelper::translateKeyword("LOGOUT", $lang);    
 }
  
  
?>
<html>
<?php
    if(!$html_hedear) $html_hedear = "$file_hzm_dir_name/hzm_html_head.php";
    //    die("include of $html_hedear");
    include($html_hedear);
    if(!$body_css_class) $body_css_class = "hzm_body $f7c $pagecode_splitted";
?>
<body class="<?=$body_css_class ?>" dir="<?=$dir?>" >
	<!-- #Header -->
<?php
   if(!$header_bg_color) $header_bg_color = "#393f50";
   if((!$no_front_header) and ($MODULE) and (!AfwSession::hasOption("FULL_SCREEN")))
   {
                if(($system_date_format != "greg") and ($lang=="ar"))
                {
                    $hijri_date =AfwDateHelper::currentHijriDate("hdate_long",$DateSeparator="/");
                    // die("hijri_date=$hijri_date");
                    $hijri_date_arr = explode($DateSeparator,$hijri_date);
                    $display_date_year = $hijri_date_arr[3];
                    $display_date_day = $hijri_date_arr[1];
                    $display_date_month = $hijri_date_arr[2];
                }
                else
                {
                    list($wday, $display_date_day, $display_date_month, $display_date_year) = current_greg_date_arr();
                }
                $system_date = "Date : ".date("d/m/Y");
        
                $welcome_user = "";
                if($objme)
                {
                        $me_id = $objme->id;
                        
                        list($cache_found, $quick_links_arr, $mau_info, $menu, $user_info, $user_cache_file_path) = AfwFrontMenu::loadUmsCacheForUser($me_id, $lang);

                        if($cache_found)
                        {
                            $user_full = $user_info["user_full_name"][$lang];
                            $user_dep = $user_info["user_department"][$lang];
                            $user_job = $user_info["user_job"][$lang];

                            if(!$user_full) AfwSession::pushWarning("System cache $file_cache gived user_full_name=[$user_full] for uid=$me_id");    
                            
                        }
                        else 
                        {
                                AfwSession::pushWarning("System need cache optimisation file for user $me_id <!-- file not found $file_cache -->");    

                                $user_full = $objme->getShortDisplay($lang);
                                $user_dep = $objme->getMyDepartmentName($lang);
                                $user_job = $objme->getMyJob($lang);
                        }

                        $welcome = $objme->translate("welcome",$lang);
                        $welcome_user = "<span> $welcome </span><br>
$user_full                                             
    <p>$user_job</p>
<p>$user_dep</p>";

                }

                $welcome_div = "";
                if($objme)
                {
                          $welcome_div = "<div class=\"title_company_user\">$welcome_user</div>";
                }
                
                $data_tokens = array();
                $run_mode_var = AfwSession::config("run_mode_var","run_mode");
                $run_mode = AfwSession::config($run_mode_var,"");
                if($run_mode) $run_mode = "-".$run_mode;
                $data_tokens["run_mode"] = $run_mode;
                $data_tokens["welcome_div"] = $welcome_div;
                if(!$config["img-company-path"]) $config["img-company-path"] = $config["img-path"];
                $data_tokens["img-path"] = $config["img-path"];
                $data_tokens["img-company-path"] = $config["img-company-path"];
                $xmodule = AfwSession::getCurrentlyExecutedModule();
                if($xmodule) $data_tokens["xmodule"] = "-".$xmodule;
                else $data_tokens["xmodule"] = "";

                $data_tokens["calendar_class"] = "calendar_bloc_g";
                $data_tokens["display_date_year"] = $display_date_year;
                $data_tokens["display_date_day"] = $display_date_day;
                $data_tokens["display_date_month"] = $display_date_month;
                if($no_menu)
                {
                        $data_tokens["no_menu_s"] = "<!---";
                        $data_tokens["no_menu_e"] = "--->";
                }
                else
                {
                        $data_tokens["no_menu_s"] = "";
                        $data_tokens["no_menu_e"] = "";
                }
                $data_tokens["banner_height"] = $banner_height[$MODULE];
                $data_tokens["title_app_height"] = $title_app_height[$MODULE];
                $data_tokens["logo_app_height"] = $logo_app_height[$MODULE];
                $data_tokens["title_comp_height"] = $title_comp_height[$MODULE];
                $data_tokens["logo_comp_height"] = $logo_comp_height[$MODULE];
                if(!$data_tokens["logo_app_height"]) $data_tokens["logo_app_height"] = AfwSession::config("LOGO_APP_HEIGHT", $HEADER_PIC_HEIGHT);
                if(!$data_tokens["logo_app_margin_top"]) $data_tokens["logo_app_margin_top"] = AfwSession::config("LOGO_APP_MARGIN_TOP", 5);
                
                if(!$data_tokens["title_app_height"]) $data_tokens["title_app_height"] = AfwSession::config("TITLE_APP_HEIGHT", $HEADER_PIC_HEIGHT);
                if(!$data_tokens["title_app_margin_top"]) $data_tokens["title_app_margin_top"] = AfwSession::config("TITLE_APP_MARGIN_TOP", 5);
                
                if(!$data_tokens["title_comp_height"]) $data_tokens["title_comp_height"] = AfwSession::config("TITLE_COMP_HEIGHT", $HEADER_PIC_HEIGHT);                
                if(!$data_tokens["title_comp_margin_top"]) $data_tokens["title_comp_margin_top"] = AfwSession::config("TITLE_COMP_MARGIN_TOP", 5);
                if(!$data_tokens["logo_comp_height"]) $data_tokens["logo_comp_height"] = AfwSession::config("LOGO_COMP_HEIGHT", $HEADER_PIC_HEIGHT);
                if(!$data_tokens["logo_comp_margin_top"]) $data_tokens["logo_comp_margin_top"] = AfwSession::config("LOGO_COMP_MARGIN_TOP", 5);
                
                
                if(!$data_tokens["banner_height"]) $data_tokens["banner_height"] = 100;
                if(!$data_tokens["logo_app_height"]) $data_tokens["logo_app_height"] = 90;
                if(!$data_tokens["title_app_height"]) $data_tokens["title_app_height"] = 90;
                if(!$std_banner[$MODULE])
                {
                        $banner_log = var_export($std_banner,true);
                        $data_tokens["no_banner_s"] = "<!-- no banner for module $MODULE : $banner_log";
                        $data_tokens["no_banner_e"] = "-->";
                }
                else
                {
                        $banner_log = var_export($std_banner,true);
                        $data_tokens["no_banner_s"] = "<!-- banner active for module $MODULE : $banner_log -->";
                        $data_tokens["no_banner_e"] = "";
                }
                
                if(!$scroll_banner[$MODULE])
                {
                        $scroll_banner_log = var_export($scroll_banner,true);
                        $data_tokens["no_scroll_banner_s"] = "<!-- no scroll banner for module $MODULE : $scroll_banner_log";
                        $data_tokens["no_scroll_banner_e"] = "-->";
                }
                else
                {
                        $scroll_banner_log = var_export($scroll_banner,true);
                        $data_tokens["no_scroll_banner_s"] = "<!-- scroll banner active for module $MODULE : $scroll_banner_log -->";
                        $data_tokens["no_scroll_banner_e"] = "";
                }
                
                
                
                if(AfwSession::config("DISABLE_PROJECT_ITEMS_MENU", false) and (!$no_menu))
                {
                        $data_tokens["main_menu_item_s"] = "<!--";
                        $data_tokens["main_menu_item_e"] = "-->";
                }
                else
                {
                        $data_tokens["main_menu_item_s"] = "";
                        $data_tokens["main_menu_item_e"] = "";
                }        
                
                
                if($r) $data_tokens["main_item_css_class"] = "class='home_page'";
                else $data_tokens["main_item_css_class"] = "class='home_page active'";

                $data_tokens["register_css_class"] = "class='registerme'";
                $data_tokens["register_file"] = AfwSession::config("register_file", "user_register");
                

                
                
                $data_tokens["login_out_cl"] = $login_out_cl;
                $data_tokens["login_out_page"] = $login_out_page;
                $data_tokens["login_out_css"] = $login_out_css;
                $data_tokens["login_out_title"] = $login_out_title;
                
                include "hzm_front_menu.php";
                $data_tokens["hzm_front_menu"] = $html_hzm_menu;                              
                
                if((!$objme) and (!$no_menu))
                {
                        $data_tokens["me_connected_s"] = "<!--";
                        $data_tokens["me_connected_e"] = "-->";
                        $data_tokens["me_not_connected_s"] = "";
                        $data_tokens["me_not_connected_e"] = "";
                }
                elseif((!$objme) and ($no_menu))
                {
                        $data_tokens["me_connected_s"] = "";
                        $data_tokens["me_connected_e"] = "";
                        $data_tokens["me_not_connected_s"] = "";
                        $data_tokens["me_not_connected_e"] = "";
                }
                else
                {
                        $data_tokens["me_connected_s"] = "";
                        $data_tokens["me_connected_e"] = "";
                        $data_tokens["me_not_connected_s"] = "<!--";
                        $data_tokens["me_not_connected_e"] = "-->";
                }
                
                if($connecting and (!$no_menu))
                {
                        $data_tokens["me_connecting_s"] = "<!--";
                        $data_tokens["me_connecting_e"] = "-->";
                }
                else
                {
                        $data_tokens["me_connecting_s"] = "";
                        $data_tokens["me_connecting_e"] = "";
                }
                
                 
                                              
                $data_tokens["site_name"] = $NOM_SITE[$lang];                              
                if(!$bg_height) $bg_height = 400; 
                $data_tokens["bg_height"] = $bg_height;
                if(!$out_index_page) $out_index_page = "#";
                $data_tokens["out_index_page"] = $out_index_page;
                $html_template_file = "$file_hzm_dir_name/hzm_header_${my_theme}_tpl.php";
                
                echo showUsingHzmTemplate($html_template_file, $data_tokens);
         
   }
   
?>
        <div class="container <?php echo $MODULE." ".$page_name ?>  ">   
            <div class="notification_message_container">  

<?php
   if(AfwSession::getSessionVar("error"))
   {
?>
                <div class="alert messages messages--error alert-dismissable" role="alert" ><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?php 
                  $cnt = count(explode("<br>",AfwSession::getSessionVar("error")));
                  if ($cnt>1)
                  {
                ?>
                يوجد أخطاء : <br>
                <?php 
                  }
                  echo AfwSession::pullSessionVar("error","header"); 
                ?>
                </div><br>

<?php
   }

   if(AfwSession::getSessionVar("warning"))
   {
?>
                <div class="alert messages messages--warning alert-dismissable" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?php 
                  $cnt = count(explode("<br>",AfwSession::getSessionVar("warning")));
                  if ($cnt>1)
                  {
                ?>
                يوجد تنبيهات : <br>
                <?php 
                  }
                  echo AfwSession::pullSessionVar("warning","header"); 
                ?>
                </div><br>
<?php
   }

   if(AfwSession::getSessionVar("information"))
   {
?>
                <div class="alert messages xx messages--status  alert-dismissable <?=AfwSession::getSessionVar("information-class")?>" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?php echo AfwSession::pullSessionVar("information","header");?>
                </div><br>
<?php
   }
   
   if(AfwSession::getSessionVar("success"))
   {
?>
                <div class="alert messages messages--success alert-dismissable  <?=AfwSession::getSessionVar("information-class")?>" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <?php echo AfwSession::pullSessionVar("success","header");?>
                </div><br>
<?php
   }

   if(AfwSession::getSessionVar("slog"))
   {
?>
                <!-- SLOG :
                <?php echo AfwSession::pullSessionVar("slog","header");?>
                -->
<?php
   }
   
?> 
            </div>
        <div class='<?=$body_css_class?> no_spaces'>

<?php
  include_once("gpie_body.php");
  $file_special_php_page_code = "/../../../$xmodule/special/$pagecode.php";
  $full_file_special_php_page_code = $file_hzm_dir_name.$file_special_php_page_code;
  if(!file_exists($full_file_special_php_page_code))
  {
        echo "<!-- any specific html code to generate here is to put in xxx$file_special_php_page_code";
  }
  else include_once($full_file_special_php_page_code);
?>
<!-- #END OF Header -->

