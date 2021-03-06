<?php
include("conf_scripts.php");
include("utils.php");
$zibase = new ZiBase($ipzibase);

//Au prealable rechercher sa ville sur weather.com et relever la valeur dans l'adresse qui ressemmble a FRXX1879:1:FR
//Declarer une sonde Virtuelle THx128 avec un identifiant OSxxxxxxx
//Declarer une sonde Virtuelle WGR800 avec un identifiant OSxxxxxxx


//Url a parser
$weather = simplexml_load_file("http://wxdata.weather.com/wxdata/weather/local/".$meteo_ville."?cc=*&unit=m"); 

// Temperature exterieure et humidite
$zibase->sendVirtualProbeValues($meteo_sonde_temperature,$weather->cc->tmp*10,$weather->cc->hmid,0); 
//123456788 : identifiant radio de la sonde sans OS devant, tmp*10 : il faut multiplier la temperature par 10 car la zibase attend l.information au dixieme de degre
// Vent 
$zibase->sendVirtualProbeValues($meteo_sonde_vent,$weather->cc->wind->s*2.8,$weather->cc->wind->d/3,0);
//Coefficient de conversion = 0.28 (la vitesse est exprime sur weather.com en m/s et sur zibase en km/h)
?>
