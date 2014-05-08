<?php
if (!isset($filter_img)) $filter_img = get_option(MailPress_filter_img::option_name);
$filter_img['img'] = str_replace('<', '&lt;', $filter_img['img']);
$filter_img['img'] = str_replace('>', '&gt;', $filter_img['img']);
if (!isset($filter_img['align'])) $filter_img['align'] = 'none';
if (!isset($filter_img['extra_style'])) $filter_img['extra_style'] = '';

$formname = substr(basename(__FILE__), 0, -4); 
?>
<form name='<?php echo $formname ?>' action='' method='post' class='mp_settings'>
	<input type='hidden' name='formname' value='<?php echo $formname ?>' />
	<table class='form-table'>
		<tr valign='top'>
			<th scope='row'><?php _e('&lt;img&gt; defaults', MP_TXTDOM); ?></th>
			<td class='field'>
				<table>
					<tr>
						<td class='nobd'><?php _e('Alignment'); ?></td>
						<td class='nobd'>
							<input name='filter_img[align]' type='radio'<?php checked($filter_img['align'],'none'); ?> id='align-none' value='none' />
							<label for='align-none'   class='align image-align-none-label'><?php _e('None'); ?></label>
							<input name='filter_img[align]' type='radio'<?php checked($filter_img['align'],'left'); ?>  id='align-left' value='left' />
							<label for='align-left'   class='align image-align-left-label'><?php _e('Left'); ?></label>
							<input name='filter_img[align]' type='radio'<?php checked($filter_img['align'],'center'); ?>  id='align-center' value='center' />
							<label for='align-center' class='align image-align-center-label'><?php _e('Center'); ?></label>
							<input name='filter_img[align]' type='radio'<?php checked($filter_img['align'],'right'); ?> id='align-right' value='right' />
							<label for='align-right'  class='align image-align-right-label'><?php _e('Right'); ?></label>
						</td>
					</tr>
					<tr>
						<td class='nobd'><?php _e('style=', MP_TXTDOM); ?></td>
						<td class='nobd'>
							<textarea rows='2' cols='61' name='filter_img[extra_style]'  style='font-family:Courier, "Courier New", monospace;'><?php echo htmlspecialchars(stripslashes($filter_img['extra_style']),ENT_QUOTES);?></textarea>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th scope='row'><?php _e('Enter full &lt;img&gt; html tag', MP_TXTDOM); ?></th>
			<td>
				<textarea rows='2' cols='72' name='filter_img[img]'  style='font-family:Courier, "Courier New", monospace;'><?php echo esc_attr($filter_img['img']); ?></textarea>
			</td>
		</tr>
<?php 
if (!empty($filter_img['img']))
{
?>
		<tr>
			<th scope='row'><?php _e('Filter result', MP_TXTDOM); ?></th>
			<td style='font-family:Courier, "Courier New", monospace;'>
				<div class='filter-img bkgndc bd1sc'>
<?php 
	$x = $filter_img['img'];
	$x = stripslashes($x);
	$x = htmlspecialchars_decode($x);
	$x = MailPress_filter_img::img_mail($x);
	$x = str_ireplace('<!-- MailPress_filter_img start -->','',$x);
	$x = str_ireplace('<!-- MailPress_filter_img end -->','',$x);
	$x = htmlspecialchars($x,ENT_QUOTES);
	echo $x;
?>
				</div>
			</td>
		</tr>
<?php } ?>
		<tr valign='top'>
			<th scope='row'><?php _e('Keep url', MP_TXTDOM); ?></th>
			<td class='field'>
				<input name='filter_img[keepurl]' type='checkbox'<?php if (isset($filter_img['keepurl'])) checked($filter_img['keepurl'],'on'); ?>  id='attach-none' value='on' style='margin-right:10px;' />
				<label for='attach-none'><?php printf(__('NO mail attachements with site images when full url (<i>&lt;img src="<b>%1$s/...</b>"</i>) is provided.', MP_TXTDOM), site_url()); ?></label>
			</td>
		</tr>
	</table>
<?php MP_AdminPage::save_button(); ?>
</form>