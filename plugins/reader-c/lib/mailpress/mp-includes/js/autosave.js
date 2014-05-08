var autosave = {

	bool 		: {},
	oldmessage  : '',
	periodical	: null,

	tinyMCE_triggerSave : function() {
		if ( (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden() ) 	
		{
			if ( 'mce_fullscreen' == tinyMCE.activeEditor.id ) 
				tinyMCE.get('content').setContent(tinyMCE.activeEditor.getContent({format : 'raw'}), {format : 'raw'});
			tinyMCE.triggerSave();
			return true;
		}
		return false;
	},

	tinyMCE_spellcheckerOn : function() {
		return ( autosave.tinyMCE_triggerSave() && tinyMCE.activeEditor.plugins.spellchecker && tinyMCE.activeEditor.plugins.spellchecker.active );
	},

	init : function() {
		if (jQuery('#autosavenonce').val())
		{
			autosave.tinyMCE_triggerSave();

			autosave.periodical 	= jQuery.schedule({	time: autosaveL10n.autosaveInterval * 1000, 
											func: function() { autosave.show_preview = false; autosave.main(); }, 
											repeat: true, 
											protect: true
			});

			jQuery("#mail_newform").submit(function() { jQuery.cancel(autosave.periodical); });	// Disable autosave after the form has been submitted

			autosave.init_html2txt();
			autosave.click_html2txt();
			autosave.click_preview();										// Autosave when the preview button is clicked.
			autosave.click_attachements_loader();								// Autosave when the change loader link is clicked.

			autosave.olddata = autosave.retrieve_data();
			autosave.newdata = autosave.olddata;
		}
		else
		{
			tb_init('span#preview-button a.preview');
		}
	},

// buttons
	enable_buttons : function() {
		jQuery(':button, :submit', '#submitpost').removeAttr('disabled');
	},

	disable_buttons : function() {
		jQuery(':button, :submit', '#submitpost').prop('disabled', true);
		setTimeout(autosave.enable_buttons, 5000); // Re-enable 5 sec later.  Just gives autosave a head start to avoid collisions.
	},

// data
	olddata 	: {},
	newdata 	: {},
	data 		: { 	toemail 	: 	'toemail',
				toname	:	'toname',
				fromemail	:	'fromemail',
				fromname	:	'fromname',
				to_list	:	'to_list',
				Theme		:	'Theme',
				subject	:	'title',
				html		:	'content',
				plaintext	:	'plaintext',
				mail_format:	'mail_format'
			  },

	retrieve_data : function() {
		var x = new Array();
		for (key in autosave.data) { x[key] = jQuery("#"+autosave.data[key]).val() || ""; }
		return x;
	},

	compare_data : function (a,b) {
		for (key in a) if (a[key] != b[key]) return false;
		return true;
	},

// preview
	show_preview : false,

	click_preview : function() {
		jQuery('#preview-button a').click(function(e) {
			autosave.show_preview = true;
			autosave.main();
			return false;
		});
	},

	callback_preview : function (previewlink) {
		var previewText = autosaveL10n.previewMailText;
		jQuery('#preview-button').html('<a class="button preview" target="_blank" href="'+previewlink+'" tabindex="4">'+previewText+'</a>');
		if (autosave.show_preview)
		{
			autosave.display_preview();
			autosave.show_preview = false;
		}
		autosave.click_preview();											// Autosave when the preview button is clicked. 
	},

	display_preview : function () {
		var href = jQuery('#preview-button a').attr("href");

		var selectedtheme = jQuery('#theme').val();

		if (selectedtheme != '')
		{
			var preview_iframe = href.indexOf('&preview_iframe');
			hrefnew = href.substring(0,preview_iframe)+'&theme='+selectedtheme+href.substring(preview_iframe);
			href = hrefnew;
		}

		var thickboxW = jQuery('#TB_window');
		var H = jQuery(window).height();
		var W = jQuery(window).width();

		var nW = parseInt(W *.8);
		var nH = parseInt(H *.9);

		if ( thickboxW.size() ) 
		{
			thickboxW.width( nW ).height( nH );
			jQuery('#TB_iframeContent').width('100%').height('100%');
			thickboxW.css({'margin-left': '-' + parseInt( nW / 2 ) + 'px'});
			if ( typeof document.body.style.maxWidth != 'undefined' )
				thickboxW.css({'top':'30px','margin-top':'0'});
		};

		href = href.replace(/&width=[0-9]+/g, '');
		href = href.replace(/&height=[0-9]+/g, '');
		hrefnew = href+'&width=' + ( nW ) + '&height=' + ( nH );
		href = hrefnew;

		tb_show(null,href,null);
	},

// attachements
	click_attachements_loader : function() {
		jQuery('#mp_loader_link').click(function(e) {
			autosave.clean_attachements();
			autosave.main();
			return true;
		});
	},

	enable_attachements : function(item_id) {
		draft_id = item_id;

		var href = jQuery('#mp_loader_link').attr('href');
		var oldhref = href;
		href.replace(/\&id=0/, '&id=' + draft_id);
		if (href == oldhref) href += '&id=' + draft_id;
		jQuery('#mp_loader_link').attr({href : href});

		mp_fileupload.add();
		jQuery('#attachementsdiv').show();
	},

	clean_attachements : function() {
		jQuery('.mp_fileupload_cb').not(':checked').each(function(){
			var meta_id = jQuery(this).val();
			var cb_data = {	action: 		"delete_attachement",
						meta_id:  		meta_id
			};
// ajax
			jQuery.ajax({
				data: cb_data,
				type: "POST",
				url: autosaveL10n.requestFile,
				success: function() {jQuery('#attachement-item-'+ meta_id).remove();jQuery('#attachement-item-u-'+ meta_id).remove();}
			});
		});
	},

// html2txt
	init_html2txt : function() {
		var dest ; 
		var html2txt = jQuery('div#div_html2txt');

		dest = jQuery('div#wp-content-media-buttons');
		if (!dest.length) 
		{
			jQuery('div#wp-content-editor-tools').append("<div class='hide-if-no-js wp-media-buttons' id='wp-content-media-buttons'></div>");
			dest = jQuery('div#wp-content-media-buttons');
		}
		dest.append(html2txt.html());
		html2txt.remove();
	},

	click_html2txt : function() {
		jQuery('a#html2txt').click(function() {

			autosave.tinyMCE_triggerSave();

			if (jQuery("#content").val()   == '') return;
			if (jQuery("#plaintext").val() != '' && !confirm(MP_AdminPageL10n.html2txt) ) return;

			var html2txt_data = {	action: "html2txt",
							html  : jQuery("#content").val()
			};
// ajax
			jQuery.ajax({
				beforeSend: function() { jQuery('#html2txt_loading').toggle(); },
				data: html2txt_data,
				type: 'POST',
				url: autosaveL10n.requestFile,
				success: function(response) { jQuery('#plaintext').val(response); jQuery('#html2txt_loading').toggle(); }
			});
			return false;
		});
	},


// ajax
	loading_ajax : function() {
		jQuery('#autosave').html(autosaveL10n.savingText);
	},

	callback_ajax : function(response) {
		var message = '';
		var res = wpAjax.parseAjaxResponse(response, 'autosave'); 						// parse the ajax response
		if ( res && res.responses && res.responses.length ) 
		{
			message = res.responses[0].data;
			if ( res.responses[0].supplemental ) 
			{
				jQuery.each(res.responses[0].supplemental, function(selector, value) {
					if ( selector.match(/^replace-/) ) 
					{
						jQuery('#'+selector.replace('replace-', '')).val(value);
					}
				});

				if ( 'disable' == res.responses[0].supplemental['disable_autosave'] ) 
				{
					autosave = function() {};
				}
				if ( '' != res.responses[0].supplemental['tipe'] ) 
				{
					var type 	= res.responses[0].supplemental['tipe'] ;
					var item_id = parseInt( res.responses[0].id );
					var item_main_id = parseInt( res.responses[0].oldId );
					if (item_id > 0)
					{
						autosave.update_id(item_id, type); 
																	// ajax to get preview link
						jQuery.post(autosaveL10n.requestFile, 
								{action: "get-previewlink", id: item_id, main_id: item_main_id, getpreviewlinknonce: jQuery('#getpreviewlinknonce').val(), height : parseInt(jQuery(window).height() * .8), width : parseInt(jQuery(window).width()  * .9) }, 
								autosave.callback_preview);
					}
					else
					{
						if (autosave.show_preview)
						{
							autosave.display_preview();
							autosave.show_preview = false;
						}
					}
				}
				else
				{
					if (autosave.show_preview)
					{
						autosave.display_preview();
						autosave.show_preview = false;
					}
				}
				jQuery.each(	res.responses[0].supplemental, 
							function(selector, value) 
							{
								if ( (selector != 'tipe') && (selector.match(/^replace-/)) ) 
								{
									jQuery('#'+selector.replace('replace-', '')).val(value);
								}
							}
						);
			}
			if ( message ) { jQuery('#autosave').html(message); } 					// update autosave message
			else if ( autosave.oldmessage && res ) { jQuery('#autosave').html( autosave.oldmessage ); }
		}
		autosave.clean_attachements();
		if (autosave.show_preview)
		{
			autosave.display_preview();
			autosave.show_preview = false;
		}
		autosave.enable_buttons(); 
	},

	update_id : function(item_id, type) {
		if ( isNaN(item_id)) 						return;
		if ( item_id <= 0 )						return;
		var attr = (type == 'mail') ? 'id' : type;
		if ( item_id == parseInt(jQuery('#mail_'+attr).val()) ) 	return;				// no need to do this more than once

		jQuery('#mail_'+attr).val(item_id);

		if (type != 'mail') return;

		autosave.enable_attachements(item_id);
	},


	main : function() {

		autosave.bool['spell'] = autosave.tinyMCE_spellcheckerOn();

		var mail_data = 	{	action: 		"autosave",
						autosavenonce: 	jQuery('#autosavenonce').val(),
						id:  			jQuery("#mail_id").val() || 0,
						revision:  		jQuery("#mail_revision").val() || -1
					};
		var x = 0;
		for (key in autosave.data)
		{
			mail_data[key] = jQuery("#"+autosave.data[key]).val() || "";
			x = x + mail_data[key].length;
		}

		autosave.newdata = autosave.retrieve_data();

		autosave.bool['empty'] = (x == 0);
		autosave.bool['nochg'] = autosave.compare_data(autosave.olddata,autosave.newdata);
		autosave.bool['thick'] = ( jQuery("#TB_window").css('display') == 'block' );
																	// We always send the ajax request in order to keep the post lock fresh.
		mail_data['autosave'] = 1;											// This (bool) tells whether or not to write the mail to the DB during the ajax request.
		for (key in autosave.bool) if (autosave.bool[key]) mail_data['autosave'] = 0;

		autosave.oldmessage 	= jQuery('#autosave').html();
		autosave.olddata 		= autosave.newdata;
		autosave.disable_buttons();
// ajax
		jQuery.ajax({
			data: mail_data,
			beforeSend: (mail_data['autosave'] == 1) ? autosave.loading_ajax : null,
			type: "POST",
			url: autosaveL10n.requestFile,
			success: autosave.callback_ajax
		});
	}
};