<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$md5_user = $_SESSION['md5'];

if(isset($_SESSION['md5']))
{
	$SQL = "INSERT INTO blacklist (md5user,md5) VALUES ('$md5_user','$md5')";
	$pdo->query($SQL);
}

?>