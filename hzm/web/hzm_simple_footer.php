<?php

 $objme = AfwSession::getUserConnected();


  if($datatable_on) include("../lib/datatable/datatable_js.php");

  include_once("hzm_footer_features_js.php");
?>


<div class="section simple_footer footer_bg" id="footer_div">
        <div class="rowfooter expanded rowfooter<?=$MODULE?>">
                <div class="large-12 columns">
                        <img src="../lib/images/sm-01.png" alt="" class="footer_img effectscale sm-icon">
                        <img src="../lib/images/sm-02.png" alt="" class="footer_img effectscale sm-icon">
                        <img src="../lib/images/sm-03.png" alt="" class="footer_img effectscale sm-icon">
                        <img src="../lib/images/sm-04.png" alt="" class="footer_img effectscale sm-icon">
                        <img src="../lib/images/sm-05.png" alt="" class="footer_img effectscale sm-icon">
                        <img src="../lib/images/sm-06.png" alt="" class="footer_img effectscale sm-icon">
                                
                </div>
        </div>
        
        <?php
    $copyright_infos = AfwSession::config("copyright_infos",true);
    if($copyright_infos)
    {
?>
        <div class="copyright simple_footer">
                <div class="simple_copyright"><img src="../external/pic/copyright.png"></div>
                <div class="powered_by"><img src="../external/pic/powered_by_logo.png"></div>
        </div>
<?php
    }
?>
        
</div>    
<div class="footer-s hzm-loader-div hide" id="myloader">
        <div class="hzm-loading-div" id="myloading">
                الرجاء الانتظار جارٍ معالجة الطلب                   
        </div>

</div>
<?php
     
        AfwSession::log_config();

        $end_main_time = microtime();
        $duree_ms = round(($end_main_time - $start_main_time)*100000)/100;
        if($duree_ms<0) $duree_ms += 1000;
        AfwSession::hzmLog("end of footer-include $duree_ms milli-sec", $MODULE);

        if(AfwSession::config("MODE_DEVELOPMENT",false) or AfwSession::hasOption("SQL_LOG"))
        {
                echo "<div id='analysis_log'><div id=\"analysis_log\"><div class=\"fleft sql hzm\"><h1><b>System LOG activated :</b></h1></div><br><br>";
                echo AfwSession::getLog();                
                echo "</div>";
        }

        if($objme)
        {
                if(AfwSession::hasOption("ICAN_DO_LOG")) $objme->showICanDoLog();
                if(AfwSession::hasOption("MEMORY_REPORT")) AfwMemoryHelper::memReport();
        }
?>
  

