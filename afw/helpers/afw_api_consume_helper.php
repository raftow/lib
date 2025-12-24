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

                // echo "initilized\n";
                curl_setopt($curl, CURLOPT_URL, $url);
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_URL, '$url');";
                
                if($bearer)
                {
                    $http_header_array[] = "Authorization: Bearer $token";                    
                }

                
                curl_setopt($curl, CURLOPT_HTTPHEADER, $http_header_array);
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_HTTPHEADER, ".var_export($http_header_array, true).");";
                // echo "option CURLOPT_HTTPHEADER setted\n";
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, $return_transfer);
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_RETURNTRANSFER, ".var_export($return_transfer, true).");";
                // echo "option CURLOPT_RETURNTRANSFER setted\n";
                if($verify_host !== null)
                {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $verify_host);
                    $curl_commands[] = "curl_setopt(\$curl, CURLOPT_SSL_VERIFYHOST, ".var_export($verify_host, true).");";
                }
                if($verify_pear !== null)
                {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verify_pear);
                    $curl_commands[] = "curl_setopt(\$curl, CURLOPT_SSL_VERIFYPEER, ".var_export($verify_pear, true).");";                
                }
                curl_setopt($curl, CURLOPT_ENCODING, $encoding);
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_ENCODING, ".var_export($encoding, true).");";
                curl_setopt($curl, CURLOPT_MAXREDIRS, $maxredirs);
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_MAXREDIRS, ".var_export($maxredirs, true).");";
                curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_TIMEOUT, ".var_export($timeout, true).");";
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $followlocation);
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_FOLLOWLOCATION, ".var_export($followlocation, true).");";
                if(!$http_version) $http_version = CURL_HTTP_VERSION_1_1; 
                curl_setopt($curl, CURLOPT_HTTP_VERSION, $http_version);
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_HTTP_VERSION, ".var_export($http_version, true).");";
                
                
                if($proxy=="default")
                {
                     $proxy="";
                }
                
                if($proxy and ($proxy!="*"))
                {
                        list($proxy_host, $proxy_port) = explode('|', $proxy);
                        curl_setopt($curl, CURLOPT_PROXY, $proxy_host);
                        $curl_commands[] = "curl_setopt(\$curl, CURLOPT_PROXY, ".var_export($proxy_host, true).");";
                        curl_setopt($curl, CURLOPT_PROXYPORT, $proxy_port);
                        $curl_commands[] = "curl_setopt(\$curl, CURLOPT_PROXYPORT, ".var_export($proxy_port, true).");";
                        //curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        
                }
                elseif($proxy=="*")
                {
                    curl_setopt($curl, CURLOPT_NOPROXY, "*");                    
                    $curl_commands[] = "curl_setopt(\$curl, CURLOPT_NOPROXY, \"*\");";
                }
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);             
                $curl_commands[] = "curl_setopt(\$curl, CURLOPT_CUSTOMREQUEST, '$method');";       


                // echo "executing\n";
                $error_msg = "";
                $result = curl_exec($curl);
                $curl_commands[] = "\$result = curl_exec(\$curl);";       
                if($print_full_debugg) AfwBatch::print_debugg("API RESULT :\n [$result]\n");
                if(!trim($result))
                {
                    $error_msg .= "API Error result is empty ";
                }
                
                $error_msg .= curl_error($curl);
                $curl_commands[] = "\$error_msg = curl_error(\$curl);";       
                if($error_msg)
                {
                    $http_header_array_text = implode(" || ", $http_header_array);
                    $error_msg = "Error while doing curl_exec method=$method on url= $url / header = $http_header_array_text / proxy=$proxy_host port=".$proxy_port." => response=$result => error message : " . $error_msg;
                    if($print_error) AfwBatch::print_error("error_msg : $error_msg");
                }
                
                if(!$error_msg)  $success = true; else $success = false;
                // curl_close($curl);
                
                if($success) $decoded_result = json_decode($result); else $decoded_result = null;
                
                return array('url' => $p_url, 'success' => $success, 'message' => $error_msg, 'result' => $decoded_result, 'commands'=>$curl_commands);
        }
        else
        {
                $curl_commands[] = "\$curl = curl_init(); // failed";               
                $error_msg = "Error while doing curl_init";
                return array('url' => null, 'success' => false, 'message' => $error_msg, 'result' => null, 'commands'=>$curl_commands);
        }
        
    
    
    }

    public static function consume_bearer_api($url, $token, $proxy = null , $data = null, 
                    $verify_host=false, $verify_pear=false, $return_transfer=true, 
                    $encoding='', $method='GET', $maxredirs=10, $timeout=0, $followlocation=true, $http_version=null,
                    $http_header_array = ['accept: application/json', 'Content-Type: application/json'])
    {
        return self::consume_complex_api(true, $url, $token, $proxy, $data, $verify_host, $verify_pear, $return_transfer, $encoding, $method, $maxredirs, $timeout, $followlocation, $http_version, $http_header_array);
    }

    public static function consume_normal_api($url, $proxy = null, $data = null, 
                    $verify_host=false, $verify_pear=false, $return_transfer=true, 
                    $encoding='', $method='GET', $maxredirs=10, $timeout=0, $followlocation=true, $http_version=null,
                    $http_header_array = ['accept: application/json', 'Content-Type: application/json'])
    {
        return self::consume_complex_api(false, $url, "",    $proxy, $data, $verify_host, $verify_pear, $return_transfer, $encoding, $method, $maxredirs, $timeout, $followlocation, $http_version, $http_header_array);
    }
}

?>