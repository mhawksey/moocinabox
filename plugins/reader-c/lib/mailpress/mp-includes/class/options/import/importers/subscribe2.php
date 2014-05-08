<?php
class MP_import_subscribe2 extends MP_import_importer_
{
	var $id = 'subscribe2';

	function dispatch($step = 0) 
	{
		global $wpdb;
		$this->maintable	= $wpdb->prefix . 'subscribe2';

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

	function greet() 
	{
		$x = "<div style='text-align:center;'>\n";
		$x .= "<br />\n";
		$x .= __('First Things First', MP_TXTDOM);
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= __('Before importing your Subscribe2 datas : ', MP_TXTDOM);
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
	<?php _e('Howdy! Ready to import your <b>Subscribe2</b> data into <b>MailPress</b> ...', MP_TXTDOM); ?>
	<?php $nl = (class_exists('MailPress_newsletter'))  ? __("newsletters", MP_TXTDOM) : '' ; ?>
	<?php $ml = (class_exists('MailPress_mailinglist')) ? __("mailing lists", MP_TXTDOM) : '' ; ?>
	<p><?php _e('<b>Subscribe2</b> data is stored into the following tables :', MP_TXTDOM); ?></p>
	<ol>
		<li>usermeta
			<p>
				<?php _e("In this table, 'WP users' subscribers can subscribe to categories.", MP_TXTDOM); ?>
				<br />
				<?php printf(__('You will be able to convert this %1$s to subscriptions in %2$s.', MP_TXTDOM), __("'categories subscription'", MP_TXTDOM), (empty($nl) || empty($ml)) ? "$nl$ml" : "$nl, $ml"); ?>
			</p>
		</li>
		<li>subscribe2
			<p>
				<?php _e('In this table, subscribers have default subscription set by admin.', MP_TXTDOM); ?>
				<br />
				<?php printf(__('You will be able to convert this %1$s to subscriptions in %2$s.', MP_TXTDOM), __("'default subscription'", MP_TXTDOM), (empty($nl) || empty($ml)) ? "$nl$ml" : "$nl, $ml"); ?>
			</p>
		</li>
	</ol>
	<br />
	<p><?php _e('Note 1 : The tables are processed in the above order.', MP_TXTDOM); ?></p>
	<p><?php _e('Note 2 : If a subscriber already exists in MailPress, the settings for this email remains <b>unchanged</b>.', MP_TXTDOM); ?></p>

	<form method='post' action='<?php echo MailPress_import; ?>&amp;mp_import=subscribe2&amp;step=1'>
		<p class='submit'>
			<input class='button-primary' type='submit' name='Submit' value='<?php _e('Continue', MP_TXTDOM); ?>' />
		</p>
	</form>
</div>
<?php
	}

// step 1

	function validate_data()
	{
		$this->message_report(" ANALYSIS   !");
		if ($this->tableExists($this->maintable))
		{
			$this->message_report('            ! MySQL Table detected  >>> ' . $this->maintable);
			return true;
		}
		$this->message_report('** ERROR ** ! MySQL Table not detected  >>> ' . $this->maintable);

	 	$this->step1  = "<style type='text/css'> .general th {font-weight:bold;width:auto;} .general td, .general th {border:solid 1px #555;margin:0;padding:5px;vertical-align:top;} </style>";
	 	$this->step1 .= "<div>\n";
	 	$this->step1 .= "<h3>" . __('Data Analysis', MP_TXTDOM) . "</h3>\n";
	 	$this->step1 .= "<table class='form-table'>\n";
	 	$this->step1 .= "<tr>\n";
	 	$this->step1 .= "<th scope='row'>" . $this->maintable . "</th>\n";
	 	$this->step1 .= "<td>\n";
	 	$this->step1 .= "<p>" . __('*** ERROR *** Table not detected', MP_TXTDOM) . "</p>\n";
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

		$this->step1 = '';

		$countcat	= $wpdb->get_var( "SELECT count(*) FROM $wpdb->usermeta WHERE meta_key = 's2_subscribed' " );
		$subs		= $wpdb->get_var( "SELECT count(*) FROM $this->maintable " );

// $countcat 
		if ( $countcat || $subs )
		{
		 	$head1  = "<style type='text/css'> .general th {font-weight:bold;width:auto;} .general td, .general th {border:solid 1px #555;margin:0;padding:5px;vertical-align:top;} </style>";
		 	$head1 .= "<h3>" . __('Data Analysis', MP_TXTDOM) . "</h3>\n";
		 	$head1 .= "<form action='" . MailPress_import . "&amp;mp_import=subscribe2&amp;step=2' method='post'><table class='form-table'>\n";

		 	$foot1 = "</table>\n";
			$foot1 .= "<p class='submit'>\n";
			$foot1 .= "<input class='button-primary' type='submit' value='" . esc_attr( __('Submit')) . "' />\n";
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

		if ($countcat)
		{
			$this->message_report("            ! " . sprintf('%1$s WP user(s) subscriber(s) found', $countcat ));
			$this->sniff_usermeta($countcat);
		}
		else
		{
			$this->message_report("** ERROR ** ! " . sprintf('Usermeta table: %1$s, no data', $wpdb->usermeta ));

			$this->step1 .= "<tr>\n";
			$this->step1 .= "<th scope='row'>" . $wpdb->usermeta . "</th>\n";
			$this->step1 .= "<td>\n";
			$this->step1 .= "<p>" . __('no data', MP_TXTDOM) . "</p>\n";
			$this->step1 .= "</td>\n";
			$this->step1 .= "</tr>\n";
		}

// $subs
		$subs = $wpdb->get_results( "SELECT active, count(*) as count FROM $this->maintable GROUP BY active ORDER BY active;" );

		if ($subs) 
		{
			$this->message_report("            !" . sprintf('%1$s Subscribe2 subscriber(s) found', count($subs) ));
			$this->sniff_subscribe2($subs);
		}
		elseif($wpdb->last_error)
		{
			$this->message_report("** ERROR ** ! " . sprintf('Database error : %1$s', $wpdb->last_error));

			$this->step1 .= "<tr>\n";
			$this->step1 .= "<th scope='row'>" . $this->maintable . "</th>\n";
			$this->step1 .= "<td>\n";
			$this->step1 .= $this->error('<p>' . sprintf(__("*** ERROR *** Database error : %1$s", MP_TXTDOM), $wpdb->last_error) . "</p>\n", false);
			$this->step1 .= "</td>\n";
			$this->step1 .= "</tr>\n";
		}
		else
		{
			$this->message_report("** ERROR ** ! " . sprintf('Main table : %1$s empty', $this->maintable ));

			$this->step1 .= "<tr>\n";
			$this->step1 .= "<th scope='row'>" . $this->maintable . "</th>\n";
			$this->step1 .= "<td>\n";
			$this->step1 .= "<p>" . __('no data', MP_TXTDOM) . "</p>\n";
			$this->step1 .= "</td>\n";
			$this->step1 .= "</tr>\n";
		}

		$this->step1 = $head1 . $this->step1 . $foot1;
		return $import;
	}

	function sniff_usermeta($countcat )
	{
		global $wpdb;

		$recap = array();
		$metas = $wpdb->get_results( "SELECT DISTINCT meta_value FROM $wpdb->usermeta WHERE meta_key = 's2_subscribed'" );

		if ($metas)
		{
			foreach ($metas as $meta)
			{
				if ('-1' == $meta->meta_value) continue;
				$x = explode(', ', $meta->meta_value);
				if (empty($x)) continue;
				$x = array_diff($x, $recap);
				foreach ($x as $y) array_push($recap, $y);
			}
		}
		sort($recap);

		$this->step1 .= "<tr>\n";
		$this->step1 .= "<th scope='row'>" . $wpdb->usermeta . "</th>\n";
		$this->step1 .= "<td>\n";

		if (class_exists('MailPress_sync_wordpress_user')) 
		{
			$this->step1 .= "<p>" . sprintf( __('You are using %1$s, usermeta will not be processed', MP_TXTDOM), "'MailPress_sync_wordpress_user'") . "</p>\n";
		}
		else
		{
			$this->step1 .= "<ul>\n";
			$this->step1 .= "<li>\n";
			$this->step1 .= sprintf(__('%1$s WP user(s) subscriber(s) found', MP_TXTDOM), $countcat ) . "\n";
			$this->step1 .= "</li>\n";
			$this->step1 .= "</ul>\n";

			if (!empty($recap))
			{
				$this->message_report("            ! " . '>>>> ' . count($recap) . ' Categories found');

				$this->step1 .= "<table class='general'>\n";
				$this->step1 .= "\n";
				$this->step1 .= "<thead>\n";	
				$this->step1 .= "<tr>\n";	
				$this->step1 .= "<th>\n";
				$this->step1 .= __('Categories used in Subscribe2', MP_TXTDOM) . "\n";
				$this->step1 .= "</th>\n";
				if (class_exists('MailPress_newsletter')) 
				{
					$this->step1 .= "<th>\n";
					$this->step1 .= __("MailPress Newsletters", MP_TXTDOM) . "\n";
					$this->step1 .= "</th>\n";
				}
				if (class_exists('MailPress_mailinglist')) 
				{
					$this->step1 .= "<th>\n";
					$this->step1 .= __('MailPress Mailing lists', MP_TXTDOM) . "\n";
					$this->step1 .= "</th>\n";
				}
				$this->step1 .= "</tr>\n";
				$this->step1 .= "</thead>\n";
				$this->step1 .= "<tbody>\n";

				foreach ($recap as $cat_id)
				{
					$this->step1 .= "<tr>\n";	
					$this->step1 .= "<td>\n";
					$this->step1 .= get_cat_name($cat_id) . "\n";
					$this->step1 .= "</td>\n";
					if (class_exists('MailPress_newsletter')) 
					{
						$this->step1 .= "<td style='text-align:center;'>\n";
						$dropdown_options = array('show_option_all' => __('None', MP_TXTDOM), 'echo' => 0, 'type' => 'select', 'name' => 'usermeta_nl_' . $cat_id, 'admin' => true, 'selected' => $this->get_newsletter_id($cat_id));
						$this->step1 .= MP_Newsletter::get_checklist(false, $dropdown_options) . "\n";
						$this->step1 .= "</td>\n";
					}
					if (class_exists('MailPress_mailinglist')) 
					{
						$this->step1 .= "<td>\n";
						$dropdown_options = array('show_option_all' => __('None', MP_TXTDOM), 'echo' => 0, 'type' => 'select', 'name' => 'usermeta_ml_' . $cat_id);
						$this->step1 .= MailPress_mailinglist::get_checklist(false, $dropdown_options) . "\n";
						$this->step1 .= "</td>\n";
					}

					$this->step1 .= "</tr>\n";	
				}
				$this->step1 .= "</tbody>\n";
				$this->step1 .= "</table>\n";
			}
		}
		$this->step1 .= "</td>\n";
		$this->step1 .= "</tr>\n";
	}

	function get_newsletter_id($cat_id, $admin = true)
	{
		do { $x = get_category($cat_id); if (!$x->category_parent) break; $cat_id = $x->category_parent; } while($x->category_parent);

		global $mp_registered_newsletters;
		$subscriptions = get_option(MailPress::option_name_subscriptions);

		$lib_nl = ($admin) ? 'desc' : 'display';

		foreach ($mp_registered_newsletters as $newsletter)
		{
			if (!isset($subscriptions['newsletters'][$newsletter['id']])) continue;
			if ($newsletter[$lib_nl]) 
				if (isset($newsletter['params']['cat_id'])) 
					if ($cat_id == $newsletter['params']['cat_id']) return $newsletter['id'];
 
		}

		return false;
	}

	function sniff_subscribe2($subs)
	{
		global $wpdb;

		$checklist_newsletters = $checklist_mailinglists = false;

		if (class_exists('MailPress_newsletter'))  $checklist_newsletters  = MailPress_newsletter::get_checklist(false, array('admin' => true));
		if (class_exists('MailPress_mailinglist')) $checklist_mailinglists = MailPress_mailinglist::get_checklist();

		$this->step1 .= "<tr>\n";
		$this->step1 .= "<th scope='row'>" . $this->maintable . "</th>\n";
		$this->step1 .= "<td>\n";
		$this->step1 .= "<ul>\n";

		foreach ($subs as $sub)
		{
			$status = __('waiting', MP_TXTDOM);
			if (1 == $sub->active) $status = __('active', MP_TXTDOM);

			$this->step1 .= "<li>\n";
			$this->step1 .= sprintf(__(' %1$s subscriber(s) will be imported with status : "%2$s" .', MP_TXTDOM), $sub->count, $status) . "\n";
			$this->step1 .= "</li>\n";
		}

		$this->step1 .= "</ul>\n";

		if ($checklist_newsletters || $checklist_mailinglists) 
		{
			$this->step1 .= __("Select subscriptions for ACTIVE subscribers :", MP_TXTDOM) . "\n"; 

			$this->step1 .= "<table class='general'>\n";
			$this->step1 .= "\n";
			$this->step1 .= "<thead>\n";
			$this->step1 .= "<tr>\n";	
	
	 		if ($checklist_newsletters) 
			{
				$this->step1 .= "<th>\n";	
 				$this->step1 .= __("MailPress Newsletters", MP_TXTDOM) . "\n";	
				$this->step1 .= "</th>\n";	
			}
			if ($checklist_mailinglists)
			{
				$this->step1 .= "<th>\n";	
 				$this->step1 .= __("MailPress Mailing lists", MP_TXTDOM) . "\n";	
				$this->step1 .= "</th>\n";
			}
			$this->step1 .= "</tr>\n";
			$this->step1 .= "</thead>\n";
			$this->step1 .= "<tbody>\n";
			$this->step1 .= "<tr>\n";
			$this->step1 .= "<td>\n";
			$this->step1 .= $checklist_newsletters . "\n";
			$this->step1 .= "</td>\n";
			if ($checklist_mailinglists)
			{
				$this->step1 .= "<td>\n";
				$this->step1 .= $checklist_mailinglists . "\n";
				$this->step1 .= "</td>\n";
			}
			$this->step1 .= "</tr>\n";
			$this->step1 .= "</tbody>\n";
			$this->step1 .= "</table>\n";
		}
		$this->step1 .= "</td>\n";
		$this->step1 .= "</tr>\n";
		return true;
	}

// step 2

	function import() 
	{
		global $wpdb;
		if (!class_exists('MailPress_sync_wordpress_user')) $this->import_usermeta();
		$this->import_subscribe2();
		return true;
	}

	function import_usermeta() 
	{
		global $wpdb;
		$metas = $wpdb->get_results( "SELECT * FROM $wpdb->usermeta WHERE meta_key = 's2_subscribed'" );

		if ($metas) 
		{
			$this->message_report(" IMPORTING  ! " . $wpdb->usermeta);

			foreach ($metas as $meta)
			{
				$user 	= get_userdata($meta->user_id);
				$email 	= $user->user_email;
				$mp_user_id	= MP_User::get_id_by_email($email);

				if ( 'deleted' != MP_User::get_status_by_email($email) )
				{
					$xl = strlen($email);
					$xl = ((25 - $xl) < 0) ? 0 : 25 - $xl;
					$x = $email . str_repeat( ' ', $xl);
					$this->message_report(" **WARNING* ! $x already exists.");
					continue;
				}

				$mp_user_id = $this->sync_mp_user($email);

				if ('-1' == $meta->meta_value) continue;
				$recap = explode(', ', $meta->meta_value);
				if (empty($recap)) continue;
				
				unset($_POST['keep_newsletters']);
				unset($_POST['keep_mailinglists']);

				foreach ($recap as $cat_id)
				{
					if (isset( $_POST['usermeta_nl_' . $cat_id])) if ('' != $_POST['usermeta_nl_' . $cat_id]) $_POST['keep_newsletters'] [$_POST['usermeta_nl_' . $cat_id]] = 'on';
					if (isset( $_POST['usermeta_ml_' . $cat_id])) if ('' != $_POST['usermeta_ml_' . $cat_id]) $_POST['keep_mailinglists'][$_POST['usermeta_ml_' . $cat_id]] = 'on';
				}
				if (isset($_POST['keep_newsletters'])) 	MailPress_newsletter::update_checklist($mp_user_id);
				if (isset($_POST['keep_mailinglists'])) 	MailPress_mailinglist::update_checklist($mp_user_id);
			}
		}
		return true;
	}

	function import_subscribe2() 
	{
		global $wpdb;
		$subs = $wpdb->get_results( "SELECT email, active FROM $this->maintable " );

		if ($subs) 
		{
			$this->message_report(" IMPORTING  ! " .$this->maintable );
			foreach ($subs as $sub)
			{
				switch ($sub->active)
				{
					case 1 :
						if ( 'deleted' != MP_User::get_status_by_email($sub->email) )
						{
							$xl = strlen($sub->email);
							$xl = ((25 - $xl) < 0) ? 0 : 25 - $xl;
							$x = $sub->email . str_repeat( ' ', $xl);
							$this->message_report(" **WARNING* ! $x already exists.");
							break;
						}

						$mp_user_id = $this->sync_mp_user($sub->email);

						if ($mp_user_id)
						{
							 if (isset($_POST['keep_newsletters'])) 	MailPress_newsletter::update_checklist($mp_user_id);
							 if (isset($_POST['keep_mailinglists'])) 	MailPress_mailinglist::update_checklist($mp_user_id);
						}
					break;
					default :
						if ( 'deleted' != MP_User::get_status_by_email($sub->email) ) break;

						$mp_user_id = $this->sync_mp_user($sub->email, 'waiting');
					break;
				}
			}

		}
		elseif($wpdb->last_error)
			$this->message_report("** ERROR ** ! " . sprintf('Database error : %1$s', $wpdb->last_error));
		else
			$this->message_report("** ERROR ** ! " . sprintf('Main table : %1$s empty', $this->maintable ));
		return true;
	}
}
new MP_import_subscribe2(__('Import data from <strong>subscribe2</strong> plugin.', MP_TXTDOM), __('Import from Subscribe2', MP_TXTDOM));