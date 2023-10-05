<div class="section" id="section1">
	<div class="topContent">
  		  <div class="row expanded" style="padding: 4px;">
	  		<div class="medium-12 large-12 columns text-center large-text-right">
                        
                             <div class="logo_company">  
                               <img src="[img-company-path]/logo-company[xmodule].png" alt="" style="margin-top:5px;height: [logo_comp_height]px;"/> 
                             </div>  
                             <div class="title_company">  
                               <img src="[img-company-path]/title-company[xmodule].png" alt="" style="margin-top:-10px;height: [title_comp_height]px;"/> 
                             </div>
                             [welcome_div]
                             <div class="logo_application">
                                    <img src="[img-path]/logo-application.png" alt="" style="margin-top:5px;float: left;height: [logo_app_height]px"/>
                             </div>
                             <!--<div class="annonce_application">
                                    <img src="[img-path]/annonce.png" alt="" style="margin-top:5px;float: left;height: [logo_app_height]px"/>
                             </div>-->
                             <div class="title_application">
                                    <img src="[img-path]/title-application[run_mode].png" alt="" style="margin-top:5px;float: left;height: [title_app_height]px"/>
                             </div>     
                             <div class="[calendar_class]">
                                   <div id="year" class="calendar_year">[display_date_year]</div>
                                   <div class="calendar_day">
                                    <span class="dday">[display_date_day]</span>
                                    <br>
                                    [display_date_month]
                                    </div>
                             </div> 
                                                                         
                        </div>
		  </div>
                  [no_menu_s]
                  <div class="hideScreen">
                    <span class="menuBar openScreen">القائمة</span>
                  </div>
                  <nav id="front_main_menu" class="front_main_menu cms_container navbar navbar-inverse">
                          <div class="container-fluid">
                            <ul class="hzm_front_menu_bar nav navbar-nav">
                              [main_menu_item_s]
                                    <li class="navbar-header">
                                        <a class="navbar-brand" href="[out_index_page]">[site_name]</a>
                                    </li>
                              [main_menu_item_e]
                              [me_connecting_s]
                              <li class="hzm_[login_out_cl]"><a href="[login_out_page]" class="a[login_out_cl]"><i class="fa fa-[login_out_css]"></i>[login_out_title]</a></li>
                              [me_connecting_e]
                              [me_not_connected_s]
                              <li [register_css_class]><a href="[register_file].php"  class="aregister"><i class="fa fa-register"></i>التسجيل لأول مرة</a></li>
                              [me_not_connected_e]
                              [me_connected_s]
                              <li [main_item_css_class]><a href="index.php"><i class="fa fa-home"></i>الرئيسية</a></li>
                              [me_connected_e]
                              [hzm_front_menu]
                            </ul>
                          </div>
                  </nav>
                  <script> 
                          $(document).ready(function() {       
                                  $(".menuBar").click(function(){
                                          $("#front_main_menu").toggleClass("active");
                                  });
                          });
                  </script>
                  [no_menu_e]
                  [no_banner_s]
                  <div class="banner_application">
                      <div style="margin-top:0px;float: left;height: [banner_height]px;width: 100%;background-image: url(pic//banner.jpg);"></div>
                  </div>
                  [no_banner_e]
                  
                  [no_scroll_banner_s]
                  <div class="banner_application scroll-left">
                        <div class="inner">
                        <img src="pic//banner.jpg" style="margin-top:0px;float: left;height: 177px;width: auto;">
                        </div>
                  </div>
                  [no_scroll_banner_e]                  
	</div>
</div>