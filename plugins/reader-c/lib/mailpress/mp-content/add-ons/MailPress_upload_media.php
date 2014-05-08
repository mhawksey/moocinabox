<?php
if (class_exists('MailPress') && !class_exists('MailPress_upload_media') && (is_admin()))
{
/*
Plugin Name: MailPress_upload_media
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/upload_media/
Description: New Mail : add upload media button
Version: 5.4
*/

class MailPress_upload_media
{
	function __construct()
	{
		add_filter('MailPress_upload_media', 	array(__CLASS__, 'upload_media'), 8, 1);
	}

	public static function upload_media($x)
	{
		return true;
	}
}
new MailPress_upload_media();
}