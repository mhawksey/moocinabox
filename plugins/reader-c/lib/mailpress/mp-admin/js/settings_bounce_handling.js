//settings_bounce_handling

jQuery(document).ready( function(){ 
	jQuery('.submit_batch_bounce').click( function() {
		var a = jQuery(this); 
		jQuery('.toggl3').fadeTo(0,0); 
		jQuery( '.bounce_' + a.val()).fadeTo(0,1); 
	});
});
