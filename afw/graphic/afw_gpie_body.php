<?
     if((is_array($data_pie) and (count($data_pie)>0)) or (is_array($data_bar) and (count($data_bar)>0)))
     {
          $widthChart_extended = $widthChart+20; 
          $heightChart_extended = $heightChart+20;
          AfwMainPage::addOutput("<div id=\"piechart_3d\" class=\"stats_table\" ></div>");
     }

?>