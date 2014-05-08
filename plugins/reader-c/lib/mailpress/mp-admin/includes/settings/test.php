<?php

$mp_general['tab'] = 'test';

	

$test 	= stripslashes_deep($_POST['test']);



$test['template'] = $test['th'][$test['theme']]['tm'];

unset($test['th']);



switch (true)

{

	case ( !is_email($test['toemail']) ) :

		$toemailclass = true;

		$message = __('field should be an email', MP_TXTDOM); $no_error = false;

	break;

	case ( empty($test['toname']) ) :

		$tonameclass = true;

		$message = __('field should be a name', MP_TXTDOM); $no_error = false;

	break;

	default :

		update_option(MailPress::option_name_test, $test);

		update_option(MailPress::option_name_general, $mp_general);

		if (isset($_POST['Submit']))

		{

			$message = __('Test settings saved', MP_TXTDOM);

		}

		else

		{

			$url   = home_url();

			$title = get_bloginfo('name');



			$mail = new stdClass();

			$mail->Theme = $test['theme'];

			if ('0' != $test['template']) $mail->Template = $test['template'];



			$mail->id		= MP_Mail::get_id('settings test');



		// Set the from name and email

			$mail->fromemail 	= $mp_general['fromemail'];

			$mail->fromname	= $mp_general['fromname'];



		// Set destination address

			$mail->toemail 	= $test['toemail'];

			$mail->toname	= MP_Mail::display_name($test['toname']);

			$key = MP_User::get_key_by_email($mail->toemail);

			if ($key)

			{

				$mail->viewhtml	 = MP_User::get_view_url($key, $mail->id);

				$mail->unsubscribe = MP_User::get_unsubscribe_url($key);

				$mail->subscribe 	 = MP_User::get_subscribe_url($key);

			}



		// Set mail's subject and body

			$mail->subject	= sprintf( __('Connection test : %1$s - Template : %2$s', MP_TXTDOM), get_bloginfo('name'), isset($mail->Template) ? $mail->Template : __('none', MP_TXTDOM));



			$mail->plaintext   =  "\n\n" . __('This is a test message of MailPress from', MP_TXTDOM) . ' ' . $url . "\n\n";



			$message  = "<div style='font-family: verdana, geneva;'><br /><br />";

			$message .=  sprintf(__('This is a <blink>test</blink> message of %1$s from %2$s. <br /><br />', MP_TXTDOM), ' <b>MailPress</b> ', "<a href='" .  $url . "'>$title</a>");

			$message .= "<br /><br /></div>";

			$mail->html       = $message;



			if (class_exists('MailPress_newsletter'))

			{

				if (isset($mail->Template) && in_array($mail->Template, MP_Newsletter::get_templates()))

				{

					$posts = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY RAND() LIMIT 1;" );

					if ($posts)

					{

						$mail->the_title = apply_filters('the_title', $posts[0]->post_title );

						$mail->newsletter= true;

						query_posts("m=20140506");

					}

				}

			}



			if (isset($test['forcelog'])) 	$mail->forcelog = '';

			if (!isset($test['fakeit'])) 		$mail->nomail = '';

			if (!isset($test['archive'])) 	$mail->noarchive = '';

			if (!isset($test['stats'])) 		$mail->nostats = '';



			if (MailPress::mail($mail))

				if (!isset($test['fakeit'])) 	$message = __('Test settings saved, Mail not send as required', MP_TXTDOM);

				else					$message = __('Test successful, CONGRATULATIONS !', MP_TXTDOM);

			else

			{

				$message = __('FAILED. Check your logs & settings !', MP_TXTDOM); $no_error = false;

			}

		}

	break;

}