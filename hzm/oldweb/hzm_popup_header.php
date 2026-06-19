<?php

/**
 * @var string $Main_Page
 * @var string $imposed_charset
 * 
 */
if (!$my_theme)  $my_theme = "simple";
AfwSession::startSession();

$file_hzm_dir_name = dirname(__FILE__);

$f3c = substr($Main_Page, 0, 3);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?
if ($imposed_charset) $page_charset = $imposed_charset;
else $page_charset = "UTF-8";

?>
<html>
<?php
include("$file_hzm_dir_name/../lib/hzm/web/hzm_html_head.php");
?>

<body class='popupbody'>
  <div class="popupbox">
    <!-- #Header -->





    <!-- #END OF Header -->
    <div class="notification_message_container">

      <?php
      if (AfwSession::getVar("error")) {
      ?>
        <div class="alert messages messages--error alert-dismissable" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          <?php
          $cnt = count(explode("<br>", AfwSession::getVar("error")));
          if ($cnt > 1) {
          ?>
            يوجد أخطاء : <br>
          <?php
          }
          echo AfwSession::getVar("error");
          ?>
        </div><br>

      <?php
      }

      if (AfwSession::getVar("warning")) {
      ?>
        <div class="alert messages messages--warning alert-dismissable" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          <?php
          $cnt = count(explode("<br>", AfwSession::getVar("warning")));
          if ($cnt > 1) {
          ?>
            يوجد تنبيهات : <br>
          <?php
          }
          echo AfwSession::getVar("warning");
          ?>
        </div><br>
      <?php
      }

      if (AfwSession::getVar("information")) {
      ?>
        <div class="alert messages messages--status  alert-dismissable <?= AfwSession::getVar("information-class") ?>" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><?php echo AfwSession::getVar("information"); ?></div><br>
      <?php
      }

      if (AfwSession::getVar("success")) {
      ?>
        <div class="alert messages messages--success alert-dismissable  <?= AfwSession::getVar("information-class") ?>" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><?php echo AfwSession::getVar("success"); ?></div>
      <?php
      }

      if (AfwSession::getVar("slog")) {
      ?>
        <!-- SLOG :
                <?php echo AfwSession::getVar("slog"); ?>
                -->
      <?php
      }

      ?>
    </div>
    <?
    include_once("gpie_body.php");
    ?>