<?php
function is_sha1($str) {
    return (bool) preg_match('/^[0-9a-f]{40}$/i', $str);
}

// Cannot auth, db not connected
if(!$db) die();
if($force_auth) {
	if(!isset($_GET['authKey'])) {
		// no auth key, exit
		error("Authentication failure, no authKey was supplied");
	}
	$key = $_GET['authKey'];
	if(strlen($key) != 40) {
		error("Authentication failure, authKey was not in currect format");
	}
	if(!is_sha1($key)) {
		error("Authentication failure, authKey was not in currect format");
	}
	// escape string, just for safety :)
	$key = mysqli_real_escape_string($db, $key);
	
	$check_api_token = mysqli_query($db, "SELECT id FROM auth WHERE authKey = '$key'");
	
	// no authkey found in db, exit
	if(mysqli_num_rows($check_api_token) == 0) {
		error("Authentication failure, authKey was not found or is expired");
	}
	
	// found, fetch id as result
	$api_result = mysqli_fetch_assoc($check_api_token);
	
	$api_id = $api_result['id'];
	mysqli_query($db, "UPDATE auth SET last_used = NOW() WHERE id = '$api_id'");
	
	if($log_calls) {
		// log request url + some escaping
		$page = basename($_SERVER['REQUEST_URI']);
		$page = mysqli_real_escape_string($db, $page);
		
		// only use last 1024 chars of URL due to performance isues :)
		if(strlen($page) > 1024) $page = substr($page, 0, 1024);
		mysqli_query($db, "INSERT INTO api_log (id, auth_user, parameters, added) VALUES (NULL, '$api_id', '$page', NOW())");
	}
}

?>