<?php
class MP_Stat
{
	function __construct($stype, $slib, $scount) 
	{
		global $wpdb;
		$sdate   = date('Y-m-d');
		$results = $wpdb->query( $wpdb->prepare("UPDATE $wpdb->mp_stats SET scount=scount+$scount WHERE sdate = %s AND stype = %s AND slib = %s;", $sdate, $stype, $slib) );
		if (!$results)	$wpdb->insert($wpdb->mp_stats, compact('sdate', 'stype', 'slib', 'scount'));
	}
}