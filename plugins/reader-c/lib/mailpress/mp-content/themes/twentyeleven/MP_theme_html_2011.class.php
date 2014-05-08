<?php
class MP_theme_html_2011 extends MP_theme_html_
{
	const HEADER_IMAGE_WIDTH = 760;
	const HEADER_IMAGE_HEIGHT = 219;

	public static $_comments_1st = false;

	var $style = 'style="border:0 none;font-family:inherit;font-size:100%;font-style:inherit;font-weight:inherit;margin:0;padding:0;vertical-align:baseline;color:#1982D1;text-decoration:none;font-weight:bold;"';

	function build_mail_content_start($type)
	{
		if ('html' != $type) return;

		add_filter( 'wp_nav_menu', 				array($this, 'wp_nav_menu'), 8, 2 );
		add_filter( 'wp_page_menu', 				array($this, 'wp_nav_menu'), 8, 2 );

		parent::build_mail_content_start($type);
	}

	function build_mail_content_end($type)
	{
		if ('html' != $type) return;

		remove_filter( 'wp_nav_menu', 				array($this, 'wp_nav_menu'));
		remove_filter( 'wp_page_menu', 				array($this, 'wp_nav_menu'));

		parent::build_mail_content_end($type);
	}

	function wp_nav_menu($menu, $args)
	{
		$searched = array('<ul>',
					'<li class="current_page_item">',
					'<li class="page_item page-item-',
					'<a',
		);
		$replace  = array('<ul style="font-size:13px;list-style:none outside none;margin:0 7.6%;padding-left:0;">',
					'<li style="float:left;font-weight: bold;">',
					'<li style="float:left;" class="page_item page-item-',
					'<a style="color:#EEE;display:block;line-height:3.333em;padding:0 1.2125em;text-decoration:none;" ',
		);
		return str_ireplace($searched, $replace, $menu);
	}

	function comments_popup_link_attributes($attrs = '')
	{
		self::$_comments_1st = !self::$_comments_1st;
		if (self::$_comments_1st)
		{
			$url = site_url() . "/wp-content/themes/twentyeleven/images/comment-bubble.png";
			return $attrs . ' style="background: url(\'' . $url . '\') no-repeat scroll 0 0 #EEEEEE;color:#666666;font-size:13px;font-weight:normal;height:36px;line-height:35px;overflow:hidden;padding:0;position:absolute;right:0;text-align:center;text-decoration:none;top:1.5em;width:43px;" ';
		}
		return "$attrs {$this->style} ";
	}
}
new MP_theme_html_2011();