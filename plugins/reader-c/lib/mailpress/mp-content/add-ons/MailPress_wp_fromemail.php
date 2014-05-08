<?php
if (class_exists('MailPress') && !class_exists('MailPress_wp_fromemail'))
{
/*
Plugin Name: MailPress_wp_fromemail
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/wp_fromemail/
Description: New Mail : FROM email & name replaced by current user wp values
Version: 5.4
*/

class MailPress_wp_fromemail
{
	function __construct()
	{
		add_filter('MailPress_write_fromemail', 		array(__CLASS__, 'fromemail'), 1, 1);
		add_filter('MailPress_write_fromname', 		array(__CLASS__, 'fromname'), 1, 1);
	}

////  Admin  ////

	public static function fromemail($fromemail)
	{
		$user = wp_get_current_user();
		if ($user) return $user->user_email;
		return $fromemail;
	}

	public static function fromname($fromname)
	{
		$user = wp_get_current_user();
		if ($user) return $user->user_nicename;
		return $fromname;
	}
}
new MailPress_wp_fromemail();
}