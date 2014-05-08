<?php
class MP_Form_field_type_file extends MP_form_field_type_
{
	var $file	= __FILE__;

	var $id	= 'file';

	var $category = 'html';
	var $order	= 99;

	function get_name($field) { return $this->prefix . $field->form_id . '_' . $field->id; }
	function have_file($have_file) { return true; } // have file loading ?

	function submitted($field)
	{
		$name		= $this->get_name($field);

		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty 	= (!isset($_FILES[$name]) || empty($_FILES[$name]['name']) );

		if ($empty)
		{
			if ($required)
			{
				$field->submitted['on_error'] = true;
				return $field;
			}
			$field->submitted['value'] = false;
			$field->submitted['text']  = __('no file', MP_TXTDOM);
			return $field;
		}
		$field->submitted['file'] = $name;

		$i = 0;
		$field->submitted['text']  = '';
		$attributes = array('name', 'type', 'tmp_name', 'error', 'size');

		foreach($attributes as $attribute) if (isset($_FILES[$name][$attribute])) $field->submitted['value'][$attribute] = $_FILES[$name][$attribute];
		foreach($field->submitted['value'] as $attribute => $v)
		{
			$i++;
			if ($i == 1) 	$field->submitted['text'] .= "$attribute : " . ( (!empty($v)) ? "$v " : '<small>[<i>' . __('empty', MP_TXTDOM) . '</i>]</small>' ) . ( (count($field->submitted['value']) > 1)   ? ', ' : '' );
			else			$field->submitted['text'] .= "$attribute : " . ( (!empty($v)) ? "$v " : '<small>[<i>' . __('empty', MP_TXTDOM) . '</i>]</small>' ) . ( (count($field->submitted['value']) != $i) ? ', ' : '' );
		}
		return $field;
	}

	function attributes_filter($no_reset)
	{
		if (!$no_reset) return;

		$this->attributes_filter_css();
	}
}
new MP_Form_field_type_file(__('File select', MP_TXTDOM));