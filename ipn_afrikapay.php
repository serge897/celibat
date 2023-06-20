<?php

include "main.php";

$purchaseref = $_REQUEST['purchaseref'];
$amount = $_REQUEST['amount'];
$status = $_REQUEST['status'];
$paymentref = $_REQUEST['paymentref'];

$purchaseref = explode("-",$purchaseref);

if($status == 'SUCCESS')
{
	$transaction_identifiant = $purchaseref[0];
	$md5 = $purchaseref[1];
	$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$transaction_identifiant',now(),'$amount','afrikapay','valider','abonnement')";
	$pdo->query($SQL);
	
	/* On Update le paiement */
	$duree_abonnement = getParametre("duree_abonnement");
	if($duree_abonnement == 'unique')
	{
		$SQL = "UPDATE user SET paid_unique = 'oui' WHERE md5 = '$md5'";
		$pdo->query($SQL);
	}
	else if($duree_abonnement == 'abonnement')
	{
		$SQL = "UPDATE user SET paid_abonnement = now() + interval 30 day WHERE md5 = '$md5'";
		$pdo->query($SQL);
	}
}

?>