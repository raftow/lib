<div class='home_panel'>
        <!--<div class='home_menu'>
              
        </div>-->
        <div class='home_body'>
              <div class='home_path_browser'>
              <?php if($objToShow) echo $objToShow->id.".".$objToShow->getDisplay($lang) ?>
              </div>
              <div class='home_body_view'>
                  <?php 
                  
                  if($objToShow) 
                  {
                        $structure["MINIBOX-TEMPLATE"] = "AUTO";
                        echo AfwShowHelper::showMinibox($objToShow, $structure, $lang);
                  }
                  ?>
              </div>
        </div>
</div>
