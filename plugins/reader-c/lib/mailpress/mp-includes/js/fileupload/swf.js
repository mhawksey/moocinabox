var mp_fileupload = {

// swfupload

	file_dialog_start_handler : function() {
		return true;
	},

	file_queued_handler : function(fileObj) {
		jQuery('#attachement-errors').html('');
		jQuery('#attachement-items').append(mp_fileupload.html(fileObj, false, false));
	},

	upload_start_handler : function(fileObj) { 
		return true; 
	},

	upload_progress_handler : function(fileObj, bytesDone, bytesTotal) {
		// Lengthen the progress bar
		var x = 100 - Math.round(100*bytesDone/bytesTotal)+'%';
		jQuery('#attachement-item-u-' + fileObj.id + ' div.mp_fileupload_bar_foregrnd').height(x);
	},

	upload_error_handler : function(fileObj, error_code, message) {
		// first the file specific error
		switch (error_code)
		{
			case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL :
				jQuery('#attachement-errors').html(swfuploadL10n.missing_upload_url);
			break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED :
				jQuery('#attachement-errors').html(swfuploadL10n.upload_limit_exceeded);
			break;
			case SWFUpload.UPLOAD_ERROR.HTTP_ERROR :
				jQuery('#attachement-errors').html(swfuploadL10n.http_error);
			break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED :
				jQuery('#attachement-errors').html(swfuploadL10n.upload_failed);
			break;
			case SWFUpload.UPLOAD_ERROR.IO_ERROR :
				jQuery('#attachement-errors').html(swfuploadL10n.io_error);
			break;
			case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR :
				jQuery('#attachement-errors').html(swfuploadL10n.security_error);
			break;
			case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED :
				jQuery('#attachement-errors').html(swfuploadL10n.security_error);
			break;
			default :
				jQuery('#attachement-errors').html(swfuploadL10n.default_error);
		}
	},

	parsexml : function(xml){
        if( window.ActiveXObject && window.GetObject ) {
            var dom = new ActiveXObject( 'Microsoft.XMLDOM' );
            dom.loadXML( xml );
            return dom;
        }
        if( window.DOMParser )
            return new DOMParser().parseFromString( xml, 'text/xml' );
        throw new Error( 'No XML parser available' );
	},

	upload_success_handler : function(fileObj, xml) {
		// if async-upload returned an error message, place it in the media item div and return

		xml = mp_fileupload.parsexml(xml);

		var upload = jQuery(xml).find('mp_fileupload').each(function() {
			var error = jQuery(this).find('error').text();
			var id    = jQuery(this).find('id').text();
			var url   = jQuery(this).find('url').text();
			var file  = jQuery(this).find('file').text();

			if (error)
				jQuery('#attachement-item-u-' + fileObj.id).html(error);
			else
				jQuery('#attachement-item-u-' + fileObj.id).replaceWith(mp_fileupload.html(fileObj, id , url )); 
		});
	},

	upload_complete_handler : function(fileObj) {
		return true; 
	},

	file_queue_error_handler : function(fileObj, error_code, message) {
		// Handle this error separately because we don't want to create a FileProgress element for it.
		switch (error_code)
		{
			case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED :
				jQuery('#attachement-errors').html(swfuploadL10n.queue_limit_exceeded);
			break;
			case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT :
				jQuery('#attachement-errors').html(swfuploadL10n.file_exceeds_size_limit);
			break;
			case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE :
				jQuery('#attachement-errors').html(swfuploadL10n.zero_byte_file);
			break;
			case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE :
				jQuery('#attachement-errors').html(swfuploadL10n.invalid_filetype);
			break;
			default :
				jQuery('#attachement-errors').html(swfuploadL10n.default_error);
			break;
		}
	},

	file_dialog_complete_handler : function(num_files_queued) {
		try 
		{
			if (num_files_queued > 0) this.startUpload();
		} 
		catch (ex) 
		{
			this.debug(ex);
		}
	},

	swfupload_pre_load_handler : function() {
		var swfupload_element = jQuery('#'+swfu.customSettings.swfupload_element_id).get(0);
		jQuery('#' + swfu.customSettings.degraded_element_id).hide();
		// Doing this directly because jQuery().show() seems to have timing problems
		if ( swfupload_element && ! swfupload_element.style.display )
				swfupload_element.style.display = 'block';
	},

	swfupload_load_failed_handler : function() {
		jQuery('#' + swfu.customSettings.swfupload_element_id).hide();
		jQuery('#' + swfu.customSettings.degraded_element_id).show();
	},

//

	html : function (fileObj, id, url) {

		var html = '';

		var hid = (id) ? id : fileObj.id;
		html += '<div id="attachement-item-u-' + hid + '" class="attachement-item child-of-' + draft_id + '">';
		html += '<table cellspacing="0">'
		html += '<tr>';
		html += '<td>';

		html += (id) ? '<input type="checkbox" class="mp_fileupload_cb" name="Files[' + hid + ']" value="' + id + '" checked="checked" />' : '<div class="mp_fileupload_cb_anim"><div class="mp_fileupload_bar_backgrnd"></div><div class="mp_fileupload_bar_foregrnd"></div></div>';

		html += '</td>';
		html += '<td>&#160;';

		html += (id) ? '<a href="' + url + '" style="text-decoration:none;">' + fileObj.name + '</a>' : '<span>' + fileObj.name + '</span>';

		html += '</td>';
		html += '</tr>';
		html += '</table>';
		html += '</div>';

		return html;
	},

	add : function() {
		mp_swfupload.post_params.draft_id = draft_id;

		mp_swfupload.file_dialog_start_handler 	= mp_fileupload.file_dialog_start_handler,
		mp_swfupload.file_queued_handler 		= mp_fileupload.file_queued_handler,
		mp_swfupload.file_queue_error_handler 	= mp_fileupload.file_queue_error_handler,
		mp_swfupload.file_dialog_complete_handler = mp_fileupload.file_dialog_complete_handler,
		mp_swfupload.upload_start_handler 		= mp_fileupload.upload_start_handler,
		mp_swfupload.upload_progress_handler 	= mp_fileupload.upload_progress_handler,
		mp_swfupload.upload_error_handler 		= mp_fileupload.upload_error_handler,
		mp_swfupload.upload_success_handler 	= mp_fileupload.upload_success_handler,
		mp_swfupload.upload_complete_handler 	= mp_fileupload.upload_complete_handler,
		mp_swfupload.swfupload_pre_load_handler	= mp_fileupload.swfupload_pre_load_handler,
		mp_swfupload.swfupload_load_failed_handler= mp_fileupload.swfupload_load_failed_handler,

		swfu = new SWFUpload(mp_swfupload);
	},

	init : function () {
		if (draft_id != 0) 	mp_fileupload.add();
		else				jQuery('#attachementsdiv').hide();
	}
};