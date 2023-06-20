<?php

include "main.php";

$payload = @file_get_contents('php://input');
$event = null;

try 
{
	$event = \Stripe\Event::constructFrom(json_decode($payload, true));
} 
catch(\UnexpectedValueException $e)
{
	// Invalid payload
	http_response_code(400);
	exit();
}

// Handle the event
switch ($event->type) 
{
	case 'payment_intent.succeeded':
	
		$paymentIntent = $event->data->object;
		
		/* Paiement success */
		$charge = $paymentIntent->charges->data;
		$id = $paymentIntent->id;
		
		/* New API */
		$amount = $paymentIntent->amount;
		$amount = round($amount / 100);
		$currency = $paymentIntent->currency;
		
		$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('','$id',now(),'$amount','stripe','waiting','abonnement')";
		$pdo->query($SQL);
		
	break;
	case 'checkout.session.completed':
	
		$paid = $event->data->object;
		$id = $paid->payment_intent;
		/* MD5 de l'annonce */
		$ref = $paid->client_reference_id;
		$ref = explode("|",$ref);
		$md5 = $ref[0];
		$type = $ref[1];
		
		sleep(2);
		
		$SQL = "UPDATE paiement SET md5user = '$md5' WHERE idtransaction = '$id'";
		$pdo->query($SQL);
		
		$SQL = "UPDATE paiement SET status = 'valider' WHERE idtransaction = '$id'";
		$pdo->query($SQL);

		/* On Update le paiement */
		if($type == 'credit')
		{
			$SQL = "SELECT * FROM paiement WHERE idtransaction = '$id'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$montant = $req['montant'];
			
			$SQL = "SELECT * FROM offre WHERE type = 'paiement_credit' AND prix = '$montant'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$nbrmsg = $req['credit'];
			
			$SQL = "UPDATE user SET paid_credit = paid_credit + ".$nbrmsg." WHERE md5 = '$md5'";
			$pdo->query($SQL);
		}
		else if($type == 'normal')
		{
			$SQL = "SELECT * FROM paiement WHERE idtransaction = '$id'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$montant = $req['montant'];
			
			$SQL = "SELECT * FROM offre WHERE type = 'paiement_duree' AND prix = '$montant'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$duree = $req['duree'];
			$duree = $duree * 30;
			
			$SQL = "UPDATE user SET paid_abonnement = now() + interval $duree day WHERE md5 = '$md5'";
			$pdo->query($SQL);
		}
		else if($type == 'abonnement')
		{
			$SQL = "SELECT * FROM paiement WHERE idtransaction = '$id'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$montant = $req['montant'];
			
			$SQL = "SELECT * FROM offre WHERE type = 'paiement_duree_engagement' AND prix = '$montant'";
			$reponse = $pdo->query($SQL);
			$req = $reponse->fetch();
			
			$duree = $req['duree'];
			$duree = $duree * 30;
			
			$SQL = "UPDATE user SET paid_abonnement = now() + interval $duree day WHERE md5 = '$md5'";
			$pdo->query($SQL);
		}
		
	break;
	default:
	
	// Unexpected event type
	http_response_code(400);
	exit();

}

http_response_code(200);

?>