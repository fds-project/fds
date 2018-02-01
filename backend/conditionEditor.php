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
<title>Fraude Detectie Systeem</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

</head>

<body>
<div class="container">
	<div class="row">
    	<img src="images/ce_v1.png" alt="Cool Logo" class="img-fluid" />
        <h1 style="padding-top:4px;">&nbsp;Rule / Condition Editor - FDS</h1>
    </div>
    <div class="clearfix"></div>
    <div class="row">
    	<div class="col-2">
        	<br />
            <hr />
        	<h4>Add Condition</h4>
            <div class="form-group">
            	<div class="form-row">
                	<label for="condition_field">Condition Field</label>
                	<select id="condition_field" class="form-control">
                    	<?php
						$check = mysqli_query($db, "SELECT * FROM fields");
						while($row = mysqli_fetch_assoc($check)) { 
							echo '<option value="'.$row['internalValue'].'">'.$row['visibleValue'].'</option>'; 
						}
						?>
                    </select>                
                </div>
            	<div class="form-row">
                <label for="contition_type">Condition Type</label>
                	<select id="contition_type" class="form-control">
                    	<?php
						$check = mysqli_query($db, "SELECT * FROM condition_types");
						while($row = mysqli_fetch_assoc($check)) { 
							echo '<option value="'.$row['internalValue'].'">'.$row['visibleValue'].'</option>'; 
						}
						?>
                    </select>                
                </div>
                <div class="form-row">
                <label for="fieldMatchType">Value Type</label>
                	<select id="fieldMatchType" class="form-control" onChange="testCustomValue()">
                    	<option value="custom" selected>Custom Value</option>
                    	<?php
						$check = mysqli_query($db, "SELECT * FROM fields");
						while($row = mysqli_fetch_assoc($check)) { 
							echo '<option value="'.$row['internalValue'].'">'.$row['visibleValue'].'</option>'; 
						}
						?>
                    </select>                
                </div>
                <div class="form-row" id="customValueSelector">
                	<label for="match_value">Condition Value</label>
                	<input type="text" id="match_value" class="form-control" value="" />
                </div>
                <hr />
                <div class="form-row">
                <label for="resmulp">Result Multiplier</label>
                	<select id="resmulp" class="form-control">
                    	<option value="mul">Multiply</option>
                        <option value="add">Add</option>
                        <option value="sub">Subtract</option>
                        <option value="div">Devide by</option>
                        <option value="none" selected>None (Warning only)</option>
                    </select>                
                </div>
                <div class="form-row">
                    <label for="rval">Result value</label>
                    <input type="number" id="rval" value="10" class="form-control" />
                </div>
                <div class="form-row">
                    <label for="wrng">Throw Warning</label>
                    <input type="checkbox" id="throwWarning" class="form-control" />
                </div>
                <hr />
                <div class="form-row">
                	<label style="display:none;" class="text-danger" id="errors"></label>
                    <label style="display:none;" class="text-success" id="ok"></label>
                	<button type="button" id="check" onClick="checkCondition()" class="btn btn-success">Check Condition</button>
                </div>
                <br />
                <div class="form-row">
                	<button type="button" id="addCondition" onClick="addC()" class="btn btn-primary">Add Condition</button>
                </div>
            </div>
        </div>
        <div class="col-8">
        	<br />
            <hr />
            <ul style="list-style:none;">
            	<li id="loader" style="display: none;">
            		<div class="alert alert-success" id="loader_text">
            			Saving <i class="fa fa-spinner fa-pulse fa-fw"></i>
            		</div>
            	</li>
            	<li id="start" class="ignore_sort">
                	<div class="alert alert-success">
                      <strong>Start!</strong> This indicates the start point of the data.
                    </div>
                    <center><i class="fa fa-arrow-circle-down fa-3x"></i></center>
                </li>
            </ul>
            
        	<ul id="conditions" style="list-style:none;">
				
            </ul>
            <ul style="list-style:none;">
                <li id="end" class="ignore_sort">
                	<div class="alert alert-success">
                      <strong>End!</strong> This indicates the end point of the data.
                    </div>
                </li>
            </ul>
        </div>
        <div class="col-2">
        	<br />
            <hr />
            <button onClick="back()" class="btn btn-danger">Back</button>
			<hr />
			<button onClick="save()" class="btn btn-success">Save</button>
		</div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!-- Go :D -->
<script src="fds.js" type="text/javascript"></script>
<script type="text/javascript">
	// init everything
	initConditionEngine();
	api_token = '<?php echo $local_api_token; ?>';
	group_id = '<?php echo (isset($_GET['group_id']) ? intval($_GET['group_id']) : 0); ?>';
	loadConditions();
</script>
</body>
</html>
