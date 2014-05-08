<?php
$logs = get_option(MailPress::option_name_logs);
$mp_general['tab'] = 'logs';
	
foreach ($_POST['logs'] as $k => $v) $logs[$k] = $v; // so we don't delete settings if addon deactivated !
	
update_option(MailPress::option_name_logs, $logs );
update_option(MailPress::option_name_general, $mp_general);

$message = __('Logs settings saved', MP_TXTDOM);