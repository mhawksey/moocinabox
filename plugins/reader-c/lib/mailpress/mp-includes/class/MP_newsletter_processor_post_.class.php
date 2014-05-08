<?php
abstract class MP_newsletter_processor_post_ extends MP_newsletter_processor_
{
	function process($newsletter, $trace)
	{
		$this->newsletter = $newsletter;
		$this->trace 	= $trace;

		$this->post_id  = $this->newsletter['params']['post_id'];
		$this->meta_key = $this->newsletter['params']['meta_key'];
		$this->post_type= (isset($this->newsletter['params']['post_type'])) ? $this->newsletter['params']['post_type'] : 'post';

	// detect if post already processed
		if ($this->already_processed()) 
		{
			MP_Newsletter_processors::message_report($this->newsletter, "{$this->post_type} {$this->post_id} already processed", $this->trace);
			return false;
		}

	// detect if anything else is required
		if (!$this->what_else()) return false;

		$this->newsletter['query_posts'] = isset($this->newsletter[$this->args]['query_posts']) ? $this->newsletter[$this->args]['query_posts'] : array();

		MP_Newsletter_processors::send($this->newsletter, $this->trace);
	}

	function already_processed()
	{
		if (get_post_meta($this->post_id, $this->meta_key))
			return true;

		add_post_meta($this->post_id, $this->meta_key, true, true);
		return false;
	}

	function what_else()
	{
	// detect if any category required

		$cats		= $this->get_cats('cat',			'intval');
		$cats_in	= $this->get_cats('category__in',	'absint');
		$cats_out	= $this->get_cats('category__not_in','absint');

		if (!empty($cats)) foreach ( $cats as $cat )
		{
			$in = ($cat > 0);
			$cat = abs($cat);
			if ( $in ) {
				$cats_in[] = $cat;
				$cats_in   = array_merge($cats_in,  get_term_children($cat, 'category'));
			} else {
				$cats_out[]= $cat;
				$cats_out  = array_merge($cats_out, get_term_children($cat, 'category'));
			}
		}

		if (!empty($cats_in))
		{
			$post_categories = wp_get_post_categories($this->post_id);
			sort($post_categories);

			$cats_in = array_unique($cats_in);
			sort($cats_in);

			$intersect  = array_intersect($post_categories, $cats_in);
			if (empty($intersect))
			{
				MP_Newsletter_processors::message_report($this->newsletter, "newsletter categories (in) : " 	. join(',', $cats_in), $this->trace);
				MP_Newsletter_processors::message_report(false, "post categories : " . join(',', $post_categories), $this->trace);
				MP_Newsletter_processors::message_report(false, "Post {$this->post_id} not in required categories", $this->trace);
				return false;
			}
		}

		if (!empty($cats_out))
		{
			$post_categories = wp_get_post_categories($this->post_id);
			foreach($post_categories as $cat) $post_categories = array_merge($post_categories, get_term_children($cat, 'category'));
			$post_categories = array_unique($post_categories);
			sort($post_categories);

			$cats_out = array_unique($cats_out);
			sort($cats_out);

			$diff  = array_diff($post_categories, $cats_out);
			if (empty($diff))
			{
				MP_Newsletter_processors::message_report($this->newsletter, "newsletter categories (out) : " 	. join(',', $cats_out), $this->trace);
				MP_Newsletter_processors::message_report(false, "post categories : " . join(',', $post_categories), $this->trace);
				MP_Newsletter_processors::message_report(false, "Post {$this->post_id} in excluding categories", $this->trace);
				return false;
			}
		}
		return true;
	}

	function get_cats($arg, $array_map)
	{
		if (!isset($this->newsletter[$this->args]['query_posts'][$arg])) return array();
		if ( empty($this->newsletter[$this->args]['query_posts'][$arg])) return array();
		if (!is_array($this->newsletter[$this->args]['query_posts'][$arg])) $this->newsletter[$this->args]['query_posts'][$arg] = array($this->newsletter[$this->args]['query_posts'][$arg]);

		$cats = join(',', $this->newsletter[$this->args]['query_posts'][$arg]);

		return array_map($array_map, preg_split('/[,\s]+/', $cats));
	}
}