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
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		$abus = AntiInjectionSQL($_REQUEST['abus']);
		
		if($abus != '')
		{
			if($md5 != '')
			{
				$SQL = "INSERT INTO abus (md5user,message) VALUES ('$md5','$abus')";
				$pdo->query($SQL);
			}
		}
		
		header("Location: signaler.php?valid=1");
		exit;
	}
}

$md5 = AntiInjectionSQL($_REQUEST['md5']);

$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$username = $req['username'];

$class_template_loader->showHeadSetSEO("Signaler un abus de $username","Signaler un abus de $username");
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("signaler.tpl");

if(isset($_REQUEST['valid']))
{
	$class_template_loader->assign("{valid}",'<div class="valid-msg">Votre signalement à bien été envoyer à nos équipe et sera traité très prochainement.</div>');
}
else
{
	$class_template_loader->assign("{valid}","");
}

$class_template_loader->assign("{username}",$username);
$class_template_loader->assign("{md5}",$md5);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>


