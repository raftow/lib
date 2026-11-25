<?php

class AfwStepsController extends AfwRichController
{

        private $steps_arr = array(0 => "technical");
        private $steps_name_arr = array("technical" => 0);
        private $steps_title_arr = array("technical" => 'Technical step');
        private $steps_place_holder_arr = array("technical" => 'Technical step');
        private $next_btn_title_arr = array("technical" => 'Next');
        // The object shoud exists when entering step ?
        private $object_mandatory_arr = array("technical" => false);

        /**
         * @var AFWObject
         */
        private $myObject = null;

        public function defaultMethod($request)
        {
                if ($request["save_next"]) return "save_and_next";
                if ($request["save_previous"]) return "save_and_previous";
                return "index";
        }

        /**
         * @param AFWObject $object
         * @return void
         */
        protected function setObject($object)
        {
                $this->myObject = &$object;
        }

        /**
         * @param array $request
         * @return AFWObject
         */
        protected function getObject($request, $mandatory = true)
        {
                if (!$this->myObject) $request = $this->initObjectFromRequest($request, $mandatory);
                return $this->myObject;
        }

        public function getPermanentFields()
        {
                return [
                        'request_field' => array('source' => "object", 'object_field' => "object_field"),
                ];
        }

        /**
         * @return array
         */
        protected function getSpecialDataFromRequest(array $request, string $step_name)
        {
                return $request;
        }


        public final function getDataFromRequest(array $request, string $step_name)
        {
                $data = $this->getSpecialDataFromRequest($request, $step_name);
                $data["current_step"] = $this->getStepNumByName($step_name);
                $data["max_steps"] = $this->maxSteps();
                $data["step_name"] = $step_name;
                $data["save_next_title"] = $this->getStepNextBtnTitle($step_name);
                $data["step_placeholder"] = $this->getStepPlaceholder($step_name);
                $data["obj"] = $obj = $this->getObject($request, $this->stepEnteringRequireObject($step_name));


                if ($obj) {
                        $arrPermFields = $this->getPermanentFields();
                        foreach ($arrPermFields as $data_field => $rowPermField) {
                                $object_field = $rowPermField["object_field"];
                                if ((!$data[$data_field]) and ($obj->getVal($object_field))) {
                                        $data[$data_field] = $obj->getVal($object_field);
                                }
                        }
                }


                return $data;
        }

        /**
         * @param array $request
         * @param bool $mandatory
         * @return array
         */
        public function initObjectFromRequest($request, $mandatory)
        {
                // $this->setObject(null);
                if ($mandatory) $this->renderError("initObjectFromRequest not overridden in this controller class");
        }

        /**
         * @param array $request
         * @return void
         */
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

        protected function addStep(
                string $step_name,
                string $step_title,
                string $next_btn_title,
                string $place_holder,
                bool $object_mandatory,
        ) {
                $this->steps_arr[] = $step_name;
                $this->steps_name_arr[$step_name] = count($this->steps_arr) - 1;
                $this->steps_title_arr[$step_name] = $step_title;
                $this->next_btn_title_arr[$step_name] = $next_btn_title;
                $this->steps_place_holder_arr[$step_name] = $place_holder;
                $this->object_mandatory_arr[$step_name] = $object_mandatory;
        }


        public function getStepByNum(string $step_num)
        {
                $return = $this->steps_arr[$step_num];
                if ($return) return $return;
                return "step$step_num";
        }

        public function getStepNumByName(string $step_name)
        {
                $return = $this->steps_name_arr[$step_name];
                if (!$return) $this->renderError("step $step_name not found");
                return $return;
        }

        public function getStepTitle(string $step_name)
        {
                $return = $this->steps_name_arr[$step_name];
                if (!$return) $return = $step_name;
                return $return;
        }

        public function getStepPlaceholder(string $step_name)
        {
                $return = $this->steps_place_holder_arr[$step_name];
                return $return;
        }

        public function getStepNextBtnTitle(string $step_name)
        {
                $return = $this->next_btn_title_arr[$step_name];
                if (!$return) $return = "Next";
                return $return;
        }


        public function stepEnteringRequireObject(string $step_name)
        {
                $return = $this->object_mandatory_arr[$step_name];
                return $return;
        }

        public function index($request)
        {
                if ($request["current_step"]) $step_num = $request["current_step"];
                else {
                        $this->getObject($request, false);
                        if ($this->myObject) {
                                $step_num = $this->myObject->getCurrentFrontStep();
                                if (!is_numeric($step_num)) $step_num = $this->getStepNumByName($step_num);
                        } else $step_num = 1;
                }

                $methodName = $this->getStepByNum($step_num);

                if ($methodName) $this->$methodName($request);
                else $this->renderError("no step $step_num defined for this 'steps' controller");
        }

        public function showStep($request)
        {
                $step_num = $request["current_step"];
                if (!$step_num) $step_num = 1;
                $methodName = $this->getStepByNum($step_num);

                if ($methodName) $this->$methodName($request);
                else $this->renderError("no step $step_num defined for this 'steps' controller");
        }

        private function saveMyData($request, $current_step)
        {
                $methodSave = "save" . ucfirst($this->getStepByNum($current_step));
                return $this->$methodSave($request);
        }

        protected function validateData($request, $current_step)
        {
                $methodValidate = "validate" . ucfirst($this->getStepByNum($current_step));
                return $this->$methodValidate($request);
        }

        public function maxSteps()
        {
                return count($this->steps_arr) - 1;
        }

        private function saveData($request, $step_inc = 1)
        {
                //if ($this->checkLoggedIn()) {
                $log = "";
                $request = $this->saveMyData($request, $request["current_step"]);
                $log .= "current_step was " . $request["current_step"] . "\n";
                $request["current_step"] = intval($request["current_step"]) + $step_inc;
                $log .= "current_step become " . $request["current_step"] . "\n";
                //die("log rafik 261123 : $log");
                if ($request["current_step"] < 1) $request["current_step"] = 1;
                if ($request["current_step"] > $this->maxSteps()) $request["current_step"] = $this->maxSteps();
                return $request;
                //}
        }

        public function save_and_next($request)
        {
                list($request, $ok) = $this->validateData($request, $request["current_step"]);
                if ($ok) $request = $this->saveData($request, $step_inc = 1);
                $this->showStep($request);
        }

        public function save_only($request)
        {
                list($request, $ok) = $this->validateData($request, $request["current_step"]);
                if ($ok) $request = $this->saveData($request, $step_inc = 0);
                $this->showStep($request);
        }

        public function save_and_previous($request)
        {
                list($request, $ok) = $this->validateData($request, $request["current_step"]);
                if ($ok) $request = $this->saveData($request, $step_inc = -1);
                $this->showStep($request);
        }
}
