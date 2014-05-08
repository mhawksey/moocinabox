<?php
abstract class MP_autoresponder_event_
{
	const bt = 100;

	function __construct($desc)
	{
		$this->desc = $desc;
		if (!isset($this->callback)) $this->callback = array($this, 'callback');

		add_filter('MailPress_autoresponder_events_register',	array($this, 'register'), 8, 1);
		add_action($this->event, $this->callback, 8, 1);
		add_action('mp_process_autoresponder_' . $this->id, array($this, 'process'), 8, 1);

		add_action('MailPress_autoresponder_' . $this->id . '_settings_form',	array($this, 'settings_form'),	8, 1);
	}

	function register($events)
	{
		$events[$this->id] = $this->desc;
		return $events;
	}

//// Tracking events to autorespond to  ////

	function callback($args)
	{
		$autoresponders = MP_Autoresponder::get_from_event($this->id);
		if (empty($autoresponders)) return;

		foreach( $autoresponders as $autoresponder )
		{
			if (!$this->to_do($autoresponder, $args)) continue;

			$_mails = MP_Autoresponder::get_term_objects($autoresponder->term_id);

			if (!isset($_mails[0])) continue;

			$term_id = $autoresponder->term_id;

			$time = time();
			$schedule = $this->schedule($time, $_mails[0]['schedule']);
			$meta_id = MP_User_meta::add($this->mp_user_id, '_MailPress_autoresponder_' . $term_id, $time);

			$this->trace = new MP_Log('mp_process_autoresponder_'. $term_id, array('option_name' => 'autoresponder'));

			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
			$bm = "Batch Report autoresponder #$term_id            meta_id : $meta_id  mail_order : 0";
			$this->trace->log('!' . str_repeat( ' ', 5) . $bm . str_repeat( ' ', self::bt - 5 - strlen($bm)) . '!');
			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
			$bm = " mp_user    ! $this->mp_user_id";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$bm = " event      ! " . $this->event;
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$bm = " 1st sched. ! ";
			$bm .= ('000000' == $_mails[0]['schedule']) ? __('now',  MP_TXTDOM) : date('Y-m-d H:i:s', $schedule);
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');

			$this->trace->end(true);

			$_args = array('meta_id' => $meta_id, 'mail_order'=> 0 );
			if ('000000' == $_mails[0]['schedule'])
				do_action ('mp_process_autoresponder_' . $this->id, $_args);
			else
				wp_schedule_single_event($schedule, 'mp_process_autoresponder_' . $this->id, 	array('args' => $_args));
		}
	}

	function to_do($autoresponder, $args)
	{
		$this->mp_user_id = $args;
		return true;
	}

	function schedule($time, $value)
	{
		$Y = date('Y', $time) + $value['Y'];
		$M = date('n', $time) + $value['M'];
		$D = date('j', $time) + $value['D'] + ($value['W'] * 7);
		$H = date('G', $time) + $value['H'];
		$Mn= date('i', $time);
		$S = date('s', $time);

		return mktime($H, $Mn, $S, $M, $D, $Y);
	}

	function process($args)
	{
		MP_::no_abort_limit();

		extract($args);		// $meta_id, $mail_order
		$meta_id = (isset($umeta_id)) ? $umeta_id : $meta_id;

		$meta = MP_User_meta::get_by_id($meta_id);
		$term_id 	= (!$meta) ? 'unknown' : str_replace('_MailPress_autoresponder_', '', $meta->meta_key);

		$this->trace = new MP_Log('mp_process_autoresponder_'. $term_id, array('option_name' => 'autoresponder'));

		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
		$bm = "Batch Report autoresponder #$term_id            meta_id : $meta_id  mail_order : $mail_order";
		$this->trace->log('!' . str_repeat( ' ', 5) . $bm . str_repeat( ' ', self::bt - 5 - strlen($bm)) . '!');
		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');


		$bm = " start      !";
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');

		$this->trace->end($this->send($args));
	}

	function send($args)
	{
		extract($args);		// $meta_id, $mail_order
		$meta_id = (isset($umeta_id)) ? $umeta_id : $meta_id;

		$meta = MP_User_meta::get_by_id($meta_id);
		if (!$meta)
		{
			$bm = "** WARNING *! ** Unable to read table usermeta for id : $meta_id **";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$bm = " end        ! Abort";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}

		$mp_user_id = $meta->mp_user_id;
		$term_id 	= str_replace('_MailPress_autoresponder_', '', $meta->meta_key);
		$time		= $meta->meta_value;

		$autoresponder = MP_Autoresponder::get($term_id);
		if (!isset($autoresponder->description['active']))
		{
			$bm = "** WARNING *! ** Autoresponder :  $term_id is inactive **";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$bm = " end        ! Abort";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}

		$mp_user = MP_User::get($mp_user_id);
		if (!$mp_user)
		{
			$bm = "** WARNING *! ** mp_user_id : $mp_user_id is not found **";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$bm = " end        ! Abort";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}

		$_mails = MP_Autoresponder::get_term_objects($term_id);
		if (!$_mails)
		{
			$bm = "** WARNING *! ** Autoresponder :  $term_id has no mails **";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$bm = " end        ! Abort";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}
		if (!isset($_mails[$mail_order]))
		{
			$bm = "** WARNING *! ** mail_order : $mail_order NOT in mails to be processed **";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$bm = " end        ! Abort";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
			return false;
		}

		$_mail = $_mails[$mail_order];

		$draft = MP_Mail::get($_mail['mail_id']);
		if (!$draft)
		{
			$bm = " processing ! mail_id : " . $_mail['mail_id'] . " NOT in mail table, skip to next mail/schedule if any";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
		}

		if (!MP_Mail_draft::send($_mail['mail_id'], array('toemail' => $mp_user->email, 'toname' => $mp_user->name)))
		{
			$bm = " processing ! Sending mail_id : " . $_mail['mail_id'] . " failed, skip to next mail/schedule if any";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
		}
		else
		{
			$bm = " processing ! Sending mail_id : " . $_mail['mail_id'] . " successful ";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
		}

		$mail_order++;
		if (!isset($_mails[$mail_order]))
		{
			$bm = " end        ! last mail processed";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
			$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
			return true;
		}

		$schedule = $this->schedule($time, $_mails[$mail_order]['schedule']);
		wp_schedule_single_event($schedule, 'mp_process_autoresponder_' . $this->id, array('args' => array('meta_id' => $meta_id, 'mail_order'=> $mail_order)));

		$bm = " end        !  next mail to be processed : $mail_order scheduled on : " . date('Y-m-d H:i:s', $schedule);
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');

		return true;
	}

	function settings_form($settings)
	{
		return;
	}
}