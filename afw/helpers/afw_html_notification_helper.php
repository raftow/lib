<?php
class AfwHtmlNotificationHelper {
    public static function getWarningNotification(){
        self::getNotification("success", "");
    }

    public static function getInfoNotification(){
        self::getNotification("information", "");
    }
    
    public static function getErrorNotification(){
        self::getNotification("error", "يوجد أخطاء");
    }
    
    public static function getSuccessNotification(){
        self::getNotification("warning", "يوجد تنبيهات");
    }
    
    public static function getSLogNotification(){
        return "<!-- SLOG :". AfwSession::pullSessionVar("slog","header") . "-->";
    }


    private static function getNotification($type, $pre_message)
    {
        $notification_message = "";        
        if(AfwSession::getSessionVar($type))
        {
            if($pre_message)
            {
                $cnt = count(explode("<br>",AfwSession::getSessionVar($type)));
                if ($cnt>1)
                {
                    $notification_message .= "$pre_message : <br>";
                }
            }            
            $notification_message .= AfwSession::pullSessionVar($type,"header"); 
        }
        
        if($notification_message) return self::prepareNofication($notification_message, $type);
        else return "";
    }

    private static function prepareNofication($notification_message, $type)
    {
        $alter_type = $type;
        if($type = "information") $alter_type = "status";
        return "<div class=\"alert messages messages--$alter_type alert-dismissable\" role=\"alert\" >
                        <a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>
                        $notification_message                
                </div><br>";
    }
    
    

}