jQuery(document).ready(function($){
	// Initialize password check button
	if ($('.pr-password').length) {
	  $('.pr-password').passwordRequirements({
	    numCharacters: 8,
	    useLowercase: true,
	    useUppercase: true,
	    useNumbers: true,
	    useSpecial: true,
	    parentClass: '.pr-password'
	  });
	}

	// Initialize password check for restore pwd
	if ($('.pr-password-new').length) {
	  $('.pr-password-new').passwordRequirements({
	    numCharacters: 8,
	    useLowercase: true,
	    useUppercase: true,
	    useNumbers: true,
	    useSpecial: true,
	    parentClass: '.pr-password-new'
	  });
	}

	// Hide pwd strengh block for mobiles
	$(document).on('keyup','#modal-sign-up-name',function(){
		var pwdBoxItems = $('#pr-box:eq(0) ul li'),
	        checkedCount = 0,
	        totalCount = 0;

	      // Check each point of pwd box
	      pwdBoxItems.each(function(){
	        if ($(this).find('.pr-ok').length) {
	          checkedCount++;
	        }

	        totalCount++;
	      });

	      if (checkedCount == totalCount) {
	      	$('#pr-box').fadeOut('fast');
    	}
	});
});