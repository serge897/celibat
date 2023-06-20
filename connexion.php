
<?php

include "main.php";

if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	if($action == 1)
	{
		$email = AntiInjectionSQL($_REQUEST['email']);
		$password = AntiInjectionSQL($_REQUEST['password']);
		
		$password = md5($password.$salt);
		
		$SQL = "SELECT COUNT(*) FROM user WHERE email = '$email' AND password = '$password'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		if($req[0] == 0)
		{
			header("Location: connexion.php?error=1");
			exit;
		}
		else
		{
			$SQL = "SELECT * FROM user WHERE email = '$email' AND password = '$password'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$md5 = $req['md5'];
			
			$compte_valider = $req['compte_valider'];
			if($compte_valider == 'non')
			{
				header("Location: connexion.php?error=2");
				exit;
			}
			else
			{
				/* Si compte banni */
				if($req['banni'] == 'oui')
				{
					header("Location: connexion.php?error=3");
					exit;
				}
				else
				{
					$_SESSION['email'] = $email;
					$_SESSION['password'] = $password;
					$_SESSION['username'] = $req['username'];
					$_SESSION['md5'] = $req['md5'];
					
					updateConnected($md5);
					
					header("Location: $url_script/index.php");
					exit;
				}
			}
		}
	}
}

$class_template_loader->showHead('connexion',"$url_script/connexion.php");
$class_template_loader->openBody();

include "header.php";

$msg = NULL;

if(isset($_REQUEST['error']))
{
	$error = $_REQUEST['error'];
	if($error == 1)
	{
		$msg = $error_msg_no_account;
	}
	if($error == 2)
	{
		$msg = $error_msg_account_not_validate;
	}
	if($error == 3)
	{
		$msg = $error_msg_ban_account;
	}
}

$class_template_loader->loadTemplate("connexion.tpl");
$class_template_loader->assign("{url_script}",$url_script);
$class_template_loader->assign("{msg}",$msg);
$class_publicite->updatePublicite($class_template_loader);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>