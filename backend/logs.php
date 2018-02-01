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

if(isset($_POST['clear'])) {
	
}
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
        <h1 style="padding-top:4px;">&nbsp;Datasource Editor - FDS</h1>
    </div>
    <div class="clearfix"></div>
    <div class="row">
    	<div class="col-10">
    		<table class="table">
				<thead>
					<td># ID</td>
					<td>User</td>
					<td>Action</td>
					<td>Call</td>
					<td>Added</td>
				</thead>
   				<tbody>
   					<?php
					$q = mysqli_query($db, "SELECT *,(SELECT comment FROM auth WHERE id = api_log.auth_user) AS user FROM api_log ORDER BY added DESC LIMIT ".(intval(@$_GET['start']) ? intval(@$_GET['start']) : 0).",20");
					
					while($row = mysqli_fetch_assoc($q)) {
						parse_str($row['parameters']);
						$p = $row['parameters'];
						$p = preg_replace('/authKey\=[^&]+(?:&)?/', '', $p);
						$p = preg_replace('/action\=[^&]+(?:&)?/', '', $p);
						$p = preg_replace('/parser.php\?/', '', $p);
						
						echo '<tr id="entry_'.$row['id'].'"><td>'.$row['id'].'</td><td>'.$row['user'].'</td><td>'.($action ? $action : 'Unknown').'</td><td>'.htmlentities($p).'</td><td>'.$row['added'].'</td></tr>';
					}
					?>
   				</tbody>
    		</table>
    		<?php
			if(mysqli_num_rows($q) == 20) {
				echo '<a href="?start='.(intval(@$_GET['start']) ? intval(@$_GET['start']) + 20 : 20).'"><button class="btn btn-primary">Load more</button></a>';
			}
			?>
    	  </div>
    
	</div>
	<br />
	<hr />
	<a href="index.php"><button class="btn btn-sm btn-primary">Back</button></a>
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
