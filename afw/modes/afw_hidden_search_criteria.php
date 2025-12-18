<?php

$hidden_inputs_arr = array();
$class_db_structure = $obj::getDbStructure($return_type="structure", $attribute = "all");

foreach($class_db_structure as $nom_col => $desc)
{
	if(AfwPrevilegeHelper::isQSearchCol($obj, $nom_col, $desc))
        {
		ob_start();
                AfwQsearchMotor::hidden_input($nom_col, $desc, $_POST[$nom_col], $obj);
		$hidden_inputs_arr[$nom_col] = ob_get_clean();
                if($desc["TYPE"]=="DATE")
                {
                     ob_start();
                     AfwQsearchMotor::hidden_input($nom_col."_2", $desc, $_POST[$nom_col."_2"], $obj);
		     $hidden_inputs_arr[$nom_col."_2"] = ob_get_clean();
                }
	}
}
ob_start();
AfwQsearchMotor::hidden_input("qsearch_by_text", $desc, $_POST["qsearch_by_text"], $obj);
$hidden_inputs_arr["qsearch_by_text"] = ob_get_clean();
// echo "\n<!-- hidden inputs for qsearch ".var_export($hidden_inputs_arr,true)." -->\n";
foreach($hidden_inputs_arr as $col => $input_h)
{
   echo trim(trim($input_h),"﻿")."\n\t\t\t\t\t";
}

