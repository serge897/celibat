<?php

/* Updater Online */
include "main.php";

function checkBlacklist($md5)
{
	global $pdo;
	/* On tcheck si elle est pas en liste noire */
	$md5_user = $_SESSION['md5'];
	$SQL = "SELECT COUNT(*) FROM blacklist WHERE md5user = '$md5_user' AND md5 = '$md5'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	$blacklist = $req[0];
	
	if($blacklist == 0)
	{
		return false;
	}
	else
	{
		return true;
	}
}

/* Nombre de mesage disponible */
$nbr = nbrmsg();

/* Notification sonore (nouveau message) */
$SQL = "SELECT COUNT(*) FROM notificationsound WHERE md5 = '".$_SESSION['md5']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] != 0)
{
	$notif = "yes";
	$SQL = "DELETE FROM notificationsound WHERE md5 = '".$_SESSION['md5']."'";
	$pdo->query($SQL);
}
else
{
	$notif = "no";
}

/* On conserve l'information comme quoi l'utilisateur est connecté */
updateConnected($_SESSION['md5']);

/* Recherche de nouveau message tchat directe */
$newmsg = "";
$x = 0;
$SQL = "SELECT * FROM newmessagetchat WHERE md5 = '".$_SESSION['md5']."'";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($x == 0)
	{
		if(checkBlacklist($req['md5sender']) == false)
		{
			$newmsg = $req['md5sender'];
		}
	}
	else
	{
		if(checkBlacklist($req['md5sender']) == false)
		{
			$newmsg .= ",".$req['md5sender'];
		}
	}
	$x++;
}

/* Credit */

$SQL = "SELECT paid_credit FROM user WHERE md5 = '".$_SESSION['md5']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$credit = $req[0];

/* Result */
echo "$nbr|$notif|".$newmsg."|$credit";

?>