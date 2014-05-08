<?php

if (class_exists('MailPress') && !class_exists('MailPress_newsletter') )

{

/*

Plugin Name: MailPress_newsletter

Plugin URI: http://blog.mailpress.org/tutorials/add-ons/newsletter/

Description: Newsletters : for posts

Version: 5.4

*/



class MailPress_newsletter

{

	const meta_key = '_MailPress_newsletter';

	const log_name = 'newsletter';



	function __construct()

	{

// for wordpress hooks

// for plugin

		add_action('init', 	array(__CLASS__, 'init'), 99);



// register form

		add_action('user_register', 			array(__CLASS__, 'user_register'), 10, 1);

		add_action('MailPress_register_form', 	array(__CLASS__, 'register_form'), 10);



// for shortcode

		add_filter('MailPress_form_defaults', 	array(__CLASS__, 'form_defaults'), 8, 1);

		add_filter('MailPress_form_options', 		array(__CLASS__, 'form_options'), 8, 1);

		add_filter('MailPress_form_submit', 		array(__CLASS__, 'form_submit'), 8, 2);

		add_action('MailPress_form', 		  	array(__CLASS__, 'form'), 1, 2);



// for newsletter

		add_action('MailPress_register_newsletter',array(__CLASS__, 'register'), 1);



// for scheduling and processing newsletters

		add_action('mp_schedule_newsletters', 	array(__CLASS__, 'schedule'), 8, 1);

		add_action('mp_process_newsletter', 		array(__CLASS__, 'process'));

		add_action('mp_process_post_newsletter', 	array(__CLASS__, 'process'));



// for sending mails

		add_filter('MailPress_mailinglists_optgroup', 	array(__CLASS__, 'mailinglists_optgroup'), 50, 2);

		add_filter('MailPress_mailinglists', 			array(__CLASS__, 'mailinglists'), 50, 1);

		add_filter('MailPress_query_mailinglist', 		array(__CLASS__, 'query_mailinglist'), 50, 2);



// for sync wordpress user

		add_filter('MailPress_has_subscriptions', array(__CLASS__, 'has_subscriptions'), 8, 2);

		add_action('MailPress_sync_subscriptions',array(__CLASS__, 'sync_subscriptions'), 8, 2);	



// for wp admin

		if (is_admin())

		{

		// install

			register_activation_hook(  plugin_basename(__FILE__), array(__CLASS__, 'install'));

			register_deactivation_hook(plugin_basename(__FILE__), array(__CLASS__, 'uninstall'));

		// for link on plugin page

			add_filter('plugin_action_links', 			array(__CLASS__, 'plugin_action_links'), 10, 2 );

		// for role & capabilities

			add_filter('MailPress_capabilities', 		array(__CLASS__, 'capabilities'), 1, 1);

		// for settings

			add_filter('MailPress_settings_tab', 		array(__CLASS__, 'settings_tab'), 10, 1);

		// for settings general

			add_action('MailPress_settings_general', 		array(__CLASS__, 'settings_general'), 30);

		// for settings subscriptions

			add_action('MailPress_settings_subscriptions', 	array(__CLASS__, 'settings_subscriptions'), 30);

		// for settings logs

			add_action('MailPress_settings_logs', 		array(__CLASS__, 'settings_logs'), 50);

		// for meta box in user page

			if (current_user_can('MailPress_manage_newsletters'))

			{

				add_action('MailPress_update_meta_boxes_user', 	array(__CLASS__, 'update_meta_boxes_user'));

				add_action('MailPress_add_meta_boxes_user', 	array(__CLASS__, 'add_meta_boxes_user'), 20, 2);

			}

		// for meta box in write post

			if (current_user_can('MailPress_test_newsletters'))

				add_action('do_meta_boxes', 				array(__CLASS__, 'add_meta_boxes_post'), 8, 3);

		}



// for mp_users list

		add_action('MailPress_users_restrict', 	array(__CLASS__, 'users_restrict'), 9, 1);

		add_filter('MailPress_users_columns', 	array(__CLASS__, 'users_columns'), 10, 1);

		add_action('MailPress_users_get_list', 	array(__CLASS__, 'users_get_list'), 10, 2);

		add_action('MailPress_users_get_row', 	array(__CLASS__, 'users_get_row'), 10, 3);



// for ajax

		add_action('mp_action_mp_meta_box_post_test', 	array(__CLASS__, 'mp_action_mp_meta_box_post_test'));

	}



//// Subscriptions ////



	public static function get_checklist($mp_user_id = false, $args = '') 

	{

		global $mp_subscriptions;

		if (!isset($mp_subscriptions['newsletters'])) return false;

		if (empty( $mp_subscriptions['newsletters'])) return false;



		$checklist = '';

		$defaults = array (	'name' 	=> 'keep_newsletters', 

						'admin' 	=> 0, 

						'selected' 	=> false, 

						'type'	=> 'checkbox',

						'show_option_all'  => false, 

   						'htmlstart'=> '', 

						'htmlmiddle'=> '&#160;&#160;', 

						'htmlend'	=> "<br />\n"

					);

		$r = wp_parse_args( $args, $defaults );

		extract( $r );



		$lib_nl = ($admin) ? 'admin' : 'blog';



		if ($mp_user_id) 	$in = MP_Newsletter::get_object_terms($mp_user_id);

		else			$in = MP_Newsletter::get_defaults();



		global $mp_registered_newsletters;

		$nls = MP_Newsletter::get_active();



		foreach ($nls as $k => $v)

		{

			if ($mp_registered_newsletters[$k]['descriptions'][$lib_nl])

				switch ($type)

				{

					case 'checkbox' :

						$checked = checked(isset($in[$k]), true, false);

						$_type   = 'checkbox';



						$v		 = $mp_registered_newsletters[$k]['descriptions'][$lib_nl];



						$tag 		 = "<input type='$_type' id='{$name}_{$k}' name='{$name}[{$k}]'$checked />";

						$htmlstart2  = str_replace('{{id}}', "{$name}_{$k}", $htmlstart);

						$htmlmiddle2 = $htmlmiddle . str_replace('&#160;', '', $v);

						$htmlend2    = $htmlend;



						$checklist .= "$htmlstart2$tag$htmlmiddle2$htmlend2";

					break;

					case 'select' :

						if ($show_option_all)

						{

							$checklist .= "<option value=''>" . $show_option_all . "</option>\n";

							$show_option_all = false;

						}

						$sel = ($k == $selected) ? " selected='selected'" : '';

						$checklist .= "<option value=\"$k\"$sel>{$mp_registered_newsletters[$k]['descriptions'][$lib_nl]}</option>\n";

					break;

				}

		}

		if ('select' == $type) $checklist = "\n{$htmlstart}\n<select name='{$name}'>\n{$checklist}</select>\n{$htmlend}\n";



		return $checklist;

	}



	public static function update_checklist($mp_user_id, $name = 'keep_newsletters') 

	{

		MP_Newsletter::set_object_terms( $mp_user_id, $_POST[$name] );

	}



//// Plugin ////



	public static function init()

	{

		do_action('MailPress_register_newsletter');

		do_action('MailPress_registered_newsletters');



		new MP_Newsletter_schedulers();

	}



//// Register form ////



	public static function user_register($wp_user_id)

	{

		$user 	= get_userdata($wp_user_id);

		$email 	= $user->user_email;

		$mp_user_id	= MP_User::get_id_by_email($email);

                

		if(!isset($_POST['keep_newsletters'])) $_POST['keep_newsletters'] = MP_Newsletter::get_defaults();



		self::update_checklist($mp_user_id);

	}



	public static function register_form()

	{

		$checklist_newsletters = self::get_checklist();

		if (empty($checklist_newsletters)) return;

?>

	<br />

	<p>

		<label>

			<?php _e('Newsletters', MP_TXTDOM); ?>

			<br />

			<span style='color:#777;font-weight:normal;'>

				<?php echo $checklist_newsletters; ?>

			</span>

		</label>

	</p>

<?php 

	}



//// Shortcode ////



	public static function form_defaults($x) { $x['newsletter'] = false; return $x; }



	public static function form_options($x)  { return $x; }



	public static function form_submit($shortcode_message, $email)  

	{ 

		if (!isset($_POST['newsletter'])) 	return $shortcode_message;

		if (!$_POST['newsletter']) 		return $shortcode_message;

		$shortcode = 'shortcode_newsletters';



		$mp_user_id = MP_User::get_id_by_email($email);

		$_POST[$shortcode] = MP_Newsletter::get_object_terms($mp_user_id);



		$_POST[$shortcode] = array_flip(array_map(trim, explode(',', $_POST['newsletter'])));



		self::update_checklist($mp_user_id, $shortcode);



		return $shortcode_message . __('<br />Newsletters added', MP_TXTDOM);

	}



	public static function form($email, $options)  

	{

		if (!$options['newsletter']) return;



		global $mp_registered_newsletters;

		$x = array();

		foreach (array_map(trim, explode(',', $options['newsletter'])) as $k => $v) if (isset($mp_registered_newsletters[$v]) && $mp_registered_newsletters[$v]['allowed']) $x[] = $v;

		if (empty($x)) return;



		echo "<input type='hidden' name='newsletter' value='" . join(', ', $x) . "' />\n";

	}



////  Newsletter  ////



	public static function register() 

	{

		$args = array(	'root' 		=> MP_CONTENT_DIR . 'advanced/newsletters/post',

					'root_filter' 	=> 'MailPress_advanced_newsletters_root',

					'files'		=> array('post', 'daily', 'weekly', 'monthly'),



					'post_type'	=> 'post',

		);



		MP_Newsletter::register_files($args);

	}



//// Scheduling & Processing  ////



	public static function schedule($args = array())

	{

		extract($args);



		self::unschedule_hook('mp_process_newsletter');

		MP_Newsletter_schedulers::schedule($event);

	}



	public static function process($args)

	{

		extract($args);



		new MP_Newsletter_processors();



		MP_Newsletter_processors::process($newsletter);

	}



//// Sending Mails ////



	public static function mailinglists_optgroup( $label, $optgroup ) 

	{

		if (__CLASS__ == $optgroup) return __('Newsletters', MP_TXTDOM);

		return $label;

	}



	public static function mailinglists( $draft_dest = array() )

	{

		$x = MP_Newsletter::get_active();

		foreach ($x as $k => $v) $draft_dest[__CLASS__ . '~' . $k] = $v;

		return $draft_dest;

	}



	public static function query_mailinglist( $query, $draft_toemail )

	{

		if ($query) return $query;



		$id = str_replace(__CLASS__ . '~', '', $draft_toemail, $count);

		if (0 == $count) return $query;

		if (empty($id)) return $query;



		global $mp_registered_newsletters;

		if (!isset($mp_registered_newsletters[$id])) return $query;



		$in = ($mp_registered_newsletters[$id]['default']) ? 'NOT' : '';



		return MP_Newsletter::get_query_newsletter($id, $in);

	}



// Sync wordpress user



	public static function has_subscriptions($has, $mp_user_id)

	{

		$x = MP_Newsletter::get_object_terms($mp_user_id);



		if (empty($x)) return $has;

		return true;

	}



	public static function sync_subscriptions($oldid, $newid)

	{

		$old = MP_Newsletter::get_object_terms($oldid);

		if (empty($old)) return;

		$new = MP_Newsletter::get_object_terms($newid);



		MP_Newsletter::set_object_terms($newid, array_merge($old, $new));

	}



////  ADMIN  ////

////  ADMIN  ////

////  ADMIN  ////

////  ADMIN  ////



// install

	public static function install() 

	{

		self::uninstall();



		$logs = get_option(MailPress::option_name_logs);

		if (!isset($logs[self::log_name]))

		{

			$logs[self::log_name] = MailPress::$default_option_logs;

			update_option(MailPress::option_name_logs, $logs );

		}



		include (MP_ABSPATH . 'mp-admin/includes/install/newsletter.php');



		$now4cron = current_time('timestamp', 'gmt');

		wp_schedule_single_event( $now4cron - 1, 'mp_schedule_newsletters', array('args' => array('event' => '** Install **' )));



		$twicedaily = gmmktime(0, 0, 0, gmdate('n', $now4cron), gmdate('j', $now4cron), gmdate('Y', $now4cron)) - get_option('gmt_offset') * 3600;

		wp_schedule_event( $twicedaily, 'twicedaily', 'mp_schedule_newsletters', array('args' => array('event' => '** Twice daily **' )));

	}



	public static function uninstall() 

	{

		self::unschedule_hook('mp_process_newsletter');

		self::unschedule_hook('mp_schedule_newsletters');

	}



	public static function unschedule_hook($hook)

	{

		$crons = _get_cron_array();

		foreach($crons as $timestamp => $v)

		{

			unset( $crons[$timestamp][$hook] );

			if ( empty($crons[$timestamp]) ) unset( $crons[$timestamp] );

		}

		_set_cron_array( $crons );

	}



// for link on plugin page

	public static function plugin_action_links($links, $file)

	{

		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'subscriptions');

	}



// for role & capabilities

	public static function capabilities($capabilities) 

	{

		$capabilities['MailPress_manage_newsletters'] = array(	'name'  => __('Newsletters', MP_TXTDOM), 

												'group' => 'users'

										);

		$capabilities['MailPress_test_newsletters'] = array(	'name'  => __('Post test', MP_TXTDOM), 

												'group' => 'admin'

										);

		return $capabilities;

	}



// for settings

	public static function settings_tab($tabs)

	{

		$tabs['subscriptions'] = __('Subscriptions', MP_TXTDOM);

		return $tabs;

	}



// for settings general

	public static function settings_general()

	{

		global $mp_general;

?>

			<tr><th></th><td></td></tr>

			<tr>

				<th style='padding:0;'><strong><?php _e('Newsletters', MP_TXTDOM); ?></strong></th>

				<td style='padding:0;'></td>

			</tr>

			<tr valign='top' class='mp_sep'>

				<th scope='row'><?php _e('Newsletters show at most', MP_TXTDOM); ?></th>

				<td style='padding:0;'>

					<select name='general[post_limits]'>

<option value="0">&#160;</option>

<?php MP_AdminPage::select_number(1, 500, (isset($mp_general['post_limits'])) ? $mp_general['post_limits'] : ''); ?>

					</select>

					&#160;<?php _e('posts <i>(blank = WordPress Reading setting)</i>', MP_TXTDOM); ?>

				</td>

			</tr>

<?php

	}



// for settings subscriptions

	public static function settings_subscriptions()

	{

		include (MP_ABSPATH . 'mp-admin/includes/settings/subscriptions_newsletter.form.php');

	}



// for settings logs

	public static function settings_logs($logs)

	{

		MP_AdminPage::logs_sub_form(self::log_name, $logs, __('Newsletter', MP_TXTDOM));

	}



// for meta box in user page

	public static function update_meta_boxes_user() 

	{

		if (!isset($_POST['id'])) return;

		if (!isset($_POST['keep_newsletters'])) $_POST['keep_newsletters'] = array();



		self::update_checklist($_POST['id']);

	}



	public static function add_meta_boxes_user($mp_user_id, $screen)

	{

		add_meta_box('newsletterdiv', __('Newsletters', MP_TXTDOM), array(__CLASS__, 'meta_box'), $screen, 'normal', 'core');

	}



	public static function meta_box($mp_user)

	{ 

		$check_newsletters = self::get_checklist($mp_user->id, array('admin' => true));

		echo ($check_newsletters) ? $check_newsletters : __('Nothing to subscribe for ...', MP_TXTDOM) . " <a href='" . MailPress_settings . "#fragment-subscriptions'>" . __('Settings') . '</a>';

	}



// for meta box in write post  ////

	public static function add_meta_boxes_post($page, $type, $post)

	{

		if ('post' != $page) return;

		if ('side' != $type) return;



		wp_register_script('mp-meta-box-post-test', 	'/' . MP_PATH . 'mp-includes/js/meta_boxes/post/test.js', array('wp-ajax-response'), false, 1);

		wp_localize_script('mp-meta-box-post-test', 	'mpMeta_box_postL10n', array( 

			'url' => MP_Action_url

		) );

		wp_enqueue_script('mp-meta-box-post-test');



		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);

		wp_enqueue_script('mp-thickbox');



		add_meta_box('MailPress_post_test_div', __('MailPress test', MP_TXTDOM), array(__CLASS__, 'meta_box_post_test'), 'post', 'side', 'core');

	}



	public static function meta_box_post_test($post) 

	{

		include (MP_ABSPATH . 'mp-includes/meta_boxes/post/test.php');

	}





//// Mp_users list  ////



	public static function users_restrict($url_parms)

	{

		$x = (isset($url_parms['newsletter'])) ? $url_parms['newsletter'] : '';

		$dropdown_options = array('show_option_all' => __('View all newsletters', MP_TXTDOM), 'selected' => $x );

		self::dropdown($dropdown_options);

	}



	public static function users_columns($x)

	{

		$date = array_pop($x);

		$x['newsletters']=  __('Newsletters', MP_TXTDOM);

		$x['date']		= $date;

		return $x;

	}



	public static function users_get_list($array, $url_parms)

	{

		if (!isset($url_parms['newsletter']) || empty($url_parms['newsletter'])) return $array;



		global $mp_registered_newsletters;

		global $wpdb;



		if (!isset($mp_registered_newsletters[$url_parms['newsletter']])) return $array;



		list($where, $tables) = $array;



		$newsletter = $mp_registered_newsletters[$url_parms['newsletter']];

		$in 	= ($newsletter['default']) ? 'NOT' : '';



		$where .= " AND $in EXISTS 	(

					SELECT DISTINCT z.mp_user_id

					FROM 	$wpdb->mp_usermeta z

					WHERE z.meta_key = '" . self::meta_key . "'

					AND 	z.meta_value = '" . $newsletter['id'] . "'

					AND 	z.mp_user_id = a.id

				) ";



		return array($where, $tables, true);

	}



	public static function users_get_row($column_name, $mp_user, $url_parms)

	{

		if ('newsletters' != $column_name) return;

		$out = array();



		$newsletters = MP_Newsletter::get_object_terms($mp_user->id);

		if (!empty($newsletters))

		{

			foreach($newsletters as $k => $v)

			{

				$out[] = "<a href='" . MailPress_users . "&amp;newsletter=$k'>$v</a>";

			}



			echo join( ', ', $out );

			return;

		}

		_e('None ', MP_TXTDOM);

	}



	public static function dropdown($args = '')

	{

		$defaults = array('class'		=> 'postform',

					'echo' 		=> 1,

					'htmlid'		=> 'newsletter_dropdown',

					'name' 		=> 'newsletter',

					'selected' 		=> 0

					);



		$r = wp_parse_args( $args, $defaults );

		extract( $r );



		$x = MP_Newsletter::get_active();



		$output = '';

		if ( ! empty($x) )

		{

			if ( $show_option_all ) $list[0] = $show_option_all;

			foreach($x as $k => $v) $list[$k] = $v;



			$htmlid = ($htmlid === true) ? "id='$name'" : "id='$htmlid'" ;

			$output = "<select name='$name' $htmlid class='$class'>\n";

			$output .= MP_::select_option($list, $selected, false);

			$output .= "</select>\n";

		}



		if ( $echo )	echo $output;



		return $output;

	}



// for ajax

	public static function mp_action_mp_meta_box_post_test() 

	{

		global $mp_registered_newsletters;



		$post_id = $_POST['post_id'];

		if (empty($post_id))			return new WP_Error( 'post', __('post not saved', MP_TXTDOM) );

		$post = &get_post($post_id);

		if (!$post)					return new WP_Error( 'post', __('post not saved', MP_TXTDOM) );



		$newsletter = MP_Newsletter::get($_POST['newsletter']);

		if (!$newsletter)				return new WP_Error( 'newsletter', __('unknown newsletter', MP_TXTDOM) );



		$theme = $_POST['theme'];

		if (empty($theme) && isset($newsletter['mail']['Theme'])) $theme = $newsletter['mail']['Theme'];



		update_user_meta(MP_WP_User::get_id(), "_MailPress_post_$post_id", array('toemail' => $_POST['toemail'], 'theme' => $theme, 'newsletter' => $_POST['newsletter']));	



		$newsletter['mail']['Theme'] 		= $theme;

		$newsletter['mail']['subject']	= __('(Test)', MP_TXTDOM) . ' ' . $newsletter['mail']['subject'];

		$newsletter['mail']['the_title'] 	= apply_filters('the_title', $post->post_title );



		$newsletter['query_posts'] 	= array( 'p'	=>	$post_id );



		$mail			= new stdClass();

		$mail->id		= MP_Mail::get_id('send_post_ajax');

		$mail->toemail 	= $_POST['toemail'];



		$rc = MP_Newsletter::send($newsletter, false, $mail);



		if (!$rc) MP_Mail::delete($mail->id);



		$x = new WP_Ajax_Response( array	(

						'what' => 'mp_post_test', 

						'id' => $mail->id, 

						'data' => !$rc ? __('Sending mail failed !', MP_TXTDOM) : sprintf('<span id="mail-%1$s">%2$s</span>', $mail->id , sprintf(__('%1$sView%2$s sent mail', MP_TXTDOM), sprintf('<a href="%1$s" class="thickbox thickbox-preview">', esc_url(MP_::url( MP_Action_url, array('action' => 'iview', 'id' => $mail->id, 'preview_iframe' => 1, 'TB_iframe' => 'true')))), '</a>'))

						)

		);

		$x->send();

	}

}

new MailPress_newsletter();

}