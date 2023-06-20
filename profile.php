<?php

include "main.php";

if(getParametre("profil_private_connected") == 'yes')
{
	if(!checkuserConnected())
	{
		$useragent = $_SERVER["HTTP_USER_AGENT"];
		if(strpos($useragent,'Googlebot') != false)
		{
			// Si GoogleBot lui permettre l'indexation d'une page pour le référencement.
		}
		else
		{		
			header("Location: $url_script/connexion.php");
			exit;
		}
	}
}

if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	/* Ajout Topliste */
	if($action == 1)
	{
		$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();

		$md5_user = $rr['md5'];
		
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		
		$SQL = "INSERT INTO topliste (md5user,md5) VALUES ('$md5_user','$md5')";
		$pdo->query($SQL);
		
		header("Location: profile.php?md5=$md5");
		exit;		
	}
	/* Remove Topliste */
	if($action == 2)
	{
		$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();

		$md5_user = $rr['md5'];
		
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		
		$SQL = "DELETE FROM topliste WHERE md5user = '$md5_user' AND md5 = '$md5'";
		$pdo->query($SQL);
		
		header("Location: profile.php?md5=$md5");
		exit;		
	}
	/* Flasher */
	if($action == 3)
	{
		$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();

		$md5_user = $rr['md5'];
		
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		
		$SQL = "INSERT INTO flash (md5user,md5) VALUES ('$md5_user','$md5')";
		$pdo->query($SQL);
		
		header("Location: profile.php?md5=$md5");
		exit;	
	}
	/* Deflasher */
	if($action == 4)
	{
		$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();

		$md5_user = $rr['md5'];
		
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		
		$SQL = "DELETE FROM flash WHERE md5user = '$md5_user' AND md5 = '$md5'";
		$pdo->query($SQL);
		
		header("Location: profile.php?md5=$md5");
		exit;		
	}
}

$md5 = AntiInjectionSQL($_REQUEST['md5']);

$SQL = "SELECT COUNT(*) FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] == 0)
{
	/* Le compte n'existe pas */
	header("Location: $url_script/404.php");
	exit;
}

$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req['banni'] == 'oui')
{
	/* Compte banni */
	header("Location: $url_script/404.php");
	exit;
}

$idregion = $req['region'];
$pays = $req['pays'];

$type_sexe = $req['type'];

$SQL = "SELECT * FROM genre WHERE type = '$type_sexe'";
$r = $pdo->query($SQL);
$rr = $r->fetch();

$type_sexe = $rr['titre'];

$titrePays = updateTitleCountry($pays);

$idville = $req['ville'];
if($idville == '')
{
	$idville = 0;
}

$username = $req['username'];

$SQL = "SELECT * FROM region WHERE id = $idregion";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$titreRegion = $req['titre'];

$SQL = "SELECT * FROM ville WHERE id = $idville";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$titreVille = ucfirst($req['nom']);

$class_template_loader->showHeadSetSEO("Profil de ".$username." / $titrePays / $titreRegion / $titreVille",$title_seo_profil_de." ".$username." / $titrePays / $titreRegion / $titreVille");
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("profile.tpl");

$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$isConnected = checkConnected($md5);
if($isConnected)
{
	$class_template_loader->assign("{connected}",$url_script."/images/icon-connected.png");
	$class_template_loader->assign("{connected_title}",$title_user_connected);
}
else
{
	$class_template_loader->assign("{connected}",$url_script."/images/icon-disconnect.png");
	$class_template_loader->assign("{connected_title}",$title_user_disconnected);
}

$class_template_loader->assign("{sexe_profil}",$type_sexe);

/* On check si une photo existe */
$photourl = getPhoto($md5);

$cherche = $req['cherche'];
$SQL = "SELECT * FROM genre WHERE type = '$cherche'";
$r = $pdo->query($SQL);
$rr = $r->fetch();

$jecherche = $rr['jecherche'];

$class_template_loader->assign("{search}",$jecherche);

/* Cherche Age */

$cherche_age_depart = $req['cherche_age_depart'];
$cherche_age_fin = $req['cherche_age_fin'];

if($cherche_age_depart != '')
{
	if($cherche_age_fin != '')
	{
		$class_template_loader->assign("{search_age}","de $cherche_age_depart à $cherche_age_fin ans");
	}
	else
	{
		$class_template_loader->assign("{search_age}","de $cherche_age_depart");
	}
}
else
{
	$class_template_loader->assign("{search_age}","Age rechercher non specifié");
}

/* Cherche distance */

$cherche_distance = $req['cherche_distance'];
if($cherche_distance == '')
{
	$class_template_loader->assign("{search_distance}",$title_proche_de_moi);
}
if($cherche_distance == 'proche')
{
	$class_template_loader->assign("{search_distance}",$title_proche_de_moi);
}
if($cherche_distance == 'regionale')
{
	$class_template_loader->assign("{search_distance}",$title_dans_ma_region);
}
if($cherche_distance == 'nationale')
{
	$class_template_loader->assign("{search_distance}",$title_peu_importe_la_distance);
}

/* Pays */
$pays = $req['pays'];

$SQL = "SELECT * FROM pays WHERE valeur = '$pays'";
$r = $pdo->query($SQL);
$rr = $r->fetch();

$pays = $rr['nom'];
$pays_valeur = $rr['valeur'];

if($pays == '')
{
	$class_template_loader->assign("{pays}",'<i><font color="#737373" size=2>'.$title_pays_unknow.'</font></i>');
}
else
{
	$class_template_loader->assign("{pays}",'<img src="'.$url_script.'/images/flag/'.$pays_valeur.'.png" width=15> '.ucfirst($pays));
}

/* Region */
$region = $req['region'];

if($region != 0)
{
	$SQL = "SELECT * FROM region WHERE id = $region";
	$r = $pdo->query($SQL);
	$rr = $r->fetch();

	$titreRegion = $rr['titre'];
	$class_template_loader->assign("{region}",$titreRegion);
}
else
{
	$class_template_loader->assign("{region}",'<i><font color="#737373" size=2>'.$title_region_unknow.'</font></i>');
}

/* Ville */
$ville = $req['ville'];
if($ville == '')
{
	$class_template_loader->assign("{ville}",'<i><font color="#737373" size=2>'.$title_ville_unknow.'</font></i>');
}
else
{
	if($ville == '0')
	{
		$class_template_loader->assign("{ville}",'<i><font color="#737373" size=2>'.$title_ville_unknow.'</font></i>');
	}
	else
	{
		$SQL = "SELECT * FROM ville WHERE id = $ville";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		$class_template_loader->assign("{ville}",$rr['nom']);
	}
}

/* Galerie photo */

$galerie = '<div class="englobe-galerie-profil">';

$SQL = "SELECT * FROM galerie WHERE md5user = '$md5'";
$r = $pdo->query($SQL);
while($rr = $r->fetch())
{
	$galerie .= '<div class="photo-galerie"><a id="example1" href="'.$url_script.'/images/galerie/'.$rr['image'].'" target="galerie"><img src="'.$url_script.'/images/galerie/'.$rr['image'].'"></a></div>';
}

$galerie .= '</div>';

$class_template_loader->assign("{galerie}",$galerie);

/* Last connect */
$derniere_connexion = $req['derniere_connexion'];
$class_template_loader->assign("{last_connect}",$class_date->setTimestampToDate($derniere_connexion,$pattern_last_connexion));

/* On check que ce n'est pas nous */
$md5 = $req['md5'];
if($md5 == $_SESSION['md5'])
{
	/* Il s'agit du profil utilisateur */
	$profil_btn = '<div class="communication-box"></div>';
}
else
{
	if(checkuserConnected())
	{
		/* Si l'utilisateur est connecter on check que sont profil soit à 100% */
		$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		$pourcentage = $rr['pourcentage'];
		
		if(getParametre("user_profile_pourcentage_validate") == 'yes')
		{
			if($pourcentage == 100)
			{		
				$profil_btn = '<div class="communication-box">'."\n";
				$profil_btn .= '<div class="communication-box-btn"><a href="{sendmsglink}" class="btn"><i class="fas fa-comment-dots"></i> '.$title_btn_send_message.'</a></div>'."\n";
				if($req['webcam'] == 'yes')
				{
					$room = md5(microtime());
					$profil_btn .= '<div class="communication-box-btn"><a href="{url_script}/webcam.php?room='.$room.'&t=create&contact='.$md5.'#'.$room.'" class="btn"><i class="fas fa-video"></i> Contacter par Webcam</a></div>'."\n";
				}
				$profil_btn .= '</div>'."\n";
			}
			else
			{
				$profil_btn = '<div class="communication-box">'."\n";
				$profil_btn .= '<a href="javascript:void(0);" onclick="alert(\''.$error_msg_profil_not_full_complete.'\');" class="btn"><i class="fas fa-comment-dots"></i> '.$title_btn_send_message.'</a>'."\n";
				$profil_btn .= '</div>'."\n";
			}
		}
		else
		{
			$profil_btn = '<div class="communication-box">'."\n";
			$profil_btn .= '<div class="communication-box-btn"><a href="{sendmsglink}" class="btn"><i class="fas fa-comment-dots"></i> '.$title_btn_send_message.'</a></div>'."\n";
			if($req['webcam'] == 'yes')
			{
				$room = md5(microtime());
				$profil_btn .= '<div class="communication-box-btn"><a href="{url_script}/webcam.php?room='.$room.'&t=create&contact='.$md5.'#'.$room.'" class="btn"><i class="fas fa-video"></i> Contacter par Webcam</a></div>'."\n";
			}
			$profil_btn .= '</div>'."\n";
		}
	}
	else
	{
		$profil_btn = '<div class="communication-box">'."\n";
		$profil_btn .= '<a href="{sendmsglink}" class="btn"><i class="fas fa-comment-dots"></i> '.$title_btn_send_message.'</a>'."\n";
		$profil_btn .= '</div>'."\n";
	}
}

$class_template_loader->assign("{btn_message}",$profil_btn);
$class_template_loader->assign("{photo}",'<a id="example1" href="'.$photourl.'?seed='.md5(microtime()).'"><img src="'.$photourl.'?seed='.md5(microtime()).'" class="bigphoto"></a>');
$class_template_loader->assign("{pseudo}",ucfirst($req['username']));

/* On check si l'utilisateur est dans les favoris */
if(checkuserConnected())
{
	$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
	$r = $pdo->query($SQL);
	$rr = $r->fetch();

	$md5_user = $rr['md5'];
	
	/* On check si dans les favoris ou non de l'utilisateur */
	$SQL = "SELECT COUNT(*) FROM topliste WHERE md5user = '$md5_user' AND md5 = '$md5'";
	$r = $pdo->query($SQL);
	$rr = $r->fetch();
	
	if($rr[0] == 0)
	{
		$class_template_loader->assign("{favoris}",'<a href="'.$url_script.'/profile.php?md5='.$md5.'&action=1" title="'.$btn_favoris_add.'"><img src="'.$url_script.'/images/favoris-off.png"></a>');
	}
	else
	{
		$class_template_loader->assign("{favoris}",'<a href="'.$url_script.'/profile.php?md5='.$md5.'&action=2" title="'.$btn_favoris_remove.'"><img src="'.$url_script.'/images/favoris-on.png"></a>');
	}
	
	/* Flash */
	
	$SQL = "SELECT COUNT(*) FROM flash WHERE md5user = '$md5_user' AND md5 = '$md5'";
	$r = $pdo->query($SQL);
	$rr = $r->fetch();
	
	if($rr[0] == 0)
	{
		$class_template_loader->assign("{flash}",'<a href="'.$url_script.'/profile.php?md5='.$md5.'&action=3" title="'.$flash_on_title.' '.$username.'"><img src="'.$url_script.'/images/flash-off.png?seed='.md5(microtime()).'"></a>');
	}
	else
	{
		$class_template_loader->assign("{flash}",'<a href="'.$url_script.'/profile.php?md5='.$md5.'&action=4" title="'.$flash_off_title.' '.$username.'"><img src="'.$url_script.'/images/flash-on.png?seed='.md5(microtime()).'"></a>');
	}
	
	$class_template_loader->assign("{signaler}",'<a href="'.$url_script.'/signaler.php?md5='.$md5.'" class="signal-abus"><i class="fas fa-exclamation-triangle"></i> Signaler un abus</a>');
}
else
{
	$class_template_loader->assign("{favoris}","");
	$class_template_loader->assign("{flash}","");
	$class_template_loader->assign("{signaler}","");
}

if($req['age'] == '')
{
	$class_template_loader->assign("{age}",'<i><font color="#737373" size=2>'.$title_age_unknow.'</font></i>');
}
else
{
	$class_template_loader->assign("{age}",$req['age'].' '.$title_prefixe_age);
}

if($req['description'] == '')
{
	$class_template_loader->assign("{description}",'<i><font color="#737373">'.$title_description_unknow.'</font></i>');
}
else
{
	$class_template_loader->assign("{description}",$req['description']);
}

$array_filtre = NULL;

$SQL = "SELECT * FROM user_filtre WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($array_filtre == NULL)
	{
		$count = 0;
	}
	else
	{
		$count = count($array_filtre);
	}
	$array_filtre[$count]['idfiltre'] = $req['idfiltre'];
	$array_filtre[$count]['valeur'] = $req['valeur'];
}

$detail = "";

if($array_filtre == NULL)
{
	$detail = '<i><font color="#737373">'.$title_detail_unknow.'</font></i>';
}
else
{
	for($x=0;$x<count($array_filtre);$x++)
	{
		$SQL = "SELECT * FROM filtre_name WHERE id = ".$array_filtre[$x]['idfiltre'];
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$titre = $req['titre'];
		
		$SQL = "SELECT * FROM filtre WHERE id = ".$array_filtre[$x]['valeur'];
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$valeur = $req['parametre'];
		
		$detail .= '<div class="line-detail"><div class="line-title">'.$titre.'</div><div class="line-result">'.$valeur.'</div></div>';
	}
}

$class_template_loader->assign("{detail}",$detail);

if(!checkuserConnected())
{
	$sendmsglink = $url_script.'/connexion.php';
}
else
{
	$sendmsglink = $url_script.'/sendmsg.php?md5='.$md5;
}

$class_template_loader->assign("{url_script}",$url_script);

$class_template_loader->assign("{sendmsglink}",$sendmsglink);
$class_publicite->updatePublicite($class_template_loader);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>