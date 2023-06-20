<?php

$class_template_loader->loadTemplate("header.tpl");
$class_template_loader->assign("{url_script}", $url_script);

$logourl = getParametre('logo');
$class_template_loader->assign("{logo_url}", $url_script . '/images/' . $logourl);

$logoalt = getParametre('logoalt');
$class_template_loader->assign("{logoalt}", $logoalt);

$menu = '<div class="menu">';
$menu .= '<ul>';

$SQL = "SELECT * FROM menu";
$reponse = $pdo->query($SQL);
while ($req = $reponse->fetch()) {
	if ($req['type'] == 'normal') {
		$menu .= '<li><a href="' . $url_script . '/' . $req['url'] . '">' . $req['icon'] . ' ' . $req['titre'] . '</a></li>';
	} else if ($req['type'] == 'externe') {
		$menu .= '<li><a href="' . $req['url'] . '">' . $req['icon'] . ' ' . $req['titre'] . '</a></li>';
	} else if ($req['type'] == 'onlyconnected') {
		if (checkuserConnected()) {
			$menu .= '<li><a href="' . $req['url'] . '">' . $req['icon'] . ' ' . $req['titre'] . '</a></li>';
		}
	}
}
$menu .= '</ul>';
$menu .= '</div>';

$SQL = "SELECT COUNT(*) FROM offre WHERE type = 'paiement_credit'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if ($req[0] == 0) {
	$credit = false;
} else {
	$credit = true;
}

$SQL = "SELECT * FROM user WHERE email = '" . $_SESSION['email'] . "' AND password = '" . $_SESSION['password'] . "'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$paid_abonnement = $req['paid_abonnement'];

$limite = strtotime($paid_abonnement);
$now = time();

if ($now > $limite) {
	$abo = false;
} else {
	$abo = true;
}

/* Methode d'appel de l'update uniquement une fois connecter pour les interactions */
if (checkuserConnected()) {
	$menu .= '<script>';
	$menu .= 'var updateTimer;';
	$menu .= 'function updateOnline()';
	$menu .= '{';
	$menu .= '$.post("' . $url_script . '/updateonline.php", function( data ) {';
	$menu .= 'data = data.split("|");';
	if ($credit) {
		if ($abo) {
			$menu .= "$('#nbrmsguseronline').html(data[0]+' <i class=\"fas fa-envelope\"></i> | <i class=\"fas fa-award\" title=\"Abonner\"></i> <font size=1>Abonner</font> | '+data[3]+' <i class=\"fas fa-coins\"></i>');";
		} else {
			$menu .= "$('#nbrmsguseronline').html(data[0]+' <i class=\"fas fa-envelope\"></i> | '+data[3]+' <i class=\"fas fa-coins\"></i>');";
		}
	} else {
		$menu .= "$('#nbrmsguseronline').html(data[0]+' <i class=\"fas fa-envelope\"></i>');";
	}
	$menu .= "$('.new-msg').html(data[0]);";
	$menu .= 'if(data[1] == "yes")';
	$menu .= '{';
	$menu .= 'playNotification();';
	$menu .= '}';
	$menu .= 'var notif = data[2];';
	$menu .= "if(notif != '')";
	$menu .= '{';
	$menu .= 'var notifbase = notif.split(",");';
	$menu .= 'for(let i=0;i<notifbase.length;i++)';
	$menu .= '{';
	$menu .= "if($('#msg-conversation-'+notifbase[i]).length != 0)";
	$menu .= '{';
	$menu .= "$('#msg-conversation-'+notifbase[i]).load('" . $url_script . "/updateconversation.php?md5='+notifbase[i]);";
	$menu .= '}';
	$menu .= 'else';
	$menu .= '{';
	$menu .= '$.post("' . $url_script . '/getNotification.php?md5="+notifbase[i],function(notification) {';
	$menu .= "$('#msg-instant').append(notification);";
	$menu .= '});';
	$menu .= '}';
	$menu .= '}';
	$menu .= '}';
	$menu .= 'clearInterval(updateTimer);';
	$menu .= 'updateTimer = setInterval(updateOnline,8000);';
	$menu .= '});';
	$menu .= '}';
	$menu .= 'function playNotification()';
	$menu .= '{';
	$menu .= "var audio = new Audio('" . $url_script . "/sound/notification.mp3');";
	$menu .= 'audio.play();';
	$menu .= '}';
	$menu .= 'updateTimer = setInterval(updateOnline,8000);';
	$menu .= 'function blacklistUser(md5)';
	$menu .= '{';
	$menu .= '$.post("' . $url_script . '/blacklistUser.php?md5="+md5,function(info) {';
	$menu .= "$('#msg-post-'+md5).remove();";
	$menu .= '});';
	$menu .= '}';
	$menu .= 'function closeConversationTchat(id)';
	$menu .= '{';
	$menu .= '$.post("' . $url_script . '/closeConversation.php?md5="+id,function(info) {';
	$menu .= "$('#msg-post-'+id).remove();";
	$menu .= '});';
	$menu .= '}';
	$menu .= '</script>';
	$menu .= '<style>';
	$menu .= '.form-subscribe { display: none; }';
	$menu .= '</style>';
}

$class_template_loader->assign("{menu}", $menu);

if (checkuserConnected()) {
	/* On check si l'utilisateur à une photo */
	$nbr = nbrmsg();

	if (file_exists('images/photo/' . $_SESSION['md5'] . '-thumb.jpg')) {
		if ($credit) {
			if ($abo) {
				$SQL = "SELECT paid_credit FROM user WHERE md5 = '" . $_SESSION['md5'] . "'";
				$u = $pdo->query($SQL);
				$uu = $u->fetch();

				$class_template_loader->assign("{connectbox}", '<a href="javascript:void(0);" onclick="showUserMenu();" class="btn"><div class="roundphotouser"><img src="' . $url_script . '/images/photo/' . $_SESSION['md5'] . '-thumb.jpg" alt="User"></div>&nbsp;&nbsp;&nbsp;&nbsp; <span id="nbrmsguseronline">' . $nbr . ' <i class="fas fa-envelope"></i> | <i class="fas fa-award" title="Abonner"></i> <font size=1>Abonner</font> | ' . $uu[0] . ' <i class="fas fa-coins"></i></a>');
			} else {
				$SQL = "SELECT paid_credit FROM user WHERE md5 = '" . $_SESSION['md5'] . "'";
				$u = $pdo->query($SQL);
				$uu = $u->fetch();

				$class_template_loader->assign("{connectbox}", '<a href="javascript:void(0);" onclick="showUserMenu();" class="btn"><div class="roundphotouser"><img src="' . $url_script . '/images/photo/' . $_SESSION['md5'] . '-thumb.jpg" alt="User"></div>&nbsp;&nbsp;&nbsp;&nbsp; <span id="nbrmsguseronline">' . $nbr . ' <i class="fas fa-envelope"></i> | ' . $uu[0] . ' <i class="fas fa-coins"></i>)</span></a>');
			}
		} else {
			$class_template_loader->assign("{connectbox}", '<a href="javascript:void(0);" onclick="showUserMenu();" class="btn"><div class="roundphotouser"><img src="' . $url_script . '/images/photo/' . $_SESSION['md5'] . '-thumb.jpg" alt="User"></div>&nbsp;&nbsp;&nbsp;&nbsp; <span id="nbrmsguseronline">' . $nbr . ' <i class="fas fa-envelope"></i>)</span></a>');
		}
	} else {
		if ($credit) {
			if ($abo) {
				$SQL = "SELECT paid_credit FROM user WHERE md5 = '" . $_SESSION['md5'] . "'";
				$u = $pdo->query($SQL);
				$uu = $u->fetch();

				$class_template_loader->assign("{connectbox}", '<a href="javascript:void(0);" onclick="showUserMenu();" class="btn"><i class="fas fa-user"></i> <span id="nbrmsguseronline">' . $nbr . ' <i class="fas fa-envelope"></i> | <i class="fas fa-award" title="Abonner"></i> <font size=1>Abonner</font> | ' . $uu[0] . ' <i class="fas fa-coins"></i></a>');
			} else {
				$SQL = "SELECT paid_credit FROM user WHERE md5 = '" . $_SESSION['md5'] . "'";
				$u = $pdo->query($SQL);
				$uu = $u->fetch();

				$class_template_loader->assign("{connectbox}", '<a href="javascript:void(0);" onclick="showUserMenu();" class="btn"><i class="fas fa-user"></i> <span id="nbrmsguseronline">' . $nbr . ' <i class="fas fa-envelope"></i> | ' . $uu[0] . ' <i class="fas fa-coins"></i></a>');
			}
		} else {
			$class_template_loader->assign("{connectbox}", '<a href="javascript:void(0);" onclick="showUserMenu();" class="btn"><i class="fas fa-user"></i> <span id="nbrmsguseronline">' . $nbr . ' <i class="fas fa-envelope"></i>)</span></a>');
		}
	}

	if ($nbr == 0) {
		$class_template_loader->assign("{nbr_msg}", '');
	} else {
		$class_template_loader->assign("{nbr_msg}", '<span class="new-msg">' . $nbr . '</span>');
	}

	/* Si abonnement actif */
	$methode_reglement = getParametre("methode_reglement");
	$flash_activate = getParametre("flash_activate");
	if ($methode_reglement == 'all_paid_subscribe' || $methode_reglement == 'all_paid_send_msg') {
		if ($flash_activate == 'oui') {
			if (isUserPaidToUse()) {
				$class_template_loader->assign("{flash_item}", '<div class="itemmenu"><a href="' . $url_script . '/qui-flash-sur-moi.php"><img src="' . $url_script . '/images/flash-on.png" alt="Qui flash sur moi ?" width=15> Qui flash sur moi ?</a></div>');
			} else {
				$class_template_loader->assign("{flash_item}", '<div class="itemmenu"><a href="javascript:void(0);" onclick="noAbo();"><img src="' . $url_script . '/images/flash-on.png" alt="Qui flash sur moi ?" width=15> Qui flash sur moi ?</a></div>');
			}
		} else {
			$class_template_loader->assign("{flash_item}", '<div class="itemmenu"><a href="' . $url_script . '/qui-flash-sur-moi.php"><img src="' . $url_script . '/images/flash-on.png" alt="Qui flash sur moi ?" width=15> Qui flash sur moi ?</a></div>');
		}

		$class_template_loader->assign("{abonnement_item}", '<div class="itemmenu"><a href="' . $url_script . '/abonnement.php"><i class="fas fa-award"></i> ' . $btn_abonement . '</a></div>');
	} else if ($methode_reglement == 'genre_paid_subscribe') {
		$genre_subscribe_paid = getParametre("genre_subscribe_paid");

		$SQL = "SELECT * FROM user WHERE email = '" . $_SESSION['email'] . "' AND password = '" . $_SESSION['password'] . "'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		$genre = $req['type'];

		if ($flash_activate == 'oui') {
			if (isUserPaidToUse()) {
				$class_template_loader->assign("{flash_item}", '<div class="itemmenu"><a href="' . $url_script . '/qui-flash-sur-moi.php"><img src="' . $url_script . '/images/flash-on.png" width=15> ' . $btn_qui_flash_sur_moi . '</a></div>');
			} else {
				$class_template_loader->assign("{flash_item}", '<div class="itemmenu"><a href="javascript:void(0);" onclick="noAbo();"><img src="' . $url_script . '/images/flash-on.png" width=15> ' . $btn_qui_flash_sur_moi . '</a></div>');
			}
		} else {
			$class_template_loader->assign("{flash_item}", '<div class="itemmenu"><a href="' . $url_script . '/qui-flash-sur-moi.php"><img src="' . $url_script . '/images/flash-on.png" width=15> ' . $btn_qui_flash_sur_moi . '</a></div>');
		}

		if ($genre_subscribe_paid == $genre) {
			$class_template_loader->assign("{abonnement_item}", '<div class="itemmenu"><a href="' . $url_script . '/abonnement.php"><i class="fas fa-award"></i> ' . $btn_abonement . '</a></div>');
		} else {
			$class_template_loader->assign("{abonnement_item}", "");
		}
	} else {
		$class_template_loader->assign("{abonnement_item}", "");
		$class_template_loader->assign("{flash_item}", '<div class="itemmenu"><a href="' . $url_script . '/qui-flash-sur-moi.php"><img src="' . $url_script . '/images/flash-on.png" width=15> ' . $btn_qui_flash_sur_moi . '</a></div>');
	}
} else {
	$class_template_loader->assign("{connectbox}", '<style>#usermenu { display:none; }</style><a href="' . $url_script . '/connexion.php" class="btn"><i class="fas fa-user"></i> ' . $btn_connexion . '</a>');
}

$langue_selector = "";

if (checkuserConnected()) {
		$langue_selector .= '<div class="popupwebcambackground" id="popupwebcam"><div class="popupwebcam" id="popupwebcamcontent"></div></div>';
	$langue_selector .= '<script>';
	$langue_selector .= 'var intervalwebcam;';
	$langue_selector .= 'function checkWebcamAppel()';
	$langue_selector .= '{';
	$langue_selector .= '	$.post("'.$url_script.'/checkappelwebcam.php", function( data ) {';
	$langue_selector .= "   if(data == 'yes')";
	$langue_selector .= "   {";
	$langue_selector .= "   	clearInterval(intervalwebcam);";
	$langue_selector .= "		$('#popupwebcam').css('display','block');";
	$langue_selector .= "		$('#popupwebcamcontent').load('".$url_script."/getappelwebcaminfo.php');";
	$langue_selector .= "   }";
	$langue_selector .= '   });';
	$langue_selector .= '}';
	$langue_selector .= "intervalwebcam = setInterval(checkWebcamAppel,1000);";
	$langue_selector .= '</script>';
	$langue_selector .= '<script>';
	$langue_selector .= 'function playNotification()';
	$langue_selector .= '{';
	$langue_selector .= "var audio = new Audio('sound/notification.mp3');";
	$langue_selector .= 'audio.play();';
	$langue_selector .= '}';
	$langue_selector .= '</script>';
}

$multilangue_activate = getParametre("multilangue_activate");
if ($multilangue_activate == 'yes') {
	$langue_selector = '<div class="flag-selector">' . "\n";
	$SQL = "SELECT * FROM multilanguage";
	$reponse = $pdo->query($SQL);
	while ($req = $reponse->fetch()) {
		$code = $req['code'];
		$langue_selector .= '<a href="' . $url_script . '?lang=' . $code . '"><img src="' . $url_script . '/images/flag/' . $code . '.png" title="' . $req['nom'] . '"></a>' . "\n";
	}
	$langue_selector .= '</div>';
}

$class_template_loader->assign("{langue_selector}", $langue_selector);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();
