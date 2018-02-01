<?php
if(!$db) return;
$getd = print_r($_GET, true);
$postd = $_POST;
if(array_key_exists('pass', $postd)) $postd['pass'] = "*********";
if(array_key_exists('password', $postd)) $postd['pass'] = "*********";
$postd = print_r($postd, true);
$log = array("GET" => $getd, "POST" => $postd);
$ip = $_SERVER['REMOTE_ADDR'];
$r = json_encode($log);
$r = str_replace('\n', '', $r);
$r = mysqli_real_escape_string($db, $r);
$session = (isset($_SESSION['fde_login']) ? intval($_SESSION['fde_login']) : 'NULL');
mysqli_query($db, "INSERT INTO logs (`data`, `loginuser`, `added`, `added`) VALUES ('$r', $session, NOW(), '$ip')");
?>