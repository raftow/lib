<?php

// old require of afw_root 

class AFWCopyMotor extends AFWRoot {


    public static function recurseCopy($src,$dst) 
    { 

        $dir = opendir($src); 
        if (!mkdir($dst)) {
            die('Failed to create folder '.$dst);
        }
        
        while(false !== ( $file = readdir($dir)) ) 
        { 
            if (( $file != '.' ) && ( $file != '..' )) 
            { 
                if (is_dir($src . '/' . $file)) 
                { 
                    self::recurseCopy($src . '/' . $file, $dst . '/' . $file); 
                } 
                else 
                { 
                    copy($src . '/' . $file, $dst . '/' . $file); 
                }  
            } 
        } 
        
        
        closedir($dir); 
    }


    public static function copyFile($src_file, $dst_file)
    {
        copy($src_file, $dst_file);
    }

}

