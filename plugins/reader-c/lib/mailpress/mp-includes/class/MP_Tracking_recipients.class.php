<?php
class MP_Tracking_recipients extends MP_options_
{
	var $path = 'tracking/recipients';

	public static function get_all()
	{
		return apply_filters('MailPress_tracking_recipients_register', array());
	}
}