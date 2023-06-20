<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);

if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	if($action == 1)
	{
		$password = AntiInjectionSQL($_REQUEST['password']);
		$confirm = AntiInjectionSQL($_REQUEST['confirm']);
		
		if($password == '')
		{
			header("Location: reinit.php?md5=$md5&error=1");
			exit;
		}
		
		if($confirm == '')
		{
			header("Location: reinit.php?md5=$md5&error=2");
			exit;
		}
		
		if($password != $confirm)
		{
			header("Location: reinit.php?md5=$md5&error=3");
			exit;
		}
		
		$password = md5($password.$salt);
		
		$SQL = "UPDATE user SET password = '$password' WHERE md5 = '$md5'";
		$pdo->query($SQL);
		
		header("Location: reinit.php?md5=$md5&valid=1");
		exit;
	}
}

$SQL = "SELECT COUNT(*) FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] == 0)
{
	header("Location: $url_script");
	exit;
}

$class_template_loader->showHead('reinitpassword');
$class_template_loader->openBody();

include "header.php";

$msg = NULL;

if(isset($_REQUEST['error']))
{
	$error = $_REQUEST['error'];
	if($error == 1)
	{
		$msg = $reinit_error_msg_password_empty;
	}
	if($error == 2)
	{
		$msg = $reinit_error_msg_confirm_empty;
	}
	if($error == 3)
	{
		$msg = $reinit_error_msg_password_not_match;
	}
}

if(isset($_REQUEST['valid']))
{
	$valid = $_REQUEST['valid'];
	if($valid == 1)
	{
		$msg = $reinit_valid_msg_confirm;
	}
}

$class_template_loader->loadTemplate("reinitpassword.tpl");
$class_template_loader->assign("{url_script}",$url_script);
$class_template_loader->assign("{msg}",$msg);
$class_template_loader->assign("{md5}",$md5);
$class_publicite->updatePublicite($class_template_loader);
$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>