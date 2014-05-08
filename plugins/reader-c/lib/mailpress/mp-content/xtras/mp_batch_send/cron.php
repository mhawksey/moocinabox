<?php



define('DOING_CRON', true);



$path = substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'wp-content'));



include('/home/octel/wordpress-3.8/wp-load.php');

include('/home/octel/wordpress-3.8/wp-admin/includes/admin.php');



do_action('mp_process_batch_send');



MP_::mp_die();