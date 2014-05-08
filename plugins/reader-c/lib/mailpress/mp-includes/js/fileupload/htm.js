var mp_fileupload = {

	nbfiles : -1,
/*
	changed : function(id, file) {
		return true;
	},
*/
	parsexml : function(xml){
		xml = xml.replace(/\&gt;/g,'>');
		xml = xml.replace(/\&lt;/g,'<');
		xml = xml.replace(/><!--\[CDATA\[/g,'><![CDATA[');
		xml = xml.replace(/\]\]--></g,']]><');
		if( window.ActiveXObject && window.GetObject ) {
			var dom = new ActiveXObject( 'Microsoft.XMLDOM' );
			dom.loadXML( xml );
			return dom;
		}
		if( window.DOMParser )
			return new DOMParser().parseFromString( xml, 'text/xml' );
		throw new Error( 'No XML parser available' );
	},

	loaded : function(id, filename, xml, oldid) {
		jQuery('span.mp_fileupload_txt').html();
		jQuery('iframe#mp_fileupload_iframe_' + oldid).remove();
		xml = mp_fileupload.parsexml(xml);
		var upload = jQuery(xml).find('mp_fileupload').each(function() {
			var error = jQuery(this).find('error').text();
			var id    = jQuery(this).find('id').text();
			var url   = jQuery(this).find('url').text();
			var file  = jQuery(this).find('file').text();
			if (error)
				jQuery('#attachement-item-u-' + oldid).html(error);
			else
				jQuery('#attachement-item-u-' + oldid).replaceWith(mp_fileupload.html(filename, id, url, true )); 
		});
		jQuery('iframe#mp_fileupload_iframe_' + oldid).remove();
		mp_fileupload.add();
	var toto = 0;
	},

	html : function (name, id, url, ok) {
		var html = '';

		html += '<div id="attachement-item-u-' + id + '" class="attachement-item child-of-' + draft_id + '">';
		html += '<table cellspacing="0">'
		html += '<tr>';
		html += '<td>';

		var maybe = (ok) ? '<div class="mp_fileupload_cb_anim" style="border:1px solid #c0c0c0;"><img src="' + htmuploadL10n.img + '" alt="" /></div>' : '<div class="mp_fileupload_cb_anim"></div>';
		html += (url) ? '<input type="checkbox" class="mp_fileupload_cb" name="Files[' + id + ']" value="' + id + '" checked="checked" />' : maybe;

		html += '</td>';
		html += '<td>&#160;';

		html += (url) ? '<a href="' + url + '" style="text-decoration:none;">' + name + '</a>' : '<span>' + name + '</span>';
		html += '<div id="mp_htmlupload_input_file_' + id + '" style="display:none;"></div>';

		html += '</td>';
		html += '</tr>';
		html += '</table>';
		html += '</div>';

		return html;
	},

	submitted : function(id, file) {
		jQuery('span.mp_fileupload_txt').html(htmuploadL10n.uploading);
		jQuery('#attachement-items').append(mp_fileupload.html(file, id, false, true));
	},

	iframe_loaded : function(id) {
		var i = document.getElementById('mp_fileupload_iframe_' + id);
		i.onload = null;
		var count = jQuery('input.mp_fileupload_cb').size();
		jQuery('span.mp_fileupload_txt').html((count == 0)  ? htmuploadL10n.attachfirst : htmuploadL10n.attachseveral);
	},

	add    : function() {
		mp_fileupload.nbfiles++;

		var i = document.createElement('iframe');
		i.setAttribute('class', 'mp_fileupload_iframe');
		i.setAttribute('id', 'mp_fileupload_iframe_' + mp_fileupload.nbfiles);
		i.setAttribute('name', 'mp_fileupload_iframe_' + mp_fileupload.nbfiles);
		i.setAttribute('style', 'height:24px;width:132px;');
		i.setAttribute('onload', 'mp_fileupload.iframe_loaded('+ mp_fileupload.nbfiles + ')');
		iframeurl = htmuploadL10n.iframeurl + '?action=upload_iframe_html&draft_id=' + draft_id + '&id=' + mp_fileupload.nbfiles;
		i.setAttribute('src', iframeurl);
		i.style.height = '24px';
		i.style.width = '132px';
		i.style.overflow = 'hidden';
		var d = document.getElementById('mp_fileupload_file_div');
		d.appendChild(i);
	},

	init : function () {
		if (draft_id != 0) 	mp_fileupload.add();
		else 				jQuery('#attachementsdiv').hide();
	}
};