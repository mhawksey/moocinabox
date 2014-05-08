<?php
$mp_general['tab'] = 'connection_php_mail';

$connection_php_mail	= $_POST['connection_php_mail'];

update_option(MailPress_connection_php_mail::option_name, $connection_php_mail);
update_option(MailPress::option_name_general, $mp_general);

$message = __("'PHP MAIL' settings saved", MP_TXTDOM);