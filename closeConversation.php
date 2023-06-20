<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);

$SQL = "DELETE FROM newmessagetchat WHERE md5sender = '$md5' AND md5 = '".$_SESSION['md5']."'";
$pdo->query($SQL);

?>