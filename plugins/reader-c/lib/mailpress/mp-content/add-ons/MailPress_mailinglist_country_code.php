<?php
if (class_exists('MailPress') && !class_exists('MailPress_mailinglist_country_code') )
{
/*
Plugin Name: MailPress_mailinglist_country_code
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/mailinglist_country_code/
Description: New Mail : Mailing lists : based on country code (<span style='color:red;'>beware !</span> IP geolocation search is not 100% accurate)
Version: 5.4
*/

class MailPress_mailinglist_country_code
{
	function __construct()
	{
// for sending mails
		add_filter('MailPress_mailinglists_optgroup', 	array(__CLASS__, 'mailinglists_optgroup'), 11, 2);
		add_filter('MailPress_mailinglists', 			array(__CLASS__, 'mailinglists'), 11, 1);
		add_filter('MailPress_query_mailinglist', 		array(__CLASS__, 'query_mailinglist'), 11, 2);
	}

//// Sending Mails ////

	public static function mailinglists_optgroup( $label, $optgroup ) 
	{
		if (__CLASS__ == $optgroup) return __('Country codes', MP_TXTDOM);
		return $label;
	}

	public static function mailinglists( $draft_dest = array() ) 
	{
		global $wpdb;

		$rows = $wpdb->get_results( "SELECT DISTINCT created_country as country FROM $wpdb->mp_users WHERE status = 'active' ORDER BY 1" );

		foreach ($rows as $row) $draft_dest[__CLASS__ . '~' . $row->country] = $row->country;

		return $draft_dest;
	}

	public static function query_mailinglist( $query, $draft_toemail ) 
	{
		if ($query) return $query;

		$country = str_replace(__CLASS__ . '~', '', $draft_toemail, $count);
		if (0 == $count) return $query;
		if (empty($country))  return $query;

		global $wpdb;
		return "SELECT DISTINCT c.id, c.email, c.name, c.status, c.confkey FROM $wpdb->mp_users c WHERE c.created_country = '$country' AND c.status = 'active' ";
	}
}
new MailPress_mailinglist_country_code();
}