<?php
interface AfwStorable {
    
    /** 
     * @return int
    */
    public function getId();


    /** 
     * @return string
    */
    public function getStoredFileName();
}