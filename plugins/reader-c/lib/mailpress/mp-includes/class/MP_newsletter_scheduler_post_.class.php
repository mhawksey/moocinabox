<?php
abstract class MP_newsletter_scheduler_post_ extends MP_newsletter_scheduler_
{
	function __construct($description)
	{
		parent::__construct($description);

		add_action("publish_{$this->post_type}",	array($this, 'publish'), 8, 1);
	}

	function publish($post_id)
	{
		if (get_post_meta($post_id, '_MailPress_prior_to_install')) return true;

		$newsletters = MP_Newsletter::get_active_by_scheduler($this->id);
		if (empty($newsletters)) return true;

		$post = &get_post($post_id);
		$the_title = apply_filters('the_title', $post->post_title );

		$y = $this->year;
		$m = $this->month;
		$d = $this->day;

		$results = array();

		$trace = MP_Newsletter_schedulers::header_report("publish_{$this->post_type} : {$post_id}    {$this->id}");
		foreach($newsletters as $newsletter)
		{
			$this->newsletter = $newsletter;

			$this->newsletter['mail']['the_title'] = $the_title;

			$this->newsletter['processor']['query_posts']['p'] = $post_id;
			if ($this->post_type != 'post') $this->newsletter['processor']['query_posts']['post_type'] = $this->post_type;

			$this->newsletter['params']['post_id']  = $post_id;
			$this->newsletter['params']['meta_key'] = $this->get_meta_key();
			if ($this->post_type != 'post') $this->newsletter['params']['post_type']= $this->post_type;
		
			$h = $this->hour  + $this->get_hour();
			$i = $this->minute+ $this->get_minute();

			$results[] = $this->schedule_single_event( $this->mktime( $h, $i, 0, $m, $d, $y ), 'mp_process_post_newsletter' );
		}

		if (!empty($results))
		{
			MP_Newsletter_schedulers::sep_report($trace);
			$results = array_reverse($results);
			uasort($results, create_function('$a, $b', 'return strcmp($a["timestamp"], $b["timestamp"]);'));
			foreach($results as $result) $trace->log($result['log']);
		}
		MP_Newsletter_schedulers::footer_report($trace);
	}

	function get_meta_key()
	{
		$trailer = '';

		if ('post' != $this->post_type) $trailer .= "_{$this->post_type}";
		if (isset($this->taxonomy))
		{
			$trailer .= "_{$this->taxonomy}";
			if (isset($this->newsletter['params']['term_id'])) 	$trailer .= "_{$this->newsletter['params']['term_id']}";
			elseif (isset($this->newsletter['params']['cat_id']))	$trailer .= "_{$this->newsletter['params']['cat_id']}";  // backward compatibility
		}

		return "_MailPress_published{$trailer}";
	}
}