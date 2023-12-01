<?
   $page_charset = "UTF-8";
   if(!$lang) $lang = "ar";
   
   if($lang=="ar") $dir = "rtl";
   else $dir = "ltr";  
   
   if(!$MODULE) $MODULE = "pag";  
    
     
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><meta http-equiv="Content-Type" content="text/html; charset=<?=$page_charset?>">
<!-- <link type="text/css" rel="stylesheet" href="../../css/simple/pag_checkboxes.css"> -->
<!-- plugins -->
<link type="text/css" rel="stylesheet" href="../../bmulti/css/bootstrap-multiselect.css"/>
<!-- end plugins -->
<link rel="stylesheet" href="../../css/jquery-ui-1.11.4.css">
<link rel="stylesheet" href="../../css/font-awesome.min.css">
<link rel="stylesheet" href="../../css/menu_<?=$lang?>.css">
<link rel="stylesheet" href="../../css/front-application.css">
<link rel="stylesheet" href="../../css/hzm-v001.css">

<link rel="stylesheet" href="../../css/front_app.css">
<link rel="stylesheet" href="../../bootstrap/bootstrap-v3.min.css">
<link rel="stylesheet" href="../../bsel/css/bootstrap-select.css">
<link href="./css/logo-app-icon.png" rel="shortcut icon">
<title>Momken Framework</title>

<link href="../../css/simple/style_common.css" rel="stylesheet" type="text/css">
<link href="../../css/simple/style_<?=$lang?>.css" rel="stylesheet" type="text/css">

<link href="../../css/def_ar_front.css" rel="stylesheet" type="text/css">
<link href="../../css/simple/style_common.css" rel="stylesheet" type="text/css">
<link href="../../css/simple/style_ar.css" rel="stylesheet" type="text/css">
<link href="../../css/simple/front_menu.css" rel="stylesheet" type="text/css">
<link href="../../css/header_thin.css" rel="stylesheet">
</head>

<body dir="<?=$dir?>" >
<?php
   if(!$body_css_class) $body_css_class = "hzm_body";
?> 
<div class="container">
<div class='<?=$body_css_class?>'>

<!-- #END OF Header -->
