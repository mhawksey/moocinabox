<?php
class MP_import_subscribe_to_comments extends MP_import_importer_
{
	var $id = 'subscribe_to_comments';

	function dispatch($step = 0) 
	{
		global $wpdb;
		$this->column_name = 'comment_subscribe';

		if (isset($_GET['step']) && !empty($_GET['step'])) $step = (int) $_GET['step'];

		$this->header();
		switch ($step) 
		{
			case 0 :
				$this->greet();
			break;
			case 1 :
				$this->start_trace($step);
				$validate_data = $this->validate_data();
				if ( $validate_data )
				{
					$sniff = $this->sniff();
					$this->end_trace($sniff);
					if ($sniff)
						echo $this->step1;
					else
						$this->error($this->step1);
				}
				else
				{
					$this->end_trace($validate_data);
					$this->error($this->step1);
				}
			break;
			case 2:
				$this->start_trace($step);
				$import = $this->import();
				$this->end_trace($import);
				if ($import)
					$this->success('<p>' . __("<b>Data imported</b>", MP_TXTDOM) . '</p>');
				else 
					$this->error();
			break;
		}
		$this->footer();
	}

// step 0

	function greet() {
		$x = "<div style='text-align:center;'>\n";
		$x .= "<br />\n";
		$x .= __('First Things First', MP_TXTDOM);
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= __("Before importing your 'Subscribe to comments' datas : ", MP_TXTDOM);
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= "<span style='color:red;font-weight:bold;'>";
		$x .= __('SAVE YOUR DATABASE', MP_TXTDOM);
		$x .= "</span>\n";
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= __('and make sure you can restore it !', MP_TXTDOM);
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= "</div>\n";
?>
<?php MP_AdminPage::message($x, false); ?>
<br />
<div>
	<?php _e('Howdy! Ready to import your <b>Subscribe to comments</b> data into <b>MailPress</b> ...', MP_TXTDOM); ?>

	<p><?php _e('<b>Subscribe to comments</b> data are stored into the following table :', MP_TXTDOM); ?></p>
	<ol>
		<li>comments
			<p>
				<?php _e('In this table, subscribers can subscribe to posts comments.', MP_TXTDOM); ?>
				<br />
				<?php _e('You will be able to convert these subscriptions to MailPress.', MP_TXTDOM); ?>
			</p>
		</li>
	</ol>
	<br />
	<p><?php _e('Note : If a subscriber already exists in MailPress, only the subscriptions to comments are added.', MP_TXTDOM); ?></p>

	<form method='post' action='<?php echo MailPress_import; ?>&amp;mp_import=subscribe_to_comments&amp;step=1'>
		<p class='submit'>
			<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Continue', MP_TXTDOM); ?>' />
		</p>
	</form>
</div>
<?php
	}

// step 1

	function validate_data()
	{
		$this->message_report(" ANALYSIS   !");

		global $wpdb;
		foreach ( (array) $wpdb->get_col("DESC $wpdb->comments", 0) as $column )
		{
			if ($column == $this->column_name)
			{
				$this->message_report('            ! Subscribe to comments column >>> ' . $this->column_name . ' detected in >>> ' . $wpdb->comments);
				return true;
			}
		}
		$this->message_report('** ERROR ** ! Subscribe to comments column NOT in ' . $wpdb->comments);

	 	$this->step1  = "<style type='text/css'> .general th {font-weight:bold;width:auto;} .general td, .general th {border:solid 1px #555;margin:0;padding:5px;vertical-align:top;} </style>";
	 	$this->step1 .= "<div>\n";
	 	$this->step1 .= "<h3>" . __('Data Analysis', MP_TXTDOM) . "</h3>\n";
	 	$this->step1 .= "<table class='form-table'>\n";
	 	$this->step1 .= "<tr>\n";
	 	$this->step1 .= "<th scope='row'>" . $this->column_name . "</th>\n";
	 	$this->step1 .= "<td>\n";
	 	$this->step1 .= "<p>" . sprintf(__('*** ERROR *** Column not detected in %1$s', MP_TXTDOM), $wpdb->comments) . "</p>\n";
	 	$this->step1 .= "</td>\n";
	 	$this->step1 .= "</tr>\n";
	 	$this->step1 .= "</table>\n";
	 	$this->step1 .= '</div>';

		return false;
	}

	function sniff() 
	{
		global $wpdb;
		$import = false;

		$this->step1  = '';

		$subs = $wpdb->get_results( "SELECT distinct LCASE(comment_author_email) as email FROM $wpdb->comments WHERE comment_subscribe='Y' AND comment_approved = '1' " );

		if ( $subs )
		{
		 	$head1  = "<style type='text/css'> .general th {font-weight:bold;width:auto;} .general td, .general th {border:solid 1px #555;margin:0;padding:5px;vertical-align:top;} </style>";
		 	$head1 .= "<h3>" . __('Data Analysis', MP_TXTDOM) . "</h3>\n";
		 	$head1 .= "<form action='" . MailPress_import . "&amp;mp_import=subscribe_to_comments&amp;step=2' method='post'><table class='form-table'>\n";

		 	$foot1 = "</table>\n";
			$foot1 .= "<p class='submit'>\n";
			$foot1 .= "<input type='submit' value='" . esc_attr( __('Submit')) . "' />\n";
			$foot1 .= "</p>\n";
			$foot1 .= "</form>\n";

			$import = true; 
		}
		else
		{ 	
		 	$head1  = "<style type='text/css'> .general th {font-weight:bold;width:auto;} .general td, .general th {border:solid 1px #555;margin:0;padding:5px;vertical-align:top;} </style>";
		 	$head1 .= "<h3>" . __('Data Analysis', MP_TXTDOM) . "</h3>\n";
			$head1 = "<table class='form-table'>\n";

		 	$foot1 = "</table>\n";
		}

		if ($subs)
		{
			$this->message_report('             ! ' . sprintf('%1$s subscriber(s) found', count($subs) ));

			$this->step1 .= "<tr>\n";
			$this->step1 .= "<th scope='row'>" . $wpdb->comments . "</th>\n";
			$this->step1 .= "<td>\n";
			$this->step1 .= "<p>" .  sprintf(__('%1$s subscriber(s) found', MP_TXTDOM), count($subs) ) . "</p>\n";
			$this->step1 .= "</td>\n";
			$this->step1 .= "</tr>\n";
		}
		else
		{
			$this->message_report('             ! Comments table: no data');

			$this->step1 .= "<tr>\n";
			$this->step1 .= "<th scope='row'>" . $wpdb->comments . "</th>\n";
			$this->step1 .= "<td>\n";
			$this->step1 .= "<p>" .  __('no data', MP_TXTDOM) . "</p>\n";
			$this->step1 .= "</td>\n";
			$this->step1 .= "</tr>\n";
		}
		$this->step1 = $head1 . $this->step1 . $foot1;
		return $import;
	}

// step 2

	function import() 
	{
		global $wpdb;
		$this->import_subscribe_to_comments();
		return true;
	}

	function import_subscribe_to_comments() 
	{
		global $wpdb;

		$subs = $wpdb->get_results( "SELECT comment_author_email as email, comment_post_ID as post_ID FROM $wpdb->comments WHERE comment_subscribe='Y' AND comment_approved = '1' order by email " );

		if ($subs) 
		{
			$this->message_report(" IMPORTING  !");

			$email = '';
			foreach ($subs as $sub)
			{
				if ($email != $sub->email)
				{
					$email = $sub->email;
					$mp_user_id = $this->sync_mp_user($email, 'waiting');
				}

				if ($mp_user_id)
				{
					$postid = $sub->post_ID; 
					update_post_meta($postid, '_MailPress_subscribe_to_comments_', $mp_user_id);
					new MP_Stat('c', $postid, 1);
					$this->message_report(" meta       ! user [18]=>  subscribed to post #" . $postid);
				}
			}
		}
		elseif($wpdb->last_error)
			$this->message_report("** ERROR ** ! " . sprintf('Database error : %1$s', $wpdb->last_error));
		else
			$this->message_report("** ERROR ** ! no data");
		return true;
	}
}
new MP_import_subscribe_to_comments(__('Import data from <strong>subscribe to comments</strong> plugin.', MP_TXTDOM), __('Import from Subscribe to comments', MP_TXTDOM));