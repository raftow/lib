<?php
class AfwSettingsHelper
{
    /**************************       Parameters         ****************/
    public static function readParamsArray($object, $params_attribute)
    {
        $params = $object->getVal($params_attribute);
        $return = [];
        $params_arr = explode("\n", $params);
        foreach($params_arr as $params_row)
        {
            list($param, $value) = explode(":", $params_row);
            $value = trim($value);
            $param = trim($param);
            if($param) $return[$param] = $value;
        }

        return $return;
    }

    public static function paramsArrayToString($params_arr)
    {
        $return = [];
        foreach($params_arr as $param => $value)
        {            
            $value = trim($value);
            $param = trim($param);
            $return[] = $param.":".$value;
        }

        return implode("\n", $return);
    }

    
    public static function readParamValue($object, $param_attribute, $param_name, $default_value = null)
    {
        $params = $object->getVal($param_attribute);

        $params_rows = explode("\n", $params);
        foreach($params_rows as $params_row)
        {
            list($param, $value) = explode(":", $params_row);
            $value = trim($value);
            $param = trim($param);
            if($param == $param_name)
            {
                return $value;
            }
        }

        return $default_value;
    }

    public static function proposeTypeValue($type)
    {
        if($type=="integer") return 1;
        if($type=="date") return date("Y-m-d");
        if($type=="datetime") return date("Y-m-d H:i:s");

        return "????";
    }

    public static function repareParamsArray($input_arr, $input_param, $input_param_props_arr)
    {
        
        $mandatory = $input_param_props_arr["mandatory"];
        $type = $input_param_props_arr["type"];
        $repare = false;
        
        if($input_arr[$input_param] and !AfwFormatHelper::isGoodFormat($input_arr[$input_param], $type)) $repare = true;
        elseif($mandatory and (!$input_arr[$input_param])) $repare = true;
        elseif(!$input_arr[$input_param]) $input_arr[$input_param] = null;


        if($repare)
        {
            $input_arr[$input_param] = self::proposeTypeValue($type);
        }

        return $input_arr;
    }

    /**************************       Settings         ****************/
    public static function readSettingValue($object, $setting_name, $default_value = null, $settings_attribute_name="settings", $throwError=false)
    {
        $settings = $object->getVal($settings_attribute_name);
        // format JSON
        if(is_array($settings))
        {
            $settings_array = $settings;   
        }
        else
        {
            $settings_array = json_decode($settings, true);        
        }
        if(!is_array($settings_array) and $throwError) throw new AfwBusinessException("$settings_attribute_name can't be decoded as json, please check syntax");
        $settings_array = is_array($settings_array) ? $settings_array : [];
        if (array_key_exists($setting_name, $settings_array)) {
            return $settings_array[$setting_name];
        }

        return $default_value;
    }


    public static function calcErrorInSettings($settings)
    {
        $settings_array = json_decode($settings, true);  
        $json_last_error_msg = json_last_error_msg();      
        $css = is_array($settings_array) ? "ok" : "error";
        return "<span class='json $css'>$json_last_error_msg</span>";
    }
        

}