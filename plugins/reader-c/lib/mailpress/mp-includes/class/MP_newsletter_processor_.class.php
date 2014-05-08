<?php
abstract class MP_newsletter_processor_ extends MP_newsletter_
{
	public $args = 'processor';

	function __construct($description)
	{
		parent::__construct($description);

		add_action('MailPress_newsletter_processor_' . $this->id . '_process', array($this, 'process'), 8, 2);
	}

	function process($newsletter, $trace)
	{
		global $wpdb;

		$this->newsletter = $newsletter;
		$this->trace 	= $trace;

		if ($this->already_processed()) return false;

		MP_Newsletter_processors::send($this->newsletter, $this->trace, false);

		remove_filter('posts_where', array($this, 'posts_where'));
	}

	function already_processed()
	{
		$this->options = get_option($this->newsletter[$this->args]['threshold']);

		$this->get_bounds();

		$this->lower_bound = max($this->old_lower_bound, $this->lower_bound);

		MP_Newsletter_processors::message_report($this->newsletter, "old : {$this->lower_bound}, lower : {$this->lower_bound}, upper = {$this->upper_bound}", $this->trace, true);

		if ( $this->lower_bound >=  $this->upper_bound)
		{
			MP_Newsletter_processors::message_report(false, "newsletter already processed : ({$this->lower_bound} >= {$this->upper_bound}) ", $this->trace, true);
			return true;
		}

		$this->options = array('end' => $this->upper_bound);

		if (!update_option($this->newsletter[$this->args]['threshold'], $this->options))
			  add_option($this->newsletter[$this->args]['threshold'], $this->options);

		$query_posts = (isset($this->newsletter[$this->args]['query_posts'])) ? $this->newsletter[$this->args]['query_posts'] : array();
		$this->newsletter['query_posts'] = $this->query_posts($query_posts);

		if (isset($this->lower_bound)) $this->newsletter['bounds']['lower'] = $this->lower_bound ;
		if (isset($this->upper_bound)) $this->newsletter['bounds']['upper'] = $this->upper_bound ;

		return false;
	}

	function get_old_lower_bound()
	{
		$this->old_lower_bound = (isset($this->options['end'])) ? $this->options['end'] : $this->lower_bound;
	}

	function add_filter()
	{
		global $wpdb;
		MP_Newsletter_processors::message_report(false, "filter(posts_where) : \" AND $wpdb->posts.post_date >= '{$this->lower_bound}'\"", $this->trace);
		MP_Newsletter_processors::message_report(false, "filter(posts_where) : \" AND $wpdb->posts.post_date <  '{$this->upper_bound}'\"", $this->trace);

		add_filter('posts_where', array($this, 'posts_where'), 8, 1);
	}

	function posts_where($where)
	{
		global $wpdb;

		if (isset($this->lower_bound)) $where .= " AND $wpdb->posts.post_date >= '{$this->lower_bound}' ";
		if (isset($this->upper_bound)) $where .= " AND $wpdb->posts.post_date <  '{$this->upper_bound}' ";

		return $where;
	}
}