<?php

include "main.php";

$class_template_loader->showHead('paid');
$class_template_loader->openBody();


$md5 = AntiInjectionSQL($_REQUEST['md5']);


$class_template_loader->show();

$class_mtn_money->paidStep(getParametre("mtnmoney_pays"),getParametre("prix_subscribe"),getParametre("mtn_money_phone_number"),$url_script.'/waiting-validation.php?md5='.$md5.'&type=mtnmoney');


$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>