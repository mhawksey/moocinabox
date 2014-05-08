<?php
class MP_Mail_lock
{
	public static function set( $id ) 
	{
		global $current_user;
		if ( !$mail = MP_Mail::get( $id ) )		return false;
		if ( !$current_user || !$current_user->ID )	return false;

		$now = time();

		if (!MP_Mail_meta::add(     $mail->id, '_edit_lock', $now, true))
			MP_Mail_meta::update( $mail->id, '_edit_lock', $now );
		if (!MP_Mail_meta::add(     $mail->id, '_edit_last', $current_user->ID, true))
			MP_Mail_meta::update( $mail->id, '_edit_last', $current_user->ID );
	}

	public static function check( $id ) 
	{
		global $current_user;

		if ( !$mail = MP_Mail::get( $id ) ) return false;

		$lock = MP_Mail_meta::get( $id, '_edit_lock' );
		$last = MP_Mail_meta::get( $id, '_edit_last' );
		$time_window = AUTOSAVE_INTERVAL * 2 ;

		if ( $lock && $lock > time() - $time_window && $last != $current_user->ID )	return $last;
		return false;
	}
}