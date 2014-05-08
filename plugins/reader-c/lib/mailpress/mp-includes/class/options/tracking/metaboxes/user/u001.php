<?php
class MP_Tracking_metabox_u001 extends MP_tracking_metabox_
{
	var $id	= 'u001';
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

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->mp_tracks WHERE user_id = %d ORDER BY tmstp DESC LIMIT 10;", $mp_user->id) );
		if ($tracks) 
		{
			echo '<table>';
			foreach($tracks as $track) 
			{
				$view_url	= esc_url(add_query_arg( array('action' => 'iview', 'id' => $track->mail_id, 'mp_user_id' => $mp_user->id, 'key' => $mp_user->confkey, 'preview_iframe' => 1, 'TB_iframe' => 'true'), MP_Action_url ));
				$subject    = $wpdb->get_var($wpdb->prepare( "SELECT subject FROM $wpdb->mp_mails WHERE id = %d ;", $track->mail_id));
				if ($subject)
				{
					$subject 	= $x->viewsubject($subject, $track->mail_id, $track->mail_id, $mp_user->id);
					$action 	= "<a href='$view_url' class='thickbox thickbox-preview'  title=\"" . esc_attr(sprintf( __('View "%1$s"', MP_TXTDOM) , ($subject) ? $subject : $track->mail_id )) . "\">" . $track->mail_id . '</a>';
				}
				else
				{	
					$action     = $track->mail_id;
				}
				echo '<tr><td><abbr title="' . $track->tmstp . '">' . substr($track->tmstp, 0, 10) . '</abbr></td><td>&#160;' . (($track->mail_id) ? ' (' . $action . ') ' : ' ') . '</td><td>&#160;' . MailPress_tracking::translate_track($track->track, $track->mail_id) . '</td></tr>';
			}
			echo '</table>';
		}
	}
}
new MP_Tracking_metabox_u001(__('Last 10 actions', MP_TXTDOM));