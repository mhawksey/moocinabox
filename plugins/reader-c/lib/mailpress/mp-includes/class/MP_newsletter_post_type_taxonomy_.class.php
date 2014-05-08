<?php
abstract class MP_newsletter_post_type_taxonomy_ extends MP_newsletter_post_type_
{
	function __construct() 
	{
		parent::__construct();

		$filter = ('post' == $this->post_type) ? '' : "_{$this->post_type}";

		$this->args = array(	'root' 		=> MP_CONTENT_DIR . "advanced/newsletters/{$this->post_type}/{$this->taxonomy_s}",
						'root_filter' 	=> "MailPress_advanced_newsletters{$filter}_root",
						'folder'		=> $this->taxonomy_s,
						'files'		=> array($this->taxonomy_s),

						'taxonomy'		=> $this->taxonomy,
						'get_terms_args'=> array('hide_empty' => 0),

						'Template'		=> $this->post_type,

						'post_type'	=> $this->post_type,
		);
	}

	function register() 
	{
		MP_Newsletter::register_taxonomy($this->args);
	}

	function subscriptions_newsletter_th($th, $newsletter)
	{
		if (	isset($newsletter['params']['post_type']) 	&& $this->post_type == $newsletter['params']['post_type'] && 
			isset($newsletter['params']['taxonomy']) 	&& $this->taxonomy  == $newsletter['params']['taxonomy']) 
			return $newsletter['mail']['the_post_type'] . '/' .  $newsletter['mail']['the_taxonomy'] ;
		return $th;
	}
}