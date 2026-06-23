<?php

// old require of afw_root 

class CmsFrontMenu extends AFWRoot
{

        /**
         * @return array<bool,array,array,array,mixed,string>
         */
        public static function loadUmsCacheForUser($userId, $lang)
        {
                $company = AfwSession::currentCompany();
                $file_afw_dir_name = dirname(__FILE__);

                $user_cache_file_path = "$file_afw_dir_name/../../../cache/chusers/$company" . "_user_$userId" . "_data.php";

                if (file_exists($user_cache_file_path)) {
                        include($user_cache_file_path);
                        /**
                         * @var array $menu
                         */

                        foreach ($menu as $the_module => $module_menu) {
                                $module_menu_roles = $module_menu["all"];
                                foreach ($module_menu_roles as $role_id => $module_menu_role) {
                                        $role_cache_file = "$file_afw_dir_name/../../../$the_module/previleges/role/previleges_" . $the_module . "_role$role_id.php";
                                        include($role_cache_file);
                                        $menu[$the_module]["all"][$role_id] = $role_info[$role_id]['menu'];
                                }
                        }

                        return [true, $quick_links_arr, $mau_info, $menu, $user_info, $user_cache_file_path];
                } else return [false, null, null, null, null, null];
        }

        /**
         * @param string $tpl_path
         * @param string $menu_template
         * @param string $module
         * @param string $lang
         * @param array $menu_folder
         */

        public static function genereFrontMenuItem($tpl_path, $menu_template, $menu_folder, $module, $lang, $r, $menu_bar = "", $iamAdmin = false)
        {
                global $MENU_ICONS;

                $tokens = [];
                //$iamAdmin = 
                $menu_id = $menu_folder["id"];
                $childs_arr = $menu_folder["sub-folders"];
                $menu_title = $menu_folder["menu_name_$lang"];
                if (!$menu_title) $menu_title = $menu_folder["menu_name"];
                if (!$menu_title) $menu_title = "menu.arole." . $menu_folder["id"];

                $menu_title = UfwReplacement::trans_replace($menu_title, $module, $lang);
                if ($lang != "ar") {
                        $menu_title = AfwStringHelper::firstCharUpper($menu_title);
                }
                $tokens["menu_title"] = $menu_title;
                $tokens["menu_page"] = $menu_folder["page"];
                $tokens["menu_item_css"] = $menu_folder["css"];
                $menu_icon = $menu_folder["icon"];
                $menu_color_class = $menu_folder["color_class"];

                if (($r == $menu_id) or ($childs_arr[$r])) $css_class = "active";
                else $css_class = "";

                $tokens["menu_id"] = $menu_id;
                $tokens["menu_li_class"] = $css_class;
                if ($menu_color_class) $tokens["li_class"] = "front-menu-item front-$menu_color_class-item $css_class";
                else $tokens["li_class"] = "front-menu-item $css_class";

                if ($lang == "ar") $lang_align_inverse = "left";
                else $lang_align_inverse = "right";

                /*if(!$menu_bar) $fa_arrow_for_folder = "<i class='fa fa-arrow-$lang_align_inverse' aria-hidden='true'></i>";
                else*/

                $fa_arrow_for_folder = "";

                if (!$menu_icon) $menu_icon = $MENU_ICONS[$menu_id];
                if (!$menu_icon) $menu_icon = "cog";

                $tokens["menu_icon"] = $menu_icon;

                $html = "";

                if (!is_array($menu_folder["items"]) or !is_array($menu_folder["sub-folders"])) {
                        throw new AfwRuntimeException("strange menu folder's data. In fact folders or items is not an array : " . var_export($menu_folder, true));
                }

                if ((count($menu_folder["items"]) > 0) or (count($menu_folder["sub-folders"]) > 0) or $menu_folder["showme"]) {
                        if (!$tpl_path) $tpl_path = AfwHtmlHelper::hzmTplPath();
                        $li_template_file = "$tpl_path/$menu_template" . "_menu_li_tpl.php";
                        $html .= "\n" . AfwHtmlHelper::showUsingHzmTemplate($li_template_file, $tokens, $lang);
                }

                return $html;
        }
}
