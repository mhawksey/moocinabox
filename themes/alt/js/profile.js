// JavaScript Document
jQuery(document).ready(function($) {
	var instructions = "If you would like us to include your blog posts in the 'Reader' enter your blog address and <a id='searchForRSS' href='javascript:void(0)'>click here</a> and select the correct 'Blog RSS' from below. Any blog posts that you publish with octel in the post title or body will automatically be included in the Reader.";
	var blog_field = $('.field_blog input[type=text]');
	var newText = $('<legend id="bloginstruc" style="width:75%">'+instructions+'</legend>').insertAfter(blog_field);	
	$('#searchForRSS').on("click", function(event){
		$('.field_blog-rss input[type=text]').after("<img src='/wp-admin/images/loading.gif' id='loadingFeeds'/>");
		$.post(
			ajaxurl, 
			{
				'action': 'ajaxFeedSearch',
				'blog': blog_field.val(),
				'id': $('.field_blog-rss input[type=text]').attr('id')
			}, 
			function(response){
				$("#other_feed").remove();
				
				$('.field_blog-rss input[type=text]').replaceWith(response);
				$("#loadingFeeds").hide();
				// http://stackoverflow.com/a/5426112/1027723
				if ($('.field_blog-rss').find('select').children('option').length > 1){
					$("#other_feed").hide();
				}
				$('.field_blog-rss').find('select').change(function() {
				  if($(this).find('option:selected').val() == "Other"){
					$("#other_feed").show();
				  }else{
					$("#other_feed").hide();
				  }
				});
				$("#other_feed").keyup(function(ev){
					var othersOption = $('.field_blog-rss').find('option:selected');
					if(othersOption.val() == "Other")
					{
						ev.preventDefault();
						//change the selected drop down text
						$(othersOption).html($("#other_feed").val()); 
					} 
				});
				$('#bbp-your-profile').submit(function() {
					var othersOption = $('.field_blog-rss').find('option:selected');
					if(othersOption.val() == "Other")
					{
						// replace select value with text field value
						othersOption.val($("#other_feed").val());
					}
				});
			}
		);
	});
});