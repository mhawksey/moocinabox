<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= 'mailpress_tracking_u';
	const capability 	= 'MailPress_tracking_users';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/tracking/';
	const file        	= __FILE__;

////  Title  ////

	public static function title() 
	{
		new MP_Tracking_metaboxes('user');

		global $title; 
		$title = __('Tracking', MP_TXTDOM); 
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		$styles[] = 'dashboard';

		wp_register_style ('mp_user', 	'/' . MP_PATH . 'mp-admin/css/users.css' );
		$styles[] = 'mp_user';

		wp_register_style ( self::screen, 	'/' . MP_PATH . 'mp-admin/css/tracking_u.css' );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts($scripts = array())  
	{
		$scripts = apply_filters('MailPress_autorefresh_js', $scripts);

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/tracking_t.js', array('postbox'), false, 1);
		wp_localize_script( self::screen, 		'MP_AdminPageL10n',  array(
			'screen' => self::screen
		));

		$scripts[] = self::screen;

		parent::print_scripts($scripts);
	}

////  Metaboxes  ////

	public static function screen_meta() 
	{
		do_action('MailPress_tracking_add_meta_box', self::screen);
		parent::screen_meta();
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'title' 	=> __('E-mail', MP_TXTDOM), 
					'user_name'	=> __('Name', MP_TXTDOM), 
					'author' 	=> __('Author'), 
					'date'	=> __('Date'));
		$columns = apply_filters('MailPress_users_columns', $columns);
		return $columns;
	}

	public static function columns_list($id = true)
	{
		$columns = self::get_columns();
		$hidden  = array();
		foreach ( $columns as $key => $display_name ) 
		{
			$thid  = ( $id ) ? " id='$key'" : '';
			$class = ( 'cb' === $key ) ? " class='check-column'" : " class='manage-column column-$key'";
			$style = ( in_array($key, $hidden) ) ? " style='display:none;'" : '';

			echo "<th scope='col'$thid$class$style>$display_name</th>";
		} 
	}

////  Row  ////

	public static function get_row( $id, $url_parms, $xtra = false)
	{
		add_action('MailPress_get_icon_users', 	array(__CLASS__, 'get_icon_users'), 8, 1);

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

		$author = ( 0 == $user->laststatus_user_id) ? $user->created_user_id : $user->laststatus_user_id;
		if ($author != 0 && is_numeric($author)) {
			unset($url_parms['author']);
			$wp_user = get_userdata($author);
			$author_url = esc_url(self::url( MailPress_users, array_merge( array('author'=>$author), $url_parms) ));
		}

// actions
		$actions = array();
		$actions['edit']      = "<a href='$edit_url'  title='" . sprintf( __('Edit "%1$s"', MP_TXTDOM), $mp_user->email ) . "'>" . __('Edit') . '</a>';

// table row 
//	class
		$row_class = '';
		if ('waiting' == $the_user_status) $row_class = 'unapproved';
		if ('bounced' == $the_user_status) $row_class = 'bounced';
		if ('unsubscribed' == $the_user_status) $row_class = 'unsubscribed';
?>
	<tr id="user-<?php echo $id; ?>" class='<?php echo $row_class; ?>'>
<?php
		$columns = self::get_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ('bounced' == $mp_user->status) 		$style .= 'font-style:italic;';
			if ('unsubscribed' == $mp_user->status) 	$style .= 'font-style:italic;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			switch ($column_name) 
			{
				case 'title' :
					$attributes = 'class="username column-username"' . $style;
?>
		<td  <?php echo $attributes ?>>
<?php self::flag_IP(); ?>
<?php	do_action('MailPress_get_icon_users', $mp_user); ?>
<?php if (get_option('show_avatars')) echo get_avatar( $user->email, 32 ); ?>
			<strong>
				<a class='row-title' href='<?php echo $edit_url; ?>' title='<?php printf( __('Edit "%1$s"', MP_TXTDOM) ,$mp_user->email ); ?>'>
					<?php echo $mp_user->email; ?>
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
}