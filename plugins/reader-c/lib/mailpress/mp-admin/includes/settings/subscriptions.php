<?php
$mp_general['tab'] = 'subscriptions';
$old_subscriptions = get_option(MailPress::option_name_subscriptions);

$subscriptions = $_POST['subscriptions'];

if (isset($_POST['comment']['on'])) update_option(MailPress_comment::option, (isset($subscriptions['comment_checked'])) );  // so we don't delete settings if addon deactivated !

if (!isset($_POST['mailinglist']['on']))
{	// so we don't delete settings if addon deactivated !
	if (isset($old_subscriptions['display_mailinglists'])) $subscriptions['display_mailinglists'] 	= $old_subscriptions['display_mailinglists'];
}

if (isset($_POST['newsletter']['on']))
{
	$diff_default_newsletters = array();
	if (!isset($subscriptions['default_newsletters'])) 	 $subscriptions['default_newsletters'] 	= array();
	$old_default_newsletters = (isset($old_subscriptions ['default_newsletters'])) ? $old_subscriptions ['default_newsletters'] : MP_Newsletter::get_defaults();

	foreach($subscriptions['default_newsletters'] as $k => $v) if (!isset($old_default_newsletters[$k]))  $diff_default_newsletters[$k] = true;
	foreach($old_default_newsletters as $k => $v) if (!isset($subscriptions ['default_newsletters'][$k])) $diff_default_newsletters[$k] = true;
	foreach ($diff_default_newsletters as $k => $v) MP_Newsletter::reverse_subscriptions($k);

	if ($old_subscriptions['default_newsletters'] != $subscriptions['default_newsletters'] || $old_subscriptions['newsletters'] != $subscriptions['newsletters']) wp_schedule_single_event( current_time('timestamp', 'gmt') - 1, 'mp_schedule_newsletters', array('args' => array('event' => '** Subscriptions updated **' )));
}
else  
{	// so we don't delete settings if addon deactivated !
	if (isset($old_subscriptions['newsletters'])) 		 $subscriptions['newsletters'] 		= $old_subscriptions['newsletters'];
	if (isset($old_subscriptions['default_newsletters']))  $subscriptions['default_newsletters'] 	= $old_subscriptions['default_newsletters'];
}
	
$mp_subscriptions = $subscriptions;
	
update_option(MailPress::option_name_subscriptions, $mp_subscriptions);
update_option(MailPress::option_name_general, $mp_general);

$message = __('"Subscriptions" settings saved', MP_TXTDOM);