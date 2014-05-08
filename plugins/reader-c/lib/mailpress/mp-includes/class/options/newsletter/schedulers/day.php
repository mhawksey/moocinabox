<?php
class MP_Newsletter_scheduler_day extends MP_newsletter_scheduler_
{
	public $id = 'day';

	function schedule($newsletter) 
	{ 
		$this->newsletter = $newsletter;

		$y = $this->year;
		$m = $this->month;

		$d = $this->day;

		$h = $this->get_hour();
		$i = $this->get_minute();

		return $this->schedule_single_event( $this->mktime( $h, $i, 0, $m, $d, $y ) );
	}
}
new MP_Newsletter_scheduler_day(__('Every day', MP_TXTDOM));