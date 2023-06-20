<?php

include "main.php";

$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$md5_user = $req['md5'];

$class_template_loader->showHead('qui_flash_sur_moi');
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("quiflash.tpl");
$class_template_loader->assign("{url_script}",$url_script);

$SQL = "SELECT COUNT(*) FROM flash WHERE md5 = '$md5_user'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$count = $req[0];

$class_template_user_result = new TemplateLoader();
$class_template_user_result->loadTemplate("result-user.tpl");

if($count == 0)
{
	$blacklist .= '<div class="empty-result">';
	$blacklist .= '<img src="'.$url_script.'/images/flash-on.png"><br> '.$flash_user_flash_to_me_unknow;
	$blacklist .= '</div>';
}
else
{
	$SQL = "SELECT * FROM flash WHERE md5 = '$md5_user'";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$md5 = $req['md5user'];
		
		$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		$class_template_user_result->reload();
		$class_template_user_result->assign("{user_url}",$url_script.'/'.$rr['md5'].'/profil-de-'.slugify($rr['username']).'.html');
		
		if($rr['age'] == '')
		{
			$class_template_user_result->assign("{user_age}","");
		}
		else
		{
			$class_template_user_result->assign("{user_age}",'('.$rr['age'].' ans)');
		}
		
		if($rr['pays'] == '')
		{
			$pays = 'nothing';
			$pays_unique = $title_pays_unknow;
		}
		else
		{
			$pays = $rr['pays'];
			$SQL = "SELECT * FROM pays WHERE valeur = '$pays'";
			$u = $pdo->query($SQL);
			$uu = $u->fetch();
			$pays_unique = $uu['nom'];
		}
		
		$class_template_user_result->assign("{pays_icon}",'<img src="'.$url_script.'/images/flag/'.$pays.'.png" width=12>');
		
		$region = $rr['region'];
		
		$SQL = "SELECT * FROM region WHERE id = $region";
		$u = $pdo->query($SQL);
		$uu = $u->fetch();
		
		/* Titre region */
		if($uu['titre'] == '')
		{
			$titre_region = '<font color=gray>Non spécifier</font>';
			$titre_region_unique = $title_region_unknow;
		}
		else
		{
			$titre_region = $uu['titre'];
			$titre_region_unique = $uu['titre'];
		}
		
		$type = $rr['type'];
		$SQL = "SELECT * FROM genre WHERE type = '$type'";
		$u = $pdo->query($SQL);
		$uu = $u->fetch();
		
		$type_titre = ucfirst($uu['titre']);
		
		if($rr['age'] != '')
		{
			$texte_image = ucfirst($req['username'])." (".$rr['age']." ans) - $type_titre - $pays_unique - $titre_region_unique";
		}
		else
		{
			$texte_image = ucfirst($rr['username'])." - $type_titre - $pays_unique - $titre_region_unique";
		}
		
		$class_template_user_result->assign("{region}",$titre_region);
		
		$class_template_user_result->assign("{user_username}",ucfirst($rr['username']));
		
		/* On check si une image existe */
		if(file_exists('images/photo/'.$rr['md5'].'.jpg') || file_exists('images/photo/'.$rr['md5'].'.jpeg'))
		{
			$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/photo/'.$rr['md5'].'-thumb.jpg" title="'.$texte_image.'" alt="'.$texte_image.'">');
		}
		else if(file_exists('images/photo/'.$rr['md5'].'.png'))
		{
			$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/photo/'.$rr['md5'].'-thumb.jpg" title="'.$texte_image.'" alt="'.$texte_image.'">');
		}
		else
		{	
			if($req['type'] == 'homme')
			{
				$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/man-nopicture.jpg" title="'.$texte_image.'" alt="'.$texte_image.'">');
			}
			else
			{
				$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/woman-nopicture.jpg" title="'.$texte_image.'" alt="'.$texte_image.'">');
			}
		}
		
		$isConnected = checkConnected($rr['md5']);
		if($isConnected)
		{
			$class_template_user_result->assign("{userconnected}",'<div class="user-connected-item"><img src="'.$url_script.'/images/icon-connected.png" title="'.$title_user_connected.'"></div>');
		}
		else
		{
			$class_template_user_result->assign("{userconnected}",'<div class="user-connected-item"><img src="'.$url_script.'/images/icon-disconnect.png" title="'.$title_user_disconnected.'"></div>');
		}
		
		$blacklist .= $class_template_user_result->getData();
	}
}

$class_template_loader->assign("{flash_on_me}",$blacklist);
$class_publicite->updatePublicite($class_template_loader);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>