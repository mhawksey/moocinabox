<?php
class MP_Newsletter_scheduler_week extends MP_newsletter_scheduler_
{
	public $id = 'week';

	function schedule($newsletter) 
	{
		$this->newsletter = $newsletter;

		$y = $this->year;
		$m = $this->month;

		$wdiff  = $this->get_wday() - $this->wday;
		if ( $wdiff < 0 ) $wdiff += 7;
		$d = $this->day + $wdiff; 

		$h = $this->get_hour();
		$i = $this->get_minute();

		return $this->schedule_single_event( $this->mktime( $h, $i, 0, $m, $d, $y ) );
	}
}
new MP_Newsletter_scheduler_week(__('Every week', MP_TXTDOM));