<?php
if (class_exists('MailPress') && !class_exists('MailPress_mailinglist_user_role') )
{
/*
Plugin Name: MailPress_mailinglist_user_role
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/mailinglist_user_role/
Description: New Mail : Mailing lists : based on WP user roles (<span style='color:red;'>beware !</span> <span style='color:#D54E21;'>Sync_wordpress_user</span> add-on STRONGLY required)
Version: 5.4
*/

class MailPress_mailinglist_user_role
{
	function __construct()
	{
// for sending mails
		add_filter('MailPress_mailinglists_optgroup', 	array(__CLASS__, 'mailinglists_optgroup'), 20, 2);
		add_filter('MailPress_mailinglists', 			array(__CLASS__, 'mailinglists'), 20, 1);
		add_filter('MailPress_query_mailinglist', 		array(__CLASS__, 'query_mailinglist'), 20, 2);
	}

//// Sending Mails ////

	public static function mailinglists_optgroup( $label, $optgroup ) 
	{
		if (__CLASS__ == $optgroup) return __('WP User Roles', MP_TXTDOM);
		return $label;
	}

	public static function mailinglists( $draft_dest = array() ) 
	{
		global $wpdb, $wp_roles;

		$query = "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = '" . $wpdb->get_blog_prefix(get_current_blog_id()) . "capabilities' AND meta_value LIKE '%%%s%%'";

		foreach ( $wp_roles->get_names() as $role => $name )
			if ( $wpdb->get_var( sprintf( $query, like_escape( $role ) ) ) )
				$draft_dest[__CLASS__ . '~' . $role] = sprintf( __('To all "%1$s"', MP_TXTDOM), translate_user_role( $name ) );

		return $draft_dest;
	}

	public static function query_mailinglist( $query, $draft_toemail ) 
	{
		if ($query) return $query;

		$role = str_replace(__CLASS__ . '~', '', $draft_toemail, $count);
		if (0 == $count) return $query;
		if (empty($role))  return $query;

		$users = array();
		$results = get_users( array( 'role' => $role, 'fields' => array('user_email') ) );
                foreach ($results as $result) $users[] = $result->user_email;
		if (empty($users)) return $query;

		global $wpdb;
		return "SELECT DISTINCT c.id, c.email, c.name, c.status, c.confkey FROM $wpdb->mp_users c WHERE c.email IN ('" . join("', '", $users) . "') AND c.status = 'active' ";
	}
}
new MailPress_mailinglist_user_role();
}