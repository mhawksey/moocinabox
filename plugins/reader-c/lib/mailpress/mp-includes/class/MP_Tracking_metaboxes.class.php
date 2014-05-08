<?php
class MP_Tracking_metaboxes extends MP_options_
{
	var $path = 'tracking/metaboxes/';

	function __construct($type, $settings = false)
	{
		if ($settings === false)
		{
			$settings = get_option(MailPress_tracking::option_name);
			if (!is_array($settings)) $settings = array();
			$this->includes = $settings;
		}

		$this->path .= $type;
		parent::__construct();
	}

	function get_all($type)
	{
		return apply_filters('MailPress_tracking_metaboxes_register', array(), $type);
	}
}