<?php
$mp_general['tab'] = 'connection_smtp';

$connection_smtp	= stripslashes_deep($_POST['connection_smtp']);

if ('custom' == $connection_smtp['port']) $connection_smtp ['port'] = $connection_smtp['customport'];
unset($connection_smtp['customport']);

switch (true)
{
	case ( !function_exists('proc_open') ) :
		$message = sprintf(__('"proc_open" php function is not available, you need to activate <a href="%1s">Connection_php_mail</a> add-on.', MP_TXTDOM), MailPress_addons); $no_error = false;
	break;
	case ( empty($connection_smtp['server'] ) ) :
		$serverclass = true;
		$message = __('field should not be empty', MP_TXTDOM); $no_error = false;
	break;
	case ( empty($connection_smtp['username']) && !empty($connection_smtp['password']) ) :
		$usernameclass = true;
		$message = __('field should not be empty', MP_TXTDOM); $no_error = false;
	break;
	case ( (isset($connection_smtp['smtp-auth']) && ('@PopB4Smtp' == $connection_smtp['smtp-auth'])) && (empty($connection_smtp['pophost'])) ) : 
		$pophostclass = true;
		$message = __('field should not be empty', MP_TXTDOM); $no_error = false;
	break;
	default :
		update_option(MailPress::option_name_smtp, $connection_smtp);
		update_option(MailPress::option_name_general, $mp_general);
		$message = __('SMTP settings saved, Test it !!', MP_TXTDOM);
	break;
}