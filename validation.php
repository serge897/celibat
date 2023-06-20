<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$SQL = "SELECT COUNT(*) FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();
$count = $req[0];

if($count == 0)
{
	$msg = "Le compte que vous essayez de valider n'existe pas ou plus, veuillez vérifier l'exactitude de votre mail ou contacter l'administrateur du site internet.";
	$msg .= '<br><br><a href="'.$url_script.'" class="btn">Retour à la page d\'accueil</a>';
}
else
{
	/* On check la methode de paiement du script */
	$method_paiement = getParametre("methode_reglement");
	if($method_paiement == 'free')
	{
		/* L'inscription au site est gratuite */
		$SQL = "UPDATE user SET compte_valider = 'oui' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$msg = "Votre compte est désormais valider. Vous pouvez dés à présent vous connecter à votre compte et commencer à faire des rencontres.";
		$msg .= '<br><br><a href="connexion.php" class="btn">Se connecter</a>';
	}
	else if($method_paiement == 'all_paid_subscribe')
	{
		/* L'inscription au site est payante pour tous le monde */
		$SQL = "UPDATE user SET compte_valider = 'oui' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		header("Location: paid.php?md5=$md5");
		exit;
	}
	else if($method_paiement == 'all_paid_send_msg')
	{
		/* L'inscription au site est payante pour tous le monde */
		$SQL = "UPDATE user SET compte_valider = 'oui' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		$msg = "Votre compte est désormais valider. Vous pouvez dés à présent vous connecter à votre compte et commencer à faire des rencontres.";
		$msg .= '<br><br><a href="connexion.php" class="btn">Se connecter</a>';
	}
	else if($method_paiement == 'genre_paid_subscribe')
	{
		/* L'inscription au site est payante pour un certain genre */
		$genre_paid = getParametre("genre_subscribe_paid");
		
		$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		if($req['type'] == $genre_paid)
		{
			/* Le genre est identique donc il doit payer */
			$SQL = "UPDATE user SET compte_valider = 'oui' WHERE md5 = '$md5'";
			$pdo->query($SQL);
			
			header("Location: paid.php?md5=$md5");
			exit;
		}
		else
		{
			/* Il ne paye pas */
			$SQL = "UPDATE user SET compte_valider = 'oui' WHERE md5 = '$md5'";
			$pdo->query($SQL);
			
			$msg = "Votre compte est désormais valider. Vous pouvez dés à présent vous connecter à votre compte et commencer à faire des rencontres.";
			$msg .= '<br><br><a href="connexion.php" class="btn">Se connecter</a>';
		}
	}
}

$class_template_loader->showHead('index');
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("validation.tpl");
$class_template_loader->assign("{msg}",$msg);
$class_template_loader->show();

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>