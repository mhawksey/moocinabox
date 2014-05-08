<?php
if (class_exists('MailPress_mailinglist'))
{

class MP_export_mailinglist extends MP_import_importer_
{
	var $id = 'csv_export_mailing_list';

	function dispatch($step = 0) 
	{
		if (isset($_GET['step']) && !empty($_GET['step'])) $step = (int) $_GET['step'];

		$this->header();
		switch ($step) 
		{
			case 0 :
				$this->greet();
			break;
			case 1:
				$this->start_trace($step);
					$export = $this->export();
				$this->end_trace(true);
				if ($export)
				{
					$file = $this->url;
					$this->success('<p>' . sprintf(__("<b>File exported</b> : <i>%s</i>", MP_TXTDOM), "<a href='$file'>$file</a>") . '</p><p><strong>' . sprintf(__("<b>Number of records</b> : <i>%s</i>", MP_TXTDOM), $export) . '</strong></p>');
				}
				else 
					$this->error('<p><strong>' . $this->file . '</strong></p>');
			break;
		}
		$this->footer();
	}

// step 0

	function greet() 
	{
?>
<div>
	<p>
<?php		_e('Howdy! Choose your mailing list and we&#8217;ll export the emails ... into a file.', MP_TXTDOM); ?>
		<br />
	</p>
	<form id='export-mailing-list' method='post' action='<?php echo MailPress_import . '&amp;mp_import=' . $this->id . '&amp;step=1'; ?>'>
		<p>
			<label for='download'><?php _e( 'Choose a mailing list :', MP_TXTDOM ); ?></label>
<?php
			$dropdown_options = array('hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'htmlid' => 'export_mailinglist', 'name' => 'export_mailinglist', 'selected' => get_option(MailPress_mailinglist::option_name_default));
			MP_Mailinglist::dropdown($dropdown_options);
?>
		</p>
		<p class='submit'>
			<input type='submit' class='button' value='<?php esc_attr_e( 'Export', MP_TXTDOM ); ?>' />
		</p>
	</form>
</div>
<?php
	}

// step 1

	function export() 
	{
		$this->message_report(" EXPORTING  !");

		$id = $_POST['export_mailinglist'];

		$x = $id;
		$y = MP_Mailinglist::get_children($x, ', ', '');
		$x = ('' == $y) ? ' = ' . $x : ' IN (' . $x . $y . ') ';


		global $wpdb;

		$fields = array('c.id', 'c.email', 'c.name', 'c.status', 'c.created', 'c.created_IP', 'c.created_agent', 'c.created_user_id', 'c.created_country', 'c.created_US_state', 'c.laststatus', 'c.laststatus_IP', 'c.laststatus_agent', 'c.laststatus_user_id');
		$users = $wpdb->get_results("SELECT DISTINCT " . join(', ', $fields) . " FROM $wpdb->term_taxonomy a, $wpdb->term_relationships b, $wpdb->mp_users c WHERE a.taxonomy = '" . MailPress_mailinglist::taxonomy . "' AND  a.term_taxonomy_id = b.term_taxonomy_id AND a.term_id $x AND c.id = b.object_id AND c.status = 'active'; ", ARRAY_A);

		if (empty($users))
		{
			$this->message_report(' **WARNING* ! Mailing list is empty !');
			return false;
		}

		$this->file = 'csv_export_mailing_list_' . $id . '_' . date('Ymd_Hi') . '.csv';

		require_once 'parsecsv/parsecsv.lib.php';
		$csv = new parseCSV();
		$r = file_put_contents(MP_ABSPATH . 'tmp/' . $this->file, $csv->unparse($users, $fields));

		if (!$r)
		{
			$this->message_report(' ***ERROR** ! Unable to write file');
			return false;
		}

		$file['name'] = $this->file;
		$file['tmp_name'] = MP_ABSPATH . 'tmp/' . $this->file;
		$file['type'] = 'csv';

		$this->url = $this->insert_attachment($file);

		if (!$this->url) $this->url = site_url() . '/' . MP_PATH . 'tmp/' . $this->file;

		$this->message_report('   SUCCESS  ! file available at ' . $this->url);
		return count($users);
	}
}
new MP_export_mailinglist(__('Export your mailing list in a <strong>csv</strong> file.', MP_TXTDOM), __('Export mailing list', MP_TXTDOM));
}