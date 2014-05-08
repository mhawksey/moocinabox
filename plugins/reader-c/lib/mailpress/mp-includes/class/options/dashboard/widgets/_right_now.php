<?php
class MP_Dashboard__right_now extends MP_dashboard_widget_
{
	var $id = 'mp__right_now';

	function widget()
	{
		global $wpdb, $wp_locale;

		$countm = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype='t';");
		$counts = $wpdb->get_var("SELECT count(*)    FROM $wpdb->mp_users WHERE status='active';");
		if (!$countm) $countm = 0;
		if (!$counts) $counts = 0;

		$plugin_data = get_plugin_data( MP_ABSPATH . 'MailPress.php' );
		$plugin_version = $plugin_data['Version'];

		$th = new MP_Themes();
		$themes = $th->themes; 
		$ct = $th->current_theme_info(); 
?>
<div id="dashboard_right_now">
<div class="inside">
	<div class="table table_content">
		<table>
			<tr class='first'>
				<td class="first b b-posts">
<?php 	if (current_user_can('MailPress_edit_mails')) : ?>
					<a href="<?php echo MailPress_mails; ?>"><?php echo $countm; ?></a>
<?php 	else : ?>
					<?php echo $countm; ?>
<?php 	endif; ?>
				</td>
				<td class="t posts"><?php echo( _n( __('Mail sent', MP_TXTDOM), __('Mails sent', MP_TXTDOM), $countm )); ?></td>
			</tr>
		</table>
	</div>
	<div class="table table_discussion">
		<table>
			<tr class='first'>
				<td class="b b-comments">
<?php 	if (current_user_can('MailPress_edit_users')) : ?>
					<a href="<?php echo MailPress_users; ?>"><?php echo $counts; ?></a>
<?php 	else : ?>
					<?php echo $counts; ?>
<?php 	endif; ?>
				</td>
				<td class="last t approved"><?php echo(_n( __('Active subscriber', MP_TXTDOM), __('Active subscribers', MP_TXTDOM), $counts )); ?></td>
			</tr>
		</table>
	</div>
	<div class="versions">
		<p>
<?php 	
		$theme_title = (current_user_can('MailPress_switch_themes')) ? "<a href='<?php echo MailPress_themes; ?>'>{$ct->title}</a>" : $ct->title;
		printf(__('Theme %s', MP_TXTDOM),"<span class='b'>" . $theme_title . "</span>");
?>
		</p>
	</div>
	<div style='float:right;'>
		<table>
			<tr>
				<td>
					<span id='mp_paypal' style='float:right;padding:0;margin:0;'>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick" /><input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----" /><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" /><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" /></form>
					</span>
				</td>
			</tr>
		</table>
	</div>
	<div>
		<table>
			<tr>
				<td>
					<?php printf(__('You are using <strong>MailPress %s</strong>.', MP_TXTDOM), $plugin_version ); ?>
				</td>
			</tr>
		</table>
	</div>
</div>
</div>
<?php
	}
}
new MP_Dashboard__right_now(__( "MailPress - 'Right Now'", MP_TXTDOM ));