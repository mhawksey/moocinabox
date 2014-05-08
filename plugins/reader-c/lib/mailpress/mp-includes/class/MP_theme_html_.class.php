<?php
abstract class MP_theme_html_
{
	var $style = '';

	function __construct()
	{ 
		add_action('MailPress_build_mail_content_start',	array($this, 'build_mail_content_start'));
		add_action('MailPress_build_mail_content_end',		array($this, 'build_mail_content_end'));
	}

	function build_mail_content_start($type)
	{
		if ('html' != $type) return;

		add_action('MailPress_theme_html_header_image',		array($this, 'header_image'), 8, 2);

		add_filter( 'comments_popup_link_attributes', 		array($this, 'comments_popup_link_attributes'), 8, 1 );
		add_filter( 'the_category', 					array($this, 'the_category'), 8, 3 );
		add_filter( 'term_links-post_tag', 				array($this, 'term_links_post_tag'), 8, 1 );
	}

	function build_mail_content_end($type)
	{
		if ('html' != $type) return;

		remove_filter('MailPress_theme_html_header_image',	array($this, 'header_image'), 8, 2);

		remove_filter( 'comments_popup_link_attributes',	array($this, 'comments_popup_link_attributes') );
		remove_filter( 'the_category', 					array($this, 'the_category') );
		remove_filter( 'term_links-post_tag', 			array($this, 'term_links_post_tag') );
	}

	function header_image($default, $post_id = false)
	{
		switch (true)
		{
			case ( $post_id && function_exists('has_post_thumbnail') && function_exists('get_post_thumbnail_id') && function_exists('wp_get_attachment_image_src') && $post_id && has_post_thumbnail( $post_id ) && ($image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'post-thumbnail')) && ($image[1] >= self::HEADER_IMAGE_WIDTH) ) :
				echo $image[0];
			break;
			case ( function_exists('get_header_image') && get_header_image() ) :
				echo get_header_image();
			break;
			default:
				echo $default;
			break;
		}
	}

	function comments_popup_link_attributes($attrs = '')
	{
		return "$attrs {$this->style} ";
	}

	function the_category($thelist, $separator, $parents)
	{
		return str_replace(array('a href=', 'rel="category"'), array("a class=\"hover_underline\" {$this->style} href=", ''), $thelist );
	}

	function term_links_post_tag($term_links)
	{
		foreach($term_links as $k => $v)
			$term_links[$k] = str_replace('a href=', "a class=\"hover_underline\" {$this->style} href=", $v );
		return $term_links;
	}
}