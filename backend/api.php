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
$result = NULL;
if(isset($_POST['comment'])) {
	$comment = $_POST['comment'];
	$comment = mysqli_real_escape_string($db, $comment);
	$key = rand(10000000, 999999999);
	$key = sha1($key);
	$result = mysqli_query($db, "INSERT INTO auth (authkey, comment) VALUES ('$key', '".htmlentities($comment)."')");
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
        <h1 style="padding-top:4px;">&nbsp;API Editor - FDS</h1>
    </div>
    <div class="clearfix"></div>
    <div class="row">
    	<div class="col-10">
        <h2>Existing keys</h2>
    		<table class="table">
				<thead>
					<td># ID</td>
					<td>Token</td>
					<td>Comment</td>
					<td>Added</td>
                    <td>Actions</td>
				</thead>
   				<tbody>
   					<?php
					$q = mysqli_query($db, "SELECT * FROM auth ORDER BY last_used DESC LIMIT ".(intval(@$_GET['start']) ? intval(@$_GET['start']) : 0).",20");
					
					while($row = mysqli_fetch_assoc($q)) {	
						echo '<tr id="entry_'.$row['id'].'"><td>'.$row['id'].'</td><td>'.$row['authkey'].'</td><td>'.$row['comment'].'</td><td>'.(!is_null($row['last_used']) ? $row['last_used'] : 'Unused').'</td><td><button onClick="del('.$row['id'].');" class="btn btn-danger btn-small">Remove</button></td></tr>';
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
    <div class="row">
    	<div class="col-10">
        <h2>New API key</h2>
        	<form method="post">
                <div class="form-row">
                    <label>Token comment</label>
                    <input type="text" name="comment" class="form-control" placeholder="API Comment" />
                </div>
                <br />
                <div class="form-row">
                <label>API privileges</label>
                    <select class="form-control" name="privs">
                        <option value="0" selected>Full control</option>
                        <option value="1">Read only access</option>
                        <option value="2">PUT only access</option>
                    </select>
                </div>
                <br />
                <div class="form-row">
                    <input type="submit" value="Create" class="btn btn-success" />
                </div>
            </form>
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
	if(confirm("You are about to delete an API key, are you sure?")) {
		$.get("parser.php?action=removekey&id="+id+"&authKey=<?php echo $local_api_token; ?>", function(data){
			
		});
		$('#entry_'+id).remove();
	}
}	
</script>

</body>
</html>
