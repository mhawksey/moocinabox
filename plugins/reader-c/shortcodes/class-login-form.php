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
 
new Reader_C_Shortcode_Mailpress();
// Base class 'Evidence_Hub_Shortcode' defined in 'shortcodes/class-shortcode.php'.
class Reader_C_Shortcode_Mailpress extends Reader_C_Shortcode {
	var $shortcode = 'readerc_mailpress';
	public $defaults = array();
	public $options = array('do_cache' => false);
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
		if ($results){
			?>
            
			<h3><?php echo $results['title'];?> </h3>
			<?php echo $results['content']; ?>
            <?php
		}
		return ob_get_clean();
	} // end of function content

} // end of class
