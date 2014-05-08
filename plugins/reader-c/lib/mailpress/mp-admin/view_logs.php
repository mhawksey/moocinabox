<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= MailPress_page_view_logs;
	const capability	= 'MailPress_view_logs';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/view_logs/';
	const file        	= __FILE__;

////  Redirect  ////

	public static function redirect() 
	{
		if     ( !empty($_REQUEST['action'])  && ($_REQUEST['action']  != -1))	$action = $_REQUEST['action'];
		elseif ( !empty($_REQUEST['action2']) && ($_REQUEST['action2'] != -1) )	$action = $_REQUEST['action2'];
		if (!isset($action)) return;

		$path 	= '../' . self::get_path();

		$url_parms 	= self::get_url_parms();
		$checked	= (isset($_GET['checked'])) ? $_GET['checked'] : array();

		$count	= str_replace('bulk-', '', $action);
		$count     .= 'd';
		$$count	= 0;

		switch($action)
		{
			case 'bulk-delete' :
				foreach($checked as $file) if (@unlink($path . '/' . $file)) $$count++;
			break;
		}

		if ($$count) $url_parms[$count] = $$count;
		self::mp_redirect( self::url(MailPress_view_logs, $url_parms) );
	}

	// for path
	public static function get_path() 
	{
		return MP_PATH . 'tmp';
	}

	// for file template
	public static function get_file_template()
	{
		global $wpdb;
		return 'MP_Log_' . $wpdb->blogid . '_';
	}

////  Columns  ////

	public static function get_columns() 
	{
		$columns = array(	'cb'		=> "<input type='checkbox' />", 
					'name'	=> __('Name', MP_TXTDOM));
		return $columns;
	}

////  List  ////

	public static function get_list($args)
	{
		extract($args);

		$ftmplt	= self::get_file_template();
		$path 	= '../' . self::get_path();
		$all		= 0;

		$logs = array();
		if (is_dir($path) && ($l = opendir($path))) 
		{
			while (($file = readdir($l)) !== false) 
			{
		      	switch (true)
				{
					case ($file[0]  == '.') :
					break;
					case (strstr($file, $ftmplt)) :
						$all++;
						if (isset($url_parms['s']) && (!strstr($file, $url_parms['s']))) continue;
						$logs[filemtime("$path/$file") . $file] = $file;
					break;
				}
			}
			closedir($l);
		}
		krsort($logs);

		$total = count($logs);
		$rows  = array_slice ($logs, $start, $_per_page);

	// subsubsub
		$subsubsub_urls = false;

		$libs = array( 'all' => __('All'), 'search' => __('Search Results') );

		$status_links_url  = MailPress_view_logs ;

		$counts['all'] = $all;
		if (isset($url_parms['s'])) $counts['search'] = $total;
		$out = array();

		foreach($libs as $k => $lib)
		{
			if (!isset($counts[$k]) || !$counts[$k]) continue;
			$cls = '';
			if (isset($url_parms['s'])) 	  	 { if ('search' == $k) 			$cls = " class='current'"; }
			elseif ('all' == $k)									$cls = " class='current'"; 
			$url = $status_links_url . ( ('search' == $k) ? '&s=' . $url_parms['s'] : ( ('all' == $k) ? '' : "&amp;status=$k" ) );

			$sum = $counts[$k];
			$out[] = "<a$cls href='$url'>$lib <span class='count'>($sum)</span></a>";
		}

		if (!empty($out)) $subsubsub_urls = '<li>' . join( ' | </li><li>', $out ) . '</li>';

		return array($rows, $total, $subsubsub_urls);
	}

////  Row  ////

	public static function get_row($file, $url_parms)
	{
		static $row_class = '';

		$f 		= substr($file, strpos($file, str_replace( ABSPATH, '', WP_CONTENT_DIR )));
		$view_url 	= esc_url(MailPress_view_log . "&id=$f");
		$browse_url = '../' . self::get_path() . '/' . $f;
		$actions['view']   = "<a href='$view_url' title='" . sprintf( __('View "%1$s"', MP_TXTDOM) , $file ) . "'>"	. __('View', MP_TXTDOM) . '</a>';
		$actions['browse'] = "<a href='$browse_url' target='_blank' title='" . sprintf( __('Browse "%1$s"', MP_TXTDOM) , $file ) . "'>"	. __('Browse', MP_TXTDOM) . '</a>';

		$row_class = (" class='alternate'" == $row_class) ? '' : " class='alternate'";
		$attributes = "class='post-title column-title'";
?>
	<tr<?php echo $row_class; ?>>
		<th class="check-column" scope="row">
			<input type="checkbox" value="<?php echo $file; ?>" name="checked[]" />
		</th>
		<td  <?php echo $attributes ?>>
			<span style='display:block;'>
				<strong style='display:inline;'>
					<a class='row-title'href='<?php echo $view_url; ?>' title='<?php printf( __('View "%1$s"', MP_TXTDOM) , $file ); ?>'>
						<?php echo $file; ?>
					</a>
				</strong>
			</span>
<?php echo self::get_actions($actions); ?>
		</td>
	</tr>
<?php
	}
}