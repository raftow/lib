<?php
        
        // http://www.whatsmygps.com/
        // http://www.coordonnees-gps.fr/
        
        
        // calcul de la distance 3D conçue par partir-en-vtt.com
        // rafik : si on ne veut pas faire entrer la difference d'altitude entre le point de depart et le point d'arrivee
        //         c a dire on considere que les deux sont au meme niveau de la mere alors on met $alt1 = 0, $alt2 = 0
        //         ceci est le cas pour Riyadh (KSA) qui est ville quasi-plate 
        function distance($lat1, $lon1, $lat2=24.651, $lon2=46.704, $alt1=0, $alt2=0) 
	{
		// rayon de la terre
		$r = 6366;
		$lat1 = deg2rad($lat1);
		$lat2 = deg2rad($lat2);
		$lon1 = deg2rad($lon1);
		$lon2 = deg2rad($lon2);
 
		//recuperation altitude en km
		$alt1 = $alt1/1000;
		$alt2 = $alt2/1000;
 
		//calcul précis
		$dp= 2 * asin(sqrt(pow (sin(($lat1-$lat2)/2) , 2) + cos($lat1)*cos($lat2)* pow( sin(($lon1-$lon2)/2) , 2)));
 
		//sortie en km
		$d = $dp * $r;
 
		//Pythagore a dit que :
		 $h = sqrt(pow($d,2)+pow($alt2-$alt1,2));
 
		return $h;
	}
         
        function map($lat1, $lon1) 
	{
             return "http://www.google.com/maps/place/$lat1,$lon1";
        }
        
        function route($lat1, $lon1, $lat2=24.651, $lon2=46.704) 
	{
             return "https://www.google.com/maps/dir/'$lat1,$lon1'/'$lat2,$lon2'";
        }

?>