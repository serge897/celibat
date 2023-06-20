<?php

session_start();

include "sql.php";

$name_product = "Love Dating Script";

if ($debug_mode) {
	ini_set('display_startup_errors', 1);
	ini_set('display_errors', 1);
	error_reporting(-1);
} else {
	ini_set('display_startup_errors', 0);
	ini_set('display_errors', 0);
	error_reporting(0);
}

try {
	$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
} catch (Exception $e) {
	echo "Echec de la connexion à la base de données";
	exit();
}

function getLangueAdmin()
{
	global $pdo;

	$SQL = "SELECT * FROM configuration WHERE parametre = 'langue_administration'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	return $req['valeur'];
}

function AntiInjectionSQL($string)
{
	$string = str_replace("'", "\'", $string);
	$string = str_replace('"', '\"', $string);
	$string = str_replace(";", "\;", $string);
	$string = str_replace("`", "\`", $string);
	$string = str_replace("&", "\&", $string);
	$string = str_replace(",", "\,", $string);
	$string = str_replace("/*", "\/\*", $string);
	$string = str_replace("--", "\-\-", $string);
	$string = str_replace("#", "\#", $string);

	return $string;
}

function slugify($text)
{
	// Strip html tags
	$text = strip_tags($text);
	// Replace non letter or digits by -
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);
	// Transliterate
	setlocale(LC_ALL, 'en_US.utf8');
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	// Remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);
	// Trim
	$text = trim($text, '-');
	// Remove duplicate -
	$text = preg_replace('~-+~', '-', $text);
	// Lowercase
	$text = strtolower($text);
	// Check if it is empty
	if (empty($text)) {
		return 'n-a';
	}
	// Return result
	return $text;
}

/* La personne est connecter nous mettons à jour la base avec un Timestamp */
function updateConnected($md5)
{
	global $pdo;
	$time = time();

	$SQL = "UPDATE user SET derniere_connexion = '$time' WHERE md5 = '$md5'";
	$pdo->query($SQL);
}

/* On se deconnecte manuellement */
function disconnect($md5)
{
	global $pdo;

	$time = time() - 700;

	$SQL = "UPDATE user SET derniere_connexion = '$time' WHERE md5 = '$md5'";
	$pdo->query($SQL);
}

/* Check si un utilisateur est encore connecter au bout de 10 minutes */
function checkConnected($md5)
{
	global $pdo;

	$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	$d = $req['derniere_connexion'];
	if ($d == NULL) {
		$time = round((time()) / 60);
	} else {
		$time = round((time() - $d) / 60);
	}
	if ($time > 10) {
		return false;
	} else {
		return true;
	}
}

/* Supprime les adresse email d'un texte */
function removeEmailAdress($text)
{
	preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $text, $matches);
	$array = $matches[0];
	for ($x = 0; $x < count($array); $x++) {
		$u = $array[$x];
		$text = str_replace($u, "", $text);
	}

	return $text;
}

/* Supprime les URL d'un texte */
function removeURL($text)
{
	preg_match_all("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $text, $matches);
	$array = $matches[0];
	for ($x = 0; $x < count($array); $x++) {
		$u = $array[$x];
		$text = str_replace($u, "", $text);
	}

	return $text;
}

/* Supprime les numéro de téléphone à 10 Chiffre Français */
function removePhoneNumber($texte)
{
	/*10 Chiffre*/
	preg_match("#[0-9]{10}#", $texte, $match);
	if (count($match) != 0) {
		$texte = str_replace($match[0], "**********", $texte);
	}

	preg_match("#[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{2}#", $texte, $match);
	if (count($match) != 0) {
		$texte = str_replace($match[0], "**********", $texte);
	}

	preg_match("#[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}#", $texte, $match);
	if (count($match) != 0) {
		$texte = str_replace($match[0], "**********", $texte);
	}

	preg_match("#[0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2}#", $texte, $match);
	if (count($match) != 0) {
		$texte = str_replace($match[0], "**********", $texte);
	}

	return $texte;
}

/* Renvoie la photo de l'utilisateur par rapport au MD5 */
function getPhoto($md5)
{
	global $url_script;
	global $pdo;

	$image_exist = false;
	if (file_exists("images/photo/$md5.jpg")) {
		$image_exist = true;
		$photourl = "$url_script/images/photo/$md5.jpg";
	} else if (file_exists("images/photo/$md5.jpeg")) {
		$image_exist = true;
		$photourl = "$url_script/images/photo/$md5.jpeg";
	} else if (file_exists("images/photo/$md5.png")) {
		$image_exist = true;
		$photourl = "$url_script/images/photo/$md5.png";
	} else {
		$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();

		$type = $req['type'];

		$SQL = "SELECT * FROM genre WHERE type = '$type'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		$photourl = "$url_script/images/" . $req['miniature'];
	}


	return $photourl;
}

/* Renvoie le nombre de nouveau message non lu */
function nbrmsg()
{
	global $pdo;

	$SQL = "SELECT * FROM user WHERE email = '" . $_SESSION['email'] . "' AND password = '" . $_SESSION['password'] . "'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	$md5 = $req['md5'];

	$SQL = "SELECT COUNT(*) FROM messagerie WHERE md5_receipt = '$md5' AND lu = 'non'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	return $req[0];
}

function checkuserConnected()
{
	global $pdo;

	if (isset($_SESSION['email'])) {
		$email = $_SESSION['email'];
	} else {
		$email = '';
	}

	if (isset($_SESSION['password'])) {
		$password = $_SESSION['password'];
	} else {
		$password = '';
	}

	$SQL = "SELECT COUNT(*) FROM user WHERE email = '" . $email . "' AND password = '" . $password . "'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();
	if ($req[0] == 0) {
		return false;
	} else {
		updateConnected($_SESSION['md5']);
		return true;
	}
}

function checkAdminConnected()
{
	global $pdo;

	$SQL = "SELECT COUNT(*) FROM admin WHERE username = '" . $_SESSION['admin_username'] . "' AND password = '" . $_SESSION['admin_password'] . "'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	if ($req[0] == 0) {
		header("Location: index.php");
		exit;
	}
}

function updateParametre($parametre, $valeur)
{
	global $pdo;

	$SQL = "SELECT COUNT(*) FROM configuration WHERE parametre = '$parametre'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	$valeur = str_replace("'", "''", $valeur);

	if ($req[0] == 0) {
		$SQL = "INSERT INTO configuration (parametre,valeur) VALUES ('$parametre','$valeur')";
		$pdo->query($SQL);
	} else {
		$SQL = "UPDATE configuration SET valeur = '$valeur' WHERE parametre = '$parametre'";
		$pdo->query($SQL);
	}
}

function getParametre($parametre)
{
	global $pdo;

	$SQL = "SELECT * FROM configuration WHERE parametre = '$parametre'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	return $req['valeur'];
}

/* Retire un crédit de messagerie */
function removeCredit($md5)
{
	global $pdo;

	$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	if ($req['paid_credit'] == 0) {
		/* On ne fait rien*/
	} else {
		$SQL = "UPDATE user SET paid_credit = paid_credit - 1 WHERE md5 = '$md5'";
		$pdo->query($SQL);
	}
}

/* Test si l'utilisateur à payer sont abonnement pour bloquer des fonctionnalités renvoie (true = payer / false = pas payer) */
function isUserPaidToUse()
{
	global $pdo;

	/* Check si FREE ! */
	$SQL = "SELECT * FROM configuration WHERE parametre = 'free'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	if ($req['valeur'] == 'yes') {
		return true;
	}

	$SQL = "SELECT * FROM user WHERE email = '" . $_SESSION['email'] . "' AND password = '" . $_SESSION['password'] . "'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	$paid_unique = $req['paid_unique'];
	$paid_abonnement = $req['paid_abonnement'];
	$paid_credit = $req['paid_credit'];
	$genre = $req['type'];
	$md5 = $req['md5'];

	/* Est ce que le site est gratuit pas d'offre ?!? */
	$SQL = "SELECT COUNT(*) FROM offre";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	/* Gratuit */
	if ($req[0] == 0) {
		return true;
	} else {
		$limite = strtotime($paid_abonnement);
		$now = time();

		if ($now > $limite) {
			// Expirer
		} else {
			return true;
		}

		/* On check s'il a des credits */
		if ($paid_credit == 0) {
			// Plus de crédit
		} else {
			return true;
		}
	}

	return false;
}

include "engine/engine.php";

$class_statistique_visiteur->addVisite();

/* Load language */

$multilangue_activate = getParametre("multilangue_activate");
if ($multilangue_activate == 'yes') {
	if (isset($_REQUEST['lang'])) {
		$_SESSION['lang'] = $_REQUEST['lang'];
		include "lang/" . $_SESSION['lang'] . ".php";
	} else {
		if (isset($_SESSION['lang'])) {
			include "lang/" . $_SESSION['lang'] . ".php";
		} else if (isset($_REQUEST['lang'])) {
			$_SESSION['lang'] = $_REQUEST['lang'];
			include "lang/" . $_SESSION['lang'] . ".php";
		} else {
			$SQL = "SELECT * FROM multilanguage WHERE principal = 'yes'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			$_SESSION['lang'] = $req['code'];
			include "lang/" . $_SESSION['lang'] . ".php";
		}
	}
} else {
	$langue = getParametre("langue");
	$_SESSION['lang'] = $langue;
	if ($langue == '') {
		include "lang/fr.php";
	} else {
		include "lang/" . $langue . ".php";
	}
}
