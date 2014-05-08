<?php
$url_parms = MP_AdminPage::get_url_parms(array('s', 'paged', 'id'));

//
// MANAGING H2
//

$h2 = __('Forms', MP_TXTDOM);

//
// MANAGING MESSAGE
//

$messages[1] = __('Form added.', MP_TXTDOM);
$messages[2] = __('Form updated.', MP_TXTDOM);
$messages[3] = __('Form deleted.', MP_TXTDOM);
$messages[4] = __('Forms deleted.', MP_TXTDOM);
$messages[91] = __('Form not added.', MP_TXTDOM);
$messages[92] = __('Form not updated.', MP_TXTDOM);

if (isset($_GET['message']))
{
	$message = $messages[$_GET['message']];
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}

//
// MANAGING CONTENT
//

$bulk_actions[''] 	= __('Bulk Actions');
$bulk_actions['delete']	= __('Delete', MP_TXTDOM);

global $action;
wp_reset_vars(array('action'));
if ('edit' == $action)
{
	$action = 'edited';
	$cancel = "<input type='submit' class='button' name='cancel' value=\"" . __('Cancel', MP_TXTDOM) . "\" />\n";

	$id = (int) $url_parms['id'];
	$form = MP_Form::get($id);

	$h3 = sprintf(__('Edit Form # %1$s', MP_TXTDOM), $id);
	$hb3= __('Update');
	$hbclass = '-primary';
}
else
{
	$action = MP_AdminPage::add_form_id;
	$cancel = '';

	$form = new stdClass();

	$h3 = $hb3 = __('Add Form', MP_TXTDOM);
	$hbclass = '';
}

// Form settings tab

$tabs = array('attributes' => __('Attributes', MP_TXTDOM), 'options' => __('Options', MP_TXTDOM), 'messages' => __('Messages', MP_TXTDOM), 'visitor' => __('Visitor', MP_TXTDOM), 'recipient' => __('Recipient', MP_TXTDOM) );
if ( isset($_GET['action']) && ('edit' == $_GET['action']) ) $tabs['html'] = __('Html', MP_TXTDOM); 

// Form templates

$form_templates = new MP_Form_templates();
$xform_template = $form_templates->get_all();

// Subscribing visitor actions

$xvisitor_subscriptions['0'] = __('no', MP_TXTDOM);
$xvisitor_subscriptions['1'] = __('not active', MP_TXTDOM);
$xvisitor_subscriptions['2'] = __('to be confirmed', MP_TXTDOM);
$xvisitor_subscriptions['3'] = __('active', MP_TXTDOM);

$xvisitor_mail['0'] = __('no', MP_TXTDOM);
$xvisitor_mail['1'] = __('to be confirmed', MP_TXTDOM);
$xvisitor_mail['2'] = __('yes', MP_TXTDOM);

// Mail themes and templates

$th = new MP_Themes();
$themes = $th->themes; 

foreach($themes as $key => $theme)
{
	if ( 'plaintext' == $theme['Stylesheet']) unset($themes[$key]);
	if ( '_' == $theme['Stylesheet'][0] )     unset($themes[$key]);
}

if (!isset($form->settings['recipient']['theme'])) $form->settings['recipient']['theme'] = $themes[$th->current_theme]['Stylesheet'];
if (!isset($form->settings['visitor'  ]['theme'])) $form->settings['visitor'  ]['theme'] = $themes[$th->current_theme]['Stylesheet'];

$xtheme = $xtemplates = array();
foreach ($themes as $theme)
{
	if ( 'plaintext' == $theme['Stylesheet'] ) continue;
	if ( '_'         == $theme['Stylesheet'][0] ) continue;

	$xtheme[$theme['Stylesheet']] = $theme['Stylesheet'];
	if (!$templates = $th->get_page_templates($theme['Stylesheet'])) $templates = $th->get_page_templates($theme['Stylesheet'], true);

	$xtemplates[$theme['Stylesheet']] = array();
	foreach ($templates as $key => $value)
	{
		if (strpos($key, 'form') !== 0 ) continue;
		$xtemplates[$theme['Stylesheet']][$key] = $key;
	}
	if (!empty($xtemplates[$theme['Stylesheet']])) ksort($xtemplates[$theme['Stylesheet']]);

	array_unshift($xtemplates[$theme['Stylesheet']], __('none', MP_TXTDOM));
}

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
<div class='wrap nosubsub'>
	<div id='icon-mailpress-tools' class='icon32'><br /></div>
	<h2>
		<?php echo esc_html( $h2 ); ?> 
<?php if ( isset($url_parms['s']) ) printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_attr( $url_parms['s'] ) ); ?>
	</h2>
<?php if (isset($message)) MP_AdminPage::message($message, ($_GET['message'] < 90)); ?>
	<form class='search-form topmargin' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MP_AdminPage::screen; ?>' />

		<p class='search-box'>
			<input type='text' name='s' value="<?php if (isset($url_parms['s'])) echo esc_attr( $url_parms['s'] ); ?>" class="search-input" />
			<input type='submit' value="<?php _e( 'Search', MP_TXTDOM ); ?>" class='button' />
		</p>

	</form>
	<br class='clear' />
	<div id='col-container'>
		<div id='col-right'>
			<div class='col-wrap'>
				<form id='posts-filter' action='' method='get'>
					<input type='hidden' name='page' value='<?php echo MP_AdminPage::screen; ?>' />
					<div class='tablenav'>
<?php MP_AdminPage::pagination($total); ?>
						<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions); ?>
						</div>
						<br class='clear' />
					</div>
					<div class='clear'></div>
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
<?php if ($items) : ?>
<?php foreach ($items as $item) 		echo MP_AdminPage::get_row( $item->id, $url_parms ); ?>
<?php endif; ?>
						</tbody>
					</table>
					<div class='tablenav'>
<?php MP_AdminPage::pagination($total, 'bottom'); ?>
						<div class='alignleft actions'>
<?php	MP_AdminPage::get_bulk_actions($bulk_actions, 'action2'); ?>
						</div>
						<br class='clear' />
					</div>
					<br class='clear' />
				</form>
			</div>
		</div><!-- /col-right -->
		<div id='col-left'>
			<div class='col-wrap'>
				<div class='form-wrap'>
					<h3><?php echo $h3; ?></h3>
					<div id='ajax-response'></div>
					<form name='<?php echo $action; ?>'  id='<?php echo $action; ?>'  method='post' action='' class='<?php echo $action; ?>:<?php echo MP_AdminPage::list_id; ?>: validate'>
						<input type='hidden' name='action'   value='<?php echo $action; ?>' />
<?php MP_AdminPage::post_url_parms($url_parms, array('s', 'paged', 'id')); ?>
						<?php wp_nonce_field('update-' . MP_AdminPage::tr_prefix_id); ?>
						<div class="form-field form-required" style='margin:0;padding:0;'>
							<label for='form_label'><?php _e('Label', MP_TXTDOM); ?></label>
							<input name='label' id='form_label' type='text' value="<?php if (isset($form->label)) echo esc_attr($form->label); ?>" size='40' aria-required='true' />
							<p>&#160;</p>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<span style='float:right'>
								<span class='description'><small><?php _e('template', MP_TXTDOM); ?></small></span>
								<select id='f_template' name='template' style='margin-right:14px;'>
<?php MP_AdminPage::select_option($xform_template, (isset($form->template)) ? $form->template : 'default' ); ?>
								</select>
							</span>
							<label for='form_description' style='display:inline;'><?php _e('Description', MP_TXTDOM); ?></label>
							<input name='description' id='form_description' type='text' value="<?php if (isset($form->description)) echo esc_attr($form->description); ?>" size='40' />
							<p><small><?php _e('The description can be use to give further explanations', MP_TXTDOM); ?></small></p>
						</div>
						<div id='form_settings' class='form field form_settings' style='margin-top:18px;'>
							<ul>
<?php foreach($tabs as $tab_type => $tab) echo "<li><a href='#settings_tab_$tab_type'><span>$tab</span></a></li>\n"; ?>
							</ul>
							<div style='clear:both;' >
<?php
	foreach($tabs as $tab_type => $tab) 
	{
		echo "								<div id='settings_tab_$tab_type' class='ui-tabs settings_form_tabs settings_$tab_type'>\n";
		switch ($tab_type)
		{
			case 'attributes' : 
?>
									<span class='description'><small>class="</small></span><input type='text' name='settings[attributes][class]' id='form_attribute_class' 	value="<?php if (isset($form->settings['attributes']['class'])) echo esc_attr($form->settings['attributes']['class']); ?>" size='40' style='width:80%'  /><span class='description'><small>"</small></span><br />
									<span class='description'><small>style="</small></span><input type='text' name='settings[attributes][style]' id='form_attribute_style' 	value="<?php if (isset($form->settings['attributes']['style'])) echo esc_attr($form->settings['attributes']['style']); ?>" size='40' style='width:80%'  /><span class='description'><small>"</small></span><br />
									<input type='text' name='settings[attributes][misc]' id='form_attribute_misc' 	value="<?php if (isset($form->settings['attributes']['misc'])) echo esc_attr($form->settings['attributes']['misc']); ?>" size='40' style='width:98%'  /><br />
									<span class='description'><i style='color:#666;font-size:8px;'><?php _e("other attributes except 'name' & 'action'", MP_TXTDOM); ?></i></span>
<?php
			break;
			case 'options'    : 
?>
									<input type='checkbox' name='settings[options][reset]' id='form_option_reset' value='1'<?php checked('1', ((isset($form->settings['options']['reset'])) ? 1 : 0)); ?> style='width:auto;' />
									<label for='form_option_reset' style='display:inline;'><small><?php _e('Reset after submission', MP_TXTDOM); ?></small></label>
<?php
			break;
			case 'messages'    : 
?>
									<label for='f_message_ok'><small><?php _e('When processing form is successful', MP_TXTDOM); ?></small></label>
									<input name='settings[message][ok]' id='f_message_ok' type='text' value="<?php if (isset($form->settings['message']['ok'])) echo esc_attr($form->settings['message']['ok']); ?>" size='40' style='width:98%;' />
									<label for='f_message_ko'><small><?php _e('When processing form has failed', MP_TXTDOM); ?></small></label>
									<input name='settings[message][ko]' id='f_message_ko' type='text' value="<?php if (isset($form->settings['message']['ko'])) echo esc_attr($form->settings['message']['ko']); ?>" size='40' style='width:98%;' />
<?php
			break;

			case 'recipient'    : 
?>
									<div id='div_form_toemail'>
										<label for='form_toemail'><small><?php _e('Email', MP_TXTDOM); ?></small></label>
										<input type="text" id='form_toemail' name='settings[recipient][toemail]' value="<?php if (isset($form->settings['recipient']['toemail'])) echo $form->settings['recipient']['toemail']; ?>" size="40" style='width:auto;' aria-required='true' class='form-required'/>
									</div>
									<label for='form_toname'><small><?php _e('Name', MP_TXTDOM); ?></small></label>
									<input type="text" id='form_toname' name='settings[recipient][toname]' value="<?php if (isset($form->settings['recipient']['toname'])) echo esc_attr($form->settings['recipient']['toname']); ?>" size="40" style='width:auto;' />
									<label for='recipient_theme'><small><?php _e('Mail Theme/Template', MP_TXTDOM); ?></small></label>
									<select id='recipient_theme' name='settings[recipient][theme]'>
<?php MP_AdminPage::select_option($xtheme, $form->settings['recipient']['theme'] ); ?>
									</select>
<?php 
foreach ($xtemplates as $key => $xtemplate)
{
$xx = '0';
if (isset($form->settings['recipient']['template']) && $key == $form->settings['recipient']['theme']) $xx = $form->settings['recipient']['template'];
?>
									<select name='settings[recipient][th][<?php echo $key; ?>][tm]' id='recipient_<?php echo $key; ?>' class='<?php if ($key != $form->settings['recipient']['theme']) echo 'mask ';?>recipient_template'>
<?php MP_AdminPage::select_option($xtemplate, $xx);?>
									</select>
<?php
}
			break;
			case 'visitor'    : 
?>
									<label for='visitor_subscription'><small><?php _e('Subscription option', MP_TXTDOM); ?></small></label>
									<select id='visitor_subscription' name='settings[visitor][subscription]'>
<?php MP_AdminPage::select_option($xvisitor_subscriptions, (isset($form->settings['visitor']['subscription'])) ? $form->settings['visitor']['subscription'] : 0 ); ?>
									</select>
									<small><?php _e('Becomes a subscriber', MP_TXTDOM); ?></small>
									<div style='margin:0px;padding:0px;border:none;' class='<?php echo (isset($form->settings['visitor']['subscription']) && ($form->settings['visitor']['subscription'] != '0')) ? '' : 'mask '; ?>visitor_subscription_selected'>
<?php do_action('MailPress_form_visitor_subscription', $form); ?>
									</div>
									<label for='visitor_mail'><small><?php _e('Mail option', MP_TXTDOM); ?></small></label>
									<select id='visitor_mail' name='settings[visitor][mail]'>
<?php MP_AdminPage::select_option($xvisitor_mail, (isset($form->settings['visitor']['mail'])) ? $form->settings['visitor']['mail'] : 0 ); ?>
									</select>
									<small><?php _e('Receives a copy', MP_TXTDOM); ?></small>
									<div style='margin:0px;padding:0px;border:none;' class='<?php echo (isset($form->settings['visitor']['mail']) && ($form->settings['visitor']['mail'] != '0')) ? '' : 'mask '; ?>visitor_mail_selected'>
										<label for='visitor_theme'><small><?php _e('Mail Theme/Template', MP_TXTDOM); ?></small></label>
										<select id='visitor_theme' name='settings[visitor][theme]'>
<?php MP_AdminPage::select_option($xtheme, $form->settings['visitor']['theme']); ?>
										</select>
<?php 
foreach ($xtemplates as $key => $xtemplate)
{
$xx = '0';
if (isset($form->settings['visitor']['template']) && $key == $form->settings['visitor']['theme']) $xx = $form->settings['visitor']['template'];
?>
										<select name='settings[visitor][th][<?php echo $key; ?>][tm]' id='visitor_<?php echo $key; ?>' class='<?php if ($key != $form->settings['visitor']['theme']) echo 'mask ';?>visitor_template'>
<?php MP_AdminPage::select_option($xtemplate, $xx);?>
										</select>
<?php
}
?>
									</div>
<?php
			break;
			case 'html'       : 
				$html = $form_templates->get_form_template($form->template);
				if (!$html) $html = '{{form}}';

				$search = $replace = array();
				$search[] = '{{label}}'; 	$replace[] = $form->label;
				$search[] = '{{description}}'; 	$replace[] = $form->description;
				$search[] = '{{form}}'; 	$replace[] = sprintf('%1$s<!-- %2$s --></form>', MP_Form::get_tag( $form ), __('form content', MP_TXTDOM) );
				$search[] = '{{message}}'; 	$replace[] = sprintf ('<!-- %1$s -->', __('ok/ko message', MP_TXTDOM) );
				$html = str_replace($search, $replace, $html );
?>
									<div class="filter-img bkgndc bd1sc" style='font-family:"Courier new", serif;font-size:11px;font-style:normal;'>
										<?php echo htmlspecialchars($html, ENT_QUOTES); ?>
									</div>
									<p><small><?php printf(__('Template : %1$s', MP_TXTDOM), $form->template); ?></small></p>
<?php
			break;
		}
		echo "								</div>\n";
	}
?>									
							</div>
						</div>
						<p style='margin:15px 0;'>
							<input type='submit' class='button<?php echo $hbclass; ?>' name='submit' id='form_submit' value="<?php echo $hb3; ?>" />
							<?php echo $cancel; ?>	
						</p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div><!-- /wrap -->