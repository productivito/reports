DD_roundies.addRule('#LogonWindow', '10px', true);
DD_roundies.addRule('#Profile', '0px 0px 10px 10px', true);
DD_roundies.addRule('.MainMenu a', '10px', true);
DD_roundies.addRule('#Right', '10px', true);

//initilise button and calendar jq ul
$(function() {
	$(".Calendar").datepicker({dateFormat: 'dd-mm-yy', changeYear: true, minDate: new Date(2007, 1 - 1, 1)});
	$(".submit").button();
});
