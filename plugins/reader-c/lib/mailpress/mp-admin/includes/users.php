<?php
do_action('MailPress_users_addon_update');

$url_parms = MP_AdminPage::get_url_parms(array('status','s','paged','author','mailinglist','newsletter','startwith'));

//
// MANAGING H2
//

$h2 = __('Users', MP_TXTDOM);
$subtitle = '';

if (isset($url_parms['startwith'])) 
{
	$subtitle .= ' ' . sprintf(__('starting with &#8220;%s&#8221;', MP_TXTDOM), $url_parms['startwith']);
}
if (isset($url_parms['newsletter']) && !empty($url_parms['newsletter'])) 
{
	$newsletter = MP_Newsletter::get( $url_parms['newsletter'] );
	$subtitle .= ' ' . sprintf(__('in &#8220;%s&#8221;', MP_TXTDOM), esc_html( $newsletter['descriptions']['admin']));
}
if (isset($url_parms['mailinglist']) && !empty($url_parms['mailinglist'])) 
{
	$mailinglist = MP_Mailinglist::get( $url_parms['mailinglist'] );
	$subtitle .= ' ' . sprintf(__('in &#8220;%s&#8221;', MP_TXTDOM), esc_html( $mailinglist->name ));
}
if (isset($url_parms['author'])) 
{
	$author_user = get_userdata( $url_parms['author'] );
	$subtitle .= ' ' . sprintf(__('by %s'), esc_html( $author_user->display_name ));
}

//
// MANAGING MESSAGE / CHECKBOX RESULTS
//

$results = array(	'activated'	=> array('s' => __('%s subscriber activated', MP_TXTDOM),  'p' => __('%s subscribers activated', MP_TXTDOM)),
			'deactivated'=>array('s' => __('%s subscriber deactivated', MP_TXTDOM),'p' => __('%s subscribers deactivated', MP_TXTDOM)),
			'unbounced'	=> array('s' => __('%s subscriber unbounced', MP_TXTDOM),  'p' => __('%s subscribers unbounced', MP_TXTDOM)),
			'deleted'	=> array('s' => __('%s subscriber deleted', MP_TXTDOM),    'p' => __('%s subscribers deleted', MP_TXTDOM)),
			'geolocated'=> array('s' => __('%s subscriber geolocated', MP_TXTDOM), 'p' => __('%s subscribers geolocated', MP_TXTDOM)),
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

$bulk_actions[''] = __('Bulk Actions');
if ((isset($url_parms['status'])) && ( 'waiting' == $url_parms['status'] ))		$bulk_actions['activate']  = __('Activate', MP_TXTDOM);
if ((isset($url_parms['status'])) && ( 'unsubscribed' == $url_parms['status'] )) 	$bulk_actions['deactivate']= __('Deactivate', MP_TXTDOM);
if ((isset($url_parms['status'])) && ( 'active'  == $url_parms['status'] ))		$bulk_actions['deactivate']= __('Deactivate', MP_TXTDOM);
if ((isset($url_parms['status'])) && ( 'bounced' == $url_parms['status'] )) 		$bulk_actions['unbounce']  = __('Unbounce', MP_TXTDOM);
if (current_user_can('MailPress_delete_users')) 						$bulk_actions['delete']	   = __('Delete', MP_TXTDOM);

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
	<div id='icon-mailpress-users' class='icon32'><br /></div>
	<div id='mp_message'></div>
	<h2>
		<?php echo esc_html( $h2 ); ?>
<?php if ( isset($url_parms['s']) ) printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_attr( $url_parms['s'] ) ); ?>
<?php if ( !empty($subtitle) )      echo    "<span class='subtitle'>$subtitle</span>"; ?>
	</h2>
<?php if (isset($message)) MP_AdminPage::message($message); ?>

	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MP_AdminPage::screen; ?>' />
<?php MP_AdminPage::post_url_parms($url_parms, array('mode', 'status', 'author', 'mailinglist', 'newsletter')); ?>

		<ul class='subsubsub'><?php echo $subsubsub_urls; ?></ul>

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
<?php do_action('MailPress_users_restrict',$url_parms); ?>
				<input type='submit' id='restrict' value="<?php _e('Filter', MP_TXTDOM); ?>" class='button-secondary' />
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
			<tbody id='the-user-list' class='list:user'>
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

	<form id='get-extra-users' method='post' action='' class='add:the-extra-user-list:' style='display: none;'>
<?php MP_AdminPage::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-user', '_ajax_nonce', false ); ?>
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
<?php do_action('MailPress_users_addon',$url_parms); ?>
</div>