<?php
class MP_Form_field_type_geotag extends MP_form_field_type_
{
	var $file	= __FILE__;

	var $id	= 'geotag';

	var $category = 'composite';
	var $order	= 96;

	function get_name($field) { return (isset($field->settings['options']['is'])) ? $this->prefix . '[' . $field->form_id . ']['. $field->id . '][' . $field->settings['options']['is'] . ']' : $this->prefix . '[' . $field->form_id . ']['. $field->id . ']'; }
	function get_id($field)   { return (isset($field->settings['options']['is'])) ? $this->prefix .       $field->form_id .  '_'. $field->id .  '_' . $field->settings['options']['is']       : $this->prefix .       $field->form_id .  '_'. $field->id; }

	function submitted($field)
	{
		$options = $field->settings['googlemap'];

		$value	= $_POST[$this->prefix][$field->form_id][$field->id];

		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty 	= ( empty($value['lat']) || empty($value['lng']) );
		$geotag_ok 	= true;

		if ($required && $empty)
		{
			$field->submitted['on_error'] = 1;
			return $field;
		}

		if (isset($options['lat_lng']) && !isset($options['lat_lng_disabled'])) if (($value['lat'] != (string)(float)$value['lat']) || ($value['lng'] != (string)(float)$value['lng'])) $geotag_ok = false;

		if (!$geotag_ok)
		{
			$field->submitted['on_error'] = 2;
			return $field;
		}

		$value['reverse_geocoding'] = MP_Ip::get_address($value['lat'], $value['lng']);

		$width  = (float) $field->settings['googlemap']['width'];
		$height = (float) $field->settings['googlemap']['height'];
		if ($width  > 640) $width  = 640;
		if ($height > 640) $height = 640;
		$static_map  = 'http://maps.googleapis.com/maps/api/staticmap?';
		$static_map .= 'center=' . $value['center_lat'] . ',' . $value['center_lng'];
		$static_map .= '&zoom=' . $value['zoomlevel'];
		$static_map .= "&size=$width" . 'x' . $height;
		$static_map .= '&maptype='; 
		switch ($value['maptype']) { case 'SATELLITE' : $static_map .= 'satellite'; break; case 'HYBRID' : $static_map .= 'hybrid'; break; case 'PHYSICAL' : $static_map .= 'terrain'; break; case 'TERRAIN' : $static_map .= 'terrain'; break; default : $static_map .= 'roadmap'; break; }
		$static_map .= '&markers=' . $value['lat'] . ',' . $value['lng'];
		$static_map .= '&sensor=false';

		$field->submitted['value'] = $value;
		$field->submitted['text']  = '';
		$field->submitted['text']  = 'lat : ' . $value['lat'] . ' lng : ' . $value['lng'] . "<br />\n\r";
		$field->submitted['text'] .= __('Reverse geocoding :', MP_TXTDOM) . ' ' . ( (!empty($value['reverse_geocoding'])) ? $value['reverse_geocoding'] : '<small>[<i>' . __('empty', MP_TXTDOM) . '</i>]</small>' ) . "<br />\n\r";
		if (isset($value['geocode'])) $field->submitted['text'] .= ( (!empty($value['geocode'])) ? 'geocode : ' . $value['geocode'] : '<small>[<i>' . __('empty', MP_TXTDOM) . '</i>]</small>' ) . "<br />\n\r";
		$field->submitted['map']   = $static_map;

		return $field;
	}

	function attributes_filter($no_reset)
	{
		$ip = $_SERVER['REMOTE_ADDR'];

		$xlatlng = MP_Ip::get_latlng($ip);
		$options = $this->field->settings['googlemap'];

		$options['lat'] = $options['center_lat'] = ($xlatlng) ? (float) trim($xlatlng['lat']) : (float) trim($options['lat']);
		$options['lng'] = $options['center_lng'] = ($xlatlng) ? (float) trim($xlatlng['lng']) : (float) trim($options['lng']);

		if (isset($options['geocode'])) $options['geocode_value'] = MP_Ip::get_address($options['lat'], $options['lng']);
		$this->field->settings['googlemap'] = $options;

		if (!$no_reset) return;

		$Post = $_POST[$this->prefix][$this->field->form_id][$this->field->id];
		$options['lat'] 		= (float) $Post['lat'];
		$options['lng'] 		= (float) $Post['lng'];
		$options['center_lat'] 	= (float) $Post['center_lat'];
		$options['center_lng'] 	= (float) $Post['center_lng'];
		$options['zoomlevel'] 	= $Post['zoomlevel'];
		$options['maptype'] 	= $Post['maptype'];

		if (isset($options['geocode'])) $options['geocode_value'] =  esc_attr($_POST[$this->prefix][$this->field->form_id][$this->field->id]['geocode']);
		
		$this->field->settings['googlemap'] = $options;

		$this->attributes_filter_css();
	}

	function build_tag()
	{
		$options = $this->field->settings['googlemap'];
	//map
		$this->field->settings['options']['is'] = 'map';
		$id_map	= $this->get_id($this->field);
		$style	= " style='overflow:hidden;width:" . trim($options['width']) . ';height:' . trim($options['height']) . ";'";
		$tag_map	= "<div id='$id_map'$style></div>";
		//zoomlevel
			$this->field->settings['attributes']['type'] = 'hidden';

			$this->field->settings['attributes']['value'] = $options['zoomlevel'];
			$this->field->settings['options']['is'] = 'zoomlevel';
			$tag_map	 .= parent::build_tag();
		//maptype
			$this->field->settings['attributes']['value'] = $options['maptype'];
			$this->field->settings['options']['is'] = 'maptype';
			$tag_map	 .= parent::build_tag();
		//center_lat
			$this->field->settings['attributes']['value'] = $options['center_lat'];
			$this->field->settings['options']['is'] = 'center_lat';
			$tag_map	 .= parent::build_tag();
		//center_lng
			$this->field->settings['attributes']['value'] = $options['center_lng'];
			$this->field->settings['options']['is'] = 'center_lng';
			$tag_map	 .= parent::build_tag();

	// lat, lng
		$tag_lat = $tag_lng = $id_lat_d = $id_lng_d = '';
		$this->field->type = 'text';
		if (isset($options['lat_lng']))
		{
			if (!isset($options['lat_lng_disabled']))
			{
				// lat lng text
				$this->field->settings['attributes']['type'] = 'text';
				$this->field->settings['attributes']['size']  = $options['lat_lng_size'];
				$this->field->settings['attributes']['value'] = $options['lat'];

				$this->field->settings['options']['is'] = 'lat';
				$id_lat	= $this->get_id($this->field);
				$tag_lat	= parent::build_tag();

				$this->field->settings['attributes']['value'] = $options['lng'];

				$this->field->settings['options']['is'] = 'lng';
				$id_lng	= $this->get_id($this->field);
				$tag_lng	= parent::build_tag();
			}
			else
			{
				// lat lng text 			id et name differents
				$this->field->settings['attributes']['type'] = 'text';
				$this->field->settings['attributes']['size']  = $options['lat_lng_size'];
				$this->field->settings['attributes']['value'] = $options['lat'];
				$this->field->settings['attributes']['disabled'] = 'disabled';

				$this->field->settings['options']['is'] = 'lat_d';
				$id_lat_d	= $this->get_id($this->field);
				$tag_lat	= parent::build_tag();

				$this->field->settings['attributes']['value'] = $options['lng'];

				$this->field->settings['options']['is'] = 'lng_d';
				$id_lng_d	= $this->get_id($this->field);
				$tag_lng	= parent::build_tag();

				// lat lng hidden
				unset($this->field->settings['attributes']['disabled'], $this->field->settings['attributes']['size']);

				$this->field->settings['attributes']['type'] = 'hidden';
				$this->field->settings['attributes']['value'] = $options['lat'];

				$this->field->settings['options']['is'] = 'lat';
				$id_lat	= $this->get_id($this->field);
				$tag_map	.= parent::build_tag();

				$this->field->settings['attributes']['value'] = $options['lng'];

				$this->field->settings['options']['is'] = 'lng';
				$id_lng	= $this->get_id($this->field);
				$tag_map	.= parent::build_tag();
			}
		}
		else
		{
			// lat lng hidden
			unset($this->field->settings['attributes']['disabled'], $this->field->settings['attributes']['size']);

			$this->field->settings['attributes']['type'] = 'hidden';
			$this->field->settings['attributes']['value'] = $options['lat'];

			$this->field->settings['options']['is'] = 'lat';
			$id_lat	= $this->get_id($this->field);
			$tag_map	.= parent::build_tag();

			$this->field->settings['attributes']['value'] = $options['lng'];

			$this->field->settings['options']['is'] = 'lng';
			$id_lng	= $this->get_id($this->field);
			$tag_map	.= parent::build_tag();
		}

	// geocode
		$id_geocode = $tag_geocode = $id_geocode_button	= $tag_geocode_button = '';
		if (isset($options['geocode'])) 
		{
		// input text
			unset($this->field->settings['attributes']['disabled']);

			$this->field->settings['attributes']['type']  = 'text';
			$this->field->settings['attributes']['size']  = $options['geocode_size'];
			$this->field->settings['attributes']['value'] = (isset($options['geocode_value'])) ? $options['geocode_value'] : '';

			$this->field->settings['options']['is'] = 'geocode';
			$id_geocode		= $this->get_id($this->field);
			$tag_geocode	= parent::build_tag();

		// button
			$this->field->type = 'button';
			unset($this->field->settings['attributes']['size']);

			$this->field->settings['attributes']['type'] = 'button';
			$this->field->settings['attributes']['value'] = $options['geocode_button'];

			$this->field->settings['options']['is'] = 'geocode_button';
			$id_geocode_button	= $this->get_id($this->field);
			$tag_geocode_button	= parent::build_tag();
		}

	// javascript
		$js = '';
		if (!defined('MP_FORM_GEOTAG'))
		{
			define ('MP_FORM_GEOTAG', true);
			if (!isset($options['gmap']))   $js .= "\n<script type='text/javascript' src='http://maps.googleapis.com/maps/api/js?sensor=false'></script>";
			if (!isset($options['jQuery'])) $js .= "\n<script type='text/javascript' src='" . site_url() . "/wp-includes/js/jquery/jquery.js'></script>";

			$m = array( 'mp_gmapL10n'	=> array(	'url'		=> site_url() . '/' . MP_PATH . 'mp-admin/images/', 
										'center'	=> esc_js(__('Center on marker', MP_TXTDOM)), 
										'rgeocode'	=> esc_js(__('Find marker address', MP_TXTDOM)), 
										'changemap'	=> esc_js(__('Change map', MP_TXTDOM))
								)
				);
			$js .= "\n<script type='text/javascript'>\n/* <![CDATA[ */\n";
			foreach ( $m as $var => $val ) $js .= "var $var = " . MP_::print_scripts_l10n_val($val);
			$js .= ";\n/* ]]> */\n</script>";
			$js .= "\n<script type='text/javascript' src='" . site_url() . '/' . MP_PATH . "mp-includes/js/mp_field_type_geotag.js'></script>\n";
		}

		$x = array();
		$x['form'] = $this->field->form_id; $x['field'] = $this->field->id;
		foreach (array('lat', 'lng', 'center_lat', 'center_lng', 'maptype', 'zoomlevel', 'zoom', 'changemap', 'center', 'lat_lng', 'lat_lng_disabled', 'rgeocode') as $opt) $x[$opt] = (isset($options[$opt])) ? $options[$opt] : '0';
		$m = array( 'mp_field_type_geotag_' . $this->field->form_id .  '_'. $this->field->id => $x );
		$js .= "\n<script type='text/javascript'>\n/* <![CDATA[ */\n";
		foreach ( $m as $var => $val ) $js .= "var $var = " . MP_::print_scripts_l10n_val($val);
		$js .= ";\njQuery(document).ready( function() { var mp_form_" . $this->field->form_id . '_' . $this->field->id  . " = new mp_field_type_geotag(mp_field_type_geotag_" . $this->field->form_id .  '_'. $this->field->id . "); } );\n/* ]]> */\n</script>";

	//end
		$this->field->type = $this->id;

		$sf  = '';
		$sf  = (isset($options['lat_lng'])) ? 'latlng' : '';
		$sf .= (isset($options['geocode'])) ? ( (empty($sf)) ? 'geocode' : '_geocode' ) : '';
		if (empty($sf)) $sf = 'alone';

		$form_formats['alone']		=  '{{map}}';
		$form_formats['latlng']		=  '{{map}}lat:{{lat}}&#160;lng:{{lng}}';
		$form_formats['geocode']	=  '{{map}}{{geocode}}&#160;{{geocode_button}}';
		$form_formats['latlng_geocode']=  '{{map}}lat:{{lat}}&#160;lng:{{lng}}<br />{{geocode}}&#160;{{geocode_button}}';

		$form_formats = $this->get_formats($form_formats);

		$search[] = '{{map}}';			$replace[] = '%1$s';
		$search[] = '{{id_map}}';		$replace[] = '%2$s';
		$search[] = '{{lat}}'; 			$replace[] = '%3$s';
		$search[] = '{{id_lat}}'; 		$replace[] = '%4$s';
		$search[] = '{{lng}}'; 			$replace[] = '%5$s';
		$search[] = '{{id_lng}}';		$replace[] = '%6$s';
		$search[] = '{{geocode}}';		$replace[] = '%7$s';
		$search[] = '{{id_geocode}}';		$replace[] = '%8$s';
		$search[] = '{{geocode_button}}';	$replace[] = '%9$s';
		$search[] = '{{id_geocode_button}}';$replace[] = '%10$s';
   		$search[] = '{{id_lat_dis}}';		$replace[] = '%11$s';
		$search[] = '{{id_lng_dis}}';		$replace[] = '%12$s';

		$html = str_replace($search, $replace, $form_formats[$sf]);
		return sprintf($html, $tag_map, $id_map, $tag_lat, $id_lat, $tag_lng, $id_lng, $tag_geocode, $id_geocode, $tag_geocode_button, $id_geocode_button, $id_lat_d, $id_lng_d) . "\n$js\n";
	}
}
new MP_Form_field_type_geotag(__('Geotag', MP_TXTDOM));