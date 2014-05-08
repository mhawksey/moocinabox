<?php
abstract class MP_newsletter_post_type_
{
	var $register_priority = 10;

	function __construct() 
	{
		add_action('MailPress_register_newsletter',	array($this, 'register'), $this->register_priority);

		if (method_exists($this,'init')) add_action('init', array($this, 'init'), 1);

		if (is_admin())
		{
		// settings
			add_filter('MailPress_subscriptions_newsletter_th',		array($this, 'subscriptions_newsletter_th'), 10, 2 );
		// install
			register_activation_hook(  plugin_basename($this->file),	array($this, 'install'));
			register_deactivation_hook(plugin_basename($this->file),	array($this, 'uninstall'));
		// for link on plugin page
			add_filter('plugin_action_links',					array($this, 'plugin_action_links'), 10, 2 );
		}

		$this->args = array(	'root' 		=> MP_CONTENT_DIR . "advanced/newsletters/{$this->post_type}",
						'root_filter' 	=> "MailPress_advanced_newsletters_{$this->post_type}_root",
						'files'		=> array("post_type", "daily", "weekly", "monthly"),

						'Template'		=> $this->post_type,

						'post_type'	=> $this->post_type,


		);
	}

	function register() 
	{
		MP_Newsletter::register_files($this->args);
	}

	function subscriptions_newsletter_th($th, $newsletter)
	{
		if (	isset($newsletter['params']['post_type']) 	&& $this->post_type == $newsletter['params']['post_type'] && !isset($newsletter['params']['taxonomy']) )
			return '** ' . $newsletter['mail']['the_post_type'] . ' **';
		return $th;
	}

	function install() 
	{
		$event = "Install newsletter_{$this->post_type}";
		if (isset($this->taxonomy)) $event .=  "_{$this->taxonomy}";

		$now4cron = current_time('timestamp', 'gmt');
		wp_schedule_single_event($now4cron - 1, 'mp_schedule_newsletters', array('args' => array('event' => $event )));
	}

	function uninstall() 
	{
		MailPress_newsletter::unschedule_hook('mp_process_newsletter');

		$event = "Uninstall newsletter_{$this->post_type}";
		if (isset($this->taxonomy)) $event .=  "_{$this->taxonomy}";

		$now4cron = current_time('timestamp', 'gmt');
		wp_schedule_single_event($now4cron + 1, 'mp_schedule_newsletters', array('args' => array('event' => $event )));
	}

	function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename($this->file), 'subscriptions');
	}
}