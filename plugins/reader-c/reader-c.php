<?php
/*
Plugin Name: Reader
Plugin URI: https://github.com/mhawksey/wp-reader-hub
Description: Plugin designed to turn WordPress into a RSS aggregator/reader.
Version: 0.1.1
Author: Martin Hawksey
Author URI: http://mashe.hawksey.info
License: GPL2

/*
Copyright 2014  Martin Hawksey  (email : m.hawksey@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('READER_C_VERSION', '0.1.1');
define('READER_C_PATH', dirname(__FILE__));
// Handle symbolic links - code portability.
define('READER_C_URL', plugin_dir_url(preg_replace('@\/var\/www\/[^\/]+@', '', __FILE__)));
define('READER_C_REGISTER_FILE', preg_replace('@\/var\/www\/[^\/]+@', '', __FILE__));

// Initialize FeedWordPress
if (!class_exists('FeedWordPress')){
   require_once(sprintf("%s/lib/feedwordpress/feedwordpress.php", READER_C_PATH));
}

if(!class_exists('Reader_C'))
{
	class Reader_C {
		static $post_types = array(); // used in shortcode caching
		static $post_type_fields = array(); // used to collect field types for frontend data entry 
		static $options = array();
		/**
		* Construct the plugin object.
		*
		* @since 0.1.1
		*/
		public function __construct() {			
			add_action('init', array(&$this, 'init'));
			// Register custom post types - reader
			require_once(sprintf("%s/post-types/class-item.php", READER_C_PATH));		
			
			// include shortcodes
			require_once(sprintf("%s/shortcodes/class-shortcode.php", READER_C_PATH));
			require_once(sprintf("%s/shortcodes/class-reader.php", READER_C_PATH));
			// Initialize JSON API library
			if (!class_exists('JSON_API')){
			   require_once(sprintf("%s/lib/json-api/json-api.php", READER_C_PATH));
			}
			// add custom JSON API controllers
			add_filter('json_api_controllers', array(&$this,'add_hub_controller'));
			add_filter('json_api_hub_controller_path', array(&$this,'set_hub_controller_path'));
			
			

			
			// Initialize Settings pages in wp-admin
            require_once(sprintf("%s/settings/settings.php", READER_C_PATH)); //TODO Tidy
            $Reader_C_Settings = new Reader_C_Settings();
			require_once(sprintf("%s/settings/cache.php", READER_C_PATH)); //TODO Tidy
			$Reader_C_Settings_Cache = new Reader_C_Settings_Cache();
			
			// register custom query handling
			add_filter('query_vars', array(&$this, 'reader_c_queryvars') );
			add_filter('pre_get_posts', array(&$this, 'query_post_type') );
			
			add_action('admin_notices', array(&$this, 'admin_notices'));
		   	add_action('admin_enqueue_scripts', array(&$this, 'enqueue_autocomplete_scripts'),999);
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_front_scripts') );
			// debug function
			add_action( 'wp_head', array(&$this, 'show_current_query') );

			add_action('wp_ajax_ajaxify',  array(&$this, 'reader_ajax'));           // for logged in user  
			add_action('wp_ajax_nopriv_ajaxify',  array(&$this, 'reader_ajax'));
			add_action('init', array(&$this, 'infinite_scroll_init_reader_c') );
			
			global $wpdb;
			$wpdb->readerlite_mark_as_read_data = $wpdb->prefix.'readerlite_mark_as_read_data';

		} // END public function __construct
		
				
		public function infinite_scroll_init_reader_c() {
			add_theme_support( 'infinite-scroll', array(
				'container' => 'accordion',
				'render'    => array(&$this, 'infinite_scroll_render_reader_c'),	
				'wrapper'   => false,
				'footer'    => false
			) );
		}
		
		/**
		* Set the code to be rendered on for calling posts,
		* hooked to template parts when possible.
		*
		* Note: must define a loop.
		*/
		public function infinite_scroll_render_reader_c() {
			require_once(sprintf("%s/templates/content-item.php", READER_C_PATH));
		}
		
		public function reader_ajax() { // loads post content into accordion
			$post_id = $_POST['post_id'];
			Reader_C::readerlite_mark_post_as_read($post_id);
			Reader_C::eh_set_post_views($post_id);
			echo "recorded";
			die(0);
		}

		
		/**
    	* Debug function to check wp_query. Add ?q to url to use.
		*
		* @since 0.1.1
    	*/
		public function show_current_query() {
			global $wp_query;
			if ( !isset( $_GET['q'] ) )
				return;
			echo '<textarea cols="50" rows="10">';
			print_r( $wp_query );
			echo '</textarea>';
		}

		
		function query_post_type($query) {
			if(is_category() || is_tag()) {
				$post_type = get_query_var('post_type');
				if($post_type){ 
					$post_type = $post_type;
				} else {
					$post_type = array('post','item', 'nav_menu_item');
				}
				$query->set('post_type',$post_type);
				return $query;
			}
		}
		/**
    	* Hook into WP's init action hook.
		*
		* @since 0.1.1
    	*/
    	public function init() {	
			// add permalink rewrites
			$this->do_rewrites();
		}
		
		
		/**
    	* Register controllers for custom JSON_API end points.
		*
		* @since 0.1.1
		* @param object $controllers JSON_API.
		* @return object $controllers.
    	*/
		public function add_hub_controller($controllers) {
		  $controllers[] = 'hub';
		  return $controllers;
		}
		
		/**
    	* Register controllers define path custom JSON_API end points.
		*
		* @since 0.1.1
    	*/
		public function set_hub_controller_path() {
		  return sprintf("%s/api/hub.php", READER_C_PATH);
		}
		
		
		/**
    	* Handle custom admin notices.
		*
		* @since 0.1.1
    	*/
		public static function admin_notices() {
			$messages = get_option('reader_c_messages', array());
			if (count($messages)) {
				foreach ($messages as $message) { ?>
					<div class="updated">
						<p><?php echo $message; ?></p>
					</div>
				<?php }
				delete_option('reader_c_messages');
			}
		}
		
		/**
    	* Handle custom admin notices - push message for display.
		*
		* @since 0.1.1
		* @param string $message.
    	*/
		public static function add_admin_notice($message) {
			$messages = get_option('reader_c_messages', array());
			$messages[] = $message;
			update_option('reader_c_messages', $messages);
		}
		
		/**
    	* Register custom querystring variables.
		*
		* @since 0.1.1
		* @param array $qvars WP qvars.
		* @return array $qvars.
    	*/
		public function reader_c_queryvars( $qvars ) {
		  return $qvars;
		}
		
		/**
    	* Load additional CSS/JS to wp_head in wp-admin.
		*
		* @since 0.1.1
    	*/
		public function enqueue_autocomplete_scripts() {	
			
		}
		
		/**
    	* Load additional CSS/JS to wp_head in frontend.
		*
		* @since 0.1.1
    	*/
		public function enqueue_front_scripts() {
			$scripts = array( 'jquery', 'jquery-ui-core', 'jquery-ui-accordion');
			wp_register_script( 'readerlite', plugins_url( 'js/readerlite.js', READER_C_REGISTER_FILE), $scripts  );
			wp_enqueue_script( 'readerlite' );
			wp_enqueue_style( 'readerlite-jquery', plugins_url( 'css/jquery-ui-1.10.3.custom.min.css', READER_C_REGISTER_FILE ) );
			wp_enqueue_style( 'readerlitecss', plugins_url( 'css/style.css', READER_C_REGISTER_FILE ) );
		}

		/**
    	* Generates a post excerpt (used in api/hub.php).
		*
		* @since 0.1.1
		* @param int $post_id.
		* @return string filtered post content 
    	*/
		public function reader_excerpt($post_id = false) {
			if ($post_id) $post = is_numeric($post_id) ? get_post($post_id) : $post_id;
			else $post = $GLOBALS['post'];
	
			if (!$post) return '';
			//if (isset($post->post_excerpt) && !empty($post->post_excerpt)) return $post->post_excerpt;
			if (!isset($post->post_content)) return '';
		
			$content = $raw_content = $post->post_content;
		
			if (!empty($content)) {
				
				$content = strip_tags($content);
				$content = preg_replace( '/\s+/', ' ', $content );
				$excerpt = explode(' ', $content, 50);
				array_pop($excerpt);
				$excerpt = implode(" ",$excerpt).'...';
				return $excerpt;
			}
		
		}
		/**
    	* Returns first user role for author id.
		*
		* @since 0.1.1
		* @param int $user_id.
		* @return string role 
    	*/
		public function get_user_role( $user_id ){
		  $user_data = get_userdata( $user_id );
		  if(!empty( $user_data->roles )) {
			  return $user_data->roles[0];
		  }
		  return false; 
		}
		
		/**
		* record page view counts
		* taken from http://www.wpbeginner.com/wp-tutorials/how-to-track-popular-posts-by-views-in-wordpress-without-a-plugin/
		*
		* @since 0.1.1
		* @params string $postID
		*/
		public function eh_set_post_views($postID) {
			$count_key = 'post_views_count';
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				$count = 0;
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
			}else{
				$count++;
				update_post_meta($postID, $count_key, $count);
			}
		}
		
		function eh_track_post_views($post_id) {
			if ( !is_single() ) return;
			if ( empty ( $post_id) ) {
				global $post;
				$post_id = $post->ID;    
			}
			$this->eh_set_post_views($post_id);
		}
		/**
		* Record if user has read the post
		* 
		* @params string $postID
		*/
		public function readerlite_mark_post_as_read($post_id) {
			if ( empty ( $post_id) ) {
				global $post;
				$post_id = $post->ID;    
			}
			$userid = get_current_user_id();
			global $wpdb;
			$wpdb->query($wpdb->prepare( 
							"INSERT INTO $wpdb->readerlite_mark_as_read_data
							 (postid, userid, updated) 
							 VALUES (%s, %s, NOW()) 
							 ON DUPLICATE KEY UPDATE updated = NOW()",
								$post_id, $userid 
							)
					);
		}
		
		public function readerlite_get_if_read_post($postid){
			global $wpdb;
			$is = "unread";
			$userid = get_current_user_id();
			$check = $wpdb->get_results(
								$wpdb->prepare(
								"SELECT updated FROM $wpdb->readerlite_mark_as_read_data 
								 WHERE userid = %s AND postid = %s",
								 $userid, $postid ), OBJECT);
			if (!empty($check)){
				$is = "read";
			}
			echo $is;
		}
		
		/**
    	* Does WP permalink rewrites.
		*
		* @since 0.1.1
    	*/
		private function do_rewrites(){
			add_rewrite_rule("^item/category/([^/]+)/page/([0-9]+)?",'index.php?post_type=item&category_name=$matches[1]&paged=$matches[2]','top');
			add_rewrite_rule("^item/category/([^/]+)?",'index.php?post_type=item&category_name=$matches[1]','top');
		}
		
		/**
		* Activate the plugin
		*
		* @since 0.1.1
		*/
		public static function activate(){
			flush_rewrite_rules();
			Reader_C_Shortcode::activate();
			JSON_API::save_option('feedwordpress_syndicated_post_type', 'item');
			global $wpdb;
	
			//if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $wpdb->readerlite_mark_as_read_data (
					id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					userid int(11) UNSIGNED NOT NULL ,
					postid int(11) UNSIGNED NOT NULL ,
					updated datetime NOT NULL,
					CONSTRAINT tb_uq UNIQUE (userid , postid)
					)";			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			// Do nothing
		} // END public static function activate
	
		/**
		* Deactivate the plugin
		*
		* @since 0.1.1
		*/		
		public static function deactivate(){
			Reader_C_Shortcode::deactivate();
			global $wpdb;
			$wpdb->query("drop table $wpdb->readerlite_mark_as_read_data");
		} // END public static function deactivate
	} // END class Reader_C
} // END if(!class_exists('Reader_C'))

if(class_exists('Reader_C')){
	// Installation and uninstallation hooks
	register_activation_hook(READER_C_REGISTER_FILE, array('Reader_C', 'activate'));
	register_deactivation_hook(READER_C_REGISTER_FILE, array('Reader_C', 'deactivate'));

	// instantiate the plugin class
	$wp_plugin_template = new Reader_C();	
}