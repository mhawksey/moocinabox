<?php
/**
 * Shortcode to display survey data explorer
 *
 * Shortcode: [hypothesis_geosummary]
 * Options: post_id - hypothesis id (deafults to current post)
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 * @since 0.1.1
 *
 * @package Reader_C
 * @subpackage Reader_C_Shortcode
 */
 
new Reader_C_Shortcode_Reader();
// Base class 'Evidence_Hub_Shortcode' defined in 'shortcodes/class-shortcode.php'.
class Reader_C_Shortcode_Reader extends Reader_C_Shortcode {
	var $shortcode = 'readerc';
	public $defaults = array();
	/**
	* Generate post content. 
	*
	* @since 0.1.1
	* @return string.
	*/
	function content() {
		ob_start();
		extract($this->options);
		global $wp_query;
		$args = array_merge( $wp_query->query_vars, array( 'post_type' => 'item' ) );
		query_posts( $args );
		print_r($wp_query);
		if ( have_posts() ) : 
		// do the_loop?>

        <div class="readerfeed" style="text-align:right"><a href="feed/" title="RSS Feed">RSS</a></div>
        <div id="accordionLoader" class="inifiniteLoader">Loading... </div>
        <div id="accordion" style="display:none">
          <?php
                  
                  Reader_C::mec_get_admin_menu_page('content'); 
            ?>
        </div>
        <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
<?php	endif;
		wp_reset_query();
		return ob_get_clean();
	} // end of function content

} // end of class
