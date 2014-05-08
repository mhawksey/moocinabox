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
		$results = MP_Mail_links::process(); 
		print_r($results);
		return ob_get_clean();
	} // end of function content

} // end of class
