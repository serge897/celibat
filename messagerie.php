<?php

include "main.php";

$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$md5_user = $req['md5'];

if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	if($action == 1)
	{
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		$SQL = "INSERT INTO blacklist (md5user,md5) VALUES ('$md5_user','$md5')";
		$pdo->query($SQL);
		
		header("Location: messagerie.php");
		exit;
	}
}

if(!checkuserConnected())
{
	header("Location: $url_script");
	exit;
}

$class_template_loader->showHead('messagerie');
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("messagerie.tpl");

/* On check les messages */
$SQL = "SELECT COUNT(*) FROM messagerie WHERE md5_receipt = '$md5_user'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();
$count = $req[0];

if($count == 0)
{
	$class_template_loader->assign("{content}",'<div class="no-result"><i class="fas fa-envelope"></i><br>'.$title_aucun_resultat_messagerie.'</div>');
}
else
{
	$item_msg = '';
	
	$SQL = "SELECT DISTINCT(md5_send) FROM messagerie WHERE md5_receipt = '$md5_user'";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$md5_send = $req['md5_send'];
		
		/* On check si tous est lu */
		$SQL = "SELECT COUNT(*) FROM messagerie WHERE md5_receipt = '$md5_user' AND md5_send = '$md5_send' AND lu = 'non'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		if($rr[0] == 0)
		{
			$touslu = true;
		}
		else
		{
			$touslu = false;
		}
		
		$SQL = "SELECT * FROM user WHERE md5 = '$md5_send'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		$username_send = $rr['username'];
		
		/* On check si une photo existe */
		$photourl = getPhoto($md5_send);

		$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_user' AND md5_send = '$md5_send' ORDER BY date_message DESC LIMIT 1";
		$u = $pdo->query($SQL);
		$uu = $u->fetch();
		$last_date_message = $uu['date_message'];
		
		/* On check si l'utilisateur n'est pas en blacklist */
		$SQL = "SELECT COUNT(*) FROM blacklist WHERE md5user = '$md5_user' AND md5 = '$md5_send'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		if($rr[0] == 0)
		{			
			$item_msg .= '<div class="item-msg full">';
			$item_msg .= '<a href="'.$url_script.'/profile.php?md5='.$md5_send.'"><div class="item-msg-photo"><img src="'.$photourl.'"></div></a>';
			$item_msg .= '<div class="item-msg-info">';
			$item_msg .= '<div class="item-msg-date">'.$messagerie_dernier_message_recu_le.' '.$class_date->transformDate($last_date_message,'fr').'</div>';
			$item_msg .= '<a href="'.$url_script.'/profile.php?md5='.$md5_send.'"><div class="item-msg-pseudo">'.ucfirst($username_send).'</div></a>';
			$item_msg .= '</div>';
			if($touslu)
			{
				$item_msg .= '<div class="item-msg-btn"><a href="'.$url_script.'/conversation.php?md5='.$md5_send.'" class="btn gray low"><i class="fas fa-envelope-open"></i> '.$btn_lire_message.'</a>';
				$item_msg .= ' <a href="'.$url_script.'/messagerie.php?action=1&md5='.$md5_send.'" class="btn low"><i class="fas fa-ban"></i> '.$btn_add_liste_noire.'</a></div>';
			}
			else
			{
				$item_msg .= '<div class="item-msg-btn"><a href="'.$url_script.'/conversation.php?md5='.$md5_send.'" class="btn low"><i class="fas fa-envelope"></i> '.$btn_new_message.'</a>';
				$item_msg .= ' <a href="'.$url_script.'/messagerie.php?action=1&md5='.$md5_send.'" class="btn low"><i class="fas fa-ban"></i> '.$btn_add_liste_noire.'</a></div>';
			}
			
			$item_msg .= '</div>';		
		}
	}
	
	$class_template_loader->assign("{content}",$item_msg);
}

$class_publicite->updatePublicite($class_template_loader);
$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>