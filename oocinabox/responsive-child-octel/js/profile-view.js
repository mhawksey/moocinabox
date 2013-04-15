// JavaScript Document
jQuery(document).ready(function($) {
	$('label[for="user-submitted-content"]').text("Description");
	content_area = $('textarea[name="user-submitted-content"]').attr('placeholder', 'Short description');
	$('#user-submitted-posts > form').submit(function(e) {     
		var count = 0;
		var url = $('input[name="user-submitted-url"]');
		var title = $('input[name="user-submitted-title"]');
		var content_area = $('textarea[name="user-submitted-content"]');
		
        var  valid = false;
		
		// http://stackoverflow.com/a/2723190/1027723
		if(/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url.val())) {  
			$('#user-sub-url-error').remove();
			count++;
		} else {
		  if (!url.next().hasClass('user-submitted-error'))
		  	url.after('<div id="user-sub-url-error" class="user-submitted-error">Please enter a valid url</div>');
		  e.preventDefault();
          valid = false;
		}
		
		if (title.val().trim() != ""){
			$('#user-sub-title-error').remove();
			count++;
		} else {
			if (!title.next().hasClass('user-submitted-error'))
				title.after('<div id="user-sub-title-error" class="user-submitted-error">Please enter a title for your resource</div>');
			e.preventDefault();
            valid = false;
		}

		if (content_area.val().trim() != ""){
			$('#user-sub-content-error').remove();
			count++;
		} else {
			if (!content_area.next().hasClass('user-submitted-error'))
				content_area.after('<div id="user-sub-content-error" class="user-submitted-error">Please enter a short resource description</div>');
		    e.preventDefault();
            valid = false;			
		}
		if (count >= 3){
			valid = true;
		}
		return valid;
	});
});