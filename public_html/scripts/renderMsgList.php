<?php	
	// call session_start(); in surrounding code

	function renderMsgList($type){
	// Type has two values:
	//	"INBOX" - Renders all msgs for which the $user is the recipient
	//	"INBOX_UNREAD" - Renders all msgs that are unread and for which the $user is the recipient
	//	"SENT" - Renders all msgs for which the $user is the sender

		$mysql = new mysqli("mysql14.000webhost.com","a9044814_crawler","d&d4days", "a9044814_dungeon");
		if($mysql->connect_error){
			die('Connect Error ('.$mysqli->connect_errno.')'.$mysqli->connect_error);
		}

		try{

			$mysql->query("START TRANSACTION");
			if($type == "INBOX")
				$result = $mysql->query("SELECT * FROM private_messages WHERE recipient='".$_SESSION["username"]."' ORDER BY timestamp DESC");
			else if($type == "INBOX_UNREAD")
				$result = $mysql->query("SELECT * FROM private_messages WHERE recipient='".$_SESSION["username"]."' AND timestamp > now() - INTERVAL 5 SECOND ORDER BY timestamp DESC");
			else if($type == "SENT")
				$result = $mysql->query("SELECT * FROM private_messages WHERE sender='".$_SESSION["username"]."' ORDER BY timestamp DESC");

			while($row = $result->fetch_assoc()){
				$rowarr[] = $row;		
			}	

			$regx_URL = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";


			echo "<div id='message_pane'>";
			//$count = 0;
			if($rowarr){
				foreach($rowarr as $key => $value){
					$msg = $value["message"];
					if(preg_match_all($regx_URL, $value["message"], $urlarr, PREG_SET_ORDER)){
						foreach($urlarr as $num => $url){
							$msg = str_replace($url[0], "<a href='".$url[0]."'>".$url[0]."</a>", $msg);
						}
					}

					echo "<div class='msgheader' id='".$value["id"]."'><hr>";//This is set to just the ID number of the message to allow easier manipulation of the DB
					if($value["sender"] == "DungeonCrawlers")
						echo "<b>From:&nbsp<div style='text-shadow: 1px 1px 1px #FF0000; display: inline; color:'>".$value["sender"]."</div></b>";
					else{
						if($type != "SENT")
							echo "<b>From:</b>&nbsp<a href='profile.php?user=".$value["sender"]."'>".$value["sender"]."</a>";
						else
							echo "<b>To:</b>&nbsp<a href='profile.php?user=".$value["recipient"]."'>".$value["recipient"]."</a>";
					}
					echo "<b style='padding-left: 10px'>Subject:</b>&nbsp".$value["subject"];
					echo "<div class='listshowhide'>";
					echo "<a href='#' onclick='toggle_vis(\"b".$value["id"]."\",this);' style='float: right;'>[show]</a>";
					if($value["sender"] != "DungeonCrawlers" && $type != "SENT")
						echo "<a class='replylink' href='composemsg.php?recipient=".$value["sender"]."&redirect=i'>[reply]</a>";
					echo "<a href='#' onclick='del(".$value["id"].")' style='float: right;'>[delete]&nbsp</a>";
					echo "<p style='display:inline; color: grey'>".$value["timestamp"]."&nbsp</p>";
					echo "</div>";
					echo "<div class='msgbody' id='b".$value["id"]."' style='display: none'>";//use ID instead of the original count so that we can load via AJAX
					echo "<div class='msgborder'>";
					echo "<p class='msgtext'>".$msg."<p>";
					echo "</div>";
					echo "</div>";
					//echo "<br><br>";
					echo "</div>";
					//$count++;
				}
				if($type != "INBOX_UNREAD")
					echo "<hr>";
			}else{
				echo "<b>You have no messages!</b>";
			}
			echo "</div>";
		}catch(Exception $e){
			
		}
	}
	
	if($_POST["action"] == "renderMsgList")
		renderMsgList($_POST["type"]);
?>

