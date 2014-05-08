<?php
if (class_exists('MailPress') && !class_exists('MailPress_Headers_specific'))
{
/*
Plugin Name: MailPress_Headers_specific
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/headers_specific/
Description: Mails : Adding specific headers in mail (sample)
Version: 5.4
*/

class MailPress_Headers_specific
{
	function __construct()
	{
// prepare mail
		add_filter('MailPress_swift_message_headers',  	array(__CLASS__, 'swift_message_headers'), 8, 2);
	}

// prepare mail
	public static function swift_message_headers($message, $row)
	{
		if ($row->template == 'new_subscriber') return $message;

		$url = apply_filters('MailPress_header_url', '{{unsubscribe}}', $row);
		if ('{{unsubscribe}}' == $url) $url = MP_User::get_unsubscribe_url( '{{_confkey}}' );

		if (isset($row->mp_user_id))
		{
			$confkey = MP_User::get_key_by_email(MP_User::get_email($row->mp_user_id));
			$url = str_replace('{{_confkey}}', $confkey, $url);
		}

		$headers = array(
					array(	'type' => Swift_Mime_Header::TYPE_TEXT , 
							'name' => 'List-Unsubscribe', 
							'value' => "<$url>"
					),
					array(	'type' => Swift_Mime_Header::TYPE_TEXT , 
							'name' => 'List-ID', 
							'value' => get_option( 'blogname' )
					)
				);


		$_headers = $message->getHeaders();
		foreach ($headers as $header)
		{
			switch ($header['type'])
			{
				case Swift_Mime_Header::TYPE_TEXT :
					$_headers->addTextHeader($header['name'], $header['value']);
			  	break;
				case Swift_Mime_Header::TYPE_PARAMETERIZED :
					$_headers->addParameterizedHeader($header['name'], $header['value'], $header['parms']);
			  	break;
				case Swift_Mime_Header::TYPE_MAILBOX :
					$_headers->addMailboxHeader($header['name'], $header['value']);
			  	break;
				case Swift_Mime_Header::TYPE_DATE :
					$_headers->addDateHeader($header['name'], $header['value']);
			  	break;
				case Swift_Mime_Header::TYPE_ID :
					$_headers->addIdHeader($header['name'], $header['value']);
			  	break;
				case Swift_Mime_Header::TYPE_PATH :
					$_headers->addPathHeader($header['name'], $header['value']);
			  	break;
			}
		}

		return $message;
	}
}
new MailPress_Headers_specific();
}