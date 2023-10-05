        <nav class="navbar navbar-inverse">
          <div class="container-fluid">
                    <div class="navbar-header topcontrols">
                      <p class="navbar-brand" href="#"><?=$NOM_SYSTEM?></p><br>
                    </div>

                    <ul class="nav navbar-nav topcontrols">
                    <?if($objme)
                      {
                          //die(var_export($_SERVER,true));
                          include "dynamic_menu.php";
                      }    
                     ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right topcontrols">
                          <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?=AFWObject::traduireOperator("LANGUE", $lang)?>
                                <span class="caret" style="padding: 0px 0px 0px 0px !important;"></span></a>
                                <ul class='dropdown-menu'>
                                <?   
                                    foreach($uri_arr as $lang_code => $uri_item)
                                    {
                                        $menu_item_title = $LANG_NAMES[$lang][$lang_code];
                                        if($uri_item) echo "<li><a href='$uri_item'>$menu_item_title</a></li>\n"; 
                                    }    
                                        
                                ?>
                                </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right topcontrols">
                          <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?=AFWObject::traduireOperator("OPTIONS", $lang)?>
                                <span class="caret" style="padding: 0px 0px 0px 0px !important;"></span></a>
                                <ul class='dropdown-menu'>
                                <?   
                                    foreach($right_menu as $menu_item)
                                    {
                                        $menu_item_page = $menu_item["href"];
                                        $menu_item_title = $menu_item["title"];
                                        $menu_item_css = $menu_item["css"];
                                        echo "<li><a href='$menu_item_page' class='$menu_item_css'>$menu_item_title</a></li>\n"; 
                                    }
                                ?>
                                </ul>
                        </li>
                    </ul>
                    <form class="navbar-form navbar-left">
                              <div class="search fleft">
        						<input type="button" class="searchbtn fleft"><input type="text" class="searchtxt fleft" placeholder="<?=AFWObject::traduireOperator("SEARCH_HERE", $lang)?>...">
        		      </div>
                    </form>                       
                  </div>
                 
  
        </nav>