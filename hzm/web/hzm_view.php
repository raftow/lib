<?php
     // hzm_view.php
     
     list($a0,$a1,$a2) = explode(":", $a);
     
     $a0_arr = explode("-", $a0);
     $a1_arr = explode("-", $a1);
     $a2_arr = explode("-", $a2);


     
  

     // ex bau-RAMObject-9:ums-arole-212
     if(($a0_arr[0]=="bau") and ($a0_arr[1]=="RAMObject"))
     {
          $module = $a1_arr[0];
          $classe = $a1_arr[1];
          $gen_id = $a1_arr[2];
          $robj_id = $a0_arr[2];
          
          $module = $a1_arr[0];
          $classe = $a1_arr[1];
          $gen_id = $a1_arr[2];
          
          $currmod = $module;
          $cl = $classe;
          $id = $gen_id;
          $popup = 1;
          $file_hzm_dir_name = dirname(__FILE__);
          
          include("$file_hzm_dir_name/../../../lib/afw/modes/afw_mode_display.php");
          
     }
     else
     {
          $module  = $a0_arr[0];
          $classe  = $a0_arr[1];
          $my_id = $a0_arr[2];
          $mode_view = $a0_arr[3];
          
          if(!$mode_view) $mode_view = "minibox";
          
          $currmod = $module;
          $cl = $classe;
          $id = $my_id;
          $popup = 1;
          $file_hzm_dir_name = dirname(__FILE__);
          $noclose_btn = 1;
          
          include("$file_hzm_dir_name/../../../lib/afw/modes/afw_mode_$mode_view.php");
          
     }
?>