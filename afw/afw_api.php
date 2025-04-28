<?php
    class AfwApi
	{
        public static function getResponseFromApi($api_url, $request)
        {
            $data = [];
            foreach($request as $key => $val)
            {
                $data[] = "$key=".urlencode($val);
            }
                
            $url = $api_url . "?" . implode("&",$data);
            // die("url=$url");
            // Get cURL resource
            $curl = curl_init();
            // Set some options - we are passing in a useragent too here
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_URL => $url,
                CURLOPT_USERAGENT => substr(md5(date("His")),0,5).' '.rand(0,1000)  // cURL Request
            ));
            // Send the request & save response to $resp
            $resp = curl_exec($curl);
            // die("curl_exec $url => resp=$resp");
            $result_api = json_decode($resp, true);
            
            // Close request to clear up some resources
            curl_close($curl);

            // die("data_res of curl_exec($url) = ".var_export($result_api,true));
            
            
            if($result_api) return $result_api;                
            else return $resp." as response of $url";
             
        }
    }