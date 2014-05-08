<?php
class MP_Form_field_type_radio extends MP_form_field_type_
{
	var $file	= __FILE__;

	var $id	= 'radio';

	var $category = 'html';
	var $order	= 50;

	function submitted($field)
	{
		$value = (isset($_POST[$this->prefix][$field->form_id][$this->prefix . $field->settings['attributes']['name']])) ? $_POST[$this->prefix][$field->form_id][$this->prefix . $field->settings['attributes']['name']] : false;

		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty 	= ($value === false) ? true : false;

		if ($required && $empty)
		{
			$field->submitted['on_error'] = 1;
			return $field;
		}

		if ($value === $field->settings['attributes']['value'])
		{
			$field->submitted['value'] = $value;
			$field->submitted['text']  = sprintf(__('"%1$s" checked', MP_TXTDOM), $value);
			return $field;
		}

		return $field;
	}

	function attributes_filter($no_reset)
	{
		if (!$no_reset) return;

		unset($this->field->setting['attributes']['checked']);
		if ($_POST[$this->prefix][$this->field->form_id][$this->prefix . $this->field->settings['attributes']['name']] == $this->field->settings['attributes']['value']) $this->field->settings['attributes']['checked'] = 'checked';

		$this->attributes_filter_css();
	}
}
new MP_Form_field_type_radio(__('Radio Button', MP_TXTDOM));