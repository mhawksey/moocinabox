var mp_refresh = {
	every   : 60,
	reg     : new RegExp("(%i%)", "g"),
	message : '',

	init  : function() {
		mp_refresh.every = mp_refresh.get_time();

		var m1 = "<div style='background-color: rgb(255, 251, 204);' id='message' class='updated fade'><p>%i%</p></div>";
		mp_refresh.message = m1.replace(mp_refresh.reg, adminMpRefreshL10n.message.replace(mp_refresh.reg, "<span id='mp_refresh_chrono'>%i%</span>"));

		adminMpRefreshL10n.option = adminMpRefreshL10n.option.replace(/\&gt;/g,'>').replace(/\&lt;/g,'<');

		jQuery('#screen-options-wrap').append(adminMpRefreshL10n.option);
		if (jQuery('#MP_Refresh').is(':checked')) mp_refresh.start();
	// onchange checkbox
		jQuery('#MP_Refresh').change( function() { (jQuery(this).is(':checked')) ? mp_refresh.start() : mp_refresh.stop(); } );
	// onclick message
		jQuery('div#mp_message').click( function() { mp_refresh.stop(); } );
	},

	get_time : function() {
		var time = jQuery('input#MP_Refresh_every').val();
		time     = (isNaN(time)) ? adminMpRefreshL10n.every : time;
		time 	   = (adminMpRefreshL10n.every > time) ? adminMpRefreshL10n.every : time;
		jQuery('input#MP_Refresh_every').val(time);
		return time;
	},

	start : function() {

		var message = mp_refresh.message.replace(mp_refresh.reg, mp_refresh.get_time());
		jQuery('div#mp_message').html(message);

		jQuery.schedule({	id:'mp_refresh.update',
					time: 1000, 
					func: function() { mp_refresh.update(); }, 
					repeat: true, 
					protect: true
		});
	},

	stop : function() {
		jQuery('#MP_Refresh').attr('checked',false);
		jQuery('div#mp_message').html('');
		jQuery.cancel( 'mp_refresh.update' );
	},

	update : function() {
		var time = jQuery('span#mp_refresh_chrono').html();
		time--;
		if (time == 0)
		{
			mp_refresh.refresh();
			mp_refresh.stop();
		}
		if (time < 0) return;
		var message = mp_refresh.message.replace(mp_refresh.reg, time);
		jQuery('div#mp_message').html(message);
	},

	refresh : function() {
		var parms = {};
		var query = location.search.substring(1);
		var pairs = query.split('&');
		for(var i = 0; i < pairs.length; i++)
		{
			var pos = pairs[i].indexOf('=');
			if (pos == -1) continue;
			parms[pairs[i].substring(0,pos)] = unescape(pairs[i].substring(pos + 1));
		}
		parms['autorefresh'] = mp_refresh.get_time();
		
		var url = 'admin.php';
		for(a in parms)
		{
			var sep = (url == 'admin.php') ? '?' : '&';
			url += sep+a+'='+parms[a];
		}
		window.location = url;
	}
};
jQuery(document).ready( function(){ mp_refresh.init(); } );