jQuery(document).ready( function() {
	jQuery('input#mp_fileupload_file_' + uploadhtmlL10n.id).change(function(){
		var file = jQuery('input#mp_fileupload_file_' + uploadhtmlL10n.id).val(); 

		jQuery('#upload_form_' + uploadhtmlL10n.id).submit(function(){
			jQuery('label#mp_fileupload_file').hide();
			var filea = jQuery('input#mp_fileupload_file_' + uploadhtmlL10n.id).val();
			var fileb = filea.match(/(.*)[\/\\]([^\/\\]+\.\w+)$/)
            	var file  = (fileb === null) ? jQuery('input#mp_fileupload_file_' + uploadhtmlL10n.id).val() : fileb[2];

			var i = document.createElement('input');
			i.setAttribute('type', 'hidden');
			i.setAttribute('name', 'file');
			i.setAttribute('value', file);
			var f = document.getElementById('upload_form_' + uploadhtmlL10n.id);
			f.appendChild(i);

			parent.mp_fileupload.submitted(uploadhtmlL10n.id, file); 
			return true;
		});
		jQuery('#upload_form_' + uploadhtmlL10n.id).submit();
	});
});