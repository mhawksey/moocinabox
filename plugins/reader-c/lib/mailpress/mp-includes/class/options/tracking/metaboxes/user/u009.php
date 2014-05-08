<?php
class MP_Tracking_metabox_u009 extends MP_tracking_metabox_
{
	var $id	= 'u009';
	var $context= 'side';
	var $file 	= __FILE__;
	var $url 	= 'http://chart.apis.google.com/chart';

	function meta_box($mp_user)
	{
		global $wp_locale, $wpdb;
		$dend = $wpdb->get_var( $wpdb->prepare( "SELECT max(DATE(tmstp)) FROM $wpdb->mp_tracks WHERE user_id = %d AND mail_id <> 0 ;", $mp_user->id) ); 	$y = substr($dend,0,4); $m = substr($dend,5,2); $d = substr($dend,8,2);
		$dbeg = date('Y-m-d', mktime(0, 0, 0, $m, $d-65, $y)); 																$y = substr($dbeg,0,4);	$m = substr($dbeg,5,2); $d = substr($dbeg,8,2);

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT DATE(tmstp) as tmstp, track, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = %d AND mail_id <> 0 AND DATE(tmstp) BETWEEN %s AND %s GROUP BY 1, 2 ORDER BY 1 DESC, 2 DESC ;", $mp_user->id, $dbeg, $dend) );
		if (!$tracks) return;

		foreach($tracks as $track)
		{
			$time = $track->tmstp;
			if ( MailPress_tracking_openedmail == $track->track )
			{
				if (isset($Mo[$time])) 	$Mo[$time] += $track->count;
				else				$Mo[$time]  = $track->count;
			}
			else
			{
				if (isset($Mc[$time])) 	$Mc[$time] += $track->count;
				else				$Mc[$time]  = $track->count;
			}
		}

		$chxl_y = 0;
		$chxl_day = $chxl_month = $chxl_year = $chdMo = $chdMc = array();

		$time = $dbeg; $wy = $y; $wm = $m; $wd = $d;
		do 
		{
		// data
			$chdMo[] = (isset($Mo[$time])) ? $Mo[$time] : -1;
			$chdMc[] = (isset($Mc[$time])) ? $Mc[$time] : -1;

		// axis
			// y
			$chxl_y = (isset($Mo[$time])) ? max($Mo[$time], $chxl_y) : $chxl_y;
			$chxl_y = (isset($Mc[$time])) ? max($Mc[$time], $chxl_y) : $chxl_y;

			// x
			if	 (empty($chxl_year)) 						$chxl_year[] = $wy;
			elseif ('0101' == substr($time,5,2) . substr($time,8,2)) 	$chxl_year[] = substr($time,0,4);
			else 										$chxl_year[] = '';

			$chxl_month[] = ('15' == substr($time,8,2)) ? $wp_locale->get_month_abbrev($wp_locale->get_month(substr($time,5,2))) : '';

			if 		('01' == substr($time,8,2)) 	$chxl_day[] = '01';
			elseif 	('15' == substr($time,8,2)) 	$chxl_day[] = '15';
			else 							$chxl_day[] = '';

			$time = date('Y-m-d',mktime(0, 0, 0, $m, ++$d, $y));
		} while ($time <= $dend);

		$args = array();
		$args['cht']  = 'bvs';
		$args['chs']  = '570x330';
		$args['chxt'] = 'x,y,x,x';
		$args['chxl'] = '0:|' . join('|', $chxl_day) . '|1:||' . $chxl_y . '|2:|' . join('|', $chxl_month) . '|3:|' . join('|', $chxl_year);
		$args['chds'] = "0,{$chxl_y}";
		$args['chdlp']= 'b';
		$args['chdl'] = __('Opened', MP_TXTDOM) . '|' . __('Clicks', MP_TXTDOM);

		$args['chbh'] = '7,1,1';
		$args['chco'] = '84D1F5,D54E21';
		$args['chm']  = 'o,D54E21,1,,7,2';

		$args['chd']  = 't1:' . join(',', $chdMo) . '|' . join(',', $chdMc);
		$url = esc_url(add_query_arg($args, $this->url));

?>
<div style='text-align:center;'>
<img style='width:100%;' src="<?php echo $url; ?>" alt="<?php _e( 'Mails - send', MP_TXTDOM ); ?>" /></div>
<?php
	}
}
new MP_Tracking_metabox_u009(__('Opened, Clicks/day chart', MP_TXTDOM));