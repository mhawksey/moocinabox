<?php
class MP_Form_field_type_date extends MP_form_field_type_
{
	var $file			= __FILE__;

	var $id			= 'date';
	var $field_not_input 	= true;

	var $category = 'composite';
	var $order			= 70;

	function get_name($field) { return $this->prefix.'['.$field->form_id . ']['. $field->id . ']' . ( (isset($field->settings['options']['is'])) ? '[' . $field->settings['options']['is'] . ']' : '' ); }
	function get_id($field)   { return $this->prefix  .  $field->form_id . '_' . $field->id .       ( (isset($field->settings['options']['is'])) ? '_' . $field->settings['options']['is'] : '' ); }
	public static function valid_date($y, $m, $d) { $feb = ((($y % 4 == 0) && ( (!($y % 100 == 0)) || ($y % 400 == 0))) ? 29 : 28 );  $maxd = array(31,$feb,31,30,31,30,31,31,30,31,30,31); if ($d > $maxd[$m - 1]) return false; return true; }

	function submitted($field)
	{
		$value	= $_POST[$this->prefix][$field->form_id][$field->id];

		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty 	= ( empty($value['y']) || empty($value['m']) || empty($value['d']) );

		if ($required && $empty)
		{
			$field->submitted['on_error'] = 1;
			return $field;
		}
		if (!$empty)
		{
			if (!self::valid_date( $value['y'], $value['m'], $value['d']))
			{
				$field->submitted['on_error'] = 1;
				return $field;
			}
		}

		$format = $field->settings['options']['mail_date_format'];
		if (empty($format)) $format = get_option('date_format');

		$field->submitted['value'] = $value;
		$field->submitted['text']  =  mysql2date($format, mktime(0, 0, 0, $value['m'], $value['d'], $value['y'] ));

		return $field;
	}

	function attributes_filter($no_reset)
	{
		$this->field->settings['options']['is'] = 'y';
		$id_y = $this->get_id($this->field);

		$this->field->settings['options']['is'] = 'm';
		$id_m = $this->get_id($this->field);

		$this->field->settings['options']['is'] = 'd';
		$id_d = $this->get_id($this->field);

		$onchange = "var y=document.getElementById('" . $id_y . "').value;var feb=(((y%4==0)&&((!(y%100==0))||(y%400==0)))?29:28);var maxd=new Array(31,feb,31,30,31,30,31,31,30,31,30,31);var m=document.getElementById('" . $id_m . "').value;var d=document.getElementById('" . $id_d . "');for(var j=31;j>28;j--){var x=document.getElementById('" . $id_d . "_'+j);if(x){if(j>maxd[m-1]){x.style.display='none';if(d.value==j)d.selectedIndex=maxd[m-1]-1}else{x.style.display='block'}}}";
		$onchange = "onchange=\"$onchange\"";
		$this->field->settings['attributes']['misc'] = (isset($this->field->settings['attributes']['misc'])) ? $this->field->settings['attributes']['misc'] . " $onchange" : $onchange;

// years
		$start	= (isset($this->field->settings['options']['year_start_c'])) ? date('Y') : $this->field->settings['options']['year_start'];
		$max		= (isset($this->field->settings['options']['year_end_c']))   ? date('Y') : $this->field->settings['options']['year_end'];
   		$selected_y	= (isset($this->field->settings['options']['value']['y']))   ? $this->field->settings['options']['value']['y'] : date('Y');
		$this->field->settings['options']['tag_content_y'] = MP_::select_number($start, $max, $selected_y, 1, false);
// months
		$start	= 0;
		$month_f	= $this->field->settings['options']['form_month_format'];
   		$selected_m 	= (isset($this->field->settings['options']['value']['m']))   ? $this->field->settings['options']['value']['m'] : date('m');
		if ($month_f != 'n') $wpl = new WP_Locale();
		do { $start++; $k = $start; if ($k < 10) $k = '0' . $k; $v = $k; if ('s' == $month_f) $v = $wpl->get_month_abbrev($wpl->get_month($start)); if ('f' == $month_f) $v = $wpl->get_month($start); $list[$k] = $v; } while ($start < 12);
		$this->field->settings['options']['tag_content_m'] = MP_::select_option($list, $selected_m, false);
// days
		$start = 0; $days = '';
		$selected_d = (isset($this->field->settings['options']['value']['d']))   ? $this->field->settings['options']['value']['d'] : date('d');
		$feb = ((($selected_y % 4 == 0) && ( (!($selected_y % 100 == 0)) || ($selected_y % 400 == 0))) ? 29 : 28 ); $maxd = array(31,$feb,31,30,31,30,31,31,30,31,30,31); if ($selected_d > $maxd[$selected_m - 1]) $selected_d = $maxd[$selected_m - 1] - 1;
		do { $start++; $k = $start; if ($k < 10) $k = '0' . $k; $v = $k; $style = ($start > $maxd[$selected_m - 1]) ? " style='display:none;'" : ''; $days .="<option id=\"" . $id_d . '_' . $k . "\" value=\"$k\"" . MP_::selected($selected_d, $k, false)  . "$style>$k</option>"; } while ($start < 31); $days = "\n$days\n";
		$this->field->settings['options']['tag_content_d'] = $days;

		if (!$no_reset) return;
		
		$this->field->settings['options']['value'] = $_POST[$this->prefix][$this->field->form_id][$this->field->id];

		$html = MP_Form_field_type_select::no_reset( $this->field->settings['options']['tag_content_y'], $this->field->settings['options']['value']['y'] );
		$this->field->settings['options']['tag_content_y'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		$html = MP_Form_field_type_select::no_reset( $this->field->settings['options']['tag_content_m'], $this->field->settings['options']['value']['m'] );
		$this->field->settings['options']['tag_content_m'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		$html = MP_Form_field_type_select::no_reset( $this->field->settings['options']['tag_content_d'], $this->field->settings['options']['value']['d'] );
		$this->field->settings['options']['tag_content_d'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';

		$this->attributes_filter_css();
	}

	function build_tag()
	{

		$this->field->type = 'select';
// years
		$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_y'];
		$this->field->settings['options']['is'] = 'y';
		$id_y = $this->get_id($this->field);
		$tag_y = parent::build_tag();
// months
		$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_m'];
		$this->field->settings['options']['is'] = 'm';
		$id_m = $this->get_id($this->field);
		$tag_m = parent::build_tag();
// days
		$this->field->settings['attributes']['tag_content'] = $this->field->settings['options']['tag_content_d'];
		$this->field->settings['options']['is'] = 'd';
		$id_d = $this->get_id($this->field);
		$tag_d = parent::build_tag();

		$this->field->type = $this->id;

		$form_formats['ymd'] = '{{y}}&#160;{{m}}&#160;{{d}}';
		$form_formats['dmy'] = '{{d}}&#160;{{m}}&#160;{{y}}';
		$form_formats['mdy'] = '{{m}}&#160;{{d}}&#160;{{y}}';

		$form_formats = $this->get_formats($form_formats);

		$search[] = '{{y}}';	$replace[] = '%1$s';
		$search[] = '{{id_y}}';	$replace[] = '%2$s';
		$search[] = '{{m}}'; 	$replace[] = '%3$s';
		$search[] = '{{id_m}}';	$replace[] = '%4$s';
		$search[] = '{{d}}'; 	$replace[] = '%5$s';
		$search[] = '{{id_d}}';	$replace[] = '%6$s';

		$html = str_replace($search, $replace, $form_formats[$this->field->settings['options']['form_date_format']] );
		return sprintf($html, $tag_y, $id_y, $tag_m, $id_m, $tag_d, $id_d);
	}
}
new MP_Form_field_type_date(__('Date', MP_TXTDOM));