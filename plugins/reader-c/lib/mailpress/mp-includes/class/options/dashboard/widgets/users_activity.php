<?php
class MP_Dashboard_users_activity extends MP_dashboard_widget_
{
	var $id = 'mp_users_activity';

	function widget()
	{
		global $wpdb, $wp_locale;
		$empty = 0;

		// Subscriber activity

		$_Item_ids = $_Items = array();

		$dend	= date('Y-m-d'); 						$y = substr($dend,0,4); $m = substr($dend,5,2); $d = substr($dend,8,2);
		$dbeg = date('Y-m-d',mktime(0, 0, 0, $m, $d-66, $y)); $y = substr($dbeg,0,4);	$m = substr($dbeg,5,2); $d = substr($dbeg,8,2);

		// get all in one query
		$query = "	SELECT sdate, '1', slib, sum(scount) as scount FROM $wpdb->mp_stats WHERE stype = 'u' AND sdate BETWEEN '%s' AND '%s' GROUP BY 1,2,3
				UNION
				SELECT '%s',  '0', slib, sum(scount)           FROM $wpdb->mp_stats WHERE stype = 'u' AND sdate < '%s' GROUP BY 1,2,3
 				ORDER BY 1,2,3;";
		$items = $wpdb->get_results( $wpdb->prepare( $query, $dbeg, $dend, $dbeg, $dbeg ) );

		foreach ($items as $item)
		{
			if (isset($_Items[$item->sdate][$item->slib])) 	$_Items[$item->sdate][$item->slib] += $item->scount;
			else								$_Items[$item->sdate][$item->slib]  = $item->scount;
			$_Item_ids[$item->slib] = $_Items[$item->sdate][$item->slib];

			if ('active' == $item->slib)
			{
				$_Item_ids['comment'] = $_Item_ids[$item->slib];
				if (isset($_Items[$item->sdate]['comment'])) 	$_Items[$item->sdate]['comment'] += $item->scount;
				else								$_Items[$item->sdate]['comment']  = $item->scount;
			}
		}

		// clean up //
		foreach($_Item_ids as $item_id => $x)
		{
			$vide = true;
			foreach ($_Items as $date => $item)
			{
				foreach ($item as $status_id => $scount)
				{
					if ($item_id == $status_id && $scount > 0)
					{
						$vide = false;
						break 2;
					}
				}
			}
			if ($vide) unset($_Item_ids[$item_id]);
		}

		if (!class_exists('MailPress_comment')) unset($_Item_ids['comment']);

		// clean up //
		foreach ($_Items as $date => $item)
		{
			foreach ($item as $status_id => $scount)
				if (!isset($_Item_ids[$status_id])) unset($_Items[$date][$status_id]);
			if (empty($_Items[$date])) unset($_Items[$date]);
		}

		$chxl_y = 0;
		$chxl_day = $chxl_month = $chxl_year = $chds = $prev_chds = $colors = $item_ids = $values = $lines = array();

		$time = $dbeg; $wy = $y; $wm = $m; $wd = $d;

		do {
		// data
			$chds['fake'][] = $empty;
			foreach($_Item_ids as $item_id => $v)
			{
				if (isset($_Items[$time][$item_id])) 	
				{
					$total = (isset($prev_chds[$item_id]) && is_numeric($prev_chds[$item_id]) && ($prev_chds[$item_id] > -1)) ? $prev_chds[$item_id] + $_Items[$time][$item_id] : $_Items[$time][$item_id];
					if ($total < 0) $total = $empty;
					$chds[$item_id][] = $prev_chds[$item_id] = $total;
				}
				elseif (isset($prev_chds[$item_id]) && ($prev_chds[$item_id] != ''))
				{
					$chds[$item_id][] = $prev_chds[$item_id];
				}
				else	$chds[$item_id][] = $prev_chds[$item_id] = $empty;

				// y
				$chxl_y = max($prev_chds[$item_id], $chxl_y);
			}

		// axis
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

		$datas = array('waiting' => '224499', 'comment' => 'FF0000', 'active' => '80C65A', 'fake' => '000000');
		$libs  = array('waiting' => __('waiting', MP_TXTDOM),  'comment' => __('comment%20only', MP_TXTDOM), 'active' => __('active', MP_TXTDOM),);

		$x = 0;
		foreach($datas as $item_id => $color)
		{
			if (!isset($chds[$item_id])) continue;

			if (isset($libs[$item_id])) 
			{
				$item_ids[] = $libs[$item_id];
				$lines[]  = "b,$color," . $x++ . ",$x,0";
			}
			$values[]     = join(',', $chds[$item_id]);
			$colors[]     = $color;
		}

		$args = array();
		$args['cht']  = 'lc';
		$args['chs']  = $this->widget_size('570x330');
		$args['chxt'] = 'x,y,x,x';
		$args['chxtc']= '0,3';
		$args['chxl'] = '0:|' . join('|', $chxl_day) . '|1:||' . $chxl_y . '|2:|' . join('|', $chxl_month) . '|3:|' . join('|', $chxl_year);
		$args['chds'] = "0,{$chxl_y}";
		$args['chdlp']= 'b';
		$args['chdl'] = join('|', $item_ids);
		$args['chm']  = join('|', $lines);
		$args['chco'] = join(',', $colors);
		$args['chd']  = 't:' . join('|', $values);
		$url = esc_url(add_query_arg($args, $this->url));

?>
<div style='text-align:center;'><img style='width:100%;' src="<?php echo $url; ?>" alt="<?php _e( 'Subscribers - Activity', MP_TXTDOM ); ?>" />
</div>
<?php
	}
}
new MP_Dashboard_users_activity(__( 'MailPress - Subscribers activity', MP_TXTDOM ));