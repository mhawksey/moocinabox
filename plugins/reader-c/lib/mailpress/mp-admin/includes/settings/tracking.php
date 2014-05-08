<?php
$mp_general['tab'] = 'tracking';

$tracking	= $_POST['tracking'];

update_option(MailPress_tracking::option_name, $tracking);
update_option(MailPress::option_name_general, $mp_general);

$message = __("'Tracking' settings saved", MP_TXTDOM);