<?php
class MP_Tracking_metabox_u007 extends MP_tracking_metabox_
{
	var $id	= 'u007';
	var $context= 'side';
	var $file 	= __FILE__;

	function meta_box($mp_user)
	{
		global $wpdb;
		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT DATE(tmstp) as tmstp, track, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = %d AND mail_id <> 0 GROUP BY 1, 2 ORDER BY 1 DESC, 2 DESC ;", $mp_user->id) );
		if ($tracks)
		{
			$x = array();
			foreach($tracks as $track)
			{
				$time = $track->tmstp;
				if ( MailPress_tracking_openedmail == $track->track )
				{
					if (isset($x[$track->tmstp]['o'])) 	$x[$track->tmstp]['o'] += $track->count;
					else						$x[$track->tmstp]['o']  = $track->count;
				}
				else
				{
					if (isset($x[$track->tmstp]['c'])) 	$x[$track->tmstp]['c'] += $track->count;
					else						$x[$track->tmstp]['c']  = $track->count;
				}
			}
?>
<table id='tracking_mp_u007'>
	<thead>
		<tr>
			<th></th>
			<th><?php _e('Opened', MP_TXTDOM); ?></th>
			<th><?php _e('Clicks', MP_TXTDOM); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
			foreach($x as $k => $v)
			{
				echo "<tr><td>$k</td>";
				echo (isset($v['o'])) ? '<td class="number">' . $v['o'] . '</td>' : '<td class="number"></td>';
				echo (isset($v['c'])) ? '<td class="number">' . $v['c'] . '</td>' : '<td class="number"></td>';
				echo "</tr>";
			}
?>
	</tbody>
</table>
<?php
		}
	}
}
new MP_Tracking_metabox_u007(__('Opened, Clicks/day', MP_TXTDOM));