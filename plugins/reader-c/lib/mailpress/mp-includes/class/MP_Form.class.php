<?php
class MP_Form
{
	const prefix = MailPress_form::prefix;

	public static function get($form, $output = OBJECT) 
	{
		global $wpdb;

		switch (true)
		{
			case ( empty($form) ) :
				if ( isset($GLOBALS['mp_form']) ) 	$_form = & $GLOBALS['mp_form'];
				else					$_form = null;
			break;
			case ( is_object($form) ) :
				wp_cache_add($form->id, $form, 'mp_form');
				$_form = $form;
			break;
			default :
				if ( isset($GLOBALS['mp_form']) && ($GLOBALS['mp_form']->id == $form) ) 
				{
					$_form = & $GLOBALS['mp_form'];
				} 
				elseif ( ! $_form = wp_cache_get($form, 'mp_form') ) 
				{
					$_form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->mp_forms WHERE id = %d LIMIT 1", $form));
					if ($_form) wp_cache_add($_form->id, $_form, 'mp_form');
				}
			break;
		}

		if ($_form && isset($_form->settings)) $_form->settings = unserialize($_form->settings);

		if ( $output == OBJECT ) {
			return $_form;
		} elseif ( $output == ARRAY_A ) {
			return get_object_vars($_form);
		} elseif ( $output == ARRAY_N ) {
			return array_values(get_object_vars($_form));
		} else {
			return $_form;
		}
	}

	public static function get_all() 
	{
		global $wpdb;

		$forms= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->mp_forms a ORDER BY label" ) );

		if ($forms) foreach ($forms as $k => $form) if (!is_array($form->settings)) $forms[$k]->settings = unserialize($form->settings);

		return $forms;		
	}

	public static function exists($label) 
	{
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT id FROM $wpdb->mp_forms WHERE label = %s LIMIT 1", $label));
	}

	public static function get_template($id) 
	{
		$form = self::get($id);
		if ($form) return $form->template;
		return false;
	}

	public static function insert($_post_form) 
	{
		$_post_defaults = array('id' => 0);
		$_post_form = wp_parse_args(stripslashes_deep($_post_form), $_post_defaults);
		extract($_post_form, EXTR_SKIP);

		if ( trim( $label ) == '' )
		{
			if ( ! $wp_error )	return 0;
			else				return new WP_Error( 'label', __('You did not enter a valid label.', MP_TXTDOM) );
		}

	// theme/template
		$settings['recipient']['template'] = $settings['recipient']['th'][$settings['recipient']['theme']]['tm'];
		$settings['visitor']  ['template'] = $settings['visitor']  ['th'][$settings['visitor']  ['theme']]['tm'];
		unset($settings['recipient']['th'], $settings['visitor']['th']);
	// is visitor
		$has_visitor = $tbc_visitor = false;
		if ( (isset($settings['visitor']['mail']) && $settings['visitor']['mail']) || ($settings['visitor']['subscription'])) 	$has_visitor = true;
		if (!$has_visitor) unset($settings['visitor']);
		if ( (isset($settings['visitor']['mail']) && ( '1' == $settings['visitor']['mail']) )) 						$tbc_visitor = true;

		$data = $format = $where = $where_format = array();

		$data['label'] 		= $label;			$format[] = '%s';
		$data['description'] 	= $description;		$format[] = '%s';
		$data['template']		= $template;		$format[] = '%s';
		$data['settings'] 	= serialize($settings);	$format[] = '%s';

		// Are we updating or creating?
		global $wpdb;
		$update = (!empty ($id) ) ? true : false;
		if ( $update )
		{
			$where['id'] 	= (int) $id;		$where_format[] = '%d';
			$wpdb->update( $wpdb->mp_forms, $data, $where, $format, $where_format );
		}
		else
		{
			$wpdb->insert( $wpdb->mp_forms, $data, $format );
			$id = $wpdb->insert_id;
		}

		if (!$id) return $id;

		MP_Form_field::check_visitor($id, $has_visitor, $tbc_visitor);

		return $id;
	}

	public static function duplicate($id)
	{
		$form = self::get($id);
		if (!$form) return false;

		do 
		{
			$sep = '_x';
			$label = explode($sep, $form->label);
			$num = array_pop($label);
			$label[] = (is_numeric($num)) ? $num + 1 : $num . $sep . '2';
			$form->label = implode($sep, $label);
		} while (self::exists($form->label));

		$data = $format 		= array();

		$data['label'] 		= $form->label;			$format[] = '%s';
		$data['description'] 	= $form->description;		$format[] = '%s';
		$data['template']		= $form->template;		$format[] = '%s';
		$data['settings'] 	= serialize($form->settings);	$format[] = '%s';	
		global $wpdb;
		$wpdb->insert( $wpdb->mp_forms, $data, $format );
		$dup_id = $wpdb->insert_id;

		if ($dup_id)
		{
			$fields = MP_Form_field::get_all($id);
			if ($fields) foreach($fields as $field) MP_Form_field::duplicate($field->id, $dup_id);
		}

		return $dup_id;
	}

	public static function delete($id)
	{
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mp_forms WHERE id = %d;", $id) );
		MP_Form_field::delete_all($id);
		return true;
	}

//// the form /building/resetting/controlling 

	public static function form( $id )
	{
		$form = self::get($id);
		if (!$form) return '<!-- MailPress_form  ERROR : unknown form #' . $id . ' ! -->';

		$fields = MP_Form_field::get_all($form->id);
		if (!$fields) return '<!-- MailPress_form  WARNING : form #' . $form->id . ' is empty ! -->';

		$form_templates = new MP_Form_templates();
		$xform_template = $form_templates->get_all();
		if (!isset($xform_template[$form->template]))
			if (!isset($xform_template['default'])) return '<!-- MailPress_form  ERROR : form #' . $form->id . ', template : ' . $form->template . ' unknown ! -->';

		new MP_Form_field_types();

		$processed = $on_error = false;
		$submitted = ((isset($_POST[self::prefix][$id])));
		foreach($fields as $k => $field) 
		{
			$field =  MP_Form_field::get($field->id);
			$fields[$k] = ($submitted) ? MP_Form_field_types::submitted( $field ) : $field;
			if (isset($fields[$k]->submitted['on_error'])) $on_error = true;
		}

		if ($submitted)
		{
			if (!$on_error)	$processed = self::process($form, $fields, $form_templates);
			else			unset($form->settings['options']['reset']);
		}

		if ($processed && isset($form->settings['options']['reset'])) unset($_POST[self::prefix][$id]);

		return self::get_form($form, $fields, $form_templates, $submitted, $processed);
	}


//// Processing form
	public static function process( $form, $fields, $form_templates)
	{
		$array = new stdClass();
		$mail  = new stdClass();
		$attachements = array();

		$mail->id = $mail_main_id = MP_Mail::get_id('MP_Form::process');

		$array->form['id'] 	= $form->id;
		$array->form['label'] 	= $form->label;
		$array->form['description'] 	= $form->description;

		$recipient_content = $visitor_content = '';
		foreach($fields as $k => $field)
		{
			if (!isset($field->submitted) || empty($field->submitted)) continue;
			$incopy = (isset($field->settings['options']['incopy']) && $field->settings['options']['incopy']);

			$img = (!isset($field->submitted['map'])) ? '' : "<img align='center' style='border:none;margin:0;padding:0' src='" . $field->submitted['map'] . "' alt='' /></br>\r\n";

			$recipient_content .= sprintf('<p>' . "\r\n" . '<b>%1$s : </b>' . "\t\t" . ' %2$s %4$s %3$s</p>' . "\r\n", 			$field->label, ( ($field->submitted['value']) ? $field->submitted['text'] : ((empty($field->submitted['text'])) ? '' : '<small>[ ' . $field->submitted['text'] . ' ]</small> ') ), ( (!empty($field->description)) && (!empty($field->submitted['text'])) ? ' <small>[ ' . $field->description . ' ]</small> ' : ' ' ), (empty($img)) ? $img : "<div>$img</div>" ) ;
			$visitor_content   .= (!$incopy) ? '' : sprintf('<p>' . "\r\n" . '<b>%1$s : </b>' . "\t\t" . ' %3$s %2$s</p>' . "\r\n", $field->label, ( ($field->submitted['value']) ? $field->submitted['text'] : ((empty($field->submitted['text'])) ? '' : '<small>[ ' . $field->submitted['text'] . ' ]</small> ') ), (empty($img)) ? $img : "<div>$img</div>" );

			if (isset($field->settings['options']['visitor_email'])) { $visitor_toemail = $field->submitted['value']; 	$array->field[$field->id]['options']['visitor']['email'] = true; }
			if (isset($field->settings['options']['visitor_name']))  { $visitor_toname  = $field->submitted['value']; 	$array->field[$field->id]['options']['visitor']['name']  = true; }
			if (isset($field->settings['options']['visitor_mail']))  { $visitor_mail    = $field->submitted['value']; 	$array->field[$field->id]['options']['visitor']['mail']  = true; }

			$array->field[$field->id]['label'] 		= $field->label;
			$array->field[$field->id]['description'] 	= $field->description;
			$array->field[$field->id]['value'] 		= (isset($field->submitted['value'])) ? $field->submitted['value'] : false;
			if (isset($field->submitted['map'])) 
				$array->field[$field->id]['map']	= $field->submitted['map'];

			if (isset($field->submitted['file'])) $attachements[] = self::handle_upload($field->submitted['file'], $mail->id);
		}
		$subject 	= sprintf(__('[%1$s] Your form receipt "%2$s" (%3$s)', MP_TXTDOM), get_option('blogname'), $form->label, $form->id);

		$mail->Theme 	= $form->settings['recipient']['theme'];
		if ('0' != $form->settings['recipient']['template']) $mail->Template = $form->settings['recipient']['template'];

		if (isset($visitor_toemail))
		{
			$mail->fromemail 	= $visitor_toemail;
			$mail->fromname   = (isset($visitor_toname)) ? $visitor_toname : $visitor_toemail;
		}

		$mail->toemail 	= $form->settings['recipient']['toemail'];
		$mail->toname     = $form->settings['recipient']['toname'];

		$mail->subject 	= $subject;
		$mail->content 	= $recipient_content;	//self::get_form($form, $fields, $form_templates); 
		$mail->form 	= $array;

		if (!MailPress::mail($mail)) return false;
		if ( !isset($form->settings['visitor'])) return true;
        
		if ('0' != $form->settings['visitor']['subscription'] && isset($visitor_toemail))
		{

			if ($mp_user = MP_User::get( MP_User::get_id_by_email($visitor_toemail) ))
			{
				switch($mp_user->status) 
				{
					case 'active':
					break;
					case 'bounced':
					break;
					case 'waiting':
						switch ($form->settings['visitor']['subscription'])
						{
							case '1' :	// not active
							break;
							case '2' :	// tbc
								MP_User::send_confirmation_subscription($mp_user->email, $mp_user->name, $mp_user->confkey);
							break;
							case '3' :	// active
								MP_User::set_status($mp_user->id, 'active');
							break;
						}
					break;
					case 'unsubscribed':
						switch ($form->settings['visitor']['subscription'])
						{
							case '1' :	// not active
								MP_User::set_status($mp_user->id, 'waiting');
							break;
							case '2' :	// tbc
								MP_User::set_status($mp_user->id, 'waiting');
								MP_User::send_confirmation_subscription($mp_user->email, $mp_user->name, $mp_user->confkey);
							break;
							case '3' :	// active
								MP_User::set_status($mp_user->id, 'waiting');
								MP_User::set_status($mp_user->id, 'active');
							break;
						}
					break;
				}
				do_action('MailPress_visitor_subscription', 'add', $visitor_toemail, $form);
			}
			else
			{
				switch ($form->settings['visitor']['subscription'])
				{
					case '1' :	// not active
						MP_User::insert($visitor_toemail, $visitor_toname);
					break;
					case '2' :	// tbc
						MP_User::add($visitor_toemail, $visitor_toname);
					break;
					case '3' :	// active
						MP_User::insert($visitor_toemail, $visitor_toname, array('status' => 'active'));
					break;
				}
				do_action('MailPress_visitor_subscription', 'init', $visitor_toemail, $form);
			}
		}

		// no mail
		if (!isset($visitor_toemail)) return true;
		// form says no copy
		if (!isset($form->settings['visitor']['mail'])) return true;
		if ($form->settings['visitor']['mail'] == '0')  return true;
		// forms says copy but no email field in form (!!??)
		if (!isset($visitor_mail) && ($form->settings['visitor']['mail'] == '1')) return true;
		// forms says copy tbc and visitor unchecked the option
		if (isset($visitor_mail) && !$visitor_mail) return true;

		// no valid mail
		if (!is_email($visitor_toemail)) return false;

		$mail = new stdClass();
		$mail->id = MP_Mail::get_id('MP_Form::process2');

		// duplicate attachements
		if (!empty($attachements))
		{
			$metas = MP_Mail_meta::has( $mail_main_id, '_MailPress_attached_file');
			if ($metas)
			{
				foreach($metas as $meta)
				{
					$meta_value = unserialize( $meta['meta_value'] );
					if (!is_file($meta_value['file_fullpath'])) continue;
					MP_Mail_meta::add( $mail->id, '_MailPress_attached_file', $meta_value );
				}
			}
		}

		$mail->Theme 	= $form->settings['visitor']['theme'];
		if ('0' != $form->settings['visitor']['template']) $mail->Template = $form->settings['visitor']['template'];

		$mail->toemail 	= $visitor_toemail;
		$mail->toname     = $visitor_toname;

		$mail->subject 	= $subject;
		$mail->content 	= $visitor_content;

		return MailPress::mail($mail);
	}

//// Getting form
	public static function get_form($form, $fields, $form_templates, $submitted = false, $processed = false)
	{
		$message = '';
		if ($submitted) $message[(($processed) ? 'ok' : 'ko')] = $form->settings['message'][(($processed) ? 'ok' : 'ko')];

	// going for the message
		if (!empty($message))
		{
			foreach($message as $ret => $mess) {}
			$html = $form_templates->get_message_template($form->template, $ret);
			if (!$html) $html = '{{message}}';

			$search = $replace = array();
			$search[] = '{{message}}'; $replace[] = $mess;
			$html = str_replace($search, $replace, $html ); 

			$message = sprintf('%1$s<!-- start message -->%2$s<!--   end message -->%1$s', "\n", $html);
		}

	// going for the form
		$html = $form_templates->get_form_template($form->template);
		if (!$html) $html = '{{form}}';

		$search = $replace = array();
		$search[] = '{{label}}'; 	$replace[] = $form->label;
		$search[] = '{{description}}';$replace[] = $form->description;
		$search[] = '{{form}}'; 	$replace[] = self::build_form( $form, $fields, $form_templates);
		$search[] = '{{message}}'; 	$replace[] = $message;
		$html = str_replace($search, $replace, $html ); 

		return sprintf('%1$s<!-- MailPress_form : start of form #%2$s -->%1$s%3$s%1$s<!--   MailPress_form : end of form #%2$s -->%1$s%1$s', "\n", $form->id, $html);
	}

//// Building form
	public static function build_form( $form, $fields, $form_templates)
	{
	// going for the fields
		$i = 0;
		$have_groups = array();
		foreach($fields as $offset => $field)
		{
	// going for fields
			$field_template 	= $field->template;
			$html			= (isset($field->submitted['on_error'])) ? $form_templates->get_field_on_error_template($form->template, $field->template) : $form_templates->get_field_template($form->template, $field->template);
			if (!$html) {$html = $form_templates->get_field_template($form->template, $field->template); }
			if (!$html) {$html = '{{field}}'; $field_template = 'not found'; }
			if ((isset($field->settings['attributes']['type'])) && ($field->settings['attributes']['type'] == 'hidden')) {$html = '{{field}}'; $field_template = "(type='hidden') => overridden "; }
			$htmls[$offset] 	= sprintf('<!-- start field / form : %2$s, field : %3$s, type : %4$s, template : %5$s/%6$s, description : %7$s -->%1$s%8$s%1$s<!--   end field / form : %2$s, field : %3$s -->%1$s', "\n", $form->id, $field->id, $field->type, $form->template, $field_template, $field->description, self::get_field($field, $html));
	// looking for groups
			if (!$i) { $i = 1; $prev_field = $field;}
			if ( ($field->type == $prev_field->type) && ($field->template == $prev_field->template) && ($form_templates->get_group_template($form->template, $field->template)) )
			{
				switch (true) 
				{
					case ('radio' == $field->type) :
						if ($field->settings['attributes']['name'] == $prev_field->settings['attributes']['name']) 
							$have_groups[$i][$offset] = $field->id;
						else
						{	
							if (isset($have_groups[$i]))
								if (count($have_groups[$i]) < 2) 	unset($have_groups[$i]);
								else $i++;
							$have_groups[$i][$offset] = $field->id;
						}
					break;
					default :
						$have_groups[$i][$offset] = $field->id;
					break;
				}
			}
			else
			{
				if (isset($have_groups[$i]))
					if (count($have_groups[$i]) < 2) 	unset($have_groups[$i]);
					else $i++;

				$have_groups[$i][$offset] = $field->id;
			}
			$prev_field = $field;
		}

		if (isset($have_groups[$i]))
			if (count($have_groups[$i]) < 2) 	unset($have_groups[$i]);

	// going for groups
		foreach ($have_groups as $offsets)
		{
			$html	= '';
			$count = 1;
			$group_template 	= false;
			foreach ($offsets as $offset => $v)
			{
				$field = $fields[$offset];
				if (!$group_template) $group_template = $form_templates->get_group_template($form->template, $field->template);
				if (!$group_template) break;

				if (is_array($group_template))
				{
					if (!isset($group_template[$field->template])) break;
					if (($count == 1) && (isset($group_template['before'])))				$html .= $group_template['before'];
					if (($count == 1) && (isset($group_template['first'])))				$html .= $group_template['first'];
					elseif (($count == count($offsets)) && (isset($group_template['last'])))	$html .= $group_template['last'];
					else													$html .= $group_template[$field->template];
					if (($count == count($offsets)) && (isset($group_template['after']))) 		$html .= $group_template['after'];
				}
				else														$html .= $group_template;

				$search = $replace = array();
				$search[] = '{{field}}'; 	$replace[] = $htmls[$offset];
				$html = str_replace($search, $replace, $html ); 
				$count++;
			}
			if (!empty($html))
			{
				$first = true;
				foreach ($offsets as $offset => $v) if (!$first) { unset($htmls[$offset]); } else { $first = false; $htmls[$offset] = sprintf('%1$s<!-- Start grouping fields : #%2$s -->%1$s%3$s%1$s<!-- End grouping fields : #%2$s -->%1$s', "\n", implode(', #', $offsets), $html); } 
			}
		}

		return sprintf('%2$s%1$s%3$s%1$s%4$s', "\n", self::get_tag( $form ), implode("\n", $htmls), '</form>');
	}

	public static function get_tag( $form )
	{
		$attributes = $form->settings['attributes'];
	// misc
		$misc = (isset($attributes['misc'])) ? ' ' . $attributes['misc'] : '';
		unset($attributes['misc']);

	// opening tag
		$tag  = '<form' ;
	// id
		if (false === strpos($misc, 'id=')) $attributes['id'] = self::prefix . $form->id;
	// method
		$attributes['method'] = 'post';
	// action
		if (false === strpos($misc, 'action=')) $attributes['action'] = '';
	// enctype
		if (MP_Form_field::have_file($form->id)) $attributes['enctype'] = 'multipart/form-data';
	// other attributes
		foreach ($attributes as $attribute => $value)
		{
			$value = trim($value);
			$tag .= ( ('action' != $attribute) && ('' == trim($value)) ) ? '' : self::get_attribute($attribute, trim($value));
		} 
	// closing tag
		return $tag . "$misc >";
	}
	public static function get_attribute($attr, $value) { $quote = (false !== strpos($value, '"')) ? "'" : '"'; return " $attr=$quote$value$quote"; }
	public static function get_field($field, $html = false, $form_template = false)
	{
		if (!$html)
		{
			$form_templates 	= new MP_Form_templates();
			$html			= $form_templates->get_field_template($form_template, $field->template);
			if (!$html) $html = '{{field}}';
		}
		$search = $replace = array();
		$search[] = '{{label}}'; 	$replace[] = $field->label;
		$search[] = '{{description}}';$replace[] = $field->description;
		$search[] = '{{field_id}}'; 	$replace[] = MP_Form_field_types::get_id($field);
		$search[] = '{{field}}'; 	$replace[] = MP_Form_field_types::get_tag($field);
		$html = str_replace($search, $replace, $html ); 
		return $html;
	}

	public static function handle_upload($file_id, $draft_id) 
	{
		$overrides = array('test_form'=>false, 'unique_filename_callback' => 'mp_unique_filename_callback');
		$time = current_time('mysql');

		require_once (ABSPATH . 'wp-admin/includes/file.php');

		$uploaded_file = wp_handle_upload($_FILES[$file_id], $overrides, $time);

		if ( isset($uploaded_file['error']) )
			return new WP_Error( 'upload_error', $uploaded_file['error'] );

// Check file path is ok
		$uploads = wp_upload_dir();
		if ( $uploads && (false === $uploads['error']) ) 							// Get upload directory
		{ 	
			if ( 0 === strpos($uploaded_file['file'], $uploads['basedir']) ) 				// Check that the upload base exists in the file path
			{
				$file = str_replace($uploads['basedir'], '', $uploaded_file['file']); 		// Remove upload dir from the file path
				$file = ltrim($file, '/');
			}
		}

// Construct the attachment array
		$object = array(
					'name' 	=> $_FILES[$file_id]['name'],
					'mime_type'	=> $uploaded_file['type'], 
					'file'	=> $file, 
					'file_fullpath'	=> str_replace("\\", "/", $uploaded_file['file']), 
					'guid' 	=> $uploaded_file['url']
				);
// Save the data
		return MP_Mail_meta::add( $draft_id, '_MailPress_attached_file', $object );
	}

}