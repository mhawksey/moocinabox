//settings_batch_send

jQuery(document).ready( function(){ 
	jQuery('.submit_batch_delete_old_mails').click( function() {
		var a = jQuery(this); 
		jQuery('.toggl4').fadeTo(0,0); 
		jQuery( '.delete_old_mails_' + a.val()).fadeTo(0,1); 
	});
});
