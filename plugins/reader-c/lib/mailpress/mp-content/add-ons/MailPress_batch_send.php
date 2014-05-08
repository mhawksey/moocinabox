<?php
if (class_exists('MailPress') && !class_exists('MailPress_batch_send'))
{
/*
Plugin Name: MailPress_batch_send 
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/batch_send/
Description: Mails : Send them in batch mode
Version: 5.4
*/

class MailPress_batch_send
{
	const meta_key = '_MailPress_batch_send';
	const option_name = 'MailPress_batch_send';
	const log_name = 'batch_send';

	const bt = 132;

	function __construct()
	{
// prepare mail
		add_filter('MailPress_status_mail', 		array(__CLASS__, 'status_mail'));

// for batch mode
		add_action('mp_process_batch_send', 		array(__CLASS__, 'process'));

		$config = get_option(self::option_name);
		if (!empty($config['batch_mode']) && 'wpcron' == $config['batch_mode'])
		{	
			add_action('MailPress_schedule_batch_send', 	array(__CLASS__, 'schedule'));
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
		// for settings batches
			add_action('MailPress_settings_batches', 	array(__CLASS__, 'settings_batches'), 10);
		// for settings logs
			add_action('MailPress_settings_logs', 	array(__CLASS__, 'settings_logs'), 20, 1);

			if ('wpcron' == $config['batch_mode'])
			{	
			// for autorefresh
				add_filter('MailPress_autorefresh_every', array(__CLASS__, 'autorefresh_every'), 8, 1);
				add_filter('MailPress_autorefresh_js',	array(__CLASS__, 'autorefresh_js'), 8, 1);
			}

		// for meta box in tracking page
			add_action('MailPress_tracking_add_meta_box',  array(__CLASS__, 'tracking_add_meta_box'), 8, 1);
		}

// for to mails column
		add_filter('MailPress_to_mails_column', 		array(__CLASS__, 'to_mails_column'), 8, 2);
	}

// prepare mail
	public static function status_mail()
	{
		return 'unsent';
	}

// process
	public static function process()
	{
		MP_::no_abort_limit();

		new MP_Batch();
	}

// schedule
	public static function schedule()
	{
		$config = get_option(self::option_name);
		$now4cron = current_time('timestamp', 'gmt');

		if (!wp_next_scheduled( 'mp_process_batch_send' )) 
			wp_schedule_single_event($now4cron + $config['every'], 'mp_process_batch_send');
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for install
	public static function install() 
	{
		self::uninstall();

		global $wpdb;
		$wpdb->query( $wpdb->prepare("UPDATE $wpdb->mp_mailmeta SET meta_key = %s WHERE meta_key = %s;", self::meta_key, 'batch_send') );

		$logs = get_option(MailPress::option_name_logs);
		if (!isset($logs[self::log_name]))
		{
			$logs[self::log_name] = MailPress::$default_option_logs;
			update_option(MailPress::option_name_logs, $logs );
		}

		do_action('MailPress_schedule_batch_send');
	}

	public static function uninstall() 
	{
		wp_clear_scheduled_hook('mp_process_batch_send');
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

		wp_register_script( 'mp-batchsend', 	'/' . MP_PATH . 'mp-admin/js/settings_batch_send.js', array(), false, 1);
		$scripts[] = 'mp-batchsend';
		return $scripts;
	}

	public static function settings_tab($tabs)
	{
		$tabs['batches'] = __('Batches', MP_TXTDOM);
		return $tabs;
	}

	public static function settings_batches()
	{
		include (MP_ABSPATH . 'mp-admin/includes/settings/batches_batch_send.form.php');
	}

	public static function settings_logs($logs)
	{
		MP_AdminPage::logs_sub_form(self::log_name, $logs, __('Batch', MP_TXTDOM));
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

// for meta box in tracking page
	public static function tracking_add_meta_box($screen)
	{
		if ('mailpress_tracking_m' != $screen) return;
		if (!isset($_GET['id'])) return;

		if (!MP_Mail_meta::get( $_GET['id'], self::meta_key)) return;

		add_meta_box('batchsenddiv', __('Batch current status', MP_TXTDOM), array(__CLASS__, 'meta_box_status'), $screen, 'normal', 'core');
	}

	public static function meta_box_status($mail)
	{ 
		$mailmeta = MP_Mail_meta::get( $mail->id , self::meta_key);
?>
		<table style='width:100%;'>
			<tr>
				<td><?php _e('Batch status', MP_TXTDOM); ?></td>
				<td colspan='3'><?php echo $mail->status; ?></td>
			</tr>
<?php 	if (is_array($mailmeta)) : ?>
			<tr><td colspan='4'>&#160;</td></tr>
			<tr>
				<td style='width:25%;text-align:center;'><?php _e('Total recipients', MP_TXTDOM); ?></td>
				<td style='width:25%;text-align:center;'><?php _e('Sent', MP_TXTDOM); ?></td>
				<td style='width:25%;text-align:center;'><?php _e('Try/Pass', MP_TXTDOM); ?></td>
				<td style='width:25%;text-align:center;'><?php _e('Processed', MP_TXTDOM); ?></td>
			</tr>
			<tr>
   				<td style='width:25%;text-align:center;'><?php echo $mailmeta['count']; ?></td>
				<td style='width:25%;text-align:center;'><?php echo $mailmeta['sent']; ?></td>
				<td style='width:25%;text-align:center;'><?php echo $mailmeta['try']; ?>/<?php echo $mailmeta['pass']; ?></td>
				<td style='width:25%;text-align:center;'><?php echo $mailmeta['processed']; ?></td>
			</tr>
<?php 		if (!empty($mailmeta['failed'])) : ?>
			<tr><td colspan='4'>&#160;</td></tr>
			<tr>
				<td><?php printf(__('Failed (%1$s)', MP_TXTDOM), count($mailmeta['failed'])); ?></td>
				<td colspan='3'><select><?php MP_AdminPage::select_option(array_keys($mailmeta['failed']),''); ?></select></td>
			</tr>
<?php 		endif; ?>

<?php 	endif; ?>
		</table>
<?php
	}

// for mails list
	public static function to_mails_column($to, $mail)
	{
		$mailmeta = MP_Mail_meta::get( $mail->id , self::meta_key);

		if ($mailmeta)
		{
			if ($mailmeta['sent'] != $mailmeta['count']) return sprintf( _n( _x('%1$s of %2$s sent', 'Singular', MP_TXTDOM), _x('%1$s of %2$s sent', 'Plural', MP_TXTDOM), $mailmeta['sent'] ), $mailmeta['sent'], $mailmeta['count'] );
		}
		else
		{
			if (self::status_mail() == $mail->status) return __('Pending...', MP_TXTDOM);
			else
			{
				if ('paused' == $mail->status) return __('Paused...', MP_TXTDOM);
			}
		}

		return $to;
	}
}
new MailPress_batch_send();
}