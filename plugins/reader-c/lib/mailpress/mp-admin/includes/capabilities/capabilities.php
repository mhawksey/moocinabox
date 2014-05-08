<?php

/* Capabilities */

$capabilities	= array(	'MailPress_edit_dashboard' 	=> array(	'name'	=> __('Dashboard', MP_TXTDOM),
												'group'	=> 'admin'
											),

					'MailPress_manage_options'	=> array(	'name'	=> __('Settings', MP_TXTDOM),
												'group'	=> 'admin',
												'menu'	=> 99,

												'parent'	=> 'options-general.php',
												'page_title'=> __('MailPress Settings', MP_TXTDOM),
												'menu_title'=> 'MailPress',
												'page'	=> MailPress_page_settings,
												'func'	=> array('MP_AdminPage', 'body')
											),

					'MailPress_edit_mails'		=> array(	'name'	=> __('Mails', MP_TXTDOM),
												'group'	=> 'mails',
												'menu'	=> 1,
												'admin_bar'	=> __('Mails', MP_TXTDOM),

												'parent'	=> false,
												'page_title'=> __('Mails', MP_TXTDOM),
												'menu_title'=> __('All Mails', MP_TXTDOM),
												'page'	=> MailPress_page_mails,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_edit_others_mails' => array(	'name'	=> __('Edit others mails', MP_TXTDOM),
												'group'	=> 'mails'
											),
					'MailPress_send_mails'		=> array(	'name'	=> __('Send mails', MP_TXTDOM),
												'group'	=> 'mails'
											),
					'MailPress_delete_mails'	=> array(	'name'	=> __('Delete mails', MP_TXTDOM),
												'group'	=> 'mails'
											),
					'MailPress_archive_mails'	=> array(	'name'  	=> __('Archive mails', MP_TXTDOM), 
												'group'	=> 'mails'
											),
					'MailPress_mail_custom_fields'=> array(	'name'	=> __('Custom fields', MP_TXTDOM), 
												'group'	=> 'mails'
											),


					'MailPress_switch_themes'	=> array(	'name'	=> __('Themes', MP_TXTDOM),
												'group'	=> 'admin',
												'menu'	=> 45,

												'parent'	=> false,
												'page_title'=> __('MailPress Themes', MP_TXTDOM),
												'menu_title'=> '&#160;' . __('Themes', MP_TXTDOM),
												'page'	=> MailPress_page_themes,
												'func'	=> array('MP_AdminPage', 'body')
											),

					'MailPress_edit_users'		=> array(	'name'	=> __('Edit users', MP_TXTDOM),
												'group'	=> 'users',
												'menu'	=> 50,
												'admin_bar'	=> __('Users', MP_TXTDOM),

												'parent'	=> false,
												'page_title'=> __('MailPress Users', MP_TXTDOM),
												'menu_title'=> __('All Users', MP_TXTDOM),
												'page'	=> MailPress_page_users,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_delete_users'	=> array(	'name'	=> __('Delete users', MP_TXTDOM),
												'group'	=> 'users'
											),
					'MailPress_user_custom_fields'=> array(	'name'	=> __('Custom fields', MP_TXTDOM), 
												'group'	=> 'users'
											),

					'MailPress_manage_addons'	=> array(	'name'	=> __('Add-ons', MP_TXTDOM),
												'group'	=> 'admin',
												'menu'	=> 99,

												'parent'	=> 'plugins.php',
												'page_title'=> __('MailPress Add-ons', MP_TXTDOM),
												'menu_title'=> __('MailPress Add-ons', MP_TXTDOM),
												'page'	=> MailPress_page_addons,
												'func'	=> array('MP_AdminPage', 'body')
											)
);