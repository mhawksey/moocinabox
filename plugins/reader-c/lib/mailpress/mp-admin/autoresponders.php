<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen		= MailPress_page_autoresponders;
	const capability	= 'MailPress_manage_autoresponders';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/autoresponder/';
	const file			= __FILE__;

	const taxonomy 	= MailPress_autoresponder::taxonomy;

	const add_form_id 	= 'add';
	const list_id 		= 'the-list';
	const tr_prefix_id 	= 'atrspndr';

////  Redirect  ////

	public static function redirect() 
	{
		if     ( !empty($_REQUEST['action'])  && ($_REQUEST['action']  != -1))	$action = $_REQUEST['action'];
		elseif ( !empty($_REQUEST['action2']) && ($_REQUEST['action2'] != -1) )	$action = $_REQUEST['action2'];
		if (!isset($action)) return;

		$url_parms 	= self::get_url_parms(array('s', 'paged', 'id'));
		$checked	= (isset($_GET['checked'])) ? $_GET['checked'] : array();

		$count	= str_replace('bulk-', '', $action);
		$count     .= 'd';
		$$count	= 0;

		switch($action) 
		{
			case 'bulk-delete' :
				foreach($checked as $id) if (MP_Autoresponder::delete($id)) $$count++;

				if ($$count) $url_parms[$count] = $$count;
				$url_parms['message'] = ($$count <= 1) ? 3 : 4;
				self::mp_redirect( self::url(MailPress_autoresponders, $url_parms) );
			break;

			case 'add':
				$e = MP_Autoresponder::insert($_POST);
				$url_parms['message'] = ( $e && !is_wp_error( $e ) ) ? 1 : 91;
				unset($url_parms['s']);
				self::mp_redirect( self::url(MailPress_autoresponders, $url_parms) );
			break;
			case 'edited':
				unset($_GET['action']);
				if (!isset($_POST['cancel'])) 
				{
					$e = MP_Autoresponder::insert($_POST);
					$url_parms['message'] = ( $e && !is_wp_error( $e ) ) ? 2 : 92 ;
				}
				unset($url_parms['id']);
				self::mp_redirect( self::url(MailPress_autoresponders, $url_parms) );
			break;
			case 'delete':
				MP_Autoresponder::delete($url_parms['id']);
				unset($url_parms['id']);

				$url_parms['message'] = 3;
				self::mp_redirect( self::url(MailPress_autoresponders, $url_parms) );
			break;
		}
	}

////  Title  ////

	public static function title() { if (isset($_GET['id'])) { global $title; $title = __('Edit Autoresponder', MP_TXTDOM); } }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		$styles[] = 'thickbox';
		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts($scripts = array())  
	{
		wp_register_script( 'mp-ajax-response',	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
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

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/autoresponders.js', array('mp-taxonomy', 'mp-thickbox', 'jquery-ui-tabs'), false, 1);

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'cb' 		=> "<input type='checkbox' />", 
					'name' 	=> __('Name', MP_TXTDOM), 
					'active'	=> __('Active', MP_TXTDOM), 
					'desc'	=> __('Description', MP_TXTDOM), 
					'event' 	=> __('Event', MP_TXTDOM)
				);
		return $columns;
	}

//// List ////

	public static function get_list($args)
	{
		extract($args);

		$url_parms = self::get_url_parms(array('s', 'paged'));

		$_args = array('offset' => ($start - 1) * $_per_page, 'number' => $_per_page, 'hide_empty' => 0);
		if (isset($url_parms['s'])) $_args['search'] = $url_parms['s'];

		$terms = MP_Autoresponder::get_all($_args);
		if (empty($terms)) return false;

		echo self::_get_list($url_parms, $terms);
	}

	public static function _get_list($url_parms, $autoresponders)
	{
		$out = '';

		foreach( $autoresponders as $autoresponder ) 
			$out .= self::get_row( $autoresponder, $url_parms );

		return $out;
	}

////  Row  ////

	public static function get_row( $autoresponder, $url_parms ) 
	{
		$mp_autoresponder_registered_events = MP_Autoresponder_events::get_all();

		static $row_class = '';

		$autoresponder = MP_Autoresponder::get( $autoresponder );

		$name = $autoresponder->name ;

// url's
		$url_parms['action'] 	= 'edit';
		$url_parms['id'] 	= $autoresponder->term_id;

		$edit_url = esc_url(self::url( MailPress_autoresponders, $url_parms ));
		$url_parms['action'] 	= 'delete';
		$delete_url = esc_url(self::url( MailPress_autoresponders, $url_parms, 'delete-autoresponder_' . $autoresponder->term_id ));
// actions
		$actions = array();
		$actions['edit'] = '<a href="' . $edit_url . '">' . __('Edit') . '</a>';
		$actions['delete'] = "<a class='submitdelete' href='$delete_url'>" . __('Delete') . "</a>";

		$row_class = 'alternate' == $row_class ? '' : 'alternate';

		$out = '';
		$out .= "<tr id='" . self::tr_prefix_id . "-$autoresponder->term_id' class='iedit $row_class'>";

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
					$out .= '<th scope="row" class="check-column"> <input type="checkbox" name="checked[]" value="' . $autoresponder->term_id . '" /></th>';
				break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_url . '" title="' . esc_attr(sprintf(__('Edit "%s"'), $name)) . '">' . $name . '</a></strong><br />';
					$out .= self::get_actions($actions);
					$out .= '</td>';
				break;
				case 'active':
					$x = (isset($autoresponder->description['active'])) ? __('Yes', MP_TXTDOM) : __('No', MP_TXTDOM);
					$out .= "<td $attributes>" . $x . "</td>";
				break;
				case 'desc':
					$out .= "<td $attributes>" . stripslashes($autoresponder->description['desc']) . "</td>";
				break;
				case 'event':
					$out .= "<td $attributes>" . $mp_autoresponder_registered_events[$autoresponder->description['event']] . "</td>";
				break;
			}
		}
		$out .= '</tr>';

		return $out;
	}
}