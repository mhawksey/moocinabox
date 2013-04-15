// JavaScript Document
jQuery(document).ready(function($) {
    $( ".searchsubmit.bbpsw-search-submit").val("Go");
	$( ".widget-wrapper.widget_bbpress_search").children().eq(0).hide();
	$( "#accordion" ).accordion({active: false, collapsible: true, heightStyle: "content"});
	customAccordionHooks();	
	$( "#accordion" ).show();
	$( "#accordionLoader" ).hide();
	
	// shared count getter https://gist.github.com/yahelc/1413508#file-jquery-sharedcount-js
	
	$( document.body ).on( 'post-load', function(){
		$('.infinite-loader').remove();
		var opened = $("#accordion").accordion( "option", "active" );
		$("#accordion").accordion('destroy');
		$("#accordion").accordion({active: opened, collapsible: true, heightStyle: "content"});
		customAccordionHooks();	
	});

	$("#accordion").on("accordionactivate", function(event ,ui){
		event.preventDefault(); 
		var accor = $('.ajaxed', ui.newHeader);
		var loaded_post = $('.loaded-post', ui.newPanel);
		var post_id = accor.attr("id");
		var post_url  = accor.attr("url");
		var post_type = accor.attr("type");
		var _gaq = _gaq || [];
		_gaq.push(['_trackEvent', 'Course Reader', 'read', post_url]);
		if (!loaded_post.hasClass('true') && loaded_post.length > 0){ 

			// clean post url removing GA utm_ for shared count
			post_url = post_url.replace(/\?([^#]*)/, function(_, search) {
							search = search.split('&').map(function(v) {
							  return !/^utm_/.test(v) && v;
							}).filter(Boolean).join('&'); // omg filter(Boolean) so dope.
							return search ? '?' + search : '';
							});
			$.ajax({
				type: 'POST',
				url: "/wp-admin/admin-ajax.php",
				data: ({
					action : 'ajaxify',
					post_id: post_id,
					post_type: post_type
					}),
				success:function(response){
					if (post_type == "summary")	$("#post-"+post_id).html(response);
					twttr.widgets.load();
					$("#accordion").accordion("refresh");
					$("#accordion h3[aria-controls='post-"+post_id+"']").addClass("read");
					$("#post-"+post_id+" .loaded-post").addClass('true');
					// added sharedcount.com data to accordion foot
					$.sharedCount(post_url, function(data){
							$("#post-"+post_id+" span#tw-count").text(data.Twitter);
							$("#post-"+post_id+" span#fb-count").text(data.Facebook.like_count);
							$("#post-"+post_id+" span#gp-count").text(data.GooglePlusOne);
							$("#post-"+post_id+" span#li-count").text(data.LinkedIn);
							$("#post-"+post_id+" span#del-count").text(data.Delicious);
					});
					
					
				}
			});
		}
	});
});

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
function pop(title,url,optH,optW){ // script to handle social share popup
	h = optH || 500;
	w = optW || 680;
	sh = window.innerHeight || document.body.clientHeight;
	sw = window.innerWidth || document.body.clientWidth;
	wd = window.open(url, title,'scrollbars=no,menubar=no,height='+h+',width='+w+',resizable=yes,toolbar=no,location=no,status=no,top='+((sh/2)-(h/2))+',left='+((sw/2)-(w/2)));
}