<?php
if (class_exists('MailPress_newsletter') && !class_exists('MailPress_newsletter_categories') )
{
/*
Plugin Name: MailPress_newsletter_categories
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/newsletter_categories/
Description: Newsletters : for posts per category  (<span style='color:red;'>required !</span> <span style='color:#D54E21;'>Newsletter</span> add-on)
Version: 5.4
*/

class MailPress_newsletter_categories extends MP_newsletter_post_type_taxonomy_
{
	var $file	= __FILE__;
	var $register_priority = 2;

	var $post_type= 'post';
	var $taxonomy = 'category';

	var $taxonomy_s = 'categories';
	
	function __construct() 
	{
		parent::__construct();

		unset($this->args['Template']);
		$this->args['get_terms_args'] = array('parent' => 0, 'hide_empty' => 0);
	}

	function subscriptions_newsletter_th($th, $newsletter)
	{
		if (isset($newsletter['mail']['the_category'])) return __('Post') . '/' . $newsletter['mail']['the_category'];
		return $th;
	}
}
new MailPress_newsletter_categories();
}