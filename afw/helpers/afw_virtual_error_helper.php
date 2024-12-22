<?php
class AfwVirtualErrorHelper extends AFWRoot
{
    private static function prepareComponentNameAndVersion($file, $line)
    {
        $file = trim($file);
        $file = str_replace("\\", "/", $file);
        $file_arr = explode("/",$file);
        $file = "";
        if(count($file_arr)>3) $file .= $file_arr[count($file_arr)-3]."-";
        if(count($file_arr)>2) $file .= $file_arr[count($file_arr)-2]."-";
        if(count($file_arr)>1) $file .= $file_arr[count($file_arr)-1]."-";
        $file = trim($file, "-");
        $component = strtoupper($file);
        $component = str_replace("AFW_", "", $component);
        $component = str_replace("HZM_", "", $component);
        $component = str_replace(".PHP", "", $component);
        $component = str_replace(".", "-", $component);
        $component = str_replace("_", "-", $component);

        $line = 1000+$line;
        $v = floor($line/1000);
        $line = $line - 1000*$v;
        $vv = floor($line/100);
        $line = $line - 100*$vv;
        $vvv = floor($line/10);
        $vvvv = $line - 10*$vvv;

        $version = "$v.$vv.$vvv.$vvvv";

        return [$component, $version];
    }
    /**
     * @param Exception $ex
     */
    public static function getDetails($ex)
    {
        $file = $ex->getFile();
        $line = $ex->getLine();
        $traces = $ex->getTrace();
        //die("traces=".var_export($traces,true));
        list($component, $version) = self::prepareComponentNameAndVersion($file, $line);
        $relc = "";
        foreach ($traces as $i => $trace)
        {
            $file_c = isset($trace[ 'file' ]) ? basename($trace[ 'file' ]) : '';
            $line_c = isset($trace[ 'line' ]) ? $trace[ 'line' ] : '';
            if($file_c) 
            {
                list($c_component, $c_version) = self::prepareComponentNameAndVersion($file_c, $line_c);
                $relc .= "RC $c_component V$c_version\n<br>";
            }
        }
            
        return [$component, $version, $relc];       
    }
}