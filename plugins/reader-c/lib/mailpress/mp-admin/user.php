<?php
class MP_AdminPage extends MP_adminpage_
{
	const screen 		= 'mailpress_user';
	const capability 	= 'MailPress_edit_users';
	const help_url		= 'http://blog.mailpress.org/tutorials/';
	const file        	= __FILE__;

////  Redirect  ////

	public static function redirect() 
	{
		if (!empty($_REQUEST['action'])) $action = $_REQUEST['action'];
		if (!isset($action)) return;

		if (isset($_GET['id'])) $id = $_GET['id'];

		$list_url = self::url(MailPress_users, self::get_url_parms());

		switch($action) 
		{
			case 'activate' :
				if (MP_User::set_status($id, 'active'))  $list_url .= '&activated=1';
				self::mp_redirect($list_url);
			break;
			case 'deactivate' :
				if (MP_User::set_status($id, 'waiting'))	$list_url .= '&deactivated=1';
				self::mp_redirect($list_url);
			break;
			case 'unbounce' :
				if (MP_User::set_status($id, 'waiting'))
				{
					MP_User_meta::delete($id, '_MailPress_bounce_handling');
					$list_url .= '&unbounced=1';
				}
				self::mp_redirect($list_url);
			break;
			case 'delete' :
				if (MP_User::set_status($id, 'delete'))	$list_url .= '&deleted=1';
				self::mp_redirect($list_url);
			break;

			case 'save' :
				$id = (int) $_POST['id'];

				if ($_POST['mp_user_name'] != $_POST['mp_user_old_name'])
				{
					MP_User::update_name($id, $_POST['mp_user_name']);
				}

				switch (true)
				{
					case isset($_POST['addmeta']) :
						MP_User_meta::add_meta($id);
					break;
					case isset($_POST['usermeta']) :
						foreach ($_POST['usermeta'] as $meta_id => $meta)
						{
							$meta_key = $meta['key'];
							$meta_value = $meta['value'];
							MP_User_meta::update_by_id($meta_id , $meta_key, $meta_value);
						}
					break;
					case isset($_POST['deletemeta']) :
						foreach ($_POST['deletemeta'] as $meta_id => $x)
							MP_User_meta::delete_by_id( $meta_id );
					break;
				}

				// what else ?
				do_action('MailPress_update_meta_boxes_user');

				$parm = "&saved=1";

				$url = MailPress_user;
				$url .= "$parm&id=$id";
				self::mp_redirect($url);
			break;
		} 
	}

////  Title  ////

	public static function title() { global $title; $title = __('MailPress User', MP_TXTDOM); }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style (self::screen, 	'/' . MP_PATH . 'mp-admin/css/user.css', 	array('thickbox') );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts($scripts = array()) 
	{
		// google map
			wp_register_script( 'google-map',	'http://maps.googleapis.com/maps/api/js?sensor=false', false, false, 1);

		// mp-gmap3
			wp_register_script( 'mp-gmap3',	'/' . MP_PATH . 'mp-includes/js/mp_gmap3.js', array('google-map', 'schedule'), false, 1);
			wp_localize_script( 'mp-gmap3', 	'mp_gmapL10n', array(
				'id'		=> $_GET['id'],
				'type'	=> 'mp_user',
				'url'		=> site_url() . '/' . MP_PATH . 'mp-admin/images/',
				'ajaxurl'	=> MP_Action_url,
				'center'	=> esc_js(__('Center', MP_TXTDOM)),
				'changemap'	=> esc_js(__('Change map', MP_TXTDOM))
			));

			$deps[] = 'mp-gmap3';

		wp_register_script( 'mp-ajax-response', 	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 	'wpAjax', array( 	
			'noPerm' => __('Update database failed', MP_TXTDOM), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		));
		$deps[] = 'jquery-ui-tabs';

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 		'wpListL10n', array(
			'url' => MP_Action_url
		));
		$deps[] = 'mp-lists';
		$deps[] = 'postbox';

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);
		$deps[] = 'mp-thickbox';

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/user.js', $deps, false, 1);
		wp_localize_script( self::screen, 		'MP_AdminPageL10n',  array(
			'screen' => self::screen
		));

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

////  Metaboxes  ////

	public static function screen_meta() 
	{
		$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
		add_meta_box('submitdiv', 		__('Save', MP_TXTDOM), array(__CLASS__, 'meta_box_submit'), self::screen, 'side', 'core');

		do_action('MailPress_add_meta_boxes_user', $id, self::screen);

		if ( current_user_can('MailPress_user_custom_fields') )
			add_meta_box('customfieldsdiv', 	__('Custom Fields'), 	array(__CLASS__, 'meta_box_customfields'), self::screen, 'normal', 'core');
		else
		{
			if ($id)
			{
				$metas = MP_User_meta::get($id);
				if ($metas) 
				{
					if (!is_array($metas)) $metas = array($metas);
					foreach ($metas as $meta)
					{
						if ($meta->meta_key[0] == '_') continue;
						add_meta_box('customfieldsdiv', 	__('Custom Fields'), 	array(__CLASS__, 'meta_box_browse_customfields'), self::screen, 'normal', 'core');
						break;
					}
				}
			}
		}

		add_meta_box('IP_info', __('IP info', MP_TXTDOM), array(__CLASS__, 'meta_box_IP_info'), self::screen, 'side', 'core');

		parent::screen_meta();
	}
/**/
	public static function meta_box_submit($mp_user) 
	{
		$url_parms 	= self::get_url_parms();
		if (current_user_can('MailPress_delete_users')) 	$delete_url =   esc_url(self::url(MailPress_user . "&amp;action=delete&amp;id=$mp_user->id",   $url_parms));

		$unbounce_url = esc_url(self::url(MailPress_user . "&amp;action=unbounce&amp;id=$mp_user->id", $url_parms));
		$unbounce_click = "onclick=\"return (confirm('" . esc_js(sprintf( __("You are about to unbounce this MailPress user '%s'\n  'Cancel' to stop, 'OK' to unbounce.", MP_TXTDOM), $mp_user->id )) . "'));\"";
		if ('bounced' == $mp_user->status) $unbounce = "<a class='button button-highlighted' style='float:left;min-width:80px;text-align:center;' href='$unbounce_url' $unbounce_click>" . __('Unbounce', MP_TXTDOM) . "</a>";

		if (class_exists('MailPress_tracking'))
		{
			$tracking_url = esc_url(self::url(MailPress_tracking_u . "&amp;id=$mp_user->id"));
			$tracking = "<a class='button preview' href='$tracking_url'>" . __('Tracking', MP_TXTDOM) . "</a>";
		}
?>
<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
		<div id="minor-publishing-actions">
   			<span id='unbounce'><?php if (isset($unbounce)) echo $unbounce; ?></span>
			<span id='tracking'><?php if (isset($tracking)) echo $tracking; ?></span>
		</div>
		<div class="clear"><br /><br /><br /><br /><br /></div>
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
<?php 	if ($delete_url) : ?>
			<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="return (confirm('<?php echo(esc_js(sprintf( __("You are about to delete this MailPress user '%s'\n  'Cancel' to stop, 'OK' to delete.", MP_TXTDOM), $mp_user->id ))); ?>'));">
				<?php _e('Delete', MP_TXTDOM); ?>
			</a>
<?php		endif; ?>
		</div>
		<div id="publishing-action">
			<input id='publish' type="submit" name='save' class='button-primary' value="<?php _e('Save', MP_TXTDOM); ?>" />
		</div>
	<div class="clear"></div>
	</div>
</div>
<?php
	}
/**/
	public static function meta_box_browse_customfields($mp_user)
	{
?>
<div id="user-import">
<?php
		$header = true;
		$metas = MP_User_meta::get($mp_user->id);

		if ($metas)
		{
			if (!is_array($metas)) $metas = array($metas);

			foreach ($metas as $meta)
			{
				if ($meta->meta_key[0] == '_') continue;
	
				if ($header)
				{
					$header = false;
?>
	<table class='form-table'>
		<thead>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'>
				</td>
				<td style='border-bottom:none;padding:0px;'>
					<b><?php _e('Key') ?></b>
				</td>
				<td style='border-bottom:none;padding:0px;'>
					<b><?php _e('Value') ?></b>
				</td>
			</tr>
		</thead>
		<tbody>
<?php
				}
?>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
				<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
					<input style='padding:3px;margin:0 10px 0 0;width:250px;' type='text' disabled='disabled' value="<?php echo esc_attr($meta->meta_key); ?>" />
				</td>
				<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
					<input style='padding:3px;margin:0 10px 0 0;width:250px;' type='text' disabled='disabled' value="<?php echo esc_attr($meta->meta_value); ?>" />
				</td>
			</tr>
<?php
			}
		}
	
		if ($header) 	_e('No meta data', MP_TXTDOM);
		else
		{
?>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'>&#160;</td>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
			</tr>
		</tbody>
	</table>
<?php
		}
?>
</div>
<?php
	}
/**/
	public static function meta_box_customfields($mp_user)
	{
?>
<div id='postcustomstuff'>
	<div id='ajax-response'></div>
<?php
		$metadata = MP_User_meta::has($mp_user->id);
		$count = 0;
		if ( !$metadata ) : $metadata = array(); 
?>
	<table id='list-table' style='display: none;'>
		<thead>
			<tr>
				<th class='left'><?php _e( 'Name' ); ?></th>
				<th><?php _e( 'Value' ); ?></th>
			</tr>
		</thead>
		<tbody id='the-list' class='list:usermeta'>
			<tr><td></td></tr>
		</tbody>
	</table>
<?php else : ?>
	<table id='list-table'>
		<thead>
			<tr>
				<th class='left'><?php _e( 'Name' ) ?></th>
				<th><?php _e( 'Value' ) ?></th>
			</tr>
		</thead>
		<tbody id='the-list' class='list:usermeta'>
<?php foreach ( $metadata as $entry ) echo self::meta_box_customfield_row( $entry, $count ); ?>
		</tbody>
	</table>
<?php endif; ?>
<?php
		global $wpdb;
		$keys = $wpdb->get_col( "SELECT meta_key FROM $wpdb->mp_usermeta GROUP BY meta_key ORDER BY meta_key ASC LIMIT 30" );
		foreach ($keys as $k => $v)
		{
			if ($keys[$k][0] == '_') unset($keys[$k]);
		}
?>
	<p>
		<strong>
			<?php _e( 'Add New Custom Field:' ) ?>
		</strong>
	</p>
	<table id='newmeta'>
		<thead>
			<tr>
				<th class='left'>
					<label for='metakeyselect'>
						<?php _e( 'Name' ) ?>
					</label>
				</th>
				<th>
					<label for='metavalue'>
						<?php _e( 'Value' ) ?>
					</label>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id='newmetaleft' class='left'>
<?php 
		if ( $keys ) 
		{ 
?>
					<select id='metakeyselect' name='metakeyselect' tabindex='7'>
						<option value="#NONE#"><?php _e( '- Select -' ); ?></option>
<?php
			foreach ( $keys as $key ) 
			{
				$key = esc_attr($key);
				echo "\n<option value=\"$key\">$key</option>";
			}
?>
					</select>
					<input class='hide-if-js' type='text' id='metakeyinput' name='metakeyinput' tabindex='7' value='' />
					<a href='#postcustomstuff' class='hide-if-no-js' onclick="jQuery('#metakeyinput, #metakeyselect, #enternew, #cancelnew').toggle();return false;">
					<span id='enternew'><?php _e('Enter new'); ?></span>
					<span id='cancelnew' class='hidden'><?php _e('Cancel'); ?></span></a>
<?php 
		} 
		else 
		{ 
?>
					<input type='text' id='metakeyinput' name='metakeyinput' tabindex='7' value='' />
<?php 
		} 
?>
				</td>
				<td>
					<textarea id='metavalue' name='metavalue' rows='2' cols='25' tabindex='8'></textarea>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<div  class='submit'>
						<input type='submit' id='addmetasub' name='addmeta' class='add:the-list:newmeta button' tabindex='9' value="<?php _e( 'Add Custom Field' ) ?>" />
						<?php wp_nonce_field( 'add-usermeta', '_ajax_nonce', false ); ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<p><?php _e('Custom fields can be used to add extra metadata to a user that you can <a href="http://www.mailpress.org" target="_blank">use in your mail</a>.', MP_TXTDOM); ?></p>
<?php
	}

	public static function meta_box_customfield_row( $entry, &$count )
	{
		if ('_' == $entry['meta_key'] { 0 } ) return;

		static $update_nonce = false;
		if ( !$update_nonce ) $update_nonce = wp_create_nonce( 'add-usermeta' );

		$r = '';
		++ $count;

		if ( $count % 2 )	$style = 'alternate';
		else			$style = '';
	
		$entry['meta_key'] 	= esc_attr($entry['meta_key']);
		$entry['meta_value'] 	= esc_attr($entry['meta_value']); // using a <textarea />
		$entry['meta_id'] 	= (int) $entry['meta_id'];

		$delete_nonce 		= wp_create_nonce( 'delete-usermeta_' . $entry['meta_id'] );

		$r .= "
			<tr id='usermeta-{$entry['meta_id']}' class='$style'>
				<td class='left'>
					<label class='hidden' for='usermeta[{$entry['meta_id']}][key]'>
" . __( 'Key' ) . "
					</label>
					<input name='usermeta[{$entry['meta_id']}][key]' id='usermeta[{$entry['meta_id']}][key]' tabindex='6' type='text' size='20' value=\"" . esc_attr($entry['meta_key']) . "\" />
					<div class='submit'>
						<input name='deleteusermeta[{$entry['meta_id']}]' type='submit' class='delete:the-list:usermeta-{$entry['meta_id']}::_ajax_nonce=$delete_nonce deleteusermeta button' tabindex='6' value='" . esc_attr(__( 'Delete' )) . "' />
						<input name='updateusermeta' type='submit' tabindex='6' value='" . esc_attr(__( 'Update' )) . "' class='add:the-list:usermeta-{$entry['meta_id']}::_ajax_nonce=$update_nonce updateusermeta button' />
					</div>
" . wp_nonce_field( 'change-usermeta', '_ajax_nonce', false, false ) . "
				</td>
				<td>
					<label class='hidden' for='usermeta[{$entry['meta_id']}][value]'>
" . __( 'Value' ) . "
					</label>
					<textarea name='usermeta[{$entry['meta_id']}][value]' id='usermeta[{$entry['meta_id']}][value]' tabindex='6' rows='2' cols='30'>" . esc_attr($entry['meta_value']) . "</textarea>
				</td>
			</tr>
			";
		return $r;
	}
/**/
	public static function meta_box_IP_info($mp_user)
	{
	// meta_box_IP_info
		$x = false;
		$ip = ( '' == $mp_user->laststatus_IP) ? $mp_user->created_IP : $mp_user->laststatus_IP;
		$x  = MP_Ip::get_all($ip);

	// meta_box_IP_info_user_settings
		$u['meta_box_IP_info_user_settings'] = MP_User_meta::get($mp_user->id, '_MailPress_meta_box_IP_info');
		if (!$u['meta_box_IP_info_user_settings']) $u['meta_box_IP_info_user_settings'] = get_user_option('_MailPress_meta_box_IP_info');
		$def_lat = (isset($x['geo']['lat']))? $x['geo']['lat'] : 48.8352;
		$def_lng = (isset($x['geo']['lng']))? $x['geo']['lng'] : 2.4718;
		if (!$u['meta_box_IP_info_user_settings']) $u['meta_box_IP_info_user_settings'] = array('center_lat' => $def_lat, 'center_lng' => $def_lng, 'zoomlevel' => 3, 'maptype' => 'NORMAL');
		$u['meta_box_IP_info_user_settings']['prefix'] = 'meta_box_IP_info';
?>
<script type='text/javascript'>
/* <![CDATA[ */
<?php
		$eol = "";
		foreach ( $u as $var => $val ) {
			echo "var $var = " . self::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";
?>
/* ]]> */
</script>
<?php
		if (isset($x['geo']))
		{
?>
<script type='text/javascript'>
/* <![CDATA[ */
<?php
			$m['meta_box_IP_info'] = $x['geo'];
			$eol = "";
			foreach ( $m as $var => $val ) {
				echo "var $var = " . self::print_scripts_l10n_val($val);
				$eol = ", \n\t\t";
			}
			echo ";\n";	
?>
/* ]]> */
</script>
		<div id='meta_box_IP_info_map'></div>
<?php 		foreach($u['meta_box_IP_info_user_settings'] as $k => $v)
			{
	                if ('prefix' == $k) continue;
?>
		<input type='hidden' id='meta_box_IP_info_<?php echo $k; ?>' value="<?php echo $v; ?>" />
<?php
			}
		}

		if (isset($x['html']))
		{
			echo $x['html'];
		}
		if (isset($x['provider']))
		{
			printf("<div><p style='margin:3px;'><i><small>" . '%1$s' . "</small></i></p></div>\n", sprintf(__('ip data provided by %1$s', MP_TXTDOM), sprintf('<a href="%1$s">%2$s</a>', $x['provider']['credit'], $x['provider']['credit'])));
		}
	}
}