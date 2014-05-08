<?php
abstract class MP_adminpage_ extends MP_
{
	function __construct()
	{
		if ( !current_user_can(MP_AdminPage::capability) ) 
			wp_die(__('You do not have sufficient permissions to access this page.'));

		add_action('admin_init',      		array('MP_AdminPage', 'redirect'));
		add_action('admin_init',      		array('MP_AdminPage', 'title'));

		add_action('admin_head',      		array('MP_AdminPage', 'screen_meta'));
		add_filter('screen_meta_screen', 		array('MP_AdminPage', 'screen_meta_screen'));
		add_filter('current_screen', 			array('MP_AdminPage', 'current_screen'), 8, 1);

		add_action('admin_print_styles', 		array('MP_AdminPage', 'print_styles'));
		add_action('admin_print_scripts' , 		array('MP_AdminPage', 'print_header_scripts'));
		add_action('admin_print_footer_scripts' , array('MP_AdminPage', 'print_footer_scripts'));

		add_action('wp_print_scripts', 		array('MP_AdminPage', 'deregister_scripts'), 100);
		add_action('wp_print_footer_scripts', 	array('MP_AdminPage', 'deregister_scripts'), 100);
	}

////  Redirect  ////

	public static function redirect() {}

////  Title  ////

	public static function title() {}

//// Screen Options ////

	public static function screen_meta() 
	{
		global $current_screen;

		$current_screen->add_help_tab( array(
			'id'        => 'overview',
			'title'        => __('Overview'),
			'content'    =>
				  '<p><strong>' . __( 'For more information:' ) . '</strong></p>'
				. '<p>' . sprintf(__('<a href="%1$s" target="_blank">Tutorials</a>', MP_TXTDOM), (MP_AdminPage::help_url) ? MP_AdminPage::help_url : 'http://blog.mailpress.org/tutorials/' ) . '</p>'
				. '<p>' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>', MP_TXTDOM), 'http://groups.google.com/group/mailpress') . '</p>'
			)
		);

		$badge = (rand(0, 1)) ? 'mailpress_badge.gif' : 'logo_lmailpress_admin.png" style="width:125px;';

		$current_screen->set_help_sidebar(
			  '<p><strong>' . __( 'Please Donate :', MP_TXTDOM ) . '</strong></p>'
			. '<div style="text-align:center;">'
			. '<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick" /><input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----" /><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" /><img alt="" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" /></form>' 
			. '</div>'
			. '<p>' . __('and support', MP_TXTDOM ) . '</p>'
			. '<div style="text-align:center;">'
			. '<img src="' . site_url() . '/' . MP_PATH . 'mp-includes/images/' . $badge . '" alt="" />'
			. '</div>'
		);
	}

	public static function screen_meta_screen()
	{
		return MP_AdminPage::screen;
	}

	public static function current_screen($current_screen)
	{
		$current_screen->id = MP_AdminPage::screen;
		$current_screen->post_type = '';
		return $current_screen;
	}

//// Styles ////

	public static function print_styles($styles = array()) 
	{
		$styles = apply_filters('MailPress_styles', $styles, MP_AdminPage::screen);
		if (is_array($styles)) foreach ($styles as $style) wp_enqueue_style($style);
	}

//// Scripts ////

	public static function print_header_scripts() { MP_AdminPage::print_scripts(array(), false); }
	public static function print_footer_scripts() { MP_AdminPage::print_scripts(array(), true); }

	public static function print_scripts($scripts = array()) 
	{
		$scripts = apply_filters('MailPress_scripts', $scripts, MP_AdminPage::screen);
		if (is_array($scripts)) foreach ($scripts as $script)	wp_enqueue_script($script);
	}

	public static function deregister_scripts()
	{
		$root = MP_CONTENT_DIR . 'advanced/scripts';
		$root = apply_filters('MailPress_advanced_scripts_root', $root);
		$file	= "$root/deregister.xml";

		$y = '';

		if (is_file($file))
		{
			$x = file_get_contents($file);
			if ($xml = simplexml_load_string($x))
			{
				foreach ($xml->script as $script)
				{
					wp_deregister_script((string) $script);
					$y .= (!empty($y)) ? ", $script" : $script;
				}
			}
			echo "\n<!-- MailPress_deregister_scripts : $y -->\n";
		}
	}

////  Body  ////

	public static function body() { include(MP_ABSPATH . 'mp-admin/includes/' . basename(MP_AdminPage::file)); }

//// Html ////

	public static function get_url_parms($parms = array('mode', 'status', 's', 'paged', 'author', 'startwith'))
	{
		$url_parms = array();
		foreach ($parms as $parm) if (isset($_REQUEST[$parm]))
		{
			if (isset($_REQUEST[$parm]))
			{
				$url_parms[$parm] = trim(stripslashes($_REQUEST[$parm]));
				switch ($parm)
				{
					case 'startwith' :
						if (-1 == $url_parms[$parm]) 	unset($url_parms[$parm]);
					break;
					case 'paged' :
						if (1 >= $url_parms[$parm]) 	unset($url_parms[$parm]);
					case 's' :
					case 'author' :
					case 'mailinglist' :
					case 'newsletter' :
						if (empty($url_parms[$parm])) unset($url_parms[$parm]);
					break;
				}
			}
		}
		return $url_parms;
	}

	public static function post_url_parms($url_parms, $parms = array('mode', 'status', 's', 'paged', 'author'))
	{
		foreach ($parms as $key)
			if (isset($url_parms[$key]))
				echo "<input type='hidden' name='$key' value=\"" . $url_parms[$key] . "\" />\n";
	}

	public static function message($s, $b = true)
	{
		if ( $b ) 	echo "<div id='message' class='updated fade'><p>$s</p></div>";
	 	else 		echo "<div id='message' class='error'><p>$s</p></div>";
	}
}