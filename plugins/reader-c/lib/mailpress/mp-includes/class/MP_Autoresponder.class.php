<?php
class MP_Autoresponder
{
	const taxonomy = MailPress_autoresponder::taxonomy;

	public static function exists($term_name) 
	{
		$id = term_exists($term_name, self::taxonomy);
		if ( is_array($id) )	$id = $id['term_id'];
		return $id;
	}

	public static function get($term_id, $output = OBJECT, $filter = 'raw') 
	{
		$term = get_term($term_id, self::taxonomy, $output, $filter);
		if ( is_wp_error( $term ) )	return false;
		$term->slug = self::remove_slug($term->slug);
		if (!is_array($term->description)) $term->description = unserialize($term->description);
		return $term;
	}

	public static function insert($term_arr, $wp_error = false) 
	{
		$term_defaults = array('id' => 0, 'name' => '', 'slug' => '', 'description' => '');
		$term_arr = wp_parse_args($term_arr, $term_defaults);
		extract($term_arr, EXTR_SKIP);

		if ( trim( $name ) == '' ) 
		{
			if ( ! $wp_error )	return 0;
			else				return new WP_Error( 'autoresponder_name', __('You did not enter a valid autoresponder name.', MP_TXTDOM) );
		}

		$slug = self::add_slug($slug, $name);

		if (isset($description['settings'][$description['event']]))
			$description['settings'] = $description['settings'][$description['event']];
		else
			unset($description['settings']);
		$description = mysql_real_escape_string(serialize($description));

		$id = (int) $id;

		// Are we updating or creating?
		$update = (!empty ($id) ) ? true : false;

		$args = compact('name', 'slug', 'parent', 'description');

		if ( $update )	$term = wp_update_term($id,   self::taxonomy, $args);
		else			$term = wp_insert_term($name, self::taxonomy, $args);

		if ( is_wp_error($term) ) 
		{
			if ( $wp_error )	return $term;
			else			return 0;
		}

		return $term['term_id'];
	}

	public static function delete($term_id)
	{
		$meta_key = '_MailPress_autoresponder_' . $term_id;

		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mp_mailmeta WHERE meta_key = %s ;", $meta_key ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mp_usermeta WHERE meta_key = %s ;", $meta_key ) );

		return wp_delete_term( $term_id, self::taxonomy);
	}

	public static function get_all($args = '')
	{
		$defaults = array('hide_empty' => 0, 'hierarchical' => 0, 'child_of' => '0', 'parent' => '');
		$args = wp_parse_args($args, $defaults);
		$terms = get_terms(self::taxonomy, $args);
		if (empty($terms)) return array();
		foreach ($terms as $k => $term)
		{
			$terms[$k]->slug = self::remove_slug($term->slug);
			if (!is_array($term->description)) $terms[$k]->description = unserialize($term->description);
		}
		return $terms;
	}

	public static function get_from_event($event)
	{
		$defaults = array('hide_empty' => 0, 'hierarchical' => 0, 'child_of' => '0', 'parent' => '');
		$terms = get_terms(self::taxonomy, $defaults);
		if (empty($terms)) return array();
		foreach ($terms as $k => $term)
		{
			$terms[$k]->slug = self::remove_slug($term->slug);
			if (!is_array($term->description)) $terms[$k]->description = unserialize($term->description);
			if (isset($term->description['active']) && ($event == $term->description['event'])) continue;
			unset($terms[$k]);
		}
		return $terms;
	}

////  Object  ////

	public static function convert_schedule( $value, $time )
	{
                if (is_serialized($value)) $value = unserialize($value);
		if (!is_array($value))     $value = array('Y' => 0, 'M' => (int) substr($value, 0, 2), 'W' => 0, 'D' => (int) substr($value, 2, 2), 'H' =>  (int) substr($value, 4, 2));

		$Y = date('Y', $time) + $value['Y'];
		$M = date('n', $time) + $value['M'];
		$D = date('j', $time) + $value['D'] + ($value['W'] * 7);
		$H = date('G', $time) + $value['H'];
		$Mn= date('i', $time);
		$S = date('s', $time);
		$value['date'] = mktime($H, $Mn, $S, $M, $D, $Y);
                return $value;
	}

	public static function get_object_terms( $object_id = 0, $args = array() )
	{
		$_terms = array();
		$terms = self::get_all($args);

		if (!$terms) return array();

		foreach( $terms as $term )
		{
			$meta_key = '_MailPress_autoresponder_' . $term->term_id;
			$metadata = MP_Mail_meta::has($object_id, $meta_key);
			$time = time();
			if ($metadata) foreach ($metadata as $entry) $_terms[] =	array('term_id' 	=> $term->term_id, 
														'meta_id' 	=> $entry['meta_id'], 
														'mail_id' 	=> $object_id, 
														'schedule' 	=> self::convert_schedule($entry['meta_value'], $time),
													);
		}

		uasort($_terms, create_function('$a, $b', 'return strcmp($a["schedule"]["date"], $b["schedule"]["date"]);'));
		return $_terms;
	}

	public static function object_have_relations($object_id)
	{
		$terms = self::get_all();

		if (!$terms) return false;

		foreach( $terms as $term )
		{
			$meta_key = '_MailPress_autoresponder_' . $term->term_id;
			$metadata = MP_Mail_meta::has($object_id, $meta_key);
			if ($metadata) return true;
		}
		return false;
	}

	public static function get_term_objects($term_id)
	{
		$meta_key = '_MailPress_autoresponder_' . $term_id;

		global $wpdb;
		$metadata = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->mp_mailmeta WHERE meta_key = %s ORDER BY meta_value;", $meta_key ) );
		if (!$metadata) return array();
		$time = time();
		foreach ($metadata as $entry) $_objects[] = 	array(	'term_id' 	=> $term_id, 
												'meta_id' 	=> $entry->meta_id, 
												'mail_id' 	=> $entry->mp_mail_id, 
												'schedule' 	=> self::convert_schedule($entry->meta_value, $time),
											);
		uasort($_objects, create_function('$a, $b', 'return strcmp($a["schedule"]["date"], $b["schedule"]["date"]);'));
		return $_objects;
	}

	public static function get_term_meta_id($meta_id)
	{
		$entry = MP_Mail_meta::get_by_id( $meta_id );

		$term_id = str_replace('_MailPress_autoresponder_', '', $entry->meta_key);
		return 							array(	'term_id' 	=> $term_id, 
												'meta_id' 	=> $entry->meta_id, 
												'mail_id' 	=> $entry->mp_mail_id, 
												'schedule' 	=> self::convert_schedule($entry->meta_value, time()),
											);
	}

////  Slug  ////

	public static function add_slug( $slug, $name = false )
	{
		$slugs = ($name) ? array($slug, $name) : array($slug);
		foreach ($slugs as $slug)
		{
			$slug = self::remove_slug($slug);
			$slug = trim(stripslashes($slug));
			$slug = str_replace('"', '_', $slug);
			$slug = str_replace("'", '_', $slug);
			if (!empty($slug)) break;
		}
		return $slug = '_' . self::taxonomy . '_' . $slug;
	}

	public static function remove_slug( $slug )
	{
		return str_ireplace('_' . self::taxonomy . '_', '', $slug);
	}
}