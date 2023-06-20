<?php

include "main.php";

$class_template_loader->showHead('paid');
$class_template_loader->openBody();

include "header.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);

if(isset($_REQUEST['step']))
{
	$step = $_REQUEST['step'];
}
else
{
	$step = 0;
}

if($step == 2)
{
	$idoffer = AntiInjectionSQL($_REQUEST['idoffer']);
	$type = AntiInjectionSQL($_REQUEST['type']);
	$class_template_loader->loadTemplate("showpage.tpl");
	
	/* Affichage des offres disponible */
	
	$d .= '<H1>Choissisez un moyen de paiement</H1>';
	
	/* On check ici les moyens de paiement activé et on affiche les options */
	$option_paiement = NULL;
	$currency = $class_monetaire->getCurrencyCode();
	
	/* Plugin de paiement */
	$SQL = "SELECT * FROM plugin WHERE affichage = 'paiement'";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$class = $class_plugin->getClassName($req['nameclass']);
		$option_paiement .= '<div class="item-btn-paid"><a href="paidplug.php?md5='.$md5.'&type='.$type.'&c='.$req['nameclass'].'&idoffer='.$idoffer.'" class="btn">'.$class->namebutton.'</a></div>';
	}

	/* Paiement Stripe */
	if($class_stripe->isCurrencySupported($currency))
	{
		if(getParametre("stripe_activate") == 'yes')
		{
			$option_paiement .= '<div class="item-btn-paid"><a href="paid-stripe.php?md5='.$md5.'&type='.$type.'&idoffer='.$idoffer.'" class="btn">'.$btn_paid_by_stripe.'</a></div>';
		}
	}
	
	/* Paiement Paypal */
	if($class_paypal->isCurrencySupported($currency))
	{
		if(getParametre("paypal_activate") == 'yes')
		{
			$option_paiement .= '<div class="item-btn-paid"><a href="paid-paypal.php?md5='.$md5.'&idoffer='.$idoffer.'&type='.$type.'" class="btn">'.$btn_paid_by_paypal.'</a></div>';
		}
	}

	/* Paiement Orange Money */
	if(getParametre("orange_money_activate") == 'yes')
	{
		$option_paiement .= '<div class="item-btn-paid"><a href="paid-orange-money.php?md5='.$md5.'" class="btn">'.$btn_paid_by_orange_money.'</a></div>';
	}

	/* Paiement Moov Flooz / Moov Money */
	if(getParametre("moov_flooz_activate") == 'yes')
	{
		$option_paiement .= '<div class="item-btn-paid"><a href="paid-moov-flooz.php?md5='.$md5.'" class="btn">'.$btn_paid_by_moov_money.'</a></div>';
	}
	/* Paiement Mtn Money */
	if(getParametre("mtn_money_activate") == 'yes')
	{
	$option_paiement .= '<div class="item-btn-paid"><a href="paid-mtn-money.php?md5='.$md5.'" class="btn">'.$btn_paid_by_mtn_money.'</a></div>';
	}
	$d .= $option_paiement;
	
	$class_template_loader->assign("{content}",$d);
	$class_template_loader->show();
}
else
{

/* Refactoring des crédits et offre */

$class_template_loader->loadTemplate("showpage.tpl");

$content = '<h1>Selectionner une offre pour continuer à discuter sur le site</h1>';

$content .= '<style>';
$content .= '.offeritem';
$content .= '{';
$content .= 'float: left;';
$content .= 'width: 30%;';
$content .= 'margin-right: 3%;';
$content .= 'padding: 15px;';
$content .= 'box-sizing: border-box;';
$content .= 'background-color: #ececec;';
$content .= 'border-radius: 5px;';
$content .= 'margin-bottom:20px;';
$content .= '}';
$content .= '.offeritem-prix';
$content .= '{';
$content .= 'font-size: 30px;';
$content .= 'text-align: center;';
$content .= 'margin-bottom: 15px;';
$content .= '}';
$content .= '.offeritem-title';
$content .= '{';
$content .= 'text-align: center;
font-size: 20px;
margin-bottom: 20px;';
$content .= '}';
$content .= '.offeritem-desc';
$content .= '{';
$content .= 'font-size: 15px;
margin-bottom: 20px;
margin-top: 20px;
min-height: 150px;
max-height: 150px;
text-align:center;';
$content .= '}';
$content .= '.offercredit-btn-achat';
$content .= '{';
$content .= 'text-align: center;';
$content .= '}';
$content .= '.item-englobe';
$content .= '{';
$content .= 'overflow: auto;
margin-bottom: 20px;';
$content .= '}';
$content .= '</style>';

$SQL = "SELECT COUNT(*) FROM offre WHERE type = 'paiement_duree'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] != 0)
{
	
	$content .= '<div class="infogeo">Accédez à toute les fonctionnalités du site internet sans aucune limite, abonnement sans engagement à renouveller manuellement à la fin de votre abonnement.</div>';
	$content .= '<div class="item-englobe">';

	$SQL = "SELECT * FROM offre WHERE type = 'paiement_duree'";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$content .= '<div class="offeritem">';
		$content .= '<div class="offeritem-prix">'.$class_monetaire->getReturnPrice($req['prix']).'</div>';
		$content .= '<div class="offeritem-title">'.$req['titre'].'</div>';
		$content .= '<div class="offeritem-desc">'.$req['description'].'</div>';
		$content .= '<div class="offercredit-btn-achat"><a href="'.$url_script.'/paid.php?md5='.$md5.'&step=2&idoffer='.$req['id'].'&type=normal" class="btn"><i class="fas fa-star"></i> Choisir cette offre</a></div>';
		
		$content .= '</div>';
	}
	
	$content .= '</div>';

}

$SQL = "SELECT COUNT(*) FROM offre WHERE type = 'paiement_duree_engagement'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] != 0)
{
	
	$content .= '<div class="infogeo">Accédez à toute les fonctionnalités du site internet sans aucune limite, abonnement avec engagement renouvelable.</div>';
	$content .= '<div class="item-englobe">';

	$SQL = "SELECT * FROM offre WHERE type = 'paiement_duree_engagement'";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$content .= '<div class="offeritem">';
		$content .= '<div class="offeritem-prix">'.$class_monetaire->getReturnPrice($req['prix']).'</div>';
		$content .= '<div class="offeritem-title">'.$req['titre'].'</div>';
		$content .= '<div class="offeritem-desc">'.$req['description'].'</div>';
		$content .= '<div class="offercredit-btn-achat"><a href="'.$url_script.'/paid.php?md5='.$md5.'&step=2&idoffer='.$req['id'].'&type=normal" class="btn"><i class="fas fa-star"></i> Choisir cette offre</a></div>';
		
		$content .= '</div>';
	}
	
	$content .= '</div>';

}

$SQL = "SELECT COUNT(*) FROM offre WHERE type = 'paiement_credit'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req[0] != 0)
{
	
	$content .= '<div class="infogeo">Obtenez des crédits utilisable pour discuter avec les membres du site sans engagement, 1 '.getParametre("creditname").' = 1 Message</div>';
	$content .= '<div class="item-englobe">';

	$SQL = "SELECT * FROM offre WHERE type = 'paiement_credit'";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$content .= '<div class="offeritem">';
		$content .= '<div class="offeritem-prix">'.$class_monetaire->getReturnPrice($req['prix']).'</div>';
		$content .= '<div class="offeritem-title">'.$req['titre'].'</div>';
		$content .= '<div class="offeritem-desc">'.$req['description'].'</div>';
		$content .= '<div class="offercredit-btn-achat"><a href="'.$url_script.'/paid.php?md5='.$md5.'&step=2&idoffer='.$req['id'].'&type=credit" class="btn"><i class="fas fa-coins"></i> Choisir cette offre</a></div>';
		
		$content .= '</div>';
	}

	$content .= '</div>';

}

$class_template_loader->assign("{content}",$content);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

}

exit;

$duree_abonnement = getParametre("duree_abonnement");
if($duree_abonnement == 'credit')
{
	if(isset($_REQUEST['step']))
	{
		$step = $_REQUEST['step'];
	}
	else
	{
		$step = 0;
	}
	
	if($step == 2)
	{
		$idoffer = AntiInjectionSQL($_REQUEST['idoffer']);
		$class_template_loader->loadTemplate("showpage.tpl");
		
		/* Affichage des offres disponible */
		
		$d .= '<H1>Choissisez un moyen de paiement</H1>';
		
		/* On check ici les moyens de paiement activé et on affiche les options */
		$option_paiement = NULL;
		$currency = $class_monetaire->getCurrencyCode();
		
		/* Plugin de paiement */
		$SQL = "SELECT * FROM plugin WHERE affichage = 'paiement'";
		$reponse = $pdo->query($SQL);
		while($req = $reponse->fetch())
		{
			$class = $class_plugin->getClassName($req['nameclass']);
			$option_paiement .= '<div class="item-btn-paid"><a href="paidplug.php?md5='.$md5.'&type=credit&c='.$req['nameclass'].'&idoffer='.$idoffer.'" class="btn">'.$class->namebutton.'</a></div>';
		}

		/* Paiement Stripe */
		if($class_stripe->isCurrencySupported($currency))
		{
			if(getParametre("stripe_activate") == 'yes')
			{
				$option_paiement .= '<div class="item-btn-paid"><a href="paid-stripe.php?md5='.$md5.'&type=credit&idoffer='.$idoffer.'" class="btn">'.$btn_paid_by_stripe.'</a></div>';
			}
		}
		/* Paiement Paypal */
		if($class_paypal->isCurrencySupported($currency))
		{
			if(getParametre("paypal_activate") == 'yes')
			{
				$option_paiement .= '<div class="item-btn-paid"><a href="paid-paypal.php?md5='.$md5.'&idoffer='.$idoffer.'" class="btn">'.$btn_paid_by_paypal.'</a></div>';
			}
		}
		/* Paiement Afrikapay */
		if(getParametre("afrikapay_activate") == 'yes')
		{
			$duree_abonnement = getParametre("duree_abonnement");
			if($duree_abonnement == 'unique')
			{
				$description = "Accés au site ".$url_script;
			}
			else if($duree_abonnement == 'abonnement')
			{
				$description = "Abonnement au site ".$url_script." pendant 1 mois";
			}
			$option_paiement .= '<center style="margin-top:10px;">'.$class_afrikapay->paidStep($prix_subscribe,$md5,$description,$url_script."/success.php",$url_script."/paid.php?md5=".$md5."&error=1").'</center>';
		}
		/* Paiement Mobiyo */
		if(getParametre("mobiyo_activate") == 'yes')
		{
			$option_paiement .= '<div class="item-btn-paid"><a href="paid-mobiyo.php?md5='.$md5.'" class="btn">'.$btn_paid_by_mobiyo.'</a></div>';
		}
		/* Paiement Orange Money */
		if(getParametre("orange_money_activate") == 'yes')
		{
			$option_paiement .= '<div class="item-btn-paid"><a href="paid-orange-money.php?md5='.$md5.'" class="btn">'.$btn_paid_by_orange_money.'</a></div>';
		}
		/* Paiement ZCash */
		if(getParametre("zamini_cash_activate") == 'yes')
		{
			$option_paiement .= '<div class="item-btn-paid"><a href="paid-zcash.php?md5='.$md5.'" class="btn">'.$btn_paid_by_zcash.'</a></div>';
		}
		/* Paiement Airtel Money */
		if(getParametre("airtel_money_activate") == 'yes')
		{
			$option_paiement .= '<div class="item-btn-paid"><a href="paid-airtelmoney.php?md5='.$md5.'" class="btn">'.$btn_paid_by_airtel.'</a></div>';
		}
		/* Paiement TMoney */
		if(getParametre("togocell_activate") == 'yes')
		{
			$option_paiement .= '<div class="item-btn-paid"><a href="paid-tmoney.php?md5='.$md5.'" class="btn">Payer avec TMoney</a></div>';
		}
		/* Paiement Moov Flooz / Moov Money */
		if(getParametre("moov_flooz_activate") == 'yes')
		{
			$option_paiement .= '<div class="item-btn-paid"><a href="paid-moov-flooz.php?md5='.$md5.'" class="btn">'.$btn_paid_by_moov_money.'</a></div>';
		}
		/* Paiement Mtn Money */
		if(getParametre("mtn_money_activate") == 'yes')
		{
		$option_paiement .= '<div class="item-btn-paid"><a href="paid-mtn-money.php?md5='.$md5.'" class="btn">'.$btn_paid_by_mtn_money.'</a></div>';
		}
		$d .= $option_paiement;
		
		$class_template_loader->assign("{content}",$d);
		$class_template_loader->show();
	}
	else
	{		
		$class_template_loader->loadTemplate("showpage.tpl");
		
		/* Affichage des offres disponible */
		
		$d .= '<H1>Selectionner une offre pour continuer à discuter sur le site</H1>';
		$d .= '<style>';
		$d .= '.offercreditbox';
		$d .= '{';
		$d .= 'margin-top: 20px;';
		$d .= 'margin-bottom: 20px;';
		$d .= 'width: 100%;';
		$d .= 'box-sizing: border-box;';
		$d .= '}';
		
		$d .= '.offercredititem {
  float: left;
  margin-right: 1%;
  text-align: center;
  background-color: #dedede;
  padding: 10px;
    padding-top: 10px;
    padding-bottom: 10px;
  box-sizing: border-box;
  padding-top: 30px;
  padding-bottom: 30px;
  min-height: 480px;
}';
		
		$d .= '.offercredit-title {
  font-size: 26px;
  font-weight: bold;
  margin-bottom: 20px;
}';

		$d .= '.offercredit-description {
  font-size: 18px;
  margin-bottom: 20px;
  max-height: 265px;
  min-height: 265px;
}';

		$d .= '.offercredit-prix {
  font-size: 34px;
  padding-bottom: 30px;
}';

		$d .= '.offercredititem {
  text-align: center;
}';
		
		$d .= '</style>';
		$d .= '<div class="offercreditbox">';
		
		$SQL = "SELECT COUNT(*) FROM credit";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$count_offer = $req[0];
		$width_item = (100 / $count_offer);
		$width_item = $width_item - 1;
		
		$SQL = "SELECT * FROM credit";
		$reponse = $pdo->query($SQL);
		while($req = $reponse->fetch())
		{
			$d .= '<div class="offercredititem" style="width:'.$width_item.'%;">';
			
			$d .= '<div class="offercredit-title">'.$req['titre'].'</div>';
			$d .= '<div class="offercredit-description">'.$req['description'].'</div>';
			$d .= '<div class="offercredit-prix">'.$class_monetaire->getReturnPrice($req['prix']).'</div>';
			$d .= '<div class="offercredit-btn-achat"><a href="'.$url_script.'/paid.php?md5='.$md5.'&step=2&idoffer='.$req['id'].'" class="btn"><i class="fas fa-coins"></i> Choisir cette offre</a></div>';
			
			$d .= '</div>';
		}
		
		$d .= '</div>';
		
		$class_template_loader->assign("{content}",$d);
		$class_template_loader->show();	
	}
}
else
{

$class_template_loader->loadTemplate("paid.tpl");

$prix_subscribe = getParametre("prix_subscribe");

/* Code de réduction */
if(isset($_REQUEST['promo']))
{
	$promo = $_REQUEST['promo'];
	if($promo == 1)
	{
		$codepromo = AntiInjectionSQL($_REQUEST['codepromo']);
		$SQL = "SELECT COUNT(*) FROM codepromo WHERE codepromo = '$codepromo'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		if($req[0] == 0)
		{
			$class_template_loader->assign("{codepromoerreur}","Ce code de réduction n'est pas validable.");
		}
		else
		{
			$SQL = "SELECT * FROM codepromo WHERE codepromo = '$codepromo'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$reductiontype = $req['reductiontype'];
			if($reductiontype == 'moins')
			{
				$prix_subscribe = $prix_subscribe - $req['value'];
			}
			else if($reductiontype == 'pourcent')
			{
				$pourcent = round($prix_subscribe / 100);
				$pourcent = round($pourcent * $req['value']);
				$prix_subscribe = $prix_subscribe - $pourcent;
			}
			
			$class_template_loader->assign("{codepromoerreur}","<font color=green><b>CODE : ".$codepromo." (Activé)</b></font>");
		}
	}
}
else
{
	$class_template_loader->assign("{codepromoerreur}","");
}

$class_template_loader->assign("{price}",$class_monetaire->getReturnPrice($prix_subscribe));

if(isset($_REQUEST['error']))
{
	$class_template_loader->assign("{error}",'<div class="error-msg">'.$paid_error_message.'</div>');
}
else
{
	$class_template_loader->assign("{error}","");
}

/* On check ici les moyens de paiement activé et on affiche les options */
$option_paiement = NULL;
$currency = $class_monetaire->getCurrencyCode();

/* Plugin de paiement */
$SQL = "SELECT * FROM plugin WHERE affichage = 'paiement'";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	$class = $class_plugin->getClassName($req['nameclass']);
	$option_paiement .= '<div class="item-btn-paid"><a href="paidplug.php?md5='.$md5.'&type=unlimited&c='.$req['nameclass'].'" class="btn">'.$class->namebutton.'</a></div>';
}

/* Paiement Stripe */
if($class_stripe->isCurrencySupported($currency))
{
	if(getParametre("stripe_activate") == 'yes')
	{
		$option_paiement .= '<div class="item-btn-paid"><a href="paid-stripe.php?md5='.$md5.'" class="btn">'.$btn_paid_by_stripe.'</a></div>';
	}
}
/* Paiement Paypal */
if($class_paypal->isCurrencySupported($currency))
{
	if(getParametre("paypal_activate") == 'yes')
	{
		$option_paiement .= '<div class="item-btn-paid"><a href="paid-paypal.php?md5='.$md5.'" class="btn">'.$btn_paid_by_paypal.'</a></div>';
	}
}
/* Paiement Afrikapay */
if(getParametre("afrikapay_activate") == 'yes')
{
	$duree_abonnement = getParametre("duree_abonnement");
	if($duree_abonnement == 'unique')
	{
		$description = "Accés au site ".$url_script;
	}
	else if($duree_abonnement == 'abonnement')
	{
		$description = "Abonnement au site ".$url_script." pendant 1 mois";
	}
	$option_paiement .= '<center style="margin-top:10px;">'.$class_afrikapay->paidStep($prix_subscribe,$md5,$description,$url_script."/success.php",$url_script."/paid.php?md5=".$md5."&error=1").'</center>';
}
/* Paiement Mobiyo */
if(getParametre("mobiyo_activate") == 'yes')
{
	$option_paiement .= '<div class="item-btn-paid"><a href="paid-mobiyo.php?md5='.$md5.'" class="btn">'.$btn_paid_by_mobiyo.'</a></div>';
}
/* Paiement Orange Money */
if(getParametre("orange_money_activate") == 'yes')
{
	$option_paiement .= '<div class="item-btn-paid"><a href="paid-orange-money.php?md5='.$md5.'" class="btn">'.$btn_paid_by_orange_money.'</a></div>';
}
/* Paiement ZCash */
if(getParametre("zamini_cash_activate") == 'yes')
{
	$option_paiement .= '<div class="item-btn-paid"><a href="paid-zcash.php?md5='.$md5.'" class="btn">'.$btn_paid_by_zcash.'</a></div>';
}
/* Paiement Airtel Money */
if(getParametre("airtel_money_activate") == 'yes')
{
	$option_paiement .= '<div class="item-btn-paid"><a href="paid-airtelmoney.php?md5='.$md5.'" class="btn">'.$btn_paid_by_airtel.'</a></div>';
}
/* Paiement TMoney */
if(getParametre("togocell_activate") == 'yes')
{
	$option_paiement .= '<div class="item-btn-paid"><a href="paid-tmoney.php?md5='.$md5.'" class="btn">Payer avec TMoney</a></div>';
}
/* Paiement Moov Flooz / Moov Money */
if(getParametre("moov_flooz_activate") == 'yes')
{
	$option_paiement .= '<div class="item-btn-paid"><a href="paid-moov-flooz.php?md5='.$md5.'" class="btn">'.$btn_paid_by_moov_money.'</a></div>';
}
/* Paiement Mtn Money */
if(getParametre("mtn_money_activate") == 'yes')
{
	$option_paiement .= '<div class="item-btn-paid"><a href="paid-mtn-money.php?md5='.$md5.'" class="btn">'.$btn_paid_by_mtn_money.'</a></div>';
}

$class_template_loader->assign("{option_paiement}",$option_paiement);
$class_template_loader->show();

}

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>