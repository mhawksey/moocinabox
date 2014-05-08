function mp_gmap3(settings)
{
	this.settings 	= settings;
	this.map 	= null;
	this.prefix 	= this.settings.prefix;
	this.div 	= document.getElementById(this.prefix + '_map');

	this.center_lat = jQuery('#' + this.prefix + '_center_lat');
	this.center_lng = jQuery('#' + this.prefix + '_center_lng');
	this.center 	= new google.maps.LatLng(parseFloat(this.center_lat.val()), parseFloat(this.center_lng.val()));

	this.zoomlevel  = jQuery('#' + this.prefix + '_zoomlevel');
	this.maptype  	= jQuery('#' + this.prefix + '_maptype');

	this.init = function() {

		var myOptions = {
			center: 		this.center,
			draggable:		true,
			mapTypeControl:	false,
			mapTypeId: 	this.map_type(this.maptype.val()),
			panControl:	false,
			zoom: 		parseInt(this.zoomlevel.val()),
			streetViewControl:	false,
			zoomControlOptions: {style:'SMALL'}
		};

		this.map = new google.maps.Map(this.div, myOptions);

		this.map_events();
//
		var first = true;
		var ControlDiv = document.createElement('DIV');
		ControlDiv.setAttribute('style', 'margin:10px 5px 0 0;');
		this.changeMapType(ControlDiv, first); first = false;
		this.setCenter(ControlDiv, first);     first = false;
		this.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(ControlDiv);

		var schedule_id = this.prefix + '_schedule';
		var _this = this;
		jQuery.schedule({	id: schedule_id,
					time: 60000, 
					func: function() { _this.update_settings(); }, 
					repeat: true, 
					protect: true
		});

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

	this.update_settings = function() {
		var data 		= {};
		data['action'] 	= 'map_settings';
		data['id']     	= mp_gmapL10n.id;
		data['type']   	= mp_gmapL10n.type;
		data['prefix'] 	= this.prefix;
		data['settings[center_lat]'] = this.center_lat.val();
		data['settings[center_lng]'] = this.center_lng.val();
		data['settings[zoomlevel]']  = this.zoomlevel.val();
		data['settings[maptype]']    = this.maptype.val();

		jQuery.ajax({
			data: data,
			beforeSend: null,
			type: "POST",
			url: mp_gmapL10n.ajaxurl,
			success: null
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
		var center = this.center;

		var container = document.createElement('div');
		if (!first) container.setAttribute('style', 'margin-top:-10px;');
		var img = document.createElement('img');
		img.setAttribute('src', mp_gmapL10n.url+'map_center'+'.png');
		img.setAttribute('alt', mp_gmapL10n.center);
		img.setAttribute('title', mp_gmapL10n.center);
		img.setAttribute('style', 'margin-bottom:-1px;');
	 	container.appendChild(img);
		div.appendChild(container);

	  	google.maps.event.addDomListener(img, 'click', function() { map.setCenter(center); });
	}

	this.init();
}