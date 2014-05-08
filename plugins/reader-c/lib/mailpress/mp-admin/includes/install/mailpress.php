<?php

global $wp_version; 

$m = array();

if (version_compare($wp_version, $min_ver_wp , '<'))	$m[] = sprintf(__('Your %1$s version is \'%2$s\', at least version \'%3$s\' required.', MP_TXTDOM), __('WordPress'), $wp_version , $min_ver_wp );
if (!is_writable(MP_ABSPATH . 'tmp'))			$m[] = sprintf(__('The directory \'%1$s\' is not writable.', MP_TXTDOM), MP_ABSPATH . 'tmp');
if (!extension_loaded('simplexml'))				$m[] = __("Default php extension 'simplexml' not loaded.", MP_TXTDOM);

if (!empty($m))
{
	$err  = sprintf(__('<b>Sorry, but you can\'t run this plugin : %1$s. </b>', MP_TXTDOM), $_GET['plugin']);
	$err .= '<ol><li>' . implode('</li><li>', $m) . '</li></ol>';

	if (isset($_GET['plugin'])) deactivate_plugins($_GET['plugin']);	
	trigger_error($err, E_USER_ERROR);
	return false;
}

/* MailPress install */

global $wpdb;

// theme init
if (!get_option('MailPress_current_theme'))
{
	add_option ('MailPress_template',         'twentyten');
	add_option ('MailPress_stylesheet', 	'twentyten');
	add_option ('MailPress_current_theme', 	'MailPress Twenty Ten');
}

//////////////////////////////////
//// Upgrade to MailPress 4.0 ////
//////////////////////////////////

if ( $x = get_option('MailPress_widget') ) 
{
	if (isset($x['jQ'])) $x['jq'] = $x['jQ'];
	unset($x['jQ']);
	add_option('widget_mailpress', $x);
	delete_option('MailPress_widget');
}

if ( !get_option(MailPress::option_name_logs) )
{
	$parms = array('level', 'lognbr', 'lastpurge');
	$_settings = array('MailPress_general' => 'general', 'MailPress_batch_send' => 'batch_send', 'MailPress_import' => 'import', 'MailPress_autoresponder' => 'autoresponder');	
	$logs = array();
	foreach($_settings as $_setting => $_target)
	{
		$x = get_option($_setting);
		if ($x)
		{
			foreach($parms as $parm)
			{
				if (isset($x[$parm])) $logs[$_target][$parm] = $x[$parm];
				unset($x[$parm]);
			}
			if (empty($x)) 	delete_option($_setting);
			else			update_option($_setting, $x);
		}
	}
	if (empty($logs)) $logs['general'] = MailPress::$default_option_logs;
	add_option(MailPress::option_name_logs, $logs);
}

global $mp_general, $mp_subscriptions;
$mp_general = get_option(MailPress::option_name_general);

if (isset($mp_general['subscription_mngt']))
{
	$mp_subscriptions = get_option(MailPress::option_name_subscriptions);
	if (!$mp_subscriptions)
	{
		$mp_subscriptions = $mp_general;
		$parms = array('subcomment', 'newsletters', 'default_newsletters');
		foreach($parms as $parm) unset($mp_general[$parm]);
		foreach($mp_general as $k => $v) unset($mp_subscriptions[$k]);
		update_option (MailPress::option_name_general, $mp_general);

		$mailinglist = get_option('MailPress_mailinglist');
		if ($mailinglist && is_array($mailinglist)) $mp_subscriptions = array_merge($mp_subscriptions, $mailinglist);
		if (!isset($mp_subscriptions['default_newsletters'])) $mp_subscriptions['default_newsletters'] = array();
		delete_option('MailPress_mailinglist');
		update_option(MailPress::option_name_subscriptions, $mp_subscriptions);
	}
}

//////////////////////////////////
//// Upgrade to MailPress 5.0 ////
//////////////////////////////////

$convert_tables = array(	$wpdb->prefix . 'MailPress_mails'	=> 	$wpdb->prefix . 'mailpress_mails',
					$wpdb->prefix . 'MailPress_mailmeta'=>	$wpdb->prefix . 'mailpress_mailmeta',
					$wpdb->prefix . 'MailPress_users'	=>	$wpdb->prefix . 'mailpress_users',
					$wpdb->prefix . 'MailPress_usermeta'=>	$wpdb->prefix . 'mailpress_usermeta',
					$wpdb->prefix . 'MailPress_stats'	=>	$wpdb->prefix . 'mailpress_stats'
);

foreach($convert_tables as $old_table => $new_table) if (!$wpdb->get_results( "SHOW KEYS FROM $new_table" )) $wpdb->query("ALTER TABLE $old_table RENAME TO $new_table;");

foreach (array('mp_mail' => $wpdb->mp_mailmeta, 'mp_user' => $wpdb->mp_usermeta) as $object => $table)
{
	$old_index = true;
	$indexes   = $wpdb->get_results( "SHOW KEYS FROM $table" );
	if (!$indexes) continue;
	foreach($indexes as $index) if ("{$object}_id" == $index->Column_name) $old_index = false;
	if ($old_index)
	{
		$sql = "ALTER TABLE $table
				DROP PRIMARY KEY,
				DROP INDEX user_id,
				DROP INDEX meta_key,
				CHANGE COLUMN " . $object[3] . "meta_id     meta_id     BIGINT(20) NOT NULL DEFAULT NULL AUTO_INCREMENT,
				CHANGE COLUMN " . substr($object, 3) . "_id {$object}_id  BIGINT(20) NOT NULL DEFAULT 0,
				ADD PRIMARY KEY(meta_id),
				ADD INDEX {$object}_id ({$object}_id,meta_key);";

		$wpdb->query($sql);
	}
}

//////////////////////////////////
//// Install                  ////
//////////////////////////////////

$charset_collate = '';
if ( $wpdb->supports_collation() ) 
{
	if ( ! empty($wpdb->charset) ) $charset_collate  = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
}

$queries = array();

$queries[] = 
"CREATE TABLE $wpdb->mp_mails (
 id                bigint(20)       UNSIGNED NOT NULL AUTO_INCREMENT,
 status            enum('draft', 'unsent', 'sending', 'sent', 'archived', '', 'paused') NOT NULL,
 theme             varchar(255)     NOT NULL default '',
 themedir          varchar(255)     NOT NULL default '',
 template          varchar(255)     NOT NULL default '',
 fromemail         varchar(255)     NOT NULL default '',
 fromname          varchar(255)     NOT NULL default '',
 toname            varchar(255)     NOT NULL default '',
 charset           varchar(255)     NOT NULL default '',
 parent            bigint(20)       UNSIGNED NOT NULL default 0,
 child             bigint(20)       NOT NULL default 0,
 subject           varchar(255)     NOT NULL default '',
 created           timestamp        NOT NULL default '0000-00-00 00:00:00',
 created_user_id   bigint(20)       UNSIGNED NOT NULL default 0,
 sent              timestamp        NOT NULL default '0000-00-00 00:00:00',
 sent_user_id      bigint(20)       UNSIGNED NOT NULL default 0,
 toemail           longtext         NOT NULL,
 plaintext         longtext         NOT NULL,
 html              longtext         NOT NULL,
PRIMARY KEY (id),
KEY status (status)
) $charset_collate;";

$queries[] = 
"CREATE TABLE $wpdb->mp_mailmeta (
 meta_id           bigint(20)       NOT NULL auto_increment,
 mp_mail_id        bigint(20)       NOT NULL default '0',
 meta_key          varchar(255)     default NULL,
 meta_value        longtext,
 PRIMARY KEY (meta_id),
 KEY mp_mail_id (mp_mail_id,meta_key)
) $charset_collate;";

$queries[] = 
"CREATE TABLE $wpdb->mp_users (
 id                bigint(20)       UNSIGNED NOT NULL AUTO_INCREMENT, 
 email             varchar(100)     NOT NULL,
 name              varchar(100)     NOT NULL,
 status            enum('waiting', 'active', 'bounced', 'unsubscribed')	NOT NULL,
 confkey           varchar(100)     NOT NULL,
 created           timestamp        NOT NULL default '0000-00-00 00:00:00',
 created_IP        varchar(100)     NOT NULL default '',
 created_agent     text             NOT NULL,
 created_user_id   bigint(20)       UNSIGNED NOT NULL default 0,
 created_country   char(2)          NOT NULL default 'ZZ',
 created_US_state  char(2)          NOT NULL default 'ZZ',
 laststatus        timestamp        NOT NULL default '0000-00-00 00:00:00',
 laststatus_IP     varchar(100)     NOT NULL default '',
 laststatus_agent  text             NOT NULL,
 laststatus_user_id bigint(20)      UNSIGNED NOT NULL default 0,
 PRIMARY KEY (id),
 KEY status (status)
) $charset_collate;";

$queries[] = 
"CREATE TABLE $wpdb->mp_usermeta (
 meta_id           bigint(20)       NOT NULL auto_increment,
 mp_user_id        bigint(20)       NOT NULL default '0',
 meta_key          varchar(255)     default NULL,
 meta_value        longtext,
 PRIMARY KEY (meta_id),
 KEY mp_user_id (mp_user_id,meta_key)
) $charset_collate;";

$queries[] = 
"CREATE TABLE $wpdb->mp_stats (
 sdate             date             NOT NULL,
 stype             char(1)          NOT NULL,
 slib              varchar(45)      NOT NULL,
 scount            bigint           NOT NULL,
 PRIMARY KEY(stype, sdate, slib)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($queries);



// some clean up
$wpdb->query( "DELETE FROM $wpdb->mp_mails    WHERE status = '' AND theme <> '';" );
$wpdb->query( "DELETE FROM $wpdb->mp_mailmeta WHERE mp_mail_id NOT IN ( SELECT id FROM $wpdb->mp_mails );" );
$wpdb->query( "DELETE FROM $wpdb->mp_usermeta WHERE mp_user_id NOT IN ( SELECT id FROM $wpdb->mp_users );" );
$wpdb->query( "DELETE FROM $wpdb->mp_usermeta WHERE meta_value NOT IN ( SELECT id FROM $wpdb->mp_mails ) AND meta_key = '_MailPress_mail_sent' ;" );

$wpdb->query( "UPDATE $wpdb->mp_mailmeta SET meta_key = '_MailPress_attached_file' WHERE meta_key = '_mp_attached_file';" );