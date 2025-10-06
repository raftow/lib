<?php

// old require of afw_root 

class AfwNotificationManager extends AFWRoot {

        /**
         * @param AFWObject $object_related
         */
        
        public static function prepareNotificationBody($object_related, $notification_code, $notification_type, $lang, $from_template_file=null, $token_arr=[])
        {
                if(!$from_template_file) // so from translated messages
                {
                        $body_tpl = $object_related->tm($notification_code."_".$notification_type."_notification", $lang);  
                        if($body_tpl == $notification_code."_".$notification_type."_notification") $body_tpl = "";
                        if(!$body_tpl)
                        {
                                $body_tpl = $object_related->tm($notification_code."_default_notification");  
                                if($body_tpl == $notification_code."_default_notification") $body_tpl = "";
                        }
                }
                else
                {
                        $from_template_file = str_replace("[notification_type]", $notification_type, $from_template_file);
                        $from_template_file = str_replace("[notification_code]", $notification_code, $from_template_file);

                        if(!file_exists($from_template_file))
                        {
                                throw new AfwRuntimeException("template $from_template_file not found");
                        }
                        
                        $body_arr = include($from_template_file);

                        if ($body_arr[$lang]) {
                                $body_tpl = $body_arr[$lang];
                        } else {
                                throw new AfwRuntimeException("template $from_template_file does not return the email body for lang=$lang");
                        }
                        

                        if ($body_arr["subject-$lang"]) {
                                $subject_tpl = $body_arr["subject-$lang"];
                        } else {
                                throw new AfwRuntimeException("template $from_template_file does not return the email subject for lang=$lang");
                        }

                        
                }
                
                

                if($body_tpl) 
                {
                        $body_tpl_before = $body_tpl;
                        $body_tpl = $object_related->decodeTpl($body_tpl, $trad_erase=array(),$lang, $token_arr);
                        // die("body_tpl before decodeTpl =$body_tpl_before body_tpl after decodeTpl = $body_tpl token_arr=".var_export($token_arr,true));
                }

                if($subject_tpl) 
                {
                        $subject_tpl_before = $subject_tpl;
                        $subject_tpl = $object_related->decodeTpl($subject_tpl, $trad_erase=array(),$lang, $token_arr);
                        // die("subject_tpl before decodeTpl =$subject_tpl_before subject_tpl after decodeTpl = $subject_tpl token_arr=".var_export($token_arr,true));
                }

                

                return [$body_tpl, $subject_tpl];
        }


        public static function sendNotification($notification_type_settings, $receiver, $notification_code, $object_related, $lang, $from_template_file=null, $token_arr=[], $cc_to=null)
        {
                $return = array();
                $details = "notification_code=$notification_code, receiver= ".var_export($receiver,true)." notification_type_settings = ".var_export($notification_type_settings,true);
                if(!$object_related)
                {
                        AfwRunHelper::safeDie("can't sendNotification without related object", $details);
                } 

                //AfwRunHelper::safeDie("debugg rafik-crm 001 : ", $details);

                if($notification_type_settings["sms"])
                {
                        if(!$receiver["mobile"])  $return["sms"] = array(false, "no receiver mobile number given");
                        else
                        {
                                // get the notification body message template
                                list($body_sms,) = self::prepareNotificationBody($object_related, $notification_code, "sms", $lang, $from_template_file, $token_arr);        

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
                                list($body_email, $email_subject) = self::prepareNotificationBody($object_related, $notification_code, "email", $lang, $from_template_file, $token_arr);        

                                if(!$body_email) $return["email"] = array(false, "no email notification body template given for $notification_code");
                                else
                                {
                                        $to_email_arr = array();
                                        $to_email_arr[] = $receiver["email"];
                                        if($cc_to) $to_email_arr[] = $cc_to;
                                        
                                        if(!$email_subject) $email_subject = $object_related->tm("auto-notification-".$notification_code);
                                        // send email to receiver 
                                        $res = AfwMailer::htmlSimpleMail($notification_code,"NOTIFY-BY-EMAIL-".$receiver["email"], $to_email_arr, $email_subject, $body_email, $lang);

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


