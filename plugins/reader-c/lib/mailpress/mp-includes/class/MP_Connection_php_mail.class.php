<?php
class MP_Connection_php_mail extends MP_connection_
{
	public $Swift_Connection_type = 'PHP_MAIL';

	function connect($x)
	{
		$settings = get_option(MailPress_connection_php_mail::option_name);

		$addparm = $settings['addparm'];

		$conn = (empty($addparm)) ? Swift_MailTransport::newInstance() : Swift_MailTransport::newInstance($addparm);

		return $conn;
	}
}