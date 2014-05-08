<?php
class MP_AdminPage extends MP_adminpage_
{
	const screen 		= MailPress_page_settings;
	const capability 	= 'MailPress_manage_options';
	const help_url		= 'http://blog.mailpress.org/tutorials/';
	const file        	= __FILE__;

	public static $first = true;

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, '/' . MP_PATH . 'mp-admin/css/settings.css' );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts($scripts = array()) 
	{
		wp_register_script( 'mp-smtp',	'/' . MP_PATH . 'mp-admin/js/settings_smtp.js', array(), false, 1);

		wp_register_script( self::screen, 	'/' . MP_PATH . 'mp-admin/js/settings.js', array('jquery-ui-tabs', 'mp-smtp'), false, 1);
		wp_localize_script( self::screen, 'MP_AdminPageL10n', array( 'requestFile' => MP_Action_url ) );

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

////  Misc  ////

	public static function save_button()
	{
?>
<p class='submit'>
	<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Save Changes'); ?>' />
</p>
<?php
	}

	public static function logs_sub_form ($name, $data, $headertext)
	{
		if (!isset($data[$name])) $data[$name] = MailPress::$default_option_logs;

		$xlevel = array (		123456789	=> __('No logging', MP_TXTDOM) , 
							0	=> __('simple log', MP_TXTDOM) , 
							1 	=> 'E_ERROR', 
							2 	=> 'E_WARNING', 
							4 	=> 'E_PARSE', 
							8 	=> 'E_NOTICE', 
							16 	=> 'E_CORE_ERROR', 
							32 	=> 'E_CORE_WARNING', 
							64 	=> 'E_COMPILE_ERROR', 
							128 	=> 'E_COMPILE_WARNING', 
							256 	=> 'E_USER_ERROR', 
							512 	=> '* E_USER_WARNING *', 
							1024 	=> 'E_USER_NOTICE', 
							2048 	=> 'E_STRICT', 
							4096 	=> 'E_RECOVERABLE_ERROR', 
							8191 	=> 'E_ALL' );
		if (self::$first)
		{
			self::$first = false;
?>
<tr><th></th><td colspan='4'></td></tr>
<tr valign='top' class='mp_sep'>
	<th scope='row'><strong><?php _e('Logs', MP_TXTDOM); ?></strong></th>
	<td><strong><?php _e('Level', MP_TXTDOM); ?></strong></td>
	<td><strong><?php _e('Days', MP_TXTDOM); ?></strong></td>
	<td><strong><?php _e('Last purge', MP_TXTDOM); ?></strong></td>
</tr>
<?php
		}
?>
<tr valign='top' class='mp_sep'>
	<th scope='row'><strong><?php echo $headertext; ?></strong></th>
	<td>
		<select name='logs[<?php echo $name ?>][level]'>
<?php self::select_option($xlevel, $data[$name]['level']);?>
		</select> 
	</td>
	<td>
		<select name='logs[<?php echo $name ?>][lognbr]'>
<?php self::select_number(1, 10, $data[$name]['lognbr']);?>
		</select>
	</td>
	<td>
		<?php if (!empty($data[$name]['lastpurge'])) echo substr($data[$name]['lastpurge'],0 , 4) . '/' . substr($data[$name]['lastpurge'],4, 2) . '/' . substr($data[$name]['lastpurge'],6, 2); ?>
		<input type='hidden' name='logs[<?php echo $name ?>][lastpurge]' value='<?php echo $data[$name]['lastpurge']; ?>' />
	</td>
</tr>
<?php
	}
}