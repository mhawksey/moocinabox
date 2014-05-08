<?php

if(!class_exists('Reader_C_Settings'))
{

	class Reader_C_Settings
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// register actions
            //add_action('admin_init', array(&$this, 'admin_init'));
        	add_action('admin_menu', array(&$this, 'add_menu'));
		} // END public function __construct
		
        
        /**
         * add a menu
         */		
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
			add_menu_page(
				"ReaderC",
				"ReaderC",
				'manage_options',
				'reader',
				array(&$this, 'plugin_settings_page'),
				READER_C_URL.'/images/icons/hub.png',
				'29'
			);
			add_submenu_page('reader', 'JSON API', 'JSON API', 'manage_options', 'json-api', array( '__JSON_API__', 'admin_options' ));


			$GLOBALS['menu'][28] = array('', 'read', 'separator-28', '', 'wp-menu-separator');

        } // END public function add_menu()
    
        /**
         * Menu Callback
         */		
        public function plugin_settings_page()
        {
        	if(!current_user_can('manage_options'))
        	{
        		wp_die(__('You do not have sufficient permissions to access this page.'));
        	}
	
        	// Render the settings template
        	include(sprintf("%s/overview.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class Reader_C_Settings
} // END if(!class_exists('Reader_C_Settings'))
