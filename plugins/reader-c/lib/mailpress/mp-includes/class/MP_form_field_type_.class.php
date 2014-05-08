<?php
abstract class MP_form_field_type_
{
	var $field  = null;
	var $prefix = MailPress_form::prefix;

	function __construct($desc)
	{
		$this->desc = $desc;
		$this->settings	 = dirname($this->file) . '/settings.xml';

		add_filter('MailPress_form_field_types_register',				array($this, 'register'), 		8, 1);
		add_filter('MailPress_form_field_type_' . $this->id . '_have_file',	array($this, 'have_file'), 		8, 1);

		add_filter('MailPress_form_field_type_' . $this->id . '_submitted',	array($this, 'submitted'), 		8, 1);

		add_filter('MailPress_form_field_type_' . $this->id . '_get_tag',		array($this, 'get_tag'), 		8, 2);
		add_filter('MailPress_form_field_type_' . $this->id . '_get_id',		array($this, 'get_id'), 		8, 1);
		add_filter('MailPress_form_field_type_' . $this->id . '_get_name',	array($this, 'get_name'), 		8, 1);

		add_action('MailPress_form_field_type_' . $this->id . '_settings_form',	array($this, 'settings_form'),	8, 1);
	}

	function register($field_types) { $field_types[$this->id] = array( 'desc' => $this->desc, 'order' => $this->order ); return $field_types; }
	function have_file($have_file) { return $have_file; }

	function submitted($field)
	{
		if (!isset($_POST[$this->prefix][$field->form_id][$field->id])) return $field;

		$field->submitted['value'] = $_POST[$this->prefix][$field->form_id][$field->id];
		$field->submitted['text']  = stripslashes($_POST[$this->prefix][$field->form_id][$field->id]);
		return $field;
	}

	function get_tag($field, $no_reset)
	{
		unset($this->field);

		$this->field = $field;

		$this->attributes_filter($no_reset);

		return $this->build_tag();
	}

	function get_formats($default = '')
	{
		$form_template = MP_Form::get_template($this->field->form_id);
		if (!$form_template) return $default;

		$form_templates = new MP_Form_templates();
		$f = $form_templates->get_composite_template($form_template, $this->id);
		return (is_array($f)) ? array_merge($default, $f) : ((!empty($f)) ? $f : $default);
	}

	function attributes_filter($no_reset) 
	{
		if ($no_reset) $this->field->settings['attributes']['value'] = esc_attr(stripslashes($_POST[$this->prefix][$this->field->form_id][$this->field->id]));

		$this->attributes_filter_css();
	}

	function attributes_filter_css() 
	{
		if (!isset($this->field->submitted['on_error'])) return;
		$_classes = (isset($this->field->settings['controls']['class'])) ? trim($this->field->settings['controls']['class']) : '';
		if ( !empty($_classes) )
		{
			$add = $remove = array();
			$error_classes = explode(' ', $_classes);
			$classes       = explode(' ', $this->field->settings['attributes']['class']);
			foreach ($classes       as $k => $v) $classes[$k] = trim($v);
			foreach ($error_classes as $k => $v) { $v = trim($v); switch ($v[0]) { case '+' : $add[] = substr($v, 1); break; case '-' : $remove[] = substr($v, 1); break; default : $add[] = $v; break; } }
			$this->field->settings['attributes']['class'] = implode(' ', array_merge(array_diff($classes, $remove), $add) );
		}
		$_style = (isset($this->field->settings['controls']['style'])) ? trim($this->field->settings['controls']['style']) : '';
		if (!empty($_style)) $this->field->settings['attributes']['style'] .= $_style;
	}

	function build_tag()
	{
		$tag_content = $misc = '';
	// opening tag
		$tag  = '<';
		$tag .= (isset($this->field_not_input)) ? $this->field->type : 'input';
	// id
		$this->field->settings['attributes']['id']   = $this->get_id($this->field);
	// name
		$this->field->settings['attributes']['name'] = $this->get_name($this->field);
	// other attributes
		foreach ($this->field->settings['attributes'] as $attribute => $value)
		{
			if ('tag_content' == $attribute) { $tag_content = $value; continue; }
			if ('misc'        == $attribute) { $misc = trim($value); if ('' != $misc) $misc = " $misc"; continue; }
			if (''            != trim($value)) $tag .= $this->get_attribute($attribute, trim($value));
		} 
	// closing tag
		return $tag . ( (isset($this->field_not_input)) ? "$misc >$tag_content</" . $this->field->type . '>' : "$misc />" );
	}
	function get_attribute($attr, $value) { if ('value' == $attr) $value = esc_attr($value); $quote = (false !== strpos($value, '"')) ? "'" : '"'; return " $attr=$quote$value$quote"; 	}
	function get_name($field) { return (isset($field->settings['attributes']['name'])) ?  $this->prefix.'['.$field->form_id.']['.$this->prefix.$field->settings['attributes']['name'].']'	: $this->prefix.'['.$field->form_id . ']['. $field->id . ']'; }
	function get_id($field)   { return (isset($field->settings['attributes']['id']))   ?  $this->prefix  .  $field->form_id.'_'. $field->settings['attributes']['id'] 				: $this->prefix  .  $field->form_id . '_' . $field->id; }



	function settings_form($field)
	{
		$this->field = $field;
		$this->type_ok = ($this->id == $this->field->type);
		$protected = ( isset($this->field->settings['options']['protected']) && $this->field->settings['options']['protected'] );
		$has_controls = $has_controls_checked = false;
		if (method_exists($this, 'build_settings_form')) return $this->build_settings_form();

		ob_start();
			include($this->settings);
			$xml = trim(ob_get_contents());
		ob_end_clean();
		$xml = '<?xml version="1.0" ?>' . $xml;
		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		foreach ($xml->tabs->children() as $child) $tabs[$child->getName()] = (string) $child;
		if (isset($_GET['action']) && ('edit' == $_GET['action']) && $this->type_ok) { $tabs['html'] = __('Html', MP_TXTDOM); }

		if (empty($tabs)) return;
?>
<div id='field_type_<?php echo $this->id; ?>_settings' class='field_type_settings'<?php echo ( ($this->type_ok) ? '' : "style='display:none;'" ); ?>>
	<ul>
<?php 	foreach ($tabs as $tab_type => $tab) echo "<li><a href='#settings_tab_" . $this->id . '_' . $tab_type . "'><span>$tab</span></a></li>\n"; ?>
	</ul>
	<div style='clear:both;' >
<?php
		foreach ($tabs as $tab_type => $tab) 
		{
			echo "\n<div id='settings_tab_" . $this->id . '_' . $tab_type . "' class='ui-tabs settings_form_tabs settings_$tab_type'>\n";
			switch ($tab_type)
			{
				case 'html' :
					$form = MP_Form::get($this->field->form_id);
					echo "<textarea rows='5' cols='40' disabled='disabled'>" . htmlspecialchars(MP_Form::get_field($this->field, false, $form->template), ENT_QUOTES) . '</textarea>';
					echo '<p><small>' . sprintf(__('Templates : %1$s/%2$s', MP_TXTDOM), $form->template, $field->template) . '</small></p>';
				break;
				default :
					foreach ($xml->{$tab_type}->items as $items)
					{
						foreach ($items->children() as $child)
						{
							$attribute = $child->getName();
							foreach ($child->children() as $tags)
							{
								switch ($tags->getName())
								{
									case 'checkbox' :
										$checked = $this->settings_checkbox($tab_type, $attribute, $tags->value, (int) $tags->disabled, ( (isset($tags->class)) ? $tags->class : false), ( (isset($tags->forced)) ? $tags->forced : false)  );
										if ( isset($tags->class) && ('controls' == (string) $tags->class) ) $has_controls = true;
										if ($checked) $has_controls_checked = true;
										echo "<label for='" . $this->prefix . $this->id . '_settings_' . $tab_type . '_' . $attribute . '_' . $tags->value . "' class='inline' ><span class='description'><small>" . $tags->text . "</small></span></label>&#160;\n";
									break;
									case 'hidden' :
										$this->settings_hidden_value($tab_type, $attribute, $tags->value);
									break;
									case 'is' :
										$this->settings_description(__('initial state : ', MP_TXTDOM));
										$values = unserialize($tags->values);
										$disabled = unserialize($tags->disabled);
										foreach ($values as $attribute) 
										{ 
											$this->settings_checkbox( $tab_type, $attribute, $attribute, (in_array($attribute, $disabled)) );
											echo "<label for='" . $this->prefix . $this->id . '_settings_' . $tab_type . '_' . $attribute . '_' . $attribute . "' class='inline' ><span class='description'><small>$attribute</small></span></label>&#160;\n";
										}
									break;
									case 'misc' :
										$this->settings_text('attributes', 'misc', false, '', 39);
										echo "\n<br /><span class='description'><i style='color:#666;font-size:8px;'>" . ( (string) $tags ) . "</i></span><br />\n";
									break;
									case 'only_text' :
										echo "<span class='description'>" . (string) $tags . '</span>';
									break;
									case 'radio' :
										$this->settings_description($tags->text);
										$values = unserialize($tags->values);
										$disabled = unserialize($tags->disabled);
										foreach ($values as $value => $value_text) $this->settings_radio($tab_type, $attribute, $value, $value_text, $tags->default, in_array($value, $disabled) );
									break;
									case 'select_num' :
										echo "<span class='description'><small>" . $tags->text;
										if ('attributes' == $tab_type) echo '"';
										$this->settings_select_num($tab_type, $attribute, (int) $tags->min, (int) $tags->max, (int) $tags->default); 
										if ('attributes' == $tab_type) echo '"';
										echo "</small></span>&#160;\n";
									break;
									case 'select_opt' :
										$values = unserialize($tags->values);
										echo "<span class='description'><small>" . $tags->text;
										$this->settings_select_opt($tab_type, $attribute, $values, $tags->default); 
										echo "</small></span>&#160;\n";
									break;
									case 'text' :
										echo "<span class='description'><small>";
										if (isset($tags->text)) echo $tags->text; else echo $attribute; 
										if ('attributes' == $tab_type) echo '="';
										echo "</small></span>";
										$this->settings_text($tab_type, $attribute, (isset($tags->disabled)) ? (string) $tags->disabled : false, (isset($tags->default)) ? $tags->default : '', (isset($tags->size)) ? (string) $tags->size : 32);
										if ('attributes' == $tab_type) echo "<span class='description'><small>\"</small></span>\n";
									break;
									case 'textarea' :
										$this->settings_attributes_textarea($tags->text, $attribute);
										if (isset($tags->desc)) echo "<br /><span class='description'><small style='color:#666;'>" . $tags->desc . '</small></span>';
									break;
								}
							}
						}
						echo "<br />\n";
					}
					if ( ($has_controls) && ('controls' == $tab_type) )
					{
						echo "<div id='field_type_controls_" . $this->id . "'" . ( ($has_controls_checked) ? '' : " style='display:none;'" ) . " class='field_type_controls'>\n";
						echo "<hr style='border: 0pt none ; margin: 1px 5px 5px 1px; color: rgb(223, 223, 223); background-color: rgb(223, 223, 223); height: 1px;' />";
						_e('On error <small>(to remove a class : -name_of_class)</small>', MP_TXTDOM);
						echo '<br />';
						echo "<div>";
						foreach (array('class', 'style') as $attribute)
						{
							echo "<span class='description'><small>$attribute=\"</small></span>";
							$this->settings_text($tab_type, $attribute);
							echo "<span class='description'><small>\"</small></span><br />\n";
						}
						echo '</div>';
						echo "</div>\n";
					}
				break;
			}
			echo "\n</div>\n";
		}
		if (isset($xml->hiddens))
		{
			foreach ($xml->hiddens->children() as $child)
			{
				$tab_type = $child->getName();
				foreach($child->children() as $child2)
				{
					$attribute = $child2->getName();
					if (isset($child2->value))	$this->settings_hidden_value($tab_type, $attribute, $child2->value);
					else					$this->settings_hidden($tab_type, $attribute);
				}
			}
		}
?>
	</div>
</div>
<?php
	}
	function settings_description($text) { echo "<span class='description'><small>$text</small></span>"; }
	function settings_hidden($setting, $attribute) { ?><input type='hidden' name='<?php echo $this->prefix . $this->id . "[settings][$setting][$attribute]"; ?>' value="<?php if ($this->type_ok && isset($this->field->settings[$setting][$attribute])) echo esc_attr($this->field->settings[$setting][$attribute]); ?>" /><?php }
	function settings_hidden_value($setting, $attribute, $value) { ?><input type='hidden' name='<?php echo $this->prefix . $this->id . "[settings][$setting][$attribute]"; ?>' value="<?php echo esc_attr($value); ?>" /><?php }
	function settings_text($setting, $attribute, $disabled = false, $default = '', $size = 32) { ?><input type='text' name='<?php echo $this->prefix . $this->id . "[settings][$setting][$attribute]"; ?>' id='<?php echo $this->prefix . $this->id . '_settings_' . $setting . '_' . $attribute; ?>' value="<?php echo ( ($this->type_ok && isset($this->field->settings[$setting][$attribute])) ? esc_attr($this->field->settings[$setting][$attribute]) : esc_attr($default) ); ?>" size='<?php echo $size; ?>' /><?php }
	function settings_checkbox($setting, $attribute, $value, $disabled = false, $class = false, $forced = false) { ?><input type='checkbox' name='<?php echo $this->prefix . $this->id . "[settings][$setting][$attribute]"; ?>' id='<?php echo $this->prefix . $this->id . '_settings_' . $setting . '_' . $attribute . '_' . $value; ?>' value="<?php echo esc_attr($value); ?>" style='width:auto;'<?php if ($disabled) echo " disabled='disabled'"; ?><?php if ($class) echo " class='$class'"; ?><?php if ($forced !== false) checked(true); else checked( $value, ( ($this->type_ok && isset($this->field->settings[$setting][$attribute])) ? $this->field->settings[$setting][$attribute] : null ) ); ?> /><?php if ($forced !== false) return true; return ($this->type_ok && isset($this->field->settings[$setting][$attribute]) && ($value == $this->field->settings[$setting][$attribute]) );}
	function settings_radio($setting, $attribute, $value, $value_text, $default, $disabled = false)	{ ?><input type='radio'	name='<?php echo $this->prefix . $this->id . "[settings][$setting][$attribute]"; ?>' id='<?php echo $this->prefix . $this->id .'_settings_' . $setting . '_' . $attribute . '_' . $value; ?>' 	value="<?php echo esc_attr($value); ?>" style='width:auto;'<?php checked($value, ( ($this->type_ok && isset($this->field->settings[$setting][$attribute])) ? $this->field->settings[$setting][$attribute] : $default) ); ?><?php if ($disabled) echo " disabled='disabled'"; ?> /><label class='inline' for='<?php echo $this->prefix . $this->id .'_settings_' . $setting . '_' . $attribute . '_' . $value; ?>' ><span class='description'><small<?php if ($disabled) echo " style='color:#888'";?>><?php echo $value_text; ?></small></span></label>&#160;<?php }
	function settings_select_num($setting, $attribute, $min, $max, $default) { ?><select name='<?php echo $this->prefix . $this->id . "[settings][$setting][$attribute]"; ?>' id='<?php echo $this->prefix . $this->id . '_settings_' . $setting . '_' . $attribute; ?>' ><?php MP_AdminPage::select_number($min, $max,     ( ($this->type_ok && isset($this->field->settings[$setting][$attribute])) ? $this->field->settings[$setting][$attribute] : $default) ); ?></select><?php }
	function settings_select_opt($setting, $attribute, $values, $default) { ?><select name='<?php echo $this->prefix . $this->id . "[settings][$setting][$attribute]"; ?>' id='<?php echo $this->prefix . $this->id . '_settings_' . $setting . '_' . $attribute; ?>' ><?php MP_AdminPage::select_option($values, ( ($this->type_ok && isset($this->field->settings[$setting][$attribute])) ? $this->field->settings[$setting][$attribute] : $default) ); ?></select><?php }
	function settings_attributes_textarea($text, $option) { ?><input type='hidden' name='<?php echo $this->prefix . $this->id . '[textareas][]'; ?>' value='<?php echo esc_attr($option); ?>' /><?php $this->settings_description($text); ?><br /><textarea name='<?php echo $this->prefix . $this->id . "[settings][attributes][$option]"; ?>' id='<?php echo $this->prefix . $this->id . '_settings_attributes_' . $option; ?>' cols='40' rows='4'><?php if ($this->type_ok && isset($this->field->settings['attributes']['tag_content'])) echo esc_attr(trim(base64_decode($this->field->settings['attributes']['tag_content']))); ?></textarea><?php }
}