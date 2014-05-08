// fields

jQuery(document).ready( function() {

	jQuery('.field_type_settings').tabs();

	jQuery('.field_type').click( function() {
		var a = jQuery(this); 
		jQuery('.field_type_settings').hide(); 
		jQuery('#field_type_' + a.val() + '_settings').show(); 
      } );

	jQuery('.controls').change( function() {
		var a = jQuery('.field_type:checked').val();
		jQuery('#field_type_controls_' + a).hide();
		jQuery('.controls:checked').each( function() {
			var a = jQuery('.field_type:checked').val();
			jQuery('#field_type_controls_' + a).show();
		} );
	} );

} );
