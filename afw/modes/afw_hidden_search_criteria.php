<?php

$hidden_inputs_arr = array();
$class_db_structure = $obj::getDbStructure($return_type="structure", $attribute = "all");

foreach($class_db_structure as $nom_col => $desc)
{
	if($obj->isQSearchCol($nom_col, $desc))
        {
		ob_start();
                hidden_input($nom_col, $desc, $_POST[$nom_col], $obj);
		$hidden_inputs_arr[$nom_col] = ob_get_clean();
                if($desc["TYPE"]=="DATE")
                {
                     ob_start();
                     hidden_input($nom_col."_2", $desc, $_POST[$nom_col."_2"], $obj);
		     $hidden_inputs_arr[$nom_col."_2"] = ob_get_clean();
                }
	}
}
ob_start();
hidden_input("qsearch_by_text", $desc, $_POST["qsearch_by_text"], $obj);
$hidden_inputs_arr["qsearch_by_text"] = ob_get_clean();

foreach($hidden_inputs_arr as $col => $input_h)
{
   echo $input_h;
}


?>