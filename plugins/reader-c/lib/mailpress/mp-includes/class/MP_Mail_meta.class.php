<?php
class MP_Mail_meta
{
	const meta_type = 'mp_mail';

	public static function get_protected()
	{
		return array( '_MailPress_attached_file', '_MailPress_batch_send', '_MailPress_mail_link', '_MailPress_mail_opened', '_MailPress_mail_revisions', '_edit_lock', '_edit_last' );
	}

	public static function add( $object_id, $meta_key = false, $meta_value, $unique = false ) 
	{
		return MP_Meta::add( self::meta_type, $object_id, $meta_key, $meta_value, $unique );
	}

	public static function update( $object_id, $meta_key = false, $meta_value = '', $prev_value = '') 
	{
		return MP_Meta::update( self::meta_type, $object_id, $meta_key, $meta_value, $prev_value );
	}

	public static function delete( $object_id, $meta_key = false , $meta_value = '' ) 
	{
		return MP_Meta::delete( self::meta_type, $object_id, $meta_key, $meta_value);
	}

	public static function get( $object_id, $meta_key = false, $meta_value = '') 
	{
		return MP_Meta::get( self::meta_type, $object_id, $meta_key, $meta_value);
	}

	public static function has( $object_id , $meta_key = false) 
	{
		return MP_Meta::has( self::meta_type, $object_id , $meta_key);
	}

	public static function update_by_id($meta_id, $meta_key, $meta_value) 
	{
		return MP_Meta::update_by_id( self::meta_type, $meta_id, $meta_key, $meta_value);
	}

	public static function delete_by_id( $meta_id ) 
	{
		return MP_Meta::delete_by_id( self::meta_type, $meta_id );
	}

	public static function get_by_id( $meta_id ) 
	{
		return MP_Meta::get_by_id( self::meta_type, $meta_id );
	}

	public static function get_replacements( $object_id )
	{
		return MP_Meta::get_replacements( self::meta_type, $object_id );
	}

	public static function add_meta($object_id)
	{
		return MP_Meta::add_meta( self::meta_type, $object_id, self::get_protected());
	}
}