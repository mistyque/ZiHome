<?
if (isset($_GET['vue'])) {
	$id = (int)$_GET['vue'];
	$query = 'SELECT * FROM vues WHERE id='.$id;
    $req = mysql_query($query, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
    while ($vue = mysql_fetch_assoc($req)) {
        $libelle=$vue['libelle'];
    }

    include("./lib/zibase.php");
	$zibase = new ZiBase($ipzibase);
	?>
	<title><? echo $libelle ?></title>
	<div id="global" style="position:relative;padding: 15px;margin: 15px;">
		<?	

		if(isset($_SESSION['auth'])) {
			$query3 = "SELECT * FROM vues_elements WHERE user = '".$_SESSION['auth']."' AND vue_id = '".$id."'";
		} else {
			$query3 = "SELECT * FROM vues_elements WHERE user = 'default' AND vue_id = '".$id."'";
		}
		$req3 = mysql_query($query3, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		$data = mysql_fetch_array($req3);
		if(empty($data)){
			$query3 = "SELECT * FROM vues_elements WHERE user = 'default' AND vue_id = '".$id."'";
		}
		$req3 = mysql_query($query3, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		while ($data3 = mysql_fetch_assoc($req3)) {
		    $width = $data3['width'];
		    $height = $data3['height'];
		    ?>
		    <div style="background-color: #fff;background-size:<? echo $data3['width']; ?>px <? echo $data3['height']; ?>px;background-repeat:no-repeat;width: <? echo $data3['width']; ?>px;height: <? echo $data3['height']; ?>px;top: <? echo $data3['top']; ?>px;left: <? echo $data3['left']; ?>px;border: solid <? echo $data3['border']; ?>px #CCC;position: absolute;z-index: <? echo $data3['id']; ?>;color: black;font-size: 20px;<? echo $data3['option']; ?>;">
		      <? 
		      $query = "SELECT * FROM peripheriques WHERE id = '".$data3['peripherique']."'";
		      $req = mysql_query($query, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		      while ($data1 = mysql_fetch_assoc($req))
		      {
		        $periph = $data1;
		      }
		      $query = "SELECT * FROM scenarios WHERE nom = '".$data3['peripherique']."'"; // les scenarios sont appelés par leur nom et pas leur id (car la zibase modifie l'id à chaque suppression de scénario)
		      $req = mysql_query($query, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		      while ($data1 = mysql_fetch_assoc($req))
		      {
		        $periph = $data1;
		      }
		      include("./fonctions/".$data3['url'].".php"); 
		      ?>
		    </div>
		    <?
		}
		  
		// -----------------------------------------------------------------------------
		// Gestion des stickers et des textes dynamiques
		// -----------------------------------------------------------------------------
		  
		include("./fonctions/dynaInfo.php");
		generateDynInfo($id, "#global", "");

		// -----------------------------------------------------------------------------
		// Gestion des cadres et des rapports (équiv mode plan)
		// -----------------------------------------------------------------------------
		// Calcul de la largeur max et hauteur max
		$query = "SELECT max( `width` + `left` ) AS width, max( `height` + `top` ) AS height FROM `vues_elements` WHERE `vue_id`= '".$id."' AND `id`= 'cadre'";
		$res_query = mysql_query($query, $link);
		if (mysql_numrows($res_query) > 0){
	    	$width = mysql_result($res_query,0,"width") + 2;
	    	$height = mysql_result($res_query,0,"height") + 2;
		}

		// Recuperation de la largeur des icones
		$query = "SELECT * FROM paramettres WHERE libelle = 'largeur icones'";
		$res_query = mysql_query($query, $link);
		if (mysql_numrows($res_query) > 0) {
			$data = mysql_fetch_assoc($res_query);
			$widthIcones = $data['value'];
			$labelOffsetLeft = max($widthIcones - 30, $widthIcones / 2);
		} else {
			$widthIcones = 60;
			$labelOffsetLeft = 30;
		}

		// Recuperation de la hauteur des icones
		$query = "SELECT * FROM paramettres WHERE libelle = 'hauteur icones'";
		$res_query = mysql_query($query, $link);
		if (mysql_numrows($res_query) > 0) {
			$data = mysql_fetch_assoc($res_query);
			$heightIcones = $data['value'];
		} else {
			$heightIcones = 60;
		}
		if ($heightIcones < 40) {
			$labelWidth = 30;
			$labelOffsetTop = $heightIcones - 13;
			$labelFontSize = 8;
			$labelFontOffsetTop = 1;
			$labelFontOffsetLeft = 3; 
		} else {
			$labelWidth = 50;
			$labelOffsetTop = $heightIcones - 22; 
			$labelFontSize = 12;
			$labelFontOffsetTop = 3;
			$labelFontOffsetLeft = 6;
		}

		// Recuperation de la hauteur des icones
		$query = "SELECT * FROM paramettres WHERE id = 6";
		$res_query = mysql_query($query, $link);
		$data = mysql_fetch_assoc($res_query);
		$showAllNames = false;
		if ($data['value'] == 'true') {
			$showAllNames = true;
		}
		$image_fond = "";
		if(file_exists("img/plan/jour.png")) {
		  $image_fond = "img/plan/jour.png";
		}
		$weather = simplexml_load_file("http://wxdata.weather.com/wxdata/weather/local/".$meteo_ville."?cc=*&unit=m");
		if(file_exists("img/plan/nuit.png")) {
	  		$soleil_jour = date_create_from_format('h:i a Y-m-d', $weather->loc->sunr." ".date('Y-m-d'));
	  		$soleil_nuit = date_create_from_format('h:i a Y-m-d', $weather->loc->suns." ".date('Y-m-d'));
	  		$now = date_create_from_format('h:i a Y-m-d', date('h:i a Y-m-d'));
	  		if($now<$soleil_nuit && $now>$soleil_jour) { 
	    		$soleil = "jour"; 
	    		$image_fond = "img/plan/jour.png"; 
	    	} else { 
	    		$soleil = "nuit"; 
	    		$image_fond = "img/plan/nuit.png"; 
			}
		} else {
			$soleil = "jour";
		}

		

		

		function showTechnicalStatus($sqlData) {
			if ($sqlData['batterie']) {
	    		global $heightIcones;
	    		echo "<img src='./img/batterie_ko_highlight.png' height='".($heightIcones / 2)."px' style=\"position:absolute;top:".($heightIcones / 2)."px;left:0px;\"/>";
	  		}
	  		if ($sqlData['erreur']) {
	    		global $heightIcones;
	    		echo "<img src='./img/error.png' height='".($heightIcones / 2)."px' style=\"position:absolute;top:0px;left:0px;\"/>";
			}
		}

		function showIconSimple($sqlPiece, $sqlData, $status, $url) {
		  global $icone;
		  global $widthIcones;
		  global $heightIcones;
		  global $labelWidth;
		  global $labelOffsetLeft;
		  global $labelOffsetTop;
		  global $labelFontOffsetTop;
		  global $labelFontOffsetLeft;
		  global $labelFontSize;
		  
		  echo "<div style=\"position:absolute;top:".($sqlPiece['top'] + $sqlData['top'])."px;left:".($sqlPiece['left'] + $sqlData['left'])."px;border-style:none;z-index:300;\">";
		  echo "<a href=\"".$url."\">";
		  echo "<img src=\"./img/icones/".$icone.$status."_".$sqlData['logo']."\" width=\"".$widthIcones."\" heigth=\"".$heightIcones."\" style=\"position:absolute;top:0px;left:0px;border-style:none;\">";
		  showTechnicalStatus($sqlData);
		  echo "</a>";
		  echo "</div>";
		  
		  if ($sqlData['texte'])
		  {
		    if($sqlData['libelle'] == ""){
		      $nom = $sqlData['nom'];
		    } else {
		      $nom = $sqlData['libelle'];
		    }
		    echo "<div style=\"position:absolute;top:".($sqlPiece['top'] + $sqlData['top'] + $heightIcones)."px;left:". ($sqlPiece['left'] + $sqlData['left'] - 10)."px;width:".($widthIcones + 20)."px;padding:2px;font-size:".$labelFontSize."px;font-weight:bold;font-family:sans-serif;border-style:none;color: black;background-color:rgba(255, 255, 255, 0.7);text-align:center;z-index:300;\">".$nom."</div>";
		  }
		}

		function showIcon($sqlPiece, $sqlData, $valeur1, $unite1, $valeur2, $unite2, $url) {
		  global $icone;
		  global $widthIcones;
		  global $heightIcones;
		  global $labelWidth;
		  global $labelOffsetLeft;
		  global $labelOffsetTop;
		  global $labelFontOffsetTop;
		  global $labelFontOffsetLeft;
		  global $labelFontSize;
		  
		  echo "<div style=\"position:absolute;top:".($sqlPiece['top'] + $sqlData['top'])."px;left:".($sqlPiece['left'] + $sqlData['left'])."px;border-style:none;z-index:300;\">";
		  echo "<a href=\"".$url."\">";
		  echo "<img src=\"./img/icones/".$icone."c_".$sqlData['logo']."\" width=\"".$widthIcones."\" heigth=\"".$heightIcones."\" style=\"position:absolute;top:0px;left:0px;border-style:none;\">";
		  if ($valeur1 != "") {
		    echo "<img src=\"./img/icones/".$icone."AndroidNumberYellow.png\" width=\"".$labelWidth."\" style=\"position:absolute;top:0px;left:".$labelOffsetLeft."px;border-style:none;\">";
		    echo "<span style=\"position:absolute;top:".$labelFontOffsetTop."px;left:".($labelOffsetLeft + $labelFontOffsetLeft)."px;color: black;font-size:".$labelFontSize."px;font-weight:bold;border-style:none;\">".$valeur1.$unite1."</span>";
		  }
		  if ($sqlData['show_value2'])
		  {
		    echo "<img src=\"./img/icones/".$icone."AndroidNumberOther.png\" width=\"".$labelWidth."\" style=\"position:absolute;top:".$labelOffsetTop."px;left:".$labelOffsetLeft."px;border-style:none;\">";
		    echo "<span style=\"position:absolute;top:".($labelOffsetTop + $labelFontOffsetTop)."px;left:".($labelOffsetLeft + $labelFontOffsetLeft)."px;color: black;font-size:".$labelFontSize."px;font-weight:bold;border-style:none;\">". $valeur2 . $unite2."</span>";
		  }
		  showTechnicalStatus($sqlData);
		  echo "</a>";
		  echo "</div>";
		  
		  if ($sqlData['texte'])
		  {
		    if($sqlData['libelle'] == ""){
		      $nom = $sqlData['nom'];
		    } else {
		      $nom = $sqlData['libelle'];
		    }
		    echo "<div style=\"position:absolute;top:".($sqlPiece['top'] + $sqlData['top'] + $heightIcones)."px;left:". ($sqlPiece['left'] + $sqlData['left'] - 10)."px;width:".($widthIcones + 20)."px;padding:2px;font-size:".$labelFontSize."px;font-weight:bold;font-family:sans-serif;border-style:none;color: black;background-color:rgba(255, 255, 255, 0.7);text-align:center;z-index:300;\">".$nom."</div>";
		  }
		}
		?>

	<!-- <div id="plan" style="position: relative;padding: 15px;margin: auto;height: <? echo $height; ?>px;width: <? echo $width; ?>px;background-color: #ffffff;background-Position: center center;background:url(<? echo $image_fond; ?>);"> -->
	    <? 
	    // Affichage des cadres si nécessaires dans cette vue    
	    $query = "SELECT * FROM `vues_elements` WHERE `type`='cadre' AND `vue_id` ='".$id."';";
	    $req = mysql_query($query, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
	    while ($data = mysql_fetch_assoc($req)) {
	        $img = "";
	        if(!empty($data['url']) && file_exists("./img/plan/".$data['url'])) {
	          $img = "./img/plan/".$data['url'];
	        }
	        ?>
	        <a href="javascript:showPopup('custom<? echo $data['id']; ?>');">
		        <div style="background-color: #fff;background:url('<? echo $img; ?>');background-size:<? echo $data['width']; ?>px <? echo $data['height']; ?>px;background-repeat:no-repeat;width: <? echo $data['width']; ?>px;height: <? echo $data['height']; ?>px;top: <? echo $data['top']; ?>px;left: <? echo $data['left']; ?>px;border: solid <? echo $data['border']; ?>px #777;position: absolute;z-index: <? echo $data['id']; ?>;color: black;font-size: 20px;text-align: <? echo $data['text-align']; ?>;<? echo $data['supplementaire']; ?>;">
		        <?
		        if ($showAllNames and $data['show-libelle']) {
		            echo '<div style="line-height: '. $data['line-height'] . 'px;">'.$data['libelle'].'</div>';
		        }
		        echo '</div>';  

		        // il faudrait que l'id du cadre enregistré dans vues_elements corresponde à l'ancien id du plan = plan.id -> vues_elements

				// ----- Capteur
		            $query4 = "SELECT * FROM peripheriques WHERE periph = 'capteur' AND id_plan = '".$data['id']."' AND icone ='1'";
		            $req4 = mysql_query($query4, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		            while($data6 = mysql_fetch_assoc($req4)) {
		                if($data6['protocol'] == 6) {
		                  $protocol = true;
		                } else {
		                    $protocol = false;
		                }
		                if($protocol == true) {
		                    $value = $zibase->getState(substr($data6['id'], 1), $protocol);
		                } else {
		                    $value = $zibase->getState($data6['id'], $protocol);
		                }
		                if($value == "1") {
		                    $ic = "c";
		                } else {
		                    $ic = "g";
		                }
		                showIconSimple($data, $data6, $ic, "");
		            }

				// ----- Actionneur
		            $query6 = "SELECT * FROM peripheriques WHERE periph = 'actioneur' AND id_plan = '".$data['id']."' AND icone ='1'";
		            $req6 = mysql_query($query6, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		            while($data8 = mysql_fetch_assoc($req6)) {
		                if($data8['protocol'] == 6) {
		                    $protocol = true;
		                } else {
		                    $protocol = false;
		                }
		                $value = $zibase->getState($data8['id'], $protocol);
		                if($value == 1) {
		                    $ic = "c";
		                } else {
		                    $ic = "g";
		                }      
		                $url = "javascript:showPopupTab('custom".$data['id']."', '#tabs-".$data['id']."', '#tabs-".$data['id']."-2');";
		                showIconSimple($data, $data8, $ic, $url);
		            }

				// ----- Temperature
		            $query7 = "SELECT * FROM peripheriques WHERE periph = 'temperature' AND id_plan = '".$data['id']."' AND icone ='1'";
		            $req7 = mysql_query($query7, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		            while($data9 = mysql_fetch_assoc($req7)) 
		            {
		                $query0 = "SELECT * FROM `temperature_".$data9['nom']."` ORDER BY `date` DESC LIMIT 1";
		                $req0 = mysql_query($query0, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                if ($req0 && mysql_numrows($req0) > 0)
		                {
		                  $data0 = mysql_fetch_assoc($req0);
		                  $temperature=$data0['temp'];
		                  $hygro=$data0['hygro'];
		                }
		                else
		                {
		                  $temperature = ""; 
		                  $hygro = ""; 
		                }
		                $url = "javascript:showPopupTab('custom".$data['id']."', '#tabs-".$data['id']."', '#tabs-".$data['id']."-1');";
		                showIcon($data, $data9, $temperature, "&deg;", $hygro, "%", $url);
		            }
				
				// ----- Conso electrique
		            $query7 = "SELECT * FROM peripheriques WHERE periph = 'conso' AND id_plan = '".$data['id']."' AND icone ='1'";
		            $req7 = mysql_query($query7, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		            while($data9 = mysql_fetch_assoc($req7)) {
		              $query0 = "SELECT * FROM `conso_".$data9['nom']."` ORDER BY `date` DESC LIMIT 1";
		              $req0 = mysql_query($query0, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		              if ($req0 && mysql_numrows($req0) > 0)
		              {
		                $data0 = mysql_fetch_assoc($req0);            
		                $valeur=$data0['conso'];
		              }
		              else
		              {
		                $valeur = "";
		              }
		              $url = "javascript:showPopupTab('custom".$data['id']."', '#tabs-".$data['id']."', '#tabs-".$data['id']."-3');";
		              showIcon($data, $data9, $valeur, "", "", "", $url);
		            }

				// ----- Conso eau
		            $query7 = "SELECT * FROM peripheriques WHERE periph = 'eau' AND id_plan = '".$data['id']."' AND icone ='1'";
		            $req7 = mysql_query($query7, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		            while($data9 = mysql_fetch_assoc($req7)) {
		              $url = "javascript:showPopupTab('custom".$data['id']."', '#tabs-".$data['id']."', '#tabs-".$data['id']."-9');";
		              showIcon($data, $data9, "", "", "", "", $url);
		            }

				// ----- Vent
		            $query7 = "SELECT * FROM peripheriques WHERE periph = 'vent' AND id_plan = '".$data['id']."' AND icone ='1'";
		            $req7 = mysql_query($query7, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		            while($data9 = mysql_fetch_assoc($req7)) {
		              $query0 = "SELECT * FROM `vent_".$data9['nom']."` ORDER BY `date` DESC LIMIT 1";
		              $req0 = mysql_query($query0, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		              if ($req0 && mysql_numrows($req0) > 0)
		              {
		                $data0 = mysql_fetch_assoc($req0);
		                $valeur=$data0['vitesse']/10;
		              }
		              else
		              {
		                $valeur = "";
		              }
		              $url = "javascript:showPopupTab('custom".$data['id']."', '#tabs-".$data['id']."', '#tabs-".$data['id']."-6');";
		              showIcon($data, $data9, $valeur, "", "", "", $url);
		            }

				// ----- Pluie            
		            $query7 = "SELECT * FROM peripheriques WHERE periph = 'pluie' AND id_plan = '".$data['id']."' AND icone ='1'";
		            $req7 = mysql_query($query7, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		            while($data9 = mysql_fetch_assoc($req7)) {
		              $query0 = "SELECT * FROM `pluie_".$data9['nom']."` ORDER BY `date` DESC LIMIT 1";
		              $req0 = mysql_query($query0, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		              if ($req0 && mysql_numrows($req0) > 0)
		              {
		                $data0 = mysql_fetch_assoc($req0);
		                $valeur=$data0['pluie'];
		              }
		              else
		              {
		                $valeur = "";
		              }
		              $url = "javascript:showPopupTab('custom".$data['id']."', '#tabs-".$data['id']."', '#tabs-".$data['id']."-7');";
		              showIcon($data, $data9, $valeur, "", "", "", $url);
		            }

				// ----- Luminosite            
		            $query7 = "SELECT * FROM peripheriques WHERE periph = 'luminosite' AND id_plan = '".$data['id']."' AND icone ='1'";
		            $req7 = mysql_query($query7, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		            while($data9 = mysql_fetch_assoc($req7)) {
		              $query0 = "SELECT * FROM `luminosite_".$data9['nom']."` ORDER BY `date` DESC LIMIT 1";
		              $req0 = mysql_query($query0, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		              if ($req0 && mysql_numrows($req0) > 0)
		              {
		                $data0 = mysql_fetch_assoc($req0);
		                $valeur=$data0['lum'];
		              }
		              else
		              {
		                $valeur = "";
		              }
		              $url = "javascript:showPopupTab('custom".$data['id']."', '#tabs-".$data['id']."', '#tabs-".$data['id']."-8');";
		              showIcon($data, $data9, $valeur, "", "", "", $url);
		            }

		        ?>
	        </a>
	    	<script type="text/javascript">
		        $(document).ready(function() {
		            $("#tabs-<? echo $data['id']; ?>").tabs();
		        });
		    </script>
		    <div id="custom<? echo $data['id']; ?>" style="position: fixed;display: none;left: 50%;top: 50%;z-index: 2000;padding: 10px;width:640px;max-height:90%;background-color: #EEEEEE;font-size: 12px;line-height: 16px;color: #202020;border : 3px outset #555555;">
		        <div id="tabs-<? echo $data['id']; ?>" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
		            <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" style="width: 640px;">
		                <?
		                if(!($data3 == null)){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-1">Temp&eacute;rature</a></li>
		                <?
		                }
		                if((!($data4 == null)) && isset($_SESSION['auth'])){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-2">Actionneur</a></li>
		                <? 
		                } 
		                if(!($data5 == null)){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-3">Conso Elec</a></li>
		                <?
		                }
		                if((!($data7 == null)) && (isset($_SESSION['auth']))){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-4">Sc&eacute;nario</a></li>
		                <?
		                }
		                if((!($data11 == null)) && (isset($_SESSION['auth']))){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-5">Vid&eacute;o</a></li>
		                <?
		                }
		                if ($data12 != null){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-6">Vent</a></li>
		                <?
		                }
		                if ($data13 != null){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-7">Pr&eacute;cipitation</a></li>
		                <?
		                }
		                if ($data14 != null){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-8">Luminosit&eacute;</a></li>
		                <?
		                }
		                if ($data18 != null){
		                ?>
		                <li class="ui-state-default ui-corner-top"><a href="#tabs-<? echo $data['id']; ?>-9">Conso Eau</a></li>
		                <?
		                }
		                ?>
		            </ul>
		            <?
		            if(!($data3 == null)){
		              ?>
		              <div id="tabs-<? echo $data['id']; ?>-1" class="ui-tabs-panel ui-widget-content ui-corner-bottom" style="overflow:auto;max-height:600px;">
		                <?
		                $query1 = "SELECT * FROM peripheriques WHERE periph = 'temperature' AND id_plan = '".$data['id']."' AND graphique = '1'";
		                $req1 = mysql_query($query1, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                while($periph = mysql_fetch_assoc($req1)) {
		                  $width = "640px";
		                  $height = "340px";
		                  include("./fonctions/temperature_graph_jour.php");
		                }
		                ?>
		              </div>
		              <?
		            }
		            if(!($data12 == null)){
		              ?>
		              <div id="tabs-<? echo $data['id']; ?>-6" class="ui-tabs-panel ui-widget-content ui-corner-bottom" style="overflow:auto;max-height:600px;">
		                <?
		                $query1 = "SELECT * FROM peripheriques WHERE periph = 'vent' AND id_plan = '".$data['id']."' AND graphique = '1'";
		                $req1 = mysql_query($query1, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                while($periph = mysql_fetch_assoc($req1)) {
		                  $width = "640px";
		                  $height = "340px";
		                  include("./fonctions/vent_graph_jour.php");
		                }
		                ?>
		              </div>
		              <?
		            }
		            if(!($data13 == null)){
		              ?>
		              <div id="tabs-<? echo $data['id']; ?>-7" class="ui-tabs-panel ui-widget-content ui-corner-bottom" style="overflow:auto;max-height:600px;">
		                <?
		                $query1 = "SELECT * FROM peripheriques WHERE periph = 'pluie' AND id_plan = '".$data['id']."' AND graphique = '1'";
		                $req1 = mysql_query($query1, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                while($periph = mysql_fetch_assoc($req1)) {
		                  $width = "640px";
		                  $height = "340px";
		                  include("./fonctions/pluie_graph_global.php");
		                }
		                ?>
		              </div>
		              <?
		            }            
		            if(!($data14 == null)){
		              ?>
		              <div id="tabs-<? echo $data['id']; ?>-8" class="ui-tabs-panel ui-widget-content ui-corner-bottom" style="overflow:auto;max-height:600px;">
		                <?
		                $query1 = "SELECT * FROM peripheriques WHERE periph = 'luminosite' AND id_plan = '".$data['id']."' AND graphique = '1'";
		                $req1 = mysql_query($query1, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                while($periph = mysql_fetch_assoc($req1)) {
		                  $width = "640px";
		                  $height = "340px";
		                  include("./fonctions/luminosite_graph_jour.php");
		                }
		                ?>
		              </div>
		              <?
		            }            
		            if(isset($_SESSION['auth'])) {
		              if(!($data4 == null)){
		              $query2 = "SELECT * FROM peripheriques WHERE periph = 'actioneur' AND id_plan = '".$data['id']."'";
		              $req2 = mysql_query($query2, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		              ?>
		              <div id="tabs-<? echo $data['id']; ?>-2" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" style="overflow:auto;max-height:600px;">
		                <?
		                while($periph = mysql_fetch_assoc($req2)) {
		                  echo "<div id=\"actionneur\">";
		                  include("./fonctions/actioneur.php");
		                  echo "</div>";
		                }
		                ?> 
		              </div>
		              <?
		              }
		            }
		            if((!($data7 == null)) && (isset($_SESSION['auth']))){
		              ?>
		              <div id="tabs-<? echo $data['id']; ?>-4" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" style="overflow:auto;max-height:600px;">
		                <br>
		                <br>
		                <?
		                $query5 = "SELECT * FROM scenarios WHERE id_plan = '".$data['id']."'";
		                $req5 = mysql_query($query5, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                while($periph = mysql_fetch_assoc($req5)) {
		                  echo "<div id=\"actionneur\">";
		                  include("./fonctions/scenario.php");
		                  echo "</div>";
		                }
		               	echo "</div>"; 
		            }
		            if((!($data11 == null)) && (isset($_SESSION['auth']))){
		              ?>
		              <div id="tabs-<? echo $data['id']; ?>-5" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" style="overflow:auto;max-height:600px;">
		                <?
		                $query11 = "SELECT * FROM video WHERE id_plan = '".$data['id']."'";
		                $req11 = mysql_query($query11, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                while ($periph = mysql_fetch_assoc($req11)) {
		                  $width=$periph['width'];
		                  $libelle = $periph['libelle'];
		                  $fps = 0;
		                  $delai_tentative=0;
		                  $adloc=$periph['adresse'];
		                  $adweb=$periph['adresse_internet'];
		                  include("./fonctions/video.php");
		                }
		                echo "</div>";
		            }
		            if(!($data5 == null)){
		              ?>
		                <div id="tabs-<? echo $data['id']; ?>-3" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" style="overflow:auto;max-height:600px;">
		                  <?
		                  $query1 = "SELECT * FROM peripheriques WHERE periph = 'conso' AND id_plan = '".$data['id']."' AND graphique = '1'";
		                  $req1 = mysql_query($query1, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                  while($periph = mysql_fetch_assoc($req1)) {
		                    $width = "640px";
		                    $height = "340px";
		                    include("./fonctions/conso_elec_graph_mois.php");
		                  }
		                  ?>
		                </div>
		              <?
		            }
		            if(!($data18 == null)){
		              ?>
		                <div id="tabs-<? echo $data['id']; ?>-9" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" style="overflow:auto;max-height:600px;">
		                  <?
		                  $query1 = "SELECT * FROM peripheriques WHERE periph = 'eau' AND id_plan = '".$data['id']."' AND graphique = '1'";
		                  $req1 = mysql_query($query1, $link) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		                  while($periph = mysql_fetch_assoc($req1)) {
		                    $width = "640px";
		                    $height = "340px";
		                    $graphInterval = 3;
		                    include("./fonctions/conso_eau_graph.php");
		                  }
		                  ?>
		                </div>
		              <?
		            }
		            ?>
		        </div>
		    </div>
	        <? 
		}

		// -------------------------------------------------------------- fin plan ----------------------------------------------

		
		// Affichage icone(s) METEO si nécessaire dans cette vue
		$meteoIcon = $weather->cc->icon;
		$query= "SELECT * FROM `vues_elements` WHERE `type`='meteo' AND `vue_id`=".$id;
	    $req=mysql_query($query, $link);
	    while ($met = mysql_fetch_assoc($req)) {
	    	$meteoIconFolder = $met['fichier'];
			$meteoIconLeft = $met['left'];
			$meteoIconTop = $met['top'];
			$meteoIconWidth = $met['width'];
			$meteoIconHeight = $met['height'];
	    	echo '<img src="./img/meteo/' . $meteoIconFolder . '/' . $meteoIcon . '.png" style="position:absolute;top:' . $meteoIconTop . 'px;left:' . $meteoIconLeft . 'px;';
	        if ($meteoIconHeight) { echo 'height:' . $meteoIconHeight . 'px;'; }
	        if ($meteoIconWidth) {echo 'width:' . $meteoIconWidth . 'px;'; }
	        echo 'z-index:300"/>';
	    }


	    // Affichage icone(s) pollution si nécessaire dans cette vue
	    $query= "SELECT * FROM `vues_elements` WHERE `type`='pollution' AND `vue_id`=".$id;
	    $req=mysql_query($query, $link);
	    while ($poll = mysql_fetch_assoc($req)) {
			$pollutionIconLeft = $poll['left'];
			$pollutionIconTop = $poll['top'];
			$pollutionIconWidth = $poll['width'];
			$pollutionIconHeight = $poll['height'];
			$query = "SELECT * FROM pollution order by date DESC limit 1";
			$res_query = mysql_query($query, $link);
			if (mysql_numrows($res_query) > 0) {
				$pollution = mysql_fetch_assoc($res_query);
	        	echo '<img src="./img/pollution/Pollution' . $pollution['Indice'] . '.png" title=" <table align=\'center\'>';
	        	echo '<tr><td><b> Indice : </b></td><td><b>'. $pollution['Indice'] . '</b></td></tr>';
	        	echo '<tr><td> O3 : </td><td>'. $pollution['O3'] . '</td></tr>';
	        	echo '<tr><td> NO2 : </td><td>'. $pollution['NO2'] . '</td></tr>';
	        	echo '<tr><td> PM10 : </td><td>'. $pollution['PM10'] . '</td></tr>';
	        	echo '<tr><td> SO2 : </td><td>'. $pollution['SO2'] . '</td></tr></table>"';
	        	echo 'style="position:absolute;top:' . $pollutionIconTop . 'px;left:' . $pollutionIconLeft . 'px; height:' . $pollutionIconHeight . 'px; width:' . $pollutionIconWidth . 'px; z-index:300"/>';
	    	}
		}
	   


		?>
	</div>

	<script src="./js/highstock.js"></script>
	<script src="./config/conf_highstock.js"></script>
	<script src="./js/highcharts.js"></script>
	<script src="./js/highcharts-more.js"></script>
	<script src="./js/modules/data.js"></script>
	<script src="./config/conf_highcharts.js"></script>
	<script src="./js/modules/exporting.js"></script> 
	<?
}
?>