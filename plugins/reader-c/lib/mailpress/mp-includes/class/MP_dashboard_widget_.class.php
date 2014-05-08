<?php
abstract class MP_dashboard_widget_
{
	var $url = 'http://chart.apis.google.com/chart';

	function __construct($name)
	{
		wp_add_dashboard_widget( $this->id, $name, array($this, 'widget'), (method_exists($this, 'control')) ? array($this, 'control') : null );
	}

	function widget_size($size) 
	{
		$screen_layout_columns = $this->get_screen_layout_columns();
		if ($screen_layout_columns <= 2) return $size;

		$x = ($screen_layout_columns == 3) ? 0.65 : 0.45;
		$wh = explode('x', $size);
		return intval($wh[0] * $x) . 'x' . intval($wh[1] * $x);
	}

	function bar_size($size) 
	{
		$screen_layout_columns = $this->get_screen_layout_columns();
		if ($screen_layout_columns <= 2) return $size;

		$x = ($screen_layout_columns == 3) ? 0.65 : 0.45;
		return intval($size * $x);
	}

	function get_screen_layout_columns() 
	{
		$screen_layout_columns = get_user_option('screen_layout_dashboard');
		if (!$screen_layout_columns) $screen_layout_columns = 2;
		return $screen_layout_columns;
	}
}