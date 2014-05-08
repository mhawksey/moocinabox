<tr valign='top' style='line-height:10px;padding:0;'><td colspan='5' style='line-height:10px;padding:0;'>&#160;</td></tr>
<tr valign='top'> 
	<th style='padding:0;'><strong><?php _e('Comments', MP_TXTDOM); ?></strong></th>
	<td>
		<input type='hidden'   name='comment[on]' value='on' />
		<label>
			<input type='checkbox' name='subscriptions[comment_checked]'<?php checked( get_option(MailPress_comment::option) ); ?> />
			&#160;<?php _e('checked by default', MP_TXTDOM); ?>
		</label>
	</td>
	<td colspan='3'></td>
</tr>
<tr valign='top' class='mp_sep' style='line-height:2px;padding:0;'><td colspan='5' style='line-height:2px;padding:0;'></td></tr>
<tr><th></th><td colspan='4'></td></tr>