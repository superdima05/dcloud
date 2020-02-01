<?php

//Deezer cloud for deezer-downloader.
//Main function is sync beetwen multiple devices.

//SETTINGS
$token = "AAA"; #Enter token, token must be the same on client side too.
//SETTINGS

//SQLite DB
$db = new PDO('sqlite:tracks.db');
$db->exec("CREATE TABLE IF NOT EXISTS tracks (id INTEGER PRIMARY KEY, tname TEXT, artist TEXT, album TEXT)");
//SQLite DB

//GET CONNECTION
if($_GET['cl'] == 1){$isclient=1;}else{$isclient=0;}
if($_GET['token'] != ""){$g_token = $_GET['token'];}else{$g_token = "NONE";}
if($_GET['method'] != ""){$method = $_GET['method'];}else{$method = "NONE";}
if($_GET['data'] != ""){$data = $_GET['data'];}else{$data = "NONE";}
//GET CONNECTION

header("Content-type: application/json");
$response = [];
$response['code'] = 0;
$response['message'] = "";
$response['error'] = "";
$response['result'] = [];
$lock = 0;

if($token == "AAA"){
	if($lock != 1){
		$response['code'] = 100;
		$response['message'] = "Change token to new instead of ".$token;
		$response['error'] = "Please specify new token instead of default.";
		$response['result'] = [];
		die(json_encode($response));
	}
	$lock = 1;
}

if($token != $g_token or $g_token == "NONE"){
	if($lock != 1){
		$response['code'] = 102;
		$response['message'] = "Please check token on client side or change it on server side";
		$response['error'] = "Incorrect token!";
		$response['result'] = [];
		die(json_encode($response));
	}
}

if($method == "add"){
	if($data == "" or $data == "NONE"){if($lock != 1){
			$response['code'] = 104;
			$response['message'] = "Please specify some data or report a bug.";
			$response['error'] = "No data";
			$response['result'] = [];
			$lock = 1;
			die(json_encode($response));
		}}
	$tdata = json_decode(file_get_contents("https://api.deezer.com/track/".$data),true);
	$id = $tdata['id'];
	$tname = $tdata['title']; 
	$artist = $tdata['artist']['name'];
	$album = $tdata['album']['title'];
	$response['code'] = 200;
	$response['message'] = "";
	$response['error'] = "";
	$response['result'][0] = $id; $response['result'][1] = $tname; $response['result'][2] = $artist; $response['result'][3] = $album;
	$res = $db->query("SELECT COUNT(*) FROM tracks WHERE id=".$data);
	$ar = $res->fetch(PDO::FETCH_ASSOC);
	if($ar['COUNT(*)'] <= 0){
		$db->exec("INSERT INTO tracks (id,tname,artist,album) VALUES (".$id.",'".$tname."','".$artist."','".$album."')");
	}else{
		$response['message'] = "Already exists";
	}
}

if($method == "get"){
	$res = $db->query("SELECT COUNT(*) FROM tracks");
	$ar = $res->fetch(PDO::FETCH_ASSOC);
	if ($ar['COUNT(*)'] != 0) {
		$res = $db->query("SELECT * FROM tracks");
		$c = 0;
		$response['code'] = 200; $response['message'] = ""; $response['error'] = "";
    	while($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$response['result'][$c][0] = $row['id']; $response['result'][$c][1] = $row['tname']; $response['result'][$c][2] = $row['artist']; $response['result'][$c][3] = $row['album'];
			$c = $c + 1;
    	}
	} else {
		$response['code'] = 105; $response['message'] = "No tracks in databse"; $response['error'] = "No tracks in databse"; $response['result'] = [];
	}
}

if($method == "rem"){
	if($data == "" or $data == "NONE"){if($lock != 1){
			$response['code'] = 104;
			$response['message'] = "Please specify some data or report a bug.";
			$response['error'] = "No data";
			$response['result'] = [];
			$lock = 1;
			die(json_encode($response));
		}}
	$res = $db->query("DELETE FROM tracks WHERE id=".$data);
	$res = $db->query("SELECT COUNT(*) FROM tracks WHERE id=".$data);
	$ar = $res->fetch(PDO::FETCH_ASSOC);
	if($ar['COUNT(*)'] == 0){
		$response['code'] = 200;
		$response['message'] = "";
		$response['error'] = "";
		$response['result'] = [];
	}else{
		$response['code'] = 106;
		$response['message'] = "Track wasn't deleted, check track id and check if database is not read-only";
		$response['error'] = "Track wasn't deleted";
		$response['result'] = [];
	}
}

if($method == "auth"){
	$response['code'] = 200;
	$response['message'] = "";
	$response['error'] = "";
	$response['result'] = [];
}

if($method == "" or $method == "NONE"){
	$response['code'] = 107;
	$response['message'] = "Please specify method or report a bug.";
	$response['error'] = "No method";
	$response['result'] = [];
	die(json_encode($response));
}

echo(json_encode($response));

?>
