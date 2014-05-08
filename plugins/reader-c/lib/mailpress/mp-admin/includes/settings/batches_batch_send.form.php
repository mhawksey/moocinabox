<?php

$xevery = array (	30 	=> sprintf(__('%1$s seconds', MP_TXTDOM), '30'), 
			45 	=> sprintf(__('%1$s seconds', MP_TXTDOM), '45'), 
			60 	=> sprintf(__('%1$s minute' , MP_TXTDOM) , ''), 
			120 	=> sprintf(__('%1$s minutes', MP_TXTDOM), '2'), 
			300 	=> sprintf(__('%1$s minutes', MP_TXTDOM), '5'), 
			900 	=> sprintf(__('%1$s minutes', MP_TXTDOM), '15'), 
			1800 	=> sprintf(__('%1$s minutes', MP_TXTDOM), '30'), 
			3600 	=> sprintf(__('%1$s hour', 	MP_TXTDOM), '') ); 

if (!isset($batch_send)) $batch_send = get_option(MailPress_batch_send::option_name);
?>
<tr valign='top'>
	<th style='padding:0;'><strong><?php _e('Sending Mails', MP_TXTDOM); ?></strong></th>
	<td></td>
</tr>
<tr valign='top'>
	<th scope='row'><?php _e('Max mails sent per batch', MP_TXTDOM); ?></th>
	<td class='field'>
		<select name='batch_send[per_pass]'>
<?php MP_AdminPage::select_number(1, 10, $batch_send['per_pass'], 1);?>
<?php MP_AdminPage::select_number(11, 100, $batch_send['per_pass'], 10);?>
<?php MP_AdminPage::select_number(101, 1000, $batch_send['per_pass'], 100);?>
<?php MP_AdminPage::select_number(1001, 10000, $batch_send['per_pass'], 1000);?>
		</select>
	</td>
</tr>
<tr valign='top'>
	<th scope='row'><?php _e('Max retries', MP_TXTDOM); ?></th>
	<td class='field'>
		<select name='batch_send[max_retry]'  style='width:4em;'>
<?php MP_AdminPage::select_number(0, 5, $batch_send['max_retry']);?>
		</select>
	</td>
</tr>
<tr valign='top'>
	<th scope='row'><?php _e('Submit batch with', MP_TXTDOM); ?></th>
	<td>
		<table class='general'>
			<tr>
				<td class='pr10'>
					<label for='batch_send_wp_cron'>
						<input value='wpcron' name='batch_send[batch_mode]' id='batch_send_wp_cron' class='submit_batch tog' type='radio' <?php checked('wpcron', $batch_send['batch_mode']); ?> />
						&#160;&#160;
						<?php _e('WP_Cron', MP_TXTDOM); ?>
					</label>
				</td>
				<td class='wpcron pr10 toggl2<?php if ('wpcron' != $batch_send['batch_mode']) echo ' hide'; ?>' style='padding-left:10px;vertical-align:bottom;'>
					<?php _e('Every', MP_TXTDOM); ?>
					&#160;&#160;
					<select name='batch_send[every]' id='every' >
<?php MP_AdminPage::select_option($xevery, $batch_send['every']);?>
					</select>
				</td>
			</tr>
			<tr>
				<td class='pr10'>
					<label for='batch_send_other'>
						<input value='other' name='batch_send[batch_mode]' id='batch_send_other' class='submit_batch tog' type='radio' <?php checked('other', $batch_send['batch_mode']); ?> />
						&#160;&#160;
						<?php _e('Other', MP_TXTDOM); ?>
					</label>
				</td>
				<td class='other pr10 toggl2<?php if ('other' != $batch_send['batch_mode']) echo ' hide'; ?>' >
					<?php printf(__('see sample in "%1$s"', MP_TXTDOM), '<code>' . MP_CONTENT_DIR . 'xtras/mp_batch_send' . '</code>'); ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr valign='top' style='line-height:10px;padding:0;'><td style='line-height:10px;padding:0;'>&#160;</td></tr>
<tr valign='top' class='mp_sep' style='line-height:2px;padding:0;'><td style='line-height:2px;padding:0;'></td></tr>
<tr><th></th><td colspan='4'></td></tr>