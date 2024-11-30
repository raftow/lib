<?php
throw new AfwRuntimeException("rafik I think it is obsolete now : 10 nov 2021");
          if(!$objme) $objme = AfwSession::getUserConnected();
          $file_hzm_dir_name = dirname(__FILE__);

          function genereMenuHtml($menu_folder,$lang,$menu_bar, $iamAdmin)
          {
                   global $MENU_ICONS;
                   //$iamAdmin = 
                   $menu_id = $menu_folder["id"];
                   $menu_title = $menu_folder["menu_name"];
                   if(($lang=="en") and (!$menu_title)) $menu_title = "menu.arole.".$menu_folder["id"]; 
                   $menu_page = $menu_folder["page"];
                   $menu_item_css = $menu_folder["css"];
                   $menu_icon = $menu_folder["icon"];
                   $menu_color_class = $menu_folder["color_class"];
                   
                   if($menu_color_class) $li_class = "class='menu-item $menu_color_class-item'";
                   else $li_class = "";
                   
                   if($lang=="ar") $lang_align_inverse = "left";
                   else $lang_align_inverse = "right"; 
                   if(!$menu_bar) $fa_arrow_for_folder = "<i class='fa fa-arrow-$lang_align_inverse' aria-hidden='true'></i>";
                   else $fa_arrow_for_folder = "";
                
                   if(!$menu_icon) $menu_icon = $MENU_ICONS[$menu_id];
                   if(!$menu_icon) $menu_icon = "cog";
                   
                   $html = "";
                   
                   if((count($menu_folder["items"])>0) or (count($menu_folder["sub-folders"])>0))
                   {
                           $html .= "<li id='li01-$menu_id' $li_class><a href='$menu_page'><i class='fa fa-$menu_icon $menu_item_css' aria-hidden='true'></i>$menu_title $fa_arrow_for_folder</a>\n";
                           $html .= "<ul><!--ul-01-->\n";                     
                           foreach($menu_folder["items"] as $menu_folder_item_id => $menu_folder_item)
                           {
                                $menu_item_id = $menu_folder_item["id"];
                                $menu_item_title = $menu_folder_item["menu_name"];
                                $menu_item_page = $menu_folder_item["page"];
                                $menu_item_css = $menu_folder_item["css"];
                                $html .=  "<li id='li02-$menu_item_id'><a href='$menu_item_page'><i class='fa fa-cog $menu_item_css' aria-hidden='true'></i>$menu_item_title</a></li>\n"; 
                           }
                           foreach($menu_folder["sub-folders"] as $menu_sub_folder_id => $menu_sub_folder)
                           {
                                if(($iamAdmin) or (!$menu_sub_folder["need_admin"]))
                                {
                                        //if($menu_sub_folder["id"] == 84) die("iamAdmin = $iamAdmin, need_admin = ".$menu_sub_folder["need_admin"]);
                                        $html .= genereMenuHtml($menu_sub_folder,$lang,false, $iamAdmin);
                                }         
                           }
                           $html .= "</ul><!--ul-01-close-->\n";
                           $html .= "</li>\n";
                   }
                                         
                
                
                   return $html;
          }


          $right_menu = array();
          if($objme) $my_firstname = $objme->valFirstname();
          $my_account_title = AfwLanguageHelper::translateKeyword("MYACCOUNT", $lang);
          $my_home = $MY_HOME[$lang];
          if(!$my_home) $my_home = AfwLanguageHelper::translateKeyword("HOME", $lang);
          if($objme) $right_menu[] = array('href' => "index.php",'css' => "home", 'title' => $my_home);
          
          //if($objme and $objme->isAdmin()) $right_menu[] = array('href' => "data_admin.php",'css' => "data", 'title' => AfwLanguageHelper::translateKeyword("DATA-ADMIN", $lang));
          if($objme and $objme->isAdmin() and $PAG) $right_menu[] = array('href' => "panel_analyst.php",'css' => "analyst", 'title' => AfwLanguageHelper::translateKeyword("ANALYST", $lang));

          
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
          
          
          
          
          $my_files = AfwLanguageHelper::translateKeyword("MY-FILES", $lang);
          $right_menu[] = array('href' => "afw_my_files.php?x=1",'css' => "file", 'title' => "$my_files");
          
          $my_files = AfwLanguageHelper::translateKeyword("EDIT-MY-FILES", $lang);
          
          $codeme = substr(md5("code".$me),0,8);
          $right_menu[] = array('href' => "afw_edit_my_files.php?x=$me&y=$codeme",'css' => "files-o", 'title' => "$my_files");

          if($my_account_page) 
              $right_menu[] = array('href' => $my_account_page,'css' => "user", 'title' => "$my_account_title ($my_firstname)");
          else 
              $right_menu[] = array('href' => "#",'css' => "myprofile", 'title' => AfwLanguageHelper::translateKeyword("SIGN-UP", $lang));
              
          
          $right_menu[] = array('href' => "#",'css' => "mobile", 'title' => AfwLanguageHelper::translateKeyword("CONTACT_US", $lang));

          $right_menu[] = array('href' => $login_out_page, 'css' => $login_out_css, 'title' => $login_out_title);

          $menu_color = "skyblue";
          $menu_next_color = array("skyblue"=>"seeblue","seeblue"=>"skyblue");

          // if we want to customize menu colors for a specific module
          include "$file_hzm_dir_name/../$MODULE/menu_colors.php";

?>

<nav id="primary_nav_wrap" class="menu_hzm_horizontal">
<ul>
<?
  $uri = AfwStringHelper::clean_my_url($_SERVER["REQUEST_URI"]);
  $get_lang = $_GET["lang"];
  if(AfwStringHelper::stringEndsWith($uri,"main.php")) $uri = str_replace("main.php", "index.php?home=1", $uri);
  if(AfwStringHelper::stringEndsWith($uri,".php")) $uri = str_replace(".php", ".php?abc=1", $uri);
  if(AfwStringHelper::stringEndsWith($uri,"/")) $uri .= "?abc=1";
  if((!$get_lang) or (strpos($uri, "lang=$get_lang") === false))
  {
       $get_lang = $lang;
       if(!$uri) $uri = "index.php?x=1";
       $uri = $uri."&lang=$get_lang";
  }
  
  $uri_arr["ar"] = "";
  $uri_arr["fr"] = "";
  $uri_arr["en"] = "";
  
  if(!$LANGS_MODULE["ar"]) AfwRunHelper::simpleError("LANGS_MODULE not defined");
  
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
?>
  <li class="menu-separator">&nbsp;</li>
<?
   if(!$DISABLE_PROJECT_ITEMS_MENU)
   {
?>
   <li class="project-item"><a href="index.php"><i class="fa fa-flag" aria-hidden="true"></i><?=$NOM_SYSTEM?></a>
     <ul>

<?
        $mau_list = $objme->get("mau");
        foreach($mau_list as $mau_item)
        {
              if($mau_item->getVal("id_module")>0)
              {
                   $my_module = $mau_item->het("id_module");
                   if($my_module and $my_module->isRunnable())
                   {
                           $mtitle = $my_module->getShortDisplay($lang);
                           $mcode = $my_module->getVal("module_code");
                           $mau_description = $mau_item->getVal("description");
                           if(($mcode!=$MODULE) and ($mau_description != "--no-application"))
                           {
?>                 

      <li><a href="../<?=$mcode?>/"><i class="fa fa-flag" aria-hidden="true"></i><?=$mtitle?></a></li>
<?
                           }
                   }
              }
        }
?>
     </ul> 
  </li>
  <li class="menu-separator">&nbsp;</li>
<?
   }
   
   include "$file_hzm_dir_name/../ums/module_options.php";
   include "$file_hzm_dir_name/../$MODULE/special_module_options.php";
   


}
   
   if($active_lang_count>0)
   {
   
?>      
  <li class="menu-small-item <?=$menu_color?>-item"><a href="#"><i class="fa fa-globe" aria-hidden="true"></i><?=AfwLanguageHelper::translateKeyword("LANGUE", $lang)?></a>
     <ul>
     <?   
            foreach($uri_arr as $lang_code => $uri_item)
            {
                $menu_item_title = $LANG_NAMES[$lang][$lang_code];
                if($uri_item) echo "<li><a href='$uri_item'><i class='fa fa-flag' aria-hidden='true'></i>$menu_item_title</a></li>\n"; 
            }
            $menu_color = $menu_next_color[$menu_color];     
                
     ?>
     </ul>
  </li>
  <li class="menu-separator">&nbsp;</li>
<?php
    }
    if(is_object($objme))
    {
            $iamAdmin = $objme->isAdmin();
            
            $THIS_MODULE_ID = AfwSession::config("application_id",0);
            if($THIS_MODULE_ID)
            { 
                    $menu_folders_arr = $objme->getMenuFor($THIS_MODULE_ID,$lang);  
                    //die("objme->getMenuFor($THIS_MODULE_ID , $lang) = ".var_export($menu_folders_arr,true));
                    $i = 0;
                    //throw new AfwRuntimeException("objme->getMenuFor($THIS_MODULE_ID,$lang) = ".var_export($menu_arr,true));
                    foreach($menu_folders_arr as $menu_folder_i)
                    {
                        if(($iamAdmin) or (!$menu_folder_i["need_admin"]))
                        {   
                           $menu_color = $menu_next_color[$menu_color];
                           $menu_folder_i["color_class"] = $menu_color; 
                           echo genereMenuHtml($menu_folder_i,$lang,true, $iamAdmin);
                           echo '<li class="menu-separator">&nbsp;</li>';
                        }   
                    }
             }
             
              
             require_once "$file_hzm_dir_name/../$MODULE/module_context.php";
             
             if($objme->contextCurrModule != $MODULE)
             {
                 // throw new AfwRuntimeException("context reset because : contextModule(".$objme->contextCurrModule . ") != $MODULE");
                 unset($objme->contextObjId);
                 unset($objme->contextObjName);
                 unset($objme->contextModule);
             }
             
             if($objme->contextObjId)
             {
                  $contextMenuName = $objme->contextObjName[$lang];
                  $curr_context_id = $objme->contextObjId;
             }
             elseif(count($contextList)==1)
             {
                  foreach($contextList as $contextId => $contextObj)
                  {
                         $context = $contextId;
                         include "$file_hzm_dir_name/../$MODULE/set_context.php";
                         $contextMenuName = $objme->contextObjName[$lang];
                         $curr_context_id = $objme->contextObjId;
                         // die("included $file_hzm_dir_name/../$MODULE/set_context.php => objme = ".var_export($objme,true));
                  }       
             }
             else
             {
                  //$contextMenuName = var_export($objme,true);
                  $contextMenuName = $contextLabel[$lang];
             }
             
        if(count($contextList)>0)
        {
             
  ?>
  <li class="context-item"><a href="#"><i class="fa fa-bookmark" aria-hidden="true"></i><?=$contextMenuName?></a>
     <ul>
<?
     
             foreach($contextList as $con_id => $con_obj)
             {
                  
                  if($con_id and (is_object($con_obj)))
                  {
                       if($curr_context_id==$con_id)
                       {
                             $href = "main.php?My_Module=ums&Main_Page=show_current_context_info.php";
                             $classContext = "bookmark";
                       }
                       else
                       {
                             $href = "main.php?Main_Page=set_context.php&context=$con_id";
                             $classContext = "bookmark-o";
                       }
                       
?>     
              <li><a href="<?=$href?>"><i class="fa fa-<?=$classContext?>" aria-hidden="true"></i><?=$contextShortLabel[$lang]." ".$con_obj->getContextDisplay($lang,$MODULE)?></a></li>
<?
                  }
             }
?>     
     </ul> 
  </li>
  <li class="menu-separator">&nbsp;</li>
<?
        }
?>  
  <li class="menu-small-item <?=$menu_color?>-item"><a href="#"><i class="fa fa-cogs" aria-hidden="true"></i><?=AfwLanguageHelper::translateKeyword("CONTROL", $lang)?></a>
     <ul>
          <li id="li_options">
          <a href="#"><i class="fa fa-cog info" aria-hidden="true"></i><?=AfwLanguageHelper::translateKeyword("OPTIONS", $lang)?><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
                <ul><!--ul-options-->
                <?
                   foreach($options_arr as $option_code => $option_props) 
                   {
                     if((!$option_props["admin"]) or ($objme->isAdmin()))
                     {
                   
                       if(AfwSession::hasOption($option_code))
                       {
                             $classContext = "bookmark";
                       }
                       else
                       {
                             $classContext = "bookmark-o";
                       }
                       
                ?>                    
                        <li id="li_options-<?=$option_code?>"><a href="main.php?Main_Page=toggle_option.php&option=<?=$option_code?>&My_Module=ums">
                             <i class="fa fa-<?=$classContext?>" aria-hidden="true"></i><?=$option_props[$lang]?></a>
                        </li>
                <?
                     }
                   }

                ?> 
                </ul><!--ul-options-close-->
          </li>

     <?   
            foreach($right_menu as $menu_item)
            {
                $menu_item_page = $menu_item["href"];
                $menu_item_title = $menu_item["title"];
                $menu_item_css = $menu_item["css"];
                echo "<li><a href='$menu_item_page'><i class='fa fa-$menu_item_css'></i>$menu_item_title</a></li>\n"; 
            }    
             $menu_color = $menu_next_color[$menu_color];    
     ?>
     </ul>
  </li>
  <li class="menu-separator">&nbsp;</li>
  <li class="search-bar-item">
                    <form class="navbar-form navbar-left">
                              <div class="search fleft">
                                        <input type="button" class="searchbtn fleft"><input type="text" class="searchtxt fleft" placeholder="<?=AfwLanguageHelper::translateKeyword("SEARCH_HERE", $lang)?>...">
                              </div>
                    </form>​
  </li>
      
<?
/*

<li><a href="#"><i class="fa fa-tachometer" aria-hidden="true"></i>لوحة القوائم العميقة</a>
    <ul>
      <li><a href="#"><i class="fa fa-cog" aria-hidden="true"></i>القائمة التحتية 1</a></li>
      <li><a href="#"><i class="fa fa-cog" aria-hidden="true"></i>القائمة التحتية 2</a></li>
      <li><a href="#"><i class="fa fa-cog" aria-hidden="true"></i>القائمة التحتية 3<i class="fa fa-arrow-right" aria-hidden="true"></i></a></li>
      <li><a href="#"><i class="fa fa-cog" aria-hidden="true"></i>القائمة التحتية 4 TOTO TATA<i class="fa fa-arrow-left" aria-hidden="true"></i></a></li>
        <ul>
          <li><a href="#">Deep Menu 1</a>
            <ul>
              <li><a href="#">القائمة العميقة 1</a></li>
              <li><a href="#">القائمة العميقة 2</a></li>
              <li><a href="#">القائمة العميقة 3</a></li>
                <li><a href="#">القائمة العميقة 4</a></li>
            </ul>
          </li>
          <li><a href="#">Deep Menu 2</a></li>
        </ul>
      
      <li><a href="#"><i class="fa fa-cog" aria-hidden="true"></i>القائمة التحتية 5</a></li>
    </ul>
  </li>

    <li class="menu-bar-item"><a href="data_admin.php"><i class="fa fa-database" aria-hidden="true"></i>البيانات</a></li>
  <li class="menu-separator">&nbsp;</li>



*/

     }
?>     
</ul>
</nav>