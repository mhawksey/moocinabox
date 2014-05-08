<?php
class MP_Tracking_metabox_u003 extends MP_tracking_metabox_
{
	var $id	= 'u003';
	var $context= 'side';
	var $file 	= __FILE__;

	function meta_box($mp_user)
	{
		global $wpdb;
		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT DATE(tmstp) as tmstp, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = %d AND track = %s GROUP BY 1 ORDER BY 1 DESC ;", $mp_user->id, MailPress_tracking_openedmail) );
		if ($tracks) foreach($tracks as $track) echo $track->tmstp . ' <b>' . $track->count . '</b><br />';
	}
}
new MP_Tracking_metabox_u003(__('Opened/day', MP_TXTDOM));