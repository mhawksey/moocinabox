<?php
class MP_AdminPage extends MP_adminpage_
{
	const screen 	= MailPress_page_write;
	const capability= 'MailPress_edit_mails';
	const help_url	= 'http://blog.mailpress.org/tutorials/';
	const file		= __FILE__;

////  Redirect  ////

	public static function redirect() 
	{
		if (!empty($_REQUEST['action'])) $action = $_REQUEST['action'];
		if (!isset($action)) return;

		if (isset($_GET['id'])) $id = $_GET['id'];

		$list_url = self::url(MailPress_mails, self::get_url_parms());

		switch($action) 
		{
			case 'pause' :
				if (MP_Mail::set_status($id, 'paused'))	$list_url .= '&paused=1';
				self::mp_redirect($list_url);
			break;
			case 'restart' :
				if (MP_Mail::set_status($id, 'unsent'))	$list_url .= '&restartd=1';
				self::mp_redirect($list_url);
			break;
			case 'archive' :
				if (MP_Mail::set_status($id, 'archived'))	$list_url .= '&archived=1';
				self::mp_redirect($list_url);
			break;
			case 'unarchive' :
				if (MP_Mail::set_status($id, 'sent'))		$list_url .= '&unarchived=1';
				self::mp_redirect($list_url);
			break;
			case 'send' :
				if ('draft' != MP_Mail::get_status($id)) break;
				$x = MP_Mail_draft::send($id);
				$list_url .= (is_numeric($x))	? '&sent=' . $x : '&notsent=1';
				self::mp_redirect($list_url);
			break;
			case 'delete' :
				if (MP_Mail::set_status($id, 'delete'))		$list_url .= '&deleted=1';
				self::mp_redirect($list_url);
			break;

			case 'draft' :
				$id = (0 == $_POST['id']) ? MP_Mail::get_id(__CLASS__ . ' ' . __METHOD__ . ' ' . self::screen) : (int) $_POST['id'];

				switch (true)
				{
				// process attachements
					case isset($_POST['addmeta']) :
						MP_Mail_meta::add_meta($id);
						$parm = "&cfsaved=1";
					break;
					case isset($_POST['updatemailmeta']) :
						$cfsaved = 0;
						foreach ($_POST['meta'] as $meta_id => $meta)
						{
							$meta_key = $meta['key'];
							$meta_value = $meta['value'];
							MP_Mail_meta::update_by_id($meta_id , $meta_key, $meta_value);
							$cfsaved++;
						}
						$parm = "&cfsaved=$cfsaved";
					break;
					case isset($_POST['deletemailmeta']) :
						$cfdeleted = 0;
						foreach ($_POST['deletemailmeta'] as $meta_id => $x)
						{
							MP_Mail_meta::delete_by_id( $meta_id );
							$cfdeleted++;
						}
						$parm = "&cfdeleted=$cfdeleted";
					break;
				// process mail
					default :
						$id = (0 == $_POST['id']) ? MP_Mail::get_id(__CLASS__ . ' ' . __METHOD__ . ' ' . self::screen) : $_POST['id'];

						$scheduled = MP_Mail_draft::update($id);

					// what else ?
						do_action('MailPress_update_meta_boxes_write');
						$parm = ($scheduled) ? "&sched=1" : "&saved=1";

						if (!$scheduled && isset($_POST['send']))
						{
							wp_cache_delete($id, 'mp_mail');
							$x = MP_Mail_draft::send($id);
							if (is_numeric($x))
								if (0 == $x)	$parm = "&sent=0";
								else			$parm = "&sent=$x";
							else				$parm = "&nodest=0";
						}
					break;
				}
				$url = (strstr($_SERVER['HTTP_REFERER'], MailPress_edit)) ? MailPress_edit : MailPress_write;
				$url .= "$parm&id=$id";
				self::mp_redirect($url);
			break;
		}
	}

////  Title  ////

	public static function title() 
	{ 
		global $title; 
		$title = (isset($_GET['file']) && ('write' == $_GET['file'])) ? __('Edit Mail', MP_TXTDOM) : __('Add New Mail', MP_TXTDOM);

		add_filter('tiny_mce_before_init', array(__CLASS__, 'tiny_mce_before_init'));
	}

	public static function tiny_mce_before_init($initArray)
	{
		$x = array(	'theme_advanced_buttons1'	=> 'fullscreen',
					'plugins'				=> 'wpfullscreen',
		);

		foreach($x as $k => $v) if (isset($initArray[$k])) $initArray[$k] = str_replace( array($v, ',,') , array('', ',') , $initArray[$k] );
		return $initArray;
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, 		'/' . MP_PATH . 'mp-admin/css/write.css', 	array('thickbox') );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts($scripts, $is_footer) 
	{
		wp_register_script( 'mp-ajax-response', 	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 'wpAjax', array( 
			'noPerm' => __('Email was not sent AND/OR Update database failed', MP_TXTDOM), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		) );

		wp_register_script( 'mp-autosave', 		'/' . MP_PATH . 'mp-includes/js/autosave.js', array('schedule', 'mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-autosave', 'autosaveL10n', array	( 	
			'autosaveInterval'=> '60', 
			'previewMailText'	=>  __('Preview'), 
			'requestFile' 	=> MP_Action_url, 
			'savingText'	=> __('Saving draft...', MP_TXTDOM)
		) );

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 'wpListL10n', array( 
			'url' => MP_Action_url
		) );

		wp_register_script( 'mp_swf_upload', 	'/' . MP_PATH . 'mp-includes/js/fileupload/swf.js', array('swfupload'), false, 1);
	// these error messages came from the sample swfupload js, they might need changing.
		wp_localize_script( 'mp_swf_upload', 'swfuploadL10n', array(
			'queue_limit_exceeded' 		=> __('You have attempted to queue too many files.'), 
			'file_exceeds_size_limit' 	=> sprintf(__('This file is too big. Your php.ini upload_max_filesize is %s.'), @ini_get('upload_max_filesize')), 
			'zero_byte_file' 			=> __('This file is empty. Please try another.'), 
			'invalid_filetype' 		=> __('This file type is not allowed. Please try another.'), 
			'default_error' 			=> __('An error occurred in the upload. Please try again later.'), 
			'missing_upload_url' 		=> __('There was a configuration error. Please contact the server administrator.'), 
			'upload_limit_exceeded' 	=> __('You may only upload 1 file.'), 
			'http_error' 			=> __('HTTP error.'), 
			'upload_failed' 			=> __('Upload failed.'), 
			'io_error' 				=> __('IO error.'), 
			'security_error' 			=> __('Security error.'), 
			'file_cancelled' 			=> __('File cancelled.'), 
			'upload_stopped' 			=> __('Upload stopped.'), 
			'dismiss' 				=> __('Dismiss'), 
			'crunching' 			=> __('Crunching&hellip;'), 
			'deleted' 				=> __('Deleted'), 
			'l10n_print_after' 		=> 'try{convertEntities(swfuploadL10n);}catch(e){};'
		) );

		wp_register_script( 'mp_html_sifiles', 	'/' . MP_PATH . 'mp-includes/js/fileupload/si.files.js', array(), false, 1);
		wp_register_script( 'mp_html_upload', 	'/' . MP_PATH . 'mp-includes/js/fileupload/htm.js', array('mp_html_sifiles'), false, 1);
		wp_localize_script( 'mp_html_upload', 'htmuploadL10n', array(
			'img' => 'images/wpspin_light.gif',
			'iframeurl' 	=> MP_Action_url, 
			'uploading' 	=> __('Uploading ...', MP_TXTDOM), 
			'attachfirst' 	=> __('Attach a file', MP_TXTDOM), 
			'attachseveral' 	=> __('Attach another file', MP_TXTDOM), 
			'l10n_print_after'=> 'try{convertEntities(htmuploadL10n);}catch(e){};' 
		) );

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);

		$deps = array('quicktags', 'mp-autosave', 'mp-lists', 'postbox');
		if ( user_can_richedit() )	$deps[] = 'editor';
		$deps[] = 'thickbox';
		$deps[] = 'mp-thickbox';
		$deps[] = (self::flash()) ? 'mp_swf_upload' : 'mp_html_upload';

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/write.js', $deps, false, 1);
		wp_localize_script( self::screen, 'MP_AdminPageL10n', array( 	
			'errmess' 		=> __('Enter a valid email !', MP_TXTDOM), 
			'screen' 		=> self::screen, 

			'sendImmediately'	=> __('Send <b>immediately</b>', MP_TXTDOM),
			'sendOnFuture' 	=> __('Schedule for:'),

			'name_send' 	=> 'send',
			'schedule' 		=> __('Schedule'),
			'send' 		=> __('Send',  MP_TXTDOM),

			'name_save' 	=> 'save',
			'save' 		=> __('Save',  MP_TXTDOM),
			'update' 		=> __('Update',  MP_TXTDOM),

			'html2txt'		=> __("You are about to replace the content of plaintext area.\n 'Cancel' to stop, 'OK' to replace.",  MP_TXTDOM),

			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		) );

		$scripts[] = self::screen;
		parent::print_scripts($scripts);

		if (!$is_footer) return;

		global $hook_suffix;
		if (self::flash()) add_action("admin_footer-$hook_suffix", array('MP_AdminPage', 'swfupload'));
	}

	public static function flash() 
	{
		// If Mac and mod_security, no Flash. :(
		$flash = (isset($_GET['flash'])) ? $_GET['flash'] : true;
		$flash = ( false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac') && apache_mod_loaded('mod_security') ) ? false : $flash;
		return $flash;
	}

	// swfupload
	public static function swfupload() 
	{
		$m = array('mp_swfupload' => array(
				'flash_url' 			=> includes_url('js/swfupload/swfupload.swf'), 

				'button_text' 			=> esc_js(__('Attach a file', MP_TXTDOM)), 
				'button_text_style' 		=> '.mp_button { text-align: left; color: #21759B; text-decoration: underline; font-family:Verdana, Arial, "Bitstream Vera Sans", sans-serif; } .mp_button:hover {cursor:pointer;}', 
				'another_button_text'		=> "<span class='mp_button'>" .  esc_js(__('Attach another file', MP_TXTDOM)) . "</span>", 


				'button_height'			=> '24', 
				'button_width'			=> '132', 
				'button_image_url'		=> site_url() . '/' . MP_PATH . 'mp-includes/images/upload.png', 
				'button_placeholder_id'		=> 'flash-browse-button', 

				'upload_url' 			=> MP_Action_url, 

				'file_post_name'			=> 'async-upload', 
				'file_types'			=> '*.*', 
				'file_size_limit'			=> wp_max_upload_size() . 'b', 

				'post_params'			=> array (
										'action'		=> 'swfu_mail_attachement', 
										'auth_cookie'	=> (is_ssl()) ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE], 
										'_wpnonce'		=> wp_create_nonce('mp_attachement')
									), 

				'custom_settings'			=> array (
										'degraded_element_id' => 'html-upload-ui', // id of the element displayed when swfupload is unavailable
										'swfupload_element_id'=> 'flash-upload-ui' // id of the element displayed when swfupload is available
									), 

				'debug'				=> false
			));
		echo "<script type='text/javascript'>\n/* <![CDATA[ */\n";
		$eol = "";
		foreach ( $m as $var => $val ) {
			echo "var $var = " . self::print_scripts_l10n_val($val);
			$eol = ", \n\t\t";
		}
		echo ";\n";
		echo "/* ]]> */\n</script>";
	}

////  Metaboxes  ////

	public static function screen_meta() 
	{
		$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
		add_meta_box('submitdiv', 		__('Send', MP_TXTDOM), 			array('MP_AdminPage', 'meta_box_submit'), 		self::screen, 'side', 'core');
		add_meta_box('attachementsdiv', 	__('Attachments', MP_TXTDOM),		array('MP_AdminPage', 'meta_box_attachements'), 	self::screen, 'side', 'core');

		if ( current_user_can('MailPress_mail_custom_fields') )
			add_meta_box('customfieldsdiv', 	__('Custom Fields'), 		array('MP_AdminPage', 'meta_box_customfields'), 	self::screen, 'normal', 'core');

		if ($id)
		{
			$rev_ids 	= MP_Mail_meta::get($id, '_MailPress_mail_revisions');
		}
		if (isset($rev_ids) && $rev_ids)
			add_meta_box('revisionbox', 	__('Mail Revisions', MP_TXTDOM), 	array('MP_AdminPage', 'meta_box_revision'), 		self::screen, 'normal', 'high');

		do_action('MailPress_add_meta_boxes_write', $id, self::screen);

		parent::screen_meta();
	}
/**/
	public static function meta_box_submit($draft) 
	{
   		$datef = __( 'M j, Y @ G:i' );

		global $mp_general;

		$fromname = $draft->fromname;
		$fromemail= $draft->fromemail;
		$from = "<b>{$fromname}</b> &lt;{$fromemail}&gt;";

		$stamp = __('Send <b>immediately</b>', MP_TXTDOM);
		$date = date_i18n( $datef, strtotime( current_time('mysql') ) );

		$save_post_class = '';
		$publish_name    = 'send';
		$publish_value   = esc_attr(__('Send', MP_TXTDOM));

		if ($draft && isset($draft->id))
		{
			if (current_user_can('MailPress_delete_mails')) $delete_url = esc_url(MailPress_write  ."&amp;action=delete&amp;id=$draft->id");
			$preview_url= esc_url(add_query_arg( array('action' => 'iview', 'id' => $draft->id, 'preview_iframe' => 1, 'TB_iframe' => 'true'), MP_Action_url ));
			$preview	= "<a class='preview button' target='_blank' href='$preview_url'>" . __('Preview') . "</a>";

	            if ($draft->_scheduled)
	            {
	    			$stamp = __('Scheduled for: <b>%1$s</b>');
	    			$date = date_i18n( $datef, strtotime( $draft->sent ) );

				$save_post_class = ' hidden';
				$publish_name    = 'save';
				$publish_value   = esc_attr(__('Update', MP_TXTDOM));
	            }
		} 

		$publish = (current_user_can('MailPress_send_mails')) ? "<input id='publish' type='submit' name='$publish_name' class='button-primary' value=\"$publish_value\" />" : '';
?>
<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
		<div style='display:none'></div>
		<div id="minor-publishing-actions">
			<div id='save-action'>
				<input id='save-post' type='submit' name='save' class='button button-highlighted<?php echo $save_post_class; ?>' value="<?php _e('Save Draft', MP_TXTDOM); ?>"  />
			</div>
			<div id='preview-action'>
				<span id='preview-button'><?php if (isset($preview)) echo $preview; ?></span>
			</div>
			<div class="clear"></div>
		</div>

		<div id="misc-publishing-actions">
			<div class="misc-pub-section mp_theme">
				<label><?php _e('From: ', MP_TXTDOM); ?></label>
				<b><span id='span_from'><?php echo $from; ?></span></b>
<?php 
		if (current_user_can('MailPress_write_edit_fromemail'))
		{
?>
				<a href='#edit_from' class="edit-from hide-if-no-js"><?php _e('Edit') ?></a>
				<div id='fromdiv' class='hide-if-js'>
					<input id='hidden_fromname'  name='hidden_fromname'  type='hidden' value="<?php echo esc_attr($fromname); ?>" />
					<input id='hidden_fromemail' name='hidden_fromemail' type='hidden' value='<?php echo $fromemail; ?>' />
					<?php _e('Name: ', MP_TXTDOM); ?><input type='text' size='25' id='fromname'  name='fromname'  value="<?php echo esc_attr($fromname);  ?>" /><br />
					<?php _e('Email: ', MP_TXTDOM); ?><input type='text' size='25' id='fromemail' name='fromemail' value="<?php echo $fromemail; ?>" /><br />
					<a href="#edit_from" class="save-from hide-if-no-js button"><?php _e('OK'); ?></a>
					<a href="#edit_from" class="cancel-from hide-if-no-js"><?php _e('Cancel'); ?></a>
				</div>
<?php
		}
		else
		{
?>
					<input id='fromname'  name='fromname'  type='hidden' value="<?php echo esc_attr($fromname); ?>" />
					<input id='fromemail' name='fromemail' type='hidden' value='<?php echo $fromemail; ?>' />
<?php
		}
?>
			</div>
<?php
		$xthemes = array();
		$th = new MP_Themes();
		$themes = $th->themes;

		foreach($themes as $key => $theme)
		{
			if ( 'plaintext' == $theme['Stylesheet']) unset($themes[$key]);
			if ( '_' == $theme['Stylesheet'][0] )     unset($themes[$key]);
		}

		$xthemes = array('' => __('current', MP_TXTDOM));
		foreach ($themes as $theme) $xthemes[$theme['Stylesheet']] = $theme['Stylesheet'];

		$current_theme = $themes[$th->current_theme]['Stylesheet'];
		$theme = (isset($draft->theme)) ? $draft->theme : '';
?>
			<div class="misc-pub-section mp_theme">
				<label><?php _e('Theme: ', MP_TXTDOM); ?></label>
				<b><span id='span_theme'><?php echo $xthemes[$theme]; ?></span></b>
				<a href='#edit_theme' class="edit-theme hide-if-no-js"><?php _e('Edit') ?></a>
				<div id='themediv' class='hide-if-js'>
					<input id='hidden_theme' name='hidden_theme' type='hidden' value='<?php echo $theme; ?>' />
					<select id='theme' name='Theme'>
<?php self::select_option($xthemes, $theme);?>
					</select>
					<a href="#edit_theme" class="save-theme hide-if-no-js button"><?php _e('OK'); ?></a>
					<a href="#edit_theme" class="cancel-theme hide-if-no-js"><?php _e('Cancel'); ?></a>
				</div>
			</div>

			<div class="misc-pub-section curtime misc-pub-section-last">
				<span id="timestamp"><?php printf($stamp, $date); ?></span>
				<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" tabindex='4'><?php _e('Edit') ?></a>
				<div id="timestampdiv" class="hide-if-js"><?php self::touch_time(4); ?></div>
			</div>
		</div>

		<div class="clear"><br /><br /></div>
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
<?php 	if (isset($delete_url)) : ?>
			<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="if (confirm('<?php echo(esc_js(sprintf( __("You are about to delete this draft '%s'\n  'Cancel' to stop, 'OK' to delete."), $draft->id ))); ?>')) return true; return false;">
				<?php _e('Delete', MP_TXTDOM); ?>
			</a>
<?php		endif; ?>
		</div>
		<div id="publishing-action">
			<?php echo $publish; ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
	}

	public static function touch_time( $tab_index = 0, $edit = 1, $multi = 0 ) 
	{
		global $wp_locale, $draft;

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 ) $tab_index_attribute = " tabindex=\"$tab_index\"";

		$time_adj = current_time('timestamp');

		$draft_date = ($draft->_scheduled) ? $draft->sent : date_i18n('Y-m-d H:i');

		$jj = ($edit) ? mysql2date( 'd', $draft_date, false ) : gmdate( 'd', $time_adj );
		$mm = ($edit) ? mysql2date( 'm', $draft_date, false ) : gmdate( 'm', $time_adj );
		$aa = ($edit) ? mysql2date( 'Y', $draft_date, false ) : gmdate( 'Y', $time_adj );
		$hh = ($edit) ? mysql2date( 'H', $draft_date, false ) : gmdate( 'H', $time_adj );
		$mn = ($edit) ? mysql2date( 'i', $draft_date, false ) : gmdate( 'i', $time_adj );
		$ss = ($edit) ? mysql2date( 's', $draft_date, false ) : gmdate( 's', $time_adj );

		$cur_jj = gmdate( 'd', $time_adj );
		$cur_mm = gmdate( 'm', $time_adj );
		$cur_aa = gmdate( 'Y', $time_adj );
		$cur_hh = gmdate( 'H', $time_adj );
		$cur_mn = gmdate( 'i', $time_adj );

		$month = "<select id='mm' name='mm' $tab_index_attribute >\n";
		for ( $i = 1; $i < 13; $i = $i +1 ) $month .= "<option value='" . zeroise($i, 2) . (( $i == $mm ) ? "' selected='selected'" : "'") . '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
		$month .= '</select>';

		$day    = "<input type='text' id='jj' name='jj' value='$jj' size='2' maxlength='2' $tab_index_attribute autocomplete='off' />";
		$year   = "<input type='text' id='aa' name='aa' value='$aa' size='4' maxlength='4' $tab_index_attribute autocomplete='off' />";
		$hour   = "<input type='text' id='hh' name='hh' value='$hh' size='2' maxlength='2' $tab_index_attribute autocomplete='off' />";
		$minute = "<input type='text' id='mn' name='mn' value='$mn' size='2' maxlength='2' $tab_index_attribute autocomplete='off' />";

		echo "<div class='timestamp-wrap'>";	/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
		printf(__('%1$s%2$s, %3$s @ %4$s : %5$s'), $month, $day, $year, $hour, $minute);

		echo "</div><input type='hidden' id='ss' name='ss' value='$ss' />";

		echo "\n\n";
		foreach ( array('mm', 'jj', 'aa', 'hh', 'mn') as $timeunit ) 
		{
			echo "<input type='hidden' id='hidden_$timeunit' name='hidden_$timeunit' value='" . $$timeunit . "'    />\n";
   			$cur_timeunit = 'cur_' . $timeunit;
			echo "<input type='hidden' id='cur_$timeunit'    name='cur_$timeunit'    value='" . $$cur_timeunit . "' />\n";
		}
?>
<p>
	<a href="#edit_timestamp" class="save-timestamp hide-if-no-js button"><?php _e('OK'); ?></a>
	<a href="#edit_timestamp" class="cancel-timestamp hide-if-no-js"><?php _e('Cancel'); ?></a>
</p>
<?php
}


/**/
	public static function meta_box_plaintext($draft)
	{
?>
<textarea id='plaintext' name='plaintext' cols='40' rows='1'><?php echo (isset($draft->plaintext)) ? str_replace('&', '&amp;', $draft->plaintext) : ''; ?></textarea>
<div id='div_html2txt' class='hidden'>
	<a id='html2txt' class='button hide-if-no-js' onclick="return false;" title="<?php echo esc_attr(__('Plaintext from Html', MP_TXTDOM)); ?>" href="#">
		<span class="mp-media-buttons-icon"></span>
		<?php _e('Synchronize', MP_TXTDOM); ?> 
	</a>
</div>
<?php
	}
/**/
	public static function meta_box_revision($draft)
	{
		MP_Mail_revision::listing($draft->id);
	}
/**/
	public static function meta_box_attachements($draft) 
	{
		if ($draft) $draft_id = (isset($draft->id)) ? $draft->id : 0;
		if (self::flash()) 
		{
			$divid = 'flash-upload-ui';
			$divs  = "<div><div id='flash-browse-button'></div></div>";
			$url   = esc_url(add_query_arg('flash', 0));
			$txt   = __('homemade uploader', MP_TXTDOM);
		}
		else
		{
			$divid = 'html-upload-ui';
			$divs  = "<div class='mp_fileupload_txt'><span class='mp_fileupload_txt'></span></div><div class='mp_fileupload_file' id='mp_fileupload_file_div'></div>";
			$url   = esc_url(remove_query_arg('flash'));
			$txt   = __('Flash uploader', MP_TXTDOM);
		}
?>
<script type="text/javascript">
<!--
var draft_id = <?php echo $draft_id; ?>;
//-->
</script>
<div id="attachement-items">
<?php 	self::get_attachements_list($draft_id); ?>
</div>
<div><span id='attachement-errors'></span></div>

<div id='<?php echo $divid; ?>'><?php echo $divs; ?>
	<br class="clear" />
	<p>
		<input type='hidden' name='type_of_upload' value="<?php echo $divid; ?>" />
		<?php printf(__('Problems?  Try the %s.', MP_TXTDOM), sprintf ("<a id='mp_loader_link' href='%1s'>%2s</a>", $url , $txt )); ?>
	</p>
</div>
<?php
	}

	public static function get_attachements_list($draft_id)
	{
		$metas = MP_Mail_meta::has( $draft_id, '_MailPress_attached_file');
		if ($metas) foreach($metas as $meta) self::get_attachement_row($meta);
	}

	public static function get_attachement_row($meta)
	{
		$meta_value = maybe_unserialize( $meta['meta_value'] );
		if (!is_file($meta_value['file_fullpath'])) return;
		$href = esc_url(add_query_arg( array('action' => 'attach_download', 'id' => $meta['meta_id']), MP_Action_url ));

?>
	<div id='attachement-item-<?php echo $meta['meta_id']; ?>' class='attachement-item child-of-<?php echo $meta['mp_mail_id']; ?>'>
		<table>
			<tr>
				<td>
					<input type='checkbox' class='mp_fileupload_cb' checked='checked' name='Files[<?php echo $meta['meta_id']; ?>]' value='<?php echo $meta['meta_id']; ?>' />
				</td>
				<td>&#160;<a href='<?php echo $href; ?>' style='text-decoration:none;'><?php echo $meta_value['name']; ?></a></td>
			</tr>
		</table>
	</div>

<?php
	}
/**/
	public static function meta_box_customfields($draft)
	{
?>
<div id='postcustomstuff'>
	<div id='ajax-response'></div>
<?php
        $id = (isset($draft->id)) ? $draft->id : '';
		$metadata = MP_Mail_meta::has($id);
		$count = 0;
		if ( !$metadata ) : $metadata = array(); 
?>
	<table id='list-table' style='display: none;'>
		<thead>
			<tr>
				<th class='left'><?php _e( 'Name' ); ?></th>
				<th><?php _e( 'Value' ); ?></th>
			</tr>
		</thead>
		<tbody id='the-list' class='list:mailmeta'>
			<tr><td></td><td></td></tr>
		</tbody>
	</table>
<?php else : ?>
	<table id='list-table'>
		<thead>
			<tr>
				<th class='left'><?php _e( 'Name' ) ?></th>
				<th><?php _e( 'Value' ) ?></th>
			</tr>
		</thead>
		<tbody id='the-list' class='list:mailmeta'>
<?php foreach ( $metadata as $entry ) echo self::meta_box_customfield_row( $entry, $count ); ?>
		</tbody>
	</table>
<?php endif; ?>
<?php
		global $wpdb;
		$keys = $wpdb->get_col( "SELECT meta_key FROM $wpdb->mp_mailmeta GROUP BY meta_key ORDER BY meta_key ASC LIMIT 30" );
		foreach ($keys as $k => $v)
		{
			if ($keys[$k][0] == '_') unset($keys[$k]);
			if ('batch_send' == $v)  unset($keys[$k]);
		}
?>
	<p>
		<strong>
			<?php _e( 'Add New Custom Field:' ) ?>
		</strong>
	</p>
	<table id='newmeta'>
		<thead>
			<tr>
				<th class='left'>
					<label>
						<?php _e( 'Name' ) ?>
					</label>
				</th>
				<th>
					<label>
						<?php _e( 'Value' ) ?>
					</label>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id='newmetaleft' class='left'>
<?php 
		if ( $keys ) 
		{ 
?>
					<select id='metakeyselect' name='metakeyselect' tabindex='7'>
						<option value="#NONE#"><?php _e( '- Select -' ); ?></option>
<?php
			foreach ( $keys as $key ) 
			{
				$key = esc_attr($key);
				echo "\n<option value=\"$key\">$key</option>";
			}
?>
					</select>
					<input class='hide-if-js' type='text' id='metakeyinput' name='metakeyinput' tabindex='7' value='' />
					<a href='#postcustomstuff' class='hide-if-no-js' onclick="jQuery('#metakeyinput, #metakeyselect, #enternew, #cancelnew').toggle();return false;">
					<span id='enternew'><?php _e('Enter new'); ?></span>
					<span id='cancelnew' class='hidden'><?php _e('Cancel'); ?></span></a>
<?php 
		} 
		else 
		{ 
?>
					<input type='text' id='metakeyinput' name='metakeyinput' tabindex='7' value='' />
<?php 
		} 
?>
				</td>
				<td>
					<textarea id='metavalue' name='metavalue' rows='2' cols='25' tabindex='8'></textarea>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<div class='submit'>
						<input type='submit' id='addmetasub' name='addmailmeta' class='add:the-list:newmeta button' tabindex='9' value="<?php _e( 'Add Custom Field' ) ?>" />
						<?php wp_nonce_field( 'add-mailmeta', '_ajax_nonce', false ); ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<p><?php _e('Custom fields can be used to add extra metadata to a mail that you can <a href="http://www.mailpress.org" target="_blank">use in your mail</a>.', MP_TXTDOM); ?></p>
<?php
	}

	public static function meta_box_customfield_row( $entry, &$count )
	{
		if ('_' == $entry['meta_key'] { 0 } ) return;

		static $update_nonce = false;
		if ( !$update_nonce ) $update_nonce = wp_create_nonce( 'add-mailmeta' );

		$r = '';
		++ $count;

		if ( $count % 2 )	$style = 'alternate';
		else			$style = '';
	
		$entry['meta_key'] 	= esc_attr($entry['meta_key']);
		$entry['meta_value'] 	= esc_attr($entry['meta_value']); // using a <textarea />
		$entry['meta_id'] 	= (int) $entry['meta_id'];

		$delete_nonce 		= wp_create_nonce( 'delete-mailmeta_' . $entry['meta_id'] );

		$r .= "
			<tr id='mailmeta-{$entry['meta_id']}' class='$style'>
				<td class='left'>
					<label class='hidden' for='mailmeta[{$entry['meta_id']}][key]'>
" . __( 'Key' ) . "
					</label>
					<input name='mailmeta[{$entry['meta_id']}][key]' id='mailmeta[{$entry['meta_id']}][key]' tabindex='6' type='text' size='20' value=\"" . esc_attr($entry['meta_key']) . "\" />
					<div class='submit'>
						<input name='deletemailmeta[{$entry['meta_id']}]' type='submit' class='delete:the-list:mailmeta-{$entry['meta_id']}::_ajax_nonce=$delete_nonce deletemailmeta button' tabindex='6' value='" . esc_attr(__( 'Delete' )) . "' />
						<input name='updatemailmeta' type='submit' tabindex='6' value='" . esc_attr(__( 'Update' )) . "' class='add:the-list:mailmeta-{$entry['meta_id']}::_ajax_nonce=$update_nonce updatemailmeta button' />
					</div>
" . wp_nonce_field( 'change-mailmeta', '_ajax_nonce', false, false ) . "
				</td>
				<td>
					<label class='hidden' for='mailmeta[{$entry['meta_id']}][value]'>
" . __( 'Value' ) . "
					</label>
					<textarea name='mailmeta[{$entry['meta_id']}][value]' id='mailmeta[{$entry['meta_id']}][value]' tabindex='6' rows='2' cols='30'>" . esc_attr($entry['meta_value']) . "</textarea>
				</td>
			</tr>
			";
	return $r;
	}

	public static function select_optgroup($list, $selected, $echo = true)
	{
		foreach( $list as $value => $label )
		{
			$_selected = (!is_array($selected)) ? $selected : ( (in_array($value, $selected)) ? $value : null );
			$list[$value] = "<option " . self::selected( (string) $value, (string) $_selected, false, false ) . " value=\"" . esc_attr($value) . "\">$label</option>";
		}

		$opened = false;

		foreach( $list as $value => $html )
		{
			if (empty($value)) continue;
			switch (true)
			{
				case (in_array($value , array('2', '3'))) :
					$optgroup = 'MailPress_comment';
				break;
				case (is_numeric($value)) :
					$optgroup = 'MP_User';
				break;
				default :
					$optgroup = ($pos = strpos($value, '~')) ? substr($value, 0, $pos) : null;
				break;
			}
			if (isset($$optgroup)) continue;
			$list[$value] = (($opened) ? '</optgroup>' : '') . '<optgroup label=\'' . apply_filters('MailPress_mailinglists_optgroup', __('(unknown)', MP_TXTDOM), $optgroup) . '\'>' . $html;
			$opened = $$optgroup = true;
		}

		$x = implode('', $list) . (($opened) ? '</optgroup>' : '');

		if (!$echo) return "\n$x\n";
		echo "\n$x\n";
	}
}