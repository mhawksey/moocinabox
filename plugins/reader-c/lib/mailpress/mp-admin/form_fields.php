<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= 'mailpress_page_form_fields';
	const capability 	= 'MailPress_manage_forms';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/form/';
	const file        	= __FILE__;

	const add_form_id 	= 'add';
	const list_id 		= 'the-list';
	const tr_prefix_id 	= 'field';

////  Redirect  ////

	public static function redirect() 
	{
		if     ( !empty($_REQUEST['action'])  && ($_REQUEST['action']  != -1))	$action = $_REQUEST['action'];
		elseif ( !empty($_REQUEST['action2']) && ($_REQUEST['action2'] != -1) )	$action = $_REQUEST['action2'];
		if (!isset($action)) return;

		$url_parms = self::get_url_parms(array('s', 'paged', 'id', 'form_id'));
		$checked	= (isset($_GET['checked'])) ? $_GET['checked'] : array();

		$count	= str_replace('bulk-', '', $action);
		$count     .= 'd';
		$$count	= 0;

		switch($action) 
		{
			case 'bulk-delete' :
				foreach($checked as $id) if (MP_Form_field::delete($id, $url_parms['form_id'])) $$count++;

				if ($$count) $url_parms[$count] = $$count;
				$url_parms['message'] = ($$count <= 1) ? 3 : 4;
				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;

			case 'add':
				$e = MP_Form_field::insert($_POST);
				$url_parms['message'] = ( $e  ) ? 1 : 91;
				unset($url_parms['s']);
				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;
			case 'duplicate' :
				MP_Form_field::duplicate($url_parms['id'], $url_parms['form_id']);
				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;
			case 'edited':
				unset($_GET['action']);
				if (!isset($_POST['cancel'])) 
				{
					$e = MP_Form_field::insert($_POST);
					$url_parms['message'] = ( $e ) ? 2 : 92 ;
					$url_parms['action']  = 'edit';
				}
				else unset($url_parms['id']);

				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;
			case 'delete':
				MP_Form_field::delete($url_parms['id'], $url_parms['form_id']);
				unset($url_parms['id']);

				$url_parms['message'] = 3;
				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;
		}
	}

////  Title  ////

	public static function title() 
	{ 
		new MP_Form_field_types();
		if (isset($_GET['form_id'])) { global $title; $title = __('MailPress Forms Edit Fields', MP_TXTDOM); } 
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
// for specific css
		$pathcss		= MP_ABSPATH . 'mp-includes/class/options/form/field_types_' . get_user_option('admin_color') . '.css';
		$css_url		= '/' . MP_PATH . 'mp-includes/class/options/form/field_types_' . get_user_option('admin_color') . '.css';
		$css_url_default 	= '/' . MP_PATH . 'mp-includes/class/options/form/field_types_fresh.css';
		$css_url		= (is_file($pathcss)) ? $css_url : $css_url_default;
		wp_register_style ( 'mp_field_types', 	$css_url);

		wp_register_style ( self::screen,		'/' . MP_PATH . 'mp-admin/css/form_fields.css', array('mp_field_types', 'thickbox') );
		$styles[] = self::screen;
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

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/form_fields.js', array('mp-taxonomy', 'mp-thickbox'), false, 1);

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		if (isset($_GET['form_id'])) 
		{
			$form = MP_Form::get($_GET['form_id']);
			if (isset($form->settings['visitor']['mail']) && ($form->settings['visitor']['mail'] != '0'))
				add_filter('MailPress_form_columns_form_fields', array(__CLASS__, 'add_incopy_column'), 1, 1);
		}

		$columns = array(	'cb'		=> '<input type="checkbox" />',
					'name' 	=> __('Label', MP_TXTDOM),
					'type' 	=> __('Type', MP_TXTDOM),
					'order' 	=> __('Order', MP_TXTDOM),
					'required' 	=> __('Required', MP_TXTDOM),
					'template' 	=> __('Template', MP_TXTDOM) 
				);
		return apply_filters('MailPress_form_columns_form_fields', $columns);
	}

	public static function add_incopy_column($columns)
	{
		$template 			= array_pop($columns);
		$columns['incopy']	= __('In&#160;copy', MP_TXTDOM);
        	$columns['template'] 	= $template;
		return $columns;
    }

//// List ////

	public static function get_list($args)
	{
		extract($args);

		global $wpdb;

		$order = "a.ordre";

		$where = ' AND (a.form_id = ' . $_GET['form_id'] . ') ';

		if (isset($url_parms['s']))
		{
			$sc = array('a.label', 'a.description' );

			$where .= self::get_search_clause($url_parms['s'], $sc);
		}

		$args['query'] = "SELECT DISTINCT SQL_CALC_FOUND_ROWS a.id, a.form_id, a.ordre, a.type, a.template, a.label, a.description, a.settings FROM $wpdb->mp_fields a WHERE 1=1 $where ORDER BY $order";
		$args['cache_name'] = 'mp_field';

		return parent::get_list($args);
	}

////  Row  ////

	public static function get_row( $id, $url_parms, $checkbox = true ) 
	{
		static $row_class = '';

		$field = MP_Form_field::get( $id );

		$field_types = MP_Form_field_types::get_all();

// url's
		$url_parms['action'] 	= 'edit';
		$url_parms['id'] 		= $field->id;
		$url_parms['form_id'] 	= $field->form_id;

		$edit_url = esc_url(self::url( MailPress_fields, $url_parms ));
		$url_parms['action'] 	= 'duplicate';
		$duplicate_url = esc_url(self::url( MailPress_fields, $url_parms, 'duplicate-field_' . $field->id ));
		$url_parms['action'] 	= 'delete';
		$delete_url = esc_url(self::url( MailPress_fields, $url_parms, 'delete-form_' . $field->id ));
// actions
		$actions = array();
		$actions['edit'] = '<a href="' . $edit_url . '">' . __('Edit') . '</a>';
		$actions['duplicate'] = "<a class='dim:" . self::list_id . ":" . self::tr_prefix_id . "-" . $field->id . ":unapproved:e7e7d3:e7e7d3' href='$duplicate_url'>" . __('Duplicate', MP_TXTDOM) . "</a>";
		$actions['delete'] = "<a class='submitdelete' href='$delete_url'>" . __('Delete') . "</a>";

		$row_class = 'alternate' == $row_class ? '' : 'alternate';

// protected
		$disabled = '';
		if (isset($field->settings['options']['protected']))
		{
			unset($actions['duplicate'], $actions['delete']);
			$disabled = " disabled='disabled'";
		}

		$out = '';
		$out .= "<tr id='" . self::tr_prefix_id . "-$field->id' class='iedit $row_class'>";

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
					$out .= (!$disabled) ? '<th scope="row" class="check-column"> <input type="checkbox" name="checked[]" value="' . $field->id . '" /></th>' : '<th scope="row" class="check-column"></th>';
				break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_url . '" title="' . esc_attr(sprintf(__('Edit "%s"'), $field->label)) . '">' . $field->label . '</a></strong><br />';
					$out .= self::get_actions($actions);
					$out .= '</td>';
				break;
				case 'type':
	 				$out .= "<td $attributes>";
					$out .= "<div class='field_type_" . $field->type . "'  style='padding-left:28px;'>" . $field_types[$field->type]['desc'] . "</div>";
	 				$out .= "</td>\n";
	 			break;
				case 'incopy':
	 				$out .= "<td $attributes>";
					if (isset($field->settings['options']['incopy'])) $out .= __('yes', MP_TXTDOM);
	 				$out .= "</td>\n";
	 			break;
				case 'required':
	 				$out .= "<td $attributes>";
					if (isset($field->settings['controls']['required'])) $out .= __('yes', MP_TXTDOM);
	 				$out .= "</td>\n";
	 			break;
				case 'order':
	 				$out .= "<td $attributes>";
					$out .= $field->ordre;
	 				$out .= "</td>\n";
	 			break;
				default:
					$out .= '<td ' . $attributes . '>';
					$out .= $field->{$column_name};
					$out .= '</td>';
				break;
			}
		}
		$out .= "</tr>\n";

		return $out;
	}
}