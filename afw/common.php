<?php

# name common.php



// old require of common_string

$arr_colTech = array("id_aut","date_aut","id_mod", "date_mod",
                        "avail","niv_min_ins","niv_min_mod","niv_min_del",
                        "droit_min_ins","droit_min_mod","droit_min_del","id_valid","date_valid");

if (!function_exists('debugg'))  
{
        function debugg () 
        {
           global $set_debug;
           if($set_debug) 
           {
                for ($i=0; $i< func_num_args(); $i++) 
        	{
        	    print func_get_arg($i)." <br>\n";
        	}
           } 	
        }
}
/*
function insert_analyse($tech_infos, $duree_q)
{
   global   
     die()

}*/


function debugg_row($row)
{
  foreach($row as $col => $val) 
  {
  			echo "row[$col] = $val <br>";
  }
}

function t($string)
{
        return AFWRoot::tt($string);
}

function debugg_tab($tab,$nn)
{
      foreach($tab as $col => $val) 
      {
        $disp = "";
        if(is_Array($val))
        {
                $disp .= print_php_array($val,"",3,false,"");
        }
        else $disp = $val;
        debugg("$nn [ $col ] =$disp");
      }  
}


function exec_query($query_txt,$titre="",$sendAlerte=false)
{
   global $db_1,$logObj,$set_debug, $set_debugg,$je_suis_admin,$analyse_sql, $die_on_error;
   
   $aff_debugg = ($je_suis_admin or $set_debug or $set_debugg);     
   
   // $d b_1->Halt_On_Error="no";
   
   $start_q_time = date("Y-m-d H:i:s");
   if($aff_debugg) debugg("Start query : $start_q_time");  
   
   $res = $db_1->query($query_txt);
   if($aff_debugg) debugg("Query : $query_txt");  
   $end_q_time = date("Y-m-d H:i:s");
   $duree_q = strtotime($end_q_time) - strtotime($start_q_time);
   $affected_rows = $db_1->affected_rows();
   if($aff_debugg) debugg("End query : $end_q_time, duree : $duree_q, affected : $affected_rows row(s) \n");  
   
   
   if(!$res)
   {
        $alerte = "Error when running the query : \n $query_txt\nError : ".$db_1->error."\n";
        die($alerte);
   }
   
   if(($analyse_sql=='W') or ($analyse_sql=='Y') or ($duree_q>10))
   {
           $text_time = " ($end_q_time - $start_q_time)";
//           insert_analyse($query_txt.$text_time,$duree_q);
   }

   return $res;
}




function format_email($eml) 
{

        $eml = trim($eml);
        $first=substr($eml,0,1);
        if($first=='*')
        {
                $eml='';
        }
        //$eml = str_replace("*","",$eml);
        return trim($eml);
}

function format_tel($tel) 
{
	if (strlen($tel) == 10) 
        {
		if (substr($tel, 0, 2) == "08") {
			$tel= substr($tel, 0,1) . " " .  substr($tel, 1,3) . " " .  substr($tel, 4,3) . " " .  substr($tel, 7,3) ;
		} else {
			$tel= substr($tel, 0,2) . " " .  substr($tel, 2,2) . " " .  substr($tel, 4,2) . " " .  substr($tel, 6,2) . " " .  substr($tel, 8,2) ;
		}
	}
	return $tel;
}

function aff_time($tds,$seconds=true,$sep_hm=":",$sep_ms=":",$sep_s="",$enleve_heure_si_zero=false)
{
    debugg("tds $tds");
    $ssec = floor($tds / 10);
    debugg("ssec $ssec");
    $smin = floor($ssec / 60); $ssec = $ssec - 60*$smin;
    debugg("smin $smin");
    $shrs = floor($smin / 60); $smin = $smin - 60*$shrs;
    debugg("shrs $shrs");
    

    if($ssec<10) $ssec = "0".$ssec;
    if($smin<10) $smin = "0".$smin;
    
    if($enleve_heure_si_zero and ($shrs==0)) $shrs = "";
    else $shrs = $shrs.$sep_hm;

    if($seconds) $result = $shrs.$smin.$sep_ms.$ssec.$sep_s;
    else $result = $shrs.$smin.$sep_ms;

    return $result; 
}

function aff_naw3($naw3)
{
        if($naw3==0) return "matn"; // كلام المصنف
        if($naw3==1) return "char7"; // كلام الشارح
        if($naw3==2) return "talik"; // مشاركة أحد طلبة المعهد أو أحد المدرسين فيه
        if($naw3==3) return "talik"; // كلام أحد تلاميذ الشارح في الشريط
        if($naw3==4) return "talik"; // ملاحظة من المفرغ
        
        return $naw3;

}

function aff_naw3_ar($naw3)
{
        if($naw3==0) return "المتن"; // كلام المصنف
        if($naw3==1) return "الشرح"; // كلام الشارح
        if($naw3==2) return "طالب"; // مشاركة أحد طلبة المعهد أو أحد المدرسين فيه
        if($naw3==3) return "تلميذ"; // كلام أحد تلاميذ الشارح في الشريط
        if($naw3==4) return "المفرغ"; // ملاحظة من المفرغ
        
        return $naw3;

}


function aff_typ($typ)
{
        if($typ==1) return "talik"; // تخريج حديث أو ذكر الآية و السورة من القرآن
        if($typ==2) return "soal"; // طرح سؤال
        if($typ==3) return "talik"; // كلام و توجيهات من المعهد 
        if($typ==4) return "fayda"; // فائدة جديدة
        if($typ==5) return "talik"; // تعليق
        if($typ==6) return "jawab"; // جواب عن سؤال

        return $typ;

}


function aff_typ_ar($typ)
{
        if($typ==1) return "تعليق"; // تخريج حديث أو ذكر الآية و السورة من القرآن
        if($typ==2) return "سؤال"; // طرح سؤال
        if($typ==3) return "توجيهات"; // كلام و توجيهات من المعهد 
        if($typ==4) return "فائدة"; // فائدة جديدة
        if($typ==5) return "تعليق"; // تعليق
        if($typ==6) return "إجابة عن سؤال"; // جواب عن سؤال

        return $typ;

}
/*
function recupMysqlInsertedId()
{
      global $db_1;
      
      return  $db_1->mysqlInsertedId(); 
}*/

function recup_value($quer_txt,$col="",$titre="",$cache_delay=-1)
{
        global $logObj,$set_debug, $set_debugg;
        $row = recup_row($quer_txt,$titre,$cache_delay);
	
	if((!$col) and ($row) and (count($row)>0)) foreach($row as $colr => $val) $col = $colr;
        
        
        if($row!=0)
           $val = $row[$col];
        else 
           $val = "";
        
        if(($set_debug) or ($set_debugg)) debugg("value of $col = $val\n");
           
        return $val;   
}

function recup_row($quer_txt,$titre="",$cache_delay=-1)
{
        global $logObj,$set_debugg, $set_debug;
        $tab = recup_data($quer_txt,$titre,true,$cache_delay);
        if (count($tab)>0)
        {
            if(($set_debug) or ($set_debugg)) foreach($tab[0] as $col0 => $val0) debugg("value of $col0 = $val0\n");
            return $tab[0];
        }
        else
          return 0; 
}

function get_onerow($table,$id,$titre="",$cache_delay=-1)
{
        global $logObj;
        $query_q ="select * from $table where ${table}_ID = '$id'";
        return recup_row($query_q,$titre,$cache_delay);
}

function data_to_liste($tab,$col)
{
        $liste = array();
        
        if($tab)
        {
                  foreach($tab as $row)
                  {
                      $liste[] = $row[$col];
                  }
        }
        return $liste;
}

function recup_liste($quer_txt,$col,$titre="",$cache_delay=-1)
{
        global $logObj;

        $tab = recup_data($quer_txt,$titre,true,$cache_delay);
        $liste = data_to_liste($tab,$col);
        
        return $liste;
}


function recup_liste_txt($quer_txt,$col,$sep=",",$titre="",$cache_delay=-1)
{
        global $logObj;
        $liste = recup_liste($quer_txt,$col,$titre,$cache_delay);
        return implode($sep,$liste);
} 

// $delay_from_cache : nouvelle fonctionnalité qui permet de ne pas trop solliciter une table
// avec les memes requetes si on tolère que les données ne soient pas a jour a 100%
// par exemple si on tolere que les données soient juste a 24 heures pres a jour
// on met $delay_from_cache=24 cela ramène des données depuis le cache    

function recup_data($query,$titre="",$break_if_error=true,$delay_from_cache=-1, $log_sql=true)
{
        
        $data = AfwDatabase::db_recup_rows($query, true, true);
        return $data;
        
        
}


function recup_table($table,$key,$collist,$cond,$orderby)
{
        // @MIG-MYSQL : THIS IS OBSOLETE
        throw new RuntimeException("case 001 : migrating to new mysql functions");
        /*
        global $db_1,$analyse_sql;
        
        $requete = "select $collist from $table where $cond order by $orderby";
        
        
        $start_q_time = date("Y-m-d H:i:s");
        if(!$res=$db_1->query($requete)) echo $requete." a échouée";  
        $end_q_time = date("Y-m-d H:i:s");
        $duree_q = strtotime($end_q_time) - strtotime($start_q_time);
        
        $nb=$db_1->num_fields();

        while($db_1->next_record())
        {   
        for($i=0;$i<$nb;$i++)
        {
        if(!$key) $key = "${table}_ID";
        $id = $db_1->f($key);
        $nom_champ=mysql_field_name($res,$i);
        $tableau[$id][$nom_champ]=stripslashes($db_1->f($nom_champ));
        }
        }
        
        if(($analyse_sql=='W') or ($analyse_sql=='Y') or ($duree_q>10))
        {
                $text_time = " ($end_q_time - $start_q_time)";
                // insert_analyse($requete.$text_time,$duree_q);
        }
        
        return $tableau;
        */
}

function recup_index($requete,$keyCol,$valueCol,$titre="",$cache_delay=-1)
{
        global $logObj;
        $data = recup_data($requete,$titre,$cache_delay);
        return get_tabvalue_byid($data,$keyCol,$valueCol);
}

function get_tabvalue_byid($data,$keyCol,$valueCol)
{
        foreach($data as $ir => $row)
        {
                $key = $row[$keyCol];
                $value = $row[$valueCol];
                $new_data[$key] = $value;
        }

        return $new_data;
}

function get_tableau_byid($data,$keyCol)
{
  foreach($data as $ir => $row)
  {
        $key = $row[$keyCol];
        $new_data[$key] = $row;
  }

  return $new_data;
}


function recup_answerTable($table,$keyCol="",$valCol="",$where="1",$titre="",$cache_delay=-1)
{
        global $logObj;
        if(!$keyCol) $keyCol = "${table}_ID";
        if(!$valCol) $valCol = "${table}_NAME";

        return recup_answer_table("select $keyCol as my_pk, $valCol as my_val from $table where $where order by $valCol",$titre,$cache_delay);
}


function supprimer_doublons($str,$sep,$uppercase=false)
{
        if($str)
        {
                $tab = explode($sep,$str);
                $tab_res = array();
                foreach($tab as $tt)
                {
                   if($uppercase) $tt = strtoupper($tt);
                   
                   if(array_search($tt, $tab_res)===FALSE)
                   {
                        if ($tt) $tab_res[] = $tt;
                   }
                }
                
                return implode($sep,$tab_res);
        }
        else return $str;
}


function recup_answer_table($requete,$titre="",$cache_delay=-1)
{
        global $logObj;
        $tab = recup_data($requete,$titre,true,$cache_delay);
        $answer = array();
        foreach($tab as $row)
        {
            $answer[$row["my_pk"]] = $row["my_val"];    
        }
        
        return $answer;
}

function get_answer_labels_list($str1,$tab_answer,$newc = ", ",$c=",")
{

        $answer_labels = "";
        $notfound_labels = "";
        
        if (strlen($str1)>2)
        {
                $str = trim(substr($str1,1,strlen($str1)-2));
                $values = explode($c,$str);
                
                if ($str)
                {
                        foreach ($values as $val) 
                        {
                            if($val)
                            {    
                               if ($tab_answer[$val])
                               {
                                       if ($answer_labels) $answer_labels .=  $newc;
                                       $answer_labels .= $tab_answer[$val];                
                               }
                               else $notfound_labels .= " " . $val;
                            }   
                        }  
                }
                
                if ($notfound_labels) $notfound_labels .= "($str1)";
        }
        
        $res = array();
        
        $res[] = $notfound_labels;
        $res[] = $answer_labels;
                
        return $res; 
}


function get_answer_labels_list2($str1,$tab_answer,$newc = ", ",$c=",")
{

        $answer_labels = "";
        $notfound_labels = "";
        if (strlen($str1)>2)
        {
                $str = trim(substr($str1,1,strlen($str1)-2));
                $values = explode($c,$str);
                
                if ($str)
                {
                        foreach ($values as $val) 
                        {
                            if($val)
                            {    
                               if ($tab_answer[$val])
                               {
                                       if ($answer_labels) $answer_labels .=  $newc;
                                       $answer_labels .= $tab_answer[$val];                
                               }
                               else $notfound_labels .= " " . $val;
                            }   
                        }  
                }
                
                if ($notfound_labels) $notfound_labels .= "($str1)";
        }
        
                
        return $answer_labels; 
}

function tab_to_html($tab_data,$nb_max=0)
{
        $ccoouunntt = count($tab_data);
        return "$ccoouunntt enregistrement(s)<br>".display_dataGrid("","",$tab_data);
}

function remove_html_from($html,$b="",$_b="",$li="     ",$_li="\n",$eur=" eur")
{
      $desc = $html;
      $desc = str_replace("<b>",$b,$desc);
      $desc = str_replace("</b>",$_b,$desc);
      $desc = str_replace("<br>","\n",$desc);
      $desc = str_replace("<li>",$li,$desc);
      $desc = str_replace("</li>",$_li,$desc);
      $desc = str_replace("<ul>","\n",$desc);
      $desc = str_replace("</ul>","\n",$desc);
      $desc = str_replace("<strong>",$b,$desc);
      $desc = str_replace("</strong>",$_b,$desc);
      $desc = str_replace("<center>",$b,$desc);
      $desc = str_replace("</center>",$_b,$desc);
      $desc = str_replace("&euro;",$eur,$desc); 
 
      return $desc;
}

function remove_retour_a_la_ligne_from($html)
{
      $desc = $html;
      $desc = str_replace("\n","",$desc); 
 
      return $desc;

}


function no_cote($html_in_js)
{
      $desc = $html_in_js;
      $desc = str_replace("'"," ",$desc);
      $desc = str_replace('"'," ",$desc);
      
      return $desc;

}

function to_html($html)
{
    global $table_name, $img_field_names, $id;
      $desc = $html;
      $desc = str_replace("[[غ]]","<b>",$desc);
      $desc = str_replace("[[/غ]]","</b>",$desc);
      $desc = str_replace("\n","<br>",$desc);
      $desc = str_replace("\r","",$desc);
      $desc = str_replace("[[ق]]","<ul>",$desc);
      $desc = str_replace("[[/ق]]","</ul>",$desc);
      $desc = str_replace("[[/ع]]","<li>",$desc);
      $desc = str_replace("[[/ع]]","</li>",$desc);
      $desc = str_replace("[[و]]","<center>",$desc);
      $desc = str_replace("[[/و]]","</center>",$desc);
      
      
      
      //$desc = str_replace($eur,"&euro;",$desc);
      if(isset($img_field_names))
      {
              foreach($img_field_names as $field_name)
              {
                  $token = "<$field_name>";
                  $desc = str_replace($token,"<br><img src='pic/${table_name}_${field_name}_${id}.png' />",$desc);    
              }
      }
      return $desc;
}          

function traduire($col, $table_name)
{
   global $trad;
   //echo "trad $col, $table_name : <br>";
   $result = $trad[$table_name][$col];
   //echo "trad res $result <br>";
   if(!$result) $result = $trad["*"][$col];
   //echo "trad res $result <br>";
   if(!$result) $result = $col;
   //echo "trad res $result <br>";
   
   return $result;
}


function istodecode($col, $table_name)
{
    global $trad;
    global ${"anstab_$col"};
    
    return ((is_array($trad["${table_name}_${col}_val"])) or (count(${"anstab_$col"})>0)); 
}

function decode($col, $table_name, $tab_cell)
{
   global $trad;
   
   $decd = $trad["${table_name}_${col}_val"][$tab_cell];
   if(!$decd)
   {
        global ${"anstab_$col"};
        //echo "anstab_$col : ".print_r(${"anstab_$col"})."<br>";
        if(count(${"anstab_$col"})>0)
        {
              $decd = ${"anstab_$col"}[$tab_cell];  
        } 
   }
   if(!$decd) $decd = $tab_cell;
   return $decd;
}

function detokeniser($bloc,$row)
{
       global $set_debug;
       foreach($row as $col => $value)
       {
                $chn = ",".$col.",";
                $bloc=str_replace($chn, $value, $bloc);
                if($set_debug) debugg("detokeniser $col with $value");
       }
       
       return $bloc;
}

                        
function display_dataGrid($bgtitre,$tab_header,$tab_data,$table_name="", $col_link="", $url_link="", $arr_colHidden=array(), $col_tec=true, $class_th = "astth" ,$class_1 = "asttd1",$class_2 = "asttd2", $bgcolor="", $tab_config='',$nb_max=0,$class_b="tit_makal",$colonne_vide=false,$no_class=false,$class_h="tit_head")
{
  global $back_color, $arr_colTech;
  
  if(!$bgcolor) $bgcolor = $back_color;
       
  
  
    if(!$col_tec)
    {
      foreach($tab_data as $key => $tab_col)
      {
         foreach($tab_col as $cur_col=>$value)
         { 
          if(in_array($cur_col,$arr_colTech))
          {
            unset($tab_data[$key][$cur_col]);
          }
         }
      }
    }
    
  
  
  
  

    
    $nb_column = 0;
    if((!$tab_header) && (count($tab_data)>0))
    {
        foreach($tab_data[0] as $col => $val)
        {
            if((!$arr_colHidden) or (!in_array($col,$arr_colHidden)))
            {
               if($table_name)
               {
                  $tab_header[$nb_column] = traduire($col, $table_name);
               }
               else
               {
                  $tab_header[$nb_column] = $col;
               }
               $nb_column++; 
            }   
        }  
    }
    else $nb_column = count($tab_header);        
    
    if(!$nb_column) $nb_column=1;
    
    $html = "";
    $html .= "<table  border='0' cellpadding='0' cellspacing='2'  bgcolor='$bgcolor' ".style_or_class($class_b,$no_class).">\n";
    if(($bgtitre) && (count($tab_data)>0))
    {
            $html .= sprintf("<tr>\n");
            if($colonne_vide) $html .= sprintf("<td width=\"50\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n");
            $html .= "  <td colspan=${nb_column} align='center' ".style_or_class($class_h,$no_class).">${bgtitre}</td></tr>\n";
    }



    if($tab_header)
    {
            # Afficher les titre des colonnes
            $html .= "  <tr bgcolor='darkgray' color='white'>\n";
            if($colonne_vide) $html .= sprintf("  <th width=\"50\" ".style_or_class($class_th,$no_class)." >&nbsp;</th>\n");
            foreach($tab_header as  $title)
            {
               $html .= "    <th align=\"center\"  ".style_or_class($class_th,$no_class)." >&nbsp;$title&nbsp;</th>\n";
            }
            
            $html .= "  </tr>\n";
    
    }

        

    $clss = $class_1;
    
    
    $i= 0;
    foreach($tab_data as $tab_row)
    {        
    
            if((!$nb_max) || ($i < $nb_max))
            {
                $html .= "  <tr>\n";
                if($colonne_vide) $html .= sprintf("  <td width=\"50\">&nbsp;</td>");
                if(isset($tab_config['class']))
                        $clss = $tab_config['class'][$i];
                else              
                        $clss = ($clss == $class_1) ? $class_2 : $class_1 ;
                foreach($tab_row as $col => $tab_cell) 
                {
                        if((!$arr_colHidden) or (!in_array($col,$arr_colHidden)))
                        {
                                        if(AfwStringHelper::stringStartsWith($col,"date_")) $tab_cell = mysqldate_to_tn_date($tab_cell);
                                        elseif(AfwStringHelper::stringStartsWith($col,"heure_")) $tab_cell = mysqldate_to_tn_date($tab_cell)." ".mysqldate_to_tn_hour($tab_cell);
                                        elseif($table_name and istodecode($col, $table_name))
                                        {
                                            $tab_cell = decode($col, $table_name, $tab_cell);
                                        }
                                        
                                        if($col_link and ($col==$col_link))
                                        {
                                           $url_row = detokeniser($url_link,$tab_row);
                                           $tab_cell = "<a href='$url_row'>$tab_cell</a>"; 
                                        }
                                        
                
                                        $html .= "    <td align='center'  ".style_or_class($clss,$no_class)." >&nbsp;".$tab_cell."&nbsp;</td>\n";
                        } 
                }
                                
                $html .= "  </tr>\n";   
            }
            else
             break;
               
        $i++;
   }
             
   
  $html .= "</table>\n\n"; 
  return $html;
}


function style_or_class($clss,$no_class)
{
   if($no_class)
   {
      	
   
      if($clss=="masblocs") return " style='margin: 1em 4px 1.5em;padding: 0px 0px;width: 96%;    border: 0 none;    font-size: 26px;    outline: 0 none;    vertical-align: baseline;    color: #EEEEEE;' ";
      if(($clss=="parsoal") or ($clss=="masbloc parsoal")) return " style='padding: 3px 6px 3px 6px;  border: 0 none; font-weight: normal; background: rgb(121,85,11); color: #ffecdd; border: 1px solid rgb(193,186,17);' ";
      if(($clss=="parsoal2") or ($clss=="masbloc parsoal2")) return " style='padding: 3px 6px 3px 6px;  border: 0 none; font-weight: normal; background: rgb(140,65,30);   color: #ffecdd;  border: 1px solid rgb(193,186,17);' ";
      if(($clss=="partalik") or ($clss=="masbloc partalik")) return " style='padding: 3px 6px 3px 6px;  border: 0 none; font-weight: normal; border: 1px solid #88f799;  color: #ffffff;  background: rgb(18,95,41);' ";
      if(($clss=="partalik2") or ($clss=="masbloc partalik2")) return " style='padding: 3px 6px 3px 6px;  border: 0 none; font-weight: normal; border: 1px solid #44f733;  color: #ffffff;  background: rgb(30,80,51);' ";
      if(($clss=="parfayda") or ($clss=="masbloc parfayda")) return " style='padding: 3px 6px 3px 6px;  border: 0 none; font-weight: normal; border: 1px solid #88aaf7;  color: #ffffff;  background: rgb(49,61,85);' ";
      if(($clss=="parfayda2") or ($clss=="masbloc parfayda2")) return " style='padding: 3px 6px 3px 6px;  border: 0 none; font-weight: normal; border: 1px solid #6688f7;  color: #ffffff;  background: rgb(64,80,65);' ";
      if(($clss=="parjawab") or ($clss=="masbloc parjawab")) return " style='padding: 3px 6px 3px 6px;  border: 0 none; font-weight: normal; border: 1px solid #88f7aa;  color: #ffffff;  background: rgb(82,94,60);' ";
      if(($clss=="parjawab2") or ($clss=="masbloc parjawab2")) return " style='padding: 3px 6px 3px 6px;  border: 0 none; font-weight: normal; border: 1px solid #449722;  color: #ffffff;  background: rgb(82,94,60);' ";
      if($clss=="mastitmatn") return " style='margin: 1em 4px 1.5em;padding: 0px 0px;width: 96%;    border: 0 none;    font-size: 26px;    outline: 0 none;    vertical-align: baseline; color: #ffffff;background: rgb(145,57,0);' ";

      if($clss=="petitecrit") return " style='font-size: 20px;' ";
      /*
      
      if($clss=="astth") return " style='' ";
      if($clss=="astth") return " style='' ";
      if($clss=="astth") return " style='' ";
      if($clss=="astth") return " style='' ";
      if($clss=="astth") return " style='' ";
      if($clss=="astth") return " style='' ";*/

      
      if($clss=="mastranspbouton") return " style='border: 1px solid #445500;  padding-left: .2em;  padding-right: .2em;  font-weight: normal;  color: #445500;' ";
      if($clss=="astth") return " style='font-family: Arabic Typesetting, Traditional Arabic, tahoma;background:#4A2917;font-size:32px;color:#ffffff;' ";
      if($clss=="asttd1") return " style='font-family: Arabic Typesetting, Traditional Arabic, tahoma;background:#864A2B;font-size:28px;color:#ffffff;' ";
      if($clss=="asttd2") return " style='font-family: Arabic Typesetting, Traditional Arabic, tahoma;background:#56300A;font-size:28px;color:#ffffff;' ";
      if($clss=="tit_info") return " style='font-family: Arabic Typesetting, Traditional Arabic, tahoma;background:rgb(38,46,140);font-size:30px;color:#ffffff;font-weight: bold;' ";
      if($clss=="tit_makal") return " style='border:1px solid rgb(12,18,20);background-color:rgb(215,209,174);color:rgb(40,50,102);font-family: GE SS, Arabic Typesetting, Traditional Arabic, tahoma;font-weight: bold;font-size:24px;text-align:right;padding:3px;' ";
      //if($clss=="astth") return " style='font-family: Arabic Typesetting, Traditional Arabic, tahoma;' ";
      return " style='font-family: Arabic Typesetting, Traditional Arabic, tahoma;font-size:28px;color: #ffffff;  background: rgb(18,95,41);' ";
         
   }
   else
   {
      return " class='$clss' ";
   
   }

}

function show_row($titre, $row,$table,$col_tech=false)
{
    global $arr_colTech;
        foreach($row as $col => $val)
        {
                $col_ok = (($col_tech) or (!in_array($col,$arr_colTech)));
                $col_tr = traduire($col, $table);
                if($col_ok and $col_tr and ($col_tr!=$col)) $tab_header[$col] = $col_tr;
                global ${"anstab_$col"};
                if(count(${"anstab_$col"})>0)
                {
                        $answer_table[$col] = ${"anstab_$col"};
                } 
        }

        return "<div class='tit_makal' style='font-size:22px;'>$titre</div>\n".display_oneRow($tab_header,$row,$answer_table);
}

function display_oneRow($tab_header,$row,$answer_table)
{
    
    $html = ""; //.print_r($answer_table);
    $html .= "<table  border='0' cellpadding='2' cellspacing='2'  class='tit_makal' style='font-size:18px;'>\n";

    if((!$tab_header) && (count($row)>0))
    {
        foreach($row as $col => $val) $tab_header[$col] = $col; 
    }

    if($tab_header)
    {
            # Afficher les titre des colonnes
            $d2 = "";
            foreach($tab_header as $col => $title)
            {
                $tab_cell = $row[$col];
                if(count($answer_table[$col])>0)
                {
                    $old_tab_cell = $tab_cell;    
                    $tab_cell = $answer_table[$col][$tab_cell];
                    //echo "_____answer_table : $col : $old_tab_cell : $tab_cell <br>";    
                }
                elseif(AfwStringHelper::stringStartsWith($col,"date_")) 
                {
                     if(($tab_cell) and ($tab_cell!= "0000-00-00 00:00:00"))
                        $tab_cell = mysqldate_to_tn_date($tab_cell);
                     else   
                        $tab_cell = "";
                }
                elseif(AfwStringHelper::stringStartsWith($col,"heure_")) 
                {
                     if(($tab_cell) and ($tab_cell!= "0000-00-00 00:00:00"))   
                        $tab_cell = mysqldate_to_tn_date($tab_cell)." ".mysqldate_to_tn_hour($tab_cell);
                     else   
                        $tab_cell = "";
                }
                
                $html .= "  <tr>\n";
                $html .= "    <td class='login$d2' >$title</td>\n";
                $html .= "    <td align='center' class='login$d2'>&nbsp;$tab_cell&nbsp;</td>\n";
                $html .= "  </tr>\n";
                if($d2) $d2 = ""; else $d2 = "2";                
            }
    }
   
    $html .= "</table>\n\n"; 
    
    return $html;
}

  
function add_to_date($date_dep, $offset)
{
        $arr_date=explode('-',$date_dep);
        $date_new = date('Y-m-d',mktime(0,0,0,$arr_date[1],$arr_date[2]+$offset,$arr_date[0]));
        
        return $date_new;
}

function get_next_id_of($table_name,$key_name='',$condition='1=1',$increment=1,$titre="")
{     
      global $set_debug,$logObj;
        
      if ($key_name == '') $key_name = $table_name . "_ID";
      
      $requete = "select max($key_name)+$increment as id from $table_name where $condition";
      $tab_req = recup_data($requete);
      if (!$tab_req[0]['id'])  return 1;
      else return $tab_req[0]['id'];
}


function diffTime($time1,$time2)
{

        $arr_entree = explode(':',$time1);
        $arr_sortie = explode(':',$time2);

        $date_entree = mktime($arr_entree[0],$arr_entree[1],$arr_entree[2],1,1,2000);
        $date_sortie = mktime($arr_sortie[0],$arr_sortie[1],$arr_sortie[2],1,1,2000);
        
        return $date_sortie - $date_entree; 

}



/*
function isCorrectURLSyntax($url) 
{
        $motif_charOfFile = "[a-zA-Z0-9._-]"; 
        $motif_url=("^^http://([a-zA-Z0-9-]+\.)+([a-zA-Z0-9-]{2,4})".// site 
        "(:[0-9]{0,4}[1-9])?".// Port 
        "(/$motif_charOfFile*)*$"); 

	if (!ereg($motif_url, $url)) 
        {
	    return false;
	}
	else
            return true;
}*/




// ex add_time('2005-08-25 10:05:05', 902) => '2005-08-25 10:20:07'
//    add_time('2005-08-25 10:05:05', -902) => '2005-08-25 09:50:03'
function add_time($date, $time)
{
        $arr_dat=explode(' ',$date);
        $arr_dat1=explode('-',$arr_dat[0]);
        $arr_dat2=explode(':',$arr_dat[1]);
        $date1 = mktime($arr_dat2[0],$arr_dat2[1],$arr_dat2[2],$arr_dat1[1],$arr_dat1[2],$arr_dat1[0]);
        $date1=$date1 + $time;
        $date= date('Y-m-d H:i:s', $date1 ); 
        return $date;
}

function repeat_string($str,$nb)
{
   $res = "";
   for ($i=0;$i<$nb;$i++) 
   {
        $res .= $str;
   }
   
   return $res;
}

 


function print_php_array($tab,$tabname,$indent=3,$putvar=true,$pv=";")
{
    
    $sp = repeat_string(" ",$indent);
    $result = "";
    if($putvar) $result .= "\$$tabname = ";
    $result .= "array(\n";
    
    
    
    foreach($tab as $ind => $elem) 
    {
        $result .= $sp."  \"$ind\" => ";
        if(is_Array($elem))
        {
                $result .= print_php_array($elem,"",$indent+7,false,"");
        }
        else
        {
                $result .= "\"".addcslashes($elem,"\"")."\"";       
        }
        $result .= ",\n";
    }
    $result .= $sp.")".$pv;
    

    return $result;
}



function dd($message, $var)
{
    die($message." => ".var_export($var,true));
}          

function common_die($message, $light=true)
{
        $message = $message."<br>"._back_trace($light);
        
        die($message);
}

function _back_trace($light=false)
{
        global $lang;
        // $light=true; // otherwise sometime loop infite
        if($light) $max_trace = 20;
        else $max_trace = 50;
        $backtrace = debug_backtrace(1,$max_trace);
        $html = "<table dir='ltr' style='width:100%' class='display dataTable display_ltr back_trace'>
                        <tr>
                                <th><b>Function </b>
                                </th><th><b>File </b></th>
                                <th><b>Line </b></th>
                        </tr>
                        ";
        $odd_even = "odd";
        $i = 1;
        foreach($backtrace as $entry) 
        {
                $i++;
                $html .= "<tr class='$odd_even'>";
		$html .= "<td  style='border-top:1px solid #000;'>" . $entry['function']."</td>"; 
                $html .= "<td  style='border-top:1px solid #000;'>" . $entry['file']."</td>"; 
                $html .= "<td  style='border-top:1px solid #000;'>" . $entry['line']."</td>";
                $html .= "</tr>
                ";
                if(($entry['function'] != "safeDie") and ($entry['function'] != "_back_trace"))
                {
                        if(($entry['object']) or (count($entry['args'])>0))
                        {

                                $html .= "<tr class='backtrace_tech_details $odd_even'>";
                                $html .= "<td>" . $entry['object']->id."</td>"; 
                                if($entry['object']) 
                                {
                                        if(class_exists("AFWObject") and ($entry['object'] instanceof AFWObject) and (!$light))
                                        {
                                                $shdisp = $entry['object']->getShortDisplay($lang);
                                        } 
                                        else $shdisp = get_class($entry['object'])."-> display object";
                                }
                                else
                                {
                                        $shdisp = "no-object";
                                }
                                
                                if(!$light) 
                                {
                                        $html .= "<td colspan='2'>" . $shdisp ."</td>"; 
                                }
                                else 
                                {
                                        $html .= "<td colspan='2'>light-mode : $shdisp </td>"; // rafik may be create this->getLightDisplay ??
                                }

                                $html .= "</tr>\n";
                                $html .= "<tr class='backtrace_tech_details $odd_even'>";
                                $html .= "<td colspan='3'  style='border-bottom:1px solid #000;'>";
                                if(class_exists("AfwHtmlHelper")) 
                                {
                                        $html .= AfwHtmlHelper::genereAccordion("<pre>".var_export($entry['args'],true)."</pre>", "Arguments", "Arguments$i");
                                }                                
                                $html .= "</td>"; 
                                $html .= "</tr>\n";
                        }
                        
                }
                if($odd_even == "odd") $odd_even = "even"; else $odd_even = "odd";
	}
        $html .= "</table>\n";
        
        return $html;

}

function important_trace($files_to_skip_contient_external=[])
{
        $files_to_skip_contient = $files_to_skip_contient_external;
        $files_to_skip_contient["main.php"]=true;
        $files_to_skip_contient["afw"]=true;
        $files_to_skip_contient["hzm"]=true;
        $files_to_skip_contient["cache"]=true;
        
        $functions_to_skip = ["__construct"=>true, "loadByMainIndex"=>true, "getSpecificDataErrors" =>true, "getFreeRooms" => true, "getRoomsPrices"=>true, ""=>true];
        $backtrace = debug_backtrace();
        //
        $important_trace_line = "nothing";
	foreach($backtrace as $entry) 
        {
                $skip = false;
                if(!$skip)
                {
                        foreach($files_to_skip_contient as $file_part => $bool)
                        {
                             if(contient($entry['file'], $file_part)) 
                             {
                                $skip = true;
                                break;
                             }
                             else
                             {
                                //if($file_part=="main.php") die("contient(".$entry['file'].", " . $file_part . ") = false");
                             }   
                        }
                        
                        foreach($functions_to_skip as $function_to_skip => $bool)
                        {
                             if($entry['function'] == $function_to_skip) 
                             {
                                $skip = true;
                                break;
                             }
                             else
                             {
                                //if($file_part=="main.php") die("contient(".$entry['file'].", " . $file_part . ") = false");
                             }   
                        }
                        
                }
                
                if(!$skip) 
                {
                        $important_trace_line = $entry['file']." : function " .$entry['function'] . " line " . $entry['line'];
                        //die(var_export($backtrace));
                        break;
                }        
	}
        
        return $important_trace_line;

}

