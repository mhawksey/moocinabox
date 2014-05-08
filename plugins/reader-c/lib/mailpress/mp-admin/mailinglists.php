<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= MailPress_page_mailinglists;
	const capability 	= 'MailPress_manage_mailinglists';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/mailinglist/';
	const file        	= __FILE__;

	const taxonomy 		= MailPress_mailinglist::taxonomy;

	const add_form_id 	= 'add';
	const list_id 		= 'the-list';
	const tr_prefix_id 	= 'mlnglst';

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
				foreach($checked as $id)
				{
					if ( $id == get_option(MailPress_mailinglist::option_name_default) )
						wp_die(sprintf(__("Can&#8217;t delete the <strong>%s</strong> mailing list: this is the default one", MP_TXTDOM), MP_Mailinglist::get_name($id)));

					if (MP_Mailinglist::delete($id)) $$count++;
				}

				if ($$count) $url_parms[$count] = $$count;
				$url_parms['message'] = ($$count <= 1) ? 3 : 4;
				self::mp_redirect( self::url(MailPress_mailinglists, $url_parms) );
			break;

			case 'add':
				$e = MP_Mailinglist::insert($_POST);
				$url_parms['message'] = ( $e && !is_wp_error( $e ) ) ? 1 : 91;
				unset($url_parms['s']);
				self::mp_redirect( self::url(MailPress_mailinglists, $url_parms) );
			break;
			case 'edited':
				unset($_GET['action']);
				if (!isset($_POST['cancel'])) 
				{
					$e = MP_Mailinglist::insert($_POST);
					$url_parms['message'] = ( $e && !is_wp_error( $e ) ) ? 2 : 92 ;
				}
				unset($url_parms['id']);
				self::mp_redirect( self::url(MailPress_mailinglists, $url_parms) );
			break;
			case 'delete':
				if ( $url_parms['id'] == get_option(MailPress_mailinglist::option_name_default) )
					wp_die(sprintf(__("Can&#8217;t delete the <strong>%s</strong> mailing list: this is the default one", MP_TXTDOM), MP_Mailinglist::get_name($id)));

				MP_Mailinglist::delete($url_parms['id']);
				unset($url_parms['id']);

				$url_parms['message'] = 3;
				self::mp_redirect( self::url(MailPress_mailinglists, $url_parms) );
			break;
		}
	}

////  Title  ////

	public static function title() { if (isset($_GET['id'])) { global $title; $title = __('Edit Mailinglist', MP_TXTDOM); } }

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

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-includes/js/mp_taxonomy.js', array('mp-lists'), false, 1);
		wp_localize_script( self::screen, 		'MP_AdminPageL10n', array(
			'pending' => __('%i% pending'), 
			'screen'  => self::screen,
			'list_id' => self::list_id,
			'add_form_id' => self::add_form_id,
			'tr_prefix_id' => self::tr_prefix_id,
			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		));

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'cb'		=> '<input type="checkbox" />',
					'name' 	=> __('Name', MP_TXTDOM),
					'desc'	=> __('Description', MP_TXTDOM),
					'allowed'	=> __('Allowed', MP_TXTDOM),
					'num' 	=> __('MP users', MP_TXTDOM));
		return $columns;
	}

//// List ////

	public static function get_list($args)
	{
		extract($args);

		$url_parms = self::get_url_parms(array('s', 'paged'));

		$_args = array('offset' => ($start - 1) * $_per_page, 'number' => $_per_page, 'hide_empty' => 0);
		if (isset($url_parms['s'])) $_args['search'] = $url_parms['s'];

		$_terms = MP_Mailinglist::get_all($_args);
		if (empty($_terms)) return false;

		$children = _get_term_hierarchy(self::taxonomy);

		foreach($_terms as $_term) { $_term->_found = true; $terms[$_term->term_id] = $_term; }
		unset($_terms, $_term);

		foreach($terms as $term)
		{
			if (!$my_parent = $term->parent) continue; 

			do {  
				if (!isset($terms[$my_parent])) $terms[$my_parent] = get_term( $my_parent, self::taxonomy );  
				$my_parent = $terms[$my_parent]->parent;  
			} while ( $my_parent );
		}
		echo self::_get_list($url_parms, $terms, $children);
	}

	public static function _get_list($url_parms, $mailinglists, &$children, $level = 0, $parent = 0 )
	{
		$out = ''; 
		foreach ( $mailinglists as $key => $mailinglist )  
		{ 
			if ( $parent == $mailinglist->parent )  
			{ 
				$out .= self::get_row( $mailinglist, $url_parms, $level ); 
				unset( $mailinglists[ $key ] ); 
				if ( isset($children[$mailinglist->term_id]) ) 
				$out .= self::_get_list( $url_parms, $mailinglists, $children, $level + 1, $mailinglist->term_id );
			} 
		} 
		return $out;
	}

////  Row  ////

	public static function get_row( $mailinglist, $url_parms, $level, $name_override = false ) 
	{
		global $mp_subscriptions;

		static $row_class = '';

		$mailinglist = MP_Mailinglist::get( $mailinglist );

		$default_mailinglist_id = get_option( MailPress_mailinglist::option_name_default );
		$pad = str_repeat( '&#8212; ', $level );
		$name = ( $name_override ) ? $name_override : $pad . ' ' . $mailinglist->name ;

// url's
		$url_parms['action'] = 'edit';
		$url_parms['id'] = $mailinglist->term_id;

		$edit_url = esc_url(self::url( MailPress_mailinglists, $url_parms ));
		$url_parms['action']	= 'delete';
		$delete_url = esc_url(self::url( MailPress_mailinglists, $url_parms,  'delete-mailinglist_' . $mailinglist->term_id ));
// actions
		$actions = array();
		$actions['edit'] = '<a href="' . $edit_url . '">' . __('Edit') . '</a>';
		$actions['delete'] = "<a class='submitdelete' href='$delete_url'>" . __('Delete') . "</a>";

		if ( $default_mailinglist_id == $mailinglist->term_id ) 
			unset($actions['delete']);

		$mailinglist->allowed = (isset($mp_subscriptions['display_mailinglists'][$mailinglist->term_id]));

		$tr_style = (isset($mailinglist->_found)) ? '' : " style='background-color:#EBEEEF;'"; 

		$out = '';
		$out .= "<tr id='" . self::tr_prefix_id . "-$mailinglist->term_id' class='iedit $row_class'$tr_style>";

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
					$out .= "<th scope='row' class='check-column'>";
					if ( $default_mailinglist_id != $mailinglist->term_id ) {
						$out .= "<input type='checkbox' name='checked[]' value='$mailinglist->term_id' />";
					} else {
						$out .= "&#160;";
					}
					$out .= '</th>';
				break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_url . '" title="' . esc_attr(sprintf(__('Edit "%s"'), $name)) . '">' . $name . '</a></strong><br />';
					$out .= self::get_actions($actions);
					$out .= '</td>';
	 			break;
	 			case 'desc':
	 				$out .= "<td $attributes>" . stripslashes($mailinglist->description) . "</td>";
	 			break;
	 			case 'allowed':
	 				$out .= "<td $attributes>" . (($mailinglist->allowed) ? __('yes', MP_TXTDOM) : __('no', MP_TXTDOM)) . "</td>";
	 			break;
				case 'num':
					$mailinglist->count = number_format_i18n( $mailinglist->count );

					if (current_user_can('MailPress_edit_users')) 
						$mp_users_count = ( $mailinglist->count > 0 ) ? "<a href='" . MailPress_users . "&amp;mailinglist=$mailinglist->term_id'>$mailinglist->count</a>" : $mailinglist->count;
					else
						$mp_users_count =  $mailinglist->count;

	 				$attributes = 'class="num column-num"' . $style;
					$out .= "<td $attributes>$mp_users_count</td>\n";
	 			break;
			}
		}
		$out .= "</tr>\n";

		return $out;
	}
}