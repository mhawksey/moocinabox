<?php
abstract class MP_theme_html_template_
{
	public static function who_is($ip)
	{
		$x  = MP_Ip::get_all($ip);

		if (!$x['geo']['lat'] && !$x['geo']['lng']) return array('src' => false, 'addr' => false);

		$width  = 300;
		$height = 300;
		$src  = 'http://maps.googleapis.com/maps/api/staticmap?';
		$src .= 'center=' . $x['geo']['lat'] . ',' . $x['geo']['lng'];
		$src .= '&zoom=4';
		$src .= "&size=$width" . 'x' . $height;
		$src .= '&maptype=roadmap'; 
		$src .= '&markers=' . $x['geo']['lat'] . ',' . $x['geo']['lng'];
		$src .= '&sensor=false';

		$addr = MP_Ip::get_address($x['geo']['lat'], $x['geo']['lng']);

		return array('src' => $src, 'addr' => $addr[0]);
	}
}