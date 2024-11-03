function selectedCheckbox(check){
	var checkboxes = document.getElementsByName('check[]');
	for(var i in checkboxes){
		checkboxes[i].checked = check.checked;
	}

	var count = document.querySelectorAll('input[name="check[]"]:checked').length;
	document.getElementById('count').innerHTML = count;
}

function countCheckbox(){
	var count = document.querySelectorAll('input[name="check[]"]:checked').length;
	document.getElementById('count').innerHTML = count;
}