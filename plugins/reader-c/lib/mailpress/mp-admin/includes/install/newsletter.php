<?php

/* Newsletter install */

global $wpdb, $mp_general;
$mp_general = get_option(MailPress::option_name_general);

//////////////////////////////////
//// Install                  ////
//////////////////////////////////

//	To avoid mailing existing published post
$post_meta = '_MailPress_prior_to_install';
$ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' ;" );
if ($ids) 
	foreach ($ids as $id) 
		if (!get_post_meta($id->ID, $post_meta, true)) add_post_meta($id->ID, $post_meta, 'yes', true);

//////////////////////////////////
//// From older versions      ////
//////////////////////////////////

$older_versions = false;

if (!isset($mp_general['newsletters']))
{
	$x = array('new_post','daily','weekly','monthly');
	$newsletters = array();

	foreach ($x as $n)
	{
		if (isset($mp_general[$n]))
		{
			$older_versions = true;
			$newsletters[$n] = true;
			unset($mp_general[$n]);
		}
	}

	if ($older_versions)
	{
		$mp_general['newsletters'] = $newsletters;
		update_option ('MailPress_general', $mp_general);
	}
}

$x = false;
$x = get_option ('MailPress_daily');
if ($x && !is_array($x)) update_option('MailPress_daily', array('threshold'=>$x));
$x = false;
$x = get_option ('MailPress_weekly');
if ($x && !is_array($x)) update_option('MailPress_weekly', array('threshold'=>$x));
$x = false;
$x = get_option ('MailPress_monthly');
if ($x && !is_array($x)) update_option('MailPress_monthly', array('threshold'=>$x));

//////////////////////////////////
//// Upgrade to MailPress 4.0 ////
//////////////////////////////////

// done in mailpress install

//////////////////////////////////
//// Upgrade to MailPress 5.0 ////
//////////////////////////////////
