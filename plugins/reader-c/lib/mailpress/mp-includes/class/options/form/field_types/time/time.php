<?php
class MP_Form_field_type_time extends MP_form_field_type_
{
	var $file			= __FILE__;

	var $id			= 'time';
	var $field_not_input 	= true;

	var $category 		= 'composite';
	var $order			= 71;

	function get_name($field) { return $this->prefix.'['.$field->form_id . ']['. $field->id . ']' .  ( (isset($field->settings['options']['is'])) ? ( '[' . ( ($field->settings['options']['is'] == 'am_pm') ? $this->prefix . $field->settings['options']['is'] : $field->settings['options']['is'] ) . ']' ) : '' ) ; }
	function get_id($field)   { return $this->prefix  .  $field->form_id . '_' . $field->id .        ( (isset($field->settings['options']['is'])) ? ( '_' . ( ($field->settings['options']['is'] == 'am_pm') ? $field->settings['attributes']['value']           : $field->settings['options']['is'] )       ) : '' ) ; }
	public static function valid_date($y, $m, $d) { $feb = ((($y % 4 == 0) && ( (!($y % 100 == 0)) || ($y % 400 == 0))) ? 29 : 28 );  $maxd = array(31,$feb,31,30,31,30,31,31,30,31,30,31); if ($d > $maxd[$m - 1]) return false; return true; }

	function submitted($field)
	{
		if (isset($_POST[$this->prefix][$field->form_id][$field->id])) $value = $_POST[$this->prefix][$field->form_id][$field->id];

		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty 	= ( empty($value['h']) || empty($value['mn']) );

		if ($required && $empty)
		{
			$field->submitted['on_error'] = 1;
			return $field;
		}

		$format = $field->settings['options']['mail_time_format'];
		if (empty($format)) $format = get_option('time_format');

		$field->submitted['value'] = $value;
		$field->submitted['text']  = date($format, mktime($value['h'], $value['mn']));
		if (isset($value[$this->prefix . 'am_pm'])) 	$field->submitted['text'] .= ' '  . $value[$this->prefix . 'am_pm'];
		if (isset($value['tz'])) 				$field->submitted['text'] .= ' (' . $value['tz'] . ')'; 

		return $field;
	}

	function attributes_filter($no_reset)
	{
		if (!isset($this->field->settings['options']['form_time_init_value']))
			$this->field->settings['options']['form_time_init_value'] = '0';

		if ('1' == $this->field->settings['options']['form_time_init_value'])
		{
        		$this->field->settings['options']['form_time_init_value'] = '0';

			if ($x = MP_Ip::get_latlng($_SERVER['REMOTE_ADDR']))
			{
				$ip_url = 'http://ws.geonames.org/timezone?lat=' . $x['lat'] . '&lng=' .  $x['lng'] ;
				if ($content = @file_get_contents($ip_url))
				{
					if ($xml = simplexml_load_string($content))
					{
						$timestamp = strtotime((string) $xml->timezone->time);
						$tzstring  = (string) $xml->timezone->timezoneId;

						if (($timestamp > 0) && $tzstring)
							$this->field->settings['options']['form_time_init_value'] = '1';
					}
				}
			}
		}

		if ('0' == $this->field->settings['options']['form_time_init_value'])
		{
			$timestamp = strtotime(current_time( 'mysql' ));
			if (isset($this->field->settings['options']['form_timezones'])) 
			{
				$current_offset = get_option('gmt_offset');
				$tzstring = get_option('timezone_string');

				$check_zone_info = true;

				// Remove old Etc mappings.  Fallback to gmt_offset.
				if ( false !== strpos($tzstring,'Etc/GMT') )
					$tzstring = '';

				if (empty($tzstring)) { // set the Etc zone if no timezone string exists
					$check_zone_info = false;
					if ( 0 == $current_offset )
						$tzstring = 'UTC+0';
					elseif ($current_offset < 0)
						$tzstring = 'UTC' . $current_offset;
					else
						$tzstring = 'UTC+' . $current_offset;
				}
			}
		}
		$this->field->settings['options']['timestamp'] = $timestamp;
// hours
		$list 	= array();
		$start	= -1;
		$max		= ('0' == $this->field->settings['options']['form_time_format']) ? 23 : 12;
		do { $start++; $k = $start; if ($k < 10) $k = '0' . $k; $v = $k; $list[$k] = $v; } while ($start < $max);

   		$selectedh	= ('0' == $this->field->settings['options']['form_time_format']) ? date('H', $timestamp) : date('h', $timestamp);
		$this->field->settings['options']['tag_content_h'] = MP_::select_option($list, $selectedh, false);
// minutes
		$list 	= array();
		$start	= -1;
		$max		= 59;
		do { $start++; $k = $start; if ($k < 10) $k = '0' . $k; $v = $k; $list[$k] = $v; } while ($start < $max);

   		$selectedmn	= date('i');
		$this->field->settings['options']['tag_content_mn'] = MP_::select_option($list, $selectedmn, false);
// timezones
		if (isset($this->field->settings['options']['form_timezones'])) 
		{
			$this->field->settings['options']['tag_content_tz'] = wp_timezone_choice($tzstring);
		}

		if (!$no_reset) return;
		
		$this->field->settings['options']['value'] = $_POST[$this->prefix][$this->field->form_id][$this->field->id];

		$html = MP_Form_field_type_select::no_reset( $this->field->settings['options']['tag_content_h'], $this->field->settings['options']['value']['h'] );
		$this->field->settings['options']['tag_content_h'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		$html = MP_Form_field_type_select::no_reset( $this->field->settings['options']['tag_content_mn'], $this->field->settings['options']['value']['mn'] );
		$this->field->settings['options']['tag_content_mn'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		if (isset($this->field->settings['options']['form_timezones']))
		{	
			$html = MP_Form_field_type_select::no_reset( $this->field->settings['options']['tag_content_tz'], $this->field->settings['options']['value']['tz'] );
			$this->field->settings['options']['tag_content_tz'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		}

		$this->attributes_filter_css();
	}

	function build_tag()
	{
		$timestamp = $this->field->settings['options']['timestamp'];

		$this->field->type = 'select';
// hours
		$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_h'];
		$this->field->settings['options']['is'] = 'h';
		$id_h  = $this->get_id($this->field);
		$tag_h = parent::build_tag();
// minutes
		$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_mn'];
		$this->field->settings['options']['is'] = 'mn';
		$id_mn  = $this->get_id($this->field);
		$tag_mn = parent::build_tag();

// timezones
		$id_tz  = $tag_tz = '';
		if (isset($this->field->settings['options']['form_timezones']))
		{
			$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_tz'];
			$this->field->settings['options']['is'] = 'tz';
			$id_tz  = $this->get_id($this->field);
			$tag_tz = parent::build_tag();
		}

// am pm
		$tag_am = $id_am  = $text_am = $tag_pm = $id_pm  = $text_pm = '';
		if ('0' != $this->field->settings['options']['form_time_format'])
		{
			unset($this->field_not_input);

			$this->field->type = 'radio';
			$this->field->settings['attributes']['type']  = 'radio';
			$this->field->settings['attributes']['name']  = 'am_pm';
			$this->field->settings['options']['is'] = 'am_pm';

			$this->field->settings['attributes']['value'] = 'am';
			if (date('G', $timestamp) < 12) $this->field->settings['attributes']['checked'] = 'checked';
			$tag_am = parent::build_tag();
			$id_am  = $this->get_id($this->field);
			$text_am= __('am', MP_TXTDOM);

			$this->field->settings['attributes']['value'] = 'pm';
			if (date('G', $timestamp) >= 12) $this->field->settings['attributes']['checked'] = 'checked';
			$tag_pm = parent::build_tag();
			$id_pm  = $this->get_id($this->field);
			$text_pm= __('pm', MP_TXTDOM);

			$this->field_not_input = true;
		}

		$this->field->type = $this->id;

		$sf  = '';
		$sf  = ('0' != $this->field->settings['options']['form_time_format']) ? 'ampm' : '';
		$sf .= (isset($this->field->settings['options']['form_timezones']))   ? ( (empty($sf)) ? 'tz' : '_tz' ) : '';
		if (empty($sf)) $sf = 'alone';

		$form_formats['alone'] 		= '{{h}}&#160;:&#160;{{mn}}';
		$form_formats['ampm'] 		= '{{h}}&#160;:&#160;{{mn}}&#160;{{am}}&#160;<label id="{{id_am}}_label" for="{{id_am}}">{{text_am}}</label>&#160;{{pm}}&#160;<label id="{{id_pm}}_label" for="{{id_pm}}">{{text_pm}}</label>';
		$form_formats['tz'] 		= '{{h}}&#160;:&#160;{{mn}}&#160;{{tz}}';
		$form_formats['ampm_tz'] 	= '{{h}}&#160;:&#160;{{mn}}&#160;{{am}}&#160;<label id="{{id_am}}_label" for="{{id_am}}">{{text_am}}</label>&#160;{{pm}}&#160;<label id="{{id_pm}}_label" for="{{id_pm}}">{{text_pm}}</label>&#160;{{tz}}';

		$form_formats = $this->get_formats($form_formats);

		$search[] = '{{h}}';		$replace[] = '%1$s';
		$search[] = '{{id_h}}'; 	$replace[] = '%2$s';
		$search[] = '{{mn}}'; 		$replace[] = '%3$s';
		$search[] = '{{id_mn}}';	$replace[] = '%4$s';

		$search[] = '{{am}}';		$replace[] = '%5$s';
		$search[] = '{{id_am}}';	$replace[] = '%6$s';
		$search[] = '{{text_am}}';	$replace[] = '%7$s';

		$search[] = '{{pm}}';		$replace[] = '%8$s';
		$search[] = '{{id_pm}}';	$replace[] = '%9$s';
		$search[] = '{{text_pm}}';	$replace[] = '%10$s';

		$search[] = '{{tz}}';		$replace[] = '%11$s';
		$search[] = '{{id_tz}}';	$replace[] = '%12$s';

		$html = str_replace($search, $replace, $form_formats[$sf] );
		return sprintf($html, $tag_h, $id_h, $tag_mn, $id_mn, $tag_am, $id_am, $text_am, $tag_pm, $id_pm, $text_pm, $tag_tz, $id_tz);
	}
}
new MP_Form_field_type_time(__('Time', MP_TXTDOM));