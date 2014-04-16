<?php 
$root_cat = "Reader";
require ( get_stylesheet_directory() . '/includes/XProfile_FWP.php' );

/**
 * Register postMessage support.
 *
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 *
 * @return void
 */
function alt_customize_register( $wp_customize ) {
	/**
	 * Add custom header logo by Kirk Wight.
	 *
	 * @link http://kwight.ca/2012/12/02/adding-a-logo-uploader-to-your-wordpress-site-with-the-theme-customizer/
	 * 
	 * Create a new section for our logo upload.
	 */
	$wp_customize->add_section( 'alt_logo_section' , array(
    'title'       => __( 'Logo', 'alt' ),
    'priority'    => 30,
    'description' => 'Upload a logo to replace the default site name and description in the header',
	) );
	
	// Register our new setting.
	$wp_customize->add_setting( 'alt_logo' );
	
	// Tell the Theme Customizer to let us use an image uploader for setting our logo.
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'alt_logo', array(
    'label'    => __( 'Logo', 'alt' ),
    'section'  => 'alt_logo_section',
    'settings' => 'alt_logo',
	) ) );
}
add_action( 'customize_register', 'alt_customize_register' );

function lost_password_link(){	
	echo ('<p align="right"><a href="'.wp_lostpassword_url().'" title="Lost Password">Lost Password?</a></p>');	
	//echo ("<p>Online delegates can <a href=\"/register\" title=\"Create an account\">request an account</a> (if you've registered to attend the conference and have not received login details please use the <a href=\"javascript:void(0)\" onclick=\"usernoise.window.show()\">Feedback</a> form).</p>");
}
add_filter('bp_after_sidebar_login_form', 'lost_password_link');

function alt_widgets_init() {
	register_sidebar( array(
			'name' => 'Custom Header Widget Area',
			'id' => 'header-area',
			'description' => 'Header widget area',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );
	register_sidebar( array(
			'name' => 'Custom Footer Widget Area',
			'id' => 'footer-area',
			'description' => 'Footer Widget Area',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );
}
add_action( 'widgets_init', 'alt_widgets_init' );

function addFooterWidget(){
?>
<div id="footer-widget-area">
   <?php dynamic_sidebar( 'Custom Footer Widget Area' ); ?>
</div>     
<?
}
add_filter('bp_footer', 'addFooterWidget');

function addHeaderWidget(){
?>
<div id="header-widget-area">
   <?php dynamic_sidebar( 'Custom Header Widget Area' ); ?>
</div>     
<?
}
add_filter('bp_before_header', 'addHeaderWidget');

function edit_profile_link($atts){
		extract( shortcode_atts( array(
	      'text' => 'edit your profile',
		  'link' => '/login/',
		  'link_post' => ''
     ), $atts ) );

	if ( is_user_logged_in() ) {
		$link = bp_core_get_user_domain(bp_loggedin_user_id()) . $link_post ;
	}
	$output = '<a href="'.$link.'">'.$text.'</a>';
	return $output;
}
add_shortcode('edit_profile_link', 'edit_profile_link');

function newsletter_subscription_notification_settings() {
	global $bp ;?>
	<table class="notification-settings zebra" id="groups-notification-settings">
	<thead>
		<tr>
			<th class="icon"></th>
			<th class="title">Newsletter Subscription</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td></td>
			<td><?php get_mailpress_mlink(bp_core_get_user_email( $bp->loggedin_user->userdata->ID )); ?></td>
		</tr>
	</tbody>
	</table>	
<?php
}
if (class_exists('MailPress') && function_exists('get_mailpress_mlink')){
	add_action( 'bp_notification_settings', 'newsletter_subscription_notification_settings' );
}

// http://wordpress.org/support/topic/some-very-useful-tips-for-mailpress
function get_mailpress_mlink($user_email) {
  global $wpdb;
  $mailpress_user_key = $wpdb->get_var($wpdb->prepare("SELECT confkey FROM wp_mailpress_users WHERE email = '%s';", $user_email));
  	echo 'To manage your conference newsletter subscription goto <a href="/newsletter-subscription/?action=mail_link&del=' . $mailpress_user_key . '">Manage Newsletter Subscriptions</a>';
}

/**
 * Nav Menu Dropdown
 *
 * @package      BE_Genesis_Child
 * @since        1.0.0
 * @link         https://github.com/billerickson/BE-Genesis-Child
 * @author       Bill Erickson <bill@billerickson.net>
 * @copyright    Copyright (c) 2011, Bill Erickson
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 */
 
class Walker_Nav_Menu_Dropdown extends Walker_Nav_Menu {
	function start_lvl(&$output, $depth = 0, $args = array()){
		$indent = str_repeat("\t", $depth); // don't output children opening tag (`<ul>`)
	}
 
	function end_lvl(&$output, $depth = 0, $args = array()){
		$indent = str_repeat("\t", $depth); // don't output children closing tag
	}
 
	/**
	* Start the element output.
	*
	* @param  string $output Passed by reference. Used to append additional content.
	* @param  object $item   Menu item data object.
	* @param  int $depth     Depth of menu item. May be used for padding.
	* @param  array $args    Additional strings.
	* @return void
	*/
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0 ) {
 		$url = '#' !== $item->url ? $item->url : '';
 		$output .= '<option value="' . $url . '">' . str_repeat("—",$depth) .' '. $item->title;
	}	
 
	function end_el(&$output, $item, $depth = 0, $args = array()){
		$output .= "</option>\n"; // replace closing </li> with the option tag
	}
}