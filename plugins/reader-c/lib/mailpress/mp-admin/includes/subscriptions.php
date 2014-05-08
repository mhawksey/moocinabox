<?php
global $mp_subscriptions;

$email	= MP_WP_User::get_email();
$mp_user	= MP_User::get(MP_User::get_id_by_email($email));
$active 	= ('active' == $mp_user->status) ? true : false;

if (isset($_POST['formname']) && ('sync_wordpress_user_subscriptions' == $_POST['formname']))
{
	if ($mp_user->name != $_POST['mp_user_name'])
	{
		MP_User::update_name($mp_user->id, $_POST['mp_user_name']);
		$mp_user->name = stripslashes($_POST['mp_user_name']);
	}

	if (class_exists('MailPress_comment'))				MailPress_comment::update_checklist($mp_user->id);
	if (class_exists('MailPress_newsletter'))  if ($active) 	MailPress_newsletter::update_checklist($mp_user->id);
	if (class_exists('MailPress_mailinglist')) if ($active) 	MailPress_mailinglist::update_checklist($mp_user->id);

	$message = __('Subscriptions saved', MP_TXTDOM);
}

$checklist_comments = $checklist_mailinglists = $checklist_newsletters = false;
if (class_exists('MailPress_comment'))				$checklist_comments     = MailPress_comment::get_checklist($mp_user->id);
if (class_exists('MailPress_newsletter'))  if ($active) 	$checklist_newsletters  = MailPress_newsletter::get_checklist($mp_user->id);
if (class_exists('MailPress_mailinglist')) if ($active)	$checklist_mailinglists = MailPress_mailinglist::get_checklist($mp_user->id);

//
// MANAGING TITLE
//
	$h2    =  __('Manage Subscriptions', MP_TXTDOM);
?>
<div class='wrap'>
	<form id='posts-filter' action='' method='post'>
		<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) MP_AdminPage::message($message); ?>
		<input type='hidden' name='page' value='<?php echo MailPress_page_subscriptions; ?>' />
		<input type='hidden' name='formname' value='sync_wordpress_user_subscriptions' />

		<table class="form-table">
			<tr>
				<th scope='row'><?php _e('Email', MP_TXTDOM); ?></th>
				<td>
					<input type='text' disabled='disabled' value='<?php echo $mp_user->email; ?>' size='30' />
				</td>
			</tr>
			<tr>
				<th scope='row'><?php _e('Name', MP_TXTDOM); ?></th>
				<td>
					<input name='mp_user_name' type='text' value="<?php echo esc_attr($mp_user->name); ?>" size='30' />
				</td>
			</tr>
<?php if ($checklist_comments) : $ok = true; ?>
			<tr>
				<th scope="row"><?php _e('Comments'); ?></th>
				<td>
					<?php echo $checklist_comments; ?>
				</td>
			</tr>
<?php endif; ?> 	
<?php if ($checklist_newsletters) : $ok = true; ?>
			<tr>
				<th scope="row"><?php _e('Newsletters', MP_TXTDOM); ?></th>
				<td>
					<?php echo $checklist_newsletters; ?>
				</td>
			</tr>
<?php endif; ?> 	
<?php if ($checklist_mailinglists) : $ok = true; ?>
			<tr>
				<th scope="row"><?php _e('Mailing lists', MP_TXTDOM); ?></th>
				<td>
					<?php echo $checklist_mailinglists; ?>
				</td>
			</tr>
<?php endif; ?>
		</table>
<?php if (isset($ok)) : ?> 
		<p class='submit'>
			<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Save', MP_TXTDOM); ?>' />
		</p>
<?php else : ?> 
		<p>
<?php 
		if ($active) 	_e('Nothing to subscribe for ...', MP_TXTDOM);
		else			_e('Your email has been deactivated, ask the administrator ...', MP_TXTDOM);
?>
		</p>
<?php endif; ?> 
	</form>
</div>