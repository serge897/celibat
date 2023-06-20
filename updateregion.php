<?php

include "main.php";

$pays = AntiInjectionSQL($_REQUEST['pays']);

?>
<option value="" selected><?php echo $mon_profil_option_region; ?></option>
<?php

$SQL = "SELECT * FROM region WHERE pays = '$pays'";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	?>
	<option value="<?php echo $req['id']; ?>"><?php echo $req['titre']; ?></option>
	<?php
}

?>