<?php
abstract class MP_bounce_ extends MP_db_connect_
{
	public $bt = 132;
	public static $count = array();

	function __construct()
	{
		$this->config = get_option($this->option_name);
		if (!$this->config) return;

		$this->trace = new MP_Log($this->log_name, array('option_name' => $this->log_option_name));

		$xmailboxstatus = array(	0	=>	'no changes',
							1	=>	'mark as read',
							2	=>	'delete' );

		$this->trace->log('!' . str_repeat( '-', $this->bt) . '!');
		$bm = sprintf($this->log_title, $xmailboxstatus[$this->config['mailbox_status']]);
		$this->trace->log('!' . str_repeat( ' ', 5) . $bm . str_repeat( ' ', $this->bt - 5 - strlen($bm)) . '!');
		$this->trace->log('!' . str_repeat( '-', $this->bt) . '!');
		$bm = " start      !";
		$this->trace->log('!' . $bm . str_repeat( ' ', $this->bt - strlen($bm)) . '!');

		$return = $this->process();

		do_action($this->cron_name);

		$this->trace->log('!' . str_repeat( '-', $this->bt) . '!');
		$this->trace->end($return);
	}

// process
	function process()
	{
		$return = true;
		$pop3 = get_option($this->option_name_pop3);

		$this->pop3 = new MP_Pop3($pop3['server'], $pop3['port'], $pop3['username'], $pop3['password'], $this->trace);

		$bm = ' connecting ! ' . $pop3['server'] . ':' . $pop3['port'];
		$this->trace->log('!' . $bm . str_repeat( ' ', $this->bt - strlen($bm)) . '!');

		if ($this->pop3->connect())
		{
			if ($this->pop3->get_list())
			{
				foreach($this->pop3->messages as $this->message_id) $this->process_message();
			}
			else
			{
				$v = ' *** ALL DONE ***       *** ALL DONE ***       *** ALL DONE *** '; 
				$this->trace->log('!' . str_repeat( '-', $this->bt) . '!');
				$this->trace->log('!' . str_repeat( ' ', 15) . $v . str_repeat( ' ', $this->bt -15 - strlen($v)) . '!');
				$this->trace->log('!' . str_repeat( '-', $this->bt) . '!');
				$return = false;
			}
			if (!$this->pop3->disconnect()) $return = false;
		}
		else $return = false;

		if ($return)
		{
			$bm = " end        !";
			$this->trace->log('!' . $bm . str_repeat( ' ', $this->bt - strlen($bm)) . '!');
		}
		return $return;
	}

	function process_message()
	{
		if (!list($mail_id, $mp_user_id, $bounce_email) = $this->is_bounce()) return;
		if (!isset($mail_id, $mp_user_id, $bounce_email)) return;

		$this->mysql_disconnect($this->class);
		$this->mysql_connect($this->class);

		$this->trace->log('!' . str_repeat( '-', $this->bt) . '!');
		$bm = '            ! id         ! bounces   ! ' . $bounce_email;
		$this->trace->log('!' . $bm . str_repeat( ' ', $this->bt - strlen($bm)) . '!');

		$user_logmess = $mail_logmess = '';
		$already_processed = $already_stored = false;

		if (!$mp_user = MP_User::get($mp_user_id))
		{
			$user_logmess = '** WARNING ** user not in database'; 
			$usermeta['bounce'] = 0;
		}
		else
		{
			$bounce = array( 'message' => $this->pop3->message );

			$usermeta = MP_User_meta::get($mp_user_id, $this->meta_key);
			if (!$usermeta)
			{
				$usermeta = array();
				$usermeta['bounce'] = 1;
				$usermeta['bounces'][$mail_id][] = $bounce;	
				MP_User_meta::add($mp_user_id, $this->meta_key, $usermeta);
			}
			else
			{
				if (!is_array($usermeta)) $usermeta = array();

				if (!isset($usermeta['bounces'][$mail_id])) 
				{
					$usermeta['bounces'][$mail_id] = array();

					if (!isset($usermeta['bounce'])) 		$usermeta['bounce'] = 1;
					elseif (!is_numeric($usermeta['bounce'])) $usermeta['bounce'] = 1;
					else $usermeta['bounce']++;
				}
				else
				{
					$already_processed = true;
					foreach($usermeta['bounces'][$mail_id] as $bounces)
					{
						if ($bounces['message'] == $bounce['message'])
						{
							$already_stored = true;
							break;
						}
					}
				}

				if (!$already_stored) $usermeta['bounces'][$mail_id][] = $bounce;

				if (!MP_User_meta::add(    $mp_user_id, $this->meta_key, $usermeta, true))
					MP_User_meta::update($mp_user_id, $this->meta_key, $usermeta);
			}

			switch (true)
			{
				case $already_processed :
					$user_logmess = '-- notice -- bounce previously processed';
				break;
				case ('bounced' == $mp_user->status) :
					$user_logmess = ' <' . $mp_user->email . '> already ** BOUNCED **';
				break;
				case ($usermeta['bounce'] >= $this->config['max_bounces']) :
					MP_User::set_status($mp_user_id, 'bounced');
					$user_logmess = '** BOUNCED ** <' . $mp_user->email . '>';
				break;
				default :
					$user_logmess = 'new bounce for <' . $mp_user->email . '>';
				break;
			}
		}

		$bm  = ' user       ! ';
		$bm .= str_repeat(' ', 10 - strlen($mp_user_id) ) . $mp_user_id . ' !';
		$bm .= str_repeat(' ', 10 - strlen($usermeta['bounce']) ) . (($usermeta['bounce']) ? $usermeta['bounce'] : '') . ' !';
		$bm .= " $user_logmess";
		$this->trace->log('!' . $bm . str_repeat( ' ', $this->bt - strlen($bm)) . '!');

		$mailmeta = '';
		if (!$already_processed)
		{
			switch (true)
			{
				case (-1 == $mail_id) :
					$mail_logmess = '** WARNING ** mail unknown';
				break;
				case (!$mail = MP_Mail::get($mail_id)) :
					$mail_logmess = '** WARNING ** mail not in database';
				break;
				default :
					if (!isset(self::$count[$mail_id])) self::$count[$mail_id] = MP_Mail_meta::get($mail_id, $this->meta_key);
					self::$count[$mail_id] = ( is_numeric(self::$count[$mail_id]) ) ? ( self::$count[$mail_id] + 1 ) : 1;
					if (!MP_Mail_meta::add($mail_id, $this->meta_key, self::$count[$mail_id] , true))
						MP_Mail_meta::update($mail_id, $this->meta_key, self::$count[$mail_id] );
					$mailmeta = self::$count[$mail_id];

					$metas = MP_Mail_meta::get( $mail_id, '_MailPress_replacements');
					$mail_logmess = $mail->subject;
					if ($metas) foreach($metas as $k => $v) $mail_logmess = str_replace($k, $v, $mail_logmess);
					if ( strlen($mail_logmess) > 50 )	$mail_logmess = substr($mail_logmess, 0, 49) . '...';
				break;
			}
		}
		$bm  = ' mail       ! ';
		$bm .= str_repeat(' ', 10 - strlen($mail_id) )  . $mail_id . ' !';
		$bm .= str_repeat(' ', 10 - strlen($mailmeta) ) . $mailmeta . ' !';
		$bm .= " $mail_logmess";
		$this->trace->log('!' . $bm . str_repeat( ' ', $this->bt - strlen($bm)) . '!');
	
		$this->trace->log('!' . str_repeat( '-', $this->bt) . '!');
	}

	function process_mailbox_status()
	{
		switch ($this->config['mailbox_status'])
		{
			case 1 :
				$this->pop3->get_message($this->message_id);
			break;
			case 2 :
				$this->pop3->delete($this->message_id);
			break;
			default :
			break;
		}
	}

	function get_tag($tag, $numeric = false, $item = 0)
	{
		if (isset($this->pop3->headers[$tag][$item]))
		{
			if ($numeric && is_numeric($this->pop3->headers[$tag][$item])) 
			{
				return $this->pop3->headers[$tag][$item];
			}
			elseif (!$numeric)
			{
				return $this->pop3->headers[$tag][$item];
			}
		}
		return false;
	}

	function get_body($length = 8192)
	{
		$body = ($length) ? substr($this->pop3->message, 0, $length) : $this->pop3->message;
                        
		// Microsoft Exchange Base 64 decoding
		if (preg_match('/\r?\n(.*?)\r?\nContent-Type\:\s*text\/plain.*?Content-Transfer-Encoding\:\sbase64\r?\n\r?\n(.*?)\1/is', $body, $matches))
			$body = str_replace($matches[2], base64_decode(str_replace(array("\n", "\r"), '', $matches[2])), $body);
                        
		// clean up
		$body = preg_replace('%--- Below this line is a copy of the message.(.*)%is', '', $body);
		$body = preg_replace('%------ This is a copy (.*)%is', '', $body);
		$body = preg_replace('%----- Original message -----(.*)%is', '', $body); 
		$body = preg_replace('%Content-Type: message/rfc822.*%is', '', $body);
		$body = preg_replace('%Content-Description: Delivery report.*\s*?%i', '', $body);
		$body = str_replace("\r", "", $body);
		$body = str_replace(array("\n", "\r"), " ", $body);
		$body = preg_replace('%\s+%', ' ', $body);

		return $body;
	}
}