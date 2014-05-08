<?php
$url_parms = MP_AdminPage::get_url_parms();

$h2 = __('MailPress Themes', MP_TXTDOM); 

$max_cols = 3;
$max_rows = 5;

//
// MANAGING RESULTS/PAGINATION/
//

$_per_page = $max_cols * $max_rows;
$url_parms['paged'] = isset($url_parms['paged']) ? $url_parms['paged'] : 1;
do
{
	$start = ( $url_parms['paged'] - 1 ) * $_per_page;

	list($themes, $total, $th) = MP_AdminPage::get_list(array('start' => $start, '_per_page' => $_per_page, 'url_parms' => $url_parms));

	$url_parms['paged']--;		
} while ( $total <= $start );
$url_parms['paged']++;

$page_links = paginate_links	(array(	'base' => add_query_arg( 'paged', '%#%' ), 
							'format' => '', 
							'total' => ceil($total / $_per_page), 
							'current' => $url_parms['paged']
						)
					);
if ($url_parms['paged'] <= 1) unset($url_parms['paged']);

?>
<div class='wrap'>
	<div id="icon-mailpress-themes" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php
		if ( ! $th->validate_current_theme() ) 
		{
?>
<div id='message1' class='updated fade'><p><?php _e('The active MailPress theme is broken.  Reverting to the default MailPress theme.', MP_TXTDOM); ?></p></div>
<?php 
		}
		elseif ( isset($_GET['activated']) ) 
		{
?>
<div id='message2' class='updated fade'><p><?php printf(__('New MailPress theme activated.', MP_TXTDOM), home_url() . '/'); ?></p></div>
<?php 
		}
?>
	<h3><?php _e('Current Theme'); ?></h3>
	<div id="current-theme">
<?php 

$ct = $th->current_theme_info(); 

if ( $ct->screenshot ) : 
?>
		<img style='margin: 0 10px 0 0;' src='<?php echo $ct->theme_root_uri . '/' .  $ct->stylesheet . '/' . $ct->screenshot; ?>' alt='<?php _e('Current MailPress theme preview', MP_TXTDOM); ?>' />
<?php endif; ?>
		<h4><?php printf(__('%1$s %2$s by %3$s'), $ct->title, $ct->version, $ct->author); ?></h4>
		<p class="description"><?php echo $ct->description; ?></p>
<?php if ($ct->parent_theme) { ?>
		<p><?php printf(__('The template files are located in <code>%2$s</code>.  The stylesheet files are located in <code>%3$s</code>.  <strong>%4$s</strong> uses templates from <strong>%5$s</strong>.  Changes made to the templates will affect both MailPress themes.', MP_TXTDOM), $ct->title, $ct->template_dir, $ct->stylesheet_dir, $ct->title, $ct->parent_theme); ?></p>
<?php } else { ?>
		<p><?php printf(__('All theme&#8217;s files in : <code>%2$s</code>.', MP_TXTDOM), $ct->title, $ct->template_dir, $ct->stylesheet_dir); ?></p>
<?php } ?>
<?php if ( $ct->tags ) : ?>
		<p><?php _e('Tags:'); ?> <?php echo join(', ', $ct->tags); ?></p>
<?php endif; ?>
	</div>
	<div class="clear"></div>

<?php
if ($themes) 
{
	$row = $col = 0;
	$rows = ceil(count($themes) / $max_cols);

	if ( $page_links ) echo "\n<div class='tablenav'><div class='tablenav-pages'>$page_links</div><h3>" . __('Available Themes') . "</h3><br class='clear' /></div>\n"; 
	else echo "<h3>" . __('Available Themes') . "</h3>";
?>

	<table id='availablethemes'>
		<tbody>
<?php
	foreach($themes as $theme)
	{
		if (!$col)
		{
			$row++;
?>
		<tr>
<?php
		}
		$col++;

		MP_AdminPage::get_row($theme, $row, $col, $rows);

		if ($col == $max_cols)
		{
			$col = 0;
?>
		</tr>
<?php
		}
	}
	if ($col) echo str_repeat('<td></td>', $max_cols - $col) . '</tr>';
?>
		</tbody>
	</table>
<?php
}
?>
	<br class="clear" />
<?php if ( $page_links ) : ?>
	<div class="tablenav">
<?php echo "	<div class='tablenav-pages'>$page_links</div>"; ?>
		<br class="clear" />
	</div>
<?php endif; ?>
	<br class="clear" />
<?php
// List broken themes, if any.
$broken_themes = $th->get_broken_themes();
if ( count($broken_themes) ) 
{
?>
	<h2><?php _e('Broken Themes'); ?></h2>
	<p><?php _e('The following themes are installed but incomplete.  Themes must have a stylesheet and a template.'); ?></p>

	<table class='widefat' width="100%">
		<thead>
			<tr>
				<th><?php _e('Folder', MP_TXTDOM); ?></th>
				<th><?php _e('Name', MP_TXTDOM); ?></th>
				<th><?php _e('Description', MP_TXTDOM); ?></th>
			</tr>
		</thead>
<?php
	$class = '';
	foreach ($broken_themes as $theme) 
	{
		$class = (" class='alternate'" == $class) ? '' : " class='alternate'";
?>
		<tbody>
			<tr<?php echo $class;?>>
				 <td><?php echo $theme['Folder'];?></td>
				 <td><?php echo $theme['Title'];?></td>
				 <td><?php echo $theme['Description'];?></td>
			</tr>
		</tbody>
<?php
	}
?>
	</table>
<?php
}
?>
</div>