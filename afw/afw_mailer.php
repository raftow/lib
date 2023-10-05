<?php
define('MAIL_LINE_ENDINGS', isset($_SERVER['WINDIR']) || (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Win32') !== FALSE) ? "\r\n" : "\n");


class AfwMailer extends AFWRoot {

        
        
        private static function headerMail($dir = "rtl", $font_family="courrier")
        {
            return "<html><body dir='$dir' style='font-size:18px;font-family:$font_family'>";
        }
        
        private static function footerMail()
        {
            return "</body></html>";
        }
        

        public static function htmlSimpleMail($module, $key, $to_email_arr, $subject, $bodyHtml, $lang, $dir = "rtl", $font_family="courrier")
        {
            $body = array();
            $body[] = self::headerMail($dir, $font_family);
            $body[] = $bodyHtml;
            $body[] = self::footerMail();

            $send_from = AfwSession::config("send_from", "aa@bb");

            return self::hzmMail($module, $key, $to_email_arr, $subject, $body, $send_from, $format="html", $lang);
        }
        
        
        public static function hzmMail($module, $key, $to,$subject, $body, $send_from_arr, $format, $language="ar")
        {
                $file_dir_name = dirname(__FILE__); 
                $file_dir_mail_package = $file_dir_name."/../mail";
    
            
                if(is_array($send_from_arr))
                {
                        $send_from = $send_from_arr['From'];
                }
                else
                {
                        $send_from = $send_from_arr;
                }
                
                // obso by rafik $send_from = variable_get('site_mail', variable_get('sendmail_from', $default_from));
                // die("send_from = ".$send_from);
                if(!is_array($body))
                {
                    $body_arr = array();
                    $body_arr[] = $body;
                }
                else $body_arr = $body;
                
                if(!is_array($to))
                {
                    $to_imploded_list = $to;
                }
                else $to_imploded_list = implode(",",$to);
                
                
                $params = array();
        
                // Bundle up the variables into a structured array for altering.
                $message = array(
                    'id'       => $module . '_' . $key,
                    'module'   => $module,
                    'key'      => $key,
                    'to'       => $to_imploded_list,
                    'from'     => $send_from,
                    'language' => $language,
                    'params'   => $params,
                    'send'     => TRUE,
                    'subject'  => $subject,
                    'body'     => $body_arr
                );
                
                if($format=="html") $format="text/html; charset=UTF-8; format=flowed;";
                if($format=="text") $format="text/plain; charset=UTF-8; format=flowed; delsp=yes";
                
                // Build the default headers
                $headers = array(
                    'MIME-Version'              => '1.0',
                    'Content-Type'              => $format,       
                    'Content-Transfer-Encoding' => '8Bit',
                    'X-Mailer'                  => 'HZM Framework'
                );
                
                if(is_array($send_from_arr))
                {
                        $headers['From'] = $send_from_arr['From'];
                        $headers['Sender'] = $send_from_arr['Sender'];
                        $headers['Return-Path'] = $send_from_arr['Return-Path'];
                }
                else
                {
                        $send_from = $send_from_arr;
                        $headers['From'] = $send_from;
                        $headers['Sender'] = $send_from;
                        $headers['Return-Path'] = $send_from;
                }
                
                $message['headers'] = $headers;


                require_once("$file_dir_mail_package/mailer.inc.php");
                require_once("$file_dir_mail_package/smtp.transport.php");
                require_once("$file_dir_mail_package/smtp.mail.php");
                
                
                $mailerSystem = new SmtpMailSystem();
                $message = $mailerSystem->format($message);
                $message['result'] = $mailerSystem->mail($message);
                if(!$message['result']) $message['error'] = $mailerSystem->errorInfo; //var_export($this->smtp->error,true)
            
                return $message;
        
        } 


        
        
        
         
}