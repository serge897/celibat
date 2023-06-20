<?php

include "main.php";

function checkBlacklist($md5)
{
	global $pdo;
	/* On tcheck si elle est pas en liste noire */
	/* 0 = rien */
	/* 1 = Vous */
	/* 2 = Lui */
	
	$md5_user = $_SESSION['md5'];
	$result = 0;
	
	/* Vous */
	$SQL = "SELECT COUNT(*) FROM blacklist WHERE md5user = '$md5_user' AND md5 = '$md5'";
	$reponse = $pdo->query($SQL);
	$req = $reponse->fetch();

	$blacklist = $req[0];
	if($blacklist == 0)
	{
		$result = 0;
	}
	else
	{
		$result = 1;
	}
	
	/* Lui */
	if($result == 0)
	{
		$SQL = "SELECT COUNT(*) FROM blacklist WHERE md5user = '$md5' AND md5 = '$md5_user'";
		$reponse = $pdo->query($SQL);
		$req = $reponse->fetch();

		$blacklist = $req[0];
		if($blacklist == 0)
		{
			$result = 0;
		}
		else
		{
			$result = 2;
		}
	}
	
	return $result;
}

$md5_receipt = AntiInjectionSQL($_REQUEST['md5']);
$message = AntiInjectionSQL($_REQUEST['message']);
$message = nl2br($message);

/* Remove Rules */
if(getParametre("ban_email_adress") == 'yes')
{
	$message = removeEmailAdress($message);
}
if(getParametre("ban_url_adress") == 'yes')
{
	$message = removeURL($message);
}

$md5_send = $_SESSION['md5'];

if(isset($_SESSION['md5']))
{
	$blacklist = checkBlacklist($md5_receipt);
	
	if(isUserPaidToUse())
	{
	
	if($blacklist == 0)
	{
		/* A¨Payer on envoie le message */
		$SQL = "INSERT INTO messagerie (md5_receipt,md5_send,message,photo,date_message,lu) VALUES ('$md5_receipt','$md5_send','$message','',NOW(),'non')";
		$pdo->query($SQL);

		/* On ajoute une notification sonore */
		$SQL = "INSERT INTO notificationsound (md5) VALUES ('$md5_receipt')";
		$pdo->query($SQL);

		/* On ajoute une notification tchat */
		$SQL = "SELECT COUNT(*) FROM newmessagetchat WHERE md5sender = '$md5_send' AND md5 = '$md5_receipt'";
		$r = $pdo->query($SQL);
		$rr = $r->fetch();
		
		if($rr[0] == 0)
		{
			$SQL = "INSERT INTO newmessagetchat (md5sender,md5) VALUES ('$md5_send','$md5_receipt')";
			$pdo->query($SQL);
		}
		
		$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_send' AND md5_send = '$md5_receipt' or md5_receipt = '$md5_receipt' AND md5_send = '$md5_send' ORDER BY date_message ASC";
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
			
			// Si on à une photo
			if($photo != '')
			{
				$message .= '<br><a href="'.$url_script.'/images/stock/'.$photo.'" target="photosend"><img src="'.$url_script.'/images/stock/'.$photo.'" width="90%"></a>';
			}
			
			if($md5_send == $_SESSION['md5'])
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
			$("#msg-conversation-<?php echo $md5_receipt; ?>").scrollTop($('#msg-conversation-<?php echo $md5_receipt; ?>').get(0).scrollHeight);
		});
		</script>
		<?php
	}
	else if($blacklist == 1)
	{
		$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_send' AND md5_send = '$md5_receipt' or md5_receipt = '$md5_receipt' AND md5_send = '$md5_send' ORDER BY date_message ASC";
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
			
			// Si on à une photo
			if($photo != '')
			{
				$message .= '<br><a href="'.$url_script.'/images/stock/'.$photo.'" target="photosend"><img src="'.$url_script.'/images/stock/'.$photo.'" width="90%"></a>';
			}
			
			if($md5_send == $_SESSION['md5'])
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

		echo $item_msg.'<br><i><font size=2 style="color:#f00;">Vous avez mis cette personne en liste noire, vous ne pouvez plus lui parler !</font></i>';

		?>
		<script>
		$(document).ready(function() 
		{
			$("#msg-conversation-<?php echo $md5_receipt; ?>").scrollTop($('#msg-conversation-<?php echo $md5_receipt; ?>').get(0).scrollHeight);
		});
		</script>
		<?php
	}
	else if($blacklist == 2)
	{
		$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_send' AND md5_send = '$md5_receipt' or md5_receipt = '$md5_receipt' AND md5_send = '$md5_send' ORDER BY date_message ASC";
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
			
			// Si on à une photo
			if($photo != '')
			{
				$message .= '<br><a href="'.$url_script.'/images/stock/'.$photo.'" target="photosend"><img src="'.$url_script.'/images/stock/'.$photo.'" width="90%"></a>';
			}
			
			if($md5_send == $_SESSION['md5'])
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

		echo $item_msg.'<br><i><font size=2 style="color:#f00;">Votre correspondant vous à mis dans sa liste noire, vous ne pouvez plus lui parler !</font></i>';

		?>
		<script>
		$(document).ready(function() 
		{
			$("#msg-conversation-<?php echo $md5_receipt; ?>").scrollTop($('#msg-conversation-<?php echo $md5_receipt; ?>').get(0).scrollHeight);
		});
		</script>
		<?php
	}
	}
	else
	{
		$SQL = "SELECT * FROM messagerie WHERE md5_receipt = '$md5_send' AND md5_send = '$md5_receipt' or md5_receipt = '$md5_receipt' AND md5_send = '$md5_send' ORDER BY date_message ASC";
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
			
			// Si on à une photo
			if($photo != '')
			{
				$message .= '<br><a href="'.$url_script.'/images/stock/'.$photo.'" target="photosend"><img src="'.$url_script.'/images/stock/'.$photo.'" width="90%"></a>';
			}
			
			if($md5_send == $_SESSION['md5'])
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

		echo $item_msg.'<br><i><font size=2 style="color:#f00;">Pour envoyer un message à votre correspondant vous devez mettre à jour votre <a href="'.$url_script.'/abonnement.php">Abonnement</a> !</font></i>';

		?>
		<script>
		$(document).ready(function() 
		{
			$("#msg-conversation-<?php echo $md5_receipt; ?>").scrollTop($('#msg-conversation-<?php echo $md5_receipt; ?>').get(0).scrollHeight);
		});
		</script>
		<?php
	}
}

?>