<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$idoffer = AntiInjectionSQL($_REQUEST['idoffer']);

$logourl = getParametre('logo');
$langue = getParametre("langue");
$logourl = $url_script."/images/".$logourl;

$currency = $class_monetaire->getCurrencyCode();

$url = $url_script;
$url = str_replace("http://","",$url);
$url = str_replace("https://","",$url);

$paypal_account = getParametre("paypal_account");

/* Offre récuperation */

$SQL = "SELECT * FROM offre WHERE id = $idoffer";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

if($req['type'] == 'paiement_credit')
{
	$prix = $req['prix'];
	$titre = $req['titre'];

	/* Fix si FCFA pas supporter on passe en EURO */
	if($currency == 'fcfa')
	{
		$prix = ceil($prix / 655.957);
		$currency = 'EUR';
	}
	
	?>
	<form id="formpaypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input name="cmd" type="hidden" value="_xclick" />
		<input type="hidden" name="charset" value="utf-8">
		<input name="business" type="hidden" value="<?php echo $paypal_account; ?>" />
		<input name="item_name" type="hidden" value="<?php echo $titre; ?>" />
		<input name="amount" type="hidden" value="<?php echo number_format($prix,2); ?>" />
		<input name="shipping" type="hidden" value="0.00" />
		<input name="no_shipping" type="hidden" value="0" />
		<input name="currency_code" type="hidden" value="<?php echo strtoupper($currency); ?>" />
		<input name="tax" type="hidden" value="0.00" />
		<input name="lc" type="hidden" value="<?php echo strtoupper($langue); ?>" />
		<input name="bn" type="hidden" value="PP-BuyNowBF" />
		<input name="notify_url" type="hidden" value="<?php echo $url_script; ?>/ipn_paypal.php" />
		<input name="custom" type="hidden" value="<?php echo "$md5"; ?>|credit" />
		<input alt="" name="submit" src="" type="image" /><img src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" border="0" alt="" width="1" height="1" />
	</form>
	<script>
	document.forms["formpaypal"].submit(); 
	</script>
	<?php
}
if($req['type'] == 'paiement_duree')
{
	$prix = $req['prix'];
	$titre = $req['titre'];

	/* Fix si FCFA pas supporter on passe en EURO */
	if($currency == 'fcfa')
	{
		$prix = ceil($prix / 655.957);
		$currency = 'EUR';
	}
	
	?>
	<form id="formpaypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input name="cmd" type="hidden" value="_xclick" />
		<input type="hidden" name="charset" value="utf-8">
		<input name="business" type="hidden" value="<?php echo $paypal_account; ?>" />
		<input name="item_name" type="hidden" value="<?php echo $titre; ?>" />
		<input name="amount" type="hidden" value="<?php echo number_format($prix,2); ?>" />
		<input name="shipping" type="hidden" value="0.00" />
		<input name="no_shipping" type="hidden" value="0" />
		<input name="currency_code" type="hidden" value="<?php echo strtoupper($currency); ?>" />
		<input name="tax" type="hidden" value="0.00" />
		<input name="lc" type="hidden" value="<?php echo strtoupper($langue); ?>" />
		<input name="bn" type="hidden" value="PP-BuyNowBF" />
		<input name="notify_url" type="hidden" value="<?php echo $url_script; ?>/ipn_paypal.php" />
		<input name="custom" type="hidden" value="<?php echo "$md5"; ?>|normal" />
		<input alt="" name="submit" src="" type="image" /><img src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" border="0" alt="" width="1" height="1" />
	</form>
	<script>
	document.forms["formpaypal"].submit(); 
	</script>
	<?php
}
if($req['type'] == 'paiement_duree_engagement')
{
	$prix = $req['prix'];
	$titre = $req['titre'];
	
	/* Fix si FCFA pas supporter on passe en EURO */
	if($currency == 'fcfa')
	{
		$prix = ceil($prix / 655.957);
		$currency = 'EUR';
	}
	
	?>
	<form id="formpaypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_xclick-subscriptions">
		<input type="hidden" name="item_number" value="<?php echo $url_script; ?> | 1 month">
		<input type="hidden" name="charset" value="utf-8">
		<input name="business" type="hidden" value="<?php echo $paypal_account; ?>" />
		<input name="item_name" type="hidden" value="<?php echo $titre; ?>" />
		<input type="hidden" name="a3" value="<?php echo number_format($prix,2); ?>" />
		<input name="currency_code" type="hidden" value="<?php echo strtoupper($currency); ?>" />
		<input type="hidden" name="p3" value="1">
		<input type="hidden" name="t3" value="M">
		<input type="hidden" name="src" value="1">
		<!-- 6 mois max ... !-->
		<input type="hidden" name="srt" value="6">
		<input name="lc" type="hidden" value="<?php echo strtoupper($langue); ?>" />
		<input name="bn" type="hidden" value="PP-BuyNowBF" />
		<input name="notify_url" type="hidden" value="<?php echo $url_script; ?>/ipn_paypal.php" />
		<input name="custom" type="hidden" value="<?php echo "$md5"; ?>|abonnement" />
		<input alt="" name="submit" src="" type="image" /><img src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" border="0" alt="" width="1" height="1" />
	</form>
	<script>
	document.forms["formpaypal"].submit(); 
	</script>
	<?php
}

?>