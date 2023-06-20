<?php

include "main.php";

if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	if ($action == 1) {
		$email = AntiInjectionSQL($_REQUEST['email']);

		$SQL = "SELECT COUNT(*) FROM user WHERE email = '$email'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();

		if ($req[0] == 0) {
			header("Location: lostpassword.php?error=1");
			exit;
		} else {
			$SQL = "SELECT * FROM user WHERE email = '$email'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();

			$username = $req['username'];
			$md5 = $req['md5'];

			/* Dans ce cas on envie un email à l'utilisateur pour réinitialiser son compte */
			$subject = getParametre("sujet_lost_password_email");

			$message = getParametre("message_lost_password_email");
			$message = str_replace("{br}", '<br>', $message);
			$message = str_replace("{username}", $username, $message);
			$message = str_replace("{link_reinit}", '<a href="' . $url_script . '/reinit.php?md5=' . $md5 . '" class="btn">' . $btn_lost_password_reinit_mail . '</a>', $message);

			$class_email->sendMailTemplate($email, $subject, $message);

			header("Location: lostpassword.php?valid=1");
			exit;
		}
	}
}

$class_template_loader->showHead('lostpassword', "$url_script/lostpassword.php");
$class_template_loader->openBody();

include "header.php";

$msg = NULL;

if (isset($_REQUEST['error'])) {
	$msg = '<div class="error-msg">' . $lost_password_error_no_account . '</div>';
}
if (isset($_REQUEST['valid'])) {
	$msg = '<div class="valid-msg">' . $lost_password_valid_message . '</div>';
}

$class_template_loader->loadTemplate("lostpassword.tpl");
$class_template_loader->assign("{url_script}", $url_script);
$class_template_loader->assign("{msg}", $msg);
$class_publicite->updatePublicite($class_template_loader);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();
