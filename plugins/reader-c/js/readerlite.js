// JavaScript Document
var _gaq = _gaq || [];
// AJAX Functions
var jq = jQuery.noConflict();

jq(document).ready(function($) {
	function doReader(){
	$( "#accordion" ).accordion({active: false, collapsible: true, heightStyle: "content"});
	customAccordionHooks();	
	$( "#accordion" ).show();
	$( "#accordionLoader" ).hide();
	
	// shared count getter https://gist.github.com/yahelc/1413508#file-jq-sharedcount-js
	
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
		_gaq.push(['_trackEvent', 'Course Reader', 'read', post_url]);
		if (!loaded_post.hasClass('true') && loaded_post.length > 0){ 

			// clean post url removing GA utm_ for shared count
			post_url = post_url.replace(/\?([^#]*)/, function(_, search) {
							search = search.split('&').map(function(v) {
							  return !/^utm_/.test(v) && v;
							}).filter(Boolean).join('&'); // omg filter(Boolean) so dope.
							return search ? '?' + search : '';
							});
			$.post(
				ajaxurl, 
					  {	
						action : 'ajaxify',
						post_id: post_id,
						post_type: post_type
					  },
				function(response){
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
					});
				}
			);
		}
	});
	}
	setTimeout(doReader, 5000);
});
function backup(){
		jq( "#accordion" ).accordion({active: false, collapsible: true, heightStyle: "content"});
	customAccordionHooks();	
	jq( "#accordion" ).show();
	jq( "#accordionLoader" ).hide();
}

function customAccordionHooks(){
	jq('.jump_to_url').on("click", function(event){
		event.stopImmediatePropagation();
		event.stopPropagation();
	});	
	jq('div.fav_widget').click( function(event) {
		var target = jq(event.target);

		/* Favoriting activity stream items */
		if ( target.hasClass('fav') || target.hasClass('unfav') ) {
			var type = target.hasClass('fav') ? 'fav' : 'unfav';
			var parent = target.closest('.fav_widget');
			var parent_id = parent.attr('act-id');

			target.addClass('loading');
			
			jq.post( ajaxurl, {
				action: 'activity_mark_' + type,
				'cookie': encodeURIComponent(document.cookie),
				'id': parent_id
			},
			function(response) {
				target.removeClass('loading');

				target.fadeOut( 100, function() {
					jq(this).html(response);
					jq(this).attr('title', 'fav' == type ? BP_DTheme.remove_fav : BP_DTheme.mark_as_fav);
					jq(this).fadeIn(100);
				});

				if ( 'fav' == type ) {
					target.removeClass('fav');
					target.addClass('unfav');
					var post_url = parent.parent().next().attr('url');
					_gaq.push(['_trackEvent', 'Course Reader', 'fav', post_url]);
				} else {
					target.removeClass('unfav');
					target.addClass('fav');
				}
			});

			return false;
		}
	});
	jq('.like, .unlike, .like_blogpost, .unlike_blogpost').on('click', function() {
		event.stopImmediatePropagation();
		event.stopPropagation();
		
		// Add the BuddyPress Like plugin code back in 
		// From http://wordpress.org/plugins/buddypress-like/
		// http://plugins.svn.wordpress.org/buddypress-like/tags/0.0.8/_inc/js/bp-like.dev.js
		var type = jq(this).attr('class');
		var id = jq(this).attr('id');
		
		jq(this).addClass('loading');
		
		jq.post( ajaxurl, {
			action: 'activity_like',
			'cookie': encodeURIComponent(document.cookie),
			'type': type,
			'id': id
		},
		function(data) {
			
			jq('#' + id).fadeOut( 100, function() {
				jq(this).html(data).removeClass('loading').fadeIn(100);
			});
			var post_url = jq('#'+id.match(/\d+/)+'.ajaxed').attr('url');
			// Swap from like to unlike
			if (type == 'like') {
				var newID = id.replace("like", "unlike");
				jq('#' + id).removeClass('like').addClass('unlike').attr('title', bp_like_terms_unlike_message).attr('id', newID);
			} else if (type == 'like_blogpost') {
				var newID = id.replace("like", "unlike");
				jq('#' + id).removeClass('like_blogpost').addClass('unlike_blogpost').attr('title', bp_like_terms_unlike_message).attr('id', newID);
				_gaq.push(['_trackEvent', 'Course Reader', 'like', post_url]);
			} else if (type == 'unlike_blogpost') {
				var newID = id.replace("unlike", "like");
				jq('#' + id).removeClass('unlike_blogpost').addClass('like_blogpost').attr('title', bp_like_terms_unlike_message).attr('id', newID);
			} else {
				var newID = id.replace("unlike", "like");
				jq('#' + id).removeClass('unlike').addClass('like').attr('title', bp_like_terms_like_message).attr('id', newID);
				_gaq.push(['_trackEvent', 'Course Reader', 'like', post_url]);
			}
			
			// Nobody else liked this, so remove the 'View Likes'
			if (data == bp_like_terms_like) {
				var pureID = id.replace("unlike-activity-", "");
				jq('.view-likes#view-likes-'+ pureID).remove();
				jq('.users-who-like#users-who-like-'+ pureID).remove();
			}
			
			// Show the 'View Likes' if user is first to like
			if ( data == bp_like_terms_unlike_1 ) {
				var pureID = id.replace("like-activity-", "");
				jq('li#activity-'+ pureID + ' .activity-meta').append('<a href="" class="view-likes" id="view-likes-' + pureID + '">' + bp_like_terms_view_likes + '</a><p class="users-who-like" id="users-who-like-' + pureID + '"></p>');
			}
			
		});
		
		return false;
	});
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

jq.sharedCount = function(url, fn) {
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
	return jq.ajax(arg);
};
function pop(title,url,optH,optW){ // script to handle social share popup
	h = optH || 500;
	w = optW || 680;
	sh = window.innerHeight || document.body.clientHeight;
	sw = window.innerWidth || document.body.clientWidth;
	wd = window.open(url, title,'scrollbars=no,menubar=no,height='+h+',width='+w+',resizable=yes,toolbar=no,location=no,status=no,top='+((sh/2)-(h/2))+',left='+((sw/2)-(w/2)));
}