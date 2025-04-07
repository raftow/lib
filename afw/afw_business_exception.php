<?php

class AfwBusinessException extends RuntimeException
{
    /**
     * @return AfwBusinessException
     */

    public $picture = "be-standard.gif";
    public $return_message = null;
    public $return_page = null;

    public function __construct(string $message, $lang="ar", string $picture="", 
                       string $return_message="", string $return_page="index.php", string $technical="", string $module="ums", mixed $param1=null, mixed $param2=null, mixed $param3=null)
    {
        $message = AfwLanguageHelper::tarjemMessage($message, $module, $lang);
        $message = sprintf($message, $param1, $param2, $param3);
        if($picture) $this->picture = $picture;
        if($return_message) $this->return_message = AfwLanguageHelper::tarjemMessage($return_message, $module, $lang);
        $this->return_message = sprintf($this->return_message, $param1, $param2, $param3);
        if($return_page) $this->return_page = $return_page;
        if(!$this->return_message)
        {
            $this->return_message = "Return to main page";
        }
        $mess_post = "";

        if($_POST and is_array($_POST) and (count($_POST)>0))
        {
            foreach($_POST as $psKey => $psVal) $mess_post .= "<p>$psKey => $psVal</p>";
        }

        $technical .= $mess_post;
        
        if($technical) $message .= "<div class='technical'>$technical</div>\n";


        parent::__construct($message);


    }
}