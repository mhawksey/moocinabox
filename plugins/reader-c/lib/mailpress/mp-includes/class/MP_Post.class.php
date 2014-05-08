<?php
class MP_Post
{
	const meta_key = MailPress_post::meta_key;
	const meta_key_order =  MailPress_post::meta_key_order;

	public static function exists($mp_mail_id, $post_id) 
	{
		return MP_Mail_meta::get( $mp_mail_id, self::meta_key, $post_id );
	}

	public static function insert($mp_mail_id, $post_id) 
	{
		if (self::exists($mp_mail_id, $post_id)) return;

		$meta_value   = MP_Mail_meta::get( $mp_mail_id, self::meta_key_order );
		$meta_value[$post_id] = $post_id;
		if (!MP_Mail_meta::add( $mp_mail_id, self::meta_key_order, $meta_value, true ))
			MP_Mail_meta::update( $mp_mail_id, self::meta_key_order, $meta_value );

		return MP_Mail_meta::add( $mp_mail_id, self::meta_key, $post_id );
	}

	public static function delete($mp_mail_id, $post_id) 
	{
		$meta_value   = MP_Mail_meta::get( $mp_mail_id, self::meta_key_order );
		unset($meta_value[$post_id]);
		if (empty($meta_value)) MP_Mail_meta::delete( $mp_mail_id, self::meta_key_order );
		else 				MP_Mail_meta::update( $mp_mail_id, self::meta_key_order, $meta_value );

		return MP_Mail_meta::delete( $mp_mail_id, self::meta_key, $post_id );
	}

	public static function delete_post($post_id) 
	{
		global $wpdb;
		$mails = $wpdb->get_results( $wpdb->prepare( "SELECT mp_mail_id FROM $wpdb->mp_mailmeta WHERE meta_key = %s AND meta_value = %s", self::meta_key, $post_id ) );
		if (!$mails) return true;
		foreach($mails as $mail) self::delete( $mail->mp_mail_id, $post_id );
		return true;
	}

// retourne les drafts d'un post
	public static function get_term_objects($post_id)
	{
		global $wpdb;
		$mails = $wpdb->get_results( $wpdb->prepare( "SELECT mp_mail_id FROM $wpdb->mp_mailmeta WHERE meta_key = %s AND meta_value = %s ORDER BY mp_mail_id;", self::meta_key, $post_id ) );
		if (!$mails) return array();
		foreach($mails as $mail) $_objects[$mail->mp_mail_id] = $mail->mp_mail_id;
		return $_objects;
	}

	public static function get_object_terms( $mp_mail_id )
	{
		$_terms = MP_Mail_meta::get( $mp_mail_id, self::meta_key_order );
		return $_terms;
	}

	public static function object_have_relations( $mp_mail_id )
	{
		return MP_Mail_meta::get( $mp_mail_id, self::meta_key );
	}
}