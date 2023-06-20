<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$type = AntiInjectionSQL($_REQUEST['type']);
$c = AntiInjectionSQL($_REQUEST['c']);

/* Paiement unique */
if($type == 'unlimited')
{
	$class = $class_plugin->getClassName($c);
	if($class == NULL)
	{
		header("Location: 404.php");
		exit;
	}
	else
	{
		if($class->action == 'template')
		{
			$prix_subscribe = getParametre("prix_subscribe");
			$currency = $class_monetaire->getCurrencyCode();
			
			$class->updateStatusPaiement($md5,$md5,$prix_subscribe,'Chèque','a_valider');
			
			$class_template_loader->showHead('paid');
			$class_template_loader->openBody();

			include "header.php";
			
			$class_template_loader->loadTemplate("showpage.tpl");
			
			$content = '<h1>'.$class->titre.'</h1>';
			$content .= '<p>'.$class->description.'</p>';
			$content .= $class->paid($prix_subscribe,$md5,'ok','no');
			
			$class_template_loader->assign("{content}",$content);
			
			$class_template_loader->show();
			
			include "footer.php";

			$class_template_loader->closeBody();
			$class_template_loader->closeHTML();
		}
		else
		{
			$prix_subscribe = getParametre("prix_subscribe");
			$currency = $class_monetaire->getCurrencyCode();
			
			$class->paid($prix_subscribe,$md5,'ok','no');
		}
	}
}
else if($type == 'credit')
{
	/* Paiement à credit */
	$class = $class_plugin->getClassName($c);
	if($class == NULL)
	{
		header("Location: 404.php");
		exit;
	}
	else
	{
		if($class->action == 'template')
		{
			$idoffer = AntiInjectionSQL($_REQUEST['idoffer']);
			
			$SQL = "SELECT * FROM credit WHERE id = $idoffer";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$prix_subscribe = $req['prix'];
			$currency = $class_monetaire->getCurrencyCode();
			
			$idtransaction = md5(microtime());
			
			$class->updateStatusPaiement($md5,$idtransaction,$prix_subscribe,'Chèque','a_valider');
			
			$class_template_loader->showHead('paid');
			$class_template_loader->openBody();

			include "header.php";
			
			$class_template_loader->loadTemplate("showpage.tpl");
			
			$content = '<h1>'.$class->titre.'</h1>';
			$content .= '<p>'.$class->description.'</p>';
			$content .= $class->paid($prix_subscribe,$md5,'ok','no');
			
			$class_template_loader->assign("{content}",$content);
			
			$class_template_loader->show();
			
			include "footer.php";

			$class_template_loader->closeBody();
			$class_template_loader->closeHTML();
		}
	}
}
else if($type == 'month')
{
	/* Paiement tout les mois */
	$class = $class_plugin->getClassName($c);
	if($class == NULL)
	{
		header("Location: 404.php");
		exit;
	}
	else
	{
		if($class->action == 'template')
		{
			$prix_subscribe = getParametre("prix_subscribe");
			$currency = $class_monetaire->getCurrencyCode();
			
			$class->updateStatusPaiement($md5,$md5,$prix_subscribe,'Chèque','a_valider');
			
			$class_template_loader->showHead('paid');
			$class_template_loader->openBody();

			include "header.php";
			
			$class_template_loader->loadTemplate("showpage.tpl");
			
			$content = '<h1>'.$class->titre.'</h1>';
			$content .= '<p>'.$class->description.'</p>';
			$content .= $class->paid($prix_subscribe,$md5,'ok','no');
			
			$class_template_loader->assign("{content}",$content);
			
			$class_template_loader->show();
			
			include "footer.php";

			$class_template_loader->closeBody();
			$class_template_loader->closeHTML();
		}
	}
}

/*$class_stripe->setApiSecretAndPublisherKey(getParametre("stripe_api_secret"),getParametre("stripe_api_public"));

$duree_abonnement = getParametre("duree_abonnement");
$prix_subscribe = getParametre("prix_subscribe");
$logourl = getParametre('logo');
$logourl = $url_script."/images/".$logourl;

$currency = $class_monetaire->getCurrencyCode();

if($duree_abonnement == 'unique')
{
	$titre = "Accés au site ".$url_script;
	$description = "Accés au site ".$url_script;
}
else if($duree_abonnement == 'abonnement')
{
	$titre = "Abonnement au site ".$url_script." 1 mois";
	$description = "Abonnement au site ".$url_script." pendant 1 mois";
}

$class_stripe->stripePaidSimple($titre,$description,$logourl,$prix_subscribe,$currency,1,$url_script."/paid.php?md5=$md5&error=1",$url_script."/success.php",$_SESSION['md5']);
*/

?>