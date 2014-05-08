<?php
if ( defined('MP_Ip_ipinfodb_ApiKey') )
{
class MP_Ip_ipinfodb extends MP_ip_provider_
{
	var $id 	= 'ipinfodb';
	var $url	= 'http://api.ipinfodb.com/v2/ip_query.php?ip=%1$s&key=%2$s&timezone=true';
	var $credit	= 'http://ipinfodb.com/';
	var $type 	= 'xml';

	function content($valid, $content)
	{
		if (!strpos($content, '<Status>OK</Status>')) return false;
		if (strpos($content, '<Latitude>0</Latitude>') && strpos($content, '<Longitude>0</Longitude>')) return false;
		return $valid;
	}

	function url($arg)
	{
		$arg[] = MP_Ip_ipinfodb_ApiKey;
		return $arg;
	}

	function data($content, $ip)
	{
		$skip = array('Status', 'RegionCode', 'Gmtoffset', 'ZipPostalCode', 'Dstoffset', 'Isdst');
		$html = '';

		$xml = $this->xml2array( $content );
		foreach ($xml as $k => $v)
		{
			if ($v == 'n/a') continue;
			if (empty($v))   continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('CountryCode', 'Latitude', 'Longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}
		$geo = (isset($Latitude) && isset($Longitude)) ? array('lat' => $Latitude, 'lng' => $Longitude) : array();
		$country = (isset($CountryCode)) ? $CountryCode : '';
		$subcountry = ('US' == strtoupper($country)) ? MP_Ip::get_USstate($ip) : MP_Ip::no_state;
		return $this->cache_custom($ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}
}
new MP_Ip_ipinfodb();
}