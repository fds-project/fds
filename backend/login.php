<?php
session_start();

if(isset($_SESSION['fde_login'])) 
{
	header('Location: index.php');
}
error_reporting(E_ALL);
ini_set("display_errors", 1);
include('includes/db.php');
include('includes/log.php');
include('includes/config.php');
if(!isset($_SESSION['lcounter'])) $_SESSION['lcounter'] = 0;
if($_SESSION['lcounter'] >= $max_invalid_login) exit('
<html>
<style>
    { margin: 0; padding: 0; }

    html { 
        background: url(\'images/Banned.png\') no-repeat center center fixed; 
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
    }
</style>

<center><h1>You have too many invalid login attempts, please try again later!</h1></center>
</html>');
$errors = NULL;
if(isset($_POST['login'])) {
	$email = @$_POST['user'];
	$pass = @$_POST['pass'];
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) 
	{
		$errors = "Please enter a valid email address!";
	}
	else {
		$email = mysqli_real_escape_string($db, $email);
		$sql = "SELECT id, password, password_salt, status FROM users WHERE email = '$email'";
		$result = mysqli_query($db,$sql);
		$numrow = mysqli_num_rows($result);
		if($numrow > 0) {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
			if($row['status'] == 0) $errors = "account not active or banned";
			else {
				$pswd = sha1($pass.sha1(str_rot13($row['password_salt'])));
				if($pswd == $row['password'])
				{
					echo "welcome".$email;
					$id = $row['id'];
					$_SESSION['fde_login'] = $id;
					header('Location: index.php');
					die();
				} else {
					$_SESSION['lcounter'] = $_SESSION['lcounter'] + 1;
					$errors = "incorrect";
				}
			}						
		}
		else {
			$_SESSION['lcounter'] = $_SESSION['lcounter'] + 1;
			$errors = "incorrect";
		}
	}
}
?>	

<!doctype html>
<html lang="en" class="fullscreen-bg">

<head>
	<title>Login | FDS</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<!-- CSS -->
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link href="main.min.css" rel="stylesheet">
	<!-- GOOGLE FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">

</head>

<body style="background-image: url('images/backgroung.jpg');">
	<!-- WRAPPER -->
	<div id="wrapper">
		<div class="vertical-align-wrap">
			<div class="vertical-align-middle">
            
				<div class="auth-box ">
					<div class="left">
						<div class="content">
							<div class="logo text-center"><img src="images/ce_v1.png" class="logo_img" alt="FDS-e Logo">
                           <h2 class="wavap_logo">FDS-CE</h2>
                           <h4 class="logo_subtext">Fraud Detection System - Condition Engine</h4></div>
                            <?php echo ($errors ? '<div class="alert alert-danger" role="alert"><i class="fa fa-spin fa-linux"></i> <span class="sr-only">Error:</span>'.$errors.'</div>' : ''); ?>
							<form class="form-auth-small" method="post">
								<div class="form-group">
									<label for="signup-email" class="control-label sr-only">Email</label>
									<input type="email" class="form-control" id="signup-email" value="" name="user" placeholder="Email">
								</div>
								<div class="form-group">
									<label for="signup-password" class="control-label sr-only">Password</label>
									<input type="password" class="form-control" name="pass" id="signup-password" value="" placeholder="Password">
								</div>
								<input type="submit" class="btn btn-primary btn-lg btn-block" name="login" value="Login">
								<div class="bottom">
                                <div class="bottom">
								</div>
							</form>
						</div>
					</div>
                    </div>
					<div class="right">
						<div class="overlay"></div>
						<div class="content text">
							<h1 class="heading">Please authenticate</h1>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
    </div>
	<!-- END WRAPPER -->
</body>

</html>

