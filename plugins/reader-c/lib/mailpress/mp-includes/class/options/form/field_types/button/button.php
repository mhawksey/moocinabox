<?php
class MP_Form_field_type_button extends MP_form_field_type_
{
	var $file	= __FILE__;

	var $id 	= 'button';

	var $category = 'html';
	var $order	= 100;

	function submitted($field)
	{
		if (!isset($_POST[$this->prefix][$field->form_id][$field->id]))
		{
			$field->submitted['value'] = false;
			$field->submitted['text']  = __('not selected', MP_TXTDOM);
			return $field;
		}
		return parent::submitted($field);
	}
}
new MP_Form_field_type_button(__('Button', MP_TXTDOM));