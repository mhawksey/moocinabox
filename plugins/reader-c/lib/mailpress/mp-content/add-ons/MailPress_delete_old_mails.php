<?php
if (class_exists('MailPress') && !class_exists('MailPress_delete_old_mails'))
{
/*
Plugin Name: MailPress_delete_old_mails
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/delete_old_mails/
Description: Mails : delete old mails
Version: 5.4
*/

class MailPress_delete_old_mails
{
	const option_name = 'MailPress_delete_old_mails';

	const bt = 132;

	function __construct()
	{
		add_action('mp_process_delete_old_mails', 			array($this, 'process'));

		$config = get_option(self::option_name);
		if ('wpcron' == $config['batch_mode'])
		{	
			add_action('MailPress_schedule_delete_old_mails', 	array(__CLASS__, 'schedule'));
		}

		if (is_admin())
		{
		// for install
			register_activation_hook(plugin_basename(__FILE__), 	array(__CLASS__, 'install'));
			register_deactivation_hook(plugin_basename(__FILE__), array(__CLASS__, 'uninstall'));
		// for link on plugin page
			add_filter('plugin_action_links', 				array(__CLASS__, 'plugin_action_links'), 10, 2 );

		// for settings
			add_filter('MailPress_scripts', 				array(__CLASS__, 'scripts'), 8, 2);
			add_filter('MailPress_settings_tab', 			array(__CLASS__, 'settings_tab'), 20, 1);
		// for settings batches
			add_action('MailPress_settings_batches', 			array(__CLASS__, 'settings_batches'), 10);
		}	
	}

// process
	public static function process()
	{
		global $wpdb;

		$config = get_option(self::option_name);
		if (!$config) return false;

		MP_::no_abort_limit();

		$date = date('Y-m-d', current_time('timestamp') - ($config['days'] * 86400));

		$ids = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $wpdb->mp_mails WHERE status =  'sent' AND DATE(sent) < %s;", $date ) );

		foreach($ids as $id) MP_Mail::delete($id->id);

		self::schedule();
	}

// schedule
	public static function schedule()
	{
		$config = get_option(self::option_name);
		$now4cron = current_time('timestamp', 'gmt');

		if (!wp_next_scheduled( 'mp_process_delete_old_mails' )) 
			wp_schedule_single_event($now4cron + $config['every'] * 86400, 'mp_process_delete_old_mails');
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for install
	public static function install() 
	{
		self::uninstall();

		do_action('MailPress_schedule_delete_old_mails');
	}

	public static function uninstall() 
	{
		wp_clear_scheduled_hook('mp_process_delete_old_mails');
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'batches');
	}

// for settings
	public static function scripts($scripts, $screen) 
	{
		if ($screen != MailPress_page_settings) return $scripts;

		wp_register_script( 'mp-delete-old-mails', 	'/' . MP_PATH . 'mp-admin/js/settings_delete_old_mails.js', array(), false, 1);
		$scripts[] = 'mp-delete-old-mails';
		return $scripts;
	}

	public static function settings_tab($tabs)
	{
		$tabs['batches'] = __('Batches', MP_TXTDOM);
		return $tabs;
	}

	public static function settings_batches()
	{
		include (MP_ABSPATH . 'mp-admin/includes/settings/batches_delete_old_mails.form.php');
	}
}
new MailPress_delete_old_mails();
}