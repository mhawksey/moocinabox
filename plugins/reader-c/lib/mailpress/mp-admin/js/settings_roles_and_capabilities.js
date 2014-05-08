// settings_RandC

var mp_settings_RandC = {

	init : function() {
		jQuery('td.capacity > label > input').click( function() {
			var name = jQuery(this).attr('name');
			var checked = jQuery(this).attr('checked');
			var r_and_c = name.split(/\[|\]\[|]/g);
			var spanid = r_and_c[1] + '_' + r_and_c[2] ;

			var rc_data = {	action:	"r_and_c", 
						role:		r_and_c[1], 
						capability: r_and_c[2], 
						add:		(checked) ? '1' : '0'
					  };
		//ajax
			jQuery.ajax({
				data : rc_data, 
				type : "POST", 
				url : MP_AdminPageL10n.requestFile, 
				success: mp_settings_RandC.crko_vs_crok(checked, spanid)
			});
		});
	},

	crko_vs_crok : function(checked, spanid) {
		jQuery('span#'+spanid).removeClass( (checked) ? 'crko' : 'crok' ).addClass( (checked) ? 'crok' : 'crko' );
	} 
}
jQuery(document).ready( function() { mp_settings_RandC.init() });