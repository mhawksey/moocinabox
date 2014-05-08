<?php

/*

Plugin Name: MailPress

Plugin URI: http://www.mailpress.org

Description: The WordPress mailing platform. <b>(do not use automatic upgrade!)</b>

Author: Andre Renaut

Author URI: http://www.mailpress.org

Requires at least: 3.4

Tested up to: 3.4

Version: 5.4

*/



/** for admin plugin pages */

define ('MailPress_page_mails', 	'mailpress_mails');

define ('MailPress_page_write', 	'mailpress_write');

define ('MailPress_page_edit',		MailPress_page_mails . '&file=write');

define ('MailPress_page_revision', 	MailPress_page_mails . '&file=revision');

define ('MailPress_page_themes', 	'mailpress_themes');

define ('MailPress_page_settings', 	'mailpress_settings');

define ('MailPress_page_users', 	'mailpress_users');

define ('MailPress_page_user',		MailPress_page_users . '&file=uzer');

define ('MailPress_page_addons', 	'mailpress_addons');



/** for admin plugin urls */

$mp_file = 'admin.php';

define ('MailPress_mails',		$mp_file . '?page=' 	. MailPress_page_mails);

define ('MailPress_write',		$mp_file . '?page=' 	. MailPress_page_write);

define ('MailPress_edit',		$mp_file . '?page=' 	. MailPress_page_edit);

define ('MailPress_revision', 	$mp_file . '?page=' 	. MailPress_page_revision);

define ('MailPress_themes',	$mp_file . '?page=' 	. MailPress_page_themes);

define ('MailPress_settings', 	'options-general.php' . '?page=' 	. MailPress_page_settings);

define ('MailPress_users',		$mp_file . '?page=' 	. MailPress_page_users);

define ('MailPress_user',		$mp_file . '?page=' 	. MailPress_page_user);

define ('MailPress_addons',	'plugins.php' . '?page=' 	. MailPress_page_addons);



/** for mysql */

global $wpdb;

$wpdb->mp_mails     = $wpdb->prefix . 'mailpress_mails';

$wpdb->mp_mailmeta  = $wpdb->prefix . 'mailpress_mailmeta';

$wpdb->mp_users     = $wpdb->prefix . 'mailpress_users';

$wpdb->mp_usermeta  = $wpdb->prefix . 'mailpress_usermeta';

$wpdb->mp_stats     = $wpdb->prefix . 'mailpress_stats';



class MailPress

{

	const option_name_general = 'MailPress_general';

	const option_name_test    = 'MailPress_test';

	const option_name_logs    = 'MailPress_logs';



	const option_name_subscriptions = 'MailPress_subscriptions';



	const option_name_smtp    = 'MailPress_smtp_config';



	public static $default_option_logs = array('level' => 8191, 'lognbr' => 10, 'lastpurge' => '');



	function __construct() 

	{

		require_once('mp-load.php');



		spl_autoload_register(array(__CLASS__, 'autoload'));						// for class loader



		if (defined('MP_DEBUG_LOG')) { global $mp_debug_log; $mp_debug_log = new MP_Log('debug_mailpress', array('option_name' => 'debug')); }



		add_action('plugins_loaded', 		array(__CLASS__, 'plugins_loaded'));		// for add-ons & gettext

		add_action('init', 				array(__CLASS__, 'init'));				// for init

		add_action('widgets_init', 		array(__CLASS__, 'widgets_init'));		// for widget

		add_action('shutdown', 			array(__CLASS__, 'shutdown'), 999);		// for shutdown



		add_action('mp_process_send_draft',	array(__CLASS__, 'process'));			// for scheduled draft



		if (is_admin())

		{

			register_activation_hook(plugin_basename(__FILE__), 	array(__CLASS__, 'install'));					// for install



			add_action('admin_init', 		array(__CLASS__, 'admin_init'));				// for admin css

			add_action('admin_menu', 		array(__CLASS__, 'admin_menu'));				// for menu



			$in_plugin_update_message = 'in_plugin_update_message-' . MP_FOLDER . '/' . __FILE__;				// for plugin

			add_action($in_plugin_update_message,		array(__CLASS__, 'in_plugin_update_message') ); 	//  * update message

			add_filter('plugin_action_links',		array(__CLASS__, 'plugin_action_links'), 10, 2 );	//  * page links

		}



		add_shortcode('mailpress', 		array(__CLASS__, 'shortcode'));			// for shortcode



		do_action('MailPress_init');

	}



	public static function autoload($class)

	{

		if (0 !== strpos($class, 'MP_')) return false;

		$file = MP_ABSPATH . "mp-includes/class/{$class}.class.php";

		if (is_file($file)) return require $file;

		return false;

	}



	public static function plugins_loaded() 

	{

		load_plugin_textdomain(MP_TXTDOM, false, MP_FOLDER . '/' . MP_CONTENT_FOLDER . '/' . 'languages');



		new MP_Addons();



		defined('MP_Action_url')  or define('MP_Action_url',   add_query_arg(apply_filters('MailPress_action_url_arg', array() ),  ( (defined('WP_SITEURL')) ? WP_SITEURL : site_url() ) . '/' . MP_PATH . 'mp-includes/action.php'  ) );

		defined('MP_Action_home') or define('MP_Action_home',  add_query_arg(apply_filters('MailPress_action_url_arg', array() ),  home_url() . '/' . MP_PATH . 'mp-includes/action.php'  ) );

	}



	public static function init()

	{

	// for roles & capabilities

		$role = get_role('administrator');

		foreach (self::capabilities() as $capability => $v) $role->add_cap($capability);

		do_action('MailPress_roles_and_capabilities');



	// for admin bar menu

		add_action('admin_bar_menu', array(__CLASS__, 'admin_bar_menu'), 71 );



	// for specific mailpress admin page

		if (is_admin() && self::get_admin_page()) self::admin_page();

	}



	public static function capabilities()										// for roles & capabilities

	{

		include (MP_ABSPATH . 'mp-admin/includes/capabilities/capabilities.php');

		return apply_filters('MailPress_capabilities', $capabilities);

	}



	public static function capability_groups()

	{

		include (MP_ABSPATH . 'mp-admin/includes/capabilities/capability_groups.php');

		return apply_filters('MailPress_capability_groups', $capability_groups);

	}



	public static function widgets_init() { register_widget('MP_Widget'); }					// for widget



	public static function shutdown() { if (defined('MP_DEBUG_LOG')) { global $mp_debug_log; $mp_debug_log->end(true); } }



	public static function process($args) { return MP_Mail_draft::send($args); } 			// for scheduled draft



////  ADMIN  ////



// for install



	public static function install() 

	{

		$min_ver_wp  = '3.4';

		include (MP_ABSPATH . 'mp-admin/includes/install/mailpress.php');

	}



// for admin stuff



	public static function admin_init()

	{

	// for global css

		$pathcss		= MP_ABSPATH . 'mp-admin/css/colors_' . get_user_option('admin_color') . '.css';

		$css_url		= '/' . MP_PATH . 'mp-admin/css/colors_' . get_user_option('admin_color') . '.css';

		$css_url_default= '/' . MP_PATH . 'mp-admin/css/colors_fresh.css';

		$css_url		= (is_file($pathcss)) ? $css_url : $css_url_default;

		wp_register_style ( 'MailPress_colors', 	$css_url);

		wp_enqueue_style  ( 'MailPress_colors' );



	// for dashboard

		global $mp_general;

		if ( isset($mp_general['dashboard']) && current_user_can('MailPress_edit_dashboard') )

			add_filter('wp_dashboard_setup', 	array(__CLASS__, 'wp_dashboard_setup'));

	}



	public static function wp_dashboard_setup() { new MP_Dashboard_widgets(); }



// for menus



	public static function admin_menu() { new MP_Admin_Menu(); }



	public static function admin_bar_menu($wp_admin_bar) { new MP_Admin_Bar_Menu($wp_admin_bar); }



// for admin page



	public static function get_admin_page()

	{

		return ( !isset($_GET['page']) || strpos($_GET['page'], 'mailpress') !== 0 ) ? false : ( $_GET['page'] . ( (isset($_GET['file'])) ? '&file=' . $_GET['file'] : '' ) );

	}



	public static function admin_page()

	{

		$admin_page = self::get_admin_page();



		$hub = array (	MailPress_page_mails 	=> 'mails', 

					MailPress_page_write 	=> 'write', 

					MailPress_page_edit 	=> 'write', 

					MailPress_page_revision => 'revision', 

					MailPress_page_themes	=> 'themes', 

					MailPress_page_settings => 'settings', 

					MailPress_page_users 	=> 'users', 

					MailPress_page_user 	=> 'user',

					MailPress_page_addons	=> 'addons'

		);

		$hub  = apply_filters('MailPress_load_admin_page', $hub);

		if (!isset($hub[$admin_page])) return;



		$file = MP_ABSPATH . 'mp-admin/' . $hub[$admin_page] . '.php';

		if (!is_file($file)) return;



		require_once($file);

		if (!class_exists('MP_AdminPage')) return;



		new MP_AdminPage();

	}



// for plugin



	public static function in_plugin_update_message()

	{

?>

		<p style="color:red;margin:3px 0 0 0;border-top:1px solid #ddd;padding-top:3px">

			<?php printf(__( 'IMPORTANT: <a href="%$1s">Read this before attempting to update MailPress</a>', MP_TXTDOM), 'http://blog.mailpress.org/tutorials/'); ?>

		</p>

<?php

	}



	public static function plugin_action_links($links, $file)

	{

		if (plugin_basename(__FILE__) != $file) return $links;



		$addons_link = "<a href='" . MailPress_addons . "' title='" . __('Manage MailPress add-ons', MP_TXTDOM) . "'>" . __('Add-ons', MP_TXTDOM) . '</a>';

		array_unshift ($links, $addons_link);



		return self::plugin_links($links, $file, plugin_basename(__FILE__), '0');

	}



	public static function plugin_links($links, $file, $basename, $tab)

	{

		if ($basename != $file) return $links;



		$settings_link = "<a href='" . MailPress_settings . "#fragment-$tab'>" . __('Settings') . '</a>';

		array_unshift ($links, $settings_link);

		return $links;

	}



////	Subscription form	////



	public static function shortcode($options=false)

	{

		$options['widget_id'] = 'sc';



		ob_start();

			self::form($options);

			$x = ob_get_contents();

		ob_end_clean();

		return $x; 

	}



	public static function form($options = array())

	{

		static $_widget_id = 0;



		$options['widget_id'] = (isset($options['widget_id'])) ?  $options['widget_id'] . '_' . $_widget_id : 'mf_' . $_widget_id;



		MP_Widget::widget_form($options);



		$_widget_id++;

	}



////	THE MAIL



	public static function mail($args)

	{

		$x = new MP_Mail();

		return $x->send($args);

	}

}

new MailPress();