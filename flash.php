<?php

include "main.php";

$SQL = "SELECT * FROM user WHERE email = '" . $_SESSION['email'] . "' AND password = '" . $_SESSION['password'] . "'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$md5_user = $req['md5'];

if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	if ($action == 1) {
		$md5 = AntiInjectionSQL($_REQUEST['md5']);
		$SQL = "DELETE FROM flash WHERE md5user = '$md5_user' AND md5 = '$md5'";
		$pdo->query($SQL);

		header("Location: flash.php");
		exit;
	}
}

$class_template_loader->showHead('flash');
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("flash.tpl");
$class_template_loader->assign("{url_script}", $url_script);

$blacklist = "";

$SQL = "SELECT COUNT(*) FROM flash WHERE md5user = '$md5_user'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$count = $req[0];

if ($count == 0) {
	$blacklist .= '<div class="empty-result">';
	$blacklist .= '<img src="' . $url_script . '/images/flash-off.png"><br> ' . $flash_aucun_flash_user;
	$blacklist .= '</div>';
} else {
	$SQL = "SELECT * FROM flash WHERE md5user = '$md5_user'";
	$reponse = $pdo->query($SQL);
	while ($req = $reponse->fetch()) {
		$md5 = $req['md5'];

		$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();

		$username = $rr['username'];

		$photourl = getPhoto($md5);

		$blacklist .= '<div class="item-msg full">';
		$blacklist .= '<a href="' . $url_script . '/profile.php?md5=' . $md5 . '"><div class="item-msg-photo"><img src="' . $photourl . '"></div></a>';
		$blacklist .= '<div class="item-msg-info">';
		$blacklist .= '<a href="' . $url_script . '/profile.php?md5=' . $md5 . '"><div class="item-msg-pseudo">' . ucfirst($username) . '</div></a>';
		$blacklist .= '</div>';
		$blacklist .= '<div class="item-msg-btn"><a href="' . $url_script . '/flash.php?action=1&md5=' . $md5 . '" class="btn"><img src="' . $url_script . '/images/flash-white.png" width=15> ' . $btn_stop_flash . '</a></div>';
		$blacklist .= '</div>';
	}
}

$class_template_loader->assign("{blacklist}", $blacklist);
$class_publicite->updatePublicite($class_template_loader);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();
