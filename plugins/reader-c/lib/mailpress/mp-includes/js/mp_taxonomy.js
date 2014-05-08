var mp_taxonomy = {

	options : false,

	init : function() {

		if (( typeof(document.forms[MP_AdminPageL10n.add_form_id]) != "undefined" ) && ( document.forms[MP_AdminPageL10n.add_form_id].parent ))
			mp_taxonomy.options = document.forms[MP_AdminPageL10n.add_form_id].parent.options;

		if ( mp_taxonomy.options )
			jQuery('#'+MP_AdminPageL10n.list_id).wpList( { dimAfter: mp_taxonomy.dimAfter, addAfter: mp_taxonomy.addAfter, delBefore: mp_taxonomy.delBefore, delAfter: mp_taxonomy.delAfter } );
		else
			jQuery('#'+MP_AdminPageL10n.list_id).wpList( { dimAfter: mp_taxonomy.dimAfter, addAfter: mp_taxonomy.addAfter2, delBefore: mp_taxonomy.delBefore });

		// delete
		jQuery('.delete a[class^="delete"]').click(function(){return false;});
	},

	dimAfter : function( r, settings ) {
		var item = jQuery('response_data',r).text();
		jQuery('#' + MP_AdminPageL10n.list_id + '  tr:first').before(item).add();
	},

	addAfter : function( r, settings ) {
		var name, id;

		name = jQuery("<span>" + jQuery('name', r).text() + "</span>").html();
		id = jQuery(MP_AdminPageL10n.tr_prefix_id, r).attr('id');
		mp_taxonomy.options[mp_taxonomy.options.length] = new Option(name, id);

		if (r != '') mp_taxonomy.addAfter2( r, settings );
	},

	addAfter2 : function( x, r ) {
		var t = jQuery(r.parsed.responses[0].data);
	},

	delAfter : function( r, settings ) {
		var id = jQuery(MP_AdminPageL10n.tr_prefix_id, r).attr('id'), o;
		for ( o = 0; o < mp_taxonomy.options.length; o++ ) if ( id == mp_taxonomy.options[o].value ) mp_taxonomy.options[o] = null;
	},

	delBefore : function(s) {
		if ( 'undefined' != showNotice ) return showNotice.warn() ? s : false;
		return s;
	}
}
jQuery(document).ready(function(){ mp_taxonomy.init(); });