<?
if(!(isset($_SESSION['auth']))) 
{
  include('./lib/mac_address.php');
  
  $query = "SELECT * FROM `auto-logon` WHERE macaddress = '".$macAddr."'";
  $req = @mysql_query ($query);
  $data = mysql_fetch_assoc ($req);
  if ($data)
  {
    $query = "SELECT * FROM `users` WHERE pseudo = '".$data['pseudo']."'";
    $req = @mysql_query ($query);
    $data = mysql_fetch_assoc ($req);
    if($data) 
    {
      $_SESSION['auth'] = $data['pseudo'];
      $_SESSION['niveau'] = $data['niveau'];
      echo "<meta http-equiv=\"refresh\" content=\"0; url=".$_SERVER['PHP_SELF']."\">";
    }
    mysql_close();
  }
}
if(isset($_SESSION['auth']))
{
  if(isset($_GET['logout']))
  {
    unset($_SESSION['auth']);
    unset($_SESSION['niveau']);
    echo "<meta http-equiv=\"refresh\" content=\"0; url=./index.php\">";
  }
  else
  {
    echo "<div id=\"logout\"><center><a href='./index.php?logout=logout'><img src=\"./img/icon_logout.png\"><br>D&eacute;connexion</a></center></div>";
  }
}
else
{
  if (isset($_POST['submit1']))
  {
    $message = NULL;
    if (empty($_POST['pseudo']))
    { 
      $message .= '<p>Merci de saisir un login</p>'; 
    }
    if (empty($_POST['pass']))
    { 
      $message .= '<p>Merci de saisir un mot de passe</p>'; 
    }
    if ($message == NULL)
    {
      $query = "SELECT * FROM `users` WHERE pseudo = '".$_POST['pseudo']."' AND pass = SHA1('".$_POST['pass']."')";
      $req = @mysql_query ($query);
      $data = mysql_fetch_assoc ($req);
      if ($data)
      {
        $_SESSION['auth'] = $data['pseudo'];
        $_SESSION['niveau'] = $data['niveau'];
        mysql_close();
        echo "<meta http-equiv=\"refresh\" content=\"0; url=".$_SERVER['PHP_SELF']."\">";
      }
      else
      {
        echo "<center>erreur d'identification<br></center>";
        echo "<meta http-equiv=\"refresh\" content=\"4; url=".$_SERVER['PHP_SELF']."\">";
      }
    }
    else
    {
      echo $message;
      echo "<meta http-equiv=\"refresh\" content=\"4; url=".$_SERVER['PHP_SELF']."\">";
    }
  }
  else
  {
    echo "<center> <form method=\"POST\" action=\"".$_SERVER['PHP_SELF']."\" style=\"margin:0;padding=0;\">
    <table style=\"border: 0px;\" cellspacing=\"0\">
      <tr>
        <td><img src=\"./img/user.png\" width=\"14px\"></td>
        <td><input type=\"text\" name=\"pseudo\" size=\"12\" maxlength=\"40\"/></td>
      </tr>
      <tr>
        <td><img src=\"./img/mdp.png\" width=\"14px\"></td>
        <td><input type=\"password\" name=\"pass\" size=\"12\" maxlength=\"20\"/></td>
      </tr>
    </table>
    <input type=\"submit\" name=\"submit1\" value=\"Valider\" class=\"input\"></input></form></center>";
  }
}
?>
