<?php

include "main.php";

$ville = AntiInjectionSQL($_REQUEST['ville']);
$pays = AntiInjectionSQL($_REQUEST['pays']);

if (strlen($ville) > 1) {
	if (is_numeric($ville)) {
		$SQL = "SELECT * FROM ville WHERE codepostal like '$ville%' AND pays = '$pays'";
		$reponse = $pdo->query($SQL);
		while ($req = $reponse->fetch()) {
?>
			<a href="javascript:void(0);" onclick="updateVille('<?php echo $req['id']; ?>','<?php echo $req['nom']; ?>','<?php echo $req['codepostal']; ?>');" class="result-item-link">
				<div class="result-item"><?php echo $req['nom']; ?> (<?php echo $req['codepostal']; ?>)</div>
			</a>
			<?php
		}
	} else {
		$SQL = "SELECT * FROM ville WHERE nom like '$ville%' AND pays = '$pays'";
		$reponse = $pdo->query($SQL);
		while ($req = $reponse->fetch()) {
			if ($req['codepostal'] == '') {
			?>
				<a href="javascript:void(0);" onclick="updateVille('<?php echo $req['id']; ?>','<?php echo $req['nom']; ?>','<?php echo $req['codepostal']; ?>');" class="result-item-link">
					<div class="result-item"><?php echo $req['nom']; ?></div>
				</a>
			<?php
			} else {
			?>
				<a href="javascript:void(0);" onclick="updateVille('<?php echo $req['id']; ?>','<?php echo $req['nom']; ?>','<?php echo $req['codepostal']; ?>');" class="result-item-link">
					<div class="result-item"><?php echo $req['nom']; ?> (<?php echo $req['codepostal']; ?>)</div>
				</a>
<?php
			}
		}
	}
}

?>