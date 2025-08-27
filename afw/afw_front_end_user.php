<?php
interface AfwFrontEndUser {
    public function getShortDisplay($lang);
    public function getMyDepartmentName($lang);
    public function getMyJob($lang);
    public function translate($attribute, $lang);
    public function getUserPicture();
    public function generateCacheFile($lang="ar", $onlyIfNotDone=false, $throwError=false);
    public function isAdmin();
    public function isSuperAdmin();
    // a supervisor is a the highest business role and in technical
    // he has some roles lightly smaller than admin (can see some needed logs for example)        
    public function isSupervisor();
    
}

