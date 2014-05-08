<?php
class MP_Form_field_type_select extends MP_form_field_type_
{
	var $file			= __FILE__;

	var $id			= 'select';
	var $field_not_input 	= true;

	var $category 		= 'html';
	var $order			= 30;

	const sep			= '::';

	function get_name($field) { return (isset($field->settings['attributes']['multiple'])) ? parent::get_name($field) . '[]' : parent::get_name($field); }

	function submitted($field)
	{
		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty	= (!isset($_POST[$this->prefix][$field->form_id][$field->id]));

		if ($required && $empty)
		{
			$field->submitted['on_error'] = 1;
			return $field;
		}	

		if ($empty)
		{
			$field->submitted['value'] = false;
			$field->submitted['text']  = __('nothing selected', MP_TXTDOM);
			return $field;
		}

		if (!is_array($_POST[$this->prefix][$field->form_id][$field->id])) return parent::submitted($field);

		$field->submitted['value'] = $_POST[$this->prefix][$field->form_id][$field->id];
		$field->submitted['text']  = '';
		$i = 0;
		foreach($field->submitted['value'] as $v)
		{
			$i++;
			if ($i == 1) 	$field->submitted['text'] .= $v . ( (count($field->submitted['value']) > 1)   ? ', ' : '' );
			else			$field->submitted['text'] .= $v . ( (count($field->submitted['value']) != $i) ? ', ' : '' );
		}
		return $field;
	}

	function attributes_filter($no_reset)
	{
		$html = $this->get_select_options(base64_decode($this->field->settings['attributes']['tag_content']), $no_reset);
		$this->field->settings['attributes']['tag_content'] = ($html) ? $html : '<!-- ' . htmlspecialchars( __('invalid select options', MP_TXTDOM) ) . ' -->';
		$this->attributes_filter_css();
	}

	function get_select_options($options, $no_reset)
	{
		$is_options = ( ( ('<option' == substr($options, 0, 7)) || ('<optgroup' == substr($options, 0, 9)) ) && ( ('</option>' == substr($options, -9)) || ('</optgroup>' == substr($options, -11)) ) );
		if (!$is_options) $options = self::convert_custom_format($options);
		if (!$options) return false;
		if (!$no_reset) return $options;
		return self::no_reset($options, ( (isset($_POST[$this->prefix][$this->field->form_id][$this->field->id])) ? $_POST[$this->prefix][$this->field->form_id][$this->field->id] : null ) );
	}
	public static function convert_custom_format($options)
	{
		$datas = explode("\n", $options);
		unset($options);
		$selected = array();
		if (count($datas) > 0)
		{
			foreach($datas as $data)
			{
				$ys = explode(self::sep, $data);
				if (count($ys) < 2) continue;
				$k = esc_attr(trim($ys[0]));
				$options[$k] = trim($ys[1]);
				if (isset($ys[2]) && ('selected' == trim($ys[2]))) $selected[] = $k;
			}
		}
		return (isset($options)) ? MP_::select_option($options, $selected, false) : false;
	}
	public static function no_reset($options, $post_value)
	{
		$xml = MP_Xml::sanitize($options, 'select');
		if (!$xml) return false;

		$options = new MP_Xml($xml);
		$options->object = self::get_options_selected($options->object, $post_value);
		$new_options = $options->get_xml($options->object);
		$x = substr($new_options, 30, -10);
		return $x;
	}
	public static function get_options_selected($options, $post_value)
	{
		switch (true)
		{
			case (isset($options->children)) :
				$options->children = self::get_options_selected($options->children, $post_value);
			break;
			case (is_array($options)) :
				foreach($options as $k => $option) $options[$k] = self::get_options_selected($option, $post_value);
			break;
			default :
				$value = (isset($options->attributes['value'])) ? $options->attributes['value'] : $options->textValue;

				unset($options->attributes['selected']);
				if ( is_array($post_value) && in_array($value, $post_value ) ) $options->attributes['selected'] = 'selected';
				if ( $value == $post_value ) $options->attributes['selected'] = 'selected';
			break;
		}
		return $options;
	}
}
new MP_Form_field_type_select(__('Drop-down list', MP_TXTDOM));