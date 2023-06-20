<?php

include "main.php";

$class_template_loader->showHead('paid');
$class_template_loader->openBody();

include "header.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$type = AntiInjectionSQL($_REQUEST['type']);
$montant = AntiInjectionSQL($_REQUEST['montant']);

$class_template_loader->loadTemplate("success.tpl");
$class_template_loader->assign("{url_script}",$url_script);
$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>





