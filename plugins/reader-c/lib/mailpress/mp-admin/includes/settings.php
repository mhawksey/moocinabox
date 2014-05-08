<?php
global $wpdb, $mp_general, $mp_subscriptions;

$_tabs['general'] = __('General', MP_TXTDOM);

if (isset($_POST['formname']))
{
	$form_invalid = 'form-invalid';
	$no_error = true;

	if (substr($_POST['formname'], -5) == '.form') include('settings/' . substr($_POST['formname'], 0, -5) . '.php');
}
if ($mp_general)
{
	$t = apply_filters('MailPress_Swift_Connection_type', 'SMTP');
	$_tabs['connection_' . strtolower($t)] = $t;
	$_tabs = apply_filters('MailPress_settings_tab_connection', $_tabs);

	$_tabs['test'] = __('Test', MP_TXTDOM);

	$_tabs = apply_filters('MailPress_settings_tab', $_tabs);

	$_tabs['logs'] = __('Logs', MP_TXTDOM);
}
$divs = array();
?>
<div class='wrap'>
	<div id='icon-mailpress-settings' class='icon32'><br /></div>
	<h2><?php _e('MailPress Settings', MP_TXTDOM); ?></h2>
<?php if (isset($message)) MP_AdminPage::message($message, $no_error); ?>
	<div id='example'>
		<ul class='bkgndc tablenav<?php if (!$mp_general) echo ' ui-tabs-nav'; ?>' style='padding:3px 8px 0;vertical-align:middle;'>
<?php 
	foreach($_tabs as $_tab => $desc)
	{
		$class = (isset($mp_general['tab']) && ($mp_general['tab'] == $_tab)) ? " class='ui-tabs-selected'" : '';
		echo "\t\t\t<li$class><a href='#fragment-$_tab' title='" . esc_attr($desc) . "'><span class='button-secondary'>$desc</span></a></li>\n";
	}
?>
		</ul>
<?php
	foreach($_tabs as $_tab => $desc)
	{
?>
		<div id='fragment-<?php echo $_tab; ?>' style='clear:both;'>
			<?php include("settings/$_tab.form.php"); ?>
		</div>
<?php

	}
?>
	</div>
</div>