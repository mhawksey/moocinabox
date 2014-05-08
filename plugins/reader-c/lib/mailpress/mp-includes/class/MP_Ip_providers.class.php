<?php
class MP_Ip_providers extends MP_options_
{
	var $path = 'ip/providers';

	public static function get_all()
	{
		$providers[MP_Ip::provider] = array('id' => MP_Ip::provider, 'url' => '%1$s', 'type' => 'xml', 'md5' => false);
		return apply_filters('MailPress_ip_providers_register', $providers);
	}
}