<?php

// old require of afw_root 

class AfwFrontMenu extends AFWRoot {

        public static function genereFrontMenuItem($menu_folder, $lang="ar", $menu_bar ="", $iamAdmin = false)
        {
                global $MENU_ICONS, $r;
                //$iamAdmin = 
                $menu_id = $menu_folder["id"];
                $childs_arr = $menu_folder["sub-folders"];
                $menu_title = $menu_folder["menu_name"];
                if(($lang=="en") and (!$menu_title)) $menu_title = "menu.arole.".$menu_folder["id"]; 
                $menu_page = $menu_folder["page"];
                $menu_item_css = $menu_folder["css"];
                $menu_icon = $menu_folder["icon"];
                $menu_color_class = $menu_folder["color_class"];
                
                if(($r==$menu_id) or ($childs_arr[$r])) $css_class = "active";
                else $css_class = "";

                
                if($menu_color_class) $li_class = "class='front-menu-item front-$menu_color_class-item $css_class'";
                else $li_class = "class='front-menu-item $css_class'";
                
                if($lang=="ar") $lang_align_inverse = "left";
                else $lang_align_inverse = "right"; 
                
                /*if(!$menu_bar) $fa_arrow_for_folder = "<i class='fa fa-arrow-$lang_align_inverse' aria-hidden='true'></i>";
                else*/ 
                
                $fa_arrow_for_folder = "";
        
                if(!$menu_icon) $menu_icon = $MENU_ICONS[$menu_id];
                if(!$menu_icon) $menu_icon = "cog";
                
                $html = "";
                
                if((count($menu_folder["items"])>0) or (count($menu_folder["sub-folders"])>0) or $menu_folder["showme"])
                {
                        $html .= "<li id='li01-$menu_id' $li_class><a href='$menu_page'><i class='fa fa-$menu_icon $menu_item_css'></i>$menu_title</a></li>\n";
                }
                                        
                return $html;
        }
}