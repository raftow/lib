<?php
    class AfwThemeHelper
    {
        public static function loadTheme()
        {
            $theme_name = AfwSession::config('theme','modern'); 
            $file_dir_name = dirname(__FILE__);
            return include("$file_dir_name/../themes/".$theme_name.'_config.php');
        }
    }