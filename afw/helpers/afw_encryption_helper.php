<?php

class AfwEncryptionHelper
{
    public static function password_encrypt($pwd)
        {
                return md5($pwd);
        }

        public static function password_generate($username, $len=7)
        {
                return substr(md5(rand(4,1000).$username. date("is")),0,$len);;
        }


}