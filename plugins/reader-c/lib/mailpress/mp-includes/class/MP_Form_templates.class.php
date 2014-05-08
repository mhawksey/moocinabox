<?php
class MP_Form_templates
{
	var $templates = null;

	function __construct()
	{
   		// Load all templates.
		$root  = MP_CONTENT_DIR . 'advanced/forms';
		$root  = apply_filters('MailPress_advanced_forms_root', $root);
		$root .= '/templates';
		$dir  = @opendir($root);
		if ($dir) 
			while (($file = readdir($dir)) !== false) if (($file{0} != '.') && (substr($file, -4) == '.xml')) 
			{
				$f = file_get_contents("$root/$file");
				if (simplexml_load_string($f))
				{
					$xml = new MP_Xml($f);
					$this->templates[substr($file, 0, -4)] = self::xml_clean_up($xml->object->children);
				}
			}
		@closedir($dir);
	}

// xml 
	function xml_clean_up($items)
	{
		$tab = '';
		switch (true)
		{
			case (isset($items->children)) :
				$tab[$items->name] = self::xml_clean_up($items->children);
			break;
			case (is_array($items)) :
				foreach($items as $item) $tab[$item->name] = (isset($item->children)) ? self::xml_clean_up($item->children) : self::xml_clean_up($item);
			break;
			default :
				if (!empty($items->textValue)) $tab = $items->textValue;
			break;
		}
		return $tab;
	}

// templates
	function get_all()
	{
		foreach($this->templates as $k => $v) $templates[$k] = $k;
		return $templates;
	}

	function get($id)
	{
		return (isset($this->templates[$id])) ? $this->templates[$id] : $this->templates['default'];
	}

	function get_sub($id, $sub)
	{
		if (isset($this->templates[$id][$sub])) 				return $this->templates[$id][$sub];
		if (isset($this->templates['default'][$sub])) 			return $this->templates['default'][$sub];
		return false;
	}

	function get_template_sub($id, $sub, $field_template)
	{
		if (isset($this->templates[$id][$sub][$field_template])) 		return $this->templates[$id][$sub][$field_template];
		if (isset($this->templates[$id][$sub]['standard']))  			return $this->templates[$id][$sub]['standard'];
		if (isset($this->templates['default'][$sub][$field_template])) 	return $this->templates['default'][$sub][$field_template];
		if (isset($this->templates['default'][$sub]['standard'])) 		return $this->templates['default'][$sub]['standard'];
		return false;
	}

	// field
	function get_field($id) { return $this->get_sub($id, 'fields'); }
	function get_field_template($id, $field_template) { return $this->get_template_sub($id, 'fields', $field_template); }
	function get_all_fields($id)
	{
		foreach($this->get_field($id) as $k => $v) $subtemplates[$k] = $k;
		return $subtemplates;
	}

	// field_on_error
	function get_field_on_error_template($id, $field_template) { return $this->get_template_sub($id, 'fields_on_error', $field_template); }

	// groups
	function get_group($id) { return $this->get_sub($id, 'groups'); }
	function get_group_template($id, $fields_template) { return $this->get_template_sub($id, 'groups', $fields_template); }

	// composite field_types
	function get_composite_template($id, $field_type)
	{ 
		if (isset($this->templates[$id]['composite'][$field_type]))	return $this->templates[$id]['composite'][$field_type];
		return false;
	}

	// message
	function get_message_template($id, $ret)  //$ret->(ok/ko)
	{ 
		if (isset($this->templates[$id]['message'][$ret])) 		return $this->templates[$id]['message'][$ret];
		if (isset($this->templates[$id]['message']))  			return $this->templates[$id]['message'];
		return false;
	}

	// form
	function get_form_template($id) { return $this->get_sub($id, 'form'); }
}