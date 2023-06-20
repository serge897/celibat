<?php

include "main.php";

if(!checkuserConnected())
{
	header("Location: $url_script");
	exit;
}

if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	if($action == 1)
	{
		$md5_receipt = AntiInjectionSQL($_REQUEST['md5_receipt']);
		$message = AntiInjectionSQL($_REQUEST['message']);
		$message = strip_tags($message);
		$message = nl2br($message);
		
		/* Remove Rules */
		if(getParametre("ban_email_adress") == 'yes')
		{
			$message = removeEmailAdress($message);
		}
		if(getParametre("ban_url_adress") == 'yes')
		{
			$message = removeURL($message);
		}
		
		$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$md5_send = $req['md5'];
		$username = $req['username'];
		$autorise = false;
		
		$autorise = isUserPaidToUse();
		
		if($autorise)
		{
			if(getParametre("free") == 'yes')
			{
				
			}
			else
			{
				removeCredit($md5_send);
			}
			
			$name_file = "";
			
			if($_FILES['photo']['tmp_name'] != '')
			{
				$chemin = $_SERVER["DOCUMENT_ROOT"].$upload_path."/images/stock/";
				$tmp_file = $_FILES['photo']['tmp_name'];
				if(!is_uploaded_file($tmp_file))
				{
					// Erreur fichier introuvable
					header("Location: $url_script/conversation.php?md5=$md5_receipt&erreur=1");
					exit;
				}
				
				/* On check qu'il s'agit bien d'une image */
				$check = getimagesize($_FILES['photo']['tmp_name']);
				if($check !== false) 
				{
					// Il s'agit bien d'une image valide
				} 
				else 
				{
					// Il ne s'agit pas d'une image possible hack
					header("Location: $url_script/conversation.php?md5=$md5_receipt&erreur=5");
					exit;
				}

				// on vérifie maintenant l'extension
				$type_file = $_FILES['photo']['type'];
				// on copie le fichier dans le dossier de destination
				$name_file = $_FILES['photo']['name'];
				$extension = explode(".",$name_file);
				$extension = $extension[count($extension)-1];
				$extension = strtolower($extension);
				
				if($extension == 'jpg')
				{
					// Ok
				}
				else if($extension == 'jpeg')
				{
					// Ok
				}
				else
				{
					// Erreur fichier au mauvais format
					header("Location: $url_script/conversation.php?md5=$md5_receipt&erreur=2");
					exit;
				}
				
				$name_file = md5(microtime()).".jpg";
				
				if(!move_uploaded_file($tmp_file, $chemin.$name_file))
				{
					// Erreur systeme impossible de copier
					header("Location: $url_script/conversation.php?md5=$md5_receipt&erreur=3");
					exit;
				}
			}
			
			$SQL = "INSERT INTO messagerie (md5_receipt,md5_send,message,photo,date_message,lu) VALUES ('$md5_receipt','$md5_send','$message','$name_file',NOW(),'non')";
			$pdo->query($SQL);
			
			/* On ajoute une notification sonore */
			$SQL = "INSERT INTO notificationsound (md5) VALUES ('$md5_receipt')";
			$pdo->query($SQL);
			
			/* On ajoute une notification tchat */
			if(checkConnected($md5_receipt))
			{
				$SQL = "INSERT INTO newmessagetchat (md5sender,md5) VALUES ('$md5_send','$md5_receipt')";
				$pdo->query($SQL);
			}
			
			$SQL = "SELECT * FROM user WHERE md5 = '$md5_receipt'";
			$r = $pdo->query($SQL);
			$rr = $r->fetch();
			
			$email = $rr['email'];
			$notification = $rr['notification'];
			$message_user = $message;
			$user_sender = $rr['username'];
			$bot = $rr['bot'];
			
			if($bot != 'oui')
			{			
				/* On envoie une notification par email */
				$subject = getParametre("sujet_notification_message_email");
				$subject = str_replace("{user_sender}",$user_sender,$subject);
				$subject = str_replace("{br}","",$subject);
				$subject = str_replace("{link_connect}",'',$subject);
				$subject = str_replace("{username}",$_SESSION['username'],$subject);
				$subject = str_replace("{message_trunk}",substr($message_user,0,15)."...",$subject);

				$msg = getParametre("message_notification_message_email");
				$msg = str_replace("{user_sender}",$user_sender,$msg);
				$msg = str_replace("{br}","<br>",$msg);
				$msg = str_replace("{username}",$_SESSION['username'],$msg);
				$msg = str_replace("{link_connect}",'<a href="'.$url_script.'/connexion.php">Voir le message</a>',$msg);
				$msg = str_replace("{message_trunk}",substr($message_user,0,15)."...",$msg);
				
				$class_email->sendMailTemplate($email,$subject,$msg);
			}
			
			header("Location: $url_script/conversation.php?md5=$md5_receipt");
			exit;
		}
		else
		{
			header("Location: $url_script/conversation.php?md5=$md5_receipt&erreur=4");
			exit;
		}
	}
}

$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$md5_user = $req['md5'];
$md5 = AntiInjectionSQL($_REQUEST['md5']);

$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$user = $req['username'];

$class_template_loader->showHeadSetSEO("Conversation avec $user","Conversation avec $user");
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("conversation.tpl");

$class_template_loader->assign("{user}",ucfirst($user));
$class_template_loader->assign("{url_script}",$url_script);

/* On check les messages */
$SQL = "SELECT COUNT(*) FROM messagerie WHERE md5_receipt = '$md5_user'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();
$count = $req[0];

if(isset($_REQUEST['erreur']))
{
	$erreur = $_REQUEST['erreur'];
	if($erreur == 1)
	{
		$class_template_loader->assign("{erreur}",'<div class="error-msg">'.$messagerie_error_photo_not_found.'</div>');
	}
	else if($erreur == 2)
	{
		$class_template_loader->assign("{erreur}",'<div class="error-msg">'.$messagerie_error_photo_not_jpeg.'</div>');
	}
	else if($erreur == 3)
	{
		$class_template_loader->assign("{erreur}",'<div class="error-msg">'.$messagerie_error_upload_photo.'</div>');
	}
	else if($erreur == 4)
	{
		$class_template_loader->assign("{erreur}",'<div class="error-msg">Pour répondre à '.ucfirst($user).' et envoyer des messages à tous les membres vous devez régler votre abonnement, en cliquant <a href="'.$url_script.'/paid.php?md5='.$md5_user.'">ici</a></div>');
	}
	else if($erreur == 5)
	{
		$class_template_loader->assign("{erreur}",'<div class="error-msg">'.$messagerie_error_photo_not_jpeg.'</div>');
	}
}
else
{
	$class_template_loader->assign("{erreur}","");
}

if($count == 0)
{
	$class_template_loader->assign("{content}",'<div class="no-result"><i class="fas fa-envelope"></i><br>'.$title_aucun_resultat_messagerie.'</div>');
}
else
{
	/* On indique que l'ont à lu les messages */
	$SQL = "UPDATE messagerie SET lu = 'oui' WHERE md5_receipt = '$md5_user' AND md5_send = '$md5'";
	$pdo->query($SQL);
	
	$item_msg = '';
	
	//$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_user' or md5_receipt = '$md5' AND md5_send = '$md5_user' or md5_send = '$md5' ORDER BY date_message DESC";
	$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_user' AND md5_send = '$md5' or md5_receipt = '$md5' AND md5_send = '$md5_user' ORDER BY date_message ASC ";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$md5_send = $req['md5_send'];
		$photo = $req['photo'];
		
		$SQL = "SELECT * FROM user WHERE md5 = '$md5_send'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		$username_send = $rr['username'];
		
		$type = $req['type'];
		$photourl = getPhoto($md5_send);
		
		if($md5_send == $md5_user)
		{
			$item_msg .= '<div class="item-msg user">';
		}
		else
		{
			$item_msg .= '<div class="item-msg">';
		}
		
		$item_msg .= '<div class="item-msg-photo"><img src="'.$photourl.'"></div>';
		$item_msg .= '<div class="item-msg-info">';
		$item_msg .= '<div class="item-msg-date"><span class="item-msg-big-pseudo"><a href="'.$url_script.'/'.$md5_send.'/profil-de-'.slugify($username_send).'.html" style="text-decoration:none;color:#000000;">'.ucfirst($username_send).'</a></span> le '.$class_date->transformDate($req['date_message'],'fr').'</div>';
		
		$message = $req['message'];
		$message_min = strtolower($message);
		
		// Emoji
		$message = str_replace(":)",'<span class="emoji"><img src="'.$url_script.'/images/emoji/1.png"></span>',$message);
		$message = str_replace(":-)",'<span class="emoji"><img src="'.$url_script.'/images/emoji/1.png"></span>',$message);
		$message = str_replace(":(",'<span class="emoji"><img src="'.$url_script.'/images/emoji/sad.png"></span>',$message);
		$message = str_replace(":-(",'<span class="emoji"><img src="'.$url_script.'/images/emoji/sad.png"></span>',$message);
		$message = str_replace(":-D",'<span class="emoji"><img src="'.$url_script.'/images/emoji/2.png"></span>',$message);
		$message = str_replace(":D",'<span class="emoji"><img src="'.$url_script.'/images/emoji/2.png"></span>',$message);
		$message = str_replace(":-D",'<span class="emoji"><img src="'.$url_script.'/images/emoji/2.png"></span>',$message);
		$message = str_replace(":o",'<span class="emoji"><img src="'.$url_script.'/images/emoji/5.png"></span>',$message);
		$message = str_replace(":-o",'<span class="emoji"><img src="'.$url_script.'/images/emoji/5.png"></span>',$message);
		$message = str_replace(":-p",'<span class="emoji"><img src="'.$url_script.'/images/emoji/6.png"></span>',$message);
		$message = str_replace(":p",'<span class="emoji"><img src="'.$url_script.'/images/emoji/6.png"></span>',$message);
		$message = str_replace(";-)",'<span class="emoji"><img src="'.$url_script.'/images/emoji/wink.png"></span>',$message);
		$message = str_replace(";)",'<span class="emoji"><img src="'.$url_script.'/images/emoji/wink.png"></span>',$message);
		
		// Si on à une photo
		if($photo != '')
		{
			$message .= '<br><a href="'.$url_script.'/images/stock/'.$photo.'" target="photosend"><img src="'.$url_script.'/images/stock/'.$photo.'" width="30%"></a>';
		}
		
		$item_msg .= '<div class="item-msg-message">'.$message.'</div>';
		$item_msg .= '</div>';
		$item_msg .= '</div>';
	}
	
	$class_template_loader->assign("{content}",$item_msg);
}

$class_template_loader->assign("{md5}",$md5);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>