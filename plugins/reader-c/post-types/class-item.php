<?php
/**
 * Construct a reader custom post type
 *
 * @since 0.1.1
 *
 * @package Reader_C
 */
new Reader_C_Item();
class Reader_C_Item {
		/**
	* The Constructor
	*
	* @since 0.1.1
	*/
	public function __construct() {
		add_action( 'init', array(&$this, 'create_post_type'), 0 );
	}
	
	/**
	* Register custom post type.
	*
	* @since 0.1.1
	*/
	public function create_post_type() {
		$labels = array(
			'name'                => _x( 'Items', 'Post Type General Name', 'reader_c' ),
			'singular_name'       => _x( 'Item', 'Post Type Singular Name', 'reader_c' ),
			'menu_name'           => __( 'Items', 'reader_c' ),
			'parent_item_colon'   => __( 'Parent Item:', 'reader_c' ),
			'all_items'           => __( 'All Items', 'reader_c' ),
			'view_item'           => __( 'View Item', 'reader_c' ),
			'add_new_item'        => __( 'Add New Item', 'reader_c' ),
			'add_new'             => __( 'Add New', 'reader_c' ),
			'edit_item'           => __( 'Edit Item', 'reader_c' ),
			'update_item'         => __( 'Update Item', 'reader_c' ),
			'search_items'        => __( 'Search Item', 'reader_c' ),
			'not_found'           => __( 'Not found', 'reader_c' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'reader_c' ),
		);
		$args = array(
			'label'               => __( 'item', 'reader_c' ),
			'description'         => __( 'Aggregated items', 'reader_c' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'comments', 'custom-fields', ),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 30,
			'menu_icon'           => READER_C_URL.'images/icons/evidence.png',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);
		register_post_type( 'item', $args );
	}
} // END class Post_Type_Template