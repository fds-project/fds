<?php
session_start();
if(isset($_GET['logout'])) {
	session_destroy();
	header('Location: index.php');	
}
if(!isset($_SESSION['fde_login'])) {
	header('Location: login.php');
	exit("You are not logged in :(");	
}
include('includes/db.php');
include('includes/config.php');
$testdata = '\'{"status":"No testdata available, please add new!"}\'';
$parsed = false;
// always intval(safety lvl > 9000)
$id = intval($_GET['dsid']);
$getdata = mysqli_query($db, "SELECT * FROM datasources WHERE id = '$id'");
if(mysqli_num_rows($getdata)) {
	$data = mysqli_fetch_assoc($getdata);
	
	// xml converter
	if($data['inputtype'] == 'xml' && isset($_POST['testdata'])) {
		$tojsn = json_encode(simplexml_load_string($_POST['testdata']));
		if($tojsn) {
			// override postdata to store result in JSON format
			$_POST['testdata'] = $tojsn;
		} else {
			die('Error converting XML, is it in valid format? and not toooo complex?');
		}
	}
	if($data['inputtype'] == 'csv' && isset($_POST['testdata'])) {
		$data = $_POST['testdata'];
		$data = str_getcsv($data, "\n"); $rows = [];
		foreach($data as &$row) {
			$row = str_getcsv($row, ",");
			array_push($rows, $row);
		}
		$header = array_shift($rows);
		$csv = array();
		foreach ($rows as $row) {
		  $csv[] = array_combine($row, $header);
		}
		$_POST['testdata'] = json_encode($csv);
	}
	
	
	if(!is_null($data['testdata'])) $testdata = $data['testdata'];
} else {
	die("Error loading stuff.");
}

if(isset($_POST['submit'])) {
	$testdata = (strlen($_POST['testdata']) > 0 ? '\''.$_POST['testdata'].'\'' : NULL);
	//$testdata = print_r($testdata, true);
	$testdata_esc = mysqli_real_escape_string($db, $testdata);
	if(isset($_POST['save_data'])) {
		mysqli_query($db, "UPDATE datasources SET testdata = '$testdata_esc' WHERE id = '$id'");
	}
	$parsed = true;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Fraud Detection System</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<link rel="stylesheet" href="css/jv.css">
</head>

<body>
<div class="container">
	<div class="row">
    	<img src="images/ce_v1.png" alt="Cool Logo" class="img-fluid" />
        <h1 style="padding-top:4px;">&nbsp;Datasource Editor - FDS</h1>
    </div>
    <div class="clearfix"></div>
    <div class="row">
		<div class="col-8">
			<form method="post">
				<textarea type="text" name="testdata" placeholder="Enter testdata" class="form-control"></textarea>
				<label for="chk">Save testdata</label>
				<input type="checkbox" name="save_data" class="form-control" checked	id="chk"/> 
				<input type="submit" name="submit" value="Parse" class="form-control" />
			</form>
		</div>
	</div>
	<div class="row">
		<div class="col-8">
				<div id="loader" style="display: none;">
            		<div class="alert alert-success" id="loader_text">
            			Saving <i class="fa fa-spinner fa-pulse fa-fw"></i>
            		</div>
            	</div>
			<div id="testdata">
				<div id="view"></div>
			</div>
		</div>
   		<div class="col-2">
   			<label for="valueSelector">Select field</label>
			<select id="valueSelector" class="form-control" onChange="checkField(this.value)">
				<?php
				$datareverse = array();
				$check = mysqli_query($db, "SELECT * FROM fields");
				while($row = mysqli_fetch_assoc($check)) { 
					echo '<option value="'.$row['internalValue'].'">'.$row['visibleValue'].'</option>';
					$datareverse[$row['internalValue']] = $row['id'];
				}
				?>
			</select>
            <label for="dataType">Field type</label>
            <select id="dataType" class="form-control">
				<option value="string" selected>String (Default)</option>
                <option value="int">Number</option>
                <option value="date">Date</option>
			</select>
			<hr />
			<p>Selector: </p>
			<label class="text-success" id="pathSelector"></label>
			<hr />
			<h4>Existing</h4>
			<ul id="existFields" class="existFields" style="list-style: none;">
				
			</ul>
			<hr />
			<button class="btn btn-success" onClick="flush();">Save</button>
			<br />
			<a href="index.php"><button class="btn btn-sm btn-primary">Back</button></a>
		</div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="js/jsonview.js"></script> <br>

<script type="text/javascript">
	var selectors = {};
	var reverse_fields = <?php echo json_encode($datareverse); ?>;
	var id = <?php echo intval($_GET['dsid']); ?>;
	var api_token = '<?php echo $local_api_token; ?>';
	// todo, built support for table/array type JSON data
	
	jQuery(document).ready(function() {
    	$.jsonview('#view', <?php echo preg_replace('/\s+/', ' ', trim($testdata)); ?>);
	});
	function findUpTag(el, tag) {
		while (el.parentNode) {
			el = el.parentNode;
			if (el.nodeName == tag)
				return el;
		}
		return null;
	}
	function clickItem(entry) {
		var firstRow = entry.parentNode.parentNode.nodeName.toLowerCase() == 'div';
		var selection = null;
		
		if(!firstRow) {
			var x = $(entry);
			if(x[0].nodeName.toLowerCase() != 'dt') alert('This will only work correct on JSON with a single structure');
			var nodes = [entry.innerHTML];
			while(1) {
				x = x.parents('dl');
				if(!x) break;
				var key = x.attr('data-key');
				if(key == null || key == 'null') break;
				nodes.push(key);
				
			}
			selection = nodes.reverse().join('.');
			
		} else {
			selection = entry.innerHTML;
		}
		var dataFieldType = $('#dataType').val();
		console.log("Calculated selection: " + dataFieldType + ':' + selection);
		var field = $('#valueSelector').val();
		$('#pathSelector').html(field+" > " + dataFieldType + ':' + selection);
		selectors[field] = dataFieldType + ':' + selection;
		$('#existFields').html("");
		
		for (field in selectors) {
			$('#existFields').append('<li>'+field+' > <span class="text-success">'+selectors[field]+'</span></li>');
		}
		
	}
	function sleep(ms) {
  		return new Promise(resolve => setTimeout(resolve, ms));
	}
	async function flush() {
		x = confirm("Flushing this datasource to the database requires the old one to be deleted first, are you sure?");
		if(!x) return;
		$('#loader').show();
		var urlData = {
			"authKey": api_token,
			"id": id,
			"action": "clearDatasource"
		}

		$.getJSON('parser.php', urlData, function(data) {

		});
		// sleep some because the backend can take some time to process
		await sleep(1000);
		for (field in selectors) {
			var cfield = reverse_fields[field];
			var data = selectors[field];
			
			var x = data.split(':');

			var urlData = {
				"authKey": api_token,
				"id": id,
				"field": cfield,
				"action": "addDatafield",
				"fieldData": x[1],
				"dataType": x[0]
			}

			$.getJSON('parser.php', urlData, function(data) {

			});
		}
		$('#loader_text').text("Entries saved, you will be redirected to the overview!");
		await sleep(1000);
		location.href = 'datasource.php';
	}
	function checkField(value) {
		if(value in selectors) {
			$('#pathSelector').html(value+" > " + selectors[value]);
		} else {
			$('#pathSelector').html("");
		}
	}
</script>
</body>
</html>
