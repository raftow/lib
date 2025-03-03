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


        public static function hzmSMS($mobile_number, $message, $encoding="utf-8")
        {
                global $smsSender_wsdlUrl;
                $file_dir_hzm = dirname(__FILE__);
                $sms_config_config_file = "$file_dir_hzm/../../config/sms_config.php";
                $arrConfig = include($sms_config_config_file);
                if(!$arrConfig["type"]) $arrConfig["type"]="soap";
                if($arrConfig["type"]=="soap")
                {
                        return self::soapSMS($mobile_number, $message, $arrConfig, $encoding);
                }

                throw new AfwRuntimeException("sms api type ".$arrConfig["type"]." not implemented !");
        }


        public static function soapSMS($mobile_number, $message, $arrConfig, $encoding="utf-8")
        {        
                // 
                // obso : if($user_name == "company-crm-2factor") $user_name = $sms_username; 
                                  
                
                foreach($arrConfig as $key => $val) $$key = $val;
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
                        
                        $NumberCol = $params['MOBILE-NUMBER-PARAM'];
                        if(!$NumberCol) $NumberCol = "Number";

                        $MessageCol = $params['MESSAGE-BODY-PARAM'];
                        if(!$MessageCol) $MessageCol = "Message";
                        
                        foreach($hard_params as $key => $val) $soapParams[$key] = $val;
                        $soapParams = array($NumberCol=>$mobile_number, $MessageCol=>$message, );
                
                        $soapClient = new SoapClient($smsSender_wsdlUrl, $soapClientOptions);  // ' UTF-8'
                        $error = 0;    
                        
                        //$info = $soapClient->__call($method, array($params));
                        $info = $soapClient->$method($soapParams);
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
                        //$info->Params = $soapParams;
                        return $info;
                }
                else
                {
                        return false;
                }
        }
}


