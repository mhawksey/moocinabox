<?php
if (class_exists('MailPress') && class_exists('MailPress_tracking') && !class_exists('MailPress_tracking_rewrite_url'))
{
/*
Plugin Name: MailPress_tracking_rewrite_url
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/tracking_rewrite_url/ 
Description: Tracking : rewrite urls  (<span style='color:red;'>required !</span> <span style='color:#D54E21;'>Tracking</span> add-on + modified <span style='color:#D54E21;'>.htaccess</span> (sample in mp-content/xtras/mp_tracking_rewrite_url))
Version: 5.4
*/

class MailPress_tracking_rewrite_url
{
	function __construct()
	{
		add_filter('MailPress_tracking_url',	array(__CLASS__, 'url'), 1, 1);
	}

	public static function url($url)
	{
		$new_url = '';
		$args = self::get_args($url);

		foreach (array('home' => MP_Action_home, 'siteurl' => MP_Action_url) as $k => $v)
			if (strpos($url, $v) === 0) $new_url = get_option($k) . '/mail/analytics/';

		if (!empty($args))
		{
			if (empty($new_url)) $new_url .= substr($url, 0, strpos($url, '?'));
			$new_url .= sprintf('%1$s-%2$s-%3$s-%4$s.html', $args['tg'] ,$args['mm'] ,$args['co'] ,$args['us'] );
		}

		return $new_url;
	}

	public static function get_args($url)
	{
		$w = parse_url($url);
		if (!isset($w['query'])) return false;

		$args = $w['query'];
		$w = explode('&', $args);

		foreach($w as $x)
		{
			$y = explode('=', $x);
			$z[$y[0]] = $y[1];
		}
		return $z;
	}
}
new MailPress_tracking_rewrite_url();
}