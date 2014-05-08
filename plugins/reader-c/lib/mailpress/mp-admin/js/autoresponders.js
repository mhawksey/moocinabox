// autoresponders

jQuery(document).ready( function() {

	jQuery('#autoresponder_event').change( function() {
		var a = jQuery(this); 
		jQuery('.autoresponder_settings').hide(); 
		jQuery('#autoresponder_' + a.val() + '_settings').show(); 
      } );
} );
