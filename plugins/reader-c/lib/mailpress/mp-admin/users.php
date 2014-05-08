<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= MailPress_page_users;
	const capability 	= 'MailPress_edit_users';
	const help_url		= 'http://blog.mailpress.org/tutorials/';
	const file        	= __FILE__;

////  Redirect  ////

	public static function redirect() 
	{
		add_action('MailPress_users_restrict', 	array(__CLASS__, 'users_restrict'), 1, 1);

		if     ( !empty($_REQUEST['action'])  && ($_REQUEST['action']  != -1))	$action = $_REQUEST['action'];
		elseif ( !empty($_REQUEST['action2']) && ($_REQUEST['action2'] != -1) )	$action = $_REQUEST['action2'];
		if (!isset($action)) return;

		$url_parms 	= self::get_url_parms();
		$checked	= (isset($_GET['checked'])) ? $_GET['checked'] : array();

		$count	= str_replace('bulk-', '', $action);
		$count     .= 'd';
		$$count	= 0;

		switch($action)
		{
			case 'bulk-activate' :
				foreach($checked as $id) if (MP_User::set_status($id, 'active'))  $$count++;
			break;
			case 'bulk-deactivate' :
				foreach($checked as $id) if (MP_User::set_status($id, 'waiting')) $$count++;
			break;
			case 'bulk-unbounce' :
				foreach($checked as $id) if (MP_User::set_status($id, 'waiting'))
				{
					MP_User_meta::delete($id, '_MailPress_bounce_handling');
					$$count++;
				}
			break;
			case 'bulk-delete' :
				foreach($checked as $id) if (MP_User::set_status($id, 'delete')) $$count++;
			break;
			default :
				$$count = do_action('MailPress_do_bulk_action_' . self::screen, $action, $checked);
			break;
		}
		if ($$count) $url_parms[$count] = $$count;
		self::mp_redirect( self::url(MailPress_users, $url_parms) );
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, 		'/' . MP_PATH . 'mp-admin/css/users.css' );

		$styles[] =self::screen;
		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts($scripts = array()) 
	{
		wp_register_script( 'mp-ajax-response', 	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 	'wpAjax', array(
			'noPerm' => __('Update database failed', MP_TXTDOM), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		));

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 		'wpListL10n', array( 
			'url' => MP_Action_url
		));

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/users.js', array('mp-lists'), false, 1);
		wp_localize_script( self::screen, 	'MP_AdminPageL10n', array(
			'pending' => __('%i% pending'), 
			'screen' => self::screen, 
			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		));

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		$disabled = (!current_user_can('MailPress_delete_users')) ? " disabled='disabled'" : '';
		$columns = array(	'cb' 		=> "<input type='checkbox'$disabled />", 
					'title' 	=> __('E-mail', MP_TXTDOM), 
					'user_name'	=> __('Name', MP_TXTDOM), 
					'author' 	=> __('Author'), 
					'date'	=> __('Date'));
		$columns = apply_filters('MailPress_users_columns', $columns);
		return $columns;
	}

//// List ////

	public static function get_list($args) 
	{
		extract($args);

		global $wpdb;

		$where = $tables = '';
		$order = "a.created";

		if (isset($url_parms['s']))
		{
			$sc = array('a.email', 'a.name', 'a.laststatus_IP', 'a.created_IP');

			$where .= self::get_search_clause($url_parms['s'], $sc);
		}

		if (isset($url_parms['status']) && !empty($url_parms['status']))
			$where .= " AND a.status = '" . $url_parms['status'] . "'";
		if (isset($url_parms['author']) && !empty($url_parms['author']))
			$where .= " AND ( a.created_user_id = " . $url_parms['author'] . "  OR a.laststatus_user_id = " . $url_parms['author'] . " ) ";

		list($where, $tables, $no_cls) = apply_filters('MailPress_users_get_list', array($where, $tables, false), $url_parms);

		if (isset($url_parms['startwith']))
		{
			$where .= " AND (a.email >= '" . $url_parms['startwith'] . "') ";
			$order = "a.email";
			$no_cls = true;
		}

		$args['query'] = "SELECT DISTINCT SQL_CALC_FOUND_ROWS a.id, a.email, a.name, a.status, a.confkey, a.created, a.created_IP, a.created_agent, a.created_user_id, a.created_country, a.created_US_state, a.laststatus, a.laststatus_IP, a.laststatus_agent, a.laststatus_user_id FROM $wpdb->mp_users a $tables WHERE 1=1 $where ORDER BY $order";
		$args['cache_name'] = 'mp_user';

		list($_users, $total) = parent::get_list($args);

		$subsubsub_urls = false;

		$libs = array( 'all' => __('All'), 'waiting' => __('Waiting', MP_TXTDOM), 'active' => __('Active', MP_TXTDOM), 'bounced' => __('Bounced', MP_TXTDOM), 'unsubscribed' => __('Unsubscribed', MP_TXTDOM), 'search' => __('Search Results') );

		$counts = array();
		$query = "SELECT status, count(*) as count FROM $wpdb->mp_users GROUP BY status;";
		$statuses = $wpdb->get_results($query);

		if ($statuses)
		{
			$status_links_url  = MailPress_users ;

			foreach($statuses as $status) if ($status->count) $counts[$status->status] = $status->count;
			$counts['all'] = $wpdb->get_var("SELECT count(*) FROM $wpdb->mp_users;");
			if (isset($url_parms['s'])) $counts['search'] = count($_users);
			$out = array();

			foreach($libs as $k => $lib)
			{
				if (!isset($counts[$k]) || !$counts[$k]) continue;

				$url = $status_links_url . ( ('search' == $k) ? '&amp;s=' . $url_parms['s'] : ( ('all' == $k) ? '' : "&amp;status=$k" ) );

				$sum = $counts[$k];

				$cls = '';
				if (!$no_cls)
				{
					if (isset($url_parms['s'])) 	  	 { if ('search' == $k) 			$cls = " class='current'"; }
					elseif (isset($url_parms['status'])) { if ($url_parms['status'] == $k )	$cls = " class='current'"; }
					elseif ('all' == $k)									$cls = " class='current'"; 
				}

				$out[] = "<a$cls href='$url'>$lib <span class='count'>(<span class='user-count-$k'>$sum</span>)</span></a>";
			}

			if (!empty($out)) $subsubsub_urls = '<li>' . join( ' | </li><li>', $out ) . '</li>';
		}
		return array($_users, $total, $subsubsub_urls);
	}

////  Row  ////

	public static function get_row( $id, $url_parms, $checkbox = true ) {

		global $mp_user;

		$mp_user = $user = MP_User::get( $id );
		$the_user_status = $user->status;

		static $to_do_add_action = true;
		if ($to_do_add_action) add_action('MailPress_get_icon_users', 	array(__CLASS__, 'get_icon_users'), 8, 1);
		$to_do_add_action = false;

// url's
		$args = array();
		$args['id'] 	= $id;

		$edit_url    	= esc_url(self::url( MailPress_user, array_merge($args, $url_parms) ));

		$args['action'] 	= 'activate';
		$activate_url 	= esc_url(self::url( MailPress_user, array_merge($args, $url_parms), "activate-user_$id"));

		$args['action'] 	= 'deactivate';
		$deactivate_url 	= esc_url(self::url( MailPress_user, array_merge($args, $url_parms), "deactivate-user_$id"));

		$args['action'] 	= 'delete';
		$delete_url  	= esc_url(self::url( MailPress_user, array_merge($args, $url_parms), "delete-user_$id"));

		unset($args['action']);

// actions
		$actions = array();
		$actions['edit']      = "<a href='$edit_url'  title='" . sprintf( __('Edit "%1$s"', MP_TXTDOM), $user->email ) . "'>" . __('Edit') . '</a>';

		$actions = apply_filters('MailPress_users_actions', $actions, $mp_user, $url_parms);

		$actions['approve']   = "<a href='$activate_url' 	class='dim:the-user-list:user-$id:unapproved:e7e7d3:e7e7d3:' title='" . sprintf( __('Activate "%1$s"', MP_TXTDOM), $user->email ) . "'>" . __( 'Activate', MP_TXTDOM ) 	 . '</a>';
		$actions['unapprove'] = "<a href='$deactivate_url' 	class='dim:the-user-list:user-$id:unapproved:e7e7d3:e7e7d3:' title='" . sprintf( __('Deactivate "%1$s"', MP_TXTDOM), $user->email ) . "'>" . __( 'Deactivate', MP_TXTDOM ) . '</a>';

		if ('bounced' == $user->status)
		{
			unset($actions['approve'], $actions['unapprove']);
			$args['action'] = 'unbounce';
			$unbounce_url   =	esc_url(self::url( MailPress_user, array_merge($args, $url_parms) ));
			$unbounce_click = "onclick=\"return (confirm('" . esc_js(sprintf( __("You are about to unbounce this MailPress user '%s'\n  'Cancel' to stop, 'OK' to unbounce.", MP_TXTDOM), $id )) . "'));\"";
			$actions['unbounce'] = "<a href='$unbounce_url' $unbounce_click title='" . sprintf( __('Unbounce "%1$s"', MP_TXTDOM), $user->email ) . "'>" . __('Unbounce', MP_TXTDOM) . '</a>';
		}

		if ('unsubscribed' == $user->status)
		{
			unset($actions['approve']);
		}

		$actions['delete']    = "<a href='$delete_url' 		class='submitdelete' title='" . __('Delete this user', MP_TXTDOM ) . "'>" . __('Delete', MP_TXTDOM) . '</a>';

		if (!current_user_can('MailPress_delete_users')) 	unset($actions['delete']);

// table row 
//	class
		$row_class = '';
		if ('waiting' == $the_user_status) $row_class = 'unapproved';
		if ('bounced' == $the_user_status) $row_class = 'bounced';
		if ('unsubscribed' == $the_user_status) $row_class = 'unsubscribed';
// 	checkbox
		$disabled = (!current_user_can('MailPress_delete_users')) ? " disabled='disabled'" : '';
// 	email
		$email_display = $user->email;
		if ( strlen($email_display) > 40 )	$email_display = substr($email_display, 0, 39) . '...';
//	author
		$x 			= (isset($url_parms['s'])) ? $url_parms['s'] : '';
		$url_parms['s'] 	= self::get_user_author_IP();
		$ip_url 		= esc_url(self::url( MailPress_users, $url_parms ));
		$url_parms['s'] 	= $x;

		$author = ( 0 == $user->laststatus_user_id) ? $user->created_user_id : $user->laststatus_user_id;
		if ($author != 0 && is_numeric($author)) {
			unset($url_parms['author']);
			$wp_user = get_userdata($author);
			$author_url = esc_url(self::url( MailPress_users, array_merge( array('author'=>$author), $url_parms) ));
		}
//	date

?>
	<tr id="user-<?php echo $id; ?>" class='<?php echo $row_class; ?>'>
<?php
		$columns = self::get_columns();
		$hidden  = self::get_hidden_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ('bounced' == $user->status) 		$style .= 'font-style:italic;';
			if ('unsubscribed' == $user->status) 	$style .= 'font-style:italic;';
			if ( in_array($column_name, $hidden) )	$style .= 'display:none;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			switch ($column_name) 
			{

				case 'cb':
					if ( $checkbox ) : 
?>
		<th class='check-column' scope='row'>
			<input type='checkbox' name='checked[]' value='<?php echo $id; ?>'<?php echo $disabled; ?> />
		</th>
<?php
	 				endif;
				break;
				case 'title' :
					$attributes = 'class="username column-username"' . $style;
?>
		<td  <?php echo $attributes ?>>
<?php self::flag_IP(); ?>
<?php	do_action('MailPress_get_icon_users', $mp_user); ?>
<?php if (get_option('show_avatars')) echo get_avatar( $user->email, 32 ); ?>
					<strong>
						<a class='row-title' href='<?php echo $edit_url; ?>' title='<?php printf( __('Edit "%1$s"', MP_TXTDOM) ,$user->email ); ?>'>
							<?php echo $email_display; ?>
						</a>
					</strong>
<?php echo self::get_actions($actions); ?>
		</td>
<?php
				break;
				case 'user_name' :
?>
		<td  <?php echo $attributes ?>>
			<abbr title="<?php echo esc_attr($user->name); ?>"><?php echo esc_attr($user->name); ?></abbr>
		</td>
<?php
				break;
				case 'date' :

					$t_time = self::get_user_date(__('Y/m/d H:i:s'));
					$h_time = self::human_time_diff(self::get_user_date_raw());
?>
		<td  <?php echo $attributes ?>>
			<abbr title="<?php echo $t_time; ?>"><?php echo $h_time; ?></abbr>
		</td>
<?php
				break;
				case 'author' :
?>
		<td  <?php echo $attributes ?>>	
<?php 				if ($author != 0 && is_numeric($author)) { ?>
				<a href='<?php echo $author_url; ?>' title='<?php printf( __('Users by "%1$s"', MP_TXTDOM), $wp_user->display_name); ?>'><?php echo $wp_user->display_name; ?></a>
<?php 				} else  	_e("(unknown)", MP_TXTDOM); ?>
		</td>
<?php
				break;
				default:
?>
		<td  <?php echo $attributes ?>>
			<?php	do_action('MailPress_users_get_row', $column_name, $user, $url_parms); ?>
		</td>
<?php
				break;
			}
		}
?>
	</tr>
<?php
	}

	public static function user_date( $d = '' ) {
		echo self::get_user_date( $d );
	}

	public static function get_user_date( $d = '' ) {
		$x = self::get_user_date_raw();
		return ( '' == $d ) ? mysql2date( get_option('date_format'), $x) : mysql2date($d, $x);
	}

	public static function get_user_date_raw() {
		global $mp_user;
		return ( $mp_user->created >= $mp_user->laststatus) ? $mp_user->created : $mp_user->laststatus;
	}

	public static function user_author_IP() {
		echo self::get_user_author_IP();
	}

	public static function get_user_author_IP() {
		global $mp_user;
		$ip = ( '' == $mp_user->laststatus_IP) ? $mp_user->created_IP : $mp_user->laststatus_IP;
		return $ip;
	}

	public static function flag_IP() {
		echo self::get_flag_IP();
	}

	public static function get_flag_IP() {
		global $mp_user;
		return (('ZZ' == $mp_user->created_country) || empty($mp_user->created_country)) ? '' : "<img class='flag' alt='" . strtolower($mp_user->created_country) . "' title='" . strtolower($mp_user->created_country) . "' src='" . site_url() . '/' . MP_PATH . 'mp-admin/images/flag/' . strtolower($mp_user->created_country) . ".gif' />\n";
	}

	public static function get_icon_users($mp_user)
	{
		if ('unsubscribed' != $mp_user->status) return;
?>
			<span class='icon unsubscribed' title="<?php _e('Unsubscribed', MP_TXTDOM); ?>"></span>
<?php
	}

//// Body ////

	public static function users_restrict($url_parms)
	{
		global $wpdb;
		$list = array();

		$query = "SELECT DISTINCT UPPER(SUBSTRING(email, 1, 1)) as letter FROM $wpdb->mp_users ORDER BY 1;";
		$letters = $wpdb->get_results( $query );

		if ($letters) $list[-1] = __('Starting with...', MP_TXTDOM);
		foreach ($letters as $letter) $list[$letter->letter] = $letter->letter;

		$selected = (isset($url_parms['startwith'])) ? $url_parms['startwith'] : -1;

		echo "<select id='letters_dropdown' class='postform' name='startwith'>\n";
		self::select_option($list, $selected);
		echo "</select>\n";
	}
}