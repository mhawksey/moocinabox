<?php

define('DOING_CRON', true);

$path = substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'wp-content'));

include($path . 'wp-load.php');
include($path . 'wp-admin/includes/admin.php');

do_action('mp_process_bounce_handling_II');

MP_::mp_die();