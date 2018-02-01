// Initial setup
var parseValid = false;

// Used for condition generation
var lastconditionid = 0;

// global API key, set by application to ensure correct login
var api_token = "";

var group_id = 0;
function back() {
	var x = confirm('Leaving will lose any changes made without saving!\nAre you sure?');
	if(x) location.href = 'conditions.php';

}
// sleep function
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// Make the list sortable
function initConditionEngine() {
	$('#conditions').sortable({
		'cancel': '.ignore_sort'
	});
}

// remove action from list
function onDelete(listItem) {
	x = confirm("Do you want to delete this item?\nAfter saving it will be permanently removed, are you sure?");
	if(x) {
		$('#condition_'+listItem).remove();	
	}
}

async function save() {
	x = confirm("Flushing this condition flow to the database requires the old one to be deleted first, are you sure?");
	if(!x) return;
	$('#loader').show();
	var urlData = {
		"authKey": api_token,
		"group_id": group_id,
		"action": "clearConditions"
	}
	
	$.getJSON('parser.php', urlData, function(data) {
			
	});
	// sleep some because the clearConditions can take some time to process
	await sleep(1000);
	// start sort order at 0
	var sortOrderCounter = 0;
	$( "#conditions > li > .data_holder" ).each(function( index, item ) {
  		var ctype = $( item ).attr('data-conditiontype');
		var field = $( item ).attr('data-field');
		var condition = $( item ).attr('data-condition');
		var operation = $( item ).attr('data-operation');
		var mulp = $( item ).attr('data-multiplier');
		var mulp_value = $( item ).attr('data-multp-value');
		var wng = $( item ).attr('warning');
		
		var urlData = {
			"authKey": api_token,
			"group_id": group_id,
			"sortOrderId": sortOrderCounter,
			"action": "addCondition",
			"contitionType": ctype,
			"conditionField": field,
			"condition": condition,
			"operation": operation,
			"multiplier_type": mulp,
			"multiplier_value": mulp_value,
			"warning": wng
		}
		
		$.getJSON('parser.php', urlData, function(data) {
			
		});
		sortOrderCounter++;
	});
	$('#loader_text').text("Entries saved, the page will now refresh!");
	await sleep(1000);
	location.reload();
	
}
// load existing stuff trough rest API
function loadConditions() {
	// assuming something went horribly wrong :(
	if(group_id == 0) return;
	if(api_token == '') return;
	
	var GETdata = {
		"authKey": api_token,
		"action": "getConditions",
		"group_id": group_id
	};
	// get existing conditions, sorting should be the same as when adding (ORDER)
	$.getJSON('parser.php', GETdata, function(data) {
		if(data.result == false) return;
		if(data.rows == 0) return;
		
		$.each(data.data, function (key, value) {
			var divBuilder = '<div class="data_holder" data-conditionType="'+value.conditionType+'" data-field="'+value.fieldName+'" data-condition="'+value.condition+'" data-operation="'+value.operation+'" data-multiplier="'+value.multiplier_type+'" data-multp-value="'+value.multiplier_value+'" warning="'+value.warning+'"></div>';
			
			var levels = {0: 'success', 1: 'primary', 2: 'warning', 3:'danger'};
			var currentLevel = 0;
			
			if(value.warning == 1) currentLevel++;
			if(value.resultType == "mul" && value.resultValue > 2) currentLevel++;
			else if(value.resultType == "add" && value.resultValue > 25) currentLevel += 2;
			else if(value.resultType == "add" && value.resultValue > 10) currentLevel++;
			// construct the list item
			var cdTemplate = '<li id="condition_'+value.id+'"> '+divBuilder+' <div class="alert alert-'+levels[currentLevel]+'"> <i class="fa fa-database" title="Existing entry"></i> <a href="#" class="close" onclick="onDelete('+value.id+')"><i class="fa fa-trash-o"></i></a> <strong>'+value.fieldName+'</strong> '+value.operation+': '+value.conditionType+' ('+value.condition+'). <br />Result processing: '+value.multiplier_type+' ('+value.multiplier_value+') Warning: '+(value.warning == 1 ? 'Yes' : 'No')+' </div> <center><i class="fa fa-arrow-circle-down fa-3x"></i></center> </li>';

			// append the item to the sortable list :)
			$('#conditions').append(cdTemplate);
			
			// if existing use last id seen to prevent accidental deletion due to duplicate ID's
			if(value.id > lastconditionid) lastconditionid = value.id;
		});
		lastconditionid++;
	});
	
}
// check condition fields for errors
function checkCondition() {

	parseValid = true;
	var fieldVal = document.getElementById('condition_field').value;
	if(!fieldVal || fieldVal == "") {
		parseError("The condition field is not set!");
		return;
	}
	
	var typeVal = document.getElementById('contition_type').value;
	if(!typeVal || typeVal == "") {
		parseError("The condition type is not set!");
		return;
	}
	
	var fieldSelector = document.getElementById('fieldMatchType').value;
	if(fieldSelector == 'custom') {
		var valueSelector = document.getElementById('match_value').value;
		if(!valueSelector || valueSelector == "") {
			parseError("Field type is set to Custom but no custom value is present!");
			return;
		}
	}
	
	var multip = document.getElementById('resmulp').value;
	if(!multip || multip == '') {
		parseError("Unknown multiplier");
		return;
	}
	var multi_value = (document.getElementById('rval').value ? document.getElementById('rval').value : 0)
	if(multip == 'dev' && multi_value == 0) {
		parseError("Due to logic reasons you cannot devide by zero!");
		return;
	}
	
	if(parseValid) {
		parseOk();
	}
}

// add condition to list (only works after correct parsing)
function addC() {
	if(!parseValid) {
		alert('The conditions are not validated, use check before adding a new one');
		return;
	}
	
	var fieldVal = document.getElementById('condition_field').value;
	var typeVal = document.getElementById('contition_type').value;
	
	var fieldSelector = document.getElementById('fieldMatchType').value;
	
	var valueSelector = (fieldSelector == 'custom' ? document.getElementById('match_value').value : fieldSelector);
	var rFieldSelector = (fieldSelector == 'custom' ? 'value' : 'field')
	
	var multi_value = (document.getElementById('rval').value ? document.getElementById('rval').value : 0)
	var multip = document.getElementById('resmulp').value;
	
	var levels = {0: 'success', 1: 'primary', 2: 'warning', 3:'danger'};
	var currentLevel = 0;
	var wning = (document.getElementById('throwWarning').checked ? 'Yes' : 'No')
	if(wning == 'Yes') currentLevel++;
	if(multip == "mul" && multi_value > 2) currentLevel++;
	else if(multip == "add" && multi_value > 25) currentLevel += 2;
	else if(multip == "add" && multi_value > 10) currentLevel++;
	
	lastconditionid += 1;
	
	
	// construct the data-holder div
	var divBuilder = '<div class="data_holder" data-conditionType="'+rFieldSelector+'" data-field="'+fieldVal+'" data-condition="'+valueSelector+'" data-operation="'+typeVal+'" data-multiplier="'+multip+'" data-multp-value="'+multi_value+'" warning="'+(wning == 'Yes' ? 1 : 0)+'"></div>';
	
	// construct the list item
	var cdTemplate = '<li id="condition_'+lastconditionid+'"> '+divBuilder+' <div class="alert alert-'+levels[currentLevel]+'"> <a href="#" class="close" onclick="onDelete('+lastconditionid+')"><i class="fa fa-trash-o"></i></a> <strong>'+fieldVal+'</strong> '+typeVal+': '+rFieldSelector+' ('+valueSelector+'). <br />Result processing: '+multip+' ('+multi_value+') Warning: '+wning+' </div> <center><i class="fa fa-arrow-circle-down fa-3x"></i></center> </li>';
	
	// append the item to the sortable list :)
	$('#conditions').append(cdTemplate);
}

// toggle for custom field
function testCustomValue() {
	var field = document.getElementById('fieldMatchType').value;
	if(field == 'custom') {
		$('#customValueSelector').show();
	} else {
		$('#customValueSelector').hide();
	}
}
// generic error function
function parseError(s) {
	parseValid = false;
	$('#ok').hide();
	$('#errors').text("The condition contains errors: " + s);
	$('#errors').show();	
}
// generic OK function
function parseOk() {
	$('#errors').hide();
	$('#ok').text("The condition contains no errors");
	$('#ok').show();
	parseValid = true;
}
// validate JSON locally
function validateJSON(value) {
	try {
  		var c = $.parseJSON(value);
		$('#jsonValidated').attr('class', '');
		$('#jsonValidated').addClass('text-success');
		$('#jsonValidated').text("The JSON appears to be correct");
		testdataValid = true;
	}
	catch (err) {
		$('#jsonValidated').attr('class', '');
	    $('#jsonValidated').addClass('text-danger');
		$('#jsonValidated').text("The JSON appears to be incorrect");
		testdataValid = false;
	}	
	
}