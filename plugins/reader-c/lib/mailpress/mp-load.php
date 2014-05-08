<?php

/**

 * Bootstrap file for 

		1. setting some constants

		2. loading pluggable functions



 * If the mailpress-config.php file is not found then default constant values apply.



**/



// 1.



/** text domain for gettext. */

define ('MP_TXTDOM', 'MailPress');



/** Absolute path to the MailPress directory. */

define ('MP_ABSPATH',	dirname(__FILE__) . '/');



/** Folder name of MailPress plugin. */

define ('MP_FOLDER', 	basename( MP_ABSPATH ));



/** Relative path to the MailPress directory. */

define ('MP_PATH', 	PLUGINDIR . '/reader-c/lib/' . MP_FOLDER . '/' );



/** Plugin version. */

require_once (ABSPATH . 'wp-admin/includes/plugin.php');

$plugin_data = get_plugin_data( MP_ABSPATH . 'MailPress.php' );

define ('MP_Version',	$plugin_data['Version']);



/** Loading optional mailpress-config.php file in current directory or parent directory */

$mp_config = 'mailpress-config.php';

foreach (array(MP_ABSPATH . $mp_config, dirname(MP_ABSPATH) . '/' . $mp_config) as $mp_file)

{

	if ( !is_file( $mp_file ) ) continue;

	require_once( $mp_file );

	break;

}



/** Folder name of MailPress 'mp-content'. */

defined('MP_CONTENT_FOLDER') 	or define ('MP_CONTENT_FOLDER',	'mp-content');



/** Absolute path to the MailPress 'mp-content' folder. */

defined('MP_CONTENT_DIR') 		or define ('MP_CONTENT_DIR',	MP_ABSPATH . MP_CONTENT_FOLDER . '/');



/** Relative path to the MailPress 'mp-content' folder. */

defined('MP_PATH_CONTENT') 	or define ('MP_PATH_CONTENT',	MP_PATH . MP_CONTENT_FOLDER . '/');



if ( defined('WP_DEBUG') && WP_DEBUG && !defined('MP_DEBUG_LOG') ) 

	define('MP_DEBUG_LOG', true);



// 2.



global $mp_general, $mp_subscriptions;

$mp_general  	= get_option(MailPress::option_name_general);

$mp_subscriptions = get_option(MailPress::option_name_subscriptions);



if (isset($mp_general['wp_mail']))

	include (MP_ABSPATH . 'mp-includes/wp_pluggable.php');