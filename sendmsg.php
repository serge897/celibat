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
		
		/* On check si un abonnement est en place */
		if(isUserPaidToUse())
		{
			if(getParametre("free") == 'yes')
			{
				
			}
			else
			{
				removeCredit($md5_send);
			}
			
			/* A¨Payer on envoie le message */
			$SQL = "INSERT INTO messagerie (md5_receipt,md5_send,message,photo,date_message,lu) VALUES ('$md5_receipt','$md5_send','$message','',NOW(),'non')";
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
				$subject = str_replace("{user_sender}",$username,$subject);
				$subject = str_replace("{br}","",$subject);
				$subject = str_replace("{link_connect}",'',$subject);
				$subject = str_replace("{username}",$user_sender,$subject);
				$subject = str_replace("{message_trunk}",substr($message_user,0,10)."...",$subject);

				$message = getParametre("message_notification_message_email");
				$message = str_replace("{user_sender}",$username,$message);
				$message = str_replace("{br}","<br>",$message);
				$message = str_replace("{username}",$user_sender,$message);
				$message = str_replace("{link_connect}",'<a href="'.$url_script.'/connexion.php" class="btn">'.$btn_show_message_messagerie_notification_email.'</a>',$message);
				$message = str_replace("{message_trunk}",substr($message_user,0,10)."...",$message);
				
				$class_email->sendMailTemplate($email,$subject,$message);			
			}
			
			header("Location: $url_script/sendmsg.php?md5=$md5_receipt&valid=1");
			exit;
		}
		else
		{
			/* N'as pas payer on envoie un message d'erreur */
			header("Location: $url_script/sendmsg.php?md5=$md5_receipt&error=1");
			exit;
		}
	}
}

$md5 = AntiInjectionSQL($_REQUEST['md5']);

$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$username = $req['username'];

$class_template_loader->showHeadSetSEO("Envoyer un message à $username","Envoyer un message à $username");
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("sendmsg.tpl");
$class_template_loader->assign("{user}",ucfirst($username));

/* On check si une photo existe */
$photourl = getPhoto($md5);

if(isset($_REQUEST['valid']))
{
	$class_template_loader->assign("{validate}",'<div class="valid-msg">'.$messagerie_message_send_confirmation.'</div>');
}
else if(isset($_REQUEST['error']))
{
	$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	$md5_send = $req['md5'];
	
	$class_template_loader->assign("{validate}",'<div class="error-msg">Vous ne pouvez pas envoyer de message. Vous devez payer votre abonnement pour envoyer des messages. Vous pouvez le faire des maintenants en cliquant <a href="'.$url_script.'/paid.php?md5='.$md5_send.'">ici</a></div>');
}
else
{
	$class_template_loader->assign("{validate}",'');
}

$class_template_loader->assign("{photo}",'<img src="'.$photourl.'">');
$class_template_loader->assign("{md5}",$md5);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>