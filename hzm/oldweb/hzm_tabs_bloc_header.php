<?
$clObj = $obj->getMyClass();
$wizObj = new AfwWizardHelper($obj);
$clStep = $wizObj->getMyCLStep(); 

if(!$obj->editByStep)
{
?>
<div class="col-xs-12 hzm_vtab_body" style="height:<?=$hzm_vtab_body_height?>;margin-bottom: 15px;">
<?
}
else
{
        $step_name = array();
        $acceptedTypeArr=array("TEXT"=>true,"FK"=>true,"DATE"=>true,"MFK"=>true);
        for($kstep=1;$kstep<=$obj->editNbSteps;$kstep++)
        {
            if(AfwFrameworkHelper::stepIsApplicable($obj, $kstep))
            {
                $stepcode = "step".$kstep;
                $stepHelpcode = "step".$kstep."_help";
                $step_name[$kstep] = $obj->translate($stepcode,$lang);
                $step_help[$kstep] = $obj->translate($stepHelpcode,$lang);
                
                $implode_char=", ";
                if($lang=="ar") $implode_char="، ";
                
                
                if($step_help[$kstep] == $stepHelpcode)
                {
                    $step_help[$kstep] = AfwFrameworkHelper::getAllAttributesInMode($obj, $modeOfPage, $kstep,$acceptedTypeArr, $submode="",$for_this_instance=true, $translate=true, 
                                                                      $lang, $implode_char,$max_elekh_nb_cols=3,
                                                                      $alsoAdminFields=false, $alsoTechFields=false, $alsoNAFields=false, $max_elekh_nb_chars=32);
                }
                
                if(!$step_help[$kstep]) $step_help[$kstep] = "no desc for ".$step_name[$kstep];
            }
        }
        
        if(AfwSession::hasOption("FULL_SCREEN") or AfwSession::hasOption("HORIZONTAL_TABS"))
        {

?>

<div class="hzmSteps">
  <ul>
    <?
        $moduleObj = $obj->getMyModule();
        $idObj = $obj->getId();

        for($kstep=1;$kstep<=$obj->editNbSteps;$kstep++)
        {
             if(AfwFrameworkHelper::stepIsApplicable($obj, $kstep))
             {
                  if(($obj->general_check_errors or AfwSession::hasOption("GENERAL_CHECK_ERRORS"))  and ((!$obj->isDraft()) or ($kstep < $obj->currentStep) or $obj->show_draft_errors))
                  {
                          $stepErrorsList = AfwDataQualityHelper::getStepErrors($obj, $kstep);
                          $step_errors_list = implode("\n",$stepErrorsList);
                          $step_show_error = (count($stepErrorsList)>0);
                  
                  }
                  else 
                  {
                        $stepErrorsList = array();
                        $step_errors_list = "";
                        $step_show_error = false;
                  }      

                  if($kstep==$obj->currentStep) 
                  {
                        if($step_show_error)
                           $class_step = "CurrentStep ErronedStep XXC";
                        else 
                           $class_step = "CurrentStep YYC";
                        $link_step = "#";
                  }       
                  elseif(true)//($kstep<=$obj->getLastEditedStep())
                  {
                      if($step_show_error)
                           $class_step = "AlreadyStep ErronedStep XXD";
                      else  
                           $class_step = "AlreadyStep YYD";
                           
                      $link_step = "main.php?Main_Page=afw_mode_display.php&cl=$clObj&id=$idObj&currmod=$moduleObj&currstep=$kstep&popup=$popup";
                  }
                  else 
                  {
                        $class_step = "InactiveStep";
                        $link_step = "#";
                  }
          
           
    ?>      
    <li class="<?="tabsbloc wizstep".$kstep." ".$clStep." ".$class_step?>"><a href="<?=$link_step?>"><div class='step_num'><?=$kstep?>&nbsp;</div><div class='step_name'><?=$step_name[$kstep]?></div></a>
    <!-- <?php echo $step_errors_list?>-->
    </li>
    <?
            }
        }
    ?>
  </ul>
</div>
<div>
<?
    }
    else
    {
           if($obj->slim_tabs)
           {
                 $mode_slim = "slim";
           }
           else
           {
                 $mode_slim = "";
           }
           
           $hzm_vtab_body_height = $obj->hzm_vtab_body_height;
?>
<div class="col-xs-2 hzm_vtab<?=$mode_slim?>">
	<ul class="nav nav-pills nav-stacked" role="tablist">
        
<?
                $clObj = $obj->getMyClass();
                $moduleObj = $obj->getMyModule();
                $idObj = $obj->getId();
        
                for($kstep=1;$kstep<=$obj->editNbSteps;$kstep++)
                {
                      if(AfwFrameworkHelper::stepIsApplicable($obj, $kstep))
                      {
                          $arrStepErrors = AfwDataQualityHelper::getStepErrors($obj, $kstep);
                          /*
                          if($kstep==3)
                          {
                                throw new AfwRuntimeException("arrStepErrors = ".var_export($arrStepErrors,true));
                          }*/
                          if(($obj->general_check_errors or AfwSession::hasOption("GENERAL_CHECK_ERRORS")) and (!$obj->isDraft()) and (count($arrStepErrors)>0))
                          {
                                   $class_error = " ErronedStep YYA";
                                   $prefix_error = "erroned_";
                          }
                          else
                          {
                                   $class_error = "";
                                   $prefix_error = "";
                          }
                          
                          if($kstep==$obj->currentStep) 
                          {
                                $class_step = "activeTab".$class_error;
                                $classLabel = $prefix_error."activeTabLabel";
                                $classInfo = "activeTabInfo";
                                $link_step = "#";
                                $arraow_active_tab = "<span class='fa fa-arrow-left arraow-active-tab'></span>";
                          }       
                          elseif(true)//($kstep<=$obj->getLastEditedStep())
                          {
                              $class_step = "".$class_error;
                              $classLabel = $prefix_error."tabLabel";
                              $classInfo = "tabInfo";
                              $link_step = "main.php?Main_Page=afw_mode_display.php&cl=$clObj&id=$idObj&currmod=$moduleObj&currstep=$kstep&popup=$popup";
                              $arraow_active_tab = "";
                          }
                          else 
                          {
                                $classLabel = $prefix_error."tabLabel";
                                $classInfo = "tabInfo";
                                $class_step = "InactiveStep".$class_error;;
                                $link_step = "#";
                                $arraow_active_tab = "";
                          }
                  
                   
            ?>
            <li role="tab">
              <a href="<?=$link_step?>" class="defaultTab<?=$mode_slim?> dropdown-item <?=$class_step?>">
                <?=$arraow_active_tab?>
                <span class="<?=$classLabel?>"><?=$step_name[$kstep]?></span>
<?
                if(!$obj->no_step_help)
                {
?>        
        	<div class="<?=$classInfo?>"><span><?=$step_help[$kstep]?></span></div>
<?
                }
?>        
              </a>
            </li>
<?
                    }
              }
?>        
        
		
	</ul>
</div>
<div class="col-xs-10 hzm_vtab_body" style="height:<?=$hzm_vtab_body_height?>;margin-bottom: 15px;">
<?
    }
}

?>