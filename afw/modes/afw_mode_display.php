<?php
$file_dir_name = dirname(__FILE__);
if(AfwFrameworkHelper::displayInEditMode($cl))
{
   include("afw_mode_edit.php");
}
else
{
        // die("display_in_edit_mode=".var_export($display_in_edit_mode,true)." display_in_display_mode=".var_export($display_in_display_mode,true));

        require_once("afw_rights.php");
        require_once("afw_config.php");

        if(!$objme) $objme = AfwSession::getUserConnected();
        if(!$objme) 
        {
        AfwSession::pushError("الرجاء تسجيل الدخول أولا");
        header("Location: login.php");
        exit();
        }


        if(!$currmod)
        {
                $currmod = $uri_module;
        }

        if(!$lang) $lang = 'ar';


        $myObj = new $cl();
        $myObj->popup = $popup;
        $myObj->test_rafik = $test_rafik;

        if($myObj->datatable_on_for_mode["display"])
        {
                $datatable_on = 1;
        }



        // if($tech_notes) $myObj->tech_notes = $tech_notes;  
        // die(var_export($objme,true));
        // list($can,$bf_id, $reason) = $myObj->userCan($objme, $uri_module, "display");
        $can = $objme->iCanDoOperationOnObjClass($myObj,"display");
        $iCanDoOperationLog = var_export($objme->iCanDoOperationLog,true);
        $iCanDoBFLog = var_export($objme->iCanDoBFLog,true);
        if(!$can)
        {
                //die("rafik denied_access case to check");
                header("Location: lib/afw/modes/afw_denied_access_page.php?CL=$cl&MODE=display");      
                exit();
        }


        // die("rafik pbmon=$pbmon, _POST = ".var_export($_POST,true));

        $out_scr = "<!--iCanDo : $iCanDoOperationLog  ,  $iCanDoBFLog -->";
        if($myObj->load($id))
        {
                die("rafik myObj = ".var_export($myObj,true));
                $lv_obj =& $myObj;
                include_once("afw_save_last_visit.php");
                
                //$out_scr .= "<table class='$class_table' cellpadding='4' cellspacing='3'><tr><td colspan='2' align='center' class='$class_bloc'>";

                if($pbmon)
                {
                        foreach($_POST as $name => $value)
                        {
                                if(AfwStringHelper::stringStartsWith($name,"submit-"))
                                {
                                        $pbMethodCode = substr($name,7);
                                        list($error,$info, $warn, $technical) = $myObj->executePublicMethodForUser($objme, $pbMethodCode, $lang);

                                        if($technical)
                                        {
                                                if($warn) $warn .= $sep;
                                                $warn .= $myObj->tm($lang,"There are more technical details with administrator");
                                                $warn .= $sep."<div class='technical'>$technical</div>";
                                        }
                                        
                                        if(!$info and !$error and !$warn and $objme and $objme->isAdmin())  $info = "execute of $pbMethodCode has been successfully terminatd";
                                        
                                        if($info) AfwSession::pushInformation($info,"method-$pbMethodCode");
                                        if($error) AfwSession::pushError($error);
                                        if($warn) AfwSession::pushWarning($warn);
                                }  
                        }
                        // reload object if needed (default yes) 
                        if(!$myObj->noRelaodAfterRunOfMethod($pbMethodCode)) 
                        {
                                        unset($myObj);
                                        $myObj = new $cl();
                                        $myObj->load($id);
                        }
                
                }
                //if($myObj->test_rafik) die("rafik 1 ".var_export($myObj,true));
                if(AfwUmsPagHelper::userCanDoOperationOnObject($myObj,$objme,'display'))
                {
                    if($myObj->editByStep) 
                    {
                        if(!$currstep)
                        { 
                                if($myObj->getId()>0) 
                                {
                                        // @todo-currstep = $objme->curStepFor[$myObj->getTableName()][$myObj->getId()];
                                        // @todo-$currstep_orig = "curSF";
                                        if(!$currstep)
                                        {
                                                $currstep = $myObj->getLastEditedStep(); 
                                                $currstep_orig = "gLEStep";
                                        } 
                                }
                                
                                
                                if(!$currstep) 
                                {
                                        $currstep_orig = "default";
                                        $currstep = 1;
                                        //$out_scr .= $objme->showObjTech();
                                }    
                                // $out_scr .= '<input type="hidden" name="oldcurrstep"   value="'.$currstep.'"/>';
                        }
                        else $currstep_orig = "defined";
                        
                        // $out_scr .= '<input type="hidden" name="currstep"   value="'.$currstep.'"/>';
                        // $out_scr .= '<input type="hidden" name="currstep_orig"   value="'.$currstep_orig.'"/>';
                        
                        $myObj->currentStep = $currstep;
                        // @todo-$objme->curStepFor[$myObj->getTableName()][$myObj->getId()] = $currstep;
                        // @todo-$objme->curStepFor[$myObj->getTableName()][-1] = $currstep;
                    }
                        
                    //if($myObj->test_rafik) die("rafik 3 ".var_export($myObj,true));
                    $out_scr .= $myObj->showHTML();
                }
                else
                        $out_scr .= "لا يوجد عندك صلاحية لعرض هذا السجل";
                //$out_scr .= "</td></tr></table>";
                //$out_scr .= "</div></div></div>";
                //$out_scr .= "</td></tr></table>";
        }
        else 
        {
                $out_scr .="<center><table><tr><td><img src='image/warning.png' alt=''></td><td class='error'>لا يمكن تحميل هذا السجل، يبدوا أنه غير موجود أو حصل خطأ أثناء التحميل</td></tr></table></center>";
        }

//if($myObj->test_rafik) die("rafik 2");

//AFWDebugg::print_str('fin afw-mode-dipslay');
}
?>