<?php

// old require of afw_root 

class AfwSmsSender extends AFWRoot {

        public static function verifyCode()
        {
                return round(rand(1001,9998));
        }

         // @todo we need a specific application ID for company-crm

        public static function sendSMS($mobile, $message, $username = "company-crm-2factor", $application_id = 40)
        {
                global $lang;
                if(AfwSession::config("sms_simulation_mode", false))
                {
                        AfwSession::pushInformation("SMS simuation to $mobile :<br>".$message); 
                        $sms_ok = false;
                        $sms_info = "only simulated with push information";
                        return array($sms_ok, $sms_info);
                }
                else
                {
                        $info = "";
                        
                        $mobile = AfwFormatHelper::formatMobile($mobile);
                        
                        $mobile_error = AfwFormatHelper::mobileError($mobile, $lang);

                        $body = $message;
                        $error_details_if_failed = " failed to send to mobile=$mobile the body=$body";
                        
                        if($mobile_error) return array(false, "mobile format error : ".$mobile_error.$error_details_if_failed);
                        
                        $res = self::hzmSMS($mobile, $body, $username, $application_id);
                        $the_username = $res->SmsUser;
                        $error_details_if_failed .= " result of hzmSMS : " . var_export($res,true);
                        
                        if($res->SendSMSResult=='TRUE')
                        {
                                $info = "SMS sent successfully to $mobile with reponsible user name [$the_username] ".date("Y-m-d H:i:s");
                                
                                return array(true, $info);
                        }
                        elseif($res->SendSMSResult == 'الرسالة مرسلة من قبل')
                        {
                                return array(false, $res->SendSMSResult.$error_details_if_failed);
                        }
                        else
                        {
                                return array(false, "failed to send SMS to $mobile with reponsible user name [$the_username] ".date("Y-m-d H:i:s")." sms server api response : [" . $res->SendSMSResult."]".$error_details_if_failed);
                        }
                        
                        
                }
        }


        public static function hzmSMS($mobile_number, $message, $user_name, $application_id, $process_id=1, $encoding="utf-8", $method = 'SendSMS')
        {
                global $smsSender_wsdlUrl;
                $file_dir_hzm = dirname(__FILE__);
                $sms_config_config_file = "$file_dir_hzm/../../external/sms_config.php";
                include($sms_config_config_file);
                if($user_name == "company-crm-2factor") $user_name = $sms_username; 
                                  
                // die("smsSender_wsdlUrl = ".$smsSender_wsdlUrl." from $file_dir_hzm/../../../../external/sms_config.php");
                try 
                { 
                        $opts = array(
                                'http' => array(
                                'user_agent' => 'PHPSoapClient'
                                )
                        );
                        $context = stream_context_create($opts);
                        
                        
                        $soapClientOptions = array(
                                'stream_context' => $context,
                                'cache_wsdl' => 'WSDL_CACHE_NONE',
                                'encoding' => $encoding
                        );
                        
                        
                        $params = array('Number'=>$mobile_number, 'Message'=>$message, 'APPLICATION_ID'=>$application_id, 'PROCESS_ID'=>$process_id, 'USER_NAME'=>$user_name);
                
                        $soapClient = new SoapClient($smsSender_wsdlUrl, $soapClientOptions);  // ' UTF-8'
                        $error = 0;    
                        
                        //$info = $soapClient->__call($method, array($params));
                        $info = $soapClient->$method($params);
                } 
                catch (SoapFault $fault) 
                { 
                        $message = "hzm SMS sender failed with fault : ".var_export($fault,true);
                        AfwSession::log($message);
                        //throw $fault;
                        $error = 1; 
                }
                catch (Exception $e) 
                { 
                        // throw $e;
                        $message = "hzm SMS sender failed with exception : ".var_export($e,true);
                        AfwSession::log($message);
                        $error = 1; 
                }
                
                unset($soapClient);
                
                if ($error == 0) 
                {        
                        //$info->Params = $params;
                        //$info->Config = $sms_config_config_file;
                        $info->SmsUser = $sms_username;
                        return $info;
                }
                else
                {
                        return false;
                }
        }
}


