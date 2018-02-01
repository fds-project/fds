<?php
// include common files
include('includes/db.php');
include('includes/config.php');
include('includes/encryption.php');
error_reporting(-1);
ini_set('display_errors', 'On');
// global error function
function error($text) {
	global $debug;
	exit(json_encode(array("status" => false, "error" => ($debug ? $text : "Hidden (not in debug mode)"))));
}
if($force_https && $_SERVER['SERVER_PORT'] != 443) error("Due to security reasons this page will only work using SSL");

// include API passtrough authentication
include('includes/auth.php');

// force content type to json
header('Content-Type: application/json');

if(!isset($_GET['action'])) {
	error("No action parameters was supplied");
}
$action = $_GET['action'];
$e = new encryption();

// complex stuff
if($action == "profile") {
	// get clientToken for authentication
	$cvt = @$_GET['clientToken'];
	
	// get the IV validation code
	$iv_val = mysqli_real_escape_string($db, @$_GET['keyVerificationToken']);
	
	// get the profile id
	$profileId = intval($_GET['id']);
	// data not set, error
	if(!$iv_val) error("Invalid clientToken or verificationToken");
	
	if(!$iv_val) {
		// no IV parameter found, assuming it is zeroed out
		$iv = chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0);
		$iv_val = $e->ivCreateValidationToken ($iv);
	}
	
	$row = mysqli_query($db, "SELECT * FROM profiles WHERE id = '$profileId'");
	if(mysqli_num_rows($row) == 0) error("No profile with such ID");
	// fetch and validate remote data
	$data = mysqli_fetch_assoc($row);
	// re-creating key from local IV and validating against the remote one
	$serverIV = $data['key_iv'];
	if($e->ivCreateValidationToken ($serverIV) != $iv_val) error("Error validating IV, has the data changed?");
	
	$remotedata = $data['data'];
	$iv = $data['key_iv'];
	$key = $e->mergeKeyCiphers($data['key_data'], $iv_val);
	$decoded = $e->decode($remotedata, $key, $iv);
	$newsuite = true;
	if(!$decoded) {
		// support for weak Cipher suite
		$decoded = $e->decode($remotedata, $data['key_data'], $iv);
		$newsuite = false;

	}
	if(!$decoded) error("Something went wrong during the decoding procedure, is everything encoded with the correct keys?");
	//$data['data_raw'] = $decoded;
	if(isset($_GET['datatype']) && $_GET['datatype'] == 'json') {
		$data = explode('<div style="display: none;">', $decoded);
		$subdata = explode('</div>', $data[1])[0];
		if($subdata) {
			exit(json_encode(array("status" => true, "data" => json_decode($subdata))));
		}
	}
	exit(json_encode(array("status" => true, "data" => $decoded, "Cipherwarning" => !$newsuite), true));
}
if($action == "prepost") {
	$name = mysqli_real_escape_string($db, @$_GET['name']);
	
	$geniv = $e->randIV();
	
	$key_token = $e->ivCreateValidationToken ($geniv);
	$key = $e->randomKey();
	$keyCipher = $e->mergeKeyCiphers($key, $key_token);
	sleep(0.2);
	mysqli_query($db, "INSERT INTO profiles (`publicname`, `added`, `key_iv`, `key_data`, `encryption`) VALUES ('$name', NOW(), '$geniv', '$key', '".$e->method."')");
	$id = mysqli_insert_id($db);
	
	$result = array("status"=> true, "data" => array("IV" => base64_encode($geniv), "key" => $keyCipher, "keyVerificationToken" => $key_token, "id" => $id));
	echo json_encode($result, true);
}
if($action == "test") {
	$key_token = $e->ivCreateValidationToken (base64_decode($_GET['iv']));
	$key = $e->mergeKeyCiphers($_GET['key'], $key_token);
	$enc = $e->encode($_GET['text'], $key, base64_decode($_GET['iv']));
	exit($enc);
}
if($action == "updateprofile") {
	// some pre checks
	if(!isset($_POST['data'])) error("Error reading postdata");
	$data = $_POST['data'];
	if(isset($_GET['encoding']) && $_GET['encoding'] != "base64") $data = base64_encode($data); 
	// $data = mysqli_real_escape_string($db ,$data);
	$iv_val = mysqli_real_escape_string($db, @$_GET['keyVerificationToken']);
	$id = intval(@$_GET['id']);
	if(!$iv_val || !$id || !$data) error("Error reading request information");
	
	// more pre checks
	$res = mysqli_query($db, "SELECT * FROM profiles WHERE id = '$id'");
	if(mysqli_num_rows($res) == 0) error("Error, no such ID");
	
	$rowdata = mysqli_fetch_assoc($res);
	$key_token = $e->ivCreateValidationToken ($rowdata['key_iv']);
	
	if($key_token != $iv_val) error("Error validating request");
	$key = $e->mergeKeyCiphers($rowdata['key_data'], trim($key_token));
	
	$decoded = $e->decode($data, $key, $rowdata['key_iv']);
	
	if(!$decoded) error("Decoding error with key ".$key.", is the data encoded with the correct IV/key?");
	$data = mysqli_real_escape_string($db, $data);
	$upd = (@$set_once ? ', key_iv = NULL' : '');
	mysqli_query($db, "UPDATE `profiles` SET data='$data'$upd WHERE id = '$id'");
	
	echo json_encode(array("status"=> true, "data"=>[]));
	
}
?>