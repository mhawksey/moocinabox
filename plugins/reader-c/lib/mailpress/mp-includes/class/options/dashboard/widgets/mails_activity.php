<?php
class MP_Dashboard_mails_activity extends MP_dashboard_widget_
{
	var $id = 'mp_mails_activity';

	function widget()
	{
		global $wpdb, $wp_locale;

		ob_start();
			include(MP_CONTENT_DIR . 'advanced/dashboard/mails_activity.xml');
			$xml = trim(ob_get_contents());
		ob_end_clean();
		$xml = '<?xml version="1.0" ?>' . $xml;
		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		$MASS = array();
		foreach ($xml->group as $group)
		{
			$a = array();
			foreach($group->templates->children() as $templates) $a[] = (string) $templates;
			$MASS[(string) $group->desc] = $a;
		}

		$chd_m = $chl_m = array();
		$out = '';
		foreach ($MASS as $k => $MAS)
		{
			$in   = join("','",$MAS);
			$out .= (empty($out)) ? $in : "','" . $in;
			$x  = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype = 't' AND slib IN ('$in') ;");
			if ($x) 
			{
				$chd_m[] = $x;
				$chl_m[] = $k;
			}
		}	
		$x  = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype = 't' AND slib NOT IN ('$out') ;");
		if ($x) 
		{
			$chd_m[] = $x;
			$chl_m[] = __('Misc.', MP_TXTDOM);
		}

		if (empty($chd_m)) return;

		$args = array();
		$args['cht']  = 'p3';
		$args['chs']  = $this->widget_size('475x215');
		$args['chl']  = join('|', $chl_m);
		$args['chco'] = '0000ff';

		$sum = array_sum($chd_m);
		foreach($chd_m as $k => $v) $chd_m[$k] = round(100 * $v/$sum);

		$args['chd']  = 't:' . join(',', $chd_m);
		$url = esc_url(add_query_arg($args, $this->url));

?>
<div style='text-align:center;'>
<img style='width:100%;' src="<?php echo $url; ?>" alt="<?php _e( 'Mails - Activity', MP_TXTDOM ); ?>" />
</div>
<?php
	}
}
new MP_Dashboard_mails_activity(__( 'MailPress - Mails activity', MP_TXTDOM ));