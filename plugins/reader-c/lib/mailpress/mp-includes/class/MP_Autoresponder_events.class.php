<?php
class MP_Autoresponder_events extends MP_options_
{
	var $path = 'autoresponder/events';

	public static function get_all()
	{
		return apply_filters('MailPress_autoresponder_events_register', array());
	}
}