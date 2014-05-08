// write_autoresponders

jQuery(document).ready( function() {
	jQuery('#' + adminautorespondersL10n.table_body_id).wpList({
		response: adminautorespondersL10n.ajax_response,
		addAfter: function( xml, s ) {
			jQuery('table#' + adminautorespondersL10n.table_list_id).show();
		}, 
		addBefore: function( s ) {
			s.data += '&mail_id=' + jQuery('#mail_id').val(); 
			return s;
		}
	});
});
