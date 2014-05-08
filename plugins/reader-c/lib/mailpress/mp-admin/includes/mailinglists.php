<?php
$url_parms = MP_AdminPage::get_url_parms();

//
// MANAGING H2
//

$h2 = __('Mailing lists', MP_TXTDOM); 

//
// MANAGING MESSAGE
//

$messages[1] = __('Mailinglist added.', MP_TXTDOM);
$messages[2] = __('Mailinglist updated.', MP_TXTDOM);
$messages[3] = __('Mailinglist deleted.', MP_TXTDOM);
$messages[4] = __('Mailinglists deleted.', MP_TXTDOM);
$messages[91] = __('Mailinglist not added.', MP_TXTDOM);
$messages[92] = __('Mailinglist not updated.', MP_TXTDOM);

if (isset($_GET['message']))
{
	$message = $messages[$_GET['message']];
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}

//
// MANAGING CONTENT
//

$bulk_actions['']		= __('Bulk Actions');
$bulk_actions['delete'] = __('Delete', MP_TXTDOM);

global $action;
wp_reset_vars(array('action'));
if ('edit' == $action) 
{
	$action = 'edited';
	$cancel = "<input type='submit' class='button' name='cancel' value=\"" . __('Cancel', MP_TXTDOM) . "\" />\n";

	$id = (int) $_GET['id'];
	$mailinglist = MP_Mailinglist::get( $id, OBJECT, 'edit' );

	$h3 = __('Edit Mailing List', MP_TXTDOM);
	$hb3= __('Update');
	$hbclass = '-primary';

	$disabled = '';

	$hidden = "<input type='hidden' name='id'   value=\"" . $id . "\" />\n";
	$hidden .="<input name='name' type='hidden' value=\"" . esc_attr($mailinglist->name) . "\"/>\n";
}
else 
{
	$action = MP_AdminPage::add_form_id;
	$cancel = '';

	$mailinglist = new stdClass();

	$h3 = $hb3 = __('Add Mailing List', MP_TXTDOM);
	$hbclass = '';

	$disabled = '';
	$hidden = '';
}

//
// MANAGING LIST
//

$url_parms['paged'] = isset($url_parms['paged']) ? $url_parms['paged'] : 1;
$_per_page = MP_AdminPage::get_per_page();

$total = ( isset($url_parms['s']) ) ? count(MP_Mailinglist::get_all(array('hide_empty' => 0, 'search' => $url_parms['s']))) : wp_count_terms(MP_AdminPage::taxonomy);

?>
<div class='wrap nosubsub'>
	<div id='icon-mailpress-users' class='icon32'><br /></div>
	<h2>
		<?php echo esc_html( $h2 ); ?> 
<?php if ( isset($url_parms['s']) ) printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_attr( $url_parms['s'] ) ); ?>
	</h2>
<?php if (isset($message)) MP_AdminPage::message($message, ($_GET['message'] < 90)); ?>
	<form class='search-form topmargin' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MP_AdminPage::screen; ?>' />

		<p class='search-box'>
			<input type='text' name='s' value="<?php if (isset($url_parms['s'])) echo esc_attr( $url_parms['s'] ); ?>" class="search-input" />
			<input type='submit' value="<?php _e( 'Search', MP_TXTDOM ); ?>" class='button' />
		</p>

	</form>
	<br class='clear' />
	<div id='col-container'>
		<div id='col-right'>
			<div class='col-wrap'>
				<form id='posts-filter' action='' method='get'>
					<input type='hidden' name='page' value='<?php echo MP_AdminPage::screen; ?>' />

					<div class='tablenav'>
<?php MP_AdminPage::pagination($total); ?>
						<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions); ?>
						</div>
						<br class='clear' />
					</div>
					<div class='clear'></div>
					<table class='widefat'>
						<thead>
							<tr>
<?php MP_AdminPage::columns_list(); ?>
							</tr>
						</thead>
						<tfoot>
							<tr>
<?php MP_AdminPage::columns_list(false); ?>
							</tr>
						</tfoot>
						<tbody id='<?php echo MP_AdminPage::list_id; ?>' class='list:<?php echo MP_AdminPage::tr_prefix_id; ?> mailinglists'>
<?php MP_AdminPage::get_list(array('start' => $url_parms['paged'], '_per_page' => $_per_page)); ?>
						</tbody>
					</table>
					<div class='tablenav'>
<?php MP_AdminPage::pagination($total, 'bottom'); ?>
						<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions, 'action2'); ?>
						</div>
						<br class='clear' />
					</div>
					<br class='clear' />
				</form>
				<div class='form-wrap'>
					<p><?php printf(__('<strong>Note:</strong><br />Deleting a mailing list does not delete the MailPress users in that mailing list. Instead, MailPress users that were only assigned to the deleted mailing list are set to the mailing list <strong>%s</strong>.', MP_TXTDOM), MP_Mailinglist::get_name(get_option(MailPress_mailinglist::option_name_default))) ?></p>
				</div>
			</div>
		</div><!-- /col-right -->
		<div id='col-left'>
			<div class='col-wrap'>
				<div class='form-wrap'>
					<h3><?php echo $h3; ?></h3>
					<div id='ajax-response'></div>
					<form name='<?php echo $action; ?>'  id='<?php echo $action; ?>'  method='post' action='' class='<?php echo $action; ?>:<?php echo MP_AdminPage::list_id; ?>: validate'>
						<input type='hidden' name='action'   value='<?php echo $action; ?>' />
						<input type='hidden' name='formname' value='mailinglist_form' />
						<?php echo $hidden; ?>
						<?php wp_nonce_field('update-' . MP_AdminPage::tr_prefix_id); ?>
						<div class="form-field form-required" style='margin:0;padding:0;'>
							<label for='mailinglist_name'><?php _e('Name', MP_TXTDOM); ?></label>
							<input name='name' id='mailinglist_name' type='text'<?php echo $disabled; ?> value="<?php if (isset($mailinglist->name)) echo esc_attr($mailinglist->name); ?>" size='40' aria-required='true' />
							<p><?php _e('The name is used to identify the mailinglist almost everywhere.', MP_TXTDOM); ?></p>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label for='mailinglist_slug'><?php _e('Slug', MP_TXTDOM) ?></label>
							<input name='slug' id='mailinglist_slug' type='text' value="<?php if (isset($mailinglist->slug)) echo esc_attr($mailinglist->slug); ?>" size='40' />
							<p><?php _e('The &#8220;slug&#8221; is a unique id for the mailing list (not so friendly !).', MP_TXTDOM); ?></p>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label for='mailinglist_description'><?php _e('Description', MP_TXTDOM) ?></label>
							<input type="text" id='mailinglist_description' name='description' value="<?php if (isset($mailinglist->description)) echo stripslashes($mailinglist->description); ?>" size="40"/>
							<p><?php _e('The description is not prominent by default.', MP_TXTDOM); ?></p>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label for='mailinglist_parent'><?php _e('Mailing list Parent', MP_TXTDOM) ?></label>
							<?php MP_Mailinglist::dropdown(array('hide_empty' => 0, 'name' => 'parent', 'orderby' => 'name', 'htmlid' => 'mailinglist_parent', 'selected' => (isset($mailinglist->parent)) ? $mailinglist->parent : '', 'exclude' => (isset($id)) ? $id : '', 'hierarchical' => true, 'show_option_none' => __('None', MP_TXTDOM))); ?>
							<p><?php _e("Mailing list can have a hierarchy. You might have a Rock'n roll mailing list, and under that have children mailing lists for Elvis and The Beatles. Totally optional !", MP_TXTDOM); ?></p>
						</div>
						<p class='submit'>
							<input type='submit' class='button<?php echo $hbclass; ?>' name='submit' id='mailinglist_submit' value="<?php echo $hb3; ?>" />
							<?php echo $cancel; ?>
						</p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div><!-- /wrap -->