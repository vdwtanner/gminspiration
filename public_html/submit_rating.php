<?php
	session_start();
	if(!$_SESSION["username"]){
		header("HTTP/1.1 412 You must be logged in to submit ratings.");
		die();
	}
	if(!$_POST["id"]){
		header("HTTP/1.1 412 No contribution ID found.");
		die();
	}
	$mysql = new mysqli("mysql14.000webhost.com","a9044814_crawler","d&d4days", "a9044814_dungeon");
	if ($mysql->connect_error) {
		die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
	}
	$comment=$mysql->real_escape_string(htmlspecialchars($_POST["comment"]));
	//$balance= $_POST["bal"];
	//echo $balance ." ". $balance/2 ." </br>";
	echo $balance;
	try{
		$mysql->query("START TRANSACTION");
		$mysql->query("INSERT INTO ratings (contribution_id, username, fun, balance, comment) VALUES (".$_POST["id"].", '".$_SESSION["username"]."', ".$_POST["fun"].", ".$_POST["bal"].", '".$comment."')");
		//echo "INSERT INTO ratings (contribution_id, username, fun, balance, comment) VALUES (".$_POST["id"].", '".$_SESSION["username"]."', ".$_POST["fun"].", ".$balance.", '".$comment."')";
		$mysql->commit();
	}catch(Exception $e){
		$mysql->rollback();
		header("HTTP/1.1 500 MySL Error has occurred.");
		echo $e;
	}
	$mysql->close();
	echo "<b>Rating successful!</b>";
?>