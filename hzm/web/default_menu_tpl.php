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
                              [me_connected_s]
                              <li class="hzm_[login_out_cl]"><a href="[logout_page]" class="a[login_out_cl]"><i class="fa fa-[login_out_css]"></i>[logout_title]</a></li>
                              [me_connected_s]
                              [me_not_connected_s]
                              <li class="hzm_[login_out_cl]"><a href="[login_page]" class="a[login_out_cl]"><i class="fa fa-[login_out_css]"></i>[login_title]</a></li>
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