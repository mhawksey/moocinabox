<?php
class MP_import_csv extends MP_import_importer_
{
	var $id = 'csv';

	function dispatch($step = 0) 
	{
		if (isset($_GET['step']) && !empty($_GET['step'])) $step = (int) $_GET['step'];

		$this->header();
		switch ($step) 
		{
			case 0 :
				$this->greet();
			break;
			case 1 :
				$this->start_trace($step);
				if ( $this->handle_upload() )
				{
               			$this->message_report(" ANALYSIS   !");
						$sniff = $this->sniff($step);
					$this->end_trace(true);
					if ($sniff)
						$this->fileform();
					else
						$this->error('<p><strong>' . __('Unable to determine email location', MP_TXTDOM) . '</strong></p>');
				}
				else
				{
					$this->message_report("** ERROR ** ! Could not upload the file");
					$this->end_trace(false);
				}
			break;
			case 2:
				$this->start_trace($step);
				$import = $this->import( $_GET['id'] );
				$this->end_trace(true);
				if ($import)
					$this->success('<p>' . sprintf(__("<b>File imported</b> : <i>%s</i>", MP_TXTDOM), $this->file) . '</p><p><strong>' . sprintf(__("<b>Number of records</b> : <i>%s</i>", MP_TXTDOM), $import) . '</strong></p>');
				else 
					$this->error('<p><strong>' . $this->file . '</strong></p>');
			break;
		}
		$this->footer();
	}

// step 0

// step 1

	function sniff($step, $first=true)
	{
		$this->message_report(" sniff $step    ! >>> " . $this->file);

		require_once 'parsecsv/parsecsv.lib.php';
		$this->csv = new parseCSV();
		$this->csv->auto($this->file);
		$this->hasheader = true;

		return ($first) ? $this->find_email() : true;
	}

	function find_email()
	{
		$i = 0;
		$email = array();
		foreach ($this->csv->data as $row)
		{
			foreach ($row as $k => $v)	if (is_email($v)) if (isset($email[$k])) $email[$k]++; else $email[$k] = 1;

			$i++;
			if ($i > 9) break;
		}

		if (0 == count($email))
		{
			$this->message_report(' **WARNING* ! Unable to determine email location');
			return false;
		}

		asort($email);
		$x = array_flip($email);
		$this->emailcol = end($x);
		
		$this->message_report(' email      ! ' . sprintf('Email probably in column %s', $this->emailcol));

		return true;
	}

	function fileform() 
	{
		if (current_user_can('MailPress_manage_mailinglists')) add_filter('admin_print_footer_scripts', array(__CLASS__, 'footer_scripts'), 1);
?>
	<form id="mp_import" action="<?php echo MailPress_import; ?>&amp;mp_import=csv&amp;step=2&amp;id=<?php echo $this->file_id; ?>" method="post">
<?php 	if (current_user_can('MailPress_manage_mailinglists')) : ?>
		<h3><?php _e('Mailing list', MP_TXTDOM); ?></h3>
		<p><?php _e('Optional, you can import the MailPress users in a specific mailing list ...', MP_TXTDOM); ?></p>
<?php			MP_Mailinglist::dropdown(array('name' => 'mailinglist', 'htmlid' => 'mailinglist', 'hierarchical' => true, 'orderby' => 'name', 'hide_empty' => '0', 'show_option_none' => __('Choose mailinglist', MP_TXTDOM))); ?>
<?php endif; ?>
<?php if (class_exists('MailPress_newsletter')) : ?>
		<h3><?php _e('Newsletter', MP_TXTDOM); ?></h3>
		<p>
			<input type='checkbox' name='no_newsletter' id='no_newsletter' />
			<?php _e('<b>Delete</b> all subscriptions.', MP_TXTDOM); ?>
		</p>
		<p>
			<input type='checkbox' name='newsletter' id='newsletter' /> 
			<?php _e('<b>Add</b> default subscriptions.', MP_TXTDOM); ?>
		</p>
<?php endif; ?>
		<h3><?php _e('File scan', MP_TXTDOM); ?></h3>
		<p><?php printf(__("On the first records (see hereunder), the file scan found that the email is in column '<strong>%s</strong>'.", MP_TXTDOM), $this->emailcol); ?>
		<?php _e('However, you can select another column.<br /> Invalid emails will not be inserted.', MP_TXTDOM); ?></p>
		<table class='widefat'>
			<thead>
				<tr>
					<td style='width:auto;'><?php _e('Choose email column', MP_TXTDOM); ?></td>
<?php
		foreach ($this->csv->data as $row)
		{
			foreach ($row as $k => $v)
			{
?>
					<td><input type='radio' name='is_email' value="<?php echo $k; ?>"<?php checked( $k, $this->emailcol ); ?> /><span><?php echo $k; ?></span></td>
<?php
			}
			break;
		}
?>
				</tr>
				<tr>
					<td><?php _e('Choose name column', MP_TXTDOM); ?></td>
<?php
		foreach ($this->csv->data as $row)
		{
			foreach ($row as $k => $v)
			{
?>
					<td><input type='radio' name='is_name' value="<?php echo $k; ?>" /><span><?php echo $k; ?></span></td>
<?php
			}
			break;
		}
?>
				</tr>
			</thead>
			<tbody>
<?php
		$i = 0;
		foreach ($this->csv->data as $row)
		{
?>
				<tr>
					<td></td>
<?php
			foreach ($row as $k => $v)
			{
?>
					<td><span <?php if ($k == $this->emailcol) if (!is_email($v)) echo "style='background-color:#fdd;'"; else echo "style='background-color:#dfd;'";?>><?php echo $v; ?></span></td>
<?php
			}
?>
				</tr>
<?php
			$i++;
			if ($i > 9) break;
		}
?>
			</tbody>
		</table>
		<p class='submit'>
			<input class='button-primary' type='submit' value="<?php echo esc_attr( __('Submit') ); ?>" />
		</p>
	</form>
<?php
	}

	public static function footer_scripts() 
	{
		wp_register_script( 'mp-import', '/' . MP_PATH . 'mp-includes/js/mp_mailinglist_dropdown.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-import', 	'mp_ml_select_L10n', array(
			'error' => __('Please, choose a mailinglist', MP_TXTDOM), 
			'select' => 'mailinglist', 
			'form'   => 'mp_import',
			'l10n_print_after' => 'try{convertEntities(mp_ml_select_L10n);}catch(e){};' 
		));

		wp_enqueue_script('mp-import');
	}

// step 2

	function import($id) 
	{
		$this->file_id = (int) $id;
		$this->file    = get_attached_file($this->file_id);

		$this->message_report(" IMPORTING  !");

		$this->sniff(2, false);

		if ( !is_file($this->file) ) {	$this->message_report("File not found" . $this->file); return false;}

		$this->emailcol = $_POST['is_email'];
		$this->namecol  = $_POST['is_name'];

		if (class_exists('MailPress_mailinglist'))
		{
			$mailinglist_ok = ('-1' != $_POST['mailinglist']);
			if ($mailinglist_ok)
			{
				$this->mailinglist_ID = $_POST['mailinglist'];
				add_filter('MailPress_mailinglist_default', array($this, 'mailinglist_default'), 8, 1);

				$mailinglist_name = MP_Mailinglist::get_name($this->mailinglist_ID);
			}
		}

		if (class_exists('MailPress_newsletter'))
		{
			$no_newsletter_ok = isset($_POST['no_newsletter']);
			$newsletter_ok 	= isset($_POST['newsletter']);
		}

		$i = 0;
		foreach ($this->csv->data as $row)
		{
			$i++;

			$curremail = trim(strtolower($row[$this->emailcol]));
			$currname  = trim($row[$this->namecol]);
			$mp_user_id = $this->sync_mp_user($curremail, $currname);

			if ($mp_user_id)
			{
				if (isset($mailinglist_ok) && $mailinglist_ok)
				{
					$this->sync_mp_user_mailinglist($mp_user_id, $this->mailinglist_ID, $curremail, $mailinglist_name);
				}
				if ($no_newsletter_ok)
				{
					$this->sync_mp_user_no_newsletter($mp_user_id);
				}
				if ($newsletter_ok)
				{
					$this->sync_mp_user_newsletter($mp_user_id);
				}

				foreach ($row as $k => $v)
				{
					if ($k == $this->emailcol) continue;
					if ($k == $this->namecol) continue;

					$this->sync_mp_usermeta($mp_user_id, $k, $v);
				}
			}
		}
		return $i;
	}

	function mailinglist_default($default)
	{
		return (isset($this->mailinglist_ID)) ? $this->mailinglist_ID : $default;
	}
}
new MP_import_csv(__('Import your <strong>csv</strong> file.', MP_TXTDOM), __('Import Csv', MP_TXTDOM));