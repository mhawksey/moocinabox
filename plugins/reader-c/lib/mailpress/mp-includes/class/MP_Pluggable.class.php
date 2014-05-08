<?php
class MP_Pluggable
{
	public static function wp_mail( $to, $subject, $message, $headers = '', $attachements = false ) 
	{
	// Compact the input, apply the filters, and extract them back out
		extract( apply_filters('wp_mail', compact( 'to', 'subject', 'message', 'headers' ) ) );

		if ( !is_array($attachments) )
			$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );

		$mail =  new stdClass();

	// Attachments

		if (is_array($attachments))
		{
			$f = true;
			$attachements = @str_replace("\\", "/", $attachment);

			foreach ($attachments as $attachment)
			{
				if (@is_file($attachment))
				{
					if ($f) $mail->id = MP_Mail::get_id('wp_mail_3.0');

					$object = array(	'name' 	=> basename($attachment), 
								'mime_type'	=> 'application/octet-stream', 
								'file'	=> '', 
								'file_fullpath'	=> $attachment, 
								'guid' 	=> ''
					);
					MP_Mail_meta::add( $mail->id, '_MailPress_attached_file', $object );
				}
				$f = false;
			}
		}

	// Headers
		if ( !empty( $headers ) && !is_array( $headers ) )
		{											// Explode the headers out, so this function can take both
													// string headers and an array of headers.
			$tempheaders = (array) explode( "\n", $headers );
			$headers = array();
			if ( !empty( $tempheaders ) ) 
			{										// Iterate through the raw headers
				foreach ( $tempheaders as $header ) 
				{
					if ( strpos($header, ':') === false ) continue;
													// Explode them out
					list( $name, $content ) = explode( ':', trim( $header ), 2 );
													// Cleanup crew
					$name = trim( $name );
					$content = trim( $content );
													// Mainly for legacy -- process a From: header if it's there
					switch (true)
					{
						case ( 'from' == strtolower($name) ) :
							if ( strpos($content, '<' ) !== false ) 
							{
													// So... making my life hard again?
								$from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
								$from_name = str_replace( '"', '', $from_name );
								$from_name = trim( $from_name );
	
								$from_email = substr( $content, strpos( $content, '<' ) + 1 );
								$from_email = str_replace( '>', '', $from_email );
								$from_email = trim( $from_email );
							} 
							else 
							{
								$from_name = trim( $content );
							}
						break;
						case ( 'content-type' == strtolower($name) ) :
							if ( strpos( $content, ';' ) !== false ) 
							{
								list( $type, $charset ) = explode( ';', $content );
								$content_type = trim( $type );
								$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
							} 
							else 
							{
								$content_type = trim( $content );
							}
						break;
						default :
							$headers[trim( $name )] = trim( $content );
						break;
					}
				}
			}
		}
													// From email and name
													// Set the from name and email
		if ( isset( $from_email ) ) 
		{
			$mail->fromemail  = apply_filters('wp_mail_from', $from_email );
			$mail->fromname   = apply_filters('wp_mail_from_name', $from_name );
		}
													// Set destination address
		$mail->toemail = (is_array($to)) ? $to['email'] : $to;
		$mail->toname  = (is_array($to)) ? $to['name']  : '';
													// Set mail's subject and body
		$mail->subject = $subject;

		if (is_array($message))
		{
			if (isset($message['plaintext']))  	$mail->plaintext = $message['plaintext'];
			if (isset($message['text/plain'])) 	$mail->plaintext = $message['text/plain'];
			if (isset($message['html']))  	$mail->html = $message['html'];
			if (isset($message['text/html']))  	$mail->html = $message['text/html'];
		}
		else
		{
			$mail->content = $message;
		}

		if (!empty( $headers )) $mail->headers = $headers;

		return MailPress::mail($mail);
	}

	public static function wp_notify_postauthor($comment_id, $comment_type='') 
	{
		$comment = get_comment( $comment_id );
		$post    = get_post( $comment->comment_post_ID );
		$author  = get_userdata( $post->post_author );

		// The comment was left by the author
		if ( $comment->user_id == $post->post_author )
			return false;

		// The author moderated a comment on his own post
		if ( $post->post_author == get_current_user_id() )
			return false;

		// If there's no email to send the comment to
		if ( '' == $author->user_email )
			return false;

		$comment->author_domain = @gethostbyaddr($comment->comment_author_IP);

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$url['comments'] = get_permalink($comment->comment_post_ID) . '#comments';
		$url['permalink']= get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment_id;
		$url['trash']  = admin_url("comment.php?action=trash&c={$comment->comment_ID}");
		$url['delete'] = admin_url("comment.php?action=delete&c={$comment->comment_ID}");
		$url['spam']   = admin_url("comment.php?action=spam&c={$comment->comment_ID}");

		switch ($comment->comment_type)
		{
			case 'trackback' :
				$notify_message  = sprintf( __( 'New trackback on your post "%s"' ), $post->post_title ) . "<br />\r\n";
				$notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment->author_domain ) . "<br />\r\n";
				$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
				$notify_message .= __('Excerpt: ') . "<br />\r\n" . apply_filters('comment_text', $comment->comment_content) . "<br />\r\n<br />\r\n";
				$notify_message .= __('You can see all trackbacks on this post here: ') . "<br />\r\n";

				$subject = sprintf( __('[%1$s] Trackback: "%2$s"'), $blogname, $post->post_title );
			break;
			case 'pingback' :
				$notify_message  = sprintf( __( 'New pingback on your post "%s"' ), $post->post_title ) . "<br />\r\n";
				$notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment->author_domain ) . "<br />\r\n";
				$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
				$notify_message .= __('Excerpt: ') . "<br />\r\n" . sprintf('[...] %s [...]', apply_filters('comment_text', $comment->comment_content) ) . "<br />\r\n<br />\r\n";
				$notify_message .= __('You can see all pingbacks on this post here: ') . "<br />\r\n";
			
				$subject = sprintf( __('[%1$s] Pingback: "%2$s"'), $blogname, $post->post_title );
			break;
			default: //Comments
				$notify_message  = sprintf( __( 'New comment on your post "%s"' ), $post->post_title ) . "<br />\r\n";
				$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment->author_domain ) . "<br />\r\n";
				$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "<br />\r\n";
				$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
				$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "<br />\r\n";
				$notify_message .= __('Comment: ') . "<br />\r\n" . apply_filters('comment_text', $comment->comment_content) . "<br />\r\n<br />\r\n";
				$notify_message .= __('You can see all comments on this post here: ') . "<br />\r\n";
			
				$subject = sprintf( __('[%1$s] Comment: "%2$s"'), $blogname, $post->post_title );
			break;
		}
		$notify_message .= $url['comments'] . "<br />\r\n<br />\r\n";
		$notify_message .= sprintf( __('Permalink: %s'), $url['comments']) . "<br />\r\n";

		if ( EMPTY_TRASH_DAYS )
			$notify_message .= sprintf( __('Trash it: %s'), $url['trash'] ) . "<br />\r\n";
		else
			$notify_message .= sprintf( __('Delete it: %s'), $url['delete'] ) . "<br />\r\n";
		$notify_message .= sprintf( __('Spam it: %s'), $url['spam'] ) . "<br />\r\n";

		$mail = new stdClass();
		$mail->Template	= 'moderate';
		$mail->toemail 	= $author->user_email;
		$mail->toname     = $author->display_name;
		$mail->subject 	= $subject;
		$mail->content 	= $notify_message;

		$mail->advanced = new stdClass();
		$mail->advanced->comment       = $comment;
		$mail->advanced->user          = $author;
		unset ($post->post_content, $post->post_excerpt);
		$mail->advanced->post          = $post;
		$mail->advanced->url           = $url;

		$mail->the_title 		 = $post->post_title; 

		/* deprecated */
			$mail->c = new stdClass();
			$mail->c->id		= $comment->comment_ID;
			$mail->c->post_ID 	= $comment->comment_post_ID;
			$mail->c->author		= $comment->comment_author;
			$mail->c->author_IP 	= $comment->comment_author_IP;
			$mail->c->email		= $comment->comment_author_email;
			$mail->c->url 		= $comment->comment_author_url;
			$mail->c->domain		= $comment->author_domain;
			$mail->c->content 	= $comment->comment_content;
			$mail->c->type		= $comment->comment_type;
		/* deprecated */

		return MailPress::mail($mail);
	}

	public static function wp_notify_moderator($comment_id) 
	{
		global $wpdb;

		if ( 0 == get_option( 'moderation_notify' ) )
			return true;

		$comment = get_comment($comment_id);
		$post = get_post($comment->comment_post_ID);
		$user = get_userdata( $post->post_author );
		// Send to the administration and to the post author if the author can modify the comment.
		$email_to = array( get_option('admin_email') );
		if ( user_can($user->ID, 'edit_comment', $comment_id) && !empty($user->user_email) && ( get_option('admin_email') != $user->user_email) )
			$email_to[] = $user->user_email;

		$comment->author_domain = @gethostbyaddr($comment->comment_author_IP);
		$comment->waiting       = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$url['approve']= admin_url("comment.php?action=approve&c=$comment_id");
		$url['trash']  = admin_url("comment.php?action=trash&c=$comment_id");
		$url['delete'] = admin_url("comment.php?action=delete&c=$comment_id");
		$url['spam']   = admin_url("comment.php?action=spam&c=$comment_id");
		$url['moderate'] = admin_url("edit-comments.php?comment_status=moderated");

		switch ($comment->comment_type)
		{
			case 'trackback':
				$notify_message  = sprintf( __('A new trackback on the post "%s" is waiting for your approval'), $post->post_title ) . "<br />\r\n";
				$notify_message .= get_permalink($comment->comment_post_ID) . "<br />\r\n<br />\r\n";
				$notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment->author_domain ) . "<br />\r\n";
				$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
				$notify_message .= __('Trackback excerpt: ') . "<br />\r\n" . apply_filters('comment_text', $comment->comment_content) . "<br />\r\n<br />\r\n";
			break;
			case 'pingback':
				$notify_message  = sprintf( __('A new pingback on the post "%s" is waiting for your approval'), $post->post_title ) . "<br />\r\n";
				$notify_message .= get_permalink($comment->comment_post_ID) . "<br />\r\n<br />\r\n";
				$notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment->author_domain ) . "<br />\r\n";
				$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
				$notify_message .= __('Pingback excerpt: ') . "<br />\r\n" . apply_filters('comment_text', $comment->comment_content) . "<br />\r\n<br />\r\n";
			break;
			default: //Comments
				$notify_message  = sprintf( __('A new comment on the post "%s" is waiting for your approval'), $post->post_title ) . "<br />\r\n";
				$notify_message .= get_permalink($comment->comment_post_ID) . "<br />\r\n<br />\r\n";
				$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment->author_domain ) . "<br />\r\n";	
				$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "<br />\r\n";
				$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
				$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "<br />\r\n";
				$notify_message .= __('Comment: ') . "<br />\r\n" . apply_filters('comment_text', $comment->comment_content) . "<br />\r\n<br />\r\n";
			break;
		}

		$notify_message .= sprintf( __('Approve it: %s'), $url['approve'] ) . "<br />\r\n";
		if ( EMPTY_TRASH_DAYS )
			$notify_message .= sprintf( __('Trash it: %s'), $url['trash'] ) . "<br />\r\n";
		else
			$notify_message .= sprintf( __('Delete it: %s'), $url['delete'] ) . "<br />\r\n";
		$notify_message .= sprintf( __('Spam it: %s'), $url['spam'] ) . "<br />\r\n";

		$notify_message .= sprintf( _n('Currently %s comment is waiting for approval. Please visit the moderation panel:',
	 		'Currently %s comments are waiting for approval. Please visit the moderation panel:', $comment->waiting), number_format_i18n($comment->waiting) ) . "<br />\r\n";
		$notify_message .= $url['moderate'] . "<br />\r\n";
	
		$subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), $blogname, $post->post_title );

		$notify_message = apply_filters('comment_moderation_text', $notify_message, $comment->comment_ID);
		$subject = apply_filters('comment_moderation_subject', $subject, $comment->comment_ID);

		$mail = new stdClass();
		$mail->Template	= 'moderate';
		$mail->toemail 	= $user->user_email;
		$mail->toname     = $user->display_name;
		$mail->subject 	= $subject;
		$mail->content 	= $notify_message;

			$mail->advanced = new stdClass();
			$mail->advanced->comment = $comment;
			$mail->advanced->user    = $user;	
			unset ($post->post_content, $post->post_excerpt);
			$mail->advanced->post    = $post;
			$mail->advanced->url     = $url;
		
			$mail->the_title 		 = $post->post_title; 
	
			/* deprecated */
				$mail->c = new stdClass();
				$mail->c->id		= $comment->comment_ID;
				$mail->c->post_ID 	= $comment->comment_post_ID;
				$mail->c->author 		= $comment->comment_author;
				$mail->c->author_IP 	= $comment->comment_author_IP;
				$mail->c->email 		= $comment->comment_author_email;
				$mail->c->url 		= $comment->comment_author_url;
				$mail->c->domain 		= $comment->author_domain;
				$mail->c->content 	= $comment->comment_content;
				$mail->c->type		= $comment->comment_type;
				$mail->p = new stdClass();
				$mail->p->title		= $post->post_title;
			/* deprecated */
	
		foreach ( $email_to as $email )
		{
			$mail->toemail = $email;
			MailPress::mail($mail);
		}
		return true;
	}

	public static function wp_password_change_notification(&$user) 
	{
	// send a copy of password change notification to the admin
	// but check to see if it's the admin whose password we're changing, and skip this
		if ( $user->user_email == get_option('admin_email') ) return;

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$mail = new stdClass();
		$mail->Template	= 'changed_pwd';
		$mail->toemail 	= get_option('admin_email');
		$mail->toname     = '';
		$mail->subject 	= sprintf(__('[%s] Password Lost/Changed'), $blogname);
		$mail->content 	= sprintf(__('Password Lost and Changed for user: %s'), $user->user_login) . "<br />\r\n";

			$mail->advanced = new stdClass();
			$mail->advanced->admin   = $mail->toemail;
			$mail->advanced->user    = $user;

		return MailPress::mail($mail);
	}

	public static function wp_new_user_notification($user_id, $plaintext_pass = '') 
	{
		$user = new WP_User($user_id);

		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "<br />\r\n<br />\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "<br />\r\n<br />\r\n";
		$message .= sprintf(__('E-mail: %s'), $user_email) . "<br />\r\n";

		$mail = new stdClass();
		$mail->Template	= 'new_user';
		$mail->toemail 	= get_option('admin_email');
		$mail->toname     = '';
		$mail->subject 	= sprintf(__('[%s] New User Registration'), $blogname);
		$mail->content 	= $message;

			$mail->advanced = new stdClass();
	       	$mail->advanced->admin   = $mail->toemail;
			$mail->advanced->user    = $user;

		/* deprecated */
			$mail->u = new stdClass();
			$mail->u->login	= $user_login;
			$mail->u->email	= $user_email;
		/* deprecated */

		MailPress::mail($mail);

		if ( empty($plaintext_pass) ) return;

		$user->plaintext_pass = $plaintext_pass;

		$message  = sprintf(__('Username: %s'), $user_login) . "<br />\r\n";
		$message .= sprintf(__('Password: %s'), $plaintext_pass) . "<br />\r\n";
		$message .= wp_login_url() . "<br />\r\n";

		$mail = new stdClass();
		$mail->Template	= 'new_user';
		$mail->toemail 	= $user->user_email;
		$mail->toname     = $user->display_name;
		$mail->subject 	= sprintf(__('[%s] Your username and password'), $blogname);
		$mail->content 	= $message;

			$mail->advanced = new stdClass();
			$mail->advanced->user    = $user;

		/* deprecated */
			$mail->u = new stdClass();
			$mail->u->login	= $user_login;
			$mail->u->pwd	= $plaintext_pass;
		/* deprecated */

		MailPress::mail($mail);
	}

	public static function retrieve_password_message($message, $key)
	{
		$user = ( strpos($_POST['user_login'], '@') ) ? get_user_by('email', trim( $_POST['user_login'])) : get_user_by('login', trim($_POST['user_login']));

		$user_login = $user->user_login;
		$user_email = $user->user_email;
		$url['site']   = network_site_url();
		$url['reset']  = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

		$_message = __('Someone requested that the password be reset for the following account:') . "<br />\r\n<br />\r\n";
		$_message .= $url['site'] . "<br />\r\n<br />\r\n";
		$_message .= sprintf(__('Username: %s'), $user_login) . "<br />\r\n<br />\r\n";
		$_message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "<br />\r\n<br />\r\n";	
		$_message .= __('To reset your password, visit the following address:') . "<br />\r\n<br />\r\n";	
		$_message .= $url['reset'] . "<br />\r\n";

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$mail = new stdClass();
		$mail->Template	= 'retrieve_pwd';
		$mail->toemail 	= $user_email;
		$mail->toname     = $user->display_name;
		$mail->subject 	= sprintf( __('[%s] Password Reset'), $blogname );
		$mail->content 	= $_message;

			$mail->advanced = new stdClass();
			$mail->advanced->user = $user;
			$mail->advanced->url  = $url;

		/* deprecated */
			$mail->u = new stdClass();
			$mail->u->login	= $user_login;
			$mail->u->key	= $key;
			$mail->u->url	= $url['reset'];
		/* deprecated */

		if (MailPress::mail($mail)) return false;
		return $message;
	}
}