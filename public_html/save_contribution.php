<?php
	session_start();
?>
<!------------------------------------------------------------------------------------------------------
 FILTER THE DATA WE GET FROM CONTRIBUTION.PHP IN HERE. IT IS NOT SAFE TO FILTER IN CONTRIBUTION.PHP,
	SINCE THE USER CAN JUST EDIT THE AJAX CALL TO INCLUDE UNFILTERED DATA. 

	NOTE: WE'RE FILTERING, NOT VALIDATING. VALIDATION IS BAD BECAUSE IT LETS THE POTENTIAL
		HACKER KNOW IF HIS INJECTION HAS WORKED OR NOT. THATS TOO MUCH INFO.
------------------------------------------------------------------------------------------------------->

<DOCTYPE html>
<html>
<head>

	<title>Contribute</title>
	<link rel="stylesheet" href="css/example/global.css" media="all">
	<link rel="stylesheet" href="css/example/layout.css" media="all and (min-width: 33.236em)">
</head>
<body>
	<?php
		require_once dirname(__FILE__).'/HTMLPurifier/library/HTMLPurifier.auto.php';


		$allowedEle = "b,i,u,li,ol,ul,table,tr,td,th,br,p";

		$allowedAttri = "p.style, p.class, table.style, table.class, table.width,
				table.cellpadding, table.cellspacing, table.border, table.id
				td.abbr, td.align, td.class, td.id, td.colspan, td.rowspan, td.style,
				td.valign, tr.align, tr.class, tr.id, tr.style, tr.valign, th.abbr,
				th.align, th.class, th.id, th.colspan, th.rowspan, th.style,
				th.valign, ul.style";

		$config = HTMLPurifier_Config::createDefault();
		$config->set("Core.Encoding", "UTF-8");
		$config->set("HTML.AllowedElements", $allowedEle);
		$config->set("HTML.AllowedAttributes", $allowedAttri);
		$purifier = new HTMLPurifier($config);

		echo "Hi, ".$_SESSION["username"];
		if(!$_SESSION["username"]){
			die("You must be logged in in order to access this part of the site.");
		}
		//echo "Welcome to the contribution screen, ".$_SESSION["username"];
		echo "</br>";
		$privacy=$_POST["privacy"];
		$name=$_POST["name"];	
		$game=$_POST["game"];	
		$type=$_POST["type"];	
		$subtype=$_POST["subtype"];	
		$desc=$purifier->purify($_POST["desc"]);	
		$img=$purifier->purify($_POST["img"]);
		$json=json_decode($_POST["json"], true);
		foreach($json as $key => $value){
			$value["label"] = $purifier->purify($value["label"]);
			$value["text"] = $purifier->purify($value["text"]);
		}
		$json=json_encode($json);	

		if ($img==""){
			if ($type==Armor){
				$img = "img/Armor200.png";
			}
			if ($type==Classes){
				$img = "img/Classes200.png";
			}
			if ($type==Feat){
				$img = "img/Feats200.png";
			}
			if ($type==Item){
				$img = "img/Items200.png";
			}
			if ($type==Monster){
				$img = "img/Monsters200.png";
			}
			if ($type==Race){
				$img = "img/Races200.png";
			}
			if ($type==Spell){
				$img = "img/Spells200.png";
			}
			if ($type==Weapon){
				$img = "img/Weapons200.png";
			}
		}
		
		$mysql = new mysqli("localhost", "ab68783_crawler", "El7[Pv~?.p(1", "ab68783_dungeon");
		if ($mysql->connect_error) {
			die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
		}
		$id=null;
		try{
			$mysql->query("START TRANSACTION");
			if($img){
				//$mysql->query("INSERT INTO contributions (username, name, `type`, subtype, game, `desc`, img, json) VALUES ('".$mysql->real_escape_string($_SESSION["username"])."','".$name."','".$type."','".$subtype."','".$game."','".$desc."','".$img."','".$json."')");
				$stmt = $mysql->prepare("INSERT INTO contributions (username, name, `type`, sub_type, game, `desc`, img, json, privacy) VALUES (?,?,?,?,?,?,?,?,?)");
				$stmt->bind_param("ssssssssi", $_SESSION["username"], $name, $type, $subtype, $game, $desc, $img, $json, $privacy);
				if(!$stmt->execute()){
					echo "Failed to execute mysql command: (".$stmt->errno.") ".$stmt->error;
				}
				$id=$stmt->insert_id;
				$stmt->close();
			}else{
				//$mysql->query("INSERT INTO contributions (username, name, `type`, subype, game, `desc`, json) VALUES ('".$mysql->real_escape_string($_SESSION["username"])."','".$name."','".$type."','".$subtype."','".$game."','".$desc."','".$json."')");
				$stmt = $mysql->prepare("INSERT INTO contributions (username, name, `type`, sub_type, game, `desc`, json, privacy) VALUES (?,?,?,?,?,?,?,?)");
				$stmt->bind_param("sssssssi", $_SESSION["username"], $name, $type, $subtype, $game, $desc, $json, $privacy);
				if(!$stmt->execute()){
					echo "Failed to execute mysql command: (".$stmt->errno.") ".$stmt->error;
				}
				$id=$stmt->insert_id;
				$stmt->close();
			}
			if($privacy==0){
				$stmt=$mysql->prepare("SELECT contributions FROM users WHERE username=?");
				$stmt->bind_param("s",$_SESSION["username"]);
				if(!$stmt->execute()){
					echo "Failed to execute mysql command: (".$stmt->errno.") ".$stmt->error;
				}
				$num=null;
				$stmt->bind_result($num);
				$stmt->fetch();
				//echo "num contributiuons: ".$num;
				$num++;
				$stmt->close();
				$stmt=$mysql->prepare("UPDATE users SET contributions=? WHERE username=?");
				$stmt->bind_param("is",$num,$_SESSION["username"]);
				if(!$stmt->execute()){
					echo "Failed to execute mysql command: (".$stmt->errno.") ".$stmt->error;
				}
				$stmt->close();
			}
			file_put_contents("sitemap.txt", "\nhttp://www.gminspiration.com/view_contribution_printable.php?contid=".$id, FILE_APPEND);
			echo "Your contribution was successfully added.";
			$mysql->commit();
		}catch(Exception $e){
			$mysql->rollback();
			echo "An error occurred while saving your contribution.</br>".$e;
		}
		$mysql->close();
	?>
</body>
</html>
