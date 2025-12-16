<?php
    $nom_table_fk   = $desc["ANSWER"];
    $nom_module_fk  = $desc["ANSMODULE"];
    if (!$nom_module_fk) {
        $nom_module_fk = AfwUrlManager::currentWebModule();
    }
    $nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);

    $objRep  = new $nom_class_fk;

    // list($sql, $liste_rep) = AfwLoadHelper::loadManyFollowing StructureAndValue($objRep, $desc, $val, $obj);
    // list($mdl, $myTbl) = $obj->getThisModuleAndAtable();
    // $l_rep = AfwHtmlHelper::constructDropDownItems($liste_rep, $lang, $col_name, "$mdl.$myTbl");            
    $val_to_keep = $desc["NO_KEEP_VAL"] ? null : $val;    
    // if(get_class($objRep)=="Module")    die("AfwLoadHelper::vhGetListe=>".var_export($l_rep,true));

    $type_input_ret = "select";

    $infos_arr = array(
        "class" => "form-control $lang form-mfk",
        "name"  => $col_name . "[]",
        "id"  => $col_name,
        "size"  => 5,
        "multi" => true,
        "tabindex" => $qedit_orderindex,
        "reloadfn" => AfwJsEditHelper::getJsOfReloadOf($obj, $col_name),
        "onchange" => $onchange,
        "style" => $input_style,
        "required" => $is_required,
        "disabled" => $disabled,

    );
    if ($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr, $desc["SEL_OPTIONS"]);

    if ($desc["SEL_CSS_CLASS"]) $infos_arr["class"] = $desc["SEL_CSS_CLASS"];

    if ($desc["FORMAT"]=="dropdown")
    {
        $l_rep = AfwLoadHelper::vhGetListe($objRep, $col_name, $obj->getTableName(), $desc["WHERE"], $action = "loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true);            
        AfwEditMotor::select(
            $l_rep,
            explode($separator, trim($val, $separator)),
            $infos_arr,
            "",
            false
        );
    }
    else
    {
        $l_rep = AfwLoadHelper::vhGetListe($objRep, $col_name, $obj->getTableName(), $desc["WHERE"], $action = "loadManyFollowingStructure", $lang, $val_to_keep, $desc['ORDERBY'], $dropdown = true, $optim = true, $max_items_count=false);            
        AfwEditMotor::mobiselector(
            $l_rep,
            explode($separator, trim($val, $separator)),
            $infos_arr
        );
    }
        
