<?php
$url_parms = MP_AdminPage::get_url_parms();

//
// MANAGING H2
//

$h2 = __('Wp_cron', MP_TXTDOM);

//
// MANAGING MESSAGE
//

$messages[1] = __('Cron added.', MP_TXTDOM);
$messages[2] = __('Cron updated.', MP_TXTDOM);
$messages[3] = __('Cron deleted.', MP_TXTDOM);
$messages[4] = __('Crons deleted.', MP_TXTDOM);
$messages[5] = __('Cron executed.', MP_TXTDOM);
$messages[91] = __('Cron NOT added.', MP_TXTDOM);
$messages[92] = __('Cron NOT updated.', MP_TXTDOM);
$messages[95] = __('Cron NOT executed.', MP_TXTDOM);

if (isset($_GET['message']))
{
	$message = $messages[$_GET['message']];
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}

//
// MANAGING CONTENT
//

global $action;
wp_reset_vars(array('action'));
if ('edit' == $action) 
{
	$action = 'edited';
	$cancel = "<input type='submit' class='button' name='cancel' value=\"" . __('Cancel', MP_TXTDOM) . "\" />\n";

	$id = $_GET['id'];
	$sig = $_GET['sig'];
	$next_run = $_GET['next_run'];
	$wpcron = MP_AdminPage::get($id, $sig, $next_run);

	$h3 = __('Edit cron', MP_TXTDOM);

	$hidden = "<input type='hidden' name='id' value='$id::$sig::$next_run' />\n";

	$flipflops = array(2, 1);
}
else 
{
	$action = MP_AdminPage::add_form_id;
	$cancel = '';

	$wpcron = array();

	$h3 = __('Add cron', MP_TXTDOM);

	$hidden = '';
	$flipflops = array(1, 2);
}

//
// MANAGING BULK ACTIONS
//

$bulk_actions[''] 	= __('Bulk Actions');
$bulk_actions['delete']	= __('Delete', MP_TXTDOM);

//
// MANAGING LIST
//

$url_parms['paged'] = isset($url_parms['paged']) ? $url_parms['paged'] : 1;
$_per_page = MP_AdminPage::get_per_page();

do
{
	$start = ( $url_parms['paged'] - 1 ) * $_per_page;
	list($items, $total) = MP_AdminPage::get_list(array('start' => $start, '_per_page' => $_per_page, 'url_parms' => $url_parms));
	$url_parms['paged']--;
} while ( $total <= $start );
$url_parms['paged']++;

?>
<div class='wrap'>
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<div id='mp_message'></div>
	<h2>
		<?php echo esc_html( $h2 ); ?>
	</h2>
<?php if (isset($message)) MP_AdminPage::message($message, ($_GET['message'] < 90)); ?>
	<br class='clear' />
<?php
foreach ($flipflops as $flipflop)
{
	switch($flipflop)
	{
		case 1 :
?>
	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MP_AdminPage::screen; ?>' />

		<div class='tablenav'>
			<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions); ?>
			</div>

<?php MP_AdminPage::pagination($total); ?>

			<br class='clear' />
		</div>
		<div class="clear"></div>

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
			<tbody id='<?php echo MP_AdminPage::list_id; ?>' class='list:<?php echo MP_AdminPage::tr_prefix_id; ?>'>
<?php	foreach ($items as $item) echo MP_AdminPage::get_row( $item, $url_parms ); ?>
			</tbody>
		</table>
		<div class='tablenav'>
<?php MP_AdminPage::pagination($total, 'bottom'); ?>
			<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions, 'action2'); ?>
			</div>
			<br class='clear' />
		</div>
	</form>
<?php
		break;
		case 2 :
?>
	<form name='<?php echo $action; ?>'  id='<?php echo $action; ?>'  method='post' action='' class='<?php echo $action; ?>:<?php echo MP_AdminPage::list_id; ?>: validate'>
		<table class='widefat'>
			<thead>
				<tr>
<?php
	foreach ( array('name' => __('Hook name', MP_TXTDOM), 'next' => __('Next&#160;run',  MP_TXTDOM), 'rec' => __('Recurrence',MP_TXTDOM), 'args' => __('Arguments', MP_TXTDOM)) as $key => $display_name ) 
	{
		$display_name = ('next' != $key) ? $display_name : "<abbr title='" . __('e.g., "now", "tomorrow", "+2 days", or "06/04/08 15:27:09"', MP_TXTDOM) . "'>$display_name</abbr>";
		$display_name = ('args' != $key) ? $display_name : "<abbr title='" . __('JSON encoded string', MP_TXTDOM) . "'>$display_name</abbr>";
		echo "<th scope='col'>$display_name</th>";
	} 
?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
<input name='name' id='wpcron_name' type='text' value="<?php if (isset($wpcron['hookname'])) echo esc_attr($wpcron['hookname']); ?>" size='40' />
					</td>
					<td>
<input name='next_run' id='wpcron_next_run' type='text' value="<?php if (isset($wpcron['next_run'])) echo date('Y/m/d H:i:s', $wpcron['next_run']); else echo "now"; ?>" size='40' />
					</td>
					<td>
<select name='schedule' id='wpcron_schedule'>
	<?php MP_AdminPage::select_option(MP_AdminPage::get_schedules(), (isset($wpcron['schedule'])) ? $wpcron['schedule'] : '_oneoff'); ?>
</select>
					</td>
					<td>
<input name='args' id='wpcron_args' type='text' value="<?php if (isset($wpcron['args'])) echo htmlentities(json_encode($wpcron['args'])); ?>" size='40' />
					</td>
				</tr>
			</tbody>
		</table>
		<div class='tablenav'>
			<div class='alignright actions'>
				<input type='submit' class='button' name='submit' id='wpcron_submit' value="<?php echo $h3; ?>" />
				<?php echo $cancel; ?>
				<input type='hidden' name='action'   value='<?php echo $action; ?>' />
				<input type='hidden' name='formname' value='wp_cron_form' />
				<?php echo $hidden; ?>
				<?php wp_nonce_field('update-' . MP_AdminPage::tr_prefix_id); ?>
			</div>
			<br class='clear' />
		</div>
		<br class='clear' />
	</form>
<?php
		break;
	}
}
?>
</div>