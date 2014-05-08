<?php
$mp_general['tab'] = 'roles_and_capabilities';

global $wp_roles;
foreach($wp_roles->role_names as $role => $name)
{
	if ('administrator' == $role) continue;
	$rcs	= $_POST['cap'][$role];
	update_option('MailPress_r&c_' . $role, $rcs);
}
update_option(MailPress::option_name_general, $mp_general);

$message = __("'Roles and capabilities' settings saved", MP_TXTDOM);