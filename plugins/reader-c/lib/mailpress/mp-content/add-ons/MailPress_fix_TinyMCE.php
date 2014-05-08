<?php
if (class_exists('MailPress') && !class_exists('MailPress_fix_TinyMCE') && (is_admin()) )
{
/*
Plugin Name: MailPress_fix_TinyMCE
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/fix_tinymce/
Description: New Mail : Fix TinyMCE with Viper Video Quicktags and Cforms
Author: Amaury Balmer, Andre Renaut
Version: 5.4
Author URI: http://www.herewithme.fr
*/

add_action('init', 'MailPress_fix_TinyMCE', 999 );

function MailPress_fix_TinyMCE() 
{
	$page = MailPress::get_admin_page();
	if ( ($page != MailPress_page_write) || ($page != MailPress_page_edit) )
		return;

	// Viper video quicktags
	global $VipersVideoQuicktags; 
	
	$Viper_mce_buttons = ( 1 == $VipersVideoQuicktags->settings['tinymceline'] ) ? 'mce_buttons' : 'mce_buttons_' . $VipersVideoQuicktags->settings['tinymceline'];
	remove_filter($Viper_mce_buttons, 		array(&$VipersVideoQuicktags, 'mce_buttons') );
	remove_filter('mce_external_plugins', 	array(&$VipersVideoQuicktags, 'mce_external_plugins') );
	remove_filter('tiny_mce_version', 		array(&$VipersVideoQuicktags, 'tiny_mce_version') );
	remove_action('edit_form_advanced', 	array(&$VipersVideoQuicktags, 'AddQuicktagsAndFunctions') );
	remove_action('edit_page_form', 		array(&$VipersVideoQuicktags, 'AddQuicktagsAndFunctions') );

	// Cforms
	remove_filter('mce_buttons', 			'cforms_button');
	remove_filter('mce_external_plugins', 	'cforms_plugin');
}
}