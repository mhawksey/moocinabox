<?php
add_filter('retrieve_password_message', 	array('MP_Pluggable', 'retrieve_password_message'), 8, 2);

/**
 * wp_mail() - Function to send mail
 */
if ( !function_exists( 'wp_mail' ) ) :
	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) 
	{
		return MP_Pluggable::wp_mail( $to, $subject, $message, $headers, $attachments);
	}
endif;

/**
 * wp_notify_postauthor() - Notify an author of a comment/trackback/pingback to one of their posts
 */
if ( ! function_exists('wp_notify_postauthor') ) :
	function wp_notify_postauthor($comment_id, $comment_type='') 
	{
		MP_Pluggable::wp_notify_postauthor($comment_id, $comment_type);
	}
endif;

/**
 * wp_notify_moderator() - Notifies the moderator of the blog about a new comment that is awaiting approval
 */
if ( !function_exists('wp_notify_moderator') ) :
	function wp_notify_moderator($comment_id) 
	{
		MP_Pluggable::wp_notify_moderator($comment_id);
	}
endif;

/**
 * wp_password_change_notification() - Notify the blog admin of a user changing password, normally via email.
 */
if ( !function_exists('wp_password_change_notification') ) :
	function wp_password_change_notification(&$user) 
	{
		MP_Pluggable::wp_password_change_notification($user);
	}
endif;

/**
 * wp_new_user_notification() - Notify the blog admin of a new user, normally via email
 */
if ( !function_exists('wp_new_user_notification') ) :
	function wp_new_user_notification($user_id, $plaintext_pass = '')
	{
		MP_Pluggable::wp_new_user_notification($user_id, $plaintext_pass);
	}
endif;