<?php
if (class_exists('MailPress') && !class_exists('MailPress_sync_wordpress_user'))
{
/*
Plugin Name: MailPress_sync_wordpress_user
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/sync_wordpress_user/
Description: Users : synchronise with WordPress users
Version: 5.4
*/

// 3.

/** for admin plugin pages */
define ('MailPress_page_subscriptions', 	'mailpress_subscriptions');

class MailPress_sync_wordpress_user
{
	const option_name = 'MailPress_sync_wordpress_user';
	const meta_key    = '_MailPress_sync_wordpress_user';

	function __construct()
	{
// for wordpress hooks
// for plugin
		add_action('MailPress_addons_loaded', 	array(__CLASS__, 'addons_loaded'));

// WP user management
		$settings = get_option(self::option_name);
		if (isset($settings['register_form']))			add_action('register_form', array(__CLASS__, 'register_form'));

		add_action('user_register', 					array(__CLASS__, 'user_register'), 1, 1);
		add_action('profile_update', 					array(__CLASS__, 'update'), 1, 1);
		add_action('delete_user', 					array(__CLASS__, 'delete'), 1, 1);

		add_action('personal_options_update', 			array(__CLASS__, 'profile_update'), 8, 1);
		add_action('edit_user_profile_update', 			array(__CLASS__, 'profile_update'), 8, 1);

// MP user management
		add_action('MailPress_insert_user', 			array(__CLASS__, 'mp_insert_user'), 1, 1);
		add_action('MailPress_delete_user', 			array(__CLASS__, 'mp_delete_user'), 1, 1);

// for wp admin
		if (is_admin())
		{
		// for install
			register_activation_hook(plugin_basename(__FILE__), 	array(__CLASS__, 'install'));
		// for link on plugin page
			add_filter('plugin_action_links', 			array(__CLASS__, 'plugin_action_links'), 10, 2 );
		// for role & capabilities
			$_tabs = apply_filters('MailPress_settings_tab', array());
			if (isset($_tabs['subscriptions']))
			{
				add_filter('MailPress_capabilities', 		array(__CLASS__, 'capabilities'), 1, 1);
				if (!class_exists('MailPress_roles_and_capabilities')) add_action('MailPress_roles_and_capabilities', 	array(__CLASS__, 'roles_and_capabilities'));
			}
		// for load admin page
			add_filter('MailPress_load_admin_page', 		array(__CLASS__, 'load_admin_page'), 10, 1);
		// for settings
			add_action('MailPress_settings_general_forms', 	array(__CLASS__, 'settings_general_forms'));
			add_action('MailPress_settings_general_update',	array(__CLASS__, 'settings_general_update'));

		// for meta box in user page
			add_action('MailPress_add_meta_boxes_user', 	array(__CLASS__, 'meta_boxes_user'), 1, 2); 
		}
	}

//// Plugin ////

	public static function addons_loaded()
	{
	// for role & capabilities
		$_tabs = apply_filters('MailPress_settings_tab', array());
		if (isset($_tabs['subscriptions']))
		{
			add_filter('MailPress_capabilities', 		array(__CLASS__, 'capabilities'), 1, 1);
			if (!class_exists('MailPress_roles_and_capabilities')) add_action('MailPress_roles_and_capabilities', 	array(__CLASS__, 'roles_and_capabilities'));
		}
	}

//// WP user management ////

	public static function register_form()
	{
		do_action('MailPress_register_form');
?>
	<br /><br />
<?php
	}

	public static function user_register($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user) self::sync($wp_user);
	}

	public static function update($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user)
		{
			$oldid = get_user_meta( $wp_user->ID, self::meta_key, true);
			if ($oldid)
			{
				$oldemail = MP_User::get_email($oldid);
				if ($oldemail == $wp_user->user_email) return true;
				else
				{
					self::sync($wp_user);
					$newid =  MP_User::get_id_by_email($wp_user->user_email);

					if (apply_filters('MailPress_has_subscriptions', false, $oldid)) do_action('MailPress_sync_subscriptions', $oldid, $newid);
					$count = self::count_emails($oldemail);
					if (0 == $count)							MP_User::delete($oldid);
				}
			}
			else
			{
				self::user_register($wp_user_id);
			}
		}
	}

	public static function delete($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user) 
		{
			$id = get_user_meta( $wp_user->ID, self::meta_key, true);
			if ($id)
			{
				$email = MP_User::get_email($id);
				if ($email)
				{
					$count = self::count_emails($email);
					if ((1 == $count) && !apply_filters('MailPress_has_subscriptions', false, $id)) MP_User::delete($id);
				}
			}
		}
		return true;
	}

	public static function profile_update($wid)
	{
		$id = get_user_meta( $wid, self::meta_key, true );
		if ($id)
		{
			$mp_user = MP_User::get($id);
			if (stripslashes($_POST['display_name']) != $mp_user->name)
			{
				MP_User::update_name($id, $_POST['display_name']);
			}
			if ($_POST['email'] != $mp_user->email)
			{
				$wp_user = self::get_wp_user($wid);
				if (is_email($_POST['email'])) self::sync($wp_user);
			}
		}
		else
		{
			$wp_user = self::get_wp_user($wid);
			self::sync($wp_user);
		}
	}

//// MP user management ////

	public static function mp_insert_user($mp_user_id)
	{
		$mp_email	= MP_User::get_email($mp_user_id);
		$wp_users  	= self::get_wp_users_by_email($mp_email);
		if (is_array($wp_users)) foreach ($wp_users as $wp_user) update_user_meta( $wp_user->ID, self::meta_key, $mp_user_id);
	}

	public static function mp_delete_user($mp_user_id)
	{
		global $wpdb;
		$results = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value = %s ;", self::meta_key, $mp_user_id ) );
	}

// generic functions

	public static function sync($wp_user)
	{

 // Already a MailPress user ?

		$id = get_user_meta( $wp_user->ID, self::meta_key, true);
		if ($id)
		{
			if (MP_User::get_email($id) == $wp_user->user_email) return true;
		}

// Mail already in MailPress table ?

		$id =  MP_User::get_id_by_email($wp_user->user_email);
		if ($id) 
		{
			update_user_meta( $wp_user->ID, self::meta_key, $id );
			MP_User::set_status($id, 'active');
			return true;										  
		}

// so insert !

		return self::insert($wp_user);
	}

	public static function insert($wp_user, $type = 'activate')
	{
		if ( !is_email($wp_user->user_email) )	return false; // not an email

		if ('activate' == $type) 
		{
			if (!MP_User::insert($wp_user->user_email, $wp_user->display_name, array('status' => 'active')))	return false; // user not inserted
		}
		else
		{
			$return = MP_User::add($wp_user->user_email, $wp_user->display_name);
			if (!$return['result']) 				return false; // user not inserted
		}
		$id = MP_User::get_id_by_email($wp_user->user_email);
		update_user_meta( $wp_user->ID, self::meta_key, $id );
		return true;
	}

	public static function get_wp_user($wp_user_id)
	{
		global $wpdb;
		return $wpdb->get_row($wpdb->prepare("SELECT ID, user_email, display_name FROM $wpdb->users WHERE ID = %d ", $wp_user_id));
	}

	public static function get_wp_users_by_mp_user_id($mp_user_id)
	{
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT ID, user_email, display_name FROM $wpdb->users a, $wpdb->usermeta b WHERE a.ID = b.user_id AND b.meta_key = %s AND b.meta_value = %s ", self::meta_key, $mp_user_id ) );
	}

	public static function get_wp_users_by_email($email)
	{
		global $wpdb;
		$email = trim($email);
		if (!is_email($email)) return false;

		return $wpdb->get_results( $wpdb->prepare( "SELECT ID, user_email, display_name FROM $wpdb->users WHERE user_email = %s ;", $email ) );
	}

	public static function count_emails($email)
	{
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare( "SELECT count(*) FROM $wpdb->users WHERE user_email = %s ;", $email ) );
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for install
	public static function install()
	{
		$users = get_users();
		if ($users) foreach($users as $user) self::sync($user);
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'general');
	}

// for role & capabilities
	public static function capabilities($capabilities) 
	{
		$pu = ( current_user_can('edit_users') ) ? 'users.php' : 'profile.php';

		$capabilities['MailPress_manage_subscriptions'] = array(	'name'	=> __('Your Subscriptions', MP_TXTDOM), 
												'group'	=> 'admin', 
												'menu'	=> 33, 
	
												'parent'	=> $pu, 
												'page_title'=> __('MailPress - Subscriptions', MP_TXTDOM), 
												'menu_title'=> __('Your Subscriptions', MP_TXTDOM), 
												'page'	=> MailPress_page_subscriptions, 
												'func'	=> array('MP_AdminPage', 'body')
											);
		return $capabilities;
	}

	public static function roles_and_capabilities()
	{
		global $wp_roles;
		foreach($wp_roles->role_names as $role => $name)
		{
			if ('administrator' == $role) continue;
			$r = get_role($role);
			$r->add_cap('MailPress_manage_subscriptions');
		}
	}

// for load admin page
	public static function load_admin_page($hub)
	{
		$hub[MailPress_page_subscriptions] = 'subscriptions';
		return $hub;
	}

// for settings subscriptions
	public static function settings_general_forms()
	{
		$sync_wordpress_user = get_option(self::option_name);
?>
<tr valign='top'>
	<th scope='row'><label for='sync_wordpress_user_register_form'><?php _e('Registration Form subscriptions', MP_TXTDOM); ?></label></th>
	<td>
		<input type='checkbox' name='sync_wordpress_user[register_form]' id='sync_wordpress_user_register_form'<?php if (isset($sync_wordpress_user['register_form'])) checked($sync_wordpress_user['register_form'], 'on'); ?> />
	</td>
</tr>
<?php
	}

	public static function settings_general_update()
	{
		update_option(self::option_name, $_POST['sync_wordpress_user']);
	}

// for meta box in user page
	public static function meta_boxes_user($mp_user_id, $mp_screen)
	{
		add_meta_box('mp_user_syncwordpress', __('WP User sync', MP_TXTDOM) , array(__CLASS__, 'meta_box'), MP_AdminPage::screen, 'side', 'core');
	}

	public static function meta_box($mp_user)
	{
		$wp_users = self::get_wp_users_by_mp_user_id( $mp_user->id );
		if ($wp_users)
		{
			$count = 0;
			$total = count($wp_users);
			$separator = " style='border-bottom:1px solid #DFDFDF;'";
?>
<div id="user-syncwordpress">
	<table class='form-table'>
<?php

			foreach ($wp_users as $wp_user)
			{
				$count++;
				if ($total == $count) $separator = '';

				$wp_user = get_userdata($wp_user->ID);
				if (empty($wp_user->first_name) && empty($wp_user->last_name) && empty($wp_user->nickname)) continue;
?>
		<tr><td style='padding:0px;' colspan='2'><strong><?php printf(__('WP User # %1$s', MP_TXTDOM), $wp_user->ID); ?></strong></td></tr>
<?php if (isset($wp_user->first_name)) : ?>
		<tr><td style='padding:0px;'><?php _e('First name'); ?></td><td style='padding:0px;'><?php echo $wp_user->first_name; ?></td></tr>
<?php endif; ?>
<?php if (isset($wp_user->last_name)) : ?>
		<tr><td style='padding:0px;'><?php _e('Last name');  ?></td><td style='padding:0px;'><?php echo $wp_user->last_name; ?></td></tr>
<?php endif; ?>
<?php if (isset($wp_user->nickname)) : ?>
		<tr<?php echo $separator; ?>><td style='padding:0px;'><?php _e('Nickname');   ?></td><td style='padding:0px;'><?php echo $wp_user->nickname; ?></td></tr>
<?php endif; ?>
<?php
			}
?>
	</table>
</div>
<?php
		}
		else 
			printf(__('%1$s is not a WordPress user', MP_TXTDOM), $mp_user->email);
	}
}
new MailPress_sync_wordpress_user();
}