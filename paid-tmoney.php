<?php

include "main.php";

$class_template_loader->showHead('paid');
$class_template_loader->openBody();

include "header.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);

$class_template_loader->loadTemplate("paid.tpl");

$class_template_loader->show();

$class_togocell->paidStep(getParametre("prix_subscribe"),getParametre("tmoney_phone_number"),$url_script.'/waiting-validation.php?md5='.$md5.'&type=tmoney');

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>