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

if(isset($_POST['submit'])) {
	$name = mysqli_real_escape_string($db, $_POST['name']);
	// make it a little bit more readable :)
	$type = mysqli_real_escape_string($db, $_POST['inputType']);
	
	$copy = isset($_POST['copyfrom']) && intval($_POST['copyfrom']);
	$insert = mysqli_query($db, "INSERT INTO datasources (`name`, `inputtype`, `added`) VALUES ('$name', '$type', NOW())");
	if($copy) {
		// gets last added id for this session
		$id = mysqli_insert_id($db);
		
		mysqli_query($db, "INSERT INTO datasources_fields (fieldid, datasourceid, fielddata) SELECT fieldid, $id, fielddata FROM datasources_fields WHERE datasourceid = '".intval($_POST['copyfrom'])."'");
	}
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Fraude Detectie Systeem</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

</head>

<body>
<div class="container">
	<div class="row">
    	<img src="images/ce_v1.png" alt="Cool Logo" class="img-fluid" />
        <h1 style="padding-top:4px;">&nbsp;Datasource Editor - FDS</h1>
    </div>
    <div class="clearfix"></div>
    <div class="row">
    	<div class="col-10">
    		<table class="table">
				<thead>
					<td># ID</td>
					<td>Name</td>
					<td>inputType</td>
					<td>Added</td>
					<td># of fields</td>
					<td>Actions</td>
				</thead>
   				<tbody>
   					<?php
					$existing = array();
					$q = mysqli_query($db, "SELECT *, (SELECT COUNT(*) FROM datasources_fields WHERE datasources_fields.datasourceid = datasources.id) AS ccount FROM datasources");
					while($row = mysqli_fetch_assoc($q)) {
						echo '<tr id="entry_'.$row['id'].'"><td>'.$row['id'].'</td><td>'.htmlentities($row['name']).'</td><td>'.$row['inputtype'].'</td><td>'.$row['added'].'</td><td>'.$row['ccount'].'</td><td><a href="datasourceEditor.php?dsid='.$row['id'].'"><button class="btn btn-sm btn-primary">Edit</button></a> <a href="#" onclick="del('.$row['id'].')"><button class="btn btn-sm btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i></button></a></td></tr>';
						$existing[$row['id']] = $row['name'];
					}
					?>
   				</tbody>
    		</table>
    		<hr />
    		<h4>Add new condition datasource</h4>
    		<form method="post">
    			<div class="form-group">
    				<div class="form-row">
    					<label for="inputName">Name</label>
    					<input type="text" name="name" class="form-control" maxlength="255" placeholder="Generic data set" id="inputName" />
    				</div>
    				<div class="form-row">
    					<label for="inputType">Type</label>
    					<select name="inputType" class="form-control" id="inputType">
    						<option value="json" selected>JSON</option>
    						<option value="xml">XML</option>
    						<option value="csv">CSV</option>
						</select>
    				</div>
    				<div class="form-row">
    					<label for="copyFrom">Copy existing fields from</label>
    					<select name="copyfrom" class="form-control" id="copyFrom">
    						<option value="">Start New</option>
    						<?php
							foreach($existing as $id => $name) {
								echo '<option value="'.$id.'">'.htmlentities($name).'</option>';
							}
							?>
						</select>
    				</div>
    				<div class="form-row">
    					<input type="submit" name="submit" class="form-control" value="Create" />
    				</div>
    			</div>
    		</form>
    	</div>
    	<a href="index.php"><button class="btn btn-sm btn-primary">Back</button></a>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript">
function del(id) {
	if(confirm("You are about to delete a condition list, are you sure?")) {
		$.get("parser.php?action=removedataset&id="+id+"&authKey=<?php echo $local_api_token; ?>", function(data){
			
		});
		$('#entry_'+id).remove();
	}
}	
</script>

</body>
</html>
