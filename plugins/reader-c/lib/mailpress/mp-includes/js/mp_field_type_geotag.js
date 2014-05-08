function mp_field_type_geotag(settings)
{
	this.settings 	= settings;
	this.map 		= null;
	this.prefix 	= 'mp_' + this.settings.form + '_' + this.settings.field;
	this.div 		= document.getElementById(this.prefix + '_map');

	this.center_lat	= jQuery('#' + this.prefix + '_center_lat');
	this.center_lng	= jQuery('#' + this.prefix + '_center_lng');
	this.center 	= new google.maps.LatLng(parseFloat(this.center_lat.val()), parseFloat(this.center_lng.val()));

	this.zoomlevel 	= jQuery('#' + this.prefix + '_zoomlevel');
	this.maptype  	= jQuery('#' + this.prefix + '_maptype');

	this.lat 		= jQuery('#' + this.prefix + '_lat,#' + this.prefix + '_lat_d');
	this.lng 		= jQuery('#' + this.prefix + '_lng,#' + this.prefix + '_lng_d');
	this.rgeocode 	= jQuery('#' + this.prefix + '_geocode');

	this.init = function() {

		var myOptions = {
			center: 		this.center,
			draggable:		true,
			mapTypeControl:	false,
			mapTypeId: 	this.map_type(this.maptype.val()),
			panControl:	false,
			zoom: 		parseInt(this.zoomlevel.val()),
			streetViewControl:	false,
			zoomControl:	(this.settings.zoom 	== '1'),
			zoomControlOptions: {style:'SMALL'}
		};

		this.map = new google.maps.Map(this.div, myOptions);

		this.map_events();
//
		this.LatLng = new google.maps.LatLng(parseFloat(this.settings.lat), parseFloat(this.settings.lng));
		var mkOptions = {
			position: 	this.LatLng,
			map: 		this.map,
			draggable: 	true
		};

		this.marker = new google.maps.Marker(mkOptions);

		this.marker_events();
//
		this.geocoder = new google.maps.Geocoder();

		this.geocoder_events();
//
		var first = true;
		var ControlDiv = document.createElement('DIV');
		ControlDiv.setAttribute('style', 'margin:10px 5px 0 0;');
		if (this.settings.changemap 	== '1') {this.changeMapType(ControlDiv, first);  first = false;}
		if (this.settings.center 		== '1') {this.setCenter(ControlDiv, first); 	 first = false;}
		if (this.settings.rgeocode  	== '1') {this.reverseGeocode(ControlDiv, first); first = false;}
		this.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(ControlDiv);
	}

	this.map_type = function(maptype) {
		switch(maptype)
		{
			case 'SATELLITE'	: return google.maps.MapTypeId.SATELLITE;		break;
			case 'HYBRID' 	: return google.maps.MapTypeId.HYBRID;		break;
			case 'TERRAIN'	: return google.maps.MapTypeId.TERRAIN;		break;
			default	 	: return google.maps.MapTypeId.ROADMAP;		break;
		}
	}

	this.map_events = function() {
		var map		= this.map;

		var center_lat  = this.center_lat;
		var center_lng  = this.center_lng;
		google.maps.event.addListener(this.map, 'dragend', function() {
			var LatLng = map.getCenter();
			center_lat.val(LatLng.lat());
			center_lng.val(LatLng.lng());
		});

		var zoomlevel   = this.zoomlevel;
		google.maps.event.addListener(this.map, 'zoom_changed', function() {
			zoomlevel.val(map.getZoom());
		});
	}

	this.marker_events = function() {
		var marker = this.marker;

		var lat = this.lat;
		var lng = this.lng;
		google.maps.event.addListener(this.marker, 'drag', function() {
			var LatLng = this.getPosition();
			lat.val(LatLng.lat().toFixed(6));
			lng.val(LatLng.lng().toFixed(6));
		});
		google.maps.event.addListener(this.marker, 'dragend', function() {
			var LatLng = this.getPosition();
			lat.val(LatLng.lat().toFixed(6));
			lng.val(LatLng.lng().toFixed(6));
		});
	}

	this.geocoder_events = function() {
		var map 	= this.map;
		var marker 	= this.marker;
		var geocoder	= this.geocoder;

		var lat 	= this.lat;
		var lng 	= this.lng;
		var prefix 	= this.prefix;
		jQuery('#' + prefix + '_geocode_button').click( function() {
			var address = jQuery('#' + prefix + '_geocode').val();
			geocoder.geocode( {'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var LatLng = results[0].geometry.location;
					map.setCenter(LatLng);
					marker.setPosition(LatLng);
					lat.val(LatLng.lat().toFixed(6));
					lng.val(LatLng.lng().toFixed(6));
				} else {
					alert("Geocoder failed due to: " + status);
				}
			});
		});
	}

	this.changeMapType = function(div, first)
	{
		var map = this.map;
		var maptype = this.maptype;

		var container = document.createElement('div');
		if (!first) container.setAttribute('style', 'margin-top:-10px;');
		var img = document.createElement('img');
		img.setAttribute('src', mp_gmapL10n.url+'map_control'+'.png');
		img.setAttribute('alt', mp_gmapL10n.changemap);
		img.setAttribute('title', mp_gmapL10n.changemap);
		img.setAttribute('style', ' cursor:pointer;');
	  	container.appendChild(img);
		div.appendChild(container);

	  	google.maps.event.addDomListener(img, 'click', function() 
		{
			switch (true)
			{
				case ( map.getMapTypeId() == google.maps.MapTypeId.ROADMAP ):
					map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
					maptype.val('SATELLITE');
				break;
				case ( map.getMapTypeId() == google.maps.MapTypeId.SATELLITE ):
					map.setMapTypeId(google.maps.MapTypeId.HYBRID);
					maptype.val('HYBRID');
				break;
				case ( map.getMapTypeId() == google.maps.MapTypeId.HYBRID ):
					map.setMapTypeId(google.maps.MapTypeId.TERRAIN);
					maptype.val('TERRAIN');
				break;
				default:
					map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
					maptype.val('ROADMAP');
				break;
			}
		});
	}

	this.setCenter = function(div, first)
	{
		var map = this.map;
		var marker = this.marker;

		var center_lat  = this.center_lat;
		var center_lng  = this.center_lng;

		var container = document.createElement('div');
		if (!first) container.setAttribute('style', 'margin-top:-10px;');
		var img = document.createElement('img');
		img.setAttribute('src', mp_gmapL10n.url+'map_center'+'.png');
		img.setAttribute('alt', mp_gmapL10n.center);
		img.setAttribute('title', mp_gmapL10n.center);
		img.setAttribute('style', ' cursor:pointer;');
	 	container.appendChild(img);
		div.appendChild(container);

	  	google.maps.event.addDomListener(img, 'click', function() { 
			var LatLng = marker.getPosition();
			center_lat.val(LatLng.lat());
			center_lng.val(LatLng.lng());
			map.setCenter(LatLng);
		});
	}

	this.reverseGeocode = function(div, first)
	{
		var map 	= this.map;
		var marker 	= this.marker;
		var geocoder= this.geocoder;

		var lat 	= this.lat;
		var lng 	= this.lng;
		var prefix 	= this.prefix;
		var rgeocode= this.rgeocode;
		
		var container = document.createElement('div');
		if (!first) container.setAttribute('style', 'margin-top:-10px;');
		var img = document.createElement('img');
		img.setAttribute('src', mp_gmapL10n.url+'map_geocode'+'.png');
		img.setAttribute('alt', mp_gmapL10n.rgeocode);
		img.setAttribute('title', mp_gmapL10n.rgeocode);
		img.setAttribute('style', ' cursor:pointer;');
	 	container.appendChild(img);
		div.appendChild(container);

	  	google.maps.event.addDomListener(img, 'click', function() 
		{
			var LatLng = marker.getPosition();
			geocoder.geocode( {'latLng': LatLng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (results[0]) {
						rgeocode.val(results[0].formatted_address);
					} else {
						rgeocode.val('');
						alert("No results found");
					}
				} else {
					rgeocode.val('');
					alert("Geocoder failed due to: " + status);
				}
			});
		});
	}

	this.init();
}