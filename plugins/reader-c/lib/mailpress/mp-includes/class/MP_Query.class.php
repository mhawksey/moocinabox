<?php
class MP_Query 
{

	var $query;
	var $query_vars = array();
	var $queried_object;
	var $queried_object_id;
	var $request;
	var $mails;
	var $mail_count = 0;
	var $current_mail = -1;
	var $in_the_loop = false;
	var $mail;
	var $found_mails = 0;

	function __construct($query = '')
	{
		if ( !empty($query) ) $this->query($query);
	}

	function &query($query = '')
	{
		$this->parse_query($query);
		return $this->get_mails();
	}

	function parse_query($query) 
	{
		if ( !empty($query) || !isset($this->query) ) 
		{
			$this->init();
			if ( is_array($query) )	$this->query_vars = $query;
			else				parse_str($query, $this->query_vars);
			$this->query = $query;
		}

		$this->query_vars = $this->fill_query_vars($this->query_vars);
		$qv = &$this->query_vars;

		$absints = array(
			  'day'
			, 'm'
			, 'mail'
			, 'monthnum'
			, 'w'	
			, 'year'
		);
		foreach ( $absints as $absint ) if ( isset($qv[$absint])) $qv[$absint] = absint($qv[$absint]);
		if ( '' !== $qv['hour'] ) $qv['hour'] = absint($qv['hour']);
		if ( '' !== $qv['minute'] ) $qv['minute'] = absint($qv['minute']);
		if ( '' !== $qv['second'] ) $qv['second'] = absint($qv['second']);
	}

	function init() 
	{
		unset($this->mails);
		unset($this->query);
		$this->query_vars = array();
		unset($this->queried_object);
		unset($this->queried_object_id);
		$this->mail_count = 0;
		$this->current_mail = -1;
		$this->in_the_loop = false;

		$this->init_query_flags();
	}

	function init_query_flags() {}

	function fill_query_vars($array) {
		$keys = array(
			'error'

			, 'day'
			, 'w'	
			, 'monthnum'
			, 'year'
			, 'm'
			, 'hour'
			, 'minute'
			, 'second'

			, 'mail'
			, 'Theme'
			, 'Template'

			, 'fromemail'
			, 'fromname'
			, 'toemail'
			, 'author_name'

			, 'meta_key'
			, 'meta_value'
,'paged'
			, 's'
		);

		foreach ( $keys as $key ) if ( !isset($array[$key])) $array[$key] = '';

		$array_keys = array(
			'emails'
			, 'mail__in'
			, 'mail__not_in'
			, 'Theme__in'
			, 'Theme__not_in'
			, 'Template__in'
			, 'Template__not_in'
		);

		foreach ( $array_keys as $key ) if ( !isset($array[$key]))	$array[$key] = array();

		return $array;
	}

	function parse_query_vars() 
	{
		$this->parse_query('');
	}

	function get($query_var) 
	{
		return ( isset($this->query_vars[$query_var]) ) ? $this->query_vars[$query_var] : '';
	}

	function set($query_var, $value) 
	{
		$this->query_vars[$query_var] = $value;
	}

	function &get_mails() {
		global $wpdb;

		// Shorthand.
		$q = &$this->query_vars;

		$q = $this->fill_query_vars($q);

		// First let's clear some variables
		$distinct = '';
		$whichauthor = '';
		$where = '';
		$limits = '';
		$join = '';
		$search = '';
		$groupby = '';
		$fields = "$wpdb->mp_mails.*";
		$mail_status_join = false;
		$page = 1;

		if ( !isset($q['caller_get_mails']) ) $q['caller_get_mails'] = false;

//		if ( !isset($q['suppress_filters']) ) 
			$q['suppress_filters'] = true;

		if ( !isset($q['mails_per_page']) || $q['mails_per_page'] == 0 ) $q['mails_per_page'] = get_option('posts_per_page');
		if ( isset($q['showmails']) && $q['showmails'] ) 
		{
			$q['showmails'] = (int) $q['showmails'];
			$q['mails_per_page'] = $q['showmails'];
		}
		if ( !isset($q['nopaging']) ) $q['nopaging'] = ( $q['mails_per_page'] == -1 );

		$q['mails_per_page'] = (int) $q['mails_per_page'];
		if ( $q['mails_per_page'] < -1 )     $q['mails_per_page'] = abs($q['mails_per_page']);
		elseif ( $q['mails_per_page'] == 0 ) $q['mails_per_page'] = 1;

		// If true, forcibly turns off SQL_CALC_FOUND_ROWS even when limits are present.
		if ( isset($q['no_found_rows']) )
			$q['no_found_rows'] = (bool) $q['no_found_rows'];
		else
			$q['no_found_rows'] = false;

		// If a month is specified in the querystring, load that month
		if ( $q['m'] ) {
			$q['m'] = '' . preg_replace('|[^0-9]|', '', $q['m']);
			$where .= " AND YEAR($wpdb->mp_mails.sent)=" . substr($q['m'], 0, 4);
			if ( strlen($q['m']) > 5 )
				$where .= " AND MONTH($wpdb->mp_mails.sent)=" . substr($q['m'], 4, 2);
			if ( strlen($q['m']) > 7 )
				$where .= " AND DAYOFMONTH($wpdb->mp_mails.sent)=" . substr($q['m'], 6, 2);
			if ( strlen($q['m']) > 9 )
				$where .= " AND HOUR($wpdb->mp_mails.sent)=" . substr($q['m'], 8, 2);
			if ( strlen($q['m']) > 11 )
				$where .= " AND MINUTE($wpdb->mp_mails.sent)=" . substr($q['m'], 10, 2);
			if ( strlen($q['m']) > 13 )
				$where .= " AND SECOND($wpdb->mp_mails.sent)=" . substr($q['m'], 12, 2);
		}

		if ( '' !== $q['hour'] )
			$where .= " AND HOUR($wpdb->mp_mails.sent)='" . $q['hour'] . "'";

		if ( '' !== $q['minute'] )
			$where .= " AND MINUTE($wpdb->mp_mails.sent)='" . $q['minute'] . "'";

		if ( '' !== $q['second'] )
			$where .= " AND SECOND($wpdb->mp_mails.sent)='" . $q['second'] . "'";

		if ( $q['year'] )
			$where .= " AND YEAR($wpdb->mp_mails.sent)='" . $q['year'] . "'";

		if ( $q['monthnum'] )
			$where .= " AND MONTH($wpdb->mp_mails.sent)='" . $q['monthnum'] . "'";

		if ( $q['day'] )
			$where .= " AND DAYOFMONTH($wpdb->mp_mails.sent)='" . $q['day'] . "'";

		if ( $q['w'] )
			$where .= ' AND ' . _wp_mysql_week( "`$wpdb->mp_mails`.`sent`" ) . " = '" . $q['w'] . "'";

		// If a mail number is specified, load that mail
		if ( $q['mail'] ) 
		{
			$where .= " AND {$wpdb->mp_mails}.id = " . $q['mail'];
		} 
		elseif ( $q['mail__in'] ) 
		{
			$mail__in = implode(',', array_map( 'absint', $q['mail__in'] ));
			$where .= " AND {$wpdb->mp_mails}.id IN ($mail__in)";
		}
		elseif ( $q['mail__not_in'] ) 
		{
			$mail__not_in = implode(',',  array_map( 'absint', $q['mail__not_in'] ));
			$where .= " AND {$wpdb->mp_mails}.id NOT IN ($mail__not_in)";
		}

		// If a Theme is specified, load that mail
		if ( $q['Theme'] ) 
		{
			$where .= " AND {$wpdb->mp_mails}.theme = '" . $q['Theme'] . "'";
		} 
		elseif ( $q['Theme__in'] ) 
		{
			$Theme__in = implode("','", $q['Theme__in'] );
			$where .= " AND {$wpdb->mp_mails}.theme IN ('$Theme__in')";
		}
		elseif ( $q['Theme__not_in'] ) 
		{
			$Theme__not_in = implode("','", $q['Theme__not_in'] );
			$where .= " AND {$wpdb->mp_mails}.theme NOT IN ('$Theme__not_in')";
		}

		// If a Template is specified, load that mail
		if ( $q['Template'] ) 
		{
			$where .= " AND {$wpdb->mp_mails}.template = '" . $q['Template'] . "'";
		} 
		elseif ( $q['Template__in'] ) 
		{
			$Template__in = implode("','", $q['Template__in'] );
			$where .= " AND {$wpdb->mp_mails}.template IN ('$Template__in')";
		}
		elseif ( $q['Template__not_in'] ) 
		{
			$Template__not_in = implode("','",  $q['Template__not_in'] );
			$where .= " AND {$wpdb->mp_mails}.template NOT IN ('$Template__not_in')";
		}

		// If a fromemail is specified, load that mail
		if ( $q['fromemail'] ) 
		{
			$where .= " AND {$wpdb->mp_mails}.fromemail = '" . $q['fromemail'] . "'";
		}

		// If a fromname is specified, load that mail
		if ( $q['fromname'] ) 
		{
			$where .= " AND {$wpdb->mp_mails}.fromname = '" . $q['fromname'] . "'";
		} 

		// If a fromemail is specified, load that mail
		if ( $q['toemail'] ) 
		{
			$where .= " AND {$wpdb->mp_mails}.toemail LIKE '%" . $q['toemail'] . "%'";
		} 

		// If a search pattern is specified, load the mails that match
		if ( !empty($q['s']) ) {
			// added slashes screw with quote grouping when done early, so done later
			$q['s'] = stripslashes($q['s']);
			if ( !empty($q['sentence']) ) {
				$q['search_terms'] = array($q['s']);
			} else {
				preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $q['s'], $matches);
				$q['search_terms'] = array_map('_search_terms_tidy', $matches[0]);
			}
			$n = !empty($q['exact']) ? '' : '%';
			$searchand = '';
			foreach( (array) $q['search_terms'] as $term ) {
				$term = addslashes_gpc($term);
				$search .= "{$searchand}(($wpdb->mp_mails.toemail LIKE '{$n}{$term}{$n}') OR ($wpdb->mp_mails.toname LIKE '{$n}{$term}{$n}')) OR ($wpdb->mp_mails.subject LIKE '{$n}{$term}{$n}')) OR ($wpdb->mp_mails.html LIKE '{$n}{$term}{$n}')) OR ($wpdb->mp_mails.plaintext LIKE '{$n}{$term}{$n}'))";
				$searchand = ' AND ';
			}
			$term = esc_sql($q['s']);
			if ( empty($q['sentence']) && count($q['search_terms']) > 1 && $q['search_terms'][0] != $q['s'] )
				$search .= " OR ($wpdb->mp_mails.toemail LIKE '{$n}{$term}{$n}') OR ($wpdb->mp_mails.toname LIKE '{$n}{$term}{$n}')) OR ($wpdb->mp_mails.subject LIKE '{$n}{$term}{$n}')) OR ($wpdb->mp_mails.html LIKE '{$n}{$term}{$n}')) OR ($wpdb->mp_mails.plaintext LIKE '{$n}{$term}{$n}'))";

			if ( !empty($search) ) $search = " AND ({$search}) ";
		}

		// Allow plugins to contextually add/remove/modify the search section of the database query
		$search = apply_filters_ref_array('MailPress_mails_search', array( $search, $this ) );

		// Author/user stuff

		if ( empty($q['author']) || ($q['author'] == '0') ) {
			$whichauthor = '';
		} else {
			$q['author'] = (string)urldecode($q['author']);
			$q['author'] = addslashes_gpc($q['author']);
			if ( strpos($q['author'], '-') !== false ) {
				$eq = '!=';
				$andor = 'AND';
				$q['author'] = explode('-', $q['author']);
				$q['author'] = (string)absint($q['author'][1]);
			} else {
				$eq = '=';
				$andor = 'OR';
			}
			$author_array = preg_split('/[,\s]+/', $q['author']);
			$_author_array = array();
			foreach ( $author_array as $key => $_author )
				$_author_array[] = "$wpdb->posts.post_author " . $eq . ' ' . absint($_author);
			$whichauthor .= ' AND (' . implode(" $andor ", $_author_array) . ')';
			unset($author_array, $_author_array);
		}

		// Author stuff for nice URLs

		if ( '' != $q['author_name'] ) {
			if ( strpos($q['author_name'], '/') !== false ) {
				$q['author_name'] = explode('/', $q['author_name']);
				if ( $q['author_name'][ count($q['author_name'])-1 ] ) {
					$q['author_name'] = $q['author_name'][count($q['author_name'])-1]; // no trailing slash
				} else {
					$q['author_name'] = $q['author_name'][count($q['author_name'])-2]; // there was a trailling slash
				}
			}
			$q['author_name'] = sanitize_title($q['author_name']);
			$q['author'] = get_user_by('slug', $q['author_name']);
			if ( $q['author'] )
				$q['author'] = $q['author']->ID;
			$whichauthor .= " AND ($wpdb->posts.post_author = " . absint($q['author']) . ')';
		}

		$where .= $search . $whichauthor;

		if ( empty($q['order']) || ((strtoupper($q['order']) != 'ASC') && (strtoupper($q['order']) != 'DESC')) )
			$q['order'] = 'DESC';

		// Order by
		if ( empty($q['orderby']) ) 
		{
			$q['orderby'] = "$wpdb->mp_mails.sent " . $q['order'];
		} 
		elseif ( 'none' == $q['orderby'] ) 
		{
			$q['orderby'] = '';
		}
		else 
		{
			// Used to filter values
			$allowed_keys = array('id', 'author', 'sent', 'subject', 'rand');
			if ( !empty($q['meta_key']) ) {
				$allowed_keys[] = $q['meta_key'];
				$allowed_keys[] = 'meta_value';
				$allowed_keys[] = 'meta_value_num';
			}
			$q['orderby'] = urldecode($q['orderby']);
			$q['orderby'] = addslashes_gpc($q['orderby']);
			$orderby_array = explode(' ', $q['orderby']);
			$q['orderby'] = '';

			foreach ( $orderby_array as $i => $orderby ) {
				// Only allow certain values for safety
				if ( ! in_array($orderby, $allowed_keys) )
					continue;

				switch ( $orderby ) {
					case 'id':
						$orderby = "$wpdb->mp_mails.id";
						break;
					case 'rand':
						$orderby = 'RAND()';
						break;
					case $q['meta_key']:
					case 'meta_value':
						$orderby = "$wpdb->mp_mailmeta.meta_value";
						break;
					case 'meta_value_num':
						$orderby = "$wpdb->mp_mailmeta.meta_value+0";
						break;
					default:
						$orderby = "$wpdb->mp_mails." . $orderby;
				}

				$q['orderby'] .= (($i == 0) ? '' : ',') . $orderby;
			}

			// append ASC or DESC at the end
			if ( !empty($q['orderby']))
				$q['orderby'] .= " {$q['order']}";

			if ( empty($q['orderby']) )
				$q['orderby'] = "$wpdb->mp_mails.sent ".$q['order'];
		}

		// mailmeta queries
		if ( ! empty($q['meta_key']) || ! empty($q['meta_value']) )
			$join .= " JOIN $wpdb->mp_mailmeta ON ($wpdb->mp_mails.id = $wpdb->mp_mailmeta.mp_mail_id) ";
		if ( ! empty($q['meta_key']) )
			$where .= $wpdb->prepare(" AND $wpdb->mp_mailmeta.meta_key = %s ", $q['meta_key']);
		if ( ! empty($q['meta_value']) ) {
			if ( empty($q['meta_compare']) || ! in_array($q['meta_compare'], array('=', '!=', '>', '>=', '<', '<=')) )
				$q['meta_compare'] = '=';

			$where .= $wpdb->prepare("AND $wpdb->mp_mailmeta.meta_value {$q['meta_compare']} %s ", $q['meta_value']);
		}

		// Paging
		if ( empty($q['nopaging']) ) 
		{
			$page = absint($q['paged']);
			if ( empty($page) )
				$page = 1;

			if ( empty($q['offset']) ) 
			{
				$pgstrt = '';
				$pgstrt = ($page - 1) * $q['mails_per_page'] . ', ';
				$limits = 'LIMIT ' . $pgstrt . $q['mails_per_page'];
			}
			else 
			{
				$q['offset'] = absint($q['offset']);
				$pgstrt = $q['offset'] . ', ';
				$limits = 'LIMIT ' . $pgstrt . $q['mails_per_page'];
			}
		}

		$orderby = $q['orderby'];

		if ( ! empty($groupby) )
			$groupby = 'GROUP BY ' . $groupby;
		if ( !empty( $orderby ) )
			$orderby = 'ORDER BY ' . $orderby;
		$found_rows = '';
		if ( !$q['no_found_rows'] && !empty($limits) )
			$found_rows = 'SQL_CALC_FOUND_ROWS';

		$this->request = " SELECT $found_rows $distinct $fields FROM $wpdb->mp_mails $join WHERE $wpdb->mp_mails.status = 'archived' $where $groupby $orderby $limits";

		$this->mails = $wpdb->get_results($this->request);

		$this->mail_count = count($this->mails);

		if ( $this->mail_count > 0 ) $this->mail = $this->mails[0];

		return $this->mails;
	}

	function have_mails() 
	{
		if ( $this->current_mail + 1 < $this->mail_count ) return true;

		if ( $this->current_mail + 1 == $this->mail_count && $this->mail_count > 0 ) 
			$this->rewind_mails();

		$this->in_the_loop = false;
		return false;
	}

	function rewind_mails() 
	{
		$this->current_mail = -1;
		if ( $this->mail_count > 0 ) $this->mail = $this->mails[0];
	}

	function in_the_loop() 
	{
		return $this->in_the_loop;
	}

	function the_mail() 
	{
		$this->in_the_loop = true;
		$this->next_mail();
	}

	function next_mail() 
	{
		$this->current_mail++;
		$this->mail = $this->mails[$this->current_mail];
	}

	function the_ID() 
	{
		echo $this->get_the_ID();
	}
	function get_the_ID() 
	{
		return $this->mail->id;
	}

	function the_subject($before = '', $after = '', $echo = true) 
	{
		$subject = $this->get_the_subject();

		if ( strlen($subject) == 0 ) return;

		$subject = $before . $subject . $after;

		if ( $echo ) echo $subject;
		else		 return $subject;
	}
	function get_the_subject() 
	{
		$metas = MP_Mail_meta::get( $this->mail->id, '_MailPress_replacements' );

		$subject_display = $this->mail->subject;
		if ($metas) foreach($metas as $k => $v) $subject_display = str_replace($k, $v, $subject_display);

		return apply_filters( 'MailPress_the_subject', $subject_display, $this->mail->id );
	}

	function the_content($format = 'html') 
	{
		echo $this->get_the_content($format);
	}
	function get_the_content($format = 'html') 
	{
		if (empty($this->mail->html) || ($format == 'plaintext')) return apply_filters('the_content', $this->mail->plaintext);
		return $this->mail->html;
	}

	function the_date( $d = '' )
	{
		echo apply_filters('the_date', $this->get_the_date( $d, true ), $d);
	}
	function get_the_date( $d = '', $translate = false )
	{
		if ( '' == $d ) $d = get_option('date_format');
		return mysql2date($d, $this->mail->sent, $translate);
	}

	function the_time( $d = '' ) 
	{
		echo apply_filters('the_time', $this->get_the_time( $d, true ), $d);
	}
	function get_the_time( $d = '', $translate = false ) 
	{
		if ( '' == $d ) $d = get_option('time_format');
		return mysql2date($d, $this->mail->sent, $translate);
	}

	function the_Theme() 
	{
		echo $this->get_the_Theme();
	}
	function get_the_Theme()
	{
		return $this->mail->theme;
	}

	function the_Template() 
	{
		echo $this->get_the_Template();
	}
	function get_the_Template()
	{
		return $this->mail->template;
	}

	function the_permalink() 
	{
		echo $this->get_the_permalink();
	}
	function get_the_permalink($action = 'view', $key = 1)
	{
		return MP_::url(  MP_Action_url , array('action' => 'view', 'id' => $this->mail->id, 'key' => '0'));
	}

	function get_mp_query_var($var) 
	{
		return $this->get($var);
	}

	function set_query_var($var, $value) 
	{
		return $this->set($var, $value);
	}
}