<?php
class MP_Newsletter_processor_day_1 extends MP_newsletter_processor_
{
	const day_in_sec  = 86400;  // 24*60*60

	public $id = 'day-1';

	function get_bounds() 
	{
		$d = $this->time;

		$h = $this->get_hour();
		$i = $this->get_minute();

		$format = 'Y-m-d ' . zeroise($h, 2) . ':' . zeroise($i, 2) . ':00';

		$this->upper_bound = date($format, $d);

		if ($this->upper_bound > $this->date)
		{
			$d -= self::day_in_sec;
			$this->upper_bound = date($format, $d);
		}

		$this->lower_bound = date($format, $d - self::day_in_sec);

		switch (true)
		{
			case (isset($this->options['threshold'])) :			// old format
				$y = substr($this->options['threshold'], 0, 4);
				$m = substr($this->options['threshold'], 4, 2);
				$j = substr($this->options['threshold'], 6, 2);
				$this->old_lower_bound = "{$y}-{$m}-{$j} 00:00:00";
			break;
			default :
				$this->get_old_lower_bound();
			break;
		}
	}

	function query_posts($query_posts = array()) 
	{ 
		if (	substr($this->lower_bound, 11, 8) == '00:00:00' && 
			substr($this->upper_bound, 11, 8) == '00:00:00'	)
		{
			$query_posts['m'] = date('Ymd', $this->time - self::day_in_sec);
			return $query_posts;
		}

		$this->add_filter();
		return $query_posts;
	}
}
new MP_Newsletter_processor_day_1(__('Previous day', MP_TXTDOM));