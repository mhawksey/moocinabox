<?php

// Form templates

$form_templates = new MP_Form_templates();
$templates = $form_templates->get_all();

if (isset($_GET['template']))	$template = $_GET['template'];
if (!isset($template) )		$template = reset($templates);

$root  = MP_CONTENT_DIR . 'advanced/forms';
$root  = apply_filters('MailPress_advanced_forms_root', $root);
$root .= '/templates';
$template_file = "$root/$template.xml";

if ( ! is_file($template_file) ) wp_die(sprintf('<p>%s</p>', __('No such file exists! Double check the name and try again.')));

$content = file_get_contents( $template_file );
$content = htmlspecialchars( $content );
$codepress_lang = 'html';

// messages
$messages[1] = __('File edited successfully.', MP_TXTDOM);
$messages[2] = __('Could not save to file.',   MP_TXTDOM);
$messages[3] = __('Could not save to file, xml errors',   MP_TXTDOM);
if (isset($_GET['message'])) $message = $messages[$_GET['message']];

// file status

$file_status = is_writeable($template_file);

?>
<div class='wrap'>
	<div id='icon-mailpress-tools' class='icon32'><br /></div>
	<h2><?php _e('Edit Form templates', MP_TXTDOM); ?></h2>
<?php if (isset($message)) MP_AdminPage::message($message); ?>
	<br class='clear' />
	<div class='fileedit-sub'>
		<div class='alignleft'>
			<big>
<?php echo ($file_status) ? sprintf(__('Editing <strong>%s</strong>', MP_TXTDOM), $template) : sprintf(__('Browsing <strong>%s</strong>', MP_TXTDOM), $template); 	?>
			</big>
		</div>
		<div class='alignright'>
			<form action='' method='post'>
				<strong>
					<label for='plugin'>
						<?php _e('Select template to edit:', MP_TXTDOM); ?> 
					</label>
				</strong>
				<input type='hidden' name='action' value='toedit' />
				<select name='template' id='plugin'>
<?php MP_AdminPage::select_option($templates, $template ); ?>
				</select>
				<input type='submit' name='Submit' value='<?php esc_attr_e('Select') ?>' class='button' />
			</form>
		</div>
		<br class="clear" />
	</div>
	<form name='Template' id='Template' action='' method='post'>
		<?php wp_nonce_field('edit-mp-template_' . $template) ?>
		<input type='hidden' name='action' value='update' />
		<input type='hidden' name='template' value='<?php echo $template; ?>' />

<div style="border:1px solid #c0c0c0;padding:0px;">
			<textarea cols='100' rows='30' name='newcontent' id='newcontent' tabindex='1' ><?php echo $content ?></textarea>
</div>
<?php if ($file_status) : ?>
		<p class='submit'><input type='submit' name='submit' id='submit_xml' class='button-primary' value="<?php echo esc_attr(__('Update File')); ?>" tabindex='2' /></p>
<?php else : ?>
		<p><em><?php _e('You need to make this file writable before you can save your changes. See <a href="http://codex.wordpress.org/Changing_File_Permissions">the Codex</a> for more information.'); ?></em></p>
<?php endif; ?>
	</form>
<br class="clear" />
</div>