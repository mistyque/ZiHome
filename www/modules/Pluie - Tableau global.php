<?php
if (isset($_GET['requete'])) { // si le script est bien appelé par ajax en precisant l'objet de la requête
  include("../config/conf_zibase.php");
  include("../config/variables.php");
  include("../lib/zibase.php");
  include_once("../lib/date_francais.php");
  
  $link = mysql_connect($hote,$login,$plogin);
  if (!$link) {
    die('Non connect&eacute; : ' . mysql_error());
  }
  $db_selected = mysql_select_db($base,$link);
  if (!$db_selected) {
    die ('Impossible d\'utiliser la base : ' . mysql_error());
  }
  $i=0;
  include_once("./lib/date_francais.php");
  echo "<CENTER><TABLE width=500px>";
  echo "<TR class=tab-titre><TD></TD><TD>Capteur</TD><TD width=100px>Pr&eacute;cipitation</TD><TD width=100px>Cumul</TD><TD width=150px>Dernier enregistrement</TD></TR>";
  $query = "SELECT * FROM peripheriques WHERE periph = 'pluie'";
  $req = mysql_query($query, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
  while ($periph = mysql_fetch_assoc($req))
  {
    if($periph['batterie'] == 0)
    {
      $batterie = "";
    } else {
      $batterie = "<img src='./img/batterie_ko.png' style='height:20px'/>";
    }
    $query0 = "SELECT * FROM `pluie_".$periph['nom']."` ORDER BY `date` DESC LIMIT 1";
    $req0 = mysql_query($query0, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
    
    while ($value0 = mysql_fetch_assoc($req0))
    {
      if($periph['libelle'] == ""){
        $nom = $periph['nom'];
      } else {
        $nom = $periph['libelle'];
      }
      echo "<TR class=tab-ligne bgcolor='".( ($i % 2 == 1) ? '#dddddd' : '#eeeeee' )."' id='TR_".$periph['id']."'>";
      echo "  <TD>".$batterie."</TD>";
      echo "  <TD><span style='vertical-align:3px'>".$nom."</span></TD>";
      echo "  <TD ALIGN=CENTER>".$value0['pluie']." mm/h</TD>";
      echo "  <TD ALIGN=CENTER>".($value0['cumul'])." mm</TD>";
      echo "  <TD>".date_simplifiee($value0['date'])."</TD>";
      echo "</TR>";
      $i++;
    }
  }
  echo "</TABLE></CENTER>";
}
  ?>