<?php
class MP_Form_field_type_ereg extends MP_form_field_type_
{
	var $file	= __FILE__;

	var $id	= 'ereg';

	var $category = 'html';
	var $order	= 95;

	function submitted($field)
	{
		$value	= trim($_POST[$this->prefix][$field->form_id][$field->id]);

		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty 	= empty($value);
		$ereg_ok 	= true;

		if ($required)
		{
			if ($empty)
			{
				$field->submitted['on_error'] = 1;
				return $field;
			}
		}

		$pattern 	= $field->settings['options']['pattern'];
		if (!$empty && !empty($pattern)) $ereg_ok = (isset($field->settings['options']['ereg'])) ? @preg_match($pattern, $value) : @preg_match($pattern, strtolower($value));

		if (!$ereg_ok)
		{
			$field->submitted['on_error'] = 2;
			return $field;
		}
		return parent::submitted($field);
	}
}
new MP_Form_field_type_ereg(__('Ereg[i] Input ', MP_TXTDOM));