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
        <h1 style="padding-top:4px;">&nbsp;Profiler overview - FDS</h1>
    </div>
    <div class="clearfix"></div>
    <div class="row">
    	<div class="col-10">
    		<table class="table">
				<thead>
					<td># ID</td>
					<td>Public Name</td>
					<td>Encoded Length</td>
					<td>Added</td>
					<td>Encryption</td>
					<td>Actions</td>
				</thead>
   				<tbody>
   					<?php
					$q = mysqli_query($db, "SELECT * FROM profiles");
					while($row = mysqli_fetch_assoc($q)) {
						echo '<tr id="entry_'.$row['id'].'"><td>'.$row['id'].'</td><td>'.$row['publicname'].'</td><td>'.(strlen($row['data']) != 0 ? strlen($row['data']) : 'Empty').'</td><td>'.$row['added'].'</td><td>'.strtoupper($row['encryption']).'</td><td><a href="profiler.php?id='.$row['id'].'"><i class="fa fa-pencil-square-o" title="Edit conditions"></i></a> <a href="#" onclick="del('.$row['id'].')"><i class="fa fa-trash-o" aria-hidden="true"></i></a></td></tr>';
					}
					?>
   				</tbody>
    		</table>
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
		$.get("parser.php?action=removeprofile&id="+id+"&authKey=<?php echo $local_api_token; ?>", function(data){
			
		});
		$('#entry_'+id).remove();
	}
}	
</script>

</body>
</html>
