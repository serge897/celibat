<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$idoffer = AntiInjectionSQL($_REQUEST['idoffer']);

$SQL = "SELECT * FROM offre WHERE id = $idoffer";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$type = $req['type'];

if($type == 'paiement_credit')
{
	$SQL = "SELECT * FROM offre WHERE id = $idoffer";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();
	
	$class_stripe->setApiSecretAndPublisherKey(getParametre("stripe_api_secret"),getParametre("stripe_api_public"));

	$prix_subscribe = $req['prix'];
	$logourl = getParametre('logo');
	$logourl = $url_script."/images/".$logourl;

	$currency = $class_monetaire->getCurrencyCode();
	
	$titre = $req['titre'];
	$description = $req['description'];

	$class_stripe->stripePaidSimple($titre,$description,$logourl,$prix_subscribe,$currency,1,$url_script."/paid.php?md5=$md5&error=1",$url_script."/success.php",$_SESSION['md5'].'|credit');
}
else if($type == 'paiement_duree')
{
	$SQL = "SELECT * FROM offre WHERE id = $idoffer";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();
	
	$class_stripe->setApiSecretAndPublisherKey(getParametre("stripe_api_secret"),getParametre("stripe_api_public"));

	$prix_subscribe = $req['prix'];
	$logourl = getParametre('logo');
	$logourl = $url_script."/images/".$logourl;

	$currency = $class_monetaire->getCurrencyCode();
	
	$titre = $req['titre'];
	$description = $req['description'];

	$class_stripe->stripePaidSimple($titre,$description,$logourl,$prix_subscribe,$currency,1,$url_script."/paid.php?md5=$md5&error=1",$url_script."/success.php",$_SESSION['md5'].'|normal');
}
else
{
	/* A Faire (Pas fonctionnel) */
	$SQL = "SELECT * FROM offre WHERE id = $idoffer";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();
	
	$class_stripe->setApiSecretAndPublisherKey(getParametre("stripe_api_secret"),getParametre("stripe_api_public"));

	$prix_subscribe = $req['prix'];
	$logourl = getParametre('logo');
	$logourl = $url_script."/images/".$logourl;

	$currency = $class_monetaire->getCurrencyCode();
	
	$titre = $req['titre'];
	$description = $req['description'];

	$class_stripe->stripePaidSimple($titre,$description,$logourl,$prix_subscribe,$currency,1,$url_script."/paid.php?md5=$md5&error=1",$url_script."/success.php",$_SESSION['md5'].'|abonnement');
}

?>