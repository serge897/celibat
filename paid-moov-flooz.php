<?php

include "main.php";

$class_template_loader->showHead('paid');
$class_template_loader->openBody();


$md5 = AntiInjectionSQL($_REQUEST['md5']);


$class_template_loader->show();

$class_moov_money->paidStep(getParametre("moovflooz_pays"),getParametre("prix_subscribe"),getParametre("moov_flooz_phone_number"),$url_script.'/waiting-validation.php?md5='.$md5.'&type=moovmoney');


$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>