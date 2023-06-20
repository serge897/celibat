<?php

include "main.php";

if(!checkuserConnected())
{
	header("Location: connexion.php");
	exit;
}

$class_template_loader->showHead('autourdemoi');
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("autourdemoi.tpl");

if(isset($_REQUEST['sexe']))
{
	$sexe = AntiInjectionSQL($_REQUEST['sexe']);
	if($sexe == 'all')
	{
		$addsexe = "";
	}
	else
	{
		$addsexe = "AND type = '$sexe'";
	}
}
else
{
	$sexe = "";
	$addsexe = "";
}

if(isset($_REQUEST['agea']))
{
	$agea = AntiInjectionSQL($_REQUEST['agea']);
	if($agea == 0)
	{
		$addage = "";
	}
	else
	{
		$addage = "AND age >= $agea";
	}
}
else
{
	$agea = 0;
	$addage = "";
}

if(isset($_REQUEST['ageb']))
{
	$ageb = AntiInjectionSQL($_REQUEST['ageb']);
	if($ageb == 0)
	{
		$addage2 = "";
	}
	else
	{
		$addage2 = "AND age <= $ageb";
	}
}
else
{
	$ageb = 0;
	$addage2 = "";
}

if(isset($_REQUEST['distance']))
{
	$distance = AntiInjectionSQL($_REQUEST['distance']);
}
else
{
	$distance = "";
}

if(isset($_REQUEST['region']))
{
	$region = AntiInjectionSQL($_REQUEST['region']);
	if($region == 0)
	{
		$addregion = "";
	}
	else
	{
		$addregion = "AND region = $region";
	}
}
else
{
	$region = "";
	$addregion = "";
}

$distance_option = NULL;

if($distance == 10)
{
	$distance_option .= '<option value="10" selected>10 Km</option>';
}
else
{
	$distance_option .= '<option value="10">10 Km</option>';
}
if($distance == 25)
{
	$distance_option .= '<option value="25" selected>25 Km</option>';
}
else
{
	$distance_option .= '<option value="25">25 Km</option>';
}
if($distance == 50)
{
	$distance_option .= '<option value="50" selected>50 Km</option>';
}
else
{
	$distance_option .= '<option value="50">50 Km</option>';
}
if($distance == 100)
{
	$distance_option .= '<option value="100" selected>100 Km</option>';
}
else
{
	$distance_option .= '<option value="100">100 Km</option>';
}
if($distance == 200)
{
	$distance_option .= '<option value="200" selected>200 Km</option>';
}
else
{
	$distance_option .= '<option value="200">200 Km</option>';
}
if($distance == 300)
{
	$distance_option .= '<option value="300" selected>300 Km</option>';
}
else
{
	$distance_option .= '<option value="300">300 Km</option>';
}
if($distance == 400)
{
	$distance_option .= '<option value="400" selected>400 Km</option>';
}
else
{
	$distance_option .= '<option value="400">400 Km</option>';
}
if($distance == 500)
{
	$distance_option .= '<option value="500" selected>500 Km</option>';
}
else
{
	$distance_option .= '<option value="500">500 Km</option>';
}

$class_template_loader->assign("{distance_option}",$distance_option);

$genre_option = NULL;
if($sexe == 'all')
{
	$genre_option .= '<option value="all" selected>'.$list_search_toute.'</option>';
}
else
{
	$genre_option .= '<option value="all">'.$list_search_toute.'</option>';
}

$SQL = "SELECT * FROM genre";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($sexe == $req['type'])
	{
		$genre_option .= '<option value="'.$req['type'].'" selected>'.$req['titre'].'</option>';
	}
	else
	{
		$genre_option .= '<option value="'.$req['type'].'">'.$req['titre'].'</option>';
	}
}

$class_template_loader->assign("{genre_option}",$genre_option);

$age_option = NULL;
if($agea == 0)
{
	$age_option .= '<option value="0" selected>'.$list_search_age.'</option>';
}
else
{
	$age_option .= '<option value="0">'.$list_search_age.'</option>';
}

for($x=18;$x<99;$x++)
{
	if($agea == $x)
	{
		$age_option .= '<option value="'.$x.'" selected>'.$x.' '.$title_prefixe_age.'</option>';
	}
	else
	{
		$age_option .= '<option value="'.$x.'">'.$x.' '.$title_prefixe_age.'</option>';
	}
}

$class_template_loader->assign("{agea_option}",$age_option);

$age_option = NULL;
if($ageb == 0)
{
	$age_option .= '<option value="0" selected>'.$list_search_age.'</option>';
}
else
{
	$age_option .= '<option value="0">'.$list_search_age.'</option>';
}

for($x=18;$x<99;$x++)
{
	if($ageb == $x)
	{
		$age_option .= '<option value="'.$x.'" selected>'.$x.' '.$title_prefixe_age.'</option>';
	}
	else
	{
		$age_option .= '<option value="'.$x.'">'.$x.' '.$title_prefixe_age.'</option>';
	}
}

$class_template_loader->assign("{ageb_option}",$age_option);

/* Resultat */

$result = NULL;

$class_template_user_result = new TemplateLoader();
$class_template_user_result->loadTemplate("result-user.tpl");

/* On recupere les info de l'utilisateur connecter */
$SQL = "SELECT * FROM user WHERE md5 = '".$_SESSION['md5']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$iduser = $req['id'];
$idville = $req['ville'];

$SQL = "SELECT * FROM ville WHERE id = $idville";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$longitude = $req['longitude'];
$latitude = $req['latitude'];

if($req['nom'] == '')
{
	$class_template_loader->assign("{ville}",'<b><font color=red>'.$title_ville_unknow.'</font></b>');
}
else
{
	$class_template_loader->assign("{ville}",'<b>'.$req['nom'].'</b>');
}
$class_template_loader->assign("{codepostal}",$req['codepostal']);
$class_template_loader->assign("{url_script}",$url_script);

$SQL = "SELECT COUNT(*) FROM user WHERE compte_valider = 'oui' AND banni = 'non' $addsexe $addage $addage2";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$count = $req[0];

if($count == 0)
{
	$result = '<div class="unknow-result"><i class="fas fa-search"></i><br>'.$no_result_search.'</div>';
}
else
{
	$SQL = "SELECT * FROM user WHERE compte_valider = 'oui' AND banni = 'non' $addsexe $addage $addage2";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		/* On check la ville de l'utilisateur */
		$villeid = $req['ville'];
		$userid = $req['id'];
		
		if($villeid != 0)
		{		
			$SQL = "SELECT * FROM ville WHERE id = $villeid";
			$r = $pdo->query($SQL);
			$rr = $r->fetch();
			$lat = $rr['latitude'];
			$long = $rr['longitude'];
			
			$dst = $class_geolocalisation->distance($latitude, $longitude, $lat, $long, "K");
			if($dst < $distance)
			{
				if($iduser != $userid)
				{
					$class_template_user_result->reload();
					$class_template_user_result->assign("{user_url}",$url_script.'/profile.php?md5='.$req['md5']);
					
					/* On check si une image existe */
					if(file_exists('images/photo/'.$req['md5'].'.jpg'))
					{
						$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/photo/'.$req['md5'].'-thumb.jpg">');
					}
					else if(file_exists('images/photo/'.$req['md5'].'.jpeg'))
					{
						$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/photo/'.$req['md5'].'-thumb.jpg">');
					}
					else if(file_exists('images/photo/'.$req['md5'].'.png'))
					{
						$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/photo/'.$req['md5'].'-thumb.jpg">');
					}
					else
					{	
						$type_vignette = $req['type'];
						$SQL = "SELECT * FROM genre WHERE type = '$type_vignette'";
						$z = $pdo->query($SQL);
						$zz = $z->fetch();
						$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/'.$zz['miniature'].'">');
					}
					
					if($req['age'] == '')
					{
						$class_template_user_result->assign("{user_age}",round($dst)." Km");
					}
					else
					{
						$class_template_user_result->assign("{user_age}",'('.$req['age'].' ans) - '.round($dst).' Km');
					}
					
					$class_template_user_result->assign("{pays_icon}",'<img src="'.$url_script.'/images/flag/'.$req['pays'].'.png" width=12>');
					
					$region = $req['region'];
					
					$SQL = "SELECT * FROM region WHERE id = $region";
					$r = $pdo->query($SQL);
					$rr = $r->fetch();
					
					$class_template_user_result->assign("{region}",$rr['titre']);
					
					$isConnected = checkConnected($req['md5']);
					if($isConnected)
					{
						$class_template_user_result->assign("{userconnected}",'<div class="user-connected-item"><img src="'.$url_script.'/images/icon-connected.png" title="'.$title_user_connected.'"></div>');
					}
					else
					{
						$class_template_user_result->assign("{userconnected}",'<div class="user-connected-item"><img src="'.$url_script.'/images/icon-disconnect.png" title="'.$title_user_disconnected.'"></div>');
					}
					
					$class_template_user_result->assign("{user_username}",ucfirst($req['username']));
					$result .= $class_template_user_result->getData();
				}
			}
		}
	}
}

$class_template_loader->assign("{result}",$result);
$class_publicite->updatePublicite($class_template_loader);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>