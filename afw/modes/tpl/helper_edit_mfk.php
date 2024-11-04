<?php
    $infos_arr = array(
        "class" => "form-control form-mfk",
        "name"  => $col_name . "[]",
        "id"  => $col_name,
        "size"  => 5,
        "multi" => true,
        "tabindex" => $qedit_orderindex,
        "onchange" => $onchange,
        "style" => $input_style,

    );
    if ($desc["SEL_OPTIONS"]) $infos_arr = array_merge($infos_arr, $desc["SEL_OPTIONS"]);

    if ($desc["SEL_CSS_CLASS"]) $infos_arr["class"] = $desc["SEL_CSS_CLASS"];

    if ($desc["FORMAT"]=="dropdown")
    {
        select(
            $l_rep,
            explode($separator, trim($val, $separator)),
            $infos_arr,
            "",
            false
        );
    }
    else
    {
        mobiselector(
            $l_rep,
            explode($separator, trim($val, $separator)),
            $infos_arr
        );
    }
        
