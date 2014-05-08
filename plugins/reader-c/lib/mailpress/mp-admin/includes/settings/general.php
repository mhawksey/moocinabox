<?php
$mp_general		= get_option(MailPress::option_name_general);	
$mp_general['tab']= 'general';

$mp_general	= stripslashes_deep($_POST['general']);

switch (true)
{
	case ( !is_email($mp_general['fromemail']) ) :
		$fromemailclass = true;
		$message = __('field should be an email', MP_TXTDOM); $no_error = false;
	break;
	case ( empty($mp_general['fromname']) ) :
		$fromnameclass = true;
		$message = __('field should be a name', MP_TXTDOM); $no_error = false;
	break;
	case (('ajax' != $mp_general['subscription_mngt']) && ( !is_numeric($mp_general['id']))) :
		$idclass = true;
		$message = __('field should be numeric', MP_TXTDOM); $no_error = false;
	break;
	default :
		do_action('MailPress_settings_general_update');

		if ('ajax' == $mp_general['subscription_mngt']) $mp_general['id'] = '';
		update_option(MailPress::option_name_general, $mp_general);
		$message = __('General settings saved', MP_TXTDOM);
	break;
}