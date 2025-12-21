<?php

// old require of afw_root
// alter table hijra_date_base add unique key(HIJRI_YEAR,HIJRI_MONTH);
class AfwApiConsumeHelper 
{

    private static function consume_complex_api($bearer, $url, $token, $proxy = null , $data = null, $verify_host=false, $verify_pear=false, $return_transfer=true)
    {
        global $print_full_debugg, $print_error;
        if($curl = curl_init())
        {
                // echo "option CURLOPT_URL setted\n";
                
                
                if ($data) 
                {
                   $params = http_build_query($data);
                   $p_url = sprintf("%s?%s", $url, $params);
                   // curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                }
                else
                {
                   $p_url = $url;
                }
                
                $url = $p_url;

                // echo "initilized\n";
                curl_setopt($curl, CURLOPT_URL, $url);
                if($bearer)
                {
                    $authorization = "Authorization: Bearer $token";
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
                }
                // echo "option CURLOPT_HTTPHEADER setted\n";
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, $return_transfer);
                // echo "option CURLOPT_RETURNTRANSFER setted\n";

                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $verify_host);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verify_pear);
                if($proxy=="default")
                {
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                }
                elseif($proxy)
                {
                        $proxy_arr = explode('|', $proxy);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl, CURLOPT_PROXY, $proxy_arr[0]);
                        curl_setopt($curl, CURLOPT_PROXYPORT, $proxy_arr[1]);
                        //curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        
                }
                else
                {
                    curl_setopt($curl, CURLOPT_NOPROXY, "*");                    
                    $proxy_arr = array();
                }
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');                    


                // echo "executing\n";
                $error_msg = "";
                $result = curl_exec($curl);
                if($print_full_debugg) AfwBatch::print_debugg("API RESULT :\n [$result]\n");
                if(!trim($result))
                {
                    $error_msg .= "API Error result is empty";
                }
                
                $error_msg .= curl_error($curl);
                if($error_msg)
                {
                    $error_msg = "Error while doing curl_exec on $url / $authorization / proxy=".$proxy_arr[0]." port=".$proxy_arr[1] . " => " . $error_msg;
                    if($print_error) AfwBatch::print_error("error_msg : $error_msg");
                }
                
                if(!$error_msg)  $success = true; else $success = false;
                curl_close($curl);
                
                if($success) $decoded_result = json_decode($result); else $decoded_result = null;
                
                return array('url' => $p_url, 'success' => $success, 'message' => $error_msg, 'result' => $decoded_result);
        }
        else
        {
               
                $error_msg = "Error while doing curl_init";
                return array('url' => null, 'success' => false, 'message' => $error_msg, 'result' => null);
        }
        
    
    
    }

    public static function consume_bearer_api($url, $token, $proxy = null , $data = null, $verify_host=false, $verify_pear=false, $return_transfer=true)
    {
        return self::consume_complex_api(true, $url, $token, $proxy, $data, $verify_host, $verify_pear, $return_transfer);
    }

    public static function consume_normal_api($url, $proxy = null, $data = null, $verify_host=false, $verify_pear=false, $return_transfer=true)
    {
        return self::consume_complex_api(false, $url, "", $proxy, $data, $verify_host, $verify_pear, $return_transfer);
    }
}

?>