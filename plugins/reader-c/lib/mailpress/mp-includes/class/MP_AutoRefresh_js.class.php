<?php
class MP_AutoRefresh_js
{
	public static function register_scripts($scripts, $file = false)
	{
		$every   = apply_filters('MailPress_autorefresh_every', 30);

		$checked = checked(isset($_GET['autorefresh']), true, false);
		$time    = (isset($_GET['autorefresh'])) ?  $_GET['autorefresh'] : $every;
		$time    = (is_numeric($time) && ($time > $every)) ? $time : $every;
		$time    = "<input type='text' value='$time' maxlength='5' id='MP_Refresh_every' class='screen-per-page' style='width:5em;'/>";
		$option  = '<h5>' . __('Auto refresh', MP_TXTDOM) . '</h5>';
		$option .= "<div><input id='MP_Refresh' type='checkbox'$checked style='margin:0 5px 0 2px;' /><span class='MP_Refresh'>" . sprintf(__('%1$s Autorefresh %2$s every %3$s sec', MP_TXTDOM), "<label for='MP_Refresh' style='vertical-align:inherit;'>", '</label>', $time) . "</span></div>";

		$_script = 'mp_refresh';
		$localize = array('screen' => MP_AdminPage::screen, 'every' => $every, 'message' => __('Autorefresh in %i% sec', MP_TXTDOM), 'option'	=> $option);
		if ($file)
		{
			$_script = 'mp_refresh_i';
			$localize['iframe'] = 'mp';
			$localize['src']   = get_option('siteurl') . '/' . MP_AdminPage::get_path() . '/' . $_GET['id'];
			$localize['url'] 	 = MP_Action_url;
		}
		$localize['l10n_print_after'] = 'try{convertEntities(adminMpRefreshL10n);}catch(e){};';

		wp_register_script( $_script, 	'/' . MP_PATH . "mp-includes/js/$_script.js", array('schedule'), false, 1);
		wp_localize_script( $_script, 	'adminMpRefreshL10n', $localize );
		$scripts[] = $_script;

		return $scripts;
	}
}