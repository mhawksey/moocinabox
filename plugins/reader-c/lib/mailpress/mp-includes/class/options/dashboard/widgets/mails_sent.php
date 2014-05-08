<?php
class MP_Dashboard_mails_sent extends MP_dashboard_widget_
{
	var $id = 'mp_mails_sent';

	function widget()
	{
		global $wpdb, $wp_locale;

		$dend	= date('Y-m-d'); 						$y = substr($dend,0,4); $m = substr($dend,5,2); $d = substr($dend,8,2);
		$dbeg = date('Y-m-d',mktime(0, 0, 0, $m, $d-66, $y)); $y = substr($dbeg,0,4);	$m = substr($dbeg,5,2); $d = substr($dbeg,8,2);

		$mails = $wpdb->get_results( $wpdb->prepare( "SELECT sdate, sum(scount) AS count FROM $wpdb->mp_stats WHERE stype = 't' AND sdate BETWEEN %s AND %s GROUP BY sdate ORDER BY sdate;", $dbeg, $dend ) );
		if (!$mails) return;
		foreach($mails as $mail) $Ms[$mail->sdate] = $mail->count;

		$chxl_y = 0;
		$chxl_day = $chxl_month = $chxl_year = $chdM = array();

		$time = $dbeg; $wy = $y; $wm = $m; $wd = $d;

		do 
		{
		// data
			$chdM[] = (isset($Ms[$time])) ? $Ms[$time] : -1;

		// axis
			// y
			$chxl_y = (isset($Ms[$time])) ? max($Ms[$time], $chxl_y) : $chxl_y;

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
		$args['cht']  = 'bvg';
		$args['chs']  = $this->widget_size('570x330');
		$args['chxt'] = 'x,y,x,x';
		$args['chxl'] = '0:|' . join('|', $chxl_day) . '|1:||' . $chxl_y . '|2:|' . join('|', $chxl_month) . '|3:|' . join('|', $chxl_year);
		$args['chds'] = "0,{$chxl_y}";
		$args['chbh'] = $this->bar_size(7) . ',1,1';
		$args['chco'] = '4d89f9';
		$args['chd']  = 't:' . join(',', $chdM);
		$url = esc_url(add_query_arg($args, $this->url));

?>
<div style='text-align:center;'>
<img style='width:100%;' src="<?php echo $url; ?>" alt="<?php _e( 'Mails - send', MP_TXTDOM ); ?>" /></div>
<?php
	}
}
new MP_Dashboard_mails_sent(__( 'MailPress - Mails sent', MP_TXTDOM ));