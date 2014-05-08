<?php
if (class_exists('MailPress') && !class_exists('MailPress_write_edit_fromemail'))
{
/*
Plugin Name: MailPress_write_edit_fromemail
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/write_edit_fromemail/
Description: New Mail : make FROM email and name editable (<span style='color:red;'>info !</span> use <span style='color:#D54E21;'>Roles_and_capabilities</span> add-on for fine tuning new capability 'MailPress_write_edit_fromemail')
Version: 5.4
*/

class MailPress_write_edit_fromemail
{
	function __construct()
	{
	// for role & capabilities
		add_filter('MailPress_capabilities', 		array(__CLASS__, 'capabilities'), 1, 1);
	}

////  Admin  ////

// for role & capabilities
	public static function capabilities($capabilities)
	{
		$capabilities['MailPress_write_edit_fromemail'] = array(	'name'	=> __('Edit fromemail', MP_TXTDOM),
										'group'	=> 'mails'
		);
		return $capabilities;
	}
}
new MailPress_write_edit_fromemail();
}