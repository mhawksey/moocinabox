<?php
if (class_exists('MailPress_mailinglist'))
{

class MP_export_mailinglist_mailinglist extends MP_import_importer_
{
	var $id = 'mailinglist_export_mailinglist';

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
					$this->message_report(" **  INFO * ! 'From' Mailing list is #" . $_POST['export_mailinglist']);
				$this->end_trace(true);
				$this->step1();
			break;
			case 2:
				$this->start_trace($step);
					$export = $this->export();
				$this->end_trace(true);
				if ($export)
					$this->success("<p><b>Mailinglist exported</b></p>");
				else 
					$this->error("<p><b>No mp users imported in Mailinglist</b></p>");
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
<?php		_e('Howdy!', MP_TXTDOM); ?>
		<br />
	</p>
	<form id='export-mailing-list' method='post' action='<?php echo MailPress_import . '&amp;mp_import=' . $this->id . '&amp;step=1'; ?>'>
		<p>
			<label for='download'><?php _e( "Choose the 'From' mailing list :", MP_TXTDOM ); ?></label>
<?php
			$dropdown_options = array('hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'htmlid' => 'export_mailinglist', 'name' => 'export_mailinglist', 'selected' => get_option(MailPress_mailinglist::option_name_default));
			MP_Mailinglist::dropdown($dropdown_options);
?>
		</p>
		<p class='submit'>
			<input type='submit' class='button' value='<?php esc_attr_e( 'Continue', MP_TXTDOM ); ?>' />
		</p>
	</form>
</div>
<?php
	}

// step 1

	function step1() 
	{ 
?>
<div>
	<p>
<?php		printf(__('From mailing list is #%s', MP_TXTDOM), $_POST['export_mailinglist']); ?>
	</p>
	<form id='export-mailing-list' method='post' action='<?php echo MailPress_import . '&amp;mp_import=' . $this->id . '&amp;step=2'; ?>'>
		<p>
			<label for='download'><?php _e( "Choose the 'To' mailing list :", MP_TXTDOM ); ?></label>
			<input type='hidden' name='from_mailinglist' value='<?php echo $_POST['export_mailinglist']; ?>' />
<?php
			$dropdown_options = array('hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'htmlid' => 'to_mailinglist', 'name' => 'to_mailinglist', 'exclude' => $_POST['export_mailinglist'] );
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

// step 2
	function export() 
	{
		$count = $exported = 0;
		$from = $_POST['from_mailinglist'];
		$to   = $_POST['to_mailinglist'];

		$this->message_report(" EXPORTING  ! mailing list #{$from} to mailing list #{$to}");

		global $wpdb;

		$users = $wpdb->get_results("SELECT DISTINCT c.id as id FROM $wpdb->term_taxonomy a, $wpdb->term_relationships b, $wpdb->mp_users c WHERE a.taxonomy = '" . MailPress_mailinglist::taxonomy . "' AND  a.term_taxonomy_id = b.term_taxonomy_id AND a.term_id = $from AND c.id = b.object_id ;");

		foreach ($users as $user)
		{
			$count++;

			$mp_user_mls = MP_Mailinglist::get_object_terms($user->id);
			if (!in_array($to, $mp_user_mls)) 
			{
				$mp_user_mls[] = $to;
				if (MP_Mailinglist::set_object_terms($user->id, $mp_user_mls)) $exported++;
				continue;
			}
			$this->message_report(" **WARNING* ! mp_user #{$user->id} already in mailing list #{$to} !");
		}

		if (!$count) 
			$this->message_report(" **WARNING* ! 'From' Mailing list is empty !");
		else
			$this->message_report(" **EXPORTED*! read in #{$from} : ". $count .", imported in #{$to} : ". $exported );

		return $exported;
	}
}
new MP_export_mailinglist_mailinglist(__('Export a mailing list into another mailing list.', MP_TXTDOM), __('Export mailing list into mailing list', MP_TXTDOM));
}