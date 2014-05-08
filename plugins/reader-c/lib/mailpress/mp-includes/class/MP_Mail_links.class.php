<?php
class MP_Mail_links
{
	public static function process() 
	{
		foreach($_GET as $method => $mp_confkey) if (method_exists(__CLASS__, $method)) $results = self::$method($mp_confkey);
		if (!isset($results)) return false;

		if (is_numeric($results))
		{
			$errs[1] = __('unknown user', MP_TXTDOM);
			$errs[2] = __('unknown user', MP_TXTDOM);
			$errs[3] = __('cannot activate user', MP_TXTDOM);
			$errs[4] = __('user already active', MP_TXTDOM);
			$errs[5] = __('unknown user', MP_TXTDOM);
			$errs[6] = __('user not a recipient', MP_TXTDOM);
			$errs[7] = __('user not a recipient', MP_TXTDOM);
			$errs[8] = __('unknown mail', MP_TXTDOM);
			$errs[9] = __('unknown user', MP_TXTDOM);

			$content  = '<p>' . sprintf(__('<p> ERROR # %1$s (%2$s) !</p>', MP_TXTDOM), $results, $errs[$results]) . "</p>\n";
			$content .= '<p>' . __('Check you are using the appropriate link.', MP_TXTDOM) . "</p>\n";
			$content .= "<br />\n";

			return array('title' => '', 'content' => $content);
		}
		
		return $results;
	}

	public static function add($mp_confkey)
	{
		$mp_user_id = MP_User::get_id($mp_confkey);
		if (!$mp_user_id) 						return 5;
		if ('active' == MP_User::get_status($mp_user_id)) 	return 4;
		if (!MP_User::set_status($mp_user_id, 'active')) 	return 3;

		$email 	= MP_User::get_email($mp_user_id);
		$url 		= MP_User::get_unsubscribe_url($mp_confkey);

		$title 	= __('Subscription confirmed', MP_TXTDOM);
		$content 	= '';

		$content .= sprintf(__('<p><b>%1$s</b> has successfully subscribed.</p>', MP_TXTDOM), $email);
		$content .= "<br />\n";
		$content .= "<h3>" . sprintf(__('<a href="%1$s">Manage Subscription</a>', MP_TXTDOM), $url) . "</h3>\n";
		$content .= "<br />\n";

		return array('title' => $title, 'content' => $content);
	}

	public static function del($mp_confkey)
	{
		$mp_user_id = MP_User::get_id($mp_confkey);
		if (!$mp_user_id) return 1;

		$mp_user = MP_User::get($mp_user_id);
		$active = ('active' == $mp_user->status) ? true : false;
		$comment = ($active || ('waiting' == $mp_user->status));

		$title    =  sprintf(__('Manage Subscription (%1$s)', MP_TXTDOM), $mp_user->email);
		$content = '';

		if (isset($_POST['cancel']))
		{
			$content .= '<p>' . __('Cancelled action', MP_TXTDOM) ."</p>\n";
			$content .= "<br />\n";
			return array('title' => $title, 'content' => $content);
		}

		if (isset($_POST['delconf']))
		{
			if ($mp_user->name != $_POST['mp_user_name'])
			{
				MP_User::update_name($mp_user->id, $_POST['mp_user_name']);
				$mp_user->name = $_POST['mp_user_name'];
			}

			if (class_exists('MailPress_comment'))	 if ($comment)	MailPress_comment::update_checklist($mp_user_id);
			if (class_exists('MailPress_newsletter'))  if ($active) 	MailPress_newsletter::update_checklist($mp_user_id);
			if (class_exists('MailPress_mailinglist')) if ($active) 	MailPress_mailinglist::update_checklist($mp_user_id);

			$content .= "<div id='moderated' class='updated fade'><p>" . __('Subscriptions saved', MP_TXTDOM) . "</p></div>\n";
		}

		$content .= "<form action='' method='post'>\n";			//esc_url(MP_::url(MP_Action_url, array('action' => 'mail_link', 'del' => $mp_confkey)))

		$content .= '<div id="mp_mail_links_name">';
		$content .= "<h3>" . __('Name', MP_TXTDOM) . "</h3>\n";
		$content .= "<input name='mp_user_name' type='text' value=\"" . esc_attr($mp_user->name) . "\" size='30' />\n";
		$content .= '</div>'; 

		$args = array('htmlstart' => "<li><label for='{{id}}'>", 'htmlmiddle'=> '&#160;', 'htmlend' => "</label></li>\n");
		$ok = false;
		$checklist_comments     = (class_exists('MailPress_comment')     && $comment)	? MailPress_comment::get_checklist($mp_user_id, $args) 	: false;
		if ($checklist_comments) 
		{
			$ok = true;
			$content .= '<div id="mp_mail_links_comments">';
			$content .= "<h3>" . __('Comments') . "</h3>\n";
			$content .= "<ul>$checklist_comments</ul>" ; 
			$content .= '</div>'; 
		}

		$checklist_newsletters  = (class_exists('MailPress_newsletter')  && $active) 	? MailPress_newsletter::get_checklist($mp_user_id, $args)	: false;
		if ($checklist_newsletters) 
		{	
			$ok = true;	
			$content .= '<div id="mp_mail_links_newsletters">';
			$content .= "<h3>" . __('Newsletters', MP_TXTDOM) . "</h3>\n";
			$content .= "<ul>$checklist_newsletters</ul>" ; 
			$content .= '</div>'; 
		}

		$checklist_mailinglists = (class_exists('MailPress_mailinglist') && $active)	? MailPress_mailinglist::get_checklist($mp_user_id, $args)	: false;
		if ($checklist_mailinglists)
		{	
			$ok = true;	
			$content .= '<div id="mp_mail_links_mailinglists">';
			$content .= "<h3>" . __('Mailing lists', MP_TXTDOM) . "</h3>\n";
			$content .= "<ul>$checklist_mailinglists</ul>" ; 
			$content .= '</div>'; 
		}

		if ($ok)
		{
			$content .= "	<input type='hidden' name='status' value='" . MP_User::get_status($mp_user_id) . "' />\n";
			$content .= "	<br /><p><input class='button' type='submit' name='delconf' value='" . __('OK', MP_TXTDOM) . "' />\n";
			$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel', MP_TXTDOM) . "' /></p>\n";
		}
		else
		{
			$content .= '<br /><br />';
			if ($active || $comment) 	$content .= __('Nothing to subscribe for ...', MP_TXTDOM);
			else					$content .= __('Your email has been deactivated, ask the administrator ...', MP_TXTDOM);
			$content .= '<br /><br />';
		}
		$content .= "</form>\n";
		$content .= "<br />\n";
		$content .= "<h3><a href='" . MP_User::get_delall_url($mp_confkey) . "'>" . __('Delete Subscription', MP_TXTDOM) . "</a></h3>\n";
		$content .= "<br />\n";
		return array('title' => $title, 'content' => $content);
	}

	public static function delall($mp_confkey)
	{
		$mp_user_id = MP_User::get_id($mp_confkey);
		if (!$mp_user_id) 						return 2;

		$email 	= MP_User::get_email($mp_user_id);

		$title = __('Unsubscribe', MP_TXTDOM);
		$content = '';

		if (isset($_POST['delconf'])) 
		{
			if (MP_User::set_status($mp_user_id, 'unsubscribed'))
			{
				$content .= sprintf(__('<p>We confirm that the email address <b>%1$s</b> has been unsubscribed.</p>', MP_TXTDOM), $email);
				$content .= "<br />\n";
				return array('title' => $title, 'content' => $content);
			}
		}
		elseif (isset($_POST['cancel']))
		{
			$content .= '<p>' . __('Cancelled action', MP_TXTDOM) ."</p>\n";
			$content .= "<br />\n";
			return array('title' => $title, 'content' => $content);
		}
		else
		{
			$content .= '<p>' .sprintf(__('<p>Are you sure you want to unsubscribe <b>%1$s</b> from <b>%2$s</b>.</p>', MP_TXTDOM), $email, get_bloginfo('name')) ."</p>\n";
			$content .= "<br /><br />\n";
			$content .= "<form action='' method='post'>\n";
			$content .= "	<input class='button' type='submit' name='delconf' value='" . __('OK', MP_TXTDOM) . "' />\n";
			$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel', MP_TXTDOM) . "' />\n";
			$content .= "</form>\n";
			$content .= "<br />\n";
			return array('title' => $title, 'content' => $content);
		}
	}

	public static function view($mp_confkey)
	{
		global $mp_general;

		$mp_user_id = MP_User::get_id($mp_confkey);
		if (!$mp_user_id) 						return 9;

		$email 	= MP_User::get_email($mp_user_id);

		$mail_id = $_GET['id'];
		$mail = MP_Mail::get($mail_id);
		if (!$mail)								return 8;


		$content = '';

		$view_url = MP_::url(  MP_Action_url , array('action' => 'view', 'id' => $mail_id, 'key' => $mp_confkey));

		if (is_email($mail->toemail))
		{
			if ($email != $mail->toemail)				return 6;
			else
			{
				$title    = $mail->subject;
				$content .= sprintf(__('<p> From : <b>%1$s</b></p>', MP_TXTDOM), MP_Mail::display_name_email($mail->fromname, $mail->fromemail));
				$content .= sprintf(__('<p> To   : <b>%1$s</b></p>', MP_TXTDOM), MP_Mail::display_name_email($mail->toname, $mail->toemail));
				$content .= "<p><iframe id='mp' name='mp' style='width:800px;height:600px;border:none;' src='" . esc_url($view_url) . "'></iframe></p>";

				$metas = MP_Mail_meta::has( $mail_id, '_MailPress_attached_file');
				if ($metas)
				{
					$content .= "<div id='attachements'><table><tr><td style='vertical-align:top;'>" . __('Attachments', MP_TXTDOM) . "</td><td><table>";
					foreach($metas as $meta) $content .= "<tr><td>&#160;" . MP_Mail::get_attachement_link($meta, $mail->status) . "</td></tr>";
					$content .= "</table></td></tr></table></div>\n";
				}
				else  $content .= "<br />\n";

				if (isset($mp_general['fullscreen'])) MP_::mp_redirect($view_url);

				return array('title' => $title, 'content' => $content);
			}
		}
		else
		{
			$recipients = unserialize($mail->toemail);
			if (!(is_array($recipients) && (isset($recipients[$email]))))
			{
											return 7;
			}
			else
			{
				$m = MP_Mail_meta::get($mail_id, '_MailPress_replacements');
				if (!is_array($m)) $m = array();

				$recipients = unserialize($mail->toemail);
				$recipient = array_merge($recipients[$email], $m);

				$title    = $mail->subject;
				foreach ($recipient as $k => $v) $title = str_replace($k, $v, $title);
				$content .= sprintf(__('<p> From : <b>%1$s</b></p>', MP_TXTDOM), MP_Mail::display_name_email($mail->fromname, $mail->fromemail));
				$content .= sprintf(__('<p> To   : <b>%1$s</b></p>', MP_TXTDOM), MP_Mail::display_name_email($email, $email));
				$content .= "<p><iframe id='mp' name='mp' style='width:800px;height:600px;border:none;' src='" . esc_url($view_url) . "'></iframe></p>";

				$metas = MP_Mail_meta::has( $mail_id, '_MailPress_attached_file');
				if ($metas)
				{
					$content .= "<div id='attachements'><table><tr><td style='vertical-align:top;'>" . __('Attachments', MP_TXTDOM) . "</td><td><table>";
					foreach($metas as $meta)
					{
						$meta_value = unserialize( $meta['meta_value'] );
						$file_ok = (is_file($meta_value['file_fullpath'])) ? true : false;
						if (is_file($meta_value['file_fullpath']))
							$content .= "<tr><td>&#160;<a href='" . $meta_value['guid'] . "' style='text-decoration:none;'>" . $meta_value['name'] . "</a></td></tr>";
						else
							$content .= "<tr><td>&#160;<span>" . $meta_value['name'] . "</span></td></tr>";
					}
					$content .= "</table></td></tr></table></div>\n";
				}
				else
					$content .= "<br />\n";

				if (isset($mp_general['fullscreen'])) MP_::mp_redirect($view_url);

				return array('title' => $title, 'content' => $content);
			}
		}
	}
}