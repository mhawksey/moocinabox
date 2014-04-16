<?php


class Reader_C_Settings_Cache {
	function __construct() {
		register_activation_hook(READER_C_REGISTER_FILE, array(&$this, 'activate'));
		register_deactivation_hook(READER_C_REGISTER_FILE, array(&$this, 'deactivate'));
		
		add_action('admin_init', array(&$this, 'save'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}
	
	function activate() {
		add_option('reader_c_caching', true);
	}
	
	function deactivate() {
		delete_option('reader_c_caching');
	}
	
	function admin_menu() {
		add_submenu_page(
			'reader',
			"Caching/Settings",
			"Caching/Settings",
			'hypothesis_admin',
			'reader_c_cache',
			array(&$this, 'page')
		);
	}
	
	function page() {
		if (!current_user_can('hypothesis_admin')) wp_die("You do not have sufficient permissions to access this page.");
		
		$caching = get_option('reader_c_caching');
		$cache = Reader_C_Shortcode::get_all_cache();
		
		?>
		<div id="reader_c_cache" class="wrap">
			<h2>ReaderC Caching</h2>
			<p>ReaderC caches the content of any of it's shortcodes you use in your site.</p>

			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
				<?php wp_nonce_field('nonce_reader_c_cache'); ?>
				<p>
					<?php if ($caching) { ?>
						<input type="submit" name="reader_c_disable_cache" class="button button-primary" value="Disable Caching" />
					<?php } else { ?>
						<input type="submit" name="reader_c_enable_cache" class="button button-primary" value="Enable Caching" />
					<?php } ?>
					<input type="submit" name="reader_c_clear_cache" class="button button-primary" value="Clear Cache" />
				</p>
				<input type="hidden" name="reader_c_cache_settings" value="save" />
			</form>
			
			<?php if ($caching) { ?>
				<h3>Cached Shortcodes</h3>
				<?php if (empty($cache)) { ?>
					<p>No cached shortcodes.</p>
				<?php } else { ?>
					<table>
						<tr>
							<th>count</th>
							<th>shortcode</th>
						</tr>
						<?php foreach ($cache as $shortcode) { ?>
							<tr>
								<td><?php echo $shortcode->count; ?></td>
								<td><?php echo $shortcode->shortcode; ?></td>
							</tr>
						<?php } ?>
					</table>
				<?php } ?>
			<?php } ?>
		</div>
        <div class="wrap">
            <h2>ReaderC Settings</h2>
            <form method="post" action="options.php"> 
                <?php @settings_fields('reader_c_settings'); ?>
                <?php @do_settings_fields('reader_c_settings'); ?>
        
                <?php do_settings_sections('reader_c_template'); ?>
        
                <?php @submit_button(); ?>
            </form>
        </div>
        
		
		<?php
	}
	public function settings_section_reader_c_template()
	{
		// Think of this as help text for the section.
		echo 'The pages below can be set for custom templates for reader and hypothesis data';
	}

	/**
	 * This function provides text inputs for settings fields
	 */
	public function settings_field_input_text($args)
	{
		// Get the field name from the $args array
		$field = $args['name'];
		// Get the value of this setting
		$value = get_option($field);
		// echo a proper input type="text"
		echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
	} // END public function settings_field_input_text($args)
	
	/**
	* This function provides text inputs for settings fields
	*/
	public function settings_field_input_page_select($args)
	{
		// Get the field name from the $args array
		$field = $args['name'];
		// Get the value of this setting
		$value = get_option($field);
		$args['selected'] = ($value) ? $value : 0;
		$args['show_option_none'] = '-Select page-';
		$args['option_none_value'] = false;
		wp_dropdown_pages($args);
	} // END public function settings_field_input_page_select($args)
	
	/**
	* This function provides text inputs for settings fields
	*/
	public function settings_field_input_radio($args)
	{
		// Get the field name from the $args array
		$field = $args['name'];
		$value = get_option($field);
		echo '
		<div id="eh_settings">';

		foreach($args['choices'] as $val => $trans)
		{
			$val = esc_attr($val);

			echo '
			<input id="'.$field.'-'.$val.'" type="radio" name="'.$field.'" value="'.$val.'" '.checked($val, $value, FALSE).' />
			<label for="'.$field.'-'.$val.'">'.esc_html($trans).'</label>';
		}

		echo '
			<p class="description">'.$args['description'].'</p>
		</div>';
		
	} // END public function settings_field_input_radio($args)
	
	function save() {
		register_setting('reader_c_settings', 'display_cookie_notice');
		// add your settings section
		add_settings_section(
			'reader_c_template-section', 
			'Settings', 
			array(&$this, 'settings_section_reader_c_template'), 
			'reader_c_template'
		);

		// add your setting's fields
		
		add_settings_field(
			'reader_c_settings-display_cookie_notice', 
			'Cookie Notice', 
			array(&$this, 'settings_field_input_radio'), 
			'reader_c_template', 
			'reader_c_template-section',
			array(  'name' => 'display_cookie_notice',
					'choices' => array( 'yes' => 'Enable',
										'no' => 'Disable'),
					'description' => 'Enable or Disable cookie notices',
			)
		);
		
		
		if (isset($_POST['reader_c_cache_settings']) && check_admin_referer('nonce_reader_c_cache')) {
			if (isset($_POST['reader_c_disable_cache'])) {
				update_option('reader_c_caching', false);
				Reader_C::add_admin_notice("Caching disabled.");
			} else if (isset($_POST['reader_c_enable_cache'])) {
				update_option('reader_c_caching', true);
				Reader_C::add_admin_notice("Caching enabled.");
			} else if (isset($_POST['reader_c_clear_cache'])) {
				Reader_C_Shortcode::clear_cache();
				Reader_C::add_admin_notice("Cache cleared.");
			}
			
			header("Location: ".$_SERVER['REQUEST_URI']);
			die;
		}	
		if(isset($_POST['option_page']) && isset($_POST['hypothesis_template_page'])){
			Reader_C::add_admin_notice("Setting saved.");
		}
	}
	
}