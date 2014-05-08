<?php
if (class_exists('MailPress') && !class_exists('MailPress_bounce_handling'))
{
/*
Plugin Name: MailPress_bounce_handling
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/bounce_handling/
Description: Users : bounce management (based on <a href='http://en.wikipedia.org/wiki/VERP'>VERP</a>)
Version: 5.4
*/

class MailPress_bounce_handling
{
	const meta_key     	= '_MailPress_bounce_handling';
	const option_name 	= 'MailPress_bounce_handling';
	const option_name_pop3 	= 'MailPress_connection_pop3';
	const log_name = 'bounce_handling';

	const bt = 132;

	function __construct()
	{
// prepare mail
		add_filter('MailPress_swift_message_headers',  	array(__CLASS__, 'swift_message_headers'), 8, 2);

// for batch mode
		add_action('mp_process_bounce_handling', 		array(__CLASS__, 'process'));

		$config = get_option(self::option_name);
		if ('wpcron' == $config['batch_mode'])
		{	
			add_action('MailPress_schedule_bounce_handling', 	array(__CLASS__, 'schedule'));
		}

		if (is_admin())
		{
		// for install
			register_activation_hook(plugin_basename(__FILE__), 	array(__CLASS__, 'install'));
			register_deactivation_hook(plugin_basename(__FILE__), array(__CLASS__, 'uninstall'));
		// for link on plugin page
			add_filter('plugin_action_links', 		array(__CLASS__, 'plugin_action_links'), 10, 2 );

		// for settings
			add_filter('MailPress_scripts', 		array(__CLASS__, 'scripts'), 8, 2);
			add_filter('MailPress_settings_tab', 	array(__CLASS__, 'settings_tab'), 20, 1);
			add_filter('MailPress_settings_tab_connection', 	array(__CLASS__, 'settings_tab_connection'), 95, 1);
		// for settings batches
			add_action('MailPress_settings_batches', 	array(__CLASS__, 'settings_batches'), 20);
		// for settings logs
			add_action('MailPress_settings_logs', 	array(__CLASS__, 'settings_logs'), 30, 1);


			if ('wpcron' == $config['batch_mode'])
			{	
			// for autorefresh
				add_filter('MailPress_autorefresh_every', array(__CLASS__, 'autorefresh_every'), 8, 1);
				add_filter('MailPress_autorefresh_js',	array(__CLASS__, 'autorefresh_js'), 8, 1);
			}

		// for users list
			add_action('MailPress_get_icon_users', 	array(__CLASS__, 'get_icon_users'), 8, 1);
		// for meta box in user page
			add_action('MailPress_add_meta_boxes_user',array(__CLASS__, 'meta_boxes_user'), 8, 2);
		}

// for mails list
		add_filter('MailPress_mails_columns', 		array(__CLASS__, 'mails_columns'), 10, 1);
		add_action('MailPress_mails_get_row',  		array(__CLASS__, 'mails_get_row'), 10, 3);
// view bounce
		add_action('mp_action_view_bounce', 		array(__CLASS__, 'mp_action_view_bounce')); 
	}

// prepare mail
	public static function swift_message_headers($message, $row)
	{
		$config = get_option(self::option_name);

		if (!is_email($config['Return-Path'])) return $message;

		$prefix = substr($config['Return-Path'], 0, strpos($config['Return-Path'], '@'));
		$domain = substr($config['Return-Path'], strpos($config['Return-Path'], '@') + 1 );

		$ReturnPath = $prefix . '+' . $row->id . '+' . '{{_user_id}}' . '@' . $domain;
		if (isset($row->mp_user_id)) $ReturnPath = str_replace('{{_user_id}}', $row->mp_user_id, $ReturnPath);

		$message->setReturnPath($ReturnPath);

		return $message;
	}

// process
	public static function process()
	{
		MP_::no_abort_limit();

		new MP_Bounce();
	}

// schedule
	public static function schedule()
	{
		$config = get_option(self::option_name);
		$now4cron = current_time('timestamp', 'gmt');

		if (!wp_next_scheduled( 'mp_process_bounce_handling' )) 
			wp_schedule_single_event($now4cron + $config['every'], 'mp_process_bounce_handling');
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// install
	public static function install() 
	{
		self::uninstall();

		$pop3 = get_option(self::option_name_pop3);
		if (!$pop3)
		{
			$pop3 = get_option(self::option_name);
			if ($pop3) update_option(self::option_name_pop3, $pop3);
		}

		$logs = get_option(MailPress::option_name_logs);
		if (!isset($logs[self::log_name]))
		{
			$logs[self::log_name] = MailPress::$default_option_logs;
			update_option(MailPress::option_name_logs, $logs );
		}

		do_action('MailPress_schedule_bounce_handling');
	}

	public static function uninstall() 
	{
		wp_clear_scheduled_hook('mp_process_bounce_handling');
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

		wp_register_script( 'mp-bounce-handling', 	'/' . MP_PATH . 'mp-admin/js/settings_bounce_handling.js', array(), false, 1);
		$scripts[] = 'mp-bounce-handling';
		return $scripts;
	}

	public static function settings_tab($tabs)
	{
		$tabs['batches'] = __('Batches', MP_TXTDOM);
		return $tabs;
	}

	public static function settings_tab_connection($tabs)
	{
		$tabs['connection_pop3'] = __('POP3', MP_TXTDOM);
		return $tabs;
	}

	public static function settings_batches()
	{
		include (MP_ABSPATH . 'mp-admin/includes/settings/batches_bounce_handling.form.php');
	}

	public static function settings_logs($logs)
	{
		MP_AdminPage::logs_sub_form(self::log_name, $logs, __('Bounce', MP_TXTDOM));
	}

	public static function autorefresh_every($every = 30)
	{
		$config = get_option(self::option_name);
		if (!$config) return $every;
		if ($every < $config['every']) return $every;
		return $config['every'];
	}

	public static function autorefresh_js($scripts)
	{
		return MP_AutoRefresh_js::register_scripts($scripts);
	}

// for users list
	public static function get_icon_users($mp_user)
	{
		if ('bounced' != $mp_user->status) return;
?>
			<span class='icon bounce_handling' title="<?php _e('Bounced', MP_TXTDOM); ?>"></span>
<?php
	}

// for user page
	public static function meta_boxes_user($mp_user_id, $screen)
	{
		$usermeta = MP_User_meta::get($mp_user_id, self::meta_key);
		if (!$usermeta) return;

		add_meta_box('bouncehandlingdiv', __('Bounces', MP_TXTDOM), array(__CLASS__, 'meta_box_user'), $screen, 'side', 'core');
	}

	public static function meta_box_user($mp_user)
	{
		$usermeta = MP_User_meta::get($mp_user->id, self::meta_key);
		if (!$usermeta) return;

		global $wpdb;
		echo '<b>' . __('Bounces', MP_TXTDOM) . '</b> : &#160;' . $usermeta['bounce'] . '<br />';
		foreach($usermeta['bounces'] as $mail_id => $messages)
		{
			foreach($messages as $k => $message)
			{
				echo '<br />';
				$subject = $wpdb->get_var("SELECT subject FROM $wpdb->mp_mails WHERE id = " . $mail_id . ';');
				$subject = ($subject) ? $subject : __('(deleted)', MP_TXTDOM);

				$view_url		= esc_url(add_query_arg( array('action' => 'view_bounce', 'user_id' => $mp_user->id, 'mail_id' => $mail_id, 'id' => $k, 'preview_iframe' => 1, 'TB_iframe' => 'true'), MP_Action_url ));
				$actions['view'] = "<a href='$view_url' class='thickbox thickbox-preview'  title='" . __('View', MP_TXTDOM) . "'>" . $subject . '</a>';

				echo '(' . $mail_id . ') ' . $actions['view'];
			}
		}
	}

// for mails list
	public static function mails_columns($x)
	{
		$date = array_pop($x);
		$x['bounce_handling']	=  __('Bounce rate', MP_TXTDOM);
		$x['date']			= $date;
		return $x;
	}

	public static function mails_get_row($column_name, $mail, $url_parms)
	{
		global $wpdb;
		switch ($column_name)
		{
			case 'bounce_handling' :
				if (is_email($mail->toemail)) $total = 1;
				elseif(is_serialized($mail->toemail)) $total = count(unserialize($mail->toemail));
				else return;

				$result = MP_Mail_meta::get($mail->id, self::meta_key);
				if ($result) if ($total > 0) printf("%01.2f %%", 100 * $result/$total );
			break;
		}
	}

// view bounce
	public static function mp_action_view_bounce()
	{
		$mp_user_id = $_GET['user_id'];
		$mail_id    = $_GET['mail_id'];
		$bounce_id  = $_GET['id'];

		$usermeta = MP_User_meta::get($mp_user_id, self::meta_key);
		if (!$usermeta) return;

		$plaintext = $usermeta['bounces'][$mail_id][$bounce_id]['message'];

		include(MP_ABSPATH . 'mp-includes/html/plaintext.php');
	}
}
new MailPress_bounce_handling();
}