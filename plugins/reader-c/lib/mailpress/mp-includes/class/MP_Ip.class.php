<?php
class MP_Ip
{
	const cache_days 	= 120;			// keep it 120 days
	const provider 	= 'mailpress';
	const no_state	= 'ZZ';

	public static function get_all($ip)
	{
		return self::get_ip_info($ip);
	}

	public static function get_latlng($ip)
	{
		$content = self::get_ip_info($ip);
		if (!$content) return false;
		if (!isset($content['geo']['lat'])) return false;
		return $content['geo'];
	}

	public static function get_country($ip)
	{
		$content = self::get_ip_info($ip);
		if ( (!$content) || (!isset($content['country'])) ||(2 < strlen($content['country'])) ) return self::no_state;
		return $content['country'];
	}

	public static function get_subcountry($ip)
	{
		$content = self::get_ip_info($ip);
		if ( (!$content) || (!isset($content['subcountry'])) || empty($content['subcountry']) ) return self::no_state;
		return $content['subcountry'];
	}

	public static function get_USstate($ip)
	{
		$response = wp_remote_get("http://api.hostip.info/get_html.php?ip=$ip");
		$x = (is_wp_error($response)) ? false : $response['body'];
		if (!$x || empty($x) || (2 < strlen($x))) return self::no_state;

		$USstates = array('AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY');
		$USstate = substr($x, strlen($x)-2, 2);
		return (in_array(substr($USstate, 0, 2), $USstates)) ? substr($USstate, 0, 2) : self::no_state;
	}

	public static function get_ip_info($ip)
	{
		new MP_Ip_providers();
		$providers = MP_Ip_providers::get_all();
		foreach($providers as $provider => $pdata)
		{
			list($file, $ip_url, $cache) = self::get_context($pdata, $ip);
			if ($file == $cache) break;
		}

		switch ($file)
		{
			case $cache :
				$content = @file_get_contents($file);
				$valid   = (!empty($content) && $content);
			break;
			default :
				unset($providers[self::provider]);
				do
				{
					$provider	= array_rand($providers);
					$pdata	= $providers[$provider];
					list($file, $ip_url, $cache) = self::get_context($pdata, $ip);

					$response 	= wp_remote_get($ip_url);
					$content 	= (is_wp_error($response)) ? false : $response['body'];
					$valid 	= (!empty($content) && $content);

					if ($valid)
					{
						switch($pdata['type'])
						{
							case 'xml' :
								if (!simplexml_load_string($content)) 	$valid = false;
							break;
							case 'array' :
								if (!is_serialized($content)) 		$valid = false;
							break;
						}
						if ($valid) $valid = apply_filters('MailPress_ip_content_' . $provider, $valid, $content);	
					}
					if (!$valid) unset($providers[$provider]);
					if (empty($providers)) break;
				} while (!$valid);

				if ($valid) file_put_contents($cache, $content);
			break;
		}

		if (!$valid) return false;

		switch($provider)
		{
			case self::provider :
				return (is_serialized($content)) ? unserialize($content) : false;
			break;
			default :
				return apply_filters('MailPress_ip_data_' . $provider, $content, $ip);
			break;
		}
	}

	public static function get_context($pdata, $ip)
	{
		$file  = $ip_url = vsprintf( $pdata['url'], apply_filters('MailPress_ip_url_' . $pdata['id'] , array($ip)) );
		$cache = MP_ABSPATH . 'tmp/' . ((!isset($pdata['md5'])) ?  md5($ip_url) : $ip_url) . '.spc';

		if (is_file($cache))
		{
			$cache_days = ( (isset($pdata['cache_days'])) ? $pdata['cache_days'] : self::cache_days )*24*60*60;
			if (filemtime($cache) >= (time() - $cache_days))	$file = $cache;
			else									@unlink($cache);
		}
		return array($file, $ip_url, $cache);
	}

	public static function get_address($lat, $lng)
	{
		$url = "http://maps.googleapis.com/maps/api/geocode/xml?latlng=$lat,$lng&sensor=false";

		$xml = file_get_contents($url);
		if ( !$xml || empty($xml) ) return '';

		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		if (!$xml) return '';
		if ('OK' != $xml->status) return '';
		return (isset($xml->result[0]->formatted_address)) ? $xml->result[0]->formatted_address : '';
	}
}