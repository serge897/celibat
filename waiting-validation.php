<?php

include "main.php";

$class_template_loader->showHead('paid');
$class_template_loader->openBody();

include "header.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$type = AntiInjectionSQL($_REQUEST['type']);
$montant = AntiInjectionSQL($_REQUEST['montant']);

/* Si le paiement et de type Orange Money */
if($type == 'orangemoney')
{
	$mobicash_phone = AntiInjectionSQL($_REQUEST['mobicash_phone']);
	
	/* On ajoute le paiement */
	$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$mobicash_phone',NOW(),'$montant','orange_money','a_valider','abonnement')";
	$pdo->query($SQL);
}
/* Zcash */
if($type == 'zcash')
{
	$mobicash_phone = AntiInjectionSQL($_REQUEST['mobicash_phone']);
	
	/* On ajoute le paiement */
	$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$mobicash_phone',NOW(),'$montant','zcash','a_valider','abonnement')";
	$pdo->query($SQL);
}
/* Airtel Money */
if($type == 'airtelmoney')
{
	$mobicash_phone = AntiInjectionSQL($_REQUEST['mobicash_phone']);
	
	/* On ajoute le paiement */
	$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$mobicash_phone',NOW(),'$montant','airtelmoney','a_valider','abonnement')";
	$pdo->query($SQL);
}
/* Si Moov Money */
if($type == 'moovmoney')
{
	$mobicash_phone = AntiInjectionSQL($_REQUEST['mobicash_phone']);
	
	/* On ajoute le paiement */
	$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$mobicash_phone',NOW(),'$montant','moov_money','a_valider','abonnement')";
	$pdo->query($SQL);
}
/* Si Mtn Money */
if($type == 'mtnmoney')
{
	$mobicash_phone = AntiInjectionSQL($_REQUEST['mobicash_phone']);
	
	/* On ajoute le paiement */
	$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$mobicash_phone',NOW(),'$montant','mtn_money','a_valider','abonnement')";
	$pdo->query($SQL);
}
if($type == 'tmoney')
{
	$mobicash_phone = AntiInjectionSQL($_REQUEST['mobicash_phone']);
	
	/* On ajoute le paiement */
	$SQL = "INSERT INTO paiement (md5user,idtransaction,date_paid,montant,type_transaction,status,commentaire) VALUES ('$md5','$mobicash_phone',NOW(),'$montant','tmoney','a_valider','abonnement')";
	$pdo->query($SQL);
}

$class_template_loader->loadTemplate("waiting-paid.tpl");

$class_template_loader->assign("{url_script}",$url_script);

$class_template_loader->show();

include "footer.php";

$class_template_loader->closeBody();
$class_template_loader->closeHTML();

?>