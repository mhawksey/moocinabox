<?php
$url_parms = MP_AdminPage::get_url_parms();

//
// MANAGING H2
//

$h2 = __('Logs', MP_TXTDOM);

//
// MANAGING MESSAGE / CHECKBOX RESULTS
//

$results = array(	'deleted'	=> array('s' => __('%s file deleted', MP_TXTDOM), 'p' => __('%s files deleted', MP_TXTDOM)),
);

foreach ($results as $k => $v)
{
	if (isset($_GET[$k]) && $_GET[$k])
	{
		if (!isset($message)) $message = '';
		$message .= sprintf( _n( $v['s'], $v['p'], $_GET[$k] ), $_GET[$k] );
		$message .=  '<br />';
	}
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
	list($items, $total, $subsubsub_urls) = MP_AdminPage::get_list(array('start' => $start, '_per_page' => $_per_page, 'url_parms' => $url_parms));
	$url_parms['paged']--;
} while ( $total <= $start );
$url_parms['paged']++;

?>
<div class='wrap'>
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<h2>
		<?php echo esc_html( $h2 ); ?> 
<?php if ( isset($url_parms['s']) ) printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_attr( $url_parms['s'] ) ); ?>
	</h2>
<?php if (isset($message)) MP_AdminPage::message($message); ?>

	<ul class='subsubsub'><?php echo $subsubsub_urls; ?></ul>

	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MP_AdminPage::screen; ?>' />

		<p class='search-box'>
			<input type='text' name='s' value="<?php if (isset($url_parms['s'])) echo esc_attr( $url_parms['s'] ); ?>" class="search-input" />
			<input type='submit' value="<?php _e( 'Search', MP_TXTDOM ); ?>" class='button' />
		</p>

<?php
if ($items) {
?>
		<div class='tablenav'>
			<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions); ?>
			</div>

<?php MP_AdminPage::pagination($total); ?>

			<br class="clear" />
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
			<tbody id='the-file-list' class='list:file'>
<?php	foreach ($items as $item) MP_AdminPage::get_row( $item, $url_parms ); ?>
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

	<form id='get-extra-files' method='post' action='' class='add:the-extra-file-list:' style='display: none;'>
<?php  MP_AdminPage::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-file', '_ajax_nonce', false ); ?>
	</form>

	<div id='ajax-response'></div>

<?php
} else {
?>
	</form>
		<p>
			<?php (is_dir('../' . MP_AdminPage::get_path())) ? _e('No logs available', MP_TXTDOM) : printf( __('Wrong path : %s', MP_TXTDOM), '../' . MP_AdminPage::get_path() ); ?>
		</p>
<?php
}
?>
</div>