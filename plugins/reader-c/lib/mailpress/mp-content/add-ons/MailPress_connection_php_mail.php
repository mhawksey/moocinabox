<?php
if (class_exists('MailPress') && !class_exists('MailPress_connection_php_mail') )
{
/*
Plugin Name: MailPress_connection_php_mail 
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/connection_php_mail/
Description: Connection : use native php mail
Version: 5.4
*/

class MailPress_connection_php_mail
{
	const option_name = 'MailPress_connection_phpmail';

	function __construct()
	{
		new MP_Connection_php_mail();

// for wp admin
		if (is_admin())
		{
		// for link on plugin page
			add_filter('plugin_action_links', 			array(__CLASS__, 'plugin_action_links'), 10, 2 );
		}

	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'connection_php_mail');
	}
}
new MailPress_connection_php_mail();
}