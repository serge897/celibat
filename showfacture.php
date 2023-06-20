<?php

include "main.php";
include "engine/fpdf.php";

$id = AntiInjectionSQL($_REQUEST['id']);

$SQL = "SELECT * FROM paiement WHERE id = $id";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$facture_number = $req['id'];
$date_achat = $req['date_paid'];
$date_achat = explode(" ",$date_achat);
$date = $date_achat[0];
$heure = $date_achat[1];
$date = explode("-",$date);
$date = $date[2]."/".$date[1]."/".$date[0];
$date_achat = $date." à ".$heure;

$montant = $req['montant'];
$commentaire = $req['commentaire'];

$moyen_paiement = $req['type_transaction'];
if($moyen_paiement == 'orange_money')
{
	$moyen_paiement = 'Orange Money';
}
if($money_paiement == 'moov_money')
{
	$moyen_paiement = 'Moov Money';
}

$md5user = $req['md5user'];

$SQL = "SELECT * FROM user WHERE md5 = '$md5user'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$email = $req['email'];
$username = $req['username'];

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',18);
$pdf->SetXY(10,10);
$pdf->Cell(40,10,utf8_decode(getParametre("nom_societe")));
$pdf->SetXY(10,10);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,20,utf8_decode(getParametre("adresse_societe")));
$pdf->SetXY(10,19);
$pdf->Cell(40,10,utf8_decode(getParametre("codepostal_ville_societe")));

/* Logo */
$pdf->Image($url_script.'/images/'.getParametre("logo"),170,12,27);

$pdf->SetXY(10,40);
$pdf->SetFont('Arial','B',20);
$pdf->SetTextColor(128);
$pdf->Cell(40,10,utf8_decode('Facture n°').$facture_number);

$pdf->SetXY(10,46);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(40,10,'Date : '.utf8_decode($date_achat));

$pdf->SetXY(10,50);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(40,10,utf8_decode('Réglement par : '.$moyen_paiement));

$pdf->SetXY(10,54);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(40,10,utf8_decode('Réglement effectuer le : '.$date_achat));

$pdf->SetTextColor(128);

$pdf->SetXY(130,40);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(40,10,utf8_decode('Email client : '.$email));

$pdf->SetXY(130,44);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(40,10,utf8_decode('Utilisateur : '.ucfirst($username)));

$pdf->SetXY(10,80);
$pdf->SetTextColor(255);
$pdf->SetFillColor(48,127,192);
$pdf->Cell(30,10,utf8_decode('Quantité'),1,1,'C',true);
$pdf->SetXY(40,80);
$pdf->Cell(80,10,utf8_decode('Désignation'),1,1,'C',true);
$pdf->SetXY(120,80);
$pdf->Cell(40,10,utf8_decode('Prix Unit. TTC'),1,1,'C',true);
$pdf->SetXY(160,80);
$pdf->Cell(40,10,utf8_decode('Montant TTC'),1,1,'C',true);

/* +10 en Y */
$montant = number_format($montant,2)." eur.";
	
$pdf->SetXY(10,90);
$pdf->SetFillColor(255,255,255);
$pdf->SetTextColor(0);
$pdf->Cell(30,10,'1',1,1,'C',true);
$pdf->SetXY(40,90);
$pdf->Cell(80,10,$commentaire,1,1,'C',true);
$pdf->SetXY(120,90);
$pdf->Cell(40,10,utf8_decode($montant),1,1,'C',true);
$pdf->SetXY(160,90);
$pdf->Cell(40,10,utf8_decode($montant),1,1,'C',true);

/* +15 en Y */
$pdf->SetXY(135,105);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,10,'Total de la facture : '.$montant);

$pdf->SetXY(55,265);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','B',7);
$pdf->Cell(40,10,utf8_decode(getParametre("text_bas_facture")));

$pdf->Output();

?>