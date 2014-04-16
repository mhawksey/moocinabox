jQuery(document).ready(function($) {

	$('#cn_position, #cn_hide_effect, #cn_see_more, #cn_css_style, #cn_see_more_opt_custom_link').buttonset();
	$('.cn-color').wpColorPicker();

	$('#cn-see-more-yes, #cn-see-more-no').change(function() {
		if($('#cn-see-more-yes:checked').val() === 'yes') {
			$('#cn_see_more_opt').fadeIn(300);
		} else if($('#cn-see-more-no:checked').val() === 'no') {
			$('#cn_see_more_opt').fadeOut(300);
		}
	});

	$('#cn-see-more-link-custom, #cn-see-more-link-page').change(function() {
		if($('#cn-see-more-link-custom:checked').val() === 'custom') {
			$('#cn_see_more_opt_page').fadeOut(300, function() {
				$('#cn_see_more_opt_link').fadeIn(300);
			});
		} else if($('#cn-see-more-link-page:checked').val() === 'page') {
			$('#cn_see_more_opt_link').fadeOut(300, function() {
				$('#cn_see_more_opt_page').fadeIn(300);
			});
		}
	});
});