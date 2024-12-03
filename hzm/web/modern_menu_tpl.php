            [no_menu_s]
            <nav>
                [main_menu_item_s]
                <div class="logo-name">
                    <div class="logo_application">
                        <img src="[img-path]/logo-application.png" alt="" style="margin-top:5px;float: left;height: 48px"/>
                    </div>

                    <span class="logo_name">[site_name]</span>
                </div>
                [main_menu_item_e]
                <div class="menu-items">
                    <ul class="nav-links">
                        [hzm_front_menu]
                    </ul>
                    
                    <ul class="logout-mode">
                        [me_connected_s]
                        <li><a href="[logout_page]">
                            <i class="fa fa-signout"></i>
                            <span class="link-name">[logout_title]</span>
                        </a></li>
                        [me_connected_e]
                        [me_not_connected_s]
                        <li><a href="[login_page]">
                            <i class="uil uil-signin"></i>
                            <span class="link-name">[login_title]</span>
                        </a></li>
                        [me_not_connected_e]
                        <li class="mode">
                            <a href="#">
                                <i class="uil uil-moon"></i>
                            <span class="link-name">[dark_mode]</span>
                        </a>

                        <div class="mode-toggle">
                        <span class="switch"></span>
                        </div>
                    </li>
                    </ul>
                </div>
            </nav>
            [no_menu_e]