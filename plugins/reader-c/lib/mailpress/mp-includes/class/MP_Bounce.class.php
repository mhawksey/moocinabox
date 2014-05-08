<?php
class MP_Bounce extends MP_bounce_
{
	public $option_name 	= MailPress_bounce_handling::option_name;
	public $option_name_pop3= MailPress_bounce_handling::option_name_pop3;
	public $meta_key 		= MailPress_bounce_handling::meta_key;

	public $class		= __CLASS__;
	public $log_name 		= 'mp_process_bounce_handling';
	public $log_option_name = 'bounce_handling';
	public $log_title 	= 'Bounce Handling Report (Bounce in mailbox : %1$s )';

	public $cron_name 	= 'MailPress_schedule_bounce_handling';

	function is_bounce()
	{
		$tags = array('Return-Path', 'Return-path', 'Received', 'To', 'X-Failed-Recipients', 'Final-Recipient');
		$this->pop3->get_headers_deep($this->message_id, $tags);

		$prefix 	= preg_quote(substr($this->config['Return-Path'], 0, strpos($this->config['Return-Path'], '@')) . '+');
		$domain 	= preg_quote(substr($this->config['Return-Path'], strpos($this->config['Return-Path'], '@') + 1 ));
		$user_mask	= preg_quote('{{_user_id}}');

		foreach($this->pop3->headers as $tag => $headers)
		{
			foreach($headers as $header)
			{
				if (strpos($header, $this->config['Return-Path']) !== false) continue;

				switch (true)
				{
					case (preg_match("#{$prefix}[0-9]*\+[0-9]*@{$domain}#", $header)) :
						preg_match_all("/{$prefix}([0-9]*)\+([0-9]*)@{$domain}/", $header, $matches, PREG_SET_ORDER);
						if (empty($matches)) continue 2;
						$bounce_email	= $matches[0][0];
						$mail_id		= $matches[0][1];
						$mp_user_id		= $matches[0][2];
					break;
					case (preg_match("#{$prefix}[0-9]*\+$user_mask@{$domain}#", $header)) :
						preg_match_all("/$prefix([0-9]*)\+$user_mask@$domain/", $header, $matches, PREG_SET_ORDER);
						if (empty($matches)) continue 2;
						$bounce_email	= $matches[0][0];
						$mail_id		= $matches[0][1];
						if (!$mail = MP_Mail::get($mail_id)) continue 2;
						if (!is_email($mail->toemail))        continue 2;
						$mp_user_id 	= MP_User::get_id_by_email($mail->toemail);
						if (!$mp_user_id) continue 2;
					break;
					case (preg_match_all("/[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+@[\._a-zA-Z0-9-]{2,}+/i", $header, $matches, PREG_SET_ORDER) && ($bounce_email = is_email($matches[0][0])) ) :
						switch($tag)
						{
							case 'X-Failed-Recipients' :
							case 'Final-Recipient' :
								$mail_id = -1;
								$mp_user_id = MP_User::get_id_by_email($bounce_email);
								if (!$mp_user_id) continue 3;
							break;
							default :
								continue 3;
							break;
						}
					break;
					default :
						continue 2;
					break;
				}
			}

			if (isset($mail_id, $mp_user_id, $bounce_email))
			{
				$this->process_mailbox_status();
				return array($mail_id, $mp_user_id, $bounce_email);
				break;
			}
		}
		return false;
	}
}