<?php
// obsolete
/*
//$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
        //die($actual_link);
        
        // get current module

        $module = AfwUrlManager::currentURIModule();

        // construct title for page
        if($file_dir_name) 
        {
                $ums_dir_name = "$file_dir_name/../ums/";
                $pag_dir_name = "$file_dir_name/../pag/";
                $r fw_dir_name = "$file_dir_name/../r fw/";
        }
        else
        {
                $pag_dir_name = "";
                $r fw_dir_name = "../r fw/";
        }
        
        
        require_once("${ums_dir_name}bfunction.php");
        
        $lv_bf = Bfunction::getServerStructureObject("GBF",$Main_Page);
        if(!$lv_bf)
        {
            $lv_bf = new Bfunction();
            $lv_bf->select("bfunction_type_id",12);
            $lv_bf->select("file_specification",$Main_Page);
            $lv_bf->load();
            Bfunction::setServerStructureObject("GBF",$Main_Page,$lv_bf);
        }
        
        if($lv_bf->getId()) 
        {
                $lv_page_title = $lv_bf->getDisplay();
        
                if($cl and (!$tblid)) 
                {
                        if((!$lv_obj) and ($id>0)) 
                        {
                                $lv_ob_file = AfwStringHelper::classToFile($cl);
                                require_once($lv_ob_file);
                                $lv_obj = new $cl();
                                
                                $lv_obj->load($id);                                 
                        }
                }
                else if($tblid) 
                {
                        if((!$lv_obj) and ($id>0))
                        {
                                if(!$r fwFactoryObj) 
                                {
                                        require_once("${r fw_dir_name}r fw_factory.php");
                                        $r fwFactoryObj = new RFWFactory();
                                }
                                $lv_obj =& $r fwFactoryObj->getObject($tblid);
                                $lv_obj->load($id);                                 
                        }
                }
                
                if($lv_obj and $lv_obj->getId()) 
                {
                    $lv_page_title .= " " . $lv_obj->transClassPlural();
                    //$lv_page_title .= " [" . $lv_obj->getDisplay()."]";
                }

                $Params = "tblid=$tblid&cl=$cl&id=$id";
                $Title = $lv_page_title;
                
                $found = false;
                
                for($ss=11;$ss>=0; $ss--) 
                {
                           if(($_SE SSION["LAST_VISITED"][$module][$ss]["Main_Page"] == $Main_Page) and
                              ($_SE SSION["LAST_VISITED"][$module][$ss]["Params"] == $Params) and
                              ($_SE SSION["LAST_VISITED"][$module][$ss]["Title"] == $Title)) 
                              {
                                  $found = true;
                                  break;
                              } 
                }
                
                if(!$found)                
                {
                        for($ss=11;$ss>0; $ss--) 
                        {
                                   $_SE SSION["LAST_VISITED"][$module][$ss] = $_SESS ION["LAST_VISITED"][$module][$ss-1]; 
                        }
                            
                        $_SE SSION["LAST_VISITED"][$module][0] = array("Main_Page" => $Main_Page,
                                                             "Params" => $Params,
                                                             "Title" => $Title);
                }
                
        
        }
        //else $lv_page_title = $Main_Page;
        
        
         
        
  */      
        
?>