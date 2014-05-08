<?php
if (class_exists('MailPress') && !class_exists('MailPress_import') && (is_admin()) )
{
/*
Plugin Name: MailPress_import
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/import/
Description: Users : Import/Export mp users from/into files
Version: 5.4
*/

// 3.

/** for admin plugin pages */
define ('MailPress_page_import', 'mailpress_import');

/** for admin plugin urls */
$mp_file = 'admin.php';
define ('MailPress_import', $mp_file . '?page=' . MailPress_page_import);

class MailPress_import
{
	const log_name = 'import';

	function __construct()
	{
// for wp admin
		if (is_admin())
		{
		// for install
			register_activation_hook(plugin_basename(__FILE__), 	array(__CLASS__, 'install'));
		// for link on plugin page
			add_filter('plugin_action_links', 		array(__CLASS__, 'plugin_action_links'), 10, 2 );
		// for role & capabilities
			add_filter('MailPress_capabilities', 	array(__CLASS__, 'capabilities'), 1, 1);
		// for settings
			add_action('MailPress_settings_logs', 	array(__CLASS__, 'settings_logs'), 40, 1);

		// for load admin page
			add_filter('MailPress_load_admin_page', 	array(__CLASS__, 'load_admin_page'), 10, 1);
		}
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// install
	public static function install() 
	{
		$logs = get_option(MailPress::option_name_logs);
		if (!isset($logs[self::log_name]))
		{
			$logs[self::log_name] = MailPress::$default_option_logs;
			update_option(MailPress::option_name_logs, $logs );
		}
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'logs');
	}

// for role & capabilities
	public static function capabilities($capabilities) 
	{
		$capabilities['MailPress_import'] = array(	'name'  => __('Import', MP_TXTDOM), 
								'group' => 'admin', 
								'menu'  => 65, 
								'parent'		=> false, 
								'page_title'	=> __('MailPress Import/Export', MP_TXTDOM), 
								'menu_title'   	=> '&#160;' . __('Import/Export', MP_TXTDOM), 
								'page'  		=> MailPress_page_import, 
								'func'  		=> array('MP_AdminPage', 'body')
							);
		return $capabilities;
	}

// for settings
	public static function settings_logs($logs)
	{
		MP_AdminPage::logs_sub_form(self::log_name, $logs, __('Import/Export', MP_TXTDOM));
	}

// for load admin page
	public static function load_admin_page($hub)
	{
		$hub[MailPress_page_import] = 'import';
		return $hub;
	}
}
new MailPress_import();
}