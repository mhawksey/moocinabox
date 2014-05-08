<?php
if (isset($_GET['mp_import']))
{
	$importers = MP_Import_importers::get_all();

	$importer = $_GET['mp_import'];

	// Allow plugins to define importers as well
	if (!is_callable($importers[$importer][2]))
	{
		$_file = MP_ABSPATH . "mp-includes/class/options/import/importers/$importer.php";
		if (!is_file($_file)) wp_die(__('Cannot load importer.', MP_TXTDOM));
		include($_file);
	}

	define('MP_IMPORTING', true);

	call_user_func($importers[$importer][2]);

	return;
}

$items = MP_AdminPage::get_list(); 
?>
<div class='wrap'>
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<h2><?php _e('Import/Export'); ?></h2>
<?php
if ($items)
{
?>
		<p>
			<?php _e('If you have emails in another system, MailPress can import those into this blog.', MP_TXTDOM); ?>
			<br />
			<?php _e('MailPress can also export your MP users from this blog.', MP_TXTDOM); ?>
			<br />
			<?php _e('To get started, choose a system to import/export from below:', MP_TXTDOM); ?>
		</p>
		<table class='widefat'>
			<thead>
				<tr>
<?php 	MP_AdminPage::columns_list(); ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
<?php 	MP_AdminPage::columns_list(false); ?>
				</tr>
			</tfoot>
			<tbody>
<?php 	foreach ($items as $id => $data) echo MP_AdminPage::get_row( $id, $data ); ?>
			</tbody>
		</table>
<?php
} 
else 
{
?>
		<p><?php _e('No importers/exporters available.', MP_TXTDOM); ?></p>
<?php
}
?>
</div>