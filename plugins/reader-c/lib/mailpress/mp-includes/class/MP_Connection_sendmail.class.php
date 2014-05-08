<?php
class MP_Connection_sendmail extends MP_connection_
{
	public $Swift_Connection_type = 'SENDMAIL';

	function connect($x, $y)
	{
		$settings = get_option(MailPress_connection_sendmail::option_name);

		switch ($settings['cmd'])
		{
			case 'custom' :
				$conn = Swift_SendmailTransport::newInstance($settings['custom']);
			break;
			default :
				$conn = Swift_SendmailTransport::newInstance();
			break;
		}
		return $conn;
	}
}