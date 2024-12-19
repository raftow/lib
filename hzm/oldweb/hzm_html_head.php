<?php
if (!$lang) $lang = "ar";
if (!$my_font) $my_font = "front";
?>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo  $page_charset ?>">
  <!-- <link type="text/css" rel="stylesheet" href="../lib/css/<?php echo  $my_theme ?>/pag_checkboxes.css"> -->
  <!-- plugins -->
  <link type="text/css" rel="stylesheet" href="../lib/bmulti/css/bootstrap-multiselect.css" />
  <!-- end plugins -->
  <link rel="stylesheet" href="../lib/css/jquery-ui-1.14.0.css">
  <link rel="stylesheet" href="../lib/css/font-awesome.min-4.3.css">
  <link rel="stylesheet" href="../lib/css/font-awesome.min.css">
  <link rel="stylesheet" href="../lib/css/menu_<?php echo  $lang ?>.css">
  <?php
  if ($front_header) {
  ?>
    <link rel="stylesheet" href="../lib/css/front-application.css">
    <link rel="stylesheet" href="../lib/css/hzm-v001.css">
    
  <?php
  }
  if($otp)
  {
?>
<link rel="stylesheet" href="../lib/css/otp.css">
<?php
  }

  ?>
  <?php
  if ($front_application) {
  ?>
    <link rel="stylesheet" href="../lib/css/front_app.css">
    <link rel="stylesheet" href="../lib/css/material-design-iconic-font.min.css">
  <?php
  }
  ?>



  <link rel="stylesheet" href="../lib/bootstrap/bootstrap-v5.3.3.min.css">


  <link rel="stylesheet" href="../lib/bsel/css/bootstrap-select.css">
  <?php
  if ($jstree_activate) {
  ?>
    <link rel="stylesheet" href="../lib/css/jstree/default/style.min.css" />
  <?php
  }

  if ($ivviewer_activate) {
  ?>
    <link rel="stylesheet" type="text/css" media="screen" href="../lib/viewer/viewer.css" />
  <?php
  }
  ?>
  <link rel="stylesheet" href="../lib/css/dropdowntree.css" />
  <script src="../lib/js/qedit.js"></script>
  <script src="../lib/js/jquery-3.6.0.min.js"></script>
  <?php
  if ($ivviewer_activate) {
    // <script type="text/javascript" src="../lib/iv-viewer/dist/iv-viewer.js"></script>

  ?>

  <?php
  }
  ?>
  <script src="../lib/bootstrap/bootstrap-v5.3.3.min.js"></script>
  <!-- plugins -->
  <script src="../lib/bmulti/js/bootstrap-multiselect.js"></script>
  <script src="../lib/js/jquery-clock-timepicker.min.js"></script>
  <script src="../lib/bsel/js/bootstrap-select.js"></script>
  <script src="../lib/js/hzm.js"></script>
  <!-- script src="../lib/tree/tree.jquery.js"></script> -->
  <?php
  if ($jstree_activate) {
  ?>
    <script src="../lib/js/jstree.min.js"></script>
  <?php
  }
  ?>
  <script src="../lib/js/dropdowntree.js"></script>

  <script src="../lib/js/sweetalert.min.js"></script>
  <script src="../lib/js/jquery.validate.js"></script>
  <?php
  if ($lang != "en") {
  ?>
    <script src="../lib/js/localization/messages_<?php echo  $lang ?>.js"></script>
  <?php
  }
  $cmodule = AfwUrlManager::currentURIModule();
  $xmodule = AfwSession::getCurrentlyExecutedModule();
  $pagecode = AfwUrlManager::currentPageCode();
  
  ?>
  <!-- end plugins -->
  <!-- datatable/css/js -->
  <?php
  
  

  // echo "[$Main_Page/$datatable_css_file/$datatable_on]"

  if ($datatable_on) {
    if (!$Main_Page) $Main_Page = $_GET["Main_Page"];

    /*
      if($Main_Page=="afw_mode_search.php") include("../lib/datatable/datatable_search_css.php");
      // elseif($datatable_css_file) die($datatable_css_file);
      elseif($datatable_css_file) include($datatable_css_file);
      //else die("../lib/datatable/datatable_css.php"); 
      else include("../lib/datatable/datatable_css.php");*/
  }

  if ($datatable_on) {
    if ($lang == "ar") {
  ?>
      <script src="../lib/js/jquery.dataTables.min.js"></script>
    <?php

    } 
    else  //if($lang=="en")
    {
    ?>
      <script src="../lib/js/jquery.dataTables_en.min.js"></script>
  <?php

    }
  } 
  else 
  {
    // nothing todo for the moment
  }

  $crst = md5("crst".date("YmdHis"));
  ?>

  <!-- end datatable/css/js -->
  <script src="../lib/js/jquery-ui-1.14.0.js"></script>
  <script src="../lib/js/jquery.ui.autocomplete.html.js"></script>


  <link rel="stylesheet" href="../lib/hijra/jquery.calendars.picker.css" />


  <script src="../lib/hijra/jquery.calendars.js"></script>
  <script src="../lib/hijra/jquery.calendars.plus.js"></script>
  <script src="../lib/hijra/jquery.calendars.picker.js"></script>
  <script src="../lib/hijra/jquery.calendars.ummalqura.js"></script>

  <link href="../lib/css/autocomplete.css" rel="stylesheet" type="text/css">
  <link href="../lib/css/responsive.css" rel="stylesheet" type="text/css">

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo  $config["website-description"] ?>">
  <meta name="keywords" content="<?php echo  $config["website-keywords"] ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=EDGE">
  <link href="<?php echo $config["img-path"] ?>favicon.ico" rel="shortcut icon">

  <title><?php echo  $NOM_SITE[$lang] ?></title>

  <link href="../lib/css/def_<?php echo  $lang ?>_<?php echo $my_font ? $my_font : "front"; ?>.css" rel="stylesheet" type="text/css">
  <link href="../lib/css/<?php echo  $my_theme ?>/style_common.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css">
  <link href="../lib/css/<?php echo  $my_theme ?>/style_<?php echo  $lang ?>.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css">
  <link href="../lib/css/<?php echo  $my_theme ?>/front_menu.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css">
  <?php
  if ($header_style) {
  ?>
    <link href="../lib/css/<?php echo  $header_style ?>.css?crst=<?php echo $crst?>" rel="stylesheet">
  <?php
  }
  if ($page_css_file) {
  ?>
    <link href="./css/<?php echo $page_css_file ?>.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css" type="text/css">
  <?php
  }
  
  if ($main_module and ($xmodule != $main_module)) {
  ?>
    <link href="../<?php echo  $main_module ?>/css/module.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css" type="text/css">

  <?php
  }

  if (!$no_common_css) {
  ?>
    <link href="../external/css/common.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css" type="text/css">
  <?php
  }
  ?>
  <link href="./css/module.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css" type="text/css">
  <?php
  if ($cmodule and $xmodule and ($cmodule != $xmodule)) {
  ?>
    <link href="./css/module_<?php echo  $xmodule ?>.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css">
  <?php
  }
  if ($main_module and $cmodule and ($cmodule != $main_module)) {
  ?>
    <link href="../<?php echo  $main_module ?>/css/module_<?php echo  $cmodule ?>.css?crst=<?php echo $crst?>" rel="stylesheet" type="text/css" type="text/css">
  <?php
  }

  ?>

  <link href="../lib/skins/square/green.css" rel="stylesheet" type="text/css">
  <link href="../lib/skins/square/red.css" rel="stylesheet" type="text/css">
  <script src="../lib/js/icheck.js"></script>
  <?php

  foreach ($custom_scripts as $custom_script) {
    if ($custom_script["type"] == "css") {
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $custom_script["path"] . "\" />";
    } elseif ($custom_script["type"] == "js") {
      echo "<script  type=\"text/javascript\" src=\"" . $custom_script["path"] . "\" ></script>";
    } else die($custom_script["path"] . " has unknown type");
  }
  //die(var_export($custom_scripts,true));


  if ($fancybox_activate) {
  ?>
    <link rel="stylesheet" type="text/css" media="screen" href="../lib/fancy-box/jquery.fancybox-1.3.4.css" />
  <?php
  }


  ?>

  <script src="./js/module.js"></script>
  <script src="./js/<?php echo $pagecode; ?>.js"></script>
  
  <?php
  include("my_javascripts.php");
  ?>



  <script>
    $(document).ready(function() {
      $('input.echeckbox').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
      });

      $('input.rcheckbox').iCheck({
        checkboxClass: 'icheckbox_square-red',
        radioClass: 'iradio_square-red',
        increaseArea: '20%' // optional
      });

    });
  </script>

  <?php
  include_once("gpie_header.php");
  ?>
  <link rel="stylesheet" type="text/css" href="../lib/afw/afw_style.css" />
</head>