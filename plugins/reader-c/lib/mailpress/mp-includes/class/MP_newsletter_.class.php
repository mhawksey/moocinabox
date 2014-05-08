<?php
abstract class MP_newsletter_
{
	function __construct($desc)
	{
		$this->desc = $desc;

		$this->time = current_time('timestamp');

		$this->date  = gmdate('Y-m-d H:i:s', $this->time);
		$this->year  = (int) gmdate('Y', $this->time);
		$this->month = (int) gmdate('m', $this->time);
		$this->day   = (int) gmdate('j', $this->time);
		$this->hour  = (int) gmdate('H', $this->time);
		$this->minute= (int) gmdate('i', $this->time);

		$this->wday  = (int) gmdate('w', $this->time);

		add_filter('MailPress_newsletter_' . $this->args . 's_register',	array($this, 'register'), 8, 1);
	}

	function register($x) { $x[$this->id] = $this->desc; return $x; }

	function get_day($y, $m) 
	{
		$d = (isset($this->newsletter[$this->args]['args']['day'])) ? (int) $this->newsletter[$this->args]['args']['day'] : 1;

		$max_days = array(31,((($y%4==0)&&((!($y%100==0))||($y%400==0)))?29:28),31,30,31,30,31,31,30,31,30,31);
		$max_day  = $max_days[$m - 1];

		return (!is_numeric($d)) ? 1 : (($d <= 0 || $d > $max_day) ? $max_day : $d);
	}

	function get_wday() 
	{
		$w = (isset($this->newsletter[$this->args]['args']['wday']) && is_numeric($this->newsletter[$this->args]['args']['wday'])) ? $this->newsletter[$this->args]['args']['wday'] : get_option('start_of_week');
		if ( $w === false) 	$w = 1;
		if ( $w == 7 ) 		$w = 0;
		return (!is_numeric($w) || $w < 0 || $w > 6) ? 1 : $w;
	}

	function get_hour() 
	{
		$h = (isset($this->newsletter[$this->args]['args']['hour'])) ? (int) $this->newsletter[$this->args]['args']['hour'] : 0;
		return (!is_numeric($h) || $h < 0 || $h > 23) ? 0 : $h;
	}

	function get_minute() 
	{
		$i = (isset($this->newsletter[$this->args]['args']['minute'])) ? (int) $this->newsletter[$this->args]['args']['minute'] : 0;
		return (!is_numeric($i) || $i < 0 || $i > 59) ? 0 : $i;
	}

	function mktime( $h, $i, $s, $m, $d, $y )
	{
		return gmmktime( $h, $i, $s, $m, $d, $y ) - get_option('gmt_offset') * 3600;
	}
}