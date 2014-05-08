<?php
class MP_Newsletter_scheduler_month extends MP_newsletter_scheduler_
{
	public $id = 'month';

	function schedule($newsletter) 
	{
		$this->newsletter = $newsletter;

		$y = $this->year;
		$m = $this->month;

		$d = $this->get_day($y, $m);
		if ( $this->day > $d )
		{
			$m++;
			if ($m > 12) { $y++; $m = 1; }
			$d = $this->get_day($y, $m);
		}

		$h = $this->get_hour();
		$i = $this->get_minute();

		return $this->schedule_single_event( $this->mktime( $h, $i, 0, $m, $d, $y ) );
	}
}
new MP_Newsletter_scheduler_month(__('Every month', MP_TXTDOM));
