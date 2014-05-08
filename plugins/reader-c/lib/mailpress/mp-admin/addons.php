<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen	= MailPress_page_addons;
	const capability= 'MailPress_manage_addons';
	const help_url	= 'http://blog.mailpress.org/tutorials/add-ons/';
	const file		= __FILE__;

	const per_page 	= false;

////  Redirect  ////

	public static function redirect() 
	{
		if     ( !empty($_REQUEST['action'])  && ($_REQUEST['action']  != -1))	$action = $_REQUEST['action'];
		elseif ( !empty($_REQUEST['action2']) && ($_REQUEST['action2'] != -1) )	$action = $_REQUEST['action2'];
		if (!isset($action)) return;

		$addons = get_option(MP_Addons::option_name);
		if (!is_array($addons)) $addons = array();

		$url_parms 	= self::get_url_parms(array('status', 's'));
		$checked	= (isset($_GET['checked'])) ? $_GET['checked'] : array();

		$count	= str_replace('bulk-', '', $action);
		$count     .= 'd';
		$$count	= 0;

		switch($action)
		{
			case 'bulk-activate' :
				foreach($checked as $addon)
				{
					if (isset($addons[$addon])) continue;
					if (MP_Addons::load($addon))
					{
						$addons[$addon] = $addon;
						do_action('activate_' . $addon);
						$$count++;
					}
				}
			break;
			case 'bulk-deactivate' :
				foreach($checked as $addon)
				{
					if (!isset($addons[$addon])) continue;
					unset($addons[$addon]);
					do_action('deactivate_' . $addon);
					$$count++;
				}
			break;
			case 'activate' :
				$addon = $_GET['addon'];
				if (isset($addons[$addon])) break;
				if (MP_Addons::load($addon))
				{
					$addons[$addon] = $addon;
					do_action('activate_' . $addon);
					$$count++;
				}
			break;
			case 'deactivate' :
				$addon = $_GET['addon'];
				if (!isset($addons[$addon])) break;
				unset($addons[$addon]);
				do_action('deactivate_' . $addon);
				$$count++;
			break;
		}
		ksort($addons);

		update_option(MP_Addons::option_name, $addons);

		if ($$count) $url_parms[$count] = $$count;
		self::mp_redirect( self::url(MailPress_addons, $url_parms) );
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'cb' 		=> "<input type='checkbox' />", 
					'title' 	=> __('Add-on', MP_TXTDOM), 
					'desc' 	=> __('Description') );
		$columns = apply_filters('MailPress_addons_columns', $columns);
		return $columns;
	}

//// List ////

	public static function get_list($args) 
	{
		extract( $args );

		$addons = MP_Addons::get_all();

		$counts['active'] = $counts['inactive'] = $counts['search'] = 0;

		$counts['all'] = count($addons);
		foreach($addons as $k => $v)
		{
			($v['active']) ? $counts['active']++ : $counts['inactive']++;
			if (isset($url_parms['s']))
			{
				if (stripos($k, $url_parms['s']) !== false) continue;
				foreach($v as $kk => $vv)
				{
					if (stripos($vv, $url_parms['s']) !== false) continue 2;
				}
				unset($addons[$k]);
			}
			if (isset($url_parms['status']))
			{
				if (($url_parms['status'] == 'inactive') && $v['active']) 		unset($addons[$k]);
				elseif (($url_parms['status'] == 'active') && !$v['active']) 	unset($addons[$k]);
			}
		}
		if (isset($url_parms['s'])) $counts['search'] = count($addons);

		$libs = array( 'all' => __('All'), 'active' =>	__('Active'), 'inactive' => __('Inactive'), 'search' => __('Search Results') );
		foreach($libs as $k => $lib)
		{
			if (!isset($counts[$k]) || !$counts[$k]) continue;
			$cls = '';
			if (isset($url_parms['s'])) 	  	 { if ('search' == $k) 				$cls = " class='current'"; }
			elseif (isset($url_parms['status'])) { if ($url_parms['status'] == $k )  	$cls = " class='current'"; }
			elseif ('all' == $k)										$cls = " class='current'"; 
	            $url = MailPress_addons . ( ('search' == $k) ? '&amp;s=' . $url_parms['s'] : ( ('all' == $k) ? '' : "&amp;status=$k" ) );
			$sum = $counts[$k];
			$out[] = "<a$cls href='$url'>$lib <span class='count'>($sum)</span></a>";
		}

		return array($addons, '<li>' . join( ' | </li><li>', $out ) . '</li>');
	}

////  Row  ////

	public static function get_row( $addon, $url_parms, $xtra = false) 
	{
		$context = (isset($url_parms['status'])) ? $url_parms['status'] : false;
		$actions = array();
// url's
		$args = array();
		$args['addon'] 	= $addon['file'];

// actions
		$actions = array();
		if ($addon['active'])
		{
			$row_class 		= 'active';
			$args['action'] 	= 'deactivate';
			$deactivate_url 	= esc_url(self::url( MailPress_addons, array_merge($args, $url_parms) ));
			$actions['deactivate']	= "<a href='$deactivate_url'   	title='" .  __('Deactivate') . "'>" . __('Deactivate') . '</a>';
			$actions = apply_filters('plugin_action_links', $actions, $addon['file'], '', '' );
		}
		else
		{
			$row_class 		= 'inactive';
			$args['action'] 	= 'activate';
			$activate_url 	= esc_url(self::url( MailPress_addons, array_merge($args, $url_parms) ));
			$actions['activate']	= "<a href='$activate_url'   	title='" .  __('Activate') . "'>" . __('Activate') . '</a>';
		}
?>
	<tr class='<?php echo $row_class; ?>'>
<?php
		$columns = self::get_columns();
		$hidden  = self::get_hidden_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array($column_name, $hidden) ) 	$style .= 'display:none;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			switch ($column_name) 
			{

				case 'cb':
?>
		<th class='check-column' scope='row'>
			<input type='checkbox' name='checked[]' value="<?php echo $addon['file']; ?>" />
		</th>
<?php
				break;
				case 'title':
					$haystack = $addon['Name'];
					$needle   = 'MailPress_';
					if (strpos($haystack, $needle) === 0) 
					{
						$haystack = substr($haystack, strlen($needle));
						$haystack = ucfirst($haystack);
					}
?>
		<td  <?php echo $attributes ?>>
<?php
					echo "<strong>{$haystack}</strong>";
					echo self::get_actions($actions, 'row-actions-visible'); ?>
		</td>
<?php
				break;
				case 'desc':
					$haystack = $addon['Description'];
					$needle   = 'This is just an add-on for MailPress to ';
					if (strpos($haystack, $needle) === 0) 
					{
						$haystack = substr($haystack, strlen($needle));
						$haystack = ucfirst($haystack);
					}
?>
		<td  <?php echo $attributes ?>>  	
<?php
					echo "<p>{$haystack}</p>";
					$addon_meta = array();
					if ( !empty($addon['Version']) )
					$addon_meta[] = sprintf(__('Version %s'), $addon['Version']);
					if ( !empty($addon['Author']) ) 
					{
						$author = $addon['Author'];
						if ( !empty($addon['AuthorURI']) )
							$author = '<a href="' . $addon['AuthorURI'] . '" title="' . __( 'Visit author homepage' ) . '">' . $addon['Author'] . '</a>';
						$addon_meta[] = sprintf( __('By %s'), $author );
					}
					if ( ! empty($addon['PluginURI']) )
						$addon_meta[] = '<a href="' . $addon['PluginURI'] . '" title="' . __( 'Visit add-on page', MP_TXTDOM ) . '">' . __('Visit add-on page', MP_TXTDOM ) . '</a>';
					echo implode(' | ', $addon_meta);
?>
		</td>
<?php
				break;
				default:
?>
		<td  <?php echo $attributes ?>>
			<?php	do_action('MailPress_addons_get_row', $column_name, $addon, $url_parms); ?>
		</td>
<?php
				break;
			}
		}
?>
	  </tr>
<?php
	}
}