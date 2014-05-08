//mp_meta_box_post_drafts

jQuery(document).ready( function() {

	jQuery('#mpdraftchecklist').wpList( {
		alt: '',
		response: 'mpdraft-ajax-response',
		addBefore: function( s ) {
			if ( !jQuery('#newmpdraft').val() || jQuery('#mpdraft-'+jQuery('#newmpdraft').val()).length != 0 ) return false;
			s.data += '&newmpdraft_txt=' + jQuery('#newmpdraft-'+jQuery('#newmpdraft').val()).html() + '&post_id=' +  jQuery('#post_ID').val();
			return s;
		},
		delBefore: function( s ) {
			s.data.post_id = jQuery('#post_ID').val();
			return s;
		},
		delAfter:  function( r, settings ) {
			jQuery('#mpdraft-' + r).remove();
		}
	} );

	jQuery('#mpdraft-add-toggle').click( function() {
		jQuery('#mpdraft-adder').toggleClass( 'wp-hidden-children' );
		return false;
	} );
});