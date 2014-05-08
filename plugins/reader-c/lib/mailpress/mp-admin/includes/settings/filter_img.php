<?php
$mp_general['tab'] = 'filter_img';

$filter_img	= stripslashes_deep($_POST['filter_img']);

update_option(MailPress_filter_img::option_name, $filter_img);
update_option(MailPress::option_name_general, $mp_general);

$message = __("'Image filter' settings saved", MP_TXTDOM);