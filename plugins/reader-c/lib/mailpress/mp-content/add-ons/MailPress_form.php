<?php
if (class_exists('MailPress') && !class_exists('MailPress_form') )
{
/*
Plugin Name: MailPress_form
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/form/
Description: Forms (for contact forms only !)
Version: 5.4
*/

/** for admin plugin pages */
define ('MailPress_page_forms',	'mailpress_forms');
define ('MailPress_page_fields',	MailPress_page_forms . '&file=fields');
define ('MailPress_page_templates',	MailPress_page_forms . '&file=templates');

/** for admin plugin urls */
$mp_file = 'admin.php';
define ('MailPress_forms',  	$mp_file . '?page=' . MailPress_page_forms);
define ('MailPress_fields', 	$mp_file . '?page=' . MailPress_page_fields);
define ('MailPress_templates',$mp_file . '?page=' . MailPress_page_templates);

/** for mysql */
global $wpdb;
$wpdb->mp_forms  = $wpdb->prefix . 'mailpress_forms';
$wpdb->mp_fields = $wpdb->prefix . 'mailpress_formfields';

class MailPress_form
{
	const prefix = 'mp_';

	function __construct()
	{

// for shortcode
		add_shortcode('mailpress_form', 	array(__CLASS__, 'shortcode'));

// for field_type captcha_gd
		add_action('mp_action_1ahctpac',	array(__CLASS__, 'mp_action_captcha_gd1'));
		add_action('mp_action_2ahctpac',	array(__CLASS__, 'mp_action_captcha_gd2'));

// for wp admin
		if (is_admin())
		{
		// install
			register_activation_hook(plugin_basename(__FILE__), 	array(__CLASS__, 'install'));
		// for role & capabilities
			add_filter('MailPress_capabilities', 		array(__CLASS__, 'capabilities'), 1, 1);
		// for load admin page
			add_filter('MailPress_load_admin_page', 		array(__CLASS__, 'load_admin_page'), 10, 1);
		}

// for ajax
		add_action('mp_action_add_form', 	array(__CLASS__, 'mp_action_add_form'));
		add_action('mp_action_delete_form', array(__CLASS__, 'mp_action_delete_form'));
		add_action('mp_action_dim_form', 	array(__CLASS__, 'mp_action_dim_form'));
		add_action('mp_action_add_field', 	array(__CLASS__, 'mp_action_add_field'));
		add_action('mp_action_delete_field',array(__CLASS__, 'mp_action_delete_field'));
		add_action('mp_action_dim_field', 	array(__CLASS__, 'mp_action_dim_field'));

		add_action('mp_action_ifview', 	array(__CLASS__, 'mp_action_ifview'));
	}

////  Shortcode  ////

	public static function shortcode($options=false)
	{
		return MP_Form::form($options['id']);
	}

////  Captcha's  ////

	public static function mp_action_captcha_gd1()
	{
		include (MP_ABSPATH . 'mp-includes/class/options/form/field_types/captcha_gd1/captcha/captcha.php');
	}
	public static function mp_action_captcha_gd2()
	{
		include (MP_ABSPATH . 'mp-includes/class/options/form/field_types/captcha_gd2/captcha/captcha.php');
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// install
	public static function install() 
	{
		wp_clear_scheduled_hook('mp_purge_form');
		include (MP_ABSPATH . 'mp-admin/includes/install/form.php');
	}
	
// for role & capabilities
	public static function capabilities($capabilities)
	{
		$capabilities['MailPress_manage_forms'] = array(	'name'  	=> __('Forms', MP_TXTDOM),
											'group' 	=> 'admin',
											'menu'  	=> 90,
	
											'parent'	=> false,
											'page_title'=> __('MailPress Forms', MP_TXTDOM),
											'menu_title'=> __('Forms', MP_TXTDOM),
											'page'  	=> MailPress_page_forms,
											'func'	=> array('MP_AdminPage', 'body')
									);
		return $capabilities;
	}

// for load admin page
	public static function load_admin_page($hub)
	{
		$hub[MailPress_page_forms] 	= 'forms';
		$hub[MailPress_page_fields] 	= 'form_fields';
		$hub[MailPress_page_templates]= 'form_templates';
		return $hub;
	}

// for ajax in forms page
	public static function mp_action_add_form() 
	{
		if (!current_user_can('MailPress_manage_forms')) die('-1');

		if ( '' === trim($_POST['label']) )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'form', 
									'id' => new WP_Error( 'label', __('You did not enter a valid label.', MP_TXTDOM) )
								   ) );
			$x->send();
		}
		if ( '' === trim($_POST['settings']['recipient']['toemail']) || !is_email(($_POST['settings']['recipient']['toemail'])) )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'form', 
									'id' => new WP_Error( 'settings[recipient][toemail]', __('You did not enter a valid email.', MP_TXTDOM) )
								   ) );
			$x->send();
		}

		$form = MP_Form::insert( $_POST );

		if ( !$form )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'form', 
									'id'   => $form
								  ) );
			$x->send();
		}

		if ( !$form || (!$form = MP_Form::get( $form )) ) 	MP_::mp_die('0');

		$form = MP_Form::get($form->id);

		include (MP_ABSPATH . 'mp-admin/forms.php');
		$x = new WP_Ajax_Response( array(	'what' => 'form', 
								'id' => $form->id, 
								'data' => MP_AdminPage::get_row( $form->id, array() ), 
								'supplemental' => array('name' => $form->description, 'show-link' => sprintf(__( 'form <a href="#%s">%s</a> added' , MP_TXTDOM), 'form-' . $form->id, $form->description))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_dim_form() // duplicate
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;

		$form = MP_Form::duplicate($id);

		if ( !$form )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'form', 
									'id' => new WP_Error( __CLASS__ . '::mp_action_dim_form', __('Problems trying to duplicate form.', MP_TXTDOM), array( 'form' => 'form_description' ) ), 
								  ) );
			$x->send();
		}

		if ( !$form || (!$form = MP_Form::get( $form )) ) 	MP_::mp_die('0');

		include (MP_ABSPATH . 'mp-admin/forms.php');
		$x = new WP_Ajax_Response( array(	'what' => 'form', 
								'id' => $form->id, 
								'data' => MP_AdminPage::get_row( $form->id, array() ), 
								'supplemental' => array('name' => $form->description, 'show-link' => sprintf(__( 'form <a href="#%s">%s</a> added' , MP_TXTDOM), 'form-' . $form->id, $form->description))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_delete_form() 
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MP_::mp_die( MP_Form::delete($id) ? '1' : '0' );
	}

// for ajax in fields page
	public static function mp_action_add_field() 
	{
		if (!current_user_can('MailPress_manage_forms')) die('-1');

		if ( '' === trim($_POST['label']) )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'field', 
									'id' => new WP_Error( 'label', __('You did not enter a valid description.', MP_TXTDOM) )
								   ) );
			$x->send();
		}

		$field = MP_Form_field::insert( $_POST );

		if ( !$field )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'field', 
									'id'   => $field
								  ) );
			$x->send();
		}

		if ( !$field || (!$field = MP_Form_field::get( $field )) ) 	MP_::mp_die('0');

		$form = MP_Form::get($field->form_id);
		if (isset($form->settings['visitor']['mail']) && ($form->settings['visitor']['mail'] != '0'))
			add_filter('MailPress_form_columns_form_fields', array('MP_AdminPage', 'add_incopy_column'), 1, 1);

		new MP_Form_field_types();
		include (MP_ABSPATH . 'mp-admin/form_fields.php');
		$x = new WP_Ajax_Response( array(	'what' => 'field', 
								'id' => $field->id, 
								'data' => MP_AdminPage::get_row( $field->id, array() ), 
								'supplemental' => array('name' => $field->description, 'show-link' => sprintf(__( 'field <a href="#%s">%s</a> added' , MP_TXTDOM), "field-$field->id", $field->description))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_dim_field() // duplicate
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;

		$field = MP_Form_field::duplicate($id);

		if ( is_wp_error($field) )  
		{
			$x = new WP_Ajax_Response( array(	'what' => 'field', 
									'id' => new WP_Error( __CLASS__ . '::mp_action_dim_field', __('Problems trying to duplicate field.', MP_TXTDOM), array( 'form-field' => 'field_description' ) ), 
								  ) );
			$x->send();
		}

		if ( !$field || (!$field = MP_Form_field::get( $field )) ) 	MP_::mp_die('0');

		include (MP_ABSPATH . 'mp-admin/form_fields.php');
		$x = new WP_Ajax_Response( array(	'what' => 'field', 
								'id' => $field->id, 
								'data' => MP_AdminPage::get_row( $field->id, array() ), 
								'supplemental' => array('name' => $field->description, 'show-link' => sprintf(__( 'field <a href="#%s">%s</a> added' , MP_TXTDOM), "field-$field->id", $field->description))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_delete_field() 
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MP_::mp_die( MP_Form_field::delete($id) ? '1' : '0' );
	}

// for preview
	public static function mp_action_ifview()
	{
		$form = MP_Form::get($_GET['id']);

		$form_url = esc_url(admin_url(MailPress_forms . '&action=edit&id=' . $form->id));
		$field_url = esc_url(admin_url(MailPress_fields . '&form_id=' . $form->id));
		$template_url = esc_url(admin_url(MailPress_fields . '&form_id=' . $form->id));

		$actions['form'] 		= "<a href='$form_url' 		class='button'>" . __('Edit form', MP_TXTDOM) . '</a>';
		$actions['field'] 	= "<a href='$field_url' 	class='button'>" . __('Edit fields', MP_TXTDOM) . '</a>';
		$actions['template'] 	= "<a href='$template_url' 	class='button'>" . __('Edit template', MP_TXTDOM) . '</a>';
		$sep = ' / ';

		add_action('admin_init', array(__CLASS__, 'ifview_title'));

		include (MP_ABSPATH . 'mp-includes/html/form.php');
	}

	public static function ifview_title()
	{
		$form = MP_Form::get($_GET['id']);
		global $title; $title = sprintf(__('Preview "%1$s"', MP_TXTDOM), $form->label);
	}
}
new MailPress_form();
}