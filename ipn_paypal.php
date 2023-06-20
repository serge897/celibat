<?php

include "main.php";

$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) 
{
	$keyval = explode ('=', $keyval);
	if (count($keyval) == 2)
		$myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
$req = 'cmd=_notify-validate';
if (function_exists('get_magic_quotes_gpc')) 
{
	$get_magic_quotes_exists = true;
}

foreach ($myPost as $key => $value) 
{
  if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) 
  {
	$value = urlencode(stripslashes($value));
  } 
  else 
  {
	$value = urlencode($value);
  }  
  $req .= "&$key=$value";
}

// Step 2: POST IPN data back to PayPal to validate
$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
// In wamp-like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "https://curl.haxx.se/docs/caextract.html" and set
// the directory path of the certificate as shown below:
curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if ( !($res = curl_exec($ch)) ) 
{
  // error_log("Got " . curl_error($ch) . " when processing IPN data");
  curl_close($ch);
  exit;
}

if (strcmp ($res, "VERIFIED") == 0) 
{
	$custom = $_POST['custom'];
	$custom = explode("|",$custom);
	$md5 = $custom[0];
	$type = $custom[1];
	$payment_amount = $_POST['mc_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$payer_email = $_POST['payer_email'];
	
	if($type == 'credit')
	{
		$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$txn_id',now(),'$payment_amount','paypal','valider','credit')";
		$pdo->query($SQL);
		
		/* Fix paypal montant avec virgule 216.00 à arrondir sinon ne trouve pas */
		$montant = round($payment_amount);
		
		$SQL = "SELECT * FROM offre WHERE type = 'paiement_credit' AND prix = '$montant'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$nbrmsg = $req['credit'];
		
		$SQL = "UPDATE user SET paid_credit = paid_credit + $nbrmsg WHERE md5 = '$md5'";
		$pdo->query($SQL);
	}
	else if($type == 'normal')
	{
		$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$txn_id',now(),'$payment_amount','paypal','valider','abonnement')";
		$pdo->query($SQL);
		
		/* Fix paypal montant avec virgule 216.00 à arrondir sinon ne trouve pas */
		$montant = round($payment_amount);
		
		$SQL = "SELECT * FROM offre WHERE type = 'paiement_duree' AND prix = '$montant'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();
		
		$duree = $req['duree'];
		$duree = $duree * 30;
		
		$SQL = "UPDATE user SET paid_abonnement = now() + interval $duree day WHERE md5 = '$md5'";
		$pdo->query($SQL);
	}
	else
	{
		$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$txn_id',now(),'$payment_amount','paypal','valider','abonnement')";
		$pdo->query($SQL);
		
		$SQL = "UPDATE user SET paid_abonnement = now() + interval 30 day WHERE md5 = '$md5'";
		$pdo->query($SQL);
	}
}
else if (strcmp ($res, "INVALID") == 0) 
{
	$custom = $_POST['custom'];
}

curl_close($ch);

?>