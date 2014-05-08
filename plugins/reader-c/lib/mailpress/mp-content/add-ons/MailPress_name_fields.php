<?php
if (class_exists('MailPress') && !class_exists('MailPress_name_fields'))
{
/*
Plugin Name: MailPress_name_fields
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/name_fields/
Description: Users : generate custom fields based on subscriber's name (original idea of Graham)
Version: 5.4
*/

class MailPress_name_fields
{
	function __construct()
	{
// for wp admin
		if (is_admin())
		{
		// install
			register_activation_hook(plugin_basename(__FILE__), 	array(__CLASS__, 'install'));
		}
		add_action('MailPress_insert_user', array(__CLASS__, 'insert_user'), 8, 1);
		add_action('MailPress_update_name', array(__CLASS__, 'update_name'), 8, 2);

		add_filter('MailPress_replacements_mp_mail', array(__CLASS__, 'replacements_mp_mail'), 8, 1);
	}

	public static function install()
	{
		global $wpdb;

		$query = "SELECT DISTINCT id, name FROM $wpdb->mp_users;";

		$users = $wpdb->get_results($query);

		foreach($users as $user) self::update_name($user->id, $user->name);
	}

	public static function insert_user($mp_user_id)
	{
		$mp_user = MP_User::get($mp_user_id);
		if ($mp_user) self::update_name($mp_user_id, $mp_user->name);
	}

	public static function update_name($mp_user_id, $name)
	{
		//$space = strpos($name,' ');
		//$x['firstname'] = ($name != '') ? ucfirst(strtolower($space ? substr($name,0,$space) : $name)) : false;
		//$x['lastname']  = ($space) ? ucfirst(strtolower(substr($name,$space+1))) : false;
		//$x['fullname']  = ucwords(strtolower($name));

		preg_match('/^(\S+)(?:\s+(.*))?/u', $name, $matches);
		$x['firstname']= (empty($matches[1])) ? false : mb_strtoupper(mb_substr($matches[1], 0, 1)) . mb_substr($matches[1], 1);
		$x['lastname'] = (empty($matches[2])) ? false : mb_strtoupper(mb_substr($matches[2], 0, 1)) . mb_substr($matches[2], 1);
		$x['fullname'] = (empty($matches[0])) ? $name : $x['firstname'] . ' ' . $x['lastname'];

		foreach($x as $key => $value)
		{
			if ($value === false)
			{
				MP_User_meta::delete($mp_user_id, $key);
				continue;
			}
			if (!MP_User_meta::add($mp_user_id, $key, $value, true))
				MP_User_meta::update($mp_user_id, $key, $value);
		}
	}

	public static function replacements_mp_mail($replacements)
	{
		foreach(array('firstname', 'lastname', 'fullname') as $cf)
			if (!isset($replacements ['{{' . $cf . '}}'])) $replacements ['{{' . $cf . '}}'] = __('Friend', MP_TXTDOM);

		return $replacements;
	}
}
new MailPress_name_fields();
}