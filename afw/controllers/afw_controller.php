<?php

include_once("afw_error_handler.php");

class AfwController extends AFWRoot
{

        public function __construct($request) {}

        public function headerTemplate($methodName, $default_header_template)
        {
                return $default_header_template;
        }
        public function menuTemplate($methodName, $default_menu_template)
        {
                return $default_menu_template;
        }
        public function bodyTemplate($methodName, $default_body_template)
        {
                return $default_body_template;
        }
        public function footerTemplate($methodName, $default_footer_template)
        {
                return $default_footer_template;
        }

        public function defaultMethod($request)
        {
                return "index";
        }

        public function viewType()
        {
                return "modern";
        }

        public function myViewSettings($methodName)
        {
                return array("lib/hzm/web/hzm_html_head.php", "lib/hzm/web/hzm_header.php", "lib/hzm/web/hzm_footer.php");
        }

        public function index($request)
        {
                return "body of controller-index method not implemented";
        }

        public function alwaysNeedPrepare($request)
        {
                return false;
        }

        public function checkLoggedIn()
        {
                // to be overridden depending on controller business logic
                return false;
        }

        public function render($view_module, $view_name, $data)
        {
                $lang = AfwLanguageHelper::getGlobalLanguage();
                foreach ($data as $key => $value) $$key = $value;
                $file_dir_name = dirname(__FILE__);
                $view_name_tpl = $view_name . "_tpl";
                $view_template_path = "$file_dir_name/../../../$view_module/tpl/$view_name_tpl.php";
                if (!file_exists($view_template_path)) {
                        throw new AfwRuntimeException("view template not found : $view_template_path");
                } else {
                        if($mt=="survey_request") die("view_template_path is : ".$view_template_path);
                        include_once($view_template_path);
                }
        }


        public function renderPage($view_module, $view_page, $data, $error = null, $warning = null, $info = null, $success = null)
        {
                $lang = AfwLanguageHelper::getGlobalLanguage();
                foreach ($data as $key => $value) $$key = $value;
                $file_dir_name = dirname(__FILE__);
                if ($error) AfwSession::pushError($error);
                if ($warning) AfwSession::pushWarning($warning);
                if ($info) AfwSession::pushInformation($info);
                if ($success) AfwSession::pushSuccess($success);
                $view_page_full_path = "$file_dir_name/../../../$view_module/$view_page.php";
                if (!file_exists($view_page_full_path)) {
                        $this->renderError("view page not found : $view_page_full_path");
                } else include_once($view_page_full_path);
        }

        public function renderInternal($view_module, $view_name, $data)
        {
                ob_start();
                $this->render($view_module, $view_name, $data);
                return ob_get_clean();
        }

        public function renderHzm($view_name, $object, $data, $structure = "", $objme = null, $public_show = false)
        {
                foreach ($data as $key => $value) $token_arr["[$key]"] = $value;
                $lang = AfwLanguageHelper::getGlobalLanguage();
                if ($view_name == "minibox") echo AfwShowHelper::showMinibox($object, $structure, $lang, $token_arr, $objme, $public_show);
                else $this->renderError("hzm view unknown  : $view_name");
        }

        public function renderConfirm($data, $confirm_view_module = "lib", $confirm_view_name = "confirm")
        {
                $this->render($confirm_view_module, $confirm_view_name, $data);
        }


        public function renderError($error_message, $data = array(), $error_view_module = "lib", $error_view_name = "error", $die = true)
        {
                $data["error_message"] = $error_message;
                $this->render($error_view_module, $error_view_name, $data);
                if ($die) die();
        }

        public function renderErrorAndLogOut($error_message, $suggested_login_page = "", $suggested_login_phrase = "login", $click_here_phrase = "click here")
        {
                AfwSession::logout();
                $lang = AfwLanguageHelper::getGlobalLanguage();
                $error_message = AfwLanguageHelper::tt($error_message, $lang);
                $suggested_login_phrase = AfwLanguageHelper::tt($suggested_login_phrase, $lang);
                $click_here_phrase = AfwLanguageHelper::tt($click_here_phrase, $lang);

                if ($suggested_login_page) $error_message .= " $suggested_login_phrase,<br> <a class='error link' href='$suggested_login_page'>$click_here_phrase</a>";
                $this->renderError($error_message);
        }

        public function renderLogOutMessage($logout_message, $suggested_login_page = "", $suggested_login_phrase = "login", $click_here_phrase = "click here")
        {
                AfwSession::logout();
                $lang = AfwLanguageHelper::getGlobalLanguage();
                $error_message = AfwLanguageHelper::tt($logout_message, $lang);
                $suggested_login_phrase = AfwLanguageHelper::tt($suggested_login_phrase, $lang);
                $click_here_phrase = AfwLanguageHelper::tt($click_here_phrase, $lang);

                if ($suggested_login_page) $error_message .= " $suggested_login_phrase : <br><br> <a class='error link' href='$suggested_login_page'>$click_here_phrase</a>";
                $this->renderError($error_message, array(), "lib", "loggedout", true);
        }

        /**  added below for modern look */

        public function prepareOptions($methodName)
        {
                // can be overriden if the method page need some options featured  
                return [];
        }

        public function prepareMenuTokens($lang, $role, $selected_menu, $options)
        {
                // can be overriden if the method page need some options featured  
                return [];
        }
}
