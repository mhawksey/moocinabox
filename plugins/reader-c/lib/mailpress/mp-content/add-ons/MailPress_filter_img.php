<?php
if (class_exists('MailPress') && !class_exists('MailPress_filter_img') )
{
/*
Plugin Name: MailPress_filter_img
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/filter_img/
Description: Mails : filter ALL html img tags before mailing them
Version: 5.4
*/

class MailPress_filter_img
{
	const option_name = 'MailPress_filter_img';

	function __construct()
	{
// prepare mail
		add_filter('MailPress_img_mail_keepurl', 		array(__CLASS__, 'img_mail_keepurl'), 8, 1);
		add_filter('MailPress_img_mail', 			array(__CLASS__, 'img_mail'), 8, 1);

		if (is_admin())
		{
		// for link on plugin page
			add_filter('plugin_action_links', 		array(__CLASS__, 'plugin_action_links'), 10, 2 );
		// for settings
			add_filter('MailPress_styles', 		array(__CLASS__, 'styles'), 8, 2);
			add_filter('MailPress_settings_tab', 	array(__CLASS__, 'settings_tab'), 50, 1);
		}
	}

// prepare mail
	public static function img_mail_keepurl($bool)
	{
		$option = get_option(MailPress_filter_img::option_name);

		if (isset($option['keepurl'])) return true;
		return $bool;
	}

	public static function img_mail($img)
	{
		$wstyle 	= $inline_style = $default_style = array();
		$wattr 	= $inline_attr  = $default_attr  = array();

		$option	= get_option(MailPress_filter_img::option_name);

		if (isset($option['align']))
		{
			if ('center' == $option['align']) 	$default_style['text-align'] = 'center';
			elseif ('none' != $option['align']) $default_style['float']      = $option['align'];
		}

		if (!empty($option['extra_style']))
		{
		 	$x = self::retrieve_styles(stripslashes($option['extra_style']));
			foreach($x as $k => $v) $default_style[$k] = $v;
		}

		$x = self::retrieve_attributes($img);
		foreach($x as $k => $v)
		{
			switch ($k)
			{
				case 'style' :
					$inline_style = self::retrieve_styles($v);
				break;
				case 'class' :
					$inline_attr[$k] = $v;
					if (false !== stripos($v, 'left'))  $wstyle['float'] = 'left';
					if (false !== stripos($v, 'right')) $wstyle['float'] = 'right';
				break;
				default :
					$inline_attr[$k] = $v;
				break;				
			}
		}

		$inline_attr  = array_merge($wattr , $default_attr , $inline_attr );
		$inline_style = array_merge($wstyle, $default_style, $inline_style);

// format style
		$wstyle = '';
		$quote = '"';
		foreach ($inline_style as $k => $v)
		{
			if (false !== strpos($v, '"')) $quote = "'";
			if ($v != '') $wstyle .= $k . ':' . $v . ';';
		}

		$wimg = '<img';
// format attributes
		foreach ($inline_attr as $k => $v) if ($v != '') $wimg .= ' ' . $k . '="' . $v . '"';
		if ($wstyle != '') $wimg .= ' style=' . $quote . $wstyle . $quote;
		$wimg .= ' />';
		$wimg = "<!-- MailPress_filter_img start -->\n" . $wimg .  "\n<!-- MailPress_filter_img end -->" ;

		return $wimg;
	}

	public static function retrieve_attributes($img)
	{
		if (empty($img)) return array();

		$w = str_ireplace('<img ', '', $img);
		$w = str_ireplace('/>', '', $w);
		$w = trim($w);
		do {$w = str_ireplace('  ', ' ', $w, $count);} while ($count>0);
		do {$w = str_ireplace(' =', '=', $w, $count);} while ($count>0);
		do {$w = str_ireplace('= ', '=', $w, $count);} while ($count>0);

		if ('' == $w) return array();

		do
		{
			$att 		= strpos($w, '=');
			$key   	= substr($w, 0, $att);
			$quote 	= substr($w, $att+1, 1);
			if ("'" != $quote) if ('"' != $quote) $quote=false;
			$start 	= ($quote) ? 1 : 0;
			$end 		= ($quote) ? strpos($w, $quote, $att+1+$start) : strpos($w, ' ') ;
			$val 		= substr($w, $att+1+$start, $end-($att+1+$start));

			$x[trim($key)]=trim($val);

			$w = trim(substr($w, $end+1));
		} while ('' != $w);

		return $x;
	}

	public static function retrieve_styles($style)
	{
		if (empty($style)) return array();

		$w = explode(';', $style);
		foreach ($w as $v)
		{
			if ($v)
			{
				$zs = explode(':', $v);
				$x[trim($zs[0])] = trim($zs[1]);
			}
		}

		return $x;
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'filter_img');
	}

// for settings
	public static function styles($styles, $screen) 
	{
		if ($screen != MailPress_page_settings) return $styles;

		wp_register_style ( 'mp-filter-img', '/' . MP_PATH . 'mp-admin/css/settings_filter_img.css' );

		$styles[] = 'mp-filter-img';

		return $styles;
	}

	public static function settings_tab($tabs)
	{
		$tabs['filter_img'] = __('Image filter', MP_TXTDOM);
		return $tabs;
	}
}
new MailPress_filter_img();
}