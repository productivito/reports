jQuery(document).ready(function() {

	$.ajaxSetup( {
		cache : false
	});
	
	 $( "#datepicker_start").datepicker({
	    "changeMonth": true,
	    "changeYear" : true,
	    "yearRange" : "2009:2015",
	    "dateFormat": 'yy-mm-dd 00:00:00'
	  }
	  );
	  
	  
	   $( "#datepicker_exp").datepicker({
	    "changeMonth": true,
	    "changeYear" : true,
	    "yearRange" : "2009:2015",
	    "dateFormat": 'yy-mm-dd 23:59:59'
	  }
	  );
	

	
});

