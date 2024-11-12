<?php
if(!$page_label and !$images['calendar'])
{
    $images =array(
        'today' 		=> '../lib/images/calendar.png' ,
        'calendar' 		=> '../lib/images/calendar.png',
        'select'		=> '../lib/images/loop24.png',
        'delete'		=> '../lib/images/trash.png', 
        //'trash'		        => '../lib/images/trash.png',  
        'modifier'		=> '../lib/images/pencil.png',
        'editppp'		=> '../lib/images/editppp.png',
        'background-submit'	=> '../lib/images/box-ombre.jpg',
        'locked'  		=> '../lib/images/locked.png',
        'off'  		=> '../lib/images/off.png',
        'locked_on_me' 		=> '../lib/images/lock.png',
        'locked_him_self' 		=> '../lib/images/him-self-locked.png',
    );
        
    $header_bloc_edit = "";
    $footer_bloc_edit = "";
    $class_xqe = "";
    $class_search = "";
    if(!$Main_Page) $Main_Page = $_GET["Main_Page"];
    if(!$Main_Page) $Main_Page = $_POST["Main_Page"];

    $tres = "";

    if((!$class_xqe_special) and (!$class_xqe) 
        and (($Main_Page=="afw_mode_search.php") or 
            ($Main_Page=="afw_mode_qsearch.php")
            )
    )
    {
        $class_search = "search_";
    }

    if((!$class_xqe_special) and (!$class_xqe) 
        and (($Main_Page=="afw_mode_qedit.php") or 
            ($Main_Page=="afw_handle_default_qedit.php")
            )
    )
    {
        $class_xqe = "xqe_";
        $tres = "tres";
    }

    if((!$class_xqe_special) and (!$class_xqe) 
        and (($Main_Page=="afw_mode_edit.php") or
            ($Main_Page=="afw_handle_default_edit.php")
            )
    )
    {
        // $class_xqe = "xqe_";
    }

    // else die("class_xqe = '$class_xqe' Main_Page = '$Main_Page' ");

    $class_inputText = "${class_search}${class_xqe}inputtext"; 
    $class_inputPK = "inputreadonly input${tres}court";    
    $class_inputInt = "$class_inputText input${tres}court";
    $class_inputSelect = "${class_search}${class_xqe}comm_select inputselect";
    $class_inputSelectcourt = "${class_search}${class_xqe}comm_select inputselectcourt";
    $class_inputSelectLong = "${class_search}${class_xqe}comm_select inputselectlong";
    $class_inputSelect_multi = "${class_search}${class_xqe}comm_select inputselectlong";
    $class_inputSelect_multi_big = "${class_search}${class_xqe}comm_select inputselecttreslong";
    $class_inputOper = "${class_search}${class_xqe}comm_select inputselect inputmoyen";
    $class_inputTextFk = "$class_inputText inputmoyen";
    $class_inputTextDate = "$class_inputText ";
    $class_inputDate = "$class_inputText inputdate";
    $class_inputButton = "astboutonc";
    $class_inputRadio = "${class_search}${class_xqe}comm_select inputselectcourt";
    $class_inputSubmit = "bluebtn submit-btn fright";
    $class_ddbSubmit = "redbtn submit-btn fright";
    $class_inputDelete = "redbtn btn";
    $class_inputReset = "yellowbtn btn fleft";
    $class_inputLien = "yellowbtn btn fright_top";
    $class_inputNew = "greenbtn btn fleft";
    $class_titre = "black";
    $class_bloc = "blc_head";
    $class_table = "grid";
    $display_grid = "display dataTable afwgrid";
    $class_tr1 = "item";
    $class_tr2 = "altitem";
    $class_tr1_sel = "selecteditem";
    $class_tr2_sel = "altselecteditem";
    $class_inputSelected = "selectedinput";
    $class_th = "astth";
    $class_td1 = "item";
    $class_td2 = "altitem";
    $class_td_off = "asttdoff";
    $aligntd = "right";
    $qedit_align_td = "center";
    $pct_tab_search_criteria = "90%";
    $pct_tab_search_result = "90%";
    $pct_tab_edit_mode = "100%";
    $table_search_result_class = "search_result_table";

    $page_label = AfwLanguageHelper::tarjemOperator("page", $lang);
    $record_label = AfwLanguageHelper::tarjemOperator("record", $lang);
    // $page_of_label = AfwLanguageHelper::tarjemOperator("page_of", $lang); 
    $new_instance =  AfwLanguageHelper::tarjemOperator("new_instance", $lang);
    $qedit_new =     AfwLanguageHelper::tarjemOperator("qedit_new", $lang); 
    $qedit_update =  AfwLanguageHelper::tarjemOperator("qedit_update", $lang); 
    $other_search =  AfwLanguageHelper::tarjemOperator("other_search", $lang); 
    $back_to_last_form = AfwLanguageHelper::tarjemOperator("back_to_last_form", $lang);
    $new_search_operation = AfwLanguageHelper::tarjemOperator("new_search", $lang);

    $yes_label = AfwLanguageHelper::tarjemOperator("Y", $lang);
    $no_label = AfwLanguageHelper::tarjemOperator("N", $lang);
    $dkn_label = AfwLanguageHelper::tarjemOperator("W", $lang);
    
    $qedit_other_search = $other_search;
    $qedit_mode_default_new_rows_nb = 5;
    $qedit_mode_default_max_edit_rows_nb = 30;
    $height_tr_form_edit = 60;
}

?>