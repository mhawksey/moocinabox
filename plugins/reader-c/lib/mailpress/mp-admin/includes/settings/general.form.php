<?php
$subscription_mngt = array ('ajax' => __('Default', MP_TXTDOM), 'page_id' => __('Page template', MP_TXTDOM), 'cat' => __('Category template', MP_TXTDOM));

if (!isset($_POST['formname']) || ('general.form' != $_POST['formname'])) $mp_general = get_option(MailPress::option_name_general);	

if (!isset($mp_general['subscription_mngt']))
{
	$mp_general['subscription_mngt'] = 'ajax';
	$mp_general['id'] = '';
}

$formname = substr(basename(__FILE__), 0, -4); 
?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table'>

<!-- From -->
		<tr>
			<th style='padding:0;'><strong><?php _e('From', MP_TXTDOM); ?></strong></th>
			<td style='padding:0;'></td>
		</tr>
		<tr valign='top' class='mp_sep'>
			<th scope='row'><?php _e('All Mails sent from', MP_TXTDOM); ?></th>
			<td style='padding:0;'>
				<table class='subscriptions'>
					<tr>
						<td class='pr10<?php if (isset($fromemailclass)) echo " $form_invalid"; ?>'>
							<?php _e('Email : ', MP_TXTDOM); ?> 
							<input type='text' size='25' name='general[fromemail]' value='<?php echo (isset($mp_general['fromemail'])) ? $mp_general['fromemail'] : ''; ?>' />
						</td>
						<td class='pr10<?php if (isset($fromnameclass)) echo " $form_invalid"; ?>'>
							<?php _e('Name : ', MP_TXTDOM); ?> 
							<input type='text' size='25' name='general[fromname]'  value="<?php echo (isset($mp_general['fromname'])) ? esc_attr($mp_general['fromname']) : ''; ?>" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
<!-- Blog -->
		<tr><th></th><td></td></tr>
		<tr valign='top'>
			<th style='padding:0;'><strong><?php _e('On Blog', MP_TXTDOM); ?></strong></th>
			<td style='padding:0;'></td>
		</tr>
		<tr valign='top'>
			<th scope='row'><label for='fullscreen'><?php _e('View mail in fullscreen', MP_TXTDOM); ?></label></th>
			<td>
				<input id='fullscreen' name='general[fullscreen]' type='checkbox'<?php checked( isset($mp_general['fullscreen']) ); ?> />
			</td>
		</tr>
		<tr valign='top'>
			<th scope='row'><?php _e(' Manage subscriptions from', MP_TXTDOM); ?></th>
			<td style='padding:0;'>
				<table>
					<tr>
						<td>
							<select name='general[subscription_mngt]' class='subscription_mngt'>
<?php MP_AdminPage::select_option($subscription_mngt, $mp_general['subscription_mngt']);?>
							</select>
						</td>
						<td class='mngt_id<?php if (isset($idclass)) echo " $form_invalid"; ?>'<?php if ('ajax' == $mp_general['subscription_mngt']) echo " style='display:none;'"; ?>>
							<input type='text' size='4' name='general[id]'  value='<?php echo $mp_general['id']; ?>' />
							<span class='page_id toggle'<?php if ('page_id' != $mp_general['subscription_mngt']) echo " style='display:none;'"; ?>><?php _e("Page id", MP_TXTDOM); ?></span>
							<span class='cat     toggle'<?php if ('cat'     != $mp_general['subscription_mngt']) echo " style='display:none;'"; ?>><?php _e("Category id", MP_TXTDOM); ?></span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
<?php do_action('MailPress_settings_general_forms'); ?>
		<tr valign='top' class='mp_sep' style='line-height:2px;padding:0;'><th style='line-height:2px;padding:0;'></th><td style='line-height:2px;padding:0;'></td></tr>
<!-- Admin -->
		<tr><th></th><td></td></tr>
		<tr>
			<th style='padding:0;'><strong><?php _e('Admin', MP_TXTDOM); ?></strong></th>
			<td style='padding:0;'></td>
		</tr>
		<tr valign='top'>
			<th scope='row'><label for='dshbrd'><?php _e('Dashboard widgets', MP_TXTDOM); ?></label></th>
			<td>
				<input id='dshbrd' name='general[dashboard]' type='checkbox'<?php checked( isset($mp_general['dashboard']) ); ?> />
			</td>
		</tr>
		<tr valign='top'>
			<th scope='row'><label for='wpmail'><?php _e('MailPress version of wp_mail', MP_TXTDOM); ?></label></th>
			<td>
				<input id='wpmail' name='general[wp_mail]' type='checkbox'<?php checked( isset($mp_general['wp_mail']) ); ?> />
			</td>
		</tr>
<?php do_action('MailPress_settings_general_admin'); ?>
		<tr valign='top' class='mp_sep' style='line-height:2px;padding:0;'><th style='line-height:2px;padding:0;'></th><td style='line-height:2px;padding:0;'></td></tr>

<!-- Add ons -->
<?php do_action('MailPress_settings_general'); ?>
	</table>
<?php if(!$mp_general) { ?>
	<span class='startmsg'><?php _e('You can start to update your SMTP config, once you have saved your General settings', MP_TXTDOM); ?></span>
<?php } ?>
<?php MP_AdminPage::save_button(); ?>
</form>