<?php

// old require of afw_root 

class UfwSmsSender extends AFWRoot {

        public static function verifyCode()
        {
                return round(rand(1001,9998));
        }

        public static function loadCompanyNotificationSender()
        {
                $main_company = AfwSession::currentCompany();
                $company_notification_sender_file = 'company_notification_sender';
                $company_notification_sender_class = AfwStringHelper::tableToClass($company_notification_sender_file);
                if (!class_exists($company_notification_sender_class, false)) {
                        $file_dir_name = dirname(__FILE__);
                        require($file_dir_name . "/../../../client-$main_company/$company_notification_sender_file.php");
                }

                return $company_notification_sender_class;
        }

         // @todo we need a specific application ID for company-crm

        /**
         * @param string $mobile
         * @param string $message
         */
        public static function sendSMS($mobile, $message, $encoding = "utf-8")
        {
                $company_notification_sender_class = self::loadCompanyNotificationSender();
                if (class_exists($company_notification_sender_class, false)) {
                        return $company_notification_sender_class::sendSMS($mobile, $message, $encoding);
                } else {
                        throw new AfwRuntimeException("class " . $company_notification_sender_class . " not found !");
                }
        }

         /**
         * @param array $payload  ex ["email"=>"", "mobile"=>"", "body"=>"", "subject"=>""]
         */
        public static function sendNotification($payload, $encoding = "utf-8")
        {       
                $company_notification_sender_class = self::loadCompanyNotificationSender();
                if (class_exists($company_notification_sender_class, false)) {
                        return $company_notification_sender_class::sendNotification($payload, $encoding);
                } else {
                        throw new AfwRuntimeException("class " . $company_notification_sender_class . " not found !");
                }
        }


        /**
         * @param string $mobile
         */
        public static function partialShowMobile($mobile)
        {
                if ($mobile) {
                        $len = strlen($mobile);
                        if ($len > 4) {
                                $partial_mobile = substr($mobile, 0, 2) . str_repeat("*", $len - 5) . substr($mobile, -3);
                                return $partial_mobile;
                        } else {
                                return str_repeat("*", $len);
                        }
                } else {
                        return "";
                }
        }

        /**
         * @param string $email_adress
         */
        public static function partialShowEmail($email_adress)
        {
                if ($email_adress) {
                        list($email, $domain) = explode("@", $email_adress);
                        $len = strlen($email);
                        if ($len > 5) {
                                $partial_email = substr($email, 0, 2) . str_repeat("*", $len - 5) . substr($email, -3);
                                return $partial_email."@" . $domain;
                        } else {
                                return str_repeat("*", $len);
                        }
                } else {
                        return "";
                }
        }
                        
}
