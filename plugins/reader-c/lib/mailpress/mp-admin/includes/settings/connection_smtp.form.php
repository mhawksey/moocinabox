<?php
$xssl = array (	''	=> __('No', MP_TXTDOM),
			'ssl'	=> 'SSL' ,
			'tls'	=> 'TLS' 
); 
$xport = array (	'25'		=> __('Default SMTP Port', MP_TXTDOM),
			'465'		=> __('Use for SSL/TLS/GMAIL', MP_TXTDOM),
			'custom'	=> __('Custom Port: (Use Box)', MP_TXTDOM)
); 

if (!isset($connection_smtp)) $connection_smtp = get_option(MailPress::option_name_smtp);

$connection_smtp['customport']='';
if (isset($connection_smtp['port']) && !in_array($connection_smtp['port'], array(25, 465))) 
{
	$connection_smtp['customport'] = $connection_smtp['port']; 
	$connection_smtp['port'] = 'custom';
}

if (isset($pophostclass)) $connection_smtp['smtp-auth'] = '@PopB4Smtp';

$formname = substr(basename(__FILE__), 0, -4); 
?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table'>
		<tr valign='top'<?php if (isset($serverclass)) echo " class='form-invalid'"; ?>>
			<th scope='row'>
				<?php _e('SMTP Server', MP_TXTDOM); ?>  
			</th>
			<td colspan='2'>
				<input type='text' size='25' name='connection_smtp[server]' value='<?php echo (isset($connection_smtp['server'])) ? esc_attr($connection_smtp['server']) : ''; ?>' />
			</td>
		</tr>
		<tr<?php if (isset($usernameclass)) echo " class='form-invalid'"; ?>>
			<th>
				<?php _e('Username', MP_TXTDOM); ?>  
			</th>
			<td colspan='2'>
				<input type='text' size='25' name='connection_smtp[username]' value='<?php echo (isset($connection_smtp['username'])) ? esc_attr($connection_smtp['username']) : ''; ?>' />
			</td>
		</tr>
		<tr>
			<th>
				<?php _e('Password', MP_TXTDOM); ?>   
			</th>
			<td colspan='2'>
				<input type='password' size='25' name='connection_smtp[password]' value="<?php echo (isset($connection_smtp['password'])) ? esc_attr($connection_smtp['password']) : ''; ?>" />
			</td>
		</tr>
		<tr>
			<th>
				<?php _e('Use SSL or TLS ?', MP_TXTDOM); ?>   
			</th>
			<td colspan='2'<?php if (isset($customportclass)) echo " class='form-invalid'"; ?>>
				<select name='connection_smtp[ssl]'>
<?php MP_AdminPage::select_option($xssl,$connection_smtp['ssl']);?>
				</select>
				&#160;
<i><?php printf( __('Site registered socket transports are : <b>%1$s</b>', MP_TXTDOM), (array() == stream_get_transports()) ? __('none', MP_TXTDOM) : implode('</b>, <b>',stream_get_transports())); ?></i>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e('Port', MP_TXTDOM); ?>   
			</th>
			<td colspan='2'>
				<select name='connection_smtp[port]'>
<?php MP_AdminPage::select_option($xport,$connection_smtp['port']);?>
				</select>
				&#160;
				<input type='text' size='4' name='connection_smtp[customport]' value='<?php echo $connection_smtp['customport']; ?>' />
			</td>
		</tr>
		<tr>
			<th>
				<label for='smtp-auth'><?php _e('Pop before Smtp', MP_TXTDOM); ?></label>
			</th>
			<td> 
				<input type='checkbox' name='connection_smtp[smtp-auth]' id='smtp-auth' value='@PopB4Smtp'<?php if (isset($connection_smtp['smtp-auth'])) checked($connection_smtp['smtp-auth'], '@PopB4Smtp'); ?> />
			</td>
			<td id='POP3'<?php  echo (isset($connection_smtp['smtp-auth']) && ('@PopB4Smtp' == $connection_smtp['smtp-auth'])) ? '' : " style='display:none;'"; if (isset($pophostclass)) echo " class='form-invalid'"; ?>> 
				<?php _e("POP3 hostname", MP_TXTDOM); ?>
				&#160;&#160;
				<input type='text' size='25' name='connection_smtp[pophost]' value='<?php if (isset($connection_smtp['pophost'])) echo esc_attr($connection_smtp['pophost']); ?>' />
				<?php _e("port", MP_TXTDOM); ?>
				&#160;&#160;
				<input type='text' size='4' name='connection_smtp[popport]'  value='<?php if (isset($connection_smtp['popport'])) echo $connection_smtp['popport']; ?>' />
			</td>
		</tr>
	</table>
<?php MP_AdminPage::save_button(); ?>
</form>