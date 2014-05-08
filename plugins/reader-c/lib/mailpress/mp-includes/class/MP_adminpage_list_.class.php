<?php
abstract class MP_adminpage_list_ extends MP_adminpage_
{
	const per_page = true;

	function __construct()
	{
		parent::__construct();

		add_filter('set-screen-option',  					array('MP_AdminPage', 'set_screen_option'), 8, 3);

		add_filter('manage_' . MP_AdminPage::screen . '_columns', 	array('MP_AdminPage', 'get_columns'));
	}

//// Screen Options ////

	public static function screen_meta() 
	{
		parent::screen_meta();

		if (MP_AdminPage::per_page)
		{
			global $title;
			add_screen_option( 'per_page', array('label' => $title, 'default' => 20) );
		}
	}

	public static function set_screen_option($a, $b, $c)
	{
		return $c;
	}

	public static function get_per_page($default = 20)
	{
		$option = MP_AdminPage::screen . '_per_page';
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = $default;

		return (int) apply_filters( $option, $per_page );
	}

//// Columns ////

	public static function columns_list($id = true)
	{
		$columns = MP_AdminPage::get_columns();
		$hidden  = MP_AdminPage::get_hidden_columns();
		foreach ( $columns as $key => $display_name ) 
		{
			$thid  = ( $id ) ? " id='$key'" : '';
			$class = ( 'cb' === $key ) ? " class='check-column'" : " class='manage-column column-$key'";
			$style = ( in_array($key, $hidden) ) ? " style='display:none;'" : '';

			echo "<th scope='col'$thid$class$style>$display_name</th>";
		} 
	}

	public static function get_hidden_columns()
	{
		return get_hidden_columns(MP_AdminPage::screen);
	}

//// List ////

	public static function pagination( $args, $which = '' ) 
	{
		if (!is_array($args)) if (is_numeric($args)) $args = array('total_items' => $args); else return;

		$defaults = array (	'per_page' 	=> self::get_per_page(), 
						'current'   => isset( $_REQUEST['paged'] ) ? max( 1, $_REQUEST['paged'] ) : 1,
					);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		if ( !isset($total_items) ) return;
		if ( !isset($total_pages) && $per_page > 0 ) $total_pages = ceil( $total_items / $per_page );

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 ) $disable_first = ' disabled';
		if ( $current == $total_pages ) $disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='%s' value='%s' size='%d' />",
				esc_attr__( 'Current page' ),
				esc_attr( 'paged' ),
				$current,
				strlen( $total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$output .= "\n<span class='pagination-links'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages )
			$page_class = $total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';

		echo "<div class='tablenav-pages{$page_class}'>$output</div>\n";
	}

	public static function get_search_clause($s, $sc = array())
	{
		$replaces = array("\\" => "\\\\\\\\", "_" => "\_", "%" => "\%", "'" => "\'",);

		foreach($replaces as $k => $v) $s = str_replace($k, $v, $s);

		foreach($sc as $k => $v) $sc[$k] = "$v LIKE '%$s%'";

		return ' AND (' . join(' OR ', $sc) . ') '; 
	}

	public static function get_list($args) 
	{
		extract($args);

		global $wpdb;

		$start = abs( (int) $start );
		$_per_page = (int) $_per_page;

		$rows = $wpdb->get_results( "$query LIMIT $start, $_per_page" );

		self::update_cache($rows, $cache_name);

		$total = $wpdb->get_var( "SELECT FOUND_ROWS()" );

		return array($rows, $total);
	}

	public static function get_bulk_actions($bulk_actions = array(), $name = 'action')
	{
		$bulk_actions = apply_filters('MailPress_bulk_actions_' . MP_AdminPage::screen, $bulk_actions);
		if (count($bulk_actions) <=1 ) return;
?>
				<select name='<?php echo $name; ?>'>
<?php
		foreach($bulk_actions as $k => $v) :
?>
					<option <?php echo (!empty($k)) ? "value='bulk-$k'": "selected='selected' value='-1'"; ?>><?php echo $v; ?></option>
<?php
		endforeach;
?>
				</select>
				<input type="submit" name="do<?php echo $name; ?>" id="do<?php echo $name; ?>" value="<?php esc_attr_e('Apply'); ?>" class="button-secondary apply" />
<?php
	}

//// Row ////

	public static function get_actions($actions, $class = 'row-actions')
	{
		foreach ( $actions as $k => $v ) $actions[$k] = "<span class='$k'>$v";
		return "<div class='$class'>" . join( ' | </span>', $actions ) . '</span></div>';
	}

	public static function human_time_diff($m_time)
	{
		$time   = strtotime( get_gmt_from_date( $m_time ) );
		$time_diff = current_time('timestamp', true) - $time;

		if ( $time_diff <= 0 )			return __('now', MP_TXTDOM);
		elseif ( $time_diff < 24*60*60 )	return sprintf( __('%s ago'), human_time_diff( $time ) );
		else						return mysql2date(__('Y/m/d'), $m_time);
	}

////  ////

	public static function update_cache($xs, $y) 
	{
		foreach ( (array) $xs as $x ) wp_cache_add($x->id, $x, $y);
	}
}