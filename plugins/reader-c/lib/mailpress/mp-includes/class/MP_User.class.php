<?php
class MP_User
{
	const status_deleted = 'deleted';

	public static function get($user, $output = OBJECT) 
	{
		switch (true)
		{
			case ( empty($user) ) :
				if ( isset($GLOBALS['mp_user']) ) 	$_user = & $GLOBALS['mp_user'];
				else						return null;
			break;
			case ( is_object($user) ) :
				wp_cache_add($user->id, $user, 'mp_user');
				$_user = $user;
			break;
			default :
				if ( isset($GLOBALS['mp_user']) && ($GLOBALS['mp_user']->id == $user) ) 
				{
					$_user = & $GLOBALS['mp_user'];
				} 
				elseif ( ! $_user = wp_cache_get($user, 'mp_user') ) 
				{
               		global $wpdb;
					$_user = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->mp_users WHERE id = %d LIMIT 1", $user) );
					if ($_user) wp_cache_add($_user->id, $_user, 'mp_user');
				}
			break;
		}

		if ( $output == OBJECT ) {
			return $_user;
		} elseif ( $output == ARRAY_A ) {
			return get_object_vars($_user);
		} elseif ( $output == ARRAY_N ) {
			return array_values(get_object_vars($_user));
		} else {
			return $_user;
		}
	}

	public static function get_var($var, $key_col, $key, $format = '%s') 
	{
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare("SELECT $var FROM $wpdb->mp_users WHERE $key_col = $format LIMIT 1;", $key) );
	}

	public static function get_status($id) 
	{
		$result = self::get_var('status', 'id', $id);
		return ($result == NULL) ? self::status_deleted : $result;
	}

	public static function update_status($id, $status) 
	{
		wp_cache_delete($id, 'mp_user');

		$data = $format = $where = $where_format = array();

		$data['status'] 			= $status; 							$format[] = '%s';
		$data['laststatus'] 		= current_time( 'mysql' );				$format[] = '%s';
		$data['laststatus_IP'] 		= (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';				   $format[] = '%s';
		$data['laststatus_agent'] 	= (isset($_SERVER['HTTP_USER_AGENT'])) ? trim(strip_tags($_SERVER['HTTP_USER_AGENT'])) : ''; $format[] = '%s';
		$data['laststatus_user_id'] 	= MP_WP_User::get_id();					$format[] = '%d';

		$where['id'] 			= (int) $id;						$where_format[] = '%d';

		global $wpdb;
		return ($wpdb->update( $wpdb->mp_users, $data, $where, $format, $where_format )) ? $data['laststatus'] : false;
	}

	public static function set_status($id, $status) 
	{
		switch($status) 
		{
			case 'active':
					return self::activate($id);
			break;
			case 'waiting':
					return self::deactivate($id);
			break;
			case 'bounced':
					return self::bounced($id);
			break;
			case 'unsubscribed':
					return self::unsubscribed($id);
			break;
			case 'delete':
					return self::delete($id);
			break;
		}
		wp_cache_delete($id, 'mp_user');
		return true;
	}

	public static function activate($id) 
	{
		$the_status = 'active';
		$mp_user = self::get($id);

		if (!$mp_user) return false;
		if ($the_status == $mp_user->status) return true;

		if (in_array($mp_user->status, array('waiting')))
		{
			$first = (self::get_var('created', 'id', $id) == self::get_var('laststatus', 'id', $id));
			$update = self::update_status($id, $the_status);
			if ($update) 	
			{
				new MP_Stat('u', $the_status, 1);
				do_action('MailPress_activate_user', $id, 'MailPress_activate_user'); 
				if ($first) do_action('MailPress_activate_user_1st', $id, 'MailPress_activate_user_1st'); 
				self::send_succesfull_subscription($mp_user->email, $mp_user->name, $mp_user->confkey);
			}
			return $update;
		}
		return false;
	}

	public static function deactivate($id) 
	{
		$the_status = 'waiting';
		$status = self::get_status($id);

		if ($the_status == $status) return true;

		if (in_array($status, array('active', 'bounced', 'unsubscribed')))
		{
			$update = self::update_status($id, $the_status);

			if ($update) 	
			{
				if ('active' == $status)
				{
					do_action('MailPress_deactivate_user', $id, 'MailPress_deactivate_user'); 
					new MP_Stat('u', 'active', -1);
				}
				else
					new MP_Stat('u', $the_status, 1);
			}
			return $update;
		}
		return false;
	}

	public static function bounced($id) 
	{
		$the_status = 'bounced';
		$status = self::get_status($id);

		if ($the_status == $status) return true;

		$update = self::update_status($id, $the_status);
		if ($update) 
		{
			do_action('MailPress_bounced_user', $id);
			new MP_Stat('u', 'waiting', -1);
			if ('active' == $status) new MP_Stat('u', 'active', -1);
		}
		return $update;
	}

	public static function unsubscribed($id) 
	{
		$the_status = 'unsubscribed';
		$status = self::get_status($id);

		if ($the_status == $status) return true;

		$update = self::update_status($id, $the_status);
		if ($update) 
		{
			do_action('MailPress_unsubscribe_user', $id);
			new MP_Stat('u', 'waiting', -1);
			if ('active' == $status) new MP_Stat('u', 'active', -1);
		}
		return $update;
	}

	public static function delete($id) 
	{
		do_action('MailPress_delete_user', $id);

		$the_status = self::status_deleted;
		$status = self::get_status($id);

		if ($the_status == $status) return true;

		if ('waiting' == $status) new MP_Stat('u', 'waiting', -1);
		if ('active' == $status) { new MP_Stat('u', 'waiting', -1); new MP_Stat('u', 'active', -1); }

		MP_User_meta::delete( $id );

		wp_cache_delete($id, 'mp_user');

		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mp_users WHERE id = %d ; ", $id ) );
	}

	public static function is_user($email = '') 
	{
		return ( is_email($email) && self::status_deleted != self::get_status_by_email($email) ) ; 
	}

	public static function get_id($key) 
	{
		return self::get_var('id', 'confkey', $key);
	}

	public static function get_id_by_email($email) 
	{
		return self::get_var('id', 'email', $email);
	}

	public static function get_email($id) 
	{
		return self::get_var('email', 'id', $id);
	}

	public static function get_status_by_email($email) 
	{
	      $result = self::get_var('status', 'email', $email);
		return ($result == NULL) ? self::status_deleted : $result;
	}

	public static function get_key_by_email($email) 
	{
		return self::get_var('confkey', 'email', $email);
	}

	public static function get_flag_IP() 
	{
		global $mp_user;
		return (('ZZ' == $mp_user->created_country) || empty($mp_user->created_country)) ? '' : "<img class='flag' alt='" . strtolower($mp_user->created_country) . "' title='" . strtolower($mp_user->created_country) . "' src='" . site_url() . '/' . MP_PATH . 'mp-admin/images/flag/' . strtolower($mp_user->created_country) . ".gif' />\n";
	}

/// USER ///

	public static function insert($email, $name, $args = array())
	{
		$defaults = array(	'status'		=> 'waiting',
						'confkey' 		=> md5(uniqid(rand(), 1)),
						'created'		=> current_time( 'mysql' ),
						'created_IP'	=> trim($_SERVER['REMOTE_ADDR']),
						'created_agent'	=> trim(strip_tags($_SERVER['HTTP_USER_AGENT'])),
						'created_user_id'	=> MP_WP_User::get_id(),
						'stopPropagation' => false,
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$data = $format = array();

		$data['email'] 		= $email; 			$format[] = '%s';
		$data['name'] 		= $name; 			$format[] = '%s';
		$data['status'] 		= $status; 			$format[] = '%s';
		$data['confkey'] 		= $confkey;			$format[] = '%s';
		$data['created'] 		= $created;			$format[] = '%s';
		$data['created_IP'] 	= $created_IP;		$format[] = '%s';
		$data['created_agent'] 	= $created_agent; 	$format[] = '%s';
		$data['created_user_id']= $created_user_id;	$format[] = '%d';

		$data['created_country']= MP_Ip::get_country($data['created_IP']);			$format[] = '%s';
		$data['created_US_state']= ('US' == $data['created_country']) ? MP_Ip::get_USstate($data['created_IP']) : 'ZZ'; $format[] = '%s';

		$data['laststatus']	   = $created;		$format[] = '%s';
		$data['laststatus_IP'] 	   = $created_IP;		$format[] = '%s';
		$data['laststatus_agent']  = $created_agent; 	$format[] = '%s';
		$data['laststatus_user_id']= $created_user_id;	$format[] = '%d';

		global $wpdb;
		if (!$wpdb->insert($wpdb->mp_users, $data, $format)) return false;

		$mp_user_id = $wpdb->insert_id;

		new MP_Stat('u', 'waiting', 1);
		if ('active' == $status) new MP_Stat('u', 'active', 1);
 
		do_action('MailPress_insert_user', $mp_user_id);
		if (('active' == $status) && !$stopPropagation) 
		{
                    do_action('MailPress_activate_user', $mp_user_id, 'MailPress_activate_user');
                    do_action('MailPress_activate_user_1st', $mp_user_id, 'MailPress_activate_user_1st'); 
                }

		return $mp_user_id;
	}

	public static function update_name($id, $name) 
	{
		$name = stripslashes($name);

		do_action('MailPress_update_name', $id, $name);

		$data = $format = $where = $where_format = array();

		$data['name'] 		= $name; 		$format[] = '%s';
		$where['id'] 		= (int) $id;	$where_format[] = '%d';

		global $wpdb;
		return $wpdb->update( $wpdb->mp_users, $data, $where, $format, $where_format );
	}

	public static function set_ip($id, $ip)
	{
		$data = $format = $where = $where_format = array();

		$data['created_IP']	 = $ip; 					$format[] = '%s';
		$data['created_country'] = MP_Ip::get_country($ip); 	$format[] = '%s';
		$data['created_US_state']= MP_Ip::get_USstate($ip); 	$format[] = '%s';

		$where['id'] 		 = (int) $id;	$where_format[] = '%d';

		global $wpdb;
		return $wpdb->update( $wpdb->mp_users, $data, $where, $format, $where_format );
	}



	public static function add($email, $name) 
	{
		$return = array();
		$email  = trim($email);

		$defaults = MP_Widget::form_defaults();

		if ( !is_email($email) )
		{
			$return['result']  = false;
			$return['message'] = $defaults['txtvalidemail'];
			return $return;
		}
		
		$status = self::get_status_by_email($email);								//Test if subscription already exists

		switch ($status)
		{
			case self::status_deleted :
				$key = md5(uniqid(rand(), 1));								//generate key
				if ( self::send_confirmation_subscription($email, $name, $key) )			//email was sent
				{
					if ( self::insert($email, $name, array('confkey' => $key)) )
					{
						$return['result']  = true;
						$return['message'] = $defaults['txtwaitconf'] ;
						return $return;
					}
					else
					{
						$return['result']  = false;
						$return['message'] = $defaults['txtdberror'];
						return $return;
					}
				}
				$return['result']  = false;
				$return['message'] = $defaults['txterrconf'];
				return $return;
			break;
			case 'active' :
				$return['result']  = false;
				$return['message'] = $defaults['txtallready'];
				return $return;
			break;
			case 'unsubscribed' :
				$id = self::get_id_by_email($email);

				self::update_name($id, $name);
				self::set_status($id, 'waiting');
			case 'waiting' :
				if ( self::send_confirmation_subscription($email, $name, self::get_key_by_email($email)) )
				{
					$return['result']  = true;
					$return['message'] = $defaults['txtwaitconf'] . ((defined('MP_DEBUG_LOG')) ? ' <small>(2)</small>' : '');
				}
				else
				{
					$return['result']  = false;
					$return['message'] = $defaults['txterrconf']  . ((defined('MP_DEBUG_LOG')) ? ' <small>(2)</small>' : '');
				}
				return $return;
			break;
		}
	}

//// Recipients queries ////

	public static function get_mailinglists()
	{
		add_filter('MailPress_mailinglists_optgroup', 	array(__CLASS__, 'mailinglists_optgroup'), 8, 2);

		$draft_dest = array (	''  => '&#160;', 
						'1' => __('to blog', MP_TXTDOM), 
						'4' => __('all (active + waiting)', MP_TXTDOM),
						'5' => __('waiting', MP_TXTDOM),
					  );
		return apply_filters('MailPress_mailinglists', $draft_dest);
	}

	public static function mailinglists_optgroup( $label, $optgroup ) 
	{
		if (__CLASS__ == $optgroup) return __('Subscribers', MP_TXTDOM);
		return $label;
	}

/// MAIL URLs ///

	private static function _get_url($action, $key){
		global $wp_rewrite;
		global $mp_general;
		
		switch($mp_general['subscription_mngt'])
		{
			case 'ajax':
				return add_query_arg( array('action' => 'mail_link', $action => $key), MP_Action_url );
			break;
			case 'page_id':
				$p = get_post($mp_general['id']);
				$s = ($wp_rewrite->get_page_permastruct() != '' && isset($p->post_status) && 'draft' != $p->post_status)? '?':'&';
				$id = apply_filters('MP_User_get_url_general_id', $mp_general['id'], 'page');
				return get_permalink($id) . $s . $action . '=' . $key ;
			break;
			case 'cat':
				$a = $wp_rewrite->get_category_permastruct();
				$s = (!empty($a))? '?':'&';
				$id = apply_filters('MP_User_get_url_general_id', $mp_general['id'], 'category');
				return get_category_link($id) . $s . $action . '=' . $key ;
			break;
			default:
				$id = apply_filters('MP_User_get_url_general_id', $mp_general['id'], $mp_general['subscription_mngt']);
				return home_url() . '/?' . $mp_general['subscription_mngt'] . '=' . $id . '&' . $action . '=' . $key ;
			break;
		}
	}
 
 	public static function get_subscribe_url($key)
 	{
		return self::_get_url('add', $key);
 	}
 
 	public static function get_unsubscribe_url($key)
 	{
		return self::_get_url('del', $key);
 	}
 
 	public static function get_delall_url($key)
 	{
		return self::_get_url('delall', $key);
 	}
 
 	public static function get_view_url($key, $id)
 	{
		return self::_get_url('view', $key).'&id=' . $id;
 	}

////	send subscription mail functions 	////

	public static function send_confirmation_subscription($email, $name, $key) 
	{
		$url				= home_url();

		$mail				= new stdClass();
		$mail->Template 		= 'new_subscriber';

		$mail->toemail 		= $email;
		$mail->toname		= $name;

		$mail->subscribe		= self::get_subscribe_url($key);

		$mail->subject		= sprintf( __('[%1$s] Waiting for %2$s', MP_TXTDOM), get_bloginfo('name'), $mail->toname );

		$message  = sprintf( __('Please, confirm your subscription to %1$s emails by clicking the following link :', MP_TXTDOM), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= '{{subscribe}}';
		$message .= "\n\n";
		$message .= __('If you do not want to receive more emails, ignore this one !', MP_TXTDOM);
		$message .= "\n\n";
		$mail->plaintext   	= $message;

		$message  = sprintf( __('Please, confirm your subscription to %1$s emails by clicking the following link :', MP_TXTDOM), "<a href='$url'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br /><br />';
		$message .= "<a href='{{subscribe}}'>" . __('Confirm', MP_TXTDOM) . "</a>";
		$message .= '<br /><br />';
		$message .= __('If you do not want to receive more emails, ignore this one !', MP_TXTDOM);
		$message .= '<br /><br />';
		$mail->html    		= $message;

		return MailPress::mail($mail);
	}

	public static function send_succesfull_subscription($email, $name, $key) 
	{
		$url 		= home_url();

		$mail				= new stdClass();
		$mail->Template 		= 'confirmed';

		$mail->toemail 		= $email;
		$mail->toname		= $name;

		$mail->subject		= sprintf( __('[%1$s] Successful subscription for %2$s', MP_TXTDOM), get_bloginfo('name'), $email );

		$message  = sprintf(__('We confirm your subscription to %1$s emails', MP_TXTDOM), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= __('Congratulations !', MP_TXTDOM);
		$message .= "\n\n";
		$mail->plaintext   	= $message;

		$message  = sprintf(__('We confirm your subscription to %1$s emails', MP_TXTDOM), "<a href='$url'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br /><br />';
		$message .= __('Congratulations !', MP_TXTDOM);
		$message .= '<br /><br />';
		$mail->html    		= $message;

		return MailPress::mail($mail);
	}
}