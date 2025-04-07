<?php
class AfwHtmlFooterHelper extends AfwHtmlHelper
{

    private static function prepareTokens($footer_template, 
        $lang,
        $objme,
        $module,
        $tpl_path = "",
        $options = []
    ) {

        $data_tokens = [];

        $data_tokens["quick_links_title"] = AfwSession::config("quick_links_title", "روابط سريعة");
        $data_tokens["module"] = $module;

        if($objme)
        {
            $me_id = $objme->id;
            list($cache_found, $quick_links_arr, $mau_info, $menu, $user_info, $user_cache_file_path) = AfwFrontMenu::loadUmsCacheForUser($me_id, $lang);
            if($cache_found)
            {
                $quick_links_arr = $quick_links_arr[$lang]; 
                $tocheck = $user_cache_file_path;
            }
            else
            {
                $quick_links_arr = $objme->getMyQuickLinks($lang, $module);
                $tocheck = "from database objme->getMyQuickLinks($lang, $module)";
            } 
        }
        $quick_links_html = "";
        if($quick_links_arr and is_array($quick_links_arr) and count($quick_links_arr)>0)
        {
            if(!$tpl_path) $tpl_path = self::hzmTplPath();
            $html_template_file = "$tpl_path/$footer_template"."_qlk_li_tpl.php";

            foreach($quick_links_arr as $quick_link)
            {
                if(!$quick_link["target"]) $quick_link["target"] = "new";
                $quick_link["quick_link"] = $quick_link["name_$lang"];
                if($quick_link["target"] != $module)
                {
                    $quick_links_html .= self::showUsingHzmTemplate($html_template_file, $quick_link, $lang);
                }
            }
        }

        $data_tokens["quick_links"] = $quick_links_html;
        $data_tokens["slog"] = AfwHtmlSystemLogHelper::render($objme);

        return $data_tokens;
    }

    public static function renderFooter($footer_template,
            $lang,
            $module,
            $tpl_path = "",
            $options = []
    ) 
    {
        $objme = AfwSession::getUserConnected();

        $data_tokens = self::prepareTokens($footer_template,
            $lang,
            $objme,
            $module,
            $tpl_path,
            $options
        );
        if(!$tpl_path) $tpl_path = self::hzmTplPath();
        $html_template_file = "$tpl_path/$footer_template"."_footer_tpl.php";
                             
        return self::showUsingHzmTemplate($html_template_file, $data_tokens, $lang);

    }


}