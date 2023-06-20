<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$md5_user = $_SESSION['md5'];

$SQL = "SELECT * FROM user WHERE md5 = '$md5'";
$reponse = $pdo->query($SQL);
$req = $reponse->fetch();

$username = $req['username'];
$usernameslug = slugify($username);

/* On supprime la notification une fois afficher */
//$SQL = "DELETE FROM newmessagetchat WHERE md5sender = '$md5' AND md5 = '$md5_user'";
//$pdo->query($SQL);

/* On indique que l'ont à lu les messages */
$SQL = "UPDATE messagerie SET lu = 'oui' WHERE md5_receipt = '$md5_user' AND md5_send = '$md5'";
$pdo->query($SQL);

?>
<div class="msg-post" id="msg-post-<?php echo $md5; ?>">
	<div class="msg-post-title">
		<img src="<?php echo $url_script; ?>/images/icon-connected.png" width=10> <a href="<?php echo $url_script; ?>/<?php echo $md5; ?>/profil-de-<?php echo $usernameslug; ?>.html" class="usernameColor"><?php echo ucfirst($req['username']); ?></a>
		<div class="msg-post-close" onclick="closeConversationTchat('<?php echo $md5; ?>');">X</div>
		<div class="msg-post-blacklist" onclick="blacklistUser('<?php echo $md5; ?>');" title="Mettre en liste noire"><i class="fas fa-comment-slash"></i></div>
	</div>
	<div class="msg-conversation" id="msg-conversation-<?php echo $md5; ?>">
	<?php
	
	$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_user' AND md5_send = '$md5' or md5_receipt = '$md5' AND md5_send = '$md5_user' ORDER BY date_message ASC";
	$reponse = $pdo->query($SQL);
	while($req = $reponse->fetch())
	{
		$md5_send = $req['md5_send'];
		$photo = $req['photo'];
		
		$SQL = "SELECT * FROM user WHERE md5 = '$md5_send'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		$username_send = $rr['username'];
		
		/* On check si une photo existe */
		$image_exist = false;
		if(file_exists("images/photo/$md5_send.jpg"))
		{
			$image_exist = true;
			$photourl = "$url_script/images/photo/$md5_send.jpg";
		}
		if(file_exists("images/photo/$md5_send.jpeg"))
		{
			$image_exist = true;
			$photourl = "$url_script/images/photo/$md5_send.jpeg";
		}
		if(file_exists("images/photo/$md5_send.png"))
		{
			$image_exist = true;
			$photourl = "$url_script/images/photo/$md5_send.png";
		}

		$type = $req['type'];

		if(!$image_exist)
		{
			if($type == 'femme')
			{
				$photourl = "$url_script/images/woman-nopicture.jpg";
			}
			else
			{
				$photourl = "$url_script/images/man-nopicture.jpg";
			}
		}
		
		$message = $req['message'];
		$message_min = strtolower($message);
		
		// Emoji
		$message = str_replace(":)",'<span class="emoji"><img src="'.$url_script.'/images/emoji/1.png"></span>',$message);
		$message = str_replace(":-)",'<span class="emoji"><img src="'.$url_script.'/images/emoji/1.png"></span>',$message);
		$message = str_replace(":(",'<span class="emoji"><img src="'.$url_script.'/images/emoji/sad.png"></span>',$message);
		$message = str_replace(":-(",'<span class="emoji"><img src="'.$url_script.'/images/emoji/sad.png"></span>',$message);
		$message = str_replace(":-D",'<span class="emoji"><img src="'.$url_script.'/images/emoji/2.png"></span>',$message);
		$message = str_replace(":D",'<span class="emoji"><img src="'.$url_script.'/images/emoji/2.png"></span>',$message);
		$message = str_replace(":-D",'<span class="emoji"><img src="'.$url_script.'/images/emoji/2.png"></span>',$message);
		$message = str_replace(":o",'<span class="emoji"><img src="'.$url_script.'/images/emoji/5.png"></span>',$message);
		$message = str_replace(":-o",'<span class="emoji"><img src="'.$url_script.'/images/emoji/5.png"></span>',$message);
		$message = str_replace(":-p",'<span class="emoji"><img src="'.$url_script.'/images/emoji/6.png"></span>',$message);
		$message = str_replace(":p",'<span class="emoji"><img src="'.$url_script.'/images/emoji/6.png"></span>',$message);
		$message = str_replace(";-)",'<span class="emoji"><img src="'.$url_script.'/images/emoji/wink.png"></span>',$message);
		$message = str_replace(";)",'<span class="emoji"><img src="'.$url_script.'/images/emoji/wink.png"></span>',$message);
		
		if($md5_send == $md5_user)
		{
			$item_msg .= '<div class="msg-post-conversation">';
			$item_msg .= '<div class="msg-post-user-vignette msg-post-user"><img src="'.$photourl.'"></div>';
			$item_msg .= '<div class="msg-post-message">'.$message.'</div>';
			$item_msg .= '</div>';
		}
		else
		{
			$item_msg .= '<div class="msg-post-conversation">';
			$item_msg .= '<div class="msg-post-user-vignette"><img src="'.$photourl.'"></div>';
			$item_msg .= '<div class="msg-post-message">'.$message.'</div>';
			$item_msg .= '</div>';
		}
		
		// Si on à une photo
		if($photo != '')
		{
			$message .= '<br><a href="'.$url_script.'/images/stock/'.$photo.'" target="photosend"><img src="'.$url_script.'/images/stock/'.$photo.'" width="30%"></a>';
		}
	}
	
	echo $item_msg;
	
	?>
	</div>
	<div class="msg-sending-user">
		<input type="text" class="messagelive" id="messagelive-<?php echo $md5; ?>" onkeypress="if (event.keyCode == 13) sendNewMessage('<?php echo $md5; ?>');" placeholder="Votre message" autocomplete="off">
		<button class="msg-send-conversation-btn"><i class="fas fa-paper-plane" onclick="sendNewMessage('<?php echo $md5; ?>');"></i></button>
	</div>
	<script>
	function sendNewMessage(md5)
	{
		var msgi = $('#messagelive-'+md5).val();
		if(msgi != '')
		{
			$('#messagelive-'+md5).val('');
			$.post("<?php echo $url_script; ?>/sendmsgtchat.php?message="+encodeURIComponent(msgi)+"&md5="+encodeURIComponent(md5), function( data ) {
				$('#msg-conversation-'+md5).html(data);
			});
		}
	}
	
	$(document).ready(function() 
	{
		$("#msg-conversation-<?php echo $md5; ?>").scrollTop($('#msg-conversation-<?php echo $md5; ?>').get(0).scrollHeight);
	});
	</script>
</div>