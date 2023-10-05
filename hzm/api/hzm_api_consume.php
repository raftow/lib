<?php
    function consume_bearer_api($url, $token, $proxy = null , $data = null)
    {
        global $print_debugg, $print_full_debugg, $print_error;
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
                
                $authorization = "Authorization: Bearer $token";
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
                // echo "option CURLOPT_HTTPHEADER setted\n";
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                // echo "option CURLOPT_RETURNTRANSFER setted\n";
                if($proxy)
                {
                        $proxy_arr = explode(':', $proxy);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl, CURLOPT_PROXY, $proxy_arr[0]);
                        curl_setopt($curl, CURLOPT_PROXYPORT, $proxy_arr[1]);
                        //curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        
                }
                else
                {
                    curl_setopt($curl, CURLOPT_NOPROXY, "*");
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    // die("rafik CURLOPT_SSL_VERIFYPEER = 0 ");
                    $proxy_arr = array();
                }
                

                // echo "executing\n";
                $result = curl_exec($curl);
                if($print_full_debugg) AfwBatch::print_debugg("API RESULT :\n [$result]\n");
                if(!trim($result))
                {
                    if($print_full_debugg) AfwBatch::print_debugg("result empty for call : $url / $authorization / proxy=".$proxy_arr[0]." port=".$proxy_arr[1]);
                }
                
                $error_msg = curl_error($curl);
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
               
                $error_msg = "Error while doing curl_init : " . curl_error($curl);
                return array('url' => null, 'success' => false, 'message' => $error_msg, 'result' => null);
        }
        
    
    
    }
    
?>