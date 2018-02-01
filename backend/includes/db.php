<?php
# initial database configuration
$host = '';
$user = '';
$password = '';
$database = '';

$db = mysqli_connect($host, $user, $password, $database) or die("Cannot connect to database :(");
?>
