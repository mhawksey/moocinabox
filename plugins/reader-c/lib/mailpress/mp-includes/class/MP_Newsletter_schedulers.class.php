<?php
class MP_Newsletter_schedulers extends MP_options_
{
	const bt = 150;

	var $path    = 'newsletter/schedulers';

	public static function get_all()
	{
		return apply_filters('MailPress_newsletter_schedulers_register', array());
	}

	public static function schedule($event)
	{
		MP_::no_abort_limit();
		$results = array();
		$error = false;

		$schedulers = self::get_all();

		global $mp_registered_newsletters;
		$nls = MP_Newsletter::get_active();

		$trace = self::header_report($event);

		foreach ($nls as $k => $v)
		{
			$newsletter = $mp_registered_newsletters[$k];
			if ( isset($newsletter['scheduler']['id']) && isset($schedulers[$newsletter['scheduler']['id']]) )
			{
				if (!$results[] = apply_filters('MailPress_newsletter_scheduler_' . $newsletter['scheduler']['id'] . '_schedule', $newsletter)) array_pop($results);
			}
			else
			{
				if ( !isset($newsletter['scheduler']['id']) )
				{
					if (!$error) self::sep_report($trace);
					self::message_report( $newsletter, 'no scheduler in newsletter (see xml file) ', $trace, true);
					$error = true;
				}
				if ( !isset($schedulers[$newsletter['scheduler']['id']]) )
				{
					if (!$error) self::sep_report($trace);
					self::message_report($newsletter, 'scheduler unknown ', $trace, true);
					$error = true;
				}
			}
		}

		if (!empty($results))
		{
			self::sep_report($trace);
			$results = array_reverse($results);
			uasort($results, create_function('$a, $b', 'return strcmp($a["timestamp"], $b["timestamp"]);'));
			foreach($results as $result) $trace->log($result['log']);
		}
		self::footer_report($trace);
	}

	public static function header_report($event)
	{
		$trace = new MP_Log('mp_sched_proc_newsletter', array('option_name' => 'newsletter'));

		self::sep_report($trace);
		$bm = "Scheduling Newsletters    event : $event ";
		$trace->log('!' . self::item_report(str_repeat( ' ', 5) . $bm, self::bt, '!'));
		self::sep_report($trace);
		$bm = ' ';
		$bm .= self::item_report('Newsletter id', 30);
		$bm .= self::item_report('scheduler',     20);
		$bm .= self::item_report('processor',     20);
		$bm .= self::item_report('timestamp',     10);
		$bm .= self::item_report('time');
		$trace->log('!' . self::item_report($bm, self::bt, '!'));

		return $trace;
	}

	public static function schedule_report($newsletter, $timestamp, $id)
	{
		$bm = ' ';
		$bm .= self::item_report($newsletter['id'],			30);
		$bm .= self::item_report($id,					20);
		$bm .= self::item_report($newsletter['processor']['id'],	20);
		$bm .= self::item_report($timestamp,				10);
		$bm .= self::item_report(date_i18n( 'l jS \of F Y H:i', strtotime(get_date_from_gmt(gmdate('Y-m-d H:i:s', $timestamp)))));
		return array('timestamp' => $timestamp, 'log' => '!' . self::item_report($bm, self::bt, '!'));
	}

	public static function message_report($newsletter, $text, $trace, $error = false)
	{
		$bm = ' ';
		if ($error)
			$bm .= ($newsletter) ? self::item_report($newsletter['id'], 30) : '';
		else
			$bm .= ($newsletter) ? self::item_report($newsletter['id'], 30) : self::item_report(' ', 30);

		$bm .= $text;
		$trace->log('!' . self::item_report($bm, self::bt, '!'));
	}

	public static function item_report($item, $max = false, $trailer = ' ! ')
	{
		if (false === $max) return $item;

		$l = $max - strlen($item);

		if ($l < 0) return $item . $trailer;
		return $item . str_repeat(' ', $l) . $trailer;
	}

	public static function sep_report($trace)
	{
		$trace->log('!' . str_repeat( '-', self::bt) . '!');
	}

	public static function footer_report($trace)
	{
		self::sep_report($trace);
		$trace->end(true);
	}
}