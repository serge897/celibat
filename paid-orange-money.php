<?php

include "main.php";

$class_template_loader->showHead('paid');
$class_template_loader->openBody();


$md5 = AntiInjectionSQL($_REQUEST['md5']);


$class_template_loader->show();

$class_orange_money->paidStep(getParametre("prix_subscribe"),getParametre("orange_money_phone_number"),$url_script.'/waiting-validation.php?md5='.$md5.'&type=orangemoney');


$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>