<?php
global $mp_general, $mp_subscriptions;
if (!isset($subscriptions)) $subscriptions = $mp_subscriptions;
?>
<tr valign='top'>
	<th style='padding:0;'><strong><?php _e('Mailing lists', MP_TXTDOM); ?></strong></th>
	<td style='padding:0;' colspan='4'></td>
</tr>
<tr valign='top' class="rc_role">
	<th scope='row'>
		<input type='hidden'   name='mailinglist[on]' value='on' />
	</th>
	<td colspan='4'>
		<table id='mailinglists' class='general'>
			<tr>
				<td>
<?php
$default_mailing_list = get_option(MailPress_mailinglist::option_name_default);

$mls = array();
$mailinglists = apply_filters('MailPress_mailinglists', array());

if (empty($mailinglists))
{
	_e('You need to create at least one mailinglist.', MP_TXTDOM);
}
else
{
	foreach ($mailinglists as $k => $v) 
	{
		$x = str_replace('MailPress_mailinglist~', '', $k, $count);
		if (0 == $count) 	continue;	
		if (empty($x)) 	continue;
		$mls[$x] = $v;
	}

	foreach ($mls as $k => $v)
	{
?>
						<label for='subscriptions_display_mailinglists_<?php echo $k; ?>'><input type='checkbox' id='subscriptions_display_mailinglists_<?php echo $k; ?>' name='subscriptions[display_mailinglists][<?php echo $k; ?>]'<?php if (isset($mp_subscriptions['display_mailinglists'][$k])) checked($mp_subscriptions['display_mailinglists'][$k], 'on'); ?> />&#160;&#160;<?php echo $v; ?></label><br />
<?php
	}
}
?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr valign='top' style='line-height:10px;padding:0;'><td colspan='5' style='line-height:10px;padding:0;'>&#160;</td></tr>
<tr valign='top' class='mp_sep' style='line-height:2px;padding:0;'><td colspan='5' style='line-height:2px;padding:0;'></td></tr>
<tr><th></th><td colspan='4'></td></tr>