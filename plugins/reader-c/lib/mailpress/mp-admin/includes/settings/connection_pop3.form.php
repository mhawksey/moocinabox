<?php
if (class_exists('MailPress_bounce_handling'))
	if (!isset($connection_pop3)) $connection_pop3 = get_option(MailPress_bounce_handling::option_name_pop3);

if (class_exists('MailPress_bounce_handling_II'))
	if (!isset($connection_pop3)) $connection_pop3 = get_option(MailPress_bounce_handling_II::option_name_pop3);

$formname = substr(basename(__FILE__), 0, -4); 
?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table'>
		<tr valign='top'>
			<th scope='row'><?php _e('POP3 Server', MP_TXTDOM); ?></th>
			<td class='field'>
				<input type='text' size='25' name='connection_pop3[server]' value="<?php if (isset($connection_pop3['server'])) echo $connection_pop3['server']; ?>" />	
			</td>
		</tr>
		<tr valign='top'>
			<th><?php _e('Port', MP_TXTDOM); ?></th>
			<td class='field'>
				<input type='text' size='4' name='connection_pop3[port]' value="<?php if (isset($connection_pop3['port'])) echo $connection_pop3['port']; ?>" />
			</td>
		</tr>
		<tr valign='top'>
			<th><?php _e('Username', MP_TXTDOM); ?></th>
			<td class='field'>
				<input type='text' size='25' name='connection_pop3[username]' value="<?php if (isset($connection_pop3['username'])) echo $connection_pop3['username']; ?>" />
			</td>
		</tr>
		<tr valign='top'>
			<th><?php _e('Password', MP_TXTDOM); ?></th>
			<td colspan='2'>
				<input type='password' size='25' name='connection_pop3[password]' value="<?php if (isset($connection_pop3['password'])) echo $connection_pop3['password']; ?>" />
			</td>
		</tr>
	</table>
<?php MP_AdminPage::save_button(); ?>
</form>