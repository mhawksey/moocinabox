<?php
if (!isset($connection_php_mail)) $connection_php_mail = get_option(MailPress_connection_php_mail::option_name);

$formname = substr(basename(__FILE__), 0, -4); 
?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table'>
		<tr valign='top'>
			<th scope='row'><?php _e('Additional_parameters', MP_TXTDOM); ?></th>
			<td class='field'>
				<input type='text' size='75' name='connection_php_mail[addparm]' value="<?php echo $connection_php_mail['addparm']; ?>" />
				<br />
				<?php  printf(__("(optional) Specify here the 5th parameter of php <a href='%s'>mail()</a> function", MP_TXTDOM),__('http://fr.php.net/manual/en/function.mail.php', MP_TXTDOM)); ?>
			</td>
		</tr>
	</table>
<?php MP_AdminPage::save_button(); ?>
</form>