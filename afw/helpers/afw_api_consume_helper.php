<?php

// old require of afw_root
// alter table hijra_date_base add unique key(HIJRI_YEAR,HIJRI_MONTH);
class AfwApiConsumeHelper 
{

    private static function consume_complex_api($bearer, $url, $token, $proxy = null , $data = null, 
                    $verify_host=null, $verify_pear=null, $return_transfer=true, 
                    $encoding='', $method='GET', $maxredirs=10, $timeout=0, $followlocation=true, $http_version=null,
                    $http_header_array = ['accept: application/json', 'Content-Type: application/json'],
                    $print_full_debugg=false, $print_error=false)
    {        
        $curl_commands = [];
        if($curl = curl_init())
        {
                $curl_commands[] = "\$curl = curl_init(); // success";
                // echo "option CURLOPT_URL setted\n";
                
                
                if ($data) 
                {
                   $params = http_build_query($data);
                   $p_url = sprintf("%s?%s", $url, $params);
                }
                else
                {
                   $p_url = $url;
                }
                
                $url = $p_url;
                if($print_full_debugg) AfwBatch::print_comment("using params : " . var_export($data, true));
                if($print_full_debugg) AfwBatch::print_comment("consuming url : " . $url);
                $curl_options = array();
                // echo "initilized\n";
                $curl_options[CURLOPT_URL] = $url;
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_URL, '$url');";
                
                if($bearer)
                {
                    $http_header_array[] = "Authorization: Bearer $token";                    
                }

                
                $curl_options[CURLOPT_HTTPHEADER] = $http_header_array;
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_HTTPHEADER, ".var_export($http_header_array, true).");";
                // echo "option CURLOPT_HTTPHEADER setted\n";
                $curl_options[CURLOPT_RETURNTRANSFER] = $return_transfer;
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_RETURNTRANSFER, ".var_export($return_transfer, true).");";
                // echo "option CURLOPT_RETURNTRANSFER setted\n";
                if($verify_host !== null)
                {
                    $curl_options[CURLOPT_SSL_VERIFYHOST] = $verify_host;
                    // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_SSL_VERIFYHOST, ".var_export($verify_host, true).");";
                }
                if($verify_pear !== null)
                {
                    $curl_options[CURLOPT_SSL_VERIFYPEER] = $verify_pear;
                    // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_SSL_VERIFYPEER, ".var_export($verify_pear, true).");";                
                }
                $curl_options[CURLOPT_ENCODING] = $encoding;
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_ENCODING, ".var_export($encoding, true).");";
                $curl_options[CURLOPT_MAXREDIRS] = $maxredirs;
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_MAXREDIRS, ".var_export($maxredirs, true).");";
                $curl_options[CURLOPT_TIMEOUT] = $timeout;
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_TIMEOUT, ".var_export($timeout, true).");";
                $curl_options[CURLOPT_FOLLOWLOCATION] = $followlocation;
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_FOLLOWLOCATION, ".var_export($followlocation, true).");";
                if(!$http_version) $http_version = CURL_HTTP_VERSION_1_1; 
                $curl_options[CURLOPT_HTTP_VERSION] = $http_version;
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_HTTP_VERSION, ".var_export($http_version, true).");";
                
                
                if($proxy=="default")
                {
                     $proxy="";
                }
                
                if($proxy and ($proxy!="*"))
                {
                        list($proxy_host, $proxy_port, $proxy_credentials) = explode('|', $proxy);
                        
                        $curl_options[CURLOPT_PROXY] = $proxy_host;
                        // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_PROXY, ".var_export($proxy_host, true).");";
                        $curl_options[CURLOPT_PROXYPORT] = $proxy_port;

                        if($proxy_credentials)
                        {
                            $curl_options[CURLOPT_PROXYUSERPWD] = $proxy_credentials;                            
                        }


                        // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_PROXYPORT, ".var_export($proxy_port, true).");";
                        //$curl_options[CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        
                }
                elseif($proxy=="*")
                {
                    $curl_options[CURLOPT_NOPROXY] = "*";                    
                    // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_NOPROXY, \"*\");";
                }
                $curl_options[CURLOPT_CUSTOMREQUEST] = $method;             
                // $curl_commands[] = "curl_setopt(\$curl, CURLOPT_CUSTOMREQUEST, '$method');";       

                curl_setopt_array($curl, $curl_options);
                $curl_commands[] = "curl_setopt_array(\$curl, ".var_export($curl_options, true).");";
                // echo "executing\n";
                $error_msg = "";
                $response = curl_exec($curl);
                // $response = '{"data":[{"code":"0100","name":"Riyadh","region_code":"0001","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0101","name":"Makkah Al-Mukarramah","region_code":"0002","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0103","name":"Jeddah","region_code":"0002","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0104","name":"Taif","region_code":"0002","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0105","name":"Al-Madinah Al-Munawwarah","region_code":"0003","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0106","name":"Yanbu","region_code":"0003","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0107","name":"Buraidah","region_code":"0004","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0108","name":"Unaizah","region_code":"0004","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0109","name":"Khamis Mushait","region_code":"0006","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null},{"code":"0110","name":"Tabuk","region_code":"0007","created_at":"2024-03-04 14:17:51","updated_at":"2025-10-13 10:58:28","deleted_at":null}],"meta":{"current_page":1,"from":1,"last_page":18,"per_page":10,"to":10,"total":177}}';
                $curl_commands[] = "\$response = curl_exec(\$curl);";       
                if($print_full_debugg) AfwBatch::print_debugg("API RESULT :\n $response\n");
                if(!trim($response))
                {
                    $error_msg .= "API Error : the response is empty ";
                }
                
                
                $error_msg .= curl_error($curl);
                $curl_commands[] = "\$error_msg = curl_error(\$curl);";       
                if($error_msg)
                {
                    $http_header_array_text = implode(" || ", $http_header_array);
                    $error_msg = "Error while doing curl_exec method=$method on url= $url / header = $http_header_array_text / proxy=$proxy_host port=".$proxy_port." => response=$response => error message : " . $error_msg;
                    if($print_error) AfwBatch::print_error("error_msg : $error_msg");
                }
                
                
                if(!$error_msg)  $success = true; else $success = false;
                
                curl_close($curl);
                
                if($success) $decoded_response = json_decode($response); else $decoded_response = null;
                
                return array('url' => $p_url, 'success' => $success, 'message' => $error_msg, 'result' => $decoded_response, 'commands'=>$curl_commands, 'response' => $response);
        }
        else
        {
                $decoded_response = new stdClass();
                $curl_commands[] = "\$curl = curl_init(); // failed";               
                $response = $error_msg = "Error while doing curl_init";
                return array('url' => null, 'success' => false, 'message' => $error_msg, 'result' => $decoded_response, 'commands'=>$curl_commands, 'response'=>$response);
        }
        
    
    
    }

    public static function consume_bearer_api($url, $token, $proxy = null , $data = null, 
                    $verify_host=false, $verify_pear=false, $return_transfer=true, 
                    $encoding='', $method='GET', $maxredirs=10, $timeout=0, $followlocation=true, $http_version=null,
                    $http_header_array = ['accept: application/json', 'Content-Type: application/json'],
                    $print_full_debugg=false, $print_error=false)
    {
        return self::consume_complex_api(true, $url, $token, $proxy, $data, $verify_host, $verify_pear, $return_transfer, $encoding, $method, $maxredirs, $timeout, $followlocation, $http_version, $http_header_array, $print_full_debugg, $print_error);
    }

    public static function consume_normal_api($url, $proxy = null, $data = null, 
                    $verify_host=false, $verify_pear=false, $return_transfer=true, 
                    $encoding='', $method='GET', $maxredirs=10, $timeout=0, $followlocation=true, $http_version=null,
                    $http_header_array = ['accept: application/json', 'Content-Type: application/json'],
                    $print_full_debugg=false, $print_error=false)
    {
        return self::consume_complex_api(false, $url, "",    $proxy, $data, $verify_host, $verify_pear, $return_transfer, $encoding, $method, $maxredirs, $timeout, $followlocation, $http_version, $http_header_array, $print_full_debugg, $print_error);
    }


    public static function runAPI($url, &$object, $params_attribute="input", $lang = "ar")
    {
        $bearer_token = AfwSettingsHelper::readSettingValue($object,"bearer_token",null);
        $proxy = AfwSettingsHelper::readSettingValue($object,"proxy",null);
        $data = AfwSettingsHelper::readParamsArray($object, $params_attribute);
        $verify_host = AfwSettingsHelper::readSettingValue($object,"verify_host",null);
        $verify_pear = AfwSettingsHelper::readSettingValue($object,"verify_pear",null);
        $return_transfer = AfwSettingsHelper::readSettingValue($object,"return_transfer",true);
        $method = AfwSettingsHelper::readSettingValue($object,"method",'GET');
        $maxredirs = AfwSettingsHelper::readSettingValue($object,"maxredirs",10);
        $timeout = AfwSettingsHelper::readSettingValue($object,"timeout",0);
        $followlocation = AfwSettingsHelper::readSettingValue($object,"followlocation",true);
        $http_version = AfwSettingsHelper::readSettingValue($object,"http_version",null);
        $encoding = AfwSettingsHelper::readSettingValue($object,"encoding",'');
        $print_full_debugg = AfwSettingsHelper::readSettingValue($object,"print_full_debugg",true);
        $print_error = AfwSettingsHelper::readSettingValue($object,"print_error",true);
        
        // $xxxxx = AfwSettingsHelper::readSettingValue($object,"xxxxx",def-xxxxx);
        $http_header_array = AfwSettingsHelper::readSettingValue($object, "http_header", ['accept: application/json', 'Content-Type: application/json'],"settings",true);
        if($bearer_token)
        {
            $res = AfwApiConsumeHelper::consume_bearer_api(
                $url,
                $bearer_token,
                $proxy,
                $data,
                $verify_host,
                $verify_pear,
                $return_transfer,
                $encoding,
                $method,
                $maxredirs,
                $timeout,
                $followlocation,
                $http_version,
                $http_header_array,
                $print_full_debugg, 
                $print_error);
        }
        else
        {
            $res = AfwApiConsumeHelper::consume_normal_api($url,
                $proxy,
                $data,
                $verify_host,
                $verify_pear,
                $return_transfer,
                $encoding,
                $method,
                $maxredirs,
                $timeout,
                $followlocation,
                $http_version,
                $http_header_array,
                $print_full_debugg, 
                $print_error);
        }

        return $res;
    }
}

?>