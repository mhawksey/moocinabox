<?php
abstract class MP_tracking_useragent_ extends MP_tracking_xml_
{
	public $folder = 'useragents';

	function __construct($title)
	{
		add_filter("MailPress_tracking_{$this->folder}_{$this->id}_get_info",		array($this, 'get_info'),	8, 1);
		parent::__construct($title);
	}

	function get_info($useragent)
	{
		$ug = $this->get($useragent);

		$txt = '';
		if (isset($ug->icon_path)) $txt .= "<img src='" . $ug->icon_path . "' alt='" . esc_attr($ug->string) . "' />";
		if (isset($ug->link))
			$txt .= "&#160;<a href='" . $ug->link . "' title='" . $ug->full_name . "' >" . $ug->name . '</a>';
		else
			$txt .= '&#160;' . $ug->full_name;
		return trim($txt);
	}
}