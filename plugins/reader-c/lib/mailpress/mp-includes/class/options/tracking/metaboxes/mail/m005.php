<?php
class MP_Tracking_metabox_m005 extends MP_tracking_metabox_sysinfo_
{
	var $id	= 'm005';
	var $context= 'normal';
	var $file 	= __FILE__;

	var $item_id = 'mail_id';

	function extended_meta_box($tracks)
	{
		$total = 0;
		foreach($tracks as $track)
		{
			$agent[$track->agent] = $track->count;
			$total += $track->count;
		}
		foreach($agent as $k => $v)
		{
			$os      = apply_filters('MailPress_tracking_useragents_os_get_info',      $k);
			$browser = apply_filters('MailPress_tracking_useragents_browser_get_info', $k);
			$key = $os . '</td><td>' . $browser;
			if (isset($agents[$key])) 	$agents[$key] += $v;
			else 					$agents[$key]  = $v;
		}
		arsort($agents);
		echo '<table width="100%">';
		foreach($agents as $k => $v)
		{
			echo '<tr><td>' . $k . '</td><td class="num">' . sprintf("%01.2f %%",100 * $v/$total ) . '</td></tr>';
		}
		echo '</table>';
	}
}
new MP_Tracking_metabox_m005(__('System info', MP_TXTDOM));