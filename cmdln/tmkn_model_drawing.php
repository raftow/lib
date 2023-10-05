<?php
   function convert_position_name($posName)
   {
      $posName = strtoupper($posName);
      if($posName=="L") return 1;
      if($posName=="D") return 17;
      if($posName=="C") return 33;
      if($posName=="Q") return 49;
      if($posName=="R") return 65;
      if($posName=="T") return 1;
      if($posName=="N") return 9;
      if($posName=="M") return 17;
      if($posName=="W") return 25;
      if($posName=="B") return 33;
      
      return 1;
   }

   function calcDataStringToFitAndCenter($string, $t_width, $t_height, $font_width, $font_height, $margin_x=40)
   {
        $string_max_len = ($t_width - 2*$margin_x) / $font_width; 
        $string = truncateLongString($string, $string_max_len);
        $padding_x = paddingStringToCenterX($string, $font_width, $t_width);
        $padding_y = paddingStringToCenterY($font_height, $t_height);
        
        return array($string_max_len, $string, $padding_x, $padding_y);
   }
   
   
   function truncateLongString($string, $maxlen)
   {
       if(strlen($string)<= $maxlen) return $string;
       $string = substr($string, 0, $maxlen-2)."..";
       
       return $string;
   }

   function paddingStringToCenterX($string, $font_width, $t_width)
   {
       $string_width = strlen($string) * $font_width;
       return ($t_width-$string_width)/2;
   }
   
   function paddingStringToCenterY($font_height, $t_height)
   {
       return ($t_height-$font_height)/2;
   }
   

   function drawFilledRectangle($canvas, $x1 , $y1 , $x2 , $y2 , $border_color, $bg_color)
   {
       imagefilledrectangle ($canvas , $x1 , $y1 , $x2 , $y2 , $bg_color);
       imagerectangle($canvas , $x1 , $y1 , $x2 , $y2, $border_color);
   }
   
   function drawArrow($canvas, $x1, $y1, $x2, $y2, $color, $alength=6, $awidth=6) 
   {
            $distance = sqrt(pow($x1 - $x2, 2) + pow($y1 - $y2, 2));
        
            $dx = $x2 + ($x1 - $x2) * $alength / $distance;
            $dy = $y2 + ($y1 - $y2) * $alength / $distance;
        
            $k = $awidth / $alength;
        
            $x2o = $x2 - $dx;
            $y2o = $dy - $y2;
        
            $x3 = $y2o * $k + $dx;
            $y3 = $x2o * $k + $dy;
        
            $x4 = $dx - $y2o * $k;
            $y4 = $dy - $x2o * $k;
        
            imageline($canvas, $x1, $y1, $dx, $dy, $color);
            imagefilledpolygon($canvas, array($x2, $y2, $x3, $y3, $x4, $y4), 3, $color);
    }
   
   
   function imagelinethick($canvas, $x1, $y1, $x2, $y2, $color, $thick = 1)
   {
            /* this way it works well only for orthogonal lines
            imagesetthickness($canvas, $thick);
            return imageline($canvas, $x1, $y1, $x2, $y2, $color);
            */
            if ($thick == 1) {
                return imageline($canvas, $x1, $y1, $x2, $y2, $color);
            }
            $t = $thick / 2 - 0.5;
            if ($x1 == $x2 || $y1 == $y2) {
                return imagefilledrectangle($canvas, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
            }
            $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
            $a = $t / sqrt(1 + pow($k, 2));
            $points = array(
                round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
                round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
                round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
                round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
            );
            imagefilledpolygon($canvas, $points, 4, $color);
            return imagepolygon($canvas, $points, 4, $color);
   }

   function imagepolarline($canvas, $x1,$y1,$length,$angle,$color)
   {
        $x2 = $x1 + sin( deg2rad($angle) ) * $length;
        $y2 = $y1 + cos( deg2rad($angle+180) ) * $length;

        imageline($canvas, $x1,$y1,$x2,$y2,$color);
   }
   
   function drawGrid($canvas,  $width_canvas, $height_canvas, $square_nb_small=4, $width_small=20, $x0=1, $y0=1, $color_bold="", $color_normal="")
   {
        if(!$color_bold) $color_bold = imagecolorallocate($canvas, 231, 231, 231);
        if(!$color_normal) $color_normal = imagecolorallocate($canvas, 245, 245, 245);
        $x = $x0*$width_small;
        $x_num = 0;
        while($x<=$width_canvas)
        {
                $color = (($x_num % $square_nb_small) == 0) ? $color_bold : $color_normal;
                imageline($canvas,$x,0,$x,$height_canvas, $color);
                
                $x += $width_small;
                $x_num++;
        }
        
        $y = $y0*$width_small;
        $y_num = 0;
        while($y<=$height_canvas)
        {
                $color = (($y_num % $square_nb_small) == 0) ? $color_bold : $color_normal;
                imageline($canvas, 0,$y,$width_canvas,$y, $color);
                
                $y += $width_small;
                $y_num++;
        }
        
   }
   
   
   function drawRelatedFKs($canvas, $tableListByName, $originX, $originY, $sizeX, $nbColsMax, $position="lt", $width_small=28, $zoom=1)
   {
         $black = imagecolorallocate($canvas, 0, 0, 0);
         $green = imagecolorallocate($canvas, 132, 135, 28);
         $fk_num = 1;
         /*
         echo "<b>drawRelatedFKs for : </b> <br>\n";
         foreach($tableListByName as $table_name => $tableRow)
         {
                  $internal = $tableRow["internal"];
                  echo "<b>table_name : </b> $table_name , internal=$internal<br>\n";
         }
         die(); */
         
         $toPointArr = array();
                      
         foreach($tableListByName as $table_name => $tableRow)
         {
                $table_to = $table_name;
                $tableItem = $tableRow["item"];
                $table_to_showedFieldList = $tableRow["showedFieldList"];
                $originFK = array();
                if($tableItem)
                {
                      $relatedFieldsToMeList = $tableRow["relatedFieldsToMe"];
                      $showableFieldsToMeList = $tableRow["showedFieldList"];
                      if($table_name=="xxx")
                      {
                              echo "<b>related fields for table : </b> ".$table_name." <br>\n";
                              foreach($relatedFieldsToMeList as $relatedField)
                              {
                                  $relatedTable = $relatedField->hetTable();
                                  if($relatedTable) $table_from = $relatedTable->valName();
                                  else $table_from = "???";
                                  
                                  echo "<b>field : </b> $table_from.".$relatedField->valName()." <br>\n";
                              }
                               
                              echo("relatedFieldsToMeList count = ".count($relatedFieldsToMeList)." <br>\n");
                              
                              echo "<b>showed fields for table : </b> ".$table_name." <br>\n";
                              foreach($showableFieldsToMeList as $shFieldName => $shFieldRow)
                              {
                                  echo "<b>field : </b> $table_name.".$shFieldName." <br>\n";
                              }
                              die("showedFieldList count = ".count($showableFieldsToMeList)." <br>\n");
                      }
                      
                      foreach($relatedFieldsToMeList as $relatedFieldId => $relatedField)
                      {
                           $originFK[$relatedFieldId] = array();
                           $relatedFieldName = $relatedField->valName();
                           $originFK[$relatedFieldId]["name"] = $relatedFieldName;
                           $relatedTable = $relatedField->hetTable();
                           if($relatedTable)
                           {
                                 $table_from = $relatedTable->valName();
                                 if($tableListByName[$table_from] and (!$tableListByName[$table_from]["exclude_fields"][$relatedFieldName]))
                                 {
                                         if($relatedFieldName=="xx") die(" exclude_fields for $table_from = ".var_export($tableListByName[$table_from]["exclude_fields"],true));
                                         $table_from_showedFieldList = $tableListByName[$table_from]["showedFieldList"];
                                         $originXFrom = $originX[$table_from];
                                         $originYFrom = $originY[$table_from];
                                         
                                         $sizeXFrom = $sizeX[$table_from];
                                         $nbColsMaxFrom = $nbColsMax[$table_from];
                                         
                                         $originXTo = $originX[$table_to];
                                         $originYTo = $originY[$table_to];
                                         
                                         $sizeXTo = $sizeX[$table_to];
                                         $nbColsMaxTo = $nbColsMax[$table_to];
                                         
                                         // die("relatedFieldName=$relatedFieldName, table_from_showedFieldList[$relatedFieldName] = ".var_export($table_from_showedFieldList[$relatedFieldName],true));
                                         
                                         $yField = $table_from_showedFieldList[$relatedFieldName]["position_y"];
                                         if(!$yField) $yField = $table_from_showedFieldList["..."]["position_y"];
                                         if($relatedFieldName=="xxx") die("$table_from -> showedFieldList[$relatedFieldName] = ".var_export($table_from_showedFieldList[$relatedFieldName],true));
                                         
                                         $originFK[$relatedFieldId]["from"] = array();
                                         $originFK[$relatedFieldId]["case"] = "2lines-xy";
                                         $originFK[$relatedFieldId]["from"]["table"]=$table_from;                                 
                                         if($originXFrom<$originXTo)
                                         {
                                                
                                                $originFK[$relatedFieldId]["from"]["position"] = "R";
                                                $originFK[$relatedFieldId]["from"]["position-x"] = $originXFrom + $sizeXFrom * $width_small * $zoom;
                                                $originFK[$relatedFieldId]["from"]["position-y"] = $yField + 0.5 * $width_small * $zoom;
                                         }
                                         elseif($originXFrom>$originXTo)
                                         {
                                                $originFK[$relatedFieldId]["from"]["position"] = "L";
                                                $originFK[$relatedFieldId]["from"]["position-x"] = $originXFrom;
                                                $originFK[$relatedFieldId]["from"]["position-y"] = $yField + 0.5 * $width_small * $zoom;
                                         }
                                         else
                                         {
                                                $originFK[$relatedFieldId]["from"]["position"] = "R";
                                                $originFK[$relatedFieldId]["from"]["position-x"] = $originXFrom + $sizeXFrom * $width_small * $zoom;
                                                $originFK[$relatedFieldId]["from"]["position-y"] = $yField + 0.5 * $width_small * $zoom;
                                                
                                                $originFK[$relatedFieldId]["case"] = "same-x";
                                         }
                                         
                                         
                                         
                                         $originFK[$relatedFieldId]["to"] = array();
                                         $originFK[$relatedFieldId]["to"]["table"]=$table_to;
                                         if($originYFrom<$originYTo)
                                         {
                                                $the_position = "T";
                                                
                                                $relatedFieldName_pos = $tableListByName[$table_from]["pos_fields"][$relatedFieldName];
                                                if(!$relatedFieldName_pos)
                                                {
                                                        if(!$toPointArr[$table_to][$the_position]) $toPointArr[$table_to][$the_position] = -1;
                                                        $toPointArr[$table_to][$the_position]+=2;
                                                        $relatedFieldName_pos = $toPointArr[$table_to][$the_position];
                                                }
                                                $originFK[$relatedFieldId]["to"]["position"] = $the_position;
                                                
                                                $originFK[$relatedFieldId]["to"]["position-x"] = $originXTo + $relatedFieldName_pos * $width_small * $zoom;
                                                $originFK[$relatedFieldId]["to"]["position-y"] = $originYTo;
                                         }
                                         elseif($originYFrom>$originYTo)
                                         {
                                                $the_position = "B";
                                                $relatedFieldName_pos = $tableListByName[$table_from]["pos_fields"][$relatedFieldName];
                                                if(!$relatedFieldName_pos)
                                                {
                                                        if(!$toPointArr[$table_to][$the_position]) $toPointArr[$table_to][$the_position] = -1;
                                                        $toPointArr[$table_to][$the_position]+=2;
                                                        $relatedFieldName_pos = $toPointArr[$table_to][$the_position];
                                                }
                                                
                                                $originFK[$relatedFieldId]["to"]["position"] = $the_position;
                                                $originFK[$relatedFieldId]["to"]["position-x"] = $originXTo + $relatedFieldName_pos * $width_small * $zoom;
                                                $originFK[$relatedFieldId]["to"]["position-y"] = $originYTo + ($nbColsMaxTo + 3) * $width_small * $zoom;
                                         }
                                         else
                                         {
                                                $the_position = "T";
                                                $relatedFieldName_pos = $tableListByName[$table_from]["pos_fields"][$relatedFieldName];
                                                if(!$relatedFieldName_pos)
                                                {
                                                        if(!$toPointArr[$table_to][$the_position]) $toPointArr[$table_to][$the_position] = -1;
                                                        $toPointArr[$table_to][$the_position]+=2;
                                                        $relatedFieldName_pos = $toPointArr[$table_to][$the_position];
                                                }
                                                
                                                $originFK[$relatedFieldId]["to"]["position"] = $the_position;
                                                $originFK[$relatedFieldId]["to"]["position-x"] = $originXTo + $relatedFieldName_pos * $width_small * $zoom;
                                                $originFK[$relatedFieldId]["to"]["position-y"] = $originYTo;
                                                $originFK[$relatedFieldId]["case"] = "same-y";
                                         }
                                         
                                         //if($fk_num==5) die("originFK[$relatedFieldId]= ".var_export($originFK[$relatedFieldId],true));
                                         
                                         if($fk_num <= 9) drawFK($canvas, $originFK[$relatedFieldId], $black, $green, $width_small=28, $zoom=1);
                                         $fk_num++;
                                 }
                                 
                           }
                           else die("field $relatedField has not a known table"); 
                      }
                }    
         }
   }
   
   
   function drawFK($canvas, $originFKArr, $color, $color_text, $width_small=28, $zoom=1)
   {
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $yellow = imagecolorallocate($canvas, 255, 255, 128);
        $fromX = $originFKArr["from"]["position-x"]; 
        $fromY = $originFKArr["from"]["position-y"]; 
        $toX = $originFKArr["to"]["position-x"]; 
        $toY = $originFKArr["to"]["position-y"];
        
        $font_num = 5;
        
        $font_width = imagefontwidth ($font_num);
        $font_height = imagefontheight  ($font_num);
        $name_width = $font_width * strlen($originFKArr["name"]);
        list($case,$direction) = explode("-",$originFKArr["case"]); 
        if($case=="2lines")
        {
                //imagelinethick($canvas, $fromX, $fromY, $toX, $toY, $color, $thick = 1);
                if($direction="xy")
                {
                     imageline($canvas, $fromX, $fromY, $toX, $fromY, $color);
                     drawArrow($canvas, $toX, $fromY, $toX, $toY, $color);
                     // $label = $originFKArr["from"]["position"].$originFKArr["name"].$originFKArr["to"]["position"];
                     $label = $originFKArr["name"];
                     $centerX = ($fromX + $toX) / 2; 
                     $x_text = $centerX - $name_width/2;
                     $y_text = $fromY - $font_height / 2;
                     drawFilledRectangle($canvas, $x_text-2 , $y_text-1 , $x_text + $name_width+2, $y_text + $font_height + 1, $yellow, $yellow);
                     imagestring($canvas, $font_num, $x_text, $y_text, $label, $color_text);
                }
                else
                {
                     imageline($canvas, $fromX, $fromY, $fromX, $toY, $color);
                     drawArrow($canvas, $fromX, $toY, $toX, $toY, $color);
                     // $label = $originFKArr["from"]["position"].$originFKArr["name"].$originFKArr["to"]["position"];
                     $label = $originFKArr["name"];
                     $x_text = $fromX - $name_width/2;
                     $y_text = ($fromY+$toY)/2;
                     drawFilledRectangle($canvas, $x_text-2 , $y_text-1 , $x_text + $name_width+2, $y_text + $font_height + 1, $yellow, $yellow);
                     imagestring($canvas, $font_num, $x_text, $y_text, $label, $color_text);
                }
                
        }
        elseif($case=="same")
        {
                if($direction=="x")
                {
                      $x0 = $fromX;
                      $y0 = $fromY;
                      
                      
                      
                      if($originFKArr["from"]["position"]=="R")
                      {
                              $marginX = 2;
                              $x1 = $fromX + $marginX * $width_small * $zoom;
                              $cor = 0;
                              if(abs($toX - $x1) > (7*$width_small*$zoom))
                              {
                                  if($toX > $x1) $cor++;
                                  else $cor--;
                              } 
                              elseif(abs($toX - $x1) < (6*$width_small*$zoom))
                              {
                                  if($toX > $x1) $cor--;
                                  else $cor++;
                              }
                              
                              $marginX += $cor;
                              $x1 += ($cor*$width_small*$zoom);
                              
                              $y1 = $fromY;
                              $originFKArr["from"]["position"] = "L";
                      }
                      else
                      {
                              $marginX = 2;
                              $x1 = $fromX - $marginX * $width_small * $zoom;
                              $cor = 0;
                              if(abs($toX - $x1) > (7*$width_small*$zoom))
                              {
                                  if($toX > $x1) $cor--;
                                  else $cor++;
                              } 
                              elseif(abs($toX - $x1) < (6*$width_small*$zoom))
                              {
                                  if($toX > $x1) $cor++;
                                  else $cor--;
                              }
                              
                              $marginX += $cor;
                              $x1 -= ($cor*$width_small*$zoom);
                              
                              
                              $y1 = $fromY;
                              $originFKArr["from"]["position"] = "R";
                      }
                      
                      if($originFKArr["to"]["position"]=="T")
                      {
                              $x2 = $x1;
                              $y2 = $toY - 2 * $width_small * $zoom;
                      }
                      else
                      {
                              $x2 = $x1;
                              $y2 = $toY + 2 * $width_small * $zoom;
                      }
                      $originFKArr["from"]["position-x"] = $x2;
                      $originFKArr["from"]["position-y"] = $y2;                      
                }
                else
                {
                      $x0 = $fromX;
                      $y0 = $fromY;
                      
                      if($originFKArr["from"]["position"]=="R")
                      {
                              $marginX = 2;
                              $x1 = $fromX + $marginX * $width_small * $zoom;
                              $cor = 0;
                              if(abs($toX - $x1) > (7*$width_small*$zoom))
                              {
                                  if($toX > $x1) $cor++;    // we want to decrease distance, so X1 ++
                                  else $cor--; // we want to decrease distance, so X1 --
                              } 
                              elseif(abs($toX - $x1) < (6*$width_small*$zoom))
                              {
                                  if($toX > $x1) $cor--;  // we want to increase distance, so X1 --
                                  else $cor++; // we want to increase distance, so X1 ++
                              }
                              
                              $marginX += $cor;
                              $x1 += ($cor*$width_small*$zoom);
                              
                              
                              $y1 = $fromY;
                      }
                      else
                      {
                              $marginX = 2;
                              $x1 = $fromX - $marginX * $width_small * $zoom;
                              $cor = 0;
                              if(abs($toX - $x1) > (7*$width_small*$zoom))
                              {
                                  if($toX > $x1) $cor++;  // we want to decrease distance, so X1 ++
                                  else $cor--; // we want to decrease distance, so X1 --
                              } 
                              elseif(abs($toX - $x1) < (6*$width_small*$zoom))
                              {
                                  
                                  if($toX > $x1) $cor--;  // we want to increase distance, so X1 --
                                  else $cor++; // we want to increase distance, so X1 ++
                              }
                              
                              $marginX -= $cor;
                              $x1 += ($cor*$width_small*$zoom);
                              $y1 = $fromY;
                      }
                      
                      if($originFKArr["to"]["position"]=="T")
                      {
                              $x2 = $x1;
                              $y2 = $toY - 2 * $width_small * $zoom;
                      }
                      else
                      {
                              $x2 = $x1;
                              $y2 = $toY + 2 * $width_small * $zoom;
                      }
                      $originFKArr["from"]["position-x"] = $x2;
                      $originFKArr["from"]["position-y"] = $y2;                      
                }
                imageline($canvas, $x0, $y0, $x1, $y1, $color);
                imageline($canvas, $x1, $y1, $x2, $y2, $color);
        
                $originFKArr["case"] = "2lines-xy";
                drawFK($canvas, $originFKArr, $color, $color_text, $width_small=28, $zoom=1);
        }
   }
   
   
   
   function drawTable($canvas, $tbObj, $originX, $originY, $position="center", $width_small=28, $zoom=1, $sizeX=12, $nbColsMax=7)
   {
        $t_width = $zoom * $width_small * $sizeX;
        $t_h_title = $width_small;
        $t_h_field = $width_small;
        $t_h_header = 0;
        $t_h_footer = $width_small;
        list($fields_arr, $otherFields) = $tbObj->getImportantFields($nbColsMax);
        /*
        $fields_arr = array();
        $fields_arr[] = array('name' => "id", 'title' => "المسلسل", type=>"PK");
        $fields_arr[] = array('name' => "name", 'title' => "الإسم" , type=>"TEXT");
        $fields_arr[] = array('name' => "date", 'title' => "التاريخ", type=>"DATE" );
        $fields_arr[] = array('name' => "work_id", 'title' => "الوظيفة", type=>"FK" );
        */
        $nb_fiels = count($fields_arr);
        $t_height = $zoom * ($t_h_title*2 + $t_h_field*$nb_fiels + $t_h_header + $t_h_footer);
        
        if($position=="center")
        {
                $lt_x = intval(round($originX - ($t_width/2.0)));
                $lt_y = intval(round($originY - ($t_height/2.0)));
                
                $rb_x = intval(round($originX + ($t_width/2.0)));
                $rb_y = intval(round($originY + ($t_height/2.0)));
        }
        else // if($position=="lt")
        {
                $lt_x = $originX;
                $lt_y = $originY;
                
                $rb_x = $originX + $t_width;
                $rb_y = $originY + $t_height;
        }
        
        
        $table_color  = imagecolorallocate($canvas, 0, 45, 20);
        $fields_color  = imagecolorallocate($canvas, 45, 20, 0);
        

        // Allocate colors
        $pink = imagecolorallocate($canvas, 255, 105, 180);
        $silver = imagecolorallocate($canvas, 238, 238, 238);
        $light_silver = imagecolorallocate($canvas, 248, 248, 248);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $green = imagecolorallocate($canvas, 132, 135, 28);
        
        $font_num = 5;
        
        $font_width = imagefontwidth ($font_num);
        $font_height = imagefontheight  ($font_num); 
        
        $tb_name = $tbObj->getVal("atable_name");
        list($string_max_len, $tb_name, $tb_name_padding_x, $tb_name_padding_y) = calcDataStringToFitAndCenter($tb_name, $t_width, $t_h_title, $font_width, $font_height, $margin_x=40);
        
        $titre_short_en = $tbObj->getVal("titre_short_en");
        list($string_max_len, $titre_short_en, $titre_short_en_padding_x, $titre_short_en_padding_y) = calcDataStringToFitAndCenter($titre_short_en, $t_width, $t_h_title, $font_width, $font_height, $margin_x=40);

        // Draw three rectangles each with its own color
        // imagerectangle($canvas, $lt_x, $lt_y, $rb_x, $rb_y, $black);
        drawFilledRectangle($canvas, $lt_x, $lt_y, $rb_x, $rb_y, $black, $white);

        drawFilledRectangle($canvas, $lt_x, $lt_y, $rb_x, $lt_y+$t_h_title, $black, $white);
        imagestring($canvas, $font_num, $lt_x+$tb_name_padding_x, $lt_y+$tb_name_padding_y, $tb_name, $table_color);
        
        drawFilledRectangle($canvas, $lt_x, $lt_y+$t_h_title, $rb_x, $lt_y+2*$t_h_title, $black, $white);
        imagestring($canvas, $font_num, $lt_x+$titre_short_en_padding_x, $lt_y+$t_h_title+$titre_short_en_padding_y, $titre_short_en, $table_color);
        
        $field_padding_x0 = $zoom*15;
        $field_type_padding_x0 = $t_width - $zoom * 50;
        
        $bg_field = $light_silver;
        
        if(count($otherFields)>0)
        {
            $fields_arr[] = array('name' => "...", 'title' => "...", type=>" ");
        }
        
        $showedFieldList = array();
         
        foreach($fields_arr as $field_ord => $field_item)
        {
             $field = $field_item["name"];
             $field_type = $field_item["type"];
             list($string_max_len, $field, $field_padding_x, $field_padding_y) = calcDataStringToFitAndCenter($field, $t_width, $t_h_field, $font_width, $font_height, $margin_x=40);
             
             $y_field = $lt_y+$t_h_header+2*$t_h_title+$field_ord*$t_h_field+$field_padding_y+1; // -($font_height/2)
                        
             
             if($bg_field != $light_silver) $bg_field = $light_silver;
             else $bg_field = $white;
             
             if($bg_field == $light_silver)
             {
                 drawFilledRectangle($canvas, $lt_x+1, $y_field - 5, $rb_x-1, $y_field + 5 + $font_height, $silver, $bg_field);
             }
             
             imagestring($canvas, $font_num, $lt_x+$field_padding_x0, $y_field, $field, $field_color);
             
             imagestring($canvas, $font_num, $lt_x+$field_type_padding_x0, $y_field, $field_type, $field_color);
             
             $showedFieldList[$field] = array(position_y => $y_field, item => $field_item);
        }
        
        
        return array($showedFieldList, $otherFields);
        // imagerectangle($canvas, 1000, 1200, 275, 360, $green);
        
        
   }
   
?>