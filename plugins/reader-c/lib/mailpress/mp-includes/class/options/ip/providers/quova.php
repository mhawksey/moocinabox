<?php
if ( defined('MP_Ip_quova_ApiKey') && defined('MP_Ip_quova_Secret') )
{
class MP_Ip_quova extends MP_ip_provider_
{
	var $id 	= 'quova';
	var $url	= 'http://api.quova.com/v1/ipinfo/%1$s?apikey=%2$s&sig=%3$s&format=xml';
	var $credit= 'http://www.quova.com/';
	var $type 	= 'xml';

	function content($valid, $content)
	{
		if (strpos($content, '<gds_error>')) return false;
		return $valid;
	}

	function url($arg)
	{
		$arg[] = MP_Ip_quova_ApiKey;
		$arg[] = md5(MP_Ip_quova_ApiKey . MP_Ip_quova_Secret . gmdate('U'));
		return $arg;
	}

	function data($content, $ip)
	{
		$html = '';

		$xml = $this->xml2array( $content );
		if (!isset($xml['Location'])) return false;
		$xml = $xml['Location'];


		$latitude 		= $xml['latitude'];
		$html .= "<p style='margin:3px;'><b>latitude</b> : $latitude</p>";

		$longitude 	= $xml['longitude'];
		$html .= "<p style='margin:3px;'><b>longitude</b> : $longitude</p>";

		$country 		= ucwords($xml['CountryData']['country']);
		$html .= "<p style='margin:3px;'><b>country</b> : $country</p>";

		$country_code 	= strtoupper($xml['CountryData']['country_code']);
		if ('US' == $country_code) 
		{
			$state_code 	= (!empty($xml['StateData']['state_code'])) ? strtoupper($xml['StateData']['state_code']) : '';
			$html .= "<p style='margin:3px;'><b>state_code</b> : $state_code</p>";
		}

		$city 		= ucwords($xml['CityData']['city']);
		$html .= "<p style='margin:3px;'><b>city</b> : $city</p>";

		$postal_code 	= $xml['CityData']['postal_code'];
		$html .= "<p style='margin:3px;'><b>postal_code</b> : $postal_code</p>";

		$geo = (isset($latitude) && isset($longitude)) ? array('lat' => $latitude, 'lng' => $longitude) : array();
		return $this->cache_custom($ip, $geo, substr($country_code, 0, 2), $state_code, $html);
	}
}
new MP_Ip_quova();
}