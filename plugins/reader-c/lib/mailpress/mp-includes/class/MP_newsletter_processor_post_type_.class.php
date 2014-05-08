<?php
abstract class MP_newsletter_processor_post_type_ extends MP_newsletter_processor_post_
{
	function what_else()
	{
	// find taxonomy
		if (!isset($this->newsletter['params']['taxonomy'])) return true;

		$taxonomy = $this->newsletter['params']['taxonomy'];
		$term_id  = $this->newsletter['params']['term_id'];

		$terms = $this->get_terms( $term_id, $taxonomy );

		if (empty($terms))
		{
			$post_type = $this->newsletter['params']['post_type'];
			MP_Newsletter_processors::message_report($this->newsletter, "no terms for $post_type #{$this->post_id} in $taxonomy", $this->trace);
			return false;
		}

		if (!is_object_in_term($this->post_id, $taxonomy, $terms )) 
		{
			$post_type = $this->newsletter['params']['post_type'];
			MP_Newsletter_processors::message_report($this->newsletter, "newsletter $taxonomy (terms/ids) : " . join(',', $terms), $this->trace);
			MP_Newsletter_processors::message_report(false, "$post_type #{$this->post_id} not in required $taxonomy", $this->trace);
			return false;
		}

		return true;
	}

	function get_terms( $term_id, $taxonomy )
	{
		if (!isset($this->newsletter[$this->args]['query_posts'][$taxonomy])) return array();
		$terms = (array) $this->newsletter[$this->args]['query_posts'][$taxonomy];

		if (is_taxonomy_hierarchical($taxonomy))
		{
			$children = get_term_children( $term_id, $taxonomy );
			if ( !is_a($children, 'WP_Error') ) $terms = array_merge($terms, $children);
		}

		return $terms;
	}
}