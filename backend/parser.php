<?php
// include common files
include('includes/db.php');
include('includes/config.php');
error_reporting(-1);
ini_set('display_errors', 'On');
// global error function
function error($text) {
	global $debug;
	exit(json_encode(array("status" => false, "error" => ($debug ? $text : "Hidden (not in debug mode)"))));
}

// include API passtrough authentication
include('includes/auth.php');

// force content type to json
header('Content-Type: application/json');

if(!isset($_GET['action'])) {
	error("No action parameters was supplied");
}
$action = $_GET['action'];

if($action == "clearConditions") {
	$group = intval($_GET['group_id']);
	if($group == 0) error("Error adding, no such groupID: 0");
	$r = mysqli_query($db, "DELETE FROM conditions WHERE groupid = '$group'");
}

if($action == "clearDatasource") {
	$group = intval($_GET['id']);
	if($group == 0) error("Error adding, no such groupID: 0");
	$r = mysqli_query($db, "DELETE FROM datasources_fields WHERE datasourceid = '$group'");
}

if($action == "remove") {
	$group = intval($_GET['id']);
	if($group == 0) error("Error adding, no such ID: 0");
	mysqli_query($db, "DELETE FROM conditions WHERE groupid = '$group'");
	mysqli_query($db, "DELETE FROM condition_groups WHERE id = '$group'");
}

if($action == "removedataset") {
	$group = intval($_GET['id']);
	if($group == 0) error("Error adding, no such ID: 0");
	mysqli_query($db, "DELETE FROM datasources WHERE id = '$group'");
	mysqli_query($db, "DELETE FROM datasources_fields WHERE datasourceid = '$group'");
}

if($action == "removekey") {
	$group = intval($_GET['id']);
	if($group == 0) error("Error adding, no such ID: 0");
	mysqli_query($db, "DELETE FROM auth WHERE id = '$group'");
}

if($action == "removeprofile") {
	$group = intval($_GET['id']);
	if($group == 0) error("Error adding, no such ID: 0");
	mysqli_query($db, "DELETE FROM profiles WHERE id = '$group'");
}

// get conditions
if($action == "getConditions") {
	if(!isset($_GET['group_id']) || !intval($_GET['group_id'])) error("The getConditions action requires the group_id parameter");
	$group = intval($_GET['group_id']);
	$conditions = [];
	$getcond = mysqli_query($db, "SELECT * FROM conditions WHERE groupid = '$group' ORDER BY sort_order ASC");
	while($row = mysqli_fetch_assoc($getcond)) {
		array_push($conditions, $row);
	}
	exit(json_encode(array("result" => true, "data" => $conditions, "rows" => mysqli_num_rows($getcond))));
}

// get existing jobs
if($action == "getJobs") {
	$jobs = [];
	$getjobs = mysqli_query($db, "SELECT * FROM jobs WHERE status='0' ORDER BY added DESC");
	while($row = mysqli_fetch_assoc($getjobs)) {
		array_push($jobs, $row);
	}
	exit(json_encode(array("result" => true, "data" => $jobs, "rows" => mysqli_num_rows($getjobs))));
}

if($action == "jobSetStatus") {
	$group = intval($_GET['id']);
	$status = intval($_GET['status']);
	if($group == 0) error("Error adding, no such ID: 0");
	mysqli_query($db, "UPDATE jobs SET status='$status' WHERE id = '$group'");
}

if($action == "getTrainingData") {
	if(!isset($_GET['group_id']) || !intval($_GET['group_id'])) error("The getTrainingData action requires the group_id parameter");
	$group = intval($_GET['group_id']);
	$conditions = [];
	$getcond = mysqli_query($db, "SELECT binaryresultstring, ylabel FROM mldata WHERE groupid = '$group' ORDER BY RAND()");
	while($row = mysqli_fetch_assoc($getcond)) {
		array_push($conditions, $row);
	}
	exit(json_encode(array("result" => true, "data" => $conditions, "rows" => mysqli_num_rows($getcond))));
}

// get conditions
if($action == "getDatasources") {
	if(!isset($_GET['id']) || !intval($_GET['id'])) error("The getDatasources action requires the id parameter");
	$group = intval($_GET['id']);
	$conditions = [];
	$gettype = mysqli_query($db, "SELECT inputtype FROM datasources WHERE id = '$group'");
	$getcond = mysqli_query($db, "SELECT fielddata AS position, (SELECT `fields`.internalValue FROM `fields` WHERE `fields`.id = datasources_fields.fieldid) AS name, datatype AS `type` FROM datasources_fields WHERE datasourceid = '$group' ORDER BY id DESC");
	while($row = mysqli_fetch_assoc($getcond)) {
		array_push($conditions, $row);
	}
	$typedata = mysqli_fetch_assoc($gettype);
	exit(json_encode(array("result" => true, "data" => $conditions, "type" => $typedata['inputtype'],"rows" => mysqli_num_rows($getcond))));
}

// group_id=0&contitionType=value&conditionField=value&condition=1&operation=equals&multiplier_type=none&multiplier_value=10&warning=1
if($action == "addCondition") {
	$group = intval($_GET['group_id']);
	if($group == 0) error("Error adding, no such groupID: 0");
	
	// string types
	$ctype = mysqli_real_escape_string($db, $_GET['contitionType']);
	$cfield = mysqli_real_escape_string($db, $_GET['conditionField']);
	$condition = mysqli_real_escape_string($db, $_GET['condition']);
	$operation = mysqli_real_escape_string($db, $_GET['operation']);
	$multiplier_type = mysqli_real_escape_string($db, $_GET['multiplier_type']);
	
	// int / bool types
	$sortId = intval($_GET['sortOrderId']);
	$mulval = intval($_GET['multiplier_value']);
	$wning = intval($_GET['warning']);
	
	$r = mysqli_query($db, "INSERT INTO `conditions` (`groupid`, `sort_order`, `fieldName`, `condition`, `operation`, `conditionType`, `multiplier_value`, `multiplier_type`, `warning`, `added`) VALUES ('$group', '$sortId', '$cfield', '$condition', '$operation', '$ctype', '$mulval', '$multiplier_type', '$wning', NOW())");
	exit(json_encode(array("status" => $r)));
	
}

if($action == "upload") {
	$dsid = intval(@$_GET['datasource_id']);
	$cgid = intval(@$_GET['condition_group_id']);
	
	$upload = @$_FILES['upload'];
	if(!isset($upload)) error("No upload file");
	if($upload['size'] == 0) error("Invalid upload");
	
	$type = (isset($_POST['type']) ? mysqli_real_escape_string($db, $_POST['type']) : 'json');
	
	$tmp = $upload['tmp_name'];
	$data = file_get_contents($tmp);
	if(!$data || strlen($data) == 0) error("Invalid upload, error opening file");
	
	$data = mysqli_real_escape_string($db, $data);
	if($type == "json") $data = preg_replace('/\s+/', ' ', trim($data));
	
	$query = "INSERT INTO `jobs` (`id`, `text`, `added`, `datasourceid`, `conditiongroupid`, `addedby`, `status`, `type`) VALUES (NULL, '$data', NOW(), '$dsid', '$cgid', '1', '0', '$type')";
	$result = mysqli_query($db, $query);
	exit(json_encode(array("result" => $result)));
	
}

if($action == "addDatafield") {
	$group = intval(@$_GET['id']);
	if($group == 0) error("Error adding, no such groupID: 0");
	
	// string types
	$cfield = mysqli_real_escape_string($db, $_GET['field']);
	$data = mysqli_real_escape_string($db, $_GET['fieldData']);
	$dval = mysqli_real_escape_string($db, $_GET['dataType']);
	
	$r = mysqli_query($db, "INSERT INTO `datasources_fields` (`fieldid`, `datasourceid`, `fielddata`, `datatype`) VALUES ('$cfield', '$group', '$data', '".$dval."')");
	exit(json_encode(array("status" => $r)));
	
}

exit(json_encode(array("status" => false)));
?>