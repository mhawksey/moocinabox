<?php
class MP_Dashboard_users_per_post extends MP_dashboard_widget_
{
	var $id = 'mp_users_per_post';

	function widget()
	{
		global $wpdb, $wp_locale;
		$empty = -1;

		// Posts Activity

		$_Item_ids = $_Items = array();

		$dend	= date('Y-m-d'); 						$y = substr($dend,0,4); $m = substr($dend,5,2); $d = substr($dend,8,2);
		$dbeg = date('Y-m-d',mktime(0, 0, 0, $m, $d-66, $y)); $y = substr($dbeg,0,4);	$m = substr($dbeg,5,2); $d = substr($dbeg,8,2);

		// get all in one query
		$query = "	SELECT sdate, '1', slib, sum(scount) as scount FROM $wpdb->mp_stats WHERE stype = 'c' AND sdate BETWEEN '%s' AND '%s' GROUP BY 1,2,3
				UNION
				SELECT '%s',  '0', slib, sum(scount)           FROM $wpdb->mp_stats WHERE stype = 'c' AND sdate < '%s' GROUP BY 1,2,3
 				ORDER BY 1,2,3;";
		$items = $wpdb->get_results( $wpdb->prepare( $query, $dbeg, $dend, $dbeg, $dbeg ) );

		foreach ($items as $item)
		{
			if (isset($_Items[$item->sdate][$item->slib])) 	$_Items[$item->sdate][$item->slib] += $item->scount;
			else								$_Items[$item->sdate][$item->slib]  = $item->scount;
			$_Item_ids[$item->slib] = $_Items[$item->sdate][$item->slib];
		}

		// clean up //
		foreach($_Item_ids as $item_id => $x)
		{
			$vide = true;
			foreach ($_Items as $date => $item)
			{
				foreach ($item as $post_id => $scount)
				{
					if ($item_id == $post_id && $scount > 0)
					{
						$vide = false;
						break 2;
					}
				}
			}
			if ($vide) unset($_Item_ids[$item_id]);
		}

		// keep last 5 most subscribed
		arsort($_Item_ids);
		$_Item_ids = array_slice($_Item_ids, 0, 5, 1);

		// clean up //
		foreach ($_Items as $date => $item)
		{
			foreach ($item as $post_id => $scount)
				if (!isset($_Item_ids[$post_id])) unset($_Items[$date][$post_id]);
			if (empty($_Items[$date])) unset($_Items[$date]);
		}

		$chxl_y = 0;
		$chxl_day = $chxl_month = $chxl_year = $chds = $prev_chds = $colors = $item_ids = array();

		$time = $dbeg; $wy = $y; $wm = $m; $wd = $d;

		do 
		{
		// data
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

		foreach($_Item_ids as $item_id => $v) 
		{
			$item_ids[] = $item_id; 
			$values[] = join(',', $chds[$item_id]);
			$colors[] = $this->post_color($item_id);
		}

		if (empty($values)) return;

		$args = array();
		$args['cht']  = 'lc';
		$args['chs']  = $this->widget_size('570x330');
		$args['chxt'] = 'x,y,x,x';
		$args['chxtc']= '0,3';
		$args['chxl'] = '0:|' . join('|', $chxl_day) . '|1:||' . $chxl_y . '|2:|' . join('|', $chxl_month) . '|3:|' . join('|', $chxl_year);
		$args['chds'] = "0,{$chxl_y}";
		$args['chdlp']= 'b';
		$args['chdl'] = join('|', $item_ids);
		$args['chco'] = join(',', $colors);
		$args['chd']  = 't:' . join('|', $values);
		$url = esc_url(add_query_arg($args, $this->url));

?>
<div style='text-align:center;'>
<img style='width:100%;' src="<?php echo $url; ?>" alt="<?php _e( 'Comments subscribers per post', MP_TXTDOM ); ?>" />
</div>
<?php
	}

	function post_color($p)
	{
		$x = pow(3,$p);
		$y = intval($p/3) * 10;
		$p = (355/113)* pow($p,2);
		$c = '';
		$c = sprintf("%02X", $this->my_bcmod($p, '255')) . sprintf("%02X", $this->my_bcmod($y, '255')) . sprintf("%02X", $this->my_bcmod($x, '255'));
		return $c;
	}

	function my_bcmod( $x, $y )
	{
		// how many numbers to take at once? carefull not to exceed (int)
		$take = 5;
		$mod = '';
		do
		{
			$a = (int)$mod.substr( $x, 0, $take );
			$x = substr( $x, $take );
			$mod = $a % $y;
		}
		while ( strlen($x) );
		return (int)$mod;
	}
}
new MP_Dashboard_users_per_post(__( 'MailPress - Comments subscribers per post', MP_TXTDOM ));