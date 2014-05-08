<?php
class MP_Ip_freegeoip extends MP_ip_provider_
{
	var $id 	= 'freegeoip';
	var $url	= 'http://freegeoip.net/xml/%1$s';
	var $credit= 'http://freegeoip.net';
	var $type 	= 'xml';

	function content($valid, $content)
	{
		if (strpos($content, '<CountryName>Reserved</CountryName>')) return false;
		return $valid;
	}

	function data($content, $ip)
	{
		$html = '';

		$xml = $this->xml2array( $content );
		if (!isset($xml['Response'])) return false;
		$xml = $xml['Response'];

		$latitude 		= $xml['Latitude'];
		$html .= "<p style='margin:3px;'><b>latitude</b> : $latitude</p>";

		$longitude 	= $xml['Longitude'];
		$html .= "<p style='margin:3px;'><b>longitude</b> : $longitude</p>";

		$country 		= ucwords($xml['CountryName']);
		$html .= "<p style='margin:3px;'><b>country</b> : $country</p>";

		$country_code 	= strtoupper($xml['CountryCode']);
		if ('US' == $country_code) 
		{
			$state_code 	= (!empty($xml['RegionCode'])) ? strtoupper($xml['RegionCode']) : '';
			$html .= "<p style='margin:3px;'><b>state_code</b> : $state_code</p>";
		}

		$city 		= ucwords($xml['City']);
		$html .= "<p style='margin:3px;'><b>city</b> : $city</p>";

		$postal_code 	= $xml['ZipCode'];
		$html .= "<p style='margin:3px;'><b>postal_code</b> : $postal_code</p>";

		$geo = (isset($latitude) && isset($longitude)) ? array('lat' => $latitude, 'lng' => $longitude) : array();
		return $this->cache_custom($ip, $geo, substr($country_code, 0, 2), $state_code, $html);
	}
}
new MP_Ip_freegeoip();