<?php
if (!isset($connection_sendmail)) $connection_sendmail = get_option(MailPress_connection_sendmail::option_name);
if (!isset($connection_sendmail['cmd'])) $connection_sendmail['cmd'] = 'std';

$formname = substr(basename(__FILE__), 0, -4); 
?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table'>
		<tr valign='top'>
			<th scope='row'><?php _e('Connect', MP_TXTDOM); ?></th>
			<td class='field'>
				<label for='connection_sendmail_radio1'>
					<input name='connection_sendmail[cmd]' type='radio'<?php checked($connection_sendmail['cmd'],'std'); ?>  value='std' class='connection_sendmail' id='connection_sendmail_radio1' />
					<?php _e("using '/usr/sbin/sendmail -bs'", MP_TXTDOM); ?>
				</label>
				<br />
				<label for='sendmail-custom'>
					<input name='connection_sendmail[cmd]' id='sendmail-custom' type='radio'<?php checked($connection_sendmail['cmd'],'custom'); ?>  value='custom' class='connection_sendmail' />
					<?php _e('using a custom command', MP_TXTDOM); ?>
				</label>
				&#160;&#160;
				<span id='sendmail-custom-cmd' <?php if ('custom' != $connection_sendmail['cmd']) echo " style='display:none;'"; ?>>
					<input type='text' size='40' name='connection_sendmail[custom]' value="<?php echo $connection_sendmail['custom']; ?>" />					
				</span>
			</td>
		</tr>
	</table>
<?php MP_AdminPage::save_button(); ?>
</form>