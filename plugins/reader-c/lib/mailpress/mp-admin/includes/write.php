<?php
global $draft;

$url_parms 	= MP_AdminPage::get_url_parms();

//
// MANAGING RESULTS
//

if (isset($_GET['id']))
{
	$draft 	= MP_Mail::get($_GET['id']);
	$rev_ids 	= MP_Mail_meta::get($draft->id, '_MailPress_mail_revisions');
}

$autosave	= true;
$notice 	= false;

$h2 		= __('Add New Mail', MP_TXTDOM);
$hidden  	= "\t\t\t<input type='hidden' id='mail_id' name='id' value='0' />\n";
$list_url 	= MP_AdminPage::url(MailPress_mails, $url_parms);

if (isset($draft))
{
	$h2 		= sprintf( __('Edit Draft # %1$s', MP_TXTDOM), $draft->id);
	$hidden  	= "\t\t\t<input type='hidden' id='mail_id' name='id' value='$draft->id' />\n";
	$delete_url = esc_url(MailPress_write ."&amp;action=delete&amp;id=$draft->id");

	$last_user 	= get_userdata($draft->created_user_id);
	$lastedited	= sprintf(__('Last edited by %1$s on %2$s at %3$s', MP_TXTDOM), esc_html( $last_user->display_name ), mysql2date(get_option('date_format'), $draft->created), mysql2date(get_option('time_format'), $draft->created));

/* revisions */
	if (is_array($rev_ids))
	{
		foreach ($rev_ids as $rev_user => $rev_id)
		{
			global $current_user ;
			if ($current_user->ID == $rev_user)
			{
				$revision = MP_Mail::get($rev_id);
				break;
			}
			else
			{
				$x = MP_Mail::get($rev_id);
				if ($x)
				{
					if ($x->created > $revision->created)
					{
						$revision = $x;
						$revision->not_this_user = true;
					}
				}
			}
		}
	}

	if (isset($revision))
	{
		if ($revision->created > $draft->created)
		{
			$autosave_data = MP_Mail_revision::autosave_data();

			foreach ($autosave_data as $k => $v)
			{
				if ( wp_text_diff( $revision->$k, $draft->$k ) ) 
				{
					$autosave = false;

					$notice = sprintf( __( 'There is an autosave of this mail that is more recent than the version below.  <a href="%s">View the autosave</a>.', MP_TXTDOM ), esc_url(MailPress_revision . "&id=$draft->id&revision=$revision->id") );
					break;
				}
			}
		}
	}
	else
	{
		$revision = new stdClass();
		$revision->id = '0';
	}

	if ((isset($revision->not_this_user)) && ($revision->not_this_user)) $revision->id = '0';

	$hidden  	.= "\t\t\t<input type='hidden' id='mail_revision' 	name='revision' 	value='$revision->id' />\n";
/* end of revisions */

/* lock */

	if ($last = MP_Mail_lock::check($draft->id))
	{
		$lock_user 	= get_userdata($last);
		$lock_user_name = $lock_user ? $lock_user->display_name : __('Somebody');
		$lock = sprintf( __( 'Warning: %s is currently editing this mail' ), esc_html( $lock_user_name ) );
	}
	else
	{
		MP_Mail_lock::set($draft->id);
	}
/* end of lock */
}
else
{
	$draft = new stdClass();
	if (isset($_GET['toemail'])) $draft->toemail = $_GET['toemail'];
}

$draft->_scheduled = ( !isset($draft->sent) || '0000-00-00 00:00:00' == $draft->sent ) ? false : true;

if (isset($_SERVER['HTTP_REFERER']))
	$hidden .= "<input type='hidden' name='referredby' value='" . esc_url($_SERVER['HTTP_REFERER']) . "' />";

// what else ?
	do_action('MailPress_update_meta_boxes_write');

// messages
$class = 'fromto';
$message = ''; $err = 0;
if (isset($_GET['sched'])) 	{$err += 0; if (!empty($message)) $message .= '<br />'; $message .= sprintf( __('Mail scheduled for: <strong>%1$s</strong>. <a class="thickbox thickbox-preview" href="%2$s">Preview mail</a>',  MP_TXTDOM), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $draft->sent ) ), esc_url(add_query_arg( array('action' => 'iview', 'id' => $draft->id, 'preview_iframe' => 1, 'TB_iframe' => 'true'), MP_Action_url )));}
if (isset($_GET['saved'])) 	{$err += 0; if (!empty($message)) $message .= '<br />'; $message .= __('Mail saved', MP_TXTDOM); }
if (isset($_GET['notsent'])) 	{$err += 1; if (!empty($message)) $message .= '<br />'; $message .= __('Mail NOT sent', MP_TXTDOM); }
if (isset($_GET['nomail'])) 	{$err += 1; if (!empty($message)) $message .= '<br />'; $message .= __('Please, enter a valid email',  MP_TXTDOM); $class = "TO"; }
if (isset($_GET['nodest'])) 	{$err += 1; if (!empty($message)) $message .= '<br />'; $message .= __('Mail NOT sent, no recipient',  MP_TXTDOM); $class = "TO"; }
if (isset($lock))			{$err += 1; if (!empty($message)) $message .= '<br />'; $message .= $lock; }
if ($notice)			{$err += 1; if (!empty($message)) $message .= '<br />'; $message .= $notice; } 	
if (isset($_GET['sent'])) 	{$err += 0; if (!empty($message)) $message .= '<br />'; $message .= sprintf( _n( __('%s mail sent', MP_TXTDOM), __('%s mails sent', MP_TXTDOM), $_GET['sent']), $_GET['sent']); }
if (isset($_GET['revision'])) {$err += 0; if (!empty($message)) $message .= '<br />'; $message .= sprintf( __('Mail restored to revision from %s', MP_TXTDOM), MP_Mail_revision::title( (int) $_GET['revision'], false, $_GET['time']) ); }
$mp_general	= get_option(MailPress::option_name_general);

// from
if (empty($draft->fromemail))
{
	$draft->fromemail = apply_filters('MailPress_write_fromemail', $mp_general['fromemail']);
	$draft->fromname  = apply_filters('MailPress_write_fromname',  $mp_general['fromname'] ); 
}

// to 
if (isset($draft->toemail))
{
	if (!is_email($draft->toemail))
	{
		$draft->to_list = $draft->toemail;
		$draft->toemail = $draft->toname = '';
	}
	else
	{
		$draft->toname  = (isset($draft->toname)) ? esc_attr($draft->toname) : '';
	}
}
else
	$draft->toemail = $draft->toname = '';

// or to
$draft_dest = MP_User::get_mailinglists();

// mail formats
$mail_format = ( isset($draft->id) ) ? MP_Mail_meta::get($draft->id, '_MailPress_format') : 'standard';

$all_mail_formats =  array(
						'standard' => array (
							'description' 	=> __( 'Add a title and use the editor to compose your mail.', MP_TXTDOM ),
							'part'		=> __( 'Html', MP_TXTDOM )
						),
						'plaintext' => array (
							'description' => __( 'Type plaintext part of mail or generate it automattically using Synchronize button.', MP_TXTDOM ),
							'part'		=> __( 'Plaintext', MP_TXTDOM )
						)
				);

$mail_format_options = '';

foreach($all_mail_formats as $slug => $attr ) { 
	$mf_class= ''; 
	if ($mail_format == $slug) { $mf_class = 'class="active"'; $mf_tip = sprintf( __( '%s Part', MP_TXTDOM ), $attr['part'] ); }

	$mail_format_options .= '<a ' . $mf_class . ' href="' . MailPress_write  . '&format=' . $slug . '" data-description="' . esc_attr($attr['description']) . '" data-mp-format="' . esc_attr($slug) . '" title="' . esc_attr( sprintf( __( '%s Part', MP_TXTDOM ), $attr['part'] ) ) . '"><div class="' . $slug . '"></div></a>';
}

?>
	<div class='wrap'>
		<div id="icon-mailpress-mailnew" class="icon32"><br /></div>
		<h2><?php echo $h2; ?></h2>
<?php if ($message) MP_AdminPage::message($message, ($err) ? false : true); ?>
		<form id='writeform' name='writeform' action='' method='post'>

		<input type='hidden' 				name='action'  		value='draft' />
<?php echo $hidden; ?>
		<input type='hidden' id='user-id' 		name='user_ID' 		value="<?php echo MP_WP_User::get_id(); ?>" />

		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); echo ("\n"); ?>
<?php if ( $autosave ) : ?>
		<?php wp_nonce_field( 'autosave', 'autosavenonce', false ); echo ("\n"); ?>
		<?php wp_nonce_field( 'getpreviewlink', 'getpreviewlinknonce', false ); echo ("\n"); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
<?php endif; ?>

		<div id='poststuff' class='metabox-holder has-right-sidebar'>
			<div id="side-info-column" class="inner-sidebar">
<?php do_meta_boxes(MP_AdminPage::screen, 'side', $draft); ?>
			</div>

			<div id='post-body'>
				<div id='post-body-content'>

					<div class="mail-format-options">
						<span class="mail-format-tip"><?php echo $mf_tip; ?></span>
						<?php echo $mail_format_options; ?>
					</div>

					<div id='fromtodiv'>
							<table class='widefat'><tr><td style='border:none;'>
								<div style='padding-bottom:5px;'>
									<select name='to_list' id='to_list'  class='<?php echo $class; ?>'>
<?php MP_AdminPage::select_optgroup($draft_dest, (isset($draft->to_list)) ? $draft->to_list : '') ?>
									</select>
								</div>
								<div id='toemail-toname' style='padding-bottom:5px;<?php if (isset($draft->to_list)) echo "display:none;";?>'>
									<label id='toemail-prompt-text' class='hide-if-no-js' for='toemail' style='color:#bbb;padding:6px 0 0 8px;position:absolute;<?php if (esc_attr($draft->toemail) != '') echo 'visibility:hidden;' ; ?>'><?php _e('Enter email here', MP_TXTDOM); ?></label>
									<input  class='<?php echo $class; ?>' style='width:45%;' type='text' name='toemail' id='toemail' value="<?php echo esc_attr($draft->toemail); ?>" title="<?php _e('Email', MP_TXTDOM); ?>" />
									<label id='toname-prompt-text' class='hide-if-no-js' for='toname' style='color:#bbb;padding:6px 0 0 8px;position:absolute;<?php if (esc_attr($draft->toname) != '') echo 'visibility:hidden;' ; ?>'><?php _e('Enter name here', MP_TXTDOM); ?></label>
									<input  class='<?php echo $class; ?>' style='width:45%;' type='text' name='toname'  id='toname'  value="<?php echo esc_attr($draft->toname); ?>"  title="<?php _e('Name', MP_TXTDOM); ?>" />
								</div>
							</td></tr></table>


					</div>
					<div id='titlediv'>
						<div id='titlewrap'>
							<label for='title' id='title-prompt-text' class='hide-if-no-js' style='<?php echo (isset($draft->subject)) ? 'visibility:hidden' : ''; ?>'><?php _e('Enter subject here', MP_TXTDOM); ?></label>
							<input type='text' name='subject' id='title' size='30' tabindex='1' autocomplete='off' value="<?php echo (isset($draft->subject)) ? esc_attr($draft->subject) : ''; ?>" />
						</div>
					</div>


					<div class="mail-format-description"></div>
					<div class="mail-formats-fields">
						<input type="hidden" name="mail_format" id="mail_format" value="<?php echo $mail_format; ?>" >
						<div class="<?php if ('plaintext' != $mail_format) echo 'hidden '; ?>field mp-format-plaintext" id="mp-format-plaintext">
							<label for="plaintext"><?php _e('Plaintext', MP_TXTDOM); ?></label>
							<textarea id="plaintext" class="widefat" name="plaintext"><?php echo (isset($draft->plaintext)) ? str_replace('&', '&amp;', $draft->plaintext) : ''; ?></textarea>
						</div>
						<div id='div_html2txt' class='hidden'>
							<a id='html2txt' class='button hide-if-no-js' onclick="return false;" title="<?php echo esc_attr(__('Plaintext from Html', MP_TXTDOM)); ?>" href="#">
								<span class="mp-media-buttons-icon"></span>
								<?php _e('Synchronize', MP_TXTDOM); ?> 
							</a>
							<img alt='' id='html2txt_loading' src='images/wpspin_light.gif' />
						</div>
					</div>


					<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea edit-form-section">
<?php wp_editor( (isset($draft->html)) ? $draft->html : '', 'content', array( 'media_buttons' => apply_filters('MailPress_upload_media', false), 'tabindex' => 5 ) ); ?>
						<div id="post-status-info">
							<span id="wp-word-count" class="alignleft"></span>
							<span class="alignright">
								<span id="autosave">&#160;</span>
								<span id="last-edit">
<?php if (isset($lastedited)) : ?>
									<?php echo $lastedited; ?>
<?php	endif; ?>
								</span>
							</span>
							<br class="clear" />
						</div>
					</div>
<?php do_meta_boxes(MP_AdminPage::screen, 'normal', $draft); ?>
				</div>
			</div>
		</div>
	</form>
</div>
<?php if (!MP_AdminPage::flash()) : ?>
	<div id='html-upload-iframes'></div>
<?php endif;