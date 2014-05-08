<?php
$url_parms = MP_AdminPage::get_url_parms();

//
// MANAGING H2
//

$h2 = __('Edit Mails', MP_TXTDOM);
$subtitle = '';

if (isset($url_parms['author'])) 
{
	$author_user = get_userdata( $url_parms['author'] );
	$subtitle .= ' ' . sprintf(__('by %s'), esc_html( $author_user->display_name ));
}

//
// MANAGING MESSAGE / CHECKBOX RESULTS
//

$results = array(	'deleted'	=> array('s' => __('%s mail deleted', MP_TXTDOM), 'p' => __('%s mails deleted', MP_TXTDOM)),
			'sent'	=> array('s' => __('%s mail sent', MP_TXTDOM),    'p' => __('%s mails sent', MP_TXTDOM)),
			'notsent'	=> array('s' => __('%s mail NOT sent', MP_TXTDOM),'p' => __('%s mails NOT sent', MP_TXTDOM)),
			'archived'	=> array('s' => __('%s mail archived', MP_TXTDOM),'p' => __('%s mails archived', MP_TXTDOM)),
			'unarchived'=> array('s' => __('%s mail unarchived', MP_TXTDOM),'p' => __('%s mails unarchived', MP_TXTDOM)),
			'paused'	=> array('s' => __('%s mail paused', MP_TXTDOM),'p' => __('%s mails paused', MP_TXTDOM)),
			'restartd'	=> array('s' => __('%s mail restarted', MP_TXTDOM),'p' => __('%s mails restarted', MP_TXTDOM)),
			'saved'	=> array('s' => __('Mail saved', MP_TXTDOM),      'p' => __('Mail saved', MP_TXTDOM)),
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
// MANAGING DETAIL/LIST URL
//

if (isset($url_parms['mode'])) $wmode = $url_parms['mode'];
$url_parms['mode'] = 'detail';
$detail_url = esc_url(MP_AdminPage::url( MailPress_mails, $url_parms ));
$url_parms['mode'] = 'list';
$list_url  	= esc_url(MP_AdminPage::url( MailPress_mails, $url_parms ));
if (isset($wmode)) $url_parms['mode'] = $wmode; 

//
// MANAGING BULK ACTIONS
//

$bulk_actions[''] = __('Bulk Actions');
if (isset($url_parms['status']))
{
	switch($url_parms['status'])
	{
		case 'draft' :
			$bulk_actions['send']		= __('Send', MP_TXTDOM);
		break;
		case 'sent' :
			$bulk_actions['archive']	= __('Archive', MP_TXTDOM);
		break;
		case 'archived' :
			$bulk_actions['unarchive']	= __('Unarchive', MP_TXTDOM);
		break;
	}
}
if (current_user_can('MailPress_delete_mails')) $bulk_actions['delete']  	= __('Delete', MP_TXTDOM);

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
	<div id="icon-mailpress-mails" class="icon32"><br /></div>
	<div id='mp_message'></div>
	<h2>
		<?php echo esc_html( $h2 ); ?> 
		<a href='<?php echo MailPress_write; ?>' class="add-new-h2"><?php echo esc_html(__('Add New', MP_TXTDOM)); ?></a> 
<?php if ( isset($url_parms['s']) ) printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_attr( $url_parms['s'] ) ); ?>
<?php if ( !empty($subtitle) )      echo    "<span class='subtitle'>$subtitle</span>"; ?>
	</h2>
<?php if (isset($message)) MP_AdminPage::message($message); ?>

	<ul class='subsubsub'><?php echo $subsubsub_urls; ?></ul>

	<form id='posts-filter' action='' method='get'>
		<p class='search-box'>
			<input type='text' name='s' value="<?php if (isset($url_parms['s'])) echo esc_attr( $url_parms['s'] ); ?>" class="search-input" />
			<input type='submit' value="<?php _e( 'Search', MP_TXTDOM ); ?>" class='button' />
		</p>

		<input type='hidden' name='page' value='<?php echo MP_AdminPage::screen; ?>' />
<?php MP_AdminPage::post_url_parms($url_parms, array('mode', 'status')); ?>

<?php
if ($items) {
?>
		<div class='tablenav'>
			<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions); ?>
			</div>

<?php MP_AdminPage::pagination($total); ?>

			<div class='view-switch'>
				<a href="<?php echo $list_url;   ?>"><img id="view-switch-list"    height="20" width="20" <?php if ( 'list'   == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('List View', MP_TXTDOM)   ?>" title="<?php _e('List View', MP_TXTDOM)   ?>" src="../wp-includes/images/blank.gif" /></a>
				<a href="<?php echo $detail_url; ?>"><img id="view-switch-excerpt" height="20" width="20" <?php if ( 'detail' == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('Detail View', MP_TXTDOM) ?>" title="<?php _e('Detail View', MP_TXTDOM) ?>" src="../wp-includes/images/blank.gif" /></a>
			</div>
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
			<tbody id='the-mail-list' class='list:mail'>
<?php foreach ($items as $item) 		MP_AdminPage::get_row( $item->id, $url_parms ); ?>
			</tbody>
		</table>
		<div class='tablenav'>
<?php MP_AdminPage::pagination($total, 'bottom'); ?>
			<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions, 'action2'); ?>
			</div>
			<br class="clear" />
		</div>
	</form>

	<form id='get-extra-mails' method='post' action='' class='add:the-extra-mail-list:' style='display:none;'>
<?php MP_AdminPage::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-mail', '_ajax_nonce', false ); ?>
	</form>

	<div id='ajax-response'></div>

<?php
} else {
?>
	</form>
	<div class="clear"></div>
	<p>
		<?php _e('No results found.', MP_TXTDOM) ?>
	</p>
<?php
}
?>
</div>