<?php
   //define('MAIL_LINE_ENDINGS', isset($_SERVER['WINDIR']) || (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Win32') !== FALSE) ? "\r\n" : "\n");
   
   /*
   SMS_PROCESS_ID
            1 : survey invitations
            2 : customer login/register (CRM)
            3 : student login/register (talent)
   
   
   
   */
   
   function hzmSMS($mobile_number, $message, $user_name, $application_id, $process_id=1, $encoding="utf-8", $method = 'SendSMS')
   {
       global $sms_servers_load_balancing_arr, $smsSender_wsdlUrl;
           
            $file_dir_hzm = dirname(__FILE__);
            include("$file_dir_hzm/../../../external/sms_config.php");
             
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
                        'cache_wsdl' => WSDL_CACHE_NONE,
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
                echo var_export($fault,true);
                $error = 1; 
            }
            catch (Exception $e) 
            { 
                echo var_export($e,true);
                $error = 1; 
            }
             
            unset($soapClient);
        
            if ($error == 0) 
            {        
                return $info;
            }
            else
            {
                return false;
            }
        
        }
        /*
        ......
        
        $message['headers'] = $headers;
        
        $mailerSystem = new SmtpMailSystem();
        $message = $mailerSystem->format($message);
        $message['result'] = $mailerSystem->mail($message);
        if(!$message['result']) $message['error'] = $mailerSystem->errorInfo; //var_export($this->smtp->error,true)
    
        return $message;
   
        */

?>