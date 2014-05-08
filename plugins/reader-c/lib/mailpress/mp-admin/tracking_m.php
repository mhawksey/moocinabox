<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= 'mailpress_tracking_m';
	const capability 	= 'MailPress_tracking_mails';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/tracking/';
	const file       	= __FILE__;

////  Title  ////

	public static function title() 
	{ 
		new MP_Tracking_metaboxes('mail');

		global $title; 
		$title = __('Tracking', MP_TXTDOM); 
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		$styles[] = 'dashboard';

		wp_register_style ( 'mp_mail', 	'/' . MP_PATH . 'mp-admin/css/mails.css', array('thickbox') );
		$styles[] = 'mp_mail';

		wp_register_style ( self::screen, 	'/' . MP_PATH . 'mp-admin/css/tracking_m.css' );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts($scripts = array())
	{
		$scripts = apply_filters('MailPress_autorefresh_js', $scripts);

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/tracking_t.js', array('mp-thickbox', 'postbox'), false, 1);
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
		$columns = array(	'title' 	=> __('Subject', MP_TXTDOM), 
					'author' 	=> __('Author'), 
					'theme' 	=> __('Theme', MP_TXTDOM), 
					'to' 		=> __('To', MP_TXTDOM), 
					'date'	=> __('Date') );
		$columns = apply_filters('MailPress_mails_columns', $columns);
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
		global $mp_mail;

		$mp_mail = $mail = MP_Mail::get( $id );
		$the_mail_status = $mail->status;

// url's
		$args = array();
		$args['id'] 	= $id;
		$args['action'] 	= 'iview';
		$args['preview_iframe'] = 1; $args['TB_iframe']= 'true';
		$view_url		= esc_url(self::url(MP_Action_url, $args));

// table row 
//	to
		$draft_dest = MP_User::get_mailinglists();

		switch (true)
		{
			case ($xtra) :
				$email_display = "<blink style='color:red;font-weight:bold;'>" . $xtra . '</blink>';
			break;
			case (is_email($mail->toemail)) :
				$mail_url = self::url(MailPress_mails, $url_parms);
				$mail_url = remove_query_arg('s', $mail_url);
				$mail_url = esc_url( $mail_url . '&s=' . $mail->toemail );

				$email_display = '';

				if ( get_option('show_avatars') ) 
				{
					$email_display .= "<div style='float:left;margin-right:10px;'>";
					$email_display .= get_avatar( $mail->toemail, 32 );
					$email_display .= '</div>';
				}
				$email_display .= "<div style='float:left;'>";
				$email_display .= '<strong>';
				$email_display .= ( strlen($mail->toemail) > 40 ) ? substr($mail->toemail, 0, 39) . '...' : $mail->toemail;
				$email_display .= '</strong>';
				if (!empty($mail->toname)) $email_display .= '<br />' . $mail->toname;
				$email_display .= '</div>';
			break;
			case (isset($draft_dest[$mail->toemail])) :
				$email_display = "<strong>" . $draft_dest[$mail->toemail] . "</strong>";
			break;
			case (is_serialized($mail->toemail)) :
				$email_display = "<div class='num post-com-count-wrapper'><a class='post-com-count'><span class='comment-count'>" . count(unserialize($mail->toemail)) . "</span></a></div>"; 
			break;
			default  :
				$email_display = "<span style='color:red;font-weight:bold;'>" . __('(unknown)', MP_TXTDOM) . '</span>';
				unset($actions['send']);
			break;
		}
		$email_display = apply_filters('MailPress_to_mails_column', $email_display, $mail);
		if ($mailinglist_desc = MP_Mail_meta::get($mail->id, '_mailinglist_desc')) $email_display = "<div>{$email_display}</div>{$mailinglist_desc}";
//	author
		$author = ( 0 == $mail->sent_user_id) ? $mail->created_user_id : $mail->sent_user_id;
		if ($author != 0 && is_numeric($author)) 
		{
			unset($url_parms['author']);
			$wp_user 		= get_userdata($author);
		}
//	subject
		$metas = MP_Mail_meta::get( $id, '_MailPress_replacements');
		$subject_display = $mail->subject;
		if ($metas) foreach($metas as $k => $v) $subject_display = str_replace($k, $v, $subject_display);
//	attachements
		$attach = false;
		$metas = MP_Mail_meta::has( $id, '_MailPress_attached_file');
		if ($metas)
		{
			foreach($metas as $meta)
			{
				$meta_value = unserialize( $meta['meta_value'] );
				if (is_file($meta_value['file_fullpath']))
				{
					$attach = true;
					break;
				}
			}
		}
?>
	<tr id="mail-<?php echo $id; ?>">
<?php
		$columns = self::get_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";
			$style = '';
			if ('unsent' == $mail->status) 		$style .= 'font-style:italic;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			switch ($column_name) 
			{
				case 'title':
					$attributes = 'class="post-title column-title"' . $style;
?>
		<td  <?php echo $attributes ?>>
<?php
			if ('paused' == $mail->status) :
?>
			<span class='icon paused' title="<?php _e('Paused', MP_TXTDOM); ?>"></span>
<?php
			endif;
			if ($attach) :
?>
			<span class='icon attachement' title="<?php _e('Attachments', MP_TXTDOM); ?>"></span>
<?php
			endif;

			do_action('MailPress_get_icon_mails', $id);
?>
			<strong>
				<a class='row-title thickbox thickbox-preview' href='<?php echo $view_url; ?>' title='<?php printf( __('View "%1$s"', MP_TXTDOM) , ( '' == $subject_display) ? __('(no subject)', MP_TXTDOM) : htmlspecialchars($subject_display, ENT_QUOTES) ); ?>'>
					<?php echo ( '' == $subject_display) ? __('(no subject)', MP_TXTDOM) : (( strlen($subject_display) > 40 ) ? $subject_display = mb_substr($subject_display, 0, 39, get_option('blog_charset')) . '...' : $subject_display); ?>
				</a>
<?php if ('paused' == $mail->status) echo ' - ' . __('Paused', MP_TXTDOM); ?>
<?php if ('archived' == $mail->status) echo ' - ' . __('Archive', MP_TXTDOM); ?>
			</strong>
		</td>
<?php
				break;
				case 'author':
?>
		<td  <?php echo $attributes ?>>
<?php					if ($author != 0 && is_numeric($author)) { ?>
			<?php echo $wp_user->display_name; ?>
<?php 				} else _e("(unknown)", MP_TXTDOM); ?>
		</td>
<?php
				break;
				case 'theme':
?>
		<td  <?php echo $attributes ?>>
			<?php echo $mail->theme; ?>
			<?php if ('' != $mail->template) echo "<br />(" . $mail->template . ")"; ?>
		</td>
<?php
				break;
				case 'to':
?>
		<td  <?php echo $attributes ?>>
<?php echo $email_display; ?>
		</td>
<?php
				break;
				case 'date':

					$t_time = self::get_mail_date(__('Y/m/d H:i:s'));
					$h_time = self::human_time_diff(self::get_mail_date_raw());
?>
		<td  <?php echo $attributes ?>>
			<abbr title="<?php echo $t_time; ?>"><?php echo $h_time; ?></abbr>
		</td>
<?php
				break;
				default:
?>
		<td  <?php echo $attributes ?>>
			<?php	do_action('MailPress_mails_get_row', $column_name, $mail, array()); ?>
		</td>
<?php
				break;
			}
		}
?>
	  </tr>
<?php
	}

	public static function mail_date($d = '') {
		echo  self::get_mail_date($d);
	}

	public static function get_mail_date($d = '' ) {
		$x = self::get_mail_date_raw();
		return ( '' == $d ) ? mysql2date( get_option('date_format'), $x) : mysql2date($d, $x);
	}

	public static function get_mail_date_raw() {
		global $mp_mail;
		$x = ($mp_mail->sent >= $mp_mail->created) ? $mp_mail->sent : $mp_mail->created;
		return $x;
	}
}