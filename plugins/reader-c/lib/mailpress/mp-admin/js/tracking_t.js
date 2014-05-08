// tracking

var mp_tracking = {

	max : 30,
	infowindow : false,

	init : function() {
		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

		// postboxes
		postboxes.add_postbox_toggles(MP_AdminPageL10n.screen);

		// ip info
		mp_tracking.gmap();
	},

	gmap : function() {
		if(typeof(t006) == "undefined") return;

		var map  = new mp_gmap3(t006_user_settings);

		google.maps.event.addListener(map.map, 'click', function() {
			if (mp_tracking.infowindow) mp_tracking.infowindow.close();
		});

		var markers = [];

		for (var i in t006) markers.push(mp_tracking.marker(map.map, t006[i]));

		if ((parseInt(i) + 1) > mp_tracking.max) var markerCluster = new MarkerClusterer(map.map, markers);
	},

	marker : function(map, data) {
		var mkOptions = {
			position:new google.maps.LatLng(parseFloat(data['lat']), parseFloat(data['lng'])),
			map:map,
			title:data['ip']
		};
		if(typeof(data['icon']) != "undefined")
			mkOptions['icon'] = new google.maps.MarkerImage(data['icon']);

		var marker = new google.maps.Marker(mkOptions);

		if(typeof(data['info']) != "undefined")
		{
			google.maps.event.addListener(marker, 'click', function() {
				if (mp_tracking.infowindow) mp_tracking.infowindow.close();
				mp_tracking.infowindow = new google.maps.InfoWindow({content:data['info']});
				mp_tracking.infowindow.open(map, marker);
			});
		}
		return marker;
	}
}
jQuery(document).ready( function() { mp_tracking.init(); });