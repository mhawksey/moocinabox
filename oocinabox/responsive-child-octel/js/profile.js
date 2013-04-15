// JavaScript Document
jQuery(document).ready(function($) {
	var instructions = "If you would like us to include your blog posts in the 'Course Reader' enter your blog address in the 'Blog RSS Feed' field below.";
	instructions += " If you don't know what this is enter your blog address in the 'Blog' field above and <a id='searchForRSS' href='javascript:void(0)'>click here</a> and we'll try and detect it";
	var newText = $('<legend id="bloginstruc">'+instructions+'</legend>').insertAfter('#blog');	
	newText.css("display","block");
	$('<div id="gravHelp"><a href="http://en.gravatar.com/" target="_blank">Visit Gravatar</a> to modify avatar image</div>').insertAfter('#bbp-user-avatar');
	$('img.avatar').css("margin-bottom","3px");
	$('#searchForRSS').on("click", function(event){
		$("#blogrss").after("<img src='/wp-admin/images/loading.gif' id='loadingFeeds'/>");
		$.ajax({
			type: 'POST',
			url: "/wp-admin/admin-ajax.php",
			data: ({
				action : 'ajaxFeedSearch',
				blog: $('#blog').val()
				}),
			success:function(response){
				$("#other_feed").remove();
				$("#blogrss").replaceWith(response);
				$("#loadingFeeds").hide();
				// http://stackoverflow.com/a/5426112/1027723
				if ($('#blogrss').children('option').length > 1){
					$("#other_feed").hide();
				}
				$('#blogrss').change(function() {
				  if($(this).find('option:selected').val() == "Other"){
					$("#other_feed").show();
				  }else{
					$("#other_feed").hide();
				  }
				});
				$("#other_feed").keyup(function(ev){
					var othersOption = $('#blogrss').find('option:selected');
					if(othersOption.val() == "Other")
					{
						ev.preventDefault();
						//change the selected drop down text
						$(othersOption).html($("#other_feed").val()); 
					} 
				});
				$('#bbp-your-profile').submit(function() {
					var othersOption = $('#blogrss').find('option:selected');
					if(othersOption.val() == "Other")
					{
						// replace select value with text field value
						othersOption.val($("#other_feed").val());
					}
				});			
			}
		});
	});
});