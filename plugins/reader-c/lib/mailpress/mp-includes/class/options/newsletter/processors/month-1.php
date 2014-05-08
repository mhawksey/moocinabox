<?php
class MP_Newsletter_processor_month_1 extends MP_newsletter_processor_
{
	public $id = 'month-1';

	function get_bounds() 
	{
		$y = $this->year;
		$m = $this->month;
		$d = $this->get_day($y, $m);
		$h = $this->get_hour();
		$i = $this->get_minute();

		$format = 'Y-m-d H:i:s';

		if ( $this->day < $d ) $m--; 
		if (!$m) {$m = 12; $y--;}
		$d = $this->get_day($y, $m);

		$this->upper_bound = date($format, mktime( $h, $i, 0, $m, $d, $y ));

		if ($this->upper_bound > $this->date)
		{
			$m--; 
			if (!$m) {$m = 12; $y--;}
			$this->upper_bound = date($format, mktime( $h, $i, 0, $m, $d, $y ));
		}

		$m--; 
		if (!$m) {$m = 12; $y--;}
		$d = $this->get_day($y, $m);

		$this->lower_bound = date($format, mktime( $h, $i, 0, $m, $d, $y ));

		switch (true)
		{
			case (isset($this->options['threshold'])) :			// old format
				$y = substr($this->options['threshold'], 0, 4);
				$m = substr($this->options['threshold'], 4, 2);
				$this->old_lower_bound =  "{$y}-{$m}-01 00:00:00";
			break;
			default :
				$this->get_old_lower_bound();
			break;
		}
	}

	function query_posts($query_posts = array()) 
	{ 
		if (	substr($this->lower_bound, 8, 11) == '01 00:00:00' && 
			substr($this->upper_bound, 8, 11) == '01 00:00:00'	)
		{
			$query_posts['m'] = date('Ym', mktime(0, 0, 0, date('m', $this->time), 0, date('Y', $this->time)) );
			return $query_posts;
		}

		$this->add_filter();
		return $query_posts;
	}
}
new MP_Newsletter_processor_month_1(__('Previous month', MP_TXTDOM));