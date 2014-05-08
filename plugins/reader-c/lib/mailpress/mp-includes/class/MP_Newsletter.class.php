<?php
class MP_Newsletter
{
// for newsletters
	public static function register($newsletter = array())
	{
		if (empty($newsletter['id'])) return;

		global $mp_subscriptions, $mp_registered_newsletters;

		$newsletter['allowed'] = (isset($mp_subscriptions['newsletters'][$newsletter['id']]));
		$newsletter['default'] = (isset($mp_subscriptions['default_newsletters'][$newsletter['id']]));

		$mp_registered_newsletters[$newsletter['id']] = $newsletter;
	}

	public static function get($id) 
	{
		global $mp_registered_newsletters;
		if (!isset($mp_registered_newsletters[$id])) return false;
		return $mp_registered_newsletters[$id];
	}

	public static function get_all($lib = 'admin') 
	{
		global $mp_registered_newsletters;

		$x = array();
		foreach ($mp_registered_newsletters as $k => $v) $x[$k] = $v['descriptions'][$lib];
		ksort($x);

		return $x;
	}

	public static function get_active($lib = 'admin') 
	{
		global $mp_registered_newsletters;

		$x = array();
		if (!empty($mp_registered_newsletters))
		{
			foreach ($mp_registered_newsletters as $k => $v) if ($v['allowed']) $x[$k] = $v['descriptions'][$lib];
			ksort($x);
		}
		return $x;
	}

	public static function get_active_by_scheduler($scheduler) 
	{
		global $mp_registered_newsletters;

		$x = array();
		foreach ($mp_registered_newsletters as $k => $v) if ($v['allowed'] && $scheduler == $v['scheduler']['id']) $x[$k] = $v;
		ksort($x);

		return $x;
	}

	public static function get_defaults()
	{
		global $mp_registered_newsletters;

		$x = array();
		foreach($mp_registered_newsletters as $n) if ($n['default']) $x[$n['id']] = $n['id'];
		ksort($x);

		return $x;
	}

	public static function get_templates() 
	{
		global $mp_registered_newsletters;

		$x = array();
		foreach ($mp_registered_newsletters as $k => $v) $x[] = $v['mail']['Template'];

		return array_unique($x);
	}

////  Object  ////

	public static function get_object_terms($mp_user_id = false) 
	{
		global $mp_registered_newsletters;

		$x = self::get_active();

		$a = ($mp_user_id) ? MP_User_meta::get($mp_user_id, MailPress_newsletter::meta_key) : '';

		$y = (is_array($a)) ? array_flip($a) : ((empty($a)) ? array() : array($a => 1));

		foreach ($x as $k => $v)
		{
			if ( $mp_registered_newsletters[$k]['default'] &&  isset($y[$k])) unset($x[$k]);
			if (!$mp_registered_newsletters[$k]['default'] && !isset($y[$k])) unset($x[$k]);
		}
		return $x;
	}

	public static function set_object_terms( $mp_user_id, $object_terms = array() )
	{
		global $mp_registered_newsletters;
		$x = self::get_active();

		MP_User_meta::delete($mp_user_id, MailPress_newsletter::meta_key);

		foreach ($x as $k => $v) 
		{
			$default = ( isset($mp_registered_newsletters[$k]['default']) && $mp_registered_newsletters[$k]['default'] );

			if     ( $default && !isset($object_terms[$k])) MP_User_meta::add($mp_user_id, MailPress_newsletter::meta_key, $k);
			elseif (!$default &&  isset($object_terms[$k])) MP_User_meta::add($mp_user_id, MailPress_newsletter::meta_key, $k);
		}
	}

	public static function reverse_subscriptions($id) 
	{
		global $wpdb;

		$mp_users = $wpdb->get_results( $wpdb->prepare( "SELECT mp_user_id AS id FROM $wpdb->mp_usermeta WHERE meta_key = %s AND meta_value = %s ;", MailPress_newsletter::meta_key, $id ) );

		$to_be_reversed = array();
		foreach($mp_users as $mp_user) $to_be_reversed[] = $mp_user->id;
		$not_in  = (empty($to_be_reversed)) ? '' : 'WHERE id NOT IN (' . join(', ', $to_be_reversed) . ')';

		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mp_usermeta WHERE meta_key = %s AND meta_value =  %s ", MailPress_newsletter::meta_key, $id ) );
		$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->mp_usermeta (mp_user_id, meta_key, meta_value) SELECT id, %s, %s FROM $wpdb->mp_users $not_in;", MailPress_newsletter::meta_key, $id ) );
	}

	public static function get_query_newsletter($id, $in = 'NOT') 
	{
		global $wpdb;
		return $wpdb->prepare( "SELECT DISTINCT a.id, a.email, a.name, a.status, a.confkey 
						FROM 	$wpdb->mp_users a 
						WHERE status = 'active' 
						AND 	$in EXISTS 	(
							SELECT DISTINCT b.mp_user_id 
							FROM 	$wpdb->mp_usermeta b 
							WHERE b.meta_key   = %s
							AND 	b.meta_value = %s 
							AND 	b.mp_user_id = a.id ) ;", MailPress_newsletter::meta_key, $id);
	}

//// *** ////

	public static function post_limits($limits) 
	{
		global $mp_general;

		if (isset($mp_general['post_limits']) && ($mp_general['post_limits'])) return 'LIMIT 0, ' . $mp_general['post_limits'];

		return $limits;
	}

	public static function send($newsletter, $qp = true, $mail = false, $trace = false)
	{
		if (!isset($newsletter['query_posts'])) return 'noqp';

		if (!$mail)
		{
			$in 	= ($newsletter['default']) ? 'NOT' : '';
			$mail	= new stdClass();
			$mail->recipients_query = self::get_query_newsletter($newsletter['id'], $in);
		}

		$rc = 'npst';

		if (isset($newsletter['mail']))
			foreach($newsletter['mail'] as $k => $v)
				if (!empty($newsletter['mail'][$k])) $mail->{$k} = $newsletter['mail'][$k];

		$mail->newsletter = $newsletter;

		add_filter('post_limits', array(__CLASS__, 'post_limits'), 8, 1);

		if ($qp)
		{
			query_posts($newsletter['query_posts']);
				while (have_posts()) { $qp = false; break; }	
			wp_reset_query();

		}
		$qp = apply_filters('MailPress_newsletter_send_qp', $qp);
		if (!$qp)
		{
			query_posts($newsletter['query_posts']);
				$rc = MailPress::mail($mail);
			wp_reset_query();
		}

		remove_filter( 'post_limits', array(__CLASS__, 'post_limits'), 8, 1);

		return $rc;
	}

////  Xml Files  ////

	public static function register_files($args)
	{
		$defaults = array('file' => array());

		extract( wp_parse_args($args, $defaults) );		

		if (!$_post_type = get_post_type_object( $post_type )) return;

		if (isset($root_filter)) $root = apply_filters($root_filter, $root);
		if (empty($files)) return;

		$xml = '';
		foreach($files as $file)
		{
			$fullpath = "$root/$file.xml";
			if (!is_file($fullpath)) continue;

			ob_start();
				include($fullpath);
				$xml .= trim(ob_get_contents());
			ob_end_clean();
		}
		if (empty($xml)) return;

		self::register_xml($xml);
	}

	public static function register_taxonomy($args)
	{
		$defaults = array('file' => array(), 'get_terms_args' => array());

		extract( wp_parse_args($args, $defaults) );

		if (!$_post_type = get_post_type_object( $post_type )) return;
		if (!taxonomy_exists( $taxonomy ) ) return;

		if (isset($root_filter)) $root = apply_filters($root_filter, $root);
   		$dir  = @opendir($root);
		if ($dir) while ( ($file = readdir($dir)) !== false ) if (preg_match("/{$taxonomy}-[0-9]*\.xml/", $file)) $files[] = substr($file, 0, -4);
		if ($dir) @closedir($dir);
		if (empty($files)) return;

		$terms = ('category' == $taxonomy) ? get_categories($get_terms_args) : get_terms($taxonomy, $get_terms_args);
		if (empty($terms)) return;
		if (is_wp_error($terms)) return;

		$xml = '';
		foreach($files as $file)
		{
			$fullpath = "$root/$file.xml";
			if (!is_file($fullpath)) continue;

	            if ($folder == $file)
	            {
				foreach ($terms as $term)
				{
					if ('category' == $taxonomy) $category = $term; // backward compatibility
					ob_start();
						include($fullpath);
						$xml .= trim(ob_get_contents());
					ob_end_clean();
				}
	            }
	            else
	            {
				ob_start();
					include($fullpath);
					$xml .= trim(ob_get_contents());
				ob_end_clean();
      	      }
		}
		if (empty($xml)) return;

		self::register_xml($xml);
	}

	public static function register_xml($xml)
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><newsletters>' . $xml . '</newsletters>';
		$newsletters = new MP_Xml($xml);
		foreach($newsletters->object->children as $newsletter) self::register(self::convert_xml($newsletter));
	}

	public static function convert_xml($child)
	{
		if (isset($child->textValue) && !empty($child->textValue)) $array = (is_numeric($child->textValue)) ? (int) $child->textValue : $child->textValue;
		if (isset($child->attributes)) foreach($child->attributes as $k => $v) $array[$k] = (is_numeric($v)) ? (int) $v : $v;
		if (isset($child->children))   foreach($child->children as $children) 
		{
			if (!isset($array[$children->name]))
				$array[$children->name]   = self::convert_xml($children);
			elseif (is_array($array[$children->name]))
				$array[$children->name][] = self::convert_xml($children);
			else
			{
				$array[$children->name] = array($array[$children->name]);
				$array[$children->name][] = self::convert_xml($children);
			}
		}
		return (isset($array)) ? $array : false;
	}
}