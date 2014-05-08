//user_mailinglists

jQuery(document).ready( function() {
	var noSyncChecks = false, syncChecks;

	// mailinglist tabs
	jQuery('#mailinglist-tabs a').click(function(){
		var t = jQuery(this).attr('href');
		jQuery(this).parent().addClass('tabs').siblings('li').removeClass('tabs');
		jQuery('.tabs-panel').hide();
		jQuery(t).show();
		if ( '#mailinglists-all' == t )
			deleteUserSetting('mailinglists');
		else
			setUserSetting('mailinglists','pop');
		return false;
	});
	if ( getUserSetting('mailinglists') )
		jQuery('#mailinglist-tabs a[href="#mailinglists-pop"]').click();

	// Ajax mailinglist
	jQuery('#newmailinglist').one( 'focus', function() { jQuery(this).val( '' ).removeClass( 'form-input-tip' ) } );
	jQuery('#mailinglist-add-sumbit').click(function(){jQuery('#newmailinglist').focus();});

	syncChecks = function() {
		if ( noSyncChecks )
			return;
		noSyncChecks = true;
		var th = jQuery(this), c = th.is(':checked'), id = th.val().toString();
		jQuery('#in-mailinglist-' + id + ', #in-popular-mailinglist-' + id).attr( 'checked', c );
		noSyncChecks = false;
	};

	popularMailinglists = jQuery('#mailinglistchecklist-pop :checkbox').map( function() { return parseInt(jQuery(this).val(), 10); } ).get().join(',');
	mailinglistAddBefore = function( s ) {
		if ( !jQuery('#newmailinglist').val() )
			return false;
		s.data += '&popular_ids=' + popularMailinglists + '&' + jQuery( '#mailinglistchecklist :checked' ).serialize();
		return s;
	};

	mailinglistAddAfter = function( r, s ) {
		var newMailinglistParent = jQuery('#newmailinglist_parent'), newMailinglistParentOption = newMailinglistParent.find( 'option[value="-1"]' );
		jQuery(s.what + ' response_data', r).each( function() {
			var t = jQuery(jQuery(this).text());
			t.find( 'label' ).each( function() {
				var th = jQuery(this), val = th.find('input').val(), id = th.find('input')[0].id, name, o;
				jQuery('#' + id).change( syncChecks ).change();
				if ( newMailinglistParent.find( 'option[value="' + val + '"]' ).size() )
					return;
				name = jQuery.trim( th.text() );
				o = jQuery( '<option value="' +  parseInt( val, 10 ) + '"></option>' ).text( name );
				newMailinglistParent.prepend( o );
			} );
			newMailinglistParentOption.attr( 'selected', 'selected' );
		} );
	};

	jQuery('#mailinglistchecklist').wpList( {
		alt: '',
		response: 'mailinglist-ajax-response',
		addBefore: mailinglistAddBefore,
		addAfter: mailinglistAddAfter
	} );

	jQuery('#mailinglist-add-toggle').click( function() {
		jQuery('#mailinglist-adder').toggleClass( 'wp-hidden-children' );
		jQuery('#mailinglist-tabs a[href="#mailinglists-all"]').click();
		return false;
	} );

	jQuery('.mailinglistchecklist .popular-mailinglist :checkbox').change( syncChecks ).filter( ':checked' ).change(), sticky = '';
});
