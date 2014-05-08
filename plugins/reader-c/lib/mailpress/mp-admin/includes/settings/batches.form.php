<?php $formname = substr(basename(__FILE__), 0, -4); ?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table'>
<?php	do_action('MailPress_settings_batches'); ?>
	</table>
<?php MP_AdminPage::save_button(); ?>
</form>