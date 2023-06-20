<?php

include "main.php";

if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	/* Inscription utilisateur */
	if ($action == 1) {
		$email = AntiInjectionSQL($_REQUEST['email']);
		$email = strtolower($email);
		$phone = AntiInjectionSQL($_REQUEST['phone']);
		$phone = strtolower($phone);
		$username = AntiInjectionSQL($_REQUEST['username']);
		$password = AntiInjectionSQL($_REQUEST['password']);
		$jesuis = AntiInjectionSQL($_REQUEST['jesuis']);
		$vous = AntiInjectionSQL($_REQUEST['vous']);

		/* Vous êtes n'est pas renseigner */
		if ($jesuis == '0') {
			header("Location: $url_script/subscribe.php?errorjesuis=1&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		/* Vous n'est pas renseigner */
		if ($vous == '0') {
			header("Location: $url_script/subscribe.php?errorrecherche=1&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		/* On check que l'email n'existe pas déjà dans la base */
		if ($email != '') {
			$SQL = "SELECT COUNT(*) FROM user WHERE email = '$email'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();

			if ($req[0] != 0) {
				header("Location: $url_script/subscribe.php?erroremail=1&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
				exit;
			}
		} else {
			/* Telephone */
			$SQL = "SELECT COUNT(*) FROM user WHERE email = '$phone'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();

			if ($req[0] != 0) {
				header("Location: $url_script/subscribe.php?errorphone=1&phone=$phone&username=$username&jesuis=$jesuis&vous=$vous");
				exit;
			}
		}

		/* On check le nom d'utilisateur */
		$SQL = "SELECT COUNT(*) FROM user WHERE username = '$username'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();

		if ($req[0] != 0) {
			header("Location: $url_script/subscribe.php?errorusername=1&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		/* On check que le nom d'utilisateur ne depasse pas 12 caractères */
		if (strlen($username) > 12) {
			header("Location: $url_script/subscribe.php?errorusername=3&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		if ($username == '') {
			header("Location: $url_script/subscribe.php?errorusername=4&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		/* On check si le nom d'utilisateur comporte des espaces */
		if (strpos($username, ' ') !== false) {
			header("Location: $url_script/subscribe.php?errorusername=5&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		if (strtolower($username) == 'admin' || strtolower($username) == 'administrateur') {
			header("Location: $url_script/subscribe.php?errorusername=1&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		/* Mot de passe vide */
		if ($password == '') {
			header("Location: $url_script/subscribe.php?errorusername=2&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		/* Moins de 8 caractères */
		if (strlen($password) < 8) {
			header("Location: $url_script/subscribe.php?errorusername=2&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
			exit;
		}

		$password = md5($password . $salt);
		$md5 = md5(microtime());

		$compte_valider = 'non';

		/* Captcha */
		$captcha_activated = getParametre("captcha_activated");
		if ($captcha_activated == 'yes') {
			$c = $_REQUEST['c'];
			$copet = $_REQUEST['copet'];
			$captcha = $_REQUEST['captcha'];

			if ($captcha != '') {
				header("Location: $url_script/subscribe.php?errorusername=6&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
				exit;
			}

			$reponse = $class_captcha->getReponseCaptcha($c, 'number');
			if ($copet != $reponse) {
				header("Location: $url_script/subscribe.php?errorusername=6&email=$email&username=$username&jesuis=$jesuis&vous=$vous");
				exit;
			}
		}

		/* Envoie d'un email de validation de compte */
		if ($email != '') {
			/* Credit offer ?!? */
			$creditoffer = getParametre("creditoffer");
			if ($creditoffer == '') {
				$creditoffer = 0;
			}

			$SQL = "INSERT INTO user (md5,email,username,password,type,cherche,cherche_age_depart,cherche_age_fin,cherche_distance,date_inscription,derniere_connexion,compte_valider,banni,description,age,region,pays,ville,paid_unique,paid_abonnement,paid_credit,notification,pourcentage,bot) VALUES ('$md5','$email','$username','$password','$jesuis','$vous','18','90','proche',NOW(),'" . time() . "','$compte_valider','non','','',0,'','0','',NULL,'$creditoffer','yes',0,'')";
			$pdo->query($SQL);

			$subject = getParametre("sujet_validation_inscription_website");
			$message = getParametre("message_validation_inscription_website");
			$message = str_replace("{br}", "<br>", $message);
			$message = str_replace("{username}", $username, $message);
			$message = str_replace("{link_validation}", '<a href="' . $url_script . '/validation.php?md5=' . $md5 . '">Finaliser l\'inscription à mon compte</a>', $message);
			$class_email->sendMailTemplate($email, $subject, $message);

			header("Location: " . $url_script . "/index.php?valid=1");
			exit;
		} else {
			/* Telephone */

			/* Credit offer ?!? */
			$creditoffer = getParametre("creditoffer");
			if ($creditoffer == '') {
				$creditoffer = 0;
			}

			$SQL = "INSERT INTO user (md5,email,username,password,type,cherche,cherche_age_depart,cherche_age_fin,cherche_distance,date_inscription,derniere_connexion,compte_valider,banni,description,age,region,pays,ville,paid_unique,paid_abonnement,paid_credit,notification,pourcentage,bot) VALUES ('$md5','$phone','$username','$password','$jesuis','$vous','18','90','proche',NOW(),'" . time() . "','$compte_valider','non','','',0,'','0','',NULL,'$creditoffer','yes',0,'')";
			$pdo->query($SQL);

			header("Location: " . $url_script . "/validation.php?md5=" . $md5);
			exit;
		}
	}
}


$class_template_loader->showHead('index', "$url_script/index.php");
$class_template_loader->openBody();

include "header.php";

$msg = NULL;

if (isset($_REQUEST['error'])) {
	$error = $_REQUEST['error'];
	if ($error == 1) {
		$msg = $error_msg_no_account;
	}
	if ($error == 2) {
		$msg = $error_msg_account_not_validate;
	}
	if ($error == 3) {
		$msg = $error_msg_ban_account;
	}
}

$class_template_loader->loadTemplate("subscribe.tpl");
$class_template_loader->assign("{url_script}", $url_script);

$valid = "";

if (isset($_REQUEST['valid'])) {
	$class_template_loader->assign("{valid}", "Un email vient de vous être envoyer pour valider votre compte.");
} else {
	$class_template_loader->assign("{valid}", "");
}

if (isset($_REQUEST['errorphone'])) {
	$errorphone = $_REQUEST['errorphone'];
	if ($errorphone == 1) {
		$class_template_loader->assign("{error_email}", $error_phone_already_exist);
	}
}

if (isset($_REQUEST['erroremail'])) {
	$error = $_REQUEST['erroremail'];
	if ($error == 1) {
		$class_template_loader->assign("{error_email}", "Cette adresse email est déjà utiliser par un autre compte");
	} else {
		//$class_template_loader->assign("{error_email}","");
	}
} else if (isset($_REQUEST['errorusername'])) {
	$errorusername = $_REQUEST['errorusername'];
	if ($errorusername == 1) {
		$class_template_loader->assign("{error_email}", "Ce nom d'utilisateur est déjà utiliser par un autre membres");
	} else if ($errorusername == 2) {
		$class_template_loader->assign("{error_email}", "Le mot de passe de votre compte doit contenir 8 caractères minimum");
	} else if ($errorusername == 3) {
		$class_template_loader->assign("{error_email}", "Le nom d'utilisateur est limitée à 12 caractères maximum");
	} else if ($errorusername == 4) {
		$class_template_loader->assign("{error_email}", "Vous devez indiquer un nom d'utilisateur");
	} else if ($errorusername == 5) {
		$class_template_loader->assign("{error_email}", "Le nom d'utilisateur ne peux pas comporter d'espace");
	} else {
		$class_template_loader->assign("{error_email}", "");
	}
} else if (isset($_REQUEST['errorjesuis'])) {
	$errorjesuis = $_REQUEST['errorjesuis'];
	if ($errorjesuis == 1) {
		$class_template_loader->assign("{error_email}", "Vous devez indiquer votre sexe");
	}
} else if (isset($_REQUEST['errorrecherche'])) {
	$errorrecherche = $_REQUEST['errorrecherche'];
	if ($errorrecherche == 1) {
		$class_template_loader->assign("{error_email}", "Vous devez indiquer votre recherche");
	}
} else {
	$class_template_loader->assign("{error_email}", "");
}

if (isset($_REQUEST['email'])) {
	$class_template_loader->assign("{email}", $_REQUEST['email']);
} else {
	$class_template_loader->assign("{email}", "");
}

if (isset($_REQUEST['username'])) {
	$class_template_loader->assign("{username}", $_REQUEST['username']);
} else {
	$class_template_loader->assign("{username}", "");
}

$jesuis = NULL;
$jesuis = '<select name="jesuis" class="inputbox">';
$jesuis .= '<option value="0">' . $title_vous_etes . '</option>';

$SQL = "SELECT * FROM genre";
$reponse = $pdo->query($SQL);
while ($req = $reponse->fetch()) {
	$jesuis .= '<option value="' . $req['type'] . '">' . $req['jesuis'] . '</option>';
}

$jesuis .= '</select>';

$class_template_loader->assign("{jesuis}", $jesuis);

$jecherche = NULL;
$jecherche = '<select name="vous" class="inputbox">';
$jecherche .= '<option value="0">' . $title_votre_recherche . '</option>';

$SQL = "SELECT * FROM genre";
$reponse = $pdo->query($SQL);
while ($req = $reponse->fetch()) {
	$jecherche .= '<option value="' . $req['type'] . '">' . $req['jecherche'] . '</option>';
}

$jecherche .= '</select>';

$class_template_loader->assign("{jerecherche}", $jecherche);

$class_publicite->updatePublicite($class_template_loader);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();
