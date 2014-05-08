<?php
if (class_exists('MailPress') && !class_exists('MailPress_mailinglist_US_state') )
{
/*
Plugin Name: MailPress_mailinglist_US_state
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/mailinglist_us_state/
Description: New Mail : Mailing lists : based on US State (<span style='color:red;'>beware !</span> IP geolocation search is not 100% accurate)
Version: 5.4
*/

class MailPress_mailinglist_US_state
{
	function __construct()
	{
// for sending mails
		add_filter('MailPress_mailinglists_optgroup', 	array(__CLASS__, 'mailinglists_optgroup'), 12, 2);
		add_filter('MailPress_mailinglists', 			array(__CLASS__, 'mailinglists'), 12, 1);
		add_filter('MailPress_query_mailinglist', 		array(__CLASS__, 'query_mailinglist'), 12, 2);
	}

//// Sending Mails ////

	public static function mailinglists_optgroup( $label, $optgroup ) 
	{
		if (__CLASS__ == $optgroup) return __('US State codes', MP_TXTDOM);
		return $label;
	}

	public static function mailinglists( $draft_dest = array() ) 
	{
		global $wpdb;

		$rows = $wpdb->get_results( "SELECT DISTINCT created_US_State as US_State FROM $wpdb->mp_users WHERE created_country = 'US' AND status = 'active' ORDER BY 1" );

		foreach ($rows as $row) $draft_dest[__CLASS__ . '~' . $row->US_State] = $row->US_State;

		return $draft_dest;
	}

	public static function query_mailinglist( $query, $draft_toemail ) 
	{
		if ($query) return $query;

		$US_State = str_replace(__CLASS__ . '~', '', $draft_toemail, $count);
		if (0 == $count) return $query;
		if (empty($US_State))  return $query;

		global $wpdb;
		return "SELECT DISTINCT c.id, c.email, c.name, c.status, c.confkey FROM $wpdb->mp_users c WHERE c.created_country = 'US' AND c.created_US_State = '$US_State' AND c.status = 'active' ";
	}
}
new MailPress_mailinglist_US_state();
}