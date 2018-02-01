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
include('includes/encryption.php');

if(!isset($_GET['id'])) {
	header('Location: proverview.php');
	die();
}

$id = intval($_GET['id']);
$profile_raw = mysqli_query($db, "SELECT publicname, added, key_iv, encryption FROM profiles WHERE id = '$id'");
if(mysqli_num_rows($profile_raw) == 0) {
	header('Location: proverview.php');
	die();
}

$data = mysqli_fetch_assoc($profile_raw);
$e = new encryption();
$keytoken = $e->ivCreateValidationToken($data['key_iv']);

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
        <h1 style="padding-top:4px;">&nbsp;Profiler - FDS</h1>
    </div>
    <div class="clearfix"></div>
    <div class="row">
		<div class="col-8">
			<h4>Profile: <?php echo $data['publicname']; ?></h4>
			Encryption: <?php echo strtoupper($data['encryption']); ?> | Added: <?php echo $data['added']; ?>
		</div>
	</div>
   <hr />
    <div class="row">
		<div class="col-10" id="dataLoader">
			<div class="alert alert-success" id="loader">
				Loading data... <i class="fa fa-spinner fa-pulse fa-spin"></i> <br /><small>(decryption may take some time on large profiles)</small>
			</div>
			
		</div>
	</div>
	<br />
	<hr />
    <a href="parser_extended.php?keyVerificationToken=<?php echo $keytoken; ?>&id=<?php echo $id; ?>&authKey=<?php echo $local_api_token; ?>&action=profile&datatype=json">View as JSON</a>
	<a href="proverview.php"><button class="btn btn-sm btn-primary">Back</button></a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="js/jsonview.js"></script> <br>

<script type="text/javascript">
	async function fetchProfile() {
		var keyData = '<?php echo $keytoken; ?>';
		var profileId = '<?php echo $id; ?>';
		var ajaxURL = 'parser_extended.php';
		var api_token = '<?php echo $local_api_token; ?>';
		var urlData = {
				"authKey": api_token,
				"id": profileId,
				"action": "profile",
				"keyVerificationToken": keyData
			}
			await new Promise(resolve => setTimeout(resolve, 500));

			$.getJSON({
				url: ajaxURL,
				data: urlData,
				success: function(data) {
					if(data.status) {
						$('#loader').hide();
						$('#dataLoader').html(data.data);
						if(data.cypherwarning) {
							$('#dataLoader').html('<div class="alert alert-warning"><strong>Warning!</strong><br />This profile is encrypted using unsafe cyphers which rely on keys saved in the database, future profiles will contain merged keys which are more secure!</div>'+$('#dataLoader').html());
						} else {
							$('#dataLoader').html('<div class="alert alert-success"><strong>Yeah!</strong><br />The data is correctly decrypted and is using a strong key</div>'+$('#dataLoader').html());
						}
					} else {
						$('#loader').removeClass("alert-success");
						$('#loader').addClass("alert-danger");
						$('#loader').html("Something went wrong with the decoding procedure");
					}
			},
			error: function (data) {
				$('#loader').removeClass("alert-success");
				$('#loader').addClass("alert-danger");
				$('#loader').html("Something went wrong with the decoding procedure");
			}
			});
		}
	fetchProfile();
</script>
</body>
</html>
