<?php
abstract class MP_newsletter_scheduler_ extends MP_newsletter_
{
	public $args = 'scheduler';
	public static $delta = 1;

	function __construct($description)
	{
		parent::__construct($description);

		add_filter('MailPress_newsletter_scheduler_' . $this->id . '_schedule',	array($this, 'schedule'), 8, 1);
	}

	function schedule($newsletter) { $this->newsletter = $newsletter; return false; }

	function schedule_single_event($timestamp, $event = 'mp_process_newsletter')
	{
		$timestamp   += self::$delta;
		self::$delta += 1;

		wp_schedule_single_event( $timestamp, $event, array('args' => array('newsletter' => $this->newsletter )) );

		return MP_Newsletter_schedulers::schedule_report($this->newsletter, $timestamp, $this->id );
	}
}