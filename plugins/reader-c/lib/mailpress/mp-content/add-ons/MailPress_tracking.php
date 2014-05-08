<?php
if (class_exists('MailPress') && !class_exists('MailPress_tracking'))
{
/*
Plugin Name: MailPress_tracking
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/tracking/
Description: Tracking : mails &amp; users activity
Version: 5.4
*/

/** for admin plugin pages */
define ('MailPress_page_tracking_m', MailPress_page_mails . '&file=tracking');
define ('MailPress_page_tracking_u', MailPress_page_users . '&file=tracking');

/** for admin plugin urls */
$mp_file = 'admin.php';
define ('MailPress_tracking_m', 	$mp_file . '?page=' 	. MailPress_page_tracking_m);
define ('MailPress_tracking_u', 	$mp_file . '?page=' 	. MailPress_page_tracking_u);

/** for mysql */ 
global $wpdb;
$wpdb->mp_tracks = $wpdb->prefix . 'mailpress_tracks';

define ('MailPress_tracking_openedmail', 	'_MailPress_mail_opened');

class MailPress_tracking
{
	const option_name = 'MailPress_tracking';

	function __construct()
	{
// for wordpress hooks
		add_action('init',  					array(__CLASS__, 'init'), 100);
// for mails list
		add_filter('MailPress_mails_actions', 		array(__CLASS__, 'mails_actions'),  8, 3);
// for users list
		add_filter('MailPress_users_actions', 		array(__CLASS__, 'users_actions'),  8, 3);
		add_filter('MailPress_bulk_actions_mailpress_users',	array(__CLASS__, 'bulk_actions_mailpress_users'), 8, 1);
		add_action('MailPress_do_bulk_action_mailpress_users',array(__CLASS__, 'do_bulk_action_mailpress_users'), 8, 2);
// for referential integrity
		add_action('MailPress_delete_mail',  		array(__CLASS__, 'delete_mail'), 1, 1);
		add_action('MailPress_delete_user',  		array(__CLASS__, 'delete_user'), 1, 1);
		add_action('MailPress_unsubscribe_user',		array(__CLASS__, 'unsubscribe_user'), 1, 1);
// prepare mail
		add_filter('MailPress_is_tracking',  		array(__CLASS__, 'is_tracking'), 1, 1);
		add_filter('MailPress_header_url',			array(__CLASS__, 'header_url'), 8, 2);
		add_filter('MailPress_mail',				array(__CLASS__, 'mail'), 8, 2);
// process link
		add_action('mp_action_tracking', 			array(__CLASS__, 'tracking'), 8, 1);

		if (is_admin())
		{
		// install
			register_activation_hook(plugin_basename(__FILE__), 	array(__CLASS__, 'install'));
		// for link on plugin page
			add_filter('plugin_action_links', 		array(__CLASS__, 'plugin_action_links'), 10, 2 );
		// for role & capabilities
			add_filter('MailPress_capabilities',  	array(__CLASS__, 'capabilities'), 1, 1);
		// for load admin page
			add_filter('MailPress_load_admin_page', 	array(__CLASS__, 'load_admin_page'), 10, 1);
		// for settings
			add_filter('MailPress_settings_tab', 	array(__CLASS__, 'settings_tab'), 40, 1);
		// for autorefresh
			add_filter('MailPress_autorefresh_js',	array(__CLASS__, 'autorefresh_js'), 8, 1);
		}
	}

	public static function init()
	{
	// for mails list
		if ( current_user_can('MailPress_tracking_mails') )
		{
			add_filter('MailPress_mails_columns', 		array(__CLASS__, 'mails_columns'), 8, 1);
			add_action('MailPress_mails_get_row',  		array(__CLASS__, 'mails_get_row'), 1, 3);
		}
	}

// for mails list
	public static function mails_actions($actions, $mail, $url_parms)
	{
		if (!current_user_can('MailPress_tracking_mails'))	return $actions;
		if ('draft' == $mail->status)					return $actions;

	// url
		$args = array();
		$args['id'] 	= $mail->id;

		$tracking_url 	= esc_url(MP_::url( MailPress_tracking_m, array_merge($args, $url_parms)));

	// actions
		$actions['tracking'] = "<a href='$tracking_url' title='" . __('See tracking results', MP_TXTDOM ) . "'>" . __('Tracking', MP_TXTDOM) . '</a>';

		return $actions;
	}

// for users list
	public static function users_actions($actions, $user, $url_parms)
	{
		if (!current_user_can('MailPress_tracking_users'))	return $actions;

	// url
		$args = array();
		$args['id'] 	= $user->id;

		$tracking_url 	= esc_url(MP_::url( MailPress_tracking_u, array_merge($args, $url_parms)));

	// actions
		$actions['tracking'] = "<a href='$tracking_url' title='" . __('See tracking results', MP_TXTDOM ) . "'>" . __('Tracking', MP_TXTDOM) . '</a>';

		return $actions;
	}

	public static function bulk_actions_mailpress_users($bulk_actions)
	{
		$bulk_actions['geolocate'] = __('re-Geolocate', MP_TXTDOM);
		return $bulk_actions;
	}

	public static function do_bulk_action_mailpress_users($action, $checked)
	{
		if ('bulk-geolocate' != $action) return false;
		$count = 0;
		foreach($checked as $id) if (self::set_ip($id)) $count++;
		return $count;
	}

	public static function set_ip($id)
	{
		global $wpdb;

		$ip = $wpdb->get_var( $wpdb->prepare( "SELECT ip FROM $wpdb->mp_tracks WHERE user_id = %d AND ip <> '' ORDER BY tmstp DESC LIMIT 1;", $id) );
		if (!$ip) return false;

		return MP_User::set_ip($id, $ip);
	}

// for referential integrity
	public static function delete_mail($mail_id)
	{
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mp_tracks   WHERE mail_id = %d ; ", $mail_id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mp_usermeta WHERE meta_key = '_MailPress_mail_sent' AND meta_value = %d ; ", $mail_id ) );
	}

	public static function delete_user($mp_user_id)
	{
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mp_tracks WHERE user_id = %d ; ", $mp_user_id ) );
	}

	public static function unsubscribe_user($mp_user_id)
	{
		if (!$mp_user_id) return;

		$data = $format 		= array();

		$data['user_id'] 		= $mp_user_id;											$format[] = '%d';
		$data['mail_id'] 		= 0;													$format[] = '%d';
		$data['mmeta_id'] 	= 0;													$format[] = '%d';
		$data['track'] 		= '!!unsubscribed!!';										$format[] = '%s';
		$data['context'] 		= '?';												$format[] = '%s';
		$data['ip'] 		= trim($_SERVER['REMOTE_ADDR']);								$format[] = '%s';
		$data['agent'] 		= trim($_SERVER['HTTP_USER_AGENT']);							$format[] = '%s';
		$data['referrer']		= (isset($_SERVER['HTTP_REFERER'])) ? trim($_SERVER['HTTP_REFERER']) : '';	$format[] = '%s';
		$data['tmstp']		= current_time( 'mysql' );									$format[] = '%s';

		global $wpdb;
		$wpdb->insert( $wpdb->mp_tracks, $data, $format );
	}

// prepare mail
	public static function is_tracking($x)
	{
		return true;
	}

	public static function header_url($url, $mail)
	{
		$MP_Tracking_url = MP_Action_url;
		$MP_Tracking_url_args = '?tg=%1$s&mm=%2$s&co=%3$s&us={{_confkey}}';

		$meta_id = self::get_mmid($mail->id, '_MailPress_mail_link', str_replace('&amp;', '&', $url));
		$t_url = $MP_Tracking_url . sprintf($MP_Tracking_url_args, 'l', $meta_id, 'h');
		$t_url = apply_filters('MailPress_tracking_url', $t_url, $mail);
		return $t_url;
	}

	public static function mail($mail)
	{
		$MP_Tracking_url = MP_Action_url;
		$MP_Tracking_url_args = '?tg=%1$s&mm=%2$s&co=%3$s&us={{_confkey}}';

		foreach($mail->recipients as $k => $v)
		{
			$toemail = (is_email($k)) ? $k : $v;
			if (isset($mail->replacements[$toemail]['{{_confkey}}']))
			{
				MP_User_meta::add(MP_User::get_id_by_email($toemail), '_MailPress_mail_sent', $mail->id);
			}
		}

		$output = preg_match_all('/<a [^>]*href=[\'"]([^\'"]+)[\'"][^>]*>(.*?)<\/a>/is', $mail->html, $matches, PREG_SET_ORDER);

		$hrefs_txt = array();
		if ($matches)
		{
			foreach ($matches as $match)
			{
				if (strpos($match[1], 'mailto:') !== false) continue;
				if ('#' == $match[1][1]) continue;
				if (strpos($match[1], '#') !== false) continue;

				$meta_id = self::get_mmid($mail->id, '_MailPress_mail_link', str_replace('&amp;', '&', $match[1]));

				$t_url = $MP_Tracking_url . sprintf($MP_Tracking_url_args, 'l', $meta_id, 'h');
				$t_url = apply_filters('MailPress_tracking_url', $t_url, $mail);

				$link = self::str_replace_count($match[1], $t_url, $match[0], 1);
				$mail->html = str_replace($match[0], $link, $mail->html);

				$t_url = 'ttrraacck_uurrll' . sprintf($MP_Tracking_url_args, 'l', $meta_id, 'p');
				$t_url = apply_filters('MailPress_tracking_url', $t_url, $mail);
				$hrefs_txt[$match[1]] = $t_url;
			}
		}
		$meta_id = self::get_mmid($mail->id, MailPress_tracking_openedmail, MailPress_tracking_openedmail);
		$t_url = $MP_Tracking_url . sprintf($MP_Tracking_url_args, 'o', $meta_id, 'h');
		$t_url = apply_filters('MailPress_tracking_url', $t_url, $mail);
		$mail->html = str_ireplace('</body>', "\n<img src='" . $t_url . "' alt='' style='margin:0;padding:0;border:none;' /></body>", $mail->html);

		if (!empty($hrefs_txt))
		{
			uksort($hrefs_txt, create_function('$a, $b', 'return strcmp(strlen($a), strlen($b));'));
			$hrefs_txt = array_reverse($hrefs_txt);
			$t_url = apply_filters('MailPress_tracking_url', $MP_Tracking_url, $mail);
			$hrefs_txt['ttrraacck_uurrll'] = $t_url;
			$mail->plaintext = str_replace(array_keys($hrefs_txt), $hrefs_txt, $mail->plaintext);
		}
		return $mail;
	}

	public static function str_replace_count($search, $replace, $subject, $times=1) 
	{
		$subject_original=$subject;

		$len=strlen($search);
		$pos=0;
		for ($i=1;$i<=$times;$i++) 
		{
			$pos=strpos($subject, $search, $pos);
			if($pos!==false) 
			{
				$subject=substr($subject_original, 0, $pos);
				$subject.=$replace;
				$subject.=substr($subject_original, $pos+$len);
				$subject_original=$subject;
			}
			else
			{
				break;
			}
		}
		return($subject);
	}

	public static function get_mmid($mail_id, $meta_key, $meta_value)
	{
		global $wpdb;
		$meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->mp_mailmeta WHERE mp_mail_id = %d AND meta_key = %s AND meta_value = %s ;", $mail_id, $meta_key, $meta_value ) );
		if ($meta_id) return $meta_id;
		return MP_Mail_meta::add( $mail_id, $meta_key, $meta_value);
	}

// process link
	public static function tracking($meta)
	{
		switch ($_GET['tg'])
		{
			case ('l') :
				self::save($meta);
			break;
			case ('o') :
				self::save($meta);
			break;
			default :
				$meta->meta_value = '404';
				self::save($meta);
			break;
		}
	}

	public static function save($meta)
	{
		global $wpdb;

		$mp_user_id = MP_User::get_id($_GET['us']);

		if (!$mp_user_id) return;
		if (0 == $mp_user_id) return;

		$open_meta_id 	= (MailPress_tracking_openedmail == $meta->meta_value) ? $meta->meta_id : self::get_mmid($meta->mp_mail_id, MailPress_tracking_openedmail, MailPress_tracking_openedmail);
		$opened_mail	= $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->mp_tracks WHERE user_id = %d AND mail_id = %d AND mmeta_id = %d ;", $mp_user_id, $meta->mp_mail_id, $open_meta_id ) );
		if ((MailPress_tracking_openedmail == $meta->meta_value) && ($opened_mail)) return;

		$data = $format 		= array();

		$data['user_id'] 		= $mp_user_id;															$format[] = '%d';
		$data['mail_id'] 		= $meta->mp_mail_id;														$format[] = '%d';
		$data['mmeta_id'] 	= $meta->meta_id;															$format[] = '%d';
		$data['track'] 		= mysql_real_escape_string($meta->meta_value);										$format[] = '%s';
		$data['context'] 		= ('h' == $_GET['co']) ? 'html' : 'plaintext';										$format[] = '%s';
		$data['ip'] 		= mysql_real_escape_string(trim($_SERVER['REMOTE_ADDR']));								$format[] = '%s';
		$data['agent'] 		= mysql_real_escape_string(trim($_SERVER['HTTP_USER_AGENT']));							$format[] = '%s';
		$data['referrer']		= (isset($_SERVER['HTTP_REFERER'])) ? mysql_real_escape_string(trim($_SERVER['HTTP_REFERER'])) : '';	$format[] = '%s';
		$data['tmstp']		= current_time( 'mysql' );													$format[] = '%s';

		$wpdb->insert( $wpdb->mp_tracks, $data, $format );

		if (MailPress_tracking_openedmail == $meta->meta_value) $opened_mail = true;
		if ($opened_mail) return;

		$data['mmeta_id'] 	= $open_meta_id;
		$data['track'] 		= MailPress_tracking_openedmail;

		$wpdb->insert( $wpdb->mp_tracks, $data, $format );
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// install
	public static function install() 
	{
		include (MP_ABSPATH . 'mp-admin/includes/install/tracking.php');
	}
	
// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'tracking');
	}

// for role & capabilities
	public static function capabilities($capabilities) 
	{
		$capabilities['MailPress_tracking_mails'] = array(	'name'  	=> __('View tracking', MP_TXTDOM), 
            									'group' 	=> 'mails'
            								);
		$capabilities['MailPress_tracking_users'] = array(	'name'  	=> __('View tracking', MP_TXTDOM), 
            									'group' 	=> 'users'
            								);
		return $capabilities;
	}

//¤ for load admin page ¤//
	public static function load_admin_page($hub)
	{
		$hub[MailPress_page_tracking_m] = 'tracking_m';
		$hub[MailPress_page_tracking_u] = 'tracking_u';
		return $hub;
	}

// for settings
	public static function settings_tab($tabs)
	{
		$tabs['tracking'] = __('Tracking', MP_TXTDOM);
		return $tabs;
	}

// for autorefresh
	public static function autorefresh_js($scripts)
	{
		return MP_AutoRefresh_js::register_scripts($scripts);
	}

// for mails list
	public static function mails_columns($x)
	{
		$date = array_pop($x);
		$x['tracking_openrate']	=  __('Open rate', MP_TXTDOM);
		$x['tracking_clicks']	=  __('Clicks', MP_TXTDOM);
		$x['tracking_unsubscribe']	=  __('Unsubscribed', MP_TXTDOM);
		$x['date']		= $date;
		return $x;
	}

	public static function mails_get_row($column_name, $mail, $url_parms)
	{
		global $wpdb;
		switch ($column_name)
		{
			case 'tracking_openrate' :
				if (is_email($mail->toemail)) $total = 1;
				elseif (is_serialized($mail->toemail)) $total = count(unserialize($mail->toemail));
				else return;

				$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT user_id FROM $wpdb->mp_tracks WHERE mail_id = %d AND track = %s ;", $mail->id, MailPress_tracking_openedmail) );
				if ($result) if ($total > 0) printf("%01.2f %%", 100 * count($result)/$total );
			break;
			case 'tracking_clicks' :
				$exclude = array(MailPress_tracking_openedmail, '!!unsubscribed!!', '{{unsubscribe}}');

				$result = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->mp_tracks WHERE mail_id = %d AND track NOT IN ( '" . join("','", $exclude) . "' ) ;", $mail->id) );
				if ($result) echo "<div class='num post-com-count-wrapper'><a class='post-com-count'><span class='comment-count'>$result</span></a></div>";
			break;
			case 'tracking_unsubscribe' :
				$exclude = array('{{unsubscribe}}');

				$r  = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS DISTINCT user_id FROM $wpdb->mp_tracks WHERE mail_id = %d AND track = %s ;", $mail->id, '!!unsubscribed!!') );
				$ud = $wpdb->get_var( "SELECT FOUND_ROWS()" );

				$u  = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->mp_tracks WHERE mail_id = %d AND track IN ('" . join("','", $exclude) .  "') ;", $mail->id) );
				$title = (!$u) ? '' : esc_attr(sprintf(_n(__('%d click on unsubscribe link',  MP_TXTDOM), __('%d clicks on unsubscribe link',  MP_TXTDOM), $u), $u));

				$r = $ud;
				if (!$r) return;
				echo "<a class='post-com-count'><span class='comment-count'><abbr title='{$title}'>{$r}</abbr></span></a>";
			break;
		}
	}

// for reports

	public static function translate_track($track, $mail_id, $strlen = 20)
	{
		switch ($track)
		{
			case '{{subscribe}}' :
				return __('subscribe', MP_TXTDOM);
			break;
			case '{{unsubscribe}}' :
				return __('unsubscribe', MP_TXTDOM);
			break;
			case '{{viewhtml}}' :
				return __('view html', MP_TXTDOM);
			break;
			case MailPress_tracking_openedmail :
				return __('mail opened', MP_TXTDOM);
			break;
			case '!!unsubscribed!!' :
				return __('<b>unsubscribed</b>', MP_TXTDOM);
			break;
			default :
				$confkey = '#µ$&$µ#';
				$url = MP_User::get_subscribe_url($confkey);
				$url = str_replace($confkey, '', $url);
				if (stripos($track, $url) !== false) {return __('subscribe', MP_TXTDOM);}
				$url = MP_User::get_unsubscribe_url($confkey);
				$url = str_replace($confkey, '', $url);
				if (stripos($track, $url) !== false) {return __('unsubscribe', MP_TXTDOM);}
				$url = MP_User::get_view_url($confkey, $mail_id);
				$url = str_replace($confkey . '&id=' . $mail_id, '', $url);
				if (stripos($track, $url) !== false) {return __('view html', MP_TXTDOM);}
			break;
		}
		global $wpdb;
		$title = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE guid = '$track';");
		$title = $display_title = ($title) ? $title : $track;
		$display_title = (substr($display_title, 0, 7) == 'http://') ? substr($display_title, 7) : $display_title;
		$display_title = (substr($display_title, 0, 8) == 'https://') ? substr($display_title, 8) : $display_title;
		$display_title = (strlen($display_title) > $strlen) ? substr($display_title, 0, $strlen - 2) . '...' : $display_title;
		return "<a href='$track' title=\"" . esc_attr($title) . "\">$display_title</a>";
	}
}
new MailPress_tracking();
}