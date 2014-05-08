<?php
$mp_general['tab'] = 'connection_pop3';

$connection_pop3	= $_POST['connection_pop3'];

if (class_exists('MailPress_bounce_handling'))
	update_option(MailPress_bounce_handling::option_name_pop3, $connection_pop3);

if (class_exists('MailPress_bounce_handling_II'))
	update_option(MailPress_bounce_handling_II::option_name_pop3, $connection_pop3);

update_option(MailPress::option_name_general, $mp_general);

$message = __("'POP3' settings saved", MP_TXTDOM);