<?php

include "main.php";

$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$md5 = $req['md5'];
disconnect($md5);

session_start();
unset($_SESSION['email']);
unset($_SESSION['password']);
unset($_SESSION['username']);
unset($_SESSION['md5']);
session_destroy();

header("Location: $url_script");
exit;

?>