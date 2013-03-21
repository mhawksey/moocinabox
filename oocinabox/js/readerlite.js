// JavaScript Document
function customAccordionHooks(){
	jQuery('.jump_to_url').on("click", function(event){
		event.stopImmediatePropagation();
		event.stopPropagation();
	});	
	jQuery('.wpfp-link').on("click", function(event){	
		event.stopImmediatePropagation();
		event.stopPropagation();
		//Your Code here(For example a call to your function)
		dhis = jQuery(this);
		wpfp_do_js( dhis, 1 );
				
		// for favorite post listing page
		if (dhis.hasClass('remove-parent')) {
			dhis.parent("li").fadeOut();
		}
		return false;
	});	
	jQuery('.wpfp-link').addClass(function() { return qs(this.href).wpfpaction});
}
function wpfp_do_js( dhis, doAjax ) {
	dhis.hide();
    loadingImg = dhis.prev();
    loadingImg.show();
    beforeImg = dhis.prev().prev();
    beforeImg.hide();
    url = document.location.href.split('#')[0];
    params = dhis.attr('href').replace('?', '') + '&ajax=1';
    if ( doAjax ) {
        jQuery.get(url, params, function(data) {
                dhis.parent().html(data);
                if(typeof wpfp_after_ajax == 'function') {
                    wpfp_after_ajax( dhis ); // use this like a wp action.
                }
                loadingImg.hide();
				//customAccordionHooks();
            }
        );
    }
}

function wpfp_after_ajax( dhis ){
	customAccordionHooks();
	var action = qs(dhis[0].href).wpfpaction;
	if (action == "add"){
		dhis.parent().removeClass("remove");
		dhis.parent().addClass("add");
	} else {
		dhis.parent().removeClass("add");
		dhis.parent().addClass("remove");
	}
}

function qs(url) {
    var params = {}, queries, temp, i, l;
    // Split into key/value pairs
    queries = url.substring( url.indexOf('?') + 1 ).split("&");
    // Convert the array of strings into an object
    for ( i = 0, l = queries.length; i < l; i++ ) {
        temp = queries[i].split('=');
        params[temp[0]] = temp[1];
    }
    return params;
};

jQuery.sharedCount = function(url, fn) {
	url = encodeURIComponent(url || location.href);
	var arg = {
		url: "//" + (location.protocol == "https:" ? "sharedcount.appspot" : "api.sharedcount") + ".com/?url=" + url,
		cache: true,
		dataType: "json"
	};
	if ('withCredentials' in new XMLHttpRequest) {
		arg.success = fn;
	}
	else {
		var cb = "sc_" + url.replace(/\W/g, '');
		window[cb] = fn;
		arg.jsonpCallback = cb;
		arg.dataType += "p";
	}
	return jQuery.ajax(arg);
};