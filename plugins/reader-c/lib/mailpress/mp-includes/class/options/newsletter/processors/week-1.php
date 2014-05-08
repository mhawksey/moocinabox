<?php
class MP_Newsletter_processor_week_1 extends MP_newsletter_processor_
{
	const day_in_sec  = 86400;  // 24*60*60
	const week_in_sec = 604800; // 7*24*60*60

	public $id = 'week-1';

	function get_bounds() 
	{
		$d = $this->time;
		$wd= $this->get_wday();
		$h = $this->get_hour();
		$i = $this->get_minute();

		$format = 'Y-m-d ' . zeroise($h, 2) . ':' . zeroise($i, 2) . ':00';

		while (date('w', $d) != $wd) $d -= self::day_in_sec;

		$this->upper_bound = date($format, $d);

		if ($this->upper_bound > $this->date)
		{
			$d -= self::week_in_sec;
			$this->upper_bound = date($format, $d);
		}

		$this->lower_bound = date($format, $d - self::week_in_sec);

		switch (true)
		{
			case (isset($this->options['threshold'])) :			// old old format
				$y = substr($this->options['threshold'], 0, 4);
				$w = substr($this->options['threshold'], 4, 2);
				$this->old_lower_bound = date('Y-m-d 00:00:00', strtotime("{$y}W{$w}1"));
			break;
			case (isset($this->options['end_of_week'])) : 			// old format
				$this->old_lower_bound = date('Y-m-d 00:00:00', $this->options['end_of_week'] + self::day_in_sec);
			break;
			default :
				$this->get_old_lower_bound();
			break;
		}
	}

	function query_posts($query_posts = array()) 
	{ 
		if (substr($this->lower_bound, 11, 8) == '00:00:00' && date('w', strtotime($this->lower_bound)) == 1 &&
		    substr($this->upper_bound, 11, 8) == '00:00:00' && date('w', strtotime($this->upper_bound)) == 1 )
		{
			$query_posts['year'] = date('o', strtotime($this->lower_bound));
			$query_posts['w']    = date('W', strtotime($this->lower_bound));
			return $query_posts;
		}

		$this->add_filter();
		return $query_posts;
	}
}
new MP_Newsletter_processor_week_1(__('Previous week', MP_TXTDOM));