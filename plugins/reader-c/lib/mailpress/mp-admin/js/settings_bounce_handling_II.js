//settings_bounce_handling_II

jQuery(document).ready( function(){ 
	jQuery('.submit_batch_bounce_II').click( function() {
		var a = jQuery(this); 
		jQuery('.toggl3_II').fadeTo(0,0); 
		jQuery( '.bounce_II_' + a.val()).fadeTo(0,1); 
	});
});
