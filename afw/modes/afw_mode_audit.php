<?php
require_once(dirname(__FILE__)."/../../../external/db.php");
// here was old const php

require_once("afw_config.php");

if(!$currmod)
{
    $currmod = $uri_module;
}
if(!$currmod) $currmod = "pag";


$objme = AfwSession::getUserConnected();

if(!$objme) 
{
    AfwSession::pushError("الرجاء تسجيل الدخول أولا");
    header("Location: login.php");
    exit();
}


//echo "factory_result";
//print_r($myObj);
//die($id);
if($cl and $id and $k)
{
        $myObj = new $cl();
        
        if($myObj->datatable_on_for_mode["audit"])
        {
           $datatable_on = 1;
        }
        
        if($k=="all")
             $attribute_arr = $myObj->getAuditableCols(); 
        else
             $attribute_arr = explode(",",$k);
        
        // die(var_export($attribute_arr,true));
        
        if($tech_notes) $myObj->tech_notes = $tech_notes;  
        
        // @todo
        $can = true;
        //$can = $objme->iCanDoOperationOnObjClass($myObj,"audit");
        if(!$can)
        {
              $log_ums_work = ($objme->isAdmin() or (AfwSession::hasOption("UMS_LOG"))) ? 1 : 0;
              header("Location: lib/afw/modes/afw_denied_access_page.php?CL=($cl=$cl0)&MODE=edit&bf=$bf_id&rsn=$reason&LOG=$log_ums_work");      
              exit();
        }
        
        if($myObj->load($id))
        {
                if(!AfwUmsPagHelper::userCanDoOperationOnObject($myObj,$objme,'audit'))
                {
			die("You are not authorized to audit this object");
		}
	}
        else
        {
                die("objet can not be loaded");
        }
}
else
{
    die("objet class, id and column to audit are mandatory params");
    
    
    //die("filled object :".var_export($myObj,true));
}

$table_name_with_prefix = $myObj->getTableName($with_prefix=true);

$audit_data = array();
$id10000 = $id*10000;
$version_obj = $myObj->getVersion();
$pk_audit_obj = $id10000+$version_obj;
$update_date_obj = $myObj->getUpdateDate();
$update_user_id_obj = $myObj->getUpdateUserId();
$myObj_display = $myObj->getDisplay($lang);
$out_scr = $header_bloc_audit;
$out_scr .= "<br><h1>تقصي الأثر على التعديلات المجراة على السجل : </h2><br>";
$out_scr .= "<span class='object_view'>$myObj_display</span><br>";

$audit_header["all"] = array('version' => "النسخة", 'update_date' => "تاريخ التعديل", 'update_auser_id' => "صاحب التعديل", 'update_context' => "سياق التعديل");
$audit_data["all"] = array();
foreach($attribute_arr as $attribute)
{
        $audit_data1[$attribute][$pk_audit_obj] = array('pk' => $pk_audit_obj, 'version' => $version_obj, 'update_date' => $update_date_obj, 'update_auser_id' => $update_user_id_obj, 'update_context' => "");
        
        $audit_data1[$attribute][$pk_audit_obj][$attribute] = $myObj->getVal($attribute);
        
        $requete = "select $id10000+version as pk, version, val as $attribute, update_date, update_auser_id, update_context from ${table_name_with_prefix}_${attribute}_haudit where id = '$id' order by version desc, update_date desc";
        
        $audit_data2[$attribute] = get_tableau_byid(recup_data($requete),"pk");
        
        // echo "before merge : audit_data=".var_export($audit_data,true)." audit_data2=".var_export($audit_data2,true);
        
        $audit_data[$attribute] = $audit_data1[$attribute];
        foreach($audit_data2[$attribute] as $audit2_key => $audit2_row)
        {
            $audit_data[$attribute][$audit2_key] = $audit2_row;
        } 
        // die("after merge : audit_data=".var_export($audit_data,true));
        foreach($audit_data[$attribute] as $audit_key => $audit_row)
        {
            if(!$audit_data["all"][$audit_key]) $audit_data["all"][$audit_key] = array('vide' =>  true);
            // move up update_context
            if($audit_data[$attribute][$audit_key+1])
            {
                $audit_data[$attribute][$audit_key+1]["update_context"] = $audit_data[$attribute][$audit_key]["update_context"];
                $audit_data[$attribute][$audit_key]["update_context"] = "";
            } 
            if(!$nodecode)
            { 
                    $myObj->simulSet($attribute, $audit_data[$attribute][$audit_key][$attribute]);
                    $audit_data[$attribute][$audit_key][$attribute] = $myObj->decode($attribute);
                    if(!$audit_data[$attribute][$audit_key]["update_auser_id"])
                    {
                       $audit_data[$attribute][$audit_key]["update_auser_id"] = "غير معروف";
                    }
                    else
                    {
                       $audit_data[$attribute][$audit_key]["update_auser_id"] = $myObj->decodeSimulatedFieldValue($myObj::fld_UPDATE_USER_ID(), $audit_data[$attribute][$audit_key]["update_auser_id"]);
                    }
            } 
        }
        $attribute_label = $myObj->translate($attribute, $lang);
        $audit_header[$attribute] = array('version' => "النسخة", $attribute => $attribute_label, 'update_date' => "تاريخ التعديل", 'update_auser_id' => "صاحب التعديل", 'update_context' => "سياق التعديل");
        $audit_header["all"][$attribute] = $attribute_label;
        if($show!="merged")
        {
                $out_scr .= "<br><h2>تقصي الأثر على الحقل :$attribute_label </h2>";
                $out_scr .= "<div id='audit-$cl-$id-$attribute'>";
                
                list($html,$ids) = AfwShowHelper::tableToHtml($audit_data[$attribute], $audit_header[$attribute]);
                $out_scr .= $html;
                $out_scr .= "</div>";
                $out_scr .= "<br><hr>";
        }
}

$step = 0;

$decoded_attr_arr = array();

foreach($attribute_arr as $attribute)
{
    //die("audit_data[$attribute] = ".var_export($audit_data[$attribute],true));
    foreach($audit_data[$attribute] as $audit_key => $audit_row)
    {
            
            if($audit_data["all"][$audit_key]["vide"]) $audit_data["all"][$audit_key] = $audit_row;
            else
            {
                $audit_data["all"][$audit_key][$attribute] = $audit_row[$attribute];
            } 
            $step++;
            /*
            $out_scr .= "<br>step $step : (attribute=$attribute, audit_key=$audit_key) audit_data_all = ".var_export($audit_data["all"],true); 
            if((!$nodecode) and (!$decoded_attr_arr["all"][$audit_key][$attribute]))
            {
                    $myObj->simulSet($attribute, $audit_data["all"][$audit_key][$attribute]);
                    $audit_data["all"][$audit_key][$attribute] = $myObj->decode($attribute);
                    $decoded_attr_arr["all"][$audit_key][$attribute] = true;
            }*/
    }
    /*
    if(!$nodecode)
    {
            if(!$audit_data["all"][$audit_key]["update_auser_id"])
            {
               $audit_data["all"][$audit_key]["update_auser_id"] = "غير معروف";
            }
            else
            {
               $audit_data["all"][$audit_key]["update_auser_id"] = $myObj->decodeSimulatedFieldValue($myObj::fld_UPDATE_USER_ID(), $audit_data["all"][$audit_key]["update_auser_id"]);
            }
    }
    */   
}

if(($show=="merged") or ($show=="all"))
{
        $out_scr .= "<div id='audit-$cl-$id-all'>";
        
        list($html,$ids) = AfwShowHelper::tableToHtml($audit_data["all"], $audit_header["all"]);
        $out_scr .= $html;
        $out_scr .= "</div>";
        $out_scr .= "<br><hr>";
}

