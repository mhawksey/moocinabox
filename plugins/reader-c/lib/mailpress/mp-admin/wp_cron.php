<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= MailPress_page_wp_cron;
	const capability	= 'MailPress_manage_wp_cron';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/wp_cron/';
	const file        	= __FILE__;

	const add_form_id 	= 'add';
	const list_id 		= 'the-list';
	const tr_prefix_id 	= 'wpcron';

////  Redirect  ////

	public static function redirect() 
	{
		if     ( !empty($_REQUEST['action'])  && ($_REQUEST['action']  != -1))	$action = $_REQUEST['action'];
		elseif ( !empty($_REQUEST['action2']) && ($_REQUEST['action2'] != -1) )	$action = $_REQUEST['action2'];
		if (!isset($action)) return;

		$url_parms = self::get_url_parms(array('paged', 'id', 'sig', 'next_run'));
		$checked	= (isset($_GET['checked'])) ? $_GET['checked'] : array();

		$count	= str_replace('bulk-', '', $action);
		$count     .= 'd';
		$$count	= 0;

		switch($action) 
		{
			case 'bulk-delete' :
				$crons = _get_cron_array();
				foreach($checked as $id ) 
				{
					$x = explode('::', $id);
					if( isset( $crons[$x[2]][$x[0]][$x[1]] ) )
					{
						wp_unschedule_event($x[2], $x[0], $crons[$x[2]][$x[0]][$x[1]]['args']);
						$$count++;
					}
				}

				$url_parms['message'] = ($$count <= 1) ? 3 : 4;
				if ($$count) $url_parms[$count] = $$count;
				self::mp_redirect( self::url(MailPress_wp_cron, $url_parms) );
			break;

			case 'add':
				$_POST['args'] = json_decode(stripslashes($_POST['args']), true);
				if( !is_array($_POST['args']) ) $_POST['args'] = array();

				$_POST['next_run'] = strtotime($_POST['next_run']);
				if( $_POST['next_run'] === false || $_POST['next_run'] == -1 ) $_POST['next_run'] = time();

				if( $_POST['schedule'] == '_oneoff' )
					$e = wp_schedule_single_event($_POST['next_run'], $_POST['name'], $_POST['args']) === NULL;
				else
					$e = wp_schedule_event( $_POST['next_run'], $_POST['schedule'], $_POST['name'], $_POST['args']) === NULL;

				$url_parms['message'] = ( $e ) ? 1 : 91;
				self::mp_redirect( self::url(MailPress_wp_cron, $url_parms) );
			break;
			case 'edited':
				unset($_GET['action']);

				if (!isset($_POST['cancel'])) 
				{
					$crons = _get_cron_array();
					$x = explode('::', $_POST['id']);
					if( isset( $crons[$x[2]][$x[0]][$x[1]] ) ) 
						wp_unschedule_event($x[2], $x[0], $crons[$x[2]][$x[0]][$x[1]]['args']);

					$_POST['args'] = json_decode(stripslashes($_POST['args']), true);
					if( !is_array($_POST['args']) ) $_POST['args'] = array();

					$_POST['next_run'] = strtotime($_POST['next_run']);
					if( $_POST['next_run'] === false || $_POST['next_run'] == -1 ) $_POST['next_run'] = time();

					if( $_POST['schedule'] == '_oneoff' )
						$e = wp_schedule_single_event($_POST['next_run'], $_POST['name'], $_POST['args']) === NULL;
					else
						$e = wp_schedule_event( $_POST['next_run'], $_POST['schedule'], $_POST['name'], $_POST['args']) === NULL;

					$url_parms['message'] = ( $e ) ? 2 : 92 ;
				}
				unset($url_parms['id'], $url_parms['sig'], $url_parms['next_run']);
				self::mp_redirect( self::url(MailPress_wp_cron, $url_parms) );
			break;
			case 'do_now':
				unset($_GET['action']);
				$e = false;

				$crons = _get_cron_array();
				foreach( $crons as $time => $cron ) 
				{
					if( isset( $cron[$_GET['id']][$_GET['sig']] ) ) 
					{
						wp_schedule_single_event(time()-1, $_GET['id'], $cron[$_GET['id']][$_GET['sig']]['args']);
						spawn_cron();
						$e = true;
						break;
					}
				}
				$url_parms['message'] = ( $e ) ? 5 : 95 ;
				unset($url_parms['id'], $url_parms['sig'], $url_parms['next_run']);
				self::mp_redirect( self::url(MailPress_wp_cron, $url_parms) );
			break;
			case 'delete':
				$crons = _get_cron_array();
				if( isset( $crons[$url_parms['next_run']][$url_parms['id']][$url_parms['sig']] ) ) 
					wp_unschedule_event($url_parms['next_run'], $url_parms['id'], $crons[$url_parms['next_run']][$url_parms['id']][$url_parms['sig']]['args']);

				unset($url_parms['id'], $url_parms['sig'], $url_parms['next_run']);

				$url_parms['message'] = 3;
				self::mp_redirect( self::url(MailPress_wp_cron, $url_parms) );
			break;
		}
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, 	'/' . MP_PATH . 'mp-admin/css/wp_cron.css' );
		$styles[] = self::screen;

		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts($scripts = array()) 
	{
		$scripts = apply_filters('MailPress_autorefresh_js', $scripts);
		parent::print_scripts($scripts);
	}

////  Columns  ////

	public static function get_columns() 
	{
		$columns = array(	'cb' 		=> "<input type='checkbox' />", 
					'name'	=> __('Hook name', MP_TXTDOM),
					'next'	=> __('Next&#160;run',  MP_TXTDOM),
					'rec'		=> __('Recurrence',MP_TXTDOM),
					'args'	=> __('Arguments', MP_TXTDOM),
		);
		return $columns;
	}

////  List  ////

	public static function get_list($args)
	{
		extract($args);

		$wp_crons = array();

		$crons = _get_cron_array();
		if (!$crons) $crons = array();

		foreach($crons as $time => $cron)
		{
			foreach($cron as $hook => $dings)
			{
				foreach($dings as $sig => $data)
				{
					$wp_crons[] = array(	'hook' => $hook,
	                                                'time' => $time,
	                                                'sig'  => $sig,
	                                                'data' => $data
					);
				}
			}
		}

		$total = count($wp_crons);
		$rows  = array_slice ($wp_crons, $start, $_per_page);

		return array($rows, $total);
	}

////  Row  ////

	public static function get_row($wp_cron, $url_parms)
	{
		static $row_class = '';
// url's
		$args = array();

		$args['id'] = $wp_cron['hook'];
		$args['sig'] = $wp_cron['sig'];
		$args['next_run'] = $wp_cron['time'];

		$id = $args['id'] . '::' . $args['sig'] . '::' . $args['next_run'];

		$args['action'] = 'delete';
		$delete_url = esc_url(self::url( MailPress_wp_cron, array_merge($args, $url_parms), 'delete-cron_' . $args['id'] . '_' . $args['sig'] . '_' . $args['next_run']));
		$args['action'] = 'do_now';
		$do_now_url = esc_url(self::url( MailPress_wp_cron, array_merge($args, $url_parms)));
		$args['action'] = 'edit';
		$edit_url = esc_url(self::url( MailPress_wp_cron, array_merge($args, $url_parms)));

// actions
		$actions = array();

		$actions['edit']   = "<a href='$edit_url'>" . __('Edit') . "</a>";
		$actions['do_now'] = "<a href='$do_now_url'>" . __('Do now', MP_TXTDOM) . "</a>";
		$actions['delete'] = "<a href='$delete_url'>" . __('Delete') . "</a>";

		$out  = '';
		$out .= "<tr id='wp_cron::$id' class='$row_class'>";

		$columns = self::get_columns();
		$hidden  = self::get_hidden_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array($column_name, $hidden) ) 	$style .= 'display:none;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			$out .= "<td $attributes>";

			switch ($column_name) 
			{
				case 'cb':
					$out .= "<input type='checkbox' name='checked[]' value='$id' />";
				break;
				case 'name':
					$out .= $wp_cron['hook'];
                    		$out .= self::get_actions($actions);
				break;
				case 'args':
					$out .= json_encode($wp_cron['data']['args']);
				break;
				case 'next':
					$timestamp = $wp_cron['time'];

					$time_since = self::time_since($timestamp);
					$next_date = date_i18n( 'D Y/m/d H:i:s', strtotime(get_date_from_gmt(gmdate('Y-m-d H:i:s', $timestamp))));
					$next_date = str_replace(' ', '&#160;', $next_date);
					
					$out .= "$timestamp <abbr title=\"{$time_since}\">{$next_date}</abbr>";
				break;
				case 'rec':
					$out .= ($wp_cron['data']['schedule']) ? ('<abbr title="' . sprintf(__('%1$s sec.'), $wp_cron['data']['interval']) . '">' . self::interval($wp_cron['data']['interval']) . '</abbr>') : __('Non-repeating', MP_TXTDOM);
				break;
			}

			$out .= '</td>';
		}
		$out .= "</tr>\n";

		return $out;
	}

	public static function time_since($newer_date) 
	{
		return self::interval( $newer_date - current_time('timestamp', 'gmt') );
	}

	public static function interval( $since , $max = 2 ) 
	{
		// array of time period chunks
		$chunks = array ( array(60 * 60 * 24 * 365 ,	__('%s year',   MP_TXTDOM), __('%s years',   MP_TXTDOM)),
					array(60 * 60 * 24 * 30 ,	__('%s month',  MP_TXTDOM), __('%s months',  MP_TXTDOM)),
					array(60 * 60 * 24 * 7,		__('%s week',   MP_TXTDOM), __('%s weeks',   MP_TXTDOM)),
					array(60 * 60 * 24 , 		__('%s day',    MP_TXTDOM), __('%s days',    MP_TXTDOM)),
					array(60 * 60 , 	   		__('%s hour',   MP_TXTDOM), __('%s hours',   MP_TXTDOM)),
					array(60 , 		   		__('%s minute', MP_TXTDOM), __('%s minutes', MP_TXTDOM)),
					array(1 , 				__('%s second', MP_TXTDOM), __('%s seconds', MP_TXTDOM))
		);

		if( $since <= 0 ) return __('now', MP_TXTDOM);

		$done = $total = 0;
		$output = '';

		foreach($chunks as $chunk)
		{
			$count = floor( ($since - $total) / $chunk[0]);
			if (!$count) continue;

			$total += $count * $chunk[0];

			if ($done) $output .= ' ';
			$output .= sprintf(_n($chunk[1], $chunk[2], $count), $count);
			$done++;
			if ($done == $max) break;
		}
		return $output;
	}

	public static function get_schedules() 
	{
		$schedules = array();
		$x = wp_get_schedules();
		uasort($x, create_function('$a,$b', 'return $a["interval"]-$b["interval"];'));
		foreach( $x as $name => $data ) $schedules[$name] = $data['display'] . ' (' . self::interval($data['interval']) . ')';
		$schedules['_oneoff'] = __('Non-repeating', MP_TXTDOM);
		return $schedules;
	}

	public static function get($_hook, $_sig, $_next_run) 
	{
		$crons = _get_cron_array();
		foreach( $crons as $next_run => $cron ) 
		{
			foreach( $cron as $hook => $dings) 
			{
				foreach( $dings as $sig => $data ) 
				{
					if( $hook == $_hook && $sig == $_sig && $next_run == $_next_run ) 
					{
						return array(	'hookname'	=>	$hook,
									'next_run'	=>	$next_run,
									'schedule'	=>	($data['schedule']) ? $data['schedule'] : '_oneoff',
									'sig'		=>	$sig,
									'args'	=>	$data['args']
						);
	                        }
				}
			}
		}
	}
}