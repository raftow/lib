<?php

class AfwRichController extends AfwController 
{
        public function alwaysNeedPrepare($request)
        {
            return true;
        }

        public function prepareStandard($method)
        {
                $custom_scripts = array();
                /*
                $file_dir_name = dirname(__FILE__); 
                $con = strtolower(__CLASS__);
                $custom_scripts[] = array('type'=>'css' , 'path'=>"./css/$con/method_$method.css");
                $custom_scripts[] = array(type=>js , path=>"./js/$method.js");
                */

                return $custom_scripts;
        }

        public function __call($name, $arguments) {
		
                if(substr($name, 0, 7)=="prepare") 
                {
                    return $this->prepareStandard($method=strtolower(substr($name, 7)));
                }

                $this->renderError(__CLASS__." method not found in this rich controller : $name ");
                                
	}

}