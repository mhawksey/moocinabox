<?php
class MP_Mail_draft
{
	public static function update($id, $status='draft')
	{
		global $wpdb, $mp_general;
		$id = (int) $id;

		wp_cache_delete($id, 'mp_mail');
		$draft = MP_Mail::get($id);

// scheduled ?
		$scheduled = false;
		$draft->sent = '0000-00-00 00:00:00';

		wp_clear_scheduled_hook( 'mp_process_send_draft', array( $id ) );

		if (isset($_POST['aa'])) 
		{
			foreach ( array('aa', 'mm', 'jj', 'hh', 'mn') as $timeunit ) 
			{
				$$timeunit = $_POST[$timeunit];
				if ( $_POST['cur_' . $timeunit] == $_POST[$timeunit] ) continue;
	
				$scheduled = true;
			}
		// update schedule ?
			if ($scheduled)
			{
				$aa = ( $aa < 1 )  ? date('Y') : $aa;
				$maxd = array(31,(!($aa%4)&&($aa%100||!($aa%400)))?29:28,31,30,31,30,31,31,30,31,30,31); 
				$mm = ( $mm < 1 || $mm > 12 ) ? date('n') : $mm;
				$jj = ( $jj < 1 ) ? 1 : $jj;
				$jj = ( $jj > $maxd[$mm-1] ) ? $maxd[$mm-1] : $jj;
				$hh = ( $hh < 0 || $hh > 23 ) ? 00 : $hh;
				$mn = ( $mn < 0 || $mn > 59 ) ? 00 : $mn;
	
				$draft->sent = date('Y-m-d H:i:s', mktime($hh, $mn, 0, $mm, $jj, $aa));
				$sched_time  = strtotime( get_gmt_from_date( $draft->sent ) . ' GMT');

				wp_schedule_single_event( $sched_time, 'mp_process_send_draft', array( $id ) );

				$old_sched = strtotime( get_gmt_from_date( date('Y-m-d H:i:s', mktime($_POST['hidden_hh'], $_POST['hidden_mn'], 0, $_POST['hidden_mm'], $_POST['hidden_jj'], $_POST['hidden_aa']))) . ' GMT');
			}
		}

// process attachements
		if (isset($_POST['type_of_upload']))
		{
			$files = array();
			if (isset($_POST['Files'])) foreach ($_POST['Files'] as $k => $v) if (is_numeric($k)) $files[] = $k;

			$attach = (empty($files)) ? '' : join(', ', $files);

			$file_exits = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id FROM $wpdb->mp_mailmeta WHERE mp_mail_id = %d AND meta_key = %s", $id, '_MailPress_attached_file') . ( (empty($attach)) ? ';' : " AND meta_id NOT IN ($attach);" ) );
			if ($file_exits) foreach($file_exits as $entry) MP_Mail_meta::delete_by_id( $entry->meta_id );
		}

// mail_format

		if (isset($_POST['mail_format']))
		{
			MP_Mail_meta::delete($id, '_MailPress_format');
			if (!empty($_POST['mail_format'])) MP_Mail_meta::add($id, '_MailPress_format', $_POST['mail_format'], true);
		}

// recipients
		if (isset($_POST['to_list']) && !empty($_POST['to_list']))
		{
			$_POST['toemail'] = $_POST['to_list'];
			$_POST['toname']  = '';
		}

// content
		if (isset($_POST['content'])) $_POST['html'] = $_POST['content'];
		unset($_POST['content']);


		$_POST = stripslashes_deep($_POST);


// from
		$fromemail = trim($_POST['fromemail']);
		$fromname  = trim($_POST['fromname']) ;
		if ($fromemail == $mp_general['fromemail'] && $fromname == $mp_general['fromname']) $fromemail = $fromname = '';

		$data = $format = $where = $where_format = array();

		$data['status'] 	= $status; 								$format[] = '%s';
		$data['theme'] 		= (isset($_POST['Theme'])) ? $_POST['Theme'] : '';	$format[] = '%s';
		$data['fromemail']	= $fromemail;		 						$format[] = '%s';
		$data['fromname'] 	= $fromname ; 								$format[] = '%s';
		$data['toemail'] 	= trim($_POST['toemail']); 					$format[] = '%s';
		$data['toname'] 	= trim($_POST['toname']) ; 					$format[] = '%s';
		$data['subject'] 	= trim($_POST['subject']);						$format[] = '%s';
		$data['html'] 		= trim($_POST['html']); 						$format[] = '%s';
		$data['plaintext'] 	= trim($_POST['plaintext'], " \r\n"); 			$format[] = '%s';
		$data['created'] 	= isset($_POST['created']) ? $_POST['created'] : current_time( 'mysql' ); $format[] = '%s';
		$data['created_user_id']= MP_WP_User::get_id(); 					$format[] = '%d';
		$data['sent'] 		= $draft->sent; 							$format[] = '%s';

		if ($scheduled)
			$data['sent_user_id']   = $data['created_user_id'];				$format[] = '%d';

		$where['id'] 		= $id;								$where_format[] = '%d';

		$wpdb->update( $wpdb->mp_mails, $data, $where, $format, $where_format );

		return ( $scheduled && $sched_time != $old_sched );
	}

	public static function reset_scheduled($id = NULL)
	{
		if (NULL == $id) return false;
		$id = (int) $id;

		wp_clear_scheduled_hook( 'mp_process_send_draft', array( $id ) );

		$data = $format = $where = $where_format = array();

		$data['sent']	= '0000-00-00 00:00:00';	$format[] = '%s';

		$where['id'] 	= $id;				$where_format[] = '%d';

		global $wpdb;
		$wpdb->update( $wpdb->mp_mails, $data, $where, $format, $where_format );
	}

	public static function send($id, $args = array()) 
	{
		$defaults = array(	'ajax'		=> 0,
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$id = (int) $id;

		self::reset_scheduled($id);

		$template = apply_filters('MailPress_draft_template', false, $id);

		$draft = MP_Mail::get($id);

		if ('draft' != $draft->status) return false;
		$mail 		= new stdClass();	/* so we duplicate the draft into a new mail */
		$mail->id 		= MP_Mail::get_id(__CLASS__ . ' ' . __METHOD__);
		$mail->main_id 	= $id;

		if (!empty($draft->theme)) $mail->Theme = $draft->theme;
		if (!empty($template))     $mail->Template = $template;

		if (!empty($draft->fromemail))
		{
			$mail->fromemail= $draft->fromemail;
			$mail->fromname	= $draft->fromname;
		}

		if (isset($toemail) && !empty($toemail))
		{
			$mail->toemail	= $toemail;
			$mail->toname	= (isset($toname)) ? $toname : '';
		}
		else
		{
			$query = self::get_query_mailinglist($draft->toemail);
			if ($query)
			{
				MP_Mail_meta::add($mail->id, '_mailinglist_id', $draft->toemail, true);
				$draft_dest = MP_User::get_mailinglists();
				MP_Mail_meta::add($mail->id, '_mailinglist_desc', $draft_dest[$draft->toemail], true);

				$mail->recipients_query = $query;
			}
			else
			{
				if 	(!is_email($draft->toemail)) return 'y';
				$mail->toemail	= $draft->toemail;
				$mail->toname	= $draft->toname;
			}
		}

		$mail->subject	= $draft->subject;
		$mail->html		= $draft->html;
		$mail->plaintext	= $draft->plaintext;

		$mail->wp_user_id	= $draft->created_user_id;

		$mail->draft 	= true;

		$count = MailPress::mail($mail);

		if (0 === $count)		return 'x'; // no recipient
		if (!$count) return 0;			// something wrong !

		if ($ajax) 	return array($mail->id);
		return $count;
	}

//// Recipients queries ////

	public static function get_query_mailinglist($draft_toemail)
	{
		switch ($draft_toemail)
		{
			case '1' :
           			global $wpdb;
				return "SELECT id, email, name, status, confkey FROM $wpdb->mp_users WHERE status = 'active';";
			break;
/* 2 & 3 used by comments */
			case '4' :
           			global $wpdb;
				return "SELECT id, email, name, status, confkey FROM $wpdb->mp_users WHERE status IN ('active', 'waiting');";
			break;
			case '5' :
           			global $wpdb;
				return "SELECT id, email, name, status, confkey FROM $wpdb->mp_users WHERE status IN ('waiting');";
			break;
			default :
				return apply_filters('MailPress_query_mailinglist', false, $draft_toemail);
			break;
		}
		return false;
	}
}