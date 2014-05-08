<?php
class MP_Tracking_metabox_u006 extends MP_tracking_metabox_
{
	const prefix = 'tracking_u006';

	var $id	= 'u006';
	var $context= 'normal';
	var $file 	= __FILE__;

	function __construct($title)
	{
		add_filter('MailPress_scripts', array($this, 'scripts'), 8, 2);
		parent::__construct($title);
	}
	
	function scripts($scripts)
	{
        if (!isset($_GET['id'])) return $scripts;
	// google map
		wp_register_script( 'google-map',	'http://maps.googleapis.com/maps/api/js?sensor=false', false, false, 1);

	// mp-gmap3
		wp_register_script( 'mp-gmap3',	'/' . MP_PATH . 'mp-includes/js/mp_gmap3.js', array('google-map', 'schedule'), false, 1);
		wp_localize_script( 'mp-gmap3', 	'mp_gmapL10n', array(
			'id'		=> $_GET['id'],
			'type'	=> 'mp_user',
			'url'		=> site_url() . '/' . MP_PATH . 'mp-admin/images/',
			'ajaxurl'	=> MP_Action_url,
			'center'	=> esc_js(__('Center', MP_TXTDOM)),
			'changemap'	=> esc_js(__('Change map', MP_TXTDOM))
		));
		$scripts[] = 'mp-gmap3';

	// markerclusterer
		wp_register_script( 'mp-markerclusterer',	'/' . MP_PATH . 'mp-includes/js/markerclusterer/markerclusterer_compiled.js', false, false, 1);
		$scripts[] = 'mp-markerclusterer';

		return $scripts;
	}

	function meta_box($mp_user)
	{
	// u006
		global $wpdb;
		$m = array();

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT ip, user_id FROM $wpdb->mp_tracks WHERE user_id = %d ;", $mp_user->id) );

		if ($tracks)
		{
			foreach($tracks as $track)
			{
				$y = MP_Ip::get_all($track->ip);
				if ($y)
				{
					$x = $y['geo'];
					if (!isset($def_lat) && isset($x['lat'])) $def_lat = $x['lat'];
					if (!isset($def_lng) && isset($x['lng'])) $def_lng = $x['lng'];
					$x['ip'] = $track->ip;

					if (isset($y['html']))     $x['info']  = str_replace('"', '&quote;', $y['html']);
					if (isset($y['provider'])) $x['info'] .= str_replace('"', '&quote;', "<div><p style='margin:3px;'><i><small>" . sprintf(__('ip data provided by %1$s', MP_TXTDOM), $y['provider']['credit']) . "</small></i></p></div>");

					$m['t006'][] = $x;
				}
			}
		}
?>
<script type='text/javascript'>
/* <![CDATA[ */
<?php
	// t006_user_settings
		$u['t006_user_settings'] = MP_User_meta::get($mp_user->id, '_MailPress_' . self::prefix);
		if (!$u['t006_user_settings']) $u['t006_user_settings'] = get_user_option('_MailPress_' . self::prefix);
		if (!isset($def_lat)) $def_lat = 48.8352;
		if (!isset($def_lng)) $def_lng = 2.4718;
		if (!$u['t006_user_settings']) $u['t006_user_settings'] = array('center_lat' => $def_lat, 'center_lng' => $def_lng, 'zoomlevel' => 3, 'maptype' => 'NORMAL');
		$u['t006_user_settings']['prefix'] = self::prefix;

		$eol = "";
		foreach ( $u as $var => $val ) {
			echo "var $var = " . MP_AdminPage::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";

		$eol = "";
		foreach ( $m as $var => $val ) {
			echo "var $var = " . MP_AdminPage::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";
?>
/* ]]> */
</script>
		<div id='<?php echo self::prefix; ?>_map' style='height:500px;width:auto;padding:0;margin:0;overflow:hidden;'></div>
<?php 	
		foreach($u['t006_user_settings'] as $k => $v) 
		{
                if ('prefix' == $k) continue;
?>
		<input type='hidden' id='<?php echo self::prefix . '_' . $k; ?>' value="<?php echo $v; ?>" />
<?php
		}
	}
}
new MP_Tracking_metabox_u006(__('Geoip', MP_TXTDOM));