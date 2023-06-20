<?php

include "main.php";

$slug = AntiInjectionSQL($_REQUEST['slug']);

$SQL = "SELECT COUNT(*) FROM page WHERE slug = '$slug'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] == 0)
{
	header("Location: 404.php");
	exit;
}

$SQL = "SELECT * FROM page WHERE slug = '$slug'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$titre = $req['titre'];
$contenue = $req['contenue'];
$seo_titre = $req['seo_titre'];
$seo_description = $req['seo_description'];

$content = '<H1>'.$titre.'</H1>';
$content .= $contenue;

$class_template_loader->showHeadSetSEO($seo_titre,$seo_description);
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("showpage.tpl");
$class_template_loader->assign("{content}",$content);
$class_publicite->updatePublicite($class_template_loader);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>