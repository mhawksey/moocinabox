<?php
/**
 * Abstract class used to construct shortcodes
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 * @since 0.1.1
 *
 * @package Reader_C
 * @subpackage Reader_C_Shortcode
 */
 
abstract class Reader_C_Shortcode {
	var $shortcode = 'reader_c_shortcode';
	var $defaults = array('do_cache' => true);
	var $options = array();
	
	/**
	* Construct the plugin object.
	*
	* @since 0.1.1
	*/
	public function __construct() {
		add_shortcode($this->shortcode, array(&$this, 'shortcode'));
		add_filter('the_content', array(&$this, 'pre_add_to_page'));

		add_action('save_post', array(&$this, 'save_post'));
		add_action('trash_post', array(&$this, 'trash_post'));
		
		register_activation_hook(READER_C_REGISTER_FILE, array(&$this, 'activate'));
		register_deactivation_hook(READER_C_REGISTER_FILE, array(&$this, 'deactivate'));
		
		global $wpdb;
		$wpdb->reader_c_shortcode_cache = $wpdb->prefix.'reader_c_shortcode_cache';
	}

	/**
	* Handles shortcode rendering and caching.
	*
	* @since 0.1.1
	* @param array $options 
	*/	
	public function shortcode($options) {
		$this->options = shortcode_atts($this->defaults, $options);	
		$this->prep_options();
		if (!$content = $this->get_cache()) {
			$content = $this->content();
			if (!isset($this->options['do_cache'])) {
				$this->cache($content);
			}
		}
		return $content;
	}

	/**
	* Intercepts content rendering and adds shortcode as required.
	*
	* @since 0.1.1 
	*/
	public function pre_add_to_page($content) {
		$options = get_option('reader_c_options');
		$options['add_to_page'] = 1;
		return $options['add_to_page'] ? $this->add_to_page($content) : $content;
	}
	
	/**
	* Holder for extended classes. 
	*
	* @since 0.1.1 
	*/
	public function add_to_page($content) {
		return $content;
	}
	
	public function make_meta_bar($post_types_with_shortcode){
		ob_start();
		extract($this->options);
		$errors = array();
		$post_id = get_the_ID();
			$post = Reader_C::add_meta($post_id);
			$post['type'] = get_post_type($post_id);
			if (!$post) {
				$errors[] = "$post_id is not a valid post ID";
			} else if (!in_array($post['type'], $post_types_with_shortcode)) {
				$errors[] = "<a href='".get_permalink($post_id)."'>".get_the_title($post_id)."</a> is not the correct type of post";
			} else if ($location=="header") { 
				$this->meta_bar($post, $header_terms);
			} else if ($location=="footer") { 
	  			$this->meta_bar($post, $footer_terms);
			}
		
		if (count($errors)) return "[Shortcode errors (".$this->shortcode."): ".implode(', ', $errors)."]";	
		return ob_get_clean();
	}
	
	/**
	* Renders metadata assocated with custom postype. 
	*
	* @since 0.1.1
	* @param object $post single post object which has been through Reader_C::add_meta.
	* @param array $options passed from shortcode parameters.
	*/
	public function meta_bar($post, $options){
		$out = array();
		
		// shorcode uses comma separated list of field ids to include in bar
		foreach (explode(',', $options) as $type) {
			$type = trim($type);
			$slug = $type."_slug";
			// if there is a slug then a taxonomy term
			if (isset($post[$type]) && isset($post[$slug])){
				$out[] = get_the_term_list( $post['ID'], "reader_c_".$type, ucwords(str_replace("_", " ",$type)).": ", ", ");
			// else it's a custom field
			} else {
				$out[] = $this->get_custom_field($post, $type);
			}
		}
		// remove NULL
		$out = array_filter($out); 
		if(!empty($out)){ 
			echo '<div id="reader-meta">'.implode(" | ", $out).'</div>';
       }	
	}
	
	/**
	* Handle custom fields rendering. 
	*
	* @since 0.1.1
	* @param object $post single post object which has been through Reader_C::add_meta.
	* @param string $type field name.
	* @return string.
	*/
	public function get_custom_field($post, $type){
		// if field name not in post throw back null
		if (!isset($post[$type]) || $post[$type] == ""){
			return NULL;
		}
		// handle hypothesis as related value
		if ($type == 'hypothesis_id') {
			return  __(sprintf('<span class="meta_label">Hypothesis</span>: <a href="%s">%s</a>', get_permalink($post[$type]), get_the_title($post[$type])));
		// handle post_type
		} elseif($type == "type" ) {
			return __(sprintf('<span class="meta_label">Type</span>: <a href="%s">%s</a>', get_post_type_archive_link($post[$type]), ucwords($post[$type])));
		// special case for links	
		} elseif(isset($post[$type]) && ($type=="citation" || $type=="resource_link")) {
			// if valid link wrap in href
			if (filter_var($post[$type], FILTER_VALIDATE_URL) === FALSE) {
				return __(sprintf('<span class="meta_label">%s</span>: %s', ucwords(str_replace("_", " ",$type)),$post[$type]));
			} else {
				return __(sprintf('<span class="meta_label">%s</span>: <a href="%s">%s</a>', ucwords(str_replace("_", " ",$type)),$post[$type],$post[$type]));
			}
		// final case all other custom fields
		} elseif (isset($post[$type])) {
			return __(sprintf('<span class="meta_label">%s</span>: %s', ucwords(str_replace("_", " ",$type)),$post[$type]));
		}
		return NULL;
	}
	
	/**
	* Turns options into booleans. 
	*
	* @since 0.1.1
	*/
	function prep_options() {
		foreach ($this->options as $key => $value) {
			if (is_string($value)) {
				if ($value == 'true') $this->options[$key] = true;
				if ($value == 'false') $this->options[$key] = false;
			}
		}
		if (!isset($this->options['post_id']) && isset($GLOBALS['post'])) {
			$this->options['post_id'] = $GLOBALS['post']->ID;
		}
	}
	abstract function content();
	
	// Caching ----------------------------------------------------------------

	// TODO: doesn't $wpdb need to be globalized in this function?
	/**
	* Create table for cached shortcodes. 
	*
	* @since 0.1.1
	*/
	public function activate() {
		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta("CREATE TABLE $wpdb->reader_c_shortcode_cache (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			shortcode text NOT NULL,
			options text NOT NULL,
			content mediumtext NOT NULL,
			UNIQUE KEY id(id)
		);");
	}
	
	/**
	* Drop table for cached shortcodes. 
	*
	* @since 0.1.1
	*/
	public function deactivate() {
		global $wpdb;
		$wpdb->query("drop table $wpdb->reader_c_shortcode_cache");
	}
	
	/**
	* Hooks WP save process. Entire cache cleared on custom post type save. 
	*
	* @since 0.1.1
	* @param string $post_id
	*/
	public function save_post($post_id) {
		if (!in_array(get_post_type($post_id), Reader_C::$post_types)) return;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		self::clear_cache();
	}

	/**
	* Hooks WP trash process. Entire cache cleared on custom post type save. 
	*
	* @since 0.1.1
	* @param string $post_id
	*/	
	public function trash_post($post_id) {
		if (!in_array(get_post_type($post_id), Reader_C::$post_types)) return;
		self::clear_cache();
	}
	
	/**
	* If caching is enabled fetch cached value. 
	*
	* @since 0.1.1
	*/		
	public function get_cache() {
		if (!get_option('reader_c_caching')) return false;
		
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare(
			"SELECT content
			from $wpdb->reader_c_shortcode_cache
			where shortcode = %s
			and options = %s",
			$this->shortcode,
			serialize($this->options)
		));
	}
	
	/**
	* If caching is enabled save content to cache. 
	*
	* @since 0.1.1
	* @param string $content
	*/	
	public function cache($content) {
		if (!get_option('reader_c_caching')) return false;
		
		global $wpdb;
		$wpdb->insert($wpdb->reader_c_shortcode_cache, array(
			'created' => current_time('mysql'),
			'shortcode' => $this->shortcode,
			'options' => serialize($this->options),
			'content' => $content,
		));
	}
	
	/**
	* Get count of cached shortcode items for object. 
	*
	* @since 0.1.1
	* @return string cache count
	*/
	public static function get_all_cache() {
		global $wpdb;
		return $wpdb->get_results("SELECT shortcode, count(id) AS count FROM $wpdb->reader_c_shortcode_cache GROUP BY shortcode", OBJECT);
	}
	
	/**
	* Clear cache. 
	*
	* @since 0.1.1
	*/
	public static function clear_cache() {
		global $wpdb;
		$wpdb->query("TRUNCATE $wpdb->reader_c_shortcode_cache");
	}
}