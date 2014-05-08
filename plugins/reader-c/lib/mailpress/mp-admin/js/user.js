// user

var mp_user = {

	init : function() {
		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

		// postboxes
		postboxes.add_postbox_toggles(MP_AdminPageL10n.screen);

		// custom fields
		jQuery('#the-list').wpList({ 	
			addAfter: function( xml, s ) {
				jQuery('table#list-table').show();
			}, 
			addBefore: function( s ) {
				s.data += '&mp_user_id=' + jQuery('#mp_user_id').val(); 
				return s;
			}
		});

		// mailinglist tabs
		jQuery('#user-tabs').tabs();

		// ip info
		mp_user.gmap();
	},

	gmap : function() {
		if(typeof(meta_box_IP_info) == "undefined") return;

		var map = new mp_gmap3(meta_box_IP_info_user_settings);

		var mkOptions = {
			position:new google.maps.LatLng(parseFloat(meta_box_IP_info.lat), parseFloat(meta_box_IP_info.lng)),
			map:map.map,
			title:'lat : '+meta_box_IP_info.lat+' lng : '+meta_box_IP_info.lng
		};

		var marker = new google.maps.Marker(mkOptions);
	}
}
jQuery(document).ready( function() { mp_user.init(); });