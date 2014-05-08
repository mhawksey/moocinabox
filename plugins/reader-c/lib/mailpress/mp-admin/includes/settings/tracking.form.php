<?php
if (!isset($tracking)) $tracking = get_option(MailPress_tracking::option_name);

foreach(array('mail', 'user') as $folder)
{
	$MP_Tracking_metaboxes = new MP_Tracking_metaboxes($folder, array());
	$tracking_reports[$folder] = $MP_Tracking_metaboxes->get_all($folder);
}

$formname = substr(basename(__FILE__), 0, -4); 
?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table rc-table'>
		<tr>
			<th scope='row'></th>
			<th scope='row'><strong><?php _e('User', MP_TXTDOM); ?></strong></th>
			<th scope='row'><strong><?php _e('Mail', MP_TXTDOM); ?></strong></th>
		</tr>
		<tr>
			<td style='vertical-align:top;'><strong><i><?php _e('Boxes', MP_TXTDOM); ?></i></strong></td>
			<td class='field'>
<?php
foreach ($tracking_reports['user'] as $k => $v)
{
?>
<input type='checkbox' id='<?php echo $k; ?>' name='tracking[<?php echo $k; ?>]' value='<?php echo $k; ?>' <?php if (isset($tracking[$k])) checked($k,$tracking[$k]); ?> /><label for='<?php echo $k; ?>'>&#160;<?php echo $v['title']; ?></label><br />
<?php
}
?>
			</td>
			<td class='field'>
<?php
foreach ($tracking_reports['mail'] as $k => $v)
{
?>
<input type='checkbox' id='<?php echo $k; ?>' name='tracking[<?php echo $k; ?>]' value='<?php echo $k; ?>' <?php if (isset($tracking[$k])) checked($k,$tracking[$k]); ?> /><label for='<?php echo $k; ?>'>&#160;<?php echo $v['title']; ?></label><br />
<?php
}
?>
			</td>
		</tr>
	</table>
<?php do_action('MailPress_tracking_settings'); ?>
<?php MP_AdminPage::save_button(); ?>
</form>