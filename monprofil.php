<?php

include "main.php";

$nombre_photo_gallery = 5;

if(!checkuserConnected())
{
	header("Location: $url_script");
	exit;
}

if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	
	/* Update du profils */
	if($action == 1)
	{
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		$description = AntiInjectionSQL($_REQUEST['description']);
		$age = AntiInjectionSQL($_REQUEST['age']);
		$pays = AntiInjectionSQL($_REQUEST['pays']);
		$region = AntiInjectionSQL($_REQUEST['region']);
		$codepostal = AntiInjectionSQL($_REQUEST['codepostal']);
		$cherche = AntiInjectionSQL($_REQUEST['cherche']);
		$agedepart = AntiInjectionSQL($_REQUEST['agedepart']);
		$agefin = AntiInjectionSQL($_REQUEST['agefin']);
		$distance = AntiInjectionSQL($_REQUEST['distance']);
		$notification = AntiInjectionSQL($_REQUEST['notification']);
		$webcamactivate = AntiInjectionSQL($_REQUEST['webcamactivate']);
		$ville = AntiInjectionSQL($_REQUEST['ville']);
		$type = AntiInjectionSQL($_REQUEST['type']);
		$username = AntiInjectionSQL($_REQUEST['username']);
		
		$error = NULL;
		
		/* Fix pour améliorer l'ajout d'une ville si trop rapide ou autre */
		if($codepostal == '')
		{
			/* Si pas de id ville on tente de trouver la ville */
			$SQL = "SELECT * FROM ville WHERE pays = '$pays' AND nom LIKE '%$ville%' LIMIT 1";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$id = $req['id'];
			if($id != '')
			{
				$codepostal = $id;
			}			
		}
		
		/* On check si l'username n'est pas déjà */
		$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		if($req['username'] != $username)
		{
			$SQL = "SELECT COUNT(*) FROM user WHERE username = '$username'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			if($req[0] == 0)
			{
				$SQL = "UPDATE user SET username = '$username' WHERE md5 = '$md5'";
				$pdo->query($SQL);
				$error = ["erroruser" => 0];
			}
			else
			{
				$error = ["erroruser" => 1];
				$error += ["errorusername" => $username];
			}
		}
		
		$jauge = 0;
		if($description != '')
		{
			$jauge = $jauge + 20;
		}
		if($pays != '')
		{
			$jauge = $jauge + 20;
		}
		if($region != '' && $region != 0)
		{
			$jauge = $jauge + 20;
		}
		if($codepostal != '' && $codepostal != 0)
		{
			$jauge = $jauge + 20;
		}
		
		/* On check si une photo existe */
		$image_exist = false;
		if(file_exists("images/photo/$md5.jpg"))
		{
			$image_exist = true;
		}
		if(file_exists("images/photo/$md5.jpeg"))
		{
			$image_exist = true;
		}
		if(file_exists("images/photo/$md5.png"))
		{
			$image_exist = true;
		}
		
		if($image_exist)
		{
			$jauge = $jauge + 20;
		}
		
		if($type != '')
		{
			$SQL = "UPDATE user SET type = '$type' WHERE md5 = '$md5'";
			$pdo->query($SQL);
		}
		
		/* Remove rules */
		
		/* Remove ALL Email */
		$description = removeEmailAdress($description);
		$description = removeURL($description);
		
		$SQL = "UPDATE user SET description = '$description' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET age = '$age' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET region = $region WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET pays = '$pays' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET ville = '$codepostal' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET cherche = '$cherche' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET cherche_age_depart = '$agedepart' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET cherche_age_fin = '$agefin' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET cherche_distance = '$distance' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET notification = '$notification' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET pourcentage = $jauge WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET webcam = '$webcamactivate' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		/* Filtre */
		
		// On clean les valeur enregistrer
		$SQL = "DELETE FROM user_filtre WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "SELECT * FROM filtre_name";
		$reponse = $pdo->query($SQL);
		while($req = $reponse->fetch())
		{
			$idfiltre = $req['id'];
			$valeur = $_REQUEST[$req['nom']];
			
			$SQL = "INSERT INTO user_filtre (idfiltre,valeur,md5) VALUES ($idfiltre,'$valeur','$md5')";
			$pdo->query($SQL);
		}
		
		/* Photo profil directement dans le formulaire sinon pas logique */
		if($_FILES['photoprofil'] != '')
		{
			$target_dir = $_SERVER["DOCUMENT_ROOT"].$upload_path."/images/photo/";
			$target_file = $target_dir . basename($_FILES['photoprofil']["name"]);
			$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			
			/* On check qu'il s'agit bien d'une image */
			$check = getimagesize($_FILES['photoprofil']["tmp_name"]);
			if($check !== false) 
			{
				// Il s'agit bien d'une image valide
			} 
			else 
			{
				$msg_error = "Le fichier n'est pas une image valide";
				header("Location: $url_script/monprofil.php?errormsg=$msg_error");
				exit;
			}
			
			/* On check l'extension de l'image */
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif" ) 
			{
				$msg_error = "L'image doit être au format JPG ou PNG";
				header("Location: $url_script/monprofil.php?errormsg=$msg_error");
				exit;
			}
			
			$extension = $_FILES['photoprofil']["name"];
			$extension = explode(".",$extension);
			$extension = $extension[count($extension)-1];
			$extension = strtolower($extension);
			$name_file = $md5.".".$extension;
			$target_file = $target_dir . basename($name_file);
			
			/* Bug fix on supprime toute les fichier existante auparavant */
			unlink($target_dir.$md5.".jpg");
			unlink($target_dir.$md5.".png");
			unlink($target_dir.$md5.".jpeg");
			unlink($target_dir.$md5."-thumb.jpg");
			
			if(move_uploaded_file($_FILES['photoprofil']["tmp_name"], $target_file)) 
			{
				$filenamesave = $target_dir.$md5."-thumb.jpg";
				$class_image->resizeImage($target_file,$filenamesave,140,140);
				
				if($jauge < 100)
				{
					$jauge = $jauge + 20;
				}
				
				$SQL = "UPDATE user SET pourcentage = $pourcentage WHERE md5 = '$md5'";
				$pdo->query($SQL);
			} 
			else 
			{
				$msg_error = "Un problème à été rencontrer lors de l'upload du fichier $target_file";
				header("Location: $url_script/monprofil.php?errormsg=$msg_error");
				exit;
			}
		}
		
		/* Photo gallery */
		for($x=1;$x<$nombre_photo_gallery;$x++)
		{
			if($_FILES['photo-'.$x]['name'] != '')
			{
				$target_dir = $_SERVER["DOCUMENT_ROOT"].$upload_path."/images/galerie/";
				$target_file = $target_dir . basename($_FILES['photo-'.$x]["name"]);
				$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
				
				/* On check qu'il s'agit bien d'une image */
				$check = getimagesize($_FILES['photo-'.$x]["tmp_name"]);
				if($check !== false) 
				{
					// Il s'agit bien d'une image valide
				} 
				else 
				{
					//$error += ["errorimage" => 1];
				}
				
				/* On check l'extension de l'image */
				if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
				&& $imageFileType != "gif" ) 
				{
					//$error += ["errorimage" => 1];
				}
				
				$extension = $_FILES['photo-'.$x]["name"];
				$extension = explode(".",$extension);
				$extension = $extension[count($extension)-1];
				$extension = strtolower($extension);
				$md5p = md5(microtime());
				$name_file = $md5p.".".$extension;
				$target_file = $target_dir . basename($name_file);
				if($error == 0)
				{
					if(move_uploaded_file($_FILES['photo-'.$x]["tmp_name"], $target_file)) 
					{
						$SQL = "INSERT INTO galerie (md5user,image) VALUES ('$md5','$name_file')";
						$pdo->query($SQL);
					} 
				}
			}
		}
		
		if($error != NULL)
		{
			$parametre = http_build_query($error);
		}
		else
		{
			$parametre = '';
		}
		header("Location: $url_script/monprofil.php?".$parametre);
		exit;
	}
	
	/* Update photo de profil (Retrocompatibilité) */
	if($action == 2)
	{
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		
		/* Fix bug 100% */
		$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$pourcentage = $req['pourcentage'];
		
		if($_FILES['photo'] != '')
		{
			$target_dir = $_SERVER["DOCUMENT_ROOT"].$upload_path."/images/photo/";
			$target_file = $target_dir . basename($_FILES['photo']["name"]);
			$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			
			/* On check qu'il s'agit bien d'une image */
			$check = getimagesize($_FILES['photo']["tmp_name"]);
			if($check !== false) 
			{
				// Il s'agit bien d'une image valide
			} 
			else 
			{
				$msg_error = "Le fichier n'est pas une image valide";
				header("Location: $url_script/monprofil.php?errormsg=$msg_error");
				exit;
			}
			
			/* On check l'extension de l'image */
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif" ) 
			{
				$msg_error = "L'image doit être au format JPG ou PNG";
				header("Location: $url_script/monprofil.php?errormsg=$msg_error");
				exit;
			}
			
			$extension = $_FILES['photo']["name"];
			$extension = explode(".",$extension);
			$extension = $extension[count($extension)-1];
			$extension = strtolower($extension);
			$name_file = $md5.".".$extension;
			$target_file = $target_dir . basename($name_file);
			
			/* Bug fix on supprime toute les fichier existante auparavant */
			unlink($target_dir.$md5.".jpg");
			unlink($target_dir.$md5.".png");
			unlink($target_dir.$md5.".jpeg");
			unlink($target_dir.$md5."-thumb.jpg");
			
			if(move_uploaded_file($_FILES['photo']["tmp_name"], $target_file)) 
			{
				$filenamesave = $target_dir.$md5."-thumb.jpg";
				$class_image->resizeImage($target_file,$filenamesave,140,140);
				
				if($pourcentage < 100)
				{
					$pourcentage = $pourcentage + 20;
				}
				
				$SQL = "UPDATE user SET pourcentage = $pourcentage WHERE md5 = '$md5'";
				$pdo->query($SQL);
				
				header("Location: $url_script/monprofil.php");
				exit;
			} 
			else 
			{
				$msg_error = "Un problème à été rencontrer lors de l'upload du fichier $target_file";
				header("Location: $url_script/monprofil.php?errormsg=$msg_error");
				exit;
			}
		}
	}
	
	/* Supprimer une image de la galerie */
	if($action == 4)
	{
		$id = AntiInjectionSQL($_REQUEST['id']);
		
		$SQL = "SELECT * FROM galerie WHERE id = $id";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$image = $req['image'];
		
		$SQL = "DELETE FROM galerie WHERE id = $id";
		$pdo->query($SQL);
		
		unlink("images/galerie/$image");
		
		header("Location: monprofil.php");
		exit;
	}
	
	/* Suppression de compte */
	if($action == 3)
	{
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		
		$SQL = "DELETE FROM user WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "DELETE FROM topliste WHERE md5user = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "DELETE FROM blacklist WHERE md5user = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "DELETE FROM messagerie WHERE md5_receipt = '$md5'";
		$pdo->query($SQL);
		
		$SQL = "DELETE FROM messagerie WHERE md5_send = '$md5'";
		$pdo->query($SQL);
		
		header("Location: logout.php");
		exit;
	}
}

$class_template_loader->showHead('monprofil');
$class_template_loader->openBody();

include "header.php";

$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$md5 = $req['md5'];
$description = $req['description'];
$age = $req['age'];
$region = $req['region'];
$ville = $req['ville'];
$cherche = $req['cherche'];
$cherche_age_depart = $req['cherche_age_depart'];
$cherche_age_fin = $req['cherche_age_fin'];
$cherche_distance = $req['cherche_distance'];
$pays = $req['pays'];
$notification_check = $req['notification'];
$webcam = $req['webcam'];
$username = $req['username'];

$pourcentage_profil_completed = 0;
if($description != '')
{
	$pourcentage_profil_completed = $pourcentage_profil_completed + 20;
}
if($pays != '')
{
	$pourcentage_profil_completed = $pourcentage_profil_completed + 20;
}
if($region != 0 && $region != '')
{
	$pourcentage_profil_completed = $pourcentage_profil_completed + 20;
}
if($ville != 0 && $ville != '')
{
	$pourcentage_profil_completed = $pourcentage_profil_completed + 20;
}

if($ville != '')
{
	if($ville == 0)
	{
		$ville = "";
	}
	else
	{
		$SQL = "SELECT * FROM ville WHERE id = $ville";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		$idville = $ville;
		$ville = $rr['nom']." (".$rr['codepostal'].")";
	}
}

/* On check si une photo existe */
$image_exist = false;
if(file_exists("images/photo/$md5.jpg"))
{
	$image_exist = true;
	$photourl = "$url_script/images/photo/$md5.jpg?seed=".md5(microtime());
}
if(file_exists("images/photo/$md5.jpeg"))
{
	$image_exist = true;
	$photourl = "$url_script/images/photo/$md5.jpeg?seed=".md5(microtime());
}
if(file_exists("images/photo/$md5.png"))
{
	$image_exist = true;
	$photourl = "$url_script/images/photo/$md5.png?seed=".md5(microtime());
}

if($image_exist)
{
	$pourcentage_profil_completed = $pourcentage_profil_completed + 20;
}

$type = $req['type'];

if(!$image_exist)
{
	$type_vignette = $req['type'];
	$SQL = "SELECT * FROM genre WHERE type = '$type_vignette'";
	$z = $pdo->query($SQL);
	$zz = $z->fetch();
	$photourl = "$url_script/images/".$zz['miniature'];
}

$class_template_loader->loadTemplate("monprofil.tpl");
$class_template_loader->assign("{photourl}",$photourl);

/* Pourcentage profil completer */

if($pourcentage_profil_completed < 100)
{
	$width_jauge = $pourcentage_profil_completed;
	$color_jauge = '';
}
else
{
	$width_jauge = 100;
	$color_jauge = 'background-color:#00ff00;';
}

if(getParametre("user_profile_pourcentage_validate") == 'yes')
{
	$profil_completed = '<div class="pourcentage-profil"><div class="pourcentage-rempli" style="width:'.$width_jauge.'%;'.$color_jauge.'">'.$width_jauge.'%</div></div>';
	$class_template_loader->assign("{completed}",$profil_completed);
}
else
{
	$class_template_loader->assign("{completed}",'');
}

/* Si pas précisez */
if($type == '')
{
	$param .= '<label><b>Votre sexe :</b></label>';
	$param .= '<select name="type" class="inputbox">';
	
	$SQL = "SELECT * FROM genre";
	$u = $pdo->query($SQL);
	while($uu = $u->fetch())
	{
		$param .= '<option value="'.$uu['type'].'">'.$uu['titre'].'</option>';
	}
	
	$param .= '</select>';
}

$param .= '<label><b>'.$mon_profil_title_username.'</b></label>';
if(isset($_REQUEST['erroruser']))
{
	if($_REQUEST['erroruser'] == 1)
	{
		$param .= '<br><font color=red size=2><b>'.$mon_profil_error_username_first.' "'.$_REQUEST['errorusername'].'" '.$mon_profil_error_username_second.'</b></font>';
	}
}
$param .= '<input type="text" name="username" value="'.$username.'" class="inputbox">';

$param .= '<label><b>'.$mon_profil_title_description.'</b></label>';
if($description == '')
{
	$param .= '<textarea name="description" class="areabox" style="border: 2px solid #f00;" placeholder="Decrivez vous pour faire des rencontres">'.$description.'</textarea>';
}
else
{
	$param .= '<textarea name="description" class="areabox">'.$description.'</textarea>';
}

$param .= '<label><b>'.$mon_profil_title_age.'</b></label>';
$param .= '<select name="age" class="inputbox">';
for($x=18;$x<100;$x++)
{
	if($age == $x)
	{
		$param .= '<option value="'.$x.'" selected>'.$x.' '.$title_prefixe_age.'</option>';
	}
	else
	{
		$param .= '<option value="'.$x.'">'.$x.' '.$title_prefixe_age.'</option>';
	}
}
$param .= '</select>';

$param .= '<label><b>'.$mon_profil_title_pays.'</b></label>';
if($pays == '')
{
	$param .= '<select name="pays" class="inputbox" id="pays" onchange="updateRegion();" style="border: 2px solid #f00;">';
}
else
{
	$param .= '<select name="pays" class="inputbox" id="pays" onchange="updateRegion();">';
}

if($pays == '')
{
	$param .= '<option value="" selected>'.$mon_profil_option_pays.'</option>';
}
else
{
	$param .= '<option value="">'.$mon_profil_option_pays.'</option>';
}

$SQL = "SELECT * FROM pays";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($pays == $req['valeur'])
	{
		$pays_selected = $req['valeur'];
		$param .= '<option value="'.$req['valeur'].'" selected>'.$req['nom'].'</option>';
	}
	else
	{
		$param .= '<option value="'.$req['valeur'].'">'.$req['nom'].'</option>';
	}
}

$param .= '</select>';

$param .= '<label><b>'.$mon_profil_title_region.'</b></label>';

if($region == '' || $region == 0)
{
	$param .= '<select name="region" id="region" class="inputbox" style="border: 2px solid #f00;">';
}
else
{
	$param .= '<select name="region" id="region" class="inputbox">';
}

if($region == '')
{
	$param .= '<option value="" selected>'.$mon_profil_option_region.'</option>';
}
else
{
	$param .= '<option value="">'.$mon_profil_option_region.'</option>';
}

$SQL = "SELECT * FROM region WHERE pays = '$pays_selected'";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($region == $req['id'])
	{
		$param .= '<option value="'.$req['id'].'" selected>'.$req['titre'].'</option>';
	}
	else
	{
		$param .= '<option value="'.$req['id'].'">'.$req['titre'].'</option>';
	}
}

$param .= '</select>';

$param .= '<label><b>'.$mon_profil_title_ville.'</b></label>';
if($ville == '0' || $ville == '')
{
	$param .= '<input type="text" name="ville" id="ville" value="" placeholder="'.$mon_profil_option_ville.'" class="inputbox" onkeydown="updateResult();" autocomplete="off" style="border: 2px solid #f00;">';
}
else
{
	$param .= '<input type="text" name="ville" id="ville" value="'.$ville.'" placeholder="'.$mon_profil_option_ville.'" class="inputbox" onkeydown="updateResult();" autocomplete="off">';
}
$param .= '<input type="hidden" name="codepostal" id="codepostal" value="'.$idville.'">';
$param .= '<div id="result-ville">';
$param .= '</div>';
$param .= '<script>';
$param .= 'function updateResult()';
$param .= '{';
$param .= "var ville = $('#ville').val();";
$param .= "var pays = $('#pays').val();";
$param .= '$.post("updateresult.php?ville="+ville+"&pays="+pays, function(data)';
$param .= '{';
$param .= "$('#result-ville').html(data);";
$param .= '});';
$param .= '}';
$param .= 'function updateVille(id,titre,codepostal)';
$param .= '{';
$param .= "$('#result-ville').html('');";
$param .= "$('#ville').val(titre);";
$param .= "$('#codepostal').val(id);";
$param .= '}';
$param .= 'function updateRegion()';
$param .= '{';
$param .= "var pays = $('#pays').val();";
$param .= "$('#region').load('updateregion.php?pays='+pays);";
$param .= '}';
$param .= '</script>';

$param .= '<H3>'.$mon_profil_ma_recherche.'</H3>';

/* Recherche un Homme / Femme / Autre */
$param .= '<label><b>'.$mon_profil_je_cherche_un.'</b></label>';
$param .= '<select name="cherche" class="inputbox">';

$SQL = "SELECT * FROM genre";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($cherche == $req['type'])
	{
		$param .= '<option value="'.$req['type'].'" selected>'.$req['jecherche'].'</option>';
	}
	else
	{
		$param .= '<option value="'.$req['type'].'">'.$req['jecherche'].'</option>';
	}
}

$param .= '</select>';

/* Recherche entre Age et Age */
$param .= '<label><b>'.$mon_profil_age_depart.'</b></label>';

$param .= '<select name="agedepart" class="inputbox">';
for($x=18;$x<100;$x++)
{
	if($cherche_age_depart == $x)
	{
		$param .= '<option value="'.$x.'" selected>'.$x.' '.$title_prefixe_age.'</option>';
	}
	else
	{
		$param .= '<option value="'.$x.'">'.$x.' '.$title_prefixe_age.'</option>';
	}
}
$param .= '</select>';

$param .= '<label><b>'.$mon_profil_age_fin.'</b></label>';

$param .= '<select name="agefin" class="inputbox">';
for($x=18;$x<100;$x++)
{
	if($cherche_age_fin == $x)
	{
		$param .= '<option value="'.$x.'" selected>'.$x.' '.$title_prefixe_age.'</option>';
	}
	else
	{
		$param .= '<option value="'.$x.'">'.$x.' '.$title_prefixe_age.'</option>';
	}
}
$param .= '</select>';

/* Distance */
$param .= '<label><b>'.$mon_profil_title_distance.'</b></label>';
$param .= '<select name="distance" class="inputbox">';
if($cherche_distance == 'proche')
{
	$param .= '<option value="proche" selected>'.$mon_profil_option_distance_proche.'</option>';
}
else
{
	$param .= '<option value="proche">'.$mon_profil_option_distance_proche.'</option>';
}
if($cherche_distance == 'regionale')
{
	$param .= '<option value="regionale" selected>'.$mon_profil_option_distance_region.'</option>';
}
else
{
	$param .= '<option value="regionale">'.$mon_profil_option_distance_region.'</option>';
}
if($cherche_distance == 'nationale')
{
	$param .= '<option value="nationale" selected>'.$mon_profil_option_distance_peuimporte.'</option>';
}
else
{
	$param .= '<option value="nationale">'.$mon_profil_option_distance_peuimporte.'</option>';
}
$param .= '</select>';

$param .= '<H3>Galerie photo</H3>';
$param .= '<div class="englobe-galerry">';

$SQL = "SELECT COUNT(*) FROM galerie WHERE md5user = '$md5'";
$r = $pdo->query($SQL);
$rr = $r->fetch();

$count_photo = $rr[0];
$nbr = $nombre_photo_gallery - $count_photo;

if($count_photo != 0)
{
	$SQL = "SELECT * FROM galerie WHERE md5user = '$md5'";
	$r = $pdo->query($SQL);
	while($rr = $r->fetch())
	{
		$param .= '<div class="photo-profil" style="width:140px;"><a href="'.$url_script.'/monprofil.php?action=4&id='.$rr['id'].'" class="btn-delete-galerry">X</a><img src="'.$url_script.'/images/galerie/'.$rr['image'].'"></div>';
	}
}

for($x=1;$x<$nbr;$x++)
{
	$param .= '<div class="photo-profil" style="width:140px;">
					<div class="textaddprofil">Ajouter une photo à la Galerie</div>
					<input type="file" name="photo-'.$x.'" id="imageloader-'.$x.'" class="photo-file" onchange="changeGalery('.$x.');">
					<img src="'.$url_script.'/images/no-photo-gallery.jpg" id="photo-profil-'.$x.'">
				</div>';
}

$param .= '</div>';

$param .= '<H3>'.$mon_profil_title_info_complementaire.'</H3>';

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

$SQL = "SELECT * FROM filtre_name";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	$nom = $req['nom'];
	$titre = $req['titre'];
	$param .= '<label><b>'.$titre.'</b></label>';
	$param .= '<select name="'.$nom.'" class="inputbox">';
	
	$idfiltre = $req['id'];
	
	$SQL = "SELECT * FROM filtre WHERE nom = '$nom'";
	$r = $pdo->query($SQL);
	while($rr = $r->fetch())
	{
		$selected = false;
		
		if($array_filtre == NULL)
		{
			$param .= '<option value="'.$rr['id'].'">'.$rr['parametre'].'</option>';
		}		
		else if(count($array_filtre) == 0)
		{
			$param .= '<option value="'.$rr['id'].'">'.$rr['parametre'].'</option>';
		}
		else
		{
			for($x=0;$x<count($array_filtre);$x++)
			{
				if($array_filtre[$x]['idfiltre'] == $idfiltre)
				{
					if($array_filtre[$x]['valeur'] == $rr['id'])
					{
						$selected = true;
					}
				}
			}
			
			if($selected)
			{
				$param .= '<option value="'.$rr['id'].'" selected>'.$rr['parametre'].'</option>';
			}
			else
			{
				$param .= '<option value="'.$rr['id'].'">'.$rr['parametre'].'</option>';
			}
		}
	}
	
	$param .= '</select>';
}

$param .= '<H3>Configuration</H3>';
if($webcam == 'yes')
{
	$param .= '<input type="checkbox" name="webcamactivate" value="yes" checked> Autorisé les utilisateurs à vous contacter par Webcam<br>';
}
else
{
	$param .= '<input type="checkbox" name="webcamactivate" value="yes"> Autorisé les utilisateurs à vous contacter par Webcam<br>';
}

$param .= '<input type="hidden" name="md5" value="'.$md5.'">';

$class_template_loader->assign("{md5}",$md5);
$class_template_loader->assign("{url_script}",$url_script);
$class_template_loader->assign("{param}",$param);

/* Notification */

/*$notification = '<label class="slider_switch">';
if($notification_check == 'yes')
{
	$notification .= '<input type="checkbox" name="notification" value="yes" checked>';
}
else
{
	$notification .= '<input type="checkbox" name="notification" value="yes">';
}
$notification .= '<span class="slider round"></span>';
$notification .= '</label> <b>Recevoir les notifications par email</b>';*/

$class_template_loader->assign("{notification}",$notification);

$class_publicite->updatePublicite($class_template_loader);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>