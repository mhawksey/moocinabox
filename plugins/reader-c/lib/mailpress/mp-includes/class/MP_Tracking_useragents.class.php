<?php
class MP_Tracking_useragents extends MP_options_
{
	var $path = 'tracking/useragents';

	public static function get_all()
	{
		return apply_filters('MailPress_tracking_useragents_register', array());
	}
}