<?php
//------------------------------------------------------------------------------
// Fonctions sur les dates
//------------------------------------------------------------------------------
/**
* Cette fonction convertit une date au format YYYY-mm-dd au time stamp correspondant               
* @param string $madate 
* @return int time stamp correspondant
* @author Rafik BOUBAKER                 
*/

function from_mysql_to_timestamp($madate)
{
        $arr_dat = explode(' ',$madate);
        $arr_day = explode('-',$arr_dat[0]);
        $arr_hour = explode(':',$arr_dat[1]);
        if(!$arr_hour[0]) $arr_hour[0] = 0;
        if(!$arr_hour[1]) $arr_hour[1] = 0;
        if(!$arr_hour[2]) $arr_hour[2] = 0;
        $tmstmp = mktime($arr_hour[0],$arr_hour[1],$arr_hour[2],$arr_day[1],$arr_day[2],$arr_day[0]);
        
        return $tmstmp;
}

function add_slashes($madate)
{
    $madate_YYYY = substr($madate,0,4);
    $madate_MM = substr($madate,4,2);
    $madate_DD = substr($madate,6,2);
    
    return "$madate_YYYY/$madate_MM/$madate_DD";  
}

function add_dashes($madate)
{
    $madate_YYYY = substr($madate,0,4);
    $madate_MM = substr($madate,4,2);
    $madate_DD = substr($madate,6,2);
    
    return "$madate_YYYY-$madate_MM-$madate_DD";
}

function remove_dashes($gdate)
{
    $arr_gdate = explode("-",$gdate);
    $madate_YYYY = $arr_gdate[0];
    $madate_MM = $arr_gdate[1];
    $madate_DD = $arr_gdate[2];
    
    return $madate_YYYY.$madate_MM.$madate_DD;
}

function diff_datetime_in_sec($madate2,$madate1)
{
       $stmp2 =   from_mysql_to_timestamp($madate2);
       $stmp1 =   from_mysql_to_timestamp($madate1);
       
       return $stmp2-$stmp1;
}

function diff_datetime_formatted($madate2,$madate1, $Format="h:i")
{
        $diff = diff_datetime_in_sec($madate2,$madate1);
        // die("$diff = diff_datetime_in_sec($madate2,$madate1)");
        return convert_time_tohhmmss($diff,$Format);       
}



function diff_date($madate2,$madate1,$round=true)
{
       if(strpos($madate2, '-')===false)
       {
              $madate2 = add_dashes($madate2);
       }
       
       if(strpos($madate1, '-')===false)
       {
              $madate1 = add_dashes($madate1);
       }
       
       
       $stmp2 =   from_mysql_to_timestamp($madate2);
       $stmp1 =   from_mysql_to_timestamp($madate1);
       
       
       $result_diff = ($stmp2-$stmp1)/(24*3600);
       if($round) $result_diff = round($result_diff);
       
       return $result_diff;
}


function add_datetime_to_mysql_datetime($mondatetime="",$a=0,$m=0,$j=0,$h=0,$n=0,$s=0)
{
        if(!$mondatetime) $mondatetime =  date("Y-m-d H:i:s");
        
        $arr_dat = explode(' ',$mondatetime);
        $arr_day = explode('-',$arr_dat[0]);
        $arr_hour = explode(':',$arr_dat[1]);
        $tmstmp = mktime($arr_hour[0]+$h,$arr_hour[1]+$n,$arr_hour[2]+$s,$arr_day[1]+$m,$arr_day[2]+$j,$arr_day[0]+$a);
        
        return date("Y-m-d H:i:s",$tmstmp);
}

/**
* Cette fonction calcule la date de début de semaine (lundi) de la   
* semaine dans laquelle se trouve la date date. 
* Si aucune date est mise c la date en cours donc la semaine en cours                 
* @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/

function debut_semaine($auj=0)
{
    if(!$auj) $auj = time();
    $n = date('w',$auj);
    if($n>0) $offset = 1; else $offset = -6;
    $premier_jour = mktime(0,0,0,date("m",$auj),date("d",$auj)-$n+$offset,date("Y",$auj));
    $datedeb = date("Y-m-d", $premier_jour);

    return $datedeb;
}

/**
* Cette fonction calcule la date de fin de semaine (dimanche) de la   
* semaine dans laquelle se trouve la date date. 
* Si aucune date est mise c la date en cours donc la semaine en cours                 
* @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/

function fin_semaine($auj=0,$taille_sem=7)
{
    if(!$auj) $auj = time();
    $n = date('w',$auj);
    if($n>0) $offset = 0; else $offset = -7;
    $dernier_jour = mktime(0,0,0,date("m",$auj),date("d",$auj)-$n+$offset+$taille_sem,date("Y",$auj));
    $datefin = date("Y-m-d", $dernier_jour);

    return $datefin;
}

/**
* Cette fonction ....@todo-rafik                 
* @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/


function debut_mois($auj=0)
{
      if(!$auj) $auj = time();
      return date("Y-m-01",$auj);
}

/**
* Cette fonction ....@todo-rafik                 
* @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/

function fin_mois($auj=0)
{
     if(!$auj) $auj = time();
     return date("Y-m-",$auj).intval(date("t",date("m",$auj)));
}

function get_first_working_day($sens, $date_dep, $date_format = "Y-m-d H:i:s")
{
   global $weekend_arr;
   
   if(!$weekend_arr) $weekend_arr = array(5=>array('delta_plus'=>2,'delta_moins'=>-1,), 6=>array('delta_plus'=>1,'delta_moins'=>-2,));
   // = array(0=>array(delta_plus=>1,delta_moins=>-2,), 6=>array(delta_plus=>2,delta_moins=>-1,));
   
   $tms_dep = from_mysql_to_timestamp($date_dep);
   
   // vérifier que la journée en cours est ouvrable sinon décaler le début de calcul
   // à jusqu'au debut d'une date ouvrable
   
   $nbr_jour_in_week = date('w',$tms_dep);
   if($weekend_arr[$nbr_jour_in_week])
   {
        $arr_dat = explode(' ',$date_dep);
        $arr_day = explode('-',$arr_dat[0]);
        $arr_hour = explode(':',$arr_dat[1]);
        if($sens>=0) $delta = $weekend_arr[$nbr_jour_in_week]["delta_plus"];
        else $delta = $weekend_arr[$nbr_jour_in_week]["delta_moins"];
        $tms_dep = mktime(0,0,0,$arr_day[1],$arr_day[2]+$delta,$arr_day[0]);
        $date_dep = date($date_format,$tms_dep);
        //debugg("c'est un dimanche j'avance de $delta jour(s) = $date_dep");
   }
   
   return $date_dep;
}

/**
* Cette fonction revien N jours si elle tombe sur un weekend elle avance ou recule encore selon si n<0 ou >0                 
* @param int $n : nbre de jour
*        int $date_dep : date de départ au format mysql YYYY-mm-dd
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/

function add_n_days($working_only, $n, $date_dep, $date_format = "Y-m-d H:i:s",$sens=null)
{
   if($n>0 and $sens==null) $sens= 1;
   if($n<0 and $sens==null) $sens= -1;
   
   if($working_only)
   {
           // echo "before get_first_working_day $date_dep <br>";
           $date_dep = get_first_working_day($sens, $date_dep, $date_format);
           // echo "after get_first_working_day $date_dep <br>";
   
   
           $tms_dep = from_mysql_to_timestamp($date_dep);
        
           if($n>0) 
           {
                $nb = $n-1;
                $new_date = tomorrow($tms_dep, $date_format); 
           }
           elseif($n<0)
           {
                $nb = $n+1;
                $new_date = yesterday($tms_dep, $date_format);
           }
           elseif($n==0)
           {
                if($working_only) $date_dep = get_first_working_day($sens, $date_dep, $date_format);
                // echo "fin $date_dep <br>";        
                return $date_dep;
           }
           
           //debugg("recursivite - add_njours_ouvrables($nb,$new_date)");
           // echo "add_n_days($nb, $new_date, $date_format, $sens)<br>";
           return add_n_days($working_only, $nb, $new_date, $date_format, $sens);
   }
   else
   {
           return add_x_days_to_mysqldate($n,$date_dep);
   } 
}


function yesterday($auj=0, $date_format = "Y-m-d H:i:s")
{
    if(!$auj) $auj = time();
    $nbr_jour_in_week = date('w',$auj);
    $mois =  intval(date('m',$auj));
    $jour =  intval(date('d',$auj));
    $annee =  intval(date('Y',$auj));
    $HH =  intval(date('H',$auj));
    $ii =  intval(date('i',$auj));
    $ss =  intval(date('s',$auj));
    
    $hier = date($date_format,mktime( $HH, $ii, $ss, $mois, ($jour - 1), $annee));
    
    return $hier;    
}

function tomorrow($auj=0, $date_format = "Y-m-d H:i:s")
{
    if(!$auj) $auj = time();
    $nbr_jour_in_week = date('w',$auj);
    $mois =  intval(date('m',$auj));
    $jour =  intval(date('d',$auj));
    $annee =  intval(date('Y',$auj));
    $HH =  intval(date('H',$auj));
    $ii =  intval(date('i',$auj));
    $ss =  intval(date('s',$auj));
    
    $demain = date($date_format,mktime( $HH, $ii, $ss, $mois, ($jour + 1), $annee));
    
    return $demain;    
}


function hier_ouvrable($auj=0)
{
    if(!$auj) $auj = time();
    $nbr_jour_in_week = date('w',$auj);
    $mois =  intval(date('m',$auj));
    $jour =  intval(date('d',$auj));
    $annee =  intval(date('Y',$auj));
    $HH =  intval(date('H',$auj));
    $ii =  intval(date('i',$auj));
    $ss =  intval(date('s',$auj));
    //debugg("hier ouv of $jour/$mois/$annee $HH:$ii:$ss");
    if($nbr_jour_in_week==1) 
    {
       $hier_ouv = date('Y-m-d H:i:s',mktime( $HH, $ii, $ss, $mois, ($jour - 3), $annee));
    }
    elseif($nbr_jour_in_week==0) 
    {
       $hier_ouv = date('Y-m-d H:i:s',mktime( $HH, $ii, $ss, $mois, ($jour - 2), $annee));
    }
    else
    {
       $hier_ouv = date('Y-m-d H:i:s',mktime( $HH, $ii, $ss, $mois, ($jour - 1), $annee));
    }
    //debugg("hier ouv = $hier_ouv");
    return $hier_ouv;    
}

/**
* Cette fonction revien 1 jour en arrière si elle tombe sur un weekend elle revien a vendredi                 
* @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/


function demain_ouvrable($auj=0)
{
    if(!$auj) $auj = time();
    $nbr_jour_in_week = date('w',$auj);
    $mois =  intval(date('m',$auj));
    $jour =  intval(date('d',$auj));
    $annee =  intval(date('Y',$auj));
    $HH =  intval(date('H',$auj));
    $ii =  intval(date('i',$auj));
    $ss =  intval(date('s',$auj));
    //debugg("demain ouv of $jour/$mois/$annee $HH:$ii:$ss");
    if($nbr_jour_in_week==5) 
    {
       $hier_ouv = date('Y-m-d H:i:s',mktime( $HH, $ii, $ss, $mois, ($jour + 3), $annee));
    }
    elseif($nbr_jour_in_week==6) 
    {
       $hier_ouv = date('Y-m-d H:i:s',mktime( $HH, $ii, $ss, $mois, ($jour + 2), $annee));
    }
    else
    {
       $hier_ouv = date('Y-m-d H:i:s',mktime( $HH, $ii, $ss, $mois, ($jour + 1), $annee));
    }
    //debugg("demain ouv = $hier_ouv");
    return $hier_ouv;    
}

/**
* Avec la fonction suivante vous allez pouvoir transformer                              
* une date au format MySQL : 2002-06-11 en : Mardi 11 Juin 2002.                       
* En option vous pouvez choisir de ne pas afficher le jour de la semaine et/ou l'année.
*                 
* @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/

function current_greg_date_arr()
{
     return mysqldate_to_explicit_fr_date_arr();
}

function mysqldate_to_explicit_fr_date($MyDate="", $WeekDayOn=1, $YearOn=1, $MonthNameOn=1,$Separator=" ")
{
     return mysqldate_to_explicit_fr_date_arr($MyDate, $WeekDayOn, $YearOn, $MonthNameOn,$Separator,$return_array=false);
}


function mysqldate_to_explicit_fr_date_arr($MyDate="", $WeekDayOn=1, $YearOn=1, $MonthNameOn=1,$Separator=" ",$return_array=true)
{
  if(!$MyDate) $MyDate = date("Y-m-d H:i:s");
  
  $MyMonths = array("يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو",
        "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر");
  
  /*$MyMonths = array("جانفي", "فيفري", "مارس", "أفريل", "ماي", "جوان",
        "جويلية", "أوت", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر");*/
  $MyDays = array("الأحد", "الأثنين", "الثلاثاء", "الإربعاء", "الخميس", 
                  "الجمعة", "السبت");
  list($MyDate2,$MyDate3) = explode(' ',$MyDate);
  $DF = explode('-',$MyDate2);
  $TheDay=getdate(mktime(0,0,0,$DF[1],$DF[2],$DF[0]));

  $MyDateFinal_arr = array();
  

  //week day
  if($WeekDayOn) $MyDateFinal_arr[] = $MyDays[$TheDay["wday"]];

  //day
  $MyDateFinal_arr[] = $DF[2];
  
  //month
  if($MonthNameOn)
    $MyDateFinal_arr[] = $MyMonths[$DF[1]-1];
  else
    $MyDateFinal_arr[] = $DF[1];
    
  if($YearOn) $MyDateFinal_arr[] = $DF[0];
  
  if($return_array) return $MyDateFinal_arr;
  else return implode($Separator, $MyDateFinal_arr); 
}

function mysqldate_to_tn_hour($MyDate,$seconds=0)
{
        $arr_dat=explode(' ',$MyDate);
        $arr_dat1=explode('-',$arr_dat[0]);
        $arr_time=explode(':',$arr_dat[1]);
        $hh = $arr_time[0];
        $nn = $arr_time[1];
        $ss = $arr_time[2];
        
        $result = "الساعة $hh و $nn دق";
        if($seconds) $result .= " و $ss ث";
        return $result;
}

function mysqldate_to_tn_date($MyDate,$WeekDayOn=1)
{
   return mysqldate_to_explicit_fr_date($MyDate, $WeekDayOn, 1, 1," ");
}

function mysqldate_to_fr_date($MyDate,$WeekDayOn=0)
{
   return mysqldate_to_explicit_fr_date($MyDate, $WeekDayOn, 1, 0,"/");
}


function fr_to_mysqldate_date($MyDate)
{
  $tab_dat = explode("/",$MyDate);
  $dd = $tab_dat[0];
  $mm = $tab_dat[1];
  $yyyy = $tab_dat[2];
  
  return "$yyyy-$mm-$dd";
}



/**
* Cette fonction vous permet de calculer une date dans le futur 
* (dans un certain nombre de jours) à partir de la date du jour ou d'une date donnée
* au format MySQL (YYYY-MM-DD). Il suffit de fournir le nombre de jours en paramètre : 
*                 
* @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/
function add_x_days_to_mysqldate($nb_days,$from_date='')
{
    return add_period_to_gregdate($nb_days,0,0,$from_date);
}

function add_period_to_gregdate($nb_days, $nb_months=0, $nb_years=0, $from_date='')
{
    if(!$from_date) $from_date = date('Y-m-d');
    //echo "<br>from_date = $from_date";
    $from_tab = explode('-',$from_date);
    //echo "<br>from_tab = ".var_export($from_tab,true);
    
    
    $to_date = date("Y-m-d",mktime(0,0,0,$from_tab[1]+$nb_months,$from_tab[2]+$nb_days,$from_tab[0]+$nb_years));

    //echo "<br>from_date + $nb_days = $to_date";

    return($to_date);
}

/**
* Convertir un nombre de secondes en son équivalent heure:minute:seconde.                 
* @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
* @return date au format mysql YYYY-mm-dd
* @author Rafik BOUBAKER                 
*/

function convert_time_tohhmmss($temps,$Format='')
{
          //combien d'heures ?
          $hours = floor($temps / 3600);
        
          //combien de minutes ?
          $min = floor(($temps - ($hours * 3600)) / 60);

          //combien de secondes
          $sec = round($temps - ($hours * 3600) - ($min * 60));
          
          
          if(($Format=='h:m') or ($Format=='h:i')) 
          {
              if ($sec>29) $min += 1;
              if ($min==60) 
              {
                  $hours += 1;
                  $min = 0;
              } 
          }
          
          if ($min < 10) $min = "0".$min;
        
          if ($sec < 10) $sec = "0".$sec;
                
          if($Format=='h:m')      
              return $hours.":".$min;
          else
              return $hours.":".$min.":".$sec;
    
}

function ds_to_hhmmss($tds,$Format='')
{
          $temps = $tds/10;
          
          //combien d'heures ?
          $hours = floor($temps / 3600);
        
          //combien de minutes ?
          $min = floor(($temps - ($hours * 3600)) / 60);
          
          //combien de secondes
          $sec = round($temps - ($hours * 3600) - ($min * 60));

          if(($Format=='h:m') or ($Format=='h:i'))   
          {
              if ($sec>29) $min += 1;
              if ($min==60) 
              {
                  $hours += 1;
                  $min = 0;
              } 
          }
          
          if ($min < 10) $min = "0".$min;
        
          if ($sec < 10) $sec = "0".$sec;
                
          if($Format=='h:m')      
              return $hours.":".$min;
          else
              return $hours.":".$min.":".$sec;
    
}

function long_hijri_date($hijri_year,$mm,$dd,$TheDay, $WeekDayOn=1, $YearOn=1, $MonthNameOn=1,$Separator=" ")
{
  
  $MyMonths = array("محرم", "صفر", "ربيع الأول", "ربيع الآخر", "جمادى الأولى", "جمادى الآخرة",
        "رجب", "شعبان", "رمضان", "شوّال", "ذو القعدة", "ذو الحجة");
        
  $MyDays = array("الأحد", "الأثنين", "الثلاثاء", "الإربعاء", "الخميس", 
                  "الجمعة", "السبت");
  
  $MyDateFinal = $dd.$Separator;
  if($MonthNameOn)
    $MyDateFinal .= $MyMonths[$mm-1];
  else
    $MyDateFinal .= $mm;
    
  if($WeekDayOn) $MyDateFinal = $MyDays[$TheDay["wday"]].$Separator.$MyDateFinal;
  if($YearOn) $MyDateFinal .= $Separator.$hijri_year;
        
  return $MyDateFinal; 
}




/* 
function gregdate_of_first_hijri_day($hijri_year, $hijri_month)
{

    global $hgreg_matrix;
     if(!$hgreg_matrix) $hgreg_matrix = array();
     if($hgreg_matrix[$hijri_year.$hijri_month]) return $hgreg_matrix[$hijri_year.$hijri_month];
     
     //if(count($hgreg_matrix)>0) die("gregdate_of_first_hijri_day($hijri_year, $hijri_month) : ".var_export($hgreg_matrix,true));


     $sql_greg = " select greg_date
         from c-0pag.hijra_date_base 
               where hijri_year = $hijri_year
                 and hijri_month = $hijri_month";
     //echo "<br>sql_greg = $sql_greg";             
     
     $file_dir_name = dirname(__FILE__);
     // 
     
     $greg_date = recup_value($sql_greg);
     $hgreg_matrix[$hijri_year.$hijri_month] = add_dashes($greg_date);
     
     
     return $hgreg_matrix[$hijri_year.$hijri_month];
}



function AfwDateHelper::hijriToGreg($hdate)
{
     $dbgg = false;
     
     if($dbgg) echo "<br>\n AfwDateHelper::hijriToGreg($hdate)";
     
     if(strpos($hdate, '-')!==false)
     {
         $hdate = remove_dashes($hdate);
     }
     
     if(strpos($hdate, '/')===false)
     {
          $hdate = add_slashes($hdate);
     }
     
     $hd_arr = explode('/',$hdate);
     if($dbgg) echo "<br>\n hd_arr = ".var_export($hd_arr,true);
     $hijri_year = intval($hd_arr[0]);
     $hijri_month = intval($hd_arr[1]);
     $hijri_day = intval($hd_arr[2]);
    
     $first_gregdate = gregdate_of_first_hijri_day($hijri_year, $hijri_month);  
     if($dbgg) echo "<br>\n gregdate_of_first_hijri_day($hijri_year, $hijri_month) = $first_gregdate";
                 
     $greg_date = add_x_days_to_mysqldate($hijri_day-1,$first_gregdate);
     
     if($dbgg) echo "<br>\n add_x_days_to_mysqldate($hijri_day-1,$first_gregdate) = $greg_date";
     
     if($dbgg) die();
     
     return $greg_date;    
}*/













function greg_date_format($date,$y_offset=0)
{
        list($y, $m, $d) = array_pad(explode('-', $date, 3), 3, 0);
        return ctype_digit("$y$m$d") && (checkdate($m, $d, intval($y)+$y_offset) or ("$y$m$d"=="00000000"));
}    

function hijri_date_format($hdate)
{
      if(strlen($hdate)!=8) return false;
      if(!is_numeric($hdate)) return false;
      $hdate = add_dashes($hdate);   
      list($y, $m, $d) = array_pad(explode('-', $hdate, 3), 3, 0);
      if(($y<1000) or ($y>1700)) return false;
      if(($m<1) or ($m>12)) return false;
      if(($d<1) or ($d>30)) return false;

      return true;
}




/**
 *
 *  $time_to_add is decimal unit = hour
 *
 **/


function getSplittedTime($time_to_add)
{
   $hh_to_add = floor($time_to_add);
   $ii_to_add = floor(($time_to_add-$hh_to_add)*60);
   $ss_to_add = round((($time_to_add-$hh_to_add)*60-$ii_to_add)*60);

   return array($hh_to_add, $ii_to_add, $ss_to_add);

}

function addTimeToDayTime($day, $time, $time_to_add, $seconds=false)
{
   list($hh_to_add, $ii_to_add, $ss_to_add) = getSplittedTime($time_to_add);
   
   if(strlen($time)==5)  $time .= ":00";
   
   $to_day = date("Y-m-d");
   
   $date_time_day = $to_day . " " . $time;
   
   
   $new_date_time = add_datetime_to_mysql_datetime($date_time_day,$a=0,$m=0,$j=0,$hh_to_add,$ii_to_add,$ss_to_add);
   
   list($new_date, $new_time) = explode(" ",$new_date_time);
   
   if(!$seconds) $new_time = substr($new_time,0,5);
   
   $new_day = $day + diff_date($new_date,$to_day);
   
   return array($new_day, $new_time);

}

// getDayOfWeek($date_greg)
// saturday : 0
// sunday : 1
// ..
// friday : 6 

function getDayOfWeek($date_greg, $translate_lang="")
{
        $tms_dep = from_mysql_to_timestamp($date_greg);
        $day_of_week = date('w',$tms_dep);
        // die("ss : $date_greg > $tms_dep > w = $day_of_week");
        if(!$translate_lang) return $day_of_week;

        $days_title_arr = array();
        $days_title_arr[1] = array('ar' => "الأحد", 'en' => "sunday", 'fr' =>"dimanche");
        $days_title_arr[2] = array('ar' => "الاثنين", 'en' => "monday", 'fr' =>"lundi");
        $days_title_arr[3] = array('ar' => "الثلاثاء", 'en' => "tuesday", 'fr' =>"mardi");
        $days_title_arr[4] = array('ar' => "الاربعاء", 'en' => "wednesday", 'fr' =>"mercredi");
        $days_title_arr[5] = array('ar' => "الخميس", 'en' => "thursday", 'fr' =>"jeudi");
        $days_title_arr[6] = array('ar' => "الجمعة", 'en' => "friday", 'fr' =>"vendredi");
        $days_title_arr[7] = array('ar' => "السبت", 'en' => "saturday", 'fr' =>"samedi");
        
        return   $days_title_arr[$day_of_week+1][$translate_lang];
     
}
