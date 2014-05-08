<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= MailPress_page_forms;
	const capability 	= 'MailPress_manage_forms';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/form/';
	const file        	= __FILE__;

	const add_form_id 	= 'add';
	const list_id 		= 'the-list';
	const tr_prefix_id 	= 'form';

////  Redirect  ////

	public static function redirect() 
	{
		if     ( !empty($_REQUEST['action'])  && ($_REQUEST['action']  != -1))	$action = $_REQUEST['action'];
		elseif ( !empty($_REQUEST['action2']) && ($_REQUEST['action2'] != -1) )	$action = $_REQUEST['action2'];
		if (!isset($action)) return;

		$url_parms = self::get_url_parms(array('s', 'paged', 'id'));
		$checked	= (isset($_GET['checked'])) ? $_GET['checked'] : array();

		$count	= str_replace('bulk-', '', $action);
		$count     .= 'd';
		$$count	= 0;

		switch($action) 
		{
			case 'bulk-delete' :
				foreach($checked as $id) if (MP_Form::delete($id)) $$count++;

				if ($$count) $url_parms[$count] = $$count;
				$url_parms['message'] = ($$count <= 1) ? 3 : 4;
				self::mp_redirect( self::url(MailPress_forms, $url_parms) );
			break;

			case 'add':
				$e = MP_Form::insert($_POST);
				$url_parms['message'] = ( $e ) ? 1 : 91;
				unset($url_parms['s']);
				self::mp_redirect( self::url(MailPress_forms, $url_parms) );
			break;
			case 'duplicate' :
				MP_Form::duplicate($url_parms['id']);
				self::mp_redirect( self::url(MailPress_forms, $url_parms) );
			break;
			case 'edited':
				unset($_GET['action']);
				if (!isset($_POST['cancel'])) 
				{
					$e = MP_Form::insert($_POST);
					$url_parms['message'] = ( $e ) ? 2 : 92 ;
					$url_parms['action']  = 'edit';
				}
				else unset($url_parms['id']);

				self::mp_redirect( self::url(MailPress_forms, $url_parms) );
			break;

			case 'delete':
				MP_Form::delete($url_parms['id']);
				unset($url_parms['id']);

				$url_parms['message'] = 3;
				self::mp_redirect( self::url(MailPress_forms, $url_parms) );
			break;
		}
	}

////  Title  ////

	public static function title() 
	{ 
		new MP_Form_field_types();
		if (isset($_GET['id'])) { global $title; $title = __('Edit Form', MP_TXTDOM); } 
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( MailPress_page_forms,	'/' . MP_PATH . 'mp-admin/css/forms.css', array('thickbox') );
		$styles[] = MailPress_page_forms;
		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts($scripts = array())  
	{
		wp_register_script( 'mp-ajax-response',	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery', 'jquery-ui-tabs'), false, 1);
		wp_localize_script( 'mp-ajax-response', 	'wpAjax', array(
			'noPerm' => __('An unidentified error has occurred.'), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		));

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 		'wpListL10n', array( 
			'url' => MP_Action_url
		));

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);

		wp_register_script( 'mp-taxonomy', 		'/' . MP_PATH . 'mp-includes/js/mp_taxonomy.js', array('mp-lists'), false, 1);
		wp_localize_script( 'mp-taxonomy', 		'MP_AdminPageL10n', array(
			'pending' => __('%i% pending'), 
			'screen' => self::screen,
			'list_id' => self::list_id,
			'add_form_id' => self::add_form_id,
			'tr_prefix_id' => self::tr_prefix_id,
			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		));

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/forms.js', array('mp-taxonomy', 'mp-thickbox'), false, 1);

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'cb'			=> '<input type="checkbox" />',
					'name'		=> __('Label', MP_TXTDOM),
					'template'		=> __('Template', MP_TXTDOM),
					'recipient'		=> __('Recipient', MP_TXTDOM),
					'confirm' 		=> __('Copy', MP_TXTDOM));
		return $columns;
	}

//// List ////

	public static function get_list($args)
	{
		extract($args);

		global $wpdb;

		$where = '';

		if (isset($url_parms['s']))
		{
			$sc = array('a.label', 'a.description' );

			$where .= self::get_search_clause($url_parms['s'], $sc);
		}

		$args['query'] = "SELECT DISTINCT SQL_CALC_FOUND_ROWS a.id, a.label, a.description, a.template, a.settings FROM $wpdb->mp_forms a WHERE 1=1 $where ";
		$args['cache_name'] = 'mp_form';

		return parent::get_list($args);
	}

////  Row  ////

	public static function get_row( $form, $url_parms ) 
	{
		static $row_class = '';

		$form = MP_Form::get( $form );

// url's
		$url_parms['action'] 	= 'edit';

		$url_parms['id'] 	= $form->id;

		$edit_url = esc_url(self::url( MailPress_forms, $url_parms ));
		$url_parms['action'] 	= 'duplicate';
		$duplicate_url = esc_url(self::url( MailPress_forms, $url_parms, 'duplicate-form_' . $form->id ));

		$url_parms['action'] 	= 'edit_fields';
		$url_parms['form_id'] = $url_parms['id']; unset($url_parms['id']); 
		$edit_fields_url = esc_url(self::url( MailPress_fields, $url_parms ));
		$url_parms['id'] = $url_parms['form_id']; unset($url_parms['form_id']); 

		$edit_templates_url = esc_url(self::url( MailPress_templates, array('action' => 'edit', 'template' => $form->template)));

		$args = array();
		$args['id'] 	= $form->id;
		$args['action'] 	= 'ifview';
		$args['preview_iframe'] = 1; $args['TB_iframe']= 'true';
		$view_url		= esc_url(self::url(MP_Action_url, $args));

		$url_parms['action'] 	= 'delete';
		$delete_url = esc_url(self::url( MailPress_forms, $url_parms, 'delete-form_' . $form->id ));

// actions
		$actions = array();
		$actions['edit'] = '<a href="' . $edit_url . '">' . __('Edit') . '</a>';
		$actions['edit_templates'] = '<a href="' . $edit_templates_url . '">' . __('Templates', MP_TXTDOM) . '</a>';
		$actions['edit_fields'] = '<a href="' . $edit_fields_url . '">' . __('Fields', MP_TXTDOM) . '</a>';
		$actions['duplicate'] = "<a class='dim:" . self::list_id . ":" . self::tr_prefix_id . "-" . $form->id . ":unapproved:e7e7d3:e7e7d3' href='$duplicate_url'>" . __('Duplicate', MP_TXTDOM) . "</a>";
		$actions['delete'] = "<a class='submitdelete' href='$delete_url'>" . __('Delete') . "</a>";
		$actions['view'] = "<a class='thickbox thickbox-preview' href='$view_url' title=\"" . esc_attr(sprintf(__('Form preview #%1$s (%2$s)', MP_TXTDOM), $form->id, $form->label)) . "\" >" . __('Preview', MP_TXTDOM) . "</a>";

		$row_class = 'alternate' == $row_class ? '' : 'alternate';

		$out = '';
		$out .= "<tr id='" . self::tr_prefix_id . "-$form->id' class='iedit $row_class'>";

		$columns = self::get_columns();
		$hidden  = self::get_hidden_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array($column_name, $hidden) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";
			switch ($column_name) 
			{
				case 'cb':
					$out .= '<th scope="row" class="check-column"> <input type="checkbox" name="checked[]" value="' . $form->id . '" /></th>';
				break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_url . '" title="' . esc_attr(sprintf(__('Edit "%s"'), $form->label)) . '">' . $form->label . '</a></strong><br />';
					$out .= self::get_actions($actions);
					$out .= '</td>';
				break;
	 			case 'template':
	 				$out .= "<td $attributes>" . $form->template . "</td>\n";
	 			break;
	 			case 'Theme':
	 				$out .= "<td $attributes>" . $form->settings['recipient']['theme'];
					if (!empty($form->settings['recipient']['template'])) $out .= '<br />(' . $form->settings['recipient']['template'] . ')'; 
					$out .= "</td>\n";
	 			break;
	 			case 'recipient':
	 				$out .= "<td $attributes>" . $form->settings['recipient']['toemail'];
					if (!empty($form->settings['recipient']['toname'])) $out .= '<br />(' . $form->settings['recipient']['toname'] . ')'; 
					$out .= "</td>\n";
	 			break;
				case 'confirm':
	 				$out .= "<td $attributes>";
					$mail = (isset($form->settings['visitor']['mail'])) ? $form->settings['visitor']['mail'] : 0;
					switch ($mail)
					{
						case 1 :
							$out .= __('t.b.c.', MP_TXTDOM);
						break;
						case 2 :
							$out .= __('yes', MP_TXTDOM);
						break;
						default :
							$out .= __('no', MP_TXTDOM);
						break;
					}
	 				$out .= "</td>\n";
	 			break;
			}
		}
		$out .= "</tr>\n";

		return $out;
	}
}