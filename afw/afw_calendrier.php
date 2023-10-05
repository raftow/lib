<?php


class AFWCalendrier{

        /**
        * Cette fonction te donne le numéro du jour de semaine d'une date YYYY-MM-DD               
        * @param string $datej YYYY-MM-DD
        * @return int numéro du jour de semaine
        *         1 lundi
        *         2 mardi
        *         3 mercredi
        *         4 jeudi
        *         5 vendredi
        *         6 samedi
        *         7 dimanche
        *                                                                         
        * @author Rafik BOUBAKER                 
        */

        public static function get_num_day_of($datej)
        {
                $stmpj = AfwDateHelper::dateToTimestamp($datej);
                $res = date('w',$stmpj);
                if($res==0) $res = 7;
                return $res;
        }
        
        
        
        
        public static function genere_mini_calendrier($debut_date,$fin_date,$hdeb=0,$hfin=0)
        {
            $MyMonths = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
             
            $MyDays = array("Lundi", "Mardi", "Mercredi", "Jeudi", 
                          "Vendredi", "Samedi","Dimanche");
        
                if($fin_date<$debut_date) 
                {
                        AFWDebugg::log("error : generation min calendrier impossible car date fin '$fin_date' < date debut '$debut_date' \n");
                        return 0;
                }
        
                $mini_calendar = array();
                $date_cur = $debut_date;
                $i=0;
                while($date_cur <= $fin_date)
                {
                     list($date_pure,$heure_pure) = explode(" ",$date_cur);
                     $date_exp = explode("-",$date_pure);
                     $year = $date_exp[0];
                     $month = $date_exp[1];
                     $day = $date_exp[2];
                     
                     $jourSemaine = self::get_num_day_of($date_cur);
                        
                     $day_w = $MyDays[$jourSemaine-1];
                     $month_d = $MyMonths[$month-1];
        
                     $mini_calendar[$i]["DATEJ"] = $date_cur;
                     $mini_calendar[$i]["SEMAINE_DU"] = self::debut_semaine(AfwDateHelper::dateToTimestamp($date_cur));
                     $mini_calendar[$i]["JOUR"] = $jourSemaine;
                     $mini_calendar[$i]["ANNEE"] = $year;
                     $mini_calendar[$i]["MOIS"] = $month;
                     $mini_calendar[$i]["JOUR_IN_MONTH"] = $day;
                     $mini_calendar[$i]["JOUR_IN_WEEK"] = $day_w;
                     $mini_calendar[$i]["MOIS_FR"] = $month_d;
                     if($hdeb and $hfin) $mini_calendar[$i]["CASES_DU_JOUR"] = self::createCasesArray($hdeb,$hfin);
                
                     $date_cur = self::add_x_days_to_mysqldate(1,$date_cur);
                     $i++;   
                }
                
                return $mini_calendar;
        
        }
        
        public static function createCasesArray($hdeb=0,$hfin=0)
        {
                $cases = array();
                
                for($h=$hdeb; $h<$hfin; $h++)
                {
                        $cases[$h] = "";
                }
                
                return $cases;
        }
        
        public static function genere_std_mini_calendrier($debut_date,$fin_date,$days=20,$complete_week=false,$hdeb=0,$hfin=0)
        {
                $date_cur = date("Y-m-d");
                
                
                if(!$debut_date) 
                {
                        $debut_date = $date_cur;
                }
        
        
                if(!$fin_date) 
                {
                        $fin_date = self::add_x_days_to_mysqldate($days,$date_cur);                                                
                }
                
                if($complete_week)
                {
                       $fin_date = self::fin_semaine(AfwDateHelper::dateToTimestamp($fin_date));
                }
        
                if($fin_date<$debut_date) 
                {
                        AFWDebugg::log("Rien a faire car date fin '$fin_date' < date debut '$debut_date'");
                        return 0;
                }
                else
                {
                        AFWDebugg::log("genere_std_mini_calendrier:GENERATION de l'intervalle ['$debut_date' , '$fin_date'] ");
                }
        
                
        
                $mini_calendar = self::genere_mini_calendrier($debut_date,$fin_date,$hdeb,$hfin);
                
                return array($debut_date,$fin_date,$mini_calendar);
        
        }
        

        /**
        * Cette fonction convertit une date au format YYYY-mm-dd au time stamp correspondant               
        * @param string $str 
        * @return int time stamp correspondant
        * @author Rafik BOUBAKER                 
        */
        
        public static function from_mysql_to_timestamp($str)
        {
                $arr_dat = explode(' ',$str);
                $arr_day = explode('-',$arr_dat[0]);
        	if (! $arr_dat[1]) { $arr_dat[1]="0:0:0"; }
                $arr_hour = explode(':',$arr_dat[1]);
                $tmstmp = mktime($arr_hour[0],$arr_hour[1],$arr_hour[2],$arr_day[1],$arr_day[2],$arr_day[0]);
                
                return $tmstmp;
        }
        

        public static function parse_date($datestr)
        {
                $fc = substr($datestr, 0, 1);
                if(($fc=="+") or ($fc=="-"))
                {
                        $mondatetime = substr($datestr, 1);
                        $arr_dat = explode(' ',$mondatetime);
                        $arr_day = explode('-',$arr_dat[0]);
                        if (!$arr_dat[0]) $arr_dat[0]="0000-00-00"; 
                        if (!$arr_dat[1]) $arr_dat[1]="0:0:0";                        
                        $arr_hour = explode(':',$arr_dat[1]);
                        if($fc=="+") $coef = 1; else $coef = -1;
                        $h = intval($arr_hour[0])*$coef;
                        $n = intval($arr_hour[1])*$coef;
                        $s = intval($arr_hour[2])*$coef;

                        $a = intval($arr_day[0])*$coef;
                        $m = intval($arr_day[1])*$coef;
                        $j = intval($arr_day[2])*$coef;

                        return self::add_datetime_to_mysql_datetime("",$a,$m,$j,$h,$n,$s);

                }
            

                return $datestr;
        }

        public static function add_datetime_to_mysql_datetime($mondatetime="",$a=0,$m=0,$j=0,$h=0,$n=0,$s=0)
        {
                if(!$mondatetime) $mondatetime =  date("Y-m-d H:i:s");
                
                $arr_dat = explode(' ',$mondatetime);
                $arr_day = explode('-',$arr_dat[0]);
        	if (! $arr_dat[1]) { $arr_dat[1]="0:0:0"; }
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
        
        public static function debut_semaine($auj=0)
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
        
        public static function fin_semaine($auj=0,$taille_sem=7)
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
        
        
        public static function debut_mois($auj=0)
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
        
        public static function fin_mois($auj=0)
        {
             if(!$auj) $auj = time();
             return date("Y-m-",$auj).intval(date("t",date("m",$auj)));
        }
        
        /**
        * Cette fonction revien N jours si elle tombe sur un weekend elle avance ou recule encore selon si n<0 ou >0                 
        * @param int $n : nbre de jour
        *        int $date_dep : date de départ au format mysql YYYY-mm-dd
        * @return date au format mysql YYYY-mm-dd
        * @author Rafik BOUBAKER                 
        */
        
        public static function add_njours_ouvrables($n,$date_dep)
        {
           $tms_dep = AfwDateHelper::dateToTimestamp($date_dep);
           
           // vérifier que la journée en cours est ouvrable sinon décaler le début de calcul
           // à jusqu'au debut d'une date ouvrable
           
           $nbr_jour_in_week = date('w',$tms_dep);
           if($nbr_jour_in_week==0)
           {
                $arr_dat = explode(' ',$date_dep);
                $arr_day = explode('-',$arr_dat[0]);
                $arr_hour = explode(':',$arr_dat[1]);
                if($n>=0) $delta = 1;
                else $delta = -1;
                $tms_dep = mktime(0,0,0,$arr_day[1],$arr_day[2]+$delta,$arr_day[0]);
                $date_dep = date("Y-m-d H:i:s",$tms_dep);
                // debugg("c'est un dimanche j'avance de $delta jour(s) = $date_dep");
           }
        
           if($nbr_jour_in_week==6)
           {
                $arr_dat = explode(' ',$date_dep);
                $arr_day = explode('-',$arr_dat[0]);
                $arr_hour = explode(':',$arr_dat[1]);
                if($n>=0) $delta = 2;
                else $delta = 0;
                $tms_dep = mktime(0,0,0,$arr_day[1],$arr_day[2]+$delta,$arr_day[0]);
                $date_dep = date("Y-m-d H:i:s",$tms_dep);
                // debugg("c'est un samedi j'avance de $delta jour(s) = $date_dep");
           }
            
        
        
           if($n>0) 
           {
                $nb = $n-1;
                $new_date = self::demain_ouvrable($tms_dep); 
           }
           elseif($n<0)
           {
                $nb = $n+1;
                $new_date = self::hier_ouvrable($tms_dep);
           }
           elseif($n==0)
           {
                return $date_dep;
           }
           
           // debugg("recursivite - add_njours_ouvrables($nb,$new_date)");
           
           return self::add_njours_ouvrables($nb,$new_date); 
        }
        
        
        
        public static function hier_ouvrable($auj=0)
        {
            if(!$auj) $auj = time();
            $nbr_jour_in_week = date('w',$auj);
            $mois =  intval(date('m',$auj));
            $jour =  intval(date('d',$auj));
            $annee =  intval(date('Y',$auj));
            $HH =  intval(date('H',$auj));
            $ii =  intval(date('i',$auj));
            $ss =  intval(date('s',$auj));
            // debugg("hier ouv of $jour/$mois/$annee $HH:$ii:$ss");
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
            // debugg("hier ouv = $hier_ouv");
            return $hier_ouv;    
        }
        
        /**
        * Cette fonction revien 1 jour en arrière si elle tombe sur un weekend elle revien a vendredi                 
        * @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
        * @return date au format mysql YYYY-mm-dd
        * @author Rafik BOUBAKER                 
        */
        
        
        public static function demain_ouvrable($auj=0)
        {
            if(!$auj) $auj = time();
            $nbr_jour_in_week = date('w',$auj);
            $mois =  intval(date('m',$auj));
            $jour =  intval(date('d',$auj));
            $annee =  intval(date('Y',$auj));
            $HH =  intval(date('H',$auj));
            $ii =  intval(date('i',$auj));
            $ss =  intval(date('s',$auj));
            // debugg("demain ouv of $jour/$mois/$annee $HH:$ii:$ss");
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
            // debugg("demain ouv = $hier_ouv");
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
        
        public static function mysqldate_to_explicit_fr_date($MyDate, $WeekDayOn=1, $YearOn=1, $MonthNameOn=1,$Separator=" ")
        {
          $MyMonths = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
          $MyDays = array("Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", 
                          "Vendredi", "Samedi");
          list($MyDate2,$MyDate3) = explode(' ',$MyDate);
          $DF = explode('-',$MyDate2);
          $TheDay=getdate(mktime(0,0,0,$DF[1],$DF[2],$DF[0]));
        
          $MyDateFinal = $DF[2].$Separator;
          if($MonthNameOn)
            $MyDateFinal .= $MyMonths[$DF[1]-1];
          else
            $MyDateFinal .= $DF[1];
            
          if($WeekDayOn) $MyDateFinal = $MyDays[$TheDay["wday"]]." ".$MyDateFinal;
          if($YearOn) $MyDateFinal .= $Separator.$DF[0];
                
          return $MyDateFinal; 
        }
        
        public static function mysqldate_to_fr_date($MyDate,$WeekDayOn=0)
        {
           return self::mysqldate_to_explicit_fr_date($MyDate, $WeekDayOn, 1, 0,"/");
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
        public static function add_x_days_to_mysqldate($nb,$from_date='')
        {
            if(!$from_date) $from_date = date('Y-m-d');
            
            $from_tab = explode('-',$from_date);
            
            $to_date = date("Y-m-d",mktime(0,0,0,$from_tab[1],$from_tab[2]+$nb,$from_tab[0]));
        
            return($to_date);
        }
        
        
        /**
        * Convertir un nombre de secondes en son équivalent heure:minute:seconde.                 
        * @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
        * @return date au format mysql YYYY-mm-dd
        * @author Rafik BOUBAKER                 
        */
        
        public static function convert_time_tohhmmss($temps,$Format='')
        {
                  //combien d'heures ?
                  $hours = floor($temps / 3600);
                
                  //combien de minutes ?
                  $min = floor(($temps - ($hours * 3600)) / 60);

                  //combien de secondes ?
                  $sec = ($temps - ($hours * 3600) - ($min * 60));
                  
                  if($Format=='h:m')  
                  {
                      if ($sec>0) $min += 1;
                      if ($min==60) 
                      {
                          $hours += 1;
                          $min = 0;
                      } 
                  }
                  
                  if ($min < 10) $min = "0".$min;
                
                  //combien de secondes
                  $sec = round($temps - ($hours * 3600) - ($min * 60));
                  if ($sec < 10) $sec = "0".$sec;
                        
                  if($Format=='h:m')      
                      return $hours.":".$min;
                  else
                      return $hours.":".$min.":".$sec;
            
        }
        
        
        /**
        * Cette fonction ....@todo-rafik                 
        * @param timestamp $auj : retour de mktime ou cf from_mysql_to_timestamp aussi
        * @return date au format mysql YYYY-mm-dd
        * @author Rafik BOUBAKER                 
        */
        
        function val_or_zero($val,$zero=0)
        {
            if($val) return $val;
            else return $zero;            
        }


}

?>
