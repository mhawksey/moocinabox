var mp_meta_box_post_test = {

	fields  : {'toemail' : 0, 'newsletter' : 0, 'theme' : 0},
	select  : {'toemail' : 0, 'newsletter' : 1, 'theme' : 1},
	css_err : {'border-color' : '#CC0000', 'background-color' :'#FFEBE8'},
	css     : {},

// init
	init : function() {
		
		for (field in mp_meta_box_post_test.fields)
		{
			// edit
			jQuery('.mp-edit-' 	+ field).click(function(){ var a = jQuery(this); var fld = a.attr('href').substr(4); mp_meta_box_post_test.edit(fld); return false; });
			// cancel
			jQuery('.mp-cancel-' 	+ field).click(function(){ var a = jQuery(this); var fld = a.attr('href').substr(4); mp_meta_box_post_test.cancel(fld); return false; });
			// ok
			jQuery('.mp-save-' 	+ field).click(function(){ var a = jQuery(this); var fld = a.attr('href').substr(4); mp_meta_box_post_test.ok(fld, 'init'); return false; });
			// css
			mp_meta_box_post_test.css[field] = {};
			for (prop in mp_meta_box_post_test.css_err) mp_meta_box_post_test.css[field][prop] = jQuery('#mp_' + field).css(prop);
		}

		jQuery('.mp_meta_box_post_test').click( function(){
			//¤ check data
			for (field in mp_meta_box_post_test.fields) { mp_meta_box_post_test.ok(field, 'ajax'); if (mp_meta_box_post_test.ajax_enabled()) return; }

			//¤ collect data
			var data = {'action' : 'mp_meta_box_post_test', 'post_id' : parseInt(jQuery('#post_ID').val())};
			for (field in mp_meta_box_post_test.fields) data[field] = jQuery('#mp_' + field).val();

			//¤ loading
			jQuery('div#MailPress_post_test_ajax').fadeTo(500,0);
		 	jQuery('div#MailPress_post_test_loading').fadeTo(500,1);

			//¤ ajax
			jQuery.ajax({
				data: data,
				beforeSend: null,
				type: "POST",
				url: mpMeta_box_postL10n.url,
				success: mp_meta_box_post_test.callback_ajax
			});
		});
	},

// for each item
	open  : function(field) { jQuery('#mp_div_' + field).slideDown("normal"); jQuery('.mp-edit-' + field).hide(); },
	close : function(field) { jQuery('#mp_div_' + field).slideUp("normal");   jQuery('.mp-edit-' + field).show(); },

// for test button
	disable_ajax : function() { jQuery('.mp_meta_box_post_test').attr('disabled', 'disabled'); },
	enable_ajax  : function() { jQuery('.mp_meta_box_post_test').removeAttr('disabled'); },
	ajax_enabled : function() { return ('disabled' == jQuery('.mp_meta_box_post_test').attr('disabled')); },

// for field check
	check_data : function(field, value) {
		switch(field)
		{
			case 'toemail' :
				if (mp_meta_box_post_test.is_email(value)) break;
				mp_meta_box_post_test.is_error('toemail');
				return false;
			break;
		}
		mp_meta_box_post_test.is_ok(field);
		return true;
	},

	is_error : function(field) {
		// some css
		jQuery('#mp_' + field).css(mp_meta_box_post_test.css_err);
		mp_meta_box_post_test.fields[field]++;
		// disable Test button
		jQuery('.mp_meta_box_post_test').attr('disabled', 'disabled');
	},

	is_ok : function(field) {
		// restore css
		jQuery('#mp_' + field).css(mp_meta_box_post_test.css[field]);
		mp_meta_box_post_test.fields[field] = 0;
		// restore Test button
		for (fld in mp_meta_box_post_test.fields) if (mp_meta_box_post_test.fields[fld]) return;
		jQuery('.mp_meta_box_post_test').removeAttr('disabled');
	},

	is_email : function(m) { 
		var pattern = /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/; 
		return pattern.test(m);
	},


// for each field
	edit : function(field) { mp_meta_box_post_test.open(field); },

	cancel : function(field) {
		mp_meta_box_post_test.is_ok(field);
		jQuery('#mp_' + field).val(jQuery('#mp_hidden_' + field).val());
		if (mp_meta_box_post_test.select[field])
			jQuery('#span_' + field).html(jQuery('#mp_hidden_lib_' + field).val());
		else
			jQuery('#span_' + field).html(jQuery('#mp_hidden_' + field).val());
		mp_meta_box_post_test.close(field);
	},

	ok : function(field, status) {
		var val = jQuery('#mp_' + field).val();

		if (mp_meta_box_post_test.check_data(field, val))
		{
			if (('newsletter' == field) || ('theme' == field))
				val = jQuery('#mp_'+field+' >option').filter(':selected').text();

			jQuery('#span_' + field).html(val);
			mp_meta_box_post_test.close(field);
			return true;
		}

		if ('ajax' == status) mp_meta_box_post_test.open(field);

		return false;
	},

// back from ajax
	callback_ajax : function(response) {
		var message = '';
		var id = 0;

		var res = wpAjax.parseAjaxResponse(response, 'mp_meta_box_post_test'); 					// parse the ajax response
		if ( res && res.responses && res.responses.length ) 
		{
			message = res.responses[0].data;
			id	  = res.responses[0].id;
			if ( message ) { jQuery('#MailPress_post_test_ajax').html(message); } 				// display message
		}

	 	jQuery('div#MailPress_post_test_loading').fadeTo(500,0);
		jQuery('div#MailPress_post_test_ajax').fadeTo(500,1);
	}
}
jQuery(document).ready( function() { mp_meta_box_post_test.init() });