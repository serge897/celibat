<?php

include "main.php";

$userid = 18;

$SQL = "SELECT * FROM user WHERE id = $userid";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$idville = $req['ville'];
$username = $req['username'];

$SQL = "SELECT * FROM ville WHERE id = $idville";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$titreville = $req['nom'];
$codepostalville = $req['codepostal'];
$longitude = $req['longitude'];
$latitude = $req['latitude'];

$maxkm = 100;

echo "Position : $titreville ($codepostalville) | Utilisateur : $username<br>";
echo "Latitude = $latitude - Longitude = $longitude<br>";

/* On check toute les villes de la base */
$SQL = "SELECT * FROM user WHERE compte_valider = 'oui' AND id != $userid";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	$villeid = $req['ville'];
	$username = $req['username'];
	
	$SQL = "SELECT * FROM ville WHERE id = $villeid";
	$r = $pdo->query($SQL);
	$rr = $r->fetch();
	$lat = $rr['latitude'];
	$long = $rr['longitude'];
	
	$distance = $class_geolocalisation->distance($latitude, $longitude, $lat, $long, "K");
	if($distance < $maxkm)
	{
		echo "Moins de $maxkm Km à $distance km - $username<br>";
	}
	else
	{
		echo "Trop loin $distance Km - $username<br>";
	}
}

?>