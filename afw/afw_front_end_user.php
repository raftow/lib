<?php
interface AfwFrontEndUser {
    public function getShortDisplay($lang);
    public function getMyDepartmentName($lang);
    public function getMyJob($lang);
    public function translate($attribute, $lang);
    public function getUserPicture();
    public function generateCacheFile($lang="ar", $onlyIfNotDone=false, $throwError=false);
    
}

