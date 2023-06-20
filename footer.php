<?php

$class_template_loader->loadTemplate("prefooter.tpl");
$data = $class_footer->getFooter();
$class_template_loader->assign("{prefooter}",$data);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

$class_template_loader->loadTemplate("footer.tpl");

$class_template_loader->assign("{copyright}",getParametre("copyright"));

$class_template_loader->assign("{url_script}",$url_script);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

?>


