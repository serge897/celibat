<?php

/* Update v1.74.4 */
include "sql.php";

try
{
	$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
}
catch(Exception $e)
{
	echo "Echec de la connexion à la base de données";
	exit();
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>
<style>
body
{
	font-family: 'Open Sans', sans-serif;
	margin:0;
	padding:0;
	height: 100vh;
	background-color: #ddd;
}

.container
{
	width:1024px;
	margin-left:auto;
	margin-right:auto;
}

.popup
{
	width: 100%;
	background-color: #fff;
	margin-top: 150px;
	padding: 20px;
	box-sizing: border-box;
	border-radius: 5px;
	box-shadow: 1px 4px 6px #666;
	text-align: center;
}

.round 
{
    background-color: #ffffff;
    width: 150px;
    height: 130px;
    margin-left: auto;
    margin-right: auto;
    border-radius: 150px;
    overflow: hidden;
    padding-top: 20px;
    border: 3px solid #454545;
}

.logoconnexion 
{
    text-align: center;
    margin-top: -110px;
    margin-bottom: 10px;
}

.info-news
{
	background-color: #299cc6;
	padding: 20px;
	color: #fff;
	font-size: 13px;
	margin-bottom: 20px;
	text-align: left;
}

.operation
{
	background-color: #8f8f8f;
	color: #fff;
	font-size: 13px;
	padding: 20px;
	text-align: left;
	margin-bottom:20px;
}

.btn
{
	background-color: #06f;
	text-decoration: none;
	color: #fff;
	padding: 12px;
	font-size: 12px;
	border-radius: 5px;
	font-weight: bold;
}

.btn:hover
{
	background-color:#0049b7;
}
</style>
<body>
	<div class="container">
		<div class="popup">
			<div class="logoconnexion">
				<div class="round">
					<img src="https://www.shua-creation.com/images/logo.png">
				</div>
			</div>
			<H1>Mise à jour v1.30.9</H1>
			<div class="info-news">
			Dans cette nouvelle version vous retrouverez :<br><br>
			<ul>
			<li>+ Bug FIX - La consultation des messages dans l'admin (le suivi de profil renvoie sur un autre site).</li>
			</ul>
			</div>
			<div class="operation">
			<i>*** Mise à jour de la base de données ***</i><br><br>
			<?php
			
			echo '<br><i>*** Fin de la mise à jour ***</i>';
			
			?>
			</div>
			<a href="<?php echo $url_script; ?>/admin/home.php" class="btn">Retour à l'administration</a>
		</div>
	</div>
</body>
</html>