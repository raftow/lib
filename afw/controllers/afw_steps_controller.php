<?php

class AfwStepsController extends AfwRichController {

        private $steps_arr = array(0=>"technical");
        private $steps_name_arr = array("technical"=>0);

        private $myObject = null;

        public function defaultMethod($request)
        {
                if($request["save_next"]) return "save_and_next";
                if($request["save_previous"]) return "save_and_previous";
                return "index";
        }

        protected function setObject($object)
        {
               $this->myObject = $object;
        }

        protected function getObject($request, $mandatory=true)
        {
                if(!$this->myObject) $request = $this->initObjectFromRequest($request, $mandatory);
                return $this->myObject;
        }

        public function getPermanentFields()
        {
                return [
                          'request_field' => array('source' => "object", 'object_field'=> "object_field" ),
                       ];
        }

        public function getDataFromRequest($request)
        {
                $data = $request;
                $obj = $this->getObject($request, $mandatory=false);

                
                if($obj)
                {
                        $arrPermFields = $this->getPermanentFields();
                        foreach($arrPermFields as $data_field => $rowPermField)
                        {
                                $object_field = $rowPermField["object_field"];
                                if((!$data[$data_field]) and ($obj->getVal($object_field))) $data[$data_field] = $obj->getVal($object_field);
                        }
                }


                return $data;
        }

        public function initObjectFromRequest($request, $mandatory)
        {
               // $this->setObject(null);
               if($mandatory) $this->renderError("initObjectFromRequest not overridden in this controller class");
        }

        public function initAllSteps($request)
        {
                // $this->addStep("step1");
                // $this->addStep("step2");
        }

        public function __construct($request)
        {
              //$this->initObjectFromRequest($request);  
              $this->initAllSteps($request);  
        }

        protected function addStep($step_method)
        {
               $this->steps_arr[] = $step_method;
               $this->steps_name_arr[$step_method] = count($this->steps_arr)-1;
        }

        public function getStepByNum($step_num)
        {
               $return = $this->steps_arr[$step_num];
               if($return) return $return;
               return "step$step_num";
        }

        public function getStepNumByName($step_name)
        {
               $return = $this->steps_name_arr[$step_name];
               if(!$return) $this->renderError("step $step_name not found");
               return $return;
        }

        public function index($request)
        {
                if($request["current_step"]) $step_num = $request["current_step"];
                else
                {
                        $this->getObject($request, false);
                        if($this->myObject) 
                        {
                                $step_num = $this->myObject->getCurrentFrontStep(); 
                                if(!is_numeric($step_num)) $step_num = $this->getStepNumByName($step_num);
                        }
                        else $step_num = 1;
                }

                $methodName = $this->getStepByNum($step_num);

                if($methodName) $this->$methodName($request);
                else $this->renderError("no step $step_num defined for this 'steps' controller");
        }

        public function showStep($request)
        {
                $step_num = $request["current_step"];
                $methodName = $this->getStepByNum($step_num);

                if($methodName) $this->$methodName($request);
                else $this->renderError("no step $step_num defined for this 'steps' controller");
        }

        private function saveMyData($request, $current_step)
        {
                $methodSave = "save".ucfirst($this->getStepByNum($current_step));
                return $this->$methodSave($request);
        }        

        protected function validateData($request, $current_step)
        {
                $methodValidate = "validate".ucfirst($this->getStepByNum($current_step));
                return $this->$methodValidate($request);
        }

        public function maxSteps()
        {
                return count($this->steps_arr)-1;
        }

        private function saveData($request, $step_inc=1)
        {
                $request = $this->saveMyData($request, $request["current_step"]);
                $request["current_step"] = intval($request["current_step"])+$step_inc;
                if($request["current_step"]<1) $request["current_step"] = 1;
                if($request["current_step"]>$this->maxSteps()) $request["current_step"] = $this->maxSteps();
                return $request;
        }

        public function save_and_next($request)
        {
                list($request, $ok) = $this->validateData($request, $request["current_step"]);
                if($ok) $request = $this->saveData($request, $step_inc=1); 
                $this->showStep($request);
        }

        public function save_only($request)
        {
                list($request, $ok) = $this->validateData($request, $request["current_step"]);
                if($ok) $request = $this->saveData($request, $step_inc=0); 
                $this->showStep($request);
        }

        public function save_and_previous($request)
        {
                list($request, $ok) = $this->validateData($request, $request["current_step"]);
                if($ok) $request = $this->saveData($request, $step_inc=-1); 
                $this->showStep($request);
        }

}        