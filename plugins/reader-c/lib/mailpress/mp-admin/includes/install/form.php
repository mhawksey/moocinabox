<?php

/* Form install */

global $wpdb;

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
"CREATE TABLE $wpdb->mp_forms (
 id                bigint(20)      NOT NULL auto_increment,
 label             varchar(255)    NOT NULL default '',
 description       varchar(255)    NOT NULL default '',
 template          varchar(50)     NOT NULL default '',
 settings          longtext,
 PRIMARY KEY (id),
 UNIQUE KEY id (id)
) $charset_collate;";

$queries[] =
"CREATE TABLE $wpdb->mp_fields (
 id                bigint(20)      NOT NULL auto_increment,
 form_id           bigint(20)      NOT NULL,
 ordre             bigint(20)      UNSIGNED NOT NULL default 0,
 type              varchar(50)     NOT NULL default '',
 template          varchar(50)     NOT NULL default '',
 label             varchar(255)    NOT NULL default '',
 description       varchar(255)    NOT NULL default '',
 settings          longtext,
 PRIMARY KEY (id),
 UNIQUE KEY id (id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($queries);

//////////////////////////////////
//// Upgrade to MailPress 5.0 ////
//////////////////////////////////

foreach( array($wpdb->mp_forms, $wpdb->mp_fields) as $x)
{
	$rows = $wpdb->get_results( "SELECT id, settings FROM $x;" );
	if ($rows)
	{
		foreach($rows as $row)
		{
			if (unserialize($row->settings)) continue;
			$settings = mysql_real_escape_string(stripslashes($row->settings));
			$wpdb->query( "UPDATE $x SET settings = '$settings' WHERE id = {$row->id};" );
		}
	}
}