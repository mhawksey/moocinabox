<?php

class Reader_C_CustomPostType {
	public $post_type = "custom_post_type";
	public $archive_slug = false; // use pluralized string if you want an archive page
	public $singular = "Item";
	public $plural = "Items";
	
	public $options = array();
	
	/**
	* The Constructor
	*
	* @since 0.1.1
	*/
	public function __construct() {
		// register actions
		add_action('init', array(&$this, 'init'));
		add_action('init', array(&$this, 'set_options'));
		add_action('admin_init', array(&$this, 'admin_init'));
		// register custom columns in wp-admin
		add_action('manage_edit-'.$this->post_type.'_columns', array(&$this, 'columns'));
		add_action('manage_'.$this->post_type.'_posts_custom_column', array(&$this, 'column'),10 ,2);
		// add filters
		add_filter('post_type_link', array(&$this, 'custom_post_type_link'), 1, 3);
		add_action('edit_form_after_title', array(&$this, 'foo_move_deck'),999);
		// push post types for caching
		Reader_C::$post_types[] = $this->post_type;
	} // END public function __construct()
	
	/**
	* hook into WP's init action hook.
	*
	* @since 0.1.1
	*/
	public function init() {
		// Initialize Post Type
		$this->create_post_type();
		// save post action
		add_action('save_post', array(&$this, 'save_post'));

	} // END public function init()
	public function foo_move_deck(){
	}
	
	/**
	* Register custom post type.
	*
	* @since 0.1.1
	*/
	public function create_post_type(){
		// no action
	}
	
	/**
	* Register custom post type fields.
	*
	* @since 0.1.1
	*/
	public function set_options(){
		// no action	
	}
	
	/**
	* Save the metaboxes for this custom post type.
	*
	* @since 0.1.1
	*/
	public function save_post($post_id)	{
		// verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if (get_post_type($post_id) != $this->post_type) return;
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		
		if (isset($_POST['reader_c_nonce']) && !wp_verify_nonce($_POST['reader_c_nonce'], plugin_basename(__FILE__))) return;
		
		if (!current_user_can('edit_reader', $post_id)) return;
		

		foreach($this->options as $name => $option)	{
			// Update the post's meta field
			$field_name = "reader_c_$name";		
			
			if (isset($_POST[$field_name])){
				// if change of posttype save and return
				if ($option['type'] == 'select-posttype'){
					set_post_type( $post_id, $_REQUEST[$field_name] );
					return get_admin_url() . 'edit.php?post_type=' . $_REQUEST[$field_name];
				}
				
				if ($option['save_as'] == 'term'){
					$foundterm = false;
					if (!is_array($_REQUEST[$field_name])){ // handle if single term
						$term = term_exists($_REQUEST[$field_name], $field_name); // find if terms exists on list
						if ($term !== 0 && $term !== NULL){
							$foundterm = true; // not on list to don't update
							//
						}
					} else {
						foreach($_REQUEST[$field_name] as $value){ // handle array of terms
							$term = term_exists($value, $field_name);
							if ($term !== 0 && $term !== NULL){
								$foundterm = true; // not on list to don't update
							}
						}
					}
					if ($foundterm){ // if still true that term(s) on list update 
						wp_set_object_terms( $post_id, $_REQUEST[$field_name], $field_name);
					}		
					
				} else {
					update_post_meta($post_id, $field_name, $_POST[$field_name]);
				}
			}
		}
	} // END public function save_post($post_id)
	
	/**
	* Add action to add metaboxes.
	*
	* @since 0.1.1
	*/
	public function admin_init() {			
		add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
	} // END public function admin_init()
	
	/**
	* Register custom fields box in wp-admin.
	*
	* @since 0.1.1
	*/
	public function add_meta_boxes() {
		// no action
	}
	
	/**
	* Render side custom fields in wp-admin.
	*
	* @since 0.1.1
	*/	
	public function add_inner_meta_boxes_side($post) { 
		wp_nonce_field(plugin_basename(__FILE__), 'reader_c_nonce');
		$sub_options = Reader_C::filterOptions($this->options, 'position', 'side');
		include(sprintf("%s/custom_post_metaboxes.php", dirname(__FILE__)));			
	} // END public function add_inner_meta_boxes($post)
	
	/**
	* Render bottom custom fields in wp-admin.
	*
	* @since 0.1.1
	*/	
	public function add_inner_meta_boxes($post)	{		
		// Render the job order metabox
		$sub_options = Reader_C::filterOptions($this->options, 'position', 'bottom');
		include(sprintf("%s/custom_post_metaboxes.php", dirname(__FILE__)));			
	} // END public function add_inner_meta_boxes($post)
	
	/**
	* function to register custom slug hypothesis/%hypothesis_slug%/%post_id%/.
	*
	* @since 0.1.1
	* @return string
	*/
	public function custom_post_type_link($post_link, $post = 0, $leavename = false) {			
		// no action
		return $post_link;
	}
	
	/**
	* Add hypothesis column to wp-admin.
	*
	* @since 0.1.1
	* @params array
	* @return array
	*/
	public function columns($columns) {
		return $columns;
	}
	
	/**
	* Sets text and link for custom columns.
	*
	* @since 0.1.1
	* @return NULL
	*/	
	public function column($column, $post_id) {
		
	}
}