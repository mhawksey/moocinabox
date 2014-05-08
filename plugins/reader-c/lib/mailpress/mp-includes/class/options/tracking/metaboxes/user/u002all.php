<?php
class MP_Tracking_metabox_u002all extends MP_tracking_metabox_
{
	var $id	= 'u002all';
	var $context= 'side';
	var $file 	= __FILE__;

	function __construct($title)
	{
		add_filter('MailPress_scripts', array($this, 'scripts'), 8, 2);
		add_filter('MailPress_styles',  array($this, 'styles'),  8, 2);
		parent::__construct($title);
	}

	function styles($styles) 
	{
		$styles[] = 'thickbox';
		return $styles;
	}

	function scripts($scripts)
	{
		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);
		$scripts[] = 'mp-thickbox';
		return $scripts;
	}

	function meta_box($mp_user)
	{
		global $wpdb;
		$x = new MP_Mail();

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->mp_usermeta WHERE mp_user_id = %d AND meta_key = %s ORDER BY meta_id DESC;", $mp_user->id, '_MailPress_mail_sent') );
		if ($tracks)
		{
			echo '<div class="mp_scroll"><table>';
			foreach($tracks as $track)
			{
				$mail = $wpdb->get_results($wpdb->prepare( "SELECT subject, created, sent FROM $wpdb->mp_mails WHERE id = %d ;", $track->meta_value));
				foreach($mail as $mail) 
				{
					$subject = $mail->subject;
					$date = ('0000-00-00 00:00:00' == $mail->sent) ? $mail->created : $mail->sent;
				}

				if (isset($subject))
				{
					$subject 	= $x->viewsubject($subject, $track->meta_value, $track->meta_value, $mp_user->id);

					$view_url	= esc_url(add_query_arg( array('action' => 'iview', 'id' => $track->meta_value, 'mp_user_id' => $mp_user->id, 'key' => $mp_user->confkey, 'preview_iframe' => 1, 'TB_iframe' => 'true'), MP_Action_url ));
					$track->meta_value = "<a href='$view_url' class='thickbox thickbox-preview'  title='" . sprintf( __('View "%1$s"', MP_TXTDOM) , $subject) . "'>" . $track->meta_value . '</a>';
				}
				else
				{
					$date = '';
					$subject = __('(deleted)', MP_TXTDOM);
				}
				echo '<tr><td><abbr title="' . $date . '">' . substr($date, 0, 10) . '</abbr></td><td>&#160;(' . $track->meta_value . ')</td><td>&#160;' . $subject . '</td></tr>';
			}
			echo '</table></div>'; 
		}
	}
}
new MP_Tracking_metabox_u002all(__('All mails',  MP_TXTDOM));