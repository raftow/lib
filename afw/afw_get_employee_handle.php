<?php

require_once(dirname(__FILE__)."/../../external/db.php");
require_once('afw_rights.php');

require_once('afw_config.php');

if(!$objme) $objme = AfwSession::getUserConnected();

if(!$lang) $lang = 'ar';


$obj  = new Employee();
$class_db_structure = Employee::getDbStructure($return_type="structure", $attribute = "all");


$employee_info = $_POST["employee_info"];
$context_action = $_POST["context_action"];







$my_oper = "like X'%.%'";
if($employee_info)
{
    $where_arr = array(); 
    if(is_numeric($employee_info)) $where_arr[] = "me.id='$employee_info'"; 

    $employee_info_cols = ["mobile","idn","username","email","emp_num"];
    foreach($employee_info_cols as $nom_col)
    {
            if($obj->isInternalSearchableCol($nom_col))
            {
                    $internal_where_arr = array();
                    $objTempForInternalSearch = AfwStructureHelper::getEmptyObject($obj, $nom_col);
                    $internal_employee_info_cols = $objTempForInternalSearch->getAllTextSearchableCols();
                    foreach($internal_employee_info_cols as $nom_col_internal)
                    {
                            list($internal_where_col,$internal_fixm_col,$internal_cond_phrase) = AfwSqlHelper::getClauseWhere($objTempForInternalSearch,$nom_col_internal, $my_oper,  $employee_info, "", $lang);
                            $internal_where_arr[] = $internal_where_col;        
                    }

                    $internal_where = "((".implode(") or (",$internal_where_arr)."))";
                    $objTempForInternalSearch->where($internal_where);
                    $objTempForInternalSearch->select_visibilite_horizontale();
                    $objTempForInternal_ids_arr = AfwLoadHelper::loadManyIds($objTempForInternalSearch);
                    $objTempForInternal_ids_txt = implode(",", $objTempForInternal_ids_arr);
                    if(!$objTempForInternal_ids_txt) $objTempForInternal_ids_txt = "0";
                    $where_col = "$nom_col in (".$objTempForInternal_ids_txt.")";
            }
            else
            {
                    list($where_col,$fixm_col,$cond_phrase) = AfwSqlHelper::getClauseWhere($obj, $nom_col, $my_oper,  $employee_info, "", $lang);
            }
            $where_arr[] = $where_col;
    }
                
    // $obj->_error("employee_info_cols = ".var_export($employee_info_cols,true)." where_arr = ".var_export($where_arr,true));
    if(count($where_arr)>0)
    {
            $where = "((".implode(") or (",$where_arr)."))";                                                
            $temp[] = $where;
    }        
}

if(!empty($temp)) 
{
    $temp_where=implode(" and ",$temp);
    $temp_where=trim($temp_where);
    if(preg_match('and$', $temp_where))
            $temp_where=substr( $temp_where,0,-2);
    $obj->where($temp_where);
    $obj->select_visibilite_horizontale();
    $count_liste_obj = $obj->func("count(*)");
    $obj->where($temp_where);
    $obj->select_visibilite_horizontale();
}
else 
{
        $obj->select_visibilite_horizontale();
        $count_liste_obj = $obj->func("count(*)");
        $obj->select_visibilite_horizontale();                
}

$search_result_html = "";

if($count_liste_obj==0)
{
    AfwSession::pushWarning($obj->tm("No employee found with this criteria"));
}
elseif($count_liste_obj>1)
{
    AfwSession::pushWarning($obj->tm("More than one employee found with this criteria").". ".$obj->tm("Please choose more refined criteria"));
    
}
else
{
    if(!isset($limite))
    {
        $limite=0;
    }
    if(!$result_page_title) $result_page_title = $obj->translate('SEARCH_RESULT',$lang,true)." ".$obj->translate($obj->getTableName(),$lang);
    $obj->load();
    
    if($obj->id > 0)
    {
        $pbMethodCode = "";
        foreach($_POST as $name => $value)
        {
                    if(AfwStringHelper::stringStartsWith($name,"submit-"))
                    {
                            $pbMethodCode = substr($name,7);
                    }
                    
                    if(AfwStringHelper::stringStartsWith($name,"pbmconfirm-"))
                    {
                            $pbMethodCode = substr($name,11);
                    }
                    
                    if(AfwStringHelper::stringStartsWith($name,"pbmcancel-"))
                    {
                            $pbMethodCode = substr($name,10);
                    }   
        }
        
        if($pbMethodCode)
        {
            $pMethodItem = UmsManager::getAllowedEmployeeMethod($context_action, $pbMethodCode, $objme, $obj);        
            if(!$pMethodItem)
            {
                AfwSession::pushError($obj->tm("No method allowed width context and code")." = ($context_action,$pbMethodCode)");
            }
            else
            {
                if($pMethodItem["CONFIRMATION_NEEDED"])
                {
                        if((!$_POST["pbmconfirm-$pbMethodCode"]) and (!$_POST["pbmcancel-$pbMethodCode"]))
                        {
                                $pbm_confirmed = false;
                                $pbm_cancelled = false;
                                $confirmation_warning = $pMethodItem["CONFIRMATION_WARNING"][$lang];
                                $confirmation_question = $pMethodItem["CONFIRMATION_QUESTION"][$lang];
                                // die("confirmation_warning=$confirmation_warning , confirmation_question=$confirmation_question, pMethodItem=".var_export($pMethodItem,true));
                                $main_page = "afw_get_employee.php";
                                include("afw_mode_confirm.php");
                                $header_bloc_edit .= $confirm_html;
                        }     
                        elseif($_POST["pbmconfirm-$pbMethodCode"])
                        {
                                $pbm_confirmed = true;
                                $pbm_cancelled = false;
                        }
                        else
                        {
                                $pbm_confirmed = false;
                                $pbm_cancelled = true;
                        }
                }     
                else
                {
                        //die("pMethodItem found for code : [$pbMethodCode] for user $objme is pMethodItem = ".var_export($pMethodItem,true));
                        $pbm_confirmed = true;
                }
                
                // die("pbm_confirmed=$pbm_confirmed");
                if($pbm_confirmed)
                {     
                        $old_update_context = $update_context;
                        $update_context = "من خلال زر ". $pMethodItem["LABEL_AR"]."-".$pMethodItem["METHOD"];
                        list($error, $info, $warn) = UmsManager::executeExternalMethodOnEmployee($obj, $pMethodItem, $context_action, $lang);
                        //die("list($error, $info, $warn) = UmsManager::executeExternalMethodOnEmployee($obj, $pMethodItem, $lang) update_context=$update_context;");
                        $update_context = $old_update_context;
                        
                        if(!$info and !$error and !$warn)
                        {
                            if($objme and $objme->isAdmin())
                                    $info = "execute of $pbMethodCode has been successfully terminated";
                            else
                                    $info = "action successfully terminated";
                        }  
                        
                        AfwSession::pushInformation($info, "method-$pbMethodCode"); 
                        AfwSession::pushError($error); 
                        AfwSession::pushWarning($warn); 
                        
                                
                        // reload object if needed (default yes) 
                        /*
                        if(!UmsManager::noRelaodAfterRunOfMethod($pbMethodCode)) 
                        {
                            unset($obj);
                            $obj = new $class();
                            $obj->load($id);
                            $_SESS ION["analysis_log"] .= "xxxxxxxxxxxxxxxxxxxxxxxxx<br>";
                        }
                        */
                }
                else
                {
                        if($pbm_cancelled) AfwSession::pushInformation("بحمد الله تم إلغاء الإجراء بكل أمان");
                }  
            }   
        }


        //$token_arr = array();
        if($context_action and ($context_action != "view_only"))
        {        
            $methods_arr = UmsManager::getAllowedBFMethods($context_action::getEmployeeMethods($obj), $objme, $mode="mode_minibox_".strtolower($context_action));        
            $isAdmin = ($objme and $objme->isSuperAdmin());
            $methods_html = AfwHtmlHelper::getHtmlMethodsButtons($obj, $methods_arr, $lang, $action_lourde=true, $isAdmin);
                                                  
        }
        else
        {
            $methods_html = "";
        }


        $search_result_html = $obj->showMinibox($structure="",$lang);

        $search_result_html .= "<div dir=\"rtl\" class=\"table_rtv mb_employee mb_methods\">$methods_html</div>";
    }

    

    
}
