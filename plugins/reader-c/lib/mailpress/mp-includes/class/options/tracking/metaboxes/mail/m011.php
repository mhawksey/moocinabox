<?php
class MP_Tracking_metabox_m011 extends MP_tracking_metabox_
{
	var $id	= 'm011';
	var $context= 'normal';
	var $file 	= __FILE__;

	function __construct($title)
	{
		if (!class_exists('MP_Tracking_recipients', false)) new MP_Tracking_recipients();
		parent::__construct($title);
	}

	function meta_box($mail)
	{
		global $wpdb;

		if (is_email($mail->toemail)) $m[$mail->toemail] = $mail->toemail;
		else $m = unserialize($mail->toemail);
		unset($m['MP_Mail']);
		$total = count($m);

		foreach($m as $email => $v)
		{
			$ug = apply_filters('MailPress_tracking_recipients_domain_get', $email);
			$key = $ug->name;
			if (isset($x[$key]['count'])) 	$x[$key]['count']++;
			else 						$x[$key]['count'] = 1;
			if (isset($ug->icon_path) && !isset($x[$key]['img'])) $x[$key]['img'] = $ug->icon_path;
			$opened = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT user_id FROM $wpdb->mp_tracks WHERE mail_id = %d AND user_id = %d AND track = %s ;", $mail->id, $v['{{_user_id}}'], MailPress_tracking_openedmail) );
			if ($opened)
			{
				if (isset($x[$key]['opened'])) 	$x[$key]['opened']++;
				else 						$x[$key]['opened'] = 1;
			}
		}

		if (isset($x[''])) { $w = $x['']; unset($x['']); } else unset($w);
		uasort($x, create_function('$a, $b', 'return $b["count"] - $a["count"];'));
		if (isset($w)) $x[''] = $w;

		echo '<table id ="tracking_mp_010">';
		echo '<tr><th>' . __('domain', MP_TXTDOM) . '</th><th class="num">' . __('count', MP_TXTDOM) . '</th><th class="num">' . __('%', MP_TXTDOM) . '</th><th class="num">' . __('open rate', MP_TXTDOM) . '</th></tr>';
		foreach($x as $k => $v)
		{
			$k = (empty($k)) ? __('others', MP_TXTDOM) : $k;
			echo '<tr>';
			echo (isset($v['img'])) ? "<td><img src='{$v['img']}' alt='' /> $k </td>" : "<td> $k </td>";
			echo '<td class="num">' . $v['count'] . '</td>';
			echo '<td class="num">' . sprintf("%01.2f %%",100 * $v['count']/$total ) . '</td>';
			echo (isset($v['opened'])) ? '<td class="num">' . sprintf("%01.2f %%",100 * $v['opened']/$v['count'] ) . '</td>' : '<td></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
}
new MP_Tracking_metabox_m011(__('Domain recipients', MP_TXTDOM));