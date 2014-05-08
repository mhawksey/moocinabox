<?php
class MP_WP_User
{
	public static function get_id() 
	{
		$user = wp_get_current_user();
		return $user->ID;
	}

	public static function get_email() 
	{
		switch (true)
		{
			case (isset($_POST['email'])) :
				return $_POST['email'];
			break;
			default :
				$u = self::get_id();
				if ($u)
				{
					$user = get_userdata($u);
					return $user->user_email;
				}
				else
				{
					if (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) return $_COOKIE['comment_author_email_' . COOKIEHASH];
				}
			break;
		}
		return '';
	}

	public static function get_unsubscribe_url()
	{
		$url = false;
		$email = self::get_email();

		if (is_email($email))
		{
			$key = MP_User::get_key_by_email($email);
			if ($key) $url = MP_User::get_unsubscribe_url($key);
		}
		return $url;
	}

}