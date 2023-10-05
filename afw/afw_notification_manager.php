<?php

// old require of afw_root 

class AfwNotificationManager extends AFWRoot {

        
        private static function prepareNotificationBody($object_related, $notification_code, $notification_type, $lang)
        {
                $body_tpl = $object_related->tm($notification_code."_${notification_type}_notification", $lang);  
                if($body_tpl == $notification_code."_${notification_type}_notification") $body_tpl = "";
                if(!$body_tpl)
                {
                        $body_tpl = $object_related->tm($notification_code."_default_notification");  
                        if($body_tpl == $notification_code."_default_notification") $body_tpl = "";
                }

                if($body_tpl) $body_tpl = $object_related->decodeTpl($body_tpl, $trad_erase=array(),$lang);

                return $body_tpl;
        }


        public static function sendNotification($notification_type_settings, $receiver, $notification_code, $object_related, $lang)
        {
                $return = array();
                $details = "notification_code=$notification_code, receiver= ".var_export($receiver,true)." notification_type_settings = ".var_export($notification_type_settings,true);
                if(!$object_related)
                {
                        self::safeDie("can't sendNotification without related object", $details);
                } 

                //self::safeDie("debugg rafik-crm 001 : ", $details);

                if($notification_type_settings["sms"])
                {
                        if(!$receiver["mobile"])  $return["sms"] = array(false, "no receiver mobile number given");
                        else
                        {
                                // get the notification body message template
                                $body_sms = self::prepareNotificationBody($object_related, $notification_code, "sms", $lang);        

                                if(!$body_sms) $return["sms"] = array(false, "no sms notification body template given for $notification_code");
                                else
                                {
                                     // send SMS to receiver       
                                     list($sms_ok, $sms_info) = AfwSmsSender::sendSMS($receiver["mobile"], $body_sms);
                                     $sms_info_export = var_export($sms_info,true);   
                                     $return["sms"] = array($sms_ok, $sms_info_export, $body_sms);                                     
                                }
                        }
                }


                if($notification_type_settings["email"])
                {
                        if(!$receiver["email"])  $return["email"] = array(false, "no receiver email adress given");
                        else
                        {
                                // get the notification body message template
                                $body_email = self::prepareNotificationBody($object_related, $notification_code, "email", $lang);        

                                if(!$body_email) $return["email"] = array(false, "no email notification body template given for $notification_code");
                                else
                                {
                                        $to_email_arr = array();
                                        $to_email_arr[] = $receiver["email"];
                                        // send email to receiver 
                                        $res = AfwMailer::htmlSimpleMail($notification_code,"NOTIFY-BY-EMAIL-".$receiver["email"], $to_email_arr, "auto-notification-".$notification_code, $body_email, $lang);

                                        $email_ok = $res["result"];
                                        $email_info_export = $res["error"];
                                     
                                        $return["email"] = array($email_ok, $email_info_export, $body_email);                                     
                                }
                        }
                }

                if($notification_type_settings["web"])
                {
                        $return["web"] = array(false, "web notification not yet implemented");   
                }

                if($notification_type_settings["whatsup"])
                {
                        $return["whatsup"] = array(false, "whatsup notification not yet implemented");   
                }





                return $return;
        }


        
}


