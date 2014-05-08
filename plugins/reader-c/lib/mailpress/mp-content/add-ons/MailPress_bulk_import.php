<?php
if (class_exists('MailPress') && !class_exists('MailPress_bulk_import') && (is_admin()) )
{
/*
Plugin Name: MailPress_bulk_import
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/bulk_import/
Description: Users : import from mp users list
Author: Daniel Caleb &amp; Andre Renaut
Version: 5.4
Author URI: http://galerie-eigenheim.de
*/

class MailPress_bulk_import
{
	function __construct()
	{
		add_action('MailPress_users_addon', 		array(__CLASS__, 'form'));
		add_action('MailPress_users_addon_update',	array(__CLASS__, 'process_form'));

// for wp admin
		if (is_admin() && current_user_can('MailPress_manage_mailinglists')) 	add_filter('MailPress_scripts', array(__CLASS__, 'scripts'), 8, 2);
	}

	public static function scripts($scripts, $screen) 
	{
		if ( MailPress_page_users != MailPress::get_admin_page() ) return $scripts;

		wp_register_script( 'mp-import', '/' . MP_PATH . 'mp-includes/js/mp_mailinglist_dropdown.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-import', 	'mp_ml_select_L10n', array(
			'error' => __('Please, choose a mailinglist', MP_TXTDOM), 
			'select' => 'bulk_import_mailinglist', 
			'form'   => 'bulk-add',
			'l10n_print_after' => 'try{convertEntities(mp_ml_select_L10n);}catch(e){};' 
		));

		$scripts[] = 'mp-import';

		return $scripts;
	}

	public static function form($url_parms)
	{
?>
<!-- MailPress_bulk_import -->
<form id='bulk-add' action='' method='post' style="z-index:2000">
	<input type='text'   name='emails'   value='' id='emails' onclick="document.getElementById('bulk-add').style.width='90%';document.getElementById('emails').style.width='70%';document.getElementById('radios').style.display='block';" />
	<input type='submit' name='bulk_add' value='<?php _e('Bulk Add', MP_TXTDOM ); ?>' class='button' />
	<div id="radios" style="display:none">

<?php if (current_user_can('MailPress_manage_mailinglists')) MP_Mailinglist::dropdown(array('name' => 'bulk_import_mailinglist', 'htmlid' => 'bulk_import_mailinglist', 'hierarchical' => true, 'orderby' => 'name', 'hide_empty' => '0', 'show_option_none' => __('Choose mailinglist', MP_TXTDOM))); ?>

		<label for='bulki-activate'><input type='radio' id='bulki-activate' name='activate' value='activate' /><?php _e('Activate', MP_TXTDOM); ?></label>
		<label for='bulki-waiting'><input type='radio'  id='bulki-waiting'  name='activate' value='waiting' checked='checked' /> <?php _e('Require Authorization', MP_TXTDOM); ?></label>
		<span style="color:#f00;padding-left:50px;">
			<?php _e('email,name;email,name;...', MP_TXTDOM); ?>
		</span>
	</div>
<?php MP_AdminPage::post_url_parms($url_parms, array('mode', 'status', 'paged', 'author', 'mailinglist', 'newsletter')); ?>
</form>
<br />
<!-- MailPress_bulk_import -->
<?php
	}

	public static function process_form()
	{
		if (!(isset($_POST['bulk_add']))) return;
		if ((empty($_POST['emails']))) return;

		global $wpdb;

		$count_emails	= self::bulk_users($_POST['emails'], $_POST['activate']);

		MP_AdminPage::message( sprintf( _n( __('%s subscriber added', MP_TXTDOM), __('%s subscribers added', MP_TXTDOM), $count_emails ), $count_emails ) );
	}

	public static function bulk_users($mails, $type)
	{
		$count = 0;
		$email_array 	= explode(';', $mails);

		foreach ($email_array as $email_name) 
		{
			$x = explode(',', $email_name);

			$email = trim($x[0]);
			$name = (isset($x[1])) ? trim($x[1]) : '';

			if (is_email($email) && ('deleted' == MP_User::get_status_by_email($email)))
			{
				if ('activate' == $type) 
				{
					MP_User::insert($email, $name, array('status' => 'active'));
					$count++;
				}
				else
				{
					$return = MP_User::add($email, $name);
					if ($return['result']) $count++;
				}

				if (current_user_can('MailPress_manage_mailinglists'))
				{
					$mp_user_id  = MP_User::get_id_by_email($email);
					MP_Mailinglist::set_object_terms(MP_User::get_id_by_email($email), (is_array($_POST['bulk_import_mailinglist'])) ? $_POST['bulk_import_mailinglist'] : array($_POST['bulk_import_mailinglist']) );
				}
			}
			else
			{
				if (current_user_can('MailPress_manage_mailinglists'))
				{
					$mp_user_id  = MP_User::get_id_by_email($email);

					$mls = array();
					$mls = MP_Mailinglist::get_object_terms($mp_user_id);
					$mls[] = $_POST['bulk_import_mailinglist'];
					$mls = MP_Mailinglist::set_object_terms($mp_user_id, $mls);
				}
			}
		}
		return $count;
	}
}
new MailPress_bulk_import();
}