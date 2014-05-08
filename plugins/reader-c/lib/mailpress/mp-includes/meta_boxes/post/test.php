<?php

$fields = array('toemail' => __('Email: ', MP_TXTDOM), 'newsletter' => __('Newsletter: ', MP_TXTDOM), 'theme' => __('Theme: ', MP_TXTDOM) );

$meta = get_user_meta(MP_WP_User::get_id(), '_MailPress_post_' . $post->ID, true);
$test	= get_option(MailPress::option_name_test);

$xnewsletters = array();
global $mp_registered_newsletters;
foreach ($mp_registered_newsletters as $id => $data) if (!isset($data['params']['post_type']) || 'post' == $data['params']['post_type']) $xnewsletters[$id] = $data['descriptions']['admin'];

$th = new MP_Themes();
$themes = $th->themes;

foreach($themes as $key => $theme)
{
	if ( 'plaintext' == $theme['Stylesheet']) unset($themes[$key]);
	if ( '_' == $theme['Stylesheet'][0] )     unset($themes[$key]);
}

$xthemes = array('' => __('current', MP_TXTDOM));
foreach ($themes as $theme) $xthemes[$theme['Stylesheet']] = $theme['Stylesheet'];

$current_theme = $themes[$th->current_theme]['Stylesheet'];

if ($meta)
{
	$toemail	= $meta['toemail'];
	$newsletter	= $meta['newsletter'];
	$theme 	= (isset($xthemes[$meta['theme']])) ? $meta['theme'] : '';
}
else
{
	$toemail	= $test['toemail'];
	$newsletter = 'new_post';
	$theme 	= '';
}
?>
<div id='MailPress_test'>
	<div>
		<div style='padding:6px;text-align:right;'>
			<div style='position:relative;'>
				<div style='float:left'>
					<div id='MailPress_post_test_loading' style='position:absolute;opacity:0;filter:alpha(opacity=0);'><img src='images/wpspin_light.gif' style='padding-right:5px;' alt='' /><?php _e('Sending ...', MP_TXTDOM); ?></div>
					<div id='MailPress_post_test_ajax'    style='position:relative;'><br /></div>
				</div>
			</div>
			<div>
				<a style='min-width:80px;text-align:center;' class='mp_meta_box_post_test button' href='#mp_send'><?php _e('Test', MP_TXTDOM); ?></a>
			</div>
			<div class="clear"></div>
		</div>
		<div style='padding:6px 0 16px;'>
<?php
foreach ($fields as $field => $label)
{
	if ('newsletter' == $field) $lib = $xnewsletters[$$field];
	elseif ('theme' == $field) $lib = $xthemes[$$field];
	else $lib = $$field;
?>

			<div class='misc-pub-section'>
				<label><?php echo $label; ?></label>
				<b><span id='span_<?php echo $field ?>'> <?php echo $lib; ?></span></b>
				<a href='#mp_<?php echo $field ?>' class="mp-edit-<?php echo $field ?> hide-if-no-js"><?php _e('Edit') ?></a>
				<div id='mp_div_<?php echo $field ?>' class='hide-if-js' style='display:none;line-height:2.5em;margin-top:3px;'>

<?php
	switch ($field)
	{
		case 'toemail' :
?>
					<input id='mp_hidden_<?php echo $field ?>' 	name='mp_hidden_<?php echo $field ?>'	type='hidden' value='<?php echo $$field; ?>' />
					<input id='mp_<?php echo $field ?>' 		name='mp_<?php echo $field ?>' 		type='text'   value="<?php echo $$field; ?>" style='width:240px;' /> 
<?php
		break;
		case 'toname' :
?>
					<input id='mp_hidden_<?php echo $field ?>' 	name='mp_hidden_<?php echo $field ?>'	type='hidden' value="<?php echo esc_attr($$field); ?>" />
					<input id='mp_<?php echo $field ?>' 		name='mp_<?php echo $field ?>' 		type='text'   value="<?php echo esc_attr($$field); ?>" />
<?php
		break;
		case 'newsletter' :

?>
					<input id='mp_hidden_<?php echo $field ?>' name='mp_hidden_<?php echo $field ?>' type='hidden' value="<?php echo esc_attr($$field); ?>" />
					<input id='mp_hidden_lib_<?php echo $field ?>' name='mp_hidden_lib_<?php echo $field ?>' type='hidden' value="<?php echo esc_attr($xnewsletters[$$field]); ?>" />
					<select id='mp_<?php echo $field ?>' name='mp_<?php echo $field ?>'>
<?php MP_::select_option($xnewsletters, $$field);?>
					</select>
<?php
		break;
		case 'theme' :
?>
					<input id='mp_hidden_<?php echo $field ?>' name='mp_hidden_<?php echo $field ?>' type='hidden' value="<?php echo esc_attr($$field); ?>" />
					<input id='mp_hidden_lib_<?php echo $field ?>' name='mp_hidden_lib_<?php echo $field ?>' type='hidden' value="<?php echo esc_attr($xthemes[$$field]); ?>" />
					<select id='mp_<?php echo $field ?>' name='mp_<?php echo $field ?>'>
<?php MP_::select_option($xthemes, $theme);?>
					</select>
<?php
		break;
	}
?>
					<br />
					<a class='mp-save-<?php echo $field ?> hide-if-no-js button' href='#mp_<?php echo $field ?>'><?php _e('OK'); ?></a>
					<a class='mp-cancel-<?php echo $field ?> hide-if-no-js' href='#mp_<?php echo $field ?>'><?php _e('Cancel'); ?></a>
				</div>
			</div>
			<div class="clear"></div>
<?php
}
?>
		</div>
	</div>
</div>