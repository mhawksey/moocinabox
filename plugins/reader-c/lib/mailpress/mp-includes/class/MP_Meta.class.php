<?php
class MP_Meta
{
	public static function _get_meta_table($meta_type)
	{
		global $wpdb;

		$table_name = $meta_type . 'meta';

		if ( empty($wpdb->$table_name) ) return false;

		return $wpdb->$table_name;
	}

	public static function _get_meta_column($meta_type)
	{
		return $meta_type . '_id';
	}

	public static function add( $meta_type, $object_id, $meta_key = false, $meta_value, $unique = false ) 
	{
		$meta_table  = self::_get_meta_table($meta_type);
		$meta_column = self::_get_meta_column($meta_type);

		if ( !is_numeric( $object_id ) || !$meta_key || !$meta_table) return false;

		$data[$meta_column] = $object_id;								$format[] = '%d';
		$data['meta_key']	  = stripslashes($meta_key);						$format[] = '%s';
		$data['meta_value'] = maybe_serialize( stripslashes_deep($meta_value) );	$format[] = '%s';

		global $wpdb;

		if ( $unique && $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $meta_table WHERE meta_key = %s AND $meta_column = %d", $data['meta_key'], $data[$meta_column] ) ) )
		return false;

		$wpdb->insert( $meta_table, $data, $format );

		return $wpdb->insert_id;
	}

	public static function update( $meta_type, $object_id, $meta_key = false, $meta_value = '', $prev_value = '' ) 
	{
		$meta_table  = self::_get_meta_table($meta_type);
		$meta_column = self::_get_meta_column($meta_type);

		if ( !is_numeric( $object_id ) || !$meta_key || !$meta_table) return false;

		$data['meta_value']  = maybe_serialize(stripslashes_deep($meta_value));	$format[] = '%s';

		$where[$meta_column] = $object_id;							$where_format[] = '%d';
		$where['meta_key']   = stripslashes($meta_key);					$where_format[] = '%s';
		if ( !empty( $prev_value ) ) {
			$where['meta_value']  = maybe_serialize($prev_value);			$where_format[] = '%s';
		}

		global $wpdb;
		$wpdb->update( $meta_table, $data, $where, $format, $where_format );

		return true;
	}

	public static function delete( $meta_type, $object_id, $meta_key = false , $meta_value = '' ) 
	{
		$meta_table  = self::_get_meta_table($meta_type);
		$meta_column = self::_get_meta_column($meta_type);

		if ( !is_numeric( $object_id ) || !$meta_table) return false;

		$meta_key   = stripslashes($meta_key);
		$meta_value = maybe_serialize( stripslashes_deep($meta_value) );

		global $wpdb;

		if ( !empty($meta_value) ) 	$wpdb->query( $wpdb->prepare("DELETE FROM $meta_table WHERE $meta_column = %d AND meta_key = %s AND meta_value = %s", $object_id, $meta_key, $meta_value) );
		elseif ( $meta_key ) 		$wpdb->query( $wpdb->prepare("DELETE FROM $meta_table WHERE $meta_column = %d AND meta_key = %s", $object_id, $meta_key) );
		else  				$wpdb->query( $wpdb->prepare("DELETE FROM $meta_table WHERE $meta_column = %d", $object_id) );

		return true;
	}

	public static function get( $meta_type, $object_id, $meta_key = false, $meta_value = '') 
	{
		$meta_table  = self::_get_meta_table($meta_type);
		$meta_column = self::_get_meta_column($meta_type);

		if ( !is_numeric( $object_id ) || !$meta_table) return false;

		global $wpdb;

		if ( $meta_key ) 
		{
			if ( empty($meta_value) ) 
				$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM $meta_table WHERE $meta_column = %d AND meta_key = %s", $object_id, $meta_key) );
			else
				$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM $meta_table WHERE $meta_column = %d AND meta_key = %s AND meta_value = %s", $object_id, $meta_key, $meta_value) );
		}
		else
		{
			$metas = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value FROM $meta_table WHERE $meta_column = %d", $object_id) );
		}

		if ( empty($metas) ) return ( empty($meta_key) ) ? array() : '';

		$metas = array_map('maybe_unserialize', $metas);

		if ( count($metas) == 1 ) 	return $metas[0];
		else					return $metas;
	}


	public static function has( $meta_type, $object_id , $meta_key = false) 
	{
		$meta_table  = self::_get_meta_table($meta_type);
		$meta_column = self::_get_meta_column($meta_type);

		if ( !is_numeric( $object_id ) || !$meta_table) return false;

		global $wpdb;

		$x = ($meta_key) ? "AND meta_key = '".$meta_key."'" : ''; 

		return $wpdb->get_results( $wpdb->prepare("SELECT * FROM $meta_table WHERE $meta_column = %d $x ORDER BY meta_key, meta_id", $object_id ), ARRAY_A );
	}


	public static function update_by_id( $meta_type, $meta_id, $meta_key, $meta_value) 
	{
		$meta_table  = self::_get_meta_table($meta_type);

		if ( !is_numeric( $meta_id ) || !$meta_table ) return false;

		$data['meta_value'] = maybe_serialize( stripslashes_deep($meta_value) );	$format[] = '%s';
		$data['meta_key']   = stripslashes($meta_key);						$format[] = '%s';

		$where['meta_id']   = $meta_id;								$where_format[] = '%d';

		global $wpdb;
		$wpdb->update( $meta_table, $data, $where, $format, $where_format );

		return true;
	}

	public static function delete_by_id( $meta_type, $meta_id ) 
	{
		$meta_table  = self::_get_meta_table($meta_type);

		if ( !is_numeric( $meta_id ) || !$meta_table ) return false;

		global $wpdb;
		$wpdb->query( $wpdb->prepare("DELETE FROM $meta_table WHERE meta_id = %d", $meta_id) );

		return true;
	}

	public static function get_by_id( $meta_type, $meta_id ) 
	{
		$meta_table  = self::_get_meta_table($meta_type);

		if ( !is_numeric( $meta_id ) || !$meta_table ) return false;

		global $wpdb;
		$meta = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $meta_table WHERE meta_id = %d", $meta_id) );
		if ($meta)	$meta->meta_value = maybe_unserialize( $meta->meta_value );

		return $meta;
	}


	public static function get_replacements( $meta_type, $object_id )
	{
		if ( !is_numeric( $object_id ) ) return array();

		$metas = self::get( $meta_type, $object_id );

		if (!$metas) return array();
		if (!is_array($metas)) $metas = array($metas);

		$replacements = array();
		foreach ($metas as $meta)
		{
			if ($meta->meta_key[0] == '_') continue;
			$replacements['{{' . $meta->meta_key . '}}'] = $meta->meta_value;
		}
		
		return apply_filters('MailPress_replacements_' . $meta_type, $replacements);
	}


	public static function add_meta( $meta_type, $object_id, $protected)
	{
		$object_id = (int) $object_id;

		$metakeyselect 	= isset($_POST['metakeyselect']) ? trim( $_POST['metakeyselect'] ) : '';
		$metakeyinput 	= isset($_POST['metakeyinput'])  ? trim( $_POST['metakeyinput'] )  : '';
		$meta_value 	= isset($_POST['metavalue'])     ? trim( $_POST['metavalue'] )     : '';

		if ( ('0' === $meta_value || !empty( $meta_value ) ) && ((('#NONE#' != $metakeyselect) && !empty( $metakeyselect ) ) || !empty( $metakeyinput ) ) )
		{
			// We have a key/value pair. If both the select and the
			// input for the key have data, the input takes precedence:

			if ('#NONE#' != $metakeyselect)				$meta_key = $metakeyselect;
			if ( $metakeyinput)						$meta_key = $metakeyinput; // default
			if ( in_array($meta_key, $protected) )	return false;

			return self::add( $meta_type, $object_id, $meta_key, $meta_value );
		}
		return false;
	}
}