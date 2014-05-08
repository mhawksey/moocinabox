<?php
$mp_general['tab'] = 'connection_sendmail';

$connection_sendmail = $_POST['connection_sendmail'];

switch (true)
{
	case ( !function_exists('proc_open') ) :
		$message = sprintf(__('"proc_open" php function is not available, you need to activate <a href="%1s">Connection_php_mail</a> add-on.', MP_TXTDOM), MailPress_addons); $no_error = false;
	break;
	default :
		update_option(MailPress_connection_sendmail::option_name, $connection_sendmail);
		update_option(MailPress::option_name_general, $mp_general);
		$message = __("'SENDMAIL' settings saved", MP_TXTDOM);
	break;
}
