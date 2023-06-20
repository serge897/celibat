<?php

include "main.php";

$class_template_loader->showHead('index');
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("404.tpl");
$class_template_loader->assign("{url_script}",$url_script);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>