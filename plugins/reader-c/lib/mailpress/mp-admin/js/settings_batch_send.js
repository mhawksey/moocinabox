//settings_batch_send

jQuery(document).ready( function(){ 
	jQuery('.submit_batch').click( function() {
		var a = jQuery(this); 
		jQuery('.toggl2').fadeTo(0,0); 
		jQuery( '.' + a.val()).fadeTo(0,1); 
	});
});
