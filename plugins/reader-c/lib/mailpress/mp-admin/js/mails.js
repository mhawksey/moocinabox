// mails

var mp_mails = {
	theList : null,
	theExtraList : null,

	init : function() {
		mp_mails.theList 	= jQuery('#the-mail-list').wpList( { alt: '', dimAfter: mp_mails.dimAfter, delBefore: mp_mails.delBefore, delAfter: mp_mails.delAfter, addColor: 'none' } );
		mp_mails.theExtraList 	= jQuery('#the-extra-mail-list').wpList( { alt: '', delColor: 'none', addColor: 'none' } );

		// delete
		jQuery('.delete a[class^="delete"]').click(function(){return false;});
	},

	dimAfter : function( r, settings ) {
	 	var id = jQuery('id',r).text();
	 	var item = jQuery('item',r).text();
	 	var rc = jQuery('rc',r).text();

	 	var old_status = jQuery('old_status',r).text();
	 	var new_status = jQuery('new_status',r).text();

		if (rc == 0)
		{
			jQuery('tr#mail-' + id).after(item).remove();
			jQuery('li span.mail-count-'+old_status).each( function() {
				var a = jQuery(this);
				var n = parseInt(a.html(),10);
				n = n - 1;
				if ( n < 0 ) { n = 0; }
				a.html( n.toString() );
			});
			jQuery('li span.mail-count-'+new_status).each( function() {
				var a = jQuery(this);
				var n = parseInt(a.html(),10);
				n = n + 1;
				if ( n < 0 ) { n = 0; }
				a.html( n.toString() );
			});
		}
	},

	delBefore : function(s) {
		if ( 'undefined' != showNotice ) return showNotice.warn() ? s : false;
		return s;
	},

	delAfter : function( r, settings ) {
		jQuery('li span.mail-count').each( function() {
			var a = jQuery(this);
			var n = parseInt(a.html(),10);
			n = n + ( jQuery('#' + settings.element).is('.unapproved') ? -1 : 1 );
			if ( n < 0 ) { n = 0; }
			a.html( n.toString() );
		});
		jQuery('.post-com-count span.mail-count').each( function() {
			var a = jQuery(this);
			if ( jQuery('#' + settings.element).is('.unapproved') ) { // we deleted an unapproved mail, decrement pending title
				var t = parseInt(a.parent().attr('title'), 10);
				if ( t < 1 ) { return; }
				t = t - 1;
				a.parent().attr('title', MP_AdminPageL10n.pending.replace( /%i%/, t.toString() ) );
				if ( 0 === t ) { a.parents('strong:first').replaceWith( a.parents('strong:first').html() ); }
				return;
			}
			var n = parseInt(a.html(),10) - 1;
			a.html( n.toString() );
		});

		if ( mp_mails.theExtraList.size() == 0 || mp_mails.theExtraList.children().size() == 0 ) {
			return;
		}

		mp_mails.theList.get(0).wpList.add( mp_mails.theExtraList.children(':eq(0)').remove().clone() );
		jQuery('#get-extra-mails').submit();
	}
};
jQuery(document).ready( function() { mp_mails.init(); });