<?php
class MP_Newsletter_processors extends MP_options_
{
	const bt = 150;

	var $path 	  = 'newsletter/processors';

	public static function get_all()
	{
		return apply_filters('MailPress_newsletter_processors_register', array());
	}

	public static function process($newsletter)
	{
		MP_::no_abort_limit();

		$processors = self::get_all();

		$nls = MP_Newsletter::get_active();

		$trace = self::header_report($newsletter);
		self::sep_report($trace);

		if ( isset($newsletter['processor']['id']) && isset($processors[$newsletter['processor']['id']]) && isset($nls[$newsletter['id']]) )
		{
			do_action('MailPress_newsletter_processor_' . $newsletter['processor']['id'] . '_process', $newsletter, $trace);
		}
		else
		{
			$bm = ' ' . self::item_report($newsletter['id'], 30);
			if ( !isset($newsletter['processor']['id']) )
				self::message_report($newsletter, 'no processor in newsletter (see xml file) ', $trace, true);
			elseif ( !isset($processors[$newsletter['processor']['id']]) )
				self::message_report($newsletter, 'unknown processor : ' . $newsletter['processor']['id'], $trace, true);
			elseif ( !isset($nls[$newsletter['id']]) )
				self::message_report($newsletter, 'newsletter not active : ' . $newsletter['id'], $trace, true);
		}
		self::footer_report($trace);
		unset($trace, $newsletter);
	}

	public static function send($newsletter, $trace = false, $report = true )
	{
		if (!isset($newsletter['query_posts']))
		{
			if ($trace) self::message_report(false, '>> empty query_posts : end of process <<', $trace);
			return;
		}

		self::message_report(($report) ? $newsletter : false, 'query_posts : ' . json_encode($newsletter['query_posts']), $trace);

		$rc = MP_Newsletter::send($newsletter, true, false, $trace);

		if ($trace)
		{
			$bm = "($rc) ";
			switch ( true )
			{
				case ( 0 === $rc ) :
					$bm .= 'no recipients';
				break;
				case (!$rc) :
					$bm .= 'a problem occured (further details in appropriate Mail log)';
				break;
				case ( 'npst' == $rc ) :
					$post_type = (isset($newsletter['params']['post_type'])) ? strtolower($newsletter['params']['post_type']) : 'post';
					$bm .= sprintf('no %s for this newsletter', $post_type);
				break;
				case ( 'noqp' == $rc ) :
					$bm .= 'newsletter[\'query_posts\'] not set (error in code ?)';
				break;
				default :
					$bm = "** Process successful ** (recipients : $rc)";
				break;
			}
			self::message_report(false, $bm, $trace);
		}
	}

	public static function header_report($newsletter)
	{
		$trace = new MP_Log('mp_sched_proc_newsletter', array('option_name' => 'newsletter'));

		self::sep_report($trace);
		$bm = 'Processing Newsletter    ' . '  processor : ' . $newsletter['processor']['id'] . ' ' ;
		$trace->log('!' . self::item_report(str_repeat( ' ', 5) . $bm, self::bt, '!'));
		self::sep_report($trace);
		$bm = ' ';
		$bm .= self::item_report('Newsletter id', 30);
		$trace->log('!' . self::item_report($bm, self::bt, '!'));

		return $trace;
	}

	public static function message_report($newsletter, $text, $trace, $error = false) { MP_Newsletter_schedulers::message_report($newsletter, $text, $trace, $error); }
	public static function item_report($item, $max = false, $trailer = ' ! ') { return MP_Newsletter_schedulers::item_report($item, $max, $trailer);  }
	public static function sep_report($trace) { MP_Newsletter_schedulers::sep_report($trace); }
	public static function footer_report($trace) { MP_Newsletter_schedulers::footer_report($trace); }
}