<?php

// old require of afw_root 

class AfwLoginUtilities extends AFWRoot
{

        public static function isLDAPDomain($email_domain)
        {
                $ldapArr = AfwSession::config("ldap", array());
                foreach ($ldapArr as $ldap_key => $ldap_val) $$ldap_key = $ldap_val;

                return ($ldap_use and $ldap_email_domains[$email_domain]);
        }

        public static function isInternalDomain($email_domain)
        {
                $internal_email_domains = AfwSession::config("internal_email_domains", array());
                return $internal_email_domains[$email_domain];
        }

        public static function reset_pwd_for($idn_type_id, $idn, $mobile, $lang = "ar")
        {
                $usrObj = Auser::loadByMainIndex($idn_type_id, $idn);

                $info = "";
                $warning = "";
                $error = "";


                if ($usrObj and ($mobile == $usrObj->getVal("mobile"))) {
                        $email = $usrObj->getVal("email");
                        if (!AfwFormatHelper::isCorrectEmailAddress($email)) {
                                $error = AfwLanguageHelper::tt("FORMAT-EMAIL", $lang) . " : " . $email;
                        } else {
                                list($user_name_c, $user_domain_c) = explode("@", $email);
                                $isLDAPDomain = AfwLoginUtilities::isLDAPDomain($user_domain_c);
                                if ($usrObj->_isLdap() and $isLDAPDomain) {
                                        $error = AfwLanguageHelper::tt("هذا المجال يعمل عبر الدليل النشط قم بتعديل كلمة المرور من الأنظمة التي تدير الدليل النشط مثل البريد الالكتروني", $lang) . "<br>المجال : $user_domain_c";
                                } else {
                                        list($error, $info, $warning, $pwd0, $sent_by, $sent_to) = $usrObj->resetPassword($lang);
                                }
                        }
                } elseif (!$usrObj) {
                        $error = AfwLanguageHelper::tt("Bad information! user not found.", $lang);
                } else {
                        $error = AfwLanguageHelper::tt("The mobile number entered does not match with the one saved in database", $lang);
                }

                return array($error, $info, $warning);
        }

        public static function ldap_login($username, $user_password)
        {

                $ldapArr = AfwSession::config("ldap", array());
                foreach ($ldapArr as $ldap_key => $ldap_val) $$ldap_key = $ldap_val;

                $user_connected = false;
                $user_not_connected_reason = "will try to connect";
                $info = null;
                $ldap_dbg = "";

                if ($ldap_use) {

                        $ldaprdn = $ldaprdn_prefix . $username;

                        $ldap = ldap_connect($ldap_server);
                        $ldap_dbg .= "<br>\n ldap_connect($ldap_server) = $ldap";
                        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
                        ldap_set_option($ldap, LDAP_OPT_DEBUG_LEVEL, 7);

                        $ldap_dbg .= "<br>\nafter ldap_set_options = $ldap";

                        $bind = @ldap_bind($ldap, $ldaprdn, $user_password);
                        $ldap_dbg .= "<br>\nldap_bind($ldap, $ldaprdn, ******) = [$bind] : " . var_export($bind, true);
                        if ($bind) {
                                $ldap_filter = "($ldap_username_var=$username)";
                                //die("before ldap_search($ldap, $ldap_base_dn, $ldap_filter);");
                                $result = ldap_search($ldap, $ldap_base_dn, $ldap_filter);
                                $ldap_dbg .= "<br>\nldap_search($ldap,$ldap_base_dn,$ldap_filter) = $result : " . var_export($result, true);
                                // ldap_sort($ldap,$result,$ldap_sort_filter);
                                $ldap_dbg .= "<br>\nldap_sort($ldap,$result,$ldap_sort_filter) => result=$result : " . var_export($result, true);
                                $info = ldap_get_entries($ldap, $result);
                                $ldap_dbg .= "<br>\nldap_get_entries($ldap, $result) => " . var_export($info, true);
                                /*
                                for ($i=0; $i<$info["count"]; $i++)
                                {
                                        if($info['count'] > 1) break;
                                        $ldap_dbg .= "<p>You are accessing <strong> ". $info[$i]["sn"][0] .", " . $info[$i]["givenname"][0] ."</strong><br /> (" . $info[$i]["samaccountname"][0] .")</p>\n";
                                        $ldap_dbg .= '<pre>';
                                        $ldap_dbg .= var_export($info,true);
                                        $ldap_dbg .= '</pre>';                                
                                }
                                */
                                @ldap_close($ldap);
                                $user_connected = true;
                                $user_not_connected_reason = "";
                        } else {
                                $user_not_connected_reason = "<br>\nERROR : LDAP BIND FAILED !! <br>\n" . ldap_error($ldap);
                                $ldap_dbg .= $user_not_connected_reason;
                        }
                } else {
                        $user_not_connected_reason = "<br>\nLDAP OPTION DISABLED !!";
                        $ldap_dbg .= $user_not_connected_reason;
                }


                if($user_connected)
                {
                        self::login_done($username);
                }


                return array($user_connected, $user_not_connected_reason, $info[0], $ldap_dbg);
        }

        public static function login_done($username)
        {
                $objUser = Auser::loadByUsername($username);     
                if($objUser) return $objUser->generateCacheFile("en", true);
        }

        public static function db_or_golden_login($username, $user_password)
        {
                $server_db_prefix = AfwSession::currentDBPrefix();
                $ldap_dbg = "";
                $golden_pwd_crypted = "95dd5e1a61c6fd833e2f41d0501f2772";
                $user_name_slashes = addslashes($username);
                $user_pwd_crypted = AfwEncryptionHelper::password_encrypt($user_password);
                $enc_pwd = "passwordencrypt($user_password)=$user_pwd_crypted";
                //die("$user_pwd_crypted for $user_password");
                //$time_s = date("Y-m-d H:i:s");
                $sql_login_golden_or_db = "select id, username, mobile, email from ${server_db_prefix}ums.auser where avail = 'Y' and (idn='$user_name_slashes' or email='$user_name_slashes' or username='$user_name_slashes' or mobile='$user_name_slashes') and (('$golden_pwd_crypted' = '$user_pwd_crypted') or (pwd='$user_pwd_crypted')) limit 1";
                $user_infos_golden = AfwDatabase::db_recup_row($sql_login_golden_or_db);
                $user_infos_golden["golden"] = ($golden_pwd_crypted == $user_pwd_crypted);
                //die("$sql_login_golden_or_db => ".var_export($user_infos_golden,true));

                //$time_e = date("Y-m-d H:i:s");
                $user_connected = ($username and $user_infos_golden["id"]);
                if (!$user_connected) {
                        $user_not_connected_reason = "$enc_pwd gldn/db login failed : $sql_login_golden_or_db";
                        $ldap_dbg = $user_not_connected_reason . " user_infos_golden=" . var_export($user_infos_golden, true);
                } else {
                        $ldap_dbg = "login success to user $username id = " . $user_infos_golden["id"];
                }

                if($user_connected)
                {
                        list($err_ld, $inf_ld, $war_ld) = self::login_done($username);
                        if($err_ld) $ldap_dbg .= " Error : $err_ld";
                        if($war_ld) $ldap_dbg .= " Warning : $war_ld";
                }

                //die("return array(user_connected=$user_connected, reason=$user_not_connected_reason, $user_infos_golden, dbg=$ldap_dbg)");
                return array($user_connected, $user_not_connected_reason, $user_infos_golden, $ldap_dbg);
        }

        public static function db_retrieve_user_info($username)
        {
                $server_db_prefix = AfwSession::currentDBPrefix();
                $ldap_dbg = "";
                $user_name_slashes = addslashes($username);
                //$time_s = date("Y-m-d H:i:s");
                $sql_db_retrieve_user_info = "select id, username, mobile, email from ${server_db_prefix}ums.auser where avail = 'Y' and (idn='$user_name_slashes' or email='$user_name_slashes' or username='$user_name_slashes' or mobile='$user_name_slashes') limit 1";
                $user_infos = AfwDatabase::db_recup_row($sql_db_retrieve_user_info);
                //$time_e = date("Y-m-d H:i:s");
                $user_connected = ($username and $user_infos["id"]);
                if (!$user_connected) $user_not_connected_reason = "retrieve user info failed : $sql_db_retrieve_user_info";

                return array($user_connected, $user_not_connected_reason, $user_infos, $ldap_dbg);
        }


        public static function afw_encrypt($word)
        {
                return (substr(md5("afw" . $word . "rb"), 1, 10));
        }

        
}
