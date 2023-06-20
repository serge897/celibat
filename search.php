<?php

include "main.php";

$class_template_loader->showHead('search',$url_script.'/search.php');
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("search.tpl");

if(isset($_REQUEST['sexe']))
{
	$sexe = AntiInjectionSQL($_REQUEST['sexe']);
	if($sexe == 'all')
	{
		$addsexe = "";
	}
	else
	{
		$addsexe = "AND type = '$sexe'";
	}
}
else
{
	$sexe = "all";
	$addsexe = "";
}

if(isset($_REQUEST['agea']))
{
	$agea = AntiInjectionSQL($_REQUEST['agea']);
	if($agea == 0)
	{
		$addage = "";
	}
	else
	{
		$addage = "AND age >= $agea";
	}
}
else
{
	$agea = 0;
	$addage = "";
}

if(isset($_REQUEST['ageb']))
{
	$ageb = AntiInjectionSQL($_REQUEST['ageb']);
	if($ageb == 0)
	{
		$addage2 = "";
	}
	else
	{
		$addage2 = "AND age <= $ageb";
	}
}
else
{
	$ageb = 0;
	$addage2 = "";
}

if(isset($_REQUEST['pays']))
{
	$pays = AntiInjectionSQL($_REQUEST['pays']);
	if($pays == '0')
	{
		$addpays = "";
	}
	else if($pays == '0')
	{
		$pays = '0';
		$addpays = "";
	}
	else
	{
		$addpays = "AND pays = '$pays'";
	}
}
else
{
	$pays = '0';
	$addpays = "";
}

if(isset($_REQUEST['region']))
{
	$region = AntiInjectionSQL($_REQUEST['region']);
	if($region == 0)
	{
		$addregion = "";
	}
	else
	{
		$addregion = "AND region = $region";
	}
}
else
{
	$region = 0;
	$addregion = "";
}

$region_option = NULL;

if($region == 0)
{
	$region_option .= '<option value="0" selected>'.$list_search_toute.'</option>';
}
else
{
	$region_option .= '<option value="0">'.$list_search_toute.'</option>';
}

$SQL = "SELECT * FROM region";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($region == $req['id'])
	{
		$region_option .= '<option value="'.$req['id'].'" selected>'.$req['titre'].'</option>';
	}
	else
	{
		$region_option .= '<option value="'.$req['id'].'">'.$req['titre'].'</option>';
	}
}

$class_template_loader->assign("{region_option}",$region_option);

$genre_option = NULL;
if($sexe == 'all')
{
	$genre_option .= '<option value="all" selected>'.$list_search_toute.'</option>';
}
else
{
	$genre_option .= '<option value="all">'.$list_search_toute.'</option>';
}

$SQL = "SELECT * FROM genre";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($sexe == $req['type'])
	{
		$genre_option .= '<option value="'.$req['type'].'" selected>'.$req['titre'].'</option>';
	}
	else
	{
		$genre_option .= '<option value="'.$req['type'].'">'.$req['titre'].'</option>';
	}
}

$class_template_loader->assign("{genre_option}",$genre_option);

$pays_option = NULL;
if($pays == '0')
{
	$pays_option = '<option value="0" selected>'.$list_search_all_country.'</option>';
}
else
{
	$pays_option = '<option value="0">'.$list_search_all_country.'</option>';
}

$SQL = "SELECT * FROM pays";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	if($pays == $req['valeur'])
	{
		$pays_option .= '<option value="'.$req['valeur'].'" selected>'.$req['nom'].'</option>';
	}
	else
	{
		$pays_option .= '<option value="'.$req['valeur'].'">'.$req['nom'].'</option>';
	}
}

$class_template_loader->assign("{pays_option}",$pays_option);

$age_option = NULL;
if($agea == 0)
{
	$age_option .= '<option value="0" selected>'.$list_search_age.'</option>';
}
else
{
	$age_option .= '<option value="0">'.$list_search_age.'</option>';
}

for($x=18;$x<99;$x++)
{
	if($agea == $x)
	{
		$age_option .= '<option value="'.$x.'" selected>'.$x.' '.$title_prefixe_age.'</option>';
	}
	else
	{
		$age_option .= '<option value="'.$x.'">'.$x.' '.$title_prefixe_age.'</option>';
	}
}

$class_template_loader->assign("{agea_option}",$age_option);

$age_option = NULL;
if($ageb == 0)
{
	$age_option .= '<option value="0" selected>'.$list_search_age.'</option>';
}
else
{
	$age_option .= '<option value="0">'.$list_search_age.'</option>';
}

for($x=18;$x<99;$x++)
{
	if($ageb == $x)
	{
		$age_option .= '<option value="'.$x.'" selected>'.$x.' '.$title_prefixe_age.'</option>';
	}
	else
	{
		$age_option .= '<option value="'.$x.'">'.$x.' '.$title_prefixe_age.'</option>';
	}
}

$class_template_loader->assign("{ageb_option}",$age_option);

/* Resultat */

$result = NULL;

$class_template_user_result = new TemplateLoader();
$class_template_user_result->loadTemplate("result-user.tpl");

$user_profile_pourcentage_validate = getParametre("user_profile_pourcentage_validate");
if($user_profile_pourcentage_validate == 'yes')
{
	$SQL = "SELECT COUNT(*) FROM user WHERE compte_valider = 'oui' AND banni = 'non' AND pourcentage = 100 $addsexe $addage $addage2 $addpays $addregion";
}
else
{
	$SQL = "SELECT COUNT(*) FROM user WHERE compte_valider = 'oui' AND banni = 'non' $addsexe $addage $addage2 $addpays $addregion";
}
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$count = $req[0];

if($count == 0)
{
	$result = '<div class="unknow-result"><i class="fas fa-search"></i><br>'.$no_result_search.'</div>';
}
else
{
	$SQL = "SELECT COUNT(*) FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();
	if($req[0] != 0)
	{
		$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$userConnectedid = $req['id'];
	}
	
	if(isset($_REQUEST['page']))
	{
		$page = $_REQUEST['page'];
		$tpage = $page * 42;
	}
	else
	{
		$page = 0;
		$tpage = 0;
	}
	
	if($user_profile_pourcentage_validate == 'yes')
	{
		$SQL = "SELECT * FROM user WHERE compte_valider = 'oui' AND banni = 'non' AND pourcentage = 100 $addsexe $addage $addage2 $addpays $addregion ORDER BY id DESC LIMIT $tpage,42";
	}
	else
	{
		$SQL = "SELECT * FROM user WHERE compte_valider = 'oui' AND banni = 'non' $addsexe $addage $addage2 $addpays $addregion ORDER BY id DESC LIMIT $tpage,42";
	}
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		if($userConnectedid != $req['id'])
		{
			$class_template_user_result->reload();
			$class_template_user_result->assign("{user_url}",$url_script.'/'.$req['md5'].'/profil-de-'.slugify($req['username']).'.html');
			
			if($req['age'] == '')
			{
				$class_template_user_result->assign("{user_age}","");
			}
			else
			{
				$class_template_user_result->assign("{user_age}",'('.$req['age'].' ans)');
			}
			
			if($req['pays'] == '')
			{
				$paysuser = 'nothing';
				$pays_unique = $title_pays_unknow;
			}
			else
			{
				$paysuser = $req['pays'];
				$SQL = "SELECT * FROM pays WHERE valeur = '$paysuser'";
				$u = $pdo->query($SQL);
				$uu = $u->fetch();
				$pays_unique = $uu['nom'];
			}
			
			$class_template_user_result->assign("{pays_icon}",'<img src="'.$url_script.'/images/flag/'.$paysuser.'.png" width=12>');
			
			$regionuser = $req['region'];
			
			$SQL = "SELECT * FROM region WHERE id = $regionuser";
			$r = $pdo->query($SQL);
			$rr = $r->fetch();
			
			/* Titre region */
			if($rr['titre'] == '')
			{
				$titre_region = '<font color=gray>Non spécifier</font>';
				$titre_region_unique = $title_region_unknow;
			}
			else
			{
				$titre_region = $rr['titre'];
				$titre_region_unique = $rr['titre'];
			}
			
			$type = $req['type'];
			$SQL = "SELECT * FROM genre WHERE type = '$type'";
			$u = $pdo->query($SQL);
			$uu = $u->fetch();
			
			$type_titre = ucfirst($uu['titre']);
			
			if($req['age'] != '')
			{
				$texte_image = ucfirst($req['username'])." (".$req['age']." ans) - $type_titre - $pays_unique - $titre_region_unique";
			}
			else
			{
				$texte_image = ucfirst($req['username'])." - $type_titre - $pays_unique - $titre_region_unique";
			}
			
			$class_template_user_result->assign("{region}",$titre_region);
			
			$class_template_user_result->assign("{user_username}",ucfirst($req['username']));
			
			/* On check si une image existe */
			if(file_exists('images/photo/'.$req['md5'].'.jpg') || file_exists('images/photo/'.$req['md5'].'.jpeg'))
			{
				$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/photo/'.$req['md5'].'-thumb.jpg" title="'.$texte_image.'" alt="'.$texte_image.'">');
			}
			else if(file_exists('images/photo/'.$req['md5'].'.png'))
			{
				$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/photo/'.$req['md5'].'-thumb.jpg" title="'.$texte_image.'" alt="'.$texte_image.'">');
			}
			else
			{	
				$type_vignette = $req['type'];
				$SQL = "SELECT * FROM genre WHERE type = '$type_vignette'";
				$z = $pdo->query($SQL);
				$zz = $z->fetch();
				$class_template_user_result->assign("{user_image}",'<img src="'.$url_script.'/images/'.$zz['miniature'].'" title="'.$texte_image.'" alt="'.$texte_image.'">');
			}
			
			$isConnected = checkConnected($req['md5']);
			if($isConnected)
			{
				$class_template_user_result->assign("{userconnected}",'<div class="user-connected-item"><img src="'.$url_script.'/images/icon-connected.png" title="'.$title_user_connected.'"></div>');
			}
			else
			{
				$class_template_user_result->assign("{userconnected}",'<div class="user-connected-item"><img src="'.$url_script.'/images/icon-disconnect.png" title="'.$title_user_disconnected.'"></div>');
			}
			
			$result .= $class_template_user_result->getData();
		}
	}
}

$class_template_loader->assign("{result}",$result);

if($user_profile_pourcentage_validate == 'yes')
{
	$SQL = "SELECT COUNT(*) FROM user WHERE compte_valider = 'oui' AND banni = 'non' AND pourcentage = 100 $addsexe $addage $addage2 $addpays $addregion";
}
else
{
	$SQL = "SELECT COUNT(*) FROM user WHERE compte_valider = 'oui' AND banni = 'non' $addsexe $addage $addage2 $addpays $addregion";
}
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$count_user = $req[0];
$nbr_page = ceil($count_user / 42);
$max_result_page = 5;
if($nbr_page < $max_result_page)
{
	$start = 0;
	$max = $nbr_page;
	
	$nav = '<div class="nav-next">';
	for($x=$start;$x<$max;$x++)
	{
		if($x == $page)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">'.($x+1).'</a> ';
		}
		else
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn">'.($x+1).'</a> ';
		}
	}
	$nav .= '</div>';
}
else
{
	$start = $page;
	if($page == ($nbr_page-1))
	{
		$start = (($page)-($max_result_page)+1);
		$max = $page+1;
		$nav = '<div class="nav-next">';
		if($page != -1)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($start-1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">◄</a> ';
		}
		for($x=$start;$x<$max;$x++)
		{
			if($x == $page)
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">'.($x+1).'</a> ';
			}
			else
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn">'.($x+1).'</a> ';
			}
		}
		$nav .= '</div>';
	}
	else if($page == ($nbr_page-2))
	{
		$start = (($page)-($max_result_page)+2);
		$max = $page+2;
		$nav = '<div class="nav-next">';
		if($page != -1)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($start-1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">◄</a> ';
		}
		for($x=$start;$x<$max;$x++)
		{
			if($x == $page)
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">'.($x+1).'</a> ';
			}
			else
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn">'.($x+1).'</a> ';
			}
		}
		if($page < $nbr_page)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($page+1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">►</a> ';
		}
		$nav .= '</div>';
	}
	else if($page == ($nbr_page-3))
	{
		$start = (($page)-($max_result_page)+3);
		$max = $page+3;
		$nav = '<div class="nav-next">';
		if($page != -1)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($start-1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">◄</a> ';
		}
		for($x=$start;$x<$max;$x++)
		{
			if($x == $page)
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">'.($x+1).'</a> ';
			}
			else
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn">'.($x+1).'</a> ';
			}
		}
		if($page < $nbr_page)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($page+1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">►</a> ';
		}
		$nav .= '</div>';
	}
	else if($page == ($nbr_page-4))
	{
		$start = (($page)-($max_result_page)+4);
		$max = $page+4;
		$nav = '<div class="nav-next">';
		if($page != -1)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($start-1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">◄</a> ';
		}
		for($x=$start;$x<$max;$x++)
		{
			if($x == $page)
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">'.($x+1).'</a> ';
			}
			else
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn">'.($x+1).'</a> ';
			}
		}
		if($page < $nbr_page)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($page+1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">►</a> ';
		}
		$nav .= '</div>';
	}
	else
	{
		$max = $max_result_page+$page;
		
		$nav = '<div class="nav-next">';
		if($page == 0)
		{
			
		}
		else if($page != -1)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($start-1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">◄</a> ';
		}
		for($x=$start;$x<$max;$x++)
		{
			if($x == $page)
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">'.($x+1).'</a> ';
			}
			else
			{
				$nav .= '<a href="'.$url_script.'/search.php?page='.$x.'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn">'.($x+1).'</a> ';
			}
		}
		if($page < $nbr_page)
		{
			$nav .= '<a href="'.$url_script.'/search.php?page='.($start+1).'&sexe='.$sexe.'&agea='.$agea.'&ageb='.$ageb.'&pays='.$pays.'&region='.$region.'" class="btn selected">►</a> ';
		}
		$nav .= '</div>';
	}
}

$class_template_loader->assign("{nav}",$nav);
$class_template_loader->assign("{url_script}",$url_script);

$class_publicite->updatePublicite($class_template_loader);

$data = $class_plugin->useTemplate($class_template_loader->getData());
$class_template_loader->setData($data);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>