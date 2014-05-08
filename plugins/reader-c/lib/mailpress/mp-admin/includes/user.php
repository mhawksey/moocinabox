<?php
global $mp_user;

$url_parms = MP_AdminPage::get_url_parms(array('mode', 'status', 's', 'paged', 'author', 'mailinglist', 'newsletter', 'startwith'));

//
// MANAGING RESULTS
//

if (!isset($_GET['id'])) MP_AdminPage::mp_redirect( MP_AdminPage::url(MailPress_users, $url_parms) );

$mp_user = MP_User::get( $_GET['id'] );
$active  = ('active' == $mp_user->status) ? true : false;

$h2 = sprintf( __('Edit MailPress User # %1$s', MP_TXTDOM), $mp_user->id);

// messages
$message = ''; $err = 0;
if (isset($_GET['saved'])) 	{$err += 0; if (!empty($message)) $message .= '<br />'; $message .= __('User saved', MP_TXTDOM); }
?>
<div class='wrap'>
	<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if ($message) MP_AdminPage::message($message, ($err) ? false : true); ?>
	<form id='mp_user' name='mp_user_form' action='' method='post'>

		<input type='hidden' 				name='action'  		value='save' />

		<input type="hidden" name='id' 		value="<?php echo $mp_user->id ?>" id='mp_user_id' />
		<input type="hidden" name='referredby' 	value='<?php if(isset($_SERVER['HTTP_REFERER'])) echo esc_url($_SERVER['HTTP_REFERER']); ?>' />
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false ); ?>

		<div id='poststuff' class='metabox-holder has-right-sidebar'>
			<div id="side-info-column" class="inner-sidebar">
<?php $side_meta_boxes = do_meta_boxes(MP_AdminPage::screen, 'side', $mp_user); ?>
			</div>

			<div id="post-body" class="<?php echo $side_meta_boxes ? 'has-sidebar' : ''; ?>">
				<div id="post-body-content" class="has-sidebar-content">
					<table class='form-table'>
						<tbody>
							<tr valign='top'>
								<th scope='row' class='h1em'>
									<?php _e('Email', MP_TXTDOM); ?>
								</th>
								<td class='h1em'>
									<input type='text' disabled='disabled' value='<?php echo $mp_user->email; ?>' size='30' />
								</td>
								<td class='mp_avatar' rowspan='2'>
<?php if (get_option('show_avatars')) echo get_avatar( $mp_user->email, 64 ) . '<br /><br />'; ?>
<?php echo MP_User::get_flag_IP(); ?>
								</td>
							</tr>
							<tr valign='top'>
								<th scope='row' class='h1em'>
									<?php _e('Name', MP_TXTDOM); ?>
								</th>
								<td class='h1em'>
									<input name='mp_user_name' type='text' value="<?php echo esc_attr($mp_user->name); ?>" size='30' />
									<input name='mp_user_old_name' type='hidden' value="<?php echo esc_attr($mp_user->name); ?>" />
								</td>
							</tr>
						</tbody>
					</table>
					<br />
<?php do_meta_boxes(MP_AdminPage::screen, 'normal', $mp_user); ?>
				</div>
			</div>
		</div>
	</form>
</div>