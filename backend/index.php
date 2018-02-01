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
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Fraud Detection System</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

</head>

<body>
<div class="container">
	<div class="row">
    	<img src="images/ce_v1.png" alt="Cool Logo" class="img-fluid" />
        <h1 style="padding-top:4px;">&nbsp;FDS - Overview</h1>
    </div>
    <div class="clearfix"></div>
    <div class="row">
    	<div class="col-10">
    		<a href="conditions.php"><button class="btn btn-lg btn-primary">Condition Engine</button></a> <p>Manage condition flows</p>
    		<hr />
    		<a href="datasource.php"><button class="btn btn-lg btn-info">Datasource Control</button></a> <p>Manage datasources &amp; conversion</p>
    		<hr />
    		<a href="profiler.php"><button class="btn btn-lg btn-success">Profiler</button></a> <p>Manage profiler &amp; detection</p>
    		<hr />
    		<a href="api.php"><button class="btn btn-lg btn-default">API</button></a> <p>API Behaviour &amp; Settings</p>
    		<hr />
    		<a href="logs.php"><button class="btn btn-lg btn-danger">Logs</button></a> <p>Logging</p>
            <hr />
    		<a href="?logout=1"><button class="btn btn-lg btn-warning">Logout</button></a> <p>Exit the application</p>
		</div>
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
