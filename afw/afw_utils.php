<?php
        
        function hzm_object_command_line($type, $odd_oven, $objId, $objTitle, $dataErrors, $lang, $error_class="error")
        {
              $id_html = hzm_format_command_line("info", $objId, $lang);
              $title_html = hzm_format_command_line("success", $objTitle, $lang);
              $errors_html = hzm_format_command_line($error_class, $dataErrors, $lang);
              
              return "<div class=\"cline-obj obj-$lang bg-$odd_oven\">
                               <div class=\"cline-obj-id obj-id-$lang\">$id_html</div>
                               <div class=\"cline-obj-title obj-title-$lang\">$title_html</div>
                               <div class=\"cline-obj-errors obj-errors-$lang\">$errors_html</div>
                      </div>";
        }
        
        function hzm_attribute_command_line($type, $odd_oven, $name_att, $val_att, $lang, $name_class="success")
        {
              $val_html = hzm_format_command_line($type, $val_att, $lang);
              $name_html = hzm_format_command_line($name_class, $name_att, $lang);
              return "<div class=\"cline-att att-$lang bg-$odd_oven\"><div class=\"cline-att-name att-name-$lang\">$name_html</div><div class=\"cline-att-val att-val-$lang\">$val_html</div></div>";
        }
        
        function hzm_format_command_line($type, $string, $lang="en")
        {
            $string = str_replace("  ","&nbsp;&nbsp;", $string);
            return "<span class=\"cline-$lang cline-message cline-$type\">$string</span>";
        }
        
        function decodeHzmTemplate($tpl_content, $data_tokens)
        {
                global $lang;
                
                $token_arr = $data_tokens;
                
                $token_arr["[lang]"] = $lang;
                
                $text_to_decode = $tpl_content;
                
                foreach($token_arr as $token => $val_token)
		{
                     $text_to_decode = str_replace("[".$token."]",$val_token,$text_to_decode);
                }
                
                return $text_to_decode;
                
        } 
        
        
        function showUsingHzmTemplate($html_template_file, $data_tokens)
        {
               ob_start();
               if(file_exists($html_template_file))
               {
                       include($html_template_file);
                       $tpl_content = ob_get_clean();
            
                       return decodeHzmTemplate($tpl_content, $data_tokens);
               }
               else
               {
                       return "showUsingHzmTemplate : file $html_template_file not found";
               }        
        }
        
        
        function parse_table_and_module($object_module_class)
        {
                  $object_module_class = strtolower($object_module_class);
                  
                  if($object_module_class == "module")
                  {
                     $object_class_file = "module";
                     $object_module = "ums";
                  }
                  elseif($object_module_class == "table")
                  {
                     $object_class_file = "atable";
                     $object_module = "pag";
                  }
                  elseif($object_module_class == "field")
                  {
                     $object_class_file = "afield";
                     $object_module = "pag";
                  }
                  else
                  {
                     list($object_class_file, $object_module) = explode(".",$object_module_class);
                  }
                  
                  return array($object_class_file, $object_module);
        }
        
          
?>