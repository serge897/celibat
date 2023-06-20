<?php

include "main.php";

$md5 = AntiInjectionSQL($_REQUEST['md5']);
$md5_user = $_SESSION['md5'];

/* On supprime la notification une fois afficher */
//$SQL = "DELETE FROM newmessagetchat WHERE md5sender = '$md5' AND md5 = '$md5_user'";
//$pdo->query($SQL);
	
$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_user' AND md5_send = '$md5' or md5_receipt = '$md5' AND md5_send = '$md5_user' ORDER BY date_message ASC";
$reponse = $pdo->query($SQL);
while($req = $reponse->fetch())
{
	$md5_send = $req['md5_send'];
	$photo = $req['photo'];
	
	/* Si afficher sur le tchat donc il sont lu */
	if($md5_send == $md5)
	{
		$id = $req['id'];
		$SQL = "UPDATE messagerie SET lu = 'oui' WHERE id = $id";
		$pdo->query($SQL);
	}
	
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
	
	// Si on à une photo
	if($photo != '')
	{
		$message .= '<br><a href="'.$url_script.'/images/stock/'.$photo.'" target="photosend"><img src="'.$url_script.'/images/stock/'.$photo.'" width="90%"></a>';
	}
	
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
}

echo $item_msg;

?>
<script>
$(document).ready(function() 
{
	$("#msg-conversation-<?php echo $md5; ?>").scrollTop($('#msg-conversation-<?php echo $md5; ?>').get(0).scrollHeight);
});
</script>