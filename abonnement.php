<?php

include "main.php";

if(!checkuserConnected())
{
	header("Location: $url_script");
	exit;
}

$class_template_loader->showHead('connexion');
$class_template_loader->openBody();

include "header.php";

$class_template_loader->loadTemplate("abonnement.tpl");

$SQL = "SELECT * FROM user WHERE email = '".$_SESSION['email']."' AND password = '".$_SESSION['password']."'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$md5user = $req['md5'];
$date_abo = $req['paid_abonnement'];

$abonnement_info = '<div class="box-info-abonnement">';

/* Credit */
$SQL = "SELECT COUNT(*) FROM offre WHERE type = 'paiement_credit'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] != 0)
{
	$SQL = "SELECT * FROM user WHERE md5 = '".$_SESSION['md5']."'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();
	
	$nbr_credit = $req['paid_credit'];
	
	if($nbr_credit == '')
	{
		$nbr_credit = 0;
	}
	
	if($nbr_credit == 0)
	{
		$nbr_credit = '<b><font color=red>'.$nbr_credit.'</b></font>';
	}
	else
	{
		$nbr_credit = '<b><font color=green>'.$nbr_credit.'</b></font>';
	}
	
	$abonnement_info .= 'Vous avez actuellement '.$nbr_credit.' <i class="fas fa-coins"></i> '.getParametre("creditname").' sur votre compte.<br>';
}

/* Abonnement */
$SQL = "SELECT COUNT(*) FROM offre WHERE type = 'paiement_duree_engagement' OR type = 'paiement_duree'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] != 0)
{
	$limite = strtotime($date_abo);
	$now = time();
	
	if($date_abo == '')
	{
		
	}
	else
	{	
		$date_abo = explode("-",$date_abo);
		$date_abo = $date_abo[2]."/".$date_abo[1]."/".$date_abo[0];
	}
	
	if($now > $limite)
	{
		if($date_abo == NULL)
		{
			$abonnement_info .= 'Vous n\'avez pas souscrit à une formule d\'abonnement.<br>';
		}
		else
		{
			$abonnement_info .= $abo_title_expired.' <b><font color=red>'.$date_abo.'</font></b><br>';
		}
	}
	else
	{
		$abonnement_info .= $abo_title_expire_after.' <font color=green><b>'.$date_abo.'</b></font><br>';
	}
}

$abonnement_info .= '<br><br><a href="'.$url_script.'/paid.php?md5='.$_SESSION['md5'].'" class="btn"><i class="fas fa-coins"></i> Choisir une offre d\'abonnement</a>';

$abonnement_info .= '</div>';

/*$duree_abonnement = getParametre("duree_abonnement");
if($duree_abonnement == 'abonnement')
{
	$abonnement_info = '<div class="box-info-abonnement">';
	
	if($req['paid_abonnement'] == NULL)
	{
		$abonnement_info .= $abo_not_paid_info;
		$abonnement_info .= $abo_not_paid_type;
		$abonnement_info .= '<a href="'.$url_script.'/paid.php?md5='.$req['md5'].'" class="btn">'.$abo_btn_abonner.'</a>';
	}
	else
	{
		$date_abo = $req['paid_abonnement'];
				
		$limite = strtotime($date_abo);
		$now = time();
		
		$date_abo = explode("-",$date_abo);
		$date_abo = $date_abo[2]."/".$date_abo[1]."/".$date_abo[0];
		
		if($now > $limite)
		{
			$abonnement_info .= '<b>'.$abo_title_info.'</b> '.$abo_title_expired.' <b><font color=red>'.$date_abo.'</font></b><br>';
			$abonnement_info .= '<b>'.$abo_title_type.'</b> '.$abo_title_type_all_month.'<br><br>';
			$abonnement_info .= '<a href="'.$url_script.'/paid.php?md5='.$req['md5'].'" class="btn"><i class="fas fa-sync-alt"></i> '.$abo_btn_renouveller.'</a>';
		}
		else
		{
			$abonnement_info .= '<b>'.$abo_title_info.'</b> '.$abo_title_expire_after.' <font color=green><b>'.$date_abo.'</b></font><br>';
			$abonnement_info .= '<b>'.$abo_title_type.'</b> '.$abo_title_type_all_month.'<br>';
		}
	}
	$abonnement_info .= '</div>';
}
else if($duree_abonnement == 'credit')
{
	$abonnement_info = '<div class="box-info-abonnement">';
	
	$SQL = "SELECT * FROM user WHERE md5 = '".$_SESSION['md5']."'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();
	
	$nbr_credit = $req['paid_credit'];
	
	if($nbr_credit == '')
	{
		$nbr_credit = 0;
	}
	
	if($nbr_credit == 0)
	{
		$nbr_credit = '<b><font color=red>'.$nbr_credit.'</b></font>';
	}
	else
	{
		$nbr_credit = '<b><font color=green>'.$nbr_credit.'</b></font>';
	}
	
	$abonnement_info .= 'Vous avez actuellement '.$nbr_credit.' <i class="fas fa-coins"></i> Crédit sur votre compte.<br>Pour recharger votre compte et obtenir plus de crédit cliquer sur le bouton <b>Obtenir des crédits</b>';
	$abonnement_info .= '<br><br>';
	$abonnement_info .= '<a href="'.$url_script.'/paid.php?md5='.$_SESSION['md5'].'" class="btn"><i class="fas fa-coins"></i> Obtenir des crédits</a>';
	$abonnement_info .= '</div>';
}
else if($duree_abonnement == 'unique')
{
	$abonnement_info = '<div class="box-info-abonnement">';
	if($req['paid_unique'] == '')
	{
		$abonnement_info .= '<b>'.$abo_title_info.'</b> <font color=grey>'.$abo_unique_title_not_paid.'</font><br>';
		$abonnement_info .= '<b>'.$abo_title_type.'</b> '.$abo_title_type_one_shoot.'<br><br>';
		$abonnement_info .= '<a href="'.$url_script.'/paid.php?md5='.$req['md5'].'" class="btn">'.$abo_btn_paid.'</a>';
	}
	else
	{
		$abonnement_info .= '<b>'.$abo_title_info.'</b> <font color=green>'.$abo_unique_title_paid.'</font><br>';
		$abonnement_info .= '<b>'.$abo_title_type.'</b> '.$abo_title_type_one_shoot.'<br><br>';
	}
	$abonnement_info .= '</div>';
}*/

$class_template_loader->assign("{abonnement_info}",$abonnement_info);

/* Facture */

$SQL = "SELECT COUNT(*) FROM paiement WHERE status = 'valider' AND md5user = '$md5user'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] == 0)
{
	$facture_list = '<table>';
	$facture_list .= '<tr>';
	$facture_list .= '<th>'.$abo_table_title_date_facture.'</th>';
	$facture_list .= '<th>'.$abo_table_title_montant.'</th>';
	$facture_list .= '<th>'.$abo_table_title_description.'</th>';
	$facture_list .= '<th>'.$abo_table_title_option.'</th>';
	$facture_list .= '</tr>';
	$facture_list .= '</table>';
	$facture_list .= '<div class="empty-result"><i class="fas fa-file-invoice"></i><br>'.$abo_title_aucune_facture.'</div>';
}
else
{
	$facture_list = '<table>';
	$facture_list .= '<tr>';
	$facture_list .= '<th>'.$abo_table_title_date_facture.'</th>';
	$facture_list .= '<th>'.$abo_table_title_montant.'</th>';
	$facture_list .= '<th>'.$abo_table_title_description.'</th>';
	$facture_list .= '<th>'.$abo_table_title_option.'</th>';
	$facture_list .= '</tr>';
	
	$SQL = "SELECT * FROM paiement WHERE status = 'valider' AND md5user = '$md5user' ORDER BY date_paid DESC";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$facture_list .= '<tr>';
		$facture_list .= '<td>'.$class_date->transformDate($req['date_paid'],'fr').'</td>';
		$facture_list .= '<td>'.$class_monetaire->getReturnPrice($req['montant']).'</td>';
		$facture_list .= '<td>'.$req['commentaire'].'</td>';
		$facture_list .= '<td><a href="'.$url_script.'/showfacture.php?id='.$req['id'].'" target="facture" class="btn low"><i class="fas fa-search"></i> '.$btn_facturation_voir_facture.'</a></td>';
		$facture_list .= '</tr>';
	}
	
	$facture_list .= '</table>';
}

$class_template_loader->assign("{facture_list}",$facture_list);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>