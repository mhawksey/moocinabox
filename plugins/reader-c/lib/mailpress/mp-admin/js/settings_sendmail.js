// settings_sendmail

jQuery(document).ready( function(){ 
	jQuery('.connection_sendmail').click( function() {  
		var a = jQuery(this); 
		if (a.val() == 'custom') jQuery('#sendmail-custom-cmd').show(); 
		else jQuery('#sendmail-custom-cmd').hide(); 
	}); 
});
