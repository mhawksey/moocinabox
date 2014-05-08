<?php
global $wp_roles;

$capabilities = MailPress::capabilities();
$capability_groups = MailPress::capability_groups();
$grouping_cap = array();
foreach ($capabilities as $capability => $v)	$grouping_cap[$v['group']] [$capability] = null;

$formname = substr(basename(__FILE__), 0, -4); 
?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table rc-table'>
		<tr>
			<th scope='row'></th>
<?php
foreach($wp_roles->role_names as $role => $name)
{
	if ('administrator' == $role) continue;
	$name = __($name);
?>
			<th scope='row'><strong><?php echo $name; ?></strong></th>
<?php
}
?>
		</tr>
<?php
$prev_groupname = false;
foreach ($capability_groups as $group => $groupname)
{
	if (!isset($grouping_cap[$group])) continue;
	$count = 0;
	$total_count = count($grouping_cap[$group]);

	foreach ($grouping_cap[$group] as $capability => $v)
	{
		$count++;
		$capname = $capabilities[$capability]['name'];
?>
		<tr<?php if ($total_count == $count) echo " class='mp_sep'"; ?>>
			<td><?php if ($prev_groupname != $groupname) {$prev_groupname = $groupname; echo "<strong><i>$groupname</i></strong>";} ?></td>
<?php
		foreach($wp_roles->role_names as $role => $name)
		{
			if ('administrator' == $role) continue;
			$rcs = get_option('MailPress_r&c_' . $role);
?>
			<td class='capacity'>
				<label for='<?php echo "check_" . $role . "_" . $capability; ?>'>
					<input id='<?php echo "check_" . $role . "_" . $capability; ?>' name='cap[<?php echo $role; ?>][<?php echo $capability; ?>]' type='checkbox'<?php checked( isset($rcs[$capability]) ); ?> />
					<span id='<?php echo $role . "_" . $capability; ?>' class='<?php echo (isset($rcs[$capability])) ? 'crok' : 'crko'; ?>'><?php echo $capname; ?></span>
				</label>
			</td>
<?php
		}
?>
		</tr>
<?php
	}
}
?>
	</table>
<?php MP_AdminPage::save_button(); ?>
</form>